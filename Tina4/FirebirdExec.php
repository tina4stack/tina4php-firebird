<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * Executes queries on a Firebird database
 */
class FirebirdExec extends DataConnection implements DataBaseExec
{
    /**
     * Execute a Firebird Query Statement which ordinarily does not retrieve results
     * @param $params
     * @param $tranId
     * @return DataResult|void|null
     */
    final public function exec($params, $tranId): void
    {
        if (!empty($tranId)) {
            $preparedQuery = ibase_prepare($this->getDbh(), $tranId, $params[0]);
        } else {
            $preparedQuery = ibase_prepare($this->getDbh(), $params[0]);
        }

        if (!empty($preparedQuery)) {
            $params[0] = $preparedQuery;
            ibase_execute(...$params);
        }
    }
}
