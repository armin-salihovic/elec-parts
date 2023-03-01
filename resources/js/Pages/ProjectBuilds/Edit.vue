<script setup>
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';
import ProjectLayout from "@/Layouts/ProjectLayout.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import axios from "axios";

const props = defineProps({
    project_name: String,
    project_parts: Object,
    project_build: Object,
})

const expandedRows = ref([]);

function onRowExpand (event) {
    console.log(event.data)

    axios.get(route('match-bom-to-parts', event.data.id)).then(({data}) => {
        event.data.matched_parts = data;
        event.data.matched_parts_loading = false;
    });
}

function onRowCollapse (event) {
    console.log(event)
}

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
                                    <div>Quantity: {{ project_build.quantity }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-12">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h2 class="text-lg mb-3">Bill of Materials (BOM)</h2>
                                <DataTable
                                    :value="project_parts"
                                    :autoLayout="true"
                                    :resizableColumns="true"
                                    tableStyle="width:auto"
                                    :lazy="true"
                                    filterDisplay="menu"
                                    editMode="row"
                                    dataKey="id"
                                    :scrollable="true"
                                    v-model:expandedRows="expandedRows"
                                    @rowExpand="onRowExpand"
                                    @rowCollapse="onRowCollapse"
                                >
                                    <Column :expander="true" headerStyle="width: 3rem" />
                                    <Column field="part_name" header="Part">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" placeholder="Part name" />
                                        </template>
                                    </Column>
                                    <Column field="quantity" header="Quantity">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" placeholder="Quantity" />
                                        </template>
                                    </Column>
                                    <Column field="description" header="Description">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" placeholder="Description" />
                                        </template>
                                    </Column>
                                    <Column field="designators" header="Designators">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" placeholder="Designators" />
                                        </template>
                                    </Column>
                                    <template #expansion="slotProps">
                                        <div class="w-full p-4">
                                            <h3 class="text-base mb-3">Matching parts for <span class="font-bold">{{slotProps.data.part_name}}</span></h3>
                                            <DataTable :value="slotProps.data.matched_parts" :autoLayout="true" tableStyle="width:auto" responsiveLayout="scroll" :loading="slotProps.data.matched_parts_loading">
                                                <template #empty>
                                                    <div class="w-full text-center">No parts could be matched.</div>
                                                </template>
                                                <Column headerStyle="width:2rem">
                                                    <template #body>
                                                        <Button>Select</Button>
                                                    </template>
                                                </Column>
                                                <Column field="name" header="Name" />
                                                <Column field="sku" header="SKU" />
                                                <Column field="quantity" header="Quantity" />
                                                <Column field="source" header="Source" />
                                                <Column field="location" header="Location" />
                                            </DataTable>
                                        </div>
                                    </template>
                                </DataTable>
                            </div>
                        </div>
                    </div>
                </div>
            </ProjectLayout>
        </BreezeAuthenticatedLayout>
    </div>
</template>
