<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database;


use Exception;

class TransactionAbortException extends Exception
{
    /**
     * TransactionAbortException constructor.
     * @param string $message Message for abort
     */
    public function __construct(string $message)
    {
        parent::__construct("Transaction aborted: {$message}");
    }
}
