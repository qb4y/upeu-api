<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 2/06/21
 * Time: 18:31
 */

namespace App\Http\Data\FinancesStudent\reports;


use App\Http\Data\Utils\FilterUtil;
use Illuminate\Support\Facades\DB;

class CollectionControlData
{

    public static function getCollectionControlData($params)
    {
        return self::getCollectionControl($params);
    }

    public static function getCollectionControl($params)
    {
        $currentYear = $params['currentYear'];
        $lastYear = $params['lastYear'];
        $currentProperty = $params['currentProperty'];
        $lastProperty = $params['lastProperty'];
        $meta = intval($params['meta']);
        $inParam = $params['lastYear'] . ',' . $params['currentYear'];

        return collect(DB::select("SELECT *
            FROM (
                SELECT A.ID_ANHO,
                       VPP.ID_NIVEL_ENSENANZA NIVEL_ENS,
                       TNE.NOMBRE             NIVEL_ENSENANZA,
                       -- APE.MODALIDAD_ESTUDIO,
                       VPP.NOM_FACULTAD       FACULTAD,
                       SUM(A.IMPORTE) AS      IMPORTE
                FROM CAJA_DEPOSITO A
                         LEFT JOIN PERSONA B ON A.ID_CLIENTE = B.ID_PERSONA
                         LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON A.ID_CLIENTE = C.ID_PERSONA
                         LEFT JOIN DAVID.VW_PLAN_PROGRAMA VPP ON VPP.ID_PLAN_PROGRAMA = DAVID.FT_PLANP_ALUMNO_ID(A.ID_CLIENTE)
                         LEFT JOIN DAVID.TIPO_NIVEL_ENSENANZA TNE ON TNE.ID_NIVEL_ENSENANZA = VPP.ID_NIVEL_ENSENANZA
                         LEFT JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO = VPP.ID_PROGRAMA_ESTUDIO
                WHERE A.ID_ENTIDAD = ?
                  AND A.ID_DEPTO = ?
                  AND A.ID_ANHO IN (" . FilterUtil::implodeSqlSentence($inParam) . ")
                  AND A.ID_MES = ?
                GROUP BY A.ID_ANHO, VPP.ID_NIVEL_ENSENANZA, TNE.NOMBRE, --APE.MODALIDAD_ESTUDIO, 
                VPP.NOM_FACULTAD
            ) X
                PIVOT (
                sum(IMPORTE)
                FOR ID_ANHO IN ($lastYear as $lastProperty,$currentYear as $currentProperty)
                )
            ORDER BY NIVEL_ENS, FACULTAD",
            [
                $params['id_entidad'],
                $params['id_depto'],
                $params['id_mes'],
            ]))
            ->map(function ($item) use ($meta) {
                $item->meta = round((($meta / 100) * intval($item->last_year)) + intval($item->last_year), 2);
                $item->ga = $item->meta > 0 ? round((intval($item->current_year) / $item->meta) * 100, 2) : 0;
                return $item;
            });
    }

    public static function getCollectionControlDepart($params)
    {
        $currentYear = $params['currentYear'];
        $lastYear = $params['lastYear'];
        $currentProperty = $params['currentProperty'];
        $lastProperty = $params['lastProperty'];
        $meta = intval($params['meta']);
        $inParam = $params['lastYear'] . ',' . $params['currentYear'];
        return collect(DB::select("SELECT *
            FROM (
                SELECT A.ID_ANHO,
                       VPP.ID_NIVEL_ENSENANZA NIVEL_ENS,
                       TNE.NOMBRE             NIVEL_ENSENANZA,
                       APE.MODALIDAD_ESTUDIO,
                       VPP.NOM_FACULTAD       FACULTAD,
                       VPP.NOM_ESCUELA        ESCUELA,
            --B.NOMBRE,B.PATERNO,B.MATERNO,C.CODIGO,
                       SUM(A.IMPORTE) AS      IMPORTE
                FROM CAJA_DEPOSITO A
                         LEFT JOIN PERSONA B ON A.ID_CLIENTE = B.ID_PERSONA
                         LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON A.ID_CLIENTE = C.ID_PERSONA
                         LEFT JOIN DAVID.VW_PLAN_PROGRAMA VPP ON VPP.ID_PLAN_PROGRAMA = DAVID.FT_PLANP_ALUMNO_ID(A.ID_CLIENTE)
                         LEFT JOIN DAVID.TIPO_NIVEL_ENSENANZA TNE ON TNE.ID_NIVEL_ENSENANZA = VPP.ID_NIVEL_ENSENANZA
                         LEFT JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO = VPP.ID_PROGRAMA_ESTUDIO
                WHERE A.ID_ENTIDAD = ?
                  AND A.ID_DEPTO = ?
                  AND A.ID_ANHO IN (" . FilterUtil::implodeSqlSentence($inParam) . ")
                  AND A.ID_MES = ?
                GROUP BY A.ID_ANHO, VPP.ID_NIVEL_ENSENANZA, TNE.NOMBRE, APE.MODALIDAD_ESTUDIO, VPP.NOM_FACULTAD, VPP.NOM_ESCUELA--,B.NOMBRE,B.PATERNO,B.MATERNO,C.CODIGO
            ) X
                PIVOT (
                sum(IMPORTE)
                FOR ID_ANHO IN ($lastYear as $lastProperty,$currentYear as $currentProperty)
                )
            ORDER BY NIVEL_ENS, FACULTAD",
            [
                $params['id_entidad'],
                $params['id_depto'],
                $params['id_mes'],
            ]))
            ->map(function ($item) use ($meta) {
                $item->meta = round((($meta / 100) * intval($item->last_year)) + intval($item->last_year), 2);
                $item->porcentaje = intval($item->last_year) > 0 ? round((intval($item->current_year) / intval($item->last_year)) * 100, 2) - 100 : 0;
                $item->ga = $item->meta > 0 ? round((intval($item->current_year) / $item->meta) * 100, 2) : 0;
                return $item;
            });
    }
}