<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        $nationalities = [
            ['name_ar' => 'سعودي', 'name_en' => 'Saudi', 'sort_order' => 1],
            ['name_ar' => 'إماراتي', 'name_en' => 'Emirati', 'sort_order' => 2],
            ['name_ar' => 'كويتي', 'name_en' => 'Kuwaiti', 'sort_order' => 3],
            ['name_ar' => 'قطري', 'name_en' => 'Qatari', 'sort_order' => 4],
            ['name_ar' => 'بحريني', 'name_en' => 'Bahraini', 'sort_order' => 5],
            ['name_ar' => 'عماني', 'name_en' => 'Omani', 'sort_order' => 6],
            ['name_ar' => 'أردني', 'name_en' => 'Jordanian', 'sort_order' => 7],
            ['name_ar' => 'لبناني', 'name_en' => 'Lebanese', 'sort_order' => 8],
            ['name_ar' => 'سوري', 'name_en' => 'Syrian', 'sort_order' => 9],
            ['name_ar' => 'فلسطيني', 'name_en' => 'Palestinian', 'sort_order' => 10],
            ['name_ar' => 'عراقي', 'name_en' => 'Iraqi', 'sort_order' => 11],
            ['name_ar' => 'مصري', 'name_en' => 'Egyptian', 'sort_order' => 12],
            ['name_ar' => 'ليبي', 'name_en' => 'Libyan', 'sort_order' => 13],
            ['name_ar' => 'تونسي', 'name_en' => 'Tunisian', 'sort_order' => 14],
            ['name_ar' => 'جزائري', 'name_en' => 'Algerian', 'sort_order' => 15],
            ['name_ar' => 'مغربي', 'name_en' => 'Moroccan', 'sort_order' => 16],
            ['name_ar' => 'سوداني', 'name_en' => 'Sudanese', 'sort_order' => 17],
            ['name_ar' => 'يمني', 'name_en' => 'Yemeni', 'sort_order' => 18],
            ['name_ar' => 'تركي', 'name_en' => 'Turkish', 'sort_order' => 19],
            ['name_ar' => 'باكستاني', 'name_en' => 'Pakistani', 'sort_order' => 20],
            ['name_ar' => 'هندي', 'name_en' => 'Indian', 'sort_order' => 21],
            ['name_ar' => 'إندونيسي', 'name_en' => 'Indonesian', 'sort_order' => 22],
            ['name_ar' => 'ماليزي', 'name_en' => 'Malaysian', 'sort_order' => 23],
            ['name_ar' => 'بريطاني', 'name_en' => 'British', 'sort_order' => 24],
            ['name_ar' => 'أمريكي', 'name_en' => 'American', 'sort_order' => 25],
            ['name_ar' => 'كندي', 'name_en' => 'Canadian', 'sort_order' => 26],
            ['name_ar' => 'أسترالي', 'name_en' => 'Australian', 'sort_order' => 27],
            ['name_ar' => 'ألماني', 'name_en' => 'German', 'sort_order' => 28],
            ['name_ar' => 'فرنسي', 'name_en' => 'French', 'sort_order' => 29],
            ['name_ar' => 'أخرى', 'name_en' => 'Other', 'sort_order' => 100],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::updateOrCreate(
                ['name_en' => $nationality['name_en']],
                array_merge($nationality, ['is_active' => true])
            );
        }
    }
}
