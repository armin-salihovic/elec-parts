<script setup>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import CrudButton from "@/Components/CrudButton.vue";
import AFormCard from "@/Components/AFormCard.vue";

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

function addProjectPart() {
    router.post(route('project-parts.store', route().params.project), projectPart.value);
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
    router.delete(route('project-parts.destroy', id));
}

</script>

<style scoped>
.field * {
    display: block;
}
</style>

<template>
    <div v-if="data">
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
                <Column :rowEditor="true" style="width:10%; min-width:8rem" bodyStyle="text-align:center; padding: 0" />

                <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                    <template #body="{data}">
                        <CrudButton type="delete" @click="onDelete(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </AFormCard>
    </div>
</template>
