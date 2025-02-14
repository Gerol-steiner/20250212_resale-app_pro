<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/items/purchase.css') }}" />

    <script>
        // 画面右型の支払い方法の表示用
        function updatePaymentMethod() {
            const paymentSelect = document.getElementById('payment_method');
            const selectedPayment = paymentSelect.options[paymentSelect.selectedIndex].text;
            document.getElementById('selected-payment').innerText = selectedPayment;
        }

        // ページが読み込まれた後にoldの値を設定
        document.addEventListener('DOMContentLoaded', function() {
            const oldPaymentMethod = "{{ old('payment_method') }}"; // oldの値を取得
            if (oldPaymentMethod) {
                document.getElementById('selected-payment').innerText = oldPaymentMethod; // oldの値を表示
            }
        });
    </script>

    <!-- Stripeの JavaScript SDK を読み込む -->
    <script src="https://js.stripe.com/v3/"></script>

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

        <div class="main-container">
            <!--左側フレーム-->
            <div class="left-container">
                <!--左：上段-->
                <div class="item-detail">
                    <!--「商品画像」-->
                    <div class="img-card">
                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-image">
                        @if ($item->isPurchased)
                            <img src="{{ asset('images/sold-label.svg') }}" alt="Sold" class="sold-label">
                        @endif
                    </div>
                    <!--「商品名」と「価格」-->
                    <div class="item-info">
                        <div class="item-name">{{ $item->name }}</div>
                        <div class="price">
                            <span class="yen-symbol">￥</span>{{ number_format($item->price) }}
                        </div>
                    </div>
                </div>

                <!--左：中段「支払い方法」-->
                <div class="payment-method">
                    <div class="payment-method__title">支払い方法</div>

                    <!--「支払い方法」プルダウンメニュー-->
                    <form id="payment-form" class=payment-method__form>
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}"> <!-- アイテムIDを隠しフィールドとして追加 -->
                        <select name="payment_method" id="payment_method" onchange="updatePaymentMethod()">
                            <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>選択してください</option>
                            <option value="コンビニ支払い" {{ old('payment_method') === 'コンビニ支払い' ? 'selected' : '' }}>コンビニ支払い</option>
                            <option value="カード支払い" {{ old('payment_method') === 'カード支払い' ? 'selected' : '' }}>カード支払い</option>
                        </select>

                        <!-- 隠しフィールドで住所情報をリクエストに含めて送信 -->
                        <input type="hidden" name="postal_code" value="{{ $address->postal_code ?? '' }}">
                        <input type="hidden" name="address" value="{{ $address->address ?? '' }}">
                        <input type="hidden" name="building" value="{{ $address->building ?? '' }}">
                        <input type="hidden" name="id" value="{{ $address->id ?? '' }}">
                        <input type="hidden" name="is_default" value="{{ $address->is_default ?? '' }}">

                    </form>

                </div>

                <!--左：下段-->
                <div class="shipping-address">
                    <div class="shipping-address__upper">
                        <div class="shipping-address__title">
                            配送先
                        </div>
                        <a href="{{ url('/purchase/address/' . $item->id) }}" class="change-address-link">変更する</a>
                    </div>
                    <div class="address-details">

                        @if (!empty($address->postal_code))
                            <p>郵便番号: {{ $address->postal_code }}</p>
                        @else
                            <p>郵便番号: 　（未設定）</p>
                        @endif

                        @if (!empty($address->address))
                            <p>住所: {{ $address->address }}</p>
                        @else
                            <p>住所: 　（未設定）</p>
                        @endif

                        @if (!empty($address->building))
                            <p>建物名: {{ $address->building }}</p>
                        @else
                            <p>建物名: 　（未設定）</p>
                        @endif

                    </div>
                </div>
            </div>

            <!--右側フレーム-->
            <div class="right-container">
                <div class="item-price__right-container">
                    <div class="item-price-title__right">商品代金</div>
                    <div class="item-price__right">
                        <span class="price-wrapper">
                            <span class="yen-symbol__right">￥</span>
                            <span class="price-value">{{ number_format($item->price) }}</span>
                        </span>
                    </div>
                </div>
                <div class="payment-method__right-container">
                    <div class="payment-method__right">支払い方法</div>
                    <div id="selected-payment" class="selected-payment"></div>
                </div>
                <button type="button" id="checkout-button" class="purchase-button">購入する</button>
                        <!--エラーメッセージ-->
                        <div id="error-payment-method" class="text-danger" style="display: none;"></div>
                        <div id="error-postal-code" class="text-danger" style="display: none;"></div>
                        <div id="error-address" class="text-danger" style="display: none;"></div>
                        <div id="error-building" class="text-danger" style="display: none;"></div>
            </div>

        </div>

    </main>

