<?php

namespace App\Support;

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
