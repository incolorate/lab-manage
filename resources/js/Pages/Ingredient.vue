<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import CascadeSelect from "primevue/cascadeselect";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";

const form = useForm({
    name: null,
    description: null,
});

function submit() {
    router.post("/ingredients", form);
}

defineProps({
    ingredients: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Ingredients
            </h2>
        </template>

        <div class="py-12 text-slate-900">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <label for="name">Name:</label>
                    <input id="name" v-model="form.name" />
                    <label for="description">Description:</label>
                    <input id="description" v-model="form.description" />
                    <Button type="submit">Submit</Button>
                </form>
            </div>
        </div>
        <div class="w-full items-center">
            <DataTable :value="ingredients.data" tableStyle="max-width: 50rem">
                >
                <Column field="name" header="Name"></Column>
                <Column field="description" header="Description"></Column>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>
