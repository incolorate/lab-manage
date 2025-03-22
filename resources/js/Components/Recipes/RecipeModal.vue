<script setup>
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Card from "primevue/card";

const props = defineProps({
    openIngredientSelector: Function,
    openQuickAddIngredient: Function,
    calculateRecipePrice: Number,
    saveRecipe: Function,
});
const recipeDialog = defineModel("recipeDialog");
const recipeForm = defineModel("recipeForm");

const resetRecipeForm = () => {
    recipeForm.value.reset();
    recipeDialog.value.isEdit = false;
    recipeDialog.value.editId = null;
};

const removeIngredient = (index) => {
    recipeForm.value.ingredients.splice(index, 1);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ro-RO", {
        style: "currency",
        currency: "RON",
        minimumFractionDigits: 2,
    }).format(value || 0);
};
</script>

<template>
    <Dialog
        v-model:visible="recipeDialog.visible"
        :header="recipeDialog.isEdit ? 'Edit Recipe' : 'New Recipe'"
        :style="{ width: '650px' }"
        modal
        @hide="resetRecipeForm"
    >
        <form @submit.prevent="saveRecipe">
            <div class="mb-4">
                <label for="name" class="block mb-2">Recipe Name</label>
                <InputText
                    id="name"
                    v-model="recipeForm.name"
                    class="w-full"
                    :class="{ 'p-invalid': recipeForm.errors.name }"
                />
                <small v-if="recipeForm.errors.name" class="p-error">{{
                    recipeForm.errors.name
                }}</small>
            </div>

            <div class="mb-4">
                <label for="description" class="block mb-2">Description</label>
                <Textarea
                    id="description"
                    v-model="recipeForm.description"
                    rows="3"
                    class="w-full"
                    :class="{ 'p-invalid': recipeForm.errors.description }"
                />
                <small v-if="recipeForm.errors.description" class="p-error">{{
                    recipeForm.errors.description
                }}</small>
            </div>

            <!-- Price Calculation Card -->
            <Card class="mb-4 bg-blue-50">
                <template #title>
                    <div class="flex justify-between items-center">
                        <span>Price Calculation</span>
                        <span class="text-xl font-bold">{{
                            formatCurrency(calculateRecipePrice)
                        }}</span>
                    </div>
                </template>
                <template #subtitle>
                    <span>Per 100g of recipe</span>
                </template>
                <template #content>
                    <p class="text-sm text-gray-600">
                        This price is calculated based on the cost of
                        ingredients per kg and their concentration in the
                        recipe.
                    </p>
                </template>
            </Card>

            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="font-medium">Ingredients</label>
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            label="Select Ingredient"
                            icon="pi pi-plus"
                            size="small"
                            @click="openIngredientSelector"
                        />
                        <Button
                            type="button"
                            label="Quick Add"
                            icon="pi pi-bolt"
                            class="p-button-secondary"
                            size="small"
                            @click="openQuickAddIngredient"
                        />
                    </div>
                </div>

                <DataTable
                    :value="recipeForm.ingredients"
                    responsiveLayout="scroll"
                >
                    <Column field="name" header="Ingredient"></Column>
                    <Column field="price" header="Price/kg">
                        <template #body="{ data }">
                            {{ formatCurrency(data.price) }}
                        </template>
                    </Column>
                    <Column field="pivot.concentration" header="Concentration">
                        <template #body="{ data, index }">
                            <InputNumber
                                v-model="
                                    recipeForm.ingredients[index].pivot
                                        .concentration
                                "
                                suffix=" %"
                                :min="0"
                                :max="100"
                                :minFractionDigits="1"
                                :maxFractionDigits="1"
                            />
                        </template>
                    </Column>
                    <Column header="Cost in Recipe">
                        <template #body="{ data }">
                            {{
                                formatCurrency(
                                    data.price *
                                        (data.pivot.concentration / 100) *
                                        0.1
                                )
                            }}
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 100px">
                        <template #body="{ index }">
                            <Button
                                icon="pi pi-trash"
                                class="p-button-danger p-button-sm"
                                @click="removeIngredient(index)"
                            />
                        </template>
                    </Column>
                </DataTable>
                <small v-if="recipeForm.errors.ingredients" class="p-error">{{
                    recipeForm.errors.ingredients
                }}</small>
                <small
                    v-if="recipeForm.ingredients.length === 0"
                    class="block mt-2 text-gray-500"
                >
                    No ingredients added yet. Add at least one ingredient.
                </small>
            </div>

            <div class="flex justify-end mt-4">
                <Button
                    type="button"
                    label="Cancel"
                    class="p-button-secondary mr-2"
                    @click="recipeDialog.visible = false"
                />
                <Button
                    type="submit"
                    label="Save"
                    icon="pi pi-save"
                    :loading="recipeForm.processing"
                />
            </div>
        </form>
    </Dialog>
</template>
