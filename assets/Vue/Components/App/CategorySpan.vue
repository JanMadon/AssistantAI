<script setup>
import { computed } from 'vue';

const props = defineProps({
    active: {
        type: Boolean,
    },
    show: {
        type: Boolean
    }
});

const classes = computed(() =>
    props.active
        ? 'flex items-center px-5 py-2 mr-5  rounded-lg border-b border-blue-900 font-bold leading-5 text-blue-900 bg-gray-200 focus:outline-none focus:border-blue-900 transition duration-150 ease-in-out cursor-pointer'
        : 'flex items-center px-4 py-2 rounded-lg border-2 border-transparent font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-ou cursor-pointer'
);
</script>

<template>
    <span v-show="show" :class="classes">
        <slot />
    </span>
</template>
