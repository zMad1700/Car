<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-6">
            <div>
                <h1 class="text-h4 font-weight-bold text-secondary">
                    Bookings
                </h1>
                <p class="text-body-2 text-medium-emphasis mt-1">
                    Review customer reservations, inspect details, and confirm or cancel bookings.
                </p>
            </div>

            <v-btn
                variant="outlined"
                color="primary"
                prepend-icon="mdi-refresh"
                :loading="loading"
                @click="fetchBookings"
            >
                Refresh
            </v-btn>
        </div>

        <v-card elevation="1" border rounded="lg">
            <v-card-title class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" md="4">
                        <v-text-field
                            v-model="filters.search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search reference, customer, or email"
                            variant="outlined"
                            density="compact"
                            hide-details
                            clearable
                            @keyup.enter="fetchBookings"
                        />
                    </v-col>
                    <v-col cols="12" sm="4" md="2">
                        <v-select
                            v-model="filters.status"
                            :items="statusOptions"
                            label="Status"
                            variant="outlined"
                            density="compact"
                            hide-details
                            clearable
                            @update:model-value="fetchBookings"
                        />
                    </v-col>
                    <v-col cols="12" sm="4" md="2">
                        <v-text-field
                            v-model="filters.date_from"
                            label="Pickup From"
                            type="date"
                            variant="outlined"
                            density="compact"
                            hide-details
                            @update:model-value="fetchBookings"
                        />
                    </v-col>
                    <v-col cols="12" sm="4" md="2">
                        <v-text-field
                            v-model="filters.date_to"
                            label="Pickup To"
                            type="date"
                            variant="outlined"
                            density="compact"
                            hide-details
                            @update:model-value="fetchBookings"
                        />
                    </v-col>
                    <v-col cols="12" md="2" class="d-flex ga-2">
                        <v-btn color="primary" variant="flat" size="small" @click="fetchBookings">
                            Filter
                        </v-btn>
                        <v-btn variant="text" color="secondary" size="small" @click="resetFilters">
                            Reset
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="bookings"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.reference_code="{ item }">
                    <div class="py-2">
                        <div class="font-weight-bold text-primary">
                            {{ item.reference_code }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                            {{ formatDateTime(item.created_at) }}
                        </div>
                    </div>
                </template>

                <template #item.customer="{ item }">
                    <div class="py-2">
                        <div class="font-weight-medium text-secondary">
                            {{ item.customer?.name || '—' }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                            {{ item.customer?.email }} &bull; {{ item.customer?.contact_number }}
                        </div>
                    </div>
                </template>

                <template #item.vehicle="{ item }">
                    <div class="py-2">
                        <div class="font-weight-medium text-secondary">
                            {{ item.vehicle?.brand }} {{ item.vehicle?.model }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                            {{ item.vehicle?.plate_number }}
                            <span v-if="item.vehicle?.category">
                                &bull; {{ item.vehicle.category.name }}
                            </span>
                        </div>
                    </div>
                </template>

                <template #item.schedule="{ item }">
                    <div class="text-body-2 py-2">
                        <div>{{ formatDateTime(item.pickup_date) }}</div>
                        <div class="text-caption text-medium-emphasis">
                            to {{ formatDateTime(item.return_date) }} ({{ item.total_days }}d)
                        </div>
                    </div>
                </template>

                <template #item.total_amount="{ item }">
                    <div class="font-weight-bold text-secondary">
                        PHP {{ formatMoney(item.total_amount) }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                        PHP {{ formatMoney(item.daily_rate) }}/day
                    </div>
                </template>

                <template #item.status="{ item }">
                    <BookingStatusChip :status="item.status" />
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            icon="mdi-eye-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="View Details"
                            @click="openDetails(item)"
                        />
                        <v-btn
                            v-if="item.status === 'pending'"
                            icon="mdi-check-circle-outline"
                            size="small"
                            variant="text"
                            color="success"
                            title="Confirm Booking"
                            @click="openActionDialog(item, 'confirm')"
                        />
                        <v-btn
                            v-if="canCancel(item)"
                            icon="mdi-close-circle-outline"
                            size="small"
                            variant="text"
                            color="error"
                            title="Cancel Booking"
                            @click="openActionDialog(item, 'cancel')"
                        />
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Booking Details Dialog -->
        <v-dialog v-model="detailsDialog" max-width="820">
            <v-card rounded="lg">
                <v-card-title class="pa-5 d-flex align-center justify-space-between">
                    <div>
                        <span class="font-weight-bold text-secondary">
                            Booking {{ detailBooking?.reference_code }}
                        </span>
                        <div class="text-caption text-medium-emphasis">
                            Booked on {{ formatDateTime(detailBooking?.created_at) }}
                        </div>
                    </div>
                    <BookingStatusChip v-if="detailBooking" :status="detailBooking.status" />
                </v-card-title>
                <v-divider />

                <v-card-text class="pa-5">
                    <v-progress-linear v-if="loadingDetails" indeterminate color="primary" class="mb-4" />

                    <template v-if="detailBooking">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                                    <div class="text-overline text-primary font-weight-bold mb-2">
                                        Customer Details
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Name:</strong> {{ detailBooking.customer?.name }}
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Email:</strong> {{ detailBooking.customer?.email }}
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Contact:</strong> {{ detailBooking.customer?.contact_number }}
                                    </div>
                                    <div class="text-body-2">
                                        <strong>Address:</strong> {{ detailBooking.customer?.address }}
                                    </div>
                                </v-card>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                                    <div class="text-overline text-primary font-weight-bold mb-2">
                                        Vehicle Details
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Vehicle:</strong>
                                        {{ detailBooking.vehicle?.brand }} {{ detailBooking.vehicle?.model }}
                                        ({{ detailBooking.vehicle?.year }})
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Category:</strong> {{ detailBooking.vehicle?.category?.name || '—' }}
                                    </div>
                                    <div class="text-body-2 mb-1">
                                        <strong>Plate Number:</strong> {{ detailBooking.vehicle?.plate_number }}
                                    </div>
                                    <div class="text-body-2 text-capitalize">
                                        <strong>Specs:</strong>
                                        {{ detailBooking.vehicle?.transmission }} &bull; {{ detailBooking.vehicle?.seats }} seats
                                    </div>
                                </v-card>
                            </v-col>

                            <v-col cols="12">
                                <v-card variant="tonal" color="primary" rounded="lg" class="pa-4">
                                    <v-row dense>
                                        <v-col cols="12" sm="4">
                                            <div class="text-caption text-uppercase font-weight-bold">Pickup</div>
                                            <div class="text-body-2 font-weight-medium">
                                                {{ formatDateTime(detailBooking.pickup_date) }}
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="4">
                                            <div class="text-caption text-uppercase font-weight-bold">Return</div>
                                            <div class="text-body-2 font-weight-medium">
                                                {{ formatDateTime(detailBooking.return_date) }}
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="4">
                                            <div class="text-caption text-uppercase font-weight-bold">Total</div>
                                            <div class="text-subtitle-1 font-weight-bold">
                                                PHP {{ formatMoney(detailBooking.total_amount) }}
                                            </div>
                                            <div class="text-caption">
                                                {{ detailBooking.total_days }} day(s) &times; PHP {{ formatMoney(detailBooking.daily_rate) }}
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-card>
                            </v-col>

                            <!-- Payments & Rental Summary -->
                            <v-col cols="12" md="6">
                                <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                                    <div class="text-overline text-secondary font-weight-bold mb-2">
                                        Payments ({{ detailBooking.payments?.length || 0 }})
                                    </div>
                                    <div v-if="!detailBooking.payments?.length" class="text-body-2 text-medium-emphasis">
                                        No payments recorded yet.
                                    </div>
                                    <v-list v-else density="compact" class="pa-0">
                                        <v-list-item
                                            v-for="payment in detailBooking.payments"
                                            :key="payment.id"
                                            class="px-0"
                                        >
                                            <v-list-item-title class="text-body-2 font-weight-medium">
                                                PHP {{ formatMoney(payment.amount) }}
                                                <span class="text-capitalize">({{ payment.type }} • {{ payment.method }})</span>
                                            </v-list-item-title>
                                            <v-list-item-subtitle class="text-caption">
                                                {{ payment.receipt_number }} &bull; {{ formatDateTime(payment.paid_at) }}
                                            </v-list-item-subtitle>
                                        </v-list-item>
                                    </v-list>
                                </v-card>
                            </v-col>

                            <!-- Email Logs -->
                            <v-col cols="12" md="6">
                                <v-card variant="outlined" rounded="lg" class="pa-4 h-100">
                                    <div class="text-overline text-secondary font-weight-bold mb-2">
                                        Email Logs ({{ detailBooking.email_logs?.length || 0 }})
                                    </div>
                                    <div v-if="!detailBooking.email_logs?.length" class="text-body-2 text-medium-emphasis">
                                        No emails logged.
                                    </div>
                                    <v-list v-else density="compact" class="pa-0">
                                        <v-list-item
                                            v-for="log in detailBooking.email_logs"
                                            :key="log.id"
                                            class="px-0"
                                        >
                                            <template #append>
                                                <v-chip
                                                    :color="log.status === 'sent' ? 'success' : 'error'"
                                                    size="x-small"
                                                    variant="tonal"
                                                    class="text-uppercase"
                                                >
                                                    {{ log.status }}
                                                </v-chip>
                                            </template>
                                            <v-list-item-title class="text-body-2">
                                                {{ log.subject }}
                                            </v-list-item-title>
                                            <v-list-item-subtitle class="text-caption">
                                                To: {{ log.sent_to }} &bull; {{ formatDateTime(log.sent_at) }}
                                            </v-list-item-subtitle>
                                        </v-list-item>
                                    </v-list>
                                </v-card>
                            </v-col>
                        </v-row>
                    </template>
                </v-card-text>

                <v-divider />
                <v-card-actions class="pa-4">
                    <v-btn
                        v-if="detailBooking?.status === 'pending'"
                        color="success"
                        variant="flat"
                        prepend-icon="mdi-check"
                        @click="openActionDialog(detailBooking, 'confirm')"
                    >
                        Confirm Booking
                    </v-btn>
                    <v-btn
                        v-if="detailBooking && canCancel(detailBooking)"
                        color="error"
                        variant="tonal"
                        prepend-icon="mdi-close"
                        @click="openActionDialog(detailBooking, 'cancel')"
                    >
                        Cancel Booking
                    </v-btn>
                    <v-spacer />
                    <v-btn variant="text" color="secondary" @click="detailsDialog = false">
                        Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Confirm / Cancel Action Dialog -->
        <v-dialog v-model="actionDialog" max-width="460">
            <v-card rounded="lg">
                <v-card-title class="pa-5 font-weight-bold text-secondary">
                    {{ actionType === 'confirm' ? 'Confirm Booking' : 'Cancel Booking' }}
                </v-card-title>
                <v-card-text class="px-5 pb-4">
                    <p class="text-body-2 mb-2">
                        Are you sure you want to
                        <strong>{{ actionType === 'confirm' ? 'confirm' : 'cancel' }}</strong>
                        booking <strong>{{ actionTarget?.reference_code }}</strong> for
                        <strong>{{ actionTarget?.customer?.name }}</strong>?
                    </p>
                    <p class="text-caption text-medium-emphasis mb-0">
                        A status update email will be sent to {{ actionTarget?.customer?.email }} and logged automatically.
                    </p>
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="secondary" :disabled="acting" @click="actionDialog = false">
                        Back
                    </v-btn>
                    <v-btn
                        :color="actionType === 'confirm' ? 'success' : 'error'"
                        variant="flat"
                        :loading="acting"
                        @click="submitAction"
                    >
                        {{ actionType === 'confirm' ? 'Yes, Confirm' : 'Yes, Cancel' }}
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
import { onMounted, reactive, ref } from 'vue';
import api from '@/plugins/axios';
import BookingStatusChip from '@/components/BookingStatusChip.vue';

const bookings = ref([]);
const loading = ref(false);

const filters = reactive({
    search: '',
    status: null,
    date_from: '',
    date_to: '',
});

const statusOptions = ['pending', 'confirmed', 'cancelled', 'completed'];

const detailsDialog = ref(false);
const loadingDetails = ref(false);
const detailBooking = ref(null);

const actionDialog = ref(false);
const actionType = ref('confirm');
const actionTarget = ref(null);
const acting = ref(false);

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Reference', key: 'reference_code' },
    { title: 'Customer', key: 'customer', sortable: false },
    { title: 'Vehicle', key: 'vehicle', sortable: false },
    { title: 'Schedule', key: 'schedule', sortable: false },
    { title: 'Amount', key: 'total_amount' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
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

function canCancel(booking) {
    return ['pending', 'confirmed'].includes(booking.status) && !booking.rental;
}

async function fetchBookings() {
    loading.value = true;
    try {
        const params = {};
        if (filters.search?.trim()) params.search = filters.search.trim();
        if (filters.status) params.status = filters.status;
        if (filters.date_from) params.date_from = filters.date_from;
        if (filters.date_to) params.date_to = filters.date_to;

        const { data } = await api.get('/admin/bookings', { params });
        bookings.value = data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load bookings.', 'error');
    } finally {
        loading.value = false;
    }
}

function resetFilters() {
    filters.search = '';
    filters.status = null;
    filters.date_from = '';
    filters.date_to = '';
    fetchBookings();
}

async function openDetails(item) {
    detailBooking.value = item;
    detailsDialog.value = true;
    loadingDetails.value = true;
    try {
        const { data } = await api.get(`/admin/bookings/${item.id}`);
        detailBooking.value = data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load booking details.', 'error');
    } finally {
        loadingDetails.value = false;
    }
}

function openActionDialog(booking, type) {
    actionTarget.value = booking;
    actionType.value = type;
    actionDialog.value = true;
}

async function submitAction() {
    if (!actionTarget.value) return;
    acting.value = true;
    try {
        const endpoint = `/admin/bookings/${actionTarget.value.id}/${actionType.value}`;
        const { data } = await api.post(endpoint);

        actionDialog.value = false;
        if (detailsDialog.value && detailBooking.value?.id === data.id) {
            await openDetails(data);
        }
        notify(
            actionType.value === 'confirm'
                ? 'Booking confirmed and status email logged.'
                : 'Booking cancelled and status email logged.'
        );
        await fetchBookings();
    } catch (error) {
        actionDialog.value = false;
        notify(error.response?.data?.message || 'Action failed.', 'error');
    } finally {
        acting.value = false;
    }
}

onMounted(fetchBookings);
</script>
