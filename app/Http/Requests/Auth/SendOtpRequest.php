<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'purpose' => ['sometimes', 'string', 'in:registration,login,password_reset'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف غير صالح',
            'purpose.in' => 'الغرض غير صالح',
        ];
    }
}
