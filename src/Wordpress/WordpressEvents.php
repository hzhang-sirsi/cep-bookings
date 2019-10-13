<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Wordpress;


use Closure;
use SirsiDynix\CEPBookings\Wordpress;

/**
 * @property Wordpress wordpress
 * @property WordpressEventsDispatcher proxy
 */
class WordpressEvents
{
    /**
     * List of valid events. This protects against bugs caused by typos
     */
    private const SUBSCRIBED_EVENTS = [
        'init',
        'admin_enqueue_scripts',
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
        $this->proxy->freeze();
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
