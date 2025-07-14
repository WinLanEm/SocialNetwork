<?php

namespace App\Presentation\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'username' => 'required|string|max:511|unique:users',
            'phone' => [
                'required',
                'regex:/^(\+7|8)[0-9]{10}$/',
                'unique:users',
            ],
            'password' => 'required|string|min:8|regex:/[A-Z]/',
            'password_confirmation' => 'required|string|same:password'
        ];
    }
    public function messages()
    {
        return [
            'username.required' => 'The username field is required.',
            'username.string'   => 'The username field must be a string.',
            'username.max'     => 'The username field must not exceed 511 characters.',
            'username.unique' => 'This username is already taken.',

            'phone.required' => 'The phone field is required.',
            'phone.regex'    => 'Phone number must be in format +7XXXXXXXXXX or 8XXXXXXXXXX (11 digits).',
            'phone.unique'    => 'This phone number is already registered.',

            'password.required' => 'The password field is required.',
            'password.string'   => 'The password field must be a string.',
            'password.min'     => 'Password must be at least 8 characters long.',
            'password.regex'    => 'Password must contain at least one uppercase letter.',

            'password_confirmation.required' => 'The confirm password field is required.',
            'password_confirmation.string'  => 'The confirm password field must be a string.',
            'password_confirmation.same'     => 'Passwords do not match.',
        ];
    }
}
