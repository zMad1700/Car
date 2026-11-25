import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api from '@/plugins/axios';

const TOKEN_STORAGE_KEY = 'admin_token';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_STORAGE_KEY) || null);
    const user = ref(null);

    const isAuthenticated = computed(() => Boolean(token.value));

    function setToken(newToken) {
        token.value = newToken;
        if (newToken) {
            localStorage.setItem(TOKEN_STORAGE_KEY, newToken);
            api.defaults.headers.common.Authorization = `Bearer ${newToken}`;
        } else {
            localStorage.removeItem(TOKEN_STORAGE_KEY);
            delete api.defaults.headers.common.Authorization;
        }
    }

    function setUser(newUser) {
        user.value = newUser;
    }

    function clearAuth() {
        setToken(null);
        user.value = null;
    }

    return {
        token,
        user,
        isAuthenticated,
        setToken,
        setUser,
        clearAuth,
    };
});
