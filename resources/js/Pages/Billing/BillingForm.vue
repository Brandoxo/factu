<script setup>
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import Swal from "sweetalert2";

const props = defineProps({
  reservation: {
    type: Object,
    required: true,
  },
});
console.log(props.reservation);

const form = useForm({
  reservationID: props.reservation.reservationID,
  guestName: props.reservation.guestName,
  startDate: props.reservation.startDate,
  endDate: props.reservation.endDate,
  roomNumber: props.reservation.assigned[0].roomName,
  adults: props.reservation.assigned[0].adults,
  children: props.reservation.assigned[0].children,
  paid: props.reservation.balanceDetailed.paid,

  taxes: "%16",
  total: props.reservation.total,

  rfc: "",
  razonSocial: "",
  email: "",
  codigoPostal: "",
  regimenFiscal: "",
  usoCfdi: "",
});

if (form.paid > 1) {
  form.paid = "Sí";
} else {
  form.paid = "No";
}

const submitBillingForm = () => {
  form.post("/billing/submit", {
    onSuccess: () => {
      Swal.fire({
        icon: "success",
        title: "¡Factura generada!",
        text: "Se ha enviado a tu correo electrónico.",
        confirmButtonText: "Aceptar",
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Revisa los campos del formulario",
      });
    },
  });
};
</script>

<template>
  <LayoutMain>
    <div class="max-w-4xl mx-auto py-8 px-4">
      <h1 class="text-3xl font-bold text-white mb-6 text-center lg:text-start">
        Datos para Facturación
      </h1>

      <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-white mb-4">
          Información de la Reserva
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-white">
          <div>
            <p class="text-sm opacity-75">Huésped</p>
            <p class="font-semibold">{{ reservation.guestName }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">ID de Reserva</p>
            <p class="font-semibold">{{ reservation.reservationID }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Fecha de inicio</p>
            <p class="font-semibold">{{ reservation.startDate }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Fecha de salida</p>
            <p class="font-semibold">{{ reservation.endDate || "N/A" }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Número de habitación</p>
            <p class="font-semibold">{{ reservation.assigned[0].roomName }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Total</p>
            <p class="font-semibold">${{ reservation.total }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Impuestos</p>
            <p class="font-semibold">{{ form.taxes }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Adultos</p>
            <p class="font-semibold">{{ reservation.assigned[0].adults }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Niños</p>
            <p class="font-semibold">{{ reservation.assigned[0].children }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Pagado</p>
            <p class="font-semibold">{{ form.paid }}</p>
          </div>
        </div>
      </div>

      <form
        @submit.prevent="submitBillingForm"
        class="bg-white/10 backdrop-blur-sm rounded-lg p-6"
      >
        <h2 class="text-xl font-semibold text-white mb-4">Datos Fiscales</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <InputLabel
              for="rfc"
              value="RFC"
              class="text-white font-light text-lg"
            />
            <input
              v-model="form.rfc"
              id="rfc"
              type="text"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50"
              placeholder="XAXX010101000"
              required
              maxlength="13"
            />
            <InputError class="mt-2" :message="form.errors.rfc" />
          </div>

          <div>
            <InputLabel
              for="razonSocial"
              value="Razón Social"
              class="text-white font-light text-lg"
            />
            <input
              v-model="form.razonSocial"
              id="razonSocial"
              type="text"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50"
              placeholder="Nombre o empresa"
              required
            />
            <InputError class="mt-2" :message="form.errors.razonSocial" />
          </div>

          <div>
            <InputLabel
              for="email"
              value="Correo Electrónico"
              class="text-white font-light text-lg"
            />
            <input
              v-model="form.email"
              id="email"
              type="email"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50"
              placeholder="correo@ejemplo.com"
              required
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel
              for="codigoPostal"
              value="Código Postal"
              class="text-white font-light text-lg"
            />
            <input
              v-model="form.codigoPostal"
              id="codigoPostal"
              type="text"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50"
              placeholder="01000"
              required
              maxlength="5"
            />
            <InputError class="mt-2" :message="form.errors.codigoPostal" />
          </div>

          <div>
            <InputLabel
              for="regimenFiscal"
              value="Régimen Fiscal"
              class="text-white font-light text-lg"
            />
            <select
              v-model="form.regimenFiscal"
              id="regimenFiscal"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white"
              required
            >
              <option value="" disabled>Selecciona un régimen</option>
              <option value="601">601 - General de Ley Personas Morales</option>
              <option value="603">
                603 - Personas Morales con Fines no Lucrativos
              </option>
              <option value="605">
                605 - Sueldos y Salarios e Ingresos Asimilados a Salarios
              </option>
              <option value="606">606 - Arrendamiento</option>
              <option value="612">
                612 - Personas Físicas con Actividades Empresariales y
                Profesionales
              </option>
              <option value="621">621 - Régimen de Incorporación Fiscal</option>
            </select>
            <InputError class="mt-2" :message="form.errors.regimenFiscal" />
          </div>

          <div>
            <InputLabel
              for="usoCfdi"
              value="Uso de CFDI"
              class="text-white font-light text-lg"
            />
            <select
              v-model="form.usoCfdi"
              id="usoCfdi"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white"
              required
            >
              <option value="" disabled>Selecciona un uso</option>
              <option value="G01">G01 - Adquisición de mercancías</option>
              <option value="G02">
                G02 - Devoluciones, descuentos o bonificaciones
              </option>
              <option value="G03">G03 - Gastos en general</option>
              <option value="I01">I01 - Construcciones</option>
              <option value="I02">
                I02 - Mobilario y equipo de oficina por inversiones
              </option>
              <option value="P01">P01 - Por definir</option>
            </select>
            <InputError class="mt-2" :message="form.errors.usoCfdi" />
          </div>
        </div>

        <div class="mt-6 flex gap-4">
          <button
            type="button"
            @click="$inertia.visit('/')"
            class="px-6 py-3 bg-gray-500 text-white rounded-full hover:bg-gray-600 transition-all"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 transition-all"
          >
            {{ form.processing ? "Generando..." : "Generar Factura" }}
          </button>
        </div>
      </form>
    </div>
  </LayoutMain>
</template>
