<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\MetaboxFieldDefinition;
use Windwalker\Dom\DomElement;
use WP_Post;

interface Input
{
    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId);

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field);

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getArrayFields(string $field);
}