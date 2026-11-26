<template>
    <v-container class="py-12">
        <div class="text-center mb-10">
            <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                Reservation Lookup
            </div>
            <h1 class="font-display text-h3 font-weight-bold mb-2" style="color: #101D32;">
                Track Your Booking
            </h1>
            <div class="gold-divider mx-auto mb-3" />
            <p class="text-body-1" style="color: #777777;">
                Enter your booking reference code and the email address used when reserving.
            </p>
        </div>

        <v-card max-width="640" class="luxury-card mx-auto pa-6 pa-sm-8 mb-8" style="border-top: 4px solid #C6A15B !important;">
            <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                density="comfortable"
                class="mb-5"
                closable
                @click:close="errorMessage = ''"
            >
                {{ errorMessage }}
            </v-alert>

            <v-form ref="formRef" @submit.prevent="trackBooking">
                <v-row dense>
                    <v-col cols="12" sm="6">
                        <v-text-field
                            v-model="form.reference_code"
                            label="Reference Code *"
                            placeholder="BK-20261125-XXXX"
                            prepend-inner-icon="mdi-ticket-confirmation-outline"
                            density="comfortable"
                            :rules="[(v) => !!v?.trim() || 'Reference code is required.']"
                        />
                    </v-col>
                    <v-col cols="12" sm="6">
                        <v-text-field
                            v-model="form.email"
                            label="Email Address *"
                            type="email"
                            placeholder="you@gmail.com"
                            prepend-inner-icon="mdi-email-outline"
                            density="comfortable"
                            :rules="emailRules"
                        />
                    </v-col>
                </v-row>

                <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    block
                    variant="flat"
                    :loading="searching"
                    prepend-icon="mdi-magnify"
                    class="mt-3 font-weight-semibold"
                >
                    Check Booking Status
                </v-btn>
            </v-form>
        </v-card>

        <!-- Booking Result Card -->
        <v-card v-if="booking" max-width="820" class="luxury-card mx-auto pa-6 pa-sm-8">
            <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
                <div>
                    <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                        Booking Found
                    </div>
                    <h2 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                        {{ booking.reference_code }}
                    </h2>
                    <div class="text-caption" style="color: #777777;">
                        Submitted on {{ formatDateTime(booking.created_at) }}
                    </div>
                </div>
                <BookingStatusChip :status="booking.status" />
            </div>

            <v-alert
                v-if="booking.status === 'cancelled' && booking.cancellation_reason"
                type="error"
                variant="tonal"
                border="start"
                class="mb-5"
            >
                <strong>Reason for Cancellation:</strong> {{ booking.cancellation_reason }}
            </v-alert>

            <v-row class="mb-4">
                <v-col cols="12" sm="6">
                    <v-card variant="outlined" rounded="lg" class="pa-4 h-100" style="border-color: #E6E4DD;">
                        <div class="text-caption font-weight-bold text-uppercase mb-2" style="color: #C6A15B;">
                            Customer
                        </div>
                        <div class="text-body-2 mb-1"><strong>Name:</strong> {{ booking.customer?.name }}</div>
                        <div class="text-body-2 mb-1"><strong>Email:</strong> {{ booking.customer?.email }}</div>
                        <div class="text-body-2"><strong>Contact:</strong> {{ booking.customer?.contact_number }}</div>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                    <v-card variant="outlined" rounded="lg" class="pa-4 h-100" style="border-color: #E6E4DD;">
                        <div class="text-caption font-weight-bold text-uppercase mb-2" style="color: #C6A15B;">
                            Vehicle
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Model:</strong> {{ booking.vehicle?.brand }} {{ booking.vehicle?.model }} ({{ booking.vehicle?.year }})
                        </div>
                        <div class="text-body-2 mb-1">
                            <strong>Category:</strong> {{ booking.vehicle?.category?.name || '—' }}
                        </div>
                        <div class="text-body-2 text-capitalize">
                            <strong>Plate:</strong> {{ booking.vehicle?.plate_number }} &bull; {{ booking.vehicle?.transmission }}
                        </div>
                    </v-card>
                </v-col>
            </v-row>

            <v-card color="primary" rounded="lg" class="pa-5 text-white mb-5" style="border-bottom: 3px solid #C6A15B;">
                <v-row>
                    <v-col cols="12" sm="4">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B;">Pickup</div>
                        <div class="text-body-2 font-weight-medium mt-1">{{ formatDateTime(booking.pickup_date) }}</div>
                    </v-col>
                    <v-col cols="12" sm="4">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B;">Return</div>
                        <div class="text-body-2 font-weight-medium mt-1">{{ formatDateTime(booking.return_date) }}</div>
                    </v-col>
                    <v-col cols="12" sm="4">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B;">Total Amount</div>
                        <div class="font-display text-h5 font-weight-bold mt-1" style="color: #C6A15B;">
                            PHP {{ formatMoney(booking.total_amount) }}
                        </div>
                        <div class="text-caption" style="color: rgba(255,255,255,0.75);">
                            {{ booking.total_days }} day(s) &times; PHP {{ formatMoney(booking.daily_rate) }}
                        </div>
                    </v-col>
                </v-row>
            </v-card>

            <div class="d-flex flex-wrap justify-space-between align-center ga-3">
                <div class="text-body-2" style="color: #777777;">
                    <span v-if="booking.rental">
                        <strong>Rental Status:</strong> <span class="text-capitalize">{{ booking.rental.status }}</span>
                    </span>
                </div>
                <v-btn
                    :to="`/receipt/${booking.reference_code}`"
                    color="secondary"
                    variant="flat"
                    class="font-weight-bold text-primary"
                    prepend-icon="mdi-printer"
                >
                    View / Print Receipt
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>

<script setup>
import { reactive, ref } from 'vue';
import api from '@/plugins/axios';
import BookingStatusChip from '@/components/BookingStatusChip.vue';

const formRef = ref(null);
const searching = ref(false);
const errorMessage = ref('');
const booking = ref(null);

const form = reactive({
    reference_code: '',
    email: '',
});

const emailRules = [
    (v) => !!v?.trim() || 'Email is required.',
    (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email address.',
];

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

async function trackBooking() {
    errorMessage.value = '';
    booking.value = null;

    const { valid } = await formRef.value.validate();
    if (!valid) return;

    searching.value = true;
    try {
        const { data } = await api.post('/bookings/track', {
            reference_code: form.reference_code.trim().toUpperCase(),
            email: form.email.trim(),
        });
        booking.value = data;
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'No matching booking found for that reference code and email.';
    } finally {
        searching.value = false;
    }
}
</script>
