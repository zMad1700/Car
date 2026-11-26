<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7 d-print-none">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Financial Ledger
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Payments
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Record customer payments (deposit, rental, penalty) and issue official receipts.
                </p>
            </div>

            <div class="d-flex align-center ga-2">
                <v-btn
                    color="secondary"
                    variant="flat"
                    prepend-icon="mdi-cash-plus"
                    class="font-weight-bold text-primary px-5"
                    @click="openCreateDialog"
                >
                    Record Payment
                </v-btn>
                <v-btn
                    variant="outlined"
                    color="primary"
                    prepend-icon="mdi-refresh"
                    :loading="loading"
                    @click="fetchData"
                >
                    Refresh
                </v-btn>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <v-row class="mb-4 d-print-none">
            <v-col v-for="card in summaryCards" :key="card.label" cols="12" sm="6" md="3">
                <v-card class="luxury-card luxury-card-hover pa-5 h-100">
                    <div class="d-flex align-start justify-space-between mb-2">
                        <span class="text-caption font-weight-bold text-uppercase" style="color: #777777; letter-spacing: 0.08em;">
                            {{ card.label }}
                        </span>
                        <v-avatar size="36" rounded="lg" :color="card.iconBg">
                            <v-icon :icon="card.icon" :color="card.iconColor" size="18" />
                        </v-avatar>
                    </div>
                    <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                        PHP {{ formatMoney(card.amount) }}
                    </div>
                    <div class="text-caption mt-1" style="color: #777777;">
                        {{ card.count }} transaction(s)
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Filters & Table -->
        <v-card class="luxury-card overflow-hidden d-print-none">
            <v-card-title class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" md="5">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search receipt #, booking ref, or customer"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select
                            v-model="typeFilter"
                            :items="typeOptions"
                            label="Payment Type"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                        <v-select
                            v-model="methodFilter"
                            :items="methodOptions"
                            label="Payment Method"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                </v-row>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="filteredPayments"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.receipt_number="{ item }">
                    <div class="py-2">
                        <div class="font-weight-bold" style="color: #101D32;">
                            {{ item.receipt_number }}
                        </div>
                        <div class="text-caption" style="color: #777777;">
                            {{ formatDateTime(item.paid_at) }}
                        </div>
                    </div>
                </template>

                <template #item.booking="{ item }">
                    <div class="py-2">
                        <div class="font-weight-medium" style="color: #101D32;">
                            {{ item.booking?.reference_code || '—' }}
                        </div>
                        <div class="text-caption" style="color: #777777;">
                            {{ item.booking?.customer?.name || '—' }}
                        </div>
                    </div>
                </template>

                <template #item.vehicle="{ item }">
                    <span class="text-body-2" style="color: #252525;">
                        {{ item.booking?.vehicle?.brand }} {{ item.booking?.vehicle?.model }}
                    </span>
                </template>

                <template #item.type="{ item }">
                    <v-chip
                        size="small"
                        variant="tonal"
                        :color="typeColor(item.type)"
                        class="text-capitalize font-weight-medium"
                    >
                        {{ item.type }}
                    </v-chip>
                </template>

                <template #item.method="{ item }">
                    <v-chip
                        size="small"
                        variant="outlined"
                        :prepend-icon="methodIcon(item.method)"
                        class="text-uppercase font-weight-medium"
                        style="border-color: #E6E4DD; color: #252525;"
                    >
                        {{ item.method }}
                    </v-chip>
                </template>

                <template #item.amount="{ item }">
                    <span class="font-weight-bold" style="color: #101D32;">
                        PHP {{ formatMoney(item.amount) }}
                    </span>
                </template>

                <template #item.actions="{ item }">
                    <v-btn
                        icon="mdi-receipt-text-outline"
                        size="small"
                        variant="text"
                        color="primary"
                        title="View / Print Receipt"
                        @click="openReceipt(item)"
                    />
                </template>
            </v-data-table>
        </v-card>

        <!-- Record Payment Dialog -->
        <v-dialog v-model="formDialog" max-width="640" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    Record Payment
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-form ref="formRef" @submit.prevent="submitPayment">
                    <v-card-text class="pa-5">
                        <v-alert v-if="formError" type="error" variant="tonal" density="compact" class="mb-4">
                            {{ formError }}
                        </v-alert>

                        <v-alert v-if="!payableBookings.length" type="info" variant="tonal" class="mb-4">
                            No active bookings available to record a payment against.
                        </v-alert>

                        <v-select
                            v-model="form.booking_id"
                            :items="payableBookings"
                            :item-title="bookingLabel"
                            item-value="id"
                            label="Select Booking *"
                            density="comfortable"
                            :rules="[(v) => !!v || 'Please select a booking.']"
                            class="mb-3"
                            @update:model-value="onBookingSelected"
                        />

                        <!-- Balance Summary for Selected Booking -->
                        <v-card
                            v-if="selectedBookingSummary"
                            variant="outlined"
                            rounded="lg"
                            class="pa-4 mb-4"
                            style="border-color: #E6E4DD; background-color: #FAF9F6;"
                        >
                            <v-row dense class="text-body-2">
                                <v-col cols="6" sm="3">
                                    <div class="text-caption" style="color: #777777;">Rental Total</div>
                                    <div class="font-weight-bold">PHP {{ formatMoney(selectedBookingSummary.rentalTotal) }}</div>
                                </v-col>
                                <v-col cols="6" sm="3">
                                    <div class="text-caption" style="color: #777777;">Late Penalty</div>
                                    <div class="font-weight-bold">PHP {{ formatMoney(selectedBookingSummary.penalty) }}</div>
                                </v-col>
                                <v-col cols="6" sm="3">
                                    <div class="text-caption" style="color: #777777;">Already Paid</div>
                                    <div class="font-weight-bold text-success">PHP {{ formatMoney(selectedBookingSummary.paid) }}</div>
                                </v-col>
                                <v-col cols="6" sm="3">
                                    <div class="text-caption" style="color: #777777;">Balance</div>
                                    <div class="font-weight-bold" style="color: #101D32;">PHP {{ formatMoney(selectedBookingSummary.balance) }}</div>
                                </v-col>
                            </v-row>

                            <!-- Quick Fill Helpers for Easy Testing -->
                            <div class="d-flex flex-wrap align-center ga-2 mt-3 pt-3" style="border-top: 1px solid #EFECE6;">
                                <span class="text-caption font-weight-bold" style="color: #777777;">Quick Fill:</span>
                                <v-chip
                                    v-if="selectedBookingSummary.balance > 0"
                                    size="small"
                                    color="primary"
                                    variant="tonal"
                                    class="cursor-pointer"
                                    @click="quickFill('rental', selectedBookingSummary.balance)"
                                >
                                    Full Balance (PHP {{ formatMoney(selectedBookingSummary.balance) }})
                                </v-chip>
                                <v-chip
                                    size="small"
                                    color="secondary"
                                    variant="flat"
                                    class="cursor-pointer text-primary font-weight-medium"
                                    @click="quickFill('deposit', selectedBookingSummary.rentalTotal * 0.5)"
                                >
                                    50% Deposit (PHP {{ formatMoney(selectedBookingSummary.rentalTotal * 0.5) }})
                                </v-chip>
                                <v-chip
                                    v-if="selectedBookingSummary.penalty > 0"
                                    size="small"
                                    color="error"
                                    variant="tonal"
                                    class="cursor-pointer"
                                    @click="quickFill('penalty', selectedBookingSummary.penalty)"
                                >
                                    Penalty (PHP {{ formatMoney(selectedBookingSummary.penalty) }})
                                </v-chip>
                            </div>
                        </v-card>

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.type"
                                    :items="recordTypeOptions"
                                    label="Payment Type *"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Type is required.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.method"
                                    :items="methodOptions"
                                    label="Payment Method *"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Method is required.']"
                                />
                            </v-col>
                        </v-row>

                        <v-row dense class="mt-1">
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model.number="form.amount"
                                    label="Amount (PHP) *"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    density="comfortable"
                                    :rules="[(v) => (Number(v) > 0) || 'Amount must be greater than 0.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.paid_at"
                                    label="Paid Date & Time *"
                                    type="datetime-local"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Paid date & time is required.']"
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
                            color="secondary"
                            variant="flat"
                            class="font-weight-bold text-primary px-5"
                            :loading="saving"
                            :disabled="!payableBookings.length"
                        >
                            Save Payment
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Printable Official Payment Receipt Dialog -->
        <v-dialog v-model="receiptDialog" max-width="540">
            <v-card v-if="viewingPayment" class="luxury-card pa-6" style="border-top: 4px solid #101D32 !important;">
                <div class="d-flex justify-space-between align-start mb-4">
                    <div>
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.14em;">
                            Official Payment Receipt
                        </div>
                        <h2 class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            {{ viewingPayment.receipt_number }}
                        </h2>
                        <div class="text-caption" style="color: #777777;">
                            Paid on {{ formatDateTime(viewingPayment.paid_at) }}
                        </div>
                    </div>
                    <v-chip size="small" color="success" variant="tonal" class="font-weight-bold">
                        PAID
                    </v-chip>
                </div>

                <v-divider class="mb-4" style="border-color: #EFECE6;" />

                <div class="d-flex flex-column ga-2 text-body-2 mb-5">
                    <div class="d-flex justify-space-between">
                        <span style="color: #777777;">Booking Reference</span>
                        <span class="font-weight-bold" style="color: #101D32;">{{ viewingPayment.booking?.reference_code }}</span>
                    </div>
                    <div class="d-flex justify-space-between">
                        <span style="color: #777777;">Customer</span>
                        <span class="font-weight-medium">{{ viewingPayment.booking?.customer?.name }}</span>
                    </div>
                    <div class="d-flex justify-space-between">
                        <span style="color: #777777;">Vehicle</span>
                        <span>
                            {{ viewingPayment.booking?.vehicle?.brand }} {{ viewingPayment.booking?.vehicle?.model }}
                        </span>
                    </div>
                    <div class="d-flex justify-space-between">
                        <span style="color: #777777;">Payment Type</span>
                        <span class="text-capitalize font-weight-medium">{{ viewingPayment.type }}</span>
                    </div>
                    <div class="d-flex justify-space-between">
                        <span style="color: #777777;">Payment Method</span>
                        <span class="text-uppercase font-weight-medium">{{ viewingPayment.method }}</span>
                    </div>
                </div>

                <v-card color="primary" rounded="lg" class="pa-4 text-white d-flex justify-space-between align-baseline mb-5" style="border-bottom: 3px solid #C6A15B;">
                    <span class="text-subtitle-2 font-weight-bold">Amount Paid</span>
                    <span class="font-display text-h5 font-weight-bold" style="color: #C6A15B;">
                        PHP {{ formatMoney(viewingPayment.amount) }}
                    </span>
                </v-card>

                <div class="d-flex justify-end ga-2 d-print-none">
                    <v-btn color="secondary" variant="flat" class="font-weight-bold text-primary" prepend-icon="mdi-printer" @click="printReceipt">
                        Print
                    </v-btn>
                    <v-btn variant="text" color="primary" @click="receiptDialog = false">
                        Close
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3500">
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '@/plugins/axios';

