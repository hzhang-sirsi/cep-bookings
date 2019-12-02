<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Wordpress\Ajax;


use SirsiDynix\CEPBookings\Database\Model\RoomReservation;
use SirsiDynix\CEPBookings\Wordpress;

class RoomSearchHandler extends AjaxHandler
{
    /**
     * @var RoomReservation
     */
    private $roomReservationModel;

    /**
     * RoomSearchHandler constructor.
     * @param RoomReservation $roomReservationModel
     * @param Wordpress       $wordpress
     */
    public function __construct(RoomReservation $roomReservationModel, Wordpress $wordpress)
    {
        $this->roomReservationModel = $roomReservationModel;
        parent::__construct($wordpress);
    }

    public function getEventName(): string
    {
        return 'cb_room_search';
    }

    public function handler(array $postData)
    {
        $roomType = intval($postData['roomType']);
        $eventId = intval($postData['eventId']);
        $eventDate = $postData['eventDate'];
        $startTime = $postData['startTime'];
        $endTime = $postData['endTime'];

        $response = [
            'posts' => []
        ];

        $posts = $this->roomReservationModel->findReservationsAvailableByEventId($eventId, $eventDate, $startTime, $endTime, $roomType);
        $reservations_by_room = $this->roomReservationModel->findReservationsByRoom($eventDate, array_map(function ($e) {
            return intval($e->id);
        }, $posts));
        foreach ($posts as $post) {
            array_push($response['posts'], [
                'id' => intval($post->id),
                'title' => $post->title,
                'thumbnail' => get_the_post_thumbnail_url(intval($post->id)),
                'start_date' => $post->start_date,
                'end_date' => $post->end_date,
                'start_time' => $post->start_time,
                'end_time' => $post->end_time,
                'reservations' => $reservations_by_room[intval($post->id)],
                'available' => filter_var($post->available, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        return $response;
    }
}
