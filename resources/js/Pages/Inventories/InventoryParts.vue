<script setup>
import { router } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import InputNumber from "primevue/inputnumber";
import Dropdown from 'primevue/dropdown';
import {FilterMatchMode, FilterOperator} from "primevue/api";
import {buildQueryUrl} from "@/primevue-datatable-params-builder";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from "primevue/toast";
import {useConfirm} from "primevue/useconfirm";
import CrudButton from "@/Components/CrudButton.vue";
import {useToast} from "primevue/usetoast";

const confirm = useConfirm();
const toast = useToast();

const loading = ref(false);

const props = defineProps({
    data: Object,
    sources: Object,
    locations: Object,
})

const editingRows= ref([]);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS},
    'part.name': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.CONTAINS}]},
    'part.sku': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'quantity': { operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.EQUALS}]},
    'part.source.name': { value: null, matchMode: FilterMatchMode.IN},
    'location.name': { value: null, matchMode: FilterMatchMode.IN},
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

function changeLocation(inventory, locationId, column) {
    const newLocation = props.locations.find(location => location.id === locationId);

    confirm.require({
        message: 'Transferring this part to location ' + newLocation.name + ' will merge it with any existing parts.',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
            await axios.put(route('inventories.location.update', inventory['id']), {location: locationId})
            router.reload({ only: ['data'] })
            toast.add({severity:'success', summary:'Success', detail:'The item(s) have been transferred', life: 3000});
        },
        reject: () => {
            router.reload({ only: ['data'] })
            toast.add({severity:'error', summary:'Error', detail:'There was an error with your request', life: 3000});
        }
    });

    // warn toast that the item will be transferred to another bag and merged with any existing items
}

function onDelete(inventoryPart) {
    console.log(inventoryPart)
    confirm.require({
        message: `Are you sure you want to delete part ${inventoryPart['part.name']} from your inventory?`,
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('inventories.destroy', inventoryPart.id), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Part deleted.', life: 3000 });
                },
                onError: (data) => {
                    toast.add({ severity: 'error', summary: 'Cannot delete', detail: data.message, life: 3000 });
                }
            });
        },
    });
}

</script>

<template>
    <div>
        <Toast />
        <ConfirmDialog />
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
                        <template #empty>
                            <div class="text-center">No records found.</div>
                        </template>
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
                                <span class="image-text">{{data["part.source.name"]}}</span>
                            </template>
                            <template #filter="{filterModel}">
                                <div class="mb-3 font-bold">Distributors</div>
                                <MultiSelect v-model="filterModel.value" :options="sources" optionLabel="name" placeholder="Any" />
                            </template>
                        </Column>
                        <Column header="Location" filterField="location.name" :showFilterMatchModes="false" :filterMenuStyle="{'width':'14rem'}" style="min-width:14rem" :showFilterOperator="false" :max-constraints="1">
                            <template #body="{data}">
                                <span class="image-text">{{data["location.name"]}}</span>
                            </template>
                            <template #filter="{filterModel}">
                                <div class="mb-3 font-bold">Locations</div>
                                <MultiSelect v-model="filterModel.value" :options="locations" optionLabel="name" placeholder="Any"/>
                            </template>
                            <template #editor="{ data, field }">
                                <Dropdown v-model="data['location.id']" :options="locations" optionValue="id" @change="changeLocation(data, data['location.id'])" optionLabel="name" placeholder="Select a Location" />
                            </template>
                        </Column>
                        <Column :rowEditor="true" style="width:10%; min-width:8rem" bodyStyle="text-align:center; padding: 0"></Column>
                        <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                            <template #body="{data}">
                                <div class="flex gap-10">
                                    <CrudButton type="delete" @click="onDelete(data)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <Paginator :first="data.from" :rows="data.per_page" :totalRecords="data.total" @page="onPage($event)"/>
                </div>
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
