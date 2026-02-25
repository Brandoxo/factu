<script setup>
import { router, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import Swal from "sweetalert2";
import { computed, ref } from "vue";
import taxRegimes from "../../../utils/taxRegimes.js";
import { createCfdiData } from "../../../utils/cfdiData.js";
import { items } from "../../../utils/items.js";
import { getTotalRate } from "../../../utils/helpers.js";
import axios from "axios";
import { paymentMethods } from "../../../utils/paymentMethods.js";
import { usoCfdOptions } from "../../../utils/usoCfdi.js";
import { calculateIsh } from "../../../utils/helpers.js";
import { ishWithIvaPercent } from "../../../utils/helpers.js";

const props = defineProps({
  reservation: {
    type: Object,
    required: true,
  },
  filteredRoomsAvailable: {
    type: Array,
    required: true,
  },
});
console.log("estos son los datos de la reserva", props.reservation);
console.log(
  "Estos son los rooms disponibles:",
  props.filteredRoomsAvailable,
);

const filteredRoomsAvailableWithExtras = computed(() => {
  return props.filteredRoomsAvailable.includes(
    props.reservation.reservationID + "-extras",
  );
});
console.log(
  "Filtered Rooms Available with Extras:",
  filteredRoomsAvailableWithExtras.value,
);

//Año para ISH
const yearReservation = props.reservation.startDate.slice(0, 4);
const taxesIncluded = props.reservation.balanceDetailed.taxesFees;
const isTaxable = taxesIncluded === 0 ? true : false;

const displayRoomTotal = (room) => {
  const subtotal = getTotalRate(room.dailyRates);
  const ish = calculateIsh(subtotal, yearReservation);

  if (isTaxable) {
    // Impuestos incluidos: mostrar subtotal menos ISH
    let totalBase = Number(
      (
        subtotal /
        (1 + ishWithIvaPercent[yearReservation] || ishWithIvaPercent["default"])
      ).toFixed(2),
    );
    console.log("Total base sin impuestos:", totalBase);
    return totalBase.toFixed(2);
  }

  const iva = subtotal * 0.16;
  return Number((subtotal + iva + ish).toFixed(2));
};

const filteredRoomsAvailable = computed(() => {
  return props.reservation.assigned.filter((room) =>
    props.filteredRoomsAvailable.includes(room.subReservationID)
  );
});
console.log("Filtered Rooms Available:", filteredRoomsAvailable.value);

// Estado reactivo para trackear qué habitaciones están incluidas (usando subReservationID como clave)
const selectedRooms = ref(
  filteredRoomsAvailable.value.reduce((acc, room) => {
    acc[room.subReservationID] = true;
    return acc;
  }, {})
);
// Estado reactivo para trackear si los items adicionales están incluidos
const includeAdditionalItems = ref(true);

if (filteredRoomsAvailableWithExtras.value) {
   includeAdditionalItems.value = true;
} else {
   includeAdditionalItems.value = false;
}

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

// Computed para calcular el total dinámicamente
const totalToInvoice = computed(() => {
  let total = 0;

  // Sumar habitaciones seleccionadas
  filteredRoomsAvailable.value.forEach((room) => {
    if (selectedRooms.value[room.subReservationID]) {
      let roomSubtotal = getTotalRate(room.dailyRates);

      if (!isTaxable) {
        // Los impuestos NO están incluidos: sumar subtotal + impuestos
        const ish = calculateIsh(roomSubtotal, yearReservation);
        const iva = roomSubtotal * 0.16;
        total += Number((roomSubtotal + iva + ish).toFixed(2));
      } else {
        // Los impuestos SÍ están incluidos: el subtotal ya es el total
        total += Number(roomSubtotal.toFixed(2));
      }
    }
  });

  // Agregar complementos si están seleccionados
  if (
    includeAdditionalItems.value &&
    props.reservation.balanceDetailed.additionalItems > 0
  ) {
    const additionalAmount =
      Number(props.reservation.balanceDetailed.additionalItems) || 0;
    total += additionalAmount;
  }

  return Number(total.toFixed(2));
});

// Computed para obtener los items filtrados
const selectedItems = computed(() => {
  const allItems = items(props.reservation);
  
  // Filtrar items usando sub_reservation_id
  return allItems.filter((item) => {
    // Si el item tiene sub_reservation_id, verificar si está seleccionado
    if (item.sub_reservation_id) {
      return selectedRooms.value[item.sub_reservation_id];
    }
    // Si no tiene sub_reservation_id (items adicionales), verificar el checkbox de complementos
    return includeAdditionalItems.value;
  });
});

const form = useForm({
  rfc: "",
  razonSocial: "",
  email: "",
  codigoPostal: "",
  regimenFiscal: "",
  usoCfdi: "",
  paymentMethod: "",
});

const extrasId = computed(() => {
  if (props.reservation.balanceDetailed.additionalItems > 0)
    return {
      subReservationIDs: [
        ...props.reservation.assigned.map(
          (room) => room.subReservationID,
        ),
        props.reservation.reservationID + "-extras",
      ],
      roomIDs: props.reservation.assigned.map(
          (room) => room.roomID,
        ),
    };
    else {
      return {
        subReservationIDs: [
          ...props.reservation.assigned.map(
            (room) => room.subReservationID,
          ),
        ],
       roomIDs: props.reservation.assigned.map(
          (room) => room.roomID,
        ),
      };
    }
});
console.log("Extras ID:", extrasId.value);

const submitBillingForm = async () => {
  Swal.fire({
    title: "Generando factura...",
    text: "Por favor espera mientras se crea tu factura.",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
  const cfdiDataH = await createCfdiData(form, selectedItems.value);

  console.log("Enviando a Facturama...", cfdiDataH);

  try {
    const response = await axios.post("/billing/generate-invoice", {
      cfdiData: cfdiDataH,
      optionsId: {
        reservationId: props.reservation.reservationID ?? null,
        orderId: props.reservation.orderID ?? null,
      },
      extrasId: extrasId.value,
      filteredRoomsAvailable: props.filteredRoomsAvailable,
    });
    console.log('FIltered Rooms:', selectedItems.value);
    console.log("Respuesta Exitosa:", response.data);
    Swal.fire({"title": "¡Éxito!", "text": "Factura creada correctamente", "icon": "success"});
    // Redirigir a la página de éxito con URL firmada
    setTimeout(() => {
      console.log("Proccess proccessed oyeah oyeah");
      
      //window.location.href = response.data.successUrl;
    }, 2000);
  } catch (error) {
    console.error("Error en la petición:", error.response?.data || error.message || error);

    const errorMessage = error.response?.data?.message || error.message || "No se pudo crear la factura.";
    Swal.fire("Error", errorMessage, "error");
  }
};
</script>

<template>
  <LayoutMain>
    <div class="max-w-4xl mx-auto py-8 px-4">
      <div class="flex-col flex gap-0 m-6">
        <h1 class="text-3xl text-white text-center uppercase">
          HOTELERA FERROGAL S.A de CV
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
        v-for="room in filteredRoomsAvailable"
        class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6"
      >
        <div>
          <h2 class="text-xl font-semibold text-white mb-4">
            Información de la Reserva
          </h2>
          <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 text-white">
            <div>
              <p class="text-sm opacity-75">ID de Reserva</p>
              <p class="font-semibold">{{ room.subReservationID }}</p>
            </div>
            <div>
              <p class="text-sm opacity-75">A nombre de:</p>
              <p class="font-semibold">{{ reservation.guestName }}</p>
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
              <p class="text-sm opacity-75">Impuesto ISH</p>
              <p class="font-semibold">
                ${{
                  isTaxable
                    ? Number(
                        calculateIsh(displayRoomTotal(room), yearReservation),
                      ).toFixed(2)
                    : Number(
                        calculateIsh(
                          getTotalRate(room.dailyRates),
                          yearReservation,
                        ),
                      ).toFixed(2)
                }}
                MXN
              </p>
            </div>
            <div>
              <p class="text-sm opacity-75">SubTotal + IVA</p>
              <p class="font-semibold">
                ${{
                  isTaxable
                    ? Number(displayRoomTotal(room) * 1.16).toFixed(2)
                    : Number(getTotalRate(room.dailyRates) * 1.16).toFixed(2)
                }}
                MXN
              </p>
            </div>
          </div>
        </div>
        <div class="text-center">
          <h3 class="text-lg font-semibold text-white mt-4 mb-2">
            Total de la habitación:
          </h3>
          <p class="text-white text-2xl font-bold">
            ${{ displayRoomTotal(room) }} MXN
          </p>
        </div>
        
        <div class="mt-4 text-center">
          <input
            type="checkbox"
            :id="'includeRoom' + room.subReservationID"
            v-model="selectedRooms[room.subReservationID]"
            class="mt-4 w-4 h-4 cursor-pointer"
          />
          <label
            :for="'includeRoom' + room.subReservationID"
            class="text-white ml-2 cursor-pointer"
          >
            Incluir esta habitación en la factura
          </label>
        </div>
      </div>

      <div
        v-if="reservation.balanceDetailed.additionalItems > 0 && filteredRoomsAvailableWithExtras"
        class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mt-0 text-white"
      >
        <div class="flex w-fit mx-auto gap-2 mb-3">
          <h3 class="text-sm">Complementos:</h3>
          <span class="font-semibold text-sm">
            ${{
              Number(reservation.balanceDetailed.additionalItems).toFixed(2)
            }}
            MXN
          </span>

          <VTooltip>
            <span
              class="bg-white/30 text-sm px-2 rounded-full font-bold italic cursor-help"
            >
              ?
            </span>

            <template #popper>
              <div class="p-2">
                <p class="mb-2 font-bold text-center">Servicios adicionales</p>
                <ul class="text-sm text-gray-100 list-disc list-inside">
                  <li>Llegada anticipada</li>
                  <li>Salida Tardía</li>
                  <li>Persona Extra</li>
                  <li>Servicio de Lavandería</li>
                </ul>
                <div class="mt-2 text-xs text-gray-300 text-center">
                  <p>
                    Los costos de estos servicios se incluyen en el total a
                    facturar.
                  </p>
                  <p>(Pueden variar)</p>
                </div>
              </div>
            </template>
          </VTooltip>
        </div>

        <div class="text-center">
          <input
            type="checkbox"
            id="includeAdditionalItems"
            v-model="includeAdditionalItems"
            class="w-4 h-4 cursor-pointer"
          />
          <label
            for="includeAdditionalItems"
            class="text-white ml-2 cursor-pointer"
          >
            Incluir complementos en la factura
          </label>
        </div>
      </div>

      <div
        class="bg-white/10 backdrop-blur-sm rounded-b-lg p-6 mb-6 text-white text-center"
      >
        <h2 class="text-2xl font-semibold">
          Total a facturar:
          <span class="text-2xl font-semibold underline"
            >${{ totalToInvoice }} MXN</span
          >
        </h2>
        <p
          v-if="!Object.values(selectedRooms).some((selected) => selected)"
          class="text-red-300 text-sm mt-2"
        >
          ⚠️ Debes seleccionar al menos una habitación para facturar
        </p>
      </div>

      <form @submit.prevent="submitBillingForm" class="">
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6">
          <h2 class="text-xl font-semibold text-white mb-4 text-center">
            ¿Cuál fué el método de pago?
          </h2>
          <div>
            <select
              v-model="form.paymentMethod"
              id="paymentMethod"
              class="block mt-2 w-full border-white border-2 rounded-xl p-3 bg-white"
              required
            >
              <option value="" disabled>Selecciona el método que se uso</option>
              <option
                v-for="method in paymentMethods"
                :key="method.value"
                :value="method.value"
              >
                {{ method.value }} - {{ method.name }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.paymentMethod" />
          </div>
        </div>

        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6">
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
                <option
                  v-for="uso in usoCfdOptions"
                  :key="uso.value"
                  :value="uso.value"
                >
                  {{ uso.value }} - {{ uso.label }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.usoCfdi" />
            </div>
          </div>

          <div class="mt-6 flex gap-4">
            <button
              type="button"
              @click="router.visit('/services')"
              class="px-6 py-3 bg-gray-500 text-white rounded-full hover:bg-gray-600 transition-all cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="
                form.processing || !Object.values(selectedRooms).some((selected) => selected)
              "
              class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 transition-all cursor-pointer"
            >
              {{ form.processing ? "Generando..." : "Generar Factura" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </LayoutMain>
</template>