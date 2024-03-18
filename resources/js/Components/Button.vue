<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    color: {
        type: String,
        default: 'gray',
    },
});

const colors = {
    gray: 'text-white bg-gray-800 focus:border-gray-900 focus:shadow-outline-gray hover:bg-gray-700 active:bg-gray-900',
    orange: 'text-white bg-orange-800 focus:border-orange-900 focus:shadow-outline-orange hover:bg-orange-700 active:bg-orange-900'
}
</script>

<template>
<!--    text-white bg-gray-800 focus:border-gray-900 focus:shadow-outline-gray hover:bg-gray-700 active:bg-gray-900-->
<!--    text-white bg-orange-800 focus:border-orange-900 focus:shadow-outline-orange hover:bg-orange-700 active:bg-orange-900-->
    <button :type="type" :class="colors[color] + ' inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none transition ease-in-out duration-150'">
        <slot />
    </button>
</template>
