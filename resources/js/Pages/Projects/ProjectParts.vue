<script setup>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import CrudButton from "@/Components/CrudButton.vue";
import AFormCard from "@/Components/AFormCard.vue";
import {useToast} from "primevue/usetoast";
import Toast from "primevue/toast";

const toast = useToast();

const projectPart = ref({
    quantity: '',
    part_name: '',
    description: '',
    designators: '',
});

const editingRows= ref([]);

const props = defineProps({
    data: Object,
})

async function addProjectPart() {
    await router.post(route('project-parts.store', route().params.project), projectPart.value, {
        preserveScroll: true,
    });

    projectPart.value.quantity = '';
    projectPart.value.part_name = '';
    projectPart.value.description = '';
    projectPart.value.designators = '';
}

function onRowEditSave(event) {
    let { newData, data } = event;

    // todo: check and don't send request if there are no changes

    router.put(route('project-parts.update', newData.id), newData, {
        replace: false,
        preserveState: true,
        preserveScroll: true,
    });
}

function onDelete(id) {
    router.delete(route('project-parts.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Part deleted.', life: 3000 });
        },
        onError: (data) => {
            toast.add({ severity: 'error', summary: 'Error', detail: data.message, life: 3000 });
        }
    });
}

</script>

<style scoped>
.field * {
    display: block;
}
</style>

<template>
    <div v-if="data">
        <Toast/>
        <AFormCard title="Add new part" :collapsible="true" class="mb-5">
            <div class="flex gap-5">
                <div class="field">
                    <label for="quantity" class="text-gray-500 text-sm">Quantity</label>
                    <InputText id="quantity" v-model="projectPart.quantity" type="text" class="p-inputtext-sm" aria-describedby="username1-help" />
                </div>
                <div class="field">
                    <label for="part_name" class="text-gray-500 text-sm">Part name</label>
                    <InputText id="part_name" v-model="projectPart.part_name" type="text" class="p-inputtext-sm" aria-describedby="username1-help" />
                </div>
                <div class="field">
                    <label for="description" class="text-gray-500 text-sm">Description</label>
                    <InputText id="description" v-model="projectPart.description" type="text" class="p-inputtext-sm" aria-describedby="username1-help" />
                </div>
                <div class="field">
                    <label for="designators" class="text-gray-500 text-sm">Designators</label>
                    <InputText id="designators" v-model="projectPart.designators" type="text" class="p-inputtext-sm" aria-describedby="username1-help" />
                </div>
                <div class="field self-end mb-1">
                    <Button @click="addProjectPart">Add</Button>
                </div>
            </div>
        </AFormCard>
        <AFormCard>
            <DataTable
                :value="data"
                :autoLayout="true"
                :resizableColumns="true"
                tableStyle="width:auto"
                :lazy="true"
                filterDisplay="menu"
                editMode="row"
                @row-edit-save="onRowEditSave"
                dataKey="id"
                v-model:editingRows="editingRows"
                :scrollable="true"
                scrollHeight="400px"
            >
                <template #empty>
                    <div class="text-center">No records found.</div>
                </template>
                <Column field="quantity" header="Quantity">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" placeholder="Quantity" />
                    </template>
                </Column>
                <Column field="part_name" header="Part">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" placeholder="Part name" />
                    </template>
                </Column>
                <Column field="description" header="Description">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" placeholder="Description" />
                    </template>
                </Column>
                <Column field="designators" header="Designators">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" placeholder="Designators" />
                    </template>
                </Column>
                <Column :rowEditor="true" bodyStyle="padding: 0; display: flex; justify-content: end;" />
                <Column bodyStyle="overflow: visible; padding: 0">
                    <template #body="{data}">
                        <CrudButton type="delete" @click="onDelete(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </AFormCard>
    </div>
</template>
