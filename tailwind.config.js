import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './resources/views/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                elegant: ['"Noto Serif JP"', '"Yu Mincho"', '"Hiragino Mincho ProN"', 'serif'],
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                playfair: ['"Playfair Display"', 'serif'],
                noto: ['"Noto Serif JP"', 'serif'],
                cursive: ['"Dancing Script"', 'cursive']
            },
            colors: {
                theme: {
                    nomal: {
                        bg: '#FDF2F8',
                        text: '#111827',
                        accent: '#EC4899',
                    },
                    rose: {
                        bg: '#FFF1F2',
                        text: '#BE123C',
                        accent: '#F43F5E',
                    },
                    mist: {
                        bg: '#F9FAFB',
                        text: '#374151',
                        accent: '#6366F1',
                    },
                    antique: {
                        bg: '#FFF8E1',
                        text: '#8D6E63',
                        accent: '#FFC107',
                    }
                }
            },
        },
    },

    plugins: [forms],
};
