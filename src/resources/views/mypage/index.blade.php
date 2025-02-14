<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/mypage/index.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header_inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
            </a>
            <div class="header__search">
                <form action="/mypage" method="GET" class="search-form">
                    <button type="submit" class="search-button">検索</button>
                    <input type="text" name="search" class="search-input" placeholder="なにをお探しですか？" aria-label="商品検索" value="{{ $search }}">
                    <input type="hidden" name="tab" value="{{ $currentPage }}">
                </form>
            </div>
            <nav class="header__nav">
                <?php if ($isAuthenticated): ?> <!-- 認証or未認証で分岐 -->
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf <!-- CSRFトークン -->
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

            <nav class="item-filter-nav">

                <div class="profile-info">
                    <div class="profile-details">
                        <!-- プロフィール画像があれば表示 -->
                        <img src="{{ $profileImage ? asset($profileImage) : asset('images/user_icon_default.png') }}" alt="プロフィール写真" class="profile-image">
                        <span class="user-name">{{ $userName ?? 'guest user' }}</span> <!-- ユーザー名がnullなら'guest user' -->
                    </div>
                    <a href="/mypage/profile" class="edit-profile-button">プロフィールを編集</a> <!-- プロフィール編集ボタン -->
                </div>

                <ul class="filter-list">
                    <li class="filter-option">
                        <a href="/mypage/?tab=sell&search={{ $search }}" class="filter-link {{ $currentPage === 'sell' ? 'active' : '' }}">出品した商品</a>
                    </li>
                    <li class="filter-option">
                        <a href="/mypage/?tab=buy&search={{ $search }}" class="filter-link {{ $currentPage === 'buy' ? 'active' : '' }}">購入した商品</a>
                    </li>
                    <!-- TODO：要対応 -->
                    <li class="filter-option">
                        <a href="/mypage/?tab=in_progress&search={{ $search }}" class="filter-link {{ $currentPage === 'in_progress' ? 'active' : '' }}">取引中の商品</a>
                    </li>
                </ul>

            </nav>


            <div class="item-container">
                @foreach ($items as $item)
                    <div class="item-card">
                        <a href="{{ route('item.detail', $item->id) }}">
                            <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-image">
                        </a>
                        @if ($item->isPurchased)
                            <img src="{{ asset('images/sold-label.svg') }}" alt="Sold" class="sold-label">
                        @endif
                        <h3 class="item-name">{{ $item->name }}</h3>
                    </div>
                @endforeach
            </div>


    </main>
</body>

</html>