<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Banned = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.user_status.pending'),
            self::Active => __('enums.user_status.active'),
            self::Suspended => __('enums.user_status.suspended'),
            self::Banned => __('enums.user_status.banned'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Active => 'نشط',
            self::Suspended => 'موقوف',
            self::Banned => 'محظور',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Suspended => 'danger',
            self::Banned => 'danger',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
