<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'profile_name' => 'required|string|max:50',
            'profile_image' => 'image|mimes:jpeg,png|max:512',  // 画像ファイル形式のバリデーション
            'postal_code' => 'nullable|regex:/^\d{3}-\d{4}$/', // ハイフンありの8桁
        ];
    }

    public function messages()
    {
        return [
            'profile_name.required' => 'ユーザー名を入力してください',
            'profile_name.string' => 'ユーザー名は文字列で入力してください',
            'profile_name.max' => 'ユーザー名は50文字以内で入力してください',
            'profile_image.image' => '有効な画像ファイルを選択してください',
            'profile_image.mimes' => '画像はJPEGまたはPNG形式を選択してください',
            'profile_image.max' => '画像のサイズは0.5MBまでです',
            'postal_code.regex' => '郵便番号は「xxx-xxxx」の形式で入力してください',
        ];
    }
}
