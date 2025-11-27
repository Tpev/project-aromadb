// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [
        require('./vendor/tallstackui/tallstackui/tailwind.config.js'),
    ],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/tallstackui/tallstackui/src/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            // 🎨 Palette AromaMade
            colors: {
                primary: {
                    DEFAULT: '#647a0b',   // vert AromaMade
                    50:  '#f7f9ec',
                    100: '#e8f0c5',
                    200: '#d2e391',
                    300: '#b4d05b',
                    400: '#95ba2e',
                    500: '#7da018',
                    600: '#647a0b',
                    700: '#4c5d07',
                    800: '#394506',
                    900: '#2a3305',
                    950: '#171c02',
                },
                secondary: {
                    DEFAULT: '#854f38',   // brun AromaMade
                    50:  '#fbf3f0',
                    100: '#f4ddd3',
                    200: '#e8b79f',
                    300: '#d98a67',
                    400: '#c9663f',
                    500: '#b44c27',
                    600: '#963c1c',
                    700: '#743017',
                    800: '#592814',
                    900: '#472312',
                    950: '#261008',
                },
                dark: {
                    DEFAULT: '#2f3437',
                    50:  '#f5f6f7',
                    100: '#e4e6e8',
                    200: '#c8ccd0',
                    300: '#a0a5ac',
                    400: '#787f87',
                    500: '#5c636b',
                    600: '#474d54',
                    700: '#383d42',
                    800: '#2f3437',
                    900: '#24282b',
                    950: '#151719',
                },
            },
        },
    },

    plugins: [forms],
};
