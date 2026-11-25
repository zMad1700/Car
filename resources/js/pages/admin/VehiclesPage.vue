<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Fleet Management
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Vehicles
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Curate your fleet, daily rental rates, availability status, and exterior &amp; interior photos.
                </p>
            </div>

            <div class="d-flex align-center ga-3">
                <v-btn-toggle v-model="viewMode" mandatory density="compact" variant="outlined" color="primary">
                    <v-btn value="grid" prepend-icon="mdi-view-grid-outline" size="small">
                        Cards
                    </v-btn>
                    <v-btn value="table" prepend-icon="mdi-table" size="small">
                        Table
                    </v-btn>
                </v-btn-toggle>

                <v-btn
                    color="secondary"
                    prepend-icon="mdi-plus"
                    variant="flat"
                    class="font-weight-bold text-primary px-5"
                    @click="openCreateDialog"
                >
                    Add Vehicle
                </v-btn>
            </div>
        </div>

        <!-- Filters -->
        <v-card class="luxury-card mb-6">
            <v-card-text class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" sm="4">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search brand, model, or plate"
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
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Card / Box Grid View -->
        <div v-if="viewMode === 'grid'">
            <v-progress-linear v-if="loading" indeterminate color="secondary" class="mb-4" />

            <v-alert v-if="!loading && filteredVehicles.length === 0" type="info" variant="tonal">
                No vehicles match your current filters.
            </v-alert>

            <v-row v-else>
                <v-col
                    v-for="vehicle in filteredVehicles"
                    :key="vehicle.id"
                    cols="12"
                    sm="6"
                    lg="4"
                >
                    <VehicleCard :vehicle="vehicle" show-plate>
                        <template #actions>
                            <v-btn
                                variant="outlined"
                                color="primary"
                                size="small"
                                prepend-icon="mdi-eye-outline"
                                @click="openViewDialog(vehicle)"
                            >
                                View
                            </v-btn>
                            <v-btn
                                variant="tonal"
                                color="primary"
                                size="small"
                                prepend-icon="mdi-pencil-outline"
                                @click="openEditDialog(vehicle)"
                            >
                                Edit
                            </v-btn>
                            <v-spacer />
                            <v-btn
                                variant="text"
                                color="error"
                                size="small"
                                icon="mdi-delete-outline"
                                title="Delete Vehicle"
                                @click="openDeleteDialog(vehicle)"
                            />
                        </template>
                    </VehicleCard>
                </v-col>
            </v-row>
        </div>

        <!-- Table View -->
        <v-card v-else class="luxury-card overflow-hidden">
            <v-data-table
                :headers="headers"
                :items="filteredVehicles"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.vehicle="{ item }">
                    <div class="d-flex align-center ga-3 py-2">
                        <v-avatar rounded="lg" size="56" color="grey-lighten-3">
                            <v-img v-if="item.image_url" :src="item.image_url" cover />
                            <v-icon v-else icon="mdi-car" color="grey-darken-1" />
                        </v-avatar>
                        <div>
                            <div class="font-weight-bold" style="color: #101D32;">
                                {{ item.brand }} {{ item.model }} ({{ item.year }})
                            </div>
                            <div class="text-caption" style="color: #777777;">
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
                    <span class="font-weight-bold" style="color: #101D32;">
                        PHP {{ Number(item.daily_rate).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                    </span>
                </template>

                <template #item.status="{ item }">
                    <VehicleStatusChip :status="item.status" />
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            icon="mdi-eye-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="View"
                            @click="openViewDialog(item)"
                        />
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

        <!-- View Vehicle Details Dialog -->
        <v-dialog v-model="viewDialog" max-width="780">
            <v-card v-if="viewingVehicle" class="luxury-card">
                <v-card-title class="pa-5 d-flex align-center justify-space-between">
                    <div>
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                            {{ viewingVehicle.category?.name || 'Vehicle' }} &bull; {{ viewingVehicle.plate_number }}
                        </div>
                        <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            {{ viewingVehicle.brand }} {{ viewingVehicle.model }} ({{ viewingVehicle.year }})
                        </div>
                    </div>
                    <VehicleStatusChip :status="viewingVehicle.status" />
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-card-text class="pa-5">
                    <v-row class="mb-4">
                        <v-col cols="12" sm="6">
                            <div class="text-caption font-weight-bold mb-1" style="color: #777777;">EXTERIOR PHOTO</div>
                            <v-img
                                v-if="viewingVehicle.image_url"
                                :src="viewingVehicle.image_url"
                                height="200"
                                cover
                                class="rounded-lg border"
                            />
                            <v-sheet v-else height="200" color="#EFECE6" class="rounded-lg d-flex align-center justify-center">
                                <v-icon icon="mdi-car" size="48" color="grey" />
                            </v-sheet>
                        </v-col>
                        <v-col cols="12" sm="6">
                            <div class="text-caption font-weight-bold mb-1" style="color: #777777;">INTERIOR PHOTO</div>
                            <v-img
                                v-if="viewingVehicle.interior_image_url"
                                :src="viewingVehicle.interior_image_url"
                                height="200"
                                cover
                                class="rounded-lg border"
                            />
                            <v-sheet v-else height="200" color="#EFECE6" class="rounded-lg d-flex align-center justify-center">
                                <v-icon icon="mdi-car-seat" size="48" color="grey" />
                            </v-sheet>
                        </v-col>
                    </v-row>

                    <v-row dense>
                        <v-col cols="6" sm="3">
                            <div class="text-caption" style="color: #777777;">Color</div>
                            <div class="font-weight-bold">{{ viewingVehicle.color }}</div>
                        </v-col>
                        <v-col cols="6" sm="3">
                            <div class="text-caption" style="color: #777777;">Transmission</div>
                            <div class="font-weight-bold text-capitalize">{{ viewingVehicle.transmission }}</div>
                        </v-col>
                        <v-col cols="6" sm="3">
                            <div class="text-caption" style="color: #777777;">Seats</div>
                            <div class="font-weight-bold">{{ viewingVehicle.seats }} Seats</div>
                        </v-col>
                        <v-col cols="6" sm="3">
                            <div class="text-caption" style="color: #777777;">Daily Rate</div>
                            <div class="font-weight-bold" style="color: #101D32;">
                                PHP {{ Number(viewingVehicle.daily_rate).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>

                <v-divider style="border-color: #EFECE6;" />
                <v-card-actions class="pa-4">
                    <v-btn
                        color="primary"
                        variant="tonal"
                        prepend-icon="mdi-pencil-outline"
                        @click="viewDialog = false; openEditDialog(viewingVehicle)"
                    >
                        Edit Vehicle
                    </v-btn>
                    <v-spacer />
                    <v-btn variant="text" color="primary" @click="viewDialog = false">
                        Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Create / Edit Vehicle Dialog -->
        <v-dialog v-model="formDialog" max-width="800" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    {{ editingId ? 'Edit Vehicle' : 'Add Vehicle' }}
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

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
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Category is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.plate_number"
                                    label="Plate Number *"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Plate number is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.brand"
                                    label="Brand *"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Brand is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.model"
                                    label="Model *"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Model is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model.number="form.year"
                                    label="Year *"
                                    type="number"
                                    density="comfortable"
                                    :rules="yearRules"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model="form.color"
                                    label="Color *"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Color is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-select
                                    v-model="form.transmission"
                                    :items="['automatic', 'manual']"
                                    label="Transmission *"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Transmission is required.']"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field
                                    v-model.number="form.seats"
                                    label="Seats *"
                                    type="number"
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
                                    density="comfortable"
                                    :rules="rateRules"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.status"
                                    :items="statusOptions"
                                    label="Status *"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Status is required.']"
                                />
                            </v-col>

                            <!-- Exterior Photo -->
                            <v-col cols="12" sm="6">
                                <v-file-input
                                    v-model="imageInput"
                                    label="Exterior Photo (max 2MB)"
                                    accept="image/*"
                                    prepend-icon="mdi-car-side"
                                    variant="outlined"
                                    density="comfortable"
                                    show-size
                                    clearable
                                    @update:model-value="onExteriorSelected"
                                />
                                <v-img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    height="150"
                                    cover
                                    class="mt-2 rounded-lg border bg-grey-lighten-4"
                                />
                            </v-col>

                            <!-- Interior Photo -->
                            <v-col cols="12" sm="6">
                                <v-file-input
                                    v-model="interiorInput"
                                    label="Interior Photo (picture sa sulod)"
                                    accept="image/*"
                                    prepend-icon="mdi-car-seat"
                                    variant="outlined"
                                    density="comfortable"
                                    show-size
                                    clearable
                                    @update:model-value="onInteriorSelected"
                                />
                                <v-img
                                    v-if="interiorPreview"
                                    :src="interiorPreview"
                                    height="150"
                                    cover
                                    class="mt-2 rounded-lg border bg-grey-lighten-4"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider style="border-color: #EFECE6;" />
                    <v-card-actions class="pa-4">
                        <v-spacer />
                        <v-btn variant="text" color="primary" :disabled="saving" @click="formDialog = false">
                            Cancel
                        </v-btn>
                        <v-btn
                            type="submit"
                            color="primary"
                            variant="flat"
                            class="px-5"
                            :loading="saving"
                        >
                            {{ editingId ? 'Update Vehicle' : 'Save Vehicle' }}
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="440">
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h6 font-weight-bold" style="color: #101D32;">
                    Delete Vehicle
                </v-card-title>
                <v-card-text class="px-5 pb-4">
                    Are you sure you want to delete
                    <strong>{{ selectedVehicle?.brand }} {{ selectedVehicle?.model }} ({{ selectedVehicle?.plate_number }})</strong>?
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="primary" :disabled="deleting" @click="deleteDialog = false">
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
import VehicleCard from '@/components/VehicleCard.vue';
import VehicleStatusChip from '@/components/VehicleStatusChip.vue';

const viewMode = ref('grid');
const vehicles = ref([]);
const categories = ref([]);
const loading = ref(false);
const search = ref('');
const filterCategory = ref(null);
const filterStatus = ref(null);

const viewDialog = ref(false);
const viewingVehicle = ref(null);

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

const interiorInput = ref(null);
const selectedInteriorFile = ref(null);
const interiorPreview = ref(null);

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

function handleFileSelection(value, fileRef, previewRef, existingUrl) {
    const file = Array.isArray(value) ? value[0] : value;
    fileRef.value = file || null;
    if (fileRef.value) {
        previewRef.value = URL.createObjectURL(fileRef.value);
    } else if (editingId.value) {
        previewRef.value = existingUrl || null;
    } else {
        previewRef.value = null;
    }
}

function onExteriorSelected(value) {
    const existing = vehicles.value.find((v) => v.id === editingId.value);
    handleFileSelection(value, selectedFile, imagePreview, existing?.image_url);
}

function onInteriorSelected(value) {
    const existing = vehicles.value.find((v) => v.id === editingId.value);
    handleFileSelection(value, selectedInteriorFile, interiorPreview, existing?.interior_image_url);
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

function openViewDialog(item) {
    viewingVehicle.value = item;
    viewDialog.value = true;
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
    interiorInput.value = null;
    selectedInteriorFile.value = null;
    interiorPreview.value = null;
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
    interiorInput.value = null;
    selectedInteriorFile.value = null;
    interiorPreview.value = item.interior_image_url || null;
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
        if (selectedInteriorFile.value) {
            formData.append('interior_image', selectedInteriorFile.value);
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
