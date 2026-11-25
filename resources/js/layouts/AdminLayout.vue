<template>
    <v-app>
        <v-navigation-drawer
            v-model="drawer"
            color="primary"
            theme="dark"
            width="264"
            class="border-0"
        >
            <!-- Brand Header -->
            <div class="pa-5 d-flex align-center">
                <v-avatar
                    color="rgba(198, 161, 91, 0.16)"
                    size="42"
                    rounded="lg"
                    class="mr-3"
                    style="border: 1px solid rgba(198, 161, 91, 0.45);"
                >
                    <v-icon icon="mdi-car-sports" color="secondary" size="24" />
                </v-avatar>
                <div>
                    <div class="font-display text-h6 font-weight-bold text-white" style="line-height: 1.15;">
                        Car Rental
                    </div>
                    <div class="text-caption" style="color: #C6A15B; letter-spacing: 0.14em; font-size: 0.65rem !important; text-transform: uppercase;">
                        Executive Suite
                    </div>
                </div>
            </div>

            <v-divider style="border-color: rgba(198, 161, 91, 0.22);" />

            <div class="px-4 pt-4 pb-1 text-caption font-weight-bold" style="color: rgba(255, 255, 255, 0.48); letter-spacing: 0.12em; text-transform: uppercase;">
                Management
            </div>

            <v-list nav density="comfortable" class="px-3">
                <v-list-item
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    :exact="item.exact"
                    rounded="lg"
                    class="mb-1 admin-nav-item"
                />
            </v-list>

            <template #append>
                <v-divider style="border-color: rgba(198, 161, 91, 0.2);" />
                <div class="pa-4 d-flex flex-column ga-2">
                    <v-btn
                        to="/"
                        variant="outlined"
                        color="secondary"
                        block
                        size="small"
                        prepend-icon="mdi-open-in-new"
                    >
                        Public Site
                    </v-btn>
                    <v-btn
                        variant="tonal"
                        color="error"
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

        <v-app-bar color="surface" elevation="0" height="68" style="border-bottom: 1px solid #E6E4DD;">
            <v-app-bar-nav-icon color="primary" @click="drawer = !drawer" />
            <v-toolbar-title class="font-display text-h5 font-weight-bold" style="color: #101D32;">
                {{ pageTitle }}
            </v-toolbar-title>
        </v-app-bar>

        <v-main class="bg-background">
            <v-container fluid class="pa-5 pa-md-7">
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

<style scoped>
.admin-nav-item {
    color: rgba(255, 255, 255, 0.78) !important;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.admin-nav-item :deep(.v-icon) {
    color: rgba(198, 161, 91, 0.85) !important;
}

.admin-nav-item.v-list-item--active {
    background-color: rgba(198, 161, 91, 0.16) !important;
    color: #FFFFFF !important;
    border-left: 3px solid #C6A15B;
}

.admin-nav-item.v-list-item--active :deep(.v-icon) {
    color: #C6A15B !important;
}
</style>
