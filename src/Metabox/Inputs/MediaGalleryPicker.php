<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use Windwalker\Html\Option;
use Windwalker\Html\Select\CheckboxList;
use WP_Post;

const CLIENT_SCRIPT = <<<'TAG'
jQuery( document ).ready( function( $ ) {
    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    var set_to_post_id = $( '#input-image-id' ).val(); // Set this
    jQuery('#select-image-btn').on('click', function( event ){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            // Open frame
            file_frame.open();
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = set_to_post_id;
        }
        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            $( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
            $( '#input-image-id' ).val( attachment.id );
            $( '#span-image-width' ).text( attachment.width );
            $( '#span-image-height' ).text( attachment.height );
            // Restore the main post ID
            wp.media.model.settings.post.id = wp_media_post_id;
        });
            // Finally, open the modal
            file_frame.open();
    });
    // Restore the main ID when the add media button is pressed
    jQuery( 'a.add_media' ).on( 'click', function() {
        wp.media.model.settings.post.id = wp_media_post_id;
    });
});
TAG;

/**
 * @property string $type
 */
class MediaGalleryPicker implements Input
{
    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        wp_enqueue_media();

        $imageMeta = wp_get_attachment_metadata($post->{$field->name . '_imageId'});
        if ($imageMeta) {
            $width = $imageMeta['width'];
            $height = $imageMeta['height'];
        }
        else {
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
            new HtmlElement('script', CLIENT_SCRIPT, ['type' => 'text/javascript']),
        ], ['style' => 'display: flex; flex-direction: row;']);
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field)
    {
        return [
            "{$field}_imageId",
        ];
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