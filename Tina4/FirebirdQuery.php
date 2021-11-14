<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

class FirebirdQuery
{
    /**
     * @var Database connection to Firebird database
     */
    private $connection;

    /**
     * Constructor for Firebird Query
     * @param Database $connection
     */
    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $sql
     * @param int $noOfRecords
     * @param int $offSet
     * @param array $fieldMapping
     * @return DataResult|null
     */
    final public function query($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        $params = [];
        if (is_array($sql)) {
            $initialSQL = $sql[0];
            $params = array_merge([$this->connection->dbh], $sql);
        } else {
            $initialSQL = $sql;
        }

        if (stripos($initialSQL, "returning") === false) {
            //inject in the limits for the select - in Firebird select first x skip y
            $limit = " first {$noOfRecords} skip {$offSet} ";
            $posSelect = stripos($initialSQL, "select") + strlen("select");
            $sql = substr($initialSQL, 0, $posSelect) . $limit . substr($initialSQL, $posSelect);
            //select first 10 skip 10 from table
        }

        if (is_array($sql)) {
            $recordCursor = ibase_query(...$params);
        } else {
            $recordCursor = ibase_query($this->connection->dbh, $sql);
        }

        $records = null;
        while ($record = ibase_fetch_assoc($recordCursor)) {
            $record = (new FirebirdBlobHandler($this->connection))->decodeBlobs($record);

            $records[] = (new DataRecord(
                $record,
                $fieldMapping,
                $this->connection->getDefaultDatabaseDateFormat(),
                $this->connection->dateFormat
            ));
        }

        //populate the fields
        $fields = [];
        if (is_array($records) && count($records) > 0) {
            if (stripos($initialSQL, "returning") === false) {
                if (!empty($records)) {
                    $record = $records[0];
                    $fid = 0;
                    foreach ($record as $field) {
                        $fieldInfo = ibase_field_info($recordCursor, $fid);

                        $fields[] = (new DataField(
                            $fid,
                            $fieldInfo["name"],
                            $fieldInfo["alias"],
                            $fieldInfo["type"],
                            $fieldInfo["length"]
                        ));

                        $fid++;
                    }
                }

                $sqlCount = "select count(*) as COUNT_RECORDS from ($initialSQL)";
                $recordCount = ibase_query($this->connection->dbh, $sqlCount);
                $resultCount = ibase_fetch_assoc($recordCount);
            } else {
                $resultCount["COUNT_RECORDS"] = count($records); //used for insert into or update
            }
        } else {
            $resultCount["COUNT_RECORDS"] = 0;
        }



        $error = $this->connection->error();

        return (new DataResult($records, $fields, $resultCount["COUNT_RECORDS"], $offSet, $error));
    }
}
