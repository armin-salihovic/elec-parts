<script setup>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import CrudButton from "@/Components/CrudButton.vue";
import AFormCard from "@/Components/AFormCard.vue";

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
    router.delete(route('project-parts.destroy', id));
}

function onShow(id) {
    router.visit(route('projects.builds.show', [route().params.project, id]));
}

</script>

<style scoped>
.field * {
    display: block;
}
</style>

<template>
    <div v-if="data">
        <AFormCard>
            <Button class="mb-4" @click="router.visit(route('projects.builds.create', route().params.project))">Start a build</Button>
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
                <Column field="completed" header="Status">
                    <template #body="{data}">
                        {{ data['completed'] ? 'Finished' : 'In progress' }}
                    </template>
                </Column>
                <Column field="created_at" header="Created at"/>

                <Column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible; padding: 0">
                    <template #body="{data}">
                        <CrudButton type="delete" @click="onDelete(data.id)" />
                        <CrudButton type="show" @click="onShow(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </AFormCard>
    </div>
</template>
