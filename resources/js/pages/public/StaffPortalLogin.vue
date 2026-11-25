<template>
    <v-container class="fill-height py-12" style="min-height: calc(100vh - 120px);">
        <v-row justify="center" align="center">
            <v-col cols="12" sm="8" md="5" lg="4">
                <v-card elevation="2" border rounded="lg" class="pa-6">
                    <div class="text-center mb-6">
                        <v-avatar color="secondary" size="56" class="mb-3">
                            <v-icon icon="mdi-shield-lock-outline" size="28" color="white" />
                        </v-avatar>
                        <h1 class="text-h5 font-weight-bold text-secondary">
                            Staff Portal
                        </h1>
                        <p class="text-body-2 text-medium-emphasis mt-1">
                            Authorized administrator access only
                        </p>
                    </div>

                    <v-alert
                        v-if="errorMessage"
                        type="error"
                        variant="tonal"
                        density="compact"
                        class="mb-4"
                        closable
                        @click:close="errorMessage = ''"
                    >
                        {{ errorMessage }}
                    </v-alert>

                    <v-form ref="formRef" v-model="formValid" @submit.prevent="handleLogin">
                        <v-text-field
                            v-model="email"
                            label="Admin Email"
                            type="email"
                            prepend-inner-icon="mdi-email-outline"
                            variant="outlined"
                            density="comfortable"
                            :rules="emailRules"
                            autocomplete="email"
                            class="mb-3"
                        />

                        <v-text-field
                            v-model="password"
                            label="Password"
                            :type="showPassword ? 'text' : 'password'"
                            prepend-inner-icon="mdi-lock-outline"
                            :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                            variant="outlined"
                            density="comfortable"
                            :rules="passwordRules"
                            autocomplete="current-password"
                            class="mb-4"
                            @click:append-inner="showPassword = !showPassword"
                        />

                        <v-btn
                            type="submit"
                            color="primary"
                            size="large"
                            block
                            variant="flat"
                            :loading="authStore.loading"
                        >
                            Sign In
                        </v-btn>
                    </v-form>

                    <div class="text-center mt-5">
                        <v-btn
                            to="/"
                            variant="text"
                            size="small"
                            color="secondary"
                            prepend-icon="mdi-arrow-left"
                        >
                            Back to Car Rental
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const formRef = ref(null);
const formValid = ref(false);
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const errorMessage = ref('');

const emailRules = [
    (v) => !!v || 'Email is required.',
    (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email address.',
];

const passwordRules = [
    (v) => !!v || 'Password is required.',
];

async function handleLogin() {
    errorMessage.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    try {
        await authStore.login({
            email: email.value.trim(),
            password: password.value,
        });
        const redirectTo = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/admin')
            ? route.query.redirect
            : '/admin';
        await router.push(redirectTo);
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Unable to sign in. Please check your credentials.';
    }
}
</script>
