<?php

namespace Database\Seeders;

use App\Models\WorkField;
use Illuminate\Database\Seeder;

class WorkFieldSeeder extends Seeder
{
    public function run(): void
    {
        $workFields = [
            ['name_ar' => 'الطب والصحة', 'name_en' => 'Healthcare & Medicine', 'sort_order' => 1],
            ['name_ar' => 'الهندسة', 'name_en' => 'Engineering', 'sort_order' => 2],
            ['name_ar' => 'تقنية المعلومات', 'name_en' => 'Information Technology', 'sort_order' => 3],
            ['name_ar' => 'التعليم', 'name_en' => 'Education', 'sort_order' => 4],
            ['name_ar' => 'المحاسبة والمالية', 'name_en' => 'Accounting & Finance', 'sort_order' => 5],
            ['name_ar' => 'القانون', 'name_en' => 'Law', 'sort_order' => 6],
            ['name_ar' => 'الإدارة', 'name_en' => 'Management', 'sort_order' => 7],
            ['name_ar' => 'التسويق', 'name_en' => 'Marketing', 'sort_order' => 8],
            ['name_ar' => 'الموارد البشرية', 'name_en' => 'Human Resources', 'sort_order' => 9],
            ['name_ar' => 'التجارة', 'name_en' => 'Business & Trade', 'sort_order' => 10],
            ['name_ar' => 'البنوك', 'name_en' => 'Banking', 'sort_order' => 11],
            ['name_ar' => 'العقارات', 'name_en' => 'Real Estate', 'sort_order' => 12],
            ['name_ar' => 'النفط والغاز', 'name_en' => 'Oil & Gas', 'sort_order' => 13],
            ['name_ar' => 'الصناعة', 'name_en' => 'Manufacturing', 'sort_order' => 14],
            ['name_ar' => 'البناء والمقاولات', 'name_en' => 'Construction', 'sort_order' => 15],
            ['name_ar' => 'الطيران', 'name_en' => 'Aviation', 'sort_order' => 16],
            ['name_ar' => 'السياحة والفندقة', 'name_en' => 'Tourism & Hospitality', 'sort_order' => 17],
            ['name_ar' => 'الإعلام', 'name_en' => 'Media', 'sort_order' => 18],
            ['name_ar' => 'التصميم', 'name_en' => 'Design', 'sort_order' => 19],
            ['name_ar' => 'الحكومي', 'name_en' => 'Government', 'sort_order' => 20],
            ['name_ar' => 'العسكري', 'name_en' => 'Military', 'sort_order' => 21],
            ['name_ar' => 'الأمن', 'name_en' => 'Security', 'sort_order' => 22],
            ['name_ar' => 'ربة منزل', 'name_en' => 'Homemaker', 'sort_order' => 23],
            ['name_ar' => 'طالب/طالبة', 'name_en' => 'Student', 'sort_order' => 24],
            ['name_ar' => 'متقاعد', 'name_en' => 'Retired', 'sort_order' => 25],
            ['name_ar' => 'أعمال حرة', 'name_en' => 'Self Employed', 'sort_order' => 26],
            ['name_ar' => 'أخرى', 'name_en' => 'Other', 'sort_order' => 100],
        ];

        foreach ($workFields as $workField) {
            WorkField::updateOrCreate(
                ['name_en' => $workField['name_en']],
                array_merge($workField, ['is_active' => true])
            );
        }
    }
}
