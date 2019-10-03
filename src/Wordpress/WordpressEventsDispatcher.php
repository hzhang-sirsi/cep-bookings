<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Wordpress;


use RuntimeException;

class WordpressEventsDispatcher
{
    /**
     * @var callable[][]
     */
    private $handlers;

    /**
     * WordpressEventsDispatcher constructor.
     * @param string[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = [];
        foreach ($handlers as $handler) {
            $this->handlers[$handler] = [];
        }
    }

    public function addHandler(string $name, callable $handler)
    {
        if ($this->handlers[$name] === null) {
            throw new RuntimeException("Handler {$name} is not registered");
        }
        array_push($this->handlers[$name], $handler);
    }

    public function dispatch($name, $arguments)
    {
        foreach ($this->handlers[$name] as $handler) {
            $handler(...$arguments);
        }
    }
}