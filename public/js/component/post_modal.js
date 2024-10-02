document.addEventListener("DOMContentLoaded", async function () {
  /**
   * ポスト作成モーダル
   * datetimepicker
   */
  new tempusDominus.TempusDominus(document.getElementById("post-datetimepicker"), {
    localization: {
      format: "yyyy/MM/dd HH:mm",
    },
  });


  /**
   * ポスト作成モーダル
   * タブ切り替え時の処理
   */
  document.querySelectorAll("#createPostModal .nav-link").forEach(link => {
    link.addEventListener("click", function(event) {
      event.preventDefault();

      // 全てのnav-linkからactiveクラスを削除
      document.querySelectorAll("#createPostModal .nav-link").forEach(item => {
        item.classList.remove("active");
      });

      // クリックされたnav-linkにactiveクラスを追加
      this.classList.add("active");

      // 全てのセクションを非表示
      document.querySelectorAll("#createPostModal div[id$='-block']").forEach(section => {
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
   * ポスト作成モーダル
   * 予約投稿チェックボックス変更時の処理
   */
  const scheduleSwicher = document.getElementById("post-schedule");
  const datetimepicker = document.getElementById("post-datetimepicker");
  const draftBtn = document.getElementById("post-draft-btn");
  const createBtn = document.getElementById("post-create-btn");
  const scheduleBtn = document.getElementById("post-schedule-btn");

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
   * ポスト作成モーダル
   * 作成ボタンクリック時の処理
   */
  const form = document.getElementById("create-post-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const submitter = event.submitter.id;
    let type = "create";
    if (submitter === "post-draft-btn") type = "draft";
    else if (submitter === "post-schedule-btn") type = "schedule";

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
