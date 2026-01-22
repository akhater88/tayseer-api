<?php

namespace App\Enums;

enum BodyType: string
{
    case Slim = 'slim';
    case Athletic = 'athletic';
    case Average = 'average';
    case Curvy = 'curvy';
    case Heavy = 'heavy';

    public function label(): string
    {
        return match ($this) {
            self::Slim => __('enums.body_type.slim'),
            self::Athletic => __('enums.body_type.athletic'),
            self::Average => __('enums.body_type.average'),
            self::Curvy => __('enums.body_type.curvy'),
            self::Heavy => __('enums.body_type.heavy'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Slim => 'نحيف',
            self::Athletic => 'رياضي',
            self::Average => 'متوسط',
            self::Curvy => 'ممتلئ',
            self::Heavy => 'بدين',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
