<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

class FirebirdExec
{
    private $connection;

    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute a Firebird Query
     * @param $params
     * @param $tranId
     * @return DataResult|void|null
     */
    final public function exec($params, $tranId): void
    {
        if (!empty($tranId)) {
            $preparedQuery = ibase_prepare($this->connection->dbh, $tranId, $params[0]);
        } else {
            $preparedQuery = ibase_prepare($this->connection->dbh, $params[0]);
        }

        if (!empty($preparedQuery)) {
            $params[0] = $preparedQuery;
            ibase_execute(...$params);
        }
    }
}
