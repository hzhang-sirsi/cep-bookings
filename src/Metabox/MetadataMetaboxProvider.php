<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\Input;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use Windwalker\Html\Grid\Grid;
use WP_Post;

/**
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

    public function metaboxCallback(WP_Post $post)
    {
        $rootElem = Grid::create([
            'class' => 'form-table'
        ])->setColumns(['key', 'value']);

        foreach ($this->fields as $field) {
            $rootElem->addRow();

            $fieldId = 'input-cep-venues-assets-' . $field->name;
            $rootElem->setRowCell('key', new HtmlElement('label', $field->friendlyName, [
                'for' => $fieldId
            ]));
            $rootElem->setRowCell('value', $this->resolveType($post, $field, $fieldId));
        }

        $this->wordpress->add_meta_box('meta-cep-venues-assets', 'CEP Venues and Assets', function () use ($rootElem) {
            echo $rootElem;
        }, $post->post_type);
    }

    private function resolveType(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        if ($field->type != null && $field->type instanceof Input) {
            return $field->type->render($post, $field, $fieldId);
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

    public function savePostCallback(int $post_id, WP_Post $post, bool $update = null)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_type === 'revision' || wp_is_post_autosave($post_id) || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        if ($parent_id = $this->wordpress->wp_is_post_revision($post_id)) {
            $post_id = $parent_id;
        }

        $processField = function (WP_Post $post, string $field, $value) {
            if (is_array($value)) {
                $value = join(' ', array_map('sanitize_text_field', $value));
            } else {
                $value = sanitize_text_field($value);
            }
            $this->wordpress->update_post_meta($post->ID, $field, $value);
        };

        foreach ($this->fields as $fielddef) {
            foreach ($fielddef->getFields() as $field) {
                if (array_key_exists($field, $_POST)) {
                    $processField($post, $field, $_POST[$field]);
                }
            }

            foreach ($fielddef->getArrayFields() as $field) {
                if (array_key_exists($field, $_POST)) {
                    $processField($post, $field, $_POST[$field]);
                } elseif (isset($post->$field)) {
                    $this->wordpress->update_post_meta($post_id, $field, '');
                }
            }
        }
    }
}