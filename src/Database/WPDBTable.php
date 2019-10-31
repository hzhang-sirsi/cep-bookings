<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Database;


use wpdb;

abstract class WPDBTable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * WPDBTable constructor.
     * @param string $name
     * @param wpdb   $wpdb
     */
    public function __construct(string $name, wpdb $wpdb)
    {
        $this->name = $name;
        $this->wpdb = $wpdb;
    }

    public function getCreateTable(): string
    {
        return <<<SQL
            CREATE TABLE IF NOT EXISTS {$this->getPrefixedName()} (
                {$this->getDDL()}
            ) {$this->wpdb->get_charset_collate()};
            SQL;
    }

    public function getPrefixedName(): string
    {
        return "`{$this->wpdb->prefix}{$this->name}`";
    }

    private function getDDL(): string
    {
        $columns = array_map(function ($column) {
            return trim("`{$column['name']}` {$column['type']} {$column['attributes']}");
        }, $this->getColumns());
        $indices = $this->getIndices();
        $ddl = join(",\n", array_merge($columns, $indices));
        return $ddl;
    }

    /**
     * @return array of arrays representing columns with keys
     * 'name' => name of column (string)
     * 'type' => type of column (string)
     * 'attributes' => freeform attributes following the type (string)
     */
    abstract protected function getColumns(): array;

    abstract protected function getIndices(): array;
}
