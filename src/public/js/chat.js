$(document).ready(function () {
    $(".send-message").click(function () {
        let message = $(".message-input").val().trim();
        if (message === "") return; // 空メッセージの送信防止

        // HTML内のchat-messagesクラスのカスタムデータ属性に保持されたpurchase_id、user_idを取得
        let purchaseId = $(".chat-messages").data("purchase-id");
        let userId = $(".chat-messages").data("user-id");

        if (!purchaseId || !userId) {
            alert("取引情報が取得できませんでした。");
            return;
        }

        $.ajax({
            url: "/chat/send",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"), // CSRFトークン
                purchase_id: purchaseId, // 取得した purchase_id
                message: message,
            },
            success: function (response) {
                // 自分のメッセージか相手のメッセージかを判定してclass名を割り当てる
                let messageClass =
                    response.user_id == userId
                        ? "my-message"
                        : "partner-message";

                // 送信成功時、新しいメッセージを `.chat-messages` に追加
                $(".chat-messages").append(`
                    <div class="chat-message ${messageClass}">
                        <p class="message-text">${response.message}</p>
                        <span class="message-time">${response.time}</span>
                    </div>
                `);

                // 入力欄をクリア
                $(".message-input").val("");
            },
            error: function () {
                alert("メッセージの送信に失敗しました。");
            },
        });
    });
});
