<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Modules;


use DI\Container;

abstract class Module
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Module constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Implement module loading
     *
     * @return void
     */
    abstract public function loadModule(): void;
}