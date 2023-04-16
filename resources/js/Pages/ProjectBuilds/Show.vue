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
import RadioButton from "primevue/radiobutton";

const props = defineProps({
    project_name: String,
    project_parts: Object,
    project_build: Object,
})

const form = ref({
    selection_order: 'FIFO',
})

const expandedRows = ref([]);

function onRowExpand (event) {
    loadProjectParts(event.data);
}

function onRowCollapse (event) {
    console.log(event)
}

function isLoaded(projectPart) {
    return projectPart['inventory_quantity'] >= projectPart['quantity'] * props.project_build['quantity'];
}

function loadProjectParts(projectPart) {
    const uri = route('projects.builds.parts.draft', [route().params.project, route().params.project_build, projectPart['id']]);

    axios.get(uri).then(({data}) => {
        projectPart.matched_parts = data;
        projectPart.matched_parts_loading = false;
    });
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
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <div class="flex flex-col mb-5">
                                    <div>Quantity: {{ project_build.quantity }}</div>
                                </div>
                                <div class="flex justify-between">
                                    <div>
                                        <label>Selection order:</label>
                                        <div class="flex gap-4 mt-2">
                                            <div class="flex items-center gap-2">
                                                <RadioButton inputId="fifo" name="selection_order" :value="0" v-model="project_build['selection_priority']" disabled />
                                                <label for="fifo">FIFO</label>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <RadioButton inputId="lifo" name="selection_order" :value="1" v-model="project_build['selection_priority']" disabled />
                                                <label for="lifo">LIFO</label>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <RadioButton inputId="smallest-stock" name="selection_order" :value="2" v-model="project_build['selection_priority']" disabled />
                                                <label for="smallest-stock">Smallest stock</label>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <RadioButton inputId="largest-stock" name="selection_order" :value="3" v-model="project_build['selection_priority']" disabled />
                                                <label for="largest-stock">Largest stock</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="self-end">
                                        <Button v-if="!project_build['completed']" @click="router.post(route('projects.builds.build', [route().params.project, route().params.project_build]))">Build</Button>
                                        <Button v-else @click="router.post(route('projects.builds.undo-build', [route().params.project, route().params.project_build]))">Undo Build</Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-12">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h2 class="text-lg mb-3">Review Project Build BOM</h2>
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
                                    <Column header="Loaded">
                                        <template #body="{data}">
                                            <i v-if="isLoaded(data)" class="pi pi-check-circle text-green-600" style="font-size: 1.5rem"></i>
                                            <i v-else class="pi pi-info-circle text-orange-600" style="font-size: 1.5rem"></i>
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
                                            <h3 class="text-base mb-3">Selected parts for <span class="font-bold">{{slotProps.data.part_name}}</span></h3>
                                            <DataTable :value="slotProps.data.matched_parts" :autoLayout="true" tableStyle="width:auto" responsiveLayout="scroll" :loading="slotProps.data.matched_parts_loading">
                                                <template #empty>
                                                    <div class="w-full text-center">No parts could be matched.</div>
                                                </template>
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
