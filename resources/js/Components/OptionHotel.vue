<script setup>
    import InputLabel from './InputLabel.vue';
    import InputError from './InputError.vue';

    const errors = {};
</script>

<template>
        <main class="py-2">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200">
                        <!-- Contenido de la página -->
                        <form class="flex gap-6 flex-col lg:flex-row md:gap-4">
                            <div class="text-white flex gap-2 md:gap-4">
                                <div>
                                <InputLabel for="ticket-folio" value="Folio del Ticket" class="text-white font-extralight text-xl" />
                                <Input id="ticket-folio" class="block mt-4 w-36 md:w-full  border-white border-2 rounded-xl p-2 bg-white/10" type="text" name="ticket-folio" required autofocus />
                                </div>
                                <div>
                                <InputError class="mt-2" :message="errors['ticket-folio']" />
                                <InputLabel for="check-in" value="Check-in" class="text-white font-extralight text-xl" />
                                <Input id="check-in" class="block mt-4 w-36 md:w-full border-white border-2 rounded-xl p-2 bg-white/10" type="date" name="check-in" required />
                                <InputError class="mt-2" :message="errors['check-in']" />
                                </div>
                            </div>

                            <div class="items-end flex">
                                <button type="submit" class="p-2 px-6 w-full bg-blue-600 text-white rounded-full hover:bg-blue-700 cursor-pointer transition-all ease-in-out mt-4 md:mt-0">
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        </main>
</template>