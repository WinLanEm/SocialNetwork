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
            'consumer_id' => 'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'content.required' => 'You can not send empty message',
            'content.max' => 'Message length exceeds maximum',
            'consumer_id.required' => 'Consumer id is required param',
        ];
    }
}
