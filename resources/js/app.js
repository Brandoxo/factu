import "./bootstrap";
import AOS from "aos";
import "aos/dist/aos.css";

// Inicializar AOS directamente (SIN función initAOS)
document.addEventListener("DOMContentLoaded", function () {
    AOS.init({
        duration: 800,
        easing: "ease-in-out",
        once: true,
        offset: 100,
    });

    console.log("AOS inicializado correctamente");
});