const payments = ref([]);
const bookings = ref([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref(null);
const methodFilter = ref(null);

const formDialog = ref(false);
const receiptDialog = ref(false);
const formRef = ref(null);
const saving = ref(false);
const formError = ref('');
const viewingPayment = ref(null);

const typeOptions = [
    { title: 'Deposit', value: 'deposit' },
    { title: 'Rental', value: 'rental' },
    { title: 'Penalty', value: 'penalty' },
    { title: 'Refund', value: 'refund' },
];

const recordTypeOptions = [
    { title: 'Deposit', value: 'deposit' },
    { title: 'Rental', value: 'rental' },
    { title: 'Penalty', value: 'penalty' },
];

const methodOptions = [
    { title: 'Cash', value: 'cash' },
    { title: 'GCash', value: 'gcash' },
    { title: 'Card', value: 'card' },
];

const form = reactive({
    booking_id: null,
    amount: '',
    type: 'rental',
    method: 'cash',
    paid_at: '',
});

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Receipt #', key: 'receipt_number' },
    { title: 'Booking & Customer', key: 'booking', sortable: false },
    { title: 'Vehicle', key: 'vehicle', sortable: false },
    { title: 'Type', key: 'type' },
    { title: 'Method', key: 'method' },
    { title: 'Amount', key: 'amount' },
    { title: 'Receipt', key: 'actions', sortable: false, align: 'end' },
];

const payableBookings = computed(() =>
    bookings.value.filter((b) => b.status !== 'cancelled')
);

const selectedBooking = computed(() =>
    bookings.value.find((b) => b.id === form.booking_id) || null
);

const selectedBookingSummary = computed(() => {
    const b = selectedBooking.value;
    if (!b) return null;
    const rentalTotal = Number(b.total_amount || 0);
    const penalty = Number(b.rental?.penalty_amount || 0);
    const paid = (b.payments || []).reduce((sum, p) => sum + Number(p.amount || 0), 0);
    const balance = Math.max(0, rentalTotal + penalty - paid);
    return { rentalTotal, penalty, paid, balance };
});

const summaryCards = computed(() => {
    const sumBy = (method) => {
        const list = method ? payments.value.filter((p) => p.method === method) : payments.value;
        return {
            amount: list.reduce((sum, p) => sum + Number(p.amount || 0), 0),
            count: list.length,
        };
    };
    const total = sumBy(null);
    const cash = sumBy('cash');
    const gcash = sumBy('gcash');
    const card = sumBy('card');

    return [
        { label: 'Total Collected', ...total, icon: 'mdi-cash-multiple', iconBg: '#101D32', iconColor: '#C6A15B' },
        { label: 'Cash', ...cash, icon: 'mdi-cash', iconBg: 'rgba(46, 125, 91, 0.12)', iconColor: '#2E7D5B' },
        { label: 'GCash', ...gcash, icon: 'mdi-cellphone', iconBg: 'rgba(29, 78, 137, 0.12)', iconColor: '#1D4E89' },
        { label: 'Card', ...card, icon: 'mdi-credit-card-outline', iconBg: 'rgba(198, 161, 91, 0.18)', iconColor: '#C6A15B' },
    ];
});

const filteredPayments = computed(() => {
    return payments.value.filter((item) => {
        if (typeFilter.value && item.type !== typeFilter.value) return false;
        if (methodFilter.value && item.method !== methodFilter.value) return false;
        if (search.value?.trim()) {
            const q = search.value.trim().toLowerCase();
            const receipt = item.receipt_number?.toLowerCase() || '';
            const refCode = item.booking?.reference_code?.toLowerCase() || '';
            const cust = item.booking?.customer?.name?.toLowerCase() || '';
            return receipt.includes(q) || refCode.includes(q) || cust.includes(q);
        }
        return true;
    });
});

function toLocalInput(date = new Date()) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

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

function bookingLabel(b) {
    return `${b.reference_code} — ${b.customer?.name} (${b.vehicle?.brand} ${b.vehicle?.model})`;
}

function typeColor(type) {
    if (type === 'deposit') return 'info';
    if (type === 'rental') return 'success';
    if (type === 'penalty') return 'warning';
    if (type === 'refund') return 'error';
    return 'grey';
}

function methodIcon(method) {
    if (method === 'gcash') return 'mdi-cellphone';
    if (method === 'card') return 'mdi-credit-card-outline';
    return 'mdi-cash';
}

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
}

