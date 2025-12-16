<template>
<aside id="aside" class="overflow-hidden fixed top-0 h-screen bg-black/50 bg-opacity-50 z-50 transition-all duration-300">
    <section id="lateral-menu" class="w-0 bg-white h-screen opacity-100 shadow-inner transition-all duration-300">
        <ul>
            
            <li class="relative border-black border-opacity-30 border-b-2">
                <div class="bg-salmon w-full h-28 inner-shadow overflow-y-hidden">
                    <img class="w-19 mx-auto" src='/public/assets/img/logos/logo.svg' alt="Logo">
                    <br>
                    <br>
                </div>
                <a href="javascript:void(0)" class="absolute top-0 right-0   focus:bg-red-500 text-white float-right w-14 h-14 leading-tight text-[2.5rem] text-center transition-all duration-150 rounded-xl rounded-shadow" id="closeNavBtn">&times;</a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-hielow hover:text-white">
                <a class="text-lg font-light" href="#"><p class="p-4">Inicio</p></a>
            </li>


            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-aqua hover:text-white">
                <button class="text-lg font-light w-full text-left p-4 toggle-submenu" data-target="submenu-hoteles">Hoteles</button>
                <ul id="submenu-hoteles" class="hidden ml-4 bg-hielow text-white font-bold">
                    <li><a href="https://provi.fch-hotels.com/" class="block p-4 hover:bg-gray-200">FCH Providencia</a></li>
                    <li><a href="http://fch-expo.fchminerva.com/" class="block p-4 hover:bg-gray-200">FCH Expo</a></li>
                    <li><a href="http://fchminerva.com" class="block p-4 hover:bg-gray-200">FCH Minerva</a></li>
                </ul>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-salmon hover:text-white">
                <a class="text-lg font-light" href="/desing-and-art"><p class="p-4">Arte y diseño</p></a>
            </li>


            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-blue hover:text-white" id="liGourmet">
                <button class="text-lg font-light w-full text-left p-4 toggle-submenu" data-target="submenu-gourmet">Gourmet</button>
                <ul id="submenu-gourmet" class="hidden ml-4 bg-salmon text-white font-bold">
                    <li><a href="/restaurant-ananas" class="block p-4 hover:bg-pink-600 ">Restaurante Ananás</a></li>
                    <li><a href="/restaurant-granada" class="block p-4 hover:bg-pink-600 ">Restaurante Granada</a></li>
                </ul>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-hielow hover:text-white">
                <a class="text-lg font-light" href="/contact"><p class="p-4">Contacto</p></a>
            </li>

            <li class="relative hover:bg-main-light hover:text-dark text-dark-medium border-black border-opacity-30 border-b cursor-pointer transition hover:bg-salmon hover:text-white">
                <a class="text-lg font-light" href="#"><p class="p-4">EN</p></a>
            </li>
        </ul>
    </section>
</aside>
<span class="w-0 hidden"></span>
<span class="w-72 hidden"></span>
</template>