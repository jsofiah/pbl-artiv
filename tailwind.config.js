import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Fan-out card (folder)
        '-translate-x-[60%]',
        '-translate-y-[40%]',
        '-translate-y-[70%]',
        '-translate-y-[8%]',
        '-translate-y-[3%]',
        'translate-x-[60%]',
        'translate-x-[3%]',
        '-translate-x-[3%]',
        '-rotate-10',
        'rotate-12',
        '-rotate-2',
        'rotate-2',
        'translate-x-0',
        'translate-y-0',
        'rotate-0',
        'opacity-0',
        'opacity-100',
        'skew-x-12',
        '-skew-x-12',
        'scale-y-[0.6]',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                artiv: {
                    bg:     '#F3F1FA',
                    purple: '#6F35D9',
                    deep:   '#4B1FA8',
                    lime:   '#C9FF3D',
                    navy:   '#15172A',
                },
                primary: {
                    DEFAULT: '#6D28D9',
                    dark:    '#5B21B6',
                },
                lime: {
                    DEFAULT: '#D5FC55',
                    hover:   '#C5EC45',
                },
                ink:    '#0F172A',
                muted:  '#64748B',
                line:   '#E2E8F0',
            },
        },
    },

    plugins: [forms],
};