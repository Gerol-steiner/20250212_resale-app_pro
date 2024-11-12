<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method' => 'required|string|max:255', // 支払い方法は必須
            'postal_code' => 'required|regex:/^\d{3}-\d{4}$/', // ハイフンありの8桁
            'address' => 'required|string|max:255', // 住所は必須
            'building' => 'required|string|max:255', // 住所は必須
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'postal_code.required' => '郵便番号を入力してください',
            'address.required' => '住所を入力してください',
            'postal_code.regex' => '郵便番号は「xxx-xxxx」の形式で入力してください',
            'address.max' => '住所は255文字以内で入力してください',
            'building.required' => '建物名を入力してください',
            'building.max' => '建物名は255文字以内で入力してください',

        ];
    }
}

