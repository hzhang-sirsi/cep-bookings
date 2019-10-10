<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Wordpress;


use Closure;
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
        'wp_insert_post_empty_content',
    ];

    /**
     * WordpressEvents constructor.
     * @param Wordpress $wordpress Wordpress instance
     */
    public function __construct(Wordpress $wordpress)
    {
        $this->wordpress = $wordpress;
        $this->proxy = new WordpressEventsDispatcher(self::SUBSCRIBED_EVENTS);
    }

    public function registerHandlers()
    {
        foreach (self::SUBSCRIBED_EVENTS as $eventName) {
            foreach ($this->proxy->getPriorities($eventName) as $priority) {
                $this->wordpress->add_action($eventName, $this->dispatch($eventName, $priority), $priority);
            }
        }
    }

    /**
     * @param $eventName
     * @param $priority
     * @return Closure
     */
    private function dispatch(string $eventName, $priority)
    {
        return function (...$params) use ($eventName, $priority) {
            $this->proxy->dispatch($eventName, $priority, $params);
        };
    }

    public function addHandler(string $eventName, callable $handler, int $priority = null)
    {
        if ($priority === null) {
            $this->proxy->addHandler($eventName, $handler);
            return;
        }

        $this->proxy->addHandler($eventName, $handler, $priority);
    }
}