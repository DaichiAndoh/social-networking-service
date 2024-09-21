document.addEventListener("DOMContentLoaded", async function () {
  /**
   * コンテンツブロックの幅設定処理
   */
  function adjustContentWidth() {
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    
    const parentWidth = sidebar.parentElement.clientWidth;
    const sidebarWidth = sidebar.clientWidth;

    content.style.maxWidth = (parentWidth - sidebarWidth) + "px";
  }

  window.onload = adjustContentWidth;
  window.onresize = adjustContentWidth;


  /**
   * ログアウト処理
   */
  const logoutLink = document.getElementById("logout-link");
  logoutLink.addEventListener("click", async function(event) {
    event.preventDefault();

    const formData = new FormData();
    const resData = await apiPost("/api/logout", formData);

    if (resData === null) {
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      if (resData.redirectUrl) {
        window.location.href = resData.redirectUrl;
      }
    } else {
      if (resData.error) {
        alert(resData.error);
      }
    }
  });


  /**
   * ポスト作成モーダル内タブ切り替え時の処理
   */
  document.querySelectorAll("#ceatePostModal .nav-link").forEach(link => {
    link.addEventListener("click", function(event) {
      event.preventDefault();

      // 全てのnav-linkからactiveクラスを削除
      document.querySelectorAll("#ceatePostModal .nav-link").forEach(item => {
        item.classList.remove("active");
      });

      // クリックされたnav-linkにactiveクラスを追加
      this.classList.add("active");

      // 全てのセクションを非表示
      document.querySelectorAll("#ceatePostModal div[id$='-block']").forEach(section => {
        section.classList.add("d-none");
      });

      // クリックされたリンクに対応するセクションを表示
      const target = document.querySelector(this.getAttribute("data-target"));
      if (target) {
        target.classList.remove("d-none");
      }
    });
  });


  /**
   * ポスト作成モーダル内予約投稿設定変更時の処理
   */
  const scheduleSwicher = document.getElementById("schedule");
  const datetimepicker = document.getElementById("datetimepicker");
  const draftBtn = document.getElementById("draft-btn");
  const createBtn = document.getElementById("create-btn");
  const scheduleBtn = document.getElementById("schedule-btn");

  function toggleUploadBlock() {
    if (scheduleSwicher.checked) {
      datetimepicker.classList.remove("d-none");
      draftBtn.classList.add("d-none");
      createBtn.classList.add("d-none");
      scheduleBtn.classList.remove("d-none");
    } else {
      datetimepicker.classList.add("d-none");
      draftBtn.classList.remove("d-none");
      createBtn.classList.remove("d-none");
      scheduleBtn.classList.add("d-none");
    }
  }

  toggleUploadBlock();
  scheduleSwicher.addEventListener("change", toggleUploadBlock);


  /**
   * ポスト作成モーダル内datetimepicker
   */
  new tempusDominus.TempusDominus(document.getElementById('datetimepicker'), {
    localization: {
      format: "yyyy/MM/dd HH:mm",
    },
  });


  /**
   * ポスト作成モーダル内作成ボタンクリック時の処理
   */
  const form = document.getElementById("create-post-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const submitter = event.submitter.id;
    let type = "create";
    if (submitter === "draft-btn") type = "draft";
    else if (submitter === "schedule-btn") type = "schedule";

    const formData = new FormData(form);
    formData.append("type", type);
    const resData = await apiPost("/api/post/create", formData);

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
});
