<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database\Model;


use RuntimeException;
use SirsiDynix\CEPBookings\Database\RoomReservationTable;
use SirsiDynix\CEPBookings\Database\TransactionManager;

class RoomReservation extends BoundModel
{
    /**
     * @var RoomReservationTable
     */
    private $roomReservationTable;


    /**
     * RoomReservation constructor.
     * @param TransactionManager   $transactionManager
     * @param RoomReservationTable $roomReservationTable
     */
    public function __construct(TransactionManager $transactionManager, RoomReservationTable $roomReservationTable)
    {
        $this->roomReservationTable = $roomReservationTable;
        parent::__construct($transactionManager);
    }

    public function findReservationsAvailableByEventId(int $eventId, string $eventDate, string $startTime, string $endTime, int $roomType)
    {
        $availabilityField = 'availability';
        $postsTable = $this->tm->getPrefixedTableName('posts');
        $postMetaTable = $this->tm->getPrefixedTableName('postmeta');
        return $this->tm->get_results(<<<SQL
SELECT rooms.id, rooms.title,
    start_dates.start_date AS start_date,
    end_dates.end_date AS end_date,
    start_times.start_time AS start_time,
    end_times.end_time AS end_time,
    reservations.conflicts IS NULL AS available
FROM (
        SELECT ID AS id, post_title AS title FROM {$postsTable}
        WHERE post_type = 'room' AND post_status = 'publish'
    ) rooms
    JOIN (
        SELECT post_id, meta_value FROM {$postMetaTable}
        WHERE meta_key = 'room_type'
    ) room_types ON rooms.id = room_types.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS start_date FROM {$postMetaTable}
        WHERE meta_key = '{$availabilityField}_startDate'
    ) start_dates ON rooms.id = start_dates.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS end_date FROM {$postMetaTable}
        WHERE meta_key = '{$availabilityField}_endDate'
    ) end_dates ON rooms.id = end_dates.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS start_time FROM {$postMetaTable}
        WHERE meta_key = '{$availabilityField}_startTime'
    ) start_times ON rooms.id = start_times.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS end_time FROM {$postMetaTable}
        WHERE meta_key = '{$availabilityField}_endTime'
    ) end_times ON rooms.id = end_times.post_id
    JOIN (
        SELECT post_id, meta_value AS weekdays_available FROM {$postMetaTable}
        WHERE meta_key = '{$availabilityField}_weekdaysAvailable'
    ) weekdays ON rooms.id = weekdays.post_id
    LEFT JOIN (
        SELECT `room_id`, COUNT(*) AS conflicts FROM {$this->roomReservationTable->getPrefixedName()}
        WHERE event_id != %d AND `date` = %s AND (start_time <= %s AND %s <= end_time)
        GROUP BY `room_id`
    ) reservations ON rooms.id = reservations.room_id
WHERE
    (start_dates.start_date IS NULL OR DATE(%s) >= start_dates.start_date)
    AND (end_dates.end_date IS NULL OR DATE(%s) <= end_dates.end_date)
    AND (start_times.start_time IS NULL OR TIME_TO_SEC(%s) >= start_times.start_time)
    AND (end_times.end_time IS NULL OR TIME_TO_SEC(%s) <= end_times.end_time)
    AND weekdays.weekdays_available LIKE CONCAT('%%', DAYNAME(%s), '%%')
    AND (room_types.meta_value = %s OR %s = -1)
SQL
            , [$eventId, $eventDate, $endTime, $startTime, $eventDate, $eventDate, $startTime, $endTime, $eventDate, $roomType, $roomType]);
    }

    /**
     * @param int $eventId
     * @return array
     */
    public function findReservationsByEventId(int $eventId): array
    {
        return $this->tm->get_results(<<<SQL
SELECT `room_id`, `date`, `start_time`, `end_time`
FROM {$this->roomReservationTable->getPrefixedName()}
WHERE event_id = %d;
SQL
            , [$eventId]);
    }

    public function findReservationsByRoom(string $eventDate, array $roomIds)
    {
        if (count($roomIds) === 0) {
            return [];
        }

        $reservations_by_room = [];

        $roomIdsStr = join(', ', $roomIds);
        $reservations = $this->tm->get_results(<<<SQL
SELECT res.room_id, res.event_id, posts.post_title AS event_name, res.start_time, res.end_time
FROM {$this->roomReservationTable->getPrefixedName()} res
    JOIN {$this->tm->getPrefixedTableName('posts')} posts ON res.event_id = posts.ID
WHERE `date` = %s AND room_id IN ({$roomIdsStr}) AND posts.post_type = 'tribe_events'
SQL
            , [$eventDate]);

        foreach ($reservations as $reservation) {
            if (!array_key_exists($reservation->room_id, $reservations_by_room)) {
                $reservations_by_room[$reservation->room_id] = [];
            }
            array_push($reservations_by_room[$reservation->room_id], $reservation);
        }

        return $reservations_by_room;
    }

    public function setRoomReservation(int $eventId, int $roomId, string $date, string $startTime, string $endTime)
    {
        $tablename = $this->roomReservationTable->getPrefixedName();
        $this->tm->withTransaction(function (TransactionManager $tm) use ($tablename, $eventId, $roomId, $date, $startTime, $endTime) {
            if ($tm->query("DELETE FROM {$tablename} WHERE event_id = %d LIMIT 1;", [$eventId]) === false) {
                throw new RuntimeException('Error deleting old rows');
            }
            if ($tm->get_var("SELECT CAST(%s AS TIME) < CAST(%s AS TIME) AS `valid`;", [$startTime, $endTime]) !== '1') {
                throw new RuntimeException("Start time {$startTime} is not earlier than end time {$endTime}");
            }
            if (intval($tm->get_var("SELECT COUNT(*) FROM {$tablename} WHERE room_id = %d AND (start_time <= %s AND %s <= end_time) AND `date` = %s;", [
                    $roomId, $startTime, $endTime, $date
                ])) !== 0) {
                throw new RuntimeException('Conflict found');
            }
            if ($tm->query("INSERT INTO {$tablename} VALUES (%d, %d, %s, %s, %s);", [
                    $eventId,
                    $roomId,
                    $date,
                    $startTime,
                    $endTime,
                ]) === false) {
                throw new RuntimeException('Error inserting data');
            }
        });
    }
}
