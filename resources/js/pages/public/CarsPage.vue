<template>
    <v-container class="py-10">
        <div class="mb-8">
            <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                Our Curated Fleet
            </div>
            <h1 class="font-display text-h3 font-weight-bold mb-2" style="color: #101D32;">
                Available Vehicles
            </h1>
            <div class="gold-divider mb-3" />
            <p class="text-body-1" style="color: #777777;">
                Inspect exterior and interior views of each model and reserve immediately with no account required.
            </p>
        </div>

        <!-- Category Filter, Available Models Selector & Search -->
        <v-card class="luxury-card pa-4 mb-8">
            <v-row dense align="center">
                <v-col cols="12" md="8">
                    <div class="d-flex flex-wrap align-center ga-2">
                        <v-chip
                            :color="selectedCategory === null && !selectedVehicleId ? 'primary' : undefined"
                            :variant="selectedCategory === null && !selectedVehicleId ? 'flat' : 'outlined'"
                            class="cursor-pointer font-weight-medium"
                            :style="selectedCategory === null && !selectedVehicleId ? 'border: 1px solid #C6A15B;' : 'border-color: #E6E4DD;'"
                            @click="selectCategory(null)"
                        >
                            All Categories
                        </v-chip>
                        <v-chip
                            v-for="cat in categories"
                            :key="cat.id"
                            :color="selectedCategory === cat.id ? 'primary' : undefined"
                            :variant="selectedCategory === cat.id ? 'flat' : 'outlined'"
                            class="cursor-pointer font-weight-medium"
                            :style="selectedCategory === cat.id ? 'border: 1px solid #C6A15B;' : 'border-color: #E6E4DD;'"
                            @click="selectCategory(cat.id)"
                        >
                            {{ cat.name }}
                        </v-chip>

                        <v-btn
                            color="secondary"
                            variant="flat"
                            size="small"
                            rounded="pill"
                            prepend-icon="mdi-car-multiple"
                            append-icon="mdi-chevron-down"
                            class="font-weight-bold text-primary px-4"
                            @click="modelsDialog = true"
                        >
                            Available Models ({{ availableVehicles.length }})
                        </v-btn>

                        <v-chip
                            v-if="selectedVehicle"
                            color="primary"
                            variant="tonal"
                            closable
                            class="font-weight-medium"
                            @click:close="selectedVehicleId = null"
                        >
                            Selected: {{ selectedVehicle.brand }} {{ selectedVehicle.model }}
                        </v-chip>
                    </div>
                </v-col>

                <v-col cols="12" md="4">
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        label="Search brand or model"
                        density="compact"
                        hide-details
                        clearable
                    />
                </v-col>
            </v-row>
        </v-card>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-6">
            {{ error }}
        </v-alert>

        <v-row v-if="loading">
            <v-col v-for="n in 6" :key="n" cols="12" sm="6" md="4">
                <v-skeleton-loader type="image, article" rounded="xl" border />
            </v-col>
        </v-row>

        <v-alert v-else-if="filteredVehicles.length === 0" type="info" variant="tonal">
            No vehicles found matching your filter.
        </v-alert>

        <v-row v-else>
            <v-col
                v-for="vehicle in filteredVehicles"
                :key="vehicle.id"
                cols="12"
                sm="6"
                md="4"
            >
                <VehicleCard :vehicle="vehicle">
                    <template #actions>
                        <v-btn
                            :to="vehicle.status === 'available' ? `/book/${vehicle.id}` : undefined"
                            :disabled="vehicle.status !== 'available'"
                            :color="vehicle.status === 'available' ? 'primary' : 'grey'"
                            variant="flat"
                            block
                            size="large"
                            prepend-icon="mdi-calendar-check"
                            class="font-weight-semibold"
                        >
                            {{ vehicle.status === 'available' ? 'Book Now' : unavailableLabel(vehicle.status) }}
                        </v-btn>
                    </template>
                </VehicleCard>
            </v-col>
        </v-row>

        <!-- Available Car Models Selector Modal -->
        <v-dialog v-model="modelsDialog" max-width="680">
            <v-card class="luxury-card">
                <v-card-title class="pa-5 d-flex align-center justify-space-between">
                    <div>
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Select a Vehicle
                        </div>
                        <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            Available Car Models ({{ availableVehicles.length }})
                        </div>
                    </div>
                    <v-btn icon="mdi-close" variant="text" color="primary" @click="modelsDialog = false" />
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-card-text class="pa-4">
                    <v-alert v-if="!availableVehicles.length" type="info" variant="tonal">
                        There are currently no available vehicles.
                    </v-alert>

                    <v-list v-else lines="two" class="pa-0">
                        <v-list-item
                            v-for="car in availableVehicles"
                            :key="car.id"
                            rounded="lg"
                            class="mb-2 pa-3"
                            style="border: 1px solid #EFECE6;"
                        >
                            <template #prepend>
                                <v-avatar rounded="lg" size="64" class="mr-3" color="#EFECE6">
                                    <v-img v-if="car.image_url" :src="car.image_url" cover />
                                    <v-icon v-else icon="mdi-car" color="primary" />
                                </v-avatar>
                            </template>

                            <v-list-item-title class="font-weight-bold" style="color: #101D32;">
                                {{ car.brand }} {{ car.model }} ({{ car.year }})
                            </v-list-item-title>

                            <v-list-item-subtitle class="text-caption mt-1" style="color: #777777;">
                                {{ car.category?.name || 'Vehicle' }} &bull;
                                <span class="text-capitalize">{{ car.transmission }}</span> &bull;
                                {{ car.seats }} Seats &bull;
                                <strong style="color: #101D32;">PHP {{ formatMoney(car.daily_rate) }}/day</strong>
                            </v-list-item-subtitle>

                            <template #append>
                                <div class="d-flex align-center ga-2">
                                    <v-btn
                                        size="small"
                                        variant="outlined"
                                        color="primary"
                                        @click="selectModel(car.id)"
                                    >
                                        Select
                                    </v-btn>
                                    <v-btn
                                        :to="`/book/${car.id}`"
                                        size="small"
                                        variant="flat"
                                        color="secondary"
                                        class="font-weight-bold text-primary"
                                        @click="modelsDialog = false"
                                    >
                                        Book
                                    </v-btn>
                                </div>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card-text>

                <v-divider style="border-color: #EFECE6;" />
                <v-card-actions class="pa-4">
                    <v-btn
                        v-if="selectedVehicleId"
                        variant="text"
                        color="primary"
                        @click="selectedVehicleId = null; modelsDialog = false"
                    >
                        Show All Models
                    </v-btn>
                    <v-spacer />
                    <v-btn variant="text" color="primary" @click="modelsDialog = false">
                        Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/plugins/axios';
