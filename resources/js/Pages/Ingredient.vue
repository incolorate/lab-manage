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

const showCreateIngredientModal = ref(false);
const showCreateSupplierModal = ref(false);
const form = useForm({
    name: null,
    description: null,
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

function submitSupplier() {
    try {
        supplierForm.post("/suppliers", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Ingredients
            </h2>
        </template>
        <div class="bg-slate-50 text-slate-950 mx-auto w-full p-8">
            <Button type="submit" @click="showCreateIngredientModal = true"
                >Add ingredient</Button
            >

            <Dialog
                v-model:visible="showCreateIngredientModal"
                modal
                header="Create ingredient"
                :style="{ width: '25rem' }"
                class="bg-white text-black"
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
                    <div class="flex flex-col gap-1">
                        <Select
                            v-model="form.supplier_id"
                            :options="suppliers"
                            filter
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select a supplier"
                            class="w-full md:w-56"
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
                        <small class="text-red-500" v-if="form.errors.supplier">
                            {{ form.errors.supplier }}
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
        </div>
        <div class="w-full bg-blue-200 flex justify-center">
            <div class="w-full max-w-xl">
                <DataTable
                    v-if="ingredients.length > 0"
                    :value="ingredients"
                    :totalRecords="ingredients.length"
                    tableStyle="max-width: 50rem"
                    paginator
                    :rows="5"
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                    :globalFilterFields="['name', 'description']"
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
                    <Column field="name" header="Name"> </Column>
                    <Column field="description" header="Description"> </Column>
                    <Column field="supplier.name" header="Supplier"> </Column>

                    <Column>
                        <p>X</p>

                        <template #body="{ data }">
                            <Button
                                class="text-red-400"
                                icon="pi  pi-times"
                                @click="deleteIngredient(data.id)"
                                severity="secondary"
                                rounded
                            ></Button>
                        </template>
                    </Column>
                </DataTable>
                <p v-else>No items detected</p>
            </div>
        </div>

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
                        @click="showCreditModal = false"
                    />
                    <Button
                        type="submit"
                        label="Save"
                        :loading="form.processing"
                        @click="submitSupplier"
                    />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
