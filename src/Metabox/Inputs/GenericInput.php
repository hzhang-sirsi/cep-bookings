<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use Windwalker\Dom\DomElement;
use Windwalker\Html\Form\InputElement;
use WP_Post;

/**
 * @property string $type
 */
class GenericInput implements Input
{
    /**
     * @var callable
     */
    private $value;

    /**
     * SelectInput constructor.
     * @param string $type
     * @param callable $value
     */
    public function __construct(string $type, callable $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        return new InputElement($this->type, $field->name, call_user_func_array($this->value, [$post, $field]), ['id' => $fieldId, 'class' => 'code regular-text']);
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field)
    {
        return [$field];
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getArrayFields(string $field)
    {
        return [];
    }
}