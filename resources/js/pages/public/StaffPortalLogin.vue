<template>
    <v-container class="fill-height py-12" style="min-height: calc(100vh - 140px);">
        <v-row justify="center" align="center">
            <v-col cols="12" sm="8" md="5" lg="4">
                <v-card class="luxury-card pa-7" style="border-top: 4px solid #C6A15B !important;">
                    <div class="text-center mb-6">
                        <v-avatar
                            color="primary"
                            size="60"
                            rounded="lg"
                            class="mb-4"
                            style="border: 1px solid rgba(198, 161, 91, 0.5);"
                        >
                            <v-icon icon="mdi-shield-lock-outline" size="28" color="secondary" />
                        </v-avatar>
                        <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                            Executive Access
                        </div>
                        <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                            Staff Portal
                        </h1>
                        <p class="text-body-2 mt-1" style="color: #777777;">
                            Authorized administrator sign-in only
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
                            density="comfortable"
                            :rules="passwordRules"
                            autocomplete="current-password"
                            class="mb-5"
                            @click:append-inner="showPassword = !showPassword"
                        />

                        <v-btn
                            type="submit"
                            color="primary"
                            size="large"
                            block
                            variant="flat"
                            :loading="authStore.loading"
                            class="font-weight-semibold"
                        >
                            Sign In
                        </v-btn>
                    </v-form>

                    <div class="text-center mt-5">
                        <v-btn
                            to="/"
                            variant="text"
                            size="small"
                            color="primary"
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
