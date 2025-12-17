<script setup>
import { ref } from 'vue';
import ButtonSelection from './ButtonSelection.vue';
import { Transition } from 'vue';

const selectedService = ref(null);
console.log('data selectedService:', selectedService.value);
</script>
<template>
    <section id="hero-carousel" class="swiper w-full h-screen  z-10">
        <div class=" hero-bg bg-cover w-full h-screen relative">
                <div class="dark-overlay absolute -z-10 top-0 left-0 h-full w-full overflow-hidden" data-aos="fade-up">
                        <img class="w-full h-full bg-cover object-cover" src="/public/assets/img/bg/bg-main.webp" alt="Hero Background Image" >
                </div>
                
                <div class="text-white text-center flex flex-col justify-center gap-3 h-full logo absolute top-1/5 md:top-1/4 left-1/2 transform -translate-x-1/2 -translate-y-1/2" >
                    <Transition name="fade-out-up">
                        <div v-if="selectedService === null" class="mx-auto">
                            <img class="w-60" src="/public/assets/img/logos/logo-white.png" alt="Logo Hotel FCH Minerva">
                        </div>
                    </Transition>
                </div>
                    <ButtonSelection v-model="selectedService" />
        </div>
        </section>
</template>

<style>
    .logo{
        animation: fadeInDown 2s ease-in-out;
    }
    @keyframes fadeInDown {
        0% {
            opacity: 0;
            transform: translateY(50px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-out-up-leave-active {
        transition: all 0.2s ease-in-out;
    }

    .fade-out-up-leave-to {
        opacity: 0;
        transform: translateY(-50px);
    }

    .fade-out-up-enter-active {
        transition: all 0.8s ease-in-out;
    }

    .fade-out-up-enter-from {
        opacity: 0;
        transform: translateY(-50px);
    }

    .fade-out-up-enter-to {
        opacity: 1;
        transform: translateY(0);
    }
</style>