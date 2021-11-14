<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * FirebirdConnection
 * Establishes a connection to a Firebird database
 */
class FirebirdConnection
{
    private $connection;
    /**
     * Creates a Firebird Database Connection
     * @param string $databasePath hostname/port:path
     * @param string $username database username
     * @param string $password password of the user
     * @param boolean $persistent true or false
     */
    public function __construct(string $databasePath, string $username, string $password, bool $persistent)
    {
        if ($persistent) {
            $this->connection = ibase_pconnect($databasePath, $username, $password);
        } else {
            $this->connection = ibase_connect($databasePath, $username, $password);
        }
    }

    /**
     * Returns a databse connection or false if failed
     * @return false|resource
     */
    final public function getConnection()
    {
        return $this->connection;
    }

}