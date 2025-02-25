<?php

namespace App\Models\Salsify;

class Salsify
{

    /**
     * Given a string, return array of UOM and QTY values. Keys are configurable via $params.
     *
     * @param $str
     * @param array $params
     * @return array
     */
    public static function stringToQtyUom($str, $params=[])
    {
        $params = array_merge([
            'uom_field' => 'uom',
            'qty_field' => 'qty',
        ], $params);

        return [
            $params['uom_field'] => self::raw2uom($str),
            $params['qty_field'] => self::raw2uoq($str),
        ];
    }

    /**
     * Extract Unit of Measure from string.
     * 12g returns g
     *
     * @param $str
     * @return string
     */
    public static function raw2uom($str){
        return trim(preg_replace('/^[0-9\.]+/', '', $str));
    }

    /**
     * Extract Quantity of Measure from string
     * 12.2g returns 12.2
     *
     * @param $str
     * @return string
     */
    public static function raw2uoq($str){
        return trim(preg_replace('/[^0-9\.]+$/', '', $str));
    }

    public static function mapUomToReadable($str, $def=null)
    {
        $str = trim($str);
        $map = config('middleware.units_of_measure', []);
        if(!array_key_exists($str, $map))
        {
            return $def;
        }
        return $map[$str];
    }

}