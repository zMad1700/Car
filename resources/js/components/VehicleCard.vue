<template>
    <v-card class="luxury-card luxury-card-hover h-100 d-flex flex-column overflow-hidden">
        <!-- Vehicle Photo Showcase -->
        <div class="position-relative" style="height: 215px; background-color: #EFECE6;">
            <v-img
                v-if="currentImageUrl"
                :src="currentImageUrl"
                height="215"
                cover
                class="transition-swing"
            >
                <template #placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                        <v-progress-circular indeterminate color="primary" size="28" />
                    </div>
                </template>
            </v-img>
            <div v-else class="d-flex flex-column align-center justify-center fill-height" style="color: #777777;">
                <v-icon icon="mdi-car" size="56" />
                <span class="text-caption mt-1">No photo uploaded</span>
            </div>

            <!-- Top Overlays -->
            <div class="position-absolute top-0 left-0 right-0 pa-3 d-flex justify-space-between align-start">
                <VehicleStatusChip :status="vehicle.status" class="bg-white elevation-1" />
                <v-chip
                    v-if="vehicle.category?.name"
                    size="small"
                    color="primary"
                    variant="flat"
                    class="font-weight-medium"
                    style="border: 1px solid rgba(198, 161, 91, 0.5);"
                >
                    {{ vehicle.category.name }}
                </v-chip>
            </div>

            <!-- Exterior / Interior Switcher -->
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
                    style="border: 1px solid #E6E4DD;"
                >
                    <v-btn value="exterior" size="x-small" prepend-icon="mdi-car-side" class="px-3">
                        Exterior
                    </v-btn>
                    <v-btn
                        value="interior"
                        size="x-small"
                        prepend-icon="mdi-car-seat"
                        class="px-3"
                        :disabled="!vehicle.interior_image_url"
                    >
                        Interior
                    </v-btn>
                </v-btn-toggle>
            </div>
        </div>

        <!-- Card Body -->
        <v-card-text class="pa-5 flex-grow-1 d-flex flex-column">
            <div class="mb-1">
                <div class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.1em;">
                    {{ vehicle.brand }} &bull; {{ vehicle.year }}
                </div>
                <h3 class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                    {{ vehicle.brand }} {{ vehicle.model }}
                </h3>
                <div class="text-caption mt-1" style="color: #777777;">
                    {{ vehicle.color }}
                    <span v-if="showPlate"> &bull; Plate: {{ vehicle.plate_number }}</span>
                </div>
            </div>

            <div class="d-flex flex-wrap ga-2 my-4">
                <v-chip
                    size="small"
                    variant="outlined"
                    prepend-icon="mdi-car-shift-pattern"
                    class="text-capitalize"
                    style="border-color: #E6E4DD; color: #252525;"
                >
                    {{ vehicle.transmission }}
                </v-chip>
                <v-chip
                    size="small"
                    variant="outlined"
                    prepend-icon="mdi-account-group-outline"
                    style="border-color: #E6E4DD; color: #252525;"
                >
                    {{ vehicle.seats }} Seats
                </v-chip>
            </div>

            <v-spacer />

            <v-divider class="mb-3" style="border-color: #EFECE6;" />

            <div class="d-flex align-baseline justify-space-between">
                <span class="text-caption text-uppercase font-weight-bold" style="color: #777777; letter-spacing: 0.08em;">
                    Daily Rate
                </span>
                <div>
                    <span class="text-h6 font-weight-bold" style="color: #101D32;">
                        PHP {{ formatMoney(vehicle.daily_rate) }}
                    </span>
                    <span class="text-caption" style="color: #777777;"> / day</span>
                </div>
            </div>
        </v-card-text>

        <!-- Actions Slot -->
        <v-card-actions v-if="$slots.actions" class="px-5 pb-5 pt-0 ga-2">
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
