<script setup>
import { router, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import Swal from "sweetalert2";
import { computed, ref } from "vue";
import taxRegimes from "../../../utils/taxRegimes.js";
import { createCfdiData } from "../../../utils/cfdiData.js";
import { paymentMethods } from "../../../utils/paymentMethods.js";
import { usoCfdOptions } from "../../../utils/usoCfdi.js";

const props = defineProps({
  orderData: Object,
});
console.log("Datos de la orden recibidos en el componente: ", props.orderData);
const form = useForm({
  rfc: "",
  razonSocial: "",
  email: "",
  codigoPostal: "",
  regimenFiscal: "",
  usoCfdi: "",
  paymentMethod: "",
});

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
        class="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6"
      >
        <div>
          <h2 class="text-xl font-semibold text-white mb-4">
            Información de la Orden de Restaurante
          </h2>
          <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 text-white">
            <div>
              <p class="text-sm opacity-75">Ticket</p>
              <p class="font-semibold">11111</p>
            </div>
            <div>
              <p class="text-sm opacity-75">Fecha</p>
              <p class="font-semibold">Nose</p>
            </div>

            <div>
              <p class="text-sm opacity-75">SubTotal + IVA</p>
              <p class="font-semibold">
                $ 99.00 MXN
              </p>
            </div>
          </div>
        </div>
        <!-- Lista de articulos -->
        <div>
          <h3 class="text-lg font-semibold text-white mt-6 mb-4">
            Artículos consumidos:
          </h3>
          <div class="flex flex-col gap-4 text-white">
            <div>
              <p class="font-semibold">Hamburguesa</p>
              <p class="text-sm opacity-75">Cantidad: 1</p>
              <p class="text-sm opacity-75">Precio Unitario: $50.00 MXN</p>
            </div>

            <div>
              <p class="font-semibold">Refresco</p>
              <p class="text-sm opacity-75">Cantidad: 2</p>
              <p class="text-sm opacity-75">Precio Unitario: $25.00 MXN</p>
            </div>

          </div>
        </div>
        <div class="text-center">
          <h3 class="text-lg font-semibold text-white mt-4 mb-2">
            Total de la orden:
          </h3>
          <p class="text-white text-2xl font-bold">
            $100 MXN
          </p>
        </div>
        
      </div>

      <div
        class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mt-0 text-white"
      >
        <div class="flex w-fit mx-auto gap-2 mb-3">
          <h3 class="text-sm">Complementos:</h3>
          <span class="font-semibold text-sm">
            $ 10 MXN
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
          <span class="text-2xl font-semibold underline"> $110.00 MXN </span
          >
        </h2>
        <p
          class="text-red-300 text-sm mt-2"
        >
          ⚠️ Debes seleccionar al menos una habitación para facturar
        </p>
      </div>

      <form @submit.prevent="null" class="">
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
              :disabled="form.processing"
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