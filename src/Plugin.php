<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets;

use DI\Container;
use SirsiDynix\CEPVenuesAssets\Metabox\EquipmentMetaboxProvider;
use SirsiDynix\CEPVenuesAssets\Metabox\RoomMetaboxProvider;
use SirsiDynix\CEPVenuesAssets\Wordpress\Model\WPPostType;
use SirsiDynix\CEPVenuesAssets\Wordpress\WordpressEvents;

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
            $container->get(Settings\Registration::class)->settingsInit();
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
