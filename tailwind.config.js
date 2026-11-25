import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    forest: 'rgb(var(--color-brand-forest) / <alpha-value>)',
                    sunshine: 'rgb(var(--color-brand-sunshine) / <alpha-value>)',
                    cream: 'rgb(var(--color-brand-cream) / <alpha-value>)',
                    leaf: 'rgb(var(--color-brand-leaf) / <alpha-value>)',
                    adventure: 'rgb(var(--color-brand-adventure) / <alpha-value>)',
                    charcoal: 'rgb(var(--color-brand-charcoal) / <alpha-value>)',
                    secondary: 'rgb(var(--color-brand-secondary) / <alpha-value>)',
                    surface: 'rgb(var(--color-brand-surface) / <alpha-value>)',
                    border: 'rgb(var(--color-brand-border) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
