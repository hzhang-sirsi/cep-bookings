<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use DI\Container;
use Exception;
use SirsiDynix\CEPBookings\Database\EquipmentReservationTable;
use SirsiDynix\CEPBookings\Database\RoomReservationTable;
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
        $this->container->set(wpdb::class, $this->wpdb);
        $this->wpdb->get_results($this->container->get(RoomReservationTable::class)->getCreateTable());
        $this->wpdb->get_results($this->container->get(EquipmentReservationTable::class)->getCreateTable());
    }
}
