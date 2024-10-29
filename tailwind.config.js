const defaultTheme = require('tailwindcss/defaultTheme')
import common from './tailwind.common.js'

export default {
    plugins: [require("daisyui")],
    daisyui: {
        themes: ["light"],
    },
    content: [
        "./resources/**/*.blade.php",
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/vendor/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        "./resources/**/*.js",
        "./resources/**/*.vue",
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        fontSize: {
            xxs: '0.65rem',
            xs: '0.75rem',
            sm: '0.9rem',
            base: '1rem',
            lg: '1.125rem',
            xl: '1.25rem',
            '2xl': '1.5rem',
            '3xl': '1.75rem',
            '4xl': '2.5rem',
            '5xl': '3rem',
            '6xl': '3.43rem',
            '7xl': '4.5rem',
        },
        extend: {
            fontFamily: {
                'sans': ['"Poppins"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: common.colors.primary,
                secondary: common.colors.secondary,
            },
            lineHeight: {
                'xxs': '1.1',
                'xs': '1.2',
            },
            height: {
                '180': '45rem',
            },
            dropShadow: {
                '3xl': '0 35px 35px rgba(0, 0, 0, 0.25)',
                '4xl': [
                    '0 35px 35px rgba(0, 0, 0, 0.25)',
                    '0 45px 65px rgba(0, 0, 0, 0.15)'
                ]
            },
            blur: {
                '4xl': '75px',
            },
            listStyleImage: {
                checkmark: 'url("/images/check.svg") ',
            },
            scale: {
                '101': '1.01',
                '102': '1.02',
                '103': '1.03',
            }
        },
    }
}
