<?php

namespace App\Support;

/**
 * Online deney laboratuvarı kataloğu — admin panelden bağımsız.
 * Yeni interaktif deney eklemek için buraya kayıt ekleyin ve JS modülünü yazın.
 */
class OnlineExperimentLab
{
    public const TYPE_WALK_WATER = 'yuruyen_renkler';

    public const TYPE_COLOR_MIX = 'renk_karistirma';

    /**
     * @return array<string, array{label: string, description: string, ready: bool}>
     */
    public static function types(): array
    {
        return [
            self::TYPE_WALK_WATER => [
                'label' => 'Yürüyen renkler (gökkuşağı)',
                'description' => 'Bardaklar, renkler ve kağıt havlu köprüleri',
                'ready' => true,
            ],
            self::TYPE_COLOR_MIX => [
                'label' => 'Renk karışımı',
                'description' => 'İki rengi karıştır, sonucu gör',
                'ready' => false,
            ],
        ];
    }

    /**
     * Salon kartları ve oyun sayfası — tek kaynak.
     *
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
            'yuruyen-renkler' => [
                'slug' => 'yuruyen-renkler',
                'type' => self::TYPE_WALK_WATER,
                'title' => 'Yürüyen renkler (gökkuşağı)',
                'excerpt' => 'Bardaklara renk ekle, kağıt havlu köprüleri kur ve renklerin birleşmesini izle.',
                'age_label' => '5–9 yaş',
                'duration_label' => '5–10 dk',
                'sort_order' => 0,
                'ready' => true,
                'icon' => '🌈',
                'article_slug' => null,
            ],
        ];
    }

    public static function findBySlug(string $slug): ?array
    {
        return static::catalog()[$slug] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
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
}
