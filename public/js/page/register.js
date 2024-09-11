document.addEventListener("DOMContentLoaded", async function () {
  const form = document.getElementById("register-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const formData = new FormData(form);
    const resData = await apiPost("/api/register", formData);

    console.log(resData);

    if (resData.success) {
      if (resData.redirectPath) {
        window.location.href = resData.redirectPath;
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
