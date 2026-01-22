<?php

namespace App\Enums;

enum ReligiousLevel: string
{
    case VeryReligious = 'very_religious';
    case Religious = 'religious';
    case Moderate = 'moderate';
    case NotReligious = 'not_religious';

    public function label(): string
    {
        return match ($this) {
            self::VeryReligious => __('enums.religious_level.very_religious'),
            self::Religious => __('enums.religious_level.religious'),
            self::Moderate => __('enums.religious_level.moderate'),
            self::NotReligious => __('enums.religious_level.not_religious'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::VeryReligious => 'ملتزم جداً',
            self::Religious => 'متدين',
            self::Moderate => 'متوسط',
            self::NotReligious => 'غير ملتزم',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
