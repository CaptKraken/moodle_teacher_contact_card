window.addEventListener("load", () => {
  const allCards = document.querySelectorAll(".ti__card");
  const listHeads = document.querySelectorAll(".ti__head");
  const collapseIcons = document.querySelectorAll(".ti__collapse-icon");
  function hideAll() {
    allCards.forEach((card) => {
      card.classList.add("hide");
    });
    collapseIcons.forEach((icon) => {
      icon.textContent = "▼";
    });
  }
  listHeads.forEach((head) => {
    head.addEventListener("click", (e) => {
      const parentEl = e.target.closest(".ti__list--item");
      const cardEl = parentEl.querySelector(".ti__card");
      const collapseIcon = parentEl.querySelector(".ti__collapse-icon");

      if (!cardEl) return;
      if (!cardEl.classList.contains("hide")) hideAll();
      else {
        hideAll();
        cardEl.classList.remove("hide");
        collapseIcon.textContent = "▲";
      }
    });
  });
});
