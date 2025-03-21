<script setup>
import Button from "primevue/button";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import Dialog from "primevue/dialog";

const showImportModal = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);
const isUploading = ref(false);
const importError = ref(null);
const importResult = ref(null);

function handleFileSelected(event) {
    const file = event.target.files[0];
    if (file && file.type === "text/csv") {
        selectedFile.value = file;
        importError.value = null;
    } else {
        importError.value = "Please select a valid CSV file";
        selectedFile.value = null;
    }
}

function uploadFile() {
    if (!selectedFile.value) return;

    importError.value = null;
    importResult.value = null;
    isUploading.value = true;

    const formData = new FormData();
    formData.append("csv_file", selectedFile.value);

    axios
        .post("/ingredients/import", formData)
        .then((response) => {
            importResult.value = response.data.result;
            isUploading.value = false;
        })
        .catch((error) => {
            importError.value = error.response?.data?.error || "Import failed";
            isUploading.value = false;
        });
}
</script>

<template>
    <Button type="button" @click="showImportModal = true" severity="secondary">
        Import ingredients
    </Button>

    <Dialog
        v-model:visible="showImportModal"
        modal
        header="Import ingredients"
        :style="{ width: '30rem' }"
    >
        <div class="flex flex-col gap-4">
            <div class="text-sm">
                Upload a CSV file to import multiple ingredients at once. The
                file should have headers matching the ingredient fields.
            </div>

            <div class="flex flex-col gap-2">
                <label for="csv-file" class="font-medium"
                    >Select CSV file</label
                >
                <input
                    type="file"
                    id="csv-file"
                    ref="fileInput"
                    accept=".csv"
                    class="block w-full text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    @change="handleFileSelected"
                />
                <small v-if="selectedFile" class="text-sm text-gray-600">
                    Selected file: {{ selectedFile.name }}
                </small>
                <small v-if="importError" class="text-sm text-red-500">
                    {{ importError }}
                </small>
            </div>

            <div v-if="isUploading" class="text-center py-4">
                <i class="pi pi-spin pi-spinner text-2xl"></i>
                <div class="mt-2">Processing...</div>
            </div>

            <div
                v-if="importResult"
                class="text-sm border-l-4 border-green-500 bg-green-50 p-4"
            >
                <div><strong>Import complete:</strong></div>
                <div>Total records: {{ importResult.total }}</div>
                <div>Successfully imported: {{ importResult.success }}</div>
                <div v-if="importResult.errors > 0" class="text-red-600">
                    Failed: {{ importResult.errors }}
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex gap-4">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="showImportModal = false"
                />
                <Button
                    type="button"
                    label="Import"
                    :disabled="!selectedFile || isUploading"
                    :loading="isUploading"
                    @click="uploadFile"
                />
            </div>
        </template>
    </Dialog>
</template>
