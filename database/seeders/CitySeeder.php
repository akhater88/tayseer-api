<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            // Saudi Arabia
            'SA' => [
                ['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'sort_order' => 1],
                ['name_ar' => 'جدة', 'name_en' => 'Jeddah', 'sort_order' => 2],
                ['name_ar' => 'مكة المكرمة', 'name_en' => 'Makkah', 'sort_order' => 3],
                ['name_ar' => 'المدينة المنورة', 'name_en' => 'Madinah', 'sort_order' => 4],
                ['name_ar' => 'الدمام', 'name_en' => 'Dammam', 'sort_order' => 5],
                ['name_ar' => 'الخبر', 'name_en' => 'Khobar', 'sort_order' => 6],
                ['name_ar' => 'الظهران', 'name_en' => 'Dhahran', 'sort_order' => 7],
                ['name_ar' => 'الطائف', 'name_en' => 'Taif', 'sort_order' => 8],
                ['name_ar' => 'أبها', 'name_en' => 'Abha', 'sort_order' => 9],
                ['name_ar' => 'القصيم', 'name_en' => 'Qassim', 'sort_order' => 10],
                ['name_ar' => 'تبوك', 'name_en' => 'Tabuk', 'sort_order' => 11],
                ['name_ar' => 'حائل', 'name_en' => 'Hail', 'sort_order' => 12],
                ['name_ar' => 'الجبيل', 'name_en' => 'Jubail', 'sort_order' => 13],
                ['name_ar' => 'ينبع', 'name_en' => 'Yanbu', 'sort_order' => 14],
                ['name_ar' => 'نجران', 'name_en' => 'Najran', 'sort_order' => 15],
                ['name_ar' => 'جازان', 'name_en' => 'Jazan', 'sort_order' => 16],
            ],

            // UAE
            'AE' => [
                ['name_ar' => 'دبي', 'name_en' => 'Dubai', 'sort_order' => 1],
                ['name_ar' => 'أبوظبي', 'name_en' => 'Abu Dhabi', 'sort_order' => 2],
                ['name_ar' => 'الشارقة', 'name_en' => 'Sharjah', 'sort_order' => 3],
                ['name_ar' => 'عجمان', 'name_en' => 'Ajman', 'sort_order' => 4],
                ['name_ar' => 'رأس الخيمة', 'name_en' => 'Ras Al Khaimah', 'sort_order' => 5],
                ['name_ar' => 'الفجيرة', 'name_en' => 'Fujairah', 'sort_order' => 6],
                ['name_ar' => 'أم القيوين', 'name_en' => 'Umm Al Quwain', 'sort_order' => 7],
                ['name_ar' => 'العين', 'name_en' => 'Al Ain', 'sort_order' => 8],
            ],

            // Kuwait
            'KW' => [
                ['name_ar' => 'مدينة الكويت', 'name_en' => 'Kuwait City', 'sort_order' => 1],
                ['name_ar' => 'حولي', 'name_en' => 'Hawalli', 'sort_order' => 2],
                ['name_ar' => 'السالمية', 'name_en' => 'Salmiya', 'sort_order' => 3],
                ['name_ar' => 'الفروانية', 'name_en' => 'Farwaniya', 'sort_order' => 4],
                ['name_ar' => 'الجهراء', 'name_en' => 'Jahra', 'sort_order' => 5],
                ['name_ar' => 'الأحمدي', 'name_en' => 'Ahmadi', 'sort_order' => 6],
            ],

            // Jordan
            'JO' => [
                ['name_ar' => 'عمان', 'name_en' => 'Amman', 'sort_order' => 1],
                ['name_ar' => 'إربد', 'name_en' => 'Irbid', 'sort_order' => 2],
                ['name_ar' => 'الزرقاء', 'name_en' => 'Zarqa', 'sort_order' => 3],
                ['name_ar' => 'العقبة', 'name_en' => 'Aqaba', 'sort_order' => 4],
                ['name_ar' => 'السلط', 'name_en' => 'Salt', 'sort_order' => 5],
                ['name_ar' => 'جرش', 'name_en' => 'Jerash', 'sort_order' => 6],
                ['name_ar' => 'مادبا', 'name_en' => 'Madaba', 'sort_order' => 7],
                ['name_ar' => 'الكرك', 'name_en' => 'Karak', 'sort_order' => 8],
            ],

            // Qatar
            'QA' => [
                ['name_ar' => 'الدوحة', 'name_en' => 'Doha', 'sort_order' => 1],
                ['name_ar' => 'الوكرة', 'name_en' => 'Al Wakrah', 'sort_order' => 2],
                ['name_ar' => 'الخور', 'name_en' => 'Al Khor', 'sort_order' => 3],
                ['name_ar' => 'الريان', 'name_en' => 'Al Rayyan', 'sort_order' => 4],
            ],

            // Bahrain
            'BH' => [
                ['name_ar' => 'المنامة', 'name_en' => 'Manama', 'sort_order' => 1],
                ['name_ar' => 'المحرق', 'name_en' => 'Muharraq', 'sort_order' => 2],
                ['name_ar' => 'الرفاع', 'name_en' => 'Riffa', 'sort_order' => 3],
            ],

            // Egypt
            'EG' => [
                ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'sort_order' => 1],
                ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'sort_order' => 2],
                ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'sort_order' => 3],
                ['name_ar' => 'المنصورة', 'name_en' => 'Mansoura', 'sort_order' => 4],
                ['name_ar' => 'طنطا', 'name_en' => 'Tanta', 'sort_order' => 5],
                ['name_ar' => 'أسيوط', 'name_en' => 'Asyut', 'sort_order' => 6],
                ['name_ar' => 'الأقصر', 'name_en' => 'Luxor', 'sort_order' => 7],
                ['name_ar' => 'أسوان', 'name_en' => 'Aswan', 'sort_order' => 8],
            ],
        ];

        foreach ($cities as $countryCode => $countryCities) {
            $country = Country::where('code', $countryCode)->first();
            if (!$country) continue;

            foreach ($countryCities as $city) {
                City::updateOrCreate(
                    ['country_id' => $country->id, 'name_en' => $city['name_en']],
                    array_merge($city, ['country_id' => $country->id, 'is_active' => true])
                );
            }
        }
    }
}
