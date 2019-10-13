<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\MetaboxFieldDefinition;
use SirsiDynix\CEPBookings\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use WP_Post;


/**
 * @property string $type
 */
class MediaGalleryPicker extends Input
{
    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * MediaGalleryPicker constructor.
     * @param Wordpress $wordpress
     */
    public function __construct(Wordpress $wordpress)
    {
        $this->wordpress = $wordpress;
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public static function getFields(string $field)
    {
        return [
            "{$field}_imageId",
        ];
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
        $this->wordpress->wp_enqueue_media();
        $this->wordpress->wp_enqueue_script('media-gallery-picker', $this->wordpress->plugins_url('/static/js/media-gallery-picker.js'));

        $imageMeta = $this->wordpress->wp_get_attachment_metadata($post->{$field->name . '_imageId'});
        if ($imageMeta) {
            $width = $imageMeta['width'];
            $height = $imageMeta['height'];
        } else {
            $width = '';
            $height = '';
        }

        return new HtmlElement('div', [
            new HtmlElement('div', [
                new HtmlElement('img', [], [
                        'id' => 'image-preview',
                        'src' => wp_get_attachment_image_src($post->{$field->name . '_imageId'}, 'thumbnail')[0],
                        'style' => 'width: auto; max-width: 100%; height: 100%; max-height: 100%; object-fit: contain;']
                ),
            ], ['style' => 'flex-grow: 1; max-width: 200px; max-height: 150px;']),
            new HtmlElement('div', [
                new HtmlElement('label', [
                    'Dimensions:&nbsp;',
                    new HtmlElement('span', $width, ['id' => 'span-image-width']),
                    'px x&nbsp;',
                    new HtmlElement('span', $height, ['id' => 'span-image-height']),
                    'px',
                ]),
                new InputElement('button', $field->name . '_selectImage', 'Select Image', ['id' => 'select-image-btn', 'class' => 'button button-primary button-small']),
            ], ['style' => 'display: flex; flex-direction: column; justify-content: space-between; margin: 5px;']),
            new InputElement('hidden', $field->name . '_imageId', $post->{$field->name . '_imageId'}, ['id' => 'input-image-id']),
        ], ['style' => 'display: flex; flex-direction: row;']);
    }
}
