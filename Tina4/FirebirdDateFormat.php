<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * Sets up the date constants for the Firebird database
 */
class FirebirdDateFormat
{
    /**
     * Sets up the constants for the database format
     * @param string $databaseDateFormat
     */
    public function __construct(string $databaseDateFormat)
    {
        $dateFormat = str_replace(array("Y", "d", "m"), array("%Y", "%d", "%m"), $databaseDateFormat);

        //Set the returning format to something we can expect to transform
        ini_set(
            "ibase.dateformat",
            $dateFormat
        );
        ini_set(
            "ibase.timestampformat",
            $dateFormat. " %H:%M:%S"
        );
    }

}