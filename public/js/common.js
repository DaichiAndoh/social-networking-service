async function apiPost(url, body) {
  try {
    const res = await fetch(url, { method: "POST", body: body });
    const resData = await res.json();
    return resData;
  } catch (error) {
    console.error("Error:", error);
    return null;
  }
}

async function resetFormValidations() {
  const invalidInputs = document.querySelectorAll("input.is-invalid");
  invalidInputs.forEach(function(input) {
    input.classList.remove("is-invalid");
  });
}

async function setFormValidation(fieldId, message) {
  const field = document.getElementById(fieldId);
  field.classList.add("is-invalid");
  const errorMsg = document.getElementById(`${fieldId}-error-msg`);
  errorMsg.innerText = message;
}
