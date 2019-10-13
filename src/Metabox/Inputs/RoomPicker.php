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
class RoomPicker extends Input
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
        return [];
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
        $this->wordpress->wp_enqueue_style('jquery-modal-css');
        $this->wordpress->wp_enqueue_script('jquery-modal-js');
        $this->wordpress->wp_enqueue_style('jquery-timepicker-css');
        $this->wordpress->wp_enqueue_script('jquery-timepicker-js');
        $this->wordpress->wp_enqueue_style('room-picker-css', $this->wordpress->plugins_url('/static/css/room-picker.css'));

        return new HtmlElement('div', [
            new HtmlElement('div', [
                new HtmlElement('div', [
                    new HtmlElement('h1', ['Rooms']),
                    new HtmlElement('div', [
                        new HtmlElement('label', ['Room Type']),
                        (new WPPostSelectInput($this->wordpress, 'room_type'))->render($post, $field, $fieldId),
                    ], ['style' => 'display: flex;']),
                    new HtmlElement('div', [
                        new HtmlElement('div', [
                            new HtmlElement('div', [
                                new HtmlElement('label', ['Start Time'], ['style' => 'width: 100px;']),
                                new InputElement('time', $field->name . '_start_time', ''),
                            ], ['style' => 'display: flex; flex-direction: column;']),
                            new HtmlElement('div', [
                                new HtmlElement('label', ['End Time'], ['style' => 'width: 100px;']),
                                new InputElement('time', $field->name . '_end_time', ''),
                            ], ['style' => 'display: flex; flex-direction: column;']),
                        ], ['style' => 'display: flex; flex-direction: row;']),
                        new HtmlElement('div', [
                            new HtmlElement('a', ['Find Available Room'], ['class' => 'button'])
                        ], ['style' => 'align-self: flex-end;'])
                    ], ['style' => 'display: flex; justify-content: space-between;']),
                    new HtmlElement('div', [

                    ], []),
                ], ['style' => 'display: flex; flex-direction: column;']),
            ], ['id' => $fieldId . '_content', 'class' => 'modal', 'style' => 'display: none; width: 750px; height: 600px;']),
            new HtmlElement('a', ['Edit'], ['class' => 'button', 'href' => '#' . $fieldId . '_content', 'rel' => 'modal:open']),
        ]);
    }
}
