<template >
    <Modal :show="warehouseModal" @close="() => warehouseModal = false" >
        <div class="h-2/3 dark:text-gray-100">
            <h3 class="m-10 text-2xl font-bold text-center">e-shop KRAKÓW</h3>
            <div class="m-10 text-center">
                <p>Warehouse:</p>
                <p>ul. Reymonta 20</p>
                <p>30-059 Kraków</p>
                <p>The store is open from Monday to Friday from 8 a.m. to 8 p.m</p>
                <SecondaryButton @click="() => warehouseModal=false " class="mt-10">
                    Close
                </SecondaryButton>
            </div>
        </div>
    </Modal>
    <Modal :show="addressModal"  @close="() => addressModal = false">
        <UserAddress :closeBtn="true" @closeAction="() => addressModal = false" class="p-10"/>
    </Modal>
    <Modal :show="false">
        modal3
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '../SecondaryButton.vue';
import { ref } from 'vue';
import { watch } from 'vue';
import UserAddress from '@/Pages/Profile/Partials/UserAddress.vue';

const props = defineProps({
    typeModal: String,
    showModalAgain: Boolean
})

const warehouseModal = ref(false)
const lockerModal = ref(false)
const addressModal = ref(false)

watch(() => [props.typeModal, props.showModalAgain], showMoadal);

function showMoadal() {
    switch (props.typeModal) {
        case "locker":
        case "lockerFree":
            lockerModal.value = true
            break;

        case "curier":
            addressModal.value = true
            break;

        case "self":
            warehouseModal.value = true
            break;
    }
}

</script>