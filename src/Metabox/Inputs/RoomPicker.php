<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use InvalidArgumentException;
use RuntimeException;
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
        $this->wordpress->wp_enqueue_style('vis-js-css');
        $this->wordpress->wp_enqueue_script('vis-js');
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

        $wpdb = Wordpress::get_database();
        $room = $wpdb->get_row($wpdb->prepare("SELECT `room_id`, `date`, `start_time`, `end_time` FROM {$wpdb->prefix}cep_bookings_room_reservations WHERE event_id = %d;", [$post->ID]));
        $selected = null;
        if ($room !== null) {
            $selected = [
                'post_id' => intval($room->room_id),
                'title' => $this->wordpress->get_post(intval($room->room_id))->post_title,
                'date' => $room->date,
                'startTime' => $room->start_time,
                'endTime' => $room->end_time,
            ];
        }

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
            ],
            'selected' => $selected,
            'postId' => $post->ID,
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
            new HtmlElement('label', [], ['class' => 'room-summary-label', 'id' => $summaryLabelFieldId]),
            new HtmlElement('a', ['Edit'], [
                'class' => 'button', 'href' => '#' . $contentFieldId,
                'rel' => 'modal:open', 'id' => $editButtonFieldId,
            ]),
            new InputElement('hidden', $fieldName, '', ['id' => $fieldId])
        ], ['style' => 'display: flex; align-items: center;']);
    }

    /**
     * @param Wordpress $wordpress
     * @param WP_Post $post
     * @param string $fieldName
     * @return void
     */
    public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName)
    {
        if (array_key_exists($fieldName, $_POST)) {
            $postData = stripslashes($_POST[$fieldName]);
            $data = $this->validateArguments(json_decode($postData, true));
            if ($data === null) {
                throw new RuntimeException('Error decoding message');
            }

            $roomId = $data['post_id'];
            $startTime = $data['startTime'];
            $endTime = $data['endTime'];

            $wpdb = Wordpress::get_database();
            $tablename = "{$wpdb->prefix}cep_bookings_room_reservations";
            if ($wpdb->query('START TRANSACTION;') === false) {
                throw new RuntimeException('Error starting transaction');
            }

            $shouldRollback = true;
            try {
                if ($wpdb->query($wpdb->prepare("DELETE FROM {$tablename} WHERE event_id = %d LIMIT 1;", [$post->ID])) === false) {
                    throw new RuntimeException('Error deleting old rows');
                }
                if ($wpdb->get_var($wpdb->prepare("SELECT CAST(%s AS TIME) < CAST(%s AS TIME) AS `valid`;", [$startTime, $endTime])) !== '1') {
                    $data = print_r($data, true);
                    throw new RuntimeException("Start time is not earlier than end time {$data}");
                }
                if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tablename} WHERE room_id = %d AND (start_time <= %s AND %s <= end_time);", [
                        $roomId, $startTime, $endTime,
                    ])) !== '0') {
                    throw new RuntimeException('Conflict found');
                }
                if ($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}cep_bookings_room_reservations VALUES (%d, %d, %s, %s, %s);", [
                        $post->ID,
                        $roomId,
                        $data['date'],
                        $startTime,
                        $endTime,
                    ])) === false) {
                    throw new RuntimeException('Error inserting data');
                }

                $shouldRollback = false;
            } finally {
                if ($shouldRollback === true) {
                    $wpdb->query('ROLLBACK;');
                } else {
                    $wpdb->query('COMMIT;');
                }
            }
        }
    }

    private function validateArguments($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Argument must be an Object');
        }
        if (!is_int($data['post_id'])) {
            throw new InvalidArgumentException('post_id must be an int');
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

        if ($this->wordpress->get_post($data['post_id'])->post_type !== 'room') {
            throw new InvalidArgumentException('Post ID must be a room');
        }

        return [
            'post_id' => $data['post_id'],
            'date' => $data['date'],
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
        ];
    }
}
