<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;

use DI\Container;
use SirsiDynix\CEPBookings\Modules\AbstractModule;
use SirsiDynix\CEPBookings\Modules\DatabaseModule;
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

    /**
     * @var AbstractModule[]
     */
    private static $modules = [
        DatabaseModule::class,
        SettingsModule::class,
        PostTypesModule::class,
        MetaboxEditorModule::class,
    ];

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
        self::loadModules($container);
        $container->get(WordpressEvents::class)->registerHandlers();
    }

    public static function getContainer()
    {
        if (!isset(self::$container)) {
            self::$container = new Container();
        }
        return self::$container;
    }

    private static function loadModules(Container $container)
    {
        foreach (self::$modules as $module) {
            $container->get($module)->loadModule();
        }
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
