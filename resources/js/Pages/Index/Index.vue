<script setup>
import { ref } from 'vue';
import ButtonSelection from '@/Components/ButtonSelection.vue';
import { Head } from '@inertiajs/vue3';
import Hero from  '@/Components/Hero.vue';
import LayoutMain from '@/Layouts/LayoutMain.vue';
const selectedOption = ref(null);
</script>
<template>
    <LayoutMain>
    <Hero></Hero>
    </LayoutMain>
</template>