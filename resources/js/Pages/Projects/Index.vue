<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";

import { onMounted, ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import {FilterMatchMode, FilterOperator} from "primevue/api";
import {buildQueryUrl} from "@/primevue-datatable-params-builder";
import CrudButton from "@/Components/CrudButton.vue";

const loading = ref(false);

const props = defineProps({
    data: Object,
    sources: Object,
    create_url: String,
})

const editingRows= ref([]);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS},
    'name': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.CONTAINS}]},
});

const lazyParams = ref({});

onMounted(() => {
    lazyParams.value = {
        page: 0,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };
})

function onRowEditSave(event) {
    let { newData, data } = event;

    router.put(route('projects.update', newData.id), newData, {
        replace: false,
        preserveState: true,
        preserveScroll: true,
    });
}

function onShow(id) {
    router.visit(route('projects.show', id));
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
    const url = buildQueryUrl(lazyParams.value, route('projects.index'));
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onSuccess: () => { loading.value = false },
    })
}

</script>

<template>
    <Head title="Projects" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Projects
                </h2>
                <Button @click="router.visit(route('projects.create'))">Create</Button>
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
                            <template #editor="{ data, field }">
                                <InputText v-model="data[field]" />
                            </template>
                            <template #filter="{filterModel}">
                                <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Search by name"/>
                            </template>
                        </Column>
                        <Column :rowEditor="true" style="" bodyStyle="text-align:center; padding: 0"></Column>
                        <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                            <template #body="{data}">
                                <CrudButton type="show" @click="onShow(data.id)" />
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
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>
