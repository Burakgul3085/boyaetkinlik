<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'sort_order',
        'is_active',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @return Collection<int, self>
     */
    public function ancestorChain(): Collection
    {
        $chain = collect();
        $id = $this->parent_id;
        while ($id) {
            $row = static::query()->find($id);
            if (! $row) {
                break;
            }
            $chain->prepend($row);
            $id = $row->parent_id;
        }

        return new Collection($chain->all());
    }

    /**
     * Kategori + tüm alt kategori id'leri (liste filtreleri için).
     *
     * @return list<int>
     */
    public static function subtreeIdsIncludingSelf(int $rootId): array
    {
        $ids = [$rootId];
        $frontier = [$rootId];
        while ($frontier !== []) {
            $next = static::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();
            $frontier = $next;
            foreach ($next as $id) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<int>
     */
    public static function forbiddenParentIdsFor(self $category): array
    {
        return static::subtreeIdsIncludingSelf($category->id);
    }

    public static function allForAdminTree(): Collection
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, list<self>>
     */
    public static function childrenGroupedByParentId(Collection $all): array
    {
        $grouped = [];
        foreach ($all as $cat) {
            $key = $cat->parent_id ?? 0;
            $grouped[$key][] = $cat;
        }

        return $grouped;
    }

    /**
     * @return list<int>
     */
    public static function subtreeIdsFromCollection(Collection $all, int $rootId): array
    {
        $byParent = static::childrenGroupedByParentId($all);
        $ids = [$rootId];
        $frontier = [$rootId];
        while ($frontier !== []) {
            $next = [];
            foreach ($frontier as $pid) {
                foreach ($byParent[$pid] ?? [] as $child) {
                    $next[] = $child->id;
                    $ids[] = $child->id;
                }
            }
            $frontier = $next;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return SupportCollection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatWithDepth(?Collection $all = null): SupportCollection
    {
        return static::orderedFlatForParentSelect(null, $all);
    }

    /**
     * @return SupportCollection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatForParentSelect(?self $editing = null, ?Collection $all = null): SupportCollection
    {
        $all = $all ?? static::allForAdminTree();
        $forbidden = [];
        if ($editing !== null) {
            $forbidden = array_flip(static::subtreeIdsFromCollection($all, $editing->id));
        }

        $byParent = static::childrenGroupedByParentId($all);
        $out = collect();
        $walk = function (int $parentKey, int $depth) use (&$walk, &$out, $byParent, $forbidden): void {
            foreach ($byParent[$parentKey] ?? [] as $cat) {
                if (isset($forbidden[$cat->id])) {
                    continue;
                }
                $out->push(['id' => $cat->id, 'depth' => $depth, 'name' => $cat->name]);
                $walk($cat->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $out;
    }

    /**
     * @return array<int, string>
     */
    public static function parentBreadcrumbLabelsFor(Collection $all): array
    {
        $byId = $all->keyBy('id');
        $labels = [];
        foreach ($all as $cat) {
            if ($cat->parent_id === null) {
                $labels[$cat->id] = 'Ana kategori';

                continue;
            }
            $parts = [];
            $id = $cat->parent_id;
            $guard = 0;
            while ($id && $guard < 128) {
                $row = $byId->get($id);
                if (! $row) {
                    break;
                }
                array_unshift($parts, $row->name);
                $id = $row->parent_id;
                $guard++;
            }
            $labels[$cat->id] = $parts === [] ? 'Alt kategori' : 'Üst: '.implode(' → ', $parts);
        }

        return $labels;
    }

    /**
     * @return array<int, list<array{id: int, depth: int, name: string}>>
     */
    public static function parentSelectOptionsForAllEdits(Collection $all): array
    {
        $options = [];
        foreach ($all as $cat) {
            $options[$cat->id] = static::orderedFlatForParentSelect($cat, $all)->all();
        }

        return $options;
    }

    public static function adminSelectOptionLabel(int $depth, string $name): string
    {
        if ($depth <= 0) {
            return $name;
        }

        $indentUnit = "\u{2007}\u{2007}";

        return str_repeat($indentUnit, $depth)."\u{203A}\u{00A0}".$name;
    }

    /**
     * Her kategori için alt ağaçtaki blog sayısı (doğrudan + alt kategoriler).
     *
     * @return array<int, int>
     */
    public static function subtreeBlogCounts(Collection $all, bool $approvedOnly = false): array
    {
        $query = Blog::query();
        if ($approvedOnly) {
            $query->approved();
        }

        $direct = $query
            ->selectRaw('blog_category_id, COUNT(*) as aggregate')
            ->whereNotNull('blog_category_id')
            ->groupBy('blog_category_id')
            ->pluck('aggregate', 'blog_category_id')
            ->map(fn ($c) => (int) $c)
            ->all();

        $counts = [];
        foreach ($all as $cat) {
            $sum = 0;
            foreach (static::subtreeIdsFromCollection($all, $cat->id) as $id) {
                $sum += $direct[$id] ?? 0;
            }
            $counts[$cat->id] = $sum;
        }

        return $counts;
    }

    /**
     * Aktif kategori + üst zincir id'leri (filtre paneli açık tutmak için).
     *
     * @return list<int>
     */
    public static function activePathIds(?self $active): array
    {
        if ($active === null) {
            return [];
        }

        return array_values(array_unique(array_merge(
            $active->ancestorChain()->pluck('id')->all(),
            [$active->id]
        )));
    }

    /**
     * Ziyaretçi blog filtresi: kökten başlayan iç içe ağaç.
     *
     * @return list<array{category: self, children: array}>
     */
    public static function buildActiveFilterTree(Collection $all): array
    {
        $byParent = static::childrenGroupedByParentId($all);

        $build = function (int $parentKey) use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent[$parentKey] ?? [] as $cat) {
                $nodes[] = [
                    'category' => $cat,
                    'children' => $build($cat->id),
                ];
            }

            return $nodes;
        };

        return $build(0);
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public function breadcrumbItems(): array
    {
        return $this->ancestorChain()->map(fn (self $ancestor) => [
            'label' => $ancestor->name,
            'url' => route('blog.category', $ancestor),
        ])->push([
            'label' => $this->name,
            'url' => null,
        ])->all();
    }

    public static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name, '-', 'tr');
        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;
        $counter = 2;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function createFromName(string $name, string $source = 'admin', ?int $parentId = null): self
    {
        $name = trim($name);

        return static::query()->create([
            'name' => $name,
            'slug' => static::generateUniqueSlug($name),
            'parent_id' => $parentId,
            'source' => $source,
            'is_active' => true,
        ]);
    }
}
