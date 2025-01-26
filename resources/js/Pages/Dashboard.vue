<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import CascadeSelect from "primevue/cascadeselect";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                    <Button label="Verify" />
                </div>
                <CascadeSelect
                    v-model="selectedCity"
                    :options="countries"
                    optionLabel="cname"
                    optionGroupLabel="name"
                    :optionGroupChildren="['states', 'cities']"
                    class="w-56"
                    placeholder="Select a City"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
