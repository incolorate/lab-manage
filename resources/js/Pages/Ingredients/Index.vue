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
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import Checkbox from "primevue/checkbox";
import ImportIngredients from "@/Components/ImportIngredients.vue";

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
                    <Button type="submit" @click="showCreateIngredientModal = true"
                    >Add ingredient</Button
                    >
                    <ImportIngredients />
                </div>
            </div>
        </template>
        <div class="bg-slate-50 text-slate-950 mx-auto w-full p-8">
            <Dialog
                v-model:visible="showCreateIngredientModal"
                modal
                header="Create ingredient"
                :style="{ width: '35rem' }"
            >
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <InputText
                            id="name"
                            placeholder="Name"
                            v-model="form.name"
                            class="bg-white text-slate-950"
                        />
                        <small class="text-red-500" v-if="form.errors.name">
                            {{ form.errors.name }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputText
                            id="inci"
                            placeholder="INCI"
                            v-model="form.inci"
                            class="bg-white text-slate-950"
                        />
                        <small class="text-red-500" v-if="form.errors.inci">
                            {{ form.errors.inci }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputText
                            id="description"
                            placeholder="Description"
                            v-model="form.description"
                            class="bg-white text-slate-950"
                        />
                        <small
                            class="text-red-500"
                            v-if="form.errors.description"
                        >
                            {{ form.errors.description }}
                        </small>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label for="moq">Minimum Order Quantity</label>
                            <InputNumber
                                id="moq"
                                v-model="form.moq"
                                class="bg-white text-slate-950 w-full"
                                minFractionDigits="2"
                                maxFractionDigits="2"
                            />
                            <small class="text-red-500" v-if="form.errors.moq">
                                {{ form.errors.moq }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="price">Price</label>
                            <InputNumber
                                id="price"
                                v-model="form.price"
                                class="bg-white text-slate-950 w-full"
                                mode="currency"
                                currency="EUR"
                                locale="en-EU"
                            />
                            <small
                                class="text-red-500"
                                v-if="form.errors.price"
                            >
                                {{ form.errors.price }}
                            </small>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_sample"
                                v-model="form.is_sample"
                                :binary="true"
                            />
                            <label for="is_sample">Sample</label>
                            <small
                                class="text-red-500"
                                v-if="form.errors.is_sample"
                            >
                                {{ form.errors.is_sample }}
                            </small>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="in_stock"
                                v-model="form.in_stock"
                                :binary="true"
                            />
                            <label for="in_stock">In Stock</label>
                            <small
                                class="text-red-500"
                                v-if="form.errors.in_stock"
                            >
                                {{ form.errors.in_stock }}
                            </small>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="stock_amount">Stock Amount</label>
                        <InputNumber
                            id="stock_amount"
                            v-model="form.stock_amount"
                            class="bg-white text-slate-950"
                            minFractionDigits="2"
                            maxFractionDigits="2"
                        />
                        <small
                            class="text-red-500"
                            v-if="form.errors.stock_amount"
                        >
                            {{ form.errors.stock_amount }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Select
                            v-model="form.supplier_id"
                            :options="suppliers"
                            filter
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select a supplier"
                            class="w-full"
                        >
                            <template #option="slotProps">
                                <div>{{ slotProps.option.name }}</div>
                            </template>
                            <template #footer>
                                <div class="p-3">
                                    <Button
                                        label="Create a new supplier"
                                        fluid
                                        severity="secondary"
                                        text
                                        size="small"
                                        icon="pi pi-plus"
                                        @click="showCreateSupplierModal = true"
                                    />
                                </div>
                            </template>
                        </Select>
                        <small
                            class="text-red-500"
                            v-if="form.errors.supplier_id"
                        >
                            {{ form.errors.supplier_id }}
                        </small>
                    </div>
                </form>
                <div class="flex gap-4 mt-2">
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        @click="showCreateIngredientModal = false"
                    ></Button>
                    <Button type="submit" label="Save" @click="submit"></Button>
                </div>
            </Dialog>

            <!-- Edit Ingredient Modal -->
            <Dialog
                v-model:visible="showEditIngredientModal"
                modal
                header="Edit ingredient"
                :style="{ width: '35rem' }"
            >
                <form @submit.prevent="submitEdit" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <InputText
                            id="edit-name"
                            placeholder="Name"
                            v-model="editForm.name"
                            class="bg-white text-slate-950"
                        />
                        <small class="text-red-500" v-if="editForm.errors.name">
                            {{ editForm.errors.name }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputText
                            id="edit-inci"
                            placeholder="INCI"
                            v-model="editForm.inci"
                            class="bg-white text-slate-950"
                        />
                        <small class="text-red-500" v-if="editForm.errors.inci">
                            {{ editForm.errors.inci }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputText
                            id="edit-description"
                            placeholder="Description"
                            v-model="editForm.description"
                            class="bg-white text-slate-950"
                        />
                        <small
                            class="text-red-500"
                            v-if="editForm.errors.description"
                        >
                            {{ editForm.errors.description }}
                        </small>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label for="edit-moq">Minimum Order Quantity</label>
                            <InputNumber
                                id="edit-moq"
                                v-model="editForm.moq"
                                class="bg-white text-slate-950 w-full"
                                minFractionDigits="2"
                                maxFractionDigits="2"
                            />
                            <small
                                class="text-red-500"
                                v-if="editForm.errors.moq"
                            >
                                {{ editForm.errors.moq }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="edit-price">Price (RON)</label>
                            <InputNumber
                                id="edit-price"
                                v-model="editForm.price"
                                class="bg-white text-slate-950 w-full"
                            />

                            <small
                                class="text-red-500"
                                v-if="editForm.errors.price"
                            >
                                {{ editForm.errors.price }}
                            </small>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="edit-is_sample"
                                v-model="editForm.is_sample"
                                :binary="true"
                            />
                            <label for="edit-is_sample">Sample</label>
                            <small
                                class="text-red-500"
                                v-if="editForm.errors.is_sample"
                            >
                                {{ editForm.errors.is_sample }}
                            </small>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="edit-in_stock"
                                v-model="editForm.in_stock"
                                :binary="true"
                            />
                            <label for="edit-in_stock">In Stock</label>
                            <small
                                class="text-red-500"
                                v-if="editForm.errors.in_stock"
                            >
                                {{ editForm.errors.in_stock }}
                            </small>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="edit-stock_amount">Stock Amount</label>
                        <InputNumber
                            id="edit-stock_amount"
                            v-model="editForm.stock_amount"
                            class="bg-white text-slate-950"
                            minFractionDigits="2"
                            maxFractionDigits="2"
                        />
                        <small
                            class="text-red-500"
                            v-if="editForm.errors.stock_amount"
                        >
                            {{ editForm.errors.stock_amount }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Select
                            v-model="editForm.supplier_id"
                            :options="suppliers"
                            filter
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select a supplier"
                            class="w-full"
                        >
                            <template #option="slotProps">
                                <div>{{ slotProps.option.name }}</div>
                            </template>
                            <template #footer>
                                <div class="p-3">
                                    <Button
                                        label="Create a new supplier"
                                        fluid
                                        severity="secondary"
                                        text
                                        size="small"
                                        icon="pi pi-plus"
                                        @click="showCreateSupplierModal = true"
                                    />
                                </div>
                            </template>
                        </Select>
                        <small
                            class="text-red-500"
                            v-if="editForm.errors.supplier_id"
                        >
                            {{ editForm.errors.supplier_id }}
                        </small>
                    </div>
                </form>
                <div class="flex gap-4 mt-2">
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        @click="showEditIngredientModal = false"
                    ></Button>
                    <Button
                        type="submit"
                        label="Update"
                        @click="submitEdit"
                    ></Button>
                </div>
            </Dialog>
        </div>
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
                    {{ data.stock_amount ? data.stock_amount.toFixed(2) : "-" }}
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

        <Dialog
            v-model:visible="showCreateSupplierModal"
            modal
            header="Create supplier"
            :style="{ width: '25rem' }"
            class="bg-white text-black"
        >
            <form @submit.prevent="submitSupplier" class="flex flex-col gap-6">
                <InputText
                    id="name"
                    placeholder="Name"
                    v-model="supplierForm.name"
                    class="bg-white text-slate-950"
                    :class="{ 'p-invalid': supplierForm.errors.name }"
                />
                <small class="text-red-500" v-if="supplierForm.errors.name">
                    {{ supplierForm.errors.name }}
                </small>

                <InputText
                    id="contact_person"
                    placeholder="Contact person"
                    v-model="supplierForm.contact_person"
                    class="bg-white text-slate-950"
                    :class="{
                        'p-invalid': supplierForm.errors.contact_person,
                    }"
                />
                <small
                    class="text-red-500"
                    v-if="supplierForm.errors.contact_person"
                >
                    {{ supplierForm.errors.contact_person }}
                </small>

                <InputText
                    id="phone_number"
                    placeholder="Phone number"
                    v-model="supplierForm.phone_number"
                    class="bg-white text-slate-950"
                    :class="{
                        'p-invalid': supplierForm.errors.phone_number,
                    }"
                />

                <small
                    class="text-red-500"
                    v-if="supplierForm.errors.phone_number"
                >
                    {{ supplierForm.errors.phone_number }}
                </small>
            </form>

            <template #footer>
                <div class="flex gap-4">
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        @click="showCreateSupplierModal = false"
                    />
                    <Button
                        type="submit"
                        label="Save"
                        :loading="supplierForm.processing"
                        @click="submitSupplier"
                    />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
