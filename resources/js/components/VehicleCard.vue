<template>
    <div ref="vehicleCard" class="vehicle-card-flip">
        <div ref="photoInner" class="vehicle-card-flip__inner">
            <v-card
                v-for="side in cardSides"
                :key="side"
                :aria-hidden="photoView !== side"
                :inert="photoView !== side"
                :class="{ 'vehicle-card-face--back': side === 'interior' }"
                class="luxury-card luxury-card-hover h-100 d-flex flex-column overflow-hidden vehicle-card-face"
            >
        <template v-if="side === 'exterior'">
            <!-- Vehicle Photo Showcase -->
        <div class="position-relative" style="height: 215px; background-color: #EFECE6;">
            <v-img
                v-if="exteriorImageUrl"
                :src="exteriorImageUrl"
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

            <!-- Interior Slide Controls (when multiple interior photos exist) -->
            <template v-if="photoView === 'interior' && interiorList.length > 1">
                <v-btn
                    icon="mdi-chevron-left"
                    size="x-small"
                    color="white"
                    variant="flat"
                    class="position-absolute"
                    style="left: 10px; top: 50%; transform: translateY(-50%); background: rgba(16, 29, 50, 0.72) !important; color: #FFFFFF !important;"
                    @click.stop="prevInterior"
                />
                <v-btn
                    icon="mdi-chevron-right"
                    size="x-small"
                    color="white"
                    variant="flat"
                    class="position-absolute"
                    style="right: 10px; top: 50%; transform: translateY(-50%); background: rgba(16, 29, 50, 0.72) !important; color: #FFFFFF !important;"
                    @click.stop="nextInterior"
                />
            </template>

            <!-- Exterior / Interior Switcher -->
            <div
                v-if="interiorList.length || vehicle.image_url"
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
                    <v-btn value="exterior" size="x-small" prepend-icon="mdi-car-side" class="vehicle-photo-view-control px-3" :aria-pressed="photoView === 'exterior'">
                        Exterior
                    </v-btn>
                    <v-btn
                        value="interior"
                        size="x-small"
                        prepend-icon="mdi-car-seat"
                        class="vehicle-photo-view-control px-3" :aria-pressed="photoView === 'interior'"
                        :disabled="!interiorList.length"
                    >
                        Interior{{ interiorList.length > 1 ? ` (${interiorIndex + 1}/${interiorList.length})` : '' }}
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
        </template>
        <template v-else>
            <div class="vehicle-card-interior-back">
                <v-img
                    v-if="interiorImageUrl"
                    :src="interiorImageUrl"
                    cover
                    height="100%"
                    class="vehicle-card-interior-image"
                >
                    <template #placeholder>
                        <div class="d-flex align-center justify-center fill-height">
                            <v-progress-circular indeterminate color="primary" size="28" />
                        </div>
                    </template>
                </v-img>

                <template v-if="interiorList.length > 1">
                    <v-btn
                        icon="mdi-chevron-left"
                        size="x-small"
                        color="white"
                        variant="flat"
                        class="position-absolute"
                        style="left: 10px; top: 50%; transform: translateY(-50%); background: rgba(16, 29, 50, 0.72) !important; color: #FFFFFF !important;"
                        aria-label="Previous interior photo"
                        @click.stop="prevInterior"
                    />
                    <v-btn
                        icon="mdi-chevron-right"
                        size="x-small"
                        color="white"
                        variant="flat"
                        class="position-absolute"
                        style="right: 10px; top: 50%; transform: translateY(-50%); background: rgba(16, 29, 50, 0.72) !important; color: #FFFFFF !important;"
                        aria-label="Next interior photo"
                        @click.stop="nextInterior"
                    />
                </template>

                <div
                    v-if="interiorList.length || vehicle.image_url"
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
                        <v-btn
                            value="exterior"
                            size="x-small"
                            prepend-icon="mdi-car-side"
                            class="vehicle-photo-view-control px-3"
                            :aria-pressed="photoView === 'exterior'"
                        >
                            Exterior
                        </v-btn>
                        <v-btn
                            value="interior"
                            size="x-small"
                            prepend-icon="mdi-car-seat"
                            class="vehicle-photo-view-control px-3"
                            :aria-pressed="photoView === 'interior'"
                            :disabled="!interiorList.length"
                        >
                            Interior{{ interiorList.length > 1 ? ` (${interiorIndex + 1}/${interiorList.length})` : '' }}
                        </v-btn>
                    </v-btn-toggle>
                </div>
            </div>
        </template>
            </v-card>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { initCardFlip } from '@/animations/flip';
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

