document.addEventListener("DOMContentLoaded", async function () {
  /**
   * プロフィール初期化処理
   */
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const username = urlParams.get("un");

  const followeeLink = document.getElementById("followee-link");
  const followerLink = document.getElementById("follower-link");
  followeeLink.href = `/user/followees${username ? "?un=" + username : ""}`;
  followerLink.href = `/user/followers${username ? "?un=" + username : ""}`;

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

    const profileBlock = document.getElementById("profile-block");

    const nameEl = document.getElementById("profile-name");
    const usernameEl = document.getElementById("profile-username");
    const profileTextEl = document.getElementById("profile-profile-text");
    const profileImageEl = document.getElementById("profile-profile-image");
    const profileImageLinkEl = document.getElementById("profile-profile-image-link");
    const followeeCountEl = document.getElementById("followee-count");
    const followerCountEl = document.getElementById("follower-count");

    const nameInput = document.getElementById("name");
    const usernameInput = document.getElementById("username");
    const profileTextInput = document.getElementById("profile-text");
    const defaultRadio = document.getElementById("profile-image-type-default");
    const customRadio = document.getElementById("profile-image-type-custom");
    const profileImagePreviewEl = document.getElementById("profile-image-preview");

    const editBtn = document.getElementById("profile-edit-btn");
    const followBtn = document.getElementById("profile-follow-btn");
    const unfollowBtn = document.getElementById("profile-unfollow-btn");
    const followerLabel = document.getElementById("follower-label");

    nameEl.innerText = userData.name;
    usernameEl.innerText = "@" + userData.username;
    profileTextEl.innerText = userData.profileText ?? "";
    profileImageEl.src = userData.profileImagePath;
    profileImageLinkEl.href = userData.profileImagePath;
    followeeCountEl.innerText = userData.followeeCount;
    followerCountEl.innerText = userData.followerCount;

    nameInput.value = userData.name;
    usernameInput.value = userData.username;
    profileTextInput.value = userData.profileText ?? "";
    defaultRadio.checked = userData.profileImageType === "default";
    customRadio.checked = userData.profileImageType === "custom";
    profileImagePreviewEl.src = userData.profileImagePath;

    if (userData.isLoggedInUser) {
      editBtn.classList.remove("d-none");
    } else {
      if (userData.isFollowee) {
        unfollowBtn.classList.remove("d-none");
      } else {
        followBtn.classList.remove("d-none");
      }

      if (userData.isFollower) {
        followerLabel.classList.remove("d-none");
      }
    }

    profileBlock.classList.remove("d-none");
  }


  /**
   * ラジオボタン変更時の処理
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
   * プロフィール編集モーダル内ボタンクリック時の処理
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
});
