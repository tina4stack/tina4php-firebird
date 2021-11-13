<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

use PHPUnit\Framework\TestCase;

require_once "./Tina4/DataFirebird.php";

class DataFirebirdTest extends TestCase
{
    public $connectionString;
    public $DBA;

    final public function setUp(): void
    {
        $this->connectionString = "localhost/33050:/firebird/data/TINA4.FDB";
              $this->DBA = new \Tina4\DataFirebird($this->connectionString, "sysdba", "pass1234");
    }

    final public function testOpen(): void
    {
        $this->assertNotEmpty($this->DBA);
    }

    final public function testTableExists() : void
    {
        $exists = $this->DBA->tableExists("RDB\$DATABASE");
        $this->assertIsBool($exists, "Not working");
        $exists = $this->DBA->tableExists("user_one");
        $this->assertEquals(false, $exists, "Not working false table check");
    }

    final public function testDropCreateTable() : void
    {
        if ($this->DBA->tableExists("testing")) {
            $error = $this->DBA->exec("drop table testing");
        }

        $this->DBA->commit();

        $error = $this->DBA->exec("create table testing(id integer default 0, primary key(id))");

        $this->DBA->commit();

        $exists = $this->DBA->tableExists("testing");

        $this->assertEquals(true, $exists, "Not working false table check");
    }

    final public function testGetDatabase(): void
    {
        $database = $this->DBA->getDatabase();
        $this->assertArrayHasKey("testing", $database);
    }

    final public function testRead(): void
    {
        $this->DBA->exec("insert into testing (id) values (?)", 1);

        $this->DBA->exec("insert into testing (id) values (2)");

        $records = $this->DBA->fetch("select * from testing")->asArray();

        $this->assertCount(2, $records, "Records were not 2");

        $result = $this->DBA->exec("insert into testing (id) values (3) returning id");
    }
}
