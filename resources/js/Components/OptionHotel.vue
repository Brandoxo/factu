<script setup>
import InputLabel from './InputLabel.vue';
import InputError from './InputError.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    'ticketFolio': '',
    'checkIn': '',
});
const page = usePage();
const submitForm = () => {
    axios.post('/hotel/submit-form', {
        ticketFolio: form.ticketFolio,
        checkIn: form.checkIn,
    }).then((response) => {
        console.log(response);
        const showData = response.data.data.data;
        const message = ` Bienvenido(a) ${showData.guestName}, su reserva en ${showData.reservationID} para el día ${showData.startDate} ha sido encontrada.`;
        alert(message);
        form.reset('ticketFolio', 'checkIn');
    }).catch((error) => {
        if (error.response && error.response.data && error.response.data.errors) {
            Object.assign(errors, error.response.data.errors);
        }
    });
};

    const errors = {};
</script>

<template>
        <main class="py-2">
            <h1>{{ usePage().props }}</h1>
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200">
                        <!-- Contenido de la página -->
                        <form @submit.prevent="submitForm" class="flex gap-6 flex-col lg:flex-row md:gap-4">
                            <div class="text-white flex gap-2 md:gap-4">
                                <div>
                                <InputLabel for="ticket-folio" value="Folio del Ticket" class="text-white font-extralight text-xl" />
                                <input v-model="form.ticketFolio" id="ticket-folio" class="block mt-4 w-36 md:w-full  border-white border-2 rounded-xl p-2 bg-white/10" type="text" name="ticket-folio" required autofocus />
                                </div>
                                <div>
                                <InputError class="mt-2" :message="errors['ticket-folio']" />
                                <InputLabel for="check-in" value="Check-in" class="text-white font-extralight text-xl" />
                                <input v-model="form.checkIn" id="check-in" class="block mt-4 w-36 md:w-full border-white border-2 rounded-xl p-2 bg-white/10" type="date" name="check-in" required />
                                <InputError class="mt-2" :message="errors['check-in']" />
                                </div>
                            </div>

                            <div class="items-end flex">
                                <button :disabled="form.processing" type="submit" class="p-2 px-6 w-full bg-blue-600 text-white rounded-full hover:bg-blue-700 cursor-pointer transition-all ease-in-out mt-4 md:mt-0">
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