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
    setInterval(fetchNewMessages, 3000);

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
                // レスポンスに新しいメッセージが含まれている場合のみ処理
                if (response.messages.length > 0) {
                    response.messages.forEach((message) => {
                        let messageClass =
                            message.user_id == userId
                                ? "my-message"
                                : "partner-message";
                        let controlsClass =
                            message.user_id == userId
                                ? "my-message-controls"
                                : "partner-message-controls";
                        let userInfoClass =
                            message.user_id == userId
                                ? "my-user-info"
                                : "partner-user-info";

                        let userName =
                            message.user_id == userId
                                ? profileName
                                : partnerName;
                        let userImage =
                            message.user_id == userId
                                ? profileImage
                                : partnerProfileImage;

                        // 自分のメッセージであれば「編集」「削除」ボタンを格納、相手のメッセージなら空文字を格納
                        let editDeleteButtons =
                            message.user_id == userId
                                ? `<div class="edit-delete-buttons">
                                        <button class="edit-message" data-message-id="${message.id}">編集</button>
                                        <button class="delete-message" data-message-id="${message.id}">削除</button>
                                    </div>`
                                : "";

                        // 既にあるメッセージと被らないように `created_at` でフィルタリング
                        if (message.time !== lastMessageTime) {
                            $(".chat-messages").append(`
                                <div class="chat-message-container">
                                    <div class="user-info ${userInfoClass}">
                                        ${
                                            message.user_id == userId
                                                ? `<span class="user-name">${userName}</span><img src="${userImage}" alt="プロフィール写真" class="user-profile-image">`
                                                : `<img src="${userImage}" alt="プロフィール写真" class="user-profile-image"><span class="user-name">${userName}</span>`
                                        }
                                    </div>
                                    <div class="chat-message ${messageClass}">
                                        <p class="message-text">${
                                            message.message
                                        }</p>
                                    </div>
                                    <div class="message-controls ${controlsClass}">
                                        <span class="message-time">${
                                            message.time
                                        }</span>
                                        ${editDeleteButtons}
                                    </div>
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
                let controlsClass =
                    response.user_id == userId
                        ? "my-message-controls"
                        : "partner-message-controls";
                let userInfoClass =
                    response.user_id == userId
                        ? "my-user-info"
                        : "partner-user-info";

                let userName =
                    response.user_id == userId ? profileName : partnerName;
                let userImage =
                    response.user_id == userId
                        ? profileImage
                        : partnerProfileImage;

                // 自分のメッセージであれば「編集」「削除」ボタンを格納、相手のメッセージなら空文字を格納
                let editDeleteButtons =
                    response.user_id == userId
                        ? `<div class="edit-delete-buttons">
                                <button class="edit-message" data-message-id="${response.id}">編集</button>
                                <button class="delete-message" data-message-id="${response.id}">削除</button>
                            </div>`
                        : "";

                $(".chat-messages").append(`
                    <div class="chat-message-container">
                        <div class="user-info ${userInfoClass}">
                            ${
                                response.user_id == userId
                                    ? `<span class="user-name">${userName}</span><img src="${userImage}" alt="プロフィール写真" class="user-profile-image">`
                                    : `<img src="${userImage}" alt="プロフィール写真" class="user-profile-image"><span class="user-name">${userName}</span>`
                            }
                        </div>
                        <div class="chat-message ${messageClass}">
                            <p class="message-text">${response.message}</p>
                        </div>
                        <div class="message-controls ${controlsClass}">
                            <span class="message-time">${response.time}</span>
                            ${editDeleteButtons}
                        </div>
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

    // メッセージ削除
    $(document).ready(function () {
        console.log("初期 lastMessageTime:", lastMessageTime);

        let purchaseId = $(".chat-messages").data("purchase-id");
        let userId = $(".chat-messages").data("user-id");

        if (!purchaseId || !userId) {
            alert("取引情報が取得できませんでした。");
            return;
        }

        // メッセージ削除処理
        $(document).on("click", ".delete-message", function () {
            let messageId = $(this).data("message-id");
            let messageContainer = $(this).closest(".chat-message-container");

            if (!confirm("このメッセージを削除しますか？")) {
                return;
            }

            $.ajax({
                url: "/chat/delete",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    message_id: messageId,
                },
                success: function (response) {
                    console.log(response);
                    messageContainer.fadeOut(300, function () {
                        $(this).remove();
                    });
                },
                error: function (xhr) {
                    console.error(
                        "メッセージの削除に失敗しました。",
                        xhr.responseText
                    );
                    alert("削除に失敗しました。");
                },
            });
        });
    });
});
