<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database;


use wpdb;

class RoomReservationTable extends WPDBTable
{
    /**
     * RoomReservationTable constructor.
     * @param wpdb $wpdb
     */
    public function __construct(wpdb $wpdb)
    {
        parent::__construct('cep_bookings_room_reservations', $wpdb);
    }


    /**
     * @return array of arrays representing columns with keys
     * 'name' => name of column (string)
     * 'type' => type of column (string)
     * 'attributes' => freeform attributes following the type (string)
     */
    protected function getColumns(): array
    {
        return [
            ['name' => 'event_id', 'type' => 'BIGINT(20)', 'attributes' => 'UNSIGNED NOT NULL'],
            ['name' => 'room_id', 'type' => 'BIGINT(20)', 'attributes' => 'UNSIGNED NOT NULL'],
            ['name' => 'date', 'type' => 'DATE', 'attributes' => 'NOT NULL'],
            ['name' => 'start_time', 'type' => 'TIME', 'attributes' => 'NOT NULL'],
            ['name' => 'end_time', 'type' => 'TIME', 'attributes' => 'NOT NULL'],
        ];
    }

    protected function getIndices(): array
    {
        return [
            'KEY `date_index` (`date`) USING BTREE',
            'KEY `room_id_index` (`room_id`) USING BTREE',
            'PRIMARY KEY (`event_id`,`room_id`)',
        ];
    }
}
