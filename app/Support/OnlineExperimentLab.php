<?php

namespace App\Support;

/**
 * Online deney laboratuvarı kataloğu — admin panelden bağımsız.
 */
class OnlineExperimentLab
{
    public const TYPE_WALK_WATER = 'yuruyen_renkler';

    public const TYPE_COLOR_MIX = 'renk_karistirma';

    public const TYPE_COLOR_WHEEL = 'renk_carki';

    public const TYPE_WARM_COOL = 'sicak_soguk';

    public const TYPE_LINE_TRACE = 'cizgi_tamamlama';

    public const TYPE_LETTER_TRACE = 'harf_tamamlama';

    public const TYPE_NUMBER_TRACE = 'sayi_tamamlama';

    /**
     * @return array<string, array{label: string, description: string, ready: bool}>
     */
    public static function types(): array
    {
        return [
            self::TYPE_WALK_WATER => [
                'label' => 'Kapiler renk karışımı',
                'description' => 'Yedi bardakta emilim ve renk birleşimi',
                'ready' => true,
            ],
            self::TYPE_COLOR_MIX => [
                'label' => 'Boya paleti karışımı',
                'description' => 'İki rengi seç, boyama paletinde karışımını gör',
                'ready' => true,
            ],
            self::TYPE_COLOR_WHEEL => [
                'label' => 'Renk çarkı',
                'description' => 'Ana ve ara renkleri keşfet',
                'ready' => true,
            ],
            self::TYPE_WARM_COOL => [
                'label' => 'Sıcak & soğuk renkler',
                'description' => 'Renkleri grupla, kompozisyon öğren',
                'ready' => true,
            ],
            self::TYPE_LINE_TRACE => [
                'label' => 'Çizgi tamamlama',
                'description' => 'Çizgiyi takip et, çizimi tamamla',
                'ready' => true,
            ],
            self::TYPE_LETTER_TRACE => [
                'label' => 'Harf çizgi stüdyosu',
                'description' => 'Türkçe harf yollarını takip ederek yazmaya hazırlan',
                'ready' => true,
            ],
            self::TYPE_NUMBER_TRACE => [
                'label' => 'Sayı çizgi stüdyosu',
                'description' => '0–9 rakam yollarını takip ederek sayı yaz',
                'ready' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *     slug: string,
     *     type: string,
     *     title: string,
     *     excerpt: string,
     *     age_label: string,
     *     duration_label: string,
     *     sort_order: int,
     *     ready: bool,
     *     icon: string,
     *     article_slug: string|null
     * }>
     */
    public static function catalog(): array
    {
        return [
            'renk-karisim' => [
                'slug' => 'renk-karisim',
                'type' => self::TYPE_WALK_WATER,
                'title' => 'Renk Karışım Deneyi',
                'excerpt' => 'Yedi bardakta kapiler etki ile renklerin birleşmesini modelle.',
                'age_label' => '5–12 yaş',
                'duration_label' => '5–10 dk',
                'sort_order' => 0,
                'ready' => true,
                'icon' => '🧪',
                'article_slug' => null,
            ],
            'boya-paleti' => [
                'slug' => 'boya-paleti',
                'type' => self::TYPE_COLOR_MIX,
                'title' => 'İki Renk Karışım Stüdyosu',
                'excerpt' => 'İki rengi seç, paletinde nasıl birleştiğini keşfet.',
                'age_label' => '4–12 yaş',
                'duration_label' => '3–5 dk',
                'sort_order' => 1,
                'ready' => true,
                'icon' => '🎨',
                'article_slug' => null,
            ],
            'renk-carki' => [
                'slug' => 'renk-carki',
                'type' => self::TYPE_COLOR_WHEEL,
                'title' => 'Renk Çarkı',
                'excerpt' => 'Ana renkleri (kırmızı, sarı, mavi) seç; ara renklerin (turuncu, yeşil, mor) nasıl oluştuğunu gör.',
                'age_label' => '3–8 yaş',
                'duration_label' => '3–5 dk',
                'sort_order' => 2,
                'ready' => true,
                'icon' => '🎯',
                'article_slug' => null,
            ],
            'sicak-soguk-renkler' => [
                'slug' => 'sicak-soguk-renkler',
                'type' => self::TYPE_WARM_COOL,
                'title' => 'Sıcak & Soğuk Renkler',
                'excerpt' => 'Renkleri güneş ve buz taraflarına yerleştir; boyama sayfalarında kompozisyon için sıcak-soğuk dengesini öğren.',
                'age_label' => '6–12 yaş',
                'duration_label' => '4–6 dk',
                'sort_order' => 3,
                'ready' => true,
                'icon' => '☀️',
                'article_slug' => null,
            ],
            'cizgi-tamamlama' => [
                'slug' => 'cizgi-tamamlama',
                'type' => self::TYPE_LINE_TRACE,
                'title' => 'Çizgi Tamamlama Stüdyosu',
                'excerpt' => 'Profesyonel çizgi çalışması: ev, kelebek, çiçek, yıldız veya dalga. Kalem kalınlığı seç, ilerlemeyi takip et.',
                'age_label' => '4–10 yaş',
                'duration_label' => '3–8 dk',
                'sort_order' => 4,
                'ready' => true,
                'icon' => '✏️',
                'article_slug' => null,
            ],
            'harf-cizgi-studyosu' => [
                'slug' => 'harf-cizgi-studyosu',
                'type' => self::TYPE_LETTER_TRACE,
                'title' => 'Harf Çizgi Stüdyosu',
                'excerpt' => 'Türkçe alfabedeki büyük harflerin çizgi yollarını takip et. Ç, Ğ, İ, Ö, Ş, Ü dahil tüm harfler.',
                'age_label' => '4–8 yaş',
                'duration_label' => '5–12 dk',
                'sort_order' => 5,
                'ready' => true,
                'icon' => '🔤',
                'article_slug' => null,
            ],
            'sayi-cizgi-studyosu' => [
                'slug' => 'sayi-cizgi-studyosu',
                'type' => self::TYPE_NUMBER_TRACE,
                'title' => 'Sayı Çizgi Stüdyosu',
                'excerpt' => '0’dan 9’a rakam çizgi yolları. İlerleme halkası, kalem seçimi ve kutlama ile sayı yazımına hazırlan.',
                'age_label' => '4–7 yaş',
                'duration_label' => '4–10 dk',
                'sort_order' => 6,
                'ready' => true,
                'icon' => '🔢',
                'article_slug' => null,
            ],
        ];
    }

    public static function findBySlug(string $slug): ?array
    {
        $aliases = [
            'yuruyen-renkler' => 'renk-karisim',
        ];

        if (isset($aliases[$slug])) {
            $slug = $aliases[$slug];
        }

        return static::catalog()[$slug] ?? null;
    }

    /** @return list<array<string, mixed>> */
    public static function playable(): array
    {
        $items = array_values(array_filter(
            static::catalog(),
            fn (array $lab) => static::isPlayable($lab['type'] ?? null)
        ));

        usort($items, fn (array $a, array $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $items;
    }

    public static function playableCount(): int
    {
        return count(static::playable());
    }

    public static function isValidType(?string $type): bool
    {
        return $type !== null && $type !== '' && array_key_exists($type, static::types());
    }

    public static function isPlayable(?string $type): bool
    {
        if (! static::isValidType($type)) {
            return false;
        }

        return (bool) (static::types()[$type]['ready'] ?? false);
    }

    public static function typeLabel(?string $type): string
    {
        return static::types()[$type]['label'] ?? 'Deney';
    }

    public static function viteScriptForType(string $type): string
    {
        return match ($type) {
            self::TYPE_COLOR_MIX => 'resources/js/online-experiment-color-mix.js',
            self::TYPE_COLOR_WHEEL => 'resources/js/online-experiment-color-wheel.js',
            self::TYPE_WARM_COOL => 'resources/js/online-experiment-warm-cool.js',
            self::TYPE_LINE_TRACE => 'resources/js/online-experiment-line-trace.js',
            self::TYPE_LETTER_TRACE => 'resources/js/online-experiment-letter-trace.js',
            self::TYPE_NUMBER_TRACE => 'resources/js/online-experiment-number-trace.js',
            default => 'resources/js/online-experiment.js',
        };
    }

    public static function stagePartialForType(string $type): string
    {
        return match ($type) {
            self::TYPE_COLOR_MIX => 'frontend.experiments._online-play-palette-mix',
            self::TYPE_COLOR_WHEEL => 'frontend.experiments._online-play-color-wheel',
            self::TYPE_WARM_COOL => 'frontend.experiments._online-play-warm-cool',
            self::TYPE_LINE_TRACE, self::TYPE_LETTER_TRACE, self::TYPE_NUMBER_TRACE => 'frontend.experiments._online-play-trace-studio',
            default => 'frontend.experiments._online-play-walk-water',
        };
    }

    public static function traceStudioVariant(string $type): string
    {
        return match ($type) {
            self::TYPE_LETTER_TRACE => 'letter',
            self::TYPE_NUMBER_TRACE => 'number',
            default => 'shape',
        };
    }

    public static function modeLabelForType(string $type): string
    {
        return match ($type) {
            self::TYPE_COLOR_MIX => 'Boya paleti modeli',
            self::TYPE_COLOR_WHEEL => 'Renk çarkı',
            self::TYPE_WARM_COOL => 'Sıcak-soğuk renkler',
            self::TYPE_LINE_TRACE => 'Çizgi çalışması',
            self::TYPE_LETTER_TRACE => 'Harf çizgi stüdyosu',
            self::TYPE_NUMBER_TRACE => 'Sayı çizgi stüdyosu',
            default => '3D laboratuvar modeli',
        };
    }
}
