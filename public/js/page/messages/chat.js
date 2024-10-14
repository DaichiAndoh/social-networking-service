document.addEventListener("DOMContentLoaded", async function () {
  /**
   * ユーザーデータ初期化処理
   */
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const username = urlParams.get("un");

  async function initChatUser() {
    const formData = new FormData();
    formData.append("username", username);
    const resData = await apiPost("/api/messages/chat/user", formData);

    if (resData.success) {
      if (resData.userData) {
        const chatUserLinks = document.querySelectorAll(".chat-user-link");
        chatUserLinks.forEach(l => { l.href = resData.userData.profilePath });

        const chatUserImage = document.getElementById("chat-user-image");
        chatUserImage.src = resData.userData.profileImagePath;

        const chatUserInfo = document.getElementById("chat-user-info");
        chatUserInfo.classList.remove("d-none");
      }
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await initChatUser();


  /**
   * メッセージデータ初期化処理
   */
  const listEl = document.getElementById("messages");
  const spinner = document.getElementById("spinner");
  const limit = 30;
  let offset = 0;
  let loadAll = false;

  async function loadMessages() {
    const formData = new FormData();
    formData.append("username", username);
    formData.append("limit", limit);
    formData.append("offset", offset);
    const resData = await apiPost("/api/messages/chat/messages", formData);

    if (resData.success) {
      if (resData.messages.length) {
        for (const message of resData.messages) {
          createMessageEl(message, listEl);
        }
        offset += limit;
      } else {
        loadAll = true;
      }

      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await loadMessages();

  function createMessageEl(message, parent, direction = "start") {
    const div = document.createElement("div");
    const innerDiv = document.createElement("div");
    innerDiv.innerText = message.content;
    innerDiv.style.maxWidth = "60%";

    if (message.isMyMessage) {
      div.classList.add("msg", "d-flex", "justify-content-end", "mb-2");
      innerDiv.classList.add("bg-secondary-subtle", "d-inline-block", "p-2", "me-1", "rounded-top-3", "rounded-start-3");
    } else {
      div.classList.add("msg", "d-flex", "justify-content-start", "mb-2");
      innerDiv.classList.add("bg-primary-subtle", "d-inline-block", "p-2", "me-1", "rounded-top-3", "rounded-end-3");
    }

    div.appendChild(innerDiv);
    if (direction === "start") parent.prepend(div);
    else parent.append(div);
  }


  /**
   * messages-wrapperのスクロールバーの初期表示位置設定処理
   * スクロールバーを最下部にする
   */
  function scrollbarToBottom() {
    const messagesWrapper = document.getElementById("messages-wrapper");
    messagesWrapper.scrollTop = messagesWrapper.scrollHeight;
  }
  scrollbarToBottom();


  /**
   * messages-wrapperのスクロール時の処理
   */
  document.getElementById("messages-wrapper").addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop === 0) {
      if (!loadAll) {
        spinner.classList.remove("d-none");
        await loadMessages();
      }
    }
  });


  /**
   * 送信ボタンのdisabled切り替え処理
   */
  const messageInput = document.getElementById("message-input");
  const btn = document.getElementById("send-button");
  messageInput.addEventListener("input", (event) => {
    const value = event.target.value.trim();
    if (value.length === 0 || value.length > 200) {
      btn.classList.add("disabled");
      if (value.length) {
        setFormValidation(messageInput.id, "メッセージは200文字以下で入力してください。");
      }
    } else {
      btn.classList.remove("disabled");
      if (messageInput.classList.contains("is-invalid")) {
        resetFormValidations();
      }
    }
  });


  /**
   * WebSocket関連処理
   */
  async function connectToWsServer() {
    let fun = "";
    let tun = "";
    let token = "";

    // WebSocket用認証トークン取得
    const formData = new FormData();
    formData.append("username", username);
    const resData = await apiPost("/api/messages/chat/token", formData);
    if (resData.success) {
      fun = resData.fun ?? "";
      tun = resData.tun ?? "";
      token = resData.token ?? "";
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }

    if (!fun || !tun || !token) alert("エラーが発生しました。");

    // WebSocketサーバー接続
    const conn = new WebSocket(`ws://localhost:8080?fun=${fun}&tun=${tun}&t=${token}`);

    conn.addEventListener("open", (event) => {});

    conn.addEventListener("message", (event) => {
      const message = JSON.parse(event.data);
      createMessageEl(message, listEl, "end");
      offset++;
      resetChat();
    });

    conn.addEventListener("error", (error) => {
      console.error(error);
    });

    conn.addEventListener("close", (event) => {});

    return conn;
  }
  // WebSocket接続, 初期化
  const wsConn = await connectToWsServer();

  // メッセージ送信
  btn.addEventListener("click", (event) => {
    const content = messageInput.value;
    wsConn.send(JSON.stringify({ content }));
  });

  // メッセージ送信完了後の処理
  function resetChat() {
    // 入力欄をクリア
    messageInput.value = "";

    // チャットスクロールを最下部に移動
    const wrapper = document.getElementById("messages-wrapper");
    wrapper.scrollTop = wrapper.scrollHeight;
  }
});
