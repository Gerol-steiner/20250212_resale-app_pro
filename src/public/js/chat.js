$(document).ready(function () {
    // 最後に取得したメッセージの時間

    console.log("初期 lastMessageTime:", lastMessageTime);
    // HTML内のchat-messagesクラスのカスタムデータ属性に保持されたpurchase_id、user_idを取得
    let purchaseId = $(".chat-messages").data("purchase-id");
    let userId = $(".chat-messages").data("user-id");

    if (!purchaseId || !userId) {
        alert("取引情報が取得できませんでした。");
        return;
    }

    // メッセージ取得ポーリング
    setInterval(fetchNewMessages, 10000);

    // 新しいメッセージを取得する関数
    function fetchNewMessages() {
        $.ajax({
            url: "/chat/get-messages",
            type: "GET",
            data: {
                purchase_id: purchaseId,
                last_time: lastMessageTime, // 最後に取得したメッセージの時間
            },
            success: function (response) {
                console.log("サーバーからのレスポンス:", response.latest_time);
                // レスポンスに新しいメッセージが含まれている場合のみ処理
                if (response.messages.length > 0) {
                    response.messages.forEach((message) => {
                        let messageClass =
                            message.user_id == userId
                                ? "my-message"
                                : "partner-message";

                        // 既にあるメッセージと被らないように `created_at` でフィルタリング
                        if (message.time !== lastMessageTime) {
                            $(".chat-messages").append(`
                                <div class="chat-message ${messageClass}">
                                    <p class="message-text">${message.message}</p>
                                    <span class="message-time">${message.time}</span>
                                </div>
                            `);
                        }
                    });

                    // 最後に取得したメッセージの時間を更新
                    console.log("更新後の last_time:", lastMessageTime);
                    lastMessageTime = response.latest_time;
                }
            },
            error: function () {
                console.error("メッセージ取得に失敗しました。");
            },
        });
    }

    // メッセージ送信
    $(".send-message").click(function () {
        let message = $(".message-input").val().trim();
        if (message === "") return;

        $.ajax({
            url: "/chat/send",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                purchase_id: purchaseId,
                message: message,
            },
            success: function (response) {
                let messageClass =
                    response.user_id == userId
                        ? "my-message"
                        : "partner-message";

                $(".chat-messages").append(`
                    <div class="chat-message ${messageClass}">
                        <p class="message-text">${response.message}</p>
                        <span class="message-time">${response.time}</span>
                    </div>
                `);

                $(".message-input").val("");

                // 最後に取得したメッセージの時間を更新
                lastMessageTime = response.time;
            },
            error: function () {
                alert("メッセージの送信に失敗しました。");
            },
        });
    });
});
