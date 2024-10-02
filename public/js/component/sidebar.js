document.addEventListener("DOMContentLoaded", async function () {
  /**
   * メインコンテンツブロックの横幅設定処理
   */
  function adjustContentWidth() {
    const sidebar = document.getElementById("sidebar");
    const mailContent = document.getElementById("main-content");
    
    const parentWidth = sidebar.parentElement.clientWidth;
    const sidebarWidth = sidebar.clientWidth;

    mailContent.style.maxWidth = (parentWidth - sidebarWidth) + "px";
  }

  adjustContentWidth();
  window.addEventListener("resize", adjustContentWidth);


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
