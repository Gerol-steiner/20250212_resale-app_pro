<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required_without:image|string|max:400',
            'image' => 'nullable|image|mimes:jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'message.required_without' => '本文を入力してください',
            'message.max' => '本文は400文字以内にしてください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
