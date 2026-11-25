<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" color="secondary" theme="dark" width="250">
            <div class="pa-4 d-flex align-center">
                <v-icon icon="mdi-car-sports" color="accent" size="28" class="mr-2" />
                <div>
                    <div class="text-subtitle-1 font-weight-bold text-white">
                        Car Rental
                    </div>
                    <div class="text-caption text-grey-lighten-1">
                        Admin Panel
                    </div>
                </div>
            </div>

            <v-divider class="border-opacity-25" />

            <v-list nav density="comfortable" class="mt-2">
                <v-list-item
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    :exact="item.exact"
                    rounded="lg"
                    active-color="accent"
                />
            </v-list>

            <template #append>
                <div class="pa-3">
                    <v-btn
                        to="/"
                        variant="outlined"
                        color="grey-lighten-2"
                        block
                        size="small"
                        prepend-icon="mdi-open-in-new"
                        class="mb-2"
                    >
                        Public Site
                    </v-btn>
                    <v-btn
                        color="error"
                        variant="tonal"
                        block
                        size="small"
                        prepend-icon="mdi-logout"
                        @click="handleLogout"
                    >
                        Logout
                    </v-btn>
                </div>
            </template>
        </v-navigation-drawer>

        <v-app-bar color="white" elevation="1" border="b">
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title class="font-weight-bold text-secondary">
                {{ pageTitle }}
            </v-toolbar-title>

            <v-spacer />

            <div class="d-flex align-center ga-3 mr-4">
                <div class="text-right d-none d-sm-block">
                    <div class="text-body-2 font-weight-medium text-secondary">
                        {{ authStore.user?.name || 'Admin' }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                        {{ authStore.user?.email }}
                    </div>
                </div>
                <v-btn
                    icon="mdi-logout"
                    variant="text"
                    color="error"
                    title="Logout"
                    @click="handleLogout"
                />
            </div>
        </v-app-bar>

        <v-main class="bg-background">
            <v-container fluid class="pa-6">
                <router-view />
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const drawer = ref(true);
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const navItems = [
    { title: 'Dashboard', icon: 'mdi-view-dashboard-outline', to: '/admin', exact: true },
    { title: 'Vehicles', icon: 'mdi-car-multiple', to: '/admin/vehicles', exact: false },
    { title: 'Categories', icon: 'mdi-shape-outline', to: '/admin/categories', exact: false },
    { title: 'Bookings', icon: 'mdi-calendar-check-outline', to: '/admin/bookings', exact: false },
    { title: 'Rentals', icon: 'mdi-key-chain-variant', to: '/admin/rentals', exact: false },
    { title: 'Payments', icon: 'mdi-cash-multiple', to: '/admin/payments', exact: false },
    { title: 'Maintenance', icon: 'mdi-wrench-outline', to: '/admin/maintenance', exact: false },
    { title: 'Reports', icon: 'mdi-chart-box-outline', to: '/admin/reports', exact: false },
];

const pageTitle = computed(() => route.meta.pageTitle || 'Admin Panel');

async function handleLogout() {
    await authStore.logout();
    await router.push('/staff-portal');
}
</script>
