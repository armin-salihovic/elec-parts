<script setup>
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';
import ProjectLayout from "@/Layouts/ProjectLayout.vue";

function handleSubmit() {
    router.post(route('projects.builds.store', route().params.project), form.value);
}

defineProps({
    project_name: String,
})

const form = ref({
    quantity: 1,
});

</script>

<style scoped>
.field * {
    display: block;
}
</style>

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
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200 w-1/4">
                                <div class="flex flex-col mb-5">
                                    <label for="quantity">Quantity</label>
                                    <InputText id="quantity" v-model="form.quantity" />
                                </div>

                                <Button type="button" @click="handleSubmit" class="cursor-pointer">
                                    Start
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </ProjectLayout>
        </BreezeAuthenticatedLayout>
    </div>
</template>
