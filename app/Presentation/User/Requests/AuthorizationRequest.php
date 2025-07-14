<?php

namespace App\Presentation\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^(\+7|8)[0-9]{10}$/'
            ],
            'password' => 'required|string|min:8|regex:/[A-Z]/',
        ];
    }
    public function messages()
    {
        return [
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone field is required.',
            'phone.regex'    => 'Phone number must be in format +7XXXXXXXXXX or 8XXXXXXXXXX (11 digits).',

            'password.required' => 'The password field is required.',
            'password.string'   => 'The password field must be a string.',
            'password.min'     => 'Password must be at least 8 characters long.',
            'password.regex'    => 'Password must contain at least one uppercase letter.',
        ];
    }
}
