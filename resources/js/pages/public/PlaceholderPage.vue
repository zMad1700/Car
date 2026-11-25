<template>
    <v-container class="py-16">
        <v-card max-width="560" class="luxury-card mx-auto pa-8 text-center">
            <v-avatar
                color="primary"
                size="64"
                rounded="lg"
                class="mb-4"
                style="border: 1px solid rgba(198, 161, 91, 0.45);"
            >
                <v-icon :icon="icon" size="30" color="secondary" />
            </v-avatar>
            <h1 class="font-display text-h4 font-weight-bold mb-2" style="color: #101D32;">
                {{ title }}
            </h1>
            <p class="text-body-1 mb-6" style="color: #777777;">
                {{ description }}
            </p>
            <v-btn to="/" color="primary" variant="flat" prepend-icon="mdi-arrow-left">
                Back to Home
            </v-btn>
        </v-card>
    </v-container>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Coming Soon',
    },
    description: {
        type: String,
        default: 'This section will be implemented in an upcoming phase.',
    },
    icon: {
        type: String,
        default: 'mdi-wrench-outline',
    },
});
</script>
