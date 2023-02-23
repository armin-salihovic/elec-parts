<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import InputText from "primevue/inputtext";
import InputSwitch from "primevue/inputswitch";
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    locations: Object,
})

const locationName = ref('');
const locationId = ref(null);
const existingLocation = ref(false);

async function handleLocation() {
    if(!existingLocation.value) {
        try {
            router.post(route('locations.store'), {name: locationName.value});
        } catch(e) {
            alert('There was a problem with creating a new location');
            console.log(e);
        }
    } else {
        if(locationId.value === null) return;
        router.visit(route('inventories.create', {location: locationId.value}));
    }
}
</script>

<template>
    <Head title="Create Inventory" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Choose a location
                </h2>
                <Button @click="router.visit(route('inventories.index'))">Back</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 w-1/4">
                        <div class="flex flex-col mb-5">
                            <label for="location-name">New location name</label>
                            <InputText id="location-name" v-model="locationName" :disabled="existingLocation" />
                        </div>

                        <div class="flex flex-col mb-5" v-if="locations.length">
                            <label for="existing-location">Choose an existing location</label>
                            <InputSwitch id="existing-location" v-model="existingLocation" />
                        </div>

                        <div class="flex flex-col mb-5" v-if="existingLocation">
                            <label for="user-locations">Locations</label>
                            <Dropdown id="user-locations" v-model="locationId" :options="locations" optionValue="id" optionLabel="name" placeholder="Select a Location" />
                        </div>

                        <BreezeButton type="button" @click="handleLocation" class="cursor-pointer">
                            Continue
                        </BreezeButton>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
