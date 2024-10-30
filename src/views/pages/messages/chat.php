<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <div>
        <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
            <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
        </a>
    </div>

    <div id="chat-user-info" class="d-flex align-items-center gap-1 d-none">
        <a class="chat-user-link">
            <img id="chat-user-image" src="" alt="プロフィール画像" class="rounded-circle" height="40" width="40">
        </a>
        <a id="chat-user-name" class="fs-5 text-black text-decoration-none chat-user-link"></a>
    </div>

    <div id="messages-wrapper" class="my-3 flex-grow-1" style="overflow-y: scroll;">
        <div id="spinner" class="text-center d-none my-2">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="messages">
        </div>
    </div>

    <div class="input-group px-3">
        <textarea id="message-input" class="form-control p-2" rows="1" placeholder="新しいメッセージを作成" style="resize: none;"></textarea>
        <button class="btn btn-primary rounded-end disabled" type="button" id="send-button">送信</button>
        <div id="message-input-error-msg" class="invalid-feedback"></div>
    </div>
</div>


</div>
</div>
</div>

<script src="/js/pages/messages/chat.js"></script>
