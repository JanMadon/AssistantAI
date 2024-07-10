<template>
    <div class="relative border border-1 border-gray-200 rounded-md hover:border-purple-600 transition-colors 
    bg-slate-50 text-gray-800 dark:bg-slate-600 dark:text-gray-100">
        <a :href="route('show.product', product.id)"
            class="flex justify-center p-3 aspect-w-3 aspect-h-2 overflow-hidden">
            <img alt="product" class=" object-cover rounded-lg hover:scale-105 hover:rotate-1 transition-transform"
                :src="'data:image/jpeg;base64,' + product.photo">
        </a>

        <div class="px-4 py-10">
            <h3 class="text-lg">
                <a :href="route('show.product', product.id)">
                    {{ product.name }}
                </a>
            </h3>
            <p>
                {{ product.description }}
            </p>
            <h5 class="text-xl font-bold">PLN {{ product.price }}</h5>
        </div>
        <div class="flex justify-between items-end py-1 px-4 absolute bottom-0">
            <AddToCartBtn v-if="$page.props.auth.user" :product="product" :quantity="1" />
            <PrimaryButton v-else @click.prevent="() => router.visit(route('login'))">
                log in to add to cart
            </PrimaryButton>
        </div>

    </div>
</template>

<script setup>
import PrimaryButton from '../PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import AddToCartBtn from './AddToCartBtn.vue';


const emit = defineEmits('addToCart');

const props = defineProps({
    product: Object
})

</script>