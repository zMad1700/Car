<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Fleet Dispatch
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Rentals
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Release confirmed reservations and process vehicle returns with automatic late-penalty calculation.
                </p>
            </div>

            <div class="d-flex align-center ga-2">
                <v-btn
                    color="secondary"
                    variant="flat"
                    prepend-icon="mdi-car-key"
                    class="font-weight-bold text-primary px-5"
                    @click="openReleaseDialog"
                >
                    Release Vehicle
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

        <!-- Filters -->
        <v-card class="luxury-card overflow-hidden">
            <v-card-title class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" sm="6" md="5">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search reference, customer, or vehicle"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select
                            v-model="statusFilter"
                            :items="['active', 'returned', 'overdue']"
                            label="Filter by Status"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                </v-row>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="filteredRentals"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.reference="{ item }">
                    <div class="py-2">
                        <div class="font-weight-bold" style="color: #101D32;">
                            {{ item.booking?.reference_code || '—' }}
                        </div>
                        <div class="text-caption" style="color: #777777;">
                            Due: {{ formatDateTime(item.booking?.return_date) }}
                        </div>
                    </div>
                </template>

                <template #item.customer="{ item }">
                    <div class="py-2">
                        <div class="font-weight-medium" style="color: #252525;">
                            {{ item.booking?.customer?.name || '—' }}
                        </div>
                        <div class="text-caption" style="color: #777777;">
                            {{ item.booking?.customer?.contact_number }}
                        </div>
                    </div>
                </template>

                <template #item.vehicle="{ item }">
                    <div class="py-2">
                        <div class="font-weight-medium" style="color: #252525;">
                            {{ item.booking?.vehicle?.brand }} {{ item.booking?.vehicle?.model }}
                        </div>
                        <div class="text-caption" style="color: #777777;">
                            {{ item.booking?.vehicle?.plate_number }}
                        </div>
                    </div>
                </template>

                <template #item.odometer="{ item }">
                    <div class="text-body-2 py-2">
                        <div>Out: <strong>{{ Number(item.odometer_out).toLocaleString() }} km</strong></div>
                        <div class="text-caption" style="color: #777777;">
                            In: {{ item.odometer_in !== null ? `${Number(item.odometer_in).toLocaleString()} km` : 'Pending return' }}
                        </div>
                    </div>
                </template>

                <template #item.schedule="{ item }">
                    <div class="text-body-2 py-2">
                        <div>Out: {{ formatDateTime(item.date_out) }}</div>
                        <div class="text-caption" style="color: #777777;">
                            In: {{ item.date_in ? formatDateTime(item.date_in) : 'Active' }}
                        </div>
                    </div>
                </template>

                <template #item.penalty_amount="{ item }">
                    <span
                        class="font-weight-bold"
                        :style="Number(item.penalty_amount) > 0 ? 'color: #B83B3B;' : 'color: #777777;'"
                    >
                        PHP {{ formatMoney(item.penalty_amount) }}
                    </span>
                </template>

                <template #item.status="{ item }">
                    <div class="d-flex align-center ga-1">
                        <v-chip
                            :color="effectiveStatusColor(item)"
                            size="small"
                            variant="tonal"
                            class="text-capitalize font-weight-medium"
                        >
                            {{ effectiveStatusLabel(item) }}
                        </v-chip>
                    </div>
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            icon="mdi-eye-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="View Details"
                            @click="openViewDialog(item)"
                        />
                        <v-btn
                            v-if="item.status !== 'returned'"
                            size="small"
                            color="secondary"
                            variant="flat"
                            class="font-weight-bold text-primary"
                            prepend-icon="mdi-car-arrow-left"
                            @click="openReturnDialog(item)"
                        >
                            Return
                        </v-btn>
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Release Vehicle Dialog -->
        <v-dialog v-model="releaseDialog" max-width="620" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    Release Vehicle
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-form ref="releaseFormRef" @submit.prevent="submitRelease">
                    <v-card-text class="pa-5">
                        <v-alert v-if="releaseError" type="error" variant="tonal" density="compact" class="mb-4">
                            {{ releaseError }}
                        </v-alert>

                        <v-alert v-if="!releasableBookings.length" type="info" variant="tonal" class="mb-4">
                            No confirmed bookings waiting for release. Confirm a pending booking first in Bookings.
                        </v-alert>

                        <v-select
                            v-model="releaseForm.booking_id"
                            :items="releasableBookings"
                            :item-title="bookingLabel"
                            item-value="id"
                            label="Confirmed Booking *"
                            density="comfortable"
                            :rules="[(v) => !!v || 'Please select a confirmed booking.']"
                            class="mb-3"
                        />

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="releaseForm.date_out"
                                    label="Date & Time Out *"
                                    type="datetime-local"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Date out is required.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model.number="releaseForm.odometer_out"
                                    label="Odometer Out (km) *"
                                    type="number"
                                    min="0"
                                    density="comfortable"
                                    :rules="[(v) => (v !== '' && v !== null && Number(v) >= 0) || 'Valid odometer reading required.']"
                                />
                            </v-col>
                        </v-row>

                        <v-textarea
                            v-model="releaseForm.remarks"
                            label="Release Remarks (optional)"
                            rows="2"
                            auto-grow
                            density="comfortable"
                            placeholder="Fuel level, exterior condition, accessories..."
                            class="mt-2"
                        />
                    </v-card-text>

                    <v-divider style="border-color: #EFECE6;" />
                    <v-card-actions class="pa-4">
                        <v-spacer />
                        <v-btn variant="text" color="primary" :disabled="submitting" @click="releaseDialog = false">
                            Cancel
                        </v-btn>
                        <v-btn
                            type="submit"
                            color="primary"
                            variant="flat"
                            class="px-5"
                            :loading="submitting"
                            :disabled="!releasableBookings.length"
                        >
                            Confirm Release
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Return Vehicle Dialog -->
        <v-dialog v-model="returnDialog" max-width="620" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    Process Vehicle Return
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-form ref="returnFormRef" @submit.prevent="submitReturn">
                    <v-card-text class="pa-5">
                        <v-alert v-if="returnError" type="error" variant="tonal" density="compact" class="mb-4">
                            {{ returnError }}
                        </v-alert>

                        <v-card v-if="returningRental" variant="outlined" rounded="lg" class="pa-4 mb-4" style="border-color: #E6E4DD;">
                            <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B;">
                                {{ returningRental.booking?.reference_code }}
                            </div>
                            <div class="font-weight-bold" style="color: #101D32;">
                                {{ returningRental.booking?.vehicle?.brand }} {{ returningRental.booking?.vehicle?.model }}
                                ({{ returningRental.booking?.vehicle?.plate_number }})
                            </div>
                            <div class="text-caption mt-1" style="color: #777777;">
                                Customer: {{ returningRental.booking?.customer?.name }} &bull;
                                Odometer Out: {{ Number(returningRental.odometer_out).toLocaleString() }} km &bull;
                                Scheduled Return: {{ formatDateTime(returningRental.booking?.return_date) }}
                            </div>
                        </v-card>

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="returnForm.date_in"
                                    label="Actual Date & Time In *"
                                    type="datetime-local"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Return date & time is required.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model.number="returnForm.odometer_in"
                                    label="Odometer In (km) *"
                                    type="number"
                                    density="comfortable"
                                    :rules="odometerInRules"
                                />
                            </v-col>
                        </v-row>

                        <v-textarea
                            v-model="returnForm.remarks"
                            label="Return Remarks (optional)"
                            rows="2"
                            auto-grow
                            density="comfortable"
                            class="mt-2 mb-3"
                        />

                        <v-alert
                            :type="estimatedPenalty > 0 ? 'warning' : 'success'"
                            variant="tonal"
                            density="comfortable"
                        >
                            <template v-if="estimatedPenalty > 0">
                                <strong>Late Return Penalty:</strong>
                                PHP {{ formatMoney(estimatedPenalty) }} (auto-calculated at PHP {{ formatMoney(returningRental?.booking?.daily_rate) }}/day).
                            </template>
                            <template v-else>
                                Returned on time — <strong>No late penalty (PHP 0.00)</strong>.
                            </template>
                        </v-alert>
                    </v-card-text>

                    <v-divider style="border-color: #EFECE6;" />
                    <v-card-actions class="pa-4">
                        <v-spacer />
                        <v-btn variant="text" color="primary" :disabled="submitting" @click="returnDialog = false">
                            Cancel
                        </v-btn>
                        <v-btn type="submit" color="secondary" variant="flat" class="font-weight-bold text-primary px-5" :loading="submitting">
                            Confirm Return
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- View Rental Details Dialog -->
        <v-dialog v-model="viewDialog" max-width="640">
            <v-card v-if="viewingRental" class="luxury-card">
                <v-card-title class="pa-5 d-flex align-center justify-space-between">
                    <div>
                        <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.12em;">
                            Rental Record
                        </div>
                        <div class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                            {{ viewingRental.booking?.reference_code }}
                        </div>
                    </div>
                    <v-chip
                        :color="effectiveStatusColor(viewingRental)"
                        size="small"
                        variant="tonal"
                        class="text-capitalize font-weight-medium"
                    >
                        {{ effectiveStatusLabel(viewingRental) }}
                    </v-chip>
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-card-text class="pa-5">
                    <v-row dense>
                        <v-col cols="12" sm="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Customer</div>
                            <div class="font-weight-bold">{{ viewingRental.booking?.customer?.name }}</div>
                            <div class="text-caption" style="color: #777777;">{{ viewingRental.booking?.customer?.contact_number }}</div>
                        </v-col>
                        <v-col cols="12" sm="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Vehicle</div>
                            <div class="font-weight-bold">
                                {{ viewingRental.booking?.vehicle?.brand }} {{ viewingRental.booking?.vehicle?.model }}
                            </div>
                            <div class="text-caption" style="color: #777777;">Plate: {{ viewingRental.booking?.vehicle?.plate_number }}</div>
                        </v-col>
                        <v-col cols="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Date Out</div>
                            <div class="font-weight-medium">{{ formatDateTime(viewingRental.date_out) }}</div>
                        </v-col>
                        <v-col cols="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Date In</div>
                            <div class="font-weight-medium">{{ viewingRental.date_in ? formatDateTime(viewingRental.date_in) : 'Still out' }}</div>
                        </v-col>
                        <v-col cols="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Odometer Out / In</div>
                            <div class="font-weight-medium">
                                {{ Number(viewingRental.odometer_out).toLocaleString() }} km
                                <span v-if="viewingRental.odometer_in !== null">
                                    → {{ Number(viewingRental.odometer_in).toLocaleString() }} km
                                </span>
                            </div>
                        </v-col>
                        <v-col cols="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Released By</div>
                            <div class="font-weight-medium">{{ viewingRental.released_by?.name || 'Admin' }}</div>
                        </v-col>
                        <v-col cols="6" class="mb-3">
                            <div class="text-caption" style="color: #777777;">Late Penalty</div>
                            <div class="font-weight-bold" :style="Number(viewingRental.penalty_amount) > 0 ? 'color: #B83B3B;' : ''">
                                PHP {{ formatMoney(viewingRental.penalty_amount) }}
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <div class="text-caption" style="color: #777777;">Remarks</div>
                            <div class="text-body-2">{{ viewingRental.remarks || '—' }}</div>
                        </v-col>
                    </v-row>
                </v-card-text>

                <v-divider style="border-color: #EFECE6;" />
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="primary" @click="viewDialog = false">
                        Close
                    </v-btn>
                </v-card-actions>
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

