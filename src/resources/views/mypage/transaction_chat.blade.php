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
            <div class="ongoing-transactions">
                @foreach ($ongoingTransactions as $transaction)
                    <a href="{{ route('transaction.chat', ['item_id' => $transaction->item_id]) }}" class="transaction-link">
                        {{ $transaction->item->name }}
                    </a>
                @endforeach
            </div>
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
                <!-- 取引を完了するボタン（購入者のみ表示） -->
                @if ($showCompleteButton)
                    <button class="complete-transaction">取引を完了する</button>
                @endif
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
                <!-- カスタムデータ属性に$purchaseIdを持たせ、chat.jsに渡す -->
                <div class="chat-messages" data-purchase-id="{{ $purchaseId }}" data-user-id="{{ $userId }}">
                    @foreach ($chatMessages as $chat)
                    <div class="chat-message-container">
                            <!-- ユーザー情報（プロフィール画像＋ユーザー名） -->
                            <div class="user-info {{ $chat->user_id == $userId ? 'my-user-info' : 'partner-user-info' }}">
                                @if ($chat->user_id == $userId)
                                    <!-- 自分のメッセージ: 名前→画像の順番 -->
                                    <span class="user-name">{{ $profileName ?? 'ゲストユーザー' }}</span>
                                    <img src="{{ $profileImage ? asset($profileImage) : asset('images/user_icon_default.png') }}" alt="プロフィール写真" class="user-profile-image">
                                @else
                                    <!-- 相手のメッセージ: 画像→名前の順番 -->
                                    <img src="{{ $partnerProfileImage ? asset($partnerProfileImage) : asset('images/user_icon_default.png') }}" alt="プロフィール写真" class="user-profile-image">
                                    <span class="user-name">{{ $partnerName ?? 'ゲストユーザー' }}</span>
                                @endif
                            </div>

                            <!-- メッセージ本体 -->
                            <div class="chat-message {{ $chat->user_id == $userId ? 'my-message' : 'partner-message' }}">
                                @if ($chat->message)
                                    <p class="message-text">{{ $chat->message }}</p>
                                @endif

                                @if ($chat->image_path)
                                    <img src="{{ asset($chat->image_path) }}" class="chat-image">
                                @endif

                                @if ($chat->is_edited)
                                    <span class="edited-label">(編集済み)</span>
                                @endif
                            </div>

                            <!-- メッセージの制御ボタン -->
                            <div class="message-controls {{ $chat->user_id == $userId ? 'my-message-controls' : 'partner-message-controls' }}">
                                <span class="message-time">{{ $chat->created_at->format('H:i') }}</span>

                                <!-- 自分のメッセージの場合のみ「編集」「削除」ボタンを表示 -->
                                @if ($chat->user_id == $userId)
                                    <div class="edit-delete-buttons">
                                        <button class="edit-message" data-message-id="{{ $chat->id }}">編集</button>
                                        <button class="delete-message" data-message-id="{{ $chat->id }}">削除</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- メッセージ入力エリア -->
                <div class="chat-input">
                    <div class="error-messages"></div>
                    <div class="input-container">
                        <input type="text" class="message-input" placeholder="取引メッセージを入力してください">
                        <!-- 画像アップロード用（非表示） -->
                        <input type="file" id="image-upload" class="image-upload-input" accept="image/*" style="display: none;">
                        <button class="add-image">画像を追加</button>
                        <img src="{{ asset('images/inputbuttun.svg') }}" class="send-message-icon" alt="送信">

                    </div>
                </div>
            </div>
        </div>

    </main>

<!-- jQueryの読み込み -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- 以下 chat.js に渡す変数の定義 -->
<script>
    // ページのロード時に基準となる時間「lastMessageTime」に現在時刻をセット
    let lastMessageTime = "{{ now()->format('Y-m-d H:i:s') }}";
    let profileName = "{{ $profileName }}";
    let profileImage = "{{ asset($profileImage) }}";
    let partnerName = "{{ $partnerName }}";
    let partnerProfileImage = "{{ asset($partnerProfileImage) }}";

    // 取引完了かつ未評価の場合にモーダルを表示するかどうかのフラグ
    let showRatingModal = @json($showRatingModal);

    // ページロード時に評価モーダルを表示（必要な場合）
    $(document).ready(function () {
        if (showRatingModal) {
            showRatingModalFunction();
        }
    });

    // 評価モーダルを表示する関数
    function showRatingModalFunction() {
        let modalHtml = `
            <div class="rating-modal">
                <div class="modal-content">
                    <h3>取引が完了しました。</h3>
                    <div class="rating-text">
                        <p>今回の取引相手はどうでしたか？</p>
                        <div class="star-rating">
                            <img src="/images/star_off.svg" class="star" data-value="1">
                            <img src="/images/star_off.svg" class="star" data-value="2">
                            <img src="/images/star_off.svg" class="star" data-value="3">
                            <img src="/images/star_off.svg" class="star" data-value="4">
                            <img src="/images/star_off.svg" class="star" data-value="5">
                        </div>
                    </div>
                    <button class="submit-rating" disabled>送信する</button>
                </div>
            </div>
        `;

        $("body").append(modalHtml);
        $(".rating-modal").fadeIn();
    }

    // 星をクリックして評価を選択
    $(document).on("click", ".star", function () {
        let selectedRating = $(this).data("value");

        // すべての星を OFF にする
        $(".star").attr("src", "/images/star_off.svg");

        // クリックされた星までを ON にする
        $(".star").each(function () {
            if ($(this).data("value") <= selectedRating) {
                $(this).attr("src", "/images/star_on.svg");
            }
        });

        // 選択した評価をデータ属性に保存
        $(".star-rating").data("selected-rating", selectedRating);

        // 送信ボタンを有効化
        $(".submit-rating").prop("disabled", false);
    });

    // 評価送信処理
    $(document).on("click", ".submit-rating", function () {
        let purchaseId = $(".chat-messages").data("purchase-id");
        let rating = $(".star-rating").data("selected-rating");

        if (!rating) {
            alert("評価を選択してください。");
            return;
        }

        // 送信ボタンを無効化（連続送信防止）
        $(".submit-rating").prop("disabled", true).text("送信中...");

        $.ajax({
            url: "/transaction/rate",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                purchase_id: purchaseId,
                rating: rating,
            },
            success: function (response) {
                console.log("評価が送信されました", response);

                // モーダルを閉じる & リダイレクト
                $(".rating-modal").fadeOut(300, function () {
                    $(this).remove();
                    window.location.href = "/"; // 商品一覧ページへ遷移
                });
            },
            error: function (xhr) {
                console.error("評価の送信に失敗しました", xhr.responseText);
                alert("エラーが発生しました。もう一度お試しください。");

                // 送信ボタンを再び有効化
                $(".submit-rating").prop("disabled", false).text("送信する");
            },
        });
    });
</script>

<script src="{{ asset('js/chat.js') }}"></script>
</body>

</html>