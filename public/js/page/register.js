document.addEventListener("DOMContentLoaded", async function () {
  const form = document.getElementById("register-form");
  form.addEventListener("submit", async function(event) {
    event.preventDefault();
    resetFormValidations();

    const formData = new FormData(form);
    const resData = await apiPost("/api/register", formData);

    console.log(resData);

    if (resData.success) {
      console.log("success");
    } else {
      if (resData.fieldErrors) {
        for (const field in resData.errors) {
          setFormValidation(field, resData.errors[field]);
        }
      }
      if (resData.error) {
        alert(resData.error);
      }
    }
  });
});
