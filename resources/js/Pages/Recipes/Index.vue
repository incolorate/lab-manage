<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// PrimeVue Components
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";
import Chip from "primevue/chip";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";
import Card from "primevue/card";

// Props
const props = defineProps({
    recipes: {
        type: Array,
        default: () => [],
    },
    allIngredients: {
        type: Array,
        default: () => [],
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

// Services
const toast = useToast();
const confirm = useConfirm();

// Recipe Dialog State
const recipeDialog = ref({
    visible: false,
    isEdit: false,
    editId: null,
});

// Recipe Form
const recipeForm = useForm({
    name: "",
    description: "",
    ingredients: [],
});

// Ingredient Selector Dialog
const ingredientDialog = ref({
    visible: false,
    search: "",
    selected: null,
    concentration: 0.0,
});

// Quick Add Ingredient Dialog
const quickAddDialog = ref({
    visible: false,
    name: "",
    description: "",
    supplier_id: null,
    price: 0.0,
    concentration: 0.0,
    processing: false,
    errors: {},
});

// Filtered ingredients based on search term
const filteredIngredients = computed(() => {
    if (!ingredientDialog.value.search) return props.allIngredients;

    const searchTerm = ingredientDialog.value.search.toLowerCase();
    return props.allIngredients.filter(
        (ing) =>
            ing.name.toLowerCase().includes(searchTerm) ||
            (ing.supplier &&
                ing.supplier.name.toLowerCase().includes(searchTerm))
    );
});

// Calculate price for 100g of recipe
const calculateRecipePrice = computed(() => {
    let total = 0;

    recipeForm.ingredients.forEach((ingredient) => {
        const ingredientData = props.allIngredients.find(
            (i) => i.id === ingredient.id
        );

        if (ingredientData && ingredientData.price) {
            // Calculate price contribution: price * concentration(%) * 0.1 (for 100g)
            const priceContribution =
                ingredientData.price *
                (ingredient.pivot.concentration / 100) *
                0.1;
            total += priceContribution;
        }
    });

    return parseFloat(total.toFixed(2));
});

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("ro-RO", {
        style: "currency",
        currency: "RON",
        minimumFractionDigits: 2,
    }).format(value || 0);
};

// Open the new recipe modal
const openNewRecipeModal = () => {
    recipeDialog.value.isEdit = false;
    recipeDialog.value.editId = null;
    recipeForm.reset();
    recipeDialog.value.visible = true;
};

// Edit an existing recipe
const editRecipe = (recipe) => {
    recipeDialog.value.isEdit = true;
    recipeDialog.value.editId = recipe.id;

    recipeForm.name = recipe.name;
    recipeForm.description = recipe.description;
    recipeForm.ingredients = JSON.parse(JSON.stringify(recipe.ingredients));

    recipeDialog.value.visible = true;
};

// Reset the recipe form
const resetRecipeForm = () => {
    recipeForm.reset();
    recipeDialog.value.isEdit = false;
    recipeDialog.value.editId = null;
};

// Open the ingredient selector dialog
const openIngredientSelector = () => {
    ingredientDialog.value.visible = true;
    ingredientDialog.value.search = "";
    ingredientDialog.value.selected = null;
    ingredientDialog.value.concentration = 0.0;
};

// Open the quick add ingredient dialog
const openQuickAddIngredient = () => {
    quickAddDialog.value.visible = true;
    quickAddDialog.value.name = "";
    quickAddDialog.value.description = "";
    quickAddDialog.value.supplier_id = null;
    quickAddDialog.value.price = 0.0;
    quickAddDialog.value.concentration = 0.0;
    quickAddDialog.value.errors = {};
};

// Add selected ingredient to the recipe
const addSelectedIngredient = () => {
    if (!ingredientDialog.value.selected) return;

    // Check if ingredient already exists in the form
    const existingIndex = recipeForm.ingredients.findIndex(
        (i) => i.id === ingredientDialog.value.selected.id
    );

    if (existingIndex !== -1) {
        toast.add({
            severity: "warn",
            summary: "Ingredient already added",
            detail: "This ingredient is already in the recipe. You can adjust its concentration.",
            life: 3000,
        });
        return;
    }

    // Add ingredient with its concentration
    recipeForm.ingredients.push({
        id: ingredientDialog.value.selected.id,
        name: ingredientDialog.value.selected.name,
        supplier: ingredientDialog.value.selected.supplier,
        price: ingredientDialog.value.selected.price,
        pivot: {
            concentration: ingredientDialog.value.concentration,
        },
    });

    toast.add({
        severity: "success",
        summary: "Ingredient Added",
        detail: `${ingredientDialog.value.selected.name} added to recipe`,
        life: 3000,
    });

    // Close the dialog
    ingredientDialog.value.visible = false;
};

// Create a new ingredient and add it to the recipe
const createAndSelectIngredient = () => {
    // Validate at minimum the name is provided
    if (!quickAddDialog.value.name) {
        quickAddDialog.value.errors.name = "Ingredient name is required";
        return;
    }

    quickAddDialog.value.processing = true;

    // Use Inertia for the quick add
    router.post(
        route("ingredients.quick-add"),
        {
            name: quickAddDialog.value.name,
            description: quickAddDialog.value.description,
            supplier_id: quickAddDialog.value.supplier_id,
            price: quickAddDialog.value.price,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // Get the newly created ingredient from the response
                const newIngredient = page.props.newIngredient;

                // Add to recipe with concentration
                recipeForm.ingredients.push({
                    id: newIngredient.id,
                    name: newIngredient.name,
                    supplier: newIngredient.supplier,
                    price: newIngredient.price,
                    pivot: {
                        concentration: quickAddDialog.value.concentration,
                    },
                });

                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: `Ingredient "${newIngredient.name}" created and added to recipe`,
                    life: 3000,
                });

                // Close dialog and reset
                quickAddDialog.value.visible = false;
                quickAddDialog.value.processing = false;
            },
            onError: (errors) => {
                quickAddDialog.value.errors = errors;
                quickAddDialog.value.processing = false;
            },
        }
    );
};

