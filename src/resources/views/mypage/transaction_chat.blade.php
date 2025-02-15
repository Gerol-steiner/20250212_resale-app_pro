<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/mypage/transaction_chat.css') }}" />
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

    <main>
        <div class="chat-sidebar">
            <h2 class="sidebar-title">その他の取引</h2>
        </div>
        <div class="chat-main">
            <!-- 上部: 取引のヘッダー部分 -->
            <div class="chat-header">
                <div class="partner-profile">
                    <img
                        src="{{
                            $partnerProfileImage
                                ? asset($partnerProfileImage)
                                : asset('images/user_icon_default.png')
                        }}"
                        alt="プロフィール写真"
                        class="partner-profile-image">
                    <h2>「{{ $partnerName }}」 さんとの取引画面</h2>
                </div>
                <p>開発用 : {{ $userRole }}</p>
                <button class="complete-transaction">取引を完了する</button>
            </div>

            <!-- 中部: 商品情報エリア -->
            <div class="chat-info">
                <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-image">
                <div class="product-details">
                    <p class="product-name">{{ $item->name }}</p>
                    <p class="product-price">
                        <span class="yen-symbol">￥</span>
                        {{ number_format($item->price) }}
                        <span class="yen-symbol">(税込)</span>
                    </p>
                </div>
            </div>

            <!-- 下部: チャットエリア -->
            <div class="chat-body">
                <!-- メッセージ履歴 -->
                <!-- カスタムデータ属性に$purchaseIdを持たせる -->
                <div class="chat-messages" data-purchase-id="{{ $purchaseId }}">
                    @foreach ($chatMessages as $chat)
                        <div class="chat-message {{ $chat->user_id == $userId ? 'my-message' : 'partner-message' }}">
                            <p class="message-text">{{ $chat->message }}</p>
                            <span class="message-time">{{ $chat->created_at->format('H:i') }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- メッセージ入力エリア -->
                <div class="chat-input">
                    <input type="text" class="message-input" placeholder="取引メッセージを入力してください">
                    <button class="add-image">画像を追加</button>
                    <button class="send-message">送信</button>
                </div>
            </div>
        </div>

    </main>

<!-- jQueryの読み込み -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/chat.js') }}"></script>
</body>

</html>