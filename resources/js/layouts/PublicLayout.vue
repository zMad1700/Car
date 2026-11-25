<template>
    <v-app>
        <v-app-bar color="white" elevation="1" border="b">
            <v-container class="d-flex align-center py-0">
                <router-link
                    to="/"
                    class="d-flex align-center text-decoration-none text-secondary"
                >
                    <v-icon icon="mdi-car-sports" color="primary" size="30" class="mr-2" />
                    <span class="text-h6 font-weight-bold">Car Rental</span>
                </router-link>

                <v-spacer />

                <div class="d-flex align-center ga-1">
                    <v-btn to="/" variant="text" color="secondary" exact>
                        Home
                    </v-btn>
                    <v-btn to="/cars" variant="text" color="secondary">
                        Cars
                    </v-btn>
                    <v-btn to="/track" variant="text" color="secondary">
                        Track Booking
                    </v-btn>
                    <v-btn
                        to="/cars"
                        color="primary"
                        variant="flat"
                        class="ml-2 d-none d-sm-inline-flex"
                    >
                        Book Now
                    </v-btn>
                </div>
            </v-container>
        </v-app-bar>

        <v-main class="bg-background">
            <router-view />
        </v-main>

        <v-footer color="secondary" class="py-4">
            <v-container class="d-flex align-center justify-space-between text-body-2 text-grey-lighten-1">
                <span>&copy; {{ currentYear }} Car Rental. All rights reserved.</span>

                <v-btn
                    to="/staff-portal"
                    icon="mdi-lock-outline"
                    variant="text"
                    size="x-small"
                    color="grey-darken-1"
                    aria-label="Staff Portal"
                />
            </v-container>
        </v-footer>
    </v-app>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
