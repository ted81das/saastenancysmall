import basePreset from '../../../../tailwind.config.filament.js'

export default {
    presets: [basePreset],
    content: [
        './app/Filament/Admin/**/*.php',
        './resources/views/filament/admin/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
