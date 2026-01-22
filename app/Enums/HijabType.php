<?php

namespace App\Enums;

enum HijabType: string
{
    case Niqab = 'niqab';
    case Hijab = 'hijab';
    case NoHijab = 'no_hijab';

    public function label(): string
    {
        return match ($this) {
            self::Niqab => __('enums.hijab_type.niqab'),
            self::Hijab => __('enums.hijab_type.hijab'),
            self::NoHijab => __('enums.hijab_type.no_hijab'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Niqab => 'منتقبة',
            self::Hijab => 'محجبة',
            self::NoHijab => 'غير محجبة',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
