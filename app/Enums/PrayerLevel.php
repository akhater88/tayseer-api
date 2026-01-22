<?php

namespace App\Enums;

enum PrayerLevel: string
{
    case AllPrayers = 'all_prayers';
    case MostPrayers = 'most_prayers';
    case SomePrayers = 'some_prayers';
    case Rarely = 'rarely';
    case Never = 'never';

    public function label(): string
    {
        return match ($this) {
            self::AllPrayers => __('enums.prayer_level.all_prayers'),
            self::MostPrayers => __('enums.prayer_level.most_prayers'),
            self::SomePrayers => __('enums.prayer_level.some_prayers'),
            self::Rarely => __('enums.prayer_level.rarely'),
            self::Never => __('enums.prayer_level.never'),
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::AllPrayers => 'جميع الفروض',
            self::MostPrayers => 'معظم الفروض',
            self::SomePrayers => 'بعض الفروض',
            self::Rarely => 'نادراً',
            self::Never => 'لا يصلي',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
