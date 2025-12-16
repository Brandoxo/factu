<script setup>
import Header from '@/Components/Header.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
</script>
<template>
    <div class="min-h-screen bg-gray-100">
        <Header />
        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Contenido de la página -->
                        <form>
                            <div>
                                <Label for="ticket-folio" value="Folio del Ticket" />
                                <Input id="ticket-folio" class="block mt-1 w-full" type="text" name="ticket-folio" required autofocus />
                            </div>

                            <div class="mt-4">
                                <Label for="check-in" value="Check-in" />
                                <Input id="check-in" class="block mt-1 w-full" type="text" name="check-in" required />
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Guardar
                                </button>
                            </div>
                </div>
            </div>
        </main>
    </div>
</template>