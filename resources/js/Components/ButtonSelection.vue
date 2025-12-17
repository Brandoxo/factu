<script setup>
import { ref, defineEmits } from 'vue';
import { watch } from 'vue';
import ButtonBack from '@Components/ButtonBack.vue';
import OptionHotel from '@Components/OptionHotel.vue';
import OptionRestaurant from '@Components/OptionRestaurant.vue';
const emit = defineEmits(['update:modelValue', 'selected']);

const selectedValue = ref(null);


const options = [
    { label: 'Hotel', value: 'hotel' },
    { label: 'Restaurante', value: 'restaurante' },
];

function selectOption(value) {
    selectedValue.value = value;
    emit('update:modelValue', value);
    emit('selected', value);
}

watch(selectedValue, (newValue) => {
    console.log('Opción seleccionada:', newValue);
    emit('update:modelValue', newValue);
});
</script>

<template>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <div class="text-white text-center justify-center items-center gap-6 h-full flex flex-col">
                    <h1 class="title text-2xl font-bold">¿Qué servicio desea facturar?</h1>
                    <div class="w-fit flex-col flex md:flex-row gap-6 text-center justify-center items-center  text-xl font-extrabold">
                        <div v-if="selectedValue !== options[1].value">
                            <button @click="selectOption(options[0].value)" class="button-left p-4 font-semibold px-10 bg-red-600 w-96 rounded-md hover:bg-red-700 cursor-pointer transition-all ease-in-out">{{options[0].label}}</button>
                        </div>
                        <div v-if="selectedValue !== options[0].value">
                            <button @click="selectOption(options[1].value)" class="button-right p-4 font-semibold px-6 bg-red-600 w-96 rounded-md hover:bg-red-700 cursor-pointer transition-all ease-in-out">{{options[1].label}}</button>
                        </div>
                        <div v-if="selectedValue === options[0].value">
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-6">
                    <OptionHotel v-if="selectedValue === options[0].value" />
                    <OptionRestaurant v-if="selectedValue === options[1].value" />
                    <ButtonBack v-if="selectedValue !== null" @click="selectedValue = null" class="cursor-pointer"/>
                </div>
          </div>
</template>
<style>
  .button-left {
        animation: slideInLeft 1s ease-in-out;
    }

    @keyframes slideInLeft {
        0% {
            opacity: 0;
            transform: translateX(-100%);
        }

        25% {
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .button-right {
        animation: slideInRight 1s ease-in-out;
    }

    @keyframes slideInRight {
        0% {
            opacity: 0;
            transform: translateX(100%);
        }

        25% {
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

        .title {
        animation: retraso 3s ease-in-out;
    }

    @keyframes retraso {
        0% {
            opacity: 0;
        }

        25% {
            opacity: 0.3;
        }

        50% {
            opacity: 0.6;
        }

        75% {
            opacity: 0.8;
        }

        100% {
            opacity: 1;
        }
    }
</style>