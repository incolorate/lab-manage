<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";

const props = defineProps({
    suppliers: Array,
    createAndSelectIngredient: Function,
});
const quickAddDialog = defineModel("quickAddDialog");
</script>

<template>
    <Dialog
        v-model:visible="quickAddDialog.visible"
        header="Quick Add Ingredient"
        :style="{ width: '450px' }"
        modal
    >
        <div class="mb-4">
            <label for="quick-name" class="block mb-2">Ingredient Name</label>
            <InputText
                id="quick-name"
                v-model="quickAddDialog.name"
                class="w-full"
                :class="{ 'p-invalid': quickAddDialog.errors.name }"
            />
            <small v-if="quickAddDialog.errors.name" class="p-error">{{
                quickAddDialog.errors.name
            }}</small>
        </div>

        <div class="mb-4">
            <label for="quick-description" class="block mb-2"
                >Description</label
            >
            <Textarea
                id="quick-description"
                v-model="quickAddDialog.description"
                rows="2"
                class="w-full"
            />
        </div>

        <div class="mb-4">
            <label for="quick-supplier" class="block mb-2">Supplier</label>
            <Dropdown
                id="quick-supplier"
                v-model="quickAddDialog.supplier_id"
                :options="suppliers"
                optionLabel="name"
                optionValue="id"
                placeholder="Select a supplier (optional)"
                class="w-full"
            />
        </div>

        <div class="mb-4">
            <label for="quick-price" class="block mb-2">Price per kg</label>
            <InputNumber
                id="quick-price"
                v-model="quickAddDialog.price"
                mode="currency"
                currency="USD"
                :minFractionDigits="2"
                :maxFractionDigits="2"
                class="w-full"
            />
        </div>

        <div class="mb-4">
            <label for="quick-concentration" class="block mb-2"
                >Concentration in Recipe (%)</label
            >
            <InputNumber
                id="quick-concentration"
                v-model="quickAddDialog.concentration"
                suffix=" %"
                :min="0"
                :max="100"
                :minFractionDigits="1"
                :maxFractionDigits="1"
                class="w-full"
            />
        </div>

        <div class="flex justify-end">
            <Button
                type="button"
                label="Cancel"
                class="p-button-secondary mr-2"
                @click="quickAddDialog.visible = false"
            />
            <Button
                label="Add & Select"
                @click="createAndSelectIngredient"
                :disabled="!quickAddDialog.name"
                :loading="quickAddDialog.processing"
            />
        </div>
    </Dialog>
</template>
