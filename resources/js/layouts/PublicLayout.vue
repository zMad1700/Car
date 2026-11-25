<template>
    <v-app>
        <v-app-bar color="surface" elevation="0" height="72" class="d-print-none" style="border-bottom: 1px solid #E6E4DD;">
            <v-container class="d-flex align-center py-0">
                <router-link
                    to="/"
                    class="d-flex align-center text-decoration-none"
                    style="color: #101D32;"
                >
                    <v-avatar color="primary" size="38" class="mr-3" rounded="lg">
                        <v-icon icon="mdi-car-sports" color="secondary" size="22" />
                    </v-avatar>
                    <div>
                        <div class="font-display text-h6 font-weight-bold" style="line-height: 1.1; color: #101D32;">
                            Car Rental
                        </div>
                        <div class="text-caption" style="color: #777777; font-size: 0.68rem !important; letter-spacing: 0.14em; text-transform: uppercase;">
                            Executive Fleet
                        </div>
                    </div>
                </router-link>

                <v-spacer />

                <div class="d-flex align-center ga-1 ga-sm-2">
                    <v-btn to="/" variant="text" color="primary" exact class="text-body-2 font-weight-medium">
                        Home
                    </v-btn>
                    <v-btn to="/cars" variant="text" color="primary" class="text-body-2 font-weight-medium">
                        Cars
                    </v-btn>
                    <v-btn to="/track" variant="text" color="primary" class="text-body-2 font-weight-medium">
                        Track Booking
                    </v-btn>
                </div>
            </v-container>
        </v-app-bar>

        <v-main class="bg-background">
            <router-view />
        </v-main>

        <v-footer color="primary" class="py-5 d-print-none" style="border-top: 2px solid #C6A15B;">
            <v-container class="d-flex align-center justify-space-between text-body-2">
                <div class="d-flex align-center ga-3">
                    <v-icon icon="mdi-car-sports" color="secondary" size="20" />
                    <span style="color: rgba(255, 255, 255, 0.78);">
                        &copy; {{ currentYear }} Car Rental. Classic Luxury &amp; Mobility.
                    </span>
                </div>

                <v-btn
                    to="/staff-portal"
                    icon="mdi-lock-outline"
                    variant="text"
                    size="x-small"
                    style="color: rgba(198, 161, 91, 0.55);"
                    aria-label="Staff Portal"
                />
            </v-container>
        </v-footer>
    </v-app>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
