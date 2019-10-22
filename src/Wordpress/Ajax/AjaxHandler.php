<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Wordpress\Ajax;


use Closure;
use Exception;
use SirsiDynix\CEPBookings\Wordpress;

abstract class AjaxHandler
{
    /**
     * @var Wordpress
     */
    protected $wordpress;

    /**
     * AjaxHandler constructor.
     * @param Wordpress $wordpress
     */
    public function __construct(Wordpress $wordpress)
    {
        $this->wordpress = $wordpress;
    }

    public function register()
    {
        $eventName = $this->getEventName();
        $this->wordpress->add_action("wp_ajax_{$eventName}", Closure::fromCallable(array($this, 'handleRequest')));
    }

    abstract public function getEventName(): string;

    private function handleRequest()
    {
        try {
            check_ajax_referer($this->getEventName());
            $response = null;
            try {
                $response = $this->handler($_POST);
            } catch (Exception $e) {
                wp_send_json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }

            wp_send_json([
                'success' => true,
                'data' => $response,
            ], 200);
        } finally {
            wp_die();
        }
    }

    abstract public function handler(array $postData);
}
