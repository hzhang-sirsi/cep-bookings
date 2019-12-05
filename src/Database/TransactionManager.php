<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database;


use Closure;
use RuntimeException;
use wpdb;

final class TransactionManager
{
    private $inTransaction;

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * TransactionManager constructor.
     * @param wpdb $wpdb
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * @param string      $statement
     * @param array       $params
     * @param string|null $failureMessage If specified will abort transaction on non-truthy result
     * @return mixed
     */
    public function query(string $statement, array $params = [], string $failureMessage = null)
    {
        $query = $this->wpdb->prepare($statement, $params);

        return $this->withTransaction(function ($tm) use ($query, $failureMessage) {
            $result = $tm->wpdb->query($query);

            if ($failureMessage !== null && $result === false) {
                throw new TransactionAbortException($failureMessage);
            }

            return $result;
        });
    }

    /**
     * @param Closure $body Body of transaction accepting single parameter of this manager
     * @return mixed
     */
    public function withTransaction(Closure $body)
    {
        if ($this->inTransaction) {
            return $body($this);
        }

        if ($this->wpdb->query('START TRANSACTION;') === false) {
            throw new RuntimeException('Error starting transaction');
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $shouldAbort = true;
        $this->inTransaction = true;
        try {
            $result = $body($this);
            $shouldAbort = false;
            return $result;
        } finally {
            $this->inTransaction = false;
            if ($shouldAbort === true) {
                $this->wpdb->query('ROLLBACK;');
            } else {
                if ($this->wpdb->query('COMMIT;') === false) {
                    throw new RuntimeException('Error starting transaction');
                }
            }
        }
    }

    /**
     * @param string      $statement
     * @param array       $params
     * @param string|null $failureMessage If specified will abort transaction on non-truthy result
     * @return mixed
     */
    public function get_var(string $statement, array $params = [], string $failureMessage = null)
    {
        $query = $this->wpdb->prepare($statement, $params);

        return $this->withTransaction(function ($tm) use ($query, $failureMessage) {
            $result = $tm->wpdb->get_var($query);

            if ($failureMessage !== null && $result === null) {
                throw new TransactionAbortException($failureMessage);
            }

            return $result;
        });
    }

    /**
     * @param string      $statement
     * @param array       $params
     * @param string|null $failureMessage If specified will abort transaction on non-truthy result
     * @return mixed
     */
    public function get_row(string $statement, array $params = [], string $failureMessage = null)
    {
        $query = $this->wpdb->prepare($statement, $params);

        return $this->withTransaction(function ($tm) use ($query, $failureMessage) {
            $result = $tm->wpdb->get_row($query);

            if ($failureMessage !== null && $result === null) {
                throw new TransactionAbortException($failureMessage);
            }

            return $result;
        });
    }

    /**
     * @param string      $statement
     * @param array       $params
     * @param string|null $failureMessage If specified will abort transaction on non-truthy result
     * @return mixed
     */
    public function get_results(string $statement, array $params = [], string $failureMessage = null)
    {
        $query = $this->wpdb->prepare($statement, $params);

        return $this->withTransaction(function ($tm) use ($query, $failureMessage) {
            $result = $tm->wpdb->get_results($query);

            if ($failureMessage !== null && $result === null) {
                throw new TransactionAbortException($failureMessage);
            }

            return $result;
        });
    }

    public function getPrefixedTableName(string $tableName): string
    {
        return "{$this->wpdb->prefix}{$tableName}";
    }
}
