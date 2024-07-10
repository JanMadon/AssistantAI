<template>
    <div class="px-10 w-1/3 h-[40px] flex items-center ">
        <TextInput type="text" 
                    class="block w-full h-2/3 mr-2" 
                    v-model="search" 
                    autocomplete 
                    @keyup.enter.prevent="onSearch"
                    :placeholder="placeholder" />
    </div>
</template>

<script setup>
import TextInput from '@/Components/TextInput.vue';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { ref } from 'vue';

const props = defineProps({
    placeholder: String
})
// Uses:
let params = ''

// ref:
const search = ref('');

function onSearch() {
    
    params.set('search', search.value)
    router.get(window.location.pathname + '?' + params.toString())

}

onMounted(() => {
    params = new URLSearchParams(window.location.search)
    if (params.get('search')) {
        search.value = params.get('search')
    }
})

</script>