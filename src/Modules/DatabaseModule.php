<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use DI\Container;
use Exception;
use SirsiDynix\CEPBookings\Wordpress;
use wpdb;

class DatabaseModule extends AbstractModule
{
    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * DatabaseModule constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->wpdb = Wordpress::get_database();
    }

    /**
     * Implement module loading
     *
     * @return void
     * @throws Exception
     */
    public function loadModule(): void
    {
        $this->wpdb->get_results(<<<SQL
CREATE TABLE IF NOT EXISTS {$this->getRoomTableName()} (
	`event_id` BIGINT(20) unsigned NOT NULL,
	`room_id` BIGINT(20) unsigned NOT NULL,
	`date` DATE NOT NULL,
	`start_time` TIME NOT NULL,
	`end_time` TIME NOT NULL,
    KEY `date_index` (`date`) USING BTREE,
    KEY `room_id_index` (`room_id`) USING BTREE,
	PRIMARY KEY (`event_id`,`room_id`)
) {$this->wpdb->get_charset_collate()};
SQL
        );
    }

    private function getRoomTableName()
    {
        return "`{$this->wpdb->prefix}cep_bookings_room_reservations`";
    }
}
