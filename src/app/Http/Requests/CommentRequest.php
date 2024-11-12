<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'content' => 'required|string|max:255', // contentカラムのバリデーション
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'コメントを入力してください', // 必須メッセージ
            'content.max' => '255文字以内で入力してください', // 255文字以内メッセージ
        ];
    }
}
