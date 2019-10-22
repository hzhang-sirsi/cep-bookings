<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Rest\Script;


use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\Ajax\AjaxHandler;

class ClientScriptHelper
{
    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * @var string
     */
    private $scriptName;

    /**
     * @var string
     */
    private $scriptUrl;

    /**
     * @var string
     */
    private $objectName;

    /**
     * @var AjaxHandler[]
     */
    private $handlers;

    /**
     * @var array
     */
    private $dependencies;

    /**
     * ClientScriptHelper constructor.
     * @param Wordpress $wordpress
     * @param string $scriptName
     * @param string $scriptUrl
     * @param array $dependencies
     * @param string $objectName
     * @param AjaxHandler[] $handlers
     */
    public function __construct(Wordpress $wordpress, string $scriptName, string $scriptUrl, array $dependencies, string $objectName, $handlers)
    {
        $this->wordpress = $wordpress;
        $this->scriptName = $scriptName;
        $this->scriptUrl = $scriptUrl;
        $this->dependencies = $dependencies;
        $this->objectName = $objectName;
        $this->handlers = $handlers;
    }

    /**
     * @param array $data
     * @return void
     */
    public function enqueue(array $data = [])
    {
        $this->wordpress->wp_enqueue_script($this->scriptName, $this->wordpress->plugins_url($this->scriptUrl),
            array_merge($this->dependencies, ['jquery']));
        $this->wordpress->wp_localize_script(
            $this->scriptName,
            $this->objectName,
            array_merge([
                '_ajax' => [
                    'url' => $this->wordpress->admin_url('admin-ajax.php'),
                    'nonce' => $this->createNonces(),
                ],
            ], $data)
        );
    }

    private function createNonces()
    {
        $nonces = [];
        foreach ($this->handlers as $handler) {
            $nonces[$handler->getEventName()] = $this->wordpress->wp_create_nonce($handler->getEventName());
        }
        return $nonces;
    }
}
