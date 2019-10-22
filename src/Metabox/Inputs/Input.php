<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\MetaboxFieldDefinition;
use Windwalker\Dom\DomElement;
use WP_Post;

abstract class Input
{
    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    abstract public static function getFields(string $field);

    /**
     * @param string $field
     * @return string[] Fieldnames to store as arrays
     */
    abstract public static function getArrayFields(string $field);

    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    abstract public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId);
}
