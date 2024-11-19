<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // 認証済みかどうか確認用
use App\Models\Item; // 追加
use App\Models\Purchase; // 追加
use App\Models\Address; // 追加
use App\Http\Requests\PurchaseRequest; // フォームリクエスト
use Illuminate\Support\Facades\Log; // 追加（デバッグ用）

use Stripe\Stripe;  // Stripe決済用
use Stripe\Checkout\Session; // Stripe決済用
use Illuminate\Support\Facades\Config;  // Stripe決済用

class PurchaseController extends Controller
{
    // 商品購入画面の表示
    public function purchase($id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // アイテムを取得
        $item = Item::findOrFail($id);

        // 商品が購入済みかどうかを判定
        $item->isPurchased = $item->purchases->isNotEmpty();

        // デフォルトの住所を取得
        $address = Address::where('user_id', $userId)
            ->where('is_default', 1)
            ->first();

        return view('items.purchase', compact('item', 'isAuthenticated', 'userId', 'address'));
    }

    // 住所変更後の商品購入画面の表示
    public function show($id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // アイテムを取得
        $item = Item::findOrFail($id);

        // 商品が購入済みかどうかを判定
        $item->isPurchased = $item->purchases->isNotEmpty();

        // PurchaseRequestのエラーがあるかどうかを確認
        // 「商品購入画面」におけるthanksメソッドのPurchaseRequestエラーにおいてもgetメソッドとなる
        // よってこのshowメソッドが呼び出される
        $errors = session('errors');

        // フォームリクエストエラーがあれば、直前の住所をセッションから取得
        if ($errors) {
            // バリデーションエラー時には、リクエストから住所情報を取得
            $address = new Address();
            $address->id = old('id');
            $address->postal_code = old('postal_code');
            $address->address = old('address');
            $address->building = old('building');
            $address->is_default = old('is_default');
        } else {
            // 通常の流れで住所変更画面からの遷移の場合
            // address.updateメソッドのセッションから直前の住所を取得
            $address = session('previous_address');

            // セッションに住所がない場合はデフォルトの住所を取得
            if (!$address) {
                $address = Address::where('user_id', $userId)
                    ->where('is_default', 1) // デフォルトの住所を取得
                    ->first();
            }
        }

        return view('items.purchase', compact('item', 'isAuthenticated', 'userId', 'address'));
    }

    // pusrchasesテーブルへの登録と<script>へのJsonレスポンス
    public function thanks(PurchaseRequest $request)
    {

        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // 支払い方法を取得
        $paymentMethod = $request->input('payment_method');

        // フォームからアイテムIDを取得
        $itemId = $request->input('item_id');

        // フォームリクエストから住所情報を取得
        $addressId = $request->input('address_id'); // address_idをリクエストから取得

        // purchasesテーブルに保存
        Purchase::create([
            'user_id' => $userId, // 現在のユーザーのID
            'item_id' => $itemId, // 現在の商品（item）のID
            'payment_method' => $paymentMethod, // 支払い方法
            'address_id' => $addressId, // フォームリクエストから取得したaddress_idを設定
        ]);

        // JSONレスポンスを返す（Jsonレスポンスを返さないとエラー。サンクス画面への遷移はフロントで行う）
        return response()->json(['success' => true, 'message' => '購入処理が完了しました。']);
    }

    // 以下Stripe決済用メソッド
    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
        // リクエストから取得
        $itemId = $request->input('item_id');
        $item = Item::findOrFail($itemId);
        $addressId = $request->input('address_id'); // 入力または選択された住所ID

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $item->price,
                    'product_data' => [
                        'name' => $item->name,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchaseComplete', [
                'item_id' => $itemId,// item_idをクエリパラメータに含める（stripe決済完了後に「」メソッドでデータベース登録する）
                'address_id' => $addressId, // address_idをクエリパラメータに含める（stripe決済完了後に「」メソッドでデータベース登録する）
            ]),
            'cancel_url' => route('item.index'),
        ]);
        return $session; // セッションオブジェクトを返す
    }

    // ユーザーがStripe決済処理後に以下のメソッドでpurchasesテーブルに登録してデータベース更新
    public function successRedirect(Request $request)
    {
        dd($request->all());

        $itemId = $request->input('item_id');
        $userId = Auth::id();
        $addressId = $request->session()->get('address_id');
        $paymentMethod = 'カード支払い';

        Purchase::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'payment_method' => $paymentMethod,
            'address_id' => $addressId,
        ]);

        return view('purchase.thanks', compact('userId'));
    }


    public function validatePurchase(PurchaseRequest $request)
    {
        // バリデーションが成功した場合、支払い方法によって処理を分岐する
        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'カード支払い') {
            // カード支払いの場合は、Stripeのチェックアウトセッションを作成する
            $session = $this->createCheckoutSession($request);
            // fetch関数によるクライアントサイドのJavaScriptコードに対してJSON形式でレスポンスを返す
            return response()->json([
                'success' => true,
                'session_id' => $session->id, // チェックアウトセッションのIDを返す
            ]);

        } elseif ($paymentMethod === 'コンビニ支払い') {
            // コンビニ支払いの場合は、thanksメソッドを呼び出す
            return $this->thanks($request);
        }

        // それ以外の支払い方法が選択された場合はエラーレスポンスを返す
        return response()->json(['success' => false, 'message' => '無効な支払い方法です。']);
    }

    // 「コンビニ支払い」のときにビューの「if (data.success)」から再び戻ってきた時の処理
    public function showThanksPage()
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        return view('purchase.thanks', compact('userId', 'isAuthenticated'));
    }

    public function completePurchase(Request $request)
    {

        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        $userId = Auth::id();
        $itemId = $request->input('item_id'); // クエリパラメータから取得
        $addressId = $request->input('address_id'); // クエリパラメータから取得
        $paymentMethod = 'カード支払い';

        Purchase::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'payment_method' => $paymentMethod,
            'address_id' => $addressId,
        ]);

        return view('purchase.thanks', compact('userId', 'isAuthenticated'));
    }
}