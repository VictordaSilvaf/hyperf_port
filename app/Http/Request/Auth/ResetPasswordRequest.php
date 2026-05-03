<?php

declare(strict_types=1);

namespace App\Http\Request\Auth;

use Hyperf\Validation\Request\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|regex:/^\d{6}$/',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'verification code',
            'password' => 'password',
        ];
    }
}
