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

        if (offset === 0) {
          const notExistsLabel = document.getElementById("messages-not-exists");
          notExistsLabel.classList.remove("d-none");
          return;
        }
      }

      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await loadMessages();

  function createMessageEl(message, parent) {
    const div = document.createElement("div");
    const innerDiv = document.createElement("div");
    innerDiv.textContent = message.content;
    innerDiv.style.maxWidth = "60%";

    if (message.isMyMessage) {
      div.classList.add("msg", "d-flex", "justify-content-end", "mb-2");
      innerDiv.classList.add("bg-secondary-subtle", "d-inline-block", "p-2", "me-1", "rounded-top-3", "rounded-start-3");
    } else {
      div.classList.add("msg", "d-flex", "justify-content-start", "mb-2");
      innerDiv.classList.add("bg-primary-subtle", "d-inline-block", "p-2", "me-1", "rounded-top-3", "rounded-end-3");
    }

    div.appendChild(innerDiv);
    parent.prepend(div);
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
});
