    <script setup>
    import Header from '@/Components/Header.vue'
    import Footer from '@/Components/Footer.vue'
    import Sidebar from '@/Components/Sidebar.vue';
    import ButtonsFloat from '@/Components/ButtonsFloat.vue';
    </script>
<template>
    <Header></Header>
        <Sidebar></Sidebar>
    <slot></slot>
    <Footer></Footer>
    <ButtonsFloat></ButtonsFloat>
</template>
