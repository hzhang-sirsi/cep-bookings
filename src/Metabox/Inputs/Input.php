<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Wordpress;
use Windwalker\Dom\DomElement;
use WP_Post;

abstract class Input
{
    /**
     * @param WP_Post $post
     * @param string $fieldName
     * @param string $fieldId
     * @return DomElement
     */
    abstract public function render(WP_Post $post, string $fieldName, string $fieldId);

    /**
     * @param Wordpress $wordpress
     * @param WP_Post $post
     * @param string $fieldName
     * @return void
     */
    abstract public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName);
}
