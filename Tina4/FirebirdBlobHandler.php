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
class FirebirdBlobHandler extends DataConnection
{
    /**
     * Decodes the blobs for a returned record
     * @param $record
     * @return mixed
     */
    final public function decodeBlobs($record)
    {
        foreach ($record as $key => $value) {
            if (strpos($value, "0x") === 0) {
                //Get the blob information
                $blobData = ibase_blob_info($this->getDbh(), $value);
                //Get a handle to the blob
                $blobHandle = ibase_blob_open($this->getDbh(), $value);
                //Get the blob contents
                $content = ibase_blob_get($blobHandle, $blobData[0]);
                ibase_blob_close($blobHandle);
                $record[$key] = $content;
            }
        }

        return $record;
    }
}
