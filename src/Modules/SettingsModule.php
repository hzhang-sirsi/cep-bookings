<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use Exception;
use SirsiDynix\CEPBookings\Settings\Registration;
use SirsiDynix\CEPBookings\Utils;
use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\Constants\MenuPosition;
use SirsiDynix\CEPBookings\Wordpress\Menu\WPMenuPage;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\FormWrapper;
use function DI\autowire;
use function DI\get;

class SettingsModule extends Module
{
    /**
     * Implement module loading
     *
     * @return void
     * @throws Exception
     */
    public function loadModule(): void
    {
        $this->container->set('SettingsPage', new WPMenuPage('CEP Bookings', 'CEP Bookings', 'manage_options',
            'cep-bookings-settings', function () {
                $formOutput = Utils::captureAsString(function () {
                    settings_fields('section');
                    do_settings_sections('cep-bookings-settings');
                    submit_button();
                });

                echo new HtmlElement('div', [
                    new HtmlElement('h1', 'CEP Bookings'),
                    new FormWrapper($formOutput, ['method' => 'post', 'action' => 'options.php'])
                ], ['class' => 'wrap']);
            }, null, MenuPosition::BELOW_SETTINGS));
        $this->container->set(Registration::class, autowire()->constructorParameter('menuPage', get('SettingsPage')));

        $wpEvents = $this->container->get(WordpressEvents::class);
        $registration = $this->container->get(Registration::class);

        $wpEvents->addHandler('admin_init', function () use ($registration) {
            $registration->settingsInit();
        });
        $wpEvents->addHandler('admin_menu', function () {
            $menuPage = $this->container->get('SettingsPage');
            $this->container->get(Wordpress::class)->add_menu_page($menuPage);
        });
    }
}