<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/purchase/address.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header_inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
            </a>

            <nav class="header__nav">
                <?php if ($isAuthenticated): ?>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form>
                <?php else: ?>
                    <a class="header__link" href="/login">ログイン</a>
                <?php endif; ?>
                <a class="header__link" href="/mypage">マイページ</a>
                <a class="header__button" href="/sell" role="button">出品</a>
            </nav>
        </div>
    </header>

    <!-- 開発用 -->
    <div>user_id : <?php echo $userId ?? '未ログイン'; ?> (※ 開発用)</div>
    <div>商品ID: {{ $item->id }}</div>

    <main>
        <div class="main-container">

            <h2>住所の変更</h2>

            <!-- フォーム -->
            <form action="{{ route('address.update') }}" method="POST" class="address-form">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->id }}"> <!-- アイテムIDを隠しフィールドとして追加 -->
                <!-- 郵便番号 -->
                <div class="form-group">
                    <label for="postal_code" class="form-label">郵便番号</label>
                    <input type="text" name="postal_code" id="postal_code" class="form-input" value="{{ old('postal_code') }}">
                    <div class="error-message-area">
                        @error('postal_code')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 住所 -->
                <div class="form-group">
                    <label for="address" class="form-label">住所</label>
                    <input type="text" name="address" id="address" class="form-input" value="{{ old('address') }}">
                    <div class="error-message-area">
                        @error('address')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 建物名 -->
                <div class="form-group">
                    <label for="building" class="form-label">建物名</label>
                    <input type="text" name="building" id="building" class="form-input" value="{{ old('building') }}">
                    <div class="error-message-area">
                        @error('building')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 更新ボタン -->
                <button type="submit" class="submit-button">更新する</button>
            </form>

        </div>

    </main>


</body>

</html>