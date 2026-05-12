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
                sans: ['Montserrat', 'Avenir Next', ...defaultTheme.fontFamily.sans],
                display: ['Cormorant Garamond', 'Georgia', ...defaultTheme.fontFamily.serif],
            },

            colors: {
                brand: {
                    primary: '#A7B88A',
                    secondary: '#6B4A3A',
                    background: '#F6F2EB',
                    'surface-warm': '#EDE7DB',
                    'surface-cool': '#B8CDBD',
                    accent: '#E9B07A',
                    text: '#6B4A3A',
                    'text-strong': '#3F2B22',
                    border: '#D8CFBF',
                },
                primary: {
                    DEFAULT: '#6B4A3A',
                    50:  '#F7F8F2',
                    100: '#EDF1E5',
                    200: '#DCE4D1',
                    300: '#C7D3B2',
                    400: '#B8C79D',
                    500: '#A7B88A',
                    600: '#5F7048',
                    700: '#4E5F3A',
                    800: '#3D4B2F',
                    900: '#303D27',
                    950: '#1B2417',
                },
                secondary: {
                    DEFAULT: '#6B4A3A',
                    50:  '#F8F3EF',
                    100: '#EDE1DA',
                    200: '#D9C1B5',
                    300: '#BE9986',
                    400: '#A2745C',
                    500: '#865942',
                    600: '#6B4A3A',
                    700: '#563A2F',
                    800: '#432E26',
                    900: '#34241F',
                    950: '#1E1410',
                },
                dark: {
                    DEFAULT: '#3F2B22',
                    50:  '#F6F2EB',
                    100: '#EDE7DB',
                    200: '#D8CFBF',
                    300: '#BDAF9F',
                    400: '#988273',
                    500: '#7B6254',
                    600: '#634D42',
                    700: '#4F3B33',
                    800: '#3F2B22',
                    900: '#2F201A',
                    950: '#1C120E',
                },
            },
        },
    },

    plugins: [forms],
};
