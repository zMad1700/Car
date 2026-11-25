<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Fleet Care &amp; Service
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Maintenance
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Log vehicle servicing. Vehicles with ongoing maintenance are automatically marked unavailable for booking.
                </p>
            </div>

            <div class="d-flex align-center ga-2">
                <v-btn
                    color="secondary"
                    variant="flat"
                    prepend-icon="mdi-wrench-plus"
                    class="font-weight-bold text-primary px-5"
                    @click="openCreateDialog"
                >
                    Log Maintenance
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

        <!-- Summary Cards -->
        <v-row class="mb-4">
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
                        {{ card.value }}
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Filters & Table -->
        <v-card class="luxury-card overflow-hidden">
            <v-card-title class="pa-4">
                <v-row dense align="center">
                    <v-col cols="12" sm="6" md="5">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search vehicle, plate, or description"
                            density="compact"
                            hide-details
                            clearable
                        />
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select
                            v-model="statusFilter"
                            :items="['ongoing', 'done']"
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
                :items="filteredRecords"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.vehicle="{ item }">
                    <div class="d-flex align-center ga-3 py-2">
                        <v-avatar rounded="lg" size="48" color="#EFECE6">
                            <v-img v-if="item.vehicle?.image_url" :src="item.vehicle.image_url" cover />
                            <v-icon v-else icon="mdi-car" color="primary" />
                        </v-avatar>
                        <div>
                            <div class="font-weight-bold" style="color: #101D32;">
                                {{ item.vehicle?.brand }} {{ item.vehicle?.model }}
                            </div>
                            <div class="text-caption" style="color: #777777;">
                                {{ item.vehicle?.plate_number }}
                            </div>
                        </div>
                    </div>
                </template>

                <template #item.description="{ item }">
                    <div class="text-body-2 py-2" style="max-width: 300px; white-space: normal;">
                        {{ item.description }}
                    </div>
                </template>

                <template #item.dates="{ item }">
                    <div class="text-body-2 py-2">
                        <div>Started: {{ formatDate(item.date_started) }}</div>
                        <div class="text-caption" style="color: #777777;">
                            Finished: {{ item.date_finished ? formatDate(item.date_finished) : 'In progress' }}
                        </div>
                    </div>
                </template>

                <template #item.cost="{ item }">
                    <span class="font-weight-bold" style="color: #101D32;">
                        PHP {{ formatMoney(item.cost) }}
                    </span>
                </template>

                <template #item.status="{ item }">
                    <v-chip
                        :color="item.status === 'ongoing' ? 'error' : 'success'"
                        size="small"
                        variant="tonal"
                        class="text-capitalize font-weight-medium"
                    >
                        {{ item.status }}
                    </v-chip>
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            v-if="item.status === 'ongoing'"
                            size="small"
                            color="success"
                            variant="tonal"
                            prepend-icon="mdi-check"
                            @click="markDone(item)"
                        >
                            Mark Done
                        </v-btn>
                        <v-btn
                            icon="mdi-pencil-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="Edit"
                            @click="openEditDialog(item)"
                        />
                        <v-btn
                            icon="mdi-delete-outline"
                            size="small"
                            variant="text"
                            color="error"
                            title="Delete"
                            @click="openDeleteDialog(item)"
                        />
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Create / Edit Maintenance Dialog -->
        <v-dialog v-model="formDialog" max-width="620" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    {{ editingId ? 'Edit Maintenance Record' : 'Log Maintenance' }}
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-form ref="formRef" @submit.prevent="saveRecord">
                    <v-card-text class="pa-5">
                        <v-alert v-if="formError" type="error" variant="tonal" density="compact" class="mb-4">
                            {{ formError }}
                        </v-alert>

                        <v-select
                            v-model="form.vehicle_id"
                            :items="vehicles"
                            :item-title="(v) => `${v.brand} ${v.model} (${v.plate_number}) — ${v.status}`"
                            item-value="id"
                            label="Select Vehicle *"
                            density="comfortable"
                            :rules="[(v) => !!v || 'Vehicle is required.']"
                            class="mb-3"
                        />

                        <v-textarea
                            v-model="form.description"
                            label="Service / Repair Description *"
                            rows="3"
                            auto-grow
                            density="comfortable"
                            :rules="[(v) => !!v?.trim() || 'Description is required.']"
                            class="mb-3"
                        />

                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model.number="form.cost"
                                    label="Cost (PHP) *"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    density="comfortable"
                                    :rules="[(v) => (v !== '' && v !== null && Number(v) >= 0) || 'Cost must be 0 or higher.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="form.status"
                                    :items="['ongoing', 'done']"
                                    label="Status *"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Status is required.']"
                                />
                            </v-col>
                        </v-row>

                        <v-row dense class="mt-1">
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.date_started"
                                    label="Date Started *"
                                    type="date"
                                    density="comfortable"
                                    :rules="[(v) => !!v || 'Start date is required.']"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.date_finished"
                                    label="Date Finished (optional)"
                                    type="date"
                                    density="comfortable"
                                    :rules="dateFinishedRules"
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
                        <v-btn type="submit" color="secondary" variant="flat" class="font-weight-bold text-primary px-5" :loading="saving">
                            {{ editingId ? 'Update' : 'Save Record' }}
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Delete Dialog -->
        <v-dialog v-model="deleteDialog" max-width="440">
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h6 font-weight-bold" style="color: #101D32;">
                    Delete Maintenance Record
                </v-card-title>
                <v-card-text class="px-5 pb-4">
                    Are you sure you want to delete this maintenance log for
                    <strong>{{ selectedRecord?.vehicle?.brand }} {{ selectedRecord?.vehicle?.model }}</strong>?
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="primary" :disabled="deleting" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" :loading="deleting" @click="confirmDelete">
                        Delete
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

