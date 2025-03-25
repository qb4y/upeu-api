<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/$second_year
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportData extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function debitsGeneral($year, $month, $entity, $ctacte )
    {
        $query = "SELECT
            TO_CHAR(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
            --NOM_DIGITADOR AS Usuario,
            --NOM_CONTADOR,
            --ID_CUENTAAASI AS CTA,
            ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS LOTE,
            COMENTARIO AS HISTORICO,
            ID_DEPTO AS DPTO,
            CASE
                WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS DEBITO,
            CASE
                WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS CREDITO
            --CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
            --ID_CTACTE AS CONTA_CORRENTE
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $ctacte
            AND ID_CUENTAAASI IN (1136001,2136001,2136010,1136010,1136080)
            ORDER BY FECHA";

        $oQuery = DB::select($query);

        return $oQuery;
    }


    public static function debit()
    {
        $query = "SELECT
            TO_CHAR(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
            --NOM_DIGITADOR AS Usuario,
            --NOM_CONTADOR,
            --ID_CUENTAAASI AS CTA,
            ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS LOTE,
            COMENTARIO AS HISTORICO,
            ID_DEPTO AS DPTO,
            CASE
                WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS DEBITO,
            CASE
                WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS CREDITO
            --CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
            --ID_CTACTE AS CONTA_CORRENTE
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = 2017
            AND ID_MES = 3
            AND ID_ENTIDAD = 7124
            AND ID_CTACTE = 17112
            AND ID_CUENTAAASI IN (1136001,2136001,2136010,1136010,1136080)
            ORDER BY FECHA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function debit_account($entity, $current_account)
    {
        $query = "select id_cuentaaasi, nombre from CONTA_CTA_DENOMINACIONAL
                        where ID_CUENTAAASI = FC_GET_CUENTA_DEBITOS($entity,$current_account)";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function statementAccount($year, $month, $entity, $current_account, $account, $query_depto)
    {
        $query = "SELECT ORDEN, FECHA, LOTE, HISTORICO, DPTO, 
                    DECODE(DEBITO,0,'',TO_CHAR(DEBITO, '999,999,999,990.00')) AS DEBITO, 
                    DECODE(CREDITO,0,'',TO_CHAR(CREDITO, '999,999,999,990.00')) AS CREDITO, 
                    DECODE(SALDO,0,'',TO_CHAR(SALDO, '999,999,999,990.00')) AS SALDO, 
                    DECODE(SIGN(SALDO), 1, 'D','C') AS DC FROM (
                    SELECT ORDEN, FEC_ASIENTO, FECHA, LOTE, HISTORICO, DPTO, DEBITO, CREDITO, SUM(VALOR) OVER ( ORDER BY ORDEN, FEC_ASIENTO ROWS UNBOUNDED PRECEDING) AS SALDO FROM (
                    SELECT
                    1 AS ORDEN,
                    TO_DATE('01'||LPAD('$month', 2, '0')||'$year','DDMMYYYY') AS FEC_ASIENTO,
                    '01/'||LPAD('$month', 2, '0')||'/$year' AS FECHA,
                    '' AS LOTE,
                    'SALDO ANTERIOR' AS HISTORICO,
                    '' AS DPTO,
                    DECODE(SIGN(SUM(COS_VALOR)), 1,ABS(SUM(COS_VALOR)) ,0) AS DEBITO,
                    DECODE(SIGN(SUM(COS_VALOR)), 0,ABS(SUM(COS_VALOR)) ,0) AS CREDITO,
                    SUM(COS_VALOR) AS VALOR
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES < $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CTACTE = $current_account
                    AND ID_CUENTAAASI = $account
                    $query_depto
                    UNION ALL
                    SELECT
                    2 AS ORDEN,
                    FEC_ASIENTO AS FEC_ASIENTO,
                    TO_CHAR(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
                    ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS LOTE,
                    COMENTARIO AS HISTORICO,
                    ID_DEPTO AS DPTO,
                    CASE
                        WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                        ELSE 0
                    END AS DEBITO,
                    CASE
                        WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                        ELSE 0
                    END AS CREDITO,
                    COS_VALOR AS VALOR
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CTACTE = $current_account
                    AND ID_CUENTAAASI = $account
                    $query_depto
                    UNION ALL
                    SELECT
                    3 AS ORDEN,
                    ((TO_DATE('01'||LPAD('$month', 2, '0')||'$year','DDMMYYYY')+INTERVAL '1' MONTH)- INTERVAL '1' DAY) AS FEC_ASIENTO,
                    TO_CHAR(((TO_DATE('01'||LPAD('$month', 2, '0')||'$year','DDMMYYYY')+INTERVAL '1' MONTH)- INTERVAL '1' DAY),'DD/MM/YYYY') AS FECHA,
                    '' AS LOTE,
                    'SALDO FINAL' AS HISTORICO,
                    '' AS DPTO,
                    DEBITO,
                    CREDITO,
                    0 AS VALOR
                    FROM (
                    SELECT SUM(DEBITO) AS DEBITO, SUM(CREDITO) AS CREDITO FROM (
                    SELECT
                    DECODE(SIGN(SUM(COS_VALOR)), 1,ABS(SUM(COS_VALOR)) ,0) AS DEBITO,
                    DECODE(SIGN(SUM(COS_VALOR)), 0,ABS(SUM(COS_VALOR)) ,0) AS CREDITO
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES < $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CTACTE = $current_account
                    AND ID_CUENTAAASI = $account
                    $query_depto
                    union all 
                    SELECT
                    SUM(CASE
                        WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                        ELSE 0
                    END) AS DEBITO,
                    SUM(CASE
                        WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                        ELSE 0
                    END) AS CREDITO
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CTACTE = $current_account
                    AND ID_CUENTAAASI = $account
                    $query_depto
                    )S
                    )T                        
                    )A
                    )B
                    ORDER BY ORDEN,FEC_ASIENTO";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function statementAccountSummary($year, $month, $entity)
    {
        $query = "select * from (
                    SELECT ID_CTACTE,ID_CUENTAAASI,
                    sum(cos_valor) as saldo
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CUENTAAASI in (1136001,1136010,1136011,1136060,17811001,2136001,2136010,213611,2136060)
                    group by ID_CTACTE,ID_CUENTAAASI
                    union all 
                    SELECT ID_CTACTE,
                    '0' as ID_CUENTAAASI,
                    sum(cos_valor) as saldo
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CUENTAAASI in (1136001,1136010,1136011,1136060,17811001,2136001,2136010,213611,2136060)
                    group by ID_CTACTE    
                ) a
                PIVOT
                    (SUM (saldo)
                  FOR ID_CUENTAAASI IN (1136001 c_1136001,1136010 c_1136010,1136011 c_1136011,1136060 c_1136060,17811001 c_17811001,2136001 c_2136001,2136010 c_2136010,213611 c_213611,2136060 c_2136060,0 c_total)
                  )
                union all 
                select * from (
                    SELECT 'Total' as ID_CTACTE,ID_CUENTAAASI,
                    sum(cos_valor) as saldo
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CUENTAAASI in (1136001,1136010,1136011,1136060,17811001,2136001,2136010,213611,2136060)
                    group by ID_CUENTAAASI
                    union all 
                    SELECT 'Total' as ID_CTACTE,
                    '0' as ID_CUENTAAASI,
                    sum(cos_valor) as saldo
                    FROM VW_CONTA_DIARIO
                    WHERE ID_ANHO = $year
                    AND ID_MES = $month
                    AND ID_ENTIDAD = $entity
                    AND ID_CUENTAAASI in (1136001,1136010,1136011,1136060,17811001,2136001,2136010,213611,2136060)
                ) a
                PIVOT
                    (SUM (saldo)
                  FOR ID_CUENTAAASI IN (1136001 c_1136001,1136010 c_1136010,1136011 c_1136011,1136060 c_1136060,17811001 c_17811001,2136001 c_2136001,2136010 c_2136010,213611 c_213611,2136060 c_2136060,0 c_total)
                  ) 
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
/*
    public static function totalDebit($year, $month, $entity, $current_account, $account)
    {
        $query = "SELECT
            SUM(CAST(SALDO_INICIAL AS DECIMAL(9,2))) AS SALDO_INICIAL,
            SUM(CAST(DEBITO AS DECIMAL(9,2))) AS DEBITO,
            SUM(CAST(CREDITO AS DECIMAL(9,2))) AS CREDITO,
            SUM(CAST(SALDO AS DECIMAL(9,2))) AS SALDO
            FROM (
            SELECT
                SUM(COS_VALOR) AS SALDO_INICIAL,
                0 AS DEBITO,
                0 AS CREDITO,
                0 AS SALDO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES < $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI = $account
            UNION
            SELECT
                0 AS SALDO_INICIAL,
                SUM(CASE
                    WHEN COS_VALOR > 0 THEN ABS(-COS_VALOR)
                    ELSE 0
                END) AS DEBITO,
                SUM(CASE
                    WHEN COS_VALOR < 0 THEN ABS(COS_VALOR)
                    ELSE 0
                END) AS CREDITO,
                0 AS SALDO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI = $account
            UNION
            SELECT
                0 AS SALDO_INICIAL,
                0 AS DEBITO,
                0 AS CREDITO,
                SUM(CASE WHEN COS_VALOR > 0 THEN ABS(-COS_VALOR) ELSE 0 END) -
                SUM(CASE WHEN COS_VALOR < 0 THEN ABS(COS_VALOR) ELSE 0 END) AS SALDO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI $account
        ) DATOS";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }
    */
    public static function detail($year, $month_begin, $month_end, $entity, $department)
    {

        $query = "SELECT
        ID_ANHO AS ANHO,
        0 AS MES,
        ' ' AS FECHA_ASIENTO,
        ' ' AS FECHA_DIGITADO,
        ' ' AS FECHA_CONTABILIZADO,
        ID_DEPTO AS DPTO,
        ' ' AS CTA,
        ' ' AS CTA_CORRENTE,
        ' ' AS TIPO,
        0 AS LOTE,
        ' ' AS ITEM,
        0 AS DEBITO,
        0 AS CREDITO,
        sum(CAST(COS_VALOR AS DECIMAL(9,2))) AS VALOR,
        'Saldo Inicial' AS HISTORICO
        FROM VW_CONTA_DIARIO
        WHERE ID_ANHO = $year
        and ID_MES <= NVL($month_end,0)-1
        and ID_CUENTAAASI like ('4%')
        AND ID_ENTIDAD = $entity
        --AND ID_CTACTE = 17112
        AND ID_DEPTO IN ($department)
        GROUP BY ID_ANHO,ID_DEPTO
        UNION
        SELECT
        ID_ANHO AS ANHO,
        ID_MES AS MES,
        TO_CHAR(FEC_ASIENTO,'DD/MM/YYYY') AS FECHA_ASIENTO,
        TO_CHAR(FEC_DIGITADO,'DD/MM/YYYY') AS FECHA_DIGITADO,
        TO_CHAR(FEC_CONTABILIZADO,'DD/MM/YYYY') AS FECHA_CONTABILIZADO,
        ID_DEPTO AS DPTO,
        ID_CUENTAAASI AS CTA,
        ID_CTACTE AS CTA_CORRENTE,
        ID_TIPOASIENTO AS TIPO,
        COD_AASI AS LOTE,
        NUM_AASI AS ITEM,
        CASE
            WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
            ELSE 0
        END AS DEBITO,
        CASE
            WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
            ELSE 0
        END AS CREDITO,
        CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
        COMENTARIO AS HISTORICO
        FROM VW_CONTA_DIARIO
        WHERE ID_ANHO = $year
        AND ID_MES BETWEEN NVL($month_begin,0) and NVL($month_end,0)
        and ID_CUENTAAASI like ('4%')
        AND ID_ENTIDAD = $entity
        AND ID_DEPTO IN ($department)
        ORDER BY FECHA_ASIENTO";

        $getDetail = DB::select($query);

        return $getDetail;
    }

    public static function checkingBalance($year, $month, $entity, $fund)
    {

        $query = "SELECT
            CS.ID_CUENTAAASI AS CTA,
            CD.NOMBRE AS NOMBRE_CTA,
            CS.ID_CTACTE AS CTA_CTE,
            CCC.NOMBRE AS NOMBRE_CTA_CTE,
            SUM(CS.DEBE) AS DEBE,
            SUM(CS.HABER) AS HABER,
            SUM(CS.SALDO) AS SALDOS
            FROM VW_CONTA_SALDOS CS
            INNER JOIN CONTA_CTA_DENOMINACIONAL CD
            ON  CS.ID_CUENTAAASI = CD.ID_CUENTAAASI
            AND CS.ID_RESTRICCION = CD.ID_RESTRICCION
            AND CS.ID_TIPOPLAN = CD.ID_TIPOPLAN
            LEFT JOIN VW_CONTA_CTACTE CCC
            ON CS.ID_ENTIDAD = CCC.ID_ENTIDAD
            AND CS.ID_CTACTE = CCC.ID_CTACTE
            WHERE CS.ID_ANHO = $year
            AND CS.ID_MES = $month
            AND CS.ID_ENTIDAD = $entity
            AND CS.ID_FONDO = $fund
            GROUP BY CS.ID_CUENTAAASI,CS.ID_CTACTE,CD.NOMBRE,CCC.NOMBRE
            ORDER BY CS.ID_CUENTAAASI";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function totalCheckingBalance($year, $month, $entity, $fund)
    {

        $query = "SELECT
            SUM(DEBE) AS DEBE,
            SUM(HABER) AS HABER,
            SUM(SALDO) AS SALDOS
            FROM VW_CONTA_SALDOS
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_FONDO = $fund";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function ledger($year, $month, $entity, $current_account, $account_aasi, $department)
    {
        //dd($account_aasi);
        $query = "SELECT
            ID_ANHO AS ANHO,
            ID_MES AS MES,
            FEC_ASIENTO AS FECHA_ASIENTO,
            FEC_DIGITADO AS FECHA_DIGITADO,
            FEC_CONTABILIZADO AS FECHA_CONTABILIZADO,
            ID_DEPTO AS DPTO,
            ID_CUENTAAASI AS CTA,
            ID_CTACTE AS CTA_CORRENTE,
            ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS ITEM,
            NOM_DIGITADOR AS DIGITADOR,
            NOM_CONTADOR AS CONTADOR,
            CASE
                WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS DEBITO,
            CASE
                WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS CREDITO,
            CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
            COMENTARIO AS HISTORICO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI IN ($account_aasi)
            AND ID_DEPTO IN ($department)
            ORDER BY FECHA_CONTABILIZADO";
        dd($query);

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function totalLedger($year, $month, $entity, $current_account, $account_aasi, $department)
    {
        $query = "SELECT
            SUM(CAST(SALDO_INICIAL AS DECIMAL(9,2))) AS SALDO_INICIAL,
            SUM(CAST(DEBITO AS DECIMAL(9,2))) AS DEBITO,
            SUM(CAST(CREDITO AS DECIMAL(9,2))) AS CREDITO,
            SUM(CAST(SALDO AS DECIMAL(9,2))) AS SALDO
            FROM (
            SELECT
                SUM(COS_VALOR) AS SALDO_INICIAL,
                0 AS DEBITO,
                0 AS CREDITO,
                0 AS SALDO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = 2017
            AND ID_MES < 5
            AND ID_ENTIDAD = 7124
            AND ID_CTACTE = 11010101
            AND ID_CUENTAAASI in (1136001)
            AND ID_DEPTO IN (11010101)
            UNION
            SELECT
                0 AS SALDO_INICIAL,
                SUM(CASE
                    WHEN COS_VALOR > 0 THEN ABS(-COS_VALOR)
                    ELSE 0
                END) AS DEBITO,
                SUM(CASE
                    WHEN COS_VALOR < 0 THEN ABS(COS_VALOR)
                    ELSE 0
                END) AS CREDITO,
                SUM(CASE WHEN COS_VALOR > 0 THEN ABS(-COS_VALOR) ELSE 0 END) -
                SUM(CASE WHEN COS_VALOR < 0 THEN ABS(COS_VALOR) ELSE 0 END) AS SALDO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI IN ($account_aasi)
            AND ID_DEPTO IN ($department)
            ) DATOS";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function available($year, $month, $entity, $rank)
    {
        $query = "SELECT *
            FROM (
            SELECT
            'DISPONIBLE' GLOSA,ID_MES,
            SUM(IMP) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) AS IMP
            FROM (
            SELECT
            A.ID_MES,
            SUM(A.COS_VALOR) IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) = '111'
            GROUP BY A.ID_MES
            ))
            PIVOT
              (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function currentAsset($year, $month, $entity, $rank)
    {
        $query = "SELECT * FROM (
            SELECT
            'ACTIVO CORRIENTE' GLOSA,
            ID_MES,
            SUM(SUM(COS_VALOR)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) AS IMP
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI BETWEEN 1111001 AND 1161090
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR*-1 AS COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            )
            GROUP BY ID_MES
            )
            PIVOT
               (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function consolidationProfit($year, $month, $entity, $rank)
    {
        $query = "SELECT * FROM (
            SELECT
            'RESULTADO DEL EJERCICIO' GLOSA,
            ID_MES,
            SUM(IMP) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) AS IMP
            FROM (
                SELECT
            A.ID_MES,
            SUM(A.COS_VALOR)*-1 IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES BETWEEN 1 AND $month
                AND A.ID_CUENTAAASI >= 3000000
                AND A.ID_CUENTAAASI <= 4219505
            GROUP BY A.ID_MES
            )
            )
            PIVOT (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function operativeCapital($year, $month, $entity, $rank)
    {
        $query = "SELECT * FROM (
            SELECT
            'CAPITAL OPERATIVO' GLOSA,
            ID_MES,
            SUM(SUM(COS_VALOR)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) AS IMP
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI >= 1100000
            AND A.ID_CUENTAAASI <= 1161095
            AND A.ID_CUENTAAASI <> 1136080
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI >= 2130000
            AND A.ID_CUENTAAASI <= 2164005
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI >= 2326005
            AND A.ID_CUENTAAASI <= 2326020
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 2321020
            )
            GROUP BY ID_MES
            )
            PIVOT
               (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function immediateLiquidity($year, $month, $entity, $rank)
    {
        $query = "SELECT *
            FROM (
            SELECT
            'LIQUIDEZ INMEDIATA' GLOSA,
            ID_MES,
            ROUND(SUM(DISPONIBLE)/SUM(PAGAR),2)*-1 IMP
            FROM (
            SELECT
            A.ID_MES,
            SUM(SUM(A.COS_VALOR)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) DISPONIBLE,
            0 PAGAR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) IN ('111','112')
            GROUP BY ID_MES
            UNION ALL
            SELECT
            ID_MES,
            0 DISPONIBLE,
            SUM(SUM(IMP)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) PAGAR
            FROM
            (
            SELECT
            A.ID_MES,
            COS_VALOR AS IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) IN ('213','214','215','216','232')
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR AS IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            )
            GROUP BY ID_MES
            )
            GROUP BY ID_MES
            )
            PIVOT
               (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function currentLiquidity($year, $month, $entity, $rank)
    {
        $query = "SELECT *
            FROM (
            SELECT
            'LIQUIDEZ CORRIENTE' GLOSA,
            ID_MES,
            ROUND(SUM(AC)/SUM(PAGAR),2)*-1 IMP
            FROM
            (
            SELECT
            ID_MES,
            SUM(SUM(COS_VALOR)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) AS AC,
            0 PAGAR
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI BETWEEN 1111001 AND 1161090
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR*-1 AS COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            )
            GROUP BY ID_MES
            UNION ALL
            SELECT
            ID_MES,
            0 AC,
            SUM(SUM(IMP)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) PAGAR
            FROM
            (
            SELECT
            A.ID_MES,
            COS_VALOR AS IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) IN ('213','214','215','216','232')
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR AS IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            )
            GROUP BY ID_MES
            )
            GROUP BY ID_MES
            )
            PIVOT
               (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function dryLiquidity($year, $month, $entity, $rank)
    {
        $query = "SELECT * FROM (
            SELECT
            ID_MES,
            'LIQUIDEZ SECA' AS GLOSA,
            ROUND(SUM(IMP)/SUM(IMP1),2) IMP
            FROM (
            SELECT
            ID_MES,
            SUM(SUM(IMP)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) IMP,
            0 IMP1
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR AS IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) IN ('111','112','116')
            UNION ALL
            SELECT
            ID_MES,
            SUM(COS_VALOR) IMP
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) = '113'
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR*-1 COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 1136080
            )
            GROUP BY ID_MES
            )
            GROUP BY ID_MES
            UNION ALL
            SELECT
            ID_MES,
            0 IMP,
            SUM(SUM(IMP)) OVER (ORDER BY ID_MES ROWS UNBOUNDED PRECEDING) IMP1
            FROM (
            SELECT
            A.ID_MES,
            SUM(A.COS_VALOR)*-1 IMP
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) IN ('214','215','216','232')
            GROUP BY A.ID_MES
            UNION ALL
            SELECT
            ID_MES,
            SUM(COS_VALOR)*-1 IMP
            FROM (
            SELECT
            A.ID_MES,
            A.COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND SUBSTR(A.ID_CUENTAAASI,1,3) = '213'
            UNION ALL
            SELECT
            A.ID_MES,
            A.COS_VALOR*-1 COS_VALOR
            FROM VW_CONTA_DIARIO A
            WHERE A.ID_ENTIDAD = $entity
            AND A.ID_ANHO = $year
            AND A.ID_MES BETWEEN 1 AND $month
            AND A.ID_CUENTAAASI = 2136080
            )
            GROUP BY ID_MES
            )
            GROUP BY ID_MES
            )
            GROUP BY ID_MES
            )
            PIVOT
               (SUM (IMP)
            FOR ID_MES IN ($rank)
            )";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function ledgeAssinet($year, $month, $entity, $begin_date, $end_date, $queryFunds, $queryFunctions, $queryRestrictions, $queryAccounts, $queryTypeCurrentAccounts, $queryCurrentAccounts)
    {
        $query = "SELECT B.ID_FONDO,
          B.ID_DEPTO,
          B.ID_RESTRICCION,
          B.ID_CUENTAAASI,
          B.ID_TIPOCTACTE,
          B.ID_CTACTE,
          CONTA_FONDO.NOMBRE AS NOMBRE_FONDO,
          CONTA_ENTIDAD_DEPTO.NOMBRE AS NOMBRE_DEPTO,
          CONTA_RESTRICCION.NOMBRE AS NOMBRE_RESTRICCION,
          NOMBRE_CUENTAAASI,
          TIPO_CTA_CORRIENTE.NOMBRE AS NOMBRE_TIPOCTACTE,
          CONTA_ENTIDAD_CTA_CTE.NOMBRE AS NOMBRE_CTA_CTE,
          B.FEC_ASIENTO,B.FEC_VIEW, 
          B.ID_TIPOASIENTO||' '||B.COD_AASI||'-'||B.NUM_AASI AS LOTE, 
          B.DESCRIPCION, B.DEBE, B.HABER,
          B.SALDO AS SALDO, DECODE(SIGN(B.SALDO), 1, 'D','C') AS DC
        FROM (
        SELECT
          ID_ENTIDAD,
          ID_FONDO,
          ID_DEPTO,
          ID_RESTRICCION,
          ID_CUENTAAASI,
          ID_TIPOCTACTE,
          ID_CTACTE,
          NOMBRE_CUENTAAASI,
          A.FEC_ASIENTO,
          A.FEC_VIEW,
          A.ID_TIPOASIENTO,
          A.COD_AASI,
          A.NUM_AASI,
          A.COMENTARIO AS DESCRIPCION,
          A.DEBE,
          A.HABER,
          SUM(A.COS_VALOR) OVER (PARTITION BY ID_FONDO,
                              ID_DEPTO,
                              ID_RESTRICCION,
                              ID_CUENTAAASI,
                              ID_TIPOCTACTE,
                              ID_CTACTE
                ORDER BY
                        A.ID_CTACTE,
                        A.FEC_ASIENTO,
                        A.ID_TIPOASIENTO,
                        A.COD_AASI,
                        A.NUM_AASI
            ROWS UNBOUNDED PRECEDING ) AS saldo
        FROM (
        SELECT
          CONTA_DIARIO_DETALLE.ID_ENTIDAD,
          CONTA_DIARIO_DETALLE.ID_FONDO,
          CONTA_DIARIO_DETALLE.ID_DEPTO,
          CONTA_DIARIO_DETALLE.ID_RESTRICCION,
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI,
          CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE,
          TO_NUMBER(CONTA_DIARIO_DETALLE.ID_CTACTE) AS ID_CTACTE,
          CONTA_CTA_DENOMINACIONAL.NOMBRE AS NOMBRE_CUENTAAASI,
          TO_DATE('01010001','DDMMYYYY') AS FEC_ASIENTO,'' AS FEC_VIEW,NULL AS ID_TIPOASIENTO, NULL AS COD_AASI,NULL AS NUM_AASI,'Saldo Inicial' AS COMENTARIO,
          0 AS DEBE, 0 AS HABER,
          SUM(ROUND(CONTA_DIARIO_DETALLE.COS_VALOR, 2)) AS COS_VALOR
        FROM CONTA_DIARIO
        INNER JOIN CONTA_DIARIO_DETALLE ON
          CONTA_DIARIO.ID_ENTIDAD = CONTA_DIARIO_DETALLE.ID_ENTIDAD AND
          CONTA_DIARIO.ID_DIARIO = CONTA_DIARIO_DETALLE.ID_DIARIO
        INNER JOIN CONTA_CTA_DENOMINACIONAL ON
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
        WHERE CONTA_DIARIO.ID_TIPOASIENTO <> 'AJ'
        AND CONTA_DIARIO.ID_TIPOASIENTO <> 'BB'
        AND CONTA_DIARIO.FEC_ASIENTO < TO_DATE('$begin_date','DD/MM/YYYY')
        AND CONTA_DIARIO.ID_ENTIDAD = $entity
        $queryFunds
        $queryFunctions
        $queryRestrictions
        $queryAccounts
        $queryTypeCurrentAccounts
        $queryCurrentAccounts
        AND CONTA_DIARIO_DETALLE.ID_CUENTAAASI||'-'||NVL(CONTA_DIARIO_DETALLE.ID_CTACTE,'') IN (
            SELECT
            CONTA_DIARIO_DETALLE.ID_CUENTAAASI||'-'||NVL(CONTA_DIARIO_DETALLE.ID_CTACTE,'')
          FROM CONTA_DIARIO
        INNER JOIN CONTA_DIARIO_DETALLE ON
            CONTA_DIARIO.ID_ENTIDAD = CONTA_DIARIO_DETALLE.ID_ENTIDAD AND
            CONTA_DIARIO.ID_DIARIO = CONTA_DIARIO_DETALLE.ID_DIARIO
        WHERE CONTA_DIARIO.ID_TIPOASIENTO <> 'AJ'
        AND CONTA_DIARIO.ID_TIPOASIENTO <> 'BB'
        AND CONTA_DIARIO.FEC_ASIENTO BETWEEN TO_DATE('$begin_date','DD/MM/YYYY') AND TO_DATE('$end_date','DD/MM/YYYY')
        AND CONTA_DIARIO.ID_ENTIDAD = $entity
        $queryFunds
        $queryFunctions
        $queryRestrictions
        $queryAccounts
        $queryTypeCurrentAccounts
        $queryCurrentAccounts
        )
        GROUP BY
          CONTA_DIARIO_DETALLE.ID_ENTIDAD,
          CONTA_DIARIO_DETALLE.ID_FONDO,
          CONTA_DIARIO_DETALLE.ID_DEPTO,
          CONTA_DIARIO_DETALLE.ID_RESTRICCION,
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI,
          CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE,
          TO_NUMBER(CONTA_DIARIO_DETALLE.ID_CTACTE),
          CONTA_CTA_DENOMINACIONAL.NOMBRE
        UNION ALL
        SELECT
          CONTA_DIARIO_DETALLE.ID_ENTIDAD,
          CONTA_DIARIO_DETALLE.ID_FONDO,
          CONTA_DIARIO_DETALLE.ID_DEPTO,
          CONTA_DIARIO_DETALLE.ID_RESTRICCION,
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI,
          CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE,
          TO_NUMBER(CONTA_DIARIO_DETALLE.ID_CTACTE) AS ID_CTACTE,
          CONTA_CTA_DENOMINACIONAL.NOMBRE AS NOMBRE_CUENTAAASI,
          CONTA_DIARIO.FEC_ASIENTO,
          TO_CHAR(CONTA_DIARIO.FEC_ASIENTO,'DD/MM/YYYY') AS FEC_VIEW,
          CONTA_DIARIO.ID_TIPOASIENTO,
          CONTA_DIARIO.COD_AASI,
          CONTA_DIARIO_DETALLE.NUM_AASI,
          CONTA_DIARIO_DETALLE.COMENTARIO,
          CASE WHEN CONTA_DIARIO_DETALLE.COS_VALOR > 0 THEN ABS(ROUND(CONTA_DIARIO_DETALLE.COS_VALOR, 2)) ELSE 0 END AS DEBE,
          CASE WHEN CONTA_DIARIO_DETALLE.COS_VALOR < 0 THEN ABS(ROUND(CONTA_DIARIO_DETALLE.COS_VALOR, 2)) ELSE 0 END AS HABER,
          CONTA_DIARIO_DETALLE.COS_VALOR
        FROM CONTA_DIARIO
        INNER JOIN CONTA_DIARIO_DETALLE ON
          CONTA_DIARIO.ID_ENTIDAD = CONTA_DIARIO_DETALLE.ID_ENTIDAD AND
          CONTA_DIARIO.ID_DIARIO = CONTA_DIARIO_DETALLE.ID_DIARIO
        INNER JOIN CONTA_CTA_DENOMINACIONAL ON
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
        WHERE CONTA_DIARIO.ID_TIPOASIENTO <> 'AJ'
        AND CONTA_DIARIO.ID_TIPOASIENTO <> 'BB'
        AND CONTA_DIARIO.FEC_ASIENTO BETWEEN TO_DATE('$begin_date','DD/MM/YYYY') AND TO_DATE('$end_date','DD/MM/YYYY')
        AND CONTA_DIARIO.ID_ENTIDAD = $entity
        $queryFunds
        $queryFunctions
        $queryRestrictions
        $queryAccounts
        $queryTypeCurrentAccounts
        $queryCurrentAccounts
        UNION ALL
        SELECT
          CONTA_DIARIO_DETALLE.ID_ENTIDAD,
          CONTA_DIARIO_DETALLE.ID_FONDO,
          CONTA_DIARIO_DETALLE.ID_DEPTO,
          CONTA_DIARIO_DETALLE.ID_RESTRICCION,
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI,
          CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE,
          TO_NUMBER(CONTA_DIARIO_DETALLE.ID_CTACTE) AS ID_CTACTE,
          CONTA_CTA_DENOMINACIONAL.NOMBRE AS NOMBRE_CUENTAAASI,
          TO_DATE('01012050','DDMMYYYY') AS FEC_ASIENTO,'' AS FEC_VIEW,NULL AS ID_TIPOASIENTO, NULL AS COD_AASI,NULL AS NUM_AASI,'Saldo Final' AS COMENTARIO,
          SUM(CASE WHEN CONTA_DIARIO_DETALLE.COS_VALOR > 0 THEN ABS(ROUND(CONTA_DIARIO_DETALLE.COS_VALOR, 2)) ELSE 0 END) AS DEBE,
          SUM(CASE WHEN CONTA_DIARIO_DETALLE.COS_VALOR < 0 THEN ABS(ROUND(CONTA_DIARIO_DETALLE.COS_VALOR, 2)) ELSE 0 END) AS HABER,
          0 AS COS_VALOR
        FROM CONTA_DIARIO
        INNER JOIN CONTA_DIARIO_DETALLE ON
          CONTA_DIARIO.ID_ENTIDAD = CONTA_DIARIO_DETALLE.ID_ENTIDAD AND
          CONTA_DIARIO.ID_DIARIO = CONTA_DIARIO_DETALLE.ID_DIARIO
        INNER JOIN CONTA_CTA_DENOMINACIONAL ON
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
        INNER JOIN CONTA_CTA_DENOMINACIONAL ON
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
        WHERE CONTA_DIARIO.ID_TIPOASIENTO <> 'AJ'
        AND CONTA_DIARIO.ID_TIPOASIENTO <> 'BB'
        AND CONTA_DIARIO.FEC_ASIENTO BETWEEN TO_DATE('$begin_date','DD/MM/YYYY') AND TO_DATE('$end_date','DD/MM/YYYY')
        AND CONTA_DIARIO.ID_ENTIDAD = $entity
        $queryFunds
        $queryFunctions
        $queryRestrictions
        $queryAccounts
        $queryTypeCurrentAccounts
        $queryCurrentAccounts
        GROUP BY
          CONTA_DIARIO_DETALLE.ID_ENTIDAD,
          CONTA_DIARIO_DETALLE.ID_FONDO,
          CONTA_DIARIO_DETALLE.ID_DEPTO,
          CONTA_DIARIO_DETALLE.ID_RESTRICCION,
          CONTA_DIARIO_DETALLE.ID_CUENTAAASI,
          CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE,
          TO_NUMBER(CONTA_DIARIO_DETALLE.ID_CTACTE),
          CONTA_CTA_DENOMINACIONAL.NOMBRE
        ) A
        ) B INNER JOIN CONTA_FONDO ON
            B.ID_FONDO = CONTA_FONDO.ID_FONDO
            INNER JOIN CONTA_ENTIDAD_DEPTO ON
            B.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO
            AND B.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
            INNER JOIN CONTA_RESTRICCION ON
            B.ID_RESTRICCION = CONTA_RESTRICCION.ID_RESTRICCION
            INNER JOIN TIPO_CTA_CORRIENTE ON
            B.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
            LEFT OUTER JOIN CONTA_ENTIDAD_CTA_CTE ON
            B.ID_CTACTE = CONTA_ENTIDAD_CTA_CTE.ID_CTACTE
            AND B.ID_ENTIDAD = CONTA_ENTIDAD_CTA_CTE.ID_ENTIDAD
            AND B.ID_TIPOCTACTE = CONTA_ENTIDAD_CTA_CTE.ID_TIPOCTACTE
        ORDER BY B.ID_FONDO,
          B.ID_DEPTO,
          B.ID_RESTRICCION,
          B.ID_CUENTAAASI,
          B.ID_TIPOCTACTE,
          NOMBRE_CTA_CTE,
          B.FEC_ASIENTO,
          ID_TIPOASIENTO,
          COD_AASI,
          NUM_AASI 
                ";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function financialAnalysisDepartment($year, $month, $entity)
    {
        $query = "SELECT
                ID_DEPTO,
                (SELECT A.NOMBRE FROM CONTA_ENTIDAD_DEPTO A WHERE A.ID_DEPTO = X.ID_DEPTO AND A.ID_ENTIDAD = 7124 ) DEPTO,
                SUM(X.SALDO_INI) SALDO_INI,
                SUM(X.INGRESO)*-1 INGRESO,
                SUM(X.GASTOS) GASTOS,
                ABS(SUM(X.INGRESO))-SUM(X.GASTOS) RESULTADO,
                SUM(X.SALDO_FIN) SALDO_FIN
        FROM (
                SELECT A.ID_DEPTO,
                        (CASE WHEN A.ID_CUENTAAASI BETWEEN '3000000' AND '3214001' THEN A.COS_VALOR ELSE 0 END) INGRESO,
                        (CASE WHEN A.ID_CUENTAAASI BETWEEN '4110000' AND '4199505' THEN A.COS_VALOR ELSE 0 END) GASTOS,
                        0 SALDO_INI,
                        0 SALDO_FIN
                FROM VW_CONTA_DIARIO A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES BETWEEN 1 AND $month
                UNION ALL
                SELECT
                        ID_DEPTO,
                        0 INGRESO,
                        0 GASTOS,
                        SALDO SALDO_INI,
                        0 SALDO_FIN
                FROM VW_CONTA_SALDOS A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year-1
                AND A.ID_MES BETWEEN 1 AND 12
                UNION ALL
                SELECT
                        ID_DEPTO,
                        0 INGRESO,
                        0 GASTOS,
                        0 SALDO_INI,
                        SALDO SALDO_FIN
                FROM VW_CONTA_SALDOS A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES BETWEEN 1 AND $month
        ) X
        GROUP BY X.ID_DEPTO
        ORDER BY X.ID_DEPTO";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function statementGroupLevel1($entity)
    {
        $query = "SELECT
            ID_GRUPO,NOMBRE
            FROM CONTA_ENTIDAD_GRUPO
            WHERE ID_ENTIDAD = $entity
            AND TIPO = '1'
            AND NIVEL IN ('0')
            GROUP BY ID_GRUPO,NOMBRE
            ORDER BY ID_GRUPO";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function statementGroupLevel2($group)
    {
        $query = "SELECT
            B.ID_GRUPO AS ID_PARENT,
            B.NOMBRE AS NAME_PARENT,
            NVL(D.ID_GRUPO,-1) AS ID,
            NVL(D.NOMBRE,'-') AS NAME
            FROM CONTA_ENTIDAD_GRUPO B LEFT JOIN CONTA_ENTIDAD_GRUPO  D
            ON B.ID_GRUPO = D.ID_PARENT
            WHERE B.ID_PARENT = $group
            ORDER BY B.ID_GRUPO,D.ID_GRUPO";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function statementGroupLevel3($group)
    {
        $query = "SELECT
            A.ID_GRUPO PADRE,
            A.NOMBRE PADRE_NAME,
            B.ID_GRUPO HIJO,
            B.NOMBRE HIJO_NAME
            FROM CONTA_ENTIDAD_GRUPO A, CONTA_ENTIDAD_GRUPO B
            WHERE A.ID_GRUPO = B.ID_PARENT
            AND A.ID_GRUPO = $group
            AND B.NIVEL IN ('2')";

        $oQuery = DB::select($query);

        return $oQuery;
    }
    
    public static function statementUPeULevel($id_entidad){
        $query = "SELECT  ID_ENTIDAD,
                        ID_GRUPO,
                        ID_PARENT, 
                        NOMBRE,
                        TIPO,
                        LEVEL        
                FROM CONTA_ENTIDAD_GRUPO
                WHERE ID_ENTIDAD = $id_entidad
                AND TIPO = 1
                START WITH ID_PARENT IS NULL
                CONNECT BY PRIOR ID_GRUPO = ID_PARENT
                ORDER SIBLINGS BY ID_PARENT,ID_GRUPO  ";
        $oQuery = DB::select($query);

        return $oQuery;
    }
    
    public static function levelParent($id_entidad){
        $query = "SELECT 
                        ID_GRUPO ID_PARENT,
                        NOMBRE
                FROM CONTA_ENTIDAD_GRUPO
                WHERE ID_ENTIDAD = $id_entidad
                AND TIPO = 1
                AND ID_PARENT IS NULL ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function levelChild($id_entidad,$id_parent){
        $query = "SELECT 
                        ID_PARENT,
                        ID_GRUPO ID_HIJO,
                        NOMBRE
                FROM CONTA_ENTIDAD_GRUPO
                WHERE ID_ENTIDAD = $id_entidad
                AND TIPO = 1
                AND ID_PARENT = $id_parent ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
       public static function levelChild2($id_entidad,$id_parent){
        $query = "SELECT 
                        ID_GRUPO value,
                        NOMBRE text
                FROM CONTA_ENTIDAD_GRUPO
                WHERE ID_ENTIDAD = $id_entidad
                AND TIPO = 1
                AND ID_PARENT = $id_parent ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
          public static function levelChild3($id_entidad,$id_parent){
        $query = "SELECT 
                        ID_PARENT,
                        ID_GRUPO ID_HIJO,
                        NOMBRE
                FROM CONTA_ENTIDAD_GRUPO
                WHERE ID_ENTIDAD = $id_entidad
                AND TIPO = 1
                AND ID_PARENT = $id_parent ";
        $oQuery = DB::select($query);
        return $oQuery;
    }



    public static function exportTxt()
    {
        $query = "SELECT
    			  cast(vw_conta_diario.ID_ANHO AS varchar(4)) || SUBSTR('00' || cast(vw_conta_diario.ID_MES AS varchar(2)), 2) || '00' || '|' ||                         -- PERIODO
                  cast(vw_conta_diario.ID_ENTIDAD AS varchar(6)) || '-' || vw_conta_diario.ID_TIPOASIENTO || ' ' || cast(vw_conta_diario.COD_AASI AS varchar(10)) || '|' ||       -- CUO
                  CASE vw_conta_diario.ID_TIPOASIENTO WHEN 'BB' THEN 'A' WHEN 'EB' THEN 'C' ELSE 'M' END || vw_conta_diario.NUM_AASI || '|' ||                             -- CORRELATIVO
                  '01' || '|' ||
                  SUBSTR(NVL(CONTA_Empresa_Cta.ID_CUENTAEMPRESARIAL, '** ' || vw_conta_diario.ID_CUENTAAASI) ||
                  CASE WHEN 2017 >= 2015 THEN
                    CASE WHEN NOT NVL(conta_cta_Denominacional.ID_TIPOCTACTE, '') IN ('FUNC', 'FORN') THEN cast(vw_conta_diario.ID_ENTIDAD AS varchar(6))  ELSE '' END || NVL(SUBSTR('000' || cast(TIPO_CTA_CORRIENTE.COD_SUNAT AS varchar(3)), 3) || vw_conta_diario.ID_CTACTE, '')
                  ELSE '' END,24) /* ISNULL(cast(varchar(6), vw_conta_diario.ID_ENTIDAD) || vw_conta_diario.ID_CUENTACTE, '') END, 24)*/ || '|' ||                                     -- CUENTA
                  cast(vw_conta_diario.ID_DEPTO AS varchar(6)) || '|' ||                                                                                                                                     -- 5 Unidad de operación
                  --'|', ||                                                                                                                                     -- 6 Centro de Costos
                  'PEN|' ||                                                                                                                                  -- 7 Moneda
                  '|' ||                                                                                                                                     -- 8 Tipo documento emisor
                  '|' ||                                                                                                                                     -- 9 Documento del emisor
                  '00|' ||                                                                                                                                   -- 10 Tipo de comprobante *
                  '|' ||                                                                                                                                     -- 11 N° Serie
                  '00|' ||                                                                                                                         -- 12 N° comprobante
                  '|' ||                                                                                                                                     -- 13 Fecha contable
                  '|' ||                                                                                                                                     -- 14 Fecha de Vencimiento
                  to_char(vw_conta_diario.FEC_ASIENTO, 'dd/mm/yyyy') || '|' ||                                                                                  -- 15 Fecha de Operación
 				  SUBSTR(vw_conta_diario.COMENTARIO, 100) || '|' ||                                                                                                   -- 16 Glosa
                  SUBSTR(vw_conta_diario.COMENTARIO, 100) || '|' ||                                                                                                   -- 17 Glosa Referencial
                  cast(vw_conta_diario.DEBE AS varchar(20)) || '|' ||                                                                                              -- 18 Debe
                  cast(vw_conta_diario.HABER AS varchar(20)) || '|' ||                                                                                             -- 19 Haber
                  '|' ||                                                                                                                                     -- 20 Dato estructurado
                  CASE WHEN vw_conta_diario.ID_TIPOASIENTO = 'BB' AND vw_conta_diario.COD_AASI > 1 THEN '9' ELSE '1' END || '|' ||                                        -- 21 Estado
                  vw_conta_diario.LOTE || '-' || cast(vw_conta_diario.NUM_AASI AS varchar(10)) || '|' CONTENIDO                                                                     -- 22 LIBRE
                  FROM vw_conta_diario
                  INNER JOIN CONTA_CTA_DENOMINACIONAL ON
                    vw_conta_diario.ID_TIPOPLAN = CONTA_CTA_DENOMINACIONAL.ID_TIPOPLAN AND
                    vw_conta_diario.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI AND
                    vw_conta_diario.ID_RESTRICCION = CONTA_CTA_DENOMINACIONAL.ID_RESTRICCION
                  LEFT JOIN TIPO_CTA_CORRIENTE ON
                    CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
                  LEFT JOIN CONTA_EMPRESA_CTA ON
                    vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA AND
                    vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN AND
                    vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI AND
                    vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION AND
                    vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
                  WHERE vw_conta_diario.ID_EMPRESA = 201 AND
                    7124 IN (-1, vw_conta_diario.ID_ENTIDAD) AND
                    vw_conta_diario.ID_ANHO = 2017 AND
                    vw_conta_diario.ID_MES = 4";

        $oQuery = DB::select($query);
        //dd($oQuery);

        return $oQuery;
    }

    public static function exportTxt2()
    {
        $query = "SELECT
          PATERNO,
          MATERNO,
          NOMBRE,
          NOM_PERSONA,
          NUM_DOCUMENTO,
          '6: Jornada Laboral' AS Tipo  ,
          '0601' ||  CAST(2017 AS varchar(4)) || 6 || 20538633021 || '.jor' AS Archivo,
          ID_TIPODOCUMENTO || '|' || NUM_DOCUMENTO || '|' || NUM_HORAS || '|' || NUM_MINUTOS || '|' || NUM_HORASEXTRA || '|' || NUM_MINUTOSEXTRA || '|' AS CONTENIDO
        FROM (
          SELECT
            VW_APS_EMPLEADO.PATERNO,
            VW_APS_EMPLEADO.MATERNO,
            VW_APS_EMPLEADO.NOMBRE,
            VW_APS_EMPLEADO.NOM_PERSONA,
            VW_APS_EMPLEADO.NUM_DOCUMENTO,
            NVL(SUBSTR('0' || CAST(VW_APS_EMPLEADO.ID_TIPODOCUMENTO AS varchar(2)),1, 2), '*TIPO_DOCUMENTO') AS ID_TIPODOCUMENTO,
            NVL(CAST(CAST(ROUND(APS_Planilla.NUM_HORAS, 2) AS int) AS varchar(3)), '*HORAS') AS NUM_HORAS,
            NVL(CAST(CAST((APS_Planilla.NUM_HORAS - ROUND(APS_Planilla.NUM_HORAS, 1)) * 60 AS int) AS varchar(3)), '*MINUTOS') AS NUM_MINUTOS,
            NVL(CAST(CAST(ROUND(APS_Planilla.NUM_HORASEXTRA, 2) AS INT) AS varchar(3)), '0') AS NUM_HORASEXTRA,
            NVL(CAST(CAST((APS_Planilla.NUM_HORASEXTRA - ROUND(APS_Planilla.NUM_HORASEXTRA,1)) * 60 AS varchar(3)) AS int), '0') AS NUM_MINUTOSEXTRA
          FROM VW_APS_EMPLEADO
          INNER JOIN APS_PLANILLA ON
            VW_APS_EMPLEADO.ID_ENTIDAD = APS_Planilla.ID_ENTIDAD AND
            VW_APS_EMPLEADO.ID_PERSONA = APS_Planilla.ID_PERSONA AND
            VW_APS_EMPLEADO.ID_CONTRATO = APS_Planilla.ID_CONTRATO
          WHERE VW_APS_EMPLEADO.ID_ENTIDAD = 7124
            AND VW_APS_EMPLEADO.ID_TIPOCONTRATO <> 81
            AND APS_Planilla.ID_ANHO = 2017
            AND APS_Planilla.ID_MES = 1
            --AND 6 LIKE '%,6,%'
            ) Archivo_6";

        $oQuery = DB::select($query);

        return $oQuery;
    }
    public static function statementGroupI($year, $month, $entity, $group)
    {
        $query = "SELECT * FROM (               
                        SELECT  ID_ANHO,
                                SUM(IMP)*-1 IMP        
                        FROM (    
                                SELECT  X.ID_ANHO,
                                        SUM (X.COS_VALOR) IMP
                                FROM CONTA_ENTIDAD_GRUPO A
                                LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN VW_CONTA_DIARIO X
                                ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_PARENT = X.ID_CUENTAAASI
                                AND B.ID_CUENTAAASI = X.ID_CTACTE
                                WHERE     X.ID_ENTIDAD = $entity
                                AND X.ID_ANHO BETWEEN $year - 2 AND $year
                                AND X.ID_MES BETWEEN 1 AND $month
                                AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                                AND A.TIPO = '2'
                                AND A.ID_GRUPO IN (42,49)
                                AND D.B.TIENE_CTACTE = '0'
                                GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO          
                                UNION ALL
                                SELECT  X.ID_ANHO,
                                        SUM (X.COS_VALOR) IMP
                                FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN VW_CONTA_DIARIO X
                                ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_CUENTAAASI = X.ID_CUENTAAASI
                                WHERE     X.ID_ENTIDAD = $entity
                                AND X.ID_ANHO BETWEEN $year - 2 AND $year
                                AND X.ID_MES BETWEEN 1 AND $month
                                AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group) 
                                AND A.TIPO = '2'
                                AND B.TIENE_CTACTE = '0'
                                AND A.ID_GRUPO IN (42,49)
                                GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO     
                        )
                        GROUP BY ID_ANHO
                        )
                        PIVOT
                          (SUM (IMP)
                          FOR ID_ANHO
                          IN ($year - 2 ANHO1, $year - 1 ANHO2, $year ANHO3)) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function statementGroup($year, $month, $entity, $group,$anho1,$anho2,$anho3)
    {
        /*$anho1=100;
        $anho2=200;
        $anho3=300;*/
        $query = "SELECT 
                        --GRUP,NOMBRE,
                        NVL(NOMBRE,DECODE(GRUP,'A','TOTAL VENTAS','B','UTILIDAD BRUTA','C','UTILIDAD OPERATIVA','UTILIDAD NETA')) DETALLE,        
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO1) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO1)
                        END ) ANHO1,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO2)
                        END ) ANHO2, 
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END ) ANHO3,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ) PPTO_M,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_A) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_A)
                        END ) PPTO_A, 
                        ROUND((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )/DECODE((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ),0,1,(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ))*100) VAR_P,
                        ABS((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )-(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END )) VAR_S,           
                        ROUND(((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO1) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )/$anho1)) X1,
                        ROUND(((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )/$anho2)) X2,
                        ROUND(((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )/$anho3)) X3,
                        ROUND((((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO2)
                        END )-(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO1) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO1)
                        END ))/(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO1) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO1)
                        END ))*100) Y2,
                        ROUND((((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )-(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO2)
                        END ))/(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO2)
                        END ))*100) Y3
                FROM (
                SELECT  ID_ANHO,
                        GRUP, NOMBRE,
                        SUM(IMP)*-1 IMP,SUM(PPTO_M) PPTO_M, SUM(PPTO) PPTO_A        
                FROM (    
                        SELECT  X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,
                                B.ID_CUENTAAASI,
                                SUM (X.COS_VALOR) IMP,
                                0 PPTO,
                                0 PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A
                        LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN VW_CONTA_DIARIO X
                        ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_PARENT = X.ID_CUENTAAASI
                        AND B.ID_CUENTAAASI = X.ID_CTACTE
                        WHERE     X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO BETWEEN $year - 2 AND $year
                        AND X.ID_MES BETWEEN 1 AND $month
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        AND D.B.TIENE_CTACTE = '0'
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO          
                        UNION ALL
                        SELECT  X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,
                                B.ID_CUENTAAASI,
                                SUM (X.COS_VALOR) IMP,
                                0 PPTO,
                                0 PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN VW_CONTA_DIARIO X
                        ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_CUENTAAASI = X.ID_CUENTAAASI
                        WHERE     X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO BETWEEN $year - 2 AND $year
                        AND X.ID_MES BETWEEN 1 AND $month
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE     ID_ENTIDAD = 7124 AND ID_GRUPO = $group) 
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO
                        UNION ALL
                        SELECT
                                X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,               
                                B.ID_CUENTAAASI,
                                0 IMP,
                                SUM(X.COS_VALOR) PPTO,
                                SUM((CASE WHEN ID_MES BETWEEN 1 AND $month THEN X.COS_VALOR  ELSE  0 END)) PPTO_M                
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN CONTA_PRESUPUESTO X
                        ON B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_CUENTAAASI = X. ID_CUENTAAASI
                        WHERE  X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO = $year--BETWEEN $year - 2 AND $year
                        AND X.ID_MES BETWEEN 1 AND 12
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO
                        UNION ALL
                        SELECT
                                X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,               
                                B.ID_CUENTAAASI,
                                0 IMP,
                                SUM(X.COS_VALOR) PPTO,
                                SUM((CASE WHEN ID_MES BETWEEN 1 AND $month THEN X.COS_VALOR  ELSE  0 END)) PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN CONTA_PRESUPUESTO X
                        ON B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_PARENT = X. ID_CUENTAAASI
                        AND B.ID_CUENTAAASI = X.ID_CTACTE
                        WHERE  X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO = $year--BETWEEN 2017 - 2 AND 2017
                        AND X.ID_MES BETWEEN 1 AND 12
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO       
                )
                GROUP BY ROLLUP (ID_ANHO,GRUP,NOMBRE)
                )
                PIVOT
                  (SUM (IMP)
                  FOR ID_ANHO
                  IN ($year - 2 ANHO1, $year - 1 ANHO2, $year ANHO3))
                GROUP BY GRUP,NOMBRE
                ORDER BY GRUP,NOMBRE ";
        $oQuery = DB::select($query);

        return $oQuery;
    }
    public static function statementDatos($entity,$year,$month,$group){
        $sql = "SELECT 
                        GRUP,NOMBRE,
                        NVL(NOMBRE,DECODE(GRUP,'A','TOTAL VENTAS','B','UTILIDAD BRUTA','C','UTILIDAD OPERATIVA','UTILIDAD NETA')) DETALLE,        
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO1) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO1)
                        END ) imp1,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO2) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO2)
                        END ) imp2, 
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END ) imp3,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ) PPTO_M,
                        (CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_A) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_A)
                        END ) PPTO_A, 
                        ROUND((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )/DECODE((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ),0,1,(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ))*100) VAR_P,
                        ABS((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )-(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END )) VAR_M,
                        ABS((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )-(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_A) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_A)
                        END )) VAR_A,
                        SIGN(ABS(CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(ANHO3) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(ANHO3)
                        END )-ABS((CASE WHEN NOMBRE IS NULL THEN SUM((CASE  WHEN  NOMBRE IS NULL THEN 0 ELSE SUM(PPTO_M) END)) OVER (ORDER BY GRUP ROWS UNBOUNDED PRECEDING)
                        ELSE SUM(PPTO_M)
                        END ))) INDICADOR
                FROM (                                                                                         
                SELECT  ID_ANHO,
                        GRUP, NOMBRE,
                        SUM(IMP)*-1 IMP,SUM(PPTO_M) PPTO_M, SUM(PPTO) PPTO_A        
                FROM (    
                        SELECT  X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,
                                B.ID_CUENTAAASI,
                                SUM (X.COS_VALOR) IMP,
                                0 PPTO,
                                0 PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A
                        LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN VW_CONTA_DIARIO X
                        ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_PARENT = X.ID_CUENTAAASI
                        AND B.ID_CUENTAAASI = X.ID_CTACTE
                        WHERE     X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO BETWEEN $year - 2 AND $year
                        AND X.ID_MES BETWEEN 1 AND $month
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO WHERE ID_ENTIDAD = 7124 AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        AND D.B.TIENE_CTACTE = '0'
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO          
                        UNION ALL
                        SELECT  X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,
                                B.ID_CUENTAAASI,
                                SUM (X.COS_VALOR) IMP,
                                0 PPTO,
                                0 PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON     A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN VW_CONTA_DIARIO X
                        ON     B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_CUENTAAASI = X.ID_CUENTAAASI
                        WHERE     X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO BETWEEN $year - 2 AND $year
                        AND X.ID_MES BETWEEN 1 AND $month
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE     ID_ENTIDAD = 7124 AND ID_GRUPO = $group) 
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO
                        UNION ALL
                        SELECT
                                X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,               
                                B.ID_CUENTAAASI,
                                0 IMP,
                                SUM(X.COS_VALOR) PPTO,
                                SUM((CASE WHEN ID_MES BETWEEN 1 AND $month THEN X.COS_VALOR  ELSE  0 END)) PPTO_M                
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN CONTA_PRESUPUESTO X
                        ON B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_CUENTAAASI = X. ID_CUENTAAASI
                        WHERE  X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO = $year--BETWEEN 2017 - 2 AND 2017
                        AND X.ID_MES BETWEEN 1 AND 12
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO
                        UNION ALL
                        SELECT
                                X.ID_ANHO,
                                DECODE(A.ID_GRUPO,42,'A',49,'A',20,'B',21,'B',22,'B',23,'C',44,'C',40,'C',41,'C',56,'D',57,'D',A.ID_GRUPO) GRUP,
                                A.ID_GRUPO,
                                A.NOMBRE,               
                                B.ID_CUENTAAASI,
                                0 IMP,
                                SUM(X.COS_VALOR) PPTO,
                                SUM((CASE WHEN ID_MES BETWEEN 1 AND $month THEN X.COS_VALOR  ELSE  0 END)) PPTO_M
                        FROM CONTA_ENTIDAD_GRUPO A LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                        ON A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_GRUPO = B.ID_GRUPO
                        LEFT JOIN CONTA_PRESUPUESTO X
                        ON B.ID_ENTIDAD = X.ID_ENTIDAD
                        AND B.ID_PARENT = X. ID_CUENTAAASI
                        AND B.ID_CUENTAAASI = X.ID_CTACTE
                        WHERE  X.ID_ENTIDAD = $entity
                        AND X.ID_ANHO = $year--BETWEEN 2017 - 2 AND 2017
                        AND X.ID_MES BETWEEN 1 AND 12
                        AND X.ID_DEPTO IN (SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group)
                        AND A.TIPO = '2'
                        AND B.TIENE_CTACTE = '0'
                        AND A.ID_GRUPO IN (20,21,22,42,49,23,44,40,41,56,57)
                        GROUP BY A.ID_GRUPO,A.NOMBRE,B.ID_CUENTAAASI,X.ID_ANHO       
                )
                GROUP BY ROLLUP (ID_ANHO,GRUP,NOMBRE)                                              
                )
                PIVOT
                  (SUM (IMP)
                  FOR ID_ANHO
                  IN (2017 - 2 ANHO1, 2017 - 1 ANHO2, 2017 ANHO3))
                WHERE GRUP IS NOT NULL
                GROUP BY GRUP,NOMBRE
                ORDER BY GRUP,NOMBRE ";
        $Query = DB::select($sql);
        return $Query;    
    }
    public static function departmentalAnalysis($year, $month, $entity)
    {
        $query = "SELECT
                X.ID_ENTIDAD,
                ceg.NOMBRE AS GRUPO,
                X.ID_DEPTO AS DEPTO,
                ed.NOMBRE NOMBRE_DEPTO,
                SUM(X.SALDO_INI) SALDO_INI,
                SUM(X.INGRESO)*-1 INGRESO,
                SUM(X.GASTOS) GASTOS,
                ABS(SUM(X.INGRESO))-SUM(X.GASTOS) RESULTADO,
                SUM(X.SALDO_FIN) SALDO_FIN
        FROM (
                SELECT 
                        ID_ENTIDAD,
                        ID_DEPTO,
                        (CASE WHEN ID_CUENTAAASI BETWEEN '3000000' AND '3999999' THEN COS_VALOR ELSE 0 END) INGRESO,
                        (CASE WHEN ID_CUENTAAASI BETWEEN '4110000' AND '4999999' THEN COS_VALOR ELSE 0 END) GASTOS,
                        0 SALDO_INI,
                        0 SALDO_FIN
                FROM VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = $entity
                AND ID_ANHO = $year
                AND ID_MES BETWEEN 1 AND $month
                UNION ALL
                SELECT
                        ID_ENTIDAD,
                        ID_DEPTO,
                        0 INGRESO,
                        0 GASTOS,
                        SALDO SALDO_INI,
                        0 SALDO_FIN
                FROM VW_CONTA_SALDOS
                WHERE ID_ENTIDAD = $entity
                AND ID_ANHO = ($year)-1
                AND ID_MES BETWEEN 1 AND 12
                UNION ALL
                SELECT
                        ID_ENTIDAD,
                        ID_DEPTO,
                        0 INGRESO,
                        0 GASTOS,
                        0 SALDO_INI,
                        SALDO SALDO_FIN
                FROM VW_CONTA_SALDOS
                WHERE ID_ENTIDAD = $entity
                AND ID_ANHO = $year
                AND ID_MES BETWEEN 1 AND $month
        ) X
        INNER JOIN Conta_Entidad_Depto ed ON 
        X.ID_ENTIDAD = ed.ID_ENTIDAD
        and X.ID_DEPTO = ed.ID_DEPTO
        INNER JOIN CONTA_ENTIDAD_DEPTO_GRUPO cedg ON
        X.ID_ENTIDAD = cedg.ID_ENTIDAD
        AND ed.ID_ENTIDAD = cedg.ID_ENTIDAD
        AND X.ID_DEPTO = cedg.ID_DEPTO
        AND ed.ID_DEPTO = cedg.ID_DEPTO
        LEFT JOIN CONTA_ENTIDAD_GRUPO ceg ON
        X.ID_ENTIDAD = ceg.ID_ENTIDAD
        AND ed.ID_ENTIDAD = ceg.ID_ENTIDAD
        AND cedg.ID_ENTIDAD = ceg.ID_ENTIDAD
        AND cedg.ID_GRUPO = ceg.ID_GRUPO
        where X.ID_ENTIDAD = $entity
        group by X.ID_DEPTO,X.ID_ENTIDAD,ed.NOMBRE,ceg.NOMBRE
        order by ceg.NOMBRE,ed.NOMBRE";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function diaryBookSummary($empresa, $year, $month){
        $query = "SELECT         
                            TO_CHAR(SUM(CASE  WHEN vw_conta_diario.DEBE = 0 THEN 0 WHEN vw_conta_diario.DEBE BETWEEN 0.01 AND 0.99 THEN vw_conta_diario.DEBE ELSE vw_conta_diario.DEBE END),'999,999,999,999.99') DEBE,
                            TO_CHAR(SUM(CASE  WHEN vw_conta_diario.HABER = 0 THEN 0 WHEN vw_conta_diario.HABER BETWEEN 0.01 AND 0.99 THEN vw_conta_diario.HABER ELSE vw_conta_diario.HABER END),'999,999,999,999.99') HABER                                                            
                FROM vw_conta_diario
                INNER JOIN CONTA_CTA_DENOMINACIONAL 
                ON vw_conta_diario.ID_TIPOPLAN = CONTA_CTA_DENOMINACIONAL.ID_TIPOPLAN 
                AND vw_conta_diario.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI 
                AND vw_conta_diario.ID_RESTRICCION = CONTA_CTA_DENOMINACIONAL.ID_RESTRICCION
                LEFT JOIN TIPO_CTA_CORRIENTE 
                ON CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
                LEFT JOIN CONTA_EMPRESA_CTA 
                ON vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA 
                AND vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN 
                AND vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI 
                AND vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION 
                AND vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
                WHERE vw_conta_diario.ID_EMPRESA = $empresa 
                -- AND  IN (-1, vw_conta_diario.ID_ENTIDAD) 
                AND vw_conta_diario.ID_ANHO = $year 
                AND vw_conta_diario.ID_MES = $month ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function idCompany($ruc){
        $query = "SELECT 
                ID_EMPRESA,ID_RUC,NOM_LEGAL
                FROM VW_CONTA_EMPRESA
                WHERE ID_RUC = $ruc ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function diaryBook($empresa, $year, $month){
        $query = "SELECT
                            cast(vw_conta_diario.ID_ANHO AS varchar(4)) || lpad(vw_conta_diario.ID_MES,2,0) || '00' || '|' ||                         -- PERIODO
                            cast(vw_conta_diario.ID_ENTIDAD AS varchar(6)) || '-' || vw_conta_diario.ID_TIPOASIENTO || ' ' || cast(vw_conta_diario.COD_AASI AS varchar(10)) || '|' ||       -- CUO
                            CASE vw_conta_diario.ID_TIPOASIENTO WHEN 'BB' THEN 'A' WHEN 'EB' THEN 'C' ELSE 'M' END || vw_conta_diario.NUM_AASI || '|' ||                             -- CORRELATIVO
                            '01' || '|' ||
                            SUBSTR(NVL(CONTA_Empresa_Cta.ID_CUENTAEMPRESARIAL, '** ' || vw_conta_diario.ID_CUENTAAASI) ||
                            CASE WHEN 2017 >= 2015 THEN
                              CASE WHEN NOT NVL(conta_cta_Denominacional.ID_TIPOCTACTE, '') IN ('FUNC', 'FORN') THEN cast(vw_conta_diario.ID_ENTIDAD AS varchar(6))  ELSE '' END || NVL(SUBSTR('000' || cast(TIPO_CTA_CORRIENTE.COD_SUNAT AS varchar(3)), 3) || vw_conta_diario.ID_CTACTE, '')
                            ELSE '' END,24) /* ISNULL(cast(varchar(6), vw_conta_diario.ID_ENTIDAD) || vw_conta_diario.ID_CUENTACTE, '') END, 24)*/ || '|' ||                                     -- CUENTA
                            cast(vw_conta_diario.ID_DEPTO AS varchar(6)) || '|' ||                                                                                                                                     -- 5 Unidad de operación
                            --'|', ||                                                                                                                                     -- 6 Centro de Costos
                            'PEN|' ||                                                                                                                                  -- 7 Moneda
                            '|' ||                                                                                                                                     -- 8 Tipo documento emisor
                            '|' ||                                                                                                                                     -- 9 Documento del emisor
                            '00|' ||                                                                                                                                   -- 10 Tipo de comprobante *
                            '|' ||                                                                                                                                     -- 11 N° Serie
                            '00|' ||                                                                                                                         -- 12 N° comprobante
                            '|' ||                                                                                                                                     -- 13 Fecha contable
                            '|' ||                                                                                                                                     -- 14 Fecha de Vencimiento
                            to_char(vw_conta_diario.FEC_ASIENTO, 'dd/mm/yyyy') || '|' ||                                                                                  -- 15 Fecha de Operación
                                            NVL(SUBSTR(vw_conta_diario.COMENTARIO,1, 100),'-') || '|' ||                                                                                                   -- 16 Glosa
                            NVL(SUBSTR(vw_conta_diario.COMENTARIO,1, 100),'-') || '|' ||                                                                                                   -- 17 Glosa Referencial
                            --vw_conta_diario.DEBE || '|' ||                                                                                              -- 18 Debe
                            --vw_conta_diario.HABER || '|' ||      
                            (CASE  WHEN vw_conta_diario.DEBE = 0 THEN '0.00' WHEN vw_conta_diario.DEBE BETWEEN 0.01 AND 0.99 THEN REPLACE(TRIM(TO_CHAR(vw_conta_diario.DEBE,'90D99')),',','.') 
                            ELSE REPLACE(LTRIM(TO_CHAR(vw_conta_diario.DEBE,'9999999999D99')),',','.') END)|| '|' ||  
                            (CASE  WHEN vw_conta_diario.HABER = 0 THEN '0.00' WHEN vw_conta_diario.HABER BETWEEN 0.01 AND 0.99 THEN REPLACE(TRIM(TO_CHAR(vw_conta_diario.HABER,'90D99')),',','.') 
                            ELSE REPLACE(LTRIM(TO_CHAR(vw_conta_diario.HABER,'9999999999D99')),',','.') END)|| '|' ||                                                                               
                            -- 19 Haber
                            '|' ||                                                                                                                                     -- 20 Dato estructurado
                            CASE WHEN vw_conta_diario.ID_TIPOASIENTO = 'BB' AND vw_conta_diario.COD_AASI > 1 THEN '9' ELSE '1' END || '|' ||                                        -- 21 Estado
                            vw_conta_diario.LOTE || '-' || cast(vw_conta_diario.NUM_AASI AS varchar(10)) || '|' DATOS                                                               -- 22 LIBRE
                FROM vw_conta_diario INNER JOIN CONTA_CTA_DENOMINACIONAL 
                ON vw_conta_diario.ID_TIPOPLAN = CONTA_CTA_DENOMINACIONAL.ID_TIPOPLAN 
                AND vw_conta_diario.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI 
                AND vw_conta_diario.ID_RESTRICCION = CONTA_CTA_DENOMINACIONAL.ID_RESTRICCION
                LEFT JOIN TIPO_CTA_CORRIENTE 
                ON CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
                LEFT JOIN CONTA_EMPRESA_CTA 
                ON vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA 
                AND vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN 
                AND vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI 
                AND vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION 
                AND vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
                WHERE vw_conta_diario.ID_EMPRESA = $empresa 
                -- AND  IN (-1, vw_conta_diario.ID_ENTIDAD) 
                AND vw_conta_diario.ID_ANHO = $year 
                AND vw_conta_diario.ID_MES = $month ";        
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCheckingBalance($request){
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_fondo = $request->id_fondo;
        $digito = $request->digito;
        print_r('datos');
        print_r($id_empresa);
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa="IS NOT NULL";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad="IS NOT NULL";
        }
        if($id_depto===null or $id_depto==='*'){
            $id_depto="IS NOT NULL";
        }
        if($id_anho===null or $id_anho==='*'){
            $id_anho="IS NOT NULL";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes="IS NOT NULL";
        }
        if($id_fondo===null or $id_fondo==='*'){
            $id_fondo="IS NOT NULL";
        }
        $condition1="WHERE SUBSTR(ID_CUENTAAASI,2,7)!=LPAD(0,6,'0')";
        $condition2="B.ID_CUENTAAASI";
        if($digito===1){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,2,7)!=LPAD(0,6,'0')";
            $condition2="B.ID_PARENT";
        }else if($digito===2){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)=LPAD(0,5,'0')";
            $condition2="B.ID_PARENT";
        }else if($digito===3){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)!=LPAD(0,5,'0') AND SUBSTR(ID_CUENTAAASI,4,7)=LPAD(0,4,'0')";
            $condition2="B.ID_PARENT";
        }else if($digito===4){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)!=LPAD(0,5,'0') AND SUBSTR(ID_CUENTAAASI,4,7)!=LPAD(0,4,'0') 
            AND SUBSTR(ID_CUENTAAASI,5,7)=LPAD(0,3,'0')";
            $condition2="B.ID_PARENT";
        }else if($digito===5){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)!=LPAD(0,5,'0') AND SUBSTR(ID_CUENTAAASI,4,7)!=LPAD(0,4,'0')
             AND SUBSTR(ID_CUENTAAASI,5,7)!=LPAD(0,3,'0') AND SUBSTR(ID_CUENTAAASI,6,7)=LPAD(0,2,'0')";
             $condition2="B.ID_PARENT";
        }else if($digito===6){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)!=LPAD(0,5,'0') AND SUBSTR(ID_CUENTAAASI,4,7)!=LPAD(0,4,'0')
             AND SUBSTR(ID_CUENTAAASI,5,7)!=LPAD(0,3,'0') AND SUBSTR(ID_CUENTAAASI,6,7)!=LPAD(0,2,'0')  AND SUBSTR(ID_CUENTAAASI,7,7)=LPAD(0,1,'0')";
             $condition2="B.ID_PARENT";
        }else if($digito===7){
            $condition1="WHERE SUBSTR(ID_CUENTAAASI,3,7)!=LPAD(0,5,'0') AND SUBSTR(ID_CUENTAAASI,4,7)!=LPAD(0,4,'0')
             AND SUBSTR(ID_CUENTAAASI,5,7)!=LPAD(0,3,'0') AND SUBSTR(ID_CUENTAAASI,6,7)!=LPAD(0,2,'0')  AND SUBSTR(ID_CUENTAAASI,7,7)!=LPAD(0,1,'0')";
             $condition2="B.ID_PARENT";
        }
        $query = "SELECT A.ID_CUENTAAASI AS CODIGO_PARENT,A.NOMBRE AS CUENTA_PARENT
                        ,B.ID_CUENTAAASI AS CODIGO,B.NOMBRE AS CUENTA
                        ,SUM(H.DEBE) AS DEBE,SUM(H.HABER) AS HABER,(SUM(H.DEBE)-SUM(H.HABER)) AS SALDO
                        FROM (SELECT *
                        FROM CONTA_CTA_DENOMINACIONAL 
                        WHERE SUBSTR(ID_CUENTAAASI,(1+1),7)=LPAD(0,(7-1),'0')) A
                        INNER JOIN 
                        (SELECT *
                        FROM CONTA_CTA_DENOMINACIONAL 
                        $condition1
                        ) B
                        ON SUBSTR(A.ID_CUENTAAASI,0,1)=SUBSTR($condition2,0,1)
                        LEFT JOIN CONTA_CTA_DENOMINACIONAL C ON B.ID_CUENTAAASI=C.ID_PARENT
                        LEFT JOIN CONTA_CTA_DENOMINACIONAL D ON C.ID_CUENTAAASI=D.ID_PARENT
                        LEFT JOIN CONTA_CTA_DENOMINACIONAL E ON D.ID_CUENTAAASI=E.ID_PARENT
                        LEFT JOIN CONTA_CTA_DENOMINACIONAL F ON E.ID_CUENTAAASI=F.ID_PARENT
                        LEFT JOIN CONTA_CTA_DENOMINACIONAL G ON F.ID_CUENTAAASI=G.ID_PARENT
                        INNER JOIN VW_CONTA_DIARIO H ON A.ID_CUENTAAASI=H.ID_CUENTAAASI OR B.ID_CUENTAAASI=H.ID_CUENTAAASI OR C.ID_CUENTAAASI=H.ID_CUENTAAASI 
                        OR D.ID_CUENTAAASI=H.ID_CUENTAAASI OR E.ID_CUENTAAASI=H.ID_CUENTAAASI OR F.ID_CUENTAAASI=H.ID_CUENTAAASI OR G.ID_CUENTAAASI=H.ID_CUENTAAASI
                        WHERE A.ID_CUENTAAASI NOT IN (5000000,99,98)
                        AND H.ID_EMPRESA $id_empresa
                        AND H.ID_ENTIDAD $id_entidad
                        AND H.ID_DEPTO $id_depto
                        AND H.ID_ANHO $id_anho
                        AND H.ID_MES $id_mes
                        AND H.ID_FONDO $id_fondo
                        GROUP BY A.ID_CUENTAAASI,A.NOMBRE
                        ,B.ID_CUENTAAASI,B.NOMBRE
                        ORDER BY A.ID_CUENTAAASI
                        ,B.ID_CUENTAAASI";
        if($digito===8){
            $query = "SELECT A.ID_CUENTAAASI AS CODIGO_PARENT,A.NOMBRE AS CUENTA_PARENT
                        ,B.ID_CUENTAAASI AS CODIGO,B.NOMBRE AS CUENTA,H.ID_CTACTE AS CTA_CTE,I.NOMBRE
                        ,SUM(H.DEBE) AS DEBE,SUM(H.HABER) AS HABER,(SUM(H.DEBE)-SUM(H.HABER)) AS SALDO
                        FROM (SELECT *
                        FROM CONTA_CTA_DENOMINACIONAL 
                        WHERE SUBSTR(ID_CUENTAAASI,(1+1),7)=LPAD(0,(7-1),'0')) A
                        INNER JOIN 
                        (SELECT *
                        FROM CONTA_CTA_DENOMINACIONAL
                        ) B
                        ON SUBSTR(A.ID_CUENTAAASI,0,1)=SUBSTR(B.ID_PARENT,0,1)
                        INNER JOIN VW_CONTA_DIARIO H ON B.ID_CUENTAAASI=H.ID_CUENTAAASI
                        LEFT JOIN CONTA_ENTIDAD_CTA_CTE I ON I.ID_CTACTE=H.ID_CTACTE AND I.ID_ENTIDAD=H.ID_ENTIDAD AND I.ID_TIPOCTACTE=B.ID_TIPOCTACTE
                        WHERE A.ID_CUENTAAASI NOT IN (5000000,99,98)
                        AND H.ID_EMPRESA $id_empresa
                        AND H.ID_ENTIDAD=$id_entidad
                        AND H.ID_DEPTO $id_depto
                        AND H.ID_ANHO $id_anho
                        AND H.ID_MES $id_mes
                        AND H.ID_FONDO $id_fondo
                        GROUP BY A.ID_CUENTAAASI,A.NOMBRE
                        ,B.ID_CUENTAAASI,B.NOMBRE,H.ID_CTACTE,I.NOMBRE
                        ORDER BY A.ID_CUENTAAASI
                        ,B.ID_CUENTAAASI";
        }
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getDeparment($request) {
        $id_entidad = $request->id_entidad;
        $search = $request->search;
        $query = DB::table('CONTA_ENTIDAD_DEPTO')
        ->where("ID_ENTIDAD", "=",$id_entidad)
        ->where("ES_ACTIVO", "=",1)
        ->whereRaw("UPPER(NOMBRE) LIKE UPPER('%".$search."%')")
        ->select('*')
        ->get();
        return $query;
    }
}
