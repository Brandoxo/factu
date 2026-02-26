<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from './InputLabel.vue';
import InputError from './InputError.vue';
import Swal from "sweetalert2";
import axios from 'axios';
import { ref } from 'vue';

const form = useForm({
    'ticketFolio': '',
    'totalAmount': '',
    'date': '',
});

const errors = {};
const isProcessing = ref(false);

const submitForm = () => {
    isProcessing.value = true;
    axios.post('/api/pcbrestaurant/order/' + form.ticketFolio, {
        ticketFolio: form.ticketFolio,
        totalAmount: form.totalAmount,
        date: form.date,
    }).then((response) => {
        isProcessing.value = false;
        console.log(response);
        form.reset('ticketFolio', 'totalAmount', 'date');
        Swal.fire({
            title: "¡Éxito!",
            text: "La orden se ha buscado correctamente.",
            icon: "success",
        });
    }).catch((error) => {
        isProcessing.value = false;
        let errorMessages = "";
        console.log("Error al enviar el formulario de restuarante: ", error);
        if (error.response && error.response.data && error.response.data.errors) 
        {
            Object.assign(errors, error.response.data.errors);

            errorMessages = Object.values(errors).flat().join('\n');
        } else if (error.response && error.response.data && error.response.data.error) 
        {
            errorMessages = error.response.data.error;
        }
        Swal.fire({
            title: "Error",
            text: "Por favor, revise los valores ingresados:\n" + errorMessages,
            icon: "error",
        });
        
    }).finally(() => {
        isProcessing.value = false;
    });
};
</script>

<template>
        <main class="py-2">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200">
                        <div v-if="isProcessing" class="flex items-center justify-center">
                            <h2 class="text-white font-extralight text-2xl">Buscando orden de restaurante</h2>
                        </div>
                        <!-- Contenido de la página -->
                        <form @submit.prevent="submitForm" class="flex gap-4 flex-col md:items-center lg:flex-row">
                            <div class="text-white flex gap-2 md:gap-4">
                                <div>
                                <InputLabel for="ticket-folio" value="Folio del Ticket" class="text-white font-extralight text-xl" />
                                <input v-model="form.ticketFolio" id="ticket-folio" class="block mt-4 w-34 md:w-full  border-white border-2 rounded-xl p-2 bg-white/10" type="text" name="ticket-folio" required autofocus />
                                <InputError class="mt-2" :message="errors['ticket-folio']" />
                                </div>
                                <div>
                                <InputLabel for="total-amount" value="Importe total" class="text-white font-extralight text-xl" />
                                <input v-model="form.totalAmount" id="total-amount" class="block mt-4 w-34 md:w-full border-white border-2 rounded-xl p-2 bg-white/10" type="text" name="total-amount" required />
                                <InputError class="mt-2" :message="errors['total-amount']" />
                                </div>
                            </div>

                            <div class="text-white">
                                <InputLabel for="date" value="Fecha del consumo" class="text-white font-extralight text-xl w-full" />
                                <input v-model="form.date" id="date" class="block mt-4 w-full md:w-46 border-white border-2 rounded-xl p-2 bg-white/10" type="date" name="date" required />
                                <InputError class="mt-2" :message="errors['date']" />
                            </div>

                            <div class="items-end flex lg:mt-12">
                                <button :disabled="isProcessing" type="submit" class="p-2 px-6 w-full lg:w-34 bg-blue-600 text-white rounded-full hover:bg-blue-700 cursor-pointer transition-all ease-in-out mt-4 md:mt-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline-block mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        </main>
</template>