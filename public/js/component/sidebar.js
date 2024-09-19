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
});
