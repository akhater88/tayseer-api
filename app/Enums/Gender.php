<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('enums.gender.male'),
            self::Female => __('enums.gender.female'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Male => 'رجل',
            self::Female => 'امرأة',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
