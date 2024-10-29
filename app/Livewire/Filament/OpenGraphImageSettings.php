<?php

namespace App\Livewire\Filament;

use App\Services\ConfigManager;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use SaaSykit\OpenGraphy\ImageGenerator;

class OpenGraphImageSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    use InteractsWithForms;

    public ?array $data = [];

    public function render()
    {
        return view('livewire.filament.open-graph-image-settings');
    }

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function mount(): void
    {
        $this->form->fill([
            'open_graphy_image_enabled' => $this->configManager->get('open-graphy.enabled', false),
            'open_graphy_logo_enabled' => $this->configManager->get('open-graphy.logo.enabled', false),
            'open_graphy_screenshot_enabled' => $this->configManager->get('open-graphy.screenshot.enabled', false),
            'open_graphy_template' => $this->configManager->get('open-graphy.template'),
            'open-graphy_template_settings_strings_background' => $this->configManager->get('open-graphy.template_settings.strings.background'),
            'open-graphy_template_settings_strings_stroke_color' => $this->configManager->get('open-graphy.template_settings.strings.stroke_color'),
            'open-graphy_template_settings_strings_stroke_width' => $this->configManager->get('open-graphy.template_settings.strings.stroke_width'),
            'open-graphy_template_settings_strings_text_color' => $this->configManager->get('open-graphy.template_settings.strings.text_color'),
            'open-graphy_template_settings_stripes_start_color' => $this->configManager->get('open-graphy.template_settings.stripes.start_color'),
            'open-graphy_template_settings_stripes_end_color' => $this->configManager->get('open-graphy.template_settings.stripes.end_color'),
            'open-graphy_template_settings_stripes_text_color' => $this->configManager->get('open-graphy.template_settings.stripes.text_color'),
            'open-graphy_template_settings_sunny_start_color' => $this->configManager->get('open-graphy.template_settings.sunny.start_color'),
            'open-graphy_template_settings_sunny_end_color' => $this->configManager->get('open-graphy.template_settings.sunny.end_color'),
            'open-graphy_template_settings_sunny_text_color' => $this->configManager->get('open-graphy.template_settings.sunny.text_color'),
            'open-graphy_template_settings_verticals_start_color' => $this->configManager->get('open-graphy.template_settings.verticals.start_color'),
            'open-graphy_template_settings_verticals_mid_color' => $this->configManager->get('open-graphy.template_settings.verticals.mid_color'),
            'open-graphy_template_settings_verticals_end_color' => $this->configManager->get('open-graphy.template_settings.verticals.end_color'),
            'open-graphy_template_settings_verticals_text_color' => $this->configManager->get('open-graphy.template_settings.verticals.text_color'),
            'open-graphy_template_settings_nodes_background' => $this->configManager->get('open-graphy.template_settings.nodes.background'),
            'open-graphy_template_settings_nodes_node_color' => $this->configManager->get('open-graphy.template_settings.nodes.node_color'),
            'open-graphy_template_settings_nodes_edge_color' => $this->configManager->get('open-graphy.template_settings.nodes.edge_color'),
            'open-graphy_template_settings_nodes_text_color' => $this->configManager->get('open-graphy.template_settings.nodes.text_color'),

            'open_graphy_logo_path' => $this->configManager->get('open-graphy.logo.location', config('app.logo.light')),
            'open_graphy_preview_title' => 'Today is the most awesome day!',
            'open_graphy_preview_url' => 'https://filamentphp.com',
            'open_graphy_preview_image' => 'https://unsplash.com/photos/ndN00KmbJ1c/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8N3x8bmF0dXJlfGVufDB8fHx8MTcxNjIzNTg4Nnww&force=true&w=640',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    Section::make(__('Open Graph Images'))
                        ->schema([
                            Toggle::make('open_graphy_image_enabled')
                                ->label(__('Open Graph Image Generation Enabled'))
                                ->helperText(__('If enabled, an open graph image will be generated for each page that has a title. Make sure that you already set the chrome binary in the config file.'))
                                ->required(),
                            Toggle::make('open_graphy_logo_enabled')
                                ->label(__('Add Logo'))
                                ->live()
                                ->helperText(__('If enabled, the logo will be added to the open graph image.'))
                                ->required(),
                            Radio::make('open_graphy_logo_path')
                                ->label(__('Logo Style'))
                                ->helperText(__('Choose the style of the logo to use in the open graph image.'))
                                ->options([
                                    config('app.logo.light') => 'Light',
                                    config('app.logo.dark') => 'Dark',
                                ])
                                ->disabled(function ($get) {
                                    return !$get('open_graphy_logo_enabled');
                                })
                                ->required(function ($get) {
                                    return $get('open_graphy_logo_enabled');
                                }),
                            Toggle::make('open_graphy_screenshot_enabled')
                                ->label(__('Add Page Screenshot'))
                                ->helperText(__('If enabled, a screenshot of the page will be added to the open graph image.'))
                                ->required(),
                            Select::make('open_graphy_template')
                                ->label(__('Template'))
                                ->live()
                                ->helperText(__('Select the template to use for the open graph image. Changing the template or any of the settings will regenerate all open graph images for your pages, so choose your settings wisely.'))
                                ->options(function () {
                                    $templates = config('open-graphy.template_settings');

                                    return array_combine(array_keys($templates), array_keys($templates));
                                })
                                ->required(),
                            Grid::make('template_settings_container')
                                ->columns(2)
                                ->schema([
                                    $this->buildTemplateSettingsFields(),
                                    Fieldset::make('preview')
                                        ->label(__('Preview'))
                                        ->columnSpan(1)
                                        ->schema([
                                            TextInput::make('open_graphy_preview_title')
                                                ->columnSpanFull()
                                                ->helperText(__('Enter a title to use to preview the open graph image.'))
                                                ->label(__('Preview Title')),
                                            TextInput::make('open_graphy_preview_image')
                                                ->columnSpanFull()
                                                ->helperText(__('Enter an image URL to use to preview the open graph image. (either image or snapshot will be used, not both, snapshot takes precedence). '))
                                                ->label(__('Preview Image')),
                                            TextInput::make('open_graphy_preview_url')
                                                ->helperText(__('Enter a URL of a site to take a snapshot of to preview the open graph image. (either image or snapshot will be used, not both, snapshot takes precedence). Make sure above "Add Page Screenshot" is enabled.'))
                                                ->columnSpanFull()
                                                ->label(__('Preview Snapshot URL')),
                                            Actions::make([
                                                Action::make('preview')
                                                    ->label(__('Generate Preview'))
                                                    ->icon('heroicon-o-eye')
                                                    ->color('gray')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelAction(false)
                                                    ->modalContent(function ($get, ImageGenerator $imageGenerator) {
                                                        try {
                                                            $templateSettings = config('open-graphy.template_settings');

                                                            $currentTemplate = $get('open_graphy_template'); // this is to get the current template from form state

                                                            $currentTemplateSettings = $templateSettings[$currentTemplate];

                                                            $keys = array_keys($currentTemplateSettings);

                                                            $settings = [];

                                                            foreach ($keys as $key) {
                                                                $settings[$key] = $get('open-graphy_template_settings_' . $currentTemplate . '_' . $key);
                                                            }

                                                            $imageType = config('open-graphy.open_graph_image.type');

                                                            $imagePath = $imageGenerator->generate(
                                                                $get('open_graphy_preview_title'),
                                                                $get('open_graphy_preview_url'),
                                                                $get('open_graphy_logo_enabled'),
                                                                $get('open_graphy_screenshot_enabled'),
                                                                $get('open_graphy_preview_image'),
                                                                $currentTemplate,
                                                                $settings,
                                                                $get('open_graphy_logo_path'),
                                                                true
                                                            );

                                                            $encodedImage = $imageGenerator->base64FromPath($imagePath);

                                                            return new HtmlString("<img src=\"data:image/$imageType;base64,$encodedImage\" alt=\"Open Graph Image Preview\" class=\"w-full h-auto\" />");
                                                        } catch (\Throwable $e) {
                                                            return new HtmlString('<p class="text-red-500">' . __('Cannot render image, make sure the chrome binary is set correctly in the config file "open-graphy.php".') . '</p>');
                                                        }
                                                    })

                                            ]),
                                        ])
                                ]),
                        ]),

            ])
            ->statePath('data');
    }

    private function buildTemplateSettingsFields()
    {
        return Fieldset::make('template_settings')
            ->label(__('Template Settings'))
            ->columnSpan(1)
            ->schema(function ($get) {

                $template = $get('open_graphy_template');

                $templates = config('open-graphy.template_settings');

                $settings = $templates[$template];

                $schema = [];

                foreach ($settings as $key => $value) {
                    // if key ends with _color or background, show color picker
                    if (str_ends_with($key, '_color') || $key === 'background') {
                        $schema[] = ColorPicker::make('open-graphy_template_settings_' . $template . '_' . $key)
                            ->label(ucfirst(str_replace('_', ' ', $key)))
                            ->required();
                    } else {
                        $schema[] = TextInput::make('open-graphy_template_settings_' . $template . '_' . $key)
                            ->label(ucfirst(str_replace('_', ' ', $key)))
                            ->required();
                    }
                }

                return $schema;
            });
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('open-graphy.enabled', $data['open_graphy_image_enabled']);
        $this->configManager->set('open-graphy.screenshot.enabled', $data['open_graphy_screenshot_enabled']);
        $this->configManager->set('open-graphy.template', $data['open_graphy_template']);
        $this->saveKeyIfExists('open-graphy.template_settings.strings.background', 'open-graphy_template_settings_strings_background', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.strings.stroke_color', 'open-graphy_template_settings_strings_stroke_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.strings.stroke_width', 'open-graphy_template_settings_strings_stroke_width', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.strings.text_color', 'open-graphy_template_settings_strings_text_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.stripes.start_color', 'open-graphy_template_settings_stripes_start_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.stripes.end_color', 'open-graphy_template_settings_stripes_end_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.stripes.text_color', 'open-graphy_template_settings_stripes_text_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.sunny.start_color', 'open-graphy_template_settings_sunny_start_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.sunny.end_color', 'open-graphy_template_settings_sunny_end_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.sunny.text_color', 'open-graphy_template_settings_sunny_text_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.verticals.start_color', 'open-graphy_template_settings_verticals_start_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.verticals.mid_color', 'open-graphy_template_settings_verticals_mid_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.verticals.end_color', 'open-graphy_template_settings_verticals_end_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.verticals.text_color', 'open-graphy_template_settings_verticals_text_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.nodes.background', 'open-graphy_template_settings_nodes_background', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.nodes.node_color', 'open-graphy_template_settings_nodes_node_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.nodes.edge_color', 'open-graphy_template_settings_nodes_edge_color', $data);
        $this->saveKeyIfExists('open-graphy.template_settings.nodes.text_color', 'open-graphy_template_settings_nodes_text_color', $data);

        $this->configManager->set('open-graphy.logo.enabled', $data['open_graphy_logo_enabled']);
        $this->saveKeyIfExists('open-graphy.logo.location', 'open_graphy_logo_path', $data);


        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }

    private function saveKeyIfExists(string $configName, string $fieldName, array $data): void
    {
        if (array_key_exists($fieldName, $data)) {
            $this->configManager->set($configName, $data[$fieldName]);
        }
    }
}
