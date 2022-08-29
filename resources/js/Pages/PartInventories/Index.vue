<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import Button from "@/Components/Button.vue";
import { Inertia } from '@inertiajs/inertia'

import { reactive, onMounted, ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";

const loading = ref(false);
const page = ref(2);

const props = defineProps({
    data: Object,
    create_url: String,
})

onMounted(()=>{
    console.log(props.data.total);
    console.log(props.data)
})

function onPage (event) {
    console.log(event.page+1);
    Inertia.visit(route('part-inventories.index'), {
        data: {
            page: event.page+1,
        },
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onSuccess: () => { loading.value = false },
    })
}

function onRowEditSave(event) {
    let { newData, index } = event;

    Inertia.put(route('part-inventories.update', newData.id), newData, {
        preserveState: true,
        preserveScroll: true,
    });
}

const editingRows= ref([]);
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
                    <DataTable
                        :value="data.data"
                        :autoLayout="true"
                        :resizableColumns="true"
                        tableStyle="width:auto"
                        :lazy="true"
                        :loading="loading"
                        editMode="row"
                        @row-edit-save="onRowEditSave"
                        dataKey="id"
                        v-model:editingRows="editingRows"
                    >
                        <Column field="name" header="Name"></Column>
                        <Column field="sku" header="SKU" ></Column>
                        <Column field="quantity" header="Quantity" >
                            <template #editor="{ data, field }">
                                <InputText v-model="data[field]" />
                            </template>
                        </Column>
                        <Column field="source" header="Source" ></Column>
                        <Column :rowEditor="true" style="width:10%; min-width:8rem" bodyStyle="text-align:center; padding: 0"></Column>
                    </DataTable>

                    <Paginator :first="data.from" :rows="data.per_page" :totalRecords="data.total" @page="onPage($event)"/>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<style scoped>
::v-deep(.p-datatable-table) {
    table-layout: fixed !important;
    width: 1000px !important;
}

::v-deep(.p-datatable-tbody td) {
    white-space: nowrap;
    overflow: hidden !important;
    text-overflow: ellipsis;
}

::v-deep(.editable-cells-table td.p-cell-editing) {
    padding-top: 0;
    padding-bottom: 0;
}


</style>
