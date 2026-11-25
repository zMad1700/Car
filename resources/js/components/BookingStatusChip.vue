<template>
    <v-chip
        :color="chipConfig.color"
        :prepend-icon="chipConfig.icon"
        size="small"
        variant="tonal"
        class="font-weight-medium text-capitalize"
    >
        {{ status }}
    </v-chip>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const STATUS_MAP = {
    pending: { color: 'warning', icon: 'mdi-clock-outline' },
    confirmed: { color: 'info', icon: 'mdi-check-decagram-outline' },
    completed: { color: 'success', icon: 'mdi-flag-checkered' },
    cancelled: { color: 'error', icon: 'mdi-close-circle-outline' },
};

const chipConfig = computed(() => STATUS_MAP[props.status] || { color: 'grey', icon: 'mdi-help-circle-outline' });
</script>
