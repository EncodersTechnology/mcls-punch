import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Calm clinical teal. One confident accent, used sparingly.
                brand: {
                    50:  '#EFFAF7', 100: '#D6F2EA', 200: '#AEE5D6', 300: '#79D0BC',
                    400: '#45B39C', 500: '#249681', 600: '#157868', 700: '#136055',
                    800: '#134D45', 900: '#12403A', 950: '#052521',
                },
                // Neutrals with a faint cool-green bias so they sit with the accent.
                ink: {
                    50:  '#F6F8F7', 100: '#ECEFEE', 200: '#DCE1E0', 300: '#C0C9C7',
                    400: '#94A19E', 500: '#6E7C79', 600: '#576260', 700: '#47504F',
                    800: '#3C4443', 900: '#1A2321', 950: '#0E1514',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(14, 21, 20, .04), 0 8px 24px -18px rgba(14, 21, 20, .28)',
                lift: '0 2px 4px rgba(14, 21, 20, .05), 0 16px 40px -24px rgba(14, 21, 20, .35)',
                ring: '0 0 0 1px rgba(14, 21, 20, .06)',
            },
            borderRadius: { xl2: '0.875rem' },
            keyframes: {
                'fade-up':  { '0%': { opacity: 0, transform: 'translateY(6px)' }, '100%': { opacity: 1, transform: 'none' } },
                'fade-in':  { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
            },
            animation: {
                'fade-up': 'fade-up .28s cubic-bezier(.22,1,.36,1) both',
                'fade-in': 'fade-in .2s ease-out both',
            },
        },
    },

    plugins: [forms],
};
