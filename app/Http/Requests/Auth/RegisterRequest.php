<?php

namespace App\Http\Requests\Auth;

use App\Enums\BeardType;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\HijabType;
use App\Enums\MaritalStatus;
use App\Enums\PrayerLevel;
use App\Enums\ReligiousLevel;
use App\Enums\Smoking;
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
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username',
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

            // Profile data
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'nationality_id' => ['nullable', 'exists:nationalities,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)],
            'number_of_children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'religious_level' => ['required', Rule::enum(ReligiousLevel::class)],
            'prayer_level' => ['required', Rule::enum(PrayerLevel::class)],
            'smoking' => ['nullable', Rule::enum(Smoking::class)],
            'about_me' => ['nullable', 'string', 'max:500'],
        ];

        // Gender-specific rules
        if ($this->gender === 'male') {
            $rules['beard_type'] = ['nullable', Rule::enum(BeardType::class)];
        }

        if ($this->gender === 'female') {
            $rules['hijab_type'] = ['nullable', Rule::enum(HijabType::class)];
            $rules['is_convert'] = ['nullable', 'boolean'];

            // Guardian info (required for non-convert females)
            if (!$this->is_convert) {
                $rules['guardian_name'] = ['required_if:is_convert,false', 'nullable', 'string', 'max:100'];
                $rules['guardian_phone'] = ['required_if:is_convert,false', 'nullable', 'string', 'regex:/^\+?[0-9]{10,15}$/'];
                $rules['guardian_relationship'] = ['required_if:is_convert,false', 'nullable', Rule::enum(GuardianRelationship::class)];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.required' => 'اسم المستخدم مطلوب',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً',
            'username.regex' => 'اسم المستخدم يجب أن يحتوي على حروف وأرقام فقط',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique' => 'رقم الهاتف مسجل مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب',
            'date_of_birth.before' => 'يجب أن يكون عمرك 18 سنة على الأقل',
            'country_id.required' => 'الدولة مطلوبة',
            'city_id.required' => 'المدينة مطلوبة',
            'marital_status.required' => 'الحالة الاجتماعية مطلوبة',
            'religious_level.required' => 'مستوى الالتزام الديني مطلوب',
            'prayer_level.required' => 'مستوى الصلاة مطلوب',
            'guardian_name.required_if' => 'اسم ولي الأمر مطلوب',
            'guardian_phone.required_if' => 'رقم هاتف ولي الأمر مطلوب',
            'guardian_relationship.required_if' => 'صلة القرابة بولي الأمر مطلوبة',
        ];
    }
}
