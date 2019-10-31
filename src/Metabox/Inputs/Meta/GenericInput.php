<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs\Meta;


use Windwalker\Dom\DomElement;
use Windwalker\Html\Form\InputElement;
use WP_Post;

/**
 * @property string $type
 * @property array  $attribs
 */
class GenericInput extends PostMetaInput
{
    /**
     * @var callable
     */
    private $value;

    /**
     * SelectInput constructor.
     * @param string   $type
     * @param callable $value
     * @param array    $attribs
     */
    public function __construct(string $type, callable $value, array $attribs = [])
    {
        $this->type = $type;
        $this->value = $value;
        $this->attribs = $attribs;
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
        return new InputElement($this->type, $fieldName, call_user_func_array($this->value, [$post, $fieldName]), array_merge($this->attribs, ['id' => $fieldId, 'class' => 'code regular-text']));
    }
}
