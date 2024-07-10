<template>
    <div class="m-10 text-center ">
            <ul class="inline-flex -space-x-px text-base h-10">
                <li class="flex">
                    <a v-for="(link, index) of items" :href="link.url"
                        class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                        :class="index == 0 ? 'rounded-l-xl' : '', index == (items.length - 1) ? 'rounded-r-xl' : ''">
                        <span v-if="link.label == '&amp;laquo; Previous'">Previous</span>
                        <span v-else-if="link.label === 'Next &amp;raquo;'">Next</span>
                        <span v-else>{{ link.label }}</span>
                    </a>
                </li>
            </ul>
        </div>
</template>

<script setup>

const props = defineProps({
    items: {
        type: Object,
        default: []
    }
})

</script>