<template>
    <div>
        <!-- Hero Section -->
        <v-sheet color="primary" class="home-hero py-16" style="border-bottom: 3px solid #C6A15B;">
            <div class="home-hero__fleet" aria-hidden="true">
                <div ref="fleetTrack" class="home-hero__track">
                    <div
                        v-for="(car, index) in [...fleetCars, ...fleetCars]"
                        :key="`${car.name}-${index}`"
                        class="home-hero__car" :class="{ 'home-hero__car--flipped': car.flip }"
                    >
                        <img :src="car.src" alt="" draggable="false" loading="eager" />
                    </div>
                </div>
            </div>
            <v-container class="py-6 home-hero__content">
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
        <v-container ref="stepsSection" class="py-16">
            <div class="text-center mb-12">
                <div class="text-caption font-weight-bold text-uppercase mb-2" style="color: #C6A15B; letter-spacing: 0.16em;">
                    Effortless Experience
                </div>
                <h2 data-step-title class="font-display text-h3 font-weight-bold mb-3" style="color: #101D32;">
                    How It Works
                </h2>
                <div class="gold-divider mx-auto mb-3" />
                <p data-step-description class="text-body-1" style="color: #777777;">
                    Reserve your preferred vehicle in three refined steps without creating an account.
                </p>
            </div>

            <v-row>
                <v-col v-for="step in steps" :key="step.title" cols="12" md="4">
                    <v-card :data-reveal="step.direction" class="luxury-card luxury-card-hover pa-7 h-100">
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
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);

const fleetTrack = ref(null);
const stepsSection = ref(null);
let animationContext;

const fleetCars = [
    { name: 'Wigo', src: '/images/vehicles/wigo.jpg', flip: true },
    { name: 'Vios', src: '/images/vehicles/vios.jpg', flip: true },
    { name: 'Civic', src: '/images/vehicles/civic.jpg', flip: false },
    { name: 'Everest', src: '/images/vehicles/everest.jpg', flip: false },
    { name: 'Hiace', src: '/images/vehicles/hiace.jpg', flip: true },
];

onMounted(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) return;

    animationContext = gsap.context(() => {
        if (fleetTrack.value) {
            gsap.to(fleetTrack.value, {
                xPercent: -50,
                duration: 54,
                ease: 'none',
                repeat: -1,
            });
        }

        const section = stepsSection.value?.$el ?? stepsSection.value;
        if (!section) return;

        const title = section.querySelector('[data-step-title]');
        const description = section.querySelector('[data-step-description]');

        if (title) {
            gsap.fromTo(title, { autoAlpha: 0, y: -36 }, {
                autoAlpha: 1,
                y: 0,
                duration: 0.85,
                ease: 'power3.out',
                scrollTrigger: { trigger: section, start: 'top 78%', once: true },
            });
        }

        if (description) {
            gsap.fromTo(description, { autoAlpha: 0, y: 32 }, {
                autoAlpha: 1,
                y: 0,
                duration: 0.8,
                delay: 0.12,
                ease: 'power3.out',
                scrollTrigger: { trigger: section, start: 'top 78%', once: true },
            });
        }

        const offsets = {
            left: { x: -72, y: 0 },
            right: { x: 72, y: 0 },
            down: { x: 0, y: 64 },
        };

        section.querySelectorAll('[data-reveal]').forEach((card) => {
            const offset = offsets[card.dataset.reveal] ?? offsets.down;

            gsap.fromTo(card, { ...offset, autoAlpha: 0 }, {
                x: 0,
                y: 0,
                autoAlpha: 1,
                duration: 0.9,
                ease: 'power3.out',
                scrollTrigger: { trigger: card, start: 'top 86%', once: true },
            });
        });
    });
});

onBeforeUnmount(() => animationContext?.revert());
const steps = [
    {
        icon: 'mdi-car-search',
        direction: 'left',
        title: '1. Select Your Vehicle',
        description: 'Explore our curated sedans, SUVs, vans, and hatchbacks with exterior & interior previews.',
    },
    {
        icon: 'mdi-calendar-clock',
        direction: 'down',
        title: '2. Reserve Your Dates',
        description: 'Choose your pickup and return schedule and enter your contact details—no registration needed.',
    },
    {
        icon: 'mdi-ticket-confirmation-outline',
        direction: 'right',
        title: '3. Receive Your Receipt',
        description: 'Get your printable official booking receipt and reference code delivered straight to your email.',
    },
];
</script>

<style scoped>
.home-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
}

.home-hero__fleet {
    position: absolute;
    z-index: 0;
    inset: auto 0 0;
    height: clamp(230px, 34vh, 360px);
    overflow: hidden;
    pointer-events: none;
    opacity: 0.72;
}

.home-hero__fleet::after {
    position: absolute;
    z-index: 1;
    inset: 0;
    background: linear-gradient(180deg, #101d32 0%, rgba(16, 29, 50, 0.72) 30%, rgba(16, 29, 50, 0.28) 100%);
    content: '';
}

.home-hero__track {
    display: flex;
    align-items: flex-end;
    gap: 0;
    width: max-content;
    height: 100%;
    will-change: transform;
}

.home-hero__car {
    flex: 0 0 auto;
    width: auto;
    height: 100%;
    overflow: visible;
}

.home-hero__car + .home-hero__car {
    margin-left: clamp(-250px, -12vw, -110px);
    -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 30%);
    mask-image: linear-gradient(90deg, transparent 0%, #000 30%);
}
.home-hero__car img {
    display: block;
    width: auto;
    max-width: none;
    height: 100%;
    object-fit: contain;
    object-position: center bottom;
    filter: brightness(0.7) saturate(0.78);
}

.home-hero__car--flipped img {
    transform: scaleX(-1);
}

.home-hero__content {
    position: relative;
    z-index: 2;
}

@media (max-width: 600px) {
    .home-hero__fleet {
        height: 210px;
        opacity: 0.56;
    }

    .home-hero__track {
        gap: 0;
    }


}
</style>