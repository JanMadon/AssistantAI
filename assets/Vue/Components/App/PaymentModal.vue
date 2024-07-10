<template>
    <Modal :show="showModal">
        <div v-if="modalCase === 'accepted'" class="flex flex-col bg-white p-10 rounded-md shadow-md">
            <div class="flex items-center justify-center mb-4 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-14 h-14">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <p class="m-10 text-center text-green-600">Your payment has been successful!</p>
            <div class="text-center">
                <SecondaryButton @click="closeModal">cancel</SecondaryButton>
            </div>
        </div>

        <div v-if="modalCase === 'rejected'" class="flex flex-col bg-white p-10 rounded-md shadow-md">
            <div class="flex items-center justify-center mb-4 text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-14 h-14">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>

            </div>
            <p class="m-10 text-center text-red-600">Your payment has failed</p>
            <div class="text-center">
                <SecondaryButton @click="closeModal">cancel</SecondaryButton>
            </div>
        </div>

    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../Modal.vue';
import SecondaryButton from '../SecondaryButton.vue';


const props = defineProps({
    modalCase: String
})

const showModal = ref(props.modalCase)

const closeModal = () => {
    const url = new URL(window.location.href)
    url.search = ''
    window.location.href = url.href 
}

</script>