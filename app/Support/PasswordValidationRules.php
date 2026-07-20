<?php

namespace App\Support;

class PasswordValidationRules
{
    public static function rules(): array
    {
        return [
            'required',
            'string',
            'min:10',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[^A-Za-z0-9]/',
            'confirmed',
        ];
    }

    public static function messages(): array
    {
        return [
            'password.min' => 'The password must be at least 10 characters.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'The passwords do not match.',
        ];
    }
}
