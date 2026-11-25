<template>
    <v-container class="py-10">
        <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-6" />

        <v-alert v-else-if="error" type="error" variant="tonal" class="mb-6">
            {{ error }}
        </v-alert>

        <v-card v-else-if="booking" max-width="820" class="mx-auto pa-6 pa-sm-8" elevation="2" border rounded="lg">
            <!-- Header -->
            <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
                <div>
                    <div class="text-overline text-primary font-weight-bold">
                        Car Rental — Official Booking Receipt
                    </div>
                    <h1 class="text-h4 font-weight-bold text-secondary">
                        {{ booking.reference_code }}
                    </h1>
                    <div class="text-caption text-medium-emphasis">
                        Booked on {{ formatDateTime(booking.created_at) }}
                    </div>
                </div>

                <BookingStatusChip :status="booking.status" />
            </div>

            <v-alert type="success" variant="tonal" density="comfortable" class="mb-6 d-print-none">
                Your booking has been submitted! Save your reference code
                <strong>{{ booking.reference_code }}</strong>. A copy of this receipt has also been sent to
                <strong>{{ booking.customer?.email }}</strong>.
            </v-alert>

            <v-row class="mb-4">
                <v-col cols="12" sm="6">
                    <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                        <div class="text-overline text-primary font-weight-bold mb-2">
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
                    <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                        <div class="text-overline text-primary font-weight-bold mb-2">
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
            <v-card variant="tonal" color="primary" rounded="lg" class="pa-5 mb-6">
                <v-row>
                    <v-col cols="12" sm="6">
                        <div class="text-caption text-uppercase font-weight-bold">Pickup Date &amp; Time</div>
                        <div class="text-subtitle-1 font-weight-bold">
                            {{ formatDateTime(booking.pickup_date) }}
                        </div>
                    </v-col>
                    <v-col cols="12" sm="6">
                        <div class="text-caption text-uppercase font-weight-bold">Return Date &amp; Time</div>
                        <div class="text-subtitle-1 font-weight-bold">
                            {{ formatDateTime(booking.return_date) }}
                        </div>
                    </v-col>
                </v-row>

                <v-divider class="my-4" />

                <div class="d-flex justify-space-between text-body-2 mb-1">
                    <span>Daily Rate</span>
                    <span>PHP {{ formatMoney(booking.daily_rate) }}</span>
                </div>
                <div class="d-flex justify-space-between text-body-2 mb-2">
                    <span>Total Days</span>
                    <span>{{ booking.total_days }} day(s)</span>
                </div>
                <div class="d-flex justify-space-between text-h5 font-weight-bold">
                    <span>Total Amount</span>
                    <span>PHP {{ formatMoney(booking.total_amount) }}</span>
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
                <v-btn color="primary" variant="flat" prepend-icon="mdi-printer" @click="printReceipt">
                    Print Receipt
                </v-btn>
                <v-btn to="/cars" variant="outlined" color="secondary" prepend-icon="mdi-car-multiple">
                    Browse More Cars
                </v-btn>
                <v-spacer />
                <v-btn to="/" variant="text" color="secondary">
                    Back to Home
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/plugins/axios';
import BookingStatusChip from '@/components/BookingStatusChip.vue';

const route = useRoute();
const booking = ref(null);
const loading = ref(true);
const error = ref('');

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
