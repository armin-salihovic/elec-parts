<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";

import { onMounted, ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import {FilterMatchMode, FilterOperator} from "primevue/api";
import {buildQueryUrl} from "@/primevue-datatable-params-builder";
import CrudButton from "@/Components/CrudButton.vue";
import InputNumber from "primevue/inputnumber";

const loading = ref(false);

const props = defineProps({
    data: Object,
    categories: Object,
    create_url: String,
})

const editingRows= ref([]);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS},
    'name': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.CONTAINS}]},
    'sku': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'description': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'price': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'category.name': { value: null, matchMode: FilterMatchMode.IN},
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

    router.put(route('parts.update', newData.id), newData, {
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
    const url = buildQueryUrl(lazyParams.value, route('parts.index'));
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onSuccess: () => { loading.value = false },
    })
}

</script>

<template>
    <Head title="Parts" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Parts
                </h2>
                <Button @click="router.visit(route('parts.create'))">Create</Button>
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
                        <template #empty>
                            <div class="text-center">No records found.</div>
                        </template>
                        <Column field="name" header="Name" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                {{data["name"]}}
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by name"/>
                            </template>
                        </Column>
                        <Column field="sku" header="SKU" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                {{data["sku"]}}
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by SKU"/>
                            </template>
                        </Column>
                        <Column field="description" header="Description" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                {{data["description"]}}
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by description"/>
                            </template>
                        </Column>
                        <Column header="Category" filterField="category.name" :showFilterMatchModes="false" :filterMenuStyle="{'width':'14rem'}" style="min-width:14rem" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                <span class="image-text">{{data["category.name"]}}</span>
                            </template>
                            <template #filter="{filterModel}">
                                <div class="mb-3 font-bold">Categories</div>
                                <MultiSelect v-model="filterModel.value" :options="categories" optionLabel="name" placeholder="Any" class="p-column-filter">
                                    <template #option="slotProps">
                                        <div class="p-multiselect-representative-option">
                                            <span class="image-text">{{slotProps.option.name}}</span>
                                        </div>
                                    </template>
                                </MultiSelect>
                            </template>
                        </Column>
                        <Column field="price" header="Price" filterField="price" dataType="numeric" :showFilterOperator="false" :max-constraints="1" :sortable="true">
                            <template #editor="{ data, field }">
                                <InputText v-model="data[field]" />
                            </template>
                            <template #filter="{filterModel}">
                                <InputNumber v-model="filterModel.value" />
                            </template>
                        </Column>
                        <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                            <template #body="{data}">
                                <div class="flex gap-3">
                                    <CrudButton type="link" :url="data['url']" />
                                    <CrudButton type="edit" @click="router.visit(route('parts.edit', data['id']))" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <Paginator :first="data.from" :rows="data.per_page" :totalRecords="data.total" @page="onPage($event)"/>
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
