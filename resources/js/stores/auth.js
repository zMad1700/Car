import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api from '@/plugins/axios';

const TOKEN_STORAGE_KEY = 'admin_token';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_STORAGE_KEY) || null);
    const user = ref(null);
    const loading = ref(false);

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

    async function login(credentials) {
        loading.value = true;
        try {
            const { data } = await api.post('/login', credentials);
            setToken(data.token);
            setUser(data.user);
            return data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchUser() {
        if (!token.value) {
            return null;
        }
        try {
            const { data } = await api.get('/admin/me');
            setUser(data.user);
            return data.user;
        } catch (error) {
            clearAuth();
            return null;
        }
    }

    async function logout() {
        try {
            if (token.value) {
                await api.post('/admin/logout');
            }
        } catch (_) {
            // Ignore network/401 errors during logout and always clear local auth
        } finally {
            clearAuth();
        }
    }

    return {
        token,
        user,
        loading,
        isAuthenticated,
        setToken,
        setUser,
        clearAuth,
        login,
        fetchUser,
        logout,
    };
});
