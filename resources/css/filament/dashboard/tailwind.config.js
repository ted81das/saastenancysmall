import basePreset from '../../../../tailwind.config.filament.js'

export default {
    presets: [basePreset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './app/Livewire/**/*.php',
        './resources/views/livewire/**/*.blade.php',
        './resources/views/components/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    input: [
        'resources/css/filament/dashboard/theme.css',
    ],
}
