<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets;

use DI\Container;
use SirsiDynix\CEPVenuesAssets\Metabox\EquipmentMetaboxProvider;
use SirsiDynix\CEPVenuesAssets\Metabox\RoomMetaboxProvider;
use SirsiDynix\CEPVenuesAssets\Settings\Registration;
use SirsiDynix\CEPVenuesAssets\Wordpress\Constants\MenuPosition;
use SirsiDynix\CEPVenuesAssets\Wordpress\Menu\WPMenuPage;
use SirsiDynix\CEPVenuesAssets\Wordpress\Model\WPPostType;
use SirsiDynix\CEPVenuesAssets\Wordpress\WordpressEvents;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\FormWrapper;
use function DI\autowire;
use function DI\get;

class Plugin
{
    private static $container;

    private static $rootPath;

    public static function initialize($rootPath): bool
    {
        self::$rootPath = $rootPath;
        self::setup();
        return true;
    }

    private static function setup()
    {
        self::$container = new Container();
        self::$container->set('SettingsPage', new WPMenuPage('CEP Venues and Assets', 'CEP Venues and Assets', 'manage_options',
            'cep-venues-assets-settings', function () {
                ob_start();
                settings_fields('section');
                do_settings_sections('cep-venues-assets-settings');
                submit_button();
                $formOutput = ob_get_clean();

                echo new HtmlElement('div', [
                    new HtmlElement('h1', 'CEP Venues and Assets'),
                    new FormWrapper($formOutput, ['method' => 'post', 'action' => 'options.php'])
                ], ['class' => 'wrap']);
            }, null, MenuPosition::BELOW_SETTINGS));
        self::$container->set(Registration::class, autowire()->constructorParameter('menuPage', get('SettingsPage')));

        self::$container->get(ECP\ECPIntegration::class)->registerHandlers();
        self::$container->get(WordpressEvents::class)->registerHandlers();

        $container = self::$container;
        self::$container->get(WordpressEvents::class)->addHandler('init', function () use ($container) {
            $container->get(Wordpress::class)
                ->register_post_type((new WPPostType('room'))
                    ->setMenuIcon('dashicons-store')
                    ->setSupports(['title', 'author', 'thumbnail'])
                    ->setRegisterMetaBoxCb(array($container->get(RoomMetaboxProvider::class), 'metaboxCallback'))
                );
            $container->get(Wordpress::class)
                ->register_post_type((new WPPostType('equipment', 'Equipment', 'Equipment'))
                    ->setMenuIcon('dashicons-screenoptions')
                    ->setSupports(['title', 'author', 'thumbnail'])
                    ->setRegisterMetaBoxCb(array($container->get(EquipmentMetaboxProvider::class), 'metaboxCallback'))
                );
        });

        $wpEvents = self::$container->get(WordpressEvents::class);
        $wpEvents->addHandler('admin_init', function () use ($container) {
            $container->get(Registration::class)->settingsInit();
        });
        $wpEvents->addHandler('admin_menu', function () use ($container) {
            Wordpress::add_menu_page($container->get('SettingsPage'));
        });
        $wpEvents->addHandler('save_post', array(self::$container->get(RoomMetaboxProvider::class), 'savePostCallback'));
        $wpEvents->addHandler('save_post', array(self::$container->get(EquipmentMetaboxProvider::class), 'savePostCallback'));
    }

    public static function destroy($network): bool
    {
        return true;
    }

    public function getRoot(): string
    {
        return self::$rootPath;
    }
}

function Plugin_init()
{
}
