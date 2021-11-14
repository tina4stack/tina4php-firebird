<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * Decoder for BLR encoded data
 */
class FirebirdBlrDecoder
{
    /**
     * Decodes firebird BLR format for defaults, still will need lots of work
     * @param string $string
     * @return string
     */
    final public function decodeBlr(string $string): string
    {
        $result = "";

        $blrString = $this->readBlr($string);

        if (!empty($blrString)) {
            for ($i = 7, $iMax = count($blrString) - 1; $i < $iMax; $i++) {
                $decode = $blrString[$i];
                if (is_numeric($decode)) {
                    $decode = chr($blrString[$i]);
                } else {
                    $decode = substr($blrString[$i], 1, -1);
                }
                $result .= $decode;
            }
        }

        return $result;
    }

    /**
     * Reads a Blr string
     * blr_version5,blr_literal, blr_text2, 0,0, 10,0, '0','1',47,'0','1',47,'1','9','0','0',blr_eoc
     * @param string $string
     * @return array
     */
    private function readBlr(string $string) : array
    {
        $blrString = explode(",", $string);
        foreach ($blrString as $id => $value) {
            $blrString[$id] = trim($value);
        }

        return $blrString;
    }
}