<script setup>
import { onMounted } from 'vue';

const openNav = () => {
    const aside = document.getElementById('aside');
    const lateralMenu = document.getElementById('lateral-menu');
    const backGround = document.getElementById('background');
    
    if (aside && lateralMenu && backGround) {
        backGround.classList.remove('opacity-0', 'pointer-events-none');
        aside.classList.remove('overflow-hidden');
        aside.classList.add('overflow-visible');
        lateralMenu.classList.remove('w-0');
        lateralMenu.classList.add('w-72');
    }
};
</script>
<template>
<nav class="z-30 bg-transparent top-0 left-0 w-full transition-all duration-300 absolute" id="mainHeader">
    <div class="md:flex hidden justify-between items-center px-8 md:px-20 lg:px-40 2xl:px-60 py-2 md:py-1 mx-auto z-30">
            <a class="" href="https://rondaminervahotel.com/">
              <img class="h-14" src="/public/assets/img/logos/logo.svg" alt="Logo Holtel Ronda Minerva">
            </a>
            <ul class="text-white ml-auto md:flex gap-4 font-semibold 2xl:text-xl"><li><a href="/services"><p>Sistema de Facturación</p></a> <h1 class="active5 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-salmon group-hover:block"></h1></li>
            </ul>
    </div>

    <div class="flex justify-between p-2">

        <div class="md:hidden block open-nav-btn">
            <img @click="openNav" class="w-12" src="/public/assets/icons/menu.svg" alt="menu">
        </div>
        
        <div class="md:hidden align-center items-center">
            <a  href="/services">
                <img class="w-8" src="/public/assets/img/logos/logo.svg" alt="menu">
            </a>
        </div>
       
    </div>
</nav>
</template>