<template>
    <v-container class="py-16">
        <v-card max-width="560" class="mx-auto pa-8 text-center" elevation="1" border rounded="lg">
            <v-icon :icon="icon" size="56" color="primary" class="mb-4" />
            <h1 class="text-h4 font-weight-bold text-secondary mb-2">
                {{ title }}
            </h1>
            <p class="text-body-1 text-medium-emphasis mb-6">
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
