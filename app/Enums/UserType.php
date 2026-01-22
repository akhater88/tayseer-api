<?php

namespace App\Enums;

enum UserType: string
{
    case Member = 'member';
    case Guardian = 'guardian';

    public function label(): string
    {
        return match ($this) {
            self::Member => __('enums.user_type.member'),
            self::Guardian => __('enums.user_type.guardian'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Member => 'عضو',
            self::Guardian => 'ولي أمر',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
