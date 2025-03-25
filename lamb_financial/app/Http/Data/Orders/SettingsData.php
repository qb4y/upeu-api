<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/22/19
 * Time: 5:08 PM
 */

namespace App\Http\Data\Orders;
use Illuminate\Support\Facades\DB;

class SettingsData
{
    public static function listAreas($id_entidad, $department_id)
    {
//        dd($department_id);
        $qry = "";
        $qry = "SELECT DISTINCT
                  coed.ID_DEPTO,
                  orar.NOMBRE                                                       area,
                  coed.nombre,
                  orsa.ID_SEDEAREA,
                  (SELECT pedar.id_formulario
                   FROM PEDIDO_AREA pedar
                   WHERE pedar.ID_SEDEAREA = orsa.ID_SEDEAREA)                      id_formulario,
                  (SELECT count(ID_SEDEAREA)
                   FROM PEDIDO_AREA pedar
                   WHERE pedar.ID_SEDEAREA = orsa.ID_SEDEAREA AND pedar.ACTIVO = 1) asignado,
                  (SELECT listagg(peat.ID_TIPOPEDIDO, ',') WITHIN GROUP (
                             ORDER BY peat.ID_TIPOPEDIDO)
                    FROM PEDIDO_AREA_TIPO peat
                    WHERE peat.ID_SEDEAREA = orsa.ID_SEDEAREA) AS id_tipo_area
                FROM ORG_AREA orar, CONTA_ENTIDAD_DEPTO coed, ORG_SEDE_AREA orsa
                WHERE
                  orar.ID_AREA = orsa.ID_AREA
                  AND coed.ID_DEPTO = orsa.ID_DEPTO
                  AND coed.ES_ACTIVO = 1
                  AND orsa.ID_ENTIDAD = $id_entidad";
        if($department_id || $department_id == '0'){
            $starVal = $department_id.'0000000';
            $endVal =  $department_id.'9999999';
            $qry = $qry."AND REGEXP_LIKE(coed.ID_DEPTO, '^[[:digit:]]{8}$')
                        AND coed.ID_DEPTO BETWEEN '".$starVal."' AND '".$endVal."' ";
                         //AND to_number(coed.ID_DEPTO) >= to_number($starVal)
                         //AND to_number(coed.ID_DEPTO) <= to_number($endVal)";
        }
        $qry = $qry."ORDER BY ORAR.nombre ";
        $result = DB::connection('oracle')->select($qry);
        return $result;
    }
    public static function listAreasSearch($id_entidad, $department){
        $qry = "SELECT
                  coed.ID_DEPTO,
                  orar.NOMBRE                                                       area,
                  coed.nombre,
                  orsa.ID_SEDEAREA,
                  (SELECT pedar.id_formulario
                   FROM PEDIDO_AREA pedar
                   WHERE pedar.ID_SEDEAREA = orsa.ID_SEDEAREA)                      id_formulario,
                  (SELECT count(ID_SEDEAREA)
                   FROM PEDIDO_AREA pedar
                   WHERE pedar.ID_SEDEAREA = orsa.ID_SEDEAREA AND pedar.ACTIVO = 1) asignado,
                  (SELECT listagg(peat.ID_TIPOPEDIDO, ',') WITHIN GROUP (
                             ORDER BY peat.ID_TIPOPEDIDO)
                    FROM PEDIDO_AREA_TIPO peat
                    WHERE peat.ID_SEDEAREA = orsa.ID_SEDEAREA) AS id_tipo_area
                FROM ORG_AREA orar, CONTA_ENTIDAD_DEPTO coed, ORG_SEDE_AREA orsa
                WHERE
                  orar.ID_AREA = orsa.ID_AREA
                  AND coed.ID_DEPTO = orsa.ID_DEPTO
                  AND coed.ES_ACTIVO = 1
                  AND orsa.ID_ENTIDAD = $id_entidad 
                  AND (coed.ID_DEPTO LIKE '%".$department."%' OR UPPER(coed.nombre) LIKE UPPER('%".$department."%')) ";
        $result = DB::connection('oracle')->select($qry);
        return $result;
    }

}