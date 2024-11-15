<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/items/sell.css') }}" />
    <!-- Cropper.jsのCSSを追加 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

    <style>  /*切り取り後の画像の表示*/
        #image-preview {  /*クロップ編集前の画像サイズ上限*/
            max-width: 500px;
            max-height: 500px;
            margin-top: 10px;
        }

        #cropped-result {  /*クロップ後の画像サイズ上限*/
            max-width: 300px;
            max-height: 300px;
            width: 200px;
            margin: 20px;
        }

        .hidden {
            display: none;
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
                        @csrf
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form>
                @else
                    <a class="header__link" href="/login">ログイン</a>
                @endif
                <a class="header__link" href="/">マイページ</a>
                <a class="header__button" href="/sell" role="button">出品</a>
            </nav>
        </div>
    </header>

        <div>user_id : <?php echo $userId ?? '未ログイン'; ?> (※ 開発用)</div>  <!-- 開発用 -->

    <main>


        <div class="form__title">
            <h2>商品の出品</h2>
        </div>

        <form action="{{ route('item.store') }}" method="POST" class="form" enctype="multipart/form-data">
            @csrf

            <!-- 商品画像アップロードセクション -->
            <div class="form-group item-image-upload">
                <label for="item_image" class="form-label">商品画像</label>

                <!-- 画像クロッピングエリア（初期状態では非表示） -->
                <div id="cropping-area" class="item-image-cropping hidden">
                    <!-- 元画像のプレビュー表示 -->
                    <div class="item-image-preview-wrapper">
                        <img id="image-preview" src="" alt="画像プレビュー" class="item-image-preview">
                    </div>
                    <!-- Cropper.jsによるクロッピング操作エリア -->
                    <div id="image-cropper" class="item-image-cropper"></div>
                    <!-- クロッピング確定ボタン -->
                    <div class="item-image-action">
                        <button type="button" id="crop-button" class="btn-crop">切り取り領域を決定</button>
                    </div>
                </div>

                <!-- クロッピング後の画像プレビューエリア（初期状態では非表示） -->
                <div id="preview-area" class="item-image-result hidden">
                    <!-- クロッピング後の画像表示 -->
                    <div class="item-image-result-wrapper">
                        <img id="cropped-result" src="" alt="クロップ結果" class="item-image-result-preview">
                    </div>
                    <!-- 再編集ボタン -->
                    <div class="item-image-action">
                        <button type="button" id="edit-button" class="btn-edit">再編集</button>
                    </div>
                </div>
                <!-- クロッピングされた画像データを保持する隠しフィールド -->
                <input type="hidden" id="cropped_image" name="cropped_image">

                <!-- 画像選択ボタンエリア -->
                <div class="item-image-content">
                    <div class="item-image-input">
                        <!-- 実際のファイル入力フィールド（非表示） -->
                        <input type="file" id="item_image" name="item_image" accept="image/*"  class="item-image-file" style="display: none;">
                        <!-- カスタムデザインの画像選択ボタン -->
                        <label for="item_image" class="custom-file-upload">画像を選択する</label>
                    </div>
                </div>
                @if ($errors->has('item_image'))
                    <div class="error-message">{{ $errors->first('item_image') }}</div>
                @endif
            </div>

            <h3>商品の詳細</h3>

            <!-- カテゴリ -->
            <label for="category">カテゴリー</label>
            <div class="category-selection">
                @foreach ($categories as $category)
                    <div class="category-button">
                        <input type="checkbox" id="category_{{ $category->id }}" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                        <label for="category_{{ $category->id }}">{{ $category->name }}</label>
                    </div>
                @endforeach
            </div>
            @if ($errors->has('category_ids'))
                <div class="error-message">{{ $errors->first('category_ids') }}</div>
            @endif

            <!-- 商品の状態 -->
            <div class="condition-selection">
                <label for="condition_id">商品の状態</label>
                <select id="condition_id" name="condition_id">
                    <option value="" disabled selected>選択してください</option>
                    @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}" {{ old('condition_id') == $condition->id ? 'selected' : '' }}>{{ $condition->name }}</option>
                    @endforeach
                </select>
                @if ($errors->has('condition_id'))
                    <div class="error-message">{{ $errors->first('condition_id') }}</div>
                @endif
            </div>

            <h3>商品名と説明</h3>
            <div class="form-group">
                <label for="item_name">商品名</label>
                <input type="text" id="item_name" name="item_name" class="form-input" value="{{ old('item_name') }}">
                @if ($errors->has('item_name'))
                    <div class="error-message">{{ $errors->first('item_name') }}</div>
                @endif
            </div>

            <div class="form-group">
                <div class="label-container">
                    <label for="item_brand">ブランド名</label>
                    <span class="optional-label">任意</span>
                </div>
                <input type="text" id="item_brand" name="brand" class="form-input" value="{{ old('brand') }}">
            </div>

        <!-- 商品の説明 -->
        <div class="form-group">
            <label for="item_description">商品の説明</label>
            <textarea id="item_description" name="item_description" class="form-textarea">{{ old('item_description') }}</textarea>
            @if ($errors->has('item_description'))
                <div class="error-message">{{ $errors->first('item_description') }}</div>
            @endif
        </div>

        <!-- 販売価格 -->
        <div class="form-group">
            <label for="item_price">販売価格</label>
            <div class="price-input">
                <span class="currency-symbol">￥</span>
                <input type="text" id="item_price" name="item_price" class="form-input price-input-field" value="{{ old('item_price') }}">
            </div>
            @if ($errors->has('item_price'))
                <div class="error-message">{{ $errors->first('item_price') }}</div>
            @endif
        </div>


            <!-- 出品するボタン -->
            <button type="submit" id="submit-button" class="form-button">出品する</button>

        </form>


    </main>


    <!-- Cropper.jsのスクリプトを追加 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
let cropper;

document.getElementById('item_image').addEventListener('change', function(e) {
    const file = e.target.files[0];

    // 画像が選択された場合
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
    } else {
        // キャンセルされた場合にクロッピングフィールドをリセット
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        document.getElementById('image-preview').src = '';
        document.getElementById('cropped_image').value = '';
        document.getElementById('cropping-area').classList.add('hidden');
        document.getElementById('preview-area').classList.add('hidden');
    }
});

function initCropper() {
    const image = document.getElementById('image-preview');
    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 1,
        preview: '#image-cropper'
    });
}

document.getElementById('crop-button').addEventListener('click', function() {
    if (cropper) {
        const croppedCanvas = cropper.getCroppedCanvas();
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
    // プレビューエリアを隠し、クロッピングエリアを表示
    document.getElementById('preview-area').classList.add('hidden');
    document.getElementById('cropping-area').classList.remove('hidden');
    initCropper();
});
</script>

</body>

</html>