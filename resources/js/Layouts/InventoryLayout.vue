<template>
    <div>
        <ATabs :tabs="tabs" />
        <slot />
    </div>
</template>

<script setup>
import { ref } from "vue";
import ATabs from "@/Components/ATabs.vue";

const tabs = ref([
    { name: "Parts", href: route('inventories.index'), route: 'inventories.index', current: false },
    { name: "Locations", href: route('locations.index'), route: 'locations.index', current: false },
    { name: "Drafts", href: route('inventory-drafts.index'), route: 'inventory-drafts.index', current: false },
]);
</script>

<style scoped>

</style>
