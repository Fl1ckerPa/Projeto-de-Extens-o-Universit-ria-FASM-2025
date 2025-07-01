document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");
    const sidebarContent = document.getElementById("sidebarContent");

    toggleButton.addEventListener("click", () => {
      const isVisible = sidebar.classList.contains("d-block");

      if (isVisible) {
        sidebar.classList.replace("d-block", "d-none");
        sidebarContent.classList.add("d-none");
      } else {
        sidebar.classList.replace("d-none", "d-block");
        sidebarContent.classList.remove("d-none");
      }
    });
  });