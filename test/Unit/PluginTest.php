<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;

final class PluginTest extends BaseTestCase
{
    public function testPluginInitialize(): void
    {
        $this->assertTrue(Plugin::initialize(GetBasePath() . '/cep-bookings.php'));
    }

    public function testPluginDestroy(): void
    {
        $this->assertTrue(Plugin::destroy(false));
    }
}
