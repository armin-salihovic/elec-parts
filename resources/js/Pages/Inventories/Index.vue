<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import InventoryLayout from "@/Layouts/InventoryLayout.vue";
import InventoryParts from "@/Pages/Inventories/InventoryParts.vue";
import InventoryLocations from "@/Pages/Inventories/InventoryLocations.vue";
import InventoryDrafts from "@/Pages/Inventories/InventoryDrafts.vue";

const props = defineProps({
    data: Object,
    sources: Object,
    locations: Object,
    inventory_drafts: Object,
})
</script>

<template>
    <Head title="Inventories" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Inventories
                </h2>
                <Button @click="router.visit(route('inventories.create'))">Create</Button>
            </div>
        </template>
        <InventoryLayout>
            <InventoryParts :data="data" :sources="sources" :locations="locations" />
            <InventoryLocations :data="locations" v-if="!sources" />
            <InventoryDrafts :data="inventory_drafts" v-if="!sources" />
        </InventoryLayout>
    </BreezeAuthenticatedLayout>
</template>
