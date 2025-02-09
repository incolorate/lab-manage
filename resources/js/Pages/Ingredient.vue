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

const showCreateIngredientModal = ref(false);
const form = useForm({
    name: null,
    description: null,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function submit() {
    router.post("/ingredients", form);
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
                header="Edit Profile"
                :style="{ width: '25rem' }"
            >
                <form @submit.prevent="submit">
                    <label for="name">Name:</label>
                    <input id="name" v-model="form.name" />
                    <label for="description">Description:</label>
                    <input id="description" v-model="form.description" />
                    <Button type="submit">Submit</Button>
                </form>
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="visible = false"
                ></Button>
                <Button
                    type="button"
                    label="Save"
                    @click="visible = false"
                ></Button>
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