const rentals = ref([]);
const confirmedBookings = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref(null);

const releaseDialog = ref(false);
const returnDialog = ref(false);
const viewDialog = ref(false);
const releaseFormRef = ref(null);
const returnFormRef = ref(null);

const submitting = ref(false);
const releaseError = ref('');
const returnError = ref('');
const returningRental = ref(null);
const viewingRental = ref(null);

const releaseForm = reactive({
    booking_id: null,
    date_out: '',
    odometer_out: 0,
    remarks: '',
});

const returnForm = reactive({
    date_in: '',
    odometer_in: 0,
    remarks: '',
});

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Booking Ref', key: 'reference', sortable: false },
    { title: 'Customer', key: 'customer', sortable: false },
    { title: 'Vehicle', key: 'vehicle', sortable: false },
    { title: 'Odometer', key: 'odometer', sortable: false },
    { title: 'Schedule', key: 'schedule', sortable: false },
    { title: 'Penalty', key: 'penalty_amount' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const releasableBookings = computed(() =>
    confirmedBookings.value.filter((b) => b.status === 'confirmed' && !b.rental)
);

const filteredRentals = computed(() => {
    return rentals.value.filter((item) => {
        if (statusFilter.value) {
            if (statusFilter.value === 'overdue') {
                if (!isOverdue(item)) return false;
            } else if (item.status !== statusFilter.value) {
                return false;
            }
        }
        if (search.value?.trim()) {
            const q = search.value.trim().toLowerCase();
            const refCode = item.booking?.reference_code?.toLowerCase() || '';
            const cust = item.booking?.customer?.name?.toLowerCase() || '';
            const veh = `${item.booking?.vehicle?.brand || ''} ${item.booking?.vehicle?.model || ''} ${item.booking?.vehicle?.plate_number || ''}`.toLowerCase();
            return refCode.includes(q) || cust.includes(q) || veh.includes(q);
        }
        return true;
    });
});

const odometerInRules = computed(() => [
    (v) => (v !== '' && v !== null) || 'Odometer in is required.',
    (v) => !returningRental.value || Number(v) >= Number(returningRental.value.odometer_out) || `Must be at least ${returningRental.value.odometer_out} km.`,
]);

const estimatedPenalty = computed(() => {
    if (!returningRental.value || !returnForm.date_in) return 0;
    const actualIn = new Date(returnForm.date_in);
    const scheduledReturn = new Date(returningRental.value.booking?.return_date);
    if (Number.isNaN(actualIn.getTime()) || Number.isNaN(scheduledReturn.getTime())) return 0;
    if (actualIn <= scheduledReturn) return 0;
    const lateDays = Math.max(1, Math.ceil((actualIn - scheduledReturn) / (1000 * 60 * 60 * 24)));
    return lateDays * Number(returningRental.value.booking?.daily_rate || 0);
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
    return `${b.reference_code} — ${b.customer?.name} (${b.vehicle?.brand} ${b.vehicle?.model} • ${b.vehicle?.plate_number})`;
}

function isOverdue(rental) {
    if (rental.status === 'overdue') return true;
    if (rental.status === 'active' && rental.booking?.return_date) {
        return new Date(rental.booking.return_date) < new Date();
    }
    return false;
}

function effectiveStatusLabel(rental) {
    return isOverdue(rental) ? 'overdue' : rental.status;
}

function effectiveStatusColor(rental) {
    if (isOverdue(rental)) return 'error';
    if (rental.status === 'returned') return 'success';
    return 'info';
}

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const [rentalsRes, bookingsRes] = await Promise.all([
            api.get('/admin/rentals'),
            api.get('/admin/bookings', { params: { status: 'confirmed' } }),
        ]);
        rentals.value = rentalsRes.data;
        confirmedBookings.value = bookingsRes.data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load rentals.', 'error');
    } finally {
        loading.value = false;
    }
}

