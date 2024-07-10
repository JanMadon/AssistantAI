<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-5 py-3  bg-slate-800 dark:border-indigo-600 text-white font-bold leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out hover:bg-slate-800'
        : 'inline-flex items-center px-5 py-3 dark:border-indigo-600 text-white font-thin leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out hover:bg-slate-800'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
