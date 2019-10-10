<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets;


use Closure;

class Utils
{
    public static function captureAsString(callable $callable)
    {
        ob_start();
        Closure::fromCallable($callable)();
        return ob_get_clean();
    }
}