<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\HTML\ElementBuilder as EB;
use SirsiDynix\CEPBookings\HTML\Elements\JQueryModal;
use SirsiDynix\CEPBookings\HTML\Elements\LabeledInput;
use SirsiDynix\CEPBookings\Rest\Script\ClientScriptHelper;
use SirsiDynix\CEPBookings\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
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
     * @param Wordpress $wordpress
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
        $this->wordpress->wp_enqueue_style('equipment-picker-css', $this->wordpress->plugins_url('/static/css/equipment-picker.css'));

        $startTimeFieldId = $fieldId . '-start-time';
        $endTimeFieldId = $fieldId . '-end-time';
        $eventDateFieldId = $fieldId . '-date';
        $equipmentTypeFieldId = $fieldId . '-equipment-type';
        $searchButtonFieldId = $fieldId . '-search-button';
        $resultsContentFieldId = $fieldId . '-results';
        $editButtonFieldId = $fieldId . 'edit-button';
        $contentFieldId = $fieldId . '-content';
        $data = [
            'fieldIds' => [
                'startTime' => $startTimeFieldId,
                'endTime' => $endTimeFieldId,
                'eventDate' => $eventDateFieldId,
                'equipmentType' => $equipmentTypeFieldId,
                'searchButton' => $searchButtonFieldId,
                'results' => $resultsContentFieldId,
                'editButton' => $editButtonFieldId,
                'content' => $contentFieldId,
            ]
        ];
        $this->equipmentPickerAjaxScript->enqueue($data);

        $modal = new JQueryModal($contentFieldId, 'equipment-modal', [
            EB::div([
                new HtmlElement('h1', ['Equipment']),
                EB::div([
                    new LabeledInput('Equipment Type',
                        (new WPPostSelectInput($this->wordpress, 'equipment_type'))->render($post, $fieldName, $equipmentTypeFieldId)),
                    LabeledInput::build('Date', 'date', $eventDateFieldId),
                    EB::div([
                        EB::div([
                            LabeledInput::build('Start Time', 'time', $startTimeFieldId)->setAttribute('style', 'flex-direction: column;'),
                            LabeledInput::build('End Time', 'time', $endTimeFieldId)->setAttribute('style', 'flex-direction: column;'),
                        ], 'flex-row'),
                        EB::div([
                            new HtmlElement('a', ['Find Equipment'], ['class' => 'button', 'id' => $searchButtonFieldId])
                        ], null, null, ['style' => 'align-self: flex-end;'])
                    ], null, null, ['style' => 'justify-content: space-between;']),
                ], 'search-control'),
                EB::div([], null, $resultsContentFieldId),
            ], null, null, ['style' => 'flex-direction: column;']),
            EB::div([]),
        ]);

        return EB::div([
            $modal,
            $modal->createOpenButton('Edit', $editButtonFieldId),
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
