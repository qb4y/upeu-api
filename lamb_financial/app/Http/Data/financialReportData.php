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

class financialReportData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

// REPORT FINANCIAL STATEMENTS
    public static function cabCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,
            CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
                    SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (SUBSTR(ID_CUENTAAASI,1,2)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,2) AS CTA,
                        SUM (COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $first_year
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '11%'
                     --AND ID_CUENTAAASI NOT LIKE '1136%'
                     AND ID_DEPTO LIKE '%'
                     AND ID_FONDO LIKE '%'
                     GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,2)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,2) AS CTA,
                        0 AS IMP1,SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $second_year
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '11%'
                     --AND ID_CUENTAAASI NOT LIKE '1136%'
                     AND ID_DEPTO LIKE '%'
                     AND ID_FONDO LIKE '%'
                     GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
        )GROUP BY NOMBRE_CTA,CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,
            CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
                    SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                        SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                        SUM (COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $first_year
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '11%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                        SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                        0 AS IMP1,SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $second_year
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '11%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
        ) GROUP BY NOMBRE_CTAX,CTA
           ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function cabNonCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTAX,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
                    SELECT
                        ID_ANHO,
                        'ACTIVO NO CORRIENTE' AS NOMBRE_CTAX,
                        SUM (COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND (ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
                     GROUP BY ID_ANHO
                     UNION
                     SELECT
                        ID_ANHO,
                        'ACTIVO NO CORRIENTE' AS NOMBRE_CTAX,
                        0 AS IMP1,SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND (ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
                     GROUP BY ID_ANHO
        )GROUP BY NOMBRE_CTAX";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailNonCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,
	CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR
FROM (
    		SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1,0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1,SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($second_year)
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function totalCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
                	NOMBRE_CTAX,
                	SUM(IMP1) AS FIRST_YEAR,
                	SUM(IMP2) AS SECOND_YEAR
                FROM (
                    		SELECT
                                'TOTAL ACTIVO' AS NOMBRE_CTAX,
                                SUM (COS_VALOR) AS IMP1,
                                0 AS IMP2
                             FROM VW_CONTA_DIARIO
                             WHERE ID_ENTIDAD = $entity
                             AND ID_ANHO IN ($first_year)
                             AND ID_MES <= $first_month
                             AND (ID_CUENTAAASI LIKE '11%' OR ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
                             AND ID_CUENTAAASI NOT LIKE '1136080'
                             UNION
                             SELECT
                                'TOTAL ACTIVO' AS NOMBRE_CTAX,
                                0 AS IMP1,
                                SUM (COS_VALOR) AS IMP2
                             FROM VW_CONTA_DIARIO
                             WHERE ID_ENTIDAD = $entity
                             AND ID_ANHO IN ($second_year)
                             AND ID_MES <= $second_month
                             AND (ID_CUENTAAASI LIKE '11%' OR ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
                             AND ID_CUENTAAASI NOT LIKE '1136080'
                ) GROUP BY NOMBRE_CTAX";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function cabCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR
FROM (
    		SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 2)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                SUM (COS_VALOR) AS IMP1,0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '21%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
             UNION
             SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 2)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($second_year)
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '21%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
) GROUP BY NOMBRE_CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR
FROM (
    		SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '21%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($second_year)
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '21%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function cabNonCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR
FROM (
    		SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 2)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '22%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
             UNION
             SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 2)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($second_year)
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '22%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailNonCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR
    FROM (
    		SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
               SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '221%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
				ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($second_year)
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '221%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
         ) GROUP BY CTA,NOMBRE_CTAX
    ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

//Patrimonio NETO -- AGREGADO ---

    public static function netWorth($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
                    'PATRIMONIO NETO' AS NOMBRE_CTA,
                    SUM(IMP1) AS FIRST_YEAR,
                    SUM(IMP2) AS SECOND_YEAR
                FROM (SELECT 
                        SUM(COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                             AND ID_ANHO IN ($first_year)
                             AND ID_MES <= $first_month
                     AND (ID_CUENTAAASI LIKE '231%' OR
                     ID_CUENTAAASI LIKE '2311017' OR
                     ID_CUENTAAASI LIKE '2311015'OR
                     ID_CUENTAAASI LIKE '232%' )
                     AND ID_CUENTAAASI NOT LIKE '3141003'
                     UNION
                     SELECT 
                        SUM (COS_VALOR) AS IMP1, 
                        0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '3%'
                     UNION
                     SELECT 
                        SUM(COS_VALOR) AS IMP1, 
                        0  AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '4%'
                     UNION
                     SELECT 
                        0 AS IMP1,
                        SUM(COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND (ID_CUENTAAASI LIKE '231%' OR
                     ID_CUENTAAASI LIKE '2311017' OR
                     ID_CUENTAAASI LIKE '2311015' OR
                     ID_CUENTAAASI LIKE '232%')
                     AND ID_CUENTAAASI NOT LIKE '3141003'
                     UNION 
                     SELECT 
                        0 AS IMP1, 
                        SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '3%'
                     UNION
                     SELECT
                        0  AS IMP1,
                        SUM(COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '4%'
                     )";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function initialBalanceNetWorth($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
            SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        SUM(COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '231%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        0 AS IMP1, SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '231%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     ) GROUP BY CTA,NOMBRE_CTA
        ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function adjustmentWorth($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (ID_CUENTAAASI) AS NOMBRE_CTA,
                        ID_CUENTAAASI CTA,
                        SUM (COS_VALOR) AS IMP1, 0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '2311015'
                     GROUP BY ID_ANHO,ID_CUENTAAASI
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (ID_CUENTAAASI) AS NOMBRE_CTA,
                        ID_CUENTAAASI AS CTA,
                        0 AS IMP1,SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '2311015'
                     GROUP BY ID_ANHO,ID_CUENTAAASI
        ) GROUP BY CTA,NOMBRE_CTA
        ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function grantsSubsidiesFixedAssets($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR
        FROM (
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (ID_CUENTAAASI) AS NOMBRE_CTA,
                        ID_CUENTAAASI CTA,
                        SUM (COS_VALOR) AS IMP1, 0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '2311017'
                     GROUP BY ID_ANHO,ID_CUENTAAASI
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (ID_CUENTAAASI) AS NOMBRE_CTA,
                        ID_CUENTAAASI CTA,
                        0 AS IMP1,SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '2311017'
                     GROUP BY ID_ANHO,ID_CUENTAAASI
        ) GROUP BY CTA,NOMBRE_CTA
        ORDER BY CTA";


        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function variationWorth($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
                    'Variación Patrimonial' AS NOMBRE_CTA,
                    ABS(SUM(IMP1)) AS FIRST_YEAR,
                    ABS(SUM(IMP2)) AS SECOND_YEAR
                FROM ( 
                SELECT 
                    SUM (COS_VALOR) AS IMP1, 
                    0 AS IMP2
                 FROM VW_CONTA_DIARIO
                 WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                 AND ID_CUENTAAASI LIKE '3%'
                 UNION
                 SELECT 
                    SUM(COS_VALOR) AS IMP1, 
                    0  AS IMP2
                 FROM VW_CONTA_DIARIO
                 WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                 AND ID_CUENTAAASI LIKE '4%'
                 UNION
                 SELECT 
                    0 AS IMP1, 
                    SUM (COS_VALOR) AS IMP2
                 FROM VW_CONTA_DIARIO
                 WHERE ID_ENTIDAD = $entity
                 AND ID_ANHO IN ($second_year)
                 AND ID_MES <= $second_month
                 AND ID_CUENTAAASI LIKE '3%'
                 UNION
                 SELECT
                    0  AS IMP1,
                    SUM(COS_VALOR) AS IMP2
                 FROM VW_CONTA_DIARIO
                 WHERE ID_ENTIDAD = $entity
                 AND ID_ANHO IN ($second_year)
                 AND ID_MES <= $second_month
                 AND ID_CUENTAAASI LIKE '4%')";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function inicialBalanceFundsAvailable($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,CTA,
            ABS(SUM(IMP1)) AS FIRST_YEAR,
            ABS(SUM(IMP2)) AS SECOND_YEAR
        FROM (
            SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        SUM(COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '232%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     UNION
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        0 AS IMP1, SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '232%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     ) GROUP BY CTA,NOMBRE_CTA
        ORDER BY CTA";


        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function constitutionReversionFunds($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            NOMBRE_CTA,CTA,
            ABS(SUM(IMP1)) AS FIRST_YEAR,
            ABS(SUM(IMP2)) AS SECOND_YEAR
        FROM (
            SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        SUM(COS_VALOR) AS IMP1,0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($first_year)
                     AND ID_MES = $first_month
                     AND ID_CUENTAAASI LIKE '232%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     UNION ALL
                     SELECT
                        ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL(SUBSTR(ID_CUENTAAASI,1,3)) AS NOMBRE_CTA,
                        SUBSTR(ID_CUENTAAASI,1,3) CTA,
                        0 AS IMP1, SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO IN ($second_year)
                     AND ID_MES = $second_month
                     AND ID_CUENTAAASI LIKE '232%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     ) GROUP BY CTA,NOMBRE_CTA
        ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function totalLiabilitiesEquity($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "
SELECT
    'TOTAL PASIVO Y PATRIMONIO' AS NOMBRE_CTA,
    ABS(SUM(IMP1)) AS FIRST_YEAR,
    ABS(SUM(IMP2)) AS SECOND_YEAR
FROM (
        SELECT 
                SUM(COS_VALOR) AS IMP1,
                0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
         AND ID_ANHO IN ($first_year)
         AND ID_MES <= $first_month
         AND (ID_CUENTAAASI LIKE '231%' OR
         ID_CUENTAAASI LIKE '2311017' OR
         ID_CUENTAAASI LIKE '2311015' OR
         ID_CUENTAAASI LIKE '232%' OR 
         ID_CUENTAAASI LIKE '21%' OR 
         ID_CUENTAAASI LIKE '22%'
     )
     AND ID_CUENTAAASI NOT LIKE '3141003' 
     AND ID_CUENTAAASI NOT LIKE '2136080'
     GROUP BY ID_ANHO,ID_CUENTAAASI 
     UNION 
     SELECT 
        SUM (COS_VALOR) AS IMP1, 
        0 AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($first_year)
     AND ID_MES <= $first_month
     AND ID_CUENTAAASI LIKE '3%'
     UNION  
     SELECT 
        SUM(COS_VALOR) AS IMP1, 
        0  AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($first_year)
     AND ID_MES <= $first_month
     AND ID_CUENTAAASI LIKE '4%'
     UNION
     SELECT 
        0 AS IMP1,
        SUM(COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($second_year)
     AND ID_MES = $second_month
     AND (ID_CUENTAAASI LIKE '231%' OR
     ID_CUENTAAASI LIKE '2311017' OR
     ID_CUENTAAASI LIKE '2311015' OR
     ID_CUENTAAASI LIKE '232%' OR 
     ID_CUENTAAASI LIKE '21%' OR 
     ID_CUENTAAASI LIKE '22%'
     )
     AND ID_CUENTAAASI NOT LIKE '3141003' 
     AND ID_CUENTAAASI NOT LIKE '2136080'
     GROUP BY ID_ANHO,ID_CUENTAAASI 
     UNION
     SELECT 
        0 AS IMP1, 
        SUM (COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($second_year)
     AND ID_MES = $second_month
     AND ID_CUENTAAASI LIKE '3%'
     UNION
     SELECT
        0  AS IMP1,
        SUM(COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($second_year)
     AND ID_MES = $second_month
     AND ID_CUENTAAASI LIKE '4%'
     )";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function totalLiabilitiesAsset($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
SUM(FIRST_YEAR) AS FIRST_YEAR,
SUM(SECOND_YEAR) AS SECOND_YEAR
FROM (
SELECT
    SUM(IMP1) AS FIRST_YEAR,
    SUM(IMP2) AS SECOND_YEAR
FROM (
        SELECT 
                SUM(COS_VALOR) AS IMP1,
                0 AS IMP2
         FROM VW_CONTA_DIARIO
         WHERE ID_ENTIDAD = $entity
         AND ID_ANHO IN ($first_year)
         AND ID_MES <= $first_month
         AND (ID_CUENTAAASI LIKE '231%' OR
         ID_CUENTAAASI LIKE '2311017' OR
         ID_CUENTAAASI LIKE '2311015' OR
         ID_CUENTAAASI LIKE '232%' OR 
         ID_CUENTAAASI LIKE '21%' OR 
         ID_CUENTAAASI LIKE '22%'
     )
     AND ID_CUENTAAASI NOT LIKE '3141003' 
     AND ID_CUENTAAASI NOT LIKE '2136080'
     UNION 
     SELECT 
        SUM (COS_VALOR) AS IMP1, 
        0 AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($first_year)
     AND ID_MES <= $first_month
     AND ID_CUENTAAASI LIKE '3%'
     UNION  
     SELECT 
        SUM(COS_VALOR) AS IMP1, 
        0  AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity
     AND ID_ANHO IN ($first_year)
     AND ID_MES <= $first_month
     AND ID_CUENTAAASI LIKE '4%'
     UNION
     SELECT 
            0 AS IMP1,
            SUM(COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity 
     AND ID_ANHO IN ($second_year) 
     AND ID_MES <= $second_month
     AND (ID_CUENTAAASI LIKE '231%' OR
     ID_CUENTAAASI LIKE '2311017' OR
     ID_CUENTAAASI LIKE '2311015' OR
     ID_CUENTAAASI LIKE '232%' OR 
     ID_CUENTAAASI LIKE '21%' OR 
     ID_CUENTAAASI LIKE '22%'
     )
     AND ID_CUENTAAASI NOT LIKE '3141003' 
     AND ID_CUENTAAASI NOT LIKE '2136080' 
     UNION
     SELECT 
        0 AS IMP1, 
        SUM (COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity 
     AND ID_ANHO IN ($second_year) 
     AND ID_MES <= $second_month
     AND ID_CUENTAAASI LIKE '3%'
     UNION
     SELECT
        0  AS IMP1,
        SUM(COS_VALOR) AS IMP2
     FROM VW_CONTA_DIARIO
     WHERE ID_ENTIDAD = $entity 
     AND ID_ANHO IN ($second_year) 
     AND ID_MES <= $second_month
     AND ID_CUENTAAASI LIKE '4%'
     ) 
     UNION
     SELECT
        SUM(IMP1) AS FIRST_YEAR,
        SUM(IMP2) AS SECOND_YEAR
     FROM ( SELECT 
                SUM (COS_VALOR) AS IMP1,
                0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO IN ($first_year)
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '11%' OR ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
             AND ID_CUENTAAASI NOT LIKE '1136080'
             UNION 
             SELECT 
                0 AS IMP1,
                SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity 
             AND ID_ANHO IN ($second_year) 
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '11%' OR ID_CUENTAAASI LIKE '12%' OR ID_CUENTAAASI LIKE '13%')
             AND ID_CUENTAAASI NOT LIKE '1136080'
))";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

// REPORT PROFIT LOSS STATEMENT
    public static function inputs($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
	SELECT
	ID_ANHO,
                'ENTRADAS' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) AS CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '31%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
             UNION
             SELECT
	ID_ANHO,
	'ENTRADAS' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) AS CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '31%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function tithe($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
    FROM (   SELECT
    	ID_ANHO,
    	'DIEZMO' AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '311%'
             AND ID_CUENTAAASI NOT IN '3119015'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
	'DIEZMO' AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '311%'
             AND ID_CUENTAAASI NOT IN '3119015'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
    ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function titheAssignment($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (  SELECT
    	ID_ANHO,
    	'(-) ASIGNACIÓN DIEZMO - COMPARTIDO' AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI IN '3119015'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
	'(-) ASIGNACIÓN DIEZMO - COMPARTIDO' AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI IN '3119015'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function netTithe($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
            initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
            SUM(IMP1) AS FIRST_YEAR,
            SUM(IMP2) AS SECOND_YEAR,
            ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
        FROM (
                SELECT
            ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                        SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                        SUM (COS_VALOR) AS IMP1, 0 AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $first_year
                     AND ID_MES <= $first_month
                     AND ID_CUENTAAASI LIKE '311%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                     UNION
                     SELECT
            ID_ANHO,
                        FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                        SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                        0 AS IMP1, SUM (COS_VALOR) AS IMP2
                     FROM VW_CONTA_DIARIO
                     WHERE ID_ENTIDAD = $entity
                     AND ID_ANHO = $second_year
                     AND ID_MES <= $second_month
                     AND ID_CUENTAAASI LIKE '311%'
                     GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
                    ) GROUP BY CTA,NOMBRE_CTAX
        ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailInput($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
    initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
    SUM(IMP1) AS FIRST_YEAR,
    SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
        SELECT
    ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI between '312%' and '316%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
    ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI between '312%' and '316%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function netSales($first_year, $second_year, $first_month, $second_month, $entity)

    {
        $query = "SELECT
    '= Ventas Netas' AS NOMBRE_CTAX,
    SUM(IMP1) AS FIRST_YEAR,
    SUM(IMP2) AS SECOND_YEAR
FROM (     
            SELECT 
                ID_ANHO,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '315%' OR ID_CUENTAAASI LIKE '316%')
             GROUP BY ID_ANHO
             UNION 
             SELECT 
                ID_ANHO,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '315%' OR ID_CUENTAAASI LIKE '316%')
             GROUP BY ID_ANHO
             )";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function salesCost($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '317%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION 
             SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '317%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function resultSales($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
    'Resultado en ventas' AS NOMBRE_CTAX,
    SUM(IMP1)*-1 AS FIRST_YEAR,
    SUM(IMP2)*-1 AS SECOND_YEAR
FROM (     
            SELECT 
                ID_ANHO,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '315%' OR ID_CUENTAAASI LIKE '316%' OR ID_CUENTAAASI LIKE '317%')
             GROUP BY ID_ANHO
             UNION 
             SELECT 
                ID_ANHO,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '315%' OR ID_CUENTAAASI LIKE '316%' OR ID_CUENTAAASI LIKE '317%')
             GROUP BY ID_ANHO
             )";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function otherOperatingIncome($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '318%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '318%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function outputs($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
	SELECT
	ID_ANHO,
	'SALIDAS' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) AS CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '41%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
             UNION
             SELECT
	ID_ANHO,
	'SALIDAS' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) AS CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '41%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,2)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function detailOutput($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTAX) AS NOMBRE_CTAX,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '41%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTAX,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '41%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTAX
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function totalOperatingWithoutSubsidies($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                'RESULTADO OPERATIVO SIN SUBVENCIONES' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
             UNION
             SELECT
	ID_ANHO,
                'RESULTADO OPERATIVO SIN SUBVENCIONES' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 2) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,2)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function netGrantsReceived($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTA) AS NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
	SELECT
	ID_ANHO,
	FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) AS CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '319%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
	FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) AS CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '319%'
             GROUP BY ID_ANHO,ID_MES,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function totalOperatingWithSubsidies($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                'RESULTADO OPERATIVO CON SUBVENCIONES' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%' OR ID_CUENTAAASI LIKE '319%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                'RESULTADO OPERATIVO CON SUBVENCIONES' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%' OR ID_CUENTAAASI LIKE '319%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY NOMBRE_CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function notOperational($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTA) AS NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function nonOperatingResult($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                'RESULTADO NO OPERATIVO' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                'RESULTADO NO OPERATIVO' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
	0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY NOMBRE_CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function transfers($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	initcap(NOMBRE_CTA) AS NOMBRE_CTA,CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND ID_CUENTAAASI LIKE '600%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                FC_CUENTA_DENOMINACIONAL (SUBSTR (ID_CUENTAAASI, 1, 3)) AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND ID_CUENTAAASI LIKE '600%'
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY CTA,NOMBRE_CTA
ORDER BY CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }

    public static function resultExercise($first_year, $second_year, $first_month, $second_month, $entity)
    {
        $query = "SELECT
	NOMBRE_CTA,
	SUM(IMP1) AS FIRST_YEAR,
	SUM(IMP2) AS SECOND_YEAR,
    ROUND((SUM(IMP1)/DECODE(SUM(IMP2),0,1,SUM(IMP2))-1)*100,2) AS VARIACION
FROM (
    	SELECT
	ID_ANHO,
                'RESULTADO DEL EJERCICIO' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                SUM (COS_VALOR) AS IMP1, 0 AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $first_year
             AND ID_MES <= $first_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%' OR ID_CUENTAAASI LIKE '319%' OR ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             UNION
             SELECT
	ID_ANHO,
                'RESULTADO DEL EJERCICIO' AS NOMBRE_CTA,
                SUBSTR (ID_CUENTAAASI, 1, 3) CTA,
                0 AS IMP1, SUM (COS_VALOR) AS IMP2
             FROM VW_CONTA_DIARIO
             WHERE ID_ENTIDAD = $entity
             AND ID_ANHO = $second_year
             AND ID_MES <= $second_month
             AND (ID_CUENTAAASI LIKE '31%' OR ID_CUENTAAASI LIKE '41%' OR ID_CUENTAAASI LIKE '319%' OR ID_CUENTAAASI LIKE '321%' OR ID_CUENTAAASI LIKE '421%')
             GROUP BY ID_ANHO,SUBSTR(ID_CUENTAAASI,1,3)
             ) GROUP BY NOMBRE_CTA";

        $oQuery = DB::select($query);

        return $oQuery == [] ? $oQuery : $oQuery[0];
    }
}
