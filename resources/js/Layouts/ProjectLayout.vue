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
    { name: "Project Data", href: route('projects.show', route().params.project), route: 'projects.show', current: false },
    { name: "BOM", href: route('project-parts.index', route().params.project), route: 'project-parts.index', current: false },
    { name: "Builds", href: route('projects.builds.index', route().params.project), route: 'projects.builds.index', current: false },
]);
</script>

<style scoped>

</style>
