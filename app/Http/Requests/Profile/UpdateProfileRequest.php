<?php

namespace App\Http\Requests\Profile;

use App\Enums\BeardType;
use App\Enums\BodyType;
use App\Enums\EducationLevel;
use App\Enums\HijabType;
use App\Enums\MaritalStatus;
use App\Enums\PrayerLevel;
use App\Enums\ReligiousLevel;
use App\Enums\SkinColor;
use App\Enums\Smoking;
use App\Services\ContentFilterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            // User fields (limited)
            'username' => [
                'sometimes',
                'required',
                'string',
                'min:4',
                'max:15',
                'regex:/^[a-zA-Z0-9._]+$/',
                'not_regex:/^\./',
                'not_regex:/\.$/',
                'not_regex:/\.\./',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            // Profile - Demographics
            'full_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before:-18 years', 'after:-80 years'],
            'nationality_id' => ['sometimes', 'nullable', 'exists:nationalities,id'],
            'country_id' => ['sometimes', 'required', 'exists:countries,id'],
            'city_id' => ['sometimes', 'required', 'exists:cities,id'],

            // Marital
            'marital_status' => ['sometimes', 'required', Rule::enum(MaritalStatus::class)],
            'number_of_children' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:20'],

            // Physical
            'height_cm' => ['sometimes', 'nullable', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['sometimes', 'nullable', 'integer', 'min:30', 'max:300'],
            'skin_color' => ['sometimes', 'nullable', Rule::enum(SkinColor::class)],
            'body_type' => ['sometimes', 'nullable', Rule::enum(BodyType::class)],

            // Religious
            'religious_level' => ['sometimes', 'required', Rule::enum(ReligiousLevel::class)],
            'prayer_level' => ['sometimes', 'required', Rule::enum(PrayerLevel::class)],
            'smoking' => ['sometimes', 'nullable', Rule::enum(Smoking::class)],

            // Career
            'education_level' => ['sometimes', 'nullable', Rule::enum(EducationLevel::class)],
            'work_field_id' => ['sometimes', 'nullable', 'exists:work_fields,id'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:100'],

            // Bio
            'about_me' => [
                'sometimes',
                'nullable',
                'string',
                'min:50',
                'max:500',
                function ($attribute, $value, $fail) {
                    if ($value && ContentFilterService::containsContactInfo($value)) {
                        $fail('لا يمكن إضافة معلومات التواصل في الوصف');
                    }
                },
            ],
            'partner_preferences' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if ($value && ContentFilterService::containsContactInfo($value)) {
                        $fail('لا يمكن إضافة معلومات التواصل في المواصفات');
                    }
                },
            ],
        ];

        // Gender-specific rules
        if ($user->isMale()) {
            $rules['beard_type'] = ['sometimes', 'nullable', Rule::enum(BeardType::class)];
            $rules['number_of_wives'] = ['sometimes', 'nullable', 'integer', 'min:0', 'max:4'];
        } else {
            $rules['hijab_type'] = ['sometimes', 'nullable', Rule::enum(HijabType::class)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.min' => 'اسم المستخدم يجب أن يكون 4 أحرف على الأقل',
            'username.max' => 'اسم المستخدم يجب ألا يتجاوز 15 حرفاً',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً',
            'username.regex' => 'اسم المستخدم يجب أن يحتوي على حروف وأرقام ونقاط فقط',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
            'date_of_birth.before' => 'يجب أن يكون عمرك 18 سنة على الأقل',
            'date_of_birth.after' => 'يجب أن يكون عمرك أقل من 80 سنة',
            'country_id.exists' => 'الدولة غير موجودة',
            'city_id.exists' => 'المدينة غير موجودة',
            'about_me.min' => 'الوصف يجب أن يكون 50 حرفاً على الأقل',
            'about_me.max' => 'الوصف يجب ألا يتجاوز 500 حرف',
        ];
    }
}
