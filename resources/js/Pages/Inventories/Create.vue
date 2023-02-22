<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import {provide, ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import ADropdown from "@/Components/ADropdown.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";

import axios from "axios";
import CrudButton from "@/Components/CrudButton.vue";

const parts = ref([]);
provide('addItem', addItem);

const form = ref({
    sku: '',
    quantity: 1,
});

const handleSubmit = () => {
    router.post(route('inventories.store'), {parts: parts.value});
}

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
    str = String(str);
    str = str.trim();
    if (!str) {
        return false;
    }
    str = str.replace(/^0+/, "") || "0";
    const n = Math.floor(Number(str));
    return n !== Infinity && String(n) === str && n >= 0;
}

// datatable

function onCellEditComplete(event) {
    let { data, newValue, field } = event;

    switch (field) {
        case 'quantity':
            if (isPositiveNumber(newValue))
                data[field] = Number(newValue);
            else
                event.preventDefault();
            break;
    }
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
                                    <BreezeButton type="button" @click="handleSubmit" class="ml-5" :disabled="!parts.length">
                                        Save
                                    </BreezeButton>
                                </div>
                            </div>
                        </form>
                        <div style="height: calc(100vh - 400px)">
                            <DataTable :value="parts"
                                       tableStyle="width:auto"
                                       class="mt-5"
                                       editMode="cell"
                                       @cell-edit-complete="onCellEditComplete"
                                       :scrollable="true"
                                       scrollHeight="flex"
                                       height="400px"
                            >
                                <template #empty>
                                    <div class="w-full text-center">No records found.</div>
                                </template>
                                <Column field="name" header="Name">
                                    <template #body="{data}">
                                        <div :title="data['name']" class="whitespace-nowrap overflow-hidden text-ellipsis">
                                            {{ data['name'] }}
                                        </div>
                                    </template>
                                </Column>
                                <Column field="sku" header="SKU"></Column>
                                <Column field="category" header="Category"></Column>
                                <Column field="quantity" header="Quantity" style="width:25%" dataType="numeric" :showFilterOperator="false" :max-constraints="1" :sortable="true">
                                    <template #editor="{ data, field }">
                                        <InputText v-model="data[field]" />
                                    </template>
                                </Column>
                                <Column field="location" header="Location"></Column>
                                <Column header="Delete" bodyStyle="text-align: center; overflow: visible; padding: 0">
                                    <template #body="{data}">
                                        <div class="flex gap-3">
                                            <CrudButton type="delete" @click="deleteItem(data['id'])" />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<style scoped>

::v-deep(.p-datatable-tbody td) {
    white-space: nowrap;
    overflow: hidden !important;
    text-overflow: ellipsis;
}

@media (max-width: 960px) {
    ::v-deep(.p-datatable-table) {
        table-layout: auto !important;
        width: auto !important;
    }
}

::v-deep(td.p-editable-column.p-cell-editing) {
    padding-top: 0;
    padding-bottom: 0;
}

</style>
