<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/items/item_detail.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header_inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
            </a>

            <nav class="header__nav">
                @if ($isAuthenticated)
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="header__logout-button">ログアウト</button>
                    </form>
                    <a class="header__link" href="/mypage">マイページ</a>
                    <a class="header__button" href="/sell" role="button">出品</a>
                @else
                    <a class="header__link" href="/login">ログイン</a>
                    <a class="header__link" href="/login">マイページ</a>
                    <a class="header__button" href="/login" role="button">出品</a>
                @endif
            </nav>
        </div>
    </header>

    <!-- 開発用 -->
    <div>user_id : <?php echo $userId ?? '未ログイン'; ?> (※ 開発用)</div>
    <div>商品ID: {{ $item->id }}</div>

    <main>
        <div class="main-container">
            <div class="img-container">
                <div class="item-card">
                    <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-image">
                    @if ($item->isPurchased)
                        <img src="{{ asset('images/sold-label.svg') }}" alt="Sold" class="sold-label">
                    @endif
                </div>
            </div>

            <div class="detail-container">
                <div class="item-name">{{ $item->name }}</div>
                <div class="item-brand">{{ $item->brand }}</div>
                <div class="price">
                    <span class="yen-symbol">￥</span>{{ number_format($item->price) }}<span class="yen-symbol">(税込)</span>
                </div>

                <div class="interaction-counts">
                    <!--いいね-->
                    <div class="like-count">
                        <img id="like-button"
                            src="{{ $hasLiked ? asset('images/star-filled.svg') : asset('images/star-outline.svg') }}"
                            alt="いいねボタン"
                            class="{{ $hasLiked ? 'liked' : '' }}" />
                        <span id="likes-count">{{ $item->likes_count }}</span>
                    </div>
                    <!--コメント-->
                    <div class="comment-count">
                        <img src="{{asset('images/comment-outline.svg')}}">
                        <span id="comments-count">{{ $item->comments_count }}</span>
                    </div>
                </div>

                <!-- 「購入手続きへ」ボタン -->
                @if ($isAuthenticated)
                    @if ($item->isPurchased)
                        <button class="sold-out-button" disabled>売り切れました</button>
                    @else
                        <form action="{{ url('/purchase/' . $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="purchase-button">購入手続きへ</button>
                        </form>
                    @endif
                @else
                    @if ($item->isPurchased)
                        <button class="sold-out-button" disabled>売り切れました</button>
                    @else
                        <a href="{{ url('/login') }}" class="purchase-login-button">ログインして購入</a>
                    @endif
                @endif

                <div class="item-description-title">商品説明</div>
                <div class="item-description">{{ $item->description }}</div>
                <div class="item-info-title">商品の情報</div>
                <div class="item-info">
                <div class="item-category-wrapper">
                    <div class="item-category-title">カテゴリー</div>
                    <div class="item-category">
                        @foreach($item->categories as $category)
                            <div class="ellipse">{{ $category->name }}</div>
                        @endforeach
                    </div>
                </div>
                <div class="item-condition-wrapper">
                    <div class="item-condition-title">商品の状態</div>
                    <div class="item-condition">
                        {{ $item->condition->name ?? '状態情報がありません' }}
                    </div>
                </div>
                <div class="comments-title">コメント({{ $item->comments_count }})</div>

                <div class="comments-list">
                    @foreach($item->comments as $comment)
                        <div class="comment-item">
                            <div class="comment-user">
                                <img src="{{ $comment->user->profile_image ? asset($comment->user->profile_image) : asset('images/user_icon_default.png') }}" alt="プロフィール画像" class="profile-image">
                                <span class="profile-name">{{ $comment->user->profile_name }}</span>
                            </div>
                            <div class="comment-content">{{ $comment->content }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="comment-header">商品へのコメント</div>
                <!-- コメント入力フォーム -->
                <div class="comment-form">
                    @if ($isAuthenticated)
                        <textarea id="comment-content" placeholder="コメントする" rows="4"></textarea>
                    <!-- エラーメッセージの表示用要素 -->
                    <div class="error-message-area">
                        <span id="error-message" class="text-danger" style="display: none;"></span>
                    </div>
                    @else
                    <!--未ログインユーザにはテキストエリアを表示しない-->
                    @endif

                    @if ($isAuthenticated)
                        <button id="submit-comment" class="submit-button">コメントを送信する</button>
                    @else
                        <div class="login-prompt">
                            <a href="/login" class="comment-login-button">ログインしてコメントする</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

<script>
// 「いいね」ボタンの表示の切り替え、サーバ側へPOST
document.addEventListener('DOMContentLoaded', function() {
    const likeButton = document.getElementById('like-button');
    const likesCountElement = document.getElementById('likes-count');
    const itemId = {{ json_encode($item->id) }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const isAuthenticated = {{ json_encode($isAuthenticated) }}; // PHPからJavaScriptに渡す

    likeButton.addEventListener('click', function() {
        // 未ログインの場合はログイン画面にリダイレクト
        if (!isAuthenticated) {
            window.location.href = '/login'; // ログイン画面へリダイレクト
            return; // クリックイベントを終了
        }
        // ログイン済みユーザーのいいねボタンの処理
        const currentLikesCount = parseInt(likesCountElement.textContent);

        // AJAXリクエストを送信
        fetch(`/like/${itemId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ liked: !this.classList.contains('liked') })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.classList.toggle('liked');
                this.src = this.classList.contains('liked') ? '{{ asset('images/star-filled.svg') }}' : '{{ asset('images/star-outline.svg') }}';
                likesCountElement.textContent = this.classList.contains('liked') ? currentLikesCount + 1 : currentLikesCount - 1;
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

// 「コメント」の追加、サーバ側へPOST
document.addEventListener('DOMContentLoaded', function() {
    const submitButton = document.getElementById('submit-comment');
    const commentContent = document.getElementById('comment-content');
    const commentsCountElement = document.getElementById('comments-count');
    const commentsTitleElement = document.querySelector('.comments-title');
    const itemId = {{ json_encode($item->id) }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    submitButton.addEventListener('click', function() {
        const content = commentContent.value;

        // AJAXリクエストを送信
        fetch(`/item/${itemId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // コメントを追加する処理
                const newCommentHTML = `
                    <div class="comment-item">
                        <div class="comment-user">
                            <img src="${data.user.profile_image ? data.user.profile_image : '{{ asset('images/user_icon_default.png') }}'}" alt="プロフィール画像" class="profile-image">
                            <span class="profile-name">${data.user.profile_name}</span>
                        </div>
                        <div class="comment-content">${content}</div>
                    </div>
                `;
                document.querySelector('.comments-list').insertAdjacentHTML('beforeend', newCommentHTML);
                commentContent.value = ''; // テキストエリアをクリア

                // エラーメッセージをクリア
                const errorElement = document.getElementById('error-message');
                errorElement.style.display = 'none';

                // コメント数を更新
                const newCommentsCount = parseInt(commentsCountElement.textContent) + 1;
                commentsCountElement.textContent = newCommentsCount;
                commentsTitleElement.textContent = `コメント(${newCommentsCount})`;
            }
            else if (data.errors) {
                // バリデーションエラーがある場合、エラーメッセージを表示
                const errorMessage = data.errors.content ? data.errors.content[0] : 'エラーが発生しました';
                const errorElement = document.getElementById('error-message');
                errorElement.textContent = errorMessage;
                errorElement.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

</script>
</body>

</html>