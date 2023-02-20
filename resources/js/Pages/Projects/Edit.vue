<script setup>
import { Head } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import { router } from '@inertiajs/vue3'
import { ref } from "vue";
import ProjectParts from "@/Pages/Projects/ProjectParts.vue";
import ProjectDetails from "@/Pages/Projects/ProjectDetails.vue";
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import ProjectLayout from "@/Layouts/ProjectLayout.vue";

const props = defineProps({
    project_name: String,
    project_details: Object,
    project_parts: Object,
})

const form = ref({
    name: '',
});

const submit = () => {
    router.post(route('projects.store'), form.value);
};
</script>

<template>
    <div>
        <Head title="Project" />
        <BreezeAuthenticatedLayout>
            <template #header>
                <div class="flex justify-between">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ project_name }}
                    </h2>
                    <Button @click="router.visit(route('projects.index'))">Back</Button>
                </div>
            </template>
            <ProjectLayout>
                <div class="py-12">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <ProjectParts :data="project_parts" />
                        <ProjectDetails :data="project_details" />
                    </div>
                </div>
            </ProjectLayout>
        </BreezeAuthenticatedLayout>
    </div>
</template>
