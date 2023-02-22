<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import {provide, reactive, ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import ADropdown from "@/Components/ADropdown.vue";
import CircleButtonCellRenderer from "@/Components/CircleButtonCellRenderer.vue";

import { AgGridVue } from "ag-grid-vue3";  // the AG Grid Vue Component
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-material.css"; // Optional theme CSS
import axios from "axios";

const parts = ref([]);
provide('addItem', addItem);

const form = ref({
    sku: '',
    quantity: 1,
});

const handleSubmit = () => {
    router.post(route('inventories.store'), {parts: parts.value});
}

const gridApi = ref(null); // Optional - for accessing Grid's API

// Obtain API from grid's onGridReady event
const onGridReady = (params) => {
    gridApi.value = params.api;
};

const rowData = reactive({}); // Set rowData to Array of Objects, one Object per Row

// Each Column Definition results in one Column.
const columnDefs = reactive({
    value: [
        { field: "name" },
        { field: "sku" },
        { field: "category" },
        {
            field: "quantity",
            valueGetter: params => {
                return params.data.quantity;
            },
            valueSetter: params => {
                if(isPositiveNumber(params.newValue)) {
                    params.data.quantity = Number(params.newValue);
                    return true;
                }
                alert("problem");
                return false;
            },
            editable: true,
        },
        { field: "source" },
        {
            field: "Delete",
            cellRenderer: CircleButtonCellRenderer,
            cellRendererParams: {
                clicked: function(field) {
                    deleteItem(field.data.id);
                }
            }
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
    part: Object,
})

async function addItem(part) {
    const index = parts.value.findIndex((x) => x.sku === part.sku);

    if (index === -1) {
        if (part.name === undefined) {
            await fetchProductBySku();
        } else {
            part.quantity = Number(part.quantity);
            parts.value.push(part);
        }
    } else {
        parts.value[index].quantity += Number(part.quantity);
    }

    gridApi.value.setRowData(parts.value);

}

function deleteItem(id) {
    parts.value = parts.value.filter((part) => { return part.id !== id });
}

const fetchProductBySku = async () => {
    return await axios.get(`${route('sku-part')}?sku=${form.value.sku}`).then(({data}) => {
        const part = data.data;

        part.quantity = Number(form.value.quantity);

        parts.value.push(part);
    })
}

function isPositiveNumber(str) {
    str = str.trim();
    if (!str) {
        return false;
    }
    str = str.replace(/^0+/, "") || "0";
    const n = Math.floor(Number(str));
    return n !== Infinity && String(n) === str && n >= 0;
}

</script>

<template>
    <Head title="Create Inventory" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Inventory
                </h2>
                <Button @click="router.visit(route('inventories.index'))">Back</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent>
                            <div class="flex items-center gap-2">
                                <div>
                                    <BreezeLabel for="name" value="Name" />
                                    <BreezeInput id="name" type="text" class="mt-1 block" v-model="form.sku" required autofocus />
                                </div>
                                <div>
                                    <BreezeLabel for="quantity" value="Quantity" />
                                    <BreezeInput id="quantity" type="number" class="mt-1 block" v-model="form.quantity" required/>
                                </div>
                                <div class="flex items-center justify-start mt-6">
                                    <BreezeButton @click="addItem(form)">
                                        Add
                                    </BreezeButton>
                                </div>
                                <div class="ml-auto">
                                    <ADropdown />
                                    <BreezeButton @click="handleSubmit" class="ml-5" :disabled="!parts.length">
                                        Save
                                    </BreezeButton>
                                </div>
                            </div>
                        </form>

                        <ag-grid-vue
                            v-if="parts"
                            class="ag-theme-material"
                            style="height: 500px"
                            :columnDefs="columnDefs.value"
                            :rowData="parts"
                            :defaultColDef="defaultColDef"
                            rowSelection="multiple"
                            animateRows="true"
                            @grid-ready="onGridReady"
                        >
                        </ag-grid-vue>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
