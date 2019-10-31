<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database\Model;


use wpdb;

class BoundModel
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }
}
