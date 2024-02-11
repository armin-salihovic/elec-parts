<template>
    <div>
        <ATabs :tabs="tabs" />
        <slot />
    </div>
</template>

<script setup>
import { ref } from "vue";
import ATabs from "@/Components/ATabs.vue";
import { CpuChipIcon, MapPinIcon, DocumentIcon } from '@heroicons/vue/24/outline'

const tabs = ref([
    { name: "Parts", icon: CpuChipIcon, href: route('inventories.index'), route: 'inventories.index', current: false },
    { name: "Locations", icon: MapPinIcon, href: route('locations.index'), route: 'locations.index', current: false },
    { name: "Drafts", icon: DocumentIcon, href: route('inventory-drafts.index'), route: 'inventory-drafts.index', current: false },
]);
</script>

<style scoped>

</style>
