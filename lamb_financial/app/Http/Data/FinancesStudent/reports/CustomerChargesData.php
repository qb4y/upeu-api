<?php


namespace App\Http\Data\FinancesStudent\reports;


use Illuminate\Support\Facades\DB;

class CustomerChargesData
{

    static function getCustomerCharges($params)
    {
        $query = "select r.ID_FINANCISTA, regexp_substr(P.NOMBRE,'[^ ]+')||' '||upper(SUBSTR(P.PATERNO, 1, 1))||'.' financista, sum(R.ACUMULADO) meta, sum(R.meta) acumulado, sum(R.diferencia) diferencia
                    from (
                             SELECT X.ID_FINANCISTA, X.ID_CLIENTE, SUM(META) AS META, SUM(ACUMULADO) AS ACUMULADO, SUM(ACUMULADO) - SUM(META) diferencia
                             FROM (
                                      SELECT C.ID_ENTIDAD,
                                             C.ID_DEPTO,
                                             C.ID_ANHO,
                                             C.ID_MES,
                                             A.ID_FINANCISTA,
                                             B.ID_CLIENTE,
                                             SUM(C.TOTAL) AS META,
                                             0            AS ACUMULADO
                                      FROM FIN_FINANCISTA A
                                               JOIN FIN_ASIGNACION B ON A.ID_FINANCISTA = B.ID_FINANCISTA
                                               JOIN VW_SALES_MOV C
                                                    ON B.ID_ENTIDAD = C.ID_ENTIDAD AND B.ID_DEPTO = C.ID_DEPTO AND B.ID_ANHO = C.ID_ANHO AND
                                                       B.ID_CLIENTE = C.ID_CLIENTE
                                                        AND C.ID_TIPOVENTA IN (1, 2, 3, 4)
                                      GROUP BY A.ID_FINANCISTA, B.ID_CLIENTE, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_ANHO, C.ID_MES
                                      UNION ALL
                                      SELECT C.ID_ENTIDAD,
                                             C.ID_DEPTO,
                                             C.ID_ANHO,
                                             C.ID_MES,
                                             A.ID_FINANCISTA,
                                             B.ID_CLIENTE,
                                             0            AS META,
                                             SUM(IMPORTE) AS ACUMULADO
                                      FROM FIN_FINANCISTA A
                                               JOIN FIN_ASIGNACION B ON A.ID_FINANCISTA = B.ID_FINANCISTA
                                               JOIN CAJA_DEPOSITO C
                                                    ON B.ID_ENTIDAD = C.ID_ENTIDAD AND B.ID_DEPTO = C.ID_DEPTO AND B.ID_ANHO = C.ID_ANHO AND
                                                       B.ID_CLIENTE = C.ID_CLIENTE
                                                        AND C.ID_TIPODEPOSITO <> '5'
                                      GROUP BY A.ID_FINANCISTA, B.ID_CLIENTE, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_ANHO, C.ID_MES
                                  ) X
                             WHERE X.ID_ENTIDAD = ? --7124
                               AND X.ID_DEPTO = ? --'1'
                               AND X.ID_ANHO = ? --2020
                               AND X.ID_MES <= ? --12
                             GROUP BY X.ID_FINANCISTA, X.ID_CLIENTE) R
                    left join moises.persona P ON P.id_persona = R.id_financista
                    group by R.ID_FINANCISTA, P.NOMBRE, P.PATERNO";
        return DB::select($query, [
            $params['id_entidad'],
            $params['id_depto'],
            $params['id_anho'],
            $params['id_mes'],
        ]);
    }

}