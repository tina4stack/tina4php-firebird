<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * DataFirebird
 * The implementation for the firebird database engine
 * @package Tina4
 */
class DataFirebird implements DataBase
{
    use DataBaseCore;

    /**
     * @var null database metadata
     */
    private $databaseMetaData;

    /**
     * Open a Firebird database connection
     * @param bool $persistent
     * @throws \Exception
     */
    final public function open(bool $persistent = true): void
    {
        if (!function_exists("ibase_pconnect")) {
            throw new \Exception("Firebird extension for PHP needs to be installed");
        }

        (new FirebirdDateFormat($this->getDefaultDatabaseDateFormat()));

        $databasePath = $this->hostName . "/" . $this->port . ":" . $this->databaseName;

        $this->dbh = (new FirebirdConnection(
            $databasePath,
            $this->username,
            $this->password,
            $persistent
        ))->getConnection();
    }

    /**
     * Gets the default database date format
     * @return mixed|string
     */
    final public function getDefaultDatabaseDateFormat(): string
    {
        return "m/d/Y";
    }

    /**
     * Close a Firebird database connection
     */
    final public function close(): void
    {
        ibase_close($this->dbh);
    }

    /**
     * Execute a firebird query, format is query followed by params or variables
     * @return DataError|bool
     */
    final public function exec()
    {
        $params = $this->parseParams(func_get_args());

        if (isset($params[0]) && stripos($params[0], "returning") !== false) {
            return $this->fetch($params);
        }

        $tranId = $params["tranId"];
        $params = $params["params"];

        (new FirebirdExec($this))->exec($params, $tranId);

        return $this->error();
    }

    /**
     * Firebird implementation of fetch
     * @param string|array $sql
     * @param int $noOfRecords
     * @param int $offSet
     * @param array $fieldMapping
     * @return bool|DataResult
     */
    final public function fetch($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        return (new FirebirdQuery($this))->query($sql, $noOfRecords, $offSet, $fieldMapping);
    }

    /**
     * Returns an error
     * @return DataError
     */
    final public function error(): DataError
    {
        $errorCode = ibase_errcode();
        $errorMessage = ibase_errmsg();

        return (new DataError($errorCode, $errorMessage));
    }

    /**
     * Commit
     * @param null $transactionId
     * @return bool
     */
    final public function commit($transactionId = null)
    {
        if (!empty($transactionId)) {
            return ibase_commit($transactionId);
        }

        return ibase_commit($this->dbh);
    }

    /**
     * Rollback
     * @param null $transactionId
     * @return bool
     */
    final public function rollback($transactionId = null)
    {
        if (!empty($transactionId)) {
            return ibase_rollback($transactionId);
        }

        return ibase_rollback($this->dbh);
    }

    /**
     * Auto commit on for Firebird
     * @param bool $onState
     * @return bool|void
     */
    final public function autoCommit(bool $onState = false): void
    {
        //Firebird has commit off by default
    }

    /**
     * Start Transaction
     * @return false|int|resource
     */
    final public function startTransaction()
    {
        return ibase_trans(IBASE_COMMITTED + IBASE_NOWAIT, $this->dbh);
    }

    /**
     * Check if table exists
     * @param string $tableName
     * @return bool
     */
    final public function tableExists(string $tableName): bool
    {
        if (!empty($tableName)) {
            // table name must be in upper case
            $tableName = strtoupper($tableName);
            $exists = $this->fetch("SELECT 1 AS CONSTANT 
                                          FROM RDB\$RELATIONS 
                                         WHERE RDB\$RELATION_NAME = '$tableName'");

            return !empty($exists->records());
        }

        return false;
    }

    /**
     * Get the last id
     * @return string
     */
    final public function getLastId(): string
    {
        return "";
    }

    /**
     * Get the database metadata
     * @return array|mixed
     */
    final public function getDatabase(): array
    {
        if (!empty($this->databaseMetaData)) {
            return $this->databaseMetaData;
        }

        $this->databaseMetaData = (new FirebirdMetaData($this))->getDatabaseMetaData();

        return $this->databaseMetaData;
    }

    /**
     * Gets the default database port
     * @return int|mixed
     */
    final public function getDefaultDatabasePort(): int
    {
        return 3050;
    }

    /**
     * Specific to firebird generators
     * @param string $generatorName
     * @param int $increment
     * @return mixed
     */
    final public function getGeneratorId(string $generatorName, int $increment = 1)
    {
        return ibase_gen_id(strtoupper($generatorName), $increment, $this->dbh);
    }

    /**
     * Is it a No SQL database?
     * @return bool
     */
    final public function isNoSQL(): bool
    {
        return false;
    }
}
