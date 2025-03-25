<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/11/19
 * Time: 4:06 PM
 */

namespace App\Http\Data\Setup\Organization;
use Illuminate\Support\Facades\DB;

class NivelGestionData
{
    public static function listNivelGestion()
    {
        $getYear = DB::table('ORG_NIVEL_GESTION')->select('*')->get();
        return $getYear;
    }
}