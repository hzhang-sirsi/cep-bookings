<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Wordpress\Ajax;


use SirsiDynix\CEPBookings\Wordpress;
use wpdb;

class RoomSearchHandler extends AjaxHandler
{
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

        $wpdb = Wordpress::get_database();
        $queryString = <<<SQL
SELECT rooms.id, rooms.title,
    start_dates.start_date AS start_date,
    end_dates.end_date AS end_date,
    start_times.start_time AS start_time,
    end_times.end_time AS end_time
FROM (
        SELECT ID AS id, post_title AS title FROM {$wpdb->posts}
        WHERE post_type = 'room'
    ) rooms
    JOIN (
        SELECT post_id, meta_value FROM {$wpdb->postmeta}
        WHERE meta_key = 'room_type'
    ) room_types ON rooms.id = room_types.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS start_date FROM {$wpdb->postmeta}
        WHERE meta_key = 'availability_startDate'
    ) start_dates ON rooms.id = start_dates.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS end_date FROM {$wpdb->postmeta}
        WHERE meta_key = 'availability_endDate'
    ) end_dates ON rooms.id = end_dates.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS start_time FROM {$wpdb->postmeta}
        WHERE meta_key = 'availability_startTime'
    ) start_times ON rooms.id = start_times.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS end_time FROM {$wpdb->postmeta}
        WHERE meta_key = 'availability_endTime'
    ) end_times ON rooms.id = end_times.post_id
    JOIN (
        SELECT post_id, meta_value AS weekdays_available FROM {$wpdb->postmeta}
        WHERE meta_key = 'availability_weekdaysAvailable'
    ) weekdays ON rooms.id = weekdays.post_id
    LEFT JOIN (
        SELECT `room_id`, COUNT(*) AS conflicts FROM {$wpdb->prefix}cep_bookings_room_reservations
        WHERE event_id != %d AND `date` = %s AND (start_time <= %s AND %s <= end_time)
        GROUP BY `room_id`
    ) reservations ON rooms.id = reservations.room_id
WHERE
    (start_dates.start_date IS NULL OR DATE(%s) >= start_dates.start_date)
    AND (end_dates.end_date IS NULL OR DATE(%s) <= end_dates.end_date)
    AND (start_times.start_time IS NULL OR TIME_TO_SEC(%s) >= start_times.start_time)
    AND (end_times.end_time IS NULL OR TIME_TO_SEC(%s) <= end_times.end_time)
    AND weekdays.weekdays_available LIKE CONCAT('%%', DAYNAME(%s), '%%')
    -- AND (reservations.conflicts IS NULL)
    AND room_types.meta_value = %s
SQL;
        $query = $wpdb->prepare($queryString, [$eventId, $eventDate, $endTime, $startTime, $eventDate, $eventDate, $startTime, $endTime, $eventDate, $roomType]);
        $posts = $wpdb->get_results($query);

        $reservations_by_room = $this->getReservationsByRoom($wpdb, $eventDate, array_map(function ($e) {
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
            ]);
        }

        return $response;
    }

    private function getReservationsByRoom(wpdb $wpdb, string $eventDate, array $roomIds)
    {
        $reservations_by_room = [];

        if (count($roomIds) > 0) {
            $roomIdsStr = join(', ', $roomIds);
            $queryString = <<<SQL
SELECT res.room_id, res.event_id, posts.post_title AS event_name, res.start_time, res.end_time
FROM {$wpdb->prefix}cep_bookings_room_reservations res
    JOIN {$wpdb->posts} posts ON res.event_id = posts.ID
WHERE `date` = %s AND room_id IN ({$roomIdsStr}) AND posts.post_type = 'tribe_events'
SQL;
            $reservations = $wpdb->get_results($wpdb->prepare($queryString, [$eventDate]));
            foreach ($reservations as $reservation) {
                if (!array_key_exists($reservation->room_id, $reservations_by_room)) {
                    $reservations_by_room[$reservation->room_id] = [];
                }
                array_push($reservations_by_room[$reservation->room_id], $reservation);
            }
        }

        return $reservations_by_room;
    }
}
