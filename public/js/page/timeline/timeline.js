document.addEventListener("DOMContentLoaded", async function () {
  /**
   * タイムライン初期化処理
   */
  const spinner = document.getElementById("spinner");
  const limit = 30;
  const tlData = {
    trend: {
      offset: 0,
      loadAll: false,
      tlEl: document.getElementById("trend-timeline"),
    },
    follow: {
      offset: 0,
      loadAll: false,
      tlEl: document.getElementById("follow-timeline"),
    },
  }

  async function loadTl(tlType = "trend") {
    const formData = new FormData();
    formData.append("limit", limit);
    formData.append("offset", tlData[tlType].offset ?? 0);
    const resData = await apiPost(`/api/timeline/${tlType === "trend" ? "trend" : "follow"}`, formData);

    if (resData.success) {
      if (resData.posts.length) {
        for (const post of resData.posts) {
          createPostEl(post, tlData[tlType].tlEl);
        }
        tlData[tlType].offset += limit;
      } else {
        tlData[tlType].loadAll = true;
      }
      spinner.classList.add("d-none");
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  }
  await loadTl();


  /**
   * タイムラインタブ切り替え時の処理
   */
  let activeTab = "trend";
  document.querySelectorAll("#timeline-tabs .nav-link").forEach(link => {
    link.addEventListener("click", async function(event) {
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
      if (tlData[activeTab].offset === 0) {
        await loadTl(activeTab);
      }
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
  window.addEventListener("resize", setMaxHeight);


  /**
   * timeline-wrapperのスクロール時の処理
   */
  document.getElementById("timeline-wrapper").addEventListener("scroll", async function() {
    const content = this;

    // 要素がスクロールの最下部に達したかを確認
    if (content.scrollTop + content.clientHeight >= content.scrollHeight) {
      if (!tlData[activeTab].loadAll) {
        spinner.classList.remove("d-none");
        await loadTl(activeTab);
      }
    }
  });
});
