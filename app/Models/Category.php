<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'nav_order',
        'show_in_nav',
        'icon_path',
        'cover_image_path',
    ];

    protected function casts(): array
    {
        return [
            'show_in_nav' => 'boolean',
        ];
    }

    public function coloringPages(): HasMany
    {
        return $this->hasMany(ColoringPage::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('nav_order')->orderBy('name');
    }

    /** Header menüsü: yalnızca show_in_nav olanlar, sınırsız derinlik (eager). */
    public function childrenForNav(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('show_in_nav', true)
            ->orderBy('nav_order')
            ->orderBy('name');
    }

    public function childrenRecursiveForNav(): HasMany
    {
        return $this->childrenForNav()->with('childrenRecursiveForNav');
    }

    /**
     * Kökten bu düğüme kadar üst zincir (breadcrumb; kendisi hariç).
     *
     * @return Collection<int, Category>
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

        return $chain;
    }

    /**
     * @return list<array{label: string, url: string, children: array}>
     */
    public static function navMenuRoots(): array
    {
        $roots = static::query()
            ->whereNull('parent_id')
            ->where('show_in_nav', true)
            ->with('childrenRecursiveForNav')
            ->orderBy('nav_order')
            ->orderBy('name')
            ->get();

        return $roots->map(fn (self $c) => self::toNavNode($c))->values()->all();
    }

    /**
     * @return array{label: string, url: string, children: array}
     */
    public static function toNavNode(self $c): array
    {
        return [
            'label' => $c->name,
            'url' => route('categories.show', ['slug' => $c->slug]),
            'children' => $c->childrenRecursiveForNav
                ->map(fn (self $ch) => self::toNavNode($ch))
                ->values()
                ->all(),
        ];
    }

    /**
     * Kategori + tüm alt kategori id'leri (filtre ve kategori sayfası listeleri için).
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
     * Arama: kategori kaydının kendisi veya üst zincirindeki herhangi bir düğümde
     * (ad, açıklama, slug) verilen variantlardan biriyle eşleşen tüm kategori id'leri.
     * HomeController ana liste ve yan sonuçlar için.
     *
     * @param  list<string>  $variants  {@see HomeController::searchLikeVariants} çıktısı
     * @return list<int>
     */
    public static function idsWhereSelfOrAnyAncestorMatchesVariants(array $variants): array
    {
        $variants = array_values(array_unique(array_filter(array_map('trim', $variants))));
        if ($variants === []) {
            return [];
        }

        $cats = static::query()->get(['id', 'parent_id', 'name', 'description', 'slug']);
        if ($cats->isEmpty()) {
            return [];
        }

        $byId = $cats->keyBy('id');
        $matchedIds = [];

        foreach ($cats as $cat) {
            $node = $cat;
            $depth = 0;
            $found = false;
            while ($node !== null && $depth < 128) {
                foreach ($variants as $term) {
                    if (self::rowMatchesSearchTerm($node, $term)) {
                        $found = true;
                        break 2;
                    }
                }
                $pid = $node->parent_id;
                $node = $pid ? $byId->get($pid) : null;
                $depth++;
            }
            if ($found) {
                $matchedIds[] = $cat->id;
            }
        }

        return array_values(array_unique($matchedIds));
    }

    private static function rowMatchesSearchTerm(self $row, string $term): bool
    {
        if ($term === '') {
            return false;
        }
        foreach (['name', 'description', 'slug'] as $field) {
            $value = (string) $row->{$field};
            if ($value !== '' && mb_stripos($value, $term, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Üst kategori olarak seçilemez: kendisi + tüm altları (döngü önleme).
     *
     * @return list<int>
     */
    public static function forbiddenParentIdsFor(self $category): array
    {
        return static::subtreeIdsIncludingSelf($category->id);
    }

    /**
     * Boyama sayfası / anasayfa filtre: tüm kategoriler ağaç sırası + derinlik (girinti).
     *
     * @return Collection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatWithDepth(): Collection
    {
        $out = collect();
        $walk = function (?int $parentId, int $depth) use (&$walk, &$out): void {
            $q = static::query()->orderBy('nav_order')->orderBy('name');
            if ($parentId === null) {
                $q->whereNull('parent_id');
            } else {
                $q->where('parent_id', $parentId);
            }
            foreach ($q->get() as $cat) {
                $out->push(['id' => $cat->id, 'depth' => $depth, 'name' => $cat->name]);
                $walk($cat->id, $depth + 1);
            }
        };
        $walk(null, 0);

        return $out;
    }

    /**
     * Admin üst kategori seçimi: düzenlenen kayıt ve alt ağacı listeden çıkarılır.
     *
     * @return Collection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatForParentSelect(?self $editing = null): Collection
    {
        $forbidden = [];
        if ($editing !== null) {
            $forbidden = array_flip(static::forbiddenParentIdsFor($editing));
        }

        $out = collect();
        $walk = function (?int $parentId, int $depth) use (&$walk, &$out, $forbidden): void {
            $q = static::query()->orderBy('nav_order')->orderBy('name');
            if ($parentId === null) {
                $q->whereNull('parent_id');
            } else {
                $q->where('parent_id', $parentId);
            }
            foreach ($q->get() as $cat) {
                if (isset($forbidden[$cat->id])) {
                    continue;
                }
                $out->push(['id' => $cat->id, 'depth' => $depth, 'name' => $cat->name]);
                $walk($cat->id, $depth + 1);
            }
        };
        $walk(null, 0);

        return $out;
    }

    /** Admin rozet metni: üst zinciri (Ana kategori veya A → B → C). */
    public function parentBreadcrumbLabel(): string
    {
        if ($this->parent_id === null) {
            return 'Ana kategori';
        }
        $parts = [];
        $id = $this->parent_id;
        while ($id) {
            $row = static::query()->find($id);
            if (! $row) {
                break;
            }
            array_unshift($parts, $row->name);
            $id = $row->parent_id;
        }

        return $parts === [] ? 'Alt kategori' : 'Üst: '.implode(' → ', $parts);
    }
}
