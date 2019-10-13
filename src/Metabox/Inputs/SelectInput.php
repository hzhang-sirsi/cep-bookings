<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\MetaboxFieldDefinition;
use Windwalker\Dom\DomElement;
use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;
use WP_Post;

/**
 * @property callable elements
 */
class SelectInput extends Input
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
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public static function getFields(string $field)
    {
        return [$field];
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public static function getArrayFields(string $field)
    {
        return [];
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
        ], ['id' => $fieldId, 'class' => 'code regular-text'], $post->{$field->name}, false);
        $elements = call_user_func_array($this->elements, [$post]);

        foreach ($elements as $value => $label) {
            $root->option($label, $value);
        }

        return $root;
    }
}
