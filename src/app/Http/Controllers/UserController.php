<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 追加
use Illuminate\Support\Facades\Storage; // 追加
use App\Models\User; // 追加
use App\Models\Address; // 追加
use App\Http\Requests\ProfileRequest; // 追加

class UserController extends Controller
{
    public function show()
    {
        // 現在のユーザー情報を取得
        $user = Auth::user();
        $isAuthenticated = Auth::check();  // 認証済みユーザーかどうかを確認
        $userId = $isAuthenticated ? $user->id : null; // user_idを取得

        // 該当ユーザーのデフォルト住所を取得
        $address = Address::where('user_id', $userId)->where('is_default', 1)->first();

        return view('mypage.profile', compact('user', 'isAuthenticated', 'userId', 'address'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();

        // プロフィール画像の保存
        if ($request->has('cropped_image') && !empty($request->input('cropped_image'))) {
            // データURLから画像を取得
            $dataUrl = $request->input('cropped_image');

            // 画像データを分割
            if (strpos($dataUrl, ';') !== false) {
                list($type, $data) = explode(';', $dataUrl);
                if (strpos($data, ',') !== false) {
                    list(, $data) = explode(',', $data);
                    $data = base64_decode($data);

                    // 古い画像がある場合は削除
                    if ($user->profile_image) {
                        // 古い画像のパスを取得し、ストレージから削除
                        $oldImagePath = str_replace('storage/', '', $user->profile_image); // ストレージのパスに変換
                        if (Storage::disk('public')->exists($oldImagePath)) {
                            Storage::disk('public')->delete($oldImagePath);
                        }
                    }

                    // ファイル名を生成（例：timestamp_random.png）
                    $filename = time() . '_' . uniqid() . '.png';

                    // ストレージに保存
                    Storage::disk('public')->put('uploads/profiles/' . $filename, $data); // 保存パス
                    $user->profile_image = 'storage/uploads/profiles/' . $filename; // usersテーブルに保存
                } else {
                    return redirect()->back()->withErrors(['cropped_image' => '無効な画像データです。']);
                }
            } else {
                return redirect()->back()->withErrors(['cropped_image' => '無効な画像データです。']);
            }
        }

        // ユーザー名の保存
        $user->profile_name = $request->input('profile_name');
        $user->save(); // usersテーブルに保存

        // 住所情報の更新
        $address = Address::where('user_id', $user->id)->where('is_default', 1)->first(); // 該当ユーザーのデフォルト住所を取得

        if ($address) {
            // 住所が見つかった場合、上書きする
            $address->postal_code = $request->input('postal_code');
            $address->address = $request->input('address');
            $address->building = $request->input('building');
            $address->save(); // 住所を更新
        } else {
            // デフォルト住所が見つからない場合の処理
            // （新しい住所を追加）
            $newAddress = new Address();
            $newAddress->user_id = $user->id; // usersテーブルのIDを保存
            $newAddress->postal_code = $request->input('postal_code');
            $newAddress->address = $request->input('address');
            $newAddress->building = $request->input('building');
            $newAddress->is_default = 1; // デフォルトの住所として保存
            $newAddress->save(); // 新しい住所を保存
        }

        return redirect()->route('mypage.index')->with('success', 'プロフィールが更新されました。');
    }
}
