<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-6">
            <div>
                <h1 class="text-h4 font-weight-bold text-secondary">
                    Vehicles
                </h1>
                <p class="text-body-2 text-medium-emphasis mt-1">
                    Manage your rental fleet, pricing, availability status, and vehicle photos.
                </p>
            </div>

            <v-btn color="primary" prepend-icon="mdi-plus" variant="flat" @click="openCreateDialog">
                Add Vehicle
            </v-btn>
        </div>

        <v-card elevation="1" border rounded="lg">
            <v-card-title class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" sm="4">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search brand, model, or plate"
                            variant="outlined"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-select
                            v-model="filterCategory"
                            :items="categories"
                            item-title="name"
                            item-value="id"
                            label="Filter by Category"
                            variant="outlined"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-select
                            v-model="filterStatus"
                            :items="statusOptions"
                            label="Filter by Status"
                            variant="outlined"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                </v-row>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="filteredVehicles"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.vehicle="{ item }">
                    <div class="d-flex align-center ga-3 py-2">
                        <v-avatar rounded="md" size="52" color="grey-lighten-3">
                            <v-img v-if="item.image_url" :src="item.image_url" cover />
                            <v-icon v-else icon="mdi-car" color="grey-darken-1" />
                        </v-avatar>
                        <div>
                            <div class="font-weight-bold text-secondary">
                                {{ item.brand }} {{ item.model }} ({{ item.year }})
                            </div>
                            <div class="text-caption text-medium-emphasis">
                                Plate: {{ item.plate_number }} &bull; {{ item.color }}
                            </div>
                        </div>
                    </div>
                </template>

                <template #item.category="{ item }">
                    {{ item.category?.name || '—' }}
                </template>

                <template #item.specs="{ item }">
                    <div class="text-body-2 text-capitalize">
                        {{ item.transmission }} &bull; {{ item.seats }} seats
                    </div>
                </template>

                <template #item.daily_rate="{ item }">
                    <span class="font-weight-medium">
                        PHP {{ Number(item.daily_rate).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                    </span>
                </template>

                <template #item.status="{ item }">
                    <VehicleStatusChip :status="item.status" />
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            icon="mdi-pencil-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="Edit"
                            @click="openEditDialog(item)"
                        />
                        <v-btn
                            icon="mdi-delete-outline"
                            size="small"
                            variant="text"
                            color="error"
                            title="Delete"
                            @click="openDeleteDialog(item)"
                        />
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Create / Edit Vehicle Dialog -->
        <v-dialog v-model="formDialog" max-width="760" persistent>
            <v-card rounded="lg">
                <v-card-title class="pa-5 font-weight-bold text-secondary">
                    {{ editingId ? 'Edit Vehicle' : 'Add Vehicle' }}
                </v-card-title>
                <v-divider />

                <v-form ref="formRef" @submit.prevent="saveVehicle">
                    <v-card-text class="pa-5">
                        <v-alert
                            v-if="formError"
                            type="error"
                            variant="tonal"
                            density="compact"
                            class="mb-4"
                        >
                            {{ formError }}
                        </v-alert>

                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.category_id"
                                    :items="categories"
                                    item-title="name"
                                    item-value="id"
                                    label="Category *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Category is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.plate_number"
                                    label="Plate Number *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Plate number is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.brand"
                                    label="Brand *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Brand is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.model"
                                    label="Model *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Model is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model.number="form.year"
                                    label="Year *"
                                    type="number"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="yearRules"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.color"
                                    label="Color *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Color is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-select
                                    v-model="form.transmission"
                                    :items="['automatic', 'manual']"
                                    label="Transmission *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Transmission is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model.number="form.seats"
                                    label="Seats *"
                                    type="number"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="seatRules"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model.number="form.daily_rate"
                                    label="Daily Rate (PHP) *"
                                    type="number"
                                    step="0.01"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="rateRules"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.status"
                                    :items="statusOptions"
                                    label="Status *"
                                    variant="outlined"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Status is required.']"
                                />
                            </v-col>

                            <v-col cols="12">
                                <v-file-input
                                    v-model="imageInput"
                                    label="Vehicle Photo (optional, max 2MB)"
                                    accept="image/*"
                                    prepend-icon="mdi-camera-outline"
                                    variant="outlined"
                                    density="comfortable"
                                    show-size
                                    clearable
                                    @update:model-value="onImageSelected"
                                />

                                <v-img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    max-height="180"
                                    contain
                                    class="mt-2 rounded border bg-grey-lighten-4"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider />
                    <v-card-actions class="pa-4">
                        <v-spacer />
                        <v-btn variant="text" color="secondary" :disabled="saving" @click="formDialog = false">
                            Cancel
                        </v-btn>
                        <v-btn type="submit" color="primary" variant="flat" :loading="saving">
                            {{ editingId ? 'Update Vehicle' : 'Save Vehicle' }}
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="440">
            <v-card rounded="lg">
                <v-card-title class="pa-5 font-weight-bold text-secondary">
                    Delete Vehicle
                </v-card-title>
                <v-card-text class="px-5 pb-4">
                    Are you sure you want to delete
                    <strong>{{ selectedVehicle?.brand }} {{ selectedVehicle?.model }} ({{ selectedVehicle?.plate_number }})</strong>?
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="secondary" :disabled="deleting" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" :loading="deleting" @click="confirmDelete">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '@/plugins/axios';
import VehicleStatusChip from '@/components/VehicleStatusChip.vue';

const vehicles = ref([]);
const categories = ref([]);
const loading = ref(false);
const search = ref('');
const filterCategory = ref(null);
const filterStatus = ref(null);

const formDialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const editingId = ref(null);
const selectedVehicle = ref(null);
const saving = ref(false);
const deleting = ref(false);
const formError = ref('');

const imageInput = ref(null);
const selectedFile = ref(null);
const imagePreview = ref(null);

const statusOptions = ['available', 'booked', 'rented', 'maintenance'];

const form = reactive({
    category_id: null,
    plate_number: '',
    brand: '',
    model: '',
    year: new Date().getFullYear(),
    color: '',
    transmission: 'automatic',
    seats: 5,
    daily_rate: '',
    status: 'available',
});

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Vehicle', key: 'vehicle', sortable: false },
    { title: 'Category', key: 'category', sortable: false },
    { title: 'Specs', key: 'specs', sortable: false },
    { title: 'Daily Rate', key: 'daily_rate' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const yearRules = [
    (v) => !!v || 'Year is required.',
    (v) => (v >= 1990 && v <= 2100) || 'Enter a valid year (1990–2100).',
];

const seatRules = [
    (v) => !!v || 'Seats is required.',
    (v) => (v >= 1 && v <= 30) || 'Seats must be between 1 and 30.',
];

const rateRules = [
    (v) => (v !== '' && v !== null && Number(v) >= 0) || 'Daily rate must be 0 or higher.',
];

const filteredVehicles = computed(() => {
    return vehicles.value.filter((item) => {
        if (filterCategory.value && item.category_id !== filterCategory.value) {
            return false;
        }
        if (filterStatus.value && item.status !== filterStatus.value) {
            return false;
        }
        if (search.value?.trim()) {
            const q = search.value.trim().toLowerCase();
            const matches =
                item.brand.toLowerCase().includes(q) ||
                item.model.toLowerCase().includes(q) ||
                item.plate_number.toLowerCase().includes(q);
            if (!matches) return false;
        }
        return true;
    });
});

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
}

function onImageSelected(value) {
    const file = Array.isArray(value) ? value[0] : value;
    selectedFile.value = file || null;
    if (selectedFile.value) {
        imagePreview.value = URL.createObjectURL(selectedFile.value);
    } else if (editingId.value) {
        const existing = vehicles.value.find((v) => v.id === editingId.value);
        imagePreview.value = existing?.image_url || null;
    } else {
        imagePreview.value = null;
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const [vehiclesRes, categoriesRes] = await Promise.all([
            api.get('/admin/vehicles'),
            api.get('/admin/categories'),
        ]);
        vehicles.value = vehiclesRes.data;
        categories.value = categoriesRes.data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load vehicles.', 'error');
    } finally {
        loading.value = false;
    }
}

function openCreateDialog() {
    editingId.value = null;
    form.category_id = categories.value[0]?.id ?? null;
    form.plate_number = '';
    form.brand = '';
    form.model = '';
    form.year = new Date().getFullYear();
    form.color = '';
    form.transmission = 'automatic';
    form.seats = 5;
    form.daily_rate = '';
    form.status = 'available';
    imageInput.value = null;
    selectedFile.value = null;
    imagePreview.value = null;
    formError.value = '';
    formDialog.value = true;
}

function openEditDialog(item) {
    editingId.value = item.id;
    form.category_id = item.category_id;
    form.plate_number = item.plate_number;
    form.brand = item.brand;
    form.model = item.model;
    form.year = item.year;
    form.color = item.color;
    form.transmission = item.transmission;
    form.seats = item.seats;
    form.daily_rate = item.daily_rate;
    form.status = item.status;
    imageInput.value = null;
    selectedFile.value = null;
    imagePreview.value = item.image_url || null;
    formError.value = '';
    formDialog.value = true;
}

function openDeleteDialog(item) {
    selectedVehicle.value = item;
    deleteDialog.value = true;
}

async function saveVehicle() {
    formError.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('category_id', form.category_id);
        formData.append('plate_number', form.plate_number.trim());
        formData.append('brand', form.brand.trim());
        formData.append('model', form.model.trim());
        formData.append('year', form.year);
        formData.append('color', form.color.trim());
        formData.append('transmission', form.transmission);
        formData.append('seats', form.seats);
        formData.append('daily_rate', form.daily_rate);
        formData.append('status', form.status);

        if (selectedFile.value) {
            formData.append('image', selectedFile.value);
        }

        if (editingId.value) {
            formData.append('_method', 'PUT');
            await api.post(`/admin/vehicles/${editingId.value}`, formData);
            notify('Vehicle updated successfully.');
        } else {
            await api.post('/admin/vehicles', formData);
            notify('Vehicle added successfully.');
        }

        formDialog.value = false;
        await fetchData();
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
            formError.value = Object.values(errors).flat().join(' ');
        } else {
            formError.value = error.response?.data?.message || 'Unable to save vehicle.';
        }
    } finally {
        saving.value = false;
    }
}

async function confirmDelete() {
    if (!selectedVehicle.value) return;
    deleting.value = true;
    try {
        await api.delete(`/admin/vehicles/${selectedVehicle.value.id}`);
        deleteDialog.value = false;
        notify('Vehicle deleted successfully.');
        await fetchData();
    } catch (error) {
        deleteDialog.value = false;
        notify(error.response?.data?.message || 'Failed to delete vehicle.', 'error');
    } finally {
        deleting.value = false;
    }
}

onMounted(fetchData);
</script>
