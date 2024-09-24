document.addEventListener("DOMContentLoaded", async function () {
  /**
   * フォロイー初期化処理
   */
  const limit = 30;
  let offset = 0;

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const username = urlParams.get("un");

  const formData = new FormData();
  formData.append("username", username ?? "");
  formData.append("limit", limit);
  formData.append("offset", offset);
  const resData = await apiPost("/api/user/followers", formData);

  if (resData.success) {
    renderFollowers(resData.followers);
  } else {
    if (resData.error) {
      alert(resData.error);
    }
  }

  function renderFollowers(followers) {
    if (followers === null) {
      const userNotFound = document.getElementById("user-not-found");
      userNotFound.classList.remove("d-none");
      return;
    }

    if (followers.length === 0) {
      const notExistsLabel = document.getElementById("followers-not-exists");
      notExistsLabel.classList.remove("d-none");
      return;
    }

    const followersWrapper = document.getElementById("followers");
    for (const follower of followers) {
      createFollowerEl(followersWrapper, follower);
    }
  }

  function createFollowerEl(parent, follower) {
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
      window.location.href = follower.profilePath;
    });

    // プロフィール画像のimg
    const img = document.createElement("img");
    img.src = follower.profileImagePath;
    img.alt = "プロフィール画像";
    img.width = 50;
    img.height = 50;
    img.classList.add("rounded-circle", "border", "flex-shrink-0");

    // 名前とユーザー名を含むdiv
    const textContainer = document.createElement("div");
    textContainer.classList.add("ms-3");
    textContainer.style.minWidth = "0";

    // 名前のh6
    const nameEl = document.createElement("h6");
    nameEl.classList.add("m-0");
    nameEl.textContent = follower.name;
    nameEl.style.overflow = "hidden";
    nameEl.style.textOverflow = "ellipsis";
    nameEl.style.whiteSpace = "nowrap";

    // ユーザー名のp
    const usernameEl = document.createElement("p");
    usernameEl.id = "profile-username";
    usernameEl.classList.add("m-0", "text-secondary", "fw-light");
    usernameEl.textContent = "@" + follower.username;
    usernameEl.style.overflow = "hidden";
    usernameEl.style.textOverflow = "ellipsis";
    usernameEl.style.whiteSpace = "nowrap";

    // h6とpをdivの子要素に追加
    textContainer.appendChild(nameEl);
    textContainer.appendChild(usernameEl);

    // imgとdivを親要素のdivに追加
    container.appendChild(img);
    container.appendChild(textContainer);

    // 親要素のdivを引数で受け取った親要素に追加
    parent.appendChild(container);
  }
});
