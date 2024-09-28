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
   * replies-wrapperの max-height 設定処理
   * 初期表示時とウィンドウリサイズ時に max-height を設定
   */
  const repliesWrapper = document.getElementById("replies-wrapper");
  function setRepliesWrapperMaxHeight() {
    const repliesWrapperTop = repliesWrapper.getBoundingClientRect().top; // 要素のトップ位置
    const windowHeight = window.innerHeight; // ウィンドウの高さ

    // max-height を設定
    repliesWrapper.style.maxHeight = `${windowHeight - repliesWrapperTop}px`;
  }
  setRepliesWrapperMaxHeight();
  window.addEventListener("resize", setRepliesWrapperMaxHeight);


  /**
   * replies-wrapperのスクロール時の処理
   */
  repliesWrapper.addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!loadAllReplies) {
        spinner.classList.remove("d-none");
        await loadReplies();
      }
    }
  });
});
