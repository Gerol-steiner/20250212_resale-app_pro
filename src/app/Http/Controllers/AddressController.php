<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address; // 追加
use App\Models\Item; // 追加
use Illuminate\Support\Facades\Auth;  // 認証済みかどうか確認用
use Illuminate\Support\Facades\Session;  // updateメソッドでセッションを使用するため
use App\Http\Requests\AddressRequest; // フォームリクエスト

class AddressController extends Controller
{
    public function edit($item_id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // アイテムを取得
        $item = Item::findOrFail($item_id);

        // ユーザーのデフォルト住所を取得
        $address = Address::where('user_id', auth()->id())
            ->where('is_default', 1)
            ->first();

        // 住所変更画面を表示
        return view('purchase.address', compact('item', 'address', 'isAuthenticated', 'userId'));
    }

    // 住所を更新するメソッド
    public function update(AddressRequest $request)
    {
        // ユーザーIDを取得
        $userId = Auth::id();

        // 新しい住所を作成
        $address = new Address();
        $address->user_id = $userId; // ユーザーIDを設定
        $address->postal_code = $request->postal_code; // フォームリクエストから取得
        $address->address = $request->address; // フォームリクエストから取得
        $address->building = $request->building; // フォームリクエストから取得
        $address->is_default = 0; // デフォルトではないと設定
        $address->save(); // データベースに保存

        // フラッシュデータをセッションに保存
        Session::flash('success', '住所が更新されました。');

        // 新しい住所をセッションに保存 （purchase.showメソッドに渡す）
        Session::put('previous_address', $address);

        // 商品購入画面への遷移
        return redirect()->route('purchase.show', ['id' => $request->item_id]); // 遷移先に必要なitem_idを渡す
    }
}