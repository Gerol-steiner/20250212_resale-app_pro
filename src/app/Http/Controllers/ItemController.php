<?php

namespace App\Http\Controllers;

use App\Models\Item; // Itemモデルのインポート
use App\Models\Like; // Likeモデルのインポート
use App\Models\Purchase; // Purchaseモデルのインポート
use App\Models\Category; // Categoryモデルをインポート
use App\Models\Condition; // Conditionモデルをインポート
use Illuminate\Support\Facades\Auth;  // 認証済みかどうか確認用
use Illuminate\Support\Facades\Storage;  // 画像を保存のため
use App\Http\Requests\ExhibitionRequest; // フォームリクエスト
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // ■ 商品一覧ページの表示
    public function index(Request $request)
    {
        // クエリパラメータを取得
        $tab = $request->query('tab', 'home');  //tabがnullならhomeを使用
        $search = $request->query('search');

        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // マイリストが選択された場合
        if ($tab === 'mylist') {
            if (!$isAuthenticated) {
                // 未認証ユーザーがマイリストを選択した場合、空のアイテムリストを設定
                $items = collect();
            } else {
                // 認証済みユーザーがマイリストを選択した場合、いいねした商品を取得
                $likedItemsQuery = Like::where('user_id', $userId)->with('item');

                // 検索条件に基づいてフィルタリング
                if ($search) {
                    $likedItemsQuery->whereHas('item', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                }

                $items = $likedItemsQuery->get()->pluck('item');
            }
        } else {
            // アイテムのクエリを準備
            $query = Item::with('purchases');

            // 検索条件を追加
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            if ($isAuthenticated) {
                $items = $query->where('user_id', '!=', $userId)->get();
            } else {
                $items = $query->get();
            }
        }

        // 各アイテムに購入済みフラグを追加
        foreach ($items as $item) {
            $item->isPurchased = $item->purchases->isNotEmpty();
        }

        // 現在のページを設定
        $currentPage = $tab;

        return view('items.index', compact('items', 'currentPage', 'isAuthenticated', 'userId', 'search'));
    }

    // 出品ページを表示
    public function showSellForm()
    {
        $isAuthenticated = Auth::check();  // 認証済みユーザーかどうかを確認
        $userId = $isAuthenticated ? Auth::id() : null;  // ユーザーidを取得
        $categories = Category::all();  // カテゴリーを取得
        $conditions = Condition::all(); // 商品状態を取得

        return view('items.sell', compact('isAuthenticated', 'userId', 'categories', 'conditions'));
    }

    // 商品を出品（itemsテーブルに登録）
    public function store(ExhibitionRequest $request)
    {
        // Base64エンコードされた画像データをデコード
        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image));

        // ファイル名を生成（例：timestamp_random.png）
        $filename = time() . '_' . uniqid() . '.png';

        // 画像を保存
        Storage::disk('public')->put('uploads/items/' . $filename, $image_data);

        // 新しい商品を作成
        $item = new Item();
        $item->user_id = Auth::id();
        $item->image_url = 'storage/uploads/items/' . $filename;
        $item->condition_id = $request->condition_id;
        $item->name = $request->item_name;
        $item->brand = $request->brand;
        $item->description = $request->item_description;
        $item->price = $request->item_price;
        $item->save();

        // カテゴリーの関連付け
        $item->categories()->attach($request->category_ids, [
            'created_at' => now(), // 現在のタイムスタンプ
            'updated_at' => now(), // 現在のタイムスタンプ
        ]);

        return redirect()->route('item.index')->with('success', '商品が出品されました。');
    }

    // ■ マイページの表示
    public function mypage(Request $request)
    {
        // クエリパラメータを取得
        $tab = $request->query('tab', 'sell');  // tabがnullなら'sell'を使用
        $search = $request->query('search');

        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // ユーザー名を取得
        $userName = null;
        if ($isAuthenticated) {
            $user = Auth::user(); // 認証済みユーザーの情報を取得
            $userName = $user->profile_name; // profile_nameを取得
            $profileImage = $user->profile_image; // profile_imageを取得
        }

        // アイテムのリストを初期化
        $items = collect();

        // 「出品した商品」が選択された場合
        if ($tab === 'sell') {
            if ($isAuthenticated) {
                // 認証済みユーザーが出品した商品を取得（関連データを一度に取得）
                $items = Item::with('purchases')
                            ->where('user_id', $userId);

                // 検索条件を追加
                if ($search) {
                    $items->where('name', 'like', '%' . $search . '%');
                }

                $items = $items->get();
            }
        }
        // 「購入した商品」が選択された場合
        else if ($tab === 'buy') {
            if ($isAuthenticated) {
                // 認証済みユーザーが購入した商品を取得（関連データを一度に取得）
                $items = Item::with('purchases')
                            ->whereIn('id', function ($query) use ($userId) {
                                $query->select('item_id')
                                        ->from('purchases')
                                        ->where('user_id', $userId);
                            });

                // 検索条件を追加
                if ($search) {
                    $items->where('name', 'like', '%' . $search . '%');
                }

                $items = $items->get();
            }
        }

        // 各アイテムに購入済みフラグを追加
        foreach ($items as $item) {
            $item->isPurchased = $item->purchases->isNotEmpty();
        }

        // 現在のページを設定
        $currentPage = $tab;

        return view('mypage.index', compact('items', 'currentPage', 'isAuthenticated', 'userId', 'userName', 'profileImage', 'search'));
    }




    // 商品詳細を表示
    public function showDetail($item_id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // 指定された item_id に基づいて商品を取得
        $item = Item::withCount(['likes', 'comments'])->find($item_id);


        // 商品が見つからない場合の処理（オプション）
        if (!$item) {
            abort(404); // 404エラーを返す
        }

        // 商品が購入済みかどうかを判定
        $item->isPurchased = $item->purchases->isNotEmpty();

        // 現在のユーザーがこの商品をいいねしているかどうかを確認
        $hasLiked = $isAuthenticated && $item->likes()->where('user_id', $userId)->exists();

        // 商品情報をビューに渡す
        return view('items.item_detail', compact('item', 'isAuthenticated', 'userId', 'hasLiked'));
    }

/**
 * いいねのトグル処理を行うメソッド。
 * 現在のユーザーが指定された商品に対していいねを押下しているかを確認し、
 * いいねが存在する場合は取り消し、存在しない場合は新たに追加する。
 * 処理が成功した場合、JSON形式で成功レスポンスを返す。
 *
 * @param int $item_id 商品のID
 * @return \Illuminate\Http\JsonResponse 処理結果のJSONレスポンス
 */
    public function toggleLike($item_id)
    {
        $userId = Auth::id();

        // 該当するレコードがlikesテーブルにあるかないかを確認
        $like = Like::where('item_id', $item_id)->where('user_id', $userId)->first();

        // itemsテーブルを操作して、json形式でレスポンス
        if ($like) {
            // いいねを取り消す場合
            $like->delete();
            return response()->json(['success' => true]);
        } else {
            // いいねを追加する場合
            Like::create(['item_id' => $item_id, 'user_id' => $userId]);
            return response()->json(['success' => true]);
        }
    }
}


