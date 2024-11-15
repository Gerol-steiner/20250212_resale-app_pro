<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/auth/registration_pending.css') }}" />
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
        </a>
    </header>

    <main>

        <div class="container">
            <h1>仮登録完了</h1>
            <p>登録したメールアドレス宛に認証メールを送信しましたので、そちらを確認して登録を完了させてください。</p>
            <p>メール内のリンクをクリックしてアカウントを確認してください。</p>
        </div>

    </main>

</html>
