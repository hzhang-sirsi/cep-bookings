<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;


use DI\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SirsiDynix\CEPBookings\Modules\DatabaseModule;

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
        $this->container->set(DatabaseModule::class, $this->createMock(DatabaseModule::class));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
