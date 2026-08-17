// tailwind.config.js

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
            // === AJOUTEZ CETTE SECTION ===
            colors: {
                'primary': '#4b49ac', // Le bleu/violet de votre sidebar
                'secondary': '#f7f7f7', // Un gris très clair pour les fonds
                'light': '#ffffff', // Le blanc
                'dark': '#333333', // Le texte foncé
            },
            // =============================
        },
    },

    plugins: [forms],
};