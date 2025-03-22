<script setup>
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import Checkbox from "primevue/checkbox";

const props = defineProps({
    submit: Function,
    suppliers: Array,
});

const form = defineModel("form");
const showModal = defineModel("showModal");
const showCreateSupplierModal = defineModel("showCascadeModal");
</script>

<template>
    <Dialog
        v-model:visible="showModal"
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
                <small class="text-red-500" v-if="form.errors.description">
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
                        currency="RON"
                    />
                    <small class="text-red-500" v-if="form.errors.price">
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
                    <small class="text-red-500" v-if="form.errors.is_sample">
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
                    <small class="text-red-500" v-if="form.errors.in_stock">
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
                />
                <small class="text-red-500" v-if="form.errors.stock_amount">
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
                <small class="text-red-500" v-if="form.errors.supplier_id">
                    {{ form.errors.supplier_id }}
                </small>
            </div>
        </form>
        <div class="flex gap-4 mt-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="showModal = false"
            ></Button>
            <Button type="submit" label="Save" @click="submit"></Button>
        </div>
    </Dialog>
</template>
