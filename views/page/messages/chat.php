<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
        <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
    </a>

    <div id="chat-user-info" class="d-flex align-items-center gap-1 d-none">
        <a class="chat-user-link">
            <img id="chat-user-image" src="" alt="プロフィール画像" class="rounded-circle" height="40" width="40">
        </a>
        <a class="fs-5 text-black text-decoration-none chat-user-link">test_user</a>
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
        <input type="text" class="form-control" placeholder="新しいメッセージを作成" aria-label="新しいメッセージを作成" aria-describedby="send-button">
        <button class="btn btn-outline-primary" type="button" id="send-button">送信</button>
    </div>
</div>


</div>
</div>
</div>

<script src="/js/page/messages/chat.js"></script>
