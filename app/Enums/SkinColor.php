<?php

namespace App\Enums;

enum SkinColor: string
{
    case VeryLight = 'very_light';
    case Light = 'light';
    case Wheatish = 'wheatish';
    case Brown = 'brown';
    case Dark = 'dark';

    public function label(): string
    {
        return match ($this) {
            self::VeryLight => __('enums.skin_color.very_light'),
            self::Light => __('enums.skin_color.light'),
            self::Wheatish => __('enums.skin_color.wheatish'),
            self::Brown => __('enums.skin_color.brown'),
            self::Dark => __('enums.skin_color.dark'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::VeryLight => 'أبيض جداً',
            self::Light => 'أبيض',
            self::Wheatish => 'حنطي',
            self::Brown => 'أسمر',
            self::Dark => 'داكن',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
