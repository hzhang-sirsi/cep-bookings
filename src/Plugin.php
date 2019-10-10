<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;

use DI\Container;
use SirsiDynix\CEPBookings\Modules\MetaboxEditorModule;
use SirsiDynix\CEPBookings\Modules\PostTypesModule;
use SirsiDynix\CEPBookings\Modules\SettingsModule;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;

class Plugin
{
    /**
     * @var Container
     */
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
        $container = self::getContainer();
        $container->get(ECP\ECPIntegration::class)->registerHandlers();
        $container->get(SettingsModule::class)->loadModule();
        $container->get(PostTypesModule::class)->loadModule();
        $container->get(MetaboxEditorModule::class)->loadModule();

        $wpEvents = self::$container->get(WordpressEvents::class);
        $wpEvents->addHandler('admin_enqueue_scripts', function () use ($container) {

        });
        self::$container->get(WordpressEvents::class)->registerHandlers();
    }

    public static function getContainer()
    {
        if (!isset(self::$container)) {
            self::$container = new Container();
        }
        return self::$container;
    }

    public static function destroy($network): bool
    {
        return true;
    }

    public static function getRoot(): string
    {
        return self::$rootPath;
    }
}
