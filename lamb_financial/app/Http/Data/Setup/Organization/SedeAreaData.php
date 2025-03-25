<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/24/19
 * Time: 4:59 PM
 */

namespace App\Http\Data\Setup\Organization;


use Illuminate\Support\Facades\DB;

class SedeAreaData
{
    public static function addSedeArea($data)
    {
//        dd('priva', $data);
        $result = DB::table('ORG_SEDE_AREA')->insert($data);
        return $result;
    }

    public static function getSedeArea($idSedeArea)
    {
//        $query = DB::table('ORG_SEDE_AREA')->select()->where('id_sedearea', $idSedeArea)->get(['id_depto.nombre'])->toArray();
        $query = "SELECT
                  ORG_SEDE_AREA.ESTADO,
                  ORG_SEDE_AREA.ID_AREA,
                  ORG_SEDE_AREA.ID_DEPTO,
                  ORG_SEDE_AREA.ID_SEDE,
                  ORG_SEDE_AREA.ID_SEDEAREA,
                  CONTA_ENTIDAD_DEPTO.NOMBRE departamento
                FROM ORG_SEDE_AREA
                  INNER JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO WHERE ORG_SEDE_AREA.ID_SEDEAREA = $idSedeArea";
//        $query = DB::table('ORG_SEDE_AREA')->select($query)->toArray();
        $result = DB::connection('oracle')->select($query);
        return last($result);
    }

    public static function updateSedeArea($idSedeArea, $data)
    {
        $result = DB::table('ORG_SEDE_AREA')->where('id_sedearea', $idSedeArea)->update($data);
        return $result;
    }

    public static function deleteSedeArea($idSedeArea)
    {
        $result = false;
        $sedeArea = DB::table('ORG_SEDE_AREA')->where('id_sedearea', $idSedeArea)->first();
        if ($sedeArea) {
            $idAreasResponsable = DB::table('ORG_AREA_RESPONSABLE')->where('id_sedearea', $sedeArea->id_sedearea)->pluck('id_responsable')->toArray();
            if ($idAreasResponsable) {
                $resultsResp = DB::table('ORG_AREA_RESPONSABLE')->whereIn('id_responsable', $idAreasResponsable)->delete();
            }
            $result = DB::table('ORG_SEDE_AREA')->where('id_sedearea', $sedeArea->id_sedearea)->delete();
        }
        return $result;
    }

    public static function findSedeArea($idEntidad, $textSearch)
    {
        $querys = "SELECT 
                    A.ID_SEDEAREA,
                    D.ID_DEPTO || '. ' || D.NOMBRE || ', ' || B.NOMBRE || ' - ' || C.NOMBRE AS AREA
                    FROM ORG_SEDE_AREA A INNER JOIN ORG_AREA B ON B.ID_AREA = A.ID_AREA
                    INNER JOIN ORG_SEDE C ON C.ID_SEDE = A.ID_SEDE
                    INNER JOIN CONTA_ENTIDAD_DEPTO D ON D.ID_DEPTO = A.ID_DEPTO
                    WHERE A.ID_ENTIDAD = $idEntidad
                        AND D.ID_ENTIDAD = $idEntidad
                        AND B.ESTADO = 1
                        AND A.ESTADO = 1
                        AND D.ES_ACTIVO = 1
                        AND upper(B.NOMBRE || '' || D.NOMBRE || '' || D.ID_DEPTO || '' || C.NOMBRE) LIKE upper('%$textSearch%')
              ";
        $oQuery = DB::connection('oracle')->select($querys);
        return $oQuery;
    }

}