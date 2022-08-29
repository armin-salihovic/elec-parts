<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import Button from "@/Components/Button.vue";
import { Inertia } from '@inertiajs/inertia'

import { AgGridVue } from "ag-grid-vue3";  // the AG Grid Vue Component
import { reactive, onMounted, ref } from "vue";

import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-material.css"; // Optional theme CSS

const gridApi = ref(null); // Optional - for accessing Grid's API

// Obtain API from grid's onGridReady event
const onGridReady = (params) => {
    gridApi.value = params.api;
};

const rowData = reactive({}); // Set rowData to Array of Objects, one Object per Row

// Each Column Definition results in one Column.
const columnDefs = reactive({
    value: [
        {
            field: "name",
            editable: true
        },
    ],
});

// DefaultColDef sets props common to all Columns
const defaultColDef = {
    sortable: true,
    filter: true,
    flex: 1
};

const props = defineProps({
    data: Object,
    create_url: String,
})

const updateRow = (event) => {
    Inertia.put(route('part-sources.update', event.data.id), event.data);
}

onMounted(()=>{
    console.log(props.data)
})
</script>

<template>
    <Head title="Part Inventories" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Part Inventories
                </h2>
                <Button @click="Inertia.visit(route('part-inventories.create'))">Create</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <ag-grid-vue
                            class="ag-theme-material"
                            style="height: 500px"
                            :columnDefs="columnDefs.value"
                            :rowData="data"
                            :defaultColDef="defaultColDef"
                            rowSelection="multiple"
                            animateRows="true"
                            @grid-ready="onGridReady"
                            @cell-value-changed="updateRow"
                        >
                        </ag-grid-vue>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>