function quickFill(type, amount) {
    form.type = type;
    form.amount = Number(amount || 0).toFixed(2);
}

function onBookingSelected() {
    if (selectedBookingSummary.value && selectedBookingSummary.value.balance > 0) {
        form.type = 'rental';
        form.amount = selectedBookingSummary.value.balance.toFixed(2);
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const [paymentsRes, bookingsRes] = await Promise.all([
            api.get('/admin/payments'),
            api.get('/admin/bookings'),
        ]);
        payments.value = paymentsRes.data;
        bookings.value = bookingsRes.data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load payments.', 'error');
    } finally {
        loading.value = false;
    }
}

function openCreateDialog() {
    formError.value = '';
    const first = payableBookings.value[0] || null;
    form.booking_id = first?.id ?? null;
    form.type = 'rental';
    form.method = 'cash';
    form.paid_at = toLocalInput();
    form.amount = '';
    formDialog.value = true;
    onBookingSelected();
}

function openReceipt(payment) {
    viewingPayment.value = payment;
    receiptDialog.value = true;
}

function printReceipt() {
    window.print();
}

async function submitPayment() {
    formError.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const { data } = await api.post('/admin/payments', {
            booking_id: form.booking_id,
            amount: form.amount,
            type: form.type,
            method: form.method,
            paid_at: form.paid_at.replace('T', ' '),
        });
        formDialog.value = false;
        notify(`Payment recorded (${data.receipt_number}).`);
        await fetchData();
    } catch (error) {
        const errors = error.response?.data?.errors;
        formError.value = errors
            ? Object.values(errors).flat().join(' ')
            : (error.response?.data?.message || 'Unable to record payment.');
    } finally {
        saving.value = false;
    }
}

onMounted(fetchData);
</script>
