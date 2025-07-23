<?php

namespace App\Presentation\Message\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'content' => 'required|string|max:65535',
            'chat_id' => 'required|string',
            'temp_id' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'content.required' => 'You can not send empty message',
            'content.max' => 'Message length exceeds maximum',
            'chat_id.required' => 'Chat id is required param',
            'temp_id.required' => 'Temp id is required param',
        ];
    }
}
