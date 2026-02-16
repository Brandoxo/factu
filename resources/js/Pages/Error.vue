<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import LayoutMain from '@/Layouts/LayoutMain.vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: '503: Servicio No Disponible',
        500: '500: Error del Servidor',
        404: '404: Página No Encontrada',
        403: '403: Prohibido',
    }[props.status] || 'Error';
});

const description = computed(() => {
    return {
        503: 'Lo sentimos, estamos realizando mantenimiento. Por favor, inténtalo de nuevo más tarde.',
        500: 'Ups, algo salió mal en nuestros servidores.',
        404: 'Lo sentimos, la página que buscas no existe.',
        403: 'Lo sentimos, no tienes permiso para acceder a esta página.',
    }[props.status] || 'Ha ocurrido un error.';
});
</script>

<template>
    <Head :title="title" />
    <LayoutMain>
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-16">
            <div class="max-w-2xl w-full text-center">
                <!-- Logo -->
                <div class="mb-8 flex justify-center">
                    <img class="h-20 md:h-24" src="/public/assets/img/logos/logo.svg" alt="Logo Hotel Ronda Minerva">
                </div>

                <!-- Error Code -->
                <div class="mb-6">
                    <h1 class="text-8xl md:text-9xl font-bold text-gray-300 mb-2">
                        {{ status }}
                    </h1>
                    <div class="h-1 w-32 bg-gradient-to-r from-salmon to-orange-400 mx-auto rounded-full"></div>
                </div>

                <!-- Error Title -->
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    {{ title }}
                </h2>

                <!-- Error Description -->
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-md mx-auto">
                    {{ description }}
                </p>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link 
                        href="/" 
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-salmon to-orange-500 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition-all duration-300 hover:scale-105"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Volver al Inicio
                    </Link>
                    
                    <button 
                        @click="$inertia.visit($page.url, { preserveState: true, preserveScroll: true, only: [] })"
                        class="inline-flex items-center gap-2 bg-black text-gray-700 font-semibold px-8 py-3 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:shadow-md transition-all duration-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Intentar de Nuevo
                    </button>
                </div>

                <!-- Additional Help -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <p class="text-gray-500 text-sm mb-3">
                        ¿Necesitas ayuda? Contáctanos
                    </p>
                    <div class="flex gap-4 justify-center">
                        <a href="https://api.whatsapp.com/send/?phone=%2B523312490519&text=Hola,%20quiero%20hacer%20una%20nueva%20reserva." target="_blank" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:scale-110">
                            <img src="/public/assets/icons/WhatsApp Inc.svg" alt="WhatsApp" class="w-6 h-6">
                        </a>
                        <a href="https://www.facebook.com/HotelRondaMinerva" target="_blank" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:scale-110">
                            <img src="/public/assets/icons/Facebook.svg" alt="Facebook" class="w-6 h-6">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </LayoutMain>
</template>

<style scoped>
.bg-salmon {
    background-color: #ff6b6b;
}
</style>
