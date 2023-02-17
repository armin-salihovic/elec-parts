<template>
    <div class="flex items-center h-full">
        <Button @click="handleClick" icon="pi pi-trash" class="p-button-rounded p-button-danger p-button-text custom-size">
            <i class="pi pi-trash"></i>
        </Button>
    </div>
</template>

<style>
.custom-size {
    height: 2rem !important;
    width: 2rem !important;
}
.custom-size > i {
    font-size: 14px;
}
</style>

<script setup>
import Button from 'primevue/button';

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
});

function handleClick() {
    props.params.clicked(props.params);
}
</script>

<script>
export default {
  name: "CircleButtonCellRenderer",
};
</script>
