<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { FilterMatchMode } from "@primevue/core/api";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import { useToast } from "primevue/usetoast";
import ImportIngredients from "@/Components/ImportIngredients.vue";
import IngredientModal from "@/Components/Ingredients/IngredientModal.vue";
import IngredientSupplierModal from "@/Components/Ingredients/IngredientSupplierModal.vue";

const showCreateIngredientModal = ref(false);
const showEditIngredientModal = ref(false);
const showCreateSupplierModal = ref(false);
const form = useForm({
    name: null,
    inci: null,
    description: null,
    moq: null,
    price: null,
    is_sample: false,
    in_stock: false,
    stock_amount: null,
    supplier_id: null,
});

const editForm = useForm({
    id: null,
    name: null,
    inci: null,
    description: null,
    moq: null,
    price: null,
    is_sample: false,
    in_stock: false,
    stock_amount: null,
    supplier_id: null,
});

const supplierForm = useForm({
    name: "",
    contact_person: "",
    phone_number: "",
    return_to: "ingredients.index",
});

const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function submit() {
    try {
        form.post("/ingredients", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                toast.add({
                    severity: "success",
                    summary: "Ingredient created",
                    life: 3000,
                });
                showCreateIngredientModal.value = false;
            },
        });
    } catch (error) {
        console.log(error);
    }
}

function submitEdit() {
    try {
        editForm.put(`/ingredients/${editForm.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                editForm.reset();
                toast.add({
                    severity: "success",
                    summary: "Ingredient updated",
                    life: 3000,
                });
                showEditIngredientModal.value = false;
            },
        });
    } catch (error) {
        console.log(error);
    }
}

function openEditModal(ingredient) {
    editForm.id = ingredient.id;
    editForm.name = ingredient.name;
    editForm.inci = ingredient.inci;
    editForm.description = ingredient.description;
    editForm.moq = ingredient.moq;
    editForm.price = ingredient.price;
    editForm.is_sample = ingredient.is_sample;
    editForm.in_stock = ingredient.in_stock;
    editForm.stock_amount = ingredient.stock_amount;
    editForm.supplier_id = ingredient.supplier_id;
    showEditIngredientModal.value = true;
}

function submitSupplier() {
    try {
        supplierForm.post("/suppliers", {
            preserveScroll: true,
            onSuccess: () => {
                supplierForm.reset();
                toast.add({
                    severity: "success",
                    summary: "Supplier created",
                    life: 3000,
                });
                showCreateSupplierModal.value = false;
            },
        });
    } catch (error) {
        console.log(error);
    }
}

const deleteIngredient = (id) => {
    router.delete(`/ingredients/${id}`, {
        preserveScroll: true,
    });
};

defineProps({
    ingredients: Object,
    suppliers: Object,
});
</script>

<template>
    <Head title="Ingredients" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Ingredients
                </h2>
                <div class="space-x-4">
                    <Button
                        type="submit"
                        label="Add ingredient"
                        icon="pi pi-plus"
                        @click="showCreateIngredientModal = true"
                    />
                    <ImportIngredients />
                </div>
            </div>
        </template>
        <DataTable
            v-if="ingredients.length > 0"
            :value="ingredients"
            :totalRecords="ingredients.length"
            paginator
            :rows="10"
            :rowsPerPageOptions="[5, 10, 20, 50]"
            :globalFilterFields="['name', 'inci', 'description']"
            dataKey="id"
            filterDisplay="row"
            v-model:filters="filters"
        >
            <template #header>
                <div class="flex justify-end">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Keyword Search"
                        />
                    </IconField>
                </div>
            </template>
            <Column field="name" header="Name"></Column>
            <Column field="inci" header="INCI"></Column>
            <Column field="description" header="Description"></Column>
            <Column field="price" header="Price">
                <template #body="{ data }">
                    {{ data.price ? data.price : "-" }}
                </template>
            </Column>
            <Column field="in_stock" header="Stock">
                <template #body="{ data }">
                    <span
                        v-if="data.in_stock"
                        class="pi pi-check text-green-500"
                    ></span>
                    <span v-else class="pi pi-times text-red-500"></span>
                </template>
            </Column>
            <Column field="is_sample" header="Sample">
                <template #body="{ data }">
                    <span
                        v-if="data.is_sample"
                        class="pi pi-check text-green-500"
                    ></span>
                    <span v-else class="pi pi-times text-red-500"></span>
                </template>
            </Column>
            <Column field="stock_amount" header="Amount">
                <template #body="{ data }">
                    {{ data.stock_amount ? data.stock_amount : "-" }}
                </template>
            </Column>
            <Column field="supplier.name" header="Supplier"></Column>

            <Column header="Actions">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            @click="openEditModal(data)"
                            severity="info"
                            rounded
                        ></Button>
                        <Button
                            icon="pi pi-times"
                            @click="deleteIngredient(data.id)"
                            severity="danger"
                            rounded
                        ></Button>
                    </div>
                </template>
            </Column>
        </DataTable>
        <p v-else>No items detected</p>

        <!-- Modals -->
        <IngredientSupplierModal
            v-model:show-modal="showCreateSupplierModal"
            v-model:form="supplierForm"
            v-model:submit="submitSupplier"
        />
        <IngredientModal
            v-model:form="form"
            v-model:show-modal="showCreateIngredientModal"
            v-model:submit="submit"
            v-model:show-cascade-modal="showCreateSupplierModal"
            :suppliers="suppliers"
        />
        <IngredientModal
            v-model:form="editForm"
            v-model:show-modal="showEditIngredientModal"
            v-model:submit="submitEdit"
            :suppliers="suppliers"
        />
    </AuthenticatedLayout>
</template>
