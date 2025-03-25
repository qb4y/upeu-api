<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 08/01/20
 * Time: 03:21 PM
 */


namespace App\Http\Data\Financial;

use Illuminate\Support\Facades\DB;

class TeachingLevelData {

    protected static function tipos_nivel_ensenanza() {
    return DB::table('DAVID.TIPO_NIVEL_ENSENANZA a')
        ->select(
            'a.ID_NIVEL_ENSENANZA',
            'a.NOMBRE',
            'a.CODIGO'
        )
        ->get();
}

    public static function index(){
        return self::tipos_nivel_ensenanza();
    }

}