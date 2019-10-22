<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\MetaboxFieldDefinition;
use SirsiDynix\CEPBookings\Rest\Script\ClientScriptHelper;
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
     * @var ClientScriptHelper
     */
    private $roomPickerAjaxScript;

    /**
     * MediaGalleryPicker constructor.
     * @param Wordpress $wordpress
     * @param ClientScriptHelper $roomPickerAjaxScript
     */
    public function __construct(Wordpress $wordpress, ClientScriptHelper $roomPickerAjaxScript)
    {
        $this->wordpress = $wordpress;
        $this->roomPickerAjaxScript = $roomPickerAjaxScript;
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

        $startTimeFieldId = $field->name . '-start-time';
        $endTimeFieldId = $field->name . '-end-time';
        $eventDateFieldId = $fieldId . '-date';
        $roomTypeFieldId = $fieldId . '-room-type';
        $searchButtonFieldId = $fieldId . '-search-button';
        $resultsContentFieldId = $fieldId . '-results';
        $editButtonFieldId = $fieldId . 'edit-button';
        $contentId = $fieldId . '-content';
        $data = [
            'fieldIds' => [
                'startTime' => $startTimeFieldId,
                'endTime' => $endTimeFieldId,
                'eventDate' => $eventDateFieldId,
                'roomType' => $roomTypeFieldId,
                'searchButton' => $searchButtonFieldId,
                'results' => $resultsContentFieldId,
                'editButton' => $editButtonFieldId,
                'content' => $contentId,
            ]
        ];
        $this->roomPickerAjaxScript->enqueue($data);

        return new HtmlElement('div', [
            new HtmlElement('div', [
                new HtmlElement('div', [
                    new HtmlElement('h1', ['Rooms']),
                    new HtmlElement('div', [
                        new HtmlElement('label', ['Room Type'], ['style' => 'flex-basis: 100px;']),
                        (new WPPostSelectInput($this->wordpress, 'room_type'))->render($post, $field, $roomTypeFieldId),
                    ], ['style' => 'display: flex; align-items: center;']),
                    new HtmlElement('div', [
                        new HtmlElement('label', ['Date'], ['style' => 'flex-basis: 100px;']),
                        new InputElement('date', '', '', ['id' => $eventDateFieldId]),
                    ], ['style' => 'display: flex; align-items: center;']),
                    new HtmlElement('div', [
                        new HtmlElement('div', [
                            new HtmlElement('div', [
                                new HtmlElement('label', ['Start Time'], ['style' => 'width: 100px;']),
                                new InputElement('time', '', '', ['id' => $startTimeFieldId]),
                            ], ['style' => 'display: flex; flex-direction: column;']),
                            new HtmlElement('div', [
                                new HtmlElement('label', ['End Time'], ['style' => 'width: 100px;']),
                                new InputElement('time', '', '', ['id' => $endTimeFieldId]),
                            ], ['style' => 'display: flex; flex-direction: column;']),
                        ], ['style' => 'display: flex; flex-direction: row;']),
                        new HtmlElement('div', [
                            new HtmlElement('a', ['Find Available Room'], ['class' => 'button', 'id' => $searchButtonFieldId])
                        ], ['style' => 'align-self: flex-end;'])
                    ], ['style' => 'display: flex; justify-content: space-between;']),
                    new HtmlElement('div', [], ['id' => $resultsContentFieldId]),
                ], ['style' => 'display: flex; flex-direction: column;']),
            ], ['id' => $contentId, 'class' => 'room-modal', 'style' => 'display: none; width: 750px; height: 600px;']),
            new HtmlElement('a', ['Edit'], [
                'class' => 'button', 'href' => '#' . $contentId,
                'rel' => 'modal:open', 'id' => $editButtonFieldId,
            ]),
        ]);
    }
}