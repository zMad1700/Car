<template>
    <v-container class="py-10">
        <v-progress-linear v-if="loading" indeterminate color="secondary" class="mb-6" />

        <v-alert v-else-if="error" type="error" variant="tonal" class="mb-6">
            {{ error }}
        </v-alert>

        <v-card v-else-if="booking" max-width="820" class="luxury-card mx-auto pa-6 pa-sm-9" style="border-top: 4px solid #101D32 !important;">
            <!-- Header -->
            <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
                <div>
                    <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                        Car Rental — Official Booking Receipt
                    </div>
                    <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                        {{ booking.reference_code }}
                    </h1>
                    <div class="text-caption mt-1" style="color: #777777;">
                        Booked on {{ formatDateTime(booking.created_at) }}
                    </div>
                </div>

                <BookingStatusChip :status="booking.status" />
            </div>

            <v-alert
                :type="booking.status === 'confirmed' ? 'success' : (booking.status === 'cancelled' ? 'error' : 'info')"
                variant="tonal"
                density="comfortable"
                class="mb-6 d-print-none"
            >
                <template v-if="booking.status === 'confirmed'">
                    Your booking is confirmed! Your official receipt and reference code
                    <strong>{{ booking.reference_code }}</strong> have been sent to
                    <strong>{{ booking.customer?.email }}</strong>.
                </template>
                <template v-else-if="booking.status === 'cancelled'">
                    This booking has been cancelled.
                    <span v-if="booking.cancellation_reason">
                        <strong>Reason:</strong> {{ booking.cancellation_reason }}
                    </span>
                    <span v-if="refundPayment">
                        &bull; <strong>Refund Issued:</strong> PHP {{ formatMoney(Math.abs(Number(refundPayment.amount))) }}
                        via {{ refundPayment.method.toUpperCase() }} ({{ refundPayment.receipt_number }})
                    </span>
                </template>
                <template v-else>
                    Your reservation has been submitted! Save your reference code
                    <strong>{{ booking.reference_code }}</strong>. Once an administrator confirms your booking,
                    the official receipt will be sent to <strong>{{ booking.customer?.email }}</strong>.
                </template>
            </v-alert>

            <v-row class="mb-4">
                <v-col cols="12" sm="6">
                    <v-card variant="outlined" rounded="lg" class="pa-5 h-100" style="border-color: #E6E4DD;">
                        <div class="text-caption font-weight-bold text-uppercase mb-3" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Customer Details
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Full Name:</strong> {{ booking.customer?.name }}
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Email:</strong> {{ booking.customer?.email }}
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Contact:</strong> {{ booking.customer?.contact_number }}
                        </div>
                        <div class="text-body-2">
                            <strong>Address:</strong> {{ booking.customer?.address }}
                        </div>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                    <v-card variant="outlined" rounded="lg" class="pa-5 h-100" style="border-color: #E6E4DD;">
                        <div class="text-caption font-weight-bold text-uppercase mb-3" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Vehicle Details
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Vehicle:</strong>
                            {{ booking.vehicle?.brand }} {{ booking.vehicle?.model }} ({{ booking.vehicle?.year }})
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Category:</strong> {{ booking.vehicle?.category?.name || '—' }}
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Plate Number:</strong> {{ booking.vehicle?.plate_number }}
                        </div>
                        <div class="text-body-2 text-capitalize">
                            <strong>Transmission:</strong> {{ booking.vehicle?.transmission }} &bull;
                            {{ booking.vehicle?.seats }} seats
                        </div>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Schedule & Breakdown -->
            <v-card color="primary" rounded="lg" class="pa-6 mb-6 text-white" style="border-bottom: 3px solid #C6A15B;">
                <v-row>
                    <v-col cols="12" sm="6">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B; letter-spacing: 0.1em;">
                            Pickup Date &amp; Time
                        </div>
                        <div class="text-subtitle-1 font-weight-bold mt-1">
                            {{ formatDateTime(booking.pickup_date) }}
                        </div>
                    </v-col>
                    <v-col cols="12" sm="6">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B; letter-spacing: 0.1em;">
                            Return Date &amp; Time
                        </div>
                        <div class="text-subtitle-1 font-weight-bold mt-1">
                            {{ formatDateTime(booking.return_date) }}
                        </div>
                    </v-col>
                </v-row>

                <v-divider class="my-4" style="border-color: rgba(198, 161, 91, 0.3);" />

                <div class="d-flex justify-space-between text-body-2 mb-1" style="color: rgba(255,255,255,0.8);">
                    <span>Daily Rate</span>
                    <span>PHP {{ formatMoney(booking.daily_rate) }}</span>
                </div>
                <div class="d-flex justify-space-between text-body-2 mb-1" style="color: rgba(255,255,255,0.8);">
                    <span>Total Days</span>
                    <span>{{ booking.total_days }} day(s)</span>
                </div>
                <div class="d-flex justify-space-between text-body-2 mb-2" style="color: rgba(255,255,255,0.8);">
                    <span>Total Booking Amount</span>
                    <span>PHP {{ formatMoney(booking.total_amount) }}</span>
                </div>
                <template v-if="totalPaid > 0">
                    <div class="d-flex justify-space-between text-body-2 mb-1" style="color: #A7F3D0;">
                        <span>
                            Downpayment / Paid
                            <span v-if="firstPaymentReceipt">({{ firstPaymentReceipt }})</span>
                        </span>
                        <span>- PHP {{ formatMoney(totalPaid) }}</span>
                    </div>
                    <div class="d-flex justify-space-between align-baseline mt-2">
                        <span class="text-subtitle-1 font-weight-bold">Remaining Balance</span>
                        <span class="font-display text-h4 font-weight-bold" style="color: #C6A15B;">
                            PHP {{ formatMoney(remainingBalance) }}
                        </span>
                    </div>
                </template>
                <div v-else class="d-flex justify-space-between align-baseline">
                    <span class="text-subtitle-1 font-weight-bold">Total Amount</span>
                    <span class="font-display text-h4 font-weight-bold" style="color: #C6A15B;">
                        PHP {{ formatMoney(booking.total_amount) }}
                    </span>
                </div>
            </v-card>

            <!-- Reminders -->
            <v-alert type="warning" variant="tonal" border="start" class="mb-6">
                <div class="font-weight-bold mb-1">Important Reminders:</div>
                <ul class="pl-4 mb-0 text-body-2">
                    <li>Please bring a <strong>valid government ID</strong> and your <strong>driver's license</strong> upon pickup.</li>
                    <li><strong>Late return penalty applies</strong> if the vehicle is returned past the scheduled return date and time.</li>
                </ul>
            </v-alert>

            <!-- Print & Navigation Buttons -->
            <div class="d-flex flex-wrap ga-3 d-print-none">
                <v-btn
                    color="secondary"
                    variant="flat"
                    prepend-icon="mdi-printer"
                    class="font-weight-bold text-primary px-6"
                    @click="printReceipt"
                >
                    Print Receipt
                </v-btn>
                <v-btn to="/cars" variant="outlined" color="primary" prepend-icon="mdi-car-multiple">
                    Browse More Cars
                </v-btn>
                <v-spacer />
                <v-btn to="/" variant="text" color="primary">
                    Back to Home
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/plugins/axios';
import BookingStatusChip from '@/components/BookingStatusChip.vue';

const route = useRoute();
const booking = ref(null);
const loading = ref(true);
const error = ref('');

const totalPaid = computed(() =>
    (booking.value?.payments || [])
        .filter((p) => p.type !== 'refund')
        .reduce((sum, p) => sum + Number(p.amount || 0), 0)
);

const refundPayment = computed(() =>
    (booking.value?.payments || []).find((p) => p.type === 'refund') || null
);

const remainingBalance = computed(() =>
    Math.max(0, Number(booking.value?.total_amount || 0) - totalPaid.value)
);

const firstPaymentReceipt = computed(() =>
    booking.value?.payments?.[0]?.receipt_number || null
);

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

function printReceipt() {
    window.print();
}

async function fetchReceipt() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get(`/bookings/${route.params.referenceCode}/receipt`);
        booking.value = data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Booking receipt not found.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchReceipt);
</script>
