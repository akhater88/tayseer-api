<?php

namespace App\Enums;

enum Smoking: string
{
    case No = 'no';
    case Yes = 'yes';
    case Occasionally = 'occasionally';
    case Quit = 'quit';

    public function label(): string
    {
        return match ($this) {
            self::No => __('enums.smoking.no'),
            self::Yes => __('enums.smoking.yes'),
            self::Occasionally => __('enums.smoking.occasionally'),
            self::Quit => __('enums.smoking.quit'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::No => 'لا',
            self::Yes => 'نعم',
            self::Occasionally => 'أحياناً',
            self::Quit => 'أقلعت',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
