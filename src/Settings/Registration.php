<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Settings;

use SirsiDynix\CEPVenuesAssets\ECP\ECPIntegration;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use SirsiDynix\CEPVenuesAssets\Wordpress\Settings\WPSettingsPage;
use SirsiDynix\CEPVenuesAssets\Wordpress\Settings\WPSettingsSection;
use Windwalker\Html\Form\InputElement;

class Registration
{
    private const OPTION_DEBUG = 'cep_marketo_debug';

    private const OPTION_ECP_SYNC_FIELD = 'cep_marketo_sync_option';
    private const OPTION_ECP_ROOM_FIELD = 'cep_marketo_room_option';

    private const OPTIONS_SETTINGS_PAGE = 'writing';
    private const OPTIONS_SETTINGS_SECTION = 'cep_marketo_settings';
    private const OPTIONS_MARKETO_API_KEY = 'marketo_api_key';
    private const OPTIONS_MARKETO_API_SECRET = 'marketo_api_secret';
    private const OPTIONS_ALTERNATE_DOMAIN = 'cep_alternate_domain';
    private const OPTIONS_EMAIL_FROM_ADDRESS = 'cep_email_from_address';
    private const OPTIONS_EMAIL_FROM_NAME = 'cep_email_from_name';

    private const OPTIONS_REMINDER_1_DELAY = 'cep_reminder_1_delay';
    private const OPTIONS_REMINDER_2_DELAY = 'cep_reminder_2_delay';

    private $ecp;
    private $menuPage;

    public function __construct(ECPIntegration $ecp, $menuPage)
    {
        $this->ecp = $ecp;
        $this->menuPage = $menuPage;
    }

    public function getDebug(): bool
    {
        return Wordpress::get_option(self::OPTION_DEBUG) === "Enabled";
    }

    public function getECPRoomField(): string
    {
        return (string)Wordpress::get_option(self::OPTION_ECP_ROOM_FIELD);
    }

    public function settingsInit()
    {
        $section = new WPSettingsSection(self::OPTIONS_SETTINGS_SECTION, 'CEP Marketo Integration', function () {
            echo '<p>CEP Marketo Configuration Settings</p>';
        }, $this->menuPage);
        Wordpress::add_settings_section($section);

        $settings = [
            $section->createSetting(self::OPTION_DEBUG, 'Debug Enabled',
                function () {
                    $this->generateDropdown(self::OPTION_DEBUG,
                        [["name" => "Enabled", "label" => "Enabled"]],
                        "Disabled", "Disabled");
                }),
            $section->createSetting(self::OPTION_DEBUG, 'Debug Enabled',
                function () {
                    $this->generateDropdown(self::OPTION_DEBUG,
                        [["name" => "Enabled", "label" => "Enabled"]],
                        "Disabled", "Disabled");
                }),
            $this->createTextSetting($section, self::OPTIONS_MARKETO_API_KEY, 'Marketo API Key'),
            $this->createTextSetting($section, self::OPTIONS_MARKETO_API_SECRET, 'Marketo API Secret'),

            $section->createSetting(self::OPTION_ECP_SYNC_FIELD, 'ECP Sync Field',
                function () {
                    $this->generateDropdown(self::OPTION_ECP_SYNC_FIELD, $this->ecp->getOptions(), 'Disabled', ECPIntegration::DISABLED);
                }),
            $section->createSetting(self::OPTION_ECP_ROOM_FIELD, 'ECP Room Field',
                function () {
                    $this->generateDropdown(self::OPTION_ECP_ROOM_FIELD, $this->ecp->getOptions(), 'Disabled', ECPIntegration::DISABLED);
                }),

            $this->createTextSetting($section, self::OPTIONS_ALTERNATE_DOMAIN, 'Alternate Domain Name'),

            $this->createTextSetting($section, self::OPTIONS_EMAIL_FROM_ADDRESS, 'Email from Address'),
            $this->createTextSetting($section, self::OPTIONS_EMAIL_FROM_NAME, 'Email from Name'),

            $this->createTextSetting($section, self::OPTIONS_REMINDER_1_DELAY, 'Reminder 1 Delay (hours)'),
            $this->createTextSetting($section, self::OPTIONS_REMINDER_2_DELAY, 'Reminder 2 Delay (hours)'),
        ];

        array_map(function ($setting) {
            Wordpress::add_settings_field($setting);
            Wordpress::register_setting($setting);
        }, $settings);
    }

    private function generateDropdown(string $option_name, array $options, string $default_name, string $default_value): void
    {
        $selected = Wordpress::get_option($option_name);

        echo "<select name=\"{$option_name}\">";

        foreach ($options as $option) {
            $selectedTag = $option['name'] === $selected ? 'selected' : '';
            echo "<option value=\"{$option['name']}\" {$selectedTag}>{$option['label']}</option>";
        }

        $selectedTag = ($default_value === $selected ? 'selected' : '');
        echo "<option value=\"{$default_value}\" {$selectedTag}>{$default_name}</option>";

        echo "</select>";
    }

    private function createTextSetting(WPSettingsSection $section, string $name, string $title)
    {
        return $section->createSetting($name, $title,
            function () use ($name) {
                $currentValue = Wordpress::get_option($name);
                echo new InputElement('text', $name, $currentValue, ['class' => 'regular-text code']);
            }
        );
    }
}
