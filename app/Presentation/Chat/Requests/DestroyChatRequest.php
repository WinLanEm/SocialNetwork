<?php

namespace App\Presentation\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyChatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'chat_id' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'chat_id.required' => 'You can not send empty chat_id',
            'chat_id.string' => 'Chat_id required type is string',
        ];
    }
}
