<?php

namespace App\Presentation\Message\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakeIsReadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'message_ids' => 'array|required',
            'user_id' => 'required|integer',
            'chat_id' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'user_id.required' => 'Page is required param',
            'chat_id.required' => 'Chat id is required param',
            'message_ids.required' => 'Messages id is required param',
            'message_ids.array' => 'Messages id is array',
        ];
    }
}
