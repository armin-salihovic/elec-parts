<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import InventoryPartsForm from "@/Components/InventoryPartsForm.vue";

const handleSubmit = () => {
    router.post(route('inventories.store', route().params.inventory_draft));
}

const props = defineProps({
    locations: Object,
    location_id: Number | String,
    parts: Object,
})

</script>

<template>
    <Head title="Create Inventory" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Inventory
                </h2>
                <Button @click="router.visit(route('inventories.index'))">Back</Button>
            </div>
        </template>

        <InventoryPartsForm
            @submit="handleSubmit"
            :locations="locations"
            :location_id="location_id"
            :parts="parts"
        />
    </BreezeAuthenticatedLayout>
</template>
