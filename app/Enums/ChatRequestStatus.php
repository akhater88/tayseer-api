<?php

namespace App\Enums;

enum ChatRequestStatus: string
{
    case PendingFemale = 'pending_female';
    case PendingGuardian = 'pending_guardian';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingFemale => __('enums.chat_request_status.pending_female'),
            self::PendingGuardian => __('enums.chat_request_status.pending_guardian'),
            self::Approved => __('enums.chat_request_status.approved'),
            self::Rejected => __('enums.chat_request_status.rejected'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::PendingFemale => 'في انتظار موافقتها',
            self::PendingGuardian => 'في انتظار ولي الأمر',
            self::Approved => 'تمت الموافقة',
            self::Rejected => 'مرفوض',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingFemale => 'warning',
            self::PendingGuardian => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
