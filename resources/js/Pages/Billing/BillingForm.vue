<script setup>
import { router, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import Swal from "sweetalert2";
import { computed } from "vue";
import taxRegimes from "../../../utils/taxRegimes.js";
import { createCfdiData } from "../../../utils/cfdiData.js";
import { items } from "../../../utils/items.js";

const props = defineProps({
  reservation: {
    type: Object,
    required: true,
  },
});
console.log("estos son los datos de la reserva", props.reservation);

const filteredRegimes = computed(() => {
  const rfcLength = form.rfc.length;

  return taxRegimes.filter((regime) => {
    if (rfcLength === 12)
      return (
        regime.aplica_para.includes("Persona Moral") ||
        regime.aplica_para.includes("Ambos")
      );
    if (rfcLength === 13)
      return (
        regime.aplica_para.includes("Persona Física") ||
        regime.aplica_para.includes("Ambos")
      );
    return true;
  });
});

const getTotalRate = (dailyRates) => {
  if (!Array.isArray(dailyRates)) return 0;
  return dailyRates.reduce((sum, item) => sum + (item.rate || 0), 0);
};

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
  // Crear los datos CFDI con los valores actuales del formulario
  const cfdiDataH = createCfdiData(form, items(props.reservation));

  console.log("estos son los datos cfdi de la reserva", cfdiDataH);

  router.post("/billing/generate-invoice", {
    cfdiData: cfdiDataH,

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
      <div class="flex-col flex gap-0 m-6">
        <h1 class="text-3xl text-white text-center uppercase">
          Hotel Ronda Minerva S.A de CV
        </h1>
        <h2 class="text-2xl text-white text-center uppercase">
          Generar
          <a
            class="text-red-100 underline"
            target="_blank"
            href="http://omawww.sat.gob.mx/tramitesyservicios/Paginas/anexo_20.htm"
            >CFDI v4.0</a
          >
        </h2>
      </div>

      <div
        v-for="room in reservation.assigned"
        class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6"
      >
        <h2 class="text-xl font-semibold text-white mb-4">
          Información de la Reserva
          {{ 1 + reservation.assigned.indexOf(room) }}
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 text-white">
          <div>
            <p class="text-sm opacity-75">A nombre de:</p>
            <p class="font-semibold">{{ reservation.guestName }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">ID de Reserva</p>
            <p class="font-semibold">{{ room.subReservationID }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Número de habitación</p>
            <p class="font-semibold">{{ room.roomName }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Habitación</p>
            <p class="font-semibold">{{ room.roomTypeName }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Adultos</p>
            <p class="font-semibold">{{ room.adults }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Niños</p>
            <p class="font-semibold">{{ room.children }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Fecha de inicio</p>
            <p class="font-semibold">{{ room.startDate }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Fecha de salida</p>
            <p class="font-semibold">{{ room.endDate || "N/A" }}</p>
          </div>
          <div>
            <p class="text-sm opacity-75">Total</p>
            <p class="font-semibold">
              ${{ getTotalRate(room.dailyRates) }} MXN
            </p>
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
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50 uppercase"
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
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white/10 text-white placeholder-white/50 uppercase"
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
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white"
              required
            >
              <option value="" disabled>Selecciona un régimen</option>
              <option
                v-if="form.rfc.length >= 12"
                v-for="regime in filteredRegimes"
                :key="regime.clave"
                :value="regime.clave"
              >
                {{ regime.clave }} - {{ regime.nombre }}
              </option>
              <option v-else disabled>
                Ingresa un RFC válido para ver los regímenes aplicables
              </option>
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
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white"
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
            @click="history.back()"
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
