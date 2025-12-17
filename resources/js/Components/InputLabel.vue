<script setup>
defineProps({
    value: {
        type: String, default: '',
    },
    example: {
        type: String,
        default: '',
    },
        },);
</script>

<template>
    <label class="flex text-sm font-bold text-black gap-x-2">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span class="text-gray-500 text-xs italic" v-if="example">({{ example }})</span>  
        <span v-else></span>  
    </label>
</template>
