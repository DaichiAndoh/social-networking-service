document.addEventListener("DOMContentLoaded", async function () {
  const btn = document.getElementById("guest-login-btn");

  btn.addEventListener("click", async function(event) {
    event.preventDefault();

    if (confirm("ゲストログインボタン下の注意事項を確認してください。\nこのままゲストログインしますか？")) {
      const formData = new FormData();
      const resData = await apiPost("/api/login/guest", formData);
  
      if (resData.success) {
        if (resData.redirectUrl) {
          window.location.href = resData.redirectUrl;
        }
      } else {
        if (resData.error) {
          alert(resData.error);
        }
      }
    }
  });
});
