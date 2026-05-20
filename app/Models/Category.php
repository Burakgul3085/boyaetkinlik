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
     * Tüm kategoriler (admin / select için tek sorgu).
     */
    public static function allForAdminTree(): Collection
    {
        return static::query()
            ->orderBy('nav_order')
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
     * Boyama sayfası / anasayfa filtre: tüm kategoriler ağaç sırası + derinlik (bellek içi).
     *
     * @return Collection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatWithDepth(?Collection $all = null): Collection
    {
        return static::orderedFlatForParentSelect(null, $all);
    }

    /**
     * @return Collection<int, array{id: int, depth: int, name: string}>
     */
    public static function orderedFlatForParentSelect(?self $editing = null, ?Collection $all = null): Collection
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
     * Admin liste: her kategori id → üst kategori rozet metni (tek geçiş, ek sorgu yok).
     *
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
     * Düzenleme formları: kategori id → üst kategori select seçenekleri (bellek içi).
     *
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

    /**
     * Admin paneli select seçenek metni: derinlik figure space ile girintili, alt öğede tek › (çoklu tire yok).
     */
    public static function adminSelectOptionLabel(int $depth, string $name): string
    {
        if ($depth <= 0) {
            return $name;
        }

        $indentUnit = "\u{2007}\u{2007}";

        return str_repeat($indentUnit, $depth)."\u{203A}\u{00A0}".$name;
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
