<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets;


use DI\Container;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Plugin::getContainer();
        $this->container->set(Wordpress::class, $this->createMock(Wordpress::class));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}