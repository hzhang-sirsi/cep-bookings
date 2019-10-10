<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings;


use Closure;
use Exception;

class Utils
{
    public static function captureAsString(callable $callable)
    {
        ob_start();
        Closure::fromCallable($callable)();
        return ob_get_clean();
    }

    public static function generateUniqueIdentifier(string $prefix)
    {
        $id = self::randomHexString(32);
        return "{$prefix}_{$id}";
    }

    private static function randomHexString(int $bytes)
    {
        if (function_exists('random_bytes')) {
            try {
                return bin2hex(random_bytes($bytes));
            } catch (Exception $e) {
                // Continue
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($bytes));
        }

        return null;
    }
}