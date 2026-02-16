<script setup>
import { onMounted } from 'vue';

const closeNav = () => {
    const aside = document.getElementById('aside');
    const lateralMenu = document.getElementById('lateral-menu');
    const backGround = document.getElementById('background');
    
    if (aside && lateralMenu && backGround) {
        lateralMenu.classList.remove('w-72');
        lateralMenu.classList.add('w-0');
        backGround.classList.add('opacity-0', 'pointer-events-none');
        setTimeout(() => {
            aside.classList.remove('overflow-visible');
            aside.classList.add('overflow-hidden');
        }, 300);
    }
};

onMounted(() => {
    const closeBtn = document.getElementById('closeNavBtn');
    const aside = document.getElementById('aside');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeNav);
    }
    
    if (aside) {
        aside.addEventListener('click', (e) => {
            if (e.target.id === 'background' || e.target.id === 'aside') {
                closeNav();
            }
        });
    }
});
</script>

<template>
<div class="bg-black/90 h-screen w-screen fixed top-0 left-0 z-40 transition-opacity duration-300 opacity-0 pointer-events-none" id="background">
<aside id="aside" class="overflow-hidden fixed top-0 h-screen bg-black/50 bg-opacity-50 z-50 transition-all duration-300 md:hidden">
    <section id="lateral-menu" class="w-0 bg-white h-screen opacity-100 shadow-inner transition-all duration-300">
        <ul>
            
            <li class="relative border-black border-opacity-30 border-b-2">
                <div class="bg-black/80 w-full h-28 inner-shadow overflow-y-hidden flex items-center justify-center">
                    <img class="w-32 mx-auto" src='/public/assets/img/logos/logo-white.png' alt="Logo">
                    <br>
                    <br>
                </div>
                <a href="javascript:void(0)" class="absolute top-0 right-0   focus:bg-red-500 text-white float-right w-14 h-14 leading-tight text-[2.5rem] text-center transition-all duration-150 rounded-xl rounded-shadow" id="closeNavBtn">&times;</a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/"><p class="p-4">Inicio</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/rooms"><p class="p-4">Habitaciones</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/installations"><p class="p-4">Instalaciones</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/visita-guadalajara"><p class="p-4">Visita Guadalajara</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/services"><p class="p-4">Servicios</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-gray/10">
                <a class="text-lg font-light" href="https://rondaminervahotel.com/contact"><p class="p-4">Contacto</p></a>
            </li>

        </ul>
    </section>
</aside>
</div>
<span class="w-0 hidden"></span>
<span class="w-72 hidden"></span>
</template>