function openReleaseDialog() {
    releaseError.value = '';
    releaseForm.booking_id = releasableBookings.value[0]?.id ?? null;
    releaseForm.date_out = toLocalInput();
    releaseForm.odometer_out = 0;
    releaseForm.remarks = '';
    releaseDialog.value = true;
}

function openReturnDialog(item) {
    returnError.value = '';
    returningRental.value = item;
    returnForm.date_in = toLocalInput();
    returnForm.odometer_in = item.odometer_out;
    returnForm.remarks = item.remarks || '';
    returnDialog.value = true;
}

function openViewDialog(item) {
    viewingRental.value = item;
    viewDialog.value = true;
}

async function submitRelease() {
    releaseError.value = '';
    const { valid } = await releaseFormRef.value.validate();
    if (!valid) return;

    submitting.value = true;
    try {
        await api.post('/admin/rentals/release', {
            booking_id: releaseForm.booking_id,
            date_out: releaseForm.date_out.replace('T', ' '),
            odometer_out: releaseForm.odometer_out,
            remarks: releaseForm.remarks?.trim() || null,
        });
        releaseDialog.value = false;
        notify('Vehicle released successfully (status updated to rented).');
        await fetchData();
    } catch (error) {
        releaseError.value = error.response?.data?.message || 'Unable to release vehicle.';
    } finally {
        submitting.value = false;
    }
}

async function submitReturn() {
    returnError.value = '';
    const { valid } = await returnFormRef.value.validate();
    if (!valid || !returningRental.value) return;

    submitting.value = true;
    try {
        await api.post(`/admin/rentals/${returningRental.value.id}/return`, {
            date_in: returnForm.date_in.replace('T', ' '),
            odometer_in: returnForm.odometer_in,
            remarks: returnForm.remarks?.trim() || null,
        });
        returnDialog.value = false;
        notify('Vehicle returned successfully (status updated to available).');
        await fetchData();
    } catch (error) {
        const errors = error.response?.data?.errors;
        returnError.value = errors
            ? Object.values(errors).flat().join(' ')
            : (error.response?.data?.message || 'Unable to process return.');
    } finally {
        submitting.value = false;
    }
}

onMounted(fetchData);
</script>
