<template>
    <div>
        <!-- Hero Section -->
        <v-sheet color="primary" class="py-16" style="border-bottom: 3px solid #C6A15B;">
            <v-container class="py-6">
                <v-row align="center" justify="space-between">
                    <v-col cols="12" md="7">
                        <div class="d-inline-flex align-center ga-2 px-3 py-1 rounded-pill mb-5" style="background-color: rgba(198, 161, 91, 0.14); border: 1px solid rgba(198, 161, 91, 0.4);">
                            <v-icon icon="mdi-crown-outline" size="16" color="secondary" />
                            <span class="text-caption font-weight-bold text-uppercase" style="color: #C6A15B; letter-spacing: 0.14em;">
                                Classic Luxury &amp; Executive Mobility
                            </span>
                        </div>

                        <h1 class="font-display text-h2 text-sm-h1 font-weight-bold text-white mb-4">
                            Car Rental
                        </h1>

                        <div class="gold-divider mb-6" />

                        <p class="text-h6 font-weight-regular mb-8" style="max-width: 560px; color: rgba(255, 255, 255, 0.82); line-height: 1.6;">
                            Curated vehicles maintained to executive standards. Browse our fleet, select your dates, and reserve with instant confirmation—no account required.
                        </p>

                        <div class="d-flex flex-wrap ga-3">
                            <v-btn
                                to="/cars"
                                color="secondary"
                                size="x-large"
                                variant="flat"
                                prepend-icon="mdi-car-arrow-right"
                                class="font-weight-bold text-primary px-7"
                            >
                                Book Now
                            </v-btn>

                            <v-btn
                                to="/track"
                                color="secondary"
                                size="x-large"
                                variant="outlined"
                                prepend-icon="mdi-magnify"
                                class="px-6"
                            >
                                Track Booking
                            </v-btn>
                        </div>
                    </v-col>

                    <v-col cols="12" md="5" class="d-none d-md-flex justify-center">
                        <v-card
                            color="rgba(255, 255, 255, 0.04)"
                            rounded="xl"
                            class="pa-10 text-center"
                            width="100%"
                            max-width="400"
                            style="border: 1px solid rgba(198, 161, 91, 0.38);"
                        >
                            <v-avatar
                                size="92"
                                color="rgba(198, 161, 91, 0.14)"
                                class="mb-5"
                                style="border: 1px solid rgba(198, 161, 91, 0.45);"
                            >
                                <v-icon icon="mdi-car-multiple" size="46" color="secondary" />
                            </v-avatar>
                            <div class="font-display text-h5 font-weight-bold text-white">
                                Seamless Reservation
                            </div>
                            <div class="text-body-2 mt-2" style="color: rgba(255, 255, 255, 0.72);">
                                Instant booking reference code &amp; official email receipt upon confirmation
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-sheet>

        <!-- How It Works Section -->
        <v-container class="py-16">
            <div class="text-center mb-12">
                <div class="text-caption font-weight-bold text-uppercase mb-2" style="color: #C6A15B; letter-spacing: 0.16em;">
                    Effortless Experience
                </div>
                <h2 class="font-display text-h3 font-weight-bold mb-3" style="color: #101D32;">
                    How It Works
                </h2>
                <div class="gold-divider mx-auto mb-3" />
                <p class="text-body-1" style="color: #777777;">
                    Reserve your preferred vehicle in three refined steps without creating an account.
                </p>
            </div>

            <v-row>
                <v-col v-for="step in steps" :key="step.title" cols="12" md="4">
                    <v-card class="luxury-card luxury-card-hover pa-7 h-100">
                        <v-avatar
                            color="primary"
                            size="54"
                            rounded="lg"
                            class="mb-5"
                            style="border: 1px solid rgba(198, 161, 91, 0.5);"
                        >
                            <v-icon :icon="step.icon" color="secondary" size="26" />
                        </v-avatar>
                        <h3 class="font-display text-h5 font-weight-bold mb-2" style="color: #101D32;">
                            {{ step.title }}
                        </h3>
                        <p class="text-body-2 mb-0" style="color: #777777; line-height: 1.65;">
                            {{ step.description }}
                        </p>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>

<script setup>
const steps = [
    {
        icon: 'mdi-car-search',
        title: '1. Select Your Vehicle',
        description: 'Explore our curated sedans, SUVs, vans, and hatchbacks with exterior & interior previews.',
    },
    {
        icon: 'mdi-calendar-clock',
        title: '2. Reserve Your Dates',
        description: 'Choose your pickup and return schedule and enter your contact details—no registration needed.',
    },
    {
        icon: 'mdi-ticket-confirmation-outline',
        title: '3. Receive Your Receipt',
        description: 'Get your printable official booking receipt and reference code delivered straight to your email.',
    },
];
</script>
