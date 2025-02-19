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

    // 遷移前の入力内容を localStorage から復元
    let savedMessage = localStorage.getItem(`chatMessage_${purchaseId}`);
    if (savedMessage) {
        $(".message-input").val(savedMessage);
        console.log(`savedMessage: ${savedMessage}`);
    }

    // メッセージ入力時に localStorage に保存
    $(".message-input").on("input", function () {
        let messageText = $(this).val();
        localStorage.setItem(`chatMessage_${purchaseId}`, messageText);
        console.log(`messageText: ${messageText}`);
    });

    // サイドバーのリンクをクリックする前にメッセージを保存
    $(".transaction-link").on("click", function () {
        localStorage.setItem(
            `chatMessage_${purchaseId}`,
            $(".message-input").val()
        );
        console.log("asda");
    });

    // メッセージ取得ポーリング
    setInterval(fetchNewMessages, 8000);

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
                if (response.messages.length > 0) {
                    response.messages.forEach((message) => {
                        // 自分のメッセージはスキップ（相手のメッセージのみ追加）
                        if (message.user_id == userId) {
                            return; // 自分のメッセージなら処理しない
                        }

                        // 既にあるメッセージは追加しない
                        if (
                            $(
                                `.chat-message-container[data-message-id="${message.message_id}"]`
                            ).length > 0
                        ) {
                            return;
                        }

                        // 受信した profile_name, profile_image を使用
                        let senderName = message.profile_name
                            ? message.profile_name
                            : "ゲストユーザー";
                        let senderImage = message.profile_image
                            ? message.profile_image
                            : "/images/user_icon_default.png";

                        let newMessageHtml = `
                        <div class="chat-message-container" data-message-id="${
                            message.message_id
                        }">
                            <div class="user-info partner-user-info">
                                <img src="${senderImage}" alt="プロフィール写真" class="user-profile-image">
                                <span class="user-name">${senderName}</span>
                            </div>
                            <div class="chat-message partner-message">
                                ${
                                    message.message
                                        ? `<p class="message-text">${message.message}</p>`
                                        : ""
                                }
                                ${
                                    message.image_path
                                        ? `<img src="${message.image_path}" class="chat-image">`
                                        : ""
                                }
                            </div>
                            <div class="message-controls partner-message-controls">
                                <span class="message-time">${
                                    message.time
                                }</span>
                            </div>
                        </div>
                    `;

                        $(".chat-messages").append(newMessageHtml);
                    });

                    // 最新のメッセージ時刻を更新
                    lastMessageTime = response.latest_time;
                }
            },
            error: function () {
                console.error("メッセージ取得に失敗しました。");
            },
        });
    }

    // --- 画像アップロード処理 ---
    let selectedImage = null; // 選択した画像を保持

    // 「画像を追加」ボタンをクリック時に file input を開く
    $(".add-image").click(function () {
        $("#image-upload").click();
    });

    // 画像選択時の処理
    $("#image-upload").change(function (event) {
        let file = event.target.files[0];
        if (file) {
            selectedImage = file; // 画像を保存
            alert("画像が選択されました: " + file.name);
        }
    });

    // メッセージ送信処理（画像 + テキスト）
    $(".send-message-icon").click(function () {
        let message = $(".message-input").val().trim();
        let userId = $(".chat-messages").data("user-id");
        let formData = new FormData();
        formData.append("_token", $('meta[name="csrf-token"]').attr("content"));
        formData.append("purchase_id", $(".chat-messages").data("purchase-id"));
        if (message) formData.append("message", message);
        if (selectedImage) formData.append("image", selectedImage);

        // エラーメッセージをクリア
        $(".error-messages").remove();

        $.ajax({
            url: "/chat/send",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log("送信成功", response);

                // 送信成功後に localStorage から削除
                localStorage.removeItem(`chatMessage_${purchaseId}`);

                // エラーがあればクリア
                $(".error-messages").remove();

                if (!response.message_id) {
                    console.error("メッセージ ID が取得できません");
                    return;
                }

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

                let userName = response.profile_name
                    ? response.profile_name
                    : "ゲストユーザー";
                let userImage = response.profile_image
                    ? response.profile_image
                    : "/images/user_icon_default.png";

                let editDeleteButtons =
                    response.user_id == userId
                        ? `
                <div class="edit-delete-buttons">
                    <button class="edit-message" data-message-id="${response.message_id}">編集</button>
                    <button class="delete-message" data-message-id="${response.message_id}">削除</button>
                </div>`
                        : "";

                // テキストが空でも `message-text` を生成
                let messageTextHtml = response.message
                    ? `<p class="message-text">${response.message}</p>`
                    : `<p class="message-text" style="display:none;"></p>`; // 非表示で用意

                let newMessageHtml = `
                <div class="chat-message-container" data-message-id="${
                    response.message_id
                }">
                    <div class="user-info ${userInfoClass}">
                        ${
                            response.user_id == userId
                                ? `<span class="user-name">${userName}</span><img src="${userImage}" alt="プロフィール写真" class="user-profile-image">`
                                : `<img src="${userImage}" alt="プロフィール写真" class="user-profile-image"><span class="user-name">${userName}</span>`
                        }
                    </div>
                    <div class="chat-message ${messageClass}">
                        ${messageTextHtml}
                        ${
                            response.image_path
                                ? `<img src="${response.image_path}" class="chat-image" style="display: none;">`
                                : ""
                        }
                    </div>
                    <div class="message-controls ${controlsClass}">
                        <span class="message-time">${response.time}</span>
                        ${editDeleteButtons}
                    </div>
                </div>
            `;

                let $newMessage = $(newMessageHtml);
                $(".chat-messages").append($newMessage);
                $(".message-input").val(""); // 入力欄をクリア
                selectedImage = null;
                $("#image-upload").val("");

                // **ここで lastMessageTime を更新し、ポーリングによる重複を防ぐ**
                lastMessageTime = response.time;

                if (response.image_path) {
                    let img = new Image();
                    img.src = response.image_path;
                    img.onload = function () {
                        $newMessage
                            .find(".chat-image")
                            .attr("src", response.image_path)
                            .fadeIn();
                    };
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '<div class="error-messages">';

                    if (errors.message) {
                        errorMessages += `<p class="error-text">${errors.message[0]}</p>`;
                    }
                    if (errors.image) {
                        errorMessages += `<p class="error-text">${errors.image[0]}</p>`;
                    }

                    errorMessages += "</div>";

                    // エラーメッセージを `message-input` の上に表示
                    $(".chat-input").prepend(errorMessages);
                } else {
                    alert("メッセージの送信に失敗しました。");
                }
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

                // `message-text` がない場合は作成**
                if (messageTextElement.length === 0) {
                    messageTextElement = $('<p class="message-text"></p>');
                    chatMessageElement.prepend(messageTextElement);
                }

                // メッセージを更新して表示
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

    $(".complete-transaction").click(function () {
        let purchaseId = $(".chat-messages").data("purchase-id");

        $.ajax({
            url: "/transaction/complete",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                purchase_id: purchaseId,
            },
            success: function (response) {
                console.log("取引が完了しました", response);

                // 取引完了後に評価画面を表示
                showRatingModal();
            },
            error: function (xhr) {
                alert("取引を完了できませんでした。");
            },
        });
    });

    // 評価画面を表示する関数
    function showRatingModal() {
        let modalHtml = `
            <div class="rating-modal">
                <div class="modal-content">
                    <h3>取引が完了しました。</h3>
                    <div class="rating-text">
                        <p>今回の取引相手はどうでしたか？</p>
                        <div class="star-rating">
                            <img src="/images/star_off.svg" class="star" data-value="1">
                            <img src="/images/star_off.svg" class="star" data-value="2">
                            <img src="/images/star_off.svg" class="star" data-value="3">
                            <img src="/images/star_off.svg" class="star" data-value="4">
                            <img src="/images/star_off.svg" class="star" data-value="5">
                        </div>
                    </div>
                    <button class="submit-rating" disabled>送信する</button>
                </div>
            </div>
        `;

        $("body").append(modalHtml);
        $(".rating-modal").fadeIn();
    }

    // 星をクリックして評価を選択
    $(document).on("click", ".star", function () {
        let selectedRating = $(this).data("value");

        // すべての星を OFF にする
        $(".star").attr("src", "/images/star_off.svg");

        // クリックされた星までを ON にする
        $(".star").each(function () {
            if ($(this).data("value") <= selectedRating) {
                $(this).attr("src", "/images/star_on.svg");
            }
        });

        // 選択した評価をデータ属性に保存
        $(".star-rating").data("selected-rating", selectedRating);

        // 送信ボタンを有効化
        $(".submit-rating").prop("disabled", false);
    });

    // 送信ボタンをクリックして評価をサーバーへ送信
    $(document).on("click", ".submit-rating", function () {
        let purchaseId = $(".chat-messages").data("purchase-id");
        let rating = $(".star-rating").data("selected-rating");

        if (!rating) {
            alert("評価を選択してください。");
            return;
        }

        $.ajax({
            url: "/transaction/rate",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                purchase_id: purchaseId,
                rating: rating,
            },
            success: function (response) {
                console.log("評価が送信されました", response);

                // モーダルを閉じる
                $(".rating-modal").fadeOut(300, function () {
                    $(this).remove();
                });
            },
            error: function (xhr) {
                alert("評価の送信に失敗しました。");
            },
        });
    });
});
