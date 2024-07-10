<template>
    <div class=" ">
        <label for="">Show on page: </label>
        <select name="" id="" :value="perPage" @input.prevent="(event)=> onSearch(event)"
            class="py-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="25">25</option>
        </select>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import { onMounted } from 'vue';
import { ref } from 'vue';

let params = ''

const perPage = ref(10);


function onSearch(event) {
    params.set('perPage', event.target.value)
    router.get(window.location.pathname + '?' + params.toString())
}

onMounted(() => {
    params = new URLSearchParams(window.location.search)
    if(params.get('perPage')) {
        perPage.value = params.get('perPage')
    }
})

</script>