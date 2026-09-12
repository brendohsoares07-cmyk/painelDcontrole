"use strict";

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener("click", function (event) {
            var alvo = link.getAttribute("href");
            if (!alvo || alvo === "#") return;
            var elemento = document.querySelector(alvo);
            if (elemento) {
                event.preventDefault();
                elemento.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });
});
