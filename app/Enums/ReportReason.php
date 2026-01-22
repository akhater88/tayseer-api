<?php

namespace App\Enums;

enum ReportReason: string
{
    case InappropriatePhotos = 'inappropriate_photos';
    case OffensiveContent = 'offensive_content';
    case FakeProfile = 'fake_profile';
    case Harassment = 'harassment';
    case ContactInfo = 'contact_info';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::InappropriatePhotos => __('enums.report_reason.inappropriate_photos'),
            self::OffensiveContent => __('enums.report_reason.offensive_content'),
            self::FakeProfile => __('enums.report_reason.fake_profile'),
            self::Harassment => __('enums.report_reason.harassment'),
            self::ContactInfo => __('enums.report_reason.contact_info'),
            self::Other => __('enums.report_reason.other'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::InappropriatePhotos => 'صور غير لائقة',
            self::OffensiveContent => 'محتوى مسيء',
            self::FakeProfile => 'انتحال شخصية',
            self::Harassment => 'تحرش أو إزعاج',
            self::ContactInfo => 'معلومات اتصال في الملف',
            self::Other => 'سبب آخر',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
