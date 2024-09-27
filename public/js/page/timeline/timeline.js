document.addEventListener("DOMContentLoaded", async function () {
  /**
   * タイムライン初期化処理
   */
  const limit = 30;
  let offsetOfTrendTl = 0;
  let loadAllPostsOfTrendTl = false;
  let offsetOfFollowTl = 0;
  let loadAllPostsOfFollowTl = false;

  const trendTl = document.getElementById("trend-timeline");
  const followTl = document.getElementById("follow-timeline");
  const spinner = document.getElementById("spinner");

  async function loadTl(tlType = "trend") {
    const formData = new FormData();
    formData.append("limit", limit);
    formData.append("offset", tlType === "trend" ? offsetOfTrendTl : offsetOfFollowTl);
    const resData = await apiPost(`/api/timeline/${tlType === "trend" ? "trend" : "follow"}`, formData);

    if (resData.success) {
      if (resData.posts.length) {
        for (const post of resData.posts) {
          createPostEl(post, tlType === "trend" ? trendTl : followTl);
        }
        if (tlType === "trend") offsetOfTrendTl += limit;
        else offsetOfFollowTl += limit;
      } else {
        if (tlType === "trend") loadAllPostsOfTrendTl = true;
        else loadAllPostsOfFollowTl = true;
      }
      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await loadTl();
  await loadTl("follow");


  /**
   * タイムラインタブ切り替え時の処理
   */
  let activeTab = "trend";
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

      // タイムラインスクロールをトップに戻す
      const timelineWrapper = document.getElementById("timeline-wrapper");
      timelineWrapper.scrollTop = 0;
      activeTab = this.id === "trend-nav-link" ? "trend" : "follow";
    });
  });
  

  /**
   * timeline-wrapperの max-height 設定処理
   * 初期表示時とウィンドウリサイズ時に max-height を設定
   */
  function setMaxHeight() {
    const timelineWrapper = document.getElementById("timeline-wrapper");
    const timelineWrapperTop = timelineWrapper.getBoundingClientRect().top; // 要素のトップ位置
    const windowHeight = window.innerHeight; // ウィンドウの高さ

    // max-height を設定
    timelineWrapper.style.maxHeight = `${windowHeight - timelineWrapperTop}px`;
  }
  setMaxHeight();
  window.onresize = setMaxHeight;


  /**
   * timeline-wrapperのスクロール時の処理
   */
  document.getElementById("timeline-wrapper").addEventListener("scroll", async function() {
    const content = this;
    const loadAllPosts = activeTab === "trend" ? loadAllPostsOfTrendTl : loadAllPostsOfFollowTl;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!loadAllPosts) {
        spinner.classList.remove("d-none");
        if (activeTab === "trend") await loadTl();
        else await loadTl("follow");
      }
    }
  });
});
