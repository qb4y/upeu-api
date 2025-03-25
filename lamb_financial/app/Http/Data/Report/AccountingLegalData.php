<?php

/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/01/$mes019
 * Time: 9:1$mes AM
 */

namespace App\Http\Data\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use DateTime;
use PDO;

class AccountingLegalData extends Controller
{
  private $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  public static function libro5_1_last_month_acumulado($id_empresa, $id_entidad, $id_anho, $id_mes)
  {
    return DB::selectOne("SELECT 
                            coalesce(sum(vw_conta_diario.DEBE),0) AS DEBE,
                            coalesce(sum(vw_conta_diario.HABER),0) AS HABER
            FROM eliseo.vw_conta_diario
              inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
            LEFT JOIN (SELECT ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL
              			from CONTA_EMPRESA_CTA GROUP BY ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL ) xxx ON
                vw_conta_diario.ID_EMPRESA = xxx.ID_EMPRESA AND
                vw_conta_diario.ID_TIPOPLAN = xxx.ID_TIPOPLAN AND
                vw_conta_diario.ID_CUENTAAASI = xxx.ID_CUENTAAASI AND
                vw_conta_diario.ID_ANHO = xxx.ID_ANHO 
            LEFT JOIN CONTA_CTA_EMPRESARIAL ON xxx.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
            WHERE 
              conta_empresa.id_empresa = :id_empresa AND
              (vw_conta_diario.ID_ENTIDAD = :id_entidad or -1 = :id_entidad) AND
              vw_conta_diario.ID_ANHO = :id_anho AND
              vw_conta_diario.ID_MES <= :id_mes",  [
      'id_empresa' => $id_empresa,
      'id_entidad' => $id_entidad,
      'id_anho' => $id_anho,
      'id_mes' => $id_mes
    ]);
  }

  public static function libro5_1($id_empresa, $id_entidad, $id_anho, $id_mes)
  {
    // $query = "SELECT orden, NUM_AASI, id_cuentaempresarial, ID_CTACTE, nombre, comentario, debe, haber FROM (
    //     SELECT 
    //                       vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
    //                       2 AS orden,
    //                       TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[0-9]*[.]'),'.',''),'9999999') AS ITEM1, 
    //                        TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[.][0-9]*'),'.',''),'9999999') AS ITEM2, 
    //                       vw_conta_diario.NUM_AASI,  
    //                       CONTA_Empresa_Cta.ID_CUENTAEMPRESARIAL,
    //                       vw_conta_diario.ID_CTACTE,
    //                       CONTA_CTA_EMPRESARIAL.NOMBRE,
    //                       vw_conta_diario.COMENTARIO,
    //                       vw_conta_diario.DEBE,
    //                       vw_conta_diario.HABER 
    //       FROM vw_conta_diario
    //       LEFT JOIN CONTA_EMPRESA_CTA ON
    //         vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA AND
    //         vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN AND
    //         vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI AND
    //         vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION AND
    //         vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
    //       LEFT JOIN CONTA_CTA_EMPRESARIAL ON CONTA_EMPRESA_CTA.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
    //       WHERE 
    //         vw_conta_diario.ID_ENTIDAD = $entidad AND
    //         vw_conta_diario.ID_ANHO = $anho AND
    //         vw_conta_diario.ID_MES = $mes
    //     UNION ALL 
    //     SELECT 
    //            vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
    //            1 AS orden,
    //             to_number('0','9999999') AS item1,
    //             to_number('0','9999999') AS item2,
    //             to_char(vw_conta_diario.FEC_ASIENTO,'dd/mm/yyyy'), 
    //             '',
    //             vw_conta_diario.LOTE,
    //             NULL, NULL, NULL, NULL
    //       FROM vw_conta_diario
    //       LEFT JOIN CONTA_EMPRESA_CTA ON
    //         vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA AND
    //         vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN AND
    //         vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI AND
    //         vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION AND
    //         vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
    //       LEFT JOIN CONTA_CTA_EMPRESARIAL ON CONTA_EMPRESA_CTA.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
    //       WHERE 
    //         vw_conta_diario.ID_ENTIDAD = $entidad AND
    //         vw_conta_diario.ID_ANHO = $anho AND
    //         vw_conta_diario.ID_MES = $mes
    //         GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
    //         UNION ALL 
    //     SELECT 
    //            vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
    //            3 AS orden,
    //            0 AS item1,
    //             0 AS item2,
    //            NULL, 		
    //      NULL, NULL, NULL, NULL,
    //             sum(DEBE), sum(HABER)
    //       FROM vw_conta_diario
    //       LEFT JOIN CONTA_EMPRESA_CTA ON
    //         vw_conta_diario.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA AND
    //         vw_conta_diario.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN AND
    //         vw_conta_diario.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI AND
    //         vw_conta_diario.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION AND
    //         vw_conta_diario.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
    //       LEFT JOIN CONTA_CTA_EMPRESARIAL ON CONTA_EMPRESA_CTA.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
    //       WHERE 
    //         vw_conta_diario.ID_ENTIDAD = $entidad AND
    //         vw_conta_diario.ID_ANHO = $anho AND
    //         vw_conta_diario.ID_MES = $mes
    //       GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
    //     ) a 
    //     ORDER BY 
    //     id_tipoasiento, cod_aasi, fec_asiento, lote, orden, item1,item2";
    $query = "SELECT orden, NUM_AASI,to_char(FEC_ASIENTO,'dd/mm/yyyy') FEC_ASIENTO, id_cuentaempresarial, ID_CUENTAAASI, ID_CTACTE, nombre, comentario, debe, haber FROM (
            SELECT 
                              vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                              2 AS orden,
                              TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[0-9]*[.]'),'.',''),'9999999') AS ITEM1, 
                              TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[.][0-9]*'),'.',''),'9999999') AS ITEM2, 
                              vw_conta_diario.NUM_AASI,  
                              vw_conta_diario.ID_CUENTAAASI ,  
                              xxx.ID_CUENTAEMPRESARIAL,
                              vw_conta_diario.ID_CTACTE,
                              CONTA_CTA_EMPRESARIAL.NOMBRE,
                              vw_conta_diario.COMENTARIO,
                              vw_conta_diario.DEBE,
                              vw_conta_diario.HABER 
              FROM vw_conta_diario
              inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
              LEFT JOIN (SELECT ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL
              			from CONTA_EMPRESA_CTA GROUP BY ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL ) xxx ON
                vw_conta_diario.ID_EMPRESA = xxx.ID_EMPRESA AND
                vw_conta_diario.ID_TIPOPLAN = xxx.ID_TIPOPLAN AND
                vw_conta_diario.ID_CUENTAAASI = xxx.ID_CUENTAAASI AND
                vw_conta_diario.ID_ANHO = xxx.ID_ANHO 
              LEFT JOIN CONTA_CTA_EMPRESARIAL ON xxx.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
              WHERE 
                vw_conta_diario.ID_EMPRESA = :id_empresa AND
                (vw_conta_diario.ID_ENTIDAD = :id_entidad or -1 = :id_entidad) AND
                vw_conta_diario.ID_ANHO = :id_anho AND
                vw_conta_diario.ID_MES = :id_mes
            UNION ALL 
            SELECT 
                   vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                   1 AS orden,
                    to_number('0','9999999') AS item1,
                    to_number('0','9999999') AS item2,
                    to_char(vw_conta_diario.FEC_ASIENTO,'dd/mm/yyyy'), 
                    '',
                    '',
                    null,
                    NULL, vw_conta_diario.LOTE, NULL, NULL
              FROM vw_conta_diario
              inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
              WHERE 
                vw_conta_diario.ID_EMPRESA = :id_empresa AND
                (vw_conta_diario.ID_ENTIDAD = :id_entidad or -1 = :id_entidad) AND
                vw_conta_diario.ID_ANHO = :id_anho AND
                vw_conta_diario.ID_MES = :id_mes
                GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
                UNION ALL 
            SELECT 
                   vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                   3 AS orden,
                   0 AS item1,
                    0 AS item2,
                   NULL, NULL, 	
                    NULL, NULL, NULL,NULL,
                    sum(DEBE), sum(HABER)
              FROM vw_conta_diario
              inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
              WHERE 
                vw_conta_diario.ID_EMPRESA = :id_empresa AND
                (vw_conta_diario.ID_ENTIDAD = :id_entidad or -1 = :id_entidad) AND
                vw_conta_diario.ID_ANHO = :id_anho AND
                vw_conta_diario.ID_MES = :id_mes
              GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
            ) a
            ORDER BY 
            id_tipoasiento, cod_aasi, fec_asiento, lote, orden, item1, item2";
    $oQuery = DB::select($query, [
      'id_empresa' => $id_empresa,
      'id_entidad' => $id_entidad,
      'id_anho' => $id_anho,
      'id_mes' => $id_mes
    ]);
    return $oQuery;
  }


  public static function libro5_1_upn($id_empresa, $id_entidad, $id_anho, $id_mes)
  {
    $query = "SELECT orden, NUM_AASI,to_char(FEC_ASIENTO,'dd/mm/yyyy') FEC_ASIENTO, id_cuentaempresarial, ID_CUENTAAASI, ID_CTACTE, nombre, comentario, debe, haber FROM (
          SELECT 
                            vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                            2 AS orden,
                            TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[0-9]*[.]'),'.',''),'9999999') AS ITEM1, 
                            TO_NUMBER(REPLACE(regexp_substr(vw_conta_diario.NUM_AASI, '[.][0-9]*'),'.',''),'9999999') AS ITEM2, 
                            vw_conta_diario.NUM_AASI,  
                            vw_conta_diario.ID_CUENTAAASI ,  
                            xxx.ID_CUENTAEMPRESARIAL,
                            vw_conta_diario.ID_CTACTE,
                            CONTA_CTA_EMPRESARIAL.NOMBRE,
                            vw_conta_diario.COMENTARIO,
                            vw_conta_diario.DEBE,
                            vw_conta_diario.HABER 
            FROM vw_conta_diario
            inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
            LEFT JOIN (SELECT ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL
                  from CONTA_EMPRESA_CTA GROUP BY ID_EMPRESA,ID_TIPOPLAN,ID_CUENTAAASI,ID_ANHO, ID_CUENTAEMPRESARIAL ) xxx ON
              vw_conta_diario.ID_EMPRESA = xxx.ID_EMPRESA AND
              vw_conta_diario.ID_TIPOPLAN = xxx.ID_TIPOPLAN AND
              vw_conta_diario.ID_CUENTAAASI = xxx.ID_CUENTAAASI AND
              vw_conta_diario.ID_ANHO = xxx.ID_ANHO 
            LEFT JOIN CONTA_CTA_EMPRESARIAL ON xxx.ID_CUENTAEMPRESARIAL = CONTA_CTA_EMPRESARIAL.ID_CUENTAEMPRESARIAL 
            WHERE 
              vw_conta_diario.ID_EMPRESA = :id_empresa AND
              (vw_conta_diario.ID_ENTIDAD = :id_entidad) AND
              vw_conta_diario.ID_ANHO = :id_anho AND
              vw_conta_diario.ID_MES = :id_mes
          UNION ALL 
          SELECT 
                 vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                 1 AS orden,
                  to_number('0','9999999') AS item1,
                  to_number('0','9999999') AS item2,
                  to_char(vw_conta_diario.FEC_ASIENTO,'dd/mm/yyyy'), 
                  '',
                  '',
                  null,
                  NULL, vw_conta_diario.LOTE, NULL, NULL
            FROM vw_conta_diario
            inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
            WHERE 
              vw_conta_diario.ID_EMPRESA = :id_empresa AND
              (vw_conta_diario.ID_ENTIDAD = :id_entidad) AND
              vw_conta_diario.ID_ANHO = :id_anho AND
              vw_conta_diario.ID_MES = :id_mes
              GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
              UNION ALL 
          SELECT 
                 vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE,
                 3 AS orden,
                 0 AS item1,
                  0 AS item2,
                 NULL, NULL, 	
                  NULL, NULL, NULL,NULL,
                  sum(DEBE), sum(HABER)
            FROM vw_conta_diario
            inner join conta_empresa on vw_conta_diario.ID_EMPRESA = conta_empresa.ID_EMPRESA
            WHERE 
              vw_conta_diario.ID_EMPRESA = :id_empresa AND
              (vw_conta_diario.ID_ENTIDAD = :id_entidad) AND
              vw_conta_diario.ID_ANHO = :id_anho AND
              vw_conta_diario.ID_MES = :id_mes
            GROUP BY vw_conta_diario.FEC_ASIENTO,vw_conta_diario.COD_AASI, vw_conta_diario.ID_TIPOASIENTO , vw_conta_diario.LOTE
          ) a
          ORDER BY 
          id_tipoasiento, cod_aasi, fec_asiento, lote, orden, item1, item2";
    $oQuery = DB::select($query, [
      'id_empresa' => $id_empresa,
      'id_entidad' => $id_entidad,
      'id_anho' => $id_anho,
      'id_mes' => $id_mes
    ]);
    return $oQuery;
  }


  public static function libro_mayor($empresa, $entidad, $anho, $mes)
  {
    $query = "SELECT orden, fecha, lote, COMENTARIO, DEBE, HABER, SALDO FROM (
        SELECT DISTINCT 1 AS orden, id_cuentaaasi, NULL AS FEC_ASIENTO , 'CUENTA' AS fecha, ''||id_cuentaaasi AS lote, '' AS comentario, null AS debe, null AS haber,
        null AS saldo, null AS ID_DIARIO, null AS ID_DIARIO_DETALLE 
        FROM VW_CONTA_DIARIO vcd 
        WHERE id_entidad = $entidad
        AND id_anho = $anho
        AND ID_MES = $mes
        UNION all
        SELECT $mes AS orden, id_cuentaaasi, FEC_ASIENTO, to_char(FEC_ASIENTO,'dd/mm/yyyy') AS fecha, LOTE, COMENTARIO, DEBE, HABER, 
        SUM(COS_VALOR) OVER (PARTITION BY id_cuentaaasi ORDER BY id_cuentaaasi, FEC_ASIENTO, ID_DIARIO, ID_DIARIO_DETALLE  ROWS UNBOUNDED PRECEDING) SALDO,
        ID_DIARIO, ID_DIARIO_DETALLE 
        FROM VW_CONTA_DIARIO vcd 
        WHERE id_entidad = $entidad
        AND id_anho = $anho
        AND ID_MES = $mes
        UNION ALL 
        SELECT 	        3 AS orden, id_cuentaaasi, NULL, '',       '',                 'Saldo', sum(DEBE), sum(HABER),
        sum(COS_VALOR) AS saldo, null AS ID_DIARIO, null AS ID_DIARIO_DETALLE 
        FROM VW_CONTA_DIARIO vcd 
        WHERE id_entidad = $entidad
        AND id_anho = $anho
        AND ID_MES = $mes
        GROUP BY id_cuentaaasi
        ORDER BY ID_CUENTAAASI, orden, FEC_ASIENTO, ID_DIARIO, ID_DIARIO_DETALLE 
        ) a 
      ";
    $oQuery = DB::select($query);
    return $oQuery;
  }
    public static function get_libro_mayor_upn($id_empresa, $id_entidad, $id_anho, $id_mes){

      $query = "WITH cuentas AS (
								SELECT vcd_actual.id_cuentaaasi
			                              FROM VW_CONTA_DIARIO vcd_actual
			                              WHERE vcd_actual.id_entidad = :id_entidad
			                                AND vcd_actual.id_anho = :id_anho
			                                AND vcd_actual.id_mes <= :id_mes
			                                GROUP BY vcd_actual.id_cuentaaasi
                  )
                ,
                    main_query AS (
                        SELECT 
                            0 AS orden, 
                            vcd.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'CUENTA' AS lote, 
                            '' AS comentario, 
                            NULL AS debe, 
                            NULL AS haber, 
                            0 AS COS_VALOR, 
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas vcd 
                        UNION ALL
                        SELECT
                          1 AS orden,
                          xx.id_cuentaaasi,
                          NULL AS FEC_ASIENTO,
                          '' AS fecha,
                          'MES_ANTERIOR' AS lote,
                          NULL AS comentario,
                          SUM(vcd.DEBE) AS debe,  -- Cambiado para ser número
                          SUM(vcd.HABER) AS haber, -- Cambiado a NULL
                          SUM(vcd.COS_VALOR) AS COS_VALOR,
                          NULL AS ID_DIARIO,
                          NULL AS ID_DIARIO_DETALLE
                      FROM cuentas xx
                      left JOIN eliseo.VW_CONTA_DIARIO vcd ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                      		and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                        	AND vcd.id_anho = :id_anho
                        	AND vcd.id_mes < :id_mes 
                          GROUP BY xx.id_cuentaaasi
                        UNION ALL 
                        --
                        SELECT 
                            2 AS orden, 
                            xx.id_cuentaaasi, 
                            vcd.FEC_ASIENTO, 
                            TO_CHAR(vcd.FEC_ASIENTO, 'dd/mm/yyyy') AS fecha, 
                            vcd.LOTE, 
                            vcd.COMENTARIO AS comentario, 
                            vcd.DEBE AS debe, 
                            vcd.HABER AS haber, 
                            vcd.COS_VALOR, 
                            vcd.ID_DIARIO, 
                            vcd.ID_DIARIO_DETALLE 
                        FROM cuentas xx 
                        LEFT JOIN VW_CONTA_DIARIO vcd ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes = :id_mes
                        --
                        UNION ALL
                        --
                        SELECT 
                            3 AS orden, 
                            xx.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_MES' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes = :id_mes
                          GROUP BY xx.id_cuentaaasi
                          UNION all
                         SELECT 
                            4 AS orden, 
                            xx.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_AL_MES' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes <= :id_mes
                          GROUP BY xx.id_cuentaaasi
                        --
                          UNION ALL
                        SELECT 
                            5 AS orden, 
                            '' AS id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_TODO' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes <= :id_mes
                    )
                    SELECT 
                         a.orden, 
                         a.fecha, 
                         a.lote,
                         a.id_cuentaaasi,
                         c.ID_CUENTAEMPRESARIAL,
                         CASE
                             WHEN a.orden IN (0,3) THEN COALESCE(c.NOMBRE, 'Sin equivalencia')
                             ELSE a.comentario
                         END AS comentario
                         ,a.debe 
                         ,a.haber
                         ,SUM(a.COS_VALOR) OVER (PARTITION BY a.id_cuentaaasi ORDER BY a.id_cuentaaasi, a.orden, a.FEC_ASIENTO, a.ID_DIARIO, a.ID_DIARIO_DETALLE ROWS UNBOUNDED PRECEDING) AS saldo
                FROM main_query a
                    LEFT JOIN (select id_empresa,id_tipoplan,id_cuentaaasi,id_anho,id_cuentaempresarial 
                              from eliseo.conta_empresa_cta 
                          		group by id_empresa,id_tipoplan,id_cuentaaasi,id_anho,id_cuentaempresarial) b
                        ON a.id_cuentaaasi = b.id_cuentaaasi
                        and b.id_tipoplan = 1
                        and b.id_empresa = :id_empresa
                        and b.id_anho = :id_anho
                    LEFT JOIN eliseo.conta_cta_empresarial c
                        ON b.id_cuentaempresarial = c.id_cuentaempresarial
                    WHERE 1=1
                    ORDER BY a.id_cuentaaasi, a.orden, a.FEC_ASIENTO, a.ID_DIARIO, a.ID_DIARIO_DETALLE
                    ";
    $oQuery = DB::select($query, [
      'id_empresa' => $id_empresa,
      'id_entidad' => $id_entidad,
      'id_anho' => $id_anho,
      'id_mes' => $id_mes,
    ]);
    return $oQuery;
  }

  public static function get_libro_mayor_upn_totales($id_empresa, $id_entidad, $id_anho, $id_mes){

    $query = "WITH cuentas AS (
								SELECT vcd_actual.id_cuentaaasi
			                              FROM VW_CONTA_DIARIO vcd_actual
			                              WHERE vcd_actual.id_empresa = :id_empresa 
			                              	--AND (vcd_actual.id_entidad = :id_entidad or -1 = :id_entidad)
			                                AND vcd_actual.id_anho = :id_anho
			                                AND vcd_actual.id_mes <= :id_mes
			                                GROUP BY vcd_actual.id_cuentaaasi
                  )
                ,
                    main_query AS (
                        SELECT 
                            0 AS orden, 
                            vcd.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'CUENTA' AS lote, 
                            '' AS comentario, 
                            NULL AS debe, 
                            NULL AS haber, 
                            0 AS COS_VALOR, 
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas vcd 
                        UNION ALL
                        SELECT
                          1 AS orden,
                          xx.id_cuentaaasi,
                          NULL AS FEC_ASIENTO,
                          '' AS fecha,
                          'MES_ANTERIOR' AS lote,
                          NULL AS comentario,
                          SUM(vcd.DEBE) AS debe,  -- Cambiado para ser número
                          SUM(vcd.HABER) AS haber, -- Cambiado a NULL
                          SUM(vcd.COS_VALOR) AS COS_VALOR,
                          NULL AS ID_DIARIO,
                          NULL AS ID_DIARIO_DETALLE
                      FROM cuentas xx
                      left JOIN eliseo.VW_CONTA_DIARIO vcd ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                      		--and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                        	AND vcd.id_anho = :id_anho
                        	AND vcd.id_mes < :id_mes 
                          GROUP BY xx.id_cuentaaasi
                        /*UNION ALL 
                        --
                        SELECT 
                            2 AS orden, 
                            xx.id_cuentaaasi, 
                            vcd.FEC_ASIENTO, 
                            TO_CHAR(vcd.FEC_ASIENTO, 'dd/mm/yyyy') AS fecha, 
                            vcd.LOTE, 
                            vcd.COMENTARIO AS comentario, 
                            vcd.DEBE AS debe, 
                            vcd.HABER AS haber, 
                            vcd.COS_VALOR, 
                            vcd.ID_DIARIO, 
                            vcd.ID_DIARIO_DETALLE 
                        FROM cuentas xx 
                        LEFT JOIN VW_CONTA_DIARIO vcd ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          --and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes = :id_mes */
                        --
                        UNION ALL
                        --
                        SELECT 
                            3 AS orden, 
                            xx.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_MES' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          --and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes = :id_mes
                          GROUP BY xx.id_cuentaaasi
                          UNION all
                         SELECT 
                            4 AS orden, 
                            xx.id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_AL_MES' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          --and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes <= :id_mes
                          GROUP BY xx.id_cuentaaasi
                        --
                          UNION ALL
                        SELECT 
                            5 AS orden, 
                            '' AS id_cuentaaasi, 
                            NULL AS FEC_ASIENTO, 
                            '' AS fecha, 
                            'TOTAL_TODO' AS lote, 
                            '' AS comentario, 
                            SUM(vcd.DEBE) AS debe, 
                            SUM(vcd.HABER) AS haber, 
                            0 AS COS_VALOR,
                            NULL AS ID_DIARIO, 
                            NULL AS ID_DIARIO_DETALLE 
                        FROM cuentas xx
                        LEFT JOIN VW_CONTA_DIARIO vcd  ON xx.id_cuentaaasi=vcd.id_cuentaaasi
                          and vcd.id_empresa = :id_empresa
                          --and (vcd.id_entidad = :id_entidad or -1 = :id_entidad)
                          AND vcd.id_anho = :id_anho
                          AND vcd.id_mes <= :id_mes
                    )
                    SELECT 
                         a.orden, 
                         a.fecha, 
                         a.lote,
                         a.id_cuentaaasi,
                         c.ID_CUENTAEMPRESARIAL,
                         CASE
                             WHEN a.orden IN (0,3) THEN COALESCE(c.NOMBRE, 'Sin equivalencia')
                             ELSE a.comentario
                         END AS comentario
                         ,a.debe 
                         ,a.haber
                         ,SUM(a.COS_VALOR) OVER (PARTITION BY a.id_cuentaaasi ORDER BY a.id_cuentaaasi, a.orden, a.FEC_ASIENTO, a.ID_DIARIO, a.ID_DIARIO_DETALLE ROWS UNBOUNDED PRECEDING) AS saldo
                FROM main_query a
                    LEFT JOIN (select id_empresa,id_tipoplan,id_cuentaaasi,id_anho,id_cuentaempresarial 
                              from eliseo.conta_empresa_cta 
                          		group by id_empresa,id_tipoplan,id_cuentaaasi,id_anho,id_cuentaempresarial) b
                        ON a.id_cuentaaasi = b.id_cuentaaasi
                        and b.id_tipoplan = 1
                        and b.id_empresa = :id_empresa
                        and b.id_anho = :id_anho
                    LEFT JOIN eliseo.conta_cta_empresarial c
                        ON b.id_cuentaempresarial = c.id_cuentaempresarial
                    WHERE 1=1
                    ORDER BY a.id_cuentaaasi, a.orden, a.FEC_ASIENTO, a.ID_DIARIO, a.ID_DIARIO_DETALLE
                  ";
  $oQuery = DB::select($query, [
    'id_empresa' => $id_empresa,
    // 'id_entidad' => $id_entidad,
    'id_anho' => $id_anho,
    'id_mes' => $id_mes,
  ]);
  return $oQuery;
}




  public static function getPCGEExportData($id_empresa, $id_entidad, $id_anho, $id_mes){
   
      // Comprobar si los parámetros son nulos y asignarles un valor por defecto
      $id_empresa = $id_empresa ?? '';  // Si es null, asignamos un valor vacío
      $id_anho = $id_anho ?? '';      // Lo mismo para $id_anho
    
      // Definir la consulta SQL con los parámetros que se usarán
      $query = "SELECT 
              a.ID_CUENTAEMPRESARIAL AS codigo_empresarial,
              a.id_parent AS codigo_parent,
              a.NOMBRE AS nombre_empresarial,
              c.NOMBRE AS nombre_denominacional,
              b.ID_CUENTAAASI AS codigo_aaasi,
              b.ID_ANHO
          FROM ELISEO.CONTA_CTA_EMPRESARIAL a
          LEFT JOIN ELISEO.CONTA_EMPRESA_CTA b 
              ON a.ID_CUENTAEMPRESARIAL = b.ID_CUENTAEMPRESARIAL
              AND b.ID_EMPRESA = :id_empresa
              AND b.ID_ANHO = :id_anho
          LEFT JOIN ELISEO.CONTA_CTA_DENOMINACIONAL c 
              ON b.ID_CUENTAAASI = c.ID_CUENTAAASI
              --AND c.ID_TIPOPLAN = 1
          LEFT JOIN ELISEO.CONTA_EMPRESA d ON b.ID_EMPRESA = d.ID_EMPRESA
          --ORDER BY TO_NUMBER(a.ID_CUENTAEMPRESARIAL)
      ";
  
      // Ejecutar la consulta usando DB::select y pasar los parámetros correctamente
      return DB::select($query, [
          'id_empresa' => $id_empresa,
          'id_anho' => $id_anho,
      ]);


  }
  
  public static function getExportPCGEaPCDDataTab($id_empresa, $id_entidad, $id_anho, $id_mes){
    
    // Comprobar si los parámetros son nulos y asignarles un valor por defecto
      $id_empresa = $id_empresa ?? '';  // Si es null, asignamos un valor vacío
      $id_anho = $id_anho ?? '';      // Lo mismo para $id_anho

      $query="SELECT 
              b.ID_EMPRESA,
              b.ID_ANHO,
              a.ID_CUENTAEMPRESARIAL AS codigo_empresarial,
              a.id_parent AS codigo_parent,
              a.NOMBRE AS nombre_empresarial,
              c.NOMBRE AS nombre_denominacional,
              b.ID_CUENTAAASI AS codigo_aaasi
          FROM ELISEO.CONTA_CTA_EMPRESARIAL a
          LEFT JOIN ELISEO.CONTA_EMPRESA_CTA b 
              ON a.ID_CUENTAEMPRESARIAL = b.ID_CUENTAEMPRESARIAL
              AND b.ID_EMPRESA = :id_empresa
              AND b.ID_ANHO = :id_anho
          LEFT JOIN ELISEO.CONTA_CTA_DENOMINACIONAL c 
              ON b.ID_CUENTAAASI = c.ID_CUENTAAASI          
          LEFT JOIN ELISEO.CONTA_EMPRESA d ON b.ID_EMPRESA = d.ID_EMPRESA
          ORDER BY TO_NUMBER(a.ID_CUENTAEMPRESARIAL)
          ";

      // Ejecutar la consulta usando DB::select y pasar los parámetros correctamente
      return DB::select($query, [
          'id_empresa' => $id_empresa,
          'id_anho' => $id_anho,
      ]);
  }

  public static function getExportPCDaPCGEDataTab($id_empresa, $id_entidad, $id_anho, $id_mes){
    
    // Comprobar si los parámetros son nulos y asignarles un valor por defecto
      // $id_empresa = $id_empresa ?? '';  // Si es null, asignamos un valor vacío
      // $id_anho = $id_anho ?? '';      // Lo mismo para $id_anho
      
      $query=" SELECT
          		b.ID_ANHO, b.ID_EMPRESA,
                c.ID_CUENTAAASI AS codigo_aaasi,
                c.ID_PARENT AS codigo_parent,
                c.NOMBRE AS nombre_denominacional,
                a.NOMBRE AS nombre_empresarial,
                b.ID_CUENTAEMPRESARIAL AS codigo_empresarial
            FROM ELISEO.CONTA_CTA_DENOMINACIONAL c
                LEFT JOIN ELISEO.CONTA_EMPRESA_CTA b ON c.ID_CUENTAAASI = b.ID_CUENTAAASI AND b.ID_EMPRESA = $id_empresa AND b.ID_ANHO = $id_anho
                LEFT JOIN ELISEO.CONTA_CTA_EMPRESARIAL a ON b.ID_CUENTAEMPRESARIAL = a.ID_CUENTAEMPRESARIAL
                LEFT JOIN ELISEO.CONTA_EMPRESA d ON b.ID_EMPRESA = d.ID_EMPRESA
                LEFT JOIN ELISEO.TIPO_CTA_CORRIENTE tcc ON c.ID_TIPOCTACTE = tcc.ID_TIPOCTACTE
            WHERE 1 = 1
                AND c.ID_TIPOPLAN = 1               
            ORDER BY codigo_aaasi
          ";

      // // Ejecutar la consulta usando DB::select y pasar los parámetros correctamente
      // return DB::select($query, [
      //     'id_empresa' => $id_empresa,
      //     'id_anho' => $id_anho,
      // ]);

      $oQuery = DB::select($query);
      return $oQuery;
  }
  
  
