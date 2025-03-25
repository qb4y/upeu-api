<?php


namespace App\Http\Data\FinancesStudent;


use Illuminate\Support\Facades\DB;

class ReportData
{
        public static function getStudentBalance($params) {
                $ne = '';
                if (array_key_exists('id_nivel_ensenanza', $params) && $params->id_nivel_ensenanza != '') {
                    $ne = 'where DAVID.FT_GET_ID_NIVEL_ENSENANZA(ID_CLIENTE) = '.$params->id_nivel_ensenanza;
            
                    if (array_key_exists('codigo', $params) && $params->codigo != '') {
                        $ne = $ne." and exists (SELECT X.CODIGO FROM MOISES.PERSONA_NATURAL_ALUMNO X WHERE X.ID_PERSONA = ID_CLIENTE and x.CODIGO = '".$params->codigo."')";
                    }
                } else {
                    if (array_key_exists('codigo', $params) && $params->codigo != '') {
                        $ne = "where exists (SELECT X.CODIGO FROM MOISES.PERSONA_NATURAL_ALUMNO X WHERE X.ID_PERSONA = ID_CLIENTE and x.CODIGO = '".$params->codigo."')";
                    }
                }
            
                $q = "
                SELECT
                    (SELECT X.CODIGO FROM MOISES.PERSONA_NATURAL_ALUMNO X WHERE X.ID_PERSONA = A.ID_CLIENTE) AS CODIGO,
                    (SELECT PATERNO || ' ' || MATERNO || ' ' || NOMBRE FROM MOISES.PERSONA X WHERE X.ID_PERSONA = A.ID_CLIENTE) AS ALUMNO,
                    SUM(IMPORTE) AS SALDOS,
                    DAVID.FT_FACULTAD_ALUMNO(ID_CLIENTE) AS FACULTAD,
                    DAVID.FT_ESCUELA_ALUMNO(ID_CLIENTE) AS EP,
                    DAVID.FT_GET_ID_NIVEL_ENSENANZA(ID_CLIENTE) AS ID_NIVEL,
                    DAVID.FT_MAX_SEMESTRE_ALUMNO(ID_CLIENTE, DAVID.FT_PLANP_ALUMNO_ID(ID_CLIENTE)) AS SEMESTRE,
                    DAVID.FT_CALCULAR_CICLO(DAVID.FT_MAX_SEMESTRE_ALUMNO(ID_CLIENTE, DAVID.FT_PLANP_ALUMNO_ID(ID_CLIENTE)), ID_CLIENTE, DAVID.FT_GET_ID_NIVEL_ENSENANZA(ID_CLIENTE)) AS CICLO,
                    (SELECT X.CORREO FROM MOISES.PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE) AS CORREO,
                    (SELECT X.CELULAR FROM MOISES.PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE) AS CELULAR,
                    (SELECT NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO 
                     WHERE ID_PERSONA = A.ID_CLIENTE 
                     AND ID_TIPODOCUMENTO = 1 
                     AND ES_ACTIVO = 1 
                     AND ROWNUM = 1) AS DNI
                FROM (
                    SELECT
                        ID_CLIENTE,
                        TOTAL AS IMPORTE
                    FROM ELISEO.VW_SALES_MOV
                    WHERE ID_ENTIDAD = :id_entidad_p
                    AND ID_DEPTO = :id_depto_p
                    AND ID_ANHO = :id_anho_p
                    AND ID_MES <= :id_mes_p
                    AND ID_TIPOVENTA IN (1,2,3,4)
                    UNION ALL
                    SELECT
                        ID_CLIENTE,
                        SUM(IMPORTE) * DECODE(SIGN(SUM(IMPORTE)), 1, -1, 0) AS IMPORTE
                    FROM ELISEO.VW_SALES_ADVANCES
                    WHERE ID_ENTIDAD = :id_entidad_p
                    AND ID_DEPTO = :id_depto_p
                    AND ID_ANHO = :id_anho_p
                    AND ID_MES <= :id_mes_p
                    GROUP BY ID_CLIENTE
                ) A ".$ne."
                GROUP BY ID_CLIENTE
                HAVING SUM(IMPORTE) <> 0";
            
                $data = DB::select($q, [
                    'id_entidad_p' => $params->id_entidad,
                    'id_depto_p' => $params->id_depto,
                    'id_anho_p' => $params->id_anho,
                    'id_mes_p' => $params->id_mes
                ]);
            
                return collect($data)->sortBy('facultad')->sortBy('ep')->sortBy('alumno')->values();
        }            


    static function getSummaryBalanceFaculty($params) {


        $ne = '';
        if (array_key_exists('id_nivel_ensenanza', $params) && $params->id_nivel_ensenanza != '') {
            $ne = 'where DAVID.FT_GET_ID_NIVEL_ENSENANZA(ID_CLIENTE) = '.$params->id_nivel_ensenanza;
        }


        $q = "SELECT
        DAVID.FT_FACULTAD_ALUMNO(ID_CLIENTE) AS FACULTAD,
        SUM(IMPORTE) 					 AS SALDOS
FROM (
        SELECT
                ID_CLIENTE,
                TOTAL AS IMPORTE
        FROM ELISEO.VW_SALES_MOV
        WHERE ID_ENTIDAD = :id_entidad_p
        AND ID_DEPTO = :id_depto_p
        AND ID_ANHO = :id_anho_p
        AND ID_MES <= :id_mes_p
        AND ID_TIPOVENTA IN (1,2,3,4)
        UNION ALL
        SELECT
                ID_CLIENTE,
                SUM(IMPORTE)*DECODE(SIGN(SUM(IMPORTE)),1,-1,0) AS IMPORTE
        FROM ELISEO.VW_SALES_ADVANCES
        WHERE ID_ENTIDAD = :id_entidad_p
        AND ID_DEPTO = :id_depto_p
        AND ID_ANHO = :id_anho_p
        AND ID_MES <= :id_mes_p
        GROUP BY ID_CLIENTE
) A ".$ne."
GROUP BY DAVID.FT_FACULTAD_ALUMNO(ID_CLIENTE)
HAVING SUM(IMPORTE)  <> 0";

        return DB::select($q, [
            'id_entidad_p' => $params->id_entidad,
            'id_depto_p' => $params->id_depto,
            'id_anho_p' => $params->id_anho,
            'id_mes_p' => $params->id_mes
        ]);

    }


    static function getSummaryBalance($params) {


        $ne = '';
        if (array_key_exists('id_nivel_ensenanza', $params) && $params->id_nivel_ensenanza != '') {
            $ne = 'where DAVID.FT_GET_ID_NIVEL_ENSENANZA(ID_CLIENTE) = '.$params->id_nivel_ensenanza;
        }


        $q = "SELECT  SUM(SALDOS_TOTAL) as SALDOS_TOTAL,SUM(SALDO_A_FAVOR) as SALDO_A_FAVOR,SUM(SALDO_DEUDOR) as SALDO_DEUDOR FROM (
SELECT 
        ID_CLIENTE,
        SUM(IMPORTE) 					 AS SALDOS_TOTAL,
        (CASE WHEN SUM(IMPORTE) < 0 THEN SUM(IMPORTE) ELSE 0 END) AS SALDO_A_FAVOR,
        (CASE WHEN SUM(IMPORTE) > 0 THEN SUM(IMPORTE) ELSE 0 END) AS SALDO_DEUDOR
FROM (
        SELECT
                ID_CLIENTE,
                TOTAL AS IMPORTE
        FROM VW_SALES_MOV
        WHERE ID_ENTIDAD = :id_entidad_p
        AND ID_DEPTO = :id_depto_p
        AND ID_ANHO = :id_anho_p
        AND ID_MES <= :id_mes_p
        AND ID_TIPOVENTA IN (1,2,3,4)
        UNION ALL
        SELECT
                ID_CLIENTE,
                SUM(IMPORTE)*DECODE(SIGN(SUM(IMPORTE)),1,-1,0) AS IMPORTE
        FROM VW_SALES_ADVANCES
        WHERE ID_ENTIDAD = :id_entidad_p
        AND ID_DEPTO = :id_depto_p
        AND ID_ANHO = :id_anho_p
        AND ID_MES <= :id_mes_p
        GROUP BY ID_CLIENTE
) A ".$ne."
GROUP BY ID_CLIENTE
HAVING SUM(IMPORTE)  <> 0
)";

        $data =  DB::select($q, [
            'id_entidad_p' => $params->id_entidad,
            'id_depto_p' => $params->id_depto,
            'id_anho_p' => $params->id_anho,
            'id_mes_p' => $params->id_mes
        ]);


        return collect($data)->first();

    }


}