<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import {onMounted, ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import Dropdown from "primevue/dropdown";

const props = defineProps({
    part: {
        type: Object,
    },
    categories: {
        type: Object,
    }
})

const form = ref({
    name: '',
    sku: '',
    price: '',
    url: '',
    description: '',
    category_id: null,
});

const selectedCategory = ref({
    id: null,
    name: null,
});

const submit = () => {
    form.value.category_id = selectedCategory.value;
    router.put(route('parts.update', route().params.part), form.value);
};

onMounted(() => {
    form.value = props.part;
    selectedCategory.value = form.value.category_id;
})

</script>

<template>
    <Head title="Edit Part" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Part
                </h2>
                <Button @click="router.visit(route('parts.index'))">Back</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <BreezeLabel for="name" value="Name" />
                                <BreezeInput id="name" type="text" class="mt-1 block" v-model="form.name" required autofocus />
                            </div>
                            <div class="mb-3">
                                <BreezeLabel for="sku" value="SKU" />
                                <BreezeInput id="sku" type="text" class="mt-1 block" v-model="form.sku" />
                            </div>
                            <div class="mb-3">
                                <BreezeLabel for="price" value="Price" />
                                <BreezeInput id="price" type="text" class="mt-1 block" v-model="form.price" />
                            </div>
                            <div class="mb-3">
                                <BreezeLabel for="url" value="URL" />
                                <BreezeInput id="url" type="text" class="mt-1 block" v-model="form.url" />
                            </div>
                            <div class="mb-3">
                                <BreezeLabel for="description" value="Description" />
                                <BreezeInput id="description" type="text" class="mt-1 block" v-model="form.description" />
                            </div>
                            <div class="mb-3">
                                <BreezeLabel for="category" value="Category" />
                                <Dropdown id="category" v-model="selectedCategory" :options="categories" optionLabel="name" optionValue="id" placeholder="Select a category" />
                            </div>

                            <div class="flex items-center justify-start mt-4">
                                <BreezeButton>
                                    Save
                                </BreezeButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
