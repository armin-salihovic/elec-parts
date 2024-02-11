<template>
  <AFormCard>
      <div class="sm:hidden">
          <label for="tabs" class="sr-only">Select a tab</label>
          <select id="tabs" class="block w-full focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md" v-model="selectedTab" @change="onTabSelect">
              <option v-for="tab in tabs" :key="tab.name" :value="tab">{{ tab.name }}</option>
          </select>
      </div>
      <div class="hidden sm:block">
          <div class="border-b border-gray-200">
              <nav class="-mb-px flex" aria-label="Tabs">
                  <Link
                      v-for="tab in tabs"
                      :key="tab.name"
                      :href="tab.href"
                      :class="[
                          tab.route === route().current() ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                          'py-4 px-5 text-center border-b-2 font-medium text-sm cursor-pointer',
                      ]"
                      :aria-current="tab.route === route().current() ? 'page' : undefined"
                  >
                      <span class="flex items-center"><component :is="tab.icon" class="h-6 w-6 mr-1"></component> <span>{{ tab.name }}</span></span>
                  </Link>
              </nav>
          </div>
      </div>
  </AFormCard>
</template>

<script setup>
import AFormCard from "@/components/AFormCard.vue";
import {Link, router} from '@inertiajs/vue3';
import {onMounted, ref} from "vue";

const props = defineProps({
    tabs: {
        type: Object,
        required: true,
    },
});

// for mobile

const selectedTab = ref();

onMounted(() => {
    initializeSelectedTab();
});

function onTabSelect() {
    router.visit(route(selectedTab.value.route, route().params.project));
    initializeSelectedTab();
}

function initializeSelectedTab() {
    for (const tab of props.tabs) {
        if (tab.route === route().current()) {
            selectedTab.value = tab;
            break;
        }
    }
}
</script>
