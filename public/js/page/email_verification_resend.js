document.addEventListener("DOMContentLoaded", async function () {
  const form = document.getElementById("email-verification-resend-form");
  const btn = document.getElementById("email-verification-resend-btn");
  const spinner = document.getElementById("btn-spinner");
  const msg = document.getElementById("completion-msg");

  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    btn.classList.add("disabled");
    spinner.classList.remove("d-none");

    const resData = await apiPost("/api/email/verification/resend");

    if (resData === null) {
      btn.classList.remove("disabled");
      spinner.classList.add("d-none");
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      form.classList.add("d-none");
      msg.classList.remove("d-none");
    } else {
      btn.classList.remove("disabled");
      spinner.classList.add("d-none");

      if (resData.error) {
        alert(resData.error);
      }
    }
  });
});
