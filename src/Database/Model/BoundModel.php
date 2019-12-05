<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database\Model;


use SirsiDynix\CEPBookings\Database\TransactionManager;

class BoundModel
{
    /**
     * @var TransactionManager
     */
    protected $tm;

    public function __construct(TransactionManager $transactionManager)
    {
        $this->tm = $transactionManager;
    }
}
