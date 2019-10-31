<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Wordpress\Ajax;


use SirsiDynix\CEPBookings\Database\Model\EquipmentReservation;
use SirsiDynix\CEPBookings\Wordpress;

class EquipmentSearchHandler extends AjaxHandler
{
    /**
     * @var EquipmentReservation
     */
    private $equipmentReservationModel;

    /**
     * EquipmentSearchHandler constructor.
     * @param EquipmentReservation $equipmentReservationModel
     * @param Wordpress            $wordpress
     */
    public function __construct(EquipmentReservation $equipmentReservationModel, Wordpress $wordpress)
    {
        $this->equipmentReservationModel = $equipmentReservationModel;
        parent::__construct($wordpress);
    }

    public function getEventName(): string
    {
        return 'cb_equip_search';
    }

    public function handler(array $postData)
    {
        $equipmentType = intval($postData['equipmentType']);
        $eventId = intval($postData['eventId']);
        $eventDate = $postData['eventDate'];
        $startTime = $postData['startTime'];
        $endTime = $postData['endTime'];

        $response = [
            'posts' => []
        ];

        $posts = $this->equipmentReservationModel->findReservationsAvailableByEventId($eventId, $eventDate, $startTime, $endTime, $equipmentType);
        foreach ($posts as $post) {
            array_push($response['posts'], [
                'id' => intval($post->id),
                'title' => $post->title,
                'thumbnail' => get_the_post_thumbnail_url(intval($post->id)),
                'booked' => intval($post->booked),
                'quantity' => intval($post->quantity),
            ]);
        }

        return $response;
    }
}
