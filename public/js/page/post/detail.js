document.addEventListener("DOMContentLoaded", async function () {
  /**
   * ポスト, リプライ初期化処理
   */
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const postId = urlParams.get("id");

  const replyLimit = 10;
  let replyOffset = 0;
  let loadAllReplies = false;

  const postBlock = document.getElementById("post-block");
  const repliesBlock = document.getElementById("replies-block");
  const parentPostBlock = document.getElementById("parent-post-block");
  const parentPost = document.getElementById("parent-post");
  const spinner = document.getElementById("spinner");

  async function loadPost() {
    const formData = new FormData();
    formData.append("postId", postId ?? "");
    const resData = await apiPost("/api/post/detail", formData);
  
    if (resData.success) {
      if (resData.post === null) {
        const postNotFound = document.getElementById("post-not-found");
        postNotFound.classList.remove("d-none");
        return;
      } else {
        createPostEl(resData.post, postBlock);

        if (resData.parentPost) {
          createPostEl(resData.parentPost, parentPost);
          parentPostBlock.classList.remove("d-none");
        }
        postBlock.classList.remove("d-none");
      }
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }

  async function loadReplies() {
    let showReplies = true;

    const formData = new FormData();
    formData.append("postId", postId ?? "");
    formData.append("replyLimit", replyLimit);
    formData.append("replyOffset", replyOffset);
    const resData = await apiPost("/api/post/replies", formData);
  
    if (resData.success) {
      if (resData.replies && resData.replies.length) {
        const replies = document.getElementById("replies");
        for (const reply of resData.replies) {
          createPostEl(reply, replies);
        }
        replyOffset += replyLimit;
      } else {
        loadAllReplies = true;
        if (replyOffset === 0) {
          showReplies = false;
        }
      }

      if (showReplies) {
        repliesBlock.classList.add("d-flex");
        repliesBlock.classList.remove("d-none");
      }

      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }

  await loadPost();
  await loadReplies();


  /**
   * replies-wrapperのスクロール時の処理
   */
  document.getElementById("replies-wrapper").addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!loadAllReplies) {
        spinner.classList.remove("d-none");
        await loadReplies();
      }
    }
  });


  /**
   * toggle-parent-post-linkのクリック時の処理
   * リンクのテキストとアイコンを変更する
   */
  const toggleLink = document.getElementById("toggle-parent-post-link");
  const linkText = document.getElementById("link-text");
  const linkIcon = document.getElementById("link-icon");
  const targetBlock = document.getElementById("collapse-block");

  toggleLink.addEventListener("click", function() {
    setTimeout(function() {
      if (targetBlock.classList.contains("show")) {
        linkText.textContent = "返信元ポストを隠す";
        linkIcon.name = "chevron-up-outline";
      } else {
        linkText.textContent = "返信元ポストを見る";
        linkIcon.name = "chevron-down-outline";
      }
    }, 400); // Bootstrap Collapseのデフォルトアニメーション分の待機
  });
});
