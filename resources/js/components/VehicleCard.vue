<template>
    <v-card elevation="2" border rounded="lg" class="h-100 d-flex flex-column overflow-hidden">
        <!-- Vehicle Photo Box (Exterior / Interior Switcher) -->
        <div class="position-relative bg-grey-lighten-3" style="height: 210px;">
            <v-img
                v-if="currentImageUrl"
                :src="currentImageUrl"
                height="210"
                cover
                class="bg-grey-lighten-3"
            >
                <template #placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                        <v-progress-circular indeterminate color="primary" />
                    </div>
                </template>
            </v-img>
            <div v-else class="d-flex flex-column align-center justify-center fill-height text-grey">
                <v-icon icon="mdi-car" size="56" />
                <span class="text-caption mt-1">No photo uploaded</span>
            </div>

            <!-- Status & Category Overlays -->
            <div class="position-absolute top-0 left-0 right-0 pa-3 d-flex justify-space-between align-start">
                <VehicleStatusChip :status="vehicle.status" class="bg-white" />
                <v-chip
                    v-if="vehicle.category?.name"
                    size="small"
                    color="secondary"
                    variant="flat"
                    class="font-weight-medium"
                >
                    {{ vehicle.category.name }}
                </v-chip>
            </div>

            <!-- Exterior / Interior Toggle -->
            <div
                v-if="vehicle.interior_image_url || vehicle.image_url"
                class="position-absolute bottom-0 left-0 right-0 pa-2 d-flex justify-center"
            >
                <v-btn-toggle
                    v-model="photoView"
                    mandatory
                    density="compact"
                    rounded="pill"
                    color="primary"
                    class="elevation-2 bg-white"
                >
                    <v-btn value="exterior" size="x-small" prepend-icon="mdi-car-side">
                        Exterior
                    </v-btn>
                    <v-btn
                        value="interior"
                        size="x-small"
                        prepend-icon="mdi-car-seat"
                        :disabled="!vehicle.interior_image_url"
                    >
                        Interior
                    </v-btn>
                </v-btn-toggle>
            </div>
        </div>

        <!-- Card Body -->
        <v-card-text class="pa-4 flex-grow-1 d-flex flex-column">
            <div class="d-flex align-start justify-space-between ga-2 mb-1">
                <div>
                    <h3 class="text-h6 font-weight-bold text-secondary">
                        {{ vehicle.brand }} {{ vehicle.model }}
                    </h3>
                    <div class="text-caption text-medium-emphasis">
                        {{ vehicle.year }} &bull; {{ vehicle.color }}
                        <span v-if="showPlate"> &bull; Plate: {{ vehicle.plate_number }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap ga-2 my-3">
                <v-chip size="small" variant="tonal" color="secondary" prepend-icon="mdi-car-shift-pattern" class="text-capitalize">
                    {{ vehicle.transmission }}
                </v-chip>
                <v-chip size="small" variant="tonal" color="secondary" prepend-icon="mdi-account-group-outline">
                    {{ vehicle.seats }} seats
                </v-chip>
            </div>

            <v-spacer />

            <v-divider class="mb-3" />

            <div class="d-flex align-baseline justify-space-between">
                <span class="text-caption text-medium-emphasis text-uppercase font-weight-bold">
                    Daily Rate
                </span>
                <div>
                    <span class="text-h6 font-weight-bold text-primary">
                        PHP {{ formatMoney(vehicle.daily_rate) }}
                    </span>
                    <span class="text-caption text-medium-emphasis"> / day</span>
                </div>
            </div>
        </v-card-text>

        <!-- Actions Slot -->
        <v-card-actions v-if="$slots.actions" class="px-4 pb-4 pt-0">
            <slot name="actions" />
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { computed, ref } from 'vue';
import VehicleStatusChip from '@/components/VehicleStatusChip.vue';

const props = defineProps({
    vehicle: {
        type: Object,
        required: true,
    },
    showPlate: {
        type: Boolean,
        default: false,
    },
});

const photoView = ref('exterior');

const currentImageUrl = computed(() => {
    if (photoView.value === 'interior' && props.vehicle.interior_image_url) {
        return props.vehicle.interior_image_url;
    }
    return props.vehicle.image_url || props.vehicle.interior_image_url || null;
});

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
