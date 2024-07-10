<template>
    <div class="flex text-white bg-slate-700 w-full pl-10">
        <NavLink :href="route('home')" >
                Home
            </NavLink>
        <div v-for="(category, index) in categories"
        :id="index"
        @mouseover="(event) => showSubCategories(event)" 
        @mouseleave="subCategory = 'false'"
        :class="{ hover: showList }"
        >
        <NavLink :href="route('home', category.name)" :active="category.name == $page.props.category">
            {{ category.name }}
        </NavLink>
        <ul v-if="subCategory == index" class="absolute flex flex-col text-white bg-slate-700 w-fit z-10">
            <NavLink v-for="(subCategory, index) in category.sub_category"
                 :href="route('home', [category.name, subCategory.name])">
                {{subCategory.name}}
            </NavLink>
        </ul>
    </div>
</div>

</template>

<script setup>
//@click="() => visit(category.name)"
import NavLink from '../NavLink.vue';
import axios from 'axios';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { onBeforeMount } from 'vue';

const showList = ref(false)
const categories = ref();
const subCategory = ref();

const visit = (name) => {
    router.get(route('home', {'category' : name}))
    console.log(name)
}

const showSubCategories = event => {
    subCategory.value = event.currentTarget.id
}
// spróbujmy dodać zakłatki pobierając je z backendu;

const getCategory = () => {
    axios.post(route('category.get'), { data: 'zapytanie o kategorie' })
        .then(response => {
            categories.value = response.data
        })
        .catch(error => console.log(error))
}

const getCategoryInertia = () => {
    router.post(route('category.get'), { data: 'zapytanie o kategorie' })
}



onMounted(() => {
    //console.log('Funkcja po zmaontowaniu');

})

onBeforeMount(() => {
    getCategory()
    // Ta funkcja zostanie wykonana przed zamontowaniem komponentu
    //console.log('Funkcja przedMontowaniem została wywołana');
})



</script>