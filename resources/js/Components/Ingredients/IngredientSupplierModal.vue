<script setup>
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";

const showCreateSupplierModal = defineModel("showModal");
const supplierForm = defineModel("form");
const submitSupplier = defineModel("submit");
</script>

<template>
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

            <small class="text-red-500" v-if="supplierForm.errors.phone_number">
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
</template>
