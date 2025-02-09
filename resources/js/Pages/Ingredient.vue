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

const showCreateIngredientModal = ref(false);
const form = useForm({
    name: null,
    description: null,
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

defineProps({
    ingredients: Object,
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
                    class="bg-white text-red-200"
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
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