const records = ref([]);
const vehicles = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref(null);

const formDialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const editingId = ref(null);
const selectedRecord = ref(null);
const saving = ref(false);
const deleting = ref(false);
const formError = ref('');

const form = reactive({
    vehicle_id: null,
    description: '',
    cost: 0,
    date_started: new Date().toISOString().slice(0, 10),
    date_finished: '',
    status: 'ongoing',
});

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Vehicle', key: 'vehicle', sortable: false },
    { title: 'Description', key: 'description', sortable: false },
    { title: 'Dates', key: 'dates', sortable: false },
    { title: 'Cost', key: 'cost' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const summaryCards = computed(() => {
    const ongoing = records.value.filter((r) => r.status === 'ongoing').length;
    const done = records.value.filter((r) => r.status === 'done').length;
    const totalCost = records.value.reduce((sum, r) => sum + Number(r.cost || 0), 0);

    return [
        { label: 'Total Logs', value: records.value.length, icon: 'mdi-clipboard-text-outline', iconBg: 'rgba(16, 29, 50, 0.08)', iconColor: '#101D32' },
        { label: 'Ongoing', value: ongoing, icon: 'mdi-wrench-clock', iconBg: 'rgba(184, 59, 59, 0.12)', iconColor: '#B83B3B' },
        { label: 'Completed', value: done, icon: 'mdi-check-circle-outline', iconBg: 'rgba(46, 125, 91, 0.12)', iconColor: '#2E7D5B' },
        { label: 'Total Cost', value: `PHP ${formatMoney(totalCost)}`, icon: 'mdi-cash-minus', iconBg: '#101D32', iconColor: '#C6A15B' },
    ];
});

const filteredRecords = computed(() => {
    return records.value.filter((item) => {
        if (statusFilter.value && item.status !== statusFilter.value) return false;
        if (search.value?.trim()) {
            const q = search.value.trim().toLowerCase();
            const text = `${item.vehicle?.brand || ''} ${item.vehicle?.model || ''} ${item.vehicle?.plate_number || ''} ${item.description || ''}`.toLowerCase();
            return text.includes(q);
        }
        return true;
    });
});

const dateFinishedRules = [
    (v) => !v || !form.date_started || v >= form.date_started || 'Finish date cannot be before start date.',
];

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const [maintRes, vehiclesRes] = await Promise.all([
            api.get('/admin/maintenance'),
            api.get('/admin/vehicles'),
        ]);
        records.value = maintRes.data;
        vehicles.value = vehiclesRes.data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load maintenance logs.', 'error');
    } finally {
        loading.value = false;
    }
}

function openCreateDialog() {
    editingId.value = null;
    form.vehicle_id = vehicles.value[0]?.id ?? null;
    form.description = '';
    form.cost = 0;
    form.date_started = new Date().toISOString().slice(0, 10);
    form.date_finished = '';
    form.status = 'ongoing';
    formError.value = '';
    formDialog.value = true;
}

function openEditDialog(item) {
    editingId.value = item.id;
    form.vehicle_id = item.vehicle_id;
    form.description = item.description;
    form.cost = item.cost;
    form.date_started = item.date_started?.slice(0, 10) || '';
    form.date_finished = item.date_finished?.slice(0, 10) || '';
    form.status = item.status;
    formError.value = '';
    formDialog.value = true;
}

function openDeleteDialog(item) {
    selectedRecord.value = item;
    deleteDialog.value = true;
}

async function markDone(item) {
    try {
        await api.put(`/admin/maintenance/${item.id}`, {
            vehicle_id: item.vehicle_id,
            description: item.description,
            cost: item.cost,
            date_started: item.date_started?.slice(0, 10),
            date_finished: new Date().toISOString().slice(0, 10),
            status: 'done',
        });
        notify('Maintenance completed and vehicle status synced.');
        await fetchData();
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to mark done.', 'error');
    }
}

async function saveRecord() {
    formError.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const payload = {
            vehicle_id: form.vehicle_id,
            description: form.description.trim(),
            cost: form.cost,
            date_started: form.date_started,
            date_finished: form.date_finished || null,
            status: form.status,
        };

        if (editingId.value) {
            await api.put(`/admin/maintenance/${editingId.value}`, payload);
            notify('Maintenance record updated.');
        } else {
            await api.post('/admin/maintenance', payload);
            notify('Maintenance logged and vehicle marked as maintenance.');
        }
        formDialog.value = false;
        await fetchData();
    } catch (error) {
        const errors = error.response?.data?.errors;
        formError.value = errors
            ? Object.values(errors).flat().join(' ')
            : (error.response?.data?.message || 'Unable to save maintenance record.');
    } finally {
        saving.value = false;
    }
}

async function confirmDelete() {
    if (!selectedRecord.value) return;
    deleting.value = true;
    try {
        await api.delete(`/admin/maintenance/${selectedRecord.value.id}`);
        deleteDialog.value = false;
        notify('Maintenance record deleted.');
        await fetchData();
    } catch (error) {
        deleteDialog.value = false;
        notify(error.response?.data?.message || 'Failed to delete record.', 'error');
    } finally {
        deleting.value = false;
    }
}

onMounted(fetchData);
</script>
