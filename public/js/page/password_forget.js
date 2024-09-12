document.addEventListener("DOMContentLoaded", async function () {
  const form = document.getElementById("password-forget-form");
  const btn = document.getElementById("password-forget-btn");
  const spinner = document.getElementById("password-forget-btn-spinner");
  const msg = document.getElementById("password-forget-completion-msg");

  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    btn.classList.add("disabled");
    spinner.classList.remove("d-none");

    const formData = new FormData(form);
    const resData = await apiPost("/api/password_forget", formData);

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
