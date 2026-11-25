import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import PublicLayout from '@/layouts/PublicLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import HomePage from '@/pages/public/HomePage.vue';
import CarsPage from '@/pages/public/CarsPage.vue';
import BookingPage from '@/pages/public/BookingPage.vue';
import ReceiptPage from '@/pages/public/ReceiptPage.vue';
import PlaceholderPage from '@/pages/public/PlaceholderPage.vue';
import StaffPortalLogin from '@/pages/public/StaffPortalLogin.vue';
import DashboardPage from '@/pages/admin/DashboardPage.vue';
import VehiclesPage from '@/pages/admin/VehiclesPage.vue';
import CategoriesPage from '@/pages/admin/CategoriesPage.vue';
import BookingsPage from '@/pages/admin/BookingsPage.vue';

const routes = [
    {
        path: '/',
        component: PublicLayout,
        children: [
            {
                path: '',
                name: 'home',
                component: HomePage,
                meta: { title: 'Car Rental - Home' },
            },
            {
                path: 'cars',
                name: 'cars',
                component: CarsPage,
                meta: { title: 'Car Rental - Cars' },
            },
            {
                path: 'book/:vehicleId?',
                name: 'book',
                component: BookingPage,
                meta: { title: 'Car Rental - Book a Vehicle' },
            },
            {
                path: 'receipt/:referenceCode',
                name: 'receipt',
                component: ReceiptPage,
                meta: { title: 'Car Rental - Booking Receipt' },
            },
            {
                path: 'track',
                name: 'track',
                component: PlaceholderPage,
                props: {
                    title: 'Track Your Booking',
                    description: 'Booking status tracking will be added in Phase 10.',
                    icon: 'mdi-magnify',
                },
                meta: { title: 'Car Rental - Track Booking' },
            },
            {
                path: 'staff-portal',
                name: 'staff-portal',
                component: StaffPortalLogin,
                meta: { title: 'Staff Portal', guestOnly: true },
            },
        ],
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'admin.dashboard',
                component: DashboardPage,
                meta: { title: 'Admin - Dashboard', pageTitle: 'Dashboard' },
            },
            {
                path: 'vehicles',
                name: 'admin.vehicles',
                component: VehiclesPage,
                meta: { title: 'Admin - Vehicles', pageTitle: 'Vehicles' },
            },
            {
                path: 'categories',
                name: 'admin.categories',
                component: CategoriesPage,
                meta: { title: 'Admin - Categories', pageTitle: 'Vehicle Categories' },
            },
            {
                path: 'bookings',
                name: 'admin.bookings',
                component: BookingsPage,
                meta: { title: 'Admin - Bookings', pageTitle: 'Bookings' },
            },
            {
                path: 'rentals',
                name: 'admin.rentals',
                component: PlaceholderPage,
                props: {
                    title: 'Rentals Management',
                    description: 'Vehicle release and return will be implemented in Phase 8.',
                    icon: 'mdi-key-chain-variant',
                },
                meta: { title: 'Admin - Rentals', pageTitle: 'Rentals' },
            },
            {
                path: 'payments',
                name: 'admin.payments',
                component: PlaceholderPage,
                props: {
                    title: 'Payments',
                    description: 'Payment recording will be implemented in Phase 8.',
                    icon: 'mdi-cash-multiple',
                },
                meta: { title: 'Admin - Payments', pageTitle: 'Payments' },
            },
            {
                path: 'maintenance',
                name: 'admin.maintenance',
                component: PlaceholderPage,
                props: {
                    title: 'Maintenance',
                    description: 'Vehicle maintenance CRUD will be implemented in Phase 9.',
                    icon: 'mdi-wrench-outline',
                },
                meta: { title: 'Admin - Maintenance', pageTitle: 'Maintenance' },
            },
            {
                path: 'reports',
                name: 'admin.reports',
                component: PlaceholderPage,
                props: {
                    title: 'Reports',
                    description: 'Income, most rented, and overdue reports will be implemented in Phase 9.',
                    icon: 'mdi-chart-box-outline',
                },
                meta: { title: 'Admin - Reports', pageTitle: 'Reports' },
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (to.matched.some((record) => record.meta.requiresAuth)) {
        if (!authStore.isAuthenticated) {
            return {
                name: 'staff-portal',
                query: { redirect: to.fullPath },
            };
        }

        if (!authStore.user) {
            const user = await authStore.fetchUser();
            if (!user) {
                return {
                    name: 'staff-portal',
                    query: { redirect: to.fullPath },
                };
            }
        }
    }

    if (to.matched.some((record) => record.meta.guestOnly) && authStore.isAuthenticated) {
        if (!authStore.user) {
            const user = await authStore.fetchUser();
            if (user) {
                return { name: 'admin.dashboard' };
            }
        } else {
            return { name: 'admin.dashboard' };
        }
    }

    return true;
});

router.afterEach((to) => {
    document.title = to.meta.title || 'Car Rental';
});

export default router;
