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

    // メッセージ編集処理
    $(document).on("click", ".edit-message", function () {
        let messageId = $(this).data("message-id");
        let messageContainer = $(this).closest(".chat-message-container");
        let messageElement = messageContainer.find(".chat-message.my-message");
        let messageText = messageElement.find(".message-text");
        let currentText = messageElement.find(".message-text").text().trim();

        // すでに編集モードの場合は何もしない
        if (messageContainer.find(".edit-message-form").length > 0) {
            return;
        }

        // メッセージテキストを非表示
        messageText.hide();

        // 編集フォームのHTML
        let editForm = `
        <div class="edit-message-form">
            <textarea class="edit-message-input">${currentText}</textarea>
            <div class="edit-buttons">
                <button class="save-message" data-message-id="${messageId}">登録</button>
                <button class="cancel-edit">キャンセル</button>
            </div>
        </div>
    `;

        // メッセージ本体（.chat-message.my-message）の子要素として追加
        messageElement.append(editForm);
    });

    // 編集のキャンセル
    $(document).on("click", ".cancel-edit", function () {
        let messageContainer = $(this).closest(".chat-message-container");
        messageContainer.find(".edit-message-form").remove();
        messageContainer.find(".message-text").show();
    });

    // メッセージの保存（サーバーへのリクエスト発生）
    $(document).on("click", ".save-message", function () {
        let messageContainer = $(this).closest(".chat-message-container");
        let messageId = $(this).data("message-id");
        let newMessage = messageContainer
            .find(".edit-message-input")
            .val()
            .trim();
        let messageTextElement = messageContainer.find(".message-text");
        let chatMessageElement = messageContainer.find(
            ".chat-message.my-message"
        );

        if (newMessage === "") {
            alert("メッセージを入力してください。");
            return;
        }

        $.ajax({
            url: "/chat/edit",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                message_id: messageId,
                message: newMessage,
            },
            success: function (response) {
                console.log(response);

                // メッセージを更新
                messageTextElement.text(response.message).show();
                messageContainer.find(".edit-message-form").remove();

                // 既に「(編集済み)」のラベルがない場合のみ追加（message-textの下に追加）
                if (
                    response.is_edited &&
                    chatMessageElement.find(".edited-label").length === 0
                ) {
                    chatMessageElement.append(
                        '<span class="edited-label">(編集済み)</span>'
                    );
                }
            },
            error: function (xhr) {
                console.error(
                    "メッセージの編集に失敗しました。",
                    xhr.responseText
                );
                alert("編集に失敗しました。");
            },
        });
    });
});
