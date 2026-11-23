"use strict";
document.querySelectorAll("[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
document.querySelectorAll(".show-password").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.getAttribute("aria-controls"));
        const show = input.type === "password";
        input.type = show ? "text" : "password";
        button.textContent = show ? "Hide" : "Show";
        button.setAttribute("aria-pressed", String(show));
    });
});
const toggle = document.querySelector(".mobile-toggle");
if (toggle)
    toggle.addEventListener("click", () => {
        const open = toggle.getAttribute("aria-expanded") !== "true";
        toggle.setAttribute("aria-expanded", String(open));
        document.body.classList.toggle("nav-open", open);
    });
const selection = document.getElementById("subject-selection");
if (selection) {
    selection.addEventListener("change", () => {
        const units = [...selection.querySelectorAll(".subject-check:checked")].reduce(
            (total, input) => total + (Number(input.dataset.units) || 0),
            0,
        );
        document.getElementById("unit-count").textContent = String(units);
    });
    selection.addEventListener("submit", (event) => {
        if (!selection.querySelector(".subject-check:checked")) {
            event.preventDefault();
            window.alert("Select at least one subject.");
        }
    });
}
document
    .querySelectorAll("[data-print]")
    .forEach((button) => button.addEventListener("click", () => window.print()));
