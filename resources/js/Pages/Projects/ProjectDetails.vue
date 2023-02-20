<template>
<div v-if="data">
    <AFormCard>
        <div class="flex gap-64 mb-10">
            <div>
                <h2 class="font-semibold">BOM Entries:</h2>
                <p>{{ data.bom_entries }}</p>
            </div>
            <div>
                <h2 class="font-semibold">Last modified:</h2>
                <p>{{ data.created_at }}</p>
            </div>
            <div>
                <h2 class="font-semibold">Created:</h2>
                <p>{{ data.updated_at }}</p>
            </div>
        </div>
        <div>
            <div class="flex gap-5 items-center">
                <h2 class="font-semibold">Description</h2>
                <i class="pi pi-pencil cursor-pointer" style="font-size: 1rem" @click="onEdit"></i>
            </div>

            <p v-if="!editing">{{ data.description }}</p>

            <div v-else class="flex flex-col">
                <Textarea v-model="project.description"></Textarea>
                <div class="flex gap-1 mt-1 justify-end">
                    <Button @click="editing = false">Cancel</Button>
                    <Button @click="editDescription">Save</Button>
                </div>
            </div>
        </div>
    </AFormCard>
</div>
</template>

<script setup>
import AFormCard from "@/Components/AFormCard.vue";
import {ref} from "vue";
import Textarea from 'primevue/textarea';
import Button from "@/Components/Button.vue"
import {router} from "@inertiajs/vue3";

const props = defineProps({
    data: Object,
})

const editing = ref(false);

const project = ref({
    description: '',
});

function onEdit() {
    project.value.description = props.data.description;
    editing.value = true;
}

function editDescription() {
    router.put(route('projects-description.update', route().params.project), project.value);
    editing.value = false;
}
</script>