const vehicleCard = ref(null);
const photoInner = ref(null);
const cardSides = ['exterior', 'interior'];
const photoView = ref('exterior');
let photoFlip;

onMounted(() => {
    const card = vehicleCard.value?.$el ?? vehicleCard.value;
    photoFlip = initCardFlip({
        card,
        inner: photoInner.value,
        back: photoInner.value?.querySelector('.vehicle-card-face--back'),
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    });
});

watch(photoView, (view) => photoFlip?.flip(view === 'interior'));
onBeforeUnmount(() => photoFlip?.destroy());
const interiorIndex = ref(0);

const customInteriorPhotos = {
        'Toyota|Wigo|2024': [
        '/images/vehicles/interiors/wigo-rear-seat.png',
        '/images/vehicles/interiors/wigo-city-interior.png',
        '/images/vehicles/interiors/wigo-urban-cockpit.png',
    ],
    'Toyota|Fortuner|2024': [
        '/images/vehicles/interiors/fortuner-driver-view.png',
        '/images/vehicles/interiors/fortuner-seven-seater.png',
    ],
    'Ford|Everest|2024': [
        '/images/vehicles/interiors/everest-rear-seats.png',
        '/images/vehicles/interiors/everest-cockpit.png',
        '/images/vehicles/interiors/everest-seven-seater.png',
    ],
};

const interiorList = computed(() => {
    const vehicleKey = `${props.vehicle.brand}|${props.vehicle.model}|${props.vehicle.year}`;
    if (customInteriorPhotos[vehicleKey]) return customInteriorPhotos[vehicleKey];

    if (Array.isArray(props.vehicle.interior_images) && props.vehicle.interior_images.length) {
        return props.vehicle.interior_images;
    }
    return [props.vehicle.interior_image_url, props.vehicle.second_interior_image_url].filter(Boolean);
});

const exteriorImageUrl = computed(() => props.vehicle.image_url || interiorList.value[0] || null);
const interiorImageUrl = computed(() => interiorList.value[interiorIndex.value] || interiorList.value[0] || null);

function prevInterior() {
    if (!interiorList.value.length) return;
    interiorIndex.value = (interiorIndex.value - 1 + interiorList.value.length) % interiorList.value.length;
}

function nextInterior() {
    if (!interiorList.value.length) return;
    interiorIndex.value = (interiorIndex.value + 1) % interiorList.value.length;
}

function formatMoney(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>

<style scoped>
.vehicle-card-flip {
    position: relative;
    width: 100%;
    perspective: 900px;
}

.vehicle-card-flip__inner {
    position: relative;
    width: 100%;
    transform-style: preserve-3d;
}

.vehicle-card-face {
    position: relative;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.vehicle-card-face--back {
    position: absolute;
    inset: 0;
    transform: rotateY(180deg);
}

.vehicle-card-interior-back {
    position: absolute;
    inset: 0;
    overflow: hidden;
    background-color: #EFECE6;
}

:deep(.vehicle-card-interior-image) {
    position: absolute !important;
    inset: 0;
    width: 100%;
    height: 100% !important;
}
.vehicle-card-face[aria-hidden='true'] {
    pointer-events: none;
}

:deep(.vehicle-card-face.luxury-card-hover:hover) {
    transform: none !important;
}

:deep(.vehicle-card-face--back.luxury-card-hover:hover) {
    transform: rotateY(180deg) !important;
}

:deep(.vehicle-photo-view-control:focus-visible) {
    outline: 3px solid #C6A15B !important;
    outline-offset: 2px;
    z-index: 4;
}
</style>