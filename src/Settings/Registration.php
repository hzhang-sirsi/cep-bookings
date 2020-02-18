<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Settings;

use SirsiDynix\CEPBookings\ECP\ECPIntegration;
use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\Menu\WPMenuPage;
use SirsiDynix\CEPBookings\Wordpress\Settings\WPSettingsSection;

class Registration
{
    private const OPTIONS_SETTINGS_SECTION = 'cep-bookings-settings';

    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * @var ECPIntegration
     */
    private $ecp;

    /**
     * @var WPMenuPage
     */
    private $menuPage;

    public function __construct(Wordpress $wordpress, ECPIntegration $ecp, $menuPage)
    {
        $this->wordpress = $wordpress;
        $this->ecp = $ecp;
        $this->menuPage = $menuPage;
    }

    public function settingsInit()
    {
        $section = new WPSettingsSection(self::OPTIONS_SETTINGS_SECTION, 'CEP Bookings', function () {
            echo '<p>CEP Bookings</p>';
        }, $this->menuPage);
        $this->wordpress->add_settings_section($section);

        $settings = [];

        array_map(function ($setting) {
            $this->wordpress->add_settings_field($setting);
            $this->wordpress->register_setting($setting);
        }, $settings);
    }
}
