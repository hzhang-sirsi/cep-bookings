<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Rest\Script;


use Closure;
use SirsiDynix\CEPBookings\Plugin;
use SirsiDynix\CEPBookings\Wordpress;

class ClientScriptHelper
{
    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * ClientScriptHelper constructor.
     * @param Wordpress $wordpress
     */
    public function __construct(Wordpress $wordpress)
    {
        $this->wordpress = $wordpress;
        $screen = get_current_screen();
        if (is_object($screen)) {
            if (in_array($screen->base, ['post']) && in_array($screen->post_type, [$this->ecp->getEventsPostType()])) {
                global $post;
                $metadata = $this->wordpress->get_post_meta($post->ID, self::CEP_MARKETO_METADATA_FIELD, true);
                $programId = intval($metadata);

                wp_enqueue_script('editEvent', plugins_url('/static/js/hooks.js', $this->plugin->getRoot()), ['jquery']);
                wp_localize_script(
                    'editEvent',
                    'ajaxParams',
                    [
                        'url' => admin_url('admin-ajax.php'),
                        'postId' => $post->ID,
                        'program' => $programId,
                        'nonce' => wp_create_nonce('editEvent'),
                    ]
                );
            }
        };
    }

    /**
     * @param string $postType
     * @return Closure
     */
    public function handler(string $postType)
    {
        return function () use ($postType) {
            $screen = $this->wordpress->get_current_screen();
            if (is_object($screen)) {
                if (in_array($screen->base, ['post']) && in_array($screen->post_type, [$postType])) {
                    $post = $this->wordpress->get_post();

                    wp_enqueue_script('editEvent', plugins_url('/static/js/hooks.js', Plugin::getRoot()), ['jquery']);
                    wp_localize_script(
                        'editEvent',
                        'ajaxParams',
                        [
                            'url' => admin_url('admin-ajax.php'),
                            'postId' => $post->ID,
                            'program' => $programId,
                            'nonce' => wp_create_nonce('editEvent'),
                        ]
                    );
                }
            }
        };
    }
}