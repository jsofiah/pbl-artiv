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

            // Warna ARTIV, disamakan dengan halaman Katalog
            colors: {
                primary: {
                    DEFAULT: '#6D28D9',
                    dark: '#5B21B6',
                },
                lime: {
                    DEFAULT: '#D5FC55',
                    hover: '#C5EC45',
                },
                ink: '#0F172A',     // = slate-900 (judul)
                muted: '#64748B',   // = slate-500 (teks sekunder)
                line: '#E2E8F0',    // = slate-200 (border, track progress)

                // PENTING: pakai objek DEFAULT supaya skala bawaan neutral-50..900
                // tetap ada (Katalog masih memakai text-neutral-900).
                neutral: {
                    DEFAULT: '#94A3B8', // = slate-400 (placeholder)
                },
            },
        },
    },

    plugins: [forms],
};