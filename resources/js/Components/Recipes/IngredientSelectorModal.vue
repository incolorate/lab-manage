<script setup>
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";

const props = defineProps({
    filteredIngredients: Array,
    addSelectedIngredient: Function,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ro-RO", {
        style: "currency",
        currency: "RON",
        minimumFractionDigits: 2,
    }).format(value || 0);
};

const ingredientDialog = defineModel("ingredientDialog");
</script>

<template>
    <Dialog
        v-model:visible="ingredientDialog.visible"
        header="Select Ingredient"
        :style="{ width: '550px' }"
        modal
    >
        <div class="mb-4 w-full">
            <IconField>
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="ingredientDialog.search"
                    placeholder="Search ingredient..."
                    class="w-full"
                />
            </IconField>
        </div>

        <div class="mb-4 max-h-64 overflow-y-auto">
            <DataTable
                :value="filteredIngredients"
                v-model:selection="ingredientDialog.selected"
                selectionMode="single"
                class="p-datatable-sm"
                :rows="10"
                :scrollable="true"
            >
                <Column selectionMode="single" style="width: 50px"></Column>
                <Column field="name" header="Name"></Column>
                <Column field="supplier.name" header="Supplier"></Column>
                <Column field="price" header="Price/kg">
                    <template #body="{ data }">
                        {{ formatCurrency(data.price) }}
                    </template>
                </Column>
            </DataTable>
        </div>

        <div class="mb-4">
            <label for="ing-concentration" class="block mb-2"
                >Concentration (%)</label
            >
            <InputNumber
                id="ing-concentration"
                v-model="ingredientDialog.concentration"
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
                label="Add to Recipe"
                @click="addSelectedIngredient"
                :disabled="!ingredientDialog.selected"
            />
        </div>
    </Dialog>
</template>
