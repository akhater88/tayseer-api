<?php

namespace App\Http\Requests\Auth;

use App\Enums\BeardType;
use App\Enums\BodyType;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\HijabType;
use App\Enums\MaritalStatus;
use App\Enums\PrayerLevel;
use App\Enums\ReligiousLevel;
use App\Enums\SkinColor;
use App\Enums\Smoking;
use App\Services\ContentFilterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // User data
            'username' => [
                'required',
                'string',
                'min:4',
                'max:15',
                'regex:/^[a-zA-Z0-9._]+$/',
                'not_regex:/^\./',      // Cannot start with dot
                'not_regex:/\.$/',      // Cannot end with dot
                'not_regex:/\.\./',     // No consecutive dots
                'unique:users,username',
                function ($attribute, $value, $fail) {
                    // Profanity check
                    $profanityWords = [
                        'fuck', 'shit', 'ass', 'dick', 'bitch', 'porn', 'sex', 'xxx',
                        'sharmouta', 'sharmota', 'kalb', 'khanzeir', 'manyak', 'kuss', 'air',
                    ];
                    foreach ($profanityWords as $word) {
                        if (stripos($value, $word) !== false) {
                            $fail('اسم المستخدم يحتوي على كلمات غير لائقة');
                            return;
                        }
                    }
                },
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{10,15}$/',
                'unique:users,phone',
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers(),
                'confirmed',
            ],
            'gender' => ['required', Rule::enum(Gender::class)],

            // Profile data - Demographics
            'date_of_birth' => [
                'required',
                'date',
                'before:-18 years',
                'after:-80 years',
            ],
            'nationality_id' => ['nullable', 'exists:nationalities,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],

            // Marital
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)],
            'number_of_children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'number_of_wives' => ['nullable', 'integer', 'min:0', 'max:4'],

            // Physical (optional for MVP)
            'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['nullable', 'integer', 'min:30', 'max:300'],
            'skin_color' => ['nullable', Rule::enum(SkinColor::class)],
            'body_type' => ['nullable', Rule::enum(BodyType::class)],

            // Religious
            'religious_level' => ['required', Rule::enum(ReligiousLevel::class)],
            'prayer_level' => ['required', Rule::enum(PrayerLevel::class)],
            'smoking' => ['nullable', Rule::enum(Smoking::class)],

            // Career
            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'work_field_id' => ['nullable', 'exists:work_fields,id'],
            'job_title' => ['nullable', 'string', 'max:100'],

            // Bio
            'about_me' => [
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
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if ($value && ContentFilterService::containsContactInfo($value)) {
                        $fail('لا يمكن إضافة معلومات التواصل في المواصفات');
                    }
                },
            ],

            // Private
            'full_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'unique:users,email'],

            // Agreements
            'declaration_accepted' => ['required', 'accepted'],
            'terms_accepted' => ['required', 'accepted'],
        ];

        // Gender-specific rules
        if ($this->gender === 'male' || $this->gender === Gender::Male->value) {
            $rules['beard_type'] = ['nullable', Rule::enum(BeardType::class)];
            // Number of wives only for males
            $rules['number_of_wives'] = ['nullable', 'integer', 'min:0', 'max:4'];
        }

        if ($this->gender === 'female' || $this->gender === Gender::Female->value) {
            $rules['hijab_type'] = ['nullable', Rule::enum(HijabType::class)];
            $rules['is_convert'] = ['required', 'boolean'];

            // Guardian info (required for non-convert females)
            if ($this->is_convert === false || $this->is_convert === 'false' || $this->is_convert === '0' || $this->is_convert === 0) {
                $rules['guardian_name'] = ['required', 'string', 'max:100'];
                $rules['guardian_phone'] = ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'];
                $rules['guardian_relationship'] = ['required', Rule::enum(GuardianRelationship::class)];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.required' => 'اسم المستخدم مطلوب',
            'username.min' => 'اسم المستخدم يجب أن يكون 4 أحرف على الأقل',
            'username.max' => 'اسم المستخدم يجب ألا يتجاوز 15 حرفاً',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً',
            'username.regex' => 'اسم المستخدم يجب أن يحتوي على حروف وأرقام ونقاط فقط',
            'username.not_regex' => 'صيغة اسم المستخدم غير صحيحة',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique' => 'رقم الهاتف مسجل مسبقاً',
            'phone.regex' => 'صيغة رقم الهاتف غير صحيحة',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'gender.required' => 'الجنس مطلوب',
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب',
            'date_of_birth.before' => 'يجب أن يكون عمرك 18 سنة على الأقل',
            'date_of_birth.after' => 'يجب أن يكون عمرك أقل من 80 سنة',
            'country_id.required' => 'الدولة مطلوبة',
            'country_id.exists' => 'الدولة غير موجودة',
            'city_id.required' => 'المدينة مطلوبة',
            'city_id.exists' => 'المدينة غير موجودة',
            'marital_status.required' => 'الحالة الاجتماعية مطلوبة',
            'religious_level.required' => 'مستوى الالتزام الديني مطلوب',
            'prayer_level.required' => 'مستوى الصلاة مطلوب',
            'about_me.min' => 'الوصف يجب أن يكون 50 حرفاً على الأقل',
            'about_me.max' => 'الوصف يجب ألا يتجاوز 500 حرف',
            'is_convert.required' => 'يرجى الإجابة على سؤال المسلمة الجديدة',
            'guardian_name.required' => 'اسم ولي الأمر مطلوب',
            'guardian_phone.required' => 'رقم هاتف ولي الأمر مطلوب',
            'guardian_phone.regex' => 'صيغة رقم هاتف ولي الأمر غير صحيحة',
            'guardian_relationship.required' => 'صلة القرابة بولي الأمر مطلوبة',
            'declaration_accepted.required' => 'يجب الموافقة على الإقرار',
            'declaration_accepted.accepted' => 'يجب الموافقة على الإقرار',
            'terms_accepted.required' => 'يجب الموافقة على الشروط والأحكام',
            'terms_accepted.accepted' => 'يجب الموافقة على الشروط والأحكام',
        ];
    }
}
