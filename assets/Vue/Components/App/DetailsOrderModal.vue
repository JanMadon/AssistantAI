<template>
    <Modal :show="showModal" @close="() => showModal = false">
            <div class="min-h-[70vh] flex flex-col justify-between ">
                <div class="flex mt-5 ml-5">
                    <div class="mx-5">
                        <p>Order by: </p>
                        <p>Date payed:  </p>
                        <p>Order Id number:  </p>
                        <p>Status: </p>
                    </div>
                    <div class="font-bold">
                        <p>{{ selectedOrder.user.name }}</p>
                        <p>{{ selectedOrder.payd_at }} </p>
                        <p>{{ selectedOrder.id }} </p>
                        <p>{{ selectedOrder.status }}</p>
                    </div>
                </div>
                <table class="w-full border bg-gray-100 rounded overflow-hidden">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th class="text-gray-900 px-1 p-3 text-center">
                                Products
                            </th>
                            <th class="text-gray-900 px-1 p-3 text-center">
                                Quantity
                            </th>
                            <th class="text-gray-900 px-1 p-3 text-center">
                                Unit price
                            </th>
                            <th class="text-gray-900 px-1 p-3 text-center">
                                Total price
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(detals, index) of selectedOrder.detals_order" :key="index" class="bg-white border-b">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 text-center">
                                {{ detals.product.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 text-center ">
                                {{ detals.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 text-center ">
                                PLN {{ detals.product.price }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 text-center ">
                                PLN {{ detals.quantity * detals.product.price }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex flex-col items-end m-5 ml-5">
                    <div class="w-full flex  justify-between text-center">
                        <div>
                            <p class="font-bold">Shipment: </p>
                            <p> Method: {{ selectedOrder.shiping_method }} </p>
                            <p>
                                Price: PLN
                                 {{ selectedOrder.amount_paid - calculatePrice(selectedOrder.detals_order) }}
                            </p>
                        </div>
                        <p class="font-bold">Final price:
                            <p>PLN {{ selectedOrder.amount_paid }}</p>
                        </p>
                        <div class="font-bold">
                            <p>To address:</p>
                        <a v-if="$page.props.auth.user.level === 'admin'"
                            class="font-normal text-blue-600 cursor-pointer" :href="route('admin.userInfo', selectedOrder.user.id)">
                            Address 
                        </a>
                        <a v-else
                            class="font-normal text-blue-600 cursor-pointer" :href="route('profile.edit')">
                            Address 
                        </a>
                        </div>
                    </div>
                </div>
                <div>
                </div>
            </div>
        </Modal>
</template>

<script setup>
    import Modal from '../Modal.vue';

    const props = defineProps({
        showModal: Boolean,
        selectedOrder: Object
    })

    function calculatePrice(detalsOrder) {
        let sumPriceOrder = 0;
        for (const detal of detalsOrder) {
            sumPriceOrder += (detal.product.price * detal.quantity)
        }
        return sumPriceOrder
    }

</script>