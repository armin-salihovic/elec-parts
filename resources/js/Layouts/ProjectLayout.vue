<template>
    <div>
        <ATabs :tabs="tabs" />
        <slot />
    </div>
</template>

<script setup>
import { ref } from "vue";
import ATabs from "@/Components/ATabs.vue";
import { WrenchIcon, Bars3Icon, TagIcon } from '@heroicons/vue/24/outline'


const tabs = ref([
    { name: "Project Data", icon: TagIcon, href: route('projects.show', route().params.project), route: 'projects.show', current: false },
    { name: "BOM", icon: Bars3Icon, href: route('project-parts.index', route().params.project), route: 'project-parts.index', current: false },
    { name: "Builds", icon: WrenchIcon, href: route('project-builds.index', route().params.project), route: 'project-builds.index', current: false },
]);
</script>

<style scoped>

</style>
