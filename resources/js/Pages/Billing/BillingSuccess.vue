<script setup>
import LayoutMain from '../../Layouts/LayoutMain.vue';
import { ref, reactive, computed } from 'vue';
import  { formatCurrency }  from '../../../utils/formatCurrency.js';
import Swal from 'sweetalert2';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
const props = defineProps({
  billingData: {
    type: Object,
    required: true,
  },
});
console.log('Datos de facturación recibidos:', props.billingData);


const cfdiResponse = reactive(props.billingData.cfdiResponse);
const cfdiStorage = reactive(props.billingData.storageResponse);

const cfdiResponseItems = ref(cfdiResponse.Items || []);
const cfdiResponseTaxes = ref(cfdiResponse.Taxes || []);

const cfdiResponseFiles = ref(cfdiStorage.files || {});

const ishTotal = computed(() => {
  const ishTax = cfdiResponseItems.value.map(item => item.UnitValue * item.Quantity).reduce((acc, total) => acc + total * 0.05, 0);
  return ishTax;
});
  
// --- Lógica del Formulario de Email ---
const email = ref('');
const loading = ref(false);
const emailSent = ref(false);

console.log('props cfdi:', props.billingData);

const sendEmail = () => {
  if (!email.value || !email.value.includes('@')) return;

  loading.value = true;
  
  axios.post('/invoice/success/send-email',{
    cfdiData: props.billingData,
    email: email.value,
  }
  ).then(() => {
    console.log('Correo enviado con éxito');
    loading.value = false;
    emailSent.value = true;
    email.value = '';
    Swal.fire({
      icon: 'success',
      title: 'Correo enviado',
      text: 'El comprobante ha sido enviado al correo proporcionado.',
      confirmButtonAriaLabel: 'OK',
    });
  }).catch(() => {
    console.error('Error al enviar el correo');
    setTimeout(() => {
      loading.value = false;
      emailSent.value = true;
      email.value = '';
      }, 2000);
  });

};
</script>

<template>
  <LayoutMain>
    <div class="min-h-screen bg-black/80 flex items-center justify-center p-4">
      
      <div class="relative w-full max-w-xl bg-white shadow-2xl rounded-t-lg">
        
        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-green-500 rounded-full p-3 shadow-lg border-4 border-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <div class="text-center pt-12 pb-6 px-6 border-b-2 border-dashed border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 tracking-wide uppercase">{{ cfdiResponse.Issuer.TaxName }}</h2>
        <p class="text-xs text-gray-500 mt-1">{{ cfdiResponse.Issuer.TaxAddress.Street }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ cfdiResponse.Issuer.TaxAddress.Neighborhood }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ cfdiResponse.Issuer.TaxAddress.State }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ cfdiResponse.Issuer.TaxAddress.ZipCode }}</p>
        <div class="mt-4 bg-green-50 text-green-700 py-1 px-3 rounded-full text-xs font-semibold inline-block">
          ¡Facturación Exitosa!
        </div>
        <p class="text-xs text-gray-400 mt-2">Folio: {{ cfdiResponse.Folio }}</p>
        <p class="text-xs text-gray-400">ID: {{ cfdiResponse.Id }}</p>
        <p class="text-xs text-gray-400">{{ cfdiResponse.Date }}</p>
      </div>

      <div class="p-6 bg-white">
        <ul class="space-y-3">
          <li v-for="(item, index) in cfdiResponseItems" :key="index" class="flex justify-between items-start text-sm">
            <div class="flex-1">
              <span class="font-medium text-gray-700">{{ item.Description }}</span>
              <div class="text-xs text-gray-400">Cant: {{ item.Quantity }}</div>
            </div>
            <span class="font-mono text-gray-600">{{ formatCurrency(item.UnitValue * item.Quantity) }}</span>
          </li>
        </ul>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-b border-gray-100">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Subtotal</span>
          <span class="font-mono">{{ formatCurrency(cfdiResponse.Subtotal) }}</span>
        </div>
        <div v-for="item in cfdiResponseTaxes" class="flex justify-between text-xs text-gray-500 mb-3">
          <span>IVA (16%)</span>
          <span class="font-mono">{{ formatCurrency(item.Total) }}</span>
        </div>
        <div  class="flex justify-between text-xs text-gray-500 mb-3">
          <span>ISH  (5%)</span>
          <!-- Si es un cargo adicional saltarlo-->
          <span class="font-mono">{{ formatCurrency(ishTotal) }}</span>
        </div>
        <div class="flex justify-between items-center text-lg font-bold text-gray-800 border-t border-dashed border-gray-300 pt-3">
          <span>Total</span>
          <span class="font-mono text-indigo-600">{{ formatCurrency(cfdiResponse.Total) }}</span>
        </div>
      </div>
      <div class="px-6 py-4 bg-gray-50 border-t border-b border-gray-100">
      <h3 class="text-sm font-semibold text-center text-green-600">El comprobante ha sido enviado al correo registrado.</h3>
      </div>
      
      
      <div class="p-6 bg-gray-800 text-white relative">
        <div class="absolute top-0 left-0 w-full h-2 bg-gray-50" style="clip-path: polygon(0 0, 100% 0, 100% 100%, 0 0);"></div>
        <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2">Enviar comprobante a otro correo</label>
        
        <div v-if="!emailSent" class="flex gap-2">
          <input 
          v-model="email" 
          type="email" 
          placeholder="ejemplo@correo.com" 
          class="w-full px-3 py-2 rounded bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:outline-none focus:border-indigo-500 transition-colors text-sm"
            @keyup.enter="sendEmail"
          />
          <button 
          @click="sendEmail" 
            :disabled="loading"
            class="bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-400 text-white px-4 py-2 rounded transition-colors flex items-center justify-center min-w-[3rem]"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </button>
        </div>
        
        <div v-else class="flex items-center text-green-400 py-2 animate-pulse">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span class="text-sm font-medium">Enviado correctamente</span>
        </div>
      </div>
      <button class="bg-red-900 text-white px-4 py-2 cursor-pointer w-full" @click="router.visit('/services')">Regresar al inicio</button>
    </div>
  </div>
  </LayoutMain>
</template>