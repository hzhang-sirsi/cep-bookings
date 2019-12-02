<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database\Model;


use RuntimeException;
use SirsiDynix\CEPBookings\Database\EquipmentReservationTable;
use wpdb;

class EquipmentReservation extends BoundModel
{
    /**
     * @var EquipmentReservationTable
     */
    private $equipmentReservationTable;


    /**
     * EquipmentReservation constructor.
     * @param wpdb                      $wpdb
     * @param EquipmentReservationTable $equipmentReservationTable
     */
    public function __construct(wpdb $wpdb, EquipmentReservationTable $equipmentReservationTable)
    {
        $this->equipmentReservationTable = $equipmentReservationTable;
        parent::__construct($wpdb);
    }

    public function findReservationsAvailableByEventId(int $eventId, string $eventDate, string $startTime, string $endTime, int $equipmentType)
    {
        $query = $this->wpdb->prepare(<<<SQL
SELECT equipment.id, equipment.title,
    reservations.booked AS booked,
    equipment_quantities.quantity AS quantity
FROM (
        SELECT ID AS id, post_title AS title FROM {$this->wpdb->posts}
        WHERE post_type = 'equipment' AND post_status = 'publish'
    ) equipment
    JOIN (
        SELECT post_id, meta_value FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'equipment_type'
    ) equipment_types ON equipment.id = equipment_types.post_id
    JOIN (
        SELECT post_id, CAST(meta_value AS UNSIGNED) AS quantity FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'quantity'
    ) equipment_quantities ON equipment.id = equipment_quantities.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS start_date FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'availability_startDate'
    ) start_dates ON equipment.id = start_dates.post_id
    JOIN (
        SELECT post_id, DATE(meta_value) AS end_date FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'availability_endDate'
    ) end_dates ON equipment.id = end_dates.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS start_time FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'availability_startTime'
    ) start_times ON equipment.id = start_times.post_id
    JOIN (
        SELECT post_id, TIME_TO_SEC(meta_value) AS end_time FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'availability_endTime'
    ) end_times ON equipment.id = end_times.post_id
    JOIN (
        SELECT post_id, meta_value AS weekdays_available FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'availability_weekdaysAvailable'
    ) weekdays ON equipment.id = weekdays.post_id
    LEFT JOIN (
        SELECT `equipment_id`, SUM(quantity) AS booked FROM {$this->equipmentReservationTable->getPrefixedName()}
        WHERE event_id != %d AND `date` = %s AND (start_time <= %s AND %s <= end_time)
        GROUP BY `equipment_id`
    ) reservations ON equipment.id = reservations.equipment_id
WHERE
    (start_dates.start_date IS NULL OR DATE(%s) >= start_dates.start_date)
    AND (end_dates.end_date IS NULL OR DATE(%s) <= end_dates.end_date)
    AND (start_times.start_time IS NULL OR TIME_TO_SEC(%s) >= start_times.start_time)
    AND (end_times.end_time IS NULL OR TIME_TO_SEC(%s) <= end_times.end_time)
    AND weekdays.weekdays_available LIKE CONCAT('%%', DAYNAME(%s), '%%')
    AND equipment_types.meta_value = %s
SQL
, [$eventId, $eventDate, $endTime, $startTime, $eventDate, $eventDate, $startTime, $endTime, $eventDate, $equipmentType]);
        return $this->wpdb->get_results($query);
    }

    /**
     * @param int $eventId
     * @return array
     */
    public function findReservationsByEventId(int $eventId): array
    {
        $query = $this->wpdb->prepare(<<<SQL
SELECT `equipment_id`, `date`, `start_time`, `end_time`
FROM {$this->equipmentReservationTable->getPrefixedName()}
WHERE event_id = %d;
SQL
, [$eventId]);
        return $this->wpdb->get_results($query);
    }

    /**
     * @param int    $eventId
     * @param int[]  $equipmentIdToRequestedQuantities Mapping of requested equipment IDs to requested quantity of each
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     */
    public function setReservations(int $eventId, array $equipmentIdToRequestedQuantities, string $date, string $startTime, string $endTime)
    {
        $wpdb = $this->wpdb;
        $tablename = $this->equipmentReservationTable->getPrefixedName();
        if ($wpdb->query('START TRANSACTION;') === false) {
            throw new RuntimeException('Error starting transaction');
        }

        $shouldRollback = true;
        try {
            if ($wpdb->query($wpdb->prepare("DELETE FROM {$tablename} WHERE event_id = %d LIMIT 1;", [$eventId])) === false) {
                throw new RuntimeException('Error deleting old rows');
            }
            if ($wpdb->get_var($wpdb->prepare("SELECT CAST(%s AS TIME) < CAST(%s AS TIME) AS `valid`;", [$startTime, $endTime])) !== '1') {
                throw new RuntimeException("Start time {$startTime} is not earlier than end time {$endTime}");
            }

            $reservationInsertParams = [];
            foreach ($equipmentIdToRequestedQuantities as $equipmentId => $quantity) {
                array_push($reservationInsertParams, $eventId, $equipmentId, $date, $startTime, $endTime, $quantity);
            }

            $valuesStr = join(", \n", array_fill(0, sizeof($reservationInsertParams), '(%d, %d, %s, %s, %s, %d)'));
            $insertQuery = $wpdb->prepare(<<<SQL
INSERT INTO {$tablename}
VALUES {$valuesStr};
SQL
, $reservationInsertParams);
            if ($wpdb->query($insertQuery) === false) {
                throw new RuntimeException('Error inserting data');
            }

            $conflicts = $wpdb->get_results($wpdb->prepare(<<<SQL
SELECT equipment.id AS id,
       equipment_quantities.quantity AS quantity,
       SUM(equipment_reservations.quantity) AS reserved_quantity,
       equipment_quantities < SUM(equipment_reservations.quantity) AS valid
FROM (SELECT ID AS id FROM {$this->wpdb->posts} WHERE post_type = 'equipment') equipment
    JOIN (
        SELECT post_id, CAST(meta_value AS UNSIGNED) AS quantity FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'quantity'
    ) equipment_quantities ON equipment.id = equipment_types.post_id
    JOIN wp_cep_bookings_equipment_reservations equipment_reservations ON equipment.id = equipment_reservations.equipment_id
WHERE
    equipment_reservations.event_id = %d AND
    (equipment_reservations.start_time <= %s AND %s <= equipment_reservations.end_time)
GROUP BY equipment.id;
SQL
, [intval($eventId), $endTime, $startTime]));
            foreach ($conflicts as $row) {
                if (!filter_var($row->valid, FILTER_VALIDATE_BOOLEAN)) {
                    throw new RuntimeException('Conflict found');
                }
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

    /**
     * @param int[] $equipmentIds
     * @return int[]
     */
    private function getEquipmentQuantities(array $equipmentIds)
    {
        if (count($equipmentIds) === 0) {
            return [];
        }

        $equipmentIdsStr = join(', ', $equipmentIds);

        $output = [];
        $results = $this->wpdb->get_results($this->wpdb->prepare(<<<SQL
SELECT equipment.id, equipment_quantities.quantity
FROM (
        SELECT ID AS id, post_title AS title FROM {$this->wpdb->posts}
        WHERE post_type = 'equipment'
    ) equipment
    JOIN (
        SELECT post_id, CAST(meta_value AS UNSIGNED) AS quantity FROM {$this->wpdb->postmeta}
        WHERE meta_key = 'quantity'
    ) equipment_quantities ON equipment.id = equipment_types.post_id
WHERE
    equipment.id IN ({$equipmentIdsStr})
SQL
, []));

        foreach ($results as $row) {
            $output[intval($row->id)] = intval($row->quantity);
        }

        return $output;
    }
}