//   public static function insertDataPCGEExcel($listaValid, $id_empresa, $id_anho)
// {
//     $nerror = 0;
//     $msgerror = "";
//     $countInsertados = 0;

//     DB::beginTransaction();
//     try {
//         foreach ($listaValid as $datos) {
//             $items = (object) $datos;

//             // Verificar si ya existe el registro en la tabla
//             $exists = DB::table('ELISEO.CONTA_EMPRESA_CTA')
//                 ->where('ID_EMPRESA', $id_empresa)
//                 ->where('ID_ANHO', $id_anho)
//                 ->where('ID_CUENTAEMPRESARIAL', $items->codigo_empresarial)
//                 ->exists();

//             if (!$exists) {
//                 // Insertar un nuevo registro
//                 DB::table('ELISEO.CONTA_EMPRESA_CTA')->insert([
//                     'ID_EMPRESA' => $id_empresa,
//                     'ID_ANHO' => $id_anho,
//                     'ID_CUENTAEMPRESARIAL' => $items->codigo_empresarial,
//                     'ID_CUENTAAASI' => $items->codigo_aaasi,
//                 ]);
//                 $countInsertados++;
//             } else {
//                 // Aquí podrías manejar una lógica de actualización si es necesario
//             }
//         }

//         DB::commit();
//         $msgerror = "Se insertaron $countInsertados registros correctamente.";
//     } catch (\Exception $e) {
//         DB::rollBack();
//         $nerror = 1;
//         $msgerror = "Error al insertar los datos: " . $e->getMessage();
//     }

//     return [
//         'nerror' => $nerror,
//         'msgerror' => $msgerror,
//         'registros_insertados' => $countInsertados,
//     ];
// }

}

