import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

const vuetify = createVuetify({
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#1E40AF',
                    secondary: '#0F172A',
                    accent: '#F59E0B',
                    background: '#F8FAFC',
                    surface: '#FFFFFF',
                    info: '#0284C7',
                    success: '#16A34A',
                    warning: '#D97706',
                    error: '#DC2626',
                },
            },
        },
    },
});

export default vuetify;
