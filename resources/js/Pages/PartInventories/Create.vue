<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import Button from "@/Components/Button.vue";
import { Inertia } from '@inertiajs/inertia'
import {ref} from "vue";
import BreezeButton from '@/Components/Button.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';

const pdf = ref();
const file = ref(null);

const handleFileUpload = async() => {
    // debugger;
    console.log("selected file",file.value.files)

    Inertia.post(route('part-inventories.store'), file.value.files, {
        forceFormData: true,
    })

}


const form = ref({
    name: '',
});

const submit = () => {
    Inertia.post(route('part-inventories.store'), form.value);
}

</script>

<template>
    <Head title="Part Inventories" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Part Inventories
                </h2>
                <Button @click="Inertia.visit(route('part-inventories.index'))">Back</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <BreezeLabel for="name" value="Name" />
                                <BreezeInput id="name" type="text" class="mt-1 block" v-model="form.name" required autofocus />


                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="file_input">Upload file</label>
                                <input @change="handleFileUpload" ref="file" class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="file_input_help" id="file_input" type="file">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">SVG, PNG, JPG or GIF (MAX. 800x400px).</p>

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
