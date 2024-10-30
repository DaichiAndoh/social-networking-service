document.addEventListener("DOMContentLoaded", async function () {
  /**
   * フォロイー初期化処理
   */
  const listEl = document.getElementById("followees-list");
  const spinner = document.getElementById("spinner");
  const limit = 30;
  let offset = 0;
  let loadAll = false;

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const username = urlParams.get("un");

  async function loadFollowee() {
    const formData = new FormData();
    formData.append("username", username ?? "");
    formData.append("limit", limit);
    formData.append("offset", offset);
    const resData = await apiPost("/api/user/followees", formData);

    if (resData.success) {
      if (resData.followees === null) {
        const userNotFound = document.getElementById("user-not-found");
        userNotFound.classList.remove("d-none");
        return;
      }

      if (resData.followees.length) {
        for (const followee of resData.followees) {
          createFolloweeEl(followee, listEl);
        }
        offset += limit;
      } else {
        loadAll = true;

        if (offset === 0) {
          const notExistsLabel = document.getElementById("followees-not-exists");
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
  await loadFollowee();

  function createFolloweeEl(followee, parent) {
    // 親要素のdiv
    const container = document.createElement("div");
    container.classList.add("d-flex", "align-items-center", "p-1");
    container.addEventListener("mouseover", function() {
      container.style.cursor = "pointer";
      container.style.backgroundColor = "rgba(248, 249, 250, 1)";
    });
    container.addEventListener("mouseout", function() {
      container.style.backgroundColor = "";
    });
    container.addEventListener("click", function() {
      window.location.href = followee.profilePath;
    });

    // プロフィール画像のimg
    const img = document.createElement("img");
    img.src = followee.profileImagePath;
    img.alt = "プロフィール画像";
    img.width = 50;
    img.height = 50;
    img.classList.add("rounded-circle", "border", "flex-shrink-0");

    // 名前とユーザー名を含むdiv
    const textContainer = document.createElement("div");
    textContainer.classList.add("ms-3");
    textContainer.style.minWidth = "0";

    // 名前のdiv, h6
    const nameDiv = document.createElement("div");
    nameDiv.classList.add("d-flex", "align-items-center");

    const nameEl = document.createElement("h6");
    nameEl.classList.add("m-0");
    nameEl.textContent = followee.name;
    nameEl.style.overflow = "hidden";
    nameEl.style.textOverflow = "ellipsis";
    nameEl.style.whiteSpace = "nowrap";
    nameDiv.appendChild(nameEl);

    if (followee.userType === "INFLUENCER") {
      const influencerIcon = document.createElement("ion-icon");
      influencerIcon.setAttribute("name", "shield-checkmark");
      influencerIcon.classList.add("flex-shrink-0");
      influencerIcon.style.color = "#dbbf4b";
      nameDiv.appendChild(influencerIcon);
    }

    // ユーザー名のp
    const usernameEl = document.createElement("p");
    usernameEl.id = "profile-username";
    usernameEl.classList.add("m-0", "text-secondary", "fw-light");
    usernameEl.textContent = "@" + followee.username;
    usernameEl.style.overflow = "hidden";
    usernameEl.style.textOverflow = "ellipsis";
    usernameEl.style.whiteSpace = "nowrap";

    // h6とpをdivの子要素に追加
    textContainer.appendChild(nameDiv);
    textContainer.appendChild(usernameEl);

    // imgとdivを親要素のdivに追加
    container.appendChild(img);
    container.appendChild(textContainer);

    // 親要素のdivを引数で受け取った親要素に追加
    parent.appendChild(container);
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
        await loadFollowee();
      }
    }
  });
});