import VehicleCard from '@/components/VehicleCard.vue';

const vehicles = ref([]);
const categories = ref([]);
const selectedCategory = ref(null);
const selectedVehicleId = ref(null);
const modelsDialog = ref(false);
const search = ref('');
const loading = ref(true);
const error = ref('');

const availableVehicles = computed(() =>
    vehicles.value.filter((vehicle) => vehicle.status === 'available')
);

const selectedVehicle = computed(() =>
    vehicles.value.find((vehicle) => vehicle.id === selectedVehicleId.value) || null
);

const filteredVehicles = computed(() => {
    return vehicles.value.filter((vehicle) => {
        if (selectedVehicleId.value && vehicle.id !== selectedVehicleId.value) {
            return false;
        }
        if (selectedCategory.value && vehicle.category_id !== selectedCategory.value) {
            return false;
        }
        if (search.value?.trim()) {
            const q = search.value.trim().toLowerCase();
            return (
                vehicle.brand.toLowerCase().includes(q) ||
                vehicle.model.toLowerCase().includes(q)
            );
        }
        return true;
    });
});

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function selectCategory(categoryId) {
    selectedVehicleId.value = null;
    selectedCategory.value = categoryId;
}

function selectModel(vehicleId) {
    selectedCategory.value = null;
    selectedVehicleId.value = vehicleId;
    modelsDialog.value = false;
}

function unavailableLabel(status) {
    if (status === 'maintenance') return 'Under Maintenance';
    if (status === 'rented') return 'Currently Rented';
    if (status === 'booked') return 'Already Booked';
    return 'Unavailable';
}

async function fetchCatalog() {
    loading.value = true;
    error.value = '';
    try {
        const [vehiclesRes, categoriesRes] = await Promise.all([
            api.get('/vehicles'),
            api.get('/categories'),
        ]);
        vehicles.value = vehiclesRes.data;
        categories.value = categoriesRes.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Unable to load vehicles.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchCatalog);
</script>
