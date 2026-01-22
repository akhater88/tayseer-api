<?php

namespace App\Enums;

enum InterestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.interest_status.pending'),
            self::Accepted => __('enums.interest_status.accepted'),
            self::Declined => __('enums.interest_status.declined'),
            self::Withdrawn => __('enums.interest_status.withdrawn'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Accepted => 'مقبول',
            self::Declined => 'مرفوض',
            self::Withdrawn => 'تم السحب',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Withdrawn => 'gray',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
