<template>
    <div>
        <!-- Hero Section -->
        <v-sheet color="secondary" class="py-16">
            <v-container>
                <v-row align="center" justify="space-between">
                    <v-col cols="12" md="7">
                        <v-chip
                            color="accent"
                            variant="flat"
                            size="small"
                            class="mb-4 font-weight-bold text-uppercase"
                        >
                            Fast &amp; Hassle-Free Booking
                        </v-chip>

                        <h1 class="text-h2 text-sm-h1 font-weight-bold text-white mb-4">
                            Car Rental
                        </h1>

                        <p class="text-h6 text-grey-lighten-2 font-weight-regular mb-8" style="max-width: 560px;">
                            Reliable vehicles ready whenever you are. Browse available cars, pick your dates, and reserve in minutes—no account required.
                        </p>

                        <div class="d-flex flex-wrap ga-3">
                            <v-btn
                                to="/cars"
                                color="primary"
                                size="x-large"
                                variant="flat"
                                prepend-icon="mdi-car-arrow-right"
                            >
                                Book Now
                            </v-btn>

                            <v-btn
                                to="/track"
                                color="white"
                                size="x-large"
                                variant="outlined"
                                prepend-icon="mdi-magnify"
                            >
                                Track Booking
                            </v-btn>
                        </div>
                    </v-col>

                    <v-col cols="12" md="5" class="d-none d-md-flex justify-center">
                        <v-card
                            color="primary"
                            variant="tonal"
                            rounded="xl"
                            class="pa-10 text-center"
                            width="100%"
                            max-width="380"
                        >
                            <v-icon icon="mdi-car-multiple" size="110" color="white" class="mb-4" />
                            <div class="text-h6 text-white font-weight-medium">
                                Ready for the Road
                            </div>
                            <div class="text-body-2 text-grey-lighten-2 mt-1">
                                Instant booking reference &amp; email confirmation
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-sheet>

        <!-- How It Works Section -->
        <v-container class="py-14">
            <div class="text-center mb-10">
                <h2 class="text-h4 font-weight-bold text-secondary mb-2">
                    How It Works
                </h2>
                <p class="text-body-1 text-medium-emphasis">
                    Rent a car in three simple steps without creating an account.
                </p>
            </div>

            <v-row>
                <v-col v-for="step in steps" :key="step.title" cols="12" md="4">
                    <v-card elevation="1" rounded="lg" class="pa-6 h-100" border>
                        <v-avatar color="primary" variant="tonal" size="52" class="mb-4">
                            <v-icon :icon="step.icon" size="28" />
                        </v-avatar>
                        <h3 class="text-h6 font-weight-bold text-secondary mb-2">
                            {{ step.title }}
                        </h3>
                        <p class="text-body-2 text-medium-emphasis mb-0">
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
        title: '1. Choose Your Vehicle',
        description: 'Explore our fleet by category, transmission, seating capacity, and daily rate.',
    },
    {
        icon: 'mdi-calendar-clock',
        title: '2. Submit Booking Details',
        description: 'Pick your pickup and return schedule and enter your contact details—no registration needed.',
    },
    {
        icon: 'mdi-ticket-confirmation-outline',
        title: '3. Get Your Reference Code',
        description: 'Receive your printable booking receipt and email confirmation right away.',
    },
];
</script>
