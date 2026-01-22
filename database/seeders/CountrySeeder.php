<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // GCC Countries (priority)
            ['name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA', 'phone_code' => '+966', 'sort_order' => 1],
            ['name_ar' => 'الإمارات', 'name_en' => 'United Arab Emirates', 'code' => 'AE', 'phone_code' => '+971', 'sort_order' => 2],
            ['name_ar' => 'الكويت', 'name_en' => 'Kuwait', 'code' => 'KW', 'phone_code' => '+965', 'sort_order' => 3],
            ['name_ar' => 'قطر', 'name_en' => 'Qatar', 'code' => 'QA', 'phone_code' => '+974', 'sort_order' => 4],
            ['name_ar' => 'البحرين', 'name_en' => 'Bahrain', 'code' => 'BH', 'phone_code' => '+973', 'sort_order' => 5],
            ['name_ar' => 'عمان', 'name_en' => 'Oman', 'code' => 'OM', 'phone_code' => '+968', 'sort_order' => 6],

            // Levant
            ['name_ar' => 'الأردن', 'name_en' => 'Jordan', 'code' => 'JO', 'phone_code' => '+962', 'sort_order' => 7],
            ['name_ar' => 'لبنان', 'name_en' => 'Lebanon', 'code' => 'LB', 'phone_code' => '+961', 'sort_order' => 8],
            ['name_ar' => 'سوريا', 'name_en' => 'Syria', 'code' => 'SY', 'phone_code' => '+963', 'sort_order' => 9],
            ['name_ar' => 'فلسطين', 'name_en' => 'Palestine', 'code' => 'PS', 'phone_code' => '+970', 'sort_order' => 10],
            ['name_ar' => 'العراق', 'name_en' => 'Iraq', 'code' => 'IQ', 'phone_code' => '+964', 'sort_order' => 11],

            // North Africa
            ['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG', 'phone_code' => '+20', 'sort_order' => 12],
            ['name_ar' => 'ليبيا', 'name_en' => 'Libya', 'code' => 'LY', 'phone_code' => '+218', 'sort_order' => 13],
            ['name_ar' => 'تونس', 'name_en' => 'Tunisia', 'code' => 'TN', 'phone_code' => '+216', 'sort_order' => 14],
            ['name_ar' => 'الجزائر', 'name_en' => 'Algeria', 'code' => 'DZ', 'phone_code' => '+213', 'sort_order' => 15],
            ['name_ar' => 'المغرب', 'name_en' => 'Morocco', 'code' => 'MA', 'phone_code' => '+212', 'sort_order' => 16],
            ['name_ar' => 'السودان', 'name_en' => 'Sudan', 'code' => 'SD', 'phone_code' => '+249', 'sort_order' => 17],

            // Other
            ['name_ar' => 'اليمن', 'name_en' => 'Yemen', 'code' => 'YE', 'phone_code' => '+967', 'sort_order' => 18],
            ['name_ar' => 'تركيا', 'name_en' => 'Turkey', 'code' => 'TR', 'phone_code' => '+90', 'sort_order' => 19],
            ['name_ar' => 'باكستان', 'name_en' => 'Pakistan', 'code' => 'PK', 'phone_code' => '+92', 'sort_order' => 20],
            ['name_ar' => 'الهند', 'name_en' => 'India', 'code' => 'IN', 'phone_code' => '+91', 'sort_order' => 21],
            ['name_ar' => 'إندونيسيا', 'name_en' => 'Indonesia', 'code' => 'ID', 'phone_code' => '+62', 'sort_order' => 22],
            ['name_ar' => 'ماليزيا', 'name_en' => 'Malaysia', 'code' => 'MY', 'phone_code' => '+60', 'sort_order' => 23],
            ['name_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom', 'code' => 'GB', 'phone_code' => '+44', 'sort_order' => 24],
            ['name_ar' => 'الولايات المتحدة', 'name_en' => 'United States', 'code' => 'US', 'phone_code' => '+1', 'sort_order' => 25],
            ['name_ar' => 'كندا', 'name_en' => 'Canada', 'code' => 'CA', 'phone_code' => '+1', 'sort_order' => 26],
            ['name_ar' => 'أستراليا', 'name_en' => 'Australia', 'code' => 'AU', 'phone_code' => '+61', 'sort_order' => 27],
            ['name_ar' => 'ألمانيا', 'name_en' => 'Germany', 'code' => 'DE', 'phone_code' => '+49', 'sort_order' => 28],
            ['name_ar' => 'فرنسا', 'name_en' => 'France', 'code' => 'FR', 'phone_code' => '+33', 'sort_order' => 29],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                array_merge($country, ['is_active' => true])
            );
        }
    }
}
