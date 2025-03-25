<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 13/04/21
 * Time: 17:40
 */

namespace App\Http\Data\Utils;


class FilterUtil
{

    public static function getFieldsLikeRequired($fields)
    {
        $nArra = [];
        array_map(function ($item) use (&$nArra) {
            $nArra[$item] = 'required';
        }, $fields);
        return $nArra;
    }

    // pass string  like to -> "12,11,1" and return -> "12","11","1"
    public static function implodeSqlSentence($field)
    {
        return implode(',', array_unique(explode(",", $field)));
    }

}