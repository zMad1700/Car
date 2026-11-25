import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import HomePage from '@/pages/public/HomePage.vue';
import PlaceholderPage from '@/pages/public/PlaceholderPage.vue';

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
                component: PlaceholderPage,
                props: {
                    title: 'Available Vehicles',
                    description: 'Vehicle browsing and booking will be added in Phases 5 & 6.',
                    icon: 'mdi-car-multiple',
                },
                meta: { title: 'Car Rental - Cars' },
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
                component: PlaceholderPage,
                props: {
                    title: 'Staff Portal',
                    description: 'Admin authentication will be implemented in Phase 3.',
                    icon: 'mdi-shield-lock-outline',
                },
                meta: { title: 'Staff Portal' },
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

router.afterEach((to) => {
    document.title = to.meta.title || 'Car Rental';
});

export default router;
