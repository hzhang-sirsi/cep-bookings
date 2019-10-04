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
use SirsiDynix\CEPVenuesAssets\Wordpress\Menu\WPSubMenuPage;
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

        $container = self::$container;
        self::$container->get(WordpressEvents::class)->addHandler('init', function () use ($container) {
            Wordpress::register_post_type((new WPPostType('room_type', 'Room Type', 'Room Types'))
                ->setSupports(['title'])
                ->setShowInMenu(false)
            );
            Wordpress::register_post_type((new WPPostType('equipment_type', 'Equipment Type', 'Equipment Types'))
                ->setSupports(['title'])
                ->setShowInMenu(false)
            );
            Wordpress::register_post_type((new WPPostType('room'))
                ->setMenuIcon('dashicons-store')
                ->setSupports(['title', 'author', 'thumbnail'])
                ->setRegisterMetaBoxCb(array($container->get(RoomMetaboxProvider::class), 'metaboxCallback'))
            );
            Wordpress::register_post_type((new WPPostType('equipment', 'Equipment', 'Equipment'))
                ->setMenuIcon('dashicons-screenoptions')
                ->setSupports(['title', 'author', 'thumbnail'])
                ->setRegisterMetaBoxCb(array($container->get(EquipmentMetaboxProvider::class), 'metaboxCallback'))
            );
        });

        $wpEvents = self::$container->get(WordpressEvents::class);
        $wpEvents->addHandler('admin_init', function () use ($container) {
            $container->get(Registration::class)->settingsInit();

            Wordpress::add_filter('manage_equipment_posts_columns', function ($columns) {
                return array_merge(['thumbnail' => 'Thumbnail'], $columns);
            }, 5);
            Wordpress::add_action_fn('manage_posts_custom_column', function ($column_name, $id) {
                if ($column_name === 'thumbnail') {
                    echo get_the_post_thumbnail($id, 'thumbnail');
                }
            }, 5, 2);
        });
        $wpEvents->addHandler('admin_menu', function () use ($container) {
            $menuPage = $container->get('SettingsPage');
            Wordpress::add_menu_page($menuPage);

            Wordpress::add_sub_menu_page(new WPSubMenuPage('edit.php?post_type=room', 'Room Types', 'Room Types', 'edit_posts',
                'edit.php?post_type=room_type'));
            Wordpress::add_sub_menu_page(new WPSubMenuPage('edit.php?post_type=equipment', 'Equipment Types', 'Equipment Types', 'edit_posts',
                'edit.php?post_type=equipment_type'));
        });
        $wpEvents->addHandler('save_post', array(self::$container->get(RoomMetaboxProvider::class), 'savePostCallback'));
        $wpEvents->addHandler('save_post', array(self::$container->get(EquipmentMetaboxProvider::class), 'savePostCallback'));

        self::$container->get(WordpressEvents::class)->registerHandlers();
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
