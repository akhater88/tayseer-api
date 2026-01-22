<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case Single = 'single';
    case Divorced = 'divorced';
    case Widowed = 'widowed';
    case Married = 'married'; // Only for males seeking additional wives

    public function label(): string
    {
        return match ($this) {
            self::Single => __('enums.marital_status.single'),
            self::Divorced => __('enums.marital_status.divorced'),
            self::Widowed => __('enums.marital_status.widowed'),
            self::Married => __('enums.marital_status.married'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Single => 'أعزب/عزباء',
            self::Divorced => 'مطلق/مطلقة',
            self::Widowed => 'أرمل/أرملة',
            self::Married => 'متزوج',
        };
    }

    public function labelArMale(): string
    {
        return match ($this) {
            self::Single => 'أعزب',
            self::Divorced => 'مطلق',
            self::Widowed => 'أرمل',
            self::Married => 'متزوج',
        };
    }

    public function labelArFemale(): string
    {
        return match ($this) {
            self::Single => 'عزباء',
            self::Divorced => 'مطلقة',
            self::Widowed => 'أرملة',
            self::Married => 'متزوجة',
        };
    }

    public static function forMale(): array
    {
        return [
            self::Single,
            self::Divorced,
            self::Widowed,
            self::Married,
        ];
    }

    public static function forFemale(): array
    {
        return [
            self::Single,
            self::Divorced,
            self::Widowed,
        ];
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
