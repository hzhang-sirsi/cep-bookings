<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Metabox\Inputs\Meta\WPPostSelectInput;
use SirsiDynix\CEPBookings\Rest\Script\ClientScriptHelper;
use SirsiDynix\CEPBookings\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use WP_Post;


/**
 * @property string $type
 */
class EquipmentPicker extends Input
{
    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * @var ClientScriptHelper
     */
    private $equipmentPickerAjaxScript;

    /**
     * MediaGalleryPicker constructor.
     * @param Wordpress          $wordpress
     * @param ClientScriptHelper $equipmentPickerAjaxScript
     */
    public function __construct(Wordpress $wordpress, ClientScriptHelper $equipmentPickerAjaxScript)
    {
        $this->wordpress = $wordpress;
        $this->equipmentPickerAjaxScript = $equipmentPickerAjaxScript;
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
     * @param string  $fieldName
     * @param string  $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, string $fieldName, string $fieldId)
    {
        $this->wordpress->wp_enqueue_style('jquery-modal-css');
        $this->wordpress->wp_enqueue_script('jquery-modal-js');
        $this->wordpress->wp_enqueue_style('jquery-timepicker-css');
        $this->wordpress->wp_enqueue_script('jquery-timepicker-js');
        $this->wordpress->wp_enqueue_style('equipment-picker-css', $this->wordpress->plugins_url('/static/css/equipment-picker.css'));

        $startTimeFieldId = $fieldId . '-start-time';
        $endTimeFieldId = $fieldId . '-end-time';
        $eventDateFieldId = $fieldId . '-date';
        $equipmentTypeFieldId = $fieldId . '-equipment-type';
        $searchButtonFieldId = $fieldId . '-search-button';
        $resultsContentFieldId = $fieldId . '-results';
        $editButtonFieldId = $fieldId . 'edit-button';
        $contentId = $fieldId . '-content';
        $data = [
            'fieldIds' => [
                'startTime' => $startTimeFieldId,
                'endTime' => $endTimeFieldId,
                'eventDate' => $eventDateFieldId,
                'equipmentType' => $equipmentTypeFieldId,
                'searchButton' => $searchButtonFieldId,
                'results' => $resultsContentFieldId,
                'editButton' => $editButtonFieldId,
                'content' => $contentId,
            ]
        ];
        $this->equipmentPickerAjaxScript->enqueue($data);

        return new HtmlElement('div', [
            new HtmlElement('div', [
                new HtmlElement('div', [
                    new HtmlElement('h1', ['Equipment']),
                    new HtmlElement('div', [
                        new HtmlElement('div', [
                            new HtmlElement('label', ['Equipment Type']),
                            (new WPPostSelectInput($this->wordpress, 'equipment_type'))->render($post, $fieldName, $equipmentTypeFieldId),
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
                                new HtmlElement('a', ['Find Equipment'], ['class' => 'button', 'id' => $searchButtonFieldId])
                            ], ['style' => 'align-self: flex-end;'])
                        ], ['style' => 'justify-content: space-between;']),
                    ], ['class' => 'search-control']),
                    new HtmlElement('div', [], ['id' => $resultsContentFieldId]),
                ], ['style' => 'flex-direction: column;']),
            ], ['id' => $contentId, 'class' => 'equipment-modal', 'style' => 'display: none;']),
            new HtmlElement('a', ['Edit'], [
                'class' => 'button', 'href' => '#' . $contentId,
                'rel' => 'modal:open', 'id' => $editButtonFieldId,
            ]),
        ]);
    }

    /**
     * @param Wordpress $wordpress
     * @param WP_Post   $post
     * @param string    $fieldName
     * @return void
     */
    public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName)
    {
        // TODO: Implement saveFields() method.
    }
}
