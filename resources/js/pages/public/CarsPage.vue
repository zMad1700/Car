<template>
    <v-container class="py-10">
        <div class="mb-8">
            <h1 class="text-h3 font-weight-bold text-secondary mb-2">
                Available Vehicles
            </h1>
            <p class="text-body-1 text-medium-emphasis">
                Choose from our fleet, switch between exterior and interior photos, and book instantly with no account required.
            </p>
        </div>

        <!-- Category Filter & Search -->
        <v-card elevation="1" border rounded="lg" class="pa-4 mb-8">
            <v-row dense align="center">
                <v-col cols="12" md="7">
                    <div class="d-flex flex-wrap align-center ga-2">
                        <v-chip
                            :color="selectedCategory === null ? 'primary' : 'default'"
                            :variant="selectedCategory === null ? 'flat' : 'outlined'"
                            class="cursor-pointer"
                            @click="selectedCategory = null"
                        >
                            All Categories
                        </v-chip>
                        <v-chip
                            v-for="cat in categories"
                            :key="cat.id"
                            :color="selectedCategory === cat.id ? 'primary' : 'default'"
                            :variant="selectedCategory === cat.id ? 'flat' : 'outlined'"
                            class="cursor-pointer"
                            @click="selectedCategory = cat.id"
                        >
                            {{ cat.name }}
                        </v-chip>
                    </div>
                </v-col>

                <v-col cols="12" md="5">
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        label="Search brand or model"
                        variant="outlined"
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
                <v-skeleton-loader type="image, article" rounded="lg" border />
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
                        >
                            {{ vehicle.status === 'available' ? 'Book Now' : unavailableLabel(vehicle.status) }}
                        </v-btn>
                    </template>
                </VehicleCard>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/plugins/axios';
import VehicleCard from '@/components/VehicleCard.vue';

const vehicles = ref([]);
const categories = ref([]);
const selectedCategory = ref(null);
const search = ref('');
const loading = ref(true);
const error = ref('');

const filteredVehicles = computed(() => {
    return vehicles.value.filter((vehicle) => {
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
