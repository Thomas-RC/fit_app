import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    safelist: [
        // Safelist fit-green colors to ensure they're always generated
        'bg-fit-green-50',
        'bg-fit-green-100',
        'bg-fit-green-200',
        'bg-fit-green-500',
        'bg-fit-green-600',
        'bg-fit-green-700',
        'text-fit-green-50',
        'text-fit-green-100',
        'text-fit-green-200',
        'text-fit-green-500',
        'text-fit-green-600',
        'text-fit-green-700',
        'border-fit-green-50',
        'border-fit-green-100',
        'border-fit-green-200',
        'border-fit-green-500',
        'border-fit-green-600',
        'border-fit-green-700',
        'hover:text-fit-green-700',
        'hover:bg-fit-green-50',
        'bg-fit-gradient',
        'bg-green-stripes',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Segoe UI', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'fit-green': {
                    50: '#f1fdf6',
                    100: '#f4fff6',
                    200: '#6dd5a7',
                    500: '#3cb371',
                    600: '#2e8b57',
                    700: '#1e6f45',
                },
            },
            backgroundImage: {
                'green-stripes': 'repeating-linear-gradient(45deg, rgba(60, 179, 113, 0.05), rgba(60, 179, 113, 0.05) 10px, transparent 10px, transparent 20px)',
                'fit-gradient': 'linear-gradient(135deg, #6dd5a7, #3cb371)',
            },
            maxWidth: {
                'container-wide': '960px',
            },
        },
    },
    plugins: [],
};
