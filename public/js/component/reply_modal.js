document.addEventListener("DOMContentLoaded", async function () {
  /**
   * 返信ポスト作成モーダル
   * datetimepicker
   */
  new tempusDominus.TempusDominus(document.getElementById("reply-datetimepicker"), {
    localization: {
      format: "yyyy/MM/dd HH:mm",
    },
  });


  /**
   * 返信ポスト作成モーダル
   * タブ切り替え時の処理
   */
  document.querySelectorAll("#createReplyModal .nav-link").forEach(link => {
    link.addEventListener("click", function(event) {
      event.preventDefault();

      // 全てのnav-linkからactiveクラスを削除
      document.querySelectorAll("#createReplyModal .nav-link").forEach(item => {
        item.classList.remove("active");
      });

      // クリックされたnav-linkにactiveクラスを追加
      this.classList.add("active");

      // 全てのセクションを非表示
      document.querySelectorAll("#createReplyModal div[id$='-block']").forEach(section => {
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
   * 返信ポスト作成モーダル
   * 予約投稿チェックボックス変更時の処理
   */
  const scheduleSwicher = document.getElementById("reply-schedule");
  const datetimepicker = document.getElementById("reply-datetimepicker");
  const draftBtn = document.getElementById("reply-draft-btn");
  const createBtn = document.getElementById("reply-create-btn");
  const scheduleBtn = document.getElementById("reply-schedule-btn");

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
   * 返信ポスト作成モーダル
   * 作成ボタンクリック時の処理
   */
  const form = document.getElementById("create-reply-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const submitter = event.submitter.id;
    let type = "create";
    if (submitter === "reply-draft-btn") type = "draft";
    else if (submitter === "reply-schedule-btn") type = "schedule";

    const formData = new FormData(form);
    formData.append("type", type);
    const resData = await apiPost("/api/post/create", formData);

    if (resData === null) {
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      if (resData.redirectUrl) {
        window.location.href = resData.redirectUrl;
      }
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
