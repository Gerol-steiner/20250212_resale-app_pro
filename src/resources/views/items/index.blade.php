<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/items/index.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header_inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
            </a>
            <div class="header__search">
                <form action="/" method="GET" class="search-form">
                    <button type="submit" class="search-button">検索</button>
                    <input type="text" name="search" class="search-input" placeholder="なにをお探しですか？" aria-label="商品検索" value="{{ $search }}">
                    <input type="hidden" name="tab" value="{{ $currentPage }}">
                </form>
            </div>
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

    <main>
            <nav class="item-filter-nav">
                <div>user_id : <?php echo $userId ?? '未ログイン'; ?> (※ 開発用)</div>
                <!--フラッシュメッセージ 「メールアドレスは既に認証されています」-->
                <!--フラッシュメッセージ 「メールアドレスは既に認証されています。ログインしました。」-->
                @if(session('info'))
                    <div class="alert-info">
                        {{ session('info') }}
                    </div>
                @endif
                <ul class="filter-list">
                    <li class="filter-option">
                        <a href="/?tab=home&search={{ $search }}" class="filter-link {{ $currentPage === 'home' ? 'active' : '' }}">おすすめ</a>
                    </li>
                    <li class="filter-option">
                        <a href="/?tab=mylist&search={{ $search }}" class="filter-link {{ $currentPage === 'mylist' ? 'active' : '' }}">マイリスト</a>
                    </li>
                </ul>
            </nav>


            <div class="item-container">
                @foreach ($items as $item)
                    <div class="item-card">
                        <!--showDetalメソッドから渡された$itemオブジェクトからid取り出し-->
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