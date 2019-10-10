<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets;

spl_autoload_register(function ($class) {
    if (substr($class, 0, strlen(__NAMESPACE__)) != __NAMESPACE__) {
        return;
    }
    $classpath = substr(str_replace('\\', DIRECTORY_SEPARATOR, $class), strlen(__NAMESPACE__));
    $path = __DIR__ . '/' . $classpath . '.php';
    if (file_exists($path)) {
        /** @noinspection PhpIncludeInspection */
        require $path;
    }
});
