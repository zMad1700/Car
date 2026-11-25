<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7 d-print-none">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Analytics &amp; Insights
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Reports
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Inspect income breakdowns, top-performing vehicles, and overdue rental alerts.
                </p>
            </div>

            <div class="d-flex align-center ga-2">
                <v-btn
                    color="secondary"
                    variant="flat"
                    prepend-icon="mdi-printer"
                    class="font-weight-bold text-primary px-5"
                    @click="printReport"
                >
                    Print Report
                </v-btn>
                <v-btn
                    variant="outlined"
                    color="primary"
                    prepend-icon="mdi-refresh"
                    :loading="loading"
                    @click="fetchAllReports"
                >
                    Refresh
                </v-btn>
            </div>
        </div>

        <v-card class="luxury-card overflow-hidden mb-6">
            <v-tabs v-model="activeTab" color="primary" bg-color="surface" class="d-print-none" style="border-bottom: 1px solid #EFECE6;">
                <v-tab value="income" prepend-icon="mdi-cash-multiple">Income Report</v-tab>
                <v-tab value="most-rented" prepend-icon="mdi-trophy-outline">Most Rented Vehicles</v-tab>
                <v-tab value="overdue" prepend-icon="mdi-alert-circle-outline">
                    Overdue Rentals
                    <v-chip v-if="overdueRentals.length" size="x-small" color="error" variant="flat" class="ml-2">
                        {{ overdueRentals.length }}
                    </v-chip>
                </v-tab>
            </v-tabs>

            <v-window v-model="activeTab">
                <!-- Income Report Tab -->
                <v-window-item value="income" class="pa-6">
                    <v-row dense align="center" class="mb-5 d-print-none">
                        <v-col cols="12" sm="4" md="3">
                            <v-text-field
                                v-model="incomeFilters.date_from"
                                label="From Date"
                                type="date"
                                density="compact"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" sm="4" md="3">
                            <v-text-field
                                v-model="incomeFilters.date_to"
                                label="To Date"
                                type="date"
                                density="compact"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" sm="4" md="4" class="d-flex ga-2">
                            <v-btn color="primary" variant="flat" @click="fetchIncome">
                                Apply Filter
                            </v-btn>
                            <v-btn variant="text" color="primary" @click="resetIncomeFilter">
                                Reset
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-row class="mb-6">
                        <v-col cols="12" md="4">
                            <v-card color="primary" rounded="lg" class="pa-5 text-white h-100" style="border-bottom: 3px solid #C6A15B;">
                                <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B; letter-spacing: 0.1em;">
                                    Total Income
                                </div>
                                <div class="font-display text-h3 font-weight-bold mt-2">
                                    PHP {{ formatMoney(incomeReport.total_income) }}
                                </div>
                                <div class="text-caption mt-2" style="color: rgba(255,255,255,0.75);">
                                    {{ incomeReport.payments?.length || 0 }} payment transaction(s)
                                </div>
                            </v-card>
                        </v-col>

                        <v-col cols="12" sm="6" md="4">
                            <v-card variant="outlined" rounded="lg" class="pa-5 h-100" style="border-color: #E6E4DD;">
                                <div class="text-caption font-weight-bold text-uppercase mb-3" style="color: #C6A15B; letter-spacing: 0.1em;">
                                    By Payment Type
                                </div>
                                <div class="d-flex justify-space-between text-body-2 mb-2">
                                    <span style="color: #777777;">Deposit</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_type?.deposit) }}</strong>
                                </div>
                                <div class="d-flex justify-space-between text-body-2 mb-2">
                                    <span style="color: #777777;">Rental</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_type?.rental) }}</strong>
                                </div>
                                <div class="d-flex justify-space-between text-body-2">
                                    <span style="color: #777777;">Penalty</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_type?.penalty) }}</strong>
                                </div>
                            </v-card>
                        </v-col>

                        <v-col cols="12" sm="6" md="4">
                            <v-card variant="outlined" rounded="lg" class="pa-5 h-100" style="border-color: #E6E4DD;">
                                <div class="text-caption font-weight-bold text-uppercase mb-3" style="color: #C6A15B; letter-spacing: 0.1em;">
                                    By Payment Method
                                </div>
                                <div class="d-flex justify-space-between text-body-2 mb-2">
                                    <span style="color: #777777;">Cash</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_method?.cash) }}</strong>
                                </div>
                                <div class="d-flex justify-space-between text-body-2 mb-2">
                                    <span style="color: #777777;">GCash</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_method?.gcash) }}</strong>
                                </div>
                                <div class="d-flex justify-space-between text-body-2">
                                    <span style="color: #777777;">Card</span>
                                    <strong>PHP {{ formatMoney(incomeReport.by_method?.card) }}</strong>
                                </div>
                            </v-card>
                        </v-col>
                    </v-row>

                    <v-table hover>
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Booking Ref</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th>Paid At</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!incomeReport.payments?.length">
                                <td colspan="7" class="text-center py-8" style="color: #777777;">
                                    No payments recorded for the selected period.
                                </td>
                            </tr>
                            <tr v-for="p in incomeReport.payments" :key="p.id">
                                <td class="font-weight-bold" style="color: #101D32;">{{ p.receipt_number }}</td>
                                <td>{{ p.booking?.reference_code || '—' }}</td>
                                <td>{{ p.booking?.customer?.name || '—' }}</td>
                                <td class="text-capitalize">{{ p.type }}</td>
                                <td class="text-uppercase">{{ p.method }}</td>
                                <td>{{ formatDateTime(p.paid_at) }}</td>
                                <td class="text-right font-weight-bold">PHP {{ formatMoney(p.amount) }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-window-item>

                <!-- Most Rented Vehicles Tab -->
                <v-window-item value="most-rented" class="pa-6">
                    <v-table hover>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Vehicle</th>
                                <th>Category</th>
                                <th>Daily Rate</th>
                                <th class="text-center">Total Rentals</th>
                                <th class="text-right">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!mostRented.length">
                                <td colspan="6" class="text-center py-8" style="color: #777777;">
                                    No vehicle data available.
                                </td>
                            </tr>
                            <tr v-for="(v, index) in mostRented" :key="v.id">
                                <td>
                                    <v-chip
                                        size="small"
                                        :color="index === 0 ? 'secondary' : 'primary'"
                                        :variant="index === 0 ? 'flat' : 'tonal'"
                                        class="font-weight-bold"
                                    >
                                        #{{ index + 1 }}
                                    </v-chip>
                                </td>
                                <td>
                                    <div class="d-flex align-center ga-3 py-2">
                                        <v-avatar rounded="lg" size="46" color="#EFECE6">
                                            <v-img v-if="v.image_url" :src="v.image_url" cover />
                                            <v-icon v-else icon="mdi-car" color="primary" />
                                        </v-avatar>
                                        <div>
                                            <div class="font-weight-bold" style="color: #101D32;">
                                                {{ v.brand }} {{ v.model }} ({{ v.year }})
                                            </div>
                                            <div class="text-caption" style="color: #777777;">{{ v.plate_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ v.category?.name || '—' }}</td>
                                <td>PHP {{ formatMoney(v.daily_rate) }}</td>
                                <td class="text-center font-weight-bold">{{ v.rental_count ?? 0 }}</td>
                                <td class="text-right font-weight-bold" style="color: #101D32;">
                                    PHP {{ formatMoney(v.total_revenue || 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-window-item>

                <!-- Overdue Rentals Tab -->
                <v-window-item value="overdue" class="pa-6">
                    <v-alert v-if="!overdueRentals.length" type="success" variant="tonal">
                        Great news — there are currently no overdue rentals!
                    </v-alert>

                    <v-table v-else hover>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Date Out</th>
                                <th>Scheduled Return</th>
                                <th>Days Overdue</th>
                                <th class="text-right">Est. Penalty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in overdueRentals" :key="r.id">
                                <td class="font-weight-bold" style="color: #B83B3B;">
                                    {{ r.booking?.reference_code }}
                                </td>
                                <td>
                                    <div class="font-weight-medium">{{ r.booking?.customer?.name }}</div>
                                    <div class="text-caption" style="color: #777777;">{{ r.booking?.customer?.contact_number }}</div>
                                </td>
                                <td>
                                    {{ r.booking?.vehicle?.brand }} {{ r.booking?.vehicle?.model }}
                                    <div class="text-caption" style="color: #777777;">{{ r.booking?.vehicle?.plate_number }}</div>
                                </td>
                                <td>{{ formatDateTime(r.date_out) }}</td>
                                <td>{{ formatDateTime(r.booking?.return_date) }}</td>
                                <td>
                                    <v-chip color="error" size="small" variant="tonal" class="font-weight-bold">
                                        {{ overdueDays(r) }} day(s)
                                    </v-chip>
                                </td>
                                <td class="text-right font-weight-bold" style="color: #B83B3B;">
                                    PHP {{ formatMoney(overdueDays(r) * Number(r.booking?.daily_rate || 0)) }}
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-window-item>
            </v-window>
        </v-card>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '@/plugins/axios';

const activeTab = ref('income');
const loading = ref(false);

const incomeFilters = reactive({
    date_from: '',
    date_to: '',
});

const incomeReport = ref({
    total_income: 0,
    by_type: { deposit: 0, rental: 0, penalty: 0 },
    by_method: { cash: 0, gcash: 0, card: 0 },
    payments: [],
});

const mostRented = ref([]);
const overdueRentals = ref([]);

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function overdueDays(rental) {
    const due = new Date(rental.booking?.return_date);
    const diffMs = Date.now() - due.getTime();
    return Math.max(1, Math.ceil(diffMs / (1000 * 60 * 60 * 24)));
}

function printReport() {
    window.print();
}

async function fetchIncome() {
    const params = {};
    if (incomeFilters.date_from) params.date_from = incomeFilters.date_from;
    if (incomeFilters.date_to) params.date_to = incomeFilters.date_to;
    const { data } = await api.get('/admin/reports/income', { params });
    incomeReport.value = data;
}

function resetIncomeFilter() {
    incomeFilters.date_from = '';
    incomeFilters.date_to = '';
    fetchIncome();
}

async function fetchAllReports() {
    loading.value = true;
    try {
        const [, mostRentedRes, overdueRes] = await Promise.all([
            fetchIncome(),
            api.get('/admin/reports/most-rented'),
            api.get('/admin/reports/overdue'),
        ]);
        mostRented.value = mostRentedRes.data;
        overdueRentals.value = overdueRes.data;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchAllReports);
</script>
