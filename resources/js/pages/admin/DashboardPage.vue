<template>
    <div>
        <!-- Header -->
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Executive Overview
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Dashboard
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Welcome back, {{ authStore.user?.name || 'Admin' }}. Monitor fleet availability, rentals, and revenue.
                </p>
            </div>

            <div class="d-flex align-center ga-2">
                <v-btn
                    to="/admin/bookings"
                    color="secondary"
                    variant="flat"
                    class="font-weight-bold text-primary"
                    prepend-icon="mdi-calendar-check-outline"
                >
                    Pending ({{ stats.pending_bookings }})
                </v-btn>
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
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-6">
            {{ error }}
        </v-alert>

        <!-- Primary KPI Cards -->
        <v-row class="mb-4">
            <v-col
                v-for="card in statCards"
                :key="card.label"
                cols="12"
                sm="6"
                md="4"
                lg
            >
                <v-card class="luxury-card luxury-card-hover pa-5 h-100">
                    <div class="d-flex align-start justify-space-between mb-3">
                        <span class="text-caption font-weight-bold text-uppercase" style="color: #777777; letter-spacing: 0.08em;">
                            {{ card.label }}
                        </span>
                        <v-avatar
                            size="38"
                            rounded="lg"
                            :color="card.iconBg"
                            :style="card.iconBorder ? `border: 1px solid ${card.iconBorder}` : ''"
                        >
                            <v-icon :icon="card.icon" :color="card.iconColor" size="20" />
                        </v-avatar>
                    </div>

                    <div class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                        <v-skeleton-loader v-if="loading" type="text" width="90" />
                        <span v-else>{{ card.value }}</span>
                    </div>

                    <div class="text-caption mt-2" style="color: #777777;">
                        {{ card.subtitle }}
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts & Fleet Availability Overview -->
        <v-row class="mb-4">
            <!-- Revenue Chart -->
            <v-col cols="12" lg="7">
                <v-card class="luxury-card pa-6 h-100">
                    <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-5">
                        <div>
                            <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                                Financial Performance
                            </div>
                            <h2 class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                                Monthly Revenue
                            </h2>
                        </div>
                        <div class="text-right">
                            <div class="text-caption" style="color: #777777;">This Month</div>
                            <div class="text-subtitle-1 font-weight-bold" style="color: #101D32;">
                                PHP {{ formatMoney(stats.income_this_month) }}
                            </div>
                        </div>
                    </div>

                    <div v-if="loading" class="py-10 text-center">
                        <v-progress-circular indeterminate color="primary" />
                    </div>

                    <div v-else class="chart-container">
                        <svg viewBox="0 0 600 220" class="w-100" style="overflow: visible;">
                            <!-- Grid Lines -->
                            <g v-for="line in 4" :key="line">
                                <line
                                    x1="40"
                                    :y1="20 + (line - 1) * 45"
                                    x2="580"
                                    :y2="20 + (line - 1) * 45"
                                    stroke="#EFECE6"
                                    stroke-width="1"
                                />
                            </g>

                            <!-- Bars -->
                            <g
                                v-for="(bar, idx) in revenueBars"
                                :key="bar.label"
                                class="revenue-bar-group"
                            >
                                <rect
                                    :x="bar.x"
                                    :y="bar.y"
                                    width="46"
                                    :height="bar.height"
                                    rx="6"
                                    :fill="idx === revenueBars.length - 1 ? '#C6A15B' : '#101D32'"
                                />
                                <text
                                    :x="bar.x + 23"
                                    :y="Math.max(14, bar.y - 8)"
                                    text-anchor="middle"
                                    fill="#252525"
                                    font-size="11"
                                    font-weight="600"
                                >
                                    {{ bar.amount > 0 ? shortCurrency(bar.amount) : '0' }}
                                </text>
                                <text
                                    :x="bar.x + 23"
                                    y="188"
                                    text-anchor="middle"
                                    fill="#777777"
                                    font-size="12"
                                    font-weight="500"
                                >
                                    {{ bar.short_label }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </v-card>
            </v-col>

            <!-- Car Availability Overview -->
            <v-col cols="12" lg="5">
                <v-card class="luxury-card pa-6 h-100">
                    <div class="mb-5">
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Fleet Distribution
                        </div>
                        <h2 class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            Car Availability Overview
                        </h2>
                    </div>

                    <div class="d-flex align-center ga-6 mb-5">
                        <v-progress-circular
                            :model-value="availabilityPercent"
                            :size="112"
                            :width="11"
                            color="secondary"
                            bg-color="#EFECE6"
                        >
                            <div class="text-center">
                                <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                                    {{ availabilityPercent }}%
                                </div>
                                <div class="text-caption" style="color: #777777; font-size: 0.65rem !important;">
                                    Ready
                                </div>
                            </div>
                        </v-progress-circular>

                        <div class="flex-grow-1">
                            <div class="text-body-2 font-weight-medium mb-1" style="color: #252525;">
                                {{ stats.available_vehicles }} of {{ stats.total_vehicles }} cars available
                            </div>
                            <div class="text-caption" style="color: #777777;">
                                Today's Pickups: <strong>{{ stats.todays_pickups }}</strong> &bull;
                                Active Rentals: <strong>{{ stats.active_rentals }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column ga-3">
                        <div v-for="item in fleetBreakdown" :key="item.label">
                            <div class="d-flex justify-space-between text-caption font-weight-medium mb-1">
                                <span style="color: #252525;">{{ item.label }}</span>
                                <span style="color: #777777;">{{ item.count }} ({{ item.percent }}%)</span>
                            </div>
                            <v-progress-linear
                                :model-value="item.percent"
                                :color="item.color"
                                bg-color="#EFECE6"
                                height="7"
                                rounded
                            />
                        </div>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Recent Rental Transactions & Recent Bookings -->
        <v-card class="luxury-card overflow-hidden">
            <div class="pa-5 d-flex flex-wrap align-center justify-space-between ga-3" style="border-bottom: 1px solid #EFECE6;">
                <div>
                    <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                        Activity Feed
                    </div>
                    <h2 class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                        {{ activeTab === 'rentals' ? 'Recent Rental Transactions' : 'Recent Reservations' }}
                    </h2>
                </div>

                <v-btn-toggle
                    v-model="activeTab"
                    mandatory
                    density="compact"
                    color="primary"
                    variant="outlined"
                    rounded="lg"
                >
                    <v-btn value="rentals" size="small">
                        Rental Transactions
                    </v-btn>
                    <v-btn value="bookings" size="small">
                        Recent Bookings
                    </v-btn>
                </v-btn-toggle>
            </div>

            <!-- Rentals Table -->
            <v-table v-if="activeTab === 'rentals'" hover>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Date Out</th>
                        <th>Date In</th>
                        <th>Penalty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!stats.recent_rentals?.length">
                        <td colspan="7" class="text-center py-8" style="color: #777777;">
                            No rental transactions recorded yet.
                        </td>
                    </tr>
                    <tr v-for="rental in stats.recent_rentals" :key="rental.id">
                        <td class="font-weight-bold" style="color: #101D32;">
                            {{ rental.booking?.reference_code || '—' }}
                        </td>
                        <td>{{ rental.booking?.customer?.name || '—' }}</td>
                        <td>
                            {{ rental.booking?.vehicle?.brand }} {{ rental.booking?.vehicle?.model }}
                        </td>
                        <td>{{ formatDate(rental.date_out) }}</td>
                        <td>{{ formatDate(rental.date_in) }}</td>
                        <td>PHP {{ formatMoney(rental.penalty_amount) }}</td>
                        <td>
                            <v-chip
                                size="small"
                                variant="tonal"
                                :color="rentalStatusColor(rental.status)"
                                class="text-capitalize font-weight-medium"
                            >
                                {{ rental.status }}
                            </v-chip>
                        </td>
                    </tr>
                </tbody>
            </v-table>

            <!-- Bookings Table -->
            <v-table v-else hover>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Pickup</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!stats.recent_bookings?.length">
                        <td colspan="6" class="text-center py-8" style="color: #777777;">
                            No bookings recorded yet.
                        </td>
                    </tr>
                    <tr v-for="booking in stats.recent_bookings" :key="booking.id">
                        <td class="font-weight-bold" style="color: #101D32;">
                            {{ booking.reference_code }}
                        </td>
                        <td>{{ booking.customer?.name || '—' }}</td>
                        <td>
                            {{ booking.vehicle?.brand }} {{ booking.vehicle?.model }}
                        </td>
                        <td>{{ formatDate(booking.pickup_date) }}</td>
                        <td class="font-weight-bold">PHP {{ formatMoney(booking.total_amount) }}</td>
                        <td>
                            <BookingStatusChip :status="booking.status" />
                        </td>
                    </tr>
                </tbody>
            </v-table>
        </v-card>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/plugins/axios';
import { useAuthStore } from '@/stores/auth';
import BookingStatusChip from '@/components/BookingStatusChip.vue';

const authStore = useAuthStore();
const loading = ref(true);
const error = ref('');
const activeTab = ref('rentals');

const stats = ref({
    total_vehicles: 0,
    available_vehicles: 0,
    rented_vehicles: 0,
    booked_vehicles: 0,
    maintenance_vehicles: 0,
    total_customers: 0,
    active_rentals: 0,
    todays_pickups: 0,
    pending_bookings: 0,
    income_this_month: 0,
    total_revenue: 0,
    status_breakdown: {
        available: 0,
        booked: 0,
        rented: 0,
        maintenance: 0,
    },
    monthly_revenue: [],
    recent_bookings: [],
    recent_rentals: [],
});

const statCards = computed(() => [
    {
        label: 'Total Cars',
        value: stats.value.total_vehicles,
        subtitle: 'Entire registered fleet',
        icon: 'mdi-car-multiple',
        iconBg: 'rgba(16, 29, 50, 0.08)',
        iconColor: '#101D32',
    },
    {
        label: 'Available Cars',
        value: stats.value.available_vehicles,
        subtitle: 'Ready for booking',
        icon: 'mdi-check-decagram-outline',
        iconBg: 'rgba(46, 125, 91, 0.1)',
        iconColor: '#2E7D5B',
    },
    {
        label: 'Rented Cars',
        value: stats.value.rented_vehicles,
        subtitle: `${stats.value.active_rentals} active rental(s)`,
        icon: 'mdi-key-chain-variant',
        iconBg: 'rgba(198, 161, 91, 0.16)',
        iconColor: '#C6A15B',
        iconBorder: 'rgba(198, 161, 91, 0.4)',
    },
    {
        label: 'Total Customers',
        value: stats.value.total_customers,
        subtitle: 'Unique booking clients',
        icon: 'mdi-account-group-outline',
        iconBg: 'rgba(29, 78, 137, 0.1)',
        iconColor: '#1D4E89',
    },
    {
        label: 'Revenue',
        value: `PHP ${formatMoney(stats.value.total_revenue)}`,
        subtitle: `This month: PHP ${formatMoney(stats.value.income_this_month)}`,
        icon: 'mdi-cash-multiple',
        iconBg: '#101D32',
        iconColor: '#C6A15B',
    },
]);

const availabilityPercent = computed(() => {
    if (!stats.value.total_vehicles) return 0;
    return Math.round((stats.value.available_vehicles / stats.value.total_vehicles) * 100);
});

const fleetBreakdown = computed(() => {
    const total = stats.value.total_vehicles || 1;
    const b = stats.value.status_breakdown || {};
    const items = [
        { label: 'Available', count: b.available || 0, color: 'success' },
        { label: 'Booked', count: b.booked || 0, color: 'info' },
        { label: 'Rented', count: b.rented || 0, color: 'secondary' },
        { label: 'Maintenance', count: b.maintenance || 0, color: 'error' },
    ];
    return items.map((item) => ({
        ...item,
        percent: stats.value.total_vehicles ? Math.round((item.count / total) * 100) : 0,
    }));
});

const revenueBars = computed(() => {
    const series = stats.value.monthly_revenue || [];
    const maxAmount = Math.max(...series.map((m) => Number(m.amount || 0)), 1);
    const chartBottom = 165;
    const maxHeight = 130;

    return series.map((item, index) => {
        const amount = Number(item.amount || 0);
        const height = amount > 0 ? Math.max(10, Math.round((amount / maxAmount) * maxHeight)) : 4;
        return {
            ...item,
            amount,
            x: 62 + index * 86,
            y: chartBottom - height,
            height,
        };
    });
});

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function shortCurrency(value) {
    if (value >= 1000) {
        return `${(value / 1000).toFixed(1)}k`;
    }
    return String(Math.round(value));
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function rentalStatusColor(status) {
    if (status === 'active') return 'info';
    if (status === 'returned') return 'success';
    if (status === 'overdue') return 'error';
    return 'grey';
}

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

<style scoped>
.revenue-bar-group rect {
    transition: opacity 0.2s ease;
}
.revenue-bar-group:hover rect {
    opacity: 0.85;
}
</style>
