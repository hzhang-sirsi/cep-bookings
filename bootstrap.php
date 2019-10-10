<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/autoload.php';

if (is_file(__DIR__ . '/test/autoload.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/test/autoload.php';
}
