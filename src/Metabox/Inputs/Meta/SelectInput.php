<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs\Meta;


use Windwalker\Dom\DomElement;
use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;
use WP_Post;

/**
 * @property callable $elements
 */
class SelectInput extends PostMetaInput
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
     * @param string  $fieldName
     * @param string  $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, string $fieldName, string $fieldId)
    {
        $root = new SelectList($fieldName, [
            new Option('Select...', '-1')
        ], ['id' => $fieldId, 'class' => 'code regular-text'], $post->{$fieldName}, false);
        $elements = call_user_func_array($this->elements, [$post]);

        foreach ($elements as $value => $label) {
            $root->option($label, $value);
        }

        return $root;
    }
}
