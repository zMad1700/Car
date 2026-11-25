<template>
    <v-chip :color="chipConfig.color" :prepend-icon="chipConfig.icon" size="small" variant="tonal" class="font-weight-medium text-capitalize">
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
    available: { color: 'success', icon: 'mdi-check-circle-outline' },
    booked: { color: 'info', icon: 'mdi-calendar-check-outline' },
    rented: { color: 'warning', icon: 'mdi-car-key' },
    maintenance: { color: 'error', icon: 'mdi-wrench-outline' },
};

const chipConfig = computed(() => STATUS_MAP[props.status] || { color: 'grey', icon: 'mdi-help-circle-outline' });
</script>
