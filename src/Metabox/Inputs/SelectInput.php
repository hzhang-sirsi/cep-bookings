<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;
use WP_Post;

/**
 * @property callable elements
 */
class SelectInput implements Input
{
    /**
     * SelectInput constructor.
     * @param callable $elements
     */
    public function __construct(callable $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        $root = new SelectList($field->name, [
            new Option('Select...', '')
        ], ['id' => $fieldId, 'class' => 'code regular-text'], Wordpress::get_post_meta($post->ID, $field->name, true), false);
        $elements = call_user_func_array($this->elements, [$post]);

        foreach ($elements as $value => $label) {
            $root->option($label, $value);
        }

        return $root;
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field)
    {
        return [$field];
    }
}