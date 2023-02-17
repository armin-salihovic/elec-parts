<template>
    <div>
        <Button v-if="type === 'delete'" icon="pi pi-trash" class="p-button-rounded p-button-danger p-button-text custom-size">
            <i class="pi pi-trash"></i>
        </Button>
        <Button v-if="type === 'edit'" icon="pi pi-pencil" class="p-button-rounded p-button-secondary p-button-text custom-size">
            <i class="pi pi-pencil"></i>
        </Button>
        <Button v-if="type === 'show'" icon="pi pi-eye" class="p-button-rounded p-button-text custom-size">
            <i class="pi pi-eye"></i>
        </Button>
        <Button @click="handleLinkClick" v-if="type === 'link'" icon="pi pi-link" class="p-button-rounded p-button-secondary p-button-text custom-size">
            <i class="pi pi-link"></i>
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
    type: {
        type: String,
        required: true,
    },
    url: {
        type: String,
        default: '#'
    }
});

function handleLinkClick() {
    window.open(props.url, '_blank');
}
</script>

<script>
export default {
  name: "CrudButton",
};
</script>
