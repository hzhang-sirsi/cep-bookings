<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox;


use Closure;
use SirsiDynix\CEPBookings\Metabox\Inputs\Input;
use SirsiDynix\CEPBookings\Utils;
use SirsiDynix\CEPBookings\Wordpress;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use Windwalker\Html\Grid\Grid;
use WP_Post;

/**
 * Constructs a metabox that takes an array of MetaboxFieldDefinition. These fields will be
 * editable, controlled by the MetaboxFieldDefinition.
 *
 * @property MetaboxFieldDefinition[] fields
 */
class MetadataMetaboxProvider
{
    /**
     * @var Wordpress\WordpressEvents
     */
    private $events;

    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * MetadataMetaboxProvider constructor.
     * @param Wordpress $wordpress
     * @param Wordpress\WordpressEvents $events
     * @param MetaboxFieldDefinition[] $fields
     */
    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $events, array $fields)
    {
        $this->wordpress = $wordpress;
        $this->events = $events;
        $this->fields = $fields;
    }

    /**
     * @param string|WP_Post $postType
     */
    public function registerMetabox($postType)
    {
        if ($postType instanceof WP_Post) {
            $postType = $postType->post_type;
        }
        $this->wordpress->add_meta_box('meta-cep-bookings', 'CEP Bookings', $this->renderMetabox(), $postType);
    }

    /**
     * @return Closure
     */
    private function renderMetabox()
    {
        return function (WP_Post $post) {
            $rootElem = Grid::create([
                'class' => 'form-table'
            ])->setColumns(['key', 'value']);

            foreach ($this->fields as $field) {
                $rootElem->addRow();

                $fieldId = Utils::generateUniqueIdentifier();
                $rootElem->setRowCell('key', new HtmlElement('label', $field->friendlyName, [
                    'for' => $fieldId
                ]));
                $rootElem->setRowCell('value', $this->resolveType($post, $field, $fieldId));
            }

            echo $rootElem;
        };
    }

    private function resolveType(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        if ($field->type != null && $field->type instanceof Input) {
            return $field->type->render($post, $field->name, $fieldId);
        } elseif ($field->type === null || $field->type === 'text') {
            return new InputElement('text', $field->name,
                $post->{$field->name}, ['id' => $fieldId, 'class' => 'code regular-text']);
        } else {
            return new InputElement('text', $field->name,
                $post->{$field->name},
                [
                    'disabled' => 'disabled',
                    'placeholder' => 'Placeholder {' . $field->type . '}',
                    'id' => $fieldId,
                    'class' => 'code regular-text',
                ]
            );
        }
    }

    public function savePostCallback(int $post_id, WP_Post $post, bool $update = null): void
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_type === 'revision' || wp_is_post_autosave($post_id) || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        if ($parent_id = $this->wordpress->wp_is_post_revision($post_id)) {
            $post_id = $parent_id;
            $post = $this->wordpress->get_post($post_id);
        }

        foreach ($this->fields as $fielddef) {
            $fielddef->saveFields($this->wordpress, $post);
        }
    }
}
