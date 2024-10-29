import preset from './vendor/filament/filament/tailwind.config.preset'
import common from './tailwind.common.js'

module.exports = {
    presets: [preset],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: common.colors.primary["50"],
                    100: common.colors.primary["100"],
                    200: common.colors.primary["200"],
                    300: common.colors.primary["300"],
                    400: common.colors.primary["400"],
                    500: common.colors.primary["500"],
                    600: common.colors.primary["600"],
                    700: common.colors.primary["700"],
                    800: common.colors.primary["800"],
                    900: common.colors.primary["900"],
                    950: common.colors.primary["950"],
                },
                secondary: {
                    50: common.colors.secondary["50"],
                    100: common.colors.secondary["100"],
                    200: common.colors.secondary["200"],
                    300: common.colors.secondary["300"],
                    400: common.colors.secondary["400"],
                    500: common.colors.secondary["500"],
                    600: common.colors.secondary["600"],
                    700: common.colors.secondary["700"],
                    800: common.colors.secondary["800"],
                    900: common.colors.secondary["900"],
                    950: common.colors.secondary["950"],
                },
            }
        },
    },
    extend: {

    },
    plugins: [require("daisyui")],
    daisyui: {
        darkTheme: "dark",
        themes: [
            {
                light: {
                    ...require("daisyui/src/theming/themes")["[data-theme=light]"],
                    "primary": common.colors.primary["500"],
                    "secondary": common.colors.primary["400"],
                    "primary-content": '#ffffff',
                    "neutral": '#c4c4c4',
                    "base-100": "#ffffff",
                }
            },
            {
                dark: {
                    ...require("daisyui/src/theming/themes")["[data-theme=dark]"],
                    primary: common.colors.primary["500"],
                    secondary: common.colors.primary["400"],
                }
            }
        ],
    },
}
