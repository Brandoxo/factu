<script setup>
import InputLabel from "./InputLabel.vue";
import InputError from "./InputError.vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const form = useForm({
  ticketFolio: "",
  checkOut: "",
});

const submitForm = () => {
  Swal.fire({
    didOpen: () => {
      Swal.showLoading();
    },
    title: "Buscando reserva...",
    text: "Por favor, espere.",
    allowOutsideClick: false,
    showConfirmButton: false,
  });

  axios
    .post("/hotel/submit-form", {
      ticketFolio: form.ticketFolio,
      checkOut: form.checkOut,
    })
    .then((response) => {
      console.log("Respuesta:", response.data);
      const billingUrl = response.data.billing_url;
      Swal.close();
      Swal.fire({
        icon: "success",
        title: "Reserva encontrada",
        timer: 1500,
        showConfirmButton: false,
      });
      if (billingUrl) {
        router.visit(billingUrl);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo generar el enlace de facturación",
        });
      }
    })
    .catch((error) => {
      if (error.response) {
        console.error("Error response:", error.response.data);
        const msg =
          error.response.data?.message || "Error al buscar la reserva";

        Swal.fire({
          icon: "error",
          title: "Error",
          text: msg,
        });

        if (error.response.data && error.response.data.errors) {
          Object.assign(errors, error.response.data.errors);
        }
      } else {
        console.error("Error:", error);
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo conectar con el servidor",
        });
      }
    });
};

const errors = {};
</script>

<template>
  <main class="py-2">
    <div class="overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-4 border-b border-gray-200">
        <!-- Contenido de la página -->
        <form
          @submit.prevent="submitForm()"
          class="flex gap-6 flex-col lg:flex-row md:gap-4"
        >
          <div class="text-white flex gap-2 md:gap-4">
            <div>
              <div class="flex">
              <InputLabel
                for="ticket-folio"
                value="ID de Reserva"
                class="text-white font-extralight text-xl"
              />
              <VTooltip>
                <span class="bg-white/30 text-sm md:px-2 rounded-full font-bold italic cursor-help ">
                  ?
                </span>

                <template #popper>
                  <div class="p-2 text-center">
                    <p class="mb-2 font-bold">Ejemplo de Folio:</p>
                    <img 
                      src="/public/assets/img/reservationIDExample.jpeg" 
                      alt="Ejemplo de ID" 
                      class="rounded border w-84"
                    />
                    <p class="mt-1 text-xs text-gray-500">El ID se encuentra abajo del nombre del huesped.</p>
                  </div>
                </template>
              </VTooltip>
              </div>
              <input
                v-model="form.ticketFolio"
                id="ticket-folio"
                class="block mt-4 w-36 md:w-full border-white border-2 rounded-xl p-2 bg-white/10"
                type="text"
                name="ticket-folio"
                required
                autofocus
              />
            </div>
            <div>
              <InputError class="mt-2" :message="errors['ticket-folio']" />
              <InputLabel
                for="check-out"
                value="check-out"
                class="text-white font-extralight text-xl"
              />
              <input
                v-model="form.checkOut"
                id="check-out"
                class="block mt-4 w-36 md:w-full border-white border-2 rounded-xl p-2 bg-white/10"
                type="date"
                name="check-out"
                required
              />
              <InputError class="mt-2" :message="errors['check-out']" />
            </div>
          </div>

          <div class="items-end flex">
            <button
              :disabled="form.processing"
              type="submit"
              class="p-2 px-6 w-full bg-blue-600 text-white rounded-full hover:bg-blue-700 cursor-pointer transition-all ease-in-out mt-4 md:mt-0"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-6 h-6 inline-block mr-2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                />
              </svg>
              Buscar
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</template>
