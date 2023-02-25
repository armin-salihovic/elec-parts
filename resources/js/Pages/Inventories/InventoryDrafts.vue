<script setup>
import { router } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import {FilterMatchMode, FilterOperator} from "primevue/api";
import {buildQueryUrl} from "@/primevue-datatable-params-builder";
import CrudButton from "@/Components/CrudButton.vue";

const loading = ref(false);

const props = defineProps({
    data: Object,
})

const editingRows= ref([]);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS},
    'name': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.CONTAINS}]},
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

    if(newData.name === data.name) return;

    router.put(route('locations.update', newData.id), newData, {
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
    const url = buildQueryUrl(lazyParams.value, route('inventory-drafts.index'));
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onSuccess: () => { loading.value = false },
    })
}

function onDelete(id) {
    router.delete(route('inventory-drafts.destroy', id));
}

function onContinue(id) {
    router.get(route('inventory-drafts.create', id));
}

</script>

<template>
    <div class="py-12" v-if="data">
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
                    <Column field="id" header="ID" :showFilterOperator="false" :max-constraints="1" />
                    <Column field="size" header="Size" filterField="size" dataType="numeric" :showFilterOperator="false" :max-constraints="1" :sortable="true" />
                    <Column field="created_at" header="Created" filterField="created_at" dataType="date" :showFilterOperator="false" :max-constraints="1" :sortable="true" />
                    <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                        <template #body="{data}">
                            <div class="flex gap-10">
                                <CrudButton type="show" @click="onContinue(data.id)" />
                                <CrudButton type="delete" @click="onDelete(data.id)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <Paginator :first="data.from" :rows="data.per_page" :totalRecords="data.total" @page="onPage($event)"/>
            </div>
        </div>
    </div>
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
