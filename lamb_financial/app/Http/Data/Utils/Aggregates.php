<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 3/06/21
 * Time: 08:51
 */

namespace App\Http\Data\Utils;


class Aggregates
{
    public static function getSumListObject($fields, $list)
    {
        $result = array();
        foreach ($fields as $value) {
            $rest = round(self::sumArray($value, $list), 2);
            $result[$value] = $rest;
        }
        return $result;
    }

    public static function sumArray($field, $list)
    {
        return array_reduce($list, function ($a, $b) use ($field) {
            return $a + floatval($b->$field);
        }, 0);
    }

}