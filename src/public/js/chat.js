$(document).ready(function () {
    $(".send-message").click(function () {
        let message = $(".message-input").val().trim();
        if (message === "") return; // 空メッセージの送信防止

        // purchase_id を取得
        let purchaseId = $(".chat-messages").data("purchase-id");

        if (!purchaseId) {
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
                // 送信成功時、新しいメッセージを `.chat-messages` に追加
                $(".chat-messages").append(`
                    <div class="chat-message my-message">
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
