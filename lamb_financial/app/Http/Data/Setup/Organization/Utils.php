<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/24/19
 * Time: 6:05 PM
 */

namespace App\Http\Data\Setup\Organization;


use Illuminate\Support\Facades\DB;

class Utils
{
    public static function generateProgresiveNumericalId($tabla, $campo)
    {
        $valor = DB::table($tabla)->max($campo);
        return $valor + 1;
    }

}