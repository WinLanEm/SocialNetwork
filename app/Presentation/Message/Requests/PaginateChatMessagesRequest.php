<?php

namespace App\Presentation\Message\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginateChatMessagesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'page' => 'required|integer',
            'chat_id' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'page.required' => 'Page is required param',
            'chat_id.required' => 'Chat id is required param',
        ];
    }
}
