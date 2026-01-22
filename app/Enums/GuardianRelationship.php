<?php

namespace App\Enums;

enum GuardianRelationship: string
{
    case Father = 'father';
    case Brother = 'brother';
    case Son = 'son';
    case Uncle = 'uncle';
    case Grandfather = 'grandfather';

    public function label(): string
    {
        return match ($this) {
            self::Father => __('enums.guardian_relationship.father'),
            self::Brother => __('enums.guardian_relationship.brother'),
            self::Son => __('enums.guardian_relationship.son'),
            self::Uncle => __('enums.guardian_relationship.uncle'),
            self::Grandfather => __('enums.guardian_relationship.grandfather'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Father => 'أبي',
            self::Brother => 'أخي',
            self::Son => 'ابني',
            self::Uncle => 'عمي',
            self::Grandfather => 'جدي',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
