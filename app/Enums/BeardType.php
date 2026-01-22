<?php

namespace App\Enums;

enum BeardType: string
{
    case FullBeard = 'full_beard';
    case LightBeard = 'light_beard';
    case NoBeard = 'no_beard';

    public function label(): string
    {
        return match ($this) {
            self::FullBeard => __('enums.beard_type.full_beard'),
            self::LightBeard => __('enums.beard_type.light_beard'),
            self::NoBeard => __('enums.beard_type.no_beard'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::FullBeard => 'ملتحي',
            self::LightBeard => 'لحية خفيفة',
            self::NoBeard => 'بدون لحية',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
