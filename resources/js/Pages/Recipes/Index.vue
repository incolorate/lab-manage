<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import RecipeModal from "@/Components/Recipes/RecipeModal.vue";
import IngredientSelectorModal from "@/Components/Recipes/IngredientSelectorModal.vue";
import QuickAddIngredientModal from "@/Components/Recipes/QuickAddIngredientModal.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Chip from "primevue/chip";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

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
});

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

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ro-RO", {
        style: "currency",
        currency: "RON",
        minimumFractionDigits: 2,
    }).format(value || 0);
};

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
            preserveState: true,
            preserveScroll: true,
            preserveUrl: true,
            only: ["newIngredient"],
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

                props.allIngredients.push({
                    id: newIngredient.id,
                    name: newIngredient.name,
                    supplier: newIngredient.supplier,
                    price: newIngredient.price,
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

// Save the recipe (create or update)
const saveRecipe = () => {
    if (recipeDialog.value.isEdit) {
        // Update existing recipe
        recipeForm.put(route("recipes.update", recipeDialog.value.editId), {
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
        });
    } else {
        recipeForm.post(route("recipes.store"), {
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
    <Head title="Recipes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Recipes
                </h2>
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

            <RecipeModal
                v-model:recipe-dialog="recipeDialog"
                v-model:recipe-form="recipeForm"
                :save-recipe="saveRecipe"
                :open-ingredient-selector="openIngredientSelector"
                :open-quick-add-ingredient="openQuickAddIngredient"
                :calculate-recipe-price="calculateRecipePrice"
            />

            <IngredientSelectorModal
                v-model:ingredient-dialog="ingredientDialog"
                :filtered-ingredients="filteredIngredients"
                :add-selected-ingredient="addSelectedIngredient"
            />

            <QuickAddIngredientModal
                v-model:quick-add-dialog="quickAddDialog"
                :suppliers="suppliers"
                :create-and-select-ingredient="createAndSelectIngredient"
            />
            <!-- Ingredient Selector Dialog -->

            <!-- Quick Add Ingredient Dialog -->

            <!-- Delete Confirmation Dialog -->
            <ConfirmDialog />
        </div>
    </AuthenticatedLayout>
</template>
