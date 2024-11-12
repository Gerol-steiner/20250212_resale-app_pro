<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\AuthenticatedSessionController; // ログアウト処理用
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PaymentController; // Stripe決済用

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// ログアウト
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// 商品一覧ページへのルート
Route::get('/', [ItemController::class, 'index'])->name('item.index');

// 出品ページへのルート
Route::get('/sell', [ItemController::class, 'showSellForm'])->name('sell.show');

// 商品の出品
Route::post('/sell', [ItemController::class, 'store'])->name('item.store');

// 商品詳細ページの表示
Route::get('/item/{item_id}', [ItemController::class, 'showDetail'])->name('item.detail');

// 商品詳細ページ
// いいねのトグル処理を実行
// 指定された商品IDに対するいいねを追加/削除
Route::post('/like/{item_id}', [ItemController::class, 'toggleLike'])->middleware('auth');

// 商品詳細ページ
// コメント登録
Route::post('/item/{item_id}/comments', [CommentController::class, 'addComment'])->middleware('auth')->name('comments.add');

// 商品購入画面への遷移 (1)
Route::post('/purchase/{id}', [PurchaseController::class, 'purchase'])->name('purchase');

// 商品購入画面への遷移 (2) ※住所変更画面からのback
Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');

// 商品の購入 および サンクス画面への遷移
Route::post('/thanks', [PurchaseController::class, 'thanks'])->name('thanks');

// 住所変更画面への遷移ルート
Route::get('/purchase/address/{item_id}', [AddressController::class, 'edit'])->name('address.edit');

// 配送先住所の変更
Route::post('/address/update', [AddressController::class, 'update'])->name('address.update');

// マイページの表示
Route::get('/mypage', [ItemController::class, 'mypage'])->name('mypage.index');

// プロフィールの編集
Route::get('/mypage/profile', [UserController::class, 'show'])->name('mypage.profile');

// プロフィールの更新
Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('profile.update');



// Stripeのチェックアウトセッションを作成するルート
// 購入ボタンがクリックされたときにAjaxリクエストで呼び出される
Route::post('/create-checkout-session', [PurchaseController::class, 'createCheckoutSession'])->name('checkout.session');

// Stripe決済が成功した後のリダイレクト先ルート
// 決済成功後の処理（purchasesテーブルへの登録、thanksページの表示）
Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');

// フォームリクエストPurchaseRequestによるバリデーション用のルート
Route::post('/validate-purchase', [PurchaseController::class, 'validatePurchase'])->name('validate.purchase');

