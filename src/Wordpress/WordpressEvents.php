<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Wordpress;


use SirsiDynix\CEPVenuesAssets\Wordpress;

/**
 * @property Wordpress wordpress
 * @property WordpressEventsDispatcher proxy
 */
class WordpressEvents
{
    private const SUBSCRIBED_EVENTS = [
        'init',
        'admin_init',
        'admin_menu',
        'plugins_loaded',
        'save_post',
    ];

    /**
     * WordpressEvents constructor.
     */
    public function __construct()
    {
        $this->proxy = new WordpressEventsDispatcher(self::SUBSCRIBED_EVENTS);
    }

    public function registerHandlers()
    {
        foreach (self::SUBSCRIBED_EVENTS as $eventName) {
            Wordpress::add_action_fn($eventName, $this->dispatch($eventName));
        }
    }

    private function dispatch($eventName)
    {
        return function (...$params) use ($eventName) {
            $this->proxy->dispatch($eventName, $params);
        };
    }

    public function addHandler(string $eventName, callable $handler)
    {
        $this->proxy->addHandler($eventName, $handler);
    }
}