<script>
const stripe = Stripe(@json(env('STRIPE_PUBLISHABLE_KEY'))); // Stripeの公開可能キーを使用してStripeインスタンスを作成
const checkoutButton = document.getElementById('checkout-button'); // チェックアウトボタンの要素を取得
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // CSRFトークンを取得

// チェックアウトボタンがクリックされたときの処理
checkoutButton.addEventListener('click', function() {
    const paymentMethod = document.getElementById('payment_method').value; // 選択された支払い方法を取得
    const itemId = @json($item->id); // 商品IDを取得
    const addressId = @json($address->id ?? null); // 住所IDを取得（nullの場合はnullを設定）
    const postalCode = @json($address->postal_code ?? ''); // 郵便番号を取得（nullの場合は空文字を設定）
    const address = @json($address->address ?? ''); // 住所を取得（nullの場合は空文字を設定）
    const building = @json($address->building ?? ''); // 建物名を取得（nullの場合は空文字を設定）

    // AJAXリクエストを送信
    fetch('{{ route('validate.purchase') }}', {
        method: 'POST', // POSTメソッドを指定
        headers: {
            'Content-Type': 'application/json', // JSON形式のデータを送信
            'X-CSRF-TOKEN': csrfToken, // CSRFトークンをヘッダーに追加
            'Accept': 'application/json', // レスポンス形式の指定
        },
        body: JSON.stringify({ // 送信するデータをJSON形式に変換
            payment_method: paymentMethod,
            item_id: itemId,
            address_id: addressId,
            postal_code: postalCode,
            address: address,
            building: building
        })
    })
    .then(response => response.json()) // レスポンスのボディを JSON 形式に変換
    .then(data => {
        if (data.success) {  // PurchaseControllerの「validatePurchaseメソッド」からのreturn

            if (paymentMethod === 'カード支払い') {
                // 成功時の処理（カード支払い）
                console.log('購入処理が成功しました:', data);
                stripe.redirectToCheckout({ sessionId: data.session_id }) // Stripe決済画面へのリダイレクト
                    .then(function (result) {
                        if (result.error) {
                            // エラーメッセージを表示
                            console.error(result.error.message);
                        }
                    });
            } else if (paymentMethod === 'コンビニ支払い') {
                // 成功時の処理（コンビニ支払い）
                console.log('購入処理が成功しました:', data);
                window.location.href = '/showthanks'; // thanks.blade.phpへ遷移
            }

        } else if (data.errors) {
            // 支払い方法のエラーメッセージを処理
            const paymentMethodErrorElement = document.getElementById('error-payment-method'); // 支払い方法エラーメッセージ表示用の要素を取得
            paymentMethodErrorElement.style.display = 'none'; // 初期状態では非表示
            if (data.errors.payment_method) {
                paymentMethodErrorElement.textContent = data.errors.payment_method[0]; // 支払い方法のエラーメッセージを設定
                paymentMethodErrorElement.style.display = 'block'; // 支払い方法のエラーメッセージを表示
            }

            // 郵便番号のエラーメッセージを処理
            const postalErrorElement = document.getElementById('error-postal-code'); // 郵便番号エラーメッセージ表示用の要素を取得
            postalErrorElement.style.display = 'none'; // 初期状態では非表示
            if (data.errors.postal_code) {
                postalErrorElement.textContent = data.errors.postal_code[0]; // 郵便番号のエラーメッセージを設定
                postalErrorElement.style.display = 'block'; // 郵便番号のエラーメッセージを表示
            }

            // 住所のエラーメッセージを処理
            const addressErrorElement = document.getElementById('error-address'); // 住所エラーメッセージ表示用の要素を取得
            addressErrorElement.style.display = 'none'; // 初期状態では非表示
            if (data.errors.address) {
                addressErrorElement.textContent = data.errors.address[0]; // 住所のエラーメッセージを設定
                addressErrorElement.style.display = 'block'; // 住所のエラーメッセージを表示
            }

            // 建物名のエラーメッセージを処理
            const buildingErrorElement = document.getElementById('error-building'); // 建物名エラーメッセージ表示用の要素を取得
            buildingErrorElement.style.display = 'none'; // 初期状態では非表示
            if (data.errors.building) {
                buildingErrorElement.textContent = data.errors.building[0]; // 建物名のエラーメッセージを設定
                buildingErrorElement.style.display = 'block'; // 建物名のエラーメッセージを表示
            }
        }
    })
    .catch(error => {
        console.error('Error:', error); // エラーをコンソールに表示
    });
});
</script>


</body>

</html>