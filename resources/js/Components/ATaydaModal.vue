<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-10">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0" @afterLeave="resetModal">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                            <div v-if="!processingComplete" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <ArrowDownTrayIcon class="h-6 w-6 text-black-600" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900"> Tayda order import </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">Import the items using the invoice PDF. The PDF can be downloaded from the orders section in your account.</p>
                                        </div>
                                        <div class="mt-3">
                                            <input type="file" id="file" name="file" ref="file" :disabled="processing" @change="errors = []">
                                        </div>
                                        <div v-if="errors.length !== 0" class="mt-1">
                                            <span v-for="error in errors" class="text-sm text-red-600"> {{ error }} </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <CheckIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900"> Success </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">The invoice PDF was successfully imported to the current inventory creation list. </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button v-if="processing && !processingComplete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-900 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm" disabled>
                                    <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </button>
                                <button v-else-if="!processing && !processingComplete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-900 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm" @click="upload">Import</button>
                                <button v-else-if="processingComplete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-700 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" @click="open = false">Done</button>
                                <button v-if="!processing" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="open = false" ref="cancelButtonRef">Cancel</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {inject, ref} from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ArrowDownTrayIcon, CheckIcon } from '@heroicons/vue/24/outline'
import {router} from "@inertiajs/vue3";

const open = ref(false);

const processing = ref(false);
const processingComplete = ref(false);

const file = ref(null);

const locationId = inject('locationId');

const errors = ref([]);

defineExpose({
    open
});

function resetModal() {
    processing.value = false;
    processingComplete.value = false;
}

const upload = async() => {
    if(file.value.files.length === 0) {
        if(errors.value.length === 0) {
            errors.value.push("Please select the invoice PDF")
        }
        return;
    }
    processing.value = true;

    axios.post(route('tayda-pdf-to-products'), {
        taydaInvoice: file.value.files[0],
        locationId: locationId.value,
        draftId: route().params.inventory_draft,
    }, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(({data}) => {
        router.reload({ only: ['parts'] })
        processingComplete.value = true;
    })

}
</script>
