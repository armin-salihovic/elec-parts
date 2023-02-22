<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";

import {onMounted, ref} from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import InputNumber from "primevue/inputnumber";
import {FilterMatchMode, FilterOperator} from "primevue/api";
import {buildQueryUrl} from "@/primevue-datatable-params-builder";

const loading = ref(false);

const props = defineProps({
    data: Object,
    sources: Object,
    create_url: String,
})

const editingRows= ref([]);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS},
    'part.name': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.CONTAINS}]},
    'part.sku': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'quantity': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'part.source.name': { value: null, matchMode: FilterMatchMode.IN},
});

const lazyParams = ref({});

onMounted(()=>{
    lazyParams.value = {
        page: 0,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };
})

function onRowEditSave(event) {
    let { newData, data } = event;

    if(Number(newData.quantity) === Number(data.quantity)) return;

    router.put(route('inventories.update', newData.id), newData, {
        replace: false,
        preserveState: true,
        preserveScroll: true,
    });
}

// datatable events

function onFilter() {
    lazyParams.value.filters = filters.value;
    loadLazyData();
}

function onSort(event) {
    lazyParams.value.sortField = event.sortField;
    lazyParams.value.sortOrder = event.sortOrder;
    loadLazyData();
}

function onPage (event) {
    lazyParams.value.page = event.page;
    loadLazyData();
}

// data is lazy loaded whenever a datatable related event fires

function loadLazyData() {
    const url = buildQueryUrl(lazyParams.value, route('inventories.index'));
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onSuccess: () => { loading.value = false },
    })
}

</script>

<template>
    <Head title="Inventories" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Inventories
                </h2>
                <Button @click="router.visit(route('inventories.create'))">Create</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <DataTable
                        :value="data.data"
                        :autoLayout="true"
                        :resizableColumns="true"
                        tableStyle="width:auto"
                        :lazy="true"
                        :loading="loading"
                        filterDisplay="menu"
                        v-model:filters="filters"
                        editMode="row"
                        @row-edit-save="onRowEditSave"
                        dataKey="id"
                        v-model:editingRows="editingRows"
                        @filter="onFilter"
                        @sort="onSort($event)"
                    >
                        <Column field="part.name" header="Name" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                {{data["part.name"]}}
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by name"/>
                            </template>
                        </Column>
                        <Column field="part.sku" header="SKU" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                {{data["part.sku"]}}
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by SKU"/>
                            </template>
                        </Column>
                        <Column field="quantity" header="Quantity" filterField="quantity" dataType="numeric" :showFilterOperator="false" :max-constraints="1" :sortable="true">
                            <template #editor="{ data, field }">
                                <InputText v-model="data[field]" />
                            </template>
                            <template #filter="{filterModel}">
                                <InputNumber v-model="filterModel.value" />
                            </template>
                        </Column>
                        <Column header="Distributor" filterField="part.source.name" :showFilterMatchModes="false" :filterMenuStyle="{'width':'14rem'}" style="min-width:14rem" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
<!--                                <img :alt="data.representative.name" src="https://www.primefaces.org/wp-content/uploads/2020/05/placeholder.png" width="32" style="vertical-align: middle" />-->
                                <span class="image-text">{{data["part.source.name"]}}</span>
                            </template>
                            <template #filter="{filterModel}">
                                <div class="mb-3 font-bold">Distributors</div>
                                <MultiSelect v-model="filterModel.value" :options="sources" optionLabel="name" placeholder="Any" class="p-column-filter">
                                    <template #option="slotProps">
                                        <div class="p-multiselect-representative-option">
<!--                                            <img :alt="slotProps.option.name" src="https://www.primefaces.org/wp-content/uploads/2020/05/placeholder.png" width="32" style="vertical-align: middle" />-->
                                            <span class="image-text">{{slotProps.option.name}}</span>
                                        </div>
                                    </template>
                                </MultiSelect>
                            </template>
                        </Column>
<!--                        <Column field="source" header="Source" ></Column>-->
                        <Column :rowEditor="true" style="width:10%; min-width:8rem" bodyStyle="text-align:center; padding: 0"></Column>
                    </DataTable>

                    <Paginator :first="data.from" :rows="data.per_page" :totalRecords="data.total" @page="onPage($event)"/>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<style scoped>
/*::v-deep(.p-datatable-table) {*/
/*    table-layout: fixed !important;*/
/*    width: 1000px !important;*/
/*}*/

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
