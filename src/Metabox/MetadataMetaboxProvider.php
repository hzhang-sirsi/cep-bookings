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
 * @property Wordpress wordpress
 * @property MetaboxFieldDefinition[] fields
 */
class MetadataMetaboxProvider
{
    /**
     * @var Wordpress\WordpressEvents
     */
    private $events;

    /**
     * MetadataMetaboxProvider constructor.
     * @param Wordpress\WordpressEvents $events
     * @param MetaboxFieldDefinition[] $fields
     */
    public function __construct(Wordpress\WordpressEvents $events, $fields)
    {
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

        Wordpress::add_meta_box('meta-cep-venues-assets', 'CEP Venues and Assets', function ($post) use ($rootElem) {
            echo $rootElem;
        }, $post->post_type);
    }

    public function savePostCallback(int $post_id, WP_Post $post, bool $update = null) {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_type === 'revision') {
            return;
        }
        if ($parent_id = Wordpress::wp_is_post_revision($post_id)) {
            $post_id = $parent_id;
        }

        foreach ($this->fields as $field) {
            if (array_key_exists($field->name, $_POST)) {
                Wordpress::update_post_meta($post_id, $field->name, sanitize_text_field($_POST[$field->name]));
            }
        }
    }

    private function resolveType(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        if ($field->type != null && $field->type instanceof Input) {
            return $field->type->render($post, $field, $fieldId);
        } elseif ($field->type === null || $field->type === 'text') {
            return new InputElement('text', $field->name,
                Wordpress::get_post_meta($post->ID, $field->name, true), ['id' => $fieldId, 'class' => 'code regular-text']);
        } else {
            return new InputElement('text', $field->name,
                Wordpress::get_post_meta($post->ID, $field->name, true),
                [
                    'disabled' => 'disabled',
                    'placeholder' => 'Placeholder {' . $field->type . '}',
                    'id' => $fieldId,
                    'class' => 'code regular-text',
                ]
            );
        }
    }
}