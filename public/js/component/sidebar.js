document.addEventListener("DOMContentLoaded", async function () {
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
});
