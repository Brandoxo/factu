import "./bootstrap";
import AOS from "aos";
import "aos/dist/aos.css";
import '../css/app.css';  
import { createInertiaApp } from "@inertiajs/vue3";
import { createApp } from "vue";
import { h } from "vue";
import Header from "./Components/Header.vue";
import Footer from "./Components/Footer.vue";

// Inicializar AOS directamente
document.addEventListener("DOMContentLoaded", function () {
    AOS.init({
        duration: 800,
        easing: "ease-in-out",
        once: true,
        offset: 100,
    });

    console.log("AOS inicializado correctamente");
});

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props }) {
    const app = createApp({ render: () => h(App, props) });
    app.component("Header", Header);
    app.component("Footer", Footer);
    app.mount(el);
  },
});