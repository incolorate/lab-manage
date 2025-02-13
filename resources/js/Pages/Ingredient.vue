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
import FloatLabel from "primevue/floatlabel";
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
            },
        });
        showCreateIngredientModal.value = false;
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
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <FloatLabel class="mt-5">
                        <InputText
                            id="name"
                            v-model="form.name"
                            class="bg-white text-slate-950"
                        />
                        <label for="name" class="text-slate-600">Name</label>
                    </FloatLabel>
                    <FloatLabel>
                        <InputText
                            id="description"
                            v-model="form.description"
                            class="bg-white text-slate-950"
                        />
                        <label for="description" class="text-slate-600"
                            >Description</label
                        >
                    </FloatLabel>
                    <Select
                        v-model="form.supplier_id"
                        :options="suppliers"
                        filter
                        optionLabel="name"
                        placeholder="Select a supplier"
                        class="w-full md:w-56"
                    >
                        <template #option="slotProps">
                            <div class="flex items-center">
                                <img
                                    :alt="slotProps.option.label"
                                    src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png"
                                    :class="`mr-2 flag flag-${slotProps.option.code.toLowerCase()}`"
                                    style="width: 18px"
                                />
                                <div>{{ slotProps.option.name }}</div>
                            </div>
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
            header="Create ingredient"
            :style="{ width: '25rem' }"
            class="bg-white text-black"
        >
            <form @submit.prevent="submitSupplier" class="flex flex-col gap-6">
                <FloatLabel class="mt-5">
                    <InputText
                        id="name"
                        v-model="supplierForm.name"
                        class="bg-white text-slate-950"
                        :class="{ 'p-invalid': supplierForm.errors.name }"
                    />
                    <label for="name" class="text-slate-600">Name</label>
                    <small class="text-red-500" v-if="supplierForm.errors.name">
                        {{ supplierForm.errors.name }}
                    </small>
                </FloatLabel>

                <FloatLabel>
                    <InputText
                        id="contact_person"
                        v-model="supplierForm.contact_person"
                        class="bg-white text-slate-950"
                        :class="{
                            'p-invalid': supplierForm.errors.contact_person,
                        }"
                    />
                    <label for="contact_person" class="text-slate-600"
                        >Contact Person</label
                    >
                    <small
                        class="text-red-500"
                        v-if="supplierForm.errors.contact_person"
                    >
                        {{ supplierForm.errors.contact_person }}
                    </small>
                </FloatLabel>

                <FloatLabel>
                    <InputText
                        id="phone_number"
                        v-model="supplierForm.phone_number"
                        class="bg-white text-slate-950"
                        :class="{
                            'p-invalid': supplierForm.errors.phone_number,
                        }"
                    />
                    <label for="phone_number" class="text-slate-600"
                        >Phone Number</label
                    >
                    <small
                        class="text-red-500"
                        v-if="supplierForm.errors.phone_number"
                    >
                        {{ supplierForm.errors.phone_number }}
                    </small>
                </FloatLabel>
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
