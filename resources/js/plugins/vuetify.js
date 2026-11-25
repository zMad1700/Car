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
    defaults: {
        VBtn: {
            rounded: 'lg',
            elevation: 0,
        },
        VCard: {
            rounded: 'xl',
            elevation: 0,
        },
        VTextField: {
            variant: 'outlined',
            color: 'primary',
            rounded: 'lg',
        },
        VSelect: {
            variant: 'outlined',
            color: 'primary',
            rounded: 'lg',
        },
        VTextarea: {
            variant: 'outlined',
            color: 'primary',
            rounded: 'lg',
        },
    },
    theme: {
        defaultTheme: 'luxuryLight',
        themes: {
            luxuryLight: {
                dark: false,
                colors: {
                    primary: '#101D32',
                    secondary: '#C6A15B',
                    accent: '#C6A15B',
                    background: '#F5F5F2',
                    surface: '#FFFFFF',
                    'on-surface': '#252525',
                    'on-background': '#252525',
                    'on-primary': '#FFFFFF',
                    'on-secondary': '#101D32',
                    info: '#1D4E89',
                    success: '#2E7D5B',
                    warning: '#B9812A',
                    error: '#B83B3B',
                },
            },
        },
    },
});

export default vuetify;
