<?php

namespace App\Presentation\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetOrCreateChatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'participants' => 'required|array',
            'type' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'participants.required' => 'You can not send empty participants',
            'type' => 'Type is required field',
        ];
    }
}
