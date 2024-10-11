document.addEventListener("DOMContentLoaded", async function () {
  /**
   * プロフィール初期化処理
   */
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const username = urlParams.get("un");

  const formData = new FormData();
  formData.append("username", username ?? "");
  const resData = await apiPost("/api/user/profile/init", formData);

  if (resData.success) {
    initProfile(resData.userData);
  } else {
    if (resData.error) {
      alert(resData.error);
    }
  }

  function initProfile(userData) {
    if (userData === null) {
      const userNotFound = document.getElementById("user-not-found");
      userNotFound.classList.remove("d-none");
      return;
    }

    // ユーザープロフィール
    const nameEl = document.getElementById("profile-name");
    const usernameEl = document.getElementById("profile-username");
    const profileTextEl = document.getElementById("profile-profile-text");
    const profileImageEl = document.getElementById("profile-profile-image");
    const profileImageLinkEl = document.getElementById("profile-profile-image-link");
    const followeeCountEl = document.getElementById("followee-count");
    const followerCountEl = document.getElementById("follower-count");
    const followeeLink = document.getElementById("followee-link");
    const followerLink = document.getElementById("follower-link");

    nameEl.innerText = userData.name;
    usernameEl.innerText = "@" + userData.username;
    profileTextEl.innerText = userData.profileText ?? "";
    profileImageEl.src = userData.profileImagePath;
    profileImageLinkEl.href = userData.profileImagePath;
    followeeCountEl.innerText = userData.followeeCount;
    followerCountEl.innerText = userData.followerCount;
    followeeLink.href = `/user/followees?un=${userData.username}`;
    followerLink.href = `/user/followers?un=${userData.username}`;
  
    // 表示ボタン判定, 編集モーダル初期値設定
    const nameInput = document.getElementById("name");
    const usernameInput = document.getElementById("username");
    const profileTextInput = document.getElementById("profile-text");
    const defaultRadio = document.getElementById("profile-image-type-default");
    const customRadio = document.getElementById("profile-image-type-custom");
    const profileImagePreviewEl = document.getElementById("profile-image-preview");

    const messageBtn = document.getElementById("message-btn");
    const editBtn = document.getElementById("profile-edit-btn");
    const followBtn = document.getElementById("profile-follow-btn");
    const unfollowBtn = document.getElementById("profile-unfollow-btn");
    const followerLabel = document.getElementById("follower-label");

    nameInput.value = userData.name;
    usernameInput.value = userData.username;
    profileTextInput.value = userData.profileText ?? "";
    defaultRadio.checked = userData.profileImageType === "default";
    customRadio.checked = userData.profileImageType === "custom";
    profileImagePreviewEl.src = userData.profileImagePath;

    if (userData.isLoggedInUser) {
      editBtn.classList.remove("d-none");
    } else {
      messageBtn.href = `/messages/chat?un=${userData.username}`;
      messageBtn.classList.remove("d-none");

      if (userData.isFollowee) {
        unfollowBtn.classList.remove("d-none");
      } else {
        followBtn.classList.remove("d-none");
      }

      if (userData.isFollower) {
        followerLabel.classList.remove("d-none");
      }
    }

    // ユーザープロフィールブロック表示（ここで表示させることで画面のちらつきを抑える）
    const profileBlock = document.getElementById("profile-block");
    profileBlock.classList.remove("d-none");
  }


  /**
   * プロフィール編集モーダル
   * 画像タイプラジオボタン変更時の処理
   */
  const defaultRadio = document.getElementById("profile-image-type-default");
  const customRadio = document.getElementById("profile-image-type-custom");
  const uploadBlock = document.getElementById("profile-image-upload-block");

  function toggleUploadBlock() {
    if (defaultRadio.checked) {
      uploadBlock.classList.add("d-none");
    } else {
      uploadBlock.classList.remove("d-none");
    }
  }

  toggleUploadBlock();
  customRadio.addEventListener("change", toggleUploadBlock);
  defaultRadio.addEventListener("change", toggleUploadBlock);


  /**
   * プロフィール編集モーダル
   * ファイルinput値変更時の処理
   */
  const profileImageInput = document.getElementById("profile-image");
  profileImageInput.addEventListener("change", function(event) {
    const file = event.target.files[0]; // アップロードされたファイルを取得

    if (file && file.type.startsWith("image/")) { // ファイルが画像の場合のみ処理
      const reader = new FileReader(); // FileReaderオブジェクトを作成

      reader.onload = function(e) {
        const previewImage = document.getElementById("profile-image-preview");
        previewImage.src = e.target.result; // 読み込んだ画像をプレビューに設定
        previewImage.style.display = "block"; // img要素を表示
      };

      reader.readAsDataURL(file); // ファイルをデータURLとして読み込む
    }
  });


  /**
   * プロフィール編集モーダル
   * 保存ボタンクリック時の処理
   */
  const form = document.getElementById("profile-edit-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const formData = new FormData(form);
    const resData = await apiPost("/api/user/profile/edit", formData);

    if (resData === null) {
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      window.location.reload();
    } else {
      if (resData.fieldErrors) {
        for (const field in resData.fieldErrors) {
          setFormValidation(field, resData.fieldErrors[field]);
        }
      }
      if (resData.error) {
        alert(resData.error);
      }
    }
  });


  /**
   * フォローボタンクリック時の処理
   */
  const followBtn = document.getElementById("profile-follow-btn");
  followBtn.addEventListener("click", async function(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append("username", username ?? "");
    const resData = await apiPost("/api/user/follow", formData);

    if (resData === null) {
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      window.location.reload();
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  });


  /**
   * アンフォローボタンクリック時の処理
   */
  const unfollowBtn = document.getElementById("profile-unfollow-btn");
  unfollowBtn.addEventListener("click", async function(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append("username", username ?? "");
    const resData = await apiPost("/api/user/unfollow", formData);

    if (resData === null) {
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      window.location.reload();
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  });


  /**
   * ポスト一覧初期化処理
  */
  const postBlock = document.getElementById("post-block");
  const spinner = document.getElementById("spinner");
  const limit = 30;
  const listData = {
    posts: {
      offset: 0,
      loadAll: false,
      listEl: document.getElementById("posts-list"),
    },
    replies: {
      offset: 0,
      loadAll: false,
      listEl: document.getElementById("replies-list")
    },
    likes: {
      offset: 0,
      loadAll: false,
      listEl: document.getElementById("likes-list"),
    },
  }

  async function loadList(listType = "posts") {
    const formData = new FormData();
    formData.append("username", username ?? "");
    formData.append("limit", limit);
    formData.append("offset", listData[listType].offset ?? 0);
    const resData = await apiPost(`/api/user/${listType}`, formData);

    if (resData.success) {
      if (resData.posts.length) {
        for (const post of resData.posts) {
          createPostEl(post, listData[listType].listEl);
        }
        listData[listType].offset += limit;
      } else {
        listData[listType].loadAll = true;

        if (listData[listType].offset === 0) {
          document.getElementById("post-not-found").classList.remove("d-none");
          document.getElementById("list-wrapper").classList.add("d-none");
        }
      }
      spinner.classList.add("d-none");
      postBlock.classList.remove("d-none");
      postBlock.classList.add("d-flex", "flex-column");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await loadList();


  /**
   * ポスト種類タブ切り替え時の処理
   */
  let activeTab = "posts";
  document.querySelectorAll("#post-type-tabs .nav-link").forEach(link => {
    link.addEventListener("click", async function(event) {
      event.preventDefault();

      // 全てのnav-linkからactiveクラスを削除
      document.querySelectorAll("#post-type-tabs .nav-link").forEach(item => {
        item.classList.remove("active");
      });

      // クリックされたnav-linkにactiveクラスを追加
      this.classList.add("active");

      // 全てのセクションを非表示
      document.querySelectorAll("div[id$='-list']").forEach(section => {
        section.classList.add("d-none");
      });

      // クリックされたリンクに対応するセクションを表示
      const target = document.querySelector(this.getAttribute("data-target"));
      if (target) {
        target.classList.remove("d-none");
      }

      // 表示を戻す
      document.getElementById("post-not-found").classList.add("d-none");
      document.getElementById("list-wrapper").classList.remove("d-none");

      // リストのスクロールをトップに戻す
      const listWrapper = document.getElementById("list-wrapper");
      listWrapper.scrollTop = 0;
      activeTab = this.id.replace("-nav-link", "");
      if (listData[activeTab].offset === 0) {
        await loadList(activeTab);
      }
    });
  });


  /**
   * list-wrapperのスクロール時の処理
   */
  document.getElementById("list-wrapper").addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!listData[activeTab].loadAll) {
        spinner.classList.remove("d-none");
        await loadList(activeTab);
      }
    }
  });
});
