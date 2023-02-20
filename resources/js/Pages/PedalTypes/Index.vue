<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";

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

// Example load data from sever
// onMounted(() => {
//     fetch("https://www.ag-grid.com/example-assets/row-data.json")
//         .then((result) => result.json())
//         .then((remoteRowData) => (rowData.value = remoteRowData));
// });

const cellWasClicked = (event) => { // Example of consuming Grid Event
    // console.log("cell was clicked", event);
}

const deselectRows = () =>{
    gridApi.value.deselectAll()
}

const props = defineProps({
    pedal_types: Object,
    create_url: String,
})

const updateRow = (event) => {
    router.put(route('pedal-types.update', event.data.id), event.data);
}

onMounted(()=>{
    // console.log(props.pedal_types)
})
</script>

<template>
    <Head title="Pedal Types" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pedal Types
                </h2>
                <Button @click="router.visit(route('pedal-types.create'))">Create</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <button @click="deselectRows">deselect rows</button>
                    </div>
                    <ag-grid-vue
                        class="ag-theme-material"
                        style="height: 500px"
                        :columnDefs="columnDefs.value"
                        :rowData="pedal_types"
                        :defaultColDef="defaultColDef"
                        rowSelection="multiple"
                        animateRows="true"
                        @cell-clicked="cellWasClicked"
                        @grid-ready="onGridReady"
                        @cell-value-changed="updateRow"
                    >
                    </ag-grid-vue>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
