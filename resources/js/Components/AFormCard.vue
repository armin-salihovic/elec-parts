<template>
  <div class="bg-white shadow px-4 py-5 rounded-lg sm:p-6 mb-5">
    <div class="space-y-6">
      <div v-if="title" class="flex justify-between">
        <h2>{{ title }}</h2>
        <p v-if="collapsible" @click="toggle" class="text-gray-600 hover:text-gray-700 cursor-pointer">
            <i :class="['pi pi-chevron-down icon-collapse', isOpen ? 'icon-collapse__open' : '']" style="font-size: 1rem"></i>
        </p>
      </div>

      <Transition>
        <div v-show="isOpen">
          <slot></slot>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import {ref} from "vue";

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    collapsible: {
        type: Boolean,
        default: false,
    }
});

const isOpen = ref(!props.collapsible);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.icon-collapse {
    transition: transform 0.5s ease;
}
.icon-collapse__open {
    transform: rotate(180deg);
}

</style>
