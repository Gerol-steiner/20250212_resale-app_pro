<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/purchase/thanks.css') }}" />
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
        <div class="message-container">
            <div class="message">商品の購入が完了しました</h2>
        </div>

    </main>


</body>

</html>