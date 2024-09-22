document.addEventListener("DOMContentLoaded", async function () {
  let viewTrendTimeline = true;
  const limit = 30;
  let offsetOfTrend = 0;
  let offsetOfFollowee = 0;
  let loadAllTrendPosts = false;
  let loadAllFolloweePosts = false;

  const trendTimeline = document.getElementById("trend-timeline");
  const followeeTimeline = document.getElementById("followee-timeline");
  const spinner = document.getElementById("spinner");

  async function loadTrendPosts() {
    const formData = new FormData();
    formData.append("limit", limit);
    formData.append("offset", offsetOfTrend);
    const resData = await apiPost("/api/timeline/trend", formData);

    if (resData.success) {
      if (resData.posts.length) {
        createPostEls(resData.posts, trendTimeline);
        offsetOfTrend += limit;
      } else {
        loadAllTrendPosts = true;
      }
      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }

  async function loadFolloweePosts() {
    const formData = new FormData();
    formData.append("limit", limit);
    formData.append("offset", offsetOfFollowee);
    const resData = await apiPost("/api/timeline/followee", formData);

    if (resData.success) {
      if (resData.posts.length) {
        createPostEls(resData.posts, followeeTimeline);
        offsetOfFollowee += limit;
      } else {
        loadAllFolloweePosts = true;
      }
      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }

  function createPostEls(posts, parent) {
    for (const post of posts) {
      createPostEl(post, parent);
    }
  }

  function createPostEl(post, parent) {
    const cardDiv = document.createElement("div");
    cardDiv.classList.add("card", "p-3", "mb-1");
    cardDiv.style.cursor = "pointer";

    const flexDiv = document.createElement("div");
    flexDiv.classList.add("d-flex");

    /** 左ブロック */
    // プロフィール画像
    const imageDiv = document.createElement("div");

    const profileLink = document.createElement("a");
    profileLink.href = post.profilePath;

    const profileImg = document.createElement("img");
    profileImg.src = post.profileImagePath;
    profileImg.alt = "プロフィール画像";
    profileImg.width = 40;
    profileImg.height = 40;
    profileImg.classList.add("rounded-circle");

    profileLink.appendChild(profileImg);
    imageDiv.appendChild(profileLink);

    /** 右ブロック */
    const contentDiv = document.createElement("div");
    contentDiv.classList.add("w-100", "ms-2");

    // 名前, ユーザー名, 3点ドットアイコン
    const profileDiv = document.createElement("div");
    profileDiv.classList.add("d-flex", "justify-content-between");

    const userLink = document.createElement("a");
    userLink.href = post.profilePath;
    userLink.classList.add("text-black", "hover-underline");

    const nameSpan = document.createElement("span");
    nameSpan.classList.add("fw-semibold", "fs-6");
    nameSpan.innerText = post.name + " ";

    const usernameSpan = document.createElement("span");
    usernameSpan.classList.add("fw-light", "text-secondary");
    usernameSpan.innerText = `@${post.username}・1h`;

    userLink.appendChild(nameSpan);
    userLink.appendChild(usernameSpan);

    const dropdownDiv = document.createElement("div");
    dropdownDiv.classList.add("dropdown");

    const ionIcon = document.createElement("ion-icon");
    ionIcon.setAttribute("name", "ellipsis-horizontal-outline");
    ionIcon.classList.add("dropdown-toggle", "rounded", "hover-action");
    ionIcon.setAttribute("data-bs-toggle", "dropdown");
    ionIcon.setAttribute("aria-expanded", "false");
    ionIcon.style.cursor = "pointer";

    const dropdownMenu = document.createElement("ul");
    dropdownMenu.classList.add("dropdown-menu");

    const deleteItem = document.createElement("li");
    const deleteLink = document.createElement("a");
    deleteLink.classList.add("dropdown-item");
    deleteLink.href = "#";
    deleteLink.innerText = "Delete";

    deleteItem.appendChild(deleteLink);
    dropdownMenu.appendChild(deleteItem);

    dropdownDiv.appendChild(ionIcon);
    dropdownDiv.appendChild(dropdownMenu);

    profileDiv.appendChild(userLink);
    profileDiv.appendChild(dropdownDiv);

    // 本文
    const textBody = document.createElement("div");
    textBody.innerText = post.content;

    // 返信アイコン・返信数, いいねアイコン・いいね数
    const iconsDiv = document.createElement("div");
    iconsDiv.classList.add("mt-2", "d-flex", "align-items-center");

    const replyDiv = document.createElement("div");
    replyDiv.classList.add("text-dark", "text-decoration-none", "d-flex", "align-items-center", "rounded", "hover-action");
    replyDiv.style.cursor = "pointer";

    const replyIcon = document.createElement("ion-icon");
    replyIcon.setAttribute("name", "chatbubbles-outline");

    const replyCount = document.createElement("span");
    replyCount.classList.add("ms-1");
    replyCount.innerText = "10";

    replyDiv.appendChild(replyIcon);
    replyDiv.appendChild(replyCount);

    const heartDiv = document.createElement("div");
    heartDiv.classList.add("ms-3", "text-dark", "text-decoration-none", "d-flex", "align-items-center", "rounded", "hover-action");
    heartDiv.style.cursor = "pointer";

    const heartIcon = document.createElement("ion-icon");
    heartIcon.setAttribute("name", "heart-outline");

    const heartCount = document.createElement("span");
    heartCount.classList.add("ms-1");
    heartCount.innerText = "10";

    heartDiv.appendChild(heartIcon);
    heartDiv.appendChild(heartCount);

    iconsDiv.appendChild(replyDiv);
    iconsDiv.appendChild(heartDiv);

    contentDiv.appendChild(profileDiv);
    contentDiv.appendChild(textBody);
    contentDiv.appendChild(iconsDiv);

    flexDiv.appendChild(imageDiv);
    flexDiv.appendChild(contentDiv);

    cardDiv.appendChild(flexDiv);

    parent.appendChild(cardDiv);
  }


  /**
   * プロフィール初期化処理
   */
  await loadTrendPosts();
  await loadFolloweePosts();


  /**
   * スクロール時の処理
   */
  document.getElementById("timeline-wrapper").addEventListener("scroll", async function() {
    const content = this;
    const loadAllPosts = viewTrendTimeline ? loadAllTrendPosts : loadAllFolloweePosts;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!loadAllPosts) {
        spinner.classList.remove("d-none");
        if (viewTrendTimeline) {
          await loadTrendPosts();
        } else {
          await loadFolloweePosts();
        }
      }
    }
  });


  /**
   * タイムラインタブ切り替え時の処理
   */
  document.querySelectorAll("#timeline-tabs .nav-link").forEach(link => {
    link.addEventListener("click", function(event) {
      event.preventDefault();

      // 全てのnav-linkからactiveクラスを削除
      document.querySelectorAll("#timeline-tabs .nav-link").forEach(item => {
        item.classList.remove("active");
      });

      // クリックされたnav-linkにactiveクラスを追加
      this.classList.add("active");

      // 全てのセクションを非表示
      document.querySelectorAll("div[id$='-timeline']").forEach(section => {
        section.classList.add("d-none");
      });

      // クリックされたリンクに対応するセクションを表示
      const target = document.querySelector(this.getAttribute("data-target"));
      if (target) {
        target.classList.remove("d-none");
      }

      viewTrendTimeline = !viewTrendTimeline;
    });
  });
});
