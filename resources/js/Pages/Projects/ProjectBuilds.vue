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
import ConfirmDialog from "primevue/confirmdialog";
import {useConfirm} from "primevue/useconfirm";

const toast = useToast();
const confirm = useConfirm();

const editingRows= ref([]);

const props = defineProps({
    data: Object,
})

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

    confirm.require({
        message: 'This build is already finished. Deleting it will restock all the used parts. Are you sure you want to continue?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('project-builds.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Build deleted.', life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Error', detail: 'Unexpected error occurred', life: 3000 });
                }
            });
        },
    });
}

function onShow(id) {
    router.visit(route('project-builds.edit', [route().params.project, id]));
}

</script>

<style scoped>
.field * {
    display: block;
}
</style>

<template>
    <div v-if="data">
        <Toast />
        <ConfirmDialog />
        <AFormCard>
            <Button class="mb-4" @click="router.visit(route('project-builds.create', route().params.project))">Start a build</Button>
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
                <Column field="completed" header="Status">
                    <template #body="{data}">
                        {{ data['completed'] ? 'Finished' : 'In progress' }}
                    </template>
                </Column>
                <Column field="created_at" header="Created at"/>
                <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                    <template #body="{data}">
                        <CrudButton v-if="data['completed']" type="show" @click="onShow(data.id)" />
                        <CrudButton v-else type="continue" @click="onShow(data.id)" />
                        <CrudButton type="delete" @click="onDelete(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </AFormCard>
    </div>
</template>