// Remove ingredient from recipe
const removeIngredient = (index) => {
    recipeForm.ingredients.splice(index, 1);
};

// Save the recipe (create or update)
const saveRecipe = () => {
    if (recipeDialog.value.isEdit) {
        // Update existing recipe
        router.put(
            route("recipes.update", recipeDialog.value.editId),
            recipeForm,
            {
                preserveScroll: true,
                onSuccess: () => {
                    recipeDialog.value.visible = false;
                    toast.add({
                        severity: "success",
                        summary: "Recipe Updated",
                        detail: "Recipe has been updated successfully",
                        life: 3000,
                    });
                },
            }
        );
    } else {
        // Create new recipe
        router.post(route("recipes.store"), recipeForm, {
            preserveScroll: true,
            onSuccess: () => {
                recipeDialog.value.visible = false;
                toast.add({
                    severity: "success",
                    summary: "Recipe Created",
                    detail: "New recipe has been created successfully",
                    life: 3000,
                });
            },
        });
    }
};

// Confirm and delete a recipe
const confirmDelete = (recipe) => {
    confirm.require({
        message: `Are you sure you want to delete "${recipe.name}"?`,
        header: "Delete Confirmation",
        icon: "pi pi-exclamation-triangle",
        accept: () => {
            router.delete(route("recipes.destroy", recipe.id), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Recipe Deleted",
                        detail: "Recipe has been deleted successfully",
                        life: 3000,
                    });
                },
            });
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Recipes</h1>
                <Button
                    label="New Recipe"
                    icon="pi pi-plus"
                    @click="openNewRecipeModal"
                />
            </div>
        </template>
        <div class="p-4">
            <Toast />

            <div class="card">

                <!-- Recipes DataTable -->
                <DataTable
                    :value="recipes"
                    :paginator="true"
                    :rows="10"
                    stripedRows
                    responsiveLayout="scroll"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    currentPageReportTemplate="{first} to {last} of {totalRecords}"
                >
                    <Column field="name" header="Name" sortable></Column>
                    <Column header="Description">
                        <template #body="{ data }">
                            <div class="line-clamp-2">
                                {{ data.description }}
                            </div>
                        </template>
                    </Column>
                    <Column header="Ingredients">
                        <template #body="{ data }">
                            <div class="flex flex-wrap gap-1">
                                <Chip
                                    v-for="ing in data.ingredients"
                                    :key="ing.id"
                                    :label="`${ing.name} (${ing.pivot.concentration}%)`"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column header="Price (per 100g)">
                        <template #body="{ data }">
                            {{ formatCurrency(data.price_per_100g) }}
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 150px">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    class="p-button-sm p-button-info"
                                    @click="editRecipe(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    class="p-button-sm p-button-danger"
                                    @click="confirmDelete(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Recipe Dialog (Create/Edit) -->
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
                            :class="{ 'p-invalid': errors.name }"
                        />
                        <small v-if="errors.name" class="p-error">{{
                            errors.name
                        }}</small>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block mb-2"
                            >Description</label
                        >
                        <Textarea
                            id="description"
                            v-model="recipeForm.description"
                            rows="3"
                            class="w-full"
                            :class="{ 'p-invalid': errors.description }"
                        />
                        <small v-if="errors.description" class="p-error">{{
                            errors.description
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
                                ingredients per kg and their concentration in
                                the recipe.
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
                            <Column
                                field="pivot.concentration"
                                header="Concentration"
                            >
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
                                                (data.pivot.concentration /
                                                    100) *
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
                        <small v-if="errors.ingredients" class="p-error">{{
                            errors.ingredients
                        }}</small>
                        <small
                            v-if="recipeForm.ingredients.length === 0"
                            class="block mt-2 text-gray-500"
                        >
                            No ingredients added yet. Add at least one
                            ingredient.
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

            <!-- Ingredient Selector Dialog -->
            <Dialog
                v-model:visible="ingredientDialog.visible"
                header="Select Ingredient"
                :style="{ width: '550px' }"
                modal
            >
                <div class="mb-4">
                    <span class="p-input-icon-left w-full">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="ingredientDialog.search"
                            placeholder="Search ingredients..."
                            class="w-full"
                        />
                    </span>
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
                        <Column
                            selectionMode="single"
                            style="width: 50px"
                        ></Column>
                        <Column field="name" header="Name"></Column>
                        <Column
                            field="supplier.name"
                            header="Supplier"
                        ></Column>
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

            <!-- Quick Add Ingredient Dialog -->
            <Dialog
                v-model:visible="quickAddDialog.visible"
                header="Quick Add Ingredient"
                :style="{ width: '450px' }"
                modal
            >
                <div class="mb-4">
                    <label for="quick-name" class="block mb-2"
                        >Ingredient Name</label
                    >
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
                    <label for="quick-supplier" class="block mb-2"
                        >Supplier</label
                    >
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
                    <label for="quick-price" class="block mb-2"
                        >Price per kg</label
                    >
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

            <!-- Delete Confirmation Dialog -->
            <ConfirmDialog />
        </div>
    </AuthenticatedLayout>
</template>
