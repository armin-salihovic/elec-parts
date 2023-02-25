<script setup>
import { router } from '@inertiajs/vue3';
import {onMounted, provide, ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import ADropdown from "@/Components/ADropdown.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import axios from "axios";
import CrudButton from "@/Components/CrudButton.vue";
import Dropdown from 'primevue/dropdown';
import ConfirmDialog from "primevue/confirmdialog";
import {useConfirm} from "primevue/useconfirm";

const confirm = useConfirm();

const form = ref({
    sku: '',
    quantity: 1,
});

const emit = defineEmits(['submit']);

const props = defineProps({
    edit: {
        type: Boolean,
        default: false,
    },
    locations: Object,
    location_id: Number | String,
    parts: Object,
})

const locationId = ref(null);

provide('locationId', locationId);

onMounted(() => {
    locationId.value = Number(props['location_id']);
})

async function addItem() {
    await addProductBySku();
}

function deleteItem(id) {
    router.delete(route('inventories.destroy', id));
}

function changeLocation(inventoryId, locationId) {
    const newLocation = props.locations.find(location => location.id === locationId);

    confirm.require({
        message: 'Transferring this part to location ' + newLocation.name + ' will merge it with any existing parts.',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
            await axios.put(route('inventories.location.update', inventoryId), {location: locationId})
            router.reload({ only: ['parts'] })
            // toast.add({severity:'info', summary:'Confirmed', detail:'You have accepted', life: 3000});
        },
        reject: () => {
            router.reload({ only: ['parts'] })
            // toast.add({severity:'error', summary:'Rejected', detail:'You have rejected', life: 3000});
        }
    });

    // warn toast that the item will be transferred to another bag and merged with any existing items
}

const addProductBySku = async () => {
    return await axios.post(route('sku-part', {draft: route().params.inventory_draft}), {
        sku: form.value.sku,
        location: locationId.value,
        quantity: form.value.quantity,
    }).then(({data}) => {
        router.reload({ only: ['parts'] })
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
            if (isPositiveNumber(newValue)) {
                const oldValue = data[field];
                data[field] = Number(newValue);
                axios
                    .put(route('inventories.update', data['id']), {quantity: Number(newValue)})
                    .then(() => {
                        // toast success
                    }).catch(() => {
                        // toast fail
                        data[field] = oldValue;
                    })
            } else {
                event.preventDefault();
            }
            break;
    }
}

</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent>
                            <div class="flex items-center gap-2">
                                <div v-if="!edit">
                                    <BreezeLabel for="name" value="Name" />
                                    <BreezeInput id="name" type="text" class="mt-1 block" v-model="form.sku" required autofocus />
                                </div>
                                <div v-if="!edit">
                                    <BreezeLabel for="quantity" value="Quantity" />
                                    <BreezeInput id="quantity" type="number" class="mt-1 block" v-model="form.quantity" required/>
                                </div>
                                <div v-if="!edit" class="flex items-center justify-start mt-6">
                                    <BreezeButton @click="addItem(form)">
                                        Add
                                    </BreezeButton>
                                </div>
                                <div class="ml-auto">
                                    <Dropdown v-if="!edit" v-model="locationId" :options="locations" optionValue="id" optionLabel="name" placeholder="Select a Location" />
                                    <ADropdown v-if="!edit" class="ml-5" />
                                    <BreezeButton type="button" @click="emit('submit')" class="ml-5">
                                        {{ edit ? 'Finish' : 'Save' }}
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
                                <Column field="location" header="Location" style="width: 200px">
                                    <template #body="{ data, field }">
                                        <Dropdown v-model="data[field]" :options="locations" optionValue="id" @change="changeLocation(data['id'], data[field])" optionLabel="name" placeholder="Select a Location" />
                                    </template>
                                </Column>
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
    </div>
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
