<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use InvalidArgumentException;
use RuntimeException;
use SirsiDynix\CEPBookings\Database\Model\EquipmentReservation;
use SirsiDynix\CEPBookings\HTML\ElementBuilder as EB;
use SirsiDynix\CEPBookings\HTML\Elements\JQueryModal;
use SirsiDynix\CEPBookings\HTML\Elements\LabeledInput;
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
     * @var EquipmentReservation
     */
    private $equipmentReservationModel;

    /**
     * MediaGalleryPicker constructor.
     * @param Wordpress            $wordpress
     * @param ClientScriptHelper   $equipmentPickerAjaxScript
     * @param EquipmentReservation $equipmentReservationModel
     */
    public function __construct(Wordpress $wordpress, ClientScriptHelper $equipmentPickerAjaxScript, EquipmentReservation $equipmentReservationModel)
    {
        $this->wordpress = $wordpress;
        $this->equipmentPickerAjaxScript = $equipmentPickerAjaxScript;
        $this->equipmentReservationModel = $equipmentReservationModel;
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
        $this->wordpress->wp_enqueue_script('font-awesome');
        $this->wordpress->wp_enqueue_style('equipment-picker-css', $this->wordpress->plugins_url('/static/css/equipment-picker.css'));

        $startTimeFieldId = $fieldId . '-start-time';
        $endTimeFieldId = $fieldId . '-end-time';
        $eventDateFieldId = $fieldId . '-date';
        $equipmentTypeFieldId = $fieldId . '-equipment-type';
        $searchButtonFieldId = $fieldId . '-search-button';
        $saveButtonFieldId = $fieldId . '-save-button';
        $resultsContentFieldId = $fieldId . '-results';
        $editButtonFieldId = $fieldId . 'edit-button';
        $summaryLabelFieldId = $fieldId . '-summary-label';
        $contentFieldId = $fieldId . '-content';

        $data = [
            'fieldIds' => [
                'startTime' => $startTimeFieldId,
                'endTime' => $endTimeFieldId,
                'eventDate' => $eventDateFieldId,
                'equipmentType' => $equipmentTypeFieldId,
                'searchButton' => $searchButtonFieldId,
                'saveButton' => $saveButtonFieldId,
                'results' => $resultsContentFieldId,
                'editButton' => $editButtonFieldId,
                'summaryLabel' => $summaryLabelFieldId,
                'content' => $contentFieldId,
                'value' => $fieldId,
            ],
            'selected' => $this->getReservations($post),
            'postId' => $post->ID,
        ];
        $this->equipmentPickerAjaxScript->enqueue($data);

        $modal = new JQueryModal($contentFieldId, 'equipment-modal', [
            EB::div([
                new HtmlElement('h1', ['Equipment']),
                EB::div([
                    new LabeledInput('Equipment Type',
                        (new WPPostSelectInput($this->wordpress, 'equipment_type'))
                            ->render($post, $fieldName, $equipmentTypeFieldId)
                            ->setAttribute('name', null)),
                    LabeledInput::build('Date', 'date', $eventDateFieldId),
                    EB::div([
                        EB::div([
                            LabeledInput::build('Start Time', 'time', $startTimeFieldId)
                                ->setAttribute('style', 'flex-direction: column;')
                                ->setAttribute('required', 'required'),
                            LabeledInput::build('End Time', 'time', $endTimeFieldId)
                                ->setAttribute('style', 'flex-direction: column;')
                                ->setAttribute('required', 'required'),
                        ], 'flex-row'),
                        EB::div([
                            new HtmlElement('a', ['Find Equipment'], ['class' => 'button', 'id' => $searchButtonFieldId])
                        ], null, null, ['style' => 'align-self: flex-end;'])
                    ], null, null, ['style' => 'justify-content: space-between;']),
                ], 'search-control'),
                EB::div([], 'content', $resultsContentFieldId),
                EB::div([
                    new HtmlElement('a', ['Save'], ['class' => 'button button-primary', 'id' => $saveButtonFieldId]),
                ], 'footer'),
            ], null, null, ['style' => 'flex-direction: column;']),
        ]);

        return EB::div([
            $modal,
            new HtmlElement('label', [], ['class' => 'equipment-summary-label', 'id' => $summaryLabelFieldId]),
            $modal->createOpenButton('Edit', $editButtonFieldId),
            new InputElement('hidden', $fieldName, '', ['id' => $fieldId])
        ], null, null, ['style' => 'display: flex; align-items: center;']);
    }

    private function getReservations(WP_Post $post)
    {
        $reservations = $this->equipmentReservationModel->findReservationsByEventId(intval($post->ID));

        $selected = [];
        foreach ($reservations as $reservation) {
            if (!isset($selected['reservations'])) {
                $selected['reservations'] = [];
            }

            $selected['date'] = $reservation->date;
            $selected['startTime'] = $reservation->start_time;
            $selected['endTime'] = $reservation->end_time;
            $selected['reservations'][$reservation->equipment_id] = $reservation->quantity;
        }

        return $selected;
    }

    /**
     * @param Wordpress $wordpress
     * @param WP_Post   $post
     * @param string    $fieldName
     * @return void
     */
    public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName)
    {
        if (array_key_exists($fieldName, $_POST)) {
            $postData = stripslashes($_POST[$fieldName]);
            if (strlen(trim($postData)) === 0) {
                return;
            }

            $data = $this->validateArguments(json_decode($postData, true));
            if ($data === null) {
                throw new RuntimeException('Error decoding message');
            }

            $equipmentReservations = $data['reservations'];
            $date = $data['date'];
            $startTime = $data['startTime'];
            $endTime = $data['endTime'];

            $this->equipmentReservationModel->setReservations(intval($post->ID), $equipmentReservations, $date, $startTime, $endTime);
        }
    }

    private function validateArguments($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Argument must be an Object');
        }
        if (!is_string($data['date'])) {
            throw new InvalidArgumentException('date must be a string');
        };
        if (!is_string($data['startTime'])) {
            throw new InvalidArgumentException('startTime must be a string');
        };
        if (!is_string($data['endTime'])) {
            throw new InvalidArgumentException('endTime must be a string');
        };
        if (!is_array($data['reservations'])) {
            throw new InvalidArgumentException('reservations must be an Object');
        }

        return [
            'date' => $data['date'],
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'reservations' => $data['reservations'],
        ];
    }
}
