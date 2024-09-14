window.setTimeout(function() {
  const alert = document.getElementById("alert");
  if (alert) {
    alert.classList.add("fade");
    alert.classList.remove("show");
  }
}, 1500);
