<?php

namespace App\Http\Controllers;

use App\Models\Item; // Itemモデルのインポート
use App\Models\Like; // Likeモデルのインポート
use App\Models\Purchase; // Purchaseモデルのインポート
use App\Models\Category; // Categoryモデルをインポート
use App\Models\Condition; // Conditionモデルをインポート
use App\Models\Chat;
use App\Models\Rating;
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

    // ユーザー情報を取得
        $userName = null;
        $profileImage = null;
        $averageRating = null;

        // ユーザー情報を取得
        $userName = null;
        if ($isAuthenticated) {
            $user = Auth::user(); // 認証済みユーザーの情報を取得
            $userName = $user->profile_name; // profile_nameを取得
            $profileImage = $user->profile_image; // profile_imageを取得

            // ログインユーザーの平均評価を計算（四捨五入）
            $averageRating = Rating::where('rated_id', $userId)
                ->avg('rating'); // 平均値を取得

            // null でなければ四捨五入して整数化
            $averageRating = $averageRating !== null ? round($averageRating) : 0;
        }

        // アイテムのリストを初期化
        $items = collect();
        $itemUnreadCounts = []; // 各商品ごとの未読メッセージ数を格納する配列

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

        // 「取引中の商品」が選択された場合
        else if ($tab === 'in_progress') {
            if ($isAuthenticated) {
                $items = Item::with('purchases')
                    ->where(function ($query) use ($userId) {

                        // 【自分が購入した取引中の商品】
                        // - purchasesテーブルから、現在のユーザーが購入者である商品を取得
                        // - in_progress = 1（取引中）の商品のみを対象
                        $query->whereIn('id', function ($subQuery) use ($userId) {
                            $subQuery->select('item_id')
                                ->from('purchases')
                                ->where('user_id', $userId)
                                ->where(function ($innerQuery) {
                                    $innerQuery->where('in_progress', 1)
                                        ->orWhere(function ($q) {
                                            $q->where('in_progress', 2)
                                            ->where('buyer_rated', 0); // buyer_rated が 0 の場合のみ
                                        });
                                });
                        })

                        // 【自分が出品した取引中の商品】
                        // - 自分が出品した商品（itemsテーブルのuser_id = 現在のユーザーID）
                        // - その商品がpurchasesテーブルに存在し、かつin_progress = 1（取引中）
                        ->orWhereIn('id', function ($subQuery) use ($userId) {
                            $subQuery->select('item_id')
                                ->from('purchases')
                                ->whereIn('item_id', function ($innerQuery) use ($userId) {
                                    $innerQuery->select('id')
                                        ->from('items')
                                        ->where('user_id', $userId);
                                })
                                ->where(function ($innerQuery) {
                                    $innerQuery->where('in_progress', 1)
                                        ->orWhere(function ($q) {
                                            $q->where('in_progress', 2)
                                            ->where('seller_rated', 0); // seller_rated が 0 の場合のみ
                                        });
                                });
                        });
                    });

                // 検索キーワードが指定されている場合、商品名でフィルタリング
                if ($search) {
                    $items->where('name', 'like', '%' . $search . '%');
                }

                // クエリを実行し、結果を取得
                $items = $items->get();

                // 各商品の未読メッセージ数を取得
                foreach ($items as $item) {
                    $purchaseId = $item->purchases->where('in_progress', 1)->first()->id ?? null;
                    if ($purchaseId) {
                        $itemUnreadCounts[$item->id] = Chat::where('purchase_id', $purchaseId)
                            ->where('user_id', '!=', $userId)
                            ->where('is_read', 0)
                            ->count();
                    } else {
                        $itemUnreadCounts[$item->id] = 0;
                    }
                }

                // ここからソート処理を追加（取得した $items の順番を変える）
                // 「取引中の商品」タブが選択されたとき、新着メッセージの最新順でソート
                $items = $items->map(function ($item) use ($userId) {
                    // 各商品の最新の未読メッセージの時間を取得
                    $latestUnreadMessageTime = Chat::where('purchase_id', $item->purchases->first()->id ?? null)
                        ->where('user_id', '!=', $userId)
                        ->where('is_read', 0)
                        ->latest()
                        ->value('created_at');

                    $item->latest_message_time = $latestUnreadMessageTime ?? null;
                    return $item;
                });

                // 新着メッセージがある順に並び替え（新しいメッセージ順）
                $items = $items->sortByDesc('latest_message_time')->values();
            }
        }


        // 各アイテムにフラグを追加
        foreach ($items as $item) {
            $purchase = $item->purchases->first();

            if ($purchase) {
                $isInProgress = $purchase->in_progress == 1 ||
                    ($purchase->in_progress == 2 &&
                        (($purchase->user_id == $userId && $purchase->buyer_rated == 0) || // 購入者で未評価
                        ($item->user_id == $userId && $purchase->seller_rated == 0))); // 出品者で未評価
            } else {
                $isInProgress = false;
            }

            $item->isPurchased = $purchase !== null; // 購入済みアイテムかどうか
            $item->isInProgress = $isInProgress; // 取引中または評価待ちかどうか
        }

        // 未読メッセージのカウント（全体の集計）
        $unreadMessageCount = 0;
        if ($isAuthenticated) {
            // 取引中の商品に紐づく purchases を取得
            $inProgressPurchases = Purchase::where('in_progress', 1)
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhereHas('item', function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        });
                })
                ->pluck('id'); // purchase_id のリストを取得

            // 未読メッセージのカウント
            $unreadMessageCount = Chat::whereIn('purchase_id', $inProgressPurchases)
                ->where('user_id', '!=', $userId) // 相手のメッセージ
                ->where('is_read', 0) // 未読のみ
                ->count();
    }

        // 現在のページを設定
        $currentPage = $tab;

        return view('mypage.index', compact(
            'items',
            'currentPage',
            'isAuthenticated',
            'userId',
            'userName',
            'profileImage',
            'search',
            'unreadMessageCount', // 全体の未読メッセージ数
            'itemUnreadCounts', // 各商品の未読メッセージ数
            'averageRating', // ログインユーザーの評価（平均値）
        ));
    }

    // 商品詳細を表示
    public function showDetail($item_id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // 指定された item_id に該当する商品レコードを取得する
        // 「いいね」「コメント」の数は「likes_count」「comments_count」 という名前の属性として $item オブジェクトに追加
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


