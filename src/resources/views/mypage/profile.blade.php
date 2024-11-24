<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/mypage/profile.css') }}" />
    <!-- Cropper.jsのCSSを追加 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

    <style>
        /* 画像クロッピングエリアのスタイル */
        #image-cropper {
            width: 100%;
            height: 100%;
            border: 2px dashed #ccc;
            border-radius: 50%; /* 円形にする */
            overflow: hidden;
        }

        #cropped-result {
            max-width: 150px;
            max-height: 150px;
            margin-left:10px;
            margin-right:35px;
            border-radius: 50%; /* 円形にする */
            object-fit: cover; /* 画像を円形にフィットさせる */
        }

        .hidden {
            display: none;
        }

        /* 円形マスクのスタイル */
        .profile-image-cropper {
            position: relative;
            width: 300px;
            height: 300px;
            margin: 0 auto;
        }

        .circular-mask {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
            pointer-events: none;
        }

        /* Cropper.jsのスタイル調整 */
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
    </style>

</head>

<body>
    <header class="header">
        <div class="header_inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
            </a>

            <nav class="header__nav">
                @if ($isAuthenticated) <!-- 認証or未認証で分岐 -->
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf <!-- CSRFトークン -->
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form>
                @else
                    <a class="header__link" href="/login">ログイン</a>
                @endif
                <a class="header__link" href="/mypage/profile">マイページ</a>
                <a class="header__button" href="/sell" role="button">出品</a>
            </nav>
        </div>
    </header>

        <!--フラッシュ―メッセージ「メールアドレスが認証されました」-->
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

    <main>



        <div class="form__title">
            <h2>プロフィール設定</h2>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="form" enctype="multipart/form-data">
            @csrf

            <!-- 商品画像アップロードセクション -->
            <div class="form-group profile-image-upload">

                <!-- 画像クロッピングエリア（初期状態では非表示） -->
                <div id="cropping-area" class="profile-image-cropping hidden">
                    <!-- 元画像のプレビュー表示 -->
                    <div class="profile-image-preview-wrapper">
                        <img id="image-preview" src="" alt="画像プレビュー" class="profile-image-preview">
                    </div>
                    <!-- Cropper.jsによるクロッピング操作エリア -->
                    <div id="image-cropper" class="profile-image-cropper">
                        <div class="circular-mask"></div>
                    </div>
                    <!-- クロッピング確定ボタン -->
                    <div class="profile-image-action">
                        <button type="button" id="crop-button" class="btn-crop">切り取り領域を決定</button>
                    </div>
                </div>

                <!-- クロッピング後の画像プレビューエリア（初期状態では非表示） -->
                <div id="preview-area" class="profile-image-result hidden">
                    <!-- クロッピング後の画像表示 -->
                    <div class="profile-image-result-wrapper">
                        <img id="cropped-result"  src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像" class="profile-image-result-preview">

                    </div>
                    <!-- 再編集ボタン -->
                    <div class="profile-image-action">
                        <button type="button" id="edit-button" class="btn-edit">画像を選択する</button>
                    </div>
                </div>
                <!-- クロッピングされた画像データを保持する隠しフィールド -->
                <input type="hidden" id="cropped_image" name="cropped_image">

                <!-- 画像選択ボタンエリア -->
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="profile-image-file" style="display: none;">
                @if ($errors->has('profile_image'))
                    <div class="error-message">{{ $errors->first('profile_image') }}</div>
                @endif
            </div>

            <div class="form-group">
                <div class="label-container">
                    <label for="profile_name">ユーザー名</label>
                </div>
                <!--バリデーションエラー時は直前の入力値を表示。 それ以外はusersテーブルの値を表示-->
                <input type="text" id="profile_name" name="profile_name" class="form-input" value="{{ old('profile_name', $user->profile_name) }}">
                @if ($errors->has('profile_name'))
                    <div class="error-message">{{ $errors->first('profile_name') }}</div>
                @endif
            </div>

            <div class="form-group">
                <div class="label-container">
                    <label for="postal_code">郵便番号</label>
                </div>
                <input type="text" id="postal_code" name="postal_code" class="form-input" value="{{ old('postal_code', optional($address)->postal_code) }}">
                @if ($errors->has('postal_code'))
                    <div class="error-message">{{ $errors->first('postal_code') }}</div>
                @endif
            </div>

            <div class="form-group">
                <div class="label-container">
                    <label for="address">住所</label>
                </div>
                <input type="text" id="address" name="address" class="form-input" value="{{ old('address', optional($address)->address) }}">
            </div>

            <div class="form-group">
                <div class="label-container">
                    <label for="building">建物名</label>
                </div>
                <input type="text" id="building" name="building" class="form-input" value="{{ old('building', optional($address)->building) }}">
            </div>


            <!-- 出品するボタン -->
            <button type="submit" id="submit-button" class="form-button">更新する</button>

        </form>


    </main>


    <!-- Cropper.jsのスクリプトを追加 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        let cropper;

        // ページ読み込み時の処理
        document.addEventListener('DOMContentLoaded', function() {
            // usersテーブルのprofile_imageカラムの値をJavaScriptに渡す
            const profileImage = "{{ $user->profile_image ? asset($user->profile_image) : asset('images/user_icon_default.png') }}";

            // 初期画像を設定
            document.getElementById('cropped-result').src = profileImage;
            document.getElementById('preview-area').classList.remove('hidden');
        });

        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('image-preview').src = event.target.result;
                    document.getElementById('cropping-area').classList.remove('hidden');
                    document.getElementById('preview-area').classList.add('hidden');
                    initCropper();
                }
                reader.readAsDataURL(file);
            }
        });

        // 編集画面の切り取り窓の形状
        function initCropper() {
            const image = document.getElementById('image-preview');
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                preview: '#image-cropper',
                guides: false,
                center: false,
                cropBoxResizable: false,
                cropBoxMovable: false,
                dragMode: 'move',
                toggleDragModeOnDblclick: false,
                ready: function() {
                    // クロップボックスを円形に見せるための調整
                    const cropBoxData = cropper.getCropBoxData();
                    const size = Math.min(cropBoxData.width, cropBoxData.height);
                    cropper.setCropBoxData({
                        left: (cropBoxData.width - size) / 2 + cropBoxData.left,
                        top: (cropBoxData.height - size) / 2 + cropBoxData.top,
                        width: size,
                        height: size
                    });
                }
            });
        }

        document.getElementById('crop-button').addEventListener('click', function() {
            if (cropper) {
                const croppedCanvas = cropper.getCroppedCanvas({
                    width: 300, // 必要に応じてサイズを指定
                    height: 300,
                });
                document.getElementById('cropped-result').src = croppedCanvas.toDataURL();
                document.getElementById('cropped_image').value = croppedCanvas.toDataURL();

                // クロッピングエリアを隠し、プレビューエリアを表示
                document.getElementById('cropping-area').classList.add('hidden');
                document.getElementById('preview-area').classList.remove('hidden');

                // クロッパーを破棄
                cropper.destroy();
                cropper = null;
            }
        });

        document.getElementById('edit-button').addEventListener('click', function() {
            document.getElementById('profile_image').click();
        });

    </script>

</body>

</html>