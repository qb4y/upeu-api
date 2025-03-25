<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/11/19
 * Time: 4:17 PM
 */

namespace App\Http\Data\Setup\Organization;
use Illuminate\Support\Facades\DB;

class SedeData
{
    public static function listSede()
    {
        $getYear = DB::table('ORG_SEDE')->select('*')->get();
        return $getYear;
    }
}