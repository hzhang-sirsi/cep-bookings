<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


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
     * @param WP_Post $post
     * @param string $fieldName
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, string $fieldName, string $fieldId)
    {
        $this->wordpress->wp_enqueue_style('jquery-modal-css');
        $this->wordpress->wp_enqueue_script('jquery-modal-js');
        $this->wordpress->wp_enqueue_style('jquery-timepicker-css');
        $this->wordpress->wp_enqueue_script('jquery-timepicker-js');
        $this->wordpress->wp_enqueue_style('room-picker-css', $this->wordpress->plugins_url('/static/css/room-picker.css'));

        $startTimeFieldId = $fieldId . '-start-time';
        $endTimeFieldId = $fieldId . '-end-time';
        $eventDateFieldId = $fieldId . '-date';
        $roomTypeFieldId = $fieldId . '-room-type';
        $searchButtonFieldId = $fieldId . '-search-button';
        $saveButtonFieldId = $fieldId . '-save-button';
        $resultsContentFieldId = $fieldId . '-results';
        $editButtonFieldId = $fieldId . '-edit-button';
        $summaryLabelFieldId = $fieldId . '-summary-label';
        $contentFieldId = $fieldId . '-content';
        $data = [
            'fieldIds' => [
                'startTime' => $startTimeFieldId,
                'endTime' => $endTimeFieldId,
                'eventDate' => $eventDateFieldId,
                'roomType' => $roomTypeFieldId,
                'searchButton' => $searchButtonFieldId,
                'saveButton' => $saveButtonFieldId,
                'results' => $resultsContentFieldId,
                'editButton' => $editButtonFieldId,
                'summaryLabel' => $summaryLabelFieldId,
                'content' => $contentFieldId,
                'value' => $fieldId,
            ]
        ];
        $this->roomPickerAjaxScript->enqueue($data);

        return new HtmlElement('div', [
            new HtmlElement('div', [
                new HtmlElement('div', [
                    new HtmlElement('h1', ['Rooms']),
                    new HtmlElement('div', [
                        new HtmlElement('div', [
                            new HtmlElement('label', ['Room Type']),
                            (new WPPostSelectInput($this->wordpress, 'room_type'))->render($post, $fieldName, $roomTypeFieldId),
                        ], ['style' => 'align-items: center;']),
                        new HtmlElement('div', [
                            new HtmlElement('label', ['Date']),
                            new InputElement('date', '', '', ['id' => $eventDateFieldId]),
                        ], ['style' => 'align-items: center;']),
                        new HtmlElement('div', [
                            new HtmlElement('div', [
                                new HtmlElement('div', [
                                    new HtmlElement('label', ['Start Time']),
                                    new InputElement('time', '', '', ['id' => $startTimeFieldId]),
                                ], ['style' => 'flex-direction: column;']),
                                new HtmlElement('div', [
                                    new HtmlElement('label', ['End Time']),
                                    new InputElement('time', '', '', ['id' => $endTimeFieldId]),
                                ], ['style' => 'flex-direction: column;']),
                            ], ['class' => 'flex-row']),
                            new HtmlElement('div', [
                                new HtmlElement('a', ['Find Available Room'], ['class' => 'button', 'id' => $searchButtonFieldId]),
                            ], ['style' => 'align-self: flex-end;']),
                        ], ['style' => 'justify-content: space-between;']),
                    ], ['class' => 'search-control']),
                    new HtmlElement('div', [], ['class' => 'content', 'id' => $resultsContentFieldId]),
                    new HtmlElement('div', [
                        new HtmlElement('a', ['Save'], ['class' => 'button button-primary', 'id' => $saveButtonFieldId]),
                    ], ['class' => 'footer']),
                ], ['style' => 'flex-direction: column;']),
            ], ['id' => $contentFieldId, 'class' => 'room-modal', 'style' => 'display: none;']),
            new HtmlElement('label', [], ['id' => $summaryLabelFieldId]),
            new HtmlElement('a', ['Edit'], [
                'class' => 'button', 'href' => '#' . $contentFieldId,
                'rel' => 'modal:open', 'id' => $editButtonFieldId,
            ]),
            new InputElement('hidden', $fieldName, '', ['id' => $fieldId])
        ]);
    }

    /**
     * @param Wordpress $wordpress
     * @param WP_Post $post
     * @param string $fieldName
     * @return void
     */
    public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName)
    {
        // TODO: Implement saveFields() method.
    }
}
