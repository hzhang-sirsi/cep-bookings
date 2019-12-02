<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;

use SirsiDynix\CEPBookings\Database\WPDBTable;

class TestDBTable extends WPDBTable
{
    /**
     * @return array of arrays representing columns with keys
     * 'name' => name of column (string)
     * 'type' => type of column (string)
     * 'attributes' => freeform attributes following the type (string)
     */
    protected function getColumns(): array
    {
        return [
            ['name' => 'a', 'type' => 'b', 'attributes' => 'c'],
            ['name' => 'd', 'type' => 'e', 'attributes' => 'f'],
        ];
    }

    protected function getIndices(): array
    {
        return [
            'foobar',
            'testing',
        ];
    }
}

final class WPDBTableTest extends BaseTestCase
{
    public function testDDL(): void
    {
        $expected = <<<STR
`a` b c,
`d` e f,
foobar,
testing
STR;
        $wpdb = $this->getMockBuilder('\wpdb')->getMock();
        $table = new TestDBTable('test_table', $wpdb);
        $this->assertEquals($expected, $this->invokeMethod($table, 'getDDL'));
    }
}
