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
            'chat' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'chat.required' => 'You can not send empty chat_id',
            'chat.string' => 'Chat_id required type is string',
        ];
    }
}
