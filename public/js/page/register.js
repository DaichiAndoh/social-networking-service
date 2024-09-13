document.addEventListener("DOMContentLoaded", async function () {
  const form = document.getElementById("register-form");
  const btn = document.getElementById("register-btn");
  const spinner = document.getElementById("btn-spinner");

  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    btn.classList.add("disabled");
    spinner.classList.remove("d-none");

    const formData = new FormData(form);
    const resData = await apiPost("/api/register", formData);

    if (resData === null) {
      btn.classList.remove("disabled");
      spinner.classList.add("d-none");
      alert("エラーが発生しました。");
    }

    if (resData.success) {
      if (resData.redirectUrl) {
        window.location.href = resData.redirectUrl;
      }
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
