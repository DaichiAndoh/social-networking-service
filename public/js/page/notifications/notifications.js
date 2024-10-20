document.addEventListener("DOMContentLoaded", async function () {
  /**
   * 通知初期化処理
   */
  const listEl = document.getElementById("notifications-list");
  const spinner = document.getElementById("spinner");
  const limit = 30;
  let offset = 0;
  let loadAll = false;

  async function loadNotifications() {
    const formData = new FormData();
    formData.append("limit", limit);
    formData.append("offset", offset);
    const resData = await apiPost("/api/notifications/list", formData);

    if (resData.success) {
      if (resData.notifications.length) {
        for (const notification of resData.notifications) {
          createNotificationEl(notification, listEl);
        }
        offset += limit;
      } else {
        loadAll = true;

        if (offset === 0) {
          const notExistsLabel = document.getElementById("notifications-not-exists");
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
  await loadNotifications();

  function createNotificationEl(notification, parent) {
    let icon;
    let text;
    switch (notification.type) {
      case "FOLLOW":
        const userIcon = document.createElement("ion-icon");
        userIcon.setAttribute("name", "person");
        userIcon.style.color = "#CCC";
        icon = userIcon;

        text = "さんにフォローされました。"
        break;

      case "LIKE":
        const heartIcon = document.createElement("ion-icon");
        heartIcon.setAttribute("name", "heart");
        heartIcon.style.color = "red";
        icon = heartIcon;

        text = "さんがあなたのポストをいいねしました。"
        break;

      case "REPLY":
        const replyIcon = document.createElement("ion-icon");
        replyIcon.setAttribute("name", "chatbubbles");
        replyIcon.style.color = "#CCC";
        icon = replyIcon;

        text = "さんがあなたのポストに返信しました。"
        break;

      case "MESSAGE":
        const chatIcon = document.createElement("ion-icon");
        chatIcon.setAttribute("name", "chatbox-ellipses");
        chatIcon.style.color = "#CCC";
        icon = chatIcon;

        text = "さんからメッセージが届きました。"
        break;

      default:
        const _heartIcon = document.createElement("ion-icon");
        _heartIcon.setAttribute("name", "heart");
        _heartIcon.style.color = "red";
        icon = _heartIcon;

        text = "さんがあなたのポストをいいねしました。"
        break;
    }
    if (icon === undefined || text === undefined) return;

    const cardDiv = document.createElement("div");
    cardDiv.classList.add("card", "p-3", "rounded-0", `notification-${"1"}`);
    cardDiv.style.cursor = "pointer";
    if (!notification.isRead) cardDiv.style.backgroundColor = "rgba(136, 238, 255, .1)";
    cardDiv.addEventListener("click", async () => {
      if (notification.isRead) {
        window.location.href = notification.notificationPath;
        return;
      }

      const formData = new FormData();
      formData.append("notification_id", notification.notificationId);
      const resData = await apiPost("/api/notifications/read", formData);

      if (resData.success) {
        window.location.href = notification.notificationPath;
      } else {
        if (resData.error) {
          alert(resData.error);
        }
      }
    });

    const cardContentDiv = document.createElement("div");
    cardContentDiv.classList.add("d-flex", "align-items-top", "gap-1");

    /** 左ブロック */
    const leftDiv = document.createElement("div");

    // アイコン
    icon.classList.add("fs-2");
    leftDiv.appendChild(icon);

    /** 右ブロック */
    const rightDiv = document.createElement("div");
    rightDiv.classList.add("w-100", "ms-2");

    // プロフィール画像
    const profileImgDiv = document.createElement("div");

    const profileImgLink = document.createElement("a");
    profileImgLink.href = notification.fromUserProfilePath;

    const profileImg = document.createElement("img");
    profileImg.src = notification.fromUserProfileImagePath;
    profileImg.alt = "プロフィール画像";
    profileImg.width = 40;
    profileImg.height = 40;
    profileImg.classList.add("rounded-circle");

    profileImgLink.appendChild(profileImg);
    profileImgDiv.appendChild(profileImgLink);

    // コンテンツ
    const content = document.createElement("div");

    // ユーザーの名前
    const profileNameLink = document.createElement("a");
    profileNameLink.href = notification.fromUserProfilePath;
    profileNameLink.classList.add("text-black", "hover-underline");
    profileNameLink.textContent = notification.fromUserName;

    if (notification.userType === "INFLUENCER") {
      const influencerIcon = document.createElement("ion-icon");
      influencerIcon.setAttribute("name", "shield-checkmark");
      influencerIcon.style.color = "#dbbf4b";
      influencerIcon.style.height = "18px";
      influencerIcon.style.verticalAlign = "text-top";
      profileNameLink.appendChild(influencerIcon);
    }
    content.appendChild(profileNameLink);

    // 通知テキスト
    const textSpan = document.createElement("span");
    textSpan.classList.add("fw-light", "text-secondary");
    textSpan.innerText = text;

    content.appendChild(textSpan);

    rightDiv.appendChild(profileImgDiv);
    rightDiv.appendChild(content);

    /** 左右のブロックをカードのコンテンツブロックの子要素に追加 */
    cardContentDiv.appendChild(leftDiv);
    cardContentDiv.appendChild(rightDiv);
    cardDiv.appendChild(cardContentDiv);

    parent.appendChild(cardDiv);
  }


  /**
   * list-wrapperのスクロール時の処理
   */
  document.getElementById("list-wrapper").addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!loadAll) {
        spinner.classList.remove("d-none");
        await loadNotifications();
      }
    }
  });
});
