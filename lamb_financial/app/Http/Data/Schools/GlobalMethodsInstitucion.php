<?php
namespace App\Http\Data\Schools;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class GlobalMethodsInstitucion extends Controller{    
    public static function datosInstitucionByIdDepto($id_entidad, $id_depto){  

        $row = DB::connection('jose')->table('SCHOOL_INSTITUCION')
        ->select(
                'SCHOOL_INSTITUCION.ID_INSTITUCION',
                'SCHOOL_INSTITUCION.NOMBRE',
                'ES_TALLER_ELECTIVO'
        )
        ->where('SCHOOL_INSTITUCION.ID_CAMPO',$id_entidad)
        ->where('SCHOOL_INSTITUCION.ID_DEPTO',$id_depto)
        ->first();
        return $row;
    }
    
}