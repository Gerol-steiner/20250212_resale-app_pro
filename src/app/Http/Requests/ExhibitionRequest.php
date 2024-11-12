<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'item_name' => 'required|string|max:255', // 商品名: 必須、文字列、最大255文字
            'item_description' => 'required|string|max:255', // 商品説明: 必須、文字列、最大255文字
            'item_image' => 'required|image|mimes:jpeg,png', // 商品画像: 必須、画像、拡張子がjpegまたはpng
            'category_ids' => 'required|array|min:1', // 商品のカテゴリー: 必須、配列、1つ以上の選択
            'condition_id' => 'required|exists:conditions,id', // 商品の状態: 必須、条件テーブルのIDが存在すること
            'item_price' => 'required|numeric|min:0|max:1000000', // 商品価格: 必須、数値型、0円以上
            'cropped_image' => 'required|string', // クロップ後の画像データ: 必須、文字列
        ];
    }

    public function messages()
    {
        return [
            'item_name.required' => '商品名を入力してください',
            'item_description.required' => '商品の説明を入力してください',
            'item_description.max' => '255文字以内で入力してください',
            'item_image.required' => '商品画像をアップロードしてください',
            'item_image.image' => '商品画像は画像ファイルでなければなりません',
            'item_image.mimes' => '商品画像はjpegまたはpng形式を選択してください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
            'category_ids.array' => 'カテゴリーは配列で指定してください',
            'category_ids.min' => '少なくとも1つのカテゴリーを選択してください',
            'condition_id.required' => '商品の状態を選択してください',
            'condition_id.exists' => '指定された商品の状態は無効です',
            'item_price.required' => '販売価格を入力してください',
            'item_price.numeric' => '数値で入力してください',
            'item_price.min' => '販売価格は0以上で入力してください',
            'item_price.max' => '販売価格は100万円までです', 
            'cropped_image.required' => 'クロップ後の画像データは必須です',
        ];
    }
}
