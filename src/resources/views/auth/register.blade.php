<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo-image">
        </a>
    </header>

    <main>
        <div class="form__container">
            <div class="form__title">
                <h2>会員登録</h2>
            </div>
            <form class="form" action="/register" method="post">
                @csrf
                <div class="form__inner">
                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">お名前</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="text" name="name" value="{{ old('name') }}" />
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <div class="error-message">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">メールアドレス</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="email" name="email" value="{{ old('email') }}" />
                                @if ($errors->has('email'))
                                    @foreach ($errors->get('email') as $error)
                                        <div class="error-message">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">パスワード</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="password" name="password" value="{{ old('password') }}"/>
                                @if ($errors->has('password'))
                                    @foreach ($errors->get('password') as $error)
                                        <div class="error-message">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">確認用パスワード</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}"/>
                                @if ($errors->has('password_confirmation'))
                                    @foreach ($errors->get('password_confirmation') as $error)
                                        <div class="error-message">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form__button">
                        <button class="form__button-submit" type="submit">登録する</button>
                    </div>

                    <div class="form__link">
                        <a href="/login">ログインはこちら</a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>

</html>