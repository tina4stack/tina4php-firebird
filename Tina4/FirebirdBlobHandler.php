<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * Fetches blob data from the database
 */
class FirebirdBlobHandler
{
    /**
     * @var Database connection to Firebird database
     */
    private $connection;

    /**
     * Constructor for Firebird Blob Handler
     * @param Database $connection
     */
    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Decodes the blobs for a returned record
     * @param $record
     */
    final public function decodeBlobs($record)
    {
        foreach ($record as $key => $value) {
            if (strpos($value, "0x") === 0) {
                //Get the blob information
                $blobData = ibase_blob_info($this->connection->dbh, $value);
                //Get a handle to the blob
                $blobHandle = ibase_blob_open($this->connection->dbh, $value);
                //Get the blob contents
                $content = ibase_blob_get($blobHandle, $blobData[0]);
                ibase_blob_close($blobHandle);
                $record[$key] = $content;
            }
        }

        return $record;
    }
}
