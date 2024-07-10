<template>
    <div>
        <PrimaryButton class="btn-primary bg-orange-500 hover:bg-orange-600 focus:bg-orange-600" @click="addToCart()">
            Add to Cart
        </PrimaryButton>
    </div>
</template>

<script setup>
import PrimaryButton from '../PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import {showSuccessNotification, showErrorNotification} from '@/event-bus.js';

const props = defineProps({
    product: Object,
    quantity: Number,
})


function addToCart() {
   router.post(route('addToCart'), 
       { //data
           productId: props.product.id,
           quantity: props.quantity
       },
       { // optins
           onSuccess: ()=> {
               showSuccessNotification(`Product: ${props.product.name} has been added to the cart.`)    
           },
           onError: ()=> {
            showErrorNotification(`Failed to add product to cart, try again later.`) 
           }

       }
   );
}

</script>