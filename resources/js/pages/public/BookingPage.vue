<template>
    <v-container class="py-10">
        <div class="mb-7">
            <v-btn to="/cars" variant="text" color="primary" prepend-icon="mdi-arrow-left" class="px-0 mb-2">
                Back to Fleet
            </v-btn>
            <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                Reservation Request
            </div>
            <h1 class="font-display text-h3 font-weight-bold" style="color: #101D32;">
                Book a Vehicle
            </h1>
            <div class="gold-divider mt-2 mb-3" />
            <p class="text-body-1" style="color: #777777;">
                Complete your reservation details and minimum PHP 500 downpayment below. No customer account required.
            </p>
        </div>

        <v-progress-linear v-if="loadingVehicles" indeterminate color="secondary" class="mb-6" />

        <v-row>
            <!-- Booking Form -->
            <v-col cols="12" md="7">
                <v-card class="luxury-card pa-6 pa-sm-7">
                    <v-alert
                        v-if="errorMessage"
                        type="error"
                        variant="tonal"
                        class="mb-5"
                        closable
                        @click:close="errorMessage = ''"
                    >
                        {{ errorMessage }}
                    </v-alert>

                    <v-form ref="formRef" @submit.prevent="openPaymentModal">
                        <v-select
                            v-model="form.vehicle_id"
                            :items="availableVehicles"
                            :item-title="(v) => `${v.brand} ${v.model} (${v.year}) — PHP ${formatMoney(v.daily_rate)}/day`"
                            item-value="id"
                            label="Select Vehicle *"
                            prepend-inner-icon="mdi-car"
                            density="comfortable"
                            :rules="[(v) => !!v || 'Please select an available vehicle.']"
                            class="mb-3"
                        />

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.name"
                                    label="Full Name *"
                                    prepend-inner-icon="mdi-account-outline"
                                    density="comfortable"
                                    :rules="[(v) => !!v?.trim() || 'Full name is required.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.contact_number"
                                    label="Contact Number *"
                                    prepend-inner-icon="mdi-phone-outline"
                                    density="comfortable"
                                    :rules="contactRules"
                                />
                            </v-col>
                        </v-row>

                        <v-text-field
                            v-model="form.email"
                            label="Gmail / Email Address *"
                            type="email"
                            prepend-inner-icon="mdi-email-outline"
                            density="comfortable"
                            :rules="emailRules"
                            hint="Your booking receipt and reference code will be sent here once confirmed."
                            persistent-hint
                            class="mb-3 mt-1"
                        />

                        <v-textarea
                            v-model="form.address"
                            label="Complete Address *"
                            prepend-inner-icon="mdi-map-marker-outline"
                            density="comfortable"
                            rows="2"
                            auto-grow
                            :rules="[(v) => !!v?.trim() || 'Address is required.']"
                            class="mb-3"
                        />

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.pickup_date"
                                    label="Pickup Date & Time *"
                                    type="datetime-local"
                                    density="comfortable"
                                    :rules="pickupRules"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.return_date"
                                    label="Return Date & Time *"
                                    type="datetime-local"
                                    density="comfortable"
                                    :rules="returnRules"
                                />
                            </v-col>
                        </v-row>

                        <!-- Downpayment Section (Minimum PHP 500) -->
                        <v-card
                            variant="outlined"
                            rounded="lg"
                            class="pa-4 mt-2 mb-4"
                            style="border-color: #C6A15B; background-color: #FAF9F6;"
                        >
                            <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-3">
                                <div>
                                    <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.1em;">
                                        Required Reservation Downpayment
                                    </div>
                                    <div class="text-body-2 font-weight-bold" style="color: #101D32;">
                                        Minimum PHP 500.00 to reserve
                                    </div>
                                </div>
                                <v-chip size="x-small" color="primary" variant="tonal">
                                    Simulated Payment
                                </v-chip>
                            </div>

                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        v-model="form.downpayment_method"
                                        :items="paymentMethods"
                                        label="Payment Method *"
                                        prepend-inner-icon="mdi-wallet-outline"
                                        density="comfortable"
                                        :rules="[(v) => !!v || 'Payment method is required.']"
                                    />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        v-model.number="form.downpayment_amount"
                                        label="Downpayment Amount (PHP) *"
                                        type="number"
                                        step="50"
                                        min="500"
                                        prepend-inner-icon="mdi-cash"
                                        density="comfortable"
                                        :rules="downpaymentRules"
                                    />
                                </v-col>
                            </v-row>

                            <div class="d-flex flex-wrap align-center ga-2 mt-1">
                                <span class="text-caption font-weight-bold" style="color: #777777;">Quick Select:</span>
                                <v-chip
                                    size="x-small"
                                    color="primary"
                                    variant="tonal"
                                    class="cursor-pointer"
                                    @click="form.downpayment_amount = 500"
                                >
                                    PHP 500 (Minimum)
                                </v-chip>
                                <v-chip
                                    v-if="estimatedTotal >= 1000"
                                    size="x-small"
                                    color="secondary"
                                    variant="flat"
                                    class="cursor-pointer text-primary font-weight-medium"
                                    @click="form.downpayment_amount = Math.round(estimatedTotal * 0.5)"
                                >
                                    50% (PHP {{ formatMoney(estimatedTotal * 0.5) }})
                                </v-chip>
                                <v-chip
                                    v-if="estimatedTotal >= 500"
                                    size="x-small"
                                    variant="outlined"
                                    color="primary"
                                    class="cursor-pointer"
                                    @click="form.downpayment_amount = estimatedTotal"
                                >
                                    Full (PHP {{ formatMoney(estimatedTotal) }})
                                </v-chip>
                            </div>
                        </v-card>

                        <v-checkbox
                            v-model="form.terms_accepted"
                            color="primary"
                            :rules="[(v) => !!v || 'You must agree to the rental terms before booking.']"
                            class="mb-4"
                        >
                            <template #label>
                                <span class="text-body-2" style="color: #252525;">
                                    I agree to bring a valid government ID &amp; driver's license on pickup, and understand that a late return penalty applies.
                                </span>
                            </template>
                        </v-checkbox>

                        <v-btn
                            type="submit"
                            color="secondary"
                            size="x-large"
                            block
                            variant="flat"
                            :loading="submitting"
                            :disabled="!availableVehicles.length"
                            prepend-icon="mdi-shield-check-outline"
                            class="font-weight-bold text-primary"
                        >
                            Pay PHP {{ formatMoney(form.downpayment_amount || 0) }} &amp; Book
                        </v-btn>
                    </v-form>
                </v-card>
            </v-col>

            <!-- Summary Column -->
            <v-col cols="12" md="5">
                <VehicleCard v-if="selectedVehicle" :vehicle="selectedVehicle" class="mb-4" />

                <v-card class="luxury-card pa-6" style="border-top: 3px solid #C6A15B !important;">
                    <div class="text-caption font-weight-bold text-uppercase mb-3" style="color: #C6A15B; letter-spacing: 0.12em;">
                        Estimated Booking Summary
                    </div>

                    <div class="d-flex justify-space-between text-body-2 mb-2">
                        <span style="color: #777777;">Daily Rate</span>
                        <span class="font-weight-medium" style="color: #252525;">
                            PHP {{ formatMoney(selectedVehicle?.daily_rate || 0) }}
                        </span>
                    </div>

                    <div class="d-flex justify-space-between text-body-2 mb-2">
                        <span style="color: #777777;">Total Days (minimum 1)</span>
                        <span class="font-weight-medium" style="color: #252525;">{{ estimatedDays }} day(s)</span>
                    </div>

                    <div class="d-flex justify-space-between text-body-2 mb-2">
                        <span style="color: #777777;">Total Booking Amount</span>
                        <span class="font-weight-bold" style="color: #101D32;">
                            PHP {{ formatMoney(estimatedTotal) }}
                        </span>
                    </div>

                    <div class="d-flex justify-space-between text-body-2 mb-3">
                        <span style="color: #777777;">Downpayment Now ({{ form.downpayment_method.toUpperCase() }})</span>
                        <span class="font-weight-bold text-success">
                            - PHP {{ formatMoney(validDownpayment) }}
                        </span>
                    </div>

                    <v-divider class="mb-3" style="border-color: #EFECE6;" />

                    <div class="d-flex justify-space-between align-baseline">
                        <span class="text-subtitle-1 font-weight-bold" style="color: #101D32;">
                            Balance on Pickup
                        </span>
                        <span class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                            PHP {{ formatMoney(remainingBalance) }}
                        </span>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Simulated Downpayment Checkout Modal -->
        <v-dialog v-model="paymentDialog" max-width="480" persistent>
            <v-card class="luxury-card" style="border-top: 4px solid #C6A15B !important;">
                <v-card-title class="pa-5 d-flex align-center justify-space-between">
                    <div>
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Simulated Checkout
                        </div>
                        <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            Confirm Downpayment
                        </div>
                    </div>
                    <v-chip size="x-small" color="info" variant="tonal">No Real Money</v-chip>
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-card-text class="pa-5">
                    <v-alert v-if="paymentError" type="error" variant="tonal" density="compact" class="mb-4">
                        {{ paymentError }}
                    </v-alert>

                    <v-card color="primary" rounded="lg" class="pa-4 text-white mb-4" style="border-bottom: 3px solid #C6A15B;">
                        <div class="text-caption text-uppercase font-weight-bold" style="color: #C6A15B;">
                            {{ selectedVehicle?.brand }} {{ selectedVehicle?.model }}
                        </div>
                        <div class="d-flex justify-space-between align-baseline mt-2">
                            <span class="text-body-2">Downpayment ({{ form.downpayment_method.toUpperCase() }})</span>
                            <span class="font-display text-h4 font-weight-bold" style="color: #C6A15B;">
                                PHP {{ formatMoney(form.downpayment_amount) }}
                            </span>
                        </div>
                        <div class="text-caption mt-1" style="color: rgba(255,255,255,0.75);">
                            Remaining Balance on Pickup: PHP {{ formatMoney(remainingBalance) }}
                        </div>
                    </v-card>

                    <v-text-field
                        v-model="mockPaymentRef"
                        :label="form.downpayment_method === 'gcash' ? 'Mock GCash Mobile / Ref # (for demo)' : (form.downpayment_method === 'card' ? 'Mock Card Last 4 Digits (for demo)' : 'Cash Counter Note (optional)')"
                        density="comfortable"
                        variant="outlined"
                        hide-details
                        class="mb-3"
                    />

                    <p class="text-caption mb-0" style="color: #777777;">
                        This simulated payment authorizes your PHP {{ formatMoney(form.downpayment_amount) }} deposit and immediately records your reservation.
                    </p>
                </v-card-text>

                <v-divider style="border-color: #EFECE6;" />
                <v-card-actions class="pa-4">
                    <v-btn variant="text" color="primary" :disabled="submitting" @click="paymentDialog = false">
                        Cancel
                    </v-btn>
                    <v-spacer />
                    <v-btn
                        color="secondary"
                        variant="flat"
                        class="font-weight-bold text-primary px-5"
                        :loading="submitting"
                        prepend-icon="mdi-check-decagram"
                        @click="submitBooking"
                    >
                        Authorize &amp; Complete Booking
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/plugins/axios';
import VehicleCard from '@/components/VehicleCard.vue';

const route = useRoute();
const router = useRouter();

const vehicles = ref([]);
const loadingVehicles = ref(true);
const submitting = ref(false);
const errorMessage = ref('');
const paymentDialog = ref(false);
const paymentError = ref('');
const mockPaymentRef = ref('0917-000-0000');
const formRef = ref(null);

const paymentMethods = [
    { title: 'GCash (Simulated)', value: 'gcash' },
    { title: 'Card (Simulated)', value: 'card' },
    { title: 'Cash (Simulated)', value: 'cash' },
];

function toLocalDateTimeInput(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const tomorrowMorning = new Date();
tomorrowMorning.setDate(tomorrowMorning.getDate() + 1);
tomorrowMorning.setHours(9, 0, 0, 0);

const dayAfterMorning = new Date(tomorrowMorning);
dayAfterMorning.setDate(dayAfterMorning.getDate() + 1);

const form = reactive({
    vehicle_id: null,
    name: '',
    address: '',
    contact_number: '',
    email: '',
    pickup_date: toLocalDateTimeInput(tomorrowMorning),
    return_date: toLocalDateTimeInput(dayAfterMorning),
    downpayment_amount: 500,
    downpayment_method: 'gcash',
    terms_accepted: false,
});

const availableVehicles = computed(() =>
    vehicles.value.filter((v) => v.status === 'available')
);

const selectedVehicle = computed(() =>
    vehicles.value.find((v) => v.id === form.vehicle_id) || null
);

const estimatedDays = computed(() => {
    if (!form.pickup_date || !form.return_date) return 1;
    const start = new Date(form.pickup_date);
    const end = new Date(form.return_date);
    const diffMs = end - start;
    if (Number.isNaN(diffMs) || diffMs <= 0) return 1;
    return Math.max(1, Math.ceil(diffMs / (1000 * 60 * 60 * 24)));
});

const estimatedTotal = computed(() => {
    if (!selectedVehicle.value) return 0;
    return estimatedDays.value * Number(selectedVehicle.value.daily_rate);
});

const validDownpayment = computed(() => {
    const val = Number(form.downpayment_amount || 0);
    if (Number.isNaN(val) || val < 0) return 0;
    return Math.min(val, estimatedTotal.value);
});

const remainingBalance = computed(() =>
    Math.max(0, estimatedTotal.value - validDownpayment.value)
);

const emailRules = [
    (v) => !!v?.trim() || 'Email is required.',
    (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email address.',
];

const contactRules = [
    (v) => !!v?.trim() || 'Contact number is required.',
    (v) => v.trim().length >= 7 || 'Please enter a valid contact number.',
];

const pickupRules = [
    (v) => !!v || 'Pickup date and time is required.',
    (v) => new Date(v) >= new Date(Date.now() - 5 * 60 * 1000) || 'Pickup cannot be in the past.',
];

const returnRules = [
    (v) => !!v || 'Return date and time is required.',
    (v) => !form.pickup_date || new Date(v) > new Date(form.pickup_date) || 'Return must be after pickup date and time.',
];

const downpaymentRules = [
    (v) => (v !== '' && v !== null) || 'Downpayment amount is required.',
    (v) => Number(v) >= 500 || 'Minimum downpayment is PHP 500.00.',
    (v) => !estimatedTotal.value || Number(v) <= estimatedTotal.value || `Downpayment cannot exceed PHP ${formatMoney(estimatedTotal.value)}.`,
];

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

async function fetchVehicles() {
    loadingVehicles.value = true;
    try {
        const { data } = await api.get('/vehicles');
        vehicles.value = data;

        const requestedId = Number(route.params.vehicleId);
        const match = availableVehicles.value.find((v) => v.id === requestedId);
        form.vehicle_id = match ? match.id : (availableVehicles.value[0]?.id ?? null);
    } catch (error) {
        errorMessage.value = 'Failed to load vehicles.';
    } finally {
        loadingVehicles.value = false;
    }
}

async function openPaymentModal() {
    errorMessage.value = '';
    paymentError.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    paymentDialog.value = true;
}

async function submitBooking() {
    paymentError.value = '';
    submitting.value = true;
    try {
        const { data } = await api.post('/bookings', {
            vehicle_id: form.vehicle_id,
            name: form.name.trim(),
            address: form.address.trim(),
            contact_number: form.contact_number.trim(),
            email: form.email.trim(),
            pickup_date: form.pickup_date.replace('T', ' '),
            return_date: form.return_date.replace('T', ' '),
            terms_accepted: form.terms_accepted,
            downpayment_amount: form.downpayment_amount,
            downpayment_method: form.downpayment_method,
        });

        paymentDialog.value = false;
        await router.push({
            name: 'receipt',
            params: { referenceCode: data.reference_code },
        });
    } catch (error) {
        const errors = error.response?.data?.errors;
        const msg = errors
            ? Object.values(errors).flat().join(' ')
            : (error.response?.data?.message || 'Could not complete booking.');
        paymentError.value = msg;
        errorMessage.value = msg;
        paymentDialog.value = false;
    } finally {
        submitting.value = false;
    }
}

onMounted(fetchVehicles);
</script>
