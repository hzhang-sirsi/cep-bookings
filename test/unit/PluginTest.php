<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SirsiDynix\CEPVenuesAssets\Plugin;

final class PluginTest extends TestCase
{
    public function testPluginInitialize(): void
    {
        $this->assertTrue(Plugin::initialize());
    }

    public function testPluginDestroy(): void
    {
        $this->assertTrue(Plugin::destroy(false));
    }
}
