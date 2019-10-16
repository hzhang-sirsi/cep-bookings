<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Wordpress;


use RuntimeException;

class WordpressEventsDispatcher
{
    /**
     * @var callable[int][string]
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

    public function addHandler(string $name, callable $handler, int $priority = 10)
    {
        if (!array_key_exists($name, $this->handlers) || $this->handlers[$name] === null) {
            throw new RuntimeException("Handler {$name} is not registered");
        }

        if (!array_key_exists($priority, $this->handlers[$name])) {
            $this->handlers[$name][$priority] = [];
        }
        array_push($this->handlers[$name][$priority], $handler);
    }

    public function dispatch(string $name, int $priority, array $arguments)
    {
        foreach ($this->handlers[$name][$priority] as $handler) {
            $handler(...$arguments);
        }
    }

    public function getPriorities(string $name)
    {
        return array_keys($this->handlers[$name]);
    }
}