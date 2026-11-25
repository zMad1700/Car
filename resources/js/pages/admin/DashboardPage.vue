<template>
    <div>
        <div class="d-flex align-center justify-space-between mb-6">
            <div>
                <h1 class="text-h4 font-weight-bold text-secondary">
                    Dashboard
                </h1>
                <p class="text-body-2 text-medium-emphasis mt-1">
                    Welcome back, {{ authStore.user?.name || 'Admin' }}. Here is your fleet overview.
                </p>
            </div>
            <v-btn
                variant="outlined"
                color="primary"
                prepend-icon="mdi-refresh"
                :loading="loading"
                @click="fetchDashboard"
            >
                Refresh
            </v-btn>
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-6">
            {{ error }}
        </v-alert>

        <v-row>
            <v-col
                v-for="card in statCards"
                :key="card.label"
                cols="12"
                sm="6"
                md="4"
            >
                <v-card elevation="1" border rounded="lg" class="pa-5">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-caption text-uppercase font-weight-bold text-medium-emphasis">
                                {{ card.label }}
                            </div>
                            <div class="text-h4 font-weight-bold text-secondary mt-2">
                                <v-skeleton-loader v-if="loading" type="text" width="80" />
                                <span v-else>{{ card.value }}</span>
                            </div>
                        </div>
                        <v-avatar :color="card.color" variant="tonal" size="50">
                            <v-icon :icon="card.icon" size="26" />
                        </v-avatar>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/plugins/axios';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const loading = ref(true);
const error = ref('');
const stats = ref({
    total_vehicles: 0,
    available_vehicles: 0,
    active_rentals: 0,
    todays_pickups: 0,
    pending_bookings: 0,
    income_this_month: 0,
});

const statCards = computed(() => [
    {
        label: 'Total Vehicles',
        value: stats.value.total_vehicles,
        icon: 'mdi-car-multiple',
        color: 'primary',
    },
    {
        label: 'Available Vehicles',
        value: stats.value.available_vehicles,
        icon: 'mdi-car-check',
        color: 'success',
    },
    {
        label: 'Active Rentals',
        value: stats.value.active_rentals,
        icon: 'mdi-key-variant',
        color: 'info',
    },
    {
        label: "Today's Pickups",
        value: stats.value.todays_pickups,
        icon: 'mdi-calendar-today',
        color: 'accent',
    },
    {
        label: 'Pending Bookings',
        value: stats.value.pending_bookings,
        icon: 'mdi-clock-outline',
        color: 'warning',
    },
    {
        label: 'Income This Month',
        value: `PHP ${Number(stats.value.income_this_month || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`,
        icon: 'mdi-cash-multiple',
        color: 'success',
    },
]);

async function fetchDashboard() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/dashboard');
        stats.value = data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load dashboard data.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDashboard);
</script>
