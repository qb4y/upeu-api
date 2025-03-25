<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/01/2019
 * Time: 9:12 AM
 */

namespace App\Http\Data\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Log;
use PDO;

class ManagementData extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function listMyDepartment($id_persona){
        $query = "SELECT
                A.ID_DEPTO, A.NOMBRE,B.ID AS ID_PERSONA,B.ESTADO, A.ES_EMPRESA
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND B.ID = ".$id_persona."
                AND ES_EMPRESA = '1'
                AND B.ACTIVO = '1'
                ORDER BY A.ID_DEPTO ";
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
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad=" IS NOT NULL";
        }else{
            $id_entidad=" = $id_entidad";
        }
        if($id_depto===null or $id_depto==='*' or $id_depto==='0'){
            $id_depto=" IS NOT NULL";
        }else{
            $id_depto=" = '$id_depto'";
        }
        if($id_anho===null or $id_anho==='*'){
            $id_anho=" IS NOT NULL";
        }else{
            $id_anho=" = $id_anho";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes="<=12";
        }else{
            $id_mes=" <= $id_mes";
        }
        if($id_fondo===null or $id_fondo==='*'){
            $id_fondo=" IS NOT NULL";
        }else{
            $id_fondo=" = $id_fondo";
        }
        $query=null;
        if($digito==='7'){
            $query="SELECT CODIGO_PARENT,CASE WHEN ES_GRUPO=1 THEN CODIGO_PARENT ELSE SUBSTR(CODIGO,0,$digito) END AS CODIGO,
            CASE WHEN ES_GRUPO=1 THEN
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=RPAD(CODIGO_PARENT,7,'0'))
            ELSE
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=CODIGO)
            END AS CUENTA,NULL AS ID_CTACTE,NULL AS NOMBRE,
            DEBE,HABER,SALDO,ES_GRUPO
            FROM (SELECT CODIGO_PARENT,CODIGO,
                    SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO,
                    GROUPING(CODIGO) AS ES_GRUPO
                    FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS CODIGO_PARENT,
                    A.ID_CUENTAAASI AS CODIGO,
                    SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO
                FROM CONTA_CTA_DENOMINACIONAL A
                INNER JOIN (SELECT ID_CUENTAAASI,
                                SUM(DEBE) AS DEBE,
                                SUM(HABER) AS HABER,
                                SUM(COS_VALOR) AS SALDO
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA  $id_empresa
                            AND ID_ENTIDAD $id_entidad
                            AND ID_DEPTO $id_depto
                            AND ID_ANHO $id_anho
                            AND ID_MES $id_mes
                            AND ID_FONDO $id_fondo
                            AND ID_TIPOASIENTO NOT IN('EA')
                            GROUP BY ID_CUENTAAASI) B ON B.ID_CUENTAAASI=A.ID_CUENTAAASI
                GROUP BY A.ID_CUENTAAASI)
            GROUP BY ROLLUP (CODIGO_PARENT, CODIGO)
            ORDER BY CODIGO_PARENT ASC,ES_GRUPO DESC, CODIGO ASC)";
        }elseif($digito==='8'){
            $query = "SELECT CODIGO_PARENT,CASE WHEN ES_GRUPO=1 THEN CODIGO_PARENT ELSE CODIGO END AS CODIGO,
            CASE WHEN ES_GRUPO=1 THEN
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=RPAD(CODIGO_PARENT,7,'0'))
            ELSE
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=CODIGO)
            END AS CUENTA,ID_CTACTE,NOMBRE,
            DEBE,HABER,SALDO,ES_GRUPO
            FROM (SELECT CODIGO_PARENT,CODIGO,ID_CTACTE,NOMBRE,
            SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO,
            GROUPING(CODIGO) AS ES_GRUPO
            FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS CODIGO_PARENT,
                            A.ID_CUENTAAASI AS CODIGO,B.ID_CTACTE,C.NOMBRE,
                            SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO
                FROM CONTA_CTA_DENOMINACIONAL A
                INNER JOIN (SELECT ID_CUENTAAASI,ID_CTACTE,ID_ENTIDAD,
                                SUM(DEBE) AS DEBE,
                                SUM(HABER) AS HABER,
                                SUM(COS_VALOR) AS SALDO
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA  $id_empresa
                            AND ID_ENTIDAD $id_entidad
                            AND ID_DEPTO $id_depto
                            AND ID_ANHO $id_anho
                            AND ID_MES $id_mes
                            AND ID_FONDO $id_fondo
                            AND ID_TIPOASIENTO NOT IN('EA')
                            GROUP BY ID_CUENTAAASI,ID_CTACTE,ID_ENTIDAD) B ON B.ID_CUENTAAASI=A.ID_CUENTAAASI
                LEFT JOIN CONTA_ENTIDAD_CTA_CTE C ON C.ID_CTACTE=B.ID_CTACTE AND C.ID_ENTIDAD=B.ID_ENTIDAD AND C.ID_TIPOCTACTE=A.ID_TIPOCTACTE
                GROUP BY A.ID_CUENTAAASI,B.ID_CTACTE,C.NOMBRE)
            GROUP BY ROLLUP (CODIGO_PARENT, (CODIGO,ID_CTACTE,NOMBRE))
            ORDER BY CODIGO_PARENT ASC,ES_GRUPO DESC, CODIGO ASC)";
        }else{
        $query="SELECT CODIGO_PARENT,CASE WHEN ES_GRUPO=1 THEN CODIGO_PARENT ELSE SUBSTR(CODIGO,0,$digito) END AS CODIGO,
            CASE WHEN ES_GRUPO=1 THEN
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=RPAD(CODIGO_PARENT,7,'0'))
            ELSE
            (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=CODIGO)
            END AS CUENTA,NULL AS ID_CTACTE,NULL AS NOMBRE,
            DEBE,HABER,SALDO,ES_GRUPO
            FROM (SELECT CODIGO_PARENT,CODIGO,
                    SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO,
                    GROUPING(CODIGO) AS ES_GRUPO
                    FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS CODIGO_PARENT,
                    A.ID_CUENTAAASI AS CODIGO,
                    SUM(DEBE) AS DEBE,SUM(HABER) AS HABER,SUM(SALDO) AS SALDO
                FROM CONTA_CTA_DENOMINACIONAL A
                INNER JOIN (SELECT ID_CUENTAAASI,
                                SUM(DEBE) AS DEBE,
                                SUM(HABER) AS HABER,
                                SUM(COS_VALOR) AS SALDO
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA  $id_empresa
                            AND ID_ENTIDAD $id_entidad
                            AND ID_DEPTO $id_depto
                            AND ID_ANHO $id_anho
                            AND ID_MES $id_mes
                            AND ID_FONDO $id_fondo
                            AND ID_TIPOASIENTO NOT IN('EA')
                            GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,$digito)=SUBSTR(A.ID_CUENTAAASI,0,$digito)
                WHERE SUBSTR(A.ID_CUENTAAASI,1+$digito,7)=RPAD('0',7-$digito,'0')
                GROUP BY A.ID_CUENTAAASI)
            GROUP BY ROLLUP (CODIGO_PARENT, CODIGO)
            ORDER BY CODIGO_PARENT ASC,ES_GRUPO DESC, CODIGO ASC)";
        }

        //print($query);

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getCheckingBalanceLegal($request) {
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_mes_ini = $request->id_mes;
        $id_fondo = $request->id_fondo;
        $addSql="";
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad=" IS NOT NULL";
        }else{
            $id_entidad=" = $id_entidad";
        }
        if($id_depto===null or $id_depto==='*' or $id_depto==='0'){
            $id_depto=" IS NOT NULL";
        }else{
            $id_depto=" = '$id_depto'";
        }
        if($id_anho===null or $id_anho==='*'){
            $id_anho=" IS NOT NULL";
        }else{
            $id_anho=" = $id_anho";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes=" <=12";
        }else{
            $id_mes=" <= $id_mes";
        }
        if($id_fondo===null or $id_fondo==='*'){
            $id_fondo=" IS NOT NULL";
        }else{
            $id_fondo=" = $id_fondo";
        }
        if($id_mes_ini===null or $id_mes_ini==='*'){
            $id_mes_ini=1;
        }

        $query1 = "SELECT X.*,
                (CASE
                WHEN
                (X.ID_CUENTAEMPRESARIAL>=73 AND X.ID_CUENTAEMPRESARIAL<=78)
                OR (X.ID_CUENTAEMPRESARIAL>=93 AND X.ID_CUENTAEMPRESARIAL<=99)
                OR X.ID_CUENTAEMPRESARIAL=66
                OR X.ID_CUENTAEMPRESARIAL=69
                OR X.ID_CUENTAEMPRESARIAL=70
                OR X.ID_CUENTAEMPRESARIAL=89
                THEN X.DEUDOR
                ELSE NULL
                END) AS PERDIDA,
                (CASE
                WHEN
                (X.ID_CUENTAEMPRESARIAL>=73 AND X.ID_CUENTAEMPRESARIAL<=78)
                OR (X.ID_CUENTAEMPRESARIAL>=93 AND X.ID_CUENTAEMPRESARIAL<=99)
                OR X.ID_CUENTAEMPRESARIAL=66
                OR X.ID_CUENTAEMPRESARIAL=69
                OR X.ID_CUENTAEMPRESARIAL=70
                OR X.ID_CUENTAEMPRESARIAL=89
                THEN X.ACREEDOR
                ELSE NULL
                END) AS GANANCIA

                FROM (SELECT Y.*,
                (CASE
                WHEN Y.ID_CUENTAEMPRESARIAL<60 THEN Y.DEUDOR
                ELSE NULL
                END) AS ACTIVO,
                (CASE
                WHEN Y.ID_CUENTAEMPRESARIAL<60 THEN Y.ACREEDOR
                ELSE NULL
                END) AS PASIVO
                FROM (SELECT Z.*,
                (CASE
                WHEN NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0)>NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0) THEN (NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0))-(NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0))
                ELSE NULL
                END) AS DEUDOR,
                (CASE
                WHEN NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0)>NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0) THEN (NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0))-(NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0))
                ELSE NULL
                END) AS ACREEDOR
                FROM (
                SELECT DISTINCT T1.ID_CUENTAEMPRESARIAL,T1.NOMBRE,SUM(T2.DEBE_INICIAL) AS DEUDORI,SUM(T2.HABER_INICIAL) AS ACREEDORI,SUM(T1.DEBE) AS DEBE,SUM(T1.HABER) AS HABER
                FROM (
                SELECT DISTINCT * FROM (
                SELECT A.ID_CUENTAEMPRESARIAL,A.NOMBRE,F.ID_CUENTAAASI,G.DEBE,G.HABER
                FROM CONTA_CTA_EMPRESARIAL A
                INNER JOIN CONTA_CTA_EMPRESARIAL B ON A.ID_CUENTAEMPRESARIAL=B.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL C ON B.ID_CUENTAEMPRESARIAL=C.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL D ON C.ID_CUENTAEMPRESARIAL=D.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL E ON D.ID_CUENTAEMPRESARIAL=E.ID_PARENT
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anho AND ID_EMPRESA $id_empresa) F ON E.ID_CUENTAEMPRESARIAL=F.ID_CUENTAEMPRESARIAL
                LEFT JOIN (
                SELECT A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE,SUM(B1.DEBE) AS DEBE,SUM(B1.HABER) AS HABER
                FROM CONTA_CTA_DENOMINACIONAL A1
                INNER JOIN VW_CONTA_DIARIO B1 ON A1.ID_CUENTAAASI=B1.ID_CUENTAAASI AND A1.ID_TIPOPLAN=B1.ID_TIPOPLAN AND A1.ID_RESTRICCION=B1.ID_RESTRICCION
                WHERE  B1.ID_EMPRESA  $id_empresa
                AND B1.ID_ENTIDAD $id_entidad
                AND B1.ID_DEPTO $id_depto
                AND B1.ID_ANHO $id_anho
                AND B1.ID_MES $id_mes
                AND B1.ID_FONDO $id_fondo
                GROUP BY A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE
                ORDER BY A1.ID_CUENTAAASI
                ) G ON F.ID_CUENTAAASI=G.ID_CUENTAAASI AND F.ID_TIPOPLAN=G.ID_TIPOPLAN AND F.ID_RESTRICCION=G.ID_RESTRICCION
                WHERE LENGTH(A.ID_CUENTAEMPRESARIAL)=2
                UNION
                SELECT A.ID_CUENTAEMPRESARIAL,A.NOMBRE,F.ID_CUENTAAASI,G.DEBE,G.HABER
                FROM CONTA_CTA_EMPRESARIAL A
                INNER JOIN CONTA_CTA_EMPRESARIAL B ON A.ID_CUENTAEMPRESARIAL=B.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL C ON B.ID_CUENTAEMPRESARIAL=C.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL D ON C.ID_CUENTAEMPRESARIAL=D.ID_PARENT
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anho AND ID_EMPRESA $id_empresa) F ON D.ID_CUENTAEMPRESARIAL=F.ID_CUENTAEMPRESARIAL
                LEFT JOIN (
                SELECT A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE,SUM(B1.DEBE) AS DEBE,SUM(B1.HABER) AS HABER
                FROM CONTA_CTA_DENOMINACIONAL A1
                INNER JOIN VW_CONTA_DIARIO B1 ON A1.ID_CUENTAAASI=B1.ID_CUENTAAASI AND A1.ID_TIPOPLAN=B1.ID_TIPOPLAN AND A1.ID_RESTRICCION=B1.ID_RESTRICCION
                WHERE  B1.ID_EMPRESA  $id_empresa
                AND B1.ID_ENTIDAD $id_entidad
                AND B1.ID_DEPTO $id_depto
                AND B1.ID_ANHO $id_anho
                AND B1.ID_MES $id_mes
                AND B1.ID_FONDO $id_fondo
                GROUP BY A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE
                ORDER BY A1.ID_CUENTAAASI
                ) G ON F.ID_CUENTAAASI=G.ID_CUENTAAASI AND F.ID_TIPOPLAN=G.ID_TIPOPLAN AND F.ID_RESTRICCION=G.ID_RESTRICCION
                WHERE LENGTH(A.ID_CUENTAEMPRESARIAL)=2
                UNION
                SELECT A.ID_CUENTAEMPRESARIAL,A.NOMBRE,F.ID_CUENTAAASI,G.DEBE,G.HABER
                FROM CONTA_CTA_EMPRESARIAL A
                INNER JOIN CONTA_CTA_EMPRESARIAL B ON A.ID_CUENTAEMPRESARIAL=B.ID_PARENT
                INNER JOIN CONTA_CTA_EMPRESARIAL C ON B.ID_CUENTAEMPRESARIAL=C.ID_PARENT
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anho AND ID_EMPRESA $id_empresa) F ON C.ID_CUENTAEMPRESARIAL=F.ID_CUENTAEMPRESARIAL
                LEFT JOIN (
                SELECT A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE,SUM(B1.DEBE) AS DEBE,SUM(B1.HABER) AS HABER
                FROM CONTA_CTA_DENOMINACIONAL A1
                INNER JOIN VW_CONTA_DIARIO B1 ON A1.ID_CUENTAAASI=B1.ID_CUENTAAASI AND A1.ID_TIPOPLAN=B1.ID_TIPOPLAN AND A1.ID_RESTRICCION=B1.ID_RESTRICCION
                WHERE  B1.ID_EMPRESA  $id_empresa
                AND B1.ID_ENTIDAD $id_entidad
                AND B1.ID_DEPTO $id_depto
                AND B1.ID_ANHO $id_anho
                AND B1.ID_MES $id_mes
                AND B1.ID_FONDO $id_fondo
                GROUP BY A1.ID_CUENTAAASI,A1.ID_TIPOPLAN,A1.ID_RESTRICCION,A1.ID_PARENT,A1.NOMBRE
                ORDER BY A1.ID_CUENTAAASI
                ) G ON F.ID_CUENTAAASI=G.ID_CUENTAAASI AND F.ID_TIPOPLAN=G.ID_TIPOPLAN AND F.ID_RESTRICCION=G.ID_RESTRICCION
                WHERE LENGTH(A.ID_CUENTAEMPRESARIAL)=2
                ))T1
                INNER JOIN (
                SELECT ID_CUENTAAASI,
                SUM(DEBE_INICIAL) AS DEBE_INICIAL,
                SUM(HABER_INICIAL) AS HABER_INICIAL
                FROM (
                    SELECT
                    ID_CUENTAAASI,
                    SUM(DEBE) AS DEBE_INICIAL,
                    SUM(HABER) AS HABER_INICIAL
                    FROM VW_CONTA_DIARIO
                    WHERE  ID_EMPRESA  $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_TIPOASIENTO = 'BB'
                    GROUP BY ID_CUENTAAASI
                UNION ALL
                    SELECT
                    ID_CUENTAAASI,
                    SUM(DEBE) AS DEBE_INICIAL,
                    SUM(HABER) AS HABER_INICIAL
                    FROM VW_CONTA_DIARIO
                    WHERE  ID_EMPRESA  $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_MES < $id_mes_ini
                    AND ID_FONDO $id_fondo
                    AND NOT ID_TIPOASIENTO IN( 'EA','BB')
                    GROUP BY ID_CUENTAAASI
                ) A
                GROUP BY ID_CUENTAAASI
                )T2 ON T1.ID_CUENTAAASI=T2.ID_CUENTAAASI
                GROUP BY T1.ID_CUENTAEMPRESARIAL,T1.NOMBRE
                ) Z
                )Y
                )X
                ORDER BY 1,2";
        //echo $query1;
        $query="SELECT X.*, (
            CASE WHEN (X.ID_CUENTAEMPRESARIAL>59 )
            THEN
                CASE WHEN NVL(X.DEUDOR,0)>NVL(X.ACREEDOR,0)
                    THEN NVL(X.DEUDOR,0)-NVL(X.ACREEDOR,0)
                ELSE NULL END
            ELSE NULL END) AS PERDIDA,

            (CASE WHEN (X.ID_CUENTAEMPRESARIAL>59)
            THEN
                CASE WHEN NVL(X.ACREEDOR,0)>NVL(X.DEUDOR,0)
                    THEN NVL(X.ACREEDOR,0)-NVL(X.DEUDOR,0)
                ELSE NULL END
            ELSE NULL END) AS GANANCIA

            FROM (SELECT Y.*, (
                    CASE WHEN Y.ID_CUENTAEMPRESARIAL<=59
                    THEN
                        CASE WHEN NVL(Y.DEUDOR,0)>NVL(Y.ACREEDOR,0)
                        THEN NVL(Y.DEUDOR,0)-NVL(Y.ACREEDOR,0)
                        ELSE NULL END
                     ELSE NULL END) AS ACTIVO,
                    (
                    CASE WHEN Y.ID_CUENTAEMPRESARIAL<=59
                    THEN
                        CASE WHEN NVL(Y.ACREEDOR,0)>NVL(Y.DEUDOR,0)
                        THEN NVL(Y.ACREEDOR,0)-NVL(Y.DEUDOR,0)
                        ELSE NULL END
                    ELSE NULL END
                    ) AS PASIVO
            FROM (SELECT Z.*,
            (CASE
            WHEN NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0)>NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0) THEN (NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0))-(NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0))
            ELSE NULL
            END) AS DEUDOR,
            (CASE
            WHEN NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0)>NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0) THEN (NVL(Z.HABER,0)+NVL(Z.ACREEDORI,0))-(NVL(Z.DEBE,0)+NVL(Z.DEUDORI,0))
            ELSE NULL
            END) AS ACREEDOR
            FROM (

            SELECT A1.ID_CUENTAEMPRESARIAL,A1.NOMBRE,
                        SUM(B1.DEBE_INI) AS DEUDORI,
                        SUM(B1.HABER_INI) AS ACREEDORI,
                        SUM(B1.DEBE) AS DEBE,
                        SUM(B1.HABER) AS HABER
                FROM CONTA_CTA_EMPRESARIAL A1
                INNER JOIN (SELECT ID_CUENTAEMPRESARIAL,ID_CUENTAAASI,
                                SUM(DEBE_INI) AS DEBE_INI,
                                SUM(HABER_INI) AS HABER_INI,
                                SUM(DEBE) AS DEBE,
                                SUM(HABER) AS HABER
                            FROM (SELECT B.ID_CUENTAEMPRESARIAL,A.ID_CUENTAAASI,
                                                0 AS DEBE_INI,
                                                0 AS HABER_INI,
                                                A.DEBE AS DEBE,
                                                A.HABER AS HABER
                                                FROM (
                                                    SELECT ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION,SUM(DEBE) AS DEBE,SUM(HABER) AS HABER
                                                    FROM VW_CONTA_DIARIO
                                                    WHERE ID_EMPRESA $id_empresa
                                                    AND ID_ENTIDAD $id_entidad
                                                    AND ID_DEPTO $id_depto
                                                    AND ID_ANHO $id_anho
                                                    AND ID_MES $id_mes
                                                    AND ID_FONDO $id_fondo
                                                    AND NOT ID_TIPOASIENTO IN('EA','BB')
                                                    --AND NOT ID_CUENTAAASI LIKE '3211006%'
                                                    --AND ID_DIARIO =13348
                                                    GROUP BY ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION
                                                ) A
                                INNER JOIN (SELECT * FROM CONTA_EMPRESA_CTA
                                WHERE ID_ANHO $id_anho AND ID_EMPRESA $id_empresa) B
                                ON A.ID_CUENTAAASI=B.ID_CUENTAAASI
                                AND A.ID_TIPOPLAN=B.ID_TIPOPLAN
                                AND A.ID_RESTRICCION=B.ID_RESTRICCION
                                WHERE B.ID_EMPRESA $id_empresa
                                AND B.ID_ANHO $id_anho
                                UNION ALL
                                SELECT B3.ID_CUENTAEMPRESARIAL,A3.ID_CUENTAAASI,
                                                A3.DEBE AS DEBE_INI,
                                                A3.HABER AS HABER_INI,
                                                0 AS DEBE,
                                                0 AS HABER
                                FROM (SELECT *
                                        FROM CONTA_EMPRESA_CTA
                                        WHERE ID_ANHO $id_anho
                                        AND ID_EMPRESA $id_empresa
                                        ) B3
                                INNER JOIN VW_CONTA_DIARIO A3
                                    ON B3.ID_CUENTAAASI = A3.ID_CUENTAAASI
                                    AND B3.ID_TIPOPLAN=A3.ID_TIPOPLAN
                                    AND B3.ID_RESTRICCION=A3.ID_RESTRICCION
                                WHERE A3.ID_EMPRESA $id_empresa
                                AND A3.ID_ENTIDAD $id_entidad
                                AND A3.ID_DEPTO $id_depto
                                AND A3.ID_ANHO $id_anho
                                AND A3.ID_FONDO $id_fondo
                                --AND B.ID_EMPRESA $id_empresa
                                --AND B.ID_ANHO $id_anho
                                AND A3.ID_TIPOASIENTO IN('BB'))
                                GROUP BY ID_CUENTAEMPRESARIAL,ID_CUENTAAASI) B1 ON SUBSTR(A1.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(B1.ID_CUENTAEMPRESARIAL,0,2)
                                --GROUP BY ID_CUENTAEMPRESARIAL,ID_CUENTAAASI) B1 ON A1.ID_CUENTAEMPRESARIAL=B1.ID_CUENTAEMPRESARIAL
                WHERE LENGTH(A1.ID_CUENTAEMPRESARIAL)=2
                GROUP BY A1.ID_CUENTAEMPRESARIAL,A1.NOMBRE
                ORDER BY A1.ID_CUENTAEMPRESARIAL
            ) Z
            )Y
            )X
            ORDER BY 1,2";
            // var_dump("asdasd");
            // var_dump($query);
         // echo($query);
            $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getEntityById($id_entidad) {
        $query = DB::table('VW_CONTA_ENTIDAD A')
        ->join('CONTA_EMPRESA C', 'A.ID_EMPRESA', '=', 'C.ID_EMPRESA')
        ->join('MOISES.PERSONA B', 'A.ID_PERSONA', '=', 'B.ID_PERSONA')
        ->where("A.ID_ENTIDAD", "=",$id_entidad)
        ->select(
            'A.ID_PERSONA',
            'A.NOMBRE AS ENTIDAD',
            'A.ID_EMPRESA',
            'A.ID_ENTIDAD',
            'B.NOMBRE',
            'B.PATERNO',
            'B.MATERNO',
            'C.LOGO',
            'C.ID_RUC AS RUC',
            'A.NOM_EMPRESA AS NOMBRE_LEGAL'
        )
        ->first();
        return $query;
    }
    public static function getCompanyById($id_empresa) {
        $query = DB::table('VW_CONTA_EMPRESA')
        ->where("ID_EMPRESA", "=",$id_empresa)
        ->select('ID_CORPORACION AS CORPORACION',
            'ID_TIPOEMPRESA AS TIPO_EMPRESA',
            'ID_EMPRESA',
            'ID_PERSONA',
            'LOGO',
            DB::raw("'" . url('') . "/' || LOGO AS url"),
            'ID_RUC AS RUC',
            'NOM_DENOMINACIONAL AS NOMBRE',
            'NOM_LEGAL AS NOMBRE_LEGAL')
        ->first();
        return $query;
    }
    public static function getAccounts($request) {
        $search = $request->search;
        $query = DB::table('CONTA_CTA_DENOMINACIONAL')
        ->where("ES_GRUPO", "=",0)
        ->where("ES_ACTIVA", "=",1)
        ->select('ID_CUENTAAASI','NOMBRE')
        ->get();
        return $query;
    }
    public static function getCtactes($request) {
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_entidad = $request->id_entidad;
        $search = $request->search;
        $query = DB::table('VW_CONTA_CTACTE A')
        ->join('CONTA_CTA_DENOMINACIONAL B', 'A.ID_TIPOCTACTE', '=', 'B.ID_TIPOCTACTE');
        if($search!==null){
            $query=$query->whereRaw("(A.ID_CTACTE LIKE '%".$search."%' OR UPPER(A.NOMBRE) LIKE UPPER('%".$search."%'))");
        }
        $query=$query->where("B.ID_CUENTAAASI", "=",$id_cuentaaasi)
        ->where("A.ID_ENTIDAD", "=",$id_entidad)->select('A.*')
        ->distinct()
        ->paginate(50);
        return $query;
    }
    public static function getDeparmentBalance($request) {
        $id_entidad = $request->id_entidad;

        $query = DB::table('CONTA_ENTIDAD_DEPTO')
        ->select(
            'CONTA_ENTIDAD_DEPTO.*',
            DB::raw("ID_DEPTO||' - '|| NOMBRE AS NAME")
        )
        ->where("ID_ENTIDAD", "=", $id_entidad)
        ->whereRaw("ID_DEPTO IN ('114151', '114181', '114071', '114101', '114811', '114221', '114201', '114031',
            '399111', '118231', '399311', '118232', '399511', '118233',
            '111131', '111121', '1111311', '1111211', '397132', '396115', '396114', '393212' , '396121', '396129', '396112', '396113', '396131', '397161', '396111', '114224',
            '113111', '114813', '113611', '113812', '113132', '114092', '114812', '114182' , '114202', '114102', '114062', '115112', '399312', '114152', '115891',
            '116531', '116522', '325822', '116532', '325811', '325812', '325821', '114023' , '114153', '397163', '115811', '115613',
            '393221', '393222', '393223', '909211', '114021', '114022', '188120', '396125' , '3961271', '3961272', '396134', '114231', '992991', '396119', '396128',
            '118221', '113171', '117211', '158111', '117212', '117213',
            '129811', '118293', '325311', '325111')")
        // ->where("ES_ACTIVO", "=",1)
        // ->where("ES_GRUPO", "=",0)
        ->get();
        
        return $query;
    }
    public static function getDeparmentByEntity($id_entidad, $id_depto) {
        $query = DB::table('CONTA_ENTIDAD_DEPTO')
        ->select(
            'CONTA_ENTIDAD_DEPTO.*',
            DB::raw("ID_DEPTO||' - '|| NOMBRE AS NAME")
        )
        ->where("ID_ENTIDAD", "=",$id_entidad)
        ->where("ID_DEPTO", "=",$id_depto)
        ->where("ES_ACTIVO", "=",1)
        // ->where("ES_GRUPO", "=",0)
        ->first();
        return $query;
    }
    public static function getDeparment($request) {
        $id_entidad = $request->id_entidad;
        $es_empresa = $request->es_empresa!=null?$request->es_empresa:1;
        $search = $request->search;
        $query = DB::table('CONTA_ENTIDAD_DEPTO')
        ->where("ID_ENTIDAD", "=",$id_entidad)
        ->where("ES_ACTIVO", "=",1)
        ->where("ES_GRUPO", "=",0)
        ->where("ES_EMPRESA", "=",$es_empresa);
        if ($search !==null and  $search !== '') {
            $query =$query->whereRaw("UPPER(NOMBRE) LIKE UPPER('%".$search."%')");
        }

        $query=$query->select('CONTA_ENTIDAD_DEPTO.*', DB::raw("ID_DEPTO||'-'|| NOMBRE AS NAME"))
        ->get();
        return $query;
    }
    public static function getAccountingEntries($request) {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $query = DB::table('TIPO_ASIENTO A')
        ->join('CONTA_DIARIO B', 'A.ID_TIPOASIENTO', '=', 'B.ID_TIPOASIENTO')
        ->join('CONTA_DIARIO_DETALLE C', 'B.ID_DIARIO', '=', 'C.ID_DIARIO')
        ->leftjoin('CONTA_DIARIO_FILE D', 'B.ID_DIARIO', '=', DB::raw("D.ID_DIARIO AND B.ID_ENTIDAD=D.ID_ENTIDAD AND B.ID_ANHO=D.ID_ANHO AND B.ID_MES=D.ID_MES
        AND D.LOTE=CAST(B.ID_ENTIDAD AS varchar(10)) || '-' || B.ID_TIPOASIENTO || ' ' || CAST(B.COD_AASI AS varchar(10))"))
        ->where("B.ID_ENTIDAD", "=",$id_entidad);
        if($id_depto!==null and $id_depto!=='*'and $id_depto!=='0'){
            $query=$query->where("C.ID_DEPTO", "=",$id_depto);
        }else{
            // $query=$query->whereRaw("C.ID_DEPTO IN ($deptos)");
            $query=$query->whereRaw("C.ID_DEPTO IS NOT NULL");
        }
        if($id_anho!==null and $id_anho!=='*'){
            $query=$query->where("B.ID_ANHO", "=",$id_anho);
        }
        if($id_mes!==null and $id_mes!=='*'){
            $query=$query->where("B.ID_MES", "=",$id_mes);
        }
        $query=$query->select(
            'A.ID_TIPOASIENTO',
            'A.NOMBRE AS TIPOASIENTO',
            'B.ID_DIARIO',
            DB::raw("CAST(B.ID_ENTIDAD AS varchar(10)) || '-' || B.ID_TIPOASIENTO || ' ' || CAST(B.COD_AASI AS varchar(10))  AS LOTE"),
            'B.FEC_ASIENTO',
            'B.NOM_CONTADOR',
            'B.FEC_CONTABILIZADO',
            'B.COMENTARIO AS GLOSA',
            'B.ID_ENTIDAD',
            'B.ID_ANHO',
            'B.ID_MES',
            'D.URL AS FILE_URL',
            'D.NOMBRE AS FILE'
        )
        ->distinct()
        ->orderBy('A.ID_TIPOASIENTO')
        ->orderBy('B.FEC_ASIENTO')
        ->orderBy('B.NOM_CONTADOR')
        ->orderBy('B.FEC_CONTABILIZADO')
        ->get();
        return $query;
    }

    public static function getSeniorAccountant($request,$all=false,$empr,$entities) {
        $dataNew=null;
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_mes_ini = $request->id_mes;
        $id_mes_old = $request->id_mes;
        $id_fondo = $request->id_fondo;
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_ctacte = $request->id_ctacte;
        $page = $request->page;
        $pageSize = $request->pageSize;
        $countData = $request->countData;
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IN ($empr) ";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad=" IN ($entities) ";
        }else{
            $id_entidad=" = $id_entidad";
        }
        if($id_depto===null or $id_depto==='*' or $id_depto==='0'){
            $id_depto=" IS NOT NULL ";
            //$id_depto=" IN ($deptos) ";
        }else{
            $id_depto=" = '$id_depto'";
        }
        if($id_anho===null or $id_anho==='*'){
            $id_anho=" IS NOT NULL";
        }else{
            $id_anho=" = $id_anho";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes=" IS NOT NULL";
        }else{
            $id_mes=" = $id_mes";
        }
        if($id_fondo===null or $id_fondo==='*'){
            $id_fondo=" IS NOT NULL";
        }else{
            $id_fondo=" = $id_fondo";
        }
        if($id_cuentaaasi===null or $id_cuentaaasi==='*'){
            $id_cuentaaasi=" IS NOT NULL";
        }else{
            $id_cuentaaasi=" = $id_cuentaaasi";
        }
        if($id_ctacte===null or $id_ctacte==='*'){
            $id_ctacte=" IS NOT NULL";
        }else{
            $id_ctacte=" = $id_ctacte";
        }
        if($id_mes_old===null or $id_mes_old==='*'){
            $id_mes_old=1;
        }
        $query_total="SELECT count(*) AS TOTAL
        FROM VW_CONTA_DIARIO A
        INNER JOIN MOISES.PERSONA_DOCUMENTO B ON B.NUM_DOCUMENTO=A.ID_CTACTE
        INNER JOIN MOISES.PERSONA C ON C.ID_PERSONA=B.ID_PERSONA
        INNER JOIN CONTA_CTA_DENOMINACIONAL E ON A.ID_CUENTAAASI=E.ID_CUENTAAASI
        WHERE A.ID_EMPRESA $id_empresa
        AND A.ID_ENTIDAD $id_entidad
        AND A.ID_DEPTO $id_depto
        AND A.ID_ANHO $id_anho
        AND A.ID_MES $id_mes
        AND A.ID_FONDO $id_fondo
        AND A.ID_CUENTAAASI $id_cuentaaasi
        AND A.ID_CTACTE $id_ctacte
        AND NOT A.ID_TIPOASIENTO IN('EA','BB')";

        $query= "SELECT * FROM (SELECT row_number() over (ORDER BY A.ID_MES,A.ID_CUENTAAASI,C.PATERNO,C.MATERNO,C.NOMBRE,A.FEC_ASIENTO,A.LOTE ASC) AS line_number,C.ID_PERSONA,C.PATERNO||' '||C.MATERNO||', '||C.NOMBRE AS NOMBRES,A.ID_CUENTAAASI,
            E.NOMBRE AS NOMBRE_CUENTA,
            A.FEC_ASIENTO,A.ID_MES,TO_CHAR(A.FEC_ASIENTO, 'Month','nls_date_language=spanish') AS NOM_MES,A.LOTE,A.ID_DEPTO,A.ID_FONDO,A.COMENTARIO,A.DEBE,A.HABER,A.COS_VALOR AS SALDO,A.ID_CTACTE
            FROM VW_CONTA_DIARIO A
            INNER JOIN MOISES.PERSONA_DOCUMENTO B ON B.NUM_DOCUMENTO=A.ID_CTACTE
            INNER JOIN MOISES.PERSONA C ON C.ID_PERSONA=B.ID_PERSONA
            INNER JOIN CONTA_CTA_DENOMINACIONAL E ON A.ID_CUENTAAASI=E.ID_CUENTAAASI
            WHERE A.ID_EMPRESA $id_empresa
            AND A.ID_ENTIDAD $id_entidad
            AND A.ID_DEPTO $id_depto
            AND A.ID_ANHO $id_anho
            AND A.ID_MES $id_mes
            AND A.ID_FONDO $id_fondo
            AND A.ID_CUENTAAASI $id_cuentaaasi
            AND A.ID_CTACTE $id_ctacte
            AND NOT A.ID_TIPOASIENTO IN('EA','BB')) ";
            if($all===false){
                $query=$query."WHERE line_number BETWEEN ".(($page*$pageSize)-$pageSize+1)." AND ".($page*$pageSize);
            }
            $oQuery = DB::select($query);
            $id_ctacte_ant=null;
            $lote_ant=null;
            $saldo_ant=0;
            $count=0;
            $j = $page*$pageSize-$pageSize;
            if($oQuery and count($oQuery)>0){
                $id_ctacte_ant=$oQuery[0]->id_ctacte;
                $lote_ant=$oQuery[0]->lote;
            }
            if($id_ctacte_ant and $all===false){
                $query_ant= "SELECT * FROM (SELECT row_number() over (ORDER BY A.ID_MES,A.ID_CUENTAAASI,C.PATERNO,C.MATERNO,C.NOMBRE,A.FEC_ASIENTO,A.LOTE ASC) AS line_number,C.ID_PERSONA,C.PATERNO||' '||C.MATERNO||', '||C.NOMBRE AS NOMBRES,A.ID_CUENTAAASI,
                E.NOMBRE AS NOMBRE_CUENTA,
                A.FEC_ASIENTO,A.ID_MES,TO_CHAR(A.FEC_ASIENTO, 'Month','nls_date_language=spanish') AS NOM_MES,A.LOTE,A.ID_DEPTO,A.ID_FONDO,A.COMENTARIO,A.DEBE,A.HABER,A.COS_VALOR AS SALDO
                FROM VW_CONTA_DIARIO A
                INNER JOIN MOISES.PERSONA_DOCUMENTO B ON B.NUM_DOCUMENTO=A.ID_CTACTE
                INNER JOIN MOISES.PERSONA C ON C.ID_PERSONA=B.ID_PERSONA
                INNER JOIN CONTA_CTA_DENOMINACIONAL E ON A.ID_CUENTAAASI=E.ID_CUENTAAASI
                WHERE A.ID_EMPRESA $id_empresa
                AND A.ID_ENTIDAD $id_entidad
                AND A.ID_DEPTO $id_depto
                AND A.ID_ANHO $id_anho
                AND A.ID_MES $id_mes
                AND A.ID_FONDO $id_fondo
                AND A.ID_CUENTAAASI $id_cuentaaasi
                AND A.ID_CTACTE = $id_ctacte_ant
                AND NOT A.ID_TIPOASIENTO IN('EA','BB'))";
            $result_ant = DB::select($query_ant);
            foreach ($result_ant as $key => $value){
                    if($value->lote===$lote_ant){
                        $count=1;
                    }
                    if($count===0){
                        $saldo_ant=number_format((float)($saldo_ant)+(float)($value->saldo),2, '.', '');
                    }
            }
        }
        if($saldo_ant===0 or $saldo_ant===0.00){
            $j=0;
        }
        $dataOld=(object) array('id_mes' => null,'id_cuentaaasi' => null,'id_persona' => null,'saldo_calculado'=>$saldo_ant,'id_ctacte'=>null);

        $result=[];
        $data=[];
        $data1=[];
        $data2=[];
        $children1=[];
        $children2=[];
        $children3=[];
        $iniciar_suma=false;
        $i = 0;
        $saldo_ini=0;
        foreach ($oQuery as $key => $value){
            $dataNew=$value;
            if($i>0){
                $children3[]=(object)array('data'=>$dataOld);
            }
            if(($dataNew->id_mes!==$dataOld->id_mes or $dataNew->id_cuentaaasi!==$dataOld->id_cuentaaasi or $dataNew->id_persona!==$dataOld->id_persona) and $i>0){
                $iniciar_suma=true;
                $query_saldo_anterior="SELECT SUM(SALDO_INI) AS SALDO_INI
                FROM (
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_TIPOASIENTO = 'BB'
                    AND ID_CTACTE= $dataOld->id_ctacte
                UNION ALL
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_MES < $id_mes_old
                    AND ID_CTACTE= $dataOld->id_ctacte AND NOT ID_TIPOASIENTO IN( 'EA','BB'))";
                $result_saldo_anterior = DB::select($query_saldo_anterior);
                    if($result_saldo_anterior && count($result_saldo_anterior)>0){
                        $saldo_ini=$result_saldo_anterior[0]->saldo_ini;
                    }
                $data2=(object)array('nombre'=>$dataOld->nombres,'saldo_ini'=>($saldo_ini==0 or $saldo_ini==null)?'-':$saldo_ini);
                $children2[]=(object)array('data'=>$data2,'children'=>$children3);
                $children3=[];
            }
            if($j>0){
                if($iniciar_suma){
                    $query_saldo_anterior="SELECT SUM(SALDO_INI) AS SALDO_INI
                    FROM (
                        SELECT SUM(COS_VALOR) AS SALDO_INI
                        FROM VW_CONTA_DIARIO
                        WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anho
                        AND ID_FONDO $id_fondo
                        AND ID_CUENTAAASI $id_cuentaaasi
                        AND ID_TIPOASIENTO = 'BB'
                        AND ID_CTACTE= $value->id_ctacte
                    UNION ALL
                        SELECT SUM(COS_VALOR) AS SALDO_INI
                        FROM VW_CONTA_DIARIO
                        WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anho
                        AND ID_FONDO $id_fondo
                        AND ID_CUENTAAASI $id_cuentaaasi
                        AND ID_MES < $id_mes_old
                        AND ID_CTACTE= $value->id_ctacte AND NOT ID_TIPOASIENTO IN( 'EA','BB'))";
                    $result_saldo_anterior = DB::select($query_saldo_anterior);
                    if($result_saldo_anterior && count($result_saldo_anterior)>0){
                        $saldo_ini=$result_saldo_anterior[0]->saldo_ini;
                    }
                    $value->saldo_calculado=$saldo_ini+number_format($value->saldo,2, '.', '');
                    $iniciar_suma=false;
                }else{
                    $value->saldo_calculado=number_format((float)($value->saldo)+(float)($dataOld->saldo_calculado),2, '.', '');
                }
            }else{
                $query_saldo_anterior="SELECT SUM(SALDO_INI) AS SALDO_INI
                FROM (
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_TIPOASIENTO = 'BB'
                    AND ID_CTACTE= $value->id_ctacte
                UNION ALL
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_MES < $id_mes_old
                    AND ID_CTACTE= $value->id_ctacte AND NOT ID_TIPOASIENTO IN( 'EA','BB'))";
                $result_saldo_anterior = DB::select($query_saldo_anterior);
                if($result_saldo_anterior && count($result_saldo_anterior)>0){
                    $saldo_ini=$result_saldo_anterior[0]->saldo_ini;
                }
                $value->saldo_calculado=$saldo_ini+number_format($value->saldo,2, '.', '');
            }
            if(($dataNew->id_mes!==$dataOld->id_mes or $dataNew->id_cuentaaasi!==$dataOld->id_cuentaaasi) and $i>0){
                $data1=(object)array('nombre'=>$dataOld->nombre_cuenta);
                $children1[]=(object)array('data'=>$data1,'children'=>$children2);
                $children2=[];
            }

            if($dataNew->id_mes!==$dataOld->id_mes and $i>0){
                $data=(object)array('nombre'=>$dataOld->nom_mes);
                $result[]=(object)array('data'=>$data,'children'=>$children1);
                $children1=[];
            }
            if($i===count($oQuery)-1){
                $query_saldo_anterior="SELECT SUM(SALDO_INI) AS SALDO_INI
                FROM (
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_TIPOASIENTO = 'BB'
                    AND ID_CTACTE= $value->id_ctacte
                UNION ALL
                    SELECT SUM(COS_VALOR) AS SALDO_INI
                    FROM VW_CONTA_DIARIO
                    WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidad
                    AND ID_DEPTO $id_depto
                    AND ID_ANHO $id_anho
                    AND ID_FONDO $id_fondo
                    AND ID_CUENTAAASI $id_cuentaaasi
                    AND ID_MES < $id_mes_old
                    AND ID_CTACTE= $value->id_ctacte AND NOT ID_TIPOASIENTO IN( 'EA','BB'))";
                $result_saldo_anterior = DB::select($query_saldo_anterior);
                if($result_saldo_anterior && count($result_saldo_anterior)>0){
                    $saldo_ini=$result_saldo_anterior[0]->saldo_ini;
                }
                $children3[]=(object)array('data'=>$value);
                $data2=(object)array('nombre'=>$value->nombres,'saldo_ini'=>($saldo_ini==0 or $saldo_ini==null)?'-':$saldo_ini);
                $children2[]=(object)array('data'=>$data2,'children'=>$children3);

                $data1=(object)array('nombre'=>$value->nombre_cuenta);
                $children1[]=(object)array('data'=>$data1,'children'=>$children2);

                $data=(object)array('nombre'=>$value->nom_mes);
                $result[]=(object)array('data'=>$data,'children'=>$children1);
            }
            $dataOld=$value;
            $i++;
            $j++;
        }
        $data_result['data']=$result;
        if($page>1 and $countData>0){
            $data_result['total']=$countData;
        }else if($all===false){
            $result_total = DB::select($query_total);
            $data_result['total']=$result_total?$result_total[0]->total:0;
        }
        return $data_result;
    }
    public static function getAccountStatusLote($request, $jResponse)
    {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        // covertir 
        $id_entidad_cte = $request->id_entidad_cte;
        $id_entidad_cte = $id_entidad_cte !== '*' ? ("'" . implode("','", explode(',', $id_entidad_cte)) . "'") : "*";

        $query = DB::table('VW_CONTA_DIARIO')
        ->select('LOTE AS NOMBRE')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_ANHO', $id_anho);

        if ($id_mes !== '*') {
            $query->where('ID_MES', $id_mes);
        }

        if ($id_entidad_cte !== '*') {
            $query->whereRaw("ID_CTACTE IN (".$id_entidad_cte.")");
        }

        $results = $query->groupBy('LOTE')
        ->get();

        return $results;
    }
    public static function getAccountStatus($request, $jResponse) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_entidad_cte = $request->id_entidad_cte; // 33,33,33,...
        // covertir 
        $id_entidad_cte = $id_entidad_cte !== '*' ? ("'" . implode("','", explode(',', $id_entidad_cte)) . "'") : "*";

        $lote = $request->lote;
        $tipo = $request->tipo;
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

        // print_r($jResponse);
        $queryCuentas = DB::table('CONTA_CUENTA_GRUPO')
        ->where("ID_CUENTAGRUPO", "=",$tipo)
        ->select(
            DB::raw("REPLACE(CUENTAS, '+', ',')  AS CUENTAS")
            )
        ->get();


        $query = DB::table('VW_CONTA_DIARIO VCD')
        ->leftjoin('CONTA_DIARIO_FILE D', 'VCD.ID_DIARIO', '=', DB::raw("D.ID_DIARIO AND VCD.ID_ENTIDAD=D.ID_ENTIDAD AND VCD.ID_ANHO=D.ID_ANHO AND VCD.ID_MES=D.ID_MES
        AND VCD.CODIGO=D.CODIGO"))
        ->leftjoin('CONTA_DIARIO_CTA_AVISO CDA', 'VCD.ID_DIARIO', '=', DB::raw("CDA.ID_DIARIO AND VCD.ID_ENTIDAD=CDA.ID_ENTIDAD AND VCD.ID_ANHO=CDA.ID_ANHO AND VCD.ID_MES=CDA.ID_MES
        AND VCD.CODIGO=CDA.CODIGO"))
        ->join('CONTA_ENTIDAD CE', 'VCD.ID_CTACTE', '=', 'CE.ID_ENTIDAD')
        ->where("VCD.ID_ENTIDAD", "=",$id_entidad)
        ->where("VCD.ID_ANHO", "=",$id_anho);

        // if($id_mes!==null and $id_mes!=='*'){
            // $query=$query->where("VCD.ID_MES", "=",$id_mes);
        //}

        if ($id_entidad_cte !== '*') {
            $query=$query->whereRaw("ID_CTACTE IN (".$id_entidad_cte.")")
            ->whereRaw("VCD.ID_CUENTAAASI IN (".$queryCuentas[0]->cuentas.")");
        } else {
           $query=$query->whereRaw("VCD.ID_CUENTAAASI IN (".$queryCuentas[0]->cuentas.")");
        }

        if ($lote !== '*') {
            $query = $query->where('VCD.LOTE', "=", $lote);
        }

        $query = $query->select(
            'VCD.ID_DIARIO',
            'VCD.ID_ENTIDAD',
            'VCD.ID_CTACTE',
            'CE.NOMBRE AS CTACTE',
            'VCD.ID_ANHO',
            DB::raw('LPAD(VCD.ID_MES,2,0) as ID_MES'),
            DB::raw("TO_CHAR(VCD.FEC_CONTABILIZADO,'DD/MM') AS FECHA"),
            'VCD.FEC_CONTABILIZADO',
            DB::raw("TO_CHAR(VCD.FEC_CONTABILIZADO,'Month', 'NLS_DATE_LANGUAGE=Spanish') AS MES"),
            'VCD.CODIGO',
            'D.FORMATO',
            'D.URL AS FILE_URL',
            'D.NOMBRE AS FILE',
            'CDA.CUENTA AS COD_CUENTA',
            'D.FECHA_DESCARGA',
            'D.ID_USER',
            'VCD.NOM_DIGITADOR',
            'VCD.NOM_CONTADOR',
            'VCD.ID_CUENTAAASI',
            'VCD.COMENTARIO',
            // DB::raw("(CASE WHEN ".$jResponse['id_entidad']." = ".$id_entidad."  THEN
            //     '1100'
            // WHEN ".$jResponse['id_entidad']." = ".$id_entidad_cte."  THEN
            //     '0011'
            // ELSE
            //     '0000'
            // END) as OPCION"),
            DB::raw("TO_CHAR(VCD.DEBE, 'fm999999999990.00') as DEBE"),
            DB::raw("TO_CHAR(VCD.HABER, 'fm999999999990.00') as HABER"),
            DB::raw(" TO_CHAR((VCD.DEBE - VCD.HABER), 'fm999999999990.00')  AS SALDO")
        );

        if ($id_entidad_cte !== '*') {
            $query->addSelect(DB::raw("(CASE 
                WHEN '{$jResponse['id_entidad']}' = '{$id_entidad}' THEN '1100'
                WHEN '{$jResponse['id_entidad']}' IN ({$id_entidad_cte}) THEN '0011'
                ELSE '0000'
            END) AS OPCION"));
        } else {
            $query->addSelect(DB::raw("(CASE 
                WHEN '{$jResponse['id_entidad']}' = '{$id_entidad}' THEN '1100'
                WHEN '{$jResponse['id_entidad']}' IN (
                    SELECT ID_ENTIDAD 
                    FROM CONTA_ENTIDAD 
                    WHERE ES_ACTIVO = 1 
                    AND ID_ENTIDAD <> '{$id_entidad}'
                ) THEN '0011' 
                ELSE '0000'
            END) AS OPCION"));
        }

        $query = $query->orderBy('VCD.ID_MES')
        ->orderBy('VCD.FEC_CONTABILIZADO')
        ->get();

        $i = 0;
        $j = 0;
        $id_mes_old=null;
        $id_mes_new=null;
        $array_resultante_init=[];
        $array_resultante=[];
        $total=0;
        $saldo_anterior=0;
        $total_mes_select=0;
        $state_finish=false;
        if($id_mes!==null and $id_mes!=='*' and intval($id_mes)>1){
            foreach($query as $item){
                if(intval($item->id_mes) === intval($id_mes) and $i === 0 and $state_finish===false) {
                    $c_date = $query[$i]->id_anho.'-'.$query[$i]->id_mes.'-01 00:00:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('first day of this month');
                    $array_resultante_init[]=(object) array(
                        'id_diario' => $query[$i]->id_diario,
                        'id_entidad' => $query[$i]->id_entidad,
                        'id_ctacte' => $query[$i]->id_ctacte,
                        'ctacte' => $query[$i]->ctacte,
                        'id_anho' => $query[$i]->id_anho,
                        'id_mes' => $query[$i]->id_mes,
                        'fecha' => $fecha->format('d/m'),
                        'fec_asiento' => $fecha->format('Y-m-d H:i:s'),
                        'mes' => $query[$i]->mes,
                        'codigo' => '',
                        'file' => '',
                        'file_url' => '',
                        'cod_cuenta' => '',
                        'fecha_descarga' => '',
                        'id_user' => '',
                        'nom_digitador' => '',
                        'nom_contador' => '',
                        'id_cuentaaasi' => '',
                        'comentario' => '::Saldo Inicial',
                        'opcion'=>'',
                        'debe' => '',
                        'haber' => '',
                        'saldo' => number_format(0,2,'.',''),
                            );
                        $array_resultante[] = $array_resultante_init[$i];
                        $saldo_anterior=$array_resultante_init[$i]->saldo;
                        $state_finish=true;
                } else if($state_finish===false){
                    $array_resultante_init[]=$item;
                    $m_monto = 0;
                    if($i!==0){
                        $m_monto = $array_resultante_init[$i-1]->saldo + $query[$i]->debe - $query[$i]->haber;
                        $array_resultante_init[$i]->saldo = number_format($m_monto,2,'.','');
                    }
                    if($i!==0 and intval($item->id_mes)===intval($id_mes) and $state_finish===false){
                        $c_date = $query[$i]->id_anho.'-'.$query[$i]->id_mes.'-01 00:00:00';
                        $fecha = new DateTime($c_date);
                        $fecha->modify('first day of this month');
                            $array_resultante[]= (object) array(
                                'id_diario' => $query[$i]->id_diario,
                                'id_entidad' => $query[$i]->id_entidad,
                                'id_ctacte' => $query[$i]->id_ctacte,
                                'ctacte' => $query[$i]->ctacte,
                                'id_anho' => $query[$i]->id_anho,
                                'id_mes' => $query[$i]->id_mes,
                                'fecha' => $fecha->format('d/m'),
                                'fec_asiento' => $fecha->format('Y-m-d H:i:s'),
                                'mes' => $query[$i]->mes,
                                'codigo' => '',
                                'file' => '',
                                'file_url' => '',
                                'cod_cuenta' => '',
                                'fecha_descarga' => '',
                                'id_user' => '',
                                'nom_digitador' => '',
                                'nom_contador' => '',
                                'id_cuentaaasi' => '',
                                'comentario' => '::Saldo Inicial',
                                'opcion'=>'',
                                'debe' => '',
                                'haber' => '',
                                'saldo' => number_format($array_resultante_init[$i-1]->saldo,2,'.',''),
                                    );
                        $saldo_anterior=$array_resultante_init[$i-1]->saldo;
                        $state_finish=true;
                    }
                }

                if(intval($item->id_mes)===intval($id_mes)){
                    $total_mes_select++;
                }
                $i++;
            }
        }
        $i = 0;
        $j = 0;
        $id_mes_old=null;
        $id_mes_new=null;
        $total=0;
        $cant=0;
        foreach($query as $item){
            if(($id_mes!==null and $id_mes!=='*' and intval($item->id_mes)===intval($id_mes)) or $id_mes===null or $id_mes==='*'){

                $id_mes_new=$item->id_mes;
                if($id_mes_new!==$id_mes_old and $i!=0){
                    $c_date = $query[$j-1]->id_anho.'-'.$query[$j-1]->id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                        $array_resultante[]= (object) array(
                            'id_diario' => $query[$j-1]->id_diario,
                            'id_entidad' => $query[$j-1]->id_entidad,
                            'id_ctacte' => $query[$j-1]->id_ctacte,
                            'ctacte' => $query[$j-1]->ctacte,
                            'id_anho' => $query[$j-1]->id_anho,
                            'id_mes' => $query[$j-1]->id_mes,
                            'fecha' => $fecha->format('d/m'),
                            'fec_asiento' => $fecha->format('Y-m-d H:i:s'),
                            'mes' => $mes,
                            'codigo' => '',
                            'file' => '',
                            'file_url' => '',
                            'cod_cuenta' => '',
                            'fecha_descarga' => '',
                            'id_user' => '',
                            'nom_digitador' => '',
                            'nom_contador' => '',
                            'id_cuentaaasi' => '',
                            'comentario' => '::Saldo Final',
                            'opcion'=>'',
                            'debe' => '',
                            'haber' => '',
                            'saldo' => number_format($total,2,'.',''),
                                );
                    $array_resultante[]=$item;
                    $m_monto = $saldo_anterior + $query[$j-1]->saldo + $query[$j]->debe - $query[$j]->haber;
                    $query[$j]->saldo = number_format($m_monto,2,'.','');
                    $array_resultante[$i + 1]->saldo = number_format($m_monto,2,'.','');
                    $total=0;
                    $total=$total+$item->saldo;
                    $i = $i + 1;
                }else{
                    $array_resultante[]=$item;
                    $m_monto = 0;
                    if($j!=0 and (intval($id_mes) === 1 or $id_mes === '*')){
                        $m_monto = $saldo_anterior + $query[$j-1]->saldo + $query[$j]->debe - $query[$j]->haber;
                        $query[$j]->saldo = number_format($m_monto,2,'.','');
                        $array_resultante[$i]->saldo = number_format($m_monto,2,'.','');
                        $saldo_anterior=0;
                    } else if ($j!=0 and $id_mes > 1) {

                        $m_monto = $query[$j - 1]->saldo + $query[$j]->debe - $query[$j]->haber;
                        $query[$j]->saldo = number_format($m_monto,2,'.','');
                        $array_resultante[$i + 1]->saldo = number_format($m_monto,2,'.','');
                        $saldo_anterior=0;
                    }
                    $total=$m_monto;
                }

                if($j===count($query)-1 or ($id_mes!==null and $id_mes!=='*' and intval($item->id_mes)===intval($id_mes) and $i===$total_mes_select-1)){
                    $c_date = $item->id_anho.'-'.$item->id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                        $array_resultante[]= (object) array(
                            'id_diario' => $item->id_diario,
                            'id_entidad' => $item->id_entidad,
                            'id_ctacte' => $item->id_ctacte,
                            'ctacte' => $item->ctacte,
                            'id_anho' => $item->id_anho,
                            'id_mes' => $item->id_mes,
                            'fecha' => $fecha->format('d/m'),
                            'fec_asiento' => $fecha->format('Y-m-d H:i:s'),
                            'mes' => $mes,
                            'codigo' => '',
                            'file' => '',
                            'file_url' => '',
                            'cod_cuenta' => '',
                            'fecha_descarga' => '',
                            'id_user' => '',
                            'nom_digitador' => '',
                            'nom_contador' => '',
                            'id_cuentaaasi' => '',
                            'comentario' => '::Saldo Final',
                            'opcion'=>'',
                            'debe' => '',
                            'haber' => '',
                            'saldo' => number_format($total,2,'.',''),
                                );
                }
                $i++;
                $id_mes_old=$item->id_mes;
            } else if((intval($item->id_mes) - 1) === intval($id_mes) and $cant === 0 and intval($id_mes) === 1) {
                $c_date = $item->id_anho.'-'.str_pad($id_mes,2,"0",STR_PAD_LEFT).'-01 23:59:00';
                $fecha = new DateTime($c_date);
                $fecha->modify('last day of this month');
                $mes = $meses[($fecha->format('n')) - 1];
                    $array_resultante[]= (object) array(
                        'id_diario' => $item->id_diario,
                        'id_entidad' => $item->id_entidad,
                        'id_ctacte' => $item->id_ctacte,
                        'ctacte' => $item->ctacte,
                        'id_anho' => $item->id_anho,
                        'id_mes' => str_pad($id_mes,2,"0",STR_PAD_LEFT),
                        'fecha' => $fecha->format('d/m'),
                        'fec_asiento' => $fecha->format('Y-m-d H:i:s'),
                        'mes' => $mes,
                        'codigo' => '',
                        'file' => '',
                        'file_url' => '',
                        'cod_cuenta' => '',
                        'fecha_descarga' => '',
                        'id_user' => '',
                        'nom_digitador' => '',
                        'nom_contador' => '',
                        'id_cuentaaasi' => '',
                        'comentario' => '::Saldo Final',
                        'opcion'=>'',
                        'debe' => '',
                        'haber' => '',
                        'saldo' => number_format($total,2,'.',''),
                            );
                            $cant++;
            }
            $j++;
        }
        return $array_resultante;
    }

    public static function uploadAccountStatus($request,$file,$id_user) {
        $res="OK";
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        // $lote = $request->lote;
        $codigo = $request->codigo;
        DB::table('CONTA_DIARIO_FILE')
            ->updateOrInsert(
                array('ID_DIARIO' => $id_diario,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO'=>$id_anho,
                    'ID_MES'=>$id_mes,
                    'CODIGO'=>$codigo,
            ),
                array('URL' => $file['url'],
                    'NOMBRE' => $file['filename'],
                    'TAMANHO'=>$file['size'],
                    'FORMATO'=>$file['format'],
                    'ID_USER' => $id_user,
                )
            );
        return $res;
    }
    public static function deleteFileAccountStatus($request) {
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $lote = $request->lote;
        $codigo = $request->codigo;
        $ret='OK';
        DB::table('CONTA_DIARIO_FILE')
                ->where('ID_DIARIO', $id_diario)
                ->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_ANHO', $id_anho)
                ->where('ID_MES', $id_mes)
                ->where('CODIGO', $codigo)
                ->delete();
        return $ret;
    }

    public static function updateUserDownloadFile($request, $id_user) {
        $res="OK";
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $codigo = $request->codigo;
        $fecha = new DateTime();
        DB::table('CONTA_DIARIO_FILE')->where(array(
            'ID_DIARIO' => $id_diario,
            'ID_ENTIDAD' => $id_entidad,
            'ID_ANHO'=>$id_anho,
            'ID_MES'=>$id_mes,
            'CODIGO'=>$codigo,
        ))->update(array('fecha_descarga' => $fecha,)
        );

        return $res;
    }

    public static function getAccountStatusSummary($request, $jResponse) {
        $data=[];
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_entidad_cte = $request->id_entidad_cte;
        $tipo = $request->tipo;
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $series = [];
        $series_data = [];
        $queryCuentas = DB::table('_GRUPO')
        ->where("ID_CUENTAGRUPO", "=",$tipo)
        ->select(
            DB::raw("REPLACE(CUENTAS, '+', ',')  AS CUENTAS")
            )
        ->get();

        $query = DB::table('VW_CONTA_DIARIO VCD')
        ->join('CONTA_ENTIDAD CE', 'VCD.ID_CTACTE', '=', 'CE.ID_ENTIDAD')
        ->where("VCD.ID_ENTIDAD", "=",$id_entidad)
        ->where("VCD.ID_ANHO", "=",$id_anho);

        if($id_mes!==null and $id_mes!=='*'){
            $query=$query->where("VCD.ID_MES", "<=",$id_mes);
        }

        $query=$query->whereRaw("VCD.ID_CTACTE IN (17211,17311,17611,17811,17911)")
        ->whereRaw("VCD.ID_CUENTAAASI IN (".$queryCuentas[0]->cuentas.")");

        $query=$query->select(
            'VCD.ID_ENTIDAD',
            'VCD.ID_CTACTE',
            'CE.NOMBRE',
            'VCD.ID_ANHO',
            DB::raw(" TO_CHAR((SUM(VCD.DEBE) - sum(VCD.HABER)), 'fm999999999990.00')  AS SALDO")
        )
        ->groupBy(DB::raw("VCD.ID_ENTIDAD,VCD.ID_CTACTE,CE.NOMBRE, VCD.ID_ANHO"))
        ->get();


        $querySum = DB::table('VW_CONTA_DIARIO VCD')
        ->where("VCD.ID_ENTIDAD", "=",$id_entidad)
        ->where("VCD.ID_ANHO", "=",$id_anho);

        if($id_mes!==null and $id_mes!=='*'){
            $querySum=$querySum->where("VCD.ID_MES", "<=",$id_mes);
        }

        $querySum=$querySum->whereRaw("VCD.ID_CTACTE IN (17211,17311,17611,17811,17911)")
        ->whereRaw("VCD.ID_CUENTAAASI IN (".$queryCuentas[0]->cuentas.")");

        $querySum=$querySum->select(
            'VCD.ID_ENTIDAD',
            'VCD.ID_ANHO',
            DB::raw(" TO_CHAR((SUM(VCD.DEBE) - sum(VCD.HABER)), 'fm999999999990.00')  AS SALDO")
        )
        ->groupBy(DB::raw("VCD.ID_ENTIDAD,VCD.ID_ANHO"))
        ->get();
        //echo($querySum[0]->saldo);
        //print_r($querySum):
        $i = 0;
        $saldo = 0;
        $percent = 0;
        $array_resultante[]= (object) array(
            'apce' => 0,
            'micop' => 0,
            'anop' => 0,
            'mpn' => 0,
            'mno' => 0,
            'saldo' => 0,
                );
        $categorias = [];

        foreach($query as $item){
            if($item->id_ctacte === '17211'){
                $array_resultante[0]->apce = number_format($item->saldo,2,'.','');
                $categorias[] = 'APCE';
            } else if($item->id_ctacte === '17311') {
                $array_resultante[0]->micop = number_format($item->saldo,2,'.','');
                $categorias[] = 'MICOP';
            } else if($item->id_ctacte === '17611') {
                $array_resultante[0]->anop = number_format($item->saldo,2,'.','');
                $categorias[] = 'ANOP';
            } else if($item->id_ctacte === '17811') {
                $array_resultante[0]->mpn = number_format($item->saldo,2,'.','');
                $categorias[] = 'MPN';
            } else if($item->id_ctacte === '17911') {
                $array_resultante[0]->mno = number_format($item->saldo,2,'.','');
                $categorias[] = 'MNO';
            }
            $array_resultante[0]->saldo = $array_resultante[0]->saldo + $item->saldo;
            $i =$i + 1;
            $percent = ($item->saldo * 100)/$querySum[0]->saldo;
            $series_data[]= floatval ($item->saldo);


        }

        $series[]=(object)array(
            'name'=>'Monto','data'=>$series_data,
            'color'=>'#023246'
        );

        $data['items']=$array_resultante;
        $data['categorias']=$categorias;
        $data['series']=$series;
        return $data;
    }


    public static function uploadAccountingEntry($request,$file) {
        $res="OK";
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $lote = $request->lote;
        DB::table('CONTA_DIARIO_FILE')
            ->updateOrInsert(
                array('ID_DIARIO' => $id_diario,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO'=>$id_anho,
                    'ID_MES'=>$id_mes,
                    'LOTE'=>$lote
            ),
                array('URL' => $file['url'],
                    'NOMBRE' => $file['filename'],
                    'TAMANHO'=>$file['size'],
                    'FORMATO'=>$file['format']
                )
            );
        return $res;
    }
    public static function deleteFileAccountingEntry($request) {
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $lote = $request->lote;
        $ret='OK';
        DB::table('CONTA_DIARIO_FILE')
                ->where('ID_DIARIO', $id_diario)
                ->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_ANHO', $id_anho)
                ->where('ID_MES', $id_mes)
                ->where('LOTE', $lote)
                ->delete();
        return $ret;
    }

    public static function addUpdateAccountNotice($request) {
        $res="OK";
        $id_diario = $request->id_diario;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $cod_cuenta = $request->cod_cuenta;
        $codigo = $request->codigo;
        DB::table('CONTA_DIARIO_CTA_AVISO')
            ->updateOrInsert(
                array('ID_DIARIO' => $id_diario,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO'=>$id_anho,
                    'ID_MES'=>$id_mes,
                    'CODIGO'=>$codigo,
            ),
                array('CUENTA'=>$cod_cuenta,
                )
            );
        return $res;
    }
    public static function getTipoArchivo($request) {
        $grupo_archivo = $request->grupo_archivo;
        $search = $request->search;
        $pageSize = $request->query('per_page');
        $conditionFileGroup ="";
        if ($grupo_archivo) {
        $conditionFileGroup =" AND GRUPO_ARCHIVO.ID_GRUPOARCHIVO  IN (" .$grupo_archivo.")";
        }
        $query = DB::table('TIPO_ARCHIVO')
        ->join('GRUPO_ARCHIVO', 'TIPO_ARCHIVO.ID_GRUPOARCHIVO', '=', DB::raw("GRUPO_ARCHIVO.ID_GRUPOARCHIVO ".$conditionFileGroup));
        if($search !==null || $search !==''){
            $query=$query->whereRaw("(TIPO_ARCHIVO.NOMBRE LIKE '%".$search."%' OR UPPER(TIPO_ARCHIVO.NOMBRE) LIKE UPPER('%".$search."%'))");
        }
        $query = $query->select('TIPO_ARCHIVO.*', 'GRUPO_ARCHIVO.NOMBRE as GRUPO_ARCHIVO',
        DB::raw( '(select count(1) from archivo_mensual where id_tipoarchivo =TIPO_ARCHIVO.id_tipoarchivo) as cant')
        )
        ;

        if($pageSize) {
            $query = $query->paginate($pageSize);
        } else {
            $query = $query->get();
        }

        return $query;
    }

    public static function getTypeEntity() {
        $query = DB::table('TIPO_ENTIDAD')
        ->select('*')
        ->orderBy('ORDEN','ASC')
        ->get();
        return $query;
    }
    public static function addTipoArchivo($request) {
        $nombre = $request->nombre;
        $abreviatura = $request->abreviatura;
        $id_grupoarchivo = $request->id_grupoarchivo;
        DB::table('TIPO_ARCHIVO')
            ->insert(
                array('NOMBRE' => $nombre,
                    'ABREVIATURA' => $abreviatura,
                    'ID_GRUPOARCHIVO' => $id_grupoarchivo
                )
            );
        $query = DB::table('TIPO_ARCHIVO')
        ->select('*')
        ->where('NOMBRE',$nombre)
        ->where('ABREVIATURA',$abreviatura)
        ->where('ID_GRUPOARCHIVO',$id_grupoarchivo)
        ->first();
        return $query;
    }

    public static function editTipoArchivo($id_tipoarchivo, $request) {
        $res="OK";
        $nombre = $request->nombre;
        $abreviatura = $request->abreviatura;
        $id_grupoarchivo = $request->id_grupoarchivo;

        DB::table('TIPO_ARCHIVO')
        ->where('ID_TIPOARCHIVO','=', $id_tipoarchivo)
        ->update(
            array('NOMBRE' => $nombre,
                'ABREVIATURA' => $abreviatura,
                'ID_GRUPOARCHIVO' => $id_grupoarchivo
            )
        );
        return $res;
    }

    public static function deleteTipoArchivo($id) {
        $res="OK";
        DB::table('TIPO_ARCHIVO')
                ->where('ID_TIPOARCHIVO', $id)
                ->whereRaw('(select count(1) from archivo_mensual where id_tipoarchivo = '.$id.') = 0')
                ->delete();
        return $res;
    }

    public static function getConfigMonthlyControl($request,$empr,$entities,$deptos) {
        $id_empresa = $request->id_empresa;
        // $id_entidad = $request->id_entidad;
        $id_entidades = $request->id_entidades;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $estado = $request->estado;
        $id_empresas=$empr;
        $id_entities=$entities;
        $id_deptos=$deptos;
        if($id_empresa==='all'){
            $id_empresa=null;
        }
/*         if($id_entidad==='all'){
            $id_entidad=null;
        } */
        if($id_entidades==='all'){
            $id_entidades=null;
        }

        if($id_empresa===null || $id_empresa!=='*'){
            $id_empresas=$id_empresa;
        }
/*         if($id_entidad===null || $id_entidad!=='*'){
            $id_entities=$id_entidad;
        } */

        if($id_entidades===null || $id_entidades!=='*'){
            // $id_entities= $id_entidades;
            if ($id_entidades===null || $id_entidades==='') {
                $id_entities = "IS NULL";
            } else {
                $id_entities = "IN ( $id_entidades)";
            }
        } elseif($id_entidades=='*') {
            $id_entities = "IN ($id_entities)";
        }

        if($id_depto==null || ($id_depto!=='*' && $id_depto!=='0')){
            $id_deptos=$id_depto;
        }
        $query="";
        // OJO: NULL AS ID_MES_F SIRVE PARA EL PROCESO DE COPIAR CONFIGURACION CONTROL MENSUAL
        if($id_empresa==='*'){
            $query="SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN,  TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE, 0 AS COPY,
            A.TIENE_PUNTAJE, A.ACTIVO,
            (SELECT COUNT(*) FROM ARCHIVO_MENSUAL_DETALLE WHERE ID_ARCHIVO_MENSUAL = A.ID_ARCHIVO_MENSUAL) AS TOTAL_ARCHIVOS
            FROM ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IS NULL AND A.ID_ENTIDAD IS NULL AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            AND A.ACTIVO = $estado
            UNION ALL";
        }
        if($id_entidades==='*'){
        $query=$query." SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN, TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE, 0 AS COPY,
            A.TIENE_PUNTAJE,A.ACTIVO,
            (SELECT COUNT(*) FROM ARCHIVO_MENSUAL_DETALLE WHERE ID_ARCHIVO_MENSUAL = A.ID_ARCHIVO_MENSUAL) AS TOTAL_ARCHIVOS
            FROM ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IN ($id_empresas) AND A.ID_ENTIDAD IS NULL AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            AND A.ACTIVO = $estado
            UNION ALL";
        }
        if($id_depto===null || $id_depto==='*'){
            $query=$query." SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN, TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE, 0 AS COPY,
            A.TIENE_PUNTAJE,A.ACTIVO,
            (SELECT COUNT(*) FROM ARCHIVO_MENSUAL_DETALLE WHERE ID_ARCHIVO_MENSUAL = A.ID_ARCHIVO_MENSUAL) AS TOTAL_ARCHIVOS
            FROM ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IN ($id_empresas) AND A.ID_ENTIDAD $id_entities AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            AND A.ACTIVO = $estado";
        }
        if($id_depto==='0'){
            $query=$query." SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA,
            NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA,
            A.ID_ENTIDAD,A.ID_DEPTO,
            (CASE WHEN A.ID_DEPTO = '0' THEN 'GENERAL' ELSE ced.NOMBRE END) AS DEPTO,
            A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN,
            TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE, 0 AS COPY,
            A.TIENE_PUNTAJE,A.ACTIVO,
            (SELECT COUNT(*) FROM ARCHIVO_MENSUAL_DETALLE WHERE ID_ARCHIVO_MENSUAL = A.ID_ARCHIVO_MENSUAL) AS TOTAL_ARCHIVOS
            FROM (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ENTIDAD $id_entities AND ID_DEPTO IN ($id_deptos,0)
            AND ID_ANHO = $id_anho AND ID_MES = $id_mes AND ACTIVO = $estado) A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO ced
            ON ced.ID_ENTIDAD = A.ID_ENTIDAD AND to_char(ced.ID_DEPTO) = to_char(A.ID_DEPTO)
            AND ced.ES_EMPRESA = '1'

/*             UNION ALL

            SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            ced.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN,
            TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE
            FROM (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ENTIDAD $id_entities AND ID_DEPTO IN ('0')
            AND ID_ANHO = $id_anho
            AND ID_MES = $id_mes ) A
            LEFT JOIN CONTA_ENTIDAD_DEPTO ced
            ON ced.ID_ENTIDAD = A.ID_ENTIDAD AND ced.ES_EMPRESA = '1'
            AND NOT ced.ID_DEPTO ='0'
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD */

            ";

        }else if($id_depto!==null && $id_depto!=='*'){
            $query=$query." SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, NULL AS ID_MES_FIN,
            TO_CHAR(A.FECHA_LIMITE, 'yyyy-mm-dd') as FECHA_LIMITE, 0 AS COPY,
            A.TIENE_PUNTAJE,A.ACTIVO,
            (SELECT COUNT(*) FROM ARCHIVO_MENSUAL_DETALLE WHERE ID_ARCHIVO_MENSUAL = A.ID_ARCHIVO_MENSUAL) AS TOTAL_ARCHIVOS
            FROM ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_ENTIDAD $id_entities AND A.ID_DEPTO IN ($id_deptos)
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            AND A.ACTIVO = $estado";
        }
        $query=$query." ORDER BY ID_EMPRESA DESC,ID_ENTIDAD DESC,ID_DEPTO DESC, ID_ANHO ASC, ID_MES ASC,FECHA_LIMITE ASC";
        // echo($query);
        $result=DB::select($query);
        return $result;
    }
    public static function addConfigMonthlyControl($request) {
        $res="OK";
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_entidades = $request->id_entidades;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $fecha_limite = $request->fecha_limite;
        $tiene_puntaje = $request->tiene_puntaje;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $pdo = DB::connection()->getPdo();

        $pdo = DB::getPdo();
        DB::beginTransaction();
        try {
            $stmt = $pdo->prepare("BEGIN ELISEO.PKG_MANAGEMENT_REPORTS.SP_IU_SETTING_CTRL_MENSUAL(
            :P_ID_EMPRESA,
	        :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_ID_ANHO,
	        :P_ID_MES,
            :P_ID_TIPOARCHIVO,
            :P_FECHA_LIMITE,
            :P_TIENE_PUNTAJE,
	        :P_ERROR,
            :P_MSGERROR
            );
            END;"
            );

            $stmt->bindParam(':P_ID_EMPRESA', $id_empresa, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidades, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOARCHIVO', $id_tipoarchivo, PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA_LIMITE', $fecha_limite, PDO::PARAM_STR);
            $stmt->bindParam(':P_TIENE_PUNTAJE', $tiene_puntaje, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if($error >= 1){
                DB::rollback();
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
            $error = 1;
            $msg_error=$e;
        }
        $result = [
            'error' => $error,
            'message' => $msg_error
        ];

        return $result;


    }
    public static function editConfigMonthlyControl($id_archivo_mensual,$request) {
        $res="OK";
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $fecha_limite = $request->fecha_limite;
        $tiene_puntaje = $request->tiene_puntaje;
        $activo = $request->activo;
        if($id_empresa===null || $id_empresa==='*' || $id_empresa==='all'){
            try{
                DB::table('ARCHIVO_MENSUAL')
                    ->where('ID_ANHO', $id_anho)
                    ->where('ID_MES', $id_mes)
                    ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                    ->delete();
                }catch(Exception $e){}
            $id_empresa=null;
        }
        if($id_entidad===null || $id_entidad==='*' || $id_entidad==='all'){
            if($id_empresa!==null and $id_empresa!=='*' and  $id_empresa!=='all'){
                try{
                    DB::table('ARCHIVO_MENSUAL')
                        ->where('ID_ANHO', $id_anho)
                        ->where('ID_MES', $id_mes)
                        ->where('ID_EMPRESA', $id_empresa)
                        ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                        ->where('ID_ARCHIVO_MENSUAL','<>', $id_archivo_mensual)
                        ->delete();
                    }catch(Exception $e){}
            }
            $id_entidad=null;
        }
        if($id_depto===null || $id_depto==='*' || $id_depto==='all'){
            if($id_entidad!==null and $id_entidad!=='*' and  $id_entidad!=='all'){
                try{
                    DB::table('ARCHIVO_MENSUAL')
                        ->where('ID_ANHO', $id_anho)
                        ->where('ID_MES', $id_mes)
                        ->where('ID_ENTIDAD', $id_entidad)
                        ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                        ->where('ID_ARCHIVO_MENSUAL','<>', $id_archivo_mensual)
                        ->delete();
                    }catch(Exception $e){}
            }
            $id_depto=null;
        }
        DB::table('ARCHIVO_MENSUAL')
            ->where('ID_ARCHIVO_MENSUAL', $id_archivo_mensual)
            ->update(
                array('ID_EMPRESA'=>$id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_ANHO' => $id_anho,
                    'ID_MES'=> $id_mes,
                    'ID_TIPOARCHIVO' => $id_tipoarchivo,
                    'FECHA_LIMITE' => $fecha_limite,
                    'TIENE_PUNTAJE' => $tiene_puntaje,
                    'ACTIVO' => $activo
                    )
            );
        return $res;
    }
    public static function deleteConfigMonthlyControl($id) {
        $res="OK";
        DB::table('ARCHIVO_MENSUAL')
                ->where('ID_ARCHIVO_MENSUAL', $id)
                ->delete();
        return $res;
    }

    public static function getTravelSummary($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_persona = $request->id_persona;
        $id_grupo = $request->id_grupo;
        $addMes = '';
        $addMes_ = '';
        $addMesAc = '';
        $addPerson = '';
        $addMonth = '';
        $addSql = '';
        $addGrupo = '';
        if($id_mes!==null and $id_mes !=='*'){
            $addMes =  " AND  ID_MES = " . $id_mes;
            $addMes_ =  " AND  ID_MES <= " . $id_mes;
            $addMesAc =  " AND  ID_MES < " . $id_mes;
            $addMonth =  " AND  CP.ID_MES = " . $id_mes;
        }
        if($id_persona !==null) {
            $addPerson =  "AND  C.ID_PERSONA = " . $id_persona;
        }

        if($id_grupo !==null and $id_grupo !== '*'){
            $addGrupo =  " AND  X.ID_GRUPO = " . $id_grupo;

        }

        if($id_mes!=='null' AND $id_mes!==null AND $id_mes !=='*' AND  $id_mes > 1){
            $addSql = " TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
                FROM VW_CONTA_DIARIO_ALL
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES = 12
                AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_TIPOASIENTO = 'EA'
                AND ID_DEPTO = CP.ID_DEPTO */
                0
                )
                +
                (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
                FROM vw_conta_Presupuesto
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES <= 12
                AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO */
                0
                )
                +
                (SELECT NVL(-1*SUM(COS_VALOR), 0) from CONTA_PRESUPUESTO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = " . $id_anho . " ". $addMesAc ."
                AND ID_CUENTAAASI IN (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO)
                +
                (SELECT NVL(-1*SUM(COS_VALOR), 0)
                FROM  VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = " . $id_anho . " ". $addMesAc ."
                AND ID_CUENTAAASI IN (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO
                AND ID_TIPOASIENTO != 'EA')

            ), 0), 'fm999999999990.00') as SALDO_ANTERIOR,
            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_TIPOASIENTO = 'EA'
            AND ID_DEPTO = CP.ID_DEPTO */
            0
            )
            +
            (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_DEPTO = CP.ID_DEPTO */
            0
            )), 0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
                ";
        } else {
            $addSql = "
            TO_CHAR(NVL(((/* select NVL(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_TIPOASIENTO = 'EA'
            AND ID_DEPTO = CP.ID_DEPTO */
            0
            )
            +
            (/* SELECT NVL(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_DEPTO = CP.ID_DEPTO */
            0
            )),0), 'fm999999999990.00') as SALDO_ANTERIOR,

            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_TIPOASIENTO = 'EA'
            AND ID_DEPTO = CP.ID_DEPTO */ 0)
            +
            (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_DEPTO = CP.ID_DEPTO */ 0)), 0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
            ";
        }
        // no se establece todas las cuentas AND ID_CUENTAAASI like '4%'
        $query = "SELECT
                        ITEM.*,
                        TO_CHAR((ITEM.PTO_GASTO + ITEM.EJE_GASTO), 'fm999999999990.00') AS SALDO,
                        TO_CHAR((ITEM.SALDO_ANTERIOR + ITEM.PTO_GASTO + ITEM.EJE_GASTO), 'fm999999999990.00') AS SALDO_ACUMULADO,
                        TO_CHAR((ITEM.SALDO_ANTERIOR_INI + ITEM.PTO_GASTO_ANUAL + ITEM.EJE_GASTO_ANUAL), 'fm999999999990.00') AS SALDO_PPTO_ANUAL,
                        (CASE WHEN ITEM.EJE_GASTO != 0 AND ITEM.PTO_GASTO != 0 THEN
                            TO_CHAR((((-1*ITEM.EJE_GASTO)*100)/ITEM.PTO_GASTO), 'fm999999999990.00')
                        ELSE
                            TO_CHAR(0,'fm999999999990.00')
                        END) AS PORCENTAJE
                FROM (
                SELECT
                    CP.ID_DEPTO ,
                    CED.NOM_DEPARTAMENTO AS DEPTO,
                    COALESCE(X.ID_GRUPO, '*') ID_GRUPO,
                    COALESCE((SELECT NOMBRE FROM CONTA_ENTIDAD_GRUPO WHERE ID_GRUPO = X.ID_GRUPO), 'SIN GRUPO') N_GRUPO,
                    (SELECT COLOR FROM CONTA_ENTIDAD_GRUPO WHERE ID_GRUPO = X.ID_GRUPO) COLOR,
                    C.PATERNO ||' '|| C.MATERNO ||', '|| C.NOMBRE AS FUNCIONARIO,
                    CP.ID_CTACTE,
                    ".$addSql."
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . " ". $addMes ."
                    AND ID_DEPTO not in ('0000','0001','0002','909211')
                    AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                    4113091, 4113092, 4113093, 4113094)
                    AND ID_CTACTE = CP.ID_CTACTE
                    AND ID_DEPTO = CP.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . "
                    AND ID_DEPTO not in ('0000','0001','0002','909211')
                    AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                    4113091, 4113092, 4113093, 4113094)
                    AND ID_CTACTE = CP.ID_CTACTE
                    AND ID_DEPTO = CP.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO_ANUAL,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMes ."
                        AND ID_DEPTO not in ('0000','0001','0002','909211')
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                        4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA' ),0), 'fm999999999990.00') AS EJE_GASTO,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMes_ ."
                        AND ID_DEPTO not in ('0000','0001','0002','909211')
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,
                        4121029 ,4113030, 4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_GASTO_ANUAL
                FROM VW_CONTA_DIARIO CP
                INNER JOIN MOISES.PERSONA_DOCUMENTO B ON CP.ID_CTACTE=B.NUM_DOCUMENTO
                INNER JOIN MOISES.PERSONA C ON B.ID_PERSONA=C.ID_PERSONA
                INNER JOIN VW_CONTA_ENTIDAD_DEPTO CED ON (CED.ID_ENTIDAD = CP.ID_ENTIDAD AND CED.ID_DEPTO = CP.ID_DEPTO)
                LEFT JOIN CONTA_ENTIDAD_DEPTO_GRUPO_C X ON (X.ID_ENTIDAD = CP.ID_ENTIDAD AND X.ID_DEPTO = CP.ID_DEPTO)
                WHERE  CP.ID_ENTIDAD = " . $id_entidad . "
                AND CP.ID_DEPTO NOT IN ('0000', '0001', '0002', '909211')
                AND CP.ID_ANHO = " . $id_anho . "
                ". $addMonth ." ".$addPerson." ".$addGrupo."
                GROUP BY CP.ID_DEPTO, CED.NOM_DEPARTAMENTO, X.ID_GRUPO,  CP.ID_CTACTE, C.PATERNO, C.MATERNO, C.NOMBRE
                ORDER BY CED.NOM_DEPARTAMENTO, C.PATERNO
                )ITEM";
        // print($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getFileGroup($request) {
        $search = $request->search;
        $pageSize = $request->query('per_page');
        $query = DB::table('GRUPO_ARCHIVO');
        if($search !==null || $search !==''){
            $query=$query->whereRaw("(GRUPO_ARCHIVO.NOMBRE LIKE '%".$search."%' OR UPPER(GRUPO_ARCHIVO.NOMBRE) LIKE UPPER('%".$search."%'))");
        }
        $query = $query->select('GRUPO_ARCHIVO.*',
        DB::raw( '(select count(1) from tipo_archivo where id_grupoarchivo =GRUPO_ARCHIVO.id_grupoarchivo) as cant')
        );

        if($pageSize) {
            $query = $query->paginate($pageSize);
        } else {
            $query = $query->get();
        }

        return $query;
    }
    public static function addFileGroup($request) {
        $nombre = $request->nombre;
        DB::table('GRUPO_ARCHIVO')
            ->insert(
                array('NOMBRE' => $nombre
                )
            );
        $query = DB::table('GRUPO_ARCHIVO')
        ->select('*')
        ->where('NOMBRE',$nombre)
        ->first();
        return $query;
    }

    public static function editFileGroup($id_grupoarchivo, $request) {
        $res="OK";
        $nombre = $request->nombre;

        DB::table('GRUPO_ARCHIVO')
        ->where('ID_GRUPOARCHIVO','=', $id_grupoarchivo)
        ->update(
            array('NOMBRE' => $nombre
            )
        );
        return $res;
    }

    public static function deleteFileGroup($id) {
        $res="OK";
        DB::table('GRUPO_ARCHIVO')
                ->where('ID_GRUPOARCHIVO', $id)
                ->whereRaw('(select count(1) from tipo_archivo where id_grupoarchivo = '.$id.') = 0')
                ->delete();
        return $res;
    }

    public static function getMonthlyControl($id_grupoarchivo, $id_anho, $id_mes, $id_entidad, $deptos, $id_empresa) {

        $ruta = '';
        //$ruta = 'management_report/';
        // $ruta = $_ENV['LAMB_MINIO_ENDPOINT'].'/media/management_report/';
        // $id_entidad = $request->id_entidad;
        // $id_depto = $request->id_depto;
        // $id_grupoarchivo = $request->id_grupoarchivo;
        // $id_anho = $request->id_anho;
        // $id_mes = $request->id_mes;

        if($id_mes===null or $id_mes==='*'){
            $id_mes=" IS NOT NULL";
        }else{
            $id_mes=" = $id_mes";
        }
        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }
        $query="";

        if($deptos === '0'){
            $query="SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,CE.NOMBRE AS ENTIDAD,A.ID_DEPTO,CED.NOMBRE AS DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,
            trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
            A.TIENE_PUNTAJE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,
            (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
            FROM  ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO CED ON A.ID_ENTIDAD = CED.ID_ENTIDAD AND A.ID_DEPTO = CED.ID_DEPTO
            LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            WHERE A.ID_ENTIDAD=$id_entidad
            AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND A.ACTIVO = '1'
            AND B.ID_GRUPOARCHIVO $id_grupoarchivo

            UNION ALL

            SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,CE.NOMBRE AS ENTIDAD,A.ID_DEPTO,CED.NOMBRE AS DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
            A.TIENE_PUNTAJE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,
            (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
            FROM  ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO CED ON A.ID_ENTIDAD = CED.ID_ENTIDAD AND A.ID_DEPTO = CED.ID_DEPTO
            LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            WHERE A.ID_EMPRESA =$id_empresa
            AND A.ID_ENTIDAD IS NULL
            AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND A.ACTIVO = '1'
            AND B.ID_GRUPOARCHIVO $id_grupoarchivo

            UNION ALL

            SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,CE.NOMBRE AS ENTIDAD,A.ID_DEPTO,CED.NOMBRE AS DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
            A.TIENE_PUNTAJE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,
            (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
            FROM  ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO CED ON A.ID_ENTIDAD = CED.ID_ENTIDAD AND A.ID_DEPTO = CED.ID_DEPTO
            LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            WHERE A.ID_EMPRESA IS NULL
            AND A.ID_ENTIDAD IS NULL
            AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND A.ACTIVO = '1'
            AND B.ID_GRUPOARCHIVO $id_grupoarchivo
            ORDER BY GRUPOARCHIVO,TIPOARCHIVO,ID_ANHO,ID_MES,FECHA_LIMITE";
        }else
        {
            $query="SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,CE.NOMBRE AS ENTIDAD,A.ID_DEPTO,CED.NOMBRE AS DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
            A.TIENE_PUNTAJE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,
            (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
            FROM  ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO CED ON A.ID_ENTIDAD = CED.ID_ENTIDAD AND A.ID_DEPTO = CED.ID_DEPTO
            LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            WHERE A.ID_ENTIDAD=$id_entidad
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND A.ID_DEPTO IN ($deptos)
            AND A.ACTIVO = '1'
            AND B.ID_GRUPOARCHIVO $id_grupoarchivo
            --ORDER BY GRUPOARCHIVO,TIPOARCHIVO,ID_ANHO,ID_MES,FECHA_LIMITE

            UNION ALL

            SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,CE.NOMBRE AS ENTIDAD,A.ID_DEPTO,CED.NOMBRE AS DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
            A.TIENE_PUNTAJE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,
            (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
            FROM  ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
            LEFT JOIN CONTA_ENTIDAD_DEPTO CED ON A.ID_ENTIDAD = CED.ID_ENTIDAD AND A.ID_DEPTO = CED.ID_DEPTO
            LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            WHERE A.ID_ENTIDAD=$id_entidad
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND A.ID_DEPTO IN (0)
            AND A.ACTIVO = '1'
            AND B.ID_GRUPOARCHIVO $id_grupoarchivo
            ORDER BY GRUPOARCHIVO,TIPOARCHIVO,ID_ANHO,ID_MES,FECHA_LIMITE
            ";
        }

        // $query = "
        //     SELECT C.ID_GRUPOARCHIVO, C.NOMBRE AS GRUPOARCHIVO, B.NOMBRE AS TIPOARCHIVO, A.ID_ARCHIVO_MENSUAL,
        //         A.ID_EMPRESA, A.ID_ENTIDAD, CE.NOMBRE AS ENTIDAD, A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES,
        //         trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
        //         A.TIENE_PUNTAJE, D.ID_DETALLE, D.FECHA_CREACION, D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,
        //         D.NOMBRE AS FILE_NOMBRE, D.FORMATO, D.TAMANHO, D.ID_USER,
        //         (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
        //     FROM ARCHIVO_MENSUAL A
        //     INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
        //     INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
        //     INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
        //     LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD = A.ID_ENTIDAD
        //     WHERE (A.ID_ENTIDAD = $id_entidad AND A.ID_DEPTO IN ($deptos))
        //     AND A.ID_ANHO = $id_anho
        //     AND A.ID_MES $id_mes
        //     AND A.ACTIVO = '1'
        //     AND B.ID_GRUPOARCHIVO $id_grupoarchivo
        //     UNION ALL
        //     SELECT C.ID_GRUPOARCHIVO, C.NOMBRE AS GRUPOARCHIVO, B.NOMBRE AS TIPOARCHIVO, A.ID_ARCHIVO_MENSUAL,
        //         A.ID_EMPRESA, A.ID_ENTIDAD, CE.NOMBRE AS ENTIDAD, A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES,
        //         trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,
        //         A.TIENE_PUNTAJE, D.ID_DETALLE, D.FECHA_CREACION, D.FECHA_MODIFICACION, '".$ruta."' ||''|| D.URL AS FILE_URL,
        //         D.NOMBRE AS FILE_NOMBRE, D.FORMATO, D.TAMANHO, D.ID_USER,
        //         (SELECT PN.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL PN WHERE PN.ID_PERSONA = D.ID_USER AND ROWNUM = 1) AS USER_NAME
        //     FROM ARCHIVO_MENSUAL A
        //     INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
        //     INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
        //     INNER JOIN CONTA_ENTIDAD CE ON A.ID_ENTIDAD = CE.ID_ENTIDAD
        //     LEFT JOIN ARCHIVO_MENSUAL_DETALLE D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD = A.ID_ENTIDAD
        //      WHERE (A.ID_ENTIDAD = $id_entidad AND A.ID_DEPTO IN ($deptos))
        //     AND A.ID_ANHO = $id_anho
        //     AND A.ID_MES $id_mes
        //     AND A.ID_DEPTO IN (0)
        //     AND A.ACTIVO = '1'
        //     AND B.ID_GRUPOARCHIVO $id_grupoarchivo
        //     ORDER BY GRUPOARCHIVO, TIPOARCHIVO, ID_ANHO, ID_MES, FECHA_LIMITE
        // ";

        $result= DB::select($query);
        return $result;
    }

    public static function uploadMonthlyControl($request,$file,$id_user) {
        $res="OK";
        $id_archivo_mensual = $request->id_archivo_mensual;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $fecha_creacion = $request->fecha_creacion;
        $fecha = new DateTime();
        if($fecha_creacion===null){
            $fecha_creacion=$fecha;
        }
        if(!$id_depto || $id_depto==='*'){
            $id_depto=null;
        }
        DB::table('ARCHIVO_MENSUAL_DETALLE')
            ->updateOrInsert(
                array('ID_ARCHIVO_MENSUAL' => $id_archivo_mensual,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto
            ),
                array(
                    'FECHA_CREACION'=>$fecha_creacion,
                    'FECHA_MODIFICACION'=>$fecha,
                    'URL' => $file['url'],
                    'NOMBRE' => $file['filename'],
                    'TAMANHO'=>$file['size'],
                    'FORMATO'=>$file['format'],
                    'ID_USER'=>$id_user
                )
            );
        return $res;
    }
    public static function deleteFileMonthlyControl($request,$id_user) {
        $id_detalle = $request->id_detalle;
        $ret='OK';
        $fecha = new DateTime();
        DB::table('ARCHIVO_MENSUAL_DETALLE')
                ->where('ID_DETALLE', $id_detalle)
                ->update(
                    array(
                        'FECHA_MODIFICACION'=>$fecha,
                        'URL' =>null,
                        'NOMBRE' => null,
                        'TAMANHO'=>null,
                        'FORMATO'=>null,
                        'ID_USER'=>$id_user
                    )
                );
        return $ret;
    }
    public static function getFinancialStatements($request,$empr,$entities) {
        $data=[];
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_fondo = $request->id_fondo;
        $id_anhofirst = $request->id_anhofirst;
        $id_mesfirst = $request->id_mesfirst;
        $id_anhosecond = $request->id_anhosecond;
        $id_messecond = $request->id_messecond;
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad=" IS NOT NULL";
        }else{
            $id_entidad=" = $id_entidad";
        }
        if($id_depto===null or $id_depto==='*' or $id_depto==='0'){
            $id_depto=" IS NOT NULL";
        }else{
            $id_depto=" = '$id_depto'";
        }
        if($id_anhofirst===null or $id_anhofirst==='*'){
            $id_anhofirst=" IS NOT NULL";
        }else{
            $id_anhofirst=" = $id_anhofirst";
        }
        if($id_mesfirst===null or $id_mesfirst==='*'){
            $id_mesfirst="<=12";
        }else{
            $id_mesfirst=" <= $id_mesfirst";
        }
        if($id_anhosecond===null or $id_anhosecond==='*'){
            $id_anhosecond=" IS NOT NULL";
        }else{
            $id_anhosecond=" = $id_anhosecond";
        }
        if($id_messecond===null or $id_messecond==='*'){
            $id_messecond="<=12";
        }else{
            $id_messecond=" <= $id_messecond";
        }
        $find_all_fondo = strpos($id_fondo,'*');
        if($id_fondo===null or $id_fondo==='*' or $find_all_fondo===true){
            $id_fondo=" IS NOT NULL";
        }else{
            $replace1=str_replace('*,','',$id_fondo);
            $replace2=str_replace(',*','',$replace1);
            $id_fondo=" IN ($replace2)";
        }
        $query="SELECT * FROM (
                SELECT CASE WHEN ES_GRUPO1=1 AND ES_GRUPO2=1 AND ES_GRUPO3=1 THEN '1' ELSE LEVEL1 END AS LEVEL1,LEVEL2,LEVEL3,
                    CASE WHEN ES_GRUPO2=1 AND ES_GRUPO3=1 THEN
                    (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=RPAD(LEVEL1,7,'0'))
                    WHEN ES_GRUPO2=0 AND ES_GRUPO3=1 THEN
                    (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=RPAD(LEVEL2,7,'0'))
                    ELSE
                    (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=LEVEL3)
                    END AS CUENTA,
                    CASE WHEN LEVEL1=2 THEN -SALDO1 ELSE SALDO1 END AS SALDO1,CASE WHEN LEVEL1=2 THEN -SALDO2 ELSE SALDO2 END AS SALDO2,ES_GRUPO1,ES_GRUPO2,ES_GRUPO3
                FROM (SELECT LEVEL1,
                            LEVEL2,
                            LEVEL3,
                            SUM(NVL(SALDO1,0))AS SALDO1,
                            SUM(NVL(SALDO2,0))AS SALDO2,
                            GROUPING(LEVEL1) AS ES_GRUPO1,
                            GROUPING(LEVEL2) AS ES_GRUPO2,
                            GROUPING(LEVEL3) AS ES_GRUPO3
                    FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS LEVEL1,
                                SUBSTR(A.ID_CUENTAAASI,0,2) AS LEVEL2,
                                A.ID_CUENTAAASI AS LEVEL3,
                                SUM(SALDO1)AS SALDO1,
                                SUM(SALDO2)AS SALDO2
                            FROM VW_CONTA_CTA_DENOMINACIONAL_C A
                            LEFT JOIN (SELECT REPLACE(ID_CUENTAAASI, '1136080','1180000') AS ID_CUENTAAASI,
                                            SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                        FROM VW_CONTA_DIARIO_ALL
                                        WHERE ID_EMPRESA $id_empresa
                                            AND ID_ENTIDAD $id_entidad
                                            AND ID_DEPTO $id_depto
                                            AND ID_ANHO $id_anhofirst
                                            AND ID_MES $id_mesfirst
                                            AND ID_FONDO $id_fondo
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND ID_CUENTAAASI LIKE '1%'
                                        GROUP BY ID_CUENTAAASI
                                        UNION ALL
                                        SELECT REPLACE(ID_CUENTAAASI, '1136080','1180000') AS ID_CUENTAAASI,
                                            0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                        FROM VW_CONTA_DIARIO_ALL
                                        WHERE ID_EMPRESA $id_empresa
                                            AND ID_ENTIDAD $id_entidad
                                            AND ID_DEPTO $id_depto
                                            AND ID_ANHO $id_anhosecond
                                            AND ID_MES $id_messecond
                                            AND ID_FONDO $id_fondo
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND ID_CUENTAAASI LIKE '1%'
                                        GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,3)=SUBSTR(A.ID_CUENTAAASI,0,3)
                        WHERE SUBSTR(A.ID_CUENTAAASI,4,7)=RPAD('0',4,'0')
                        AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,2),7,'0')
                        AND A.ID_CUENTAAASI LIKE '1%'
                        GROUP BY A.ID_CUENTAAASI)
                GROUP BY ROLLUP (LEVEL1,LEVEL2,LEVEL3)
                ORDER BY LEVEL1 ASC,ES_GRUPO2 DESC,LEVEL2 ASC,ES_GRUPO3 DESC, LEVEL3 ASC)
                UNION ALL
                SELECT CASE WHEN ES_GRUPO1=1 AND ES_GRUPO2=1 AND ES_GRUPO3=1 THEN '2' ELSE LEVEL1 END AS LEVEL1,LEVEL2,LEVEL3,
                    CASE WHEN ES_GRUPO2=1 AND ES_GRUPO3=1 THEN
                    (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=RPAD(LEVEL1,7,'0'))
                    WHEN ES_GRUPO2=0 AND ES_GRUPO3=1 THEN
                    (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=RPAD(LEVEL2,7,'0'))
                    ELSE
                        (CASE WHEN LEVEL3 = '23132317' THEN
                            'CAPITAL'
                        ELSE
                            (SELECT DISTINCT A.NOMBRE FROM VW_CONTA_CTA_DENOMINACIONAL_C A WHERE A.ID_CUENTAAASI=LEVEL3)
                        END)
                    END AS CUENTA,
                    -SALDO1,-SALDO2,ES_GRUPO1,ES_GRUPO2,ES_GRUPO3
                FROM (SELECT LEVEL1,
                            LEVEL2,
                            LEVEL3,
                            SUM(NVL(SALDO1,0))AS SALDO1,
                            SUM(NVL(SALDO2,0))AS SALDO2,
                            GROUPING(LEVEL1) AS ES_GRUPO1,
                            GROUPING(LEVEL2) AS ES_GRUPO2,
                            GROUPING(LEVEL3) AS ES_GRUPO3
                    FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS LEVEL1,
                                SUBSTR(A.ID_CUENTAAASI,0,2) AS LEVEL2,
                                A.ID_CUENTAAASI AS LEVEL3,
                                SUM(SALDO1)AS SALDO1,
                                SUM(SALDO2)AS SALDO2
                            FROM VW_CONTA_CTA_DENOMINACIONAL_C A
                            LEFT JOIN (SELECT REPLACE(ID_CUENTAAASI, '2136080','2180000') AS ID_CUENTAAASI,
                                            SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                        FROM VW_CONTA_DIARIO_ALL
                                        WHERE ID_EMPRESA $id_empresa
                                            AND ID_ENTIDAD $id_entidad
                                            AND ID_DEPTO $id_depto
                                            AND ID_ANHO $id_anhofirst
                                            AND ID_MES $id_mesfirst
                                            AND ID_FONDO $id_fondo
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,1) IN (2)
                                            AND SUBSTR(ID_CUENTAAASI,0,2) NOT IN (23)
                                        GROUP BY ID_CUENTAAASI
                                        UNION ALL
                                        SELECT REPLACE(ID_CUENTAAASI, '2136080','2180000') AS ID_CUENTAAASI,
                                            0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                        FROM VW_CONTA_DIARIO_ALL
                                        WHERE ID_EMPRESA $id_empresa
                                            AND ID_ENTIDAD $id_entidad
                                            AND ID_DEPTO $id_depto
                                            AND ID_ANHO $id_anhosecond
                                            AND ID_MES $id_messecond
                                            AND ID_FONDO $id_fondo
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,1) IN (2)
                                            AND SUBSTR(ID_CUENTAAASI,0,2) NOT IN (23)
                                        GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,3)=SUBSTR(A.ID_CUENTAAASI,0,3)
                        WHERE SUBSTR(A.ID_CUENTAAASI,4,7)=RPAD('0',4,'0')
                        AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,2),7,'0')
                        AND SUBSTR(A.ID_CUENTAAASI,0,1) IN (2)
                        AND SUBSTR(A.ID_CUENTAAASI,0,2) NOT IN (23)
                        GROUP BY A.ID_CUENTAAASI
                        UNION ALL
                        SELECT LEVEL1, LEVEL2, '2310000' AS LEVEL3,
                        SUM(SALDO1) AS SALDO1,
                        SUM(SALDO2) AS SALDO2
                        FROM (SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS LEVEL1,
                                    SUBSTR(A.ID_CUENTAAASI,0,2) AS LEVEL2,
                                    A.ID_CUENTAAASI AS LEVEL3,
                                    SUM(SALDO1)AS SALDO1,
                                    SUM(SALDO2)AS SALDO2
                                FROM CONTA_CTA_DENOMINACIONAL A
                                LEFT JOIN (SELECT ID_CUENTAAASI,
                                                SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhofirst
                                                AND ID_MES $id_mesfirst
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (23)
                                                AND SUBSTR(ID_CUENTAAASI,0,3) != 232
                                                AND SUBSTR(ID_CUENTAAASI,0,4) NOT IN (2313,2317)
                                                AND SUBSTR(ID_CUENTAAASI,0,5) NOT IN (23125)
                                            GROUP BY ID_CUENTAAASI
                                            UNION ALL
                                            SELECT ID_CUENTAAASI,
                                                0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhosecond
                                                AND ID_MES $id_messecond
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (23)
                                                AND SUBSTR(ID_CUENTAAASI,0,3) != 232
                                                AND SUBSTR(ID_CUENTAAASI,0,4) NOT IN (2313,2317)
                                                AND SUBSTR(ID_CUENTAAASI,0,5) NOT IN (23125)
                                            GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,5)=SUBSTR(A.ID_CUENTAAASI,0,5)
                            WHERE SUBSTR(A.ID_CUENTAAASI,6,7)=RPAD('0',2,'0')
                            AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,3),7,'0')
                            AND SUBSTR(A.ID_CUENTAAASI,0,2) IN (23)
                            AND SUBSTR(A.ID_CUENTAAASI,0,3) != 232
                            AND SUBSTR(A.ID_CUENTAAASI,0,4) NOT IN (2313,2317)
                            AND SUBSTR(A.ID_CUENTAAASI,0,5) NOT IN (23125)
                            GROUP BY A.ID_CUENTAAASI) WHERE LEVEL2 = 23
                        GROUP BY LEVEL1, LEVEL2


                        UNION ALL
                        SELECT LEVEL1, LEVEL2, '23132317' AS LEVEL3,
                           SUM(SALDO1) AS SALDO1,
                           SUM(SALDO2) AS SALDO2
                           FROM (
                            SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS LEVEL1,
                                    SUBSTR(A.ID_CUENTAAASI,0,2) AS LEVEL2,
                                    A.ID_CUENTAAASI AS LEVEL3,
                                    SUM(SALDO1)AS SALDO1,
                                    SUM(SALDO2)AS SALDO2
                                FROM CONTA_CTA_DENOMINACIONAL A
                                LEFT JOIN (SELECT ID_CUENTAAASI,
                                                SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhofirst
                                                AND ID_MES $id_mesfirst
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,4) IN (2313,2317)
                                            GROUP BY ID_CUENTAAASI
                                            UNION ALL
                                            SELECT ID_CUENTAAASI,
                                                0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhosecond
                                                AND ID_MES $id_messecond
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,4) IN (2313,2317)
                                            GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,5)=SUBSTR(A.ID_CUENTAAASI,0,5)
                            WHERE SUBSTR(A.ID_CUENTAAASI,6,7)=RPAD('0',2,'0')
                            AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,3),7,'0')
                            AND SUBSTR(A.ID_CUENTAAASI,0,4) IN (2313,2317)
                            GROUP BY A.ID_CUENTAAASI) WHERE   LEVEL2 = 23
                            GROUP BY LEVEL1, LEVEL2
                        UNION ALL

                        SELECT LEVEL1, LEVEL2, '2320000' AS LEVEL3,
                           SUM(SALDO1) AS SALDO1,
                           SUM(SALDO2) AS SALDO2
                           FROM (
                            SELECT SUBSTR(A.ID_CUENTAAASI,0,1) AS LEVEL1,
                                    SUBSTR(A.ID_CUENTAAASI,0,2) AS LEVEL2,
                                    A.ID_CUENTAAASI AS LEVEL3,
                                    SUM(SALDO1)AS SALDO1,
                                    SUM(SALDO2)AS SALDO2
                                FROM CONTA_CTA_DENOMINACIONAL A
                                LEFT JOIN (SELECT ID_CUENTAAASI,
                                                SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhofirst
                                                AND ID_MES $id_mesfirst
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,3) = 232
                                            GROUP BY ID_CUENTAAASI
                                            UNION ALL
                                            SELECT ID_CUENTAAASI,
                                                0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidad
                                                AND ID_DEPTO $id_depto
                                                AND ID_ANHO $id_anhosecond
                                                AND ID_MES $id_messecond
                                                AND ID_FONDO $id_fondo
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,3) = 232
                                            GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,5)=SUBSTR(A.ID_CUENTAAASI,0,5)
                            WHERE SUBSTR(A.ID_CUENTAAASI,6,7)=RPAD('0',2,'0')
                            AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,3),7,'0')
                            AND SUBSTR(A.ID_CUENTAAASI,0,3) = 232
                            GROUP BY A.ID_CUENTAAASI) WHERE   LEVEL2 = 23
                            GROUP BY LEVEL1, LEVEL2
                        UNION ALL
                        SELECT LEVEL1,LEVEL2,LEVEL3,-SUM(SALDO1) AS SALDO1,-SUM(SALDO2) AS SALDO2
                        FROM (SELECT '2' AS LEVEL1,'23' AS LEVEL2,'2312500' AS LEVEL3,-SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                            FROM VW_CONTA_DIARIO_ALL
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidad
                                AND ID_DEPTO $id_depto
                                AND ID_ANHO $id_anhofirst
                                AND ID_MES $id_mesfirst
                                AND ID_FONDO $id_fondo
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6)
                            UNION ALL
                            SELECT '2' AS LEVEL1,'23' AS LEVEL2,'2312500' AS LEVEL3,0 AS SALDO1,-SUM(COS_VALOR) AS SALDO2
                            FROM VW_CONTA_DIARIO_ALL
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidad
                                AND ID_DEPTO $id_depto
                                AND ID_ANHO $id_anhosecond
                                AND ID_MES $id_messecond
                                AND ID_FONDO $id_fondo
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6))
                        GROUP BY LEVEL1,LEVEL2,LEVEL3)
                GROUP BY ROLLUP (LEVEL1,LEVEL2,LEVEL3)
                ORDER BY LEVEL1 ASC,ES_GRUPO2 DESC,LEVEL2 ASC,ES_GRUPO3 DESC, LEVEL3 ASC)
                ) U
                WHERE saldo1!=0 OR saldo2!=0
                ";
        // echo($query);
        $financial_situation = DB::select($query);
        //var_dump($query);

        $query="SELECT * FROM (
                SELECT NULL AS LEVEL1,NULL AS LEVEL2,NULL AS LEVEL3,'OPERACIONAL' AS CUENTA,NULL AS SALDO1,NULL AS SALDO2,NULL AS VARIACION,NULL AS ES_GRUPO1,NULL AS ES_GRUPO2 FROM DUAL
                UNION ALL
                SELECT 1 AS LEVEL1, LEVEL1 AS LEVEL2,LEVEL2 AS LEVEL3,
                    CASE WHEN ES_GRUPO2=0 THEN
                        (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=LEVEL2)
                    WHEN ES_GRUPO1=0 AND ES_GRUPO2=1 THEN
                        CASE WHEN LEVEL1=1.1 THEN
                            'INGRESOS'
                        ELSE
                        'GASTOS'
                        END
                    WHEN ES_GRUPO1=1 AND ES_GRUPO2=1 THEN
                    'RESULTADO OPERACIONAL S / SUBVENCIONES'
                    ELSE
                        NULL
                    END AS CUENTA,
                    CASE WHEN LEVEL1=1.2 THEN -SALDO1 ELSE SALDO1 END AS SALDO1,
                    CASE WHEN LEVEL1=1.2 THEN -SALDO2 ELSE SALDO2 END AS SALDO2,
                    NVL(VARIACION,0) AS VARIACION,ES_GRUPO1,ES_GRUPO2
                FROM (SELECT LEVEL1,
                            LEVEL2,
                            -SUM(NVL(SALDO1,0))AS SALDO1,
                            -SUM(NVL(SALDO2,0))AS SALDO2,
                            CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                            GROUPING(LEVEL1) AS ES_GRUPO1,
                            GROUPING(LEVEL2) AS ES_GRUPO2
                    FROM (SELECT CASE WHEN SUBSTR(A.ID_CUENTAAASI,0,1)=3 THEN 1.1 ELSE 1.2 END AS LEVEL1,
                            A.ID_CUENTAAASI AS LEVEL2,
                            SUM(SALDO1)AS SALDO1,
                            SUM(SALDO2)AS SALDO2,
                            CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION
                        FROM CONTA_CTA_DENOMINACIONAL A
                        LEFT JOIN (SELECT ID_CUENTAAASI,
                                        SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                    FROM VW_CONTA_DIARIO_ALL
                                    WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidad
                                    AND ID_DEPTO $id_depto
                                    AND ID_ANHO $id_anhofirst
                                    AND ID_MES $id_mesfirst
                                    AND ID_FONDO $id_fondo
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4)
                                        AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (319,321,421)
                                    GROUP BY ID_CUENTAAASI
                                    UNION ALL
                                    SELECT ID_CUENTAAASI,
                                        0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                    FROM VW_CONTA_DIARIO_ALL
                                    WHERE ID_EMPRESA $id_empresa
                                        AND ID_ENTIDAD $id_entidad
                                        AND ID_DEPTO $id_depto
                                        AND ID_ANHO $id_anhosecond
                                        AND ID_MES $id_messecond
                                        AND ID_FONDO $id_fondo
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4)
                                        AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (319,321,421)
                                    GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,3)=SUBSTR(A.ID_CUENTAAASI,0,3)
                        WHERE SUBSTR(A.ID_CUENTAAASI,4,7)=RPAD('0',4,'0')
                        AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,2),7,'0')
                        AND SUBSTR(A.ID_CUENTAAASI,0,1) IN (3,4)
                        AND SUBSTR(A.ID_CUENTAAASI,0,3) NOT IN (319,321,421)
                        GROUP BY A.ID_CUENTAAASI)
                GROUP BY ROLLUP (LEVEL1,LEVEL2)
                ORDER BY LEVEL1 ASC,ES_GRUPO2 DESC,LEVEL2 ASC)
                UNION ALL
                SELECT 2 AS LEVEL1,2.1 AS LEVEL2,'3190000' AS LEVEL3,
                    (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=3190000) AS CUENTA,
                    -SUM(SALDO1)AS SALDO1,
                    -SUM(SALDO2)AS SALDO2,
                    CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                    0 AS ES_GRUPO1,0 AS ES_GRUPO2
                FROM (SELECT A.ID_CUENTAAASI,SUM(NVL(B.SALDO1,0)) AS SALDO1,SUM(NVL(B.SALDO2,0)) AS SALDO2
                        FROM CONTA_CTA_DENOMINACIONAL A
                        LEFT JOIN (SELECT ID_CUENTAAASI,
                                    SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                FROM VW_CONTA_DIARIO_ALL
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidad
                                    AND ID_DEPTO $id_depto
                                    AND ID_ANHO $id_anhofirst
                                    AND ID_MES $id_mesfirst
                                    AND ID_FONDO $id_fondo
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND SUBSTR(ID_CUENTAAASI,0,3) IN (319)
                                GROUP BY ID_CUENTAAASI
                                UNION ALL
                                SELECT ID_CUENTAAASI,
                                    0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                FROM VW_CONTA_DIARIO_ALL
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidad
                                    AND ID_DEPTO $id_depto
                                    AND ID_ANHO $id_anhosecond
                                    AND ID_MES $id_messecond
                                    AND ID_FONDO $id_fondo
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND SUBSTR(ID_CUENTAAASI,0,3) IN (319)
                                GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,3)=SUBSTR(A.ID_CUENTAAASI,0,3)
                        WHERE A.ID_CUENTAAASI=3190000
                        GROUP BY A.ID_CUENTAAASI)
                GROUP BY SUBSTR(ID_CUENTAAASI,0,3)
                UNION ALL
                SELECT 2 AS LEVEL1,NULL AS LEVEL2,NULL AS LEVEL3,
                    'RESULTADO OPERACIONAL C / SUBVENCIONES' AS CUENTA,
                    -SUM(SALDO1)AS SALDO1,
                    -SUM(SALDO2)AS SALDO2,
                    CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                    1 AS ES_GRUPO1,1 AS ES_GRUPO2
                FROM (SELECT SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                    FROM VW_CONTA_DIARIO_ALL
                    WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anhofirst
                        AND ID_MES $id_mesfirst
                        AND ID_FONDO $id_fondo
                        AND ID_TIPOASIENTO NOT IN ('EA')
                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4)
                        AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (321,421)
                    UNION ALL
                    SELECT 0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                    FROM VW_CONTA_DIARIO_ALL
                    WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anhosecond
                        AND ID_MES $id_messecond
                        AND ID_FONDO $id_fondo
                        AND ID_TIPOASIENTO NOT IN ('EA')
                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4)
                        AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (321,421))
                UNION ALL
                SELECT NULL AS LEVEL1,NULL AS LEVEL2,NULL AS LEVEL3,'NO OPERACIONAL' AS CUENTA,NULL AS SALDO1,NULL AS SALDO2,NULL AS VARIACION,NULL AS ES_GRUPO1,NULL AS ES_GRUPO2 FROM DUAL
                UNION ALL
                SELECT 3 AS LEVEL1, LEVEL1 AS LEVEL2,LEVEL2 AS LEVEL3,
                    CASE WHEN ES_GRUPO1=0 THEN
                        (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=LEVEL2)
                    WHEN ES_GRUPO1=1 THEN
                    'RESULTADO NO OPERACIONAL'
                    ELSE
                        NULL
                    END AS CUENTA,
                    CASE WHEN LEVEL1=3.2 THEN -SALDO1 ELSE SALDO1 END AS SALDO1,
                    CASE WHEN LEVEL1=3.2 THEN -SALDO2 ELSE SALDO2 END AS SALDO2,
                    NVL(VARIACION,0) AS VARIACION,
                    CASE WHEN LEVEL1 IS NULL THEN 1 ELSE  0 END AS ES_GRUPO1,ES_GRUPO1 AS ES_GRUPO2
                FROM (SELECT CASE WHEN SUBSTR(LEVEL1,0,1)=3 THEN 3.1 WHEN LEVEL1 IS NULL THEN NULL ELSE 3.2 END AS LEVEL1,
                            LEVEL1 AS LEVEL2,
                            SUM(NVL(SALDO1,0))AS SALDO1,
                            SUM(NVL(SALDO2,0))AS SALDO2,
                            CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                            GROUPING(LEVEL1) AS ES_GRUPO1
                    FROM (SELECT A.ID_CUENTAAASI AS LEVEL1,
                            -SUM(B.SALDO1)AS SALDO1,
                            -SUM(B.SALDO2)AS SALDO2,
                            CASE WHEN SUM(B.SALDO2)=0 THEN 0 ELSE ROUND(((SUM(B.SALDO1)-SUM(B.SALDO2))/SUM(B.SALDO2))*100,2) END AS VARIACION
                        FROM CONTA_CTA_DENOMINACIONAL A
                        LEFT JOIN (SELECT ID_CUENTAAASI,
                                        SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                    FROM VW_CONTA_DIARIO_ALL
                                    WHERE ID_EMPRESA $id_empresa
                                        AND ID_ENTIDAD $id_entidad
                                        AND ID_DEPTO $id_depto
                                        AND ID_ANHO $id_anhofirst
                                        AND ID_MES $id_mesfirst
                                        AND ID_FONDO $id_fondo
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,3) IN (321,421)
                                    GROUP BY ID_CUENTAAASI
                                    UNION ALL
                                    SELECT ID_CUENTAAASI,
                                        0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                    FROM VW_CONTA_DIARIO_ALL
                                    WHERE ID_EMPRESA $id_empresa
                                        AND ID_ENTIDAD $id_entidad
                                        AND ID_DEPTO $id_depto
                                        AND ID_ANHO $id_anhosecond
                                        AND ID_MES $id_messecond
                                        AND ID_FONDO $id_fondo
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,3) IN (321,421)
                                    GROUP BY ID_CUENTAAASI) B ON SUBSTR(B.ID_CUENTAAASI,0,3)=SUBSTR(A.ID_CUENTAAASI,0,3)
                        WHERE SUBSTR(A.ID_CUENTAAASI,4,7)=RPAD('0',4,'0')
                        AND A.ID_PARENT=RPAD(SUBSTR(A.ID_CUENTAAASI,0,2),7,'0')
                        AND SUBSTR(A.ID_CUENTAAASI,0,3) IN (321,421)
                        GROUP BY A.ID_CUENTAAASI)
                    GROUP BY ROLLUP (LEVEL1)
                    ORDER BY LEVEL2 ASC,ES_GRUPO1 DESC)
                UNION ALL
                SELECT 4 AS LEVEL1,4.1 AS LEVEL2,'6000000' AS LEVEL3,
                    (SELECT DISTINCT A.NOMBRE FROM CONTA_CTA_DENOMINACIONAL A WHERE A.ID_CUENTAAASI=6000000) AS CUENTA,
                    SUM(SALDO1)AS SALDO1,
                    SUM(SALDO2)AS SALDO2,
                    CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                    0 AS ES_GRUPO1,0 AS ES_GRUPO2
                FROM (SELECT A.ID_CUENTAAASI,SUM(NVL(B.SALDO1,0)) AS SALDO1,SUM(NVL(B.SALDO2,0)) AS SALDO2
                        FROM CONTA_CTA_DENOMINACIONAL A
                        LEFT JOIN (SELECT ID_CUENTAAASI,
                                    SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                                FROM VW_CONTA_DIARIO_ALL
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidad
                                    AND ID_DEPTO $id_depto
                                    AND ID_ANHO $id_anhofirst
                                    AND ID_MES $id_mesfirst
                                    AND ID_FONDO $id_fondo
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND SUBSTR(ID_CUENTAAASI,0,1) IN (6)
                                GROUP BY ID_CUENTAAASI
                                UNION ALL
                                SELECT ID_CUENTAAASI,
                                    0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                                FROM VW_CONTA_DIARIO_ALL
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidad
                                    AND ID_DEPTO $id_depto
                                    AND ID_ANHO $id_anhosecond
                                    AND ID_MES $id_messecond
                                    AND ID_FONDO $id_fondo
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND SUBSTR(ID_CUENTAAASI,0,1) IN (6)
                                GROUP BY ID_CUENTAAASI) B ON B.ID_CUENTAAASI=A.ID_CUENTAAASI
                        WHERE SUBSTR(A.ID_CUENTAAASI,0,1) IN (6)
                        GROUP BY A.ID_CUENTAAASI)
                GROUP BY SUBSTR(ID_CUENTAAASI,0,1)
                UNION ALL
                SELECT 5 AS LEVEL1,NULL AS LEVEL2,NULL AS LEVEL3,
                    'RESULTADO DEL EJERCICIO' AS CUENTA,
                    -SUM(SALDO1)AS SALDO1,
                    -SUM(SALDO2)AS SALDO2,
                    CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(((SUM(SALDO1)-SUM(SALDO2))/SUM(SALDO2))*100,2) END AS VARIACION,
                    1 AS ES_GRUPO1,1 AS ES_GRUPO2
                FROM (SELECT SUM(COS_VALOR) AS SALDO1,0 AS SALDO2
                    FROM VW_CONTA_DIARIO_ALL
                    WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anhofirst
                        AND ID_MES $id_mesfirst
                        AND ID_FONDO $id_fondo
                        AND ID_TIPOASIENTO NOT IN ('EA')
                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6)
                    UNION ALL
                    SELECT 0 AS SALDO1,SUM(COS_VALOR) AS SALDO2
                    FROM VW_CONTA_DIARIO_ALL
                    WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidad
                        AND ID_DEPTO $id_depto
                        AND ID_ANHO $id_anhosecond
                        AND ID_MES $id_messecond
                        AND ID_FONDO $id_fondo
                        AND ID_TIPOASIENTO NOT IN ('EA')
                        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6))
                ) U
                WHERE saldo1!=0 OR saldo2!=0";
        //echo($query);
        $results=DB::select($query);
        // var_dump($query);
        $cash_flow=null;
        $change_equity=null;
        $data['financial_situation']=$financial_situation;
        $data['results']=$results;
        $data['cash_flow']=$cash_flow;
        $data['change_equity']=$change_equity;
        return $data;
    }


    public static function getFinancialStatementsLegal($request) {
        $data=[];
        $where=" WHERE ";
        $condition=null;
        $conditionSecond=null;
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_fondo = $request->id_fondo;
        $id_anhofirst = $request->id_anhofirst;
        $id_mesfirst = $request->id_mesfirst;
        $id_anhosecond = $request->id_anhosecond;
        $id_messecond = $request->id_messecond;

        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_entidad===null or $id_entidad==='*'){
            $id_entidad=" IS NOT NULL";
        }else{
            $id_entidad=" = $id_entidad";
        }
        if($id_depto===null or $id_depto==='*' or $id_depto==='0'){
            $id_depto=" IS NOT NULL";
        }else{
            $id_depto=" = '$id_depto'";
        }
        if($id_anhofirst===null or $id_anhofirst==='*'){
            $id_anhofirst=" IS NOT NULL";
        }else{
            $id_anhofirst=" = $id_anhofirst";
        }
        if($id_mesfirst===null or $id_mesfirst==='*'){
            $id_mesfirst="<=12";
        }else{
            $id_mesfirst=" <= $id_mesfirst";
        }
        if($id_anhosecond===null or $id_anhosecond==='*'){
            $id_anhosecond=" IS NOT NULL";
        }else{
            $id_anhosecond=" = $id_anhosecond";
        }
        if($id_messecond===null or $id_messecond==='*'){
            $id_messecond="<=12";
        }else{
            $id_messecond=" <= $id_messecond";
        }
        $find_all_fondo = strpos($id_fondo,'*');
        if($id_fondo===null or $id_fondo==='*' or $find_all_fondo===true){
            $id_fondo=" IS NOT NULL";
        }else{
            $replace1=str_replace('*,','',$id_fondo);
            $replace2=str_replace(',*','',$replace1);
            $id_fondo=" IN ($replace2)";
        }






        $query = "SELECT * FROM (
            SELECT LEVEL1, LEVEL2, LEVEL3,LEVEL4,
                    CASE WHEN ES_GRUPO2=1 AND ES_GRUPO3=1 AND ES_GRUPO4=1 THEN
                     (CASE WHEN LEVEL1 IN (1) THEN 'ACTIVO' WHEN LEVEL1 IN (2) THEN 'PASIVO Y PATRIMONIO' END)
                    WHEN ES_GRUPO2=0 AND ES_GRUPO3=0 AND ES_GRUPO4=1 THEN
                        CASE WHEN LEVEL3 IN (1) THEN 'ACTIVO CORRIENTE'
                            WHEN LEVEL3 IN (3) THEN 'ACTIVO NO CORRIENTE'
                            WHEN LEVEL2 IN (4) AND LEVEL3 IN (4) THEN 'PASIVO CORRIENTE'
                            WHEN LEVEL2 IN (5) AND LEVEL3 IN (4) THEN 'PASIVO NO CORRIENTE'
                            ELSE (SELECT NOMBRE FROM CONTA_CTA_EMPRESARIAL WHERE ID_CUENTAEMPRESARIAL=LEVEL3)
                        END
                    ELSE
                        CASE WHEN LEVEL4 = '1011' THEN 'EFECTIVO Y EQUIVALENTE DE EFECTIVO'
                            WHEN LEVEL4 = '130304' THEN 'CUENTAS POR COBRAR A VINCULADAS'
                            WHEN LEVEL4 = '141617' THEN 'OTRAS CUENTAS POR COBRAR'
                            WHEN LEVEL4 = '20' THEN 'EXISTENCIAS'
                            WHEN LEVEL4 = '121416' THEN 'CUENTAS POR COBRAR A LARGO PLAZO'
                            WHEN LEVEL4 = '1317' THEN 'CUENTAS POR COBRAR A VINCULADAS A LARGO PLAZO'
                            WHEN LEVEL4 = '3031' THEN 'INVERSIONES PERMANENTES'
                            WHEN LEVEL4 = '3339' THEN 'INMUEBLES, MAQUINARIA Y EQUIPO (NETO DE DEPRECIACIÓN  ACUMULADA)'
                            WHEN LEVEL4 = '-10' THEN 'SOBREGIROS Y PAGARÉS BANCARIOS'
                            WHEN LEVEL4 = '40' THEN 'IMPUESTOS POR PAGAR'
                            WHEN LEVEL4 = '4144454647' THEN 'OTRAS CUENTAS POR PAGAR'
                            WHEN LEVEL4 = '42444546' THEN 'DEUDAS A LARGO PLAZO'
                            WHEN LEVEL4 = '4347' THEN 'CUENTAS POR PAGAR A VINCULADAS'
                            ELSE (SELECT NOMBRE FROM CONTA_CTA_EMPRESARIAL WHERE ID_CUENTAEMPRESARIAL=LEVEL4)
                        END
                    END AS NAME,
                    CASE WHEN LEVEL1=2 THEN
                            -SALDO
                        WHEN LEVEL1=1 THEN SALDO END AS SALDO,
                    ES_GRUPO1,ES_GRUPO2,ES_GRUPO3,ES_GRUPO4
                    FROM (SELECT LEVEL1, LEVEL2, LEVEL3,LEVEL4,
                        SUM(NVL(SALDO,0))AS SALDO,
                        GROUPING(LEVEL1) AS ES_GRUPO1,
                        GROUPING(LEVEL2) AS ES_GRUPO2,
                        GROUPING(LEVEL3) AS ES_GRUPO3,
                        GROUPING(LEVEL4) AS ES_GRUPO4
                        FROM (
                SELECT li.LEVEL1,li.LEVEL2,li.LEVEL3, li.LEVEL4, sum(li.saldo) AS saldo
                FROM(
    	      	SELECT CASE WHEN CCE.ID_PARENT IN (1,2,3) THEN 1
                WHEN CCE.ID_PARENT IN (4,5) THEN 2 END AS LEVEL1,
                CASE WHEN cce.ID_PARENT IN (1,2) THEN '1'
                WHEN cce.ID_PARENT IN (4) AND cce.id_cuentaempresarial IN (48,49) THEN '5'
                WHEN cce.ID_PARENT IN (5) AND cce.id_cuentaempresarial  between 50 AND 59 THEN '6'
                ELSE cce.ID_PARENT END AS LEVEL2,
                CASE WHEN cce.ID_PARENT IN (1,2) THEN '1' ELSE cce.ID_PARENT END AS LEVEL3,
                CASE WHEN cce.id_cuentaempresarial IN (10,11) THEN '1011'
                WHEN cce.id_cuentaempresarial IN (13,03,04) THEN '130304'
                WHEN cce.id_cuentaempresarial IN (14,16,17) THEN '141617'
                WHEN cce.id_cuentaempresarial IN (30,31) THEN '3031'
                WHEN cce.id_cuentaempresarial IN (33,39) THEN '3339'
                WHEN cce.id_cuentaempresarial IN (41,44,45,46,47) THEN '4144454647'
                ELSE cce.id_cuentaempresarial
                END AS LEVEL4,
                    sum(vcd.cos_valor) AS SALDO
                    FROM (SELECT * FROM CONTA_CTA_EMPRESARIAL
                        WHERE LENGTH(ID_CUENTAEMPRESARIAL)=2
                        AND ID_PARENT IN (1,2,3,4,5)
                        AND NOT SUBSTR(ID_CUENTAEMPRESARIAL,0,2) BETWEEN 21 AND 29
                        AND NOT SUBSTR(ID_CUENTAEMPRESARIAL,0,2) IN (32,35,36,53,54,55,56)
                        ) cce
                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa
                        AND ID_CUENTAAASI NOT IN (1211001,1211002,1216001,1216005,121610,121611,1216045,1219001,1219730,1219707,1219705,2212001,2217501,2217502,2219001,2219005,2217001,2217002,221711,2217020)
                        ) cec
                        ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                        LEFT JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                        AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA')
                        AND ID_CUENTAAASI NOT IN (1211001,1211002,1216001,1216005,121610,121611,1216045,1219001,1219730,1219707,1219705,2212001,2217501,2217502,2219001,2219005,2217001,2217002,221711,2217020)
                        )vcd
                        ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                        AND vcd.id_tipoplan = cec.id_tipoplan
                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                        union ALL

                        SELECT
                        1 AS LEVEL1,
                        '1' AS LEVEL2,
                        '1' AS LEVEL3,
                        '130304' AS LEVEL4,
                        nvl (sum(vcd.cos_valor),0) AS SALDO
                        FROM (SELECT * FROM CONTA_CTA_EMPRESARIAL
                        WHERE LENGTH(ID_CUENTAEMPRESARIAL)=2
                        AND SUBSTR(ID_CUENTAEMPRESARIAL,0,2) IN (13,03,04)
                        ) cce
                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa) cec
                        ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                        LEFT JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                        AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                        ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                        AND vcd.id_tipoplan = cec.id_tipoplan
                        WHERE cce.id_cuentaempresarial IN (13,03,04)
                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                        union all

                        SELECT
                        2 AS LEVEL1,
                        '4' AS LEVEL2,
                        '4' AS LEVEL3,
                        '-10' AS LEVEL4,
                        CASE WHEN sum(vcd.cos_valor) > 0
                        THEN 0 ELSE
                            sum(vcd.cos_valor) END
                        AS SALDO
                        FROM (SELECT * FROM CONTA_CTA_EMPRESARIAL
                        WHERE LENGTH(ID_CUENTAEMPRESARIAL)=2
                        AND ID_PARENT IN (1)
                        AND SUBSTR(ID_CUENTAEMPRESARIAL,0,2) IN (10)
                        ) cce
                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa) cec
                        ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                        LEFT JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                        AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                        ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                        AND vcd.id_tipoplan = cec.id_tipoplan
                        WHERE cce.id_cuentaempresarial = 10
                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                        union all

                        SELECT
                        CASE WHEN CCE.ID_PARENT IN (1,2,3) THEN 1
                        WHEN CCE.ID_PARENT IN (4,5) THEN 2 END AS LEVEL1,
                        '3' AS LEVEL2,
                        '3' AS LEVEL3,
                        CASE WHEN cce.id_cuentaempresarial IN (12,14,16) THEN '121416'
                        WHEN cce.id_cuentaempresarial IN (13,17) THEN '1317'
                        END AS LEVEL4,
                        sum(vcd.cos_valor) AS SALDO
                        FROM (SELECT * FROM CONTA_CTA_EMPRESARIAL
                            WHERE LENGTH(ID_CUENTAEMPRESARIAL)=2
                            AND ID_PARENT IN (1)
                            AND SUBSTR(ID_CUENTAEMPRESARIAL,0,2) IN (12,13,14,16,17)
                            ) cce
                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa
                            AND ID_CUENTAAASI IN (1211001,1211002,1216001,1216005,121610,121611,1216045,1219001,1219730,1219707,1219705)) cec
                            ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                        LEFT JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                            AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                            ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                            AND vcd.id_tipoplan = cec.id_tipoplan
                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                        union all

                        SELECT
                        CASE WHEN CCE.ID_PARENT IN (1,2,3) THEN 1
                        WHEN CCE.ID_PARENT IN (4,5) THEN 2 END AS LEVEL1,
                        '5' AS LEVEL2,
                        '4' AS LEVEL3,
                        CASE WHEN cce.id_cuentaempresarial IN (42,44,45,46) THEN '42444546'
                        WHEN cce.id_cuentaempresarial IN (43,47) THEN '4347'
                        END AS LEVEL4,
                        sum(vcd.cos_valor) AS SALDO
                        FROM (SELECT * FROM CONTA_CTA_EMPRESARIAL
                        WHERE LENGTH(ID_CUENTAEMPRESARIAL)=2
                        AND ID_PARENT IN (4)
                        AND SUBSTR(ID_CUENTAEMPRESARIAL,0,2) IN (42,43,44,45,46,47)
                        ) cce
                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa
                            AND ID_CUENTAAASI IN (2212001,2217501,2217502,2219001,2219005,2217001,2217002,221711,2217020)) cec
                            ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                        LEFT JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                            AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                            ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                            AND vcd.id_tipoplan = cec.id_tipoplan
                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                        UNION ALL

                        SELECT y.LEVEL1,y.LEVEL2,y.LEVEL3,y.LEVEL4, SUM(y.saldo) AS SALDO FROM (
                            SELECT 2 AS LEVEL1, '6' AS LEVEL2, '5' AS LEVEL3,  CASE WHEN x.LEVEL3 IN (77,73,74,75,70,62,67,69,6) THEN '59' END  AS LEVEL4, x.saldo
                                FROM ( SELECT cce.id_cuentaempresarial AS LEVEL3, NVL(sum(vcd.cos_valor),0) AS SALDO
                                        FROM CONTA_CTA_EMPRESARIAL cce
                                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa) cec
                                                    ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                                        left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                                                    AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA')
                                                    )vcd ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                                                    AND vcd.id_tipoplan = cec.id_tipoplan
                                        WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2 AND cce.id_cuentaempresarial IN (70,75,77,73,74)
                                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                                        UNION ALL

                                        SELECT cce.id_cuentaempresarial AS LEVEL3, NVL(sum(vcd.cos_valor),0) AS SALDO
                                        FROM CONTA_CTA_EMPRESARIAL cce
                                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa) cec
                                                ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                                        left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                                                    AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA')
                                                )vcd ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                                                AND vcd.id_tipoplan = cec.id_tipoplan
                                        WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2 AND cce.id_cuentaempresarial IN (62,67,69)
                                        GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                                        UNION ALL

                                        SELECT cce.ID_PARENT AS LEVEL3, NVL(sum(vcd.cos_valor),0) AS SALDO
                                        FROM CONTA_CTA_EMPRESARIAL cce
                                        LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO $id_anhofirst AND ID_EMPRESA $id_empresa) cec
                                        ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                                        left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL WHERE ID_ANHO $id_anhofirst AND ID_MES $id_mesfirst
                                                    AND ID_EMPRESA $id_empresa AND ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                                                    ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                                                    AND vcd.id_tipoplan = cec.id_tipoplan
                                        WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2 AND cce.id_cuentaempresarial NOT IN (62,67,69)
                                        AND cce.ID_PARENT IN (6) GROUP BY cce.ID_PARENT
				      	   )x)y GROUP BY y.LEVEL1,y.LEVEL2,y.LEVEL3,y.LEVEL4

                      )li
                      GROUP BY li.LEVEL1,li.LEVEL2,li.LEVEL3,li.LEVEL4
                    )

                    GROUP BY ROLLUP (LEVEL1,LEVEL2,LEVEL3,LEVEL4)
            ORDER BY LEVEL1 ASC,ES_GRUPO2 DESC,LEVEL2 ASC,ES_GRUPO3 DESC, LEVEL3 ASC, ES_GRUPO4 DESC,LEVEL4 ASC) z
        )WHERE NAME IS NOT NULL";
        echo($query);
        $financial_situation = DB::select($query);

        $query="SELECT LEVEL1,LEVEL2, LEVEL3,
                CASE WHEN ES_GRUPO3 = 0 THEN
                    CASE WHEN LEVEL3 = 70 THEN
                        'Ventas Netas (ingresos operacionales)'
                    WHEN LEVEL3 = 75 THEN
                    'Otros Ingresos Operacionales'
                    WHEN LEVEL3 = 69 THEN
                    'Costo de ventas'
                    WHEN LEVEL3 = 6 THEN
                    'Gastos de Venta'
                    WHEN LEVEL3 = 62 THEN
                    'Gastos de Administración'
                    WHEN LEVEL3 = 77 THEN
                    'Ingresos Financieros'
                    WHEN LEVEL3 = 67 THEN
                    'Gastos Financieros'
                    WHEN LEVEL3 = 73 THEN
                    'Otros Ingresos'
                    WHEN LEVEL3 = 74 THEN
                    'Otros Gastos'
                    END

                WHEN ES_GRUPO1=0 AND ES_GRUPO2=0 AND ES_GRUPO3=1 THEN
                    CASE WHEN LEVEL2 = 1 THEN
                    'Total de Ingresos Brutos'
                    WHEN LEVEL2 = 2 THEN
                    'Utilidad Bruta'
                    WHEN LEVEL2 = 3 THEN
                    'Utilidad Operativa'
                    WHEN LEVEL2 = 4 THEN
                    'Otros Ingresos (gastos)'
                    END
                WHEN ES_GRUPO1=0 AND ES_GRUPO2=1 AND ES_GRUPO3=1  THEN
                    CASE WHEN LEVEL1 = 1 THEN
                    'Utilidad (Pérdida) Neta del Ejercicio'
                    END
                END AS NOMBRE,
                    SALDO,
                ES_GRUPO1,
                ES_GRUPO2,
                ES_GRUPO3
                FROM (

                SELECT LEVEL1,LEVEL2, LEVEL3,
                SUM(NVL(SALDO,0))AS SALDO,
                GROUPING(LEVEL1) AS ES_GRUPO1,
                GROUPING(LEVEL2) AS ES_GRUPO2,
                GROUPING(LEVEL3) AS ES_GRUPO3

                FROM (
                SELECT
                CASE WHEN li.LEVEL2 IN (1,2,3,4) THEN 1
                ELSE 1 END AS LEVEL1,
                li.* FROM
                (
                SELECT CASE WHEN LEVEL3 IN (70,75) THEN 1
                WHEN LEVEL3 IN (69) THEN 2 WHEN LEVEL3 IN (6,62) THEN 3
                ELSE 4 END AS LEVEL2,LEVEL3,SALDO
                FROM (
                SELECT cce.id_cuentaempresarial AS LEVEL3, -1*(NVL(sum(vcd.cos_valor),0)) AS SALDO
                FROM  CONTA_CTA_EMPRESARIAL cce
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO  $id_anhofirst AND ID_EMPRESA  $id_empresa) cec
                ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO  $id_anhofirst AND ID_MES $id_mesfirst
                AND ID_EMPRESA  $id_empresa AND ID_ENTIDAD  $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                AND vcd.id_tipoplan = cec.id_tipoplan
                WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2
                AND cce.id_cuentaempresarial IN (70,75,77,73,74)
                GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                UNION ALL

                SELECT cce.id_cuentaempresarial AS LEVEL3, -1*(NVL(sum(vcd.cos_valor),0)) AS SALDO
                FROM  CONTA_CTA_EMPRESARIAL cce
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO  $id_anhofirst AND ID_EMPRESA  $id_empresa) cec
                ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO  $id_anhofirst AND ID_MES  $id_mesfirst
                AND ID_EMPRESA  $id_empresa AND ID_ENTIDAD  $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                AND vcd.id_tipoplan = cec.id_tipoplan
                WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2
                AND cce.id_cuentaempresarial IN (62,67,69)
                GROUP BY cce.ID_PARENT ,cce.id_cuentaempresarial

                UNION ALL

                SELECT cce.ID_PARENT AS LEVEL3, -1*(NVL(sum(vcd.cos_valor),0)) AS SALDO
                FROM  CONTA_CTA_EMPRESARIAL cce
                LEFT JOIN (SELECT * FROM CONTA_EMPRESA_CTA WHERE ID_ANHO  $id_anhofirst AND ID_EMPRESA  $id_empresa) cec
                ON SUBSTR(cce.ID_CUENTAEMPRESARIAL,0,2)=SUBSTR(cec.ID_CUENTAEMPRESARIAL,0,2)
                left JOIN (SELECT * FROM VW_CONTA_DIARIO_ALL  WHERE ID_ANHO  $id_anhofirst AND ID_MES  $id_mesfirst
                AND ID_EMPRESA  $id_empresa AND ID_ENTIDAD  $id_entidad AND ID_DEPTO $id_depto AND ID_FONDO $id_fondo AND ID_TIPOASIENTO NOT IN ('EA'))vcd
                ON vcd.ID_CUENTAAASI=cec.ID_CUENTAAASI AND vcd.id_restriccion = cec.id_restriccion
                AND vcd.id_tipoplan = cec.id_tipoplan
                WHERE LENGTH(cce.ID_CUENTAEMPRESARIAL)=2
                AND cce.id_cuentaempresarial NOT IN (62,67,69)
                AND cce.ID_PARENT IN (6)
                GROUP BY cce.ID_PARENT))li
                )
                GROUP BY ROLLUP (LEVEL1,LEVEL2,LEVEL3)
                ORDER BY LEVEL1 ASC,ES_GRUPO2 ASC,LEVEL2 ASC,ES_GRUPO3 ASC, LEVEL3 ASC)";
        // echo($query);
        $results=DB::select($query);
        // $results=[];
        $cash_flow=null;
        $change_equity=null;
        $data['financial_situation']=$financial_situation;
        $data['results']=$results;
        $data['cash_flow']=$cash_flow;
        $data['change_equity']=$change_equity;
        return $data;
    }
    public static function getBudgetBalance($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_depto = $request->deptos;
        // $id_depto = $request->id_depto;
        $fondo = $request->fondo;
        $addDepto = '';
        $addFondo = '';
        $addFondo_ = '';
        $depto_pa = 'Todos los departamentos';
        $r_depto = '';
        $addMes = "";
        $addMes_ = "";
        $addMes_p = "";
        $addMesP = "";
        $addSql = "";

        if($id_depto !== null and $id_depto !== '*'){
            $addDepto =  " AND  X.ID_DEPTO IN (" . $id_depto.")";

            $r_depto = " X.ID_DEPTO AS ID_DEPTO_PA, ELISEO.FC_NAMESDEPTO(".$id_entidad.", X.ID_DEPTO) AS DEPTO_PA, ";
        } else {
            $r_depto = " '*' AS ID_DEPTO_PA, 'Todos los departamentos' AS DEPTO_PA, ";
        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*'){
            $addMes = " AND X.ID_MES = $id_mes ";
            $addMes_ = " AND ID_MES = $id_mes ";
            $addMes_p = " AND ID_MES < $id_mes ";
            $addMesP =  " AND  ID_MES <= " . $id_mes;
        }

        if($fondo > 0){
            $addFondo =  " AND ID_FONDO =  " . $fondo;
            $addFondo_ =  " AND X.ID_FONDO =  " . $fondo;

        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*' AND $id_mes > 1){
            $addSql = "
                TO_CHAR(NVL(((select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_DEPTO = X.ID_DEPTO
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            ".$addFondo."
            AND ID_TIPOASIENTO = 'EA')
            +
            (SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            ".$addFondo."
            AND ID_DEPTO = X.ID_DEPTO)
            +
            (SELECT NVL(-1*SUM(COS_VALOR), 0)
            FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . " ".$addMes_p." ".$addFondo."
            AND ID_CUENTAAASI like '4%'
            AND ID_DEPTO = X.ID_DEPTO)
            +
            (SELECT NVL(-1*SUM(COS_VALOR), 0) FROM
            VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_p."  ".$addFondo." AND ID_DEPTO = X.ID_DEPTO  AND ID_CUENTAAASI LIKE '3%' AND ID_TIPOASIENTO != 'EA')
            +
            (SELECT NVL(-1*SUM(COS_VALOR), 0) FROM
                        VW_CONTA_DIARIO_ALL
		                WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_p."  ".$addFondo." AND ID_DEPTO = X.ID_DEPTO  AND ID_CUENTAAASI LIKE '4%' AND ID_TIPOASIENTO != 'EA')
            ),0), 'fm999999999990.00') as SALDO_ANTERIOR,
            TO_CHAR(NVL(((select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_DEPTO = X.ID_DEPTO
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_TIPOASIENTO = 'EA'
            ".$addFondo."
            )
            +
            (SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            ".$addFondo."
            AND ID_DEPTO = X.ID_DEPTO)),0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,

            ";
        } else {
            $addSql = "TO_CHAR(NVL(((select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_DEPTO = X.ID_DEPTO
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_TIPOASIENTO = 'EA'
            ".$addFondo."
            )
            +
            (SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            ".$addFondo."
            AND ID_DEPTO = X.ID_DEPTO)), 0), 'fm999999999990.00') as SALDO_ANTERIOR,
            TO_CHAR(NVL(((select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_DEPTO = X.ID_DEPTO
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_TIPOASIENTO = 'EA'
            ".$addFondo."
            )
            +
            (SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            ".$addFondo."
            AND ID_DEPTO = X.ID_DEPTO)), 0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
             ";
        }

        $query = "SELECT
                        ITEM.*,
                        TO_CHAR(NVL((ITEM.SALDO_ANTERIOR + ITEM.PTO_GASTO + ITEM.EJE_INGRESOS + ITEM.EJE_GASTOS), 0), 'fm999999999990.00') AS SALDO,
                        (CASE WHEN (ITEM.EJE_GASTO_ACT != 0 AND ITEM.PTO_GASTO_ANUAL!= 0) THEN
                            TO_CHAR((((-1*ITEM.EJE_GASTO_ACT) * 100) / ITEM.PTO_GASTO_ANUAL), 'fm999999999990.00')
                        ELSE
                            TO_CHAR(0, 'fm999999999990.00')
                        END) AS PORCENTAJE
                FROM (
                SELECT
                    X.ID_DEPTO,
                    CED.NOMBRE AS DEPTO,
                    ".$r_depto."
                    REGEXP_SUBSTR(FC_OBT_RESP_ENTIDAD_DEPTO(" . $id_entidad . ", X.ID_DEPTO, ".$id_mes.", " . $id_anho . "),'[^-]+',1,1) AS ID_PERSONA,
                    REGEXP_SUBSTR(FC_OBT_RESP_ENTIDAD_DEPTO(" . $id_entidad . ", X.ID_DEPTO, ".$id_mes.", " . $id_anho . "),'[^-]+',1,2) AS RESPONSABLE,
                    ".$addSql."
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                    FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . " ".$addMes_." ".$addFondo."
                    AND ID_DEPTO not in ('0000','0001','0002','909211')
                    AND ID_CUENTAAASI like '3%'
                    AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_INGRESOS,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                    FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . " ".$addMes_." ".$addFondo."
                    AND ID_DEPTO not in ('0000','0001','0002','909211')
                    AND ID_CUENTAAASI like '4%'
                    AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . " ".$addFondo."
                    AND ID_CUENTAAASI like '4%'
                    AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO_ANUAL,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."  ".$addFondo."
                        AND ID_DEPTO = X.ID_DEPTO  AND ID_CUENTAAASI LIKE '3%' AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_INGRESOS,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."  ".$addFondo."
                        AND ID_DEPTO = X.ID_DEPTO  AND ID_CUENTAAASI LIKE '3%' AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_INGRESOS_ACT,
	                TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) FROM
                        VW_CONTA_DIARIO_ALL
		                WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."  ".$addFondo."
                        AND ID_DEPTO = X.ID_DEPTO  AND ID_CUENTAAASI LIKE '4%' AND ID_TIPOASIENTO != 'EA'), 0), 'fm999999999990.00') AS EJE_GASTOS,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMesP ." ".$addFondo."
                        AND ID_CUENTAAASI LIKE '4%'
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_GASTO_ACT
                FROM CONTA_PRESUPUESTO X
                INNER JOIN CONTA_ENTIDAD_DEPTO CED ON (CED.ID_ENTIDAD = X.ID_ENTIDAD AND CED.ID_DEPTO = X.ID_DEPTO)
                WHERE X.ID_ENTIDAD = " .$id_entidad ." ".$addDepto."  AND X.ID_ANHO = " .$id_anho. " ".$addMes."  ".$addFondo_." AND X.ID_DEPTO not in ('0000','0001','0002','909211') GROUP BY X.ID_DEPTO, CED.NOMBRE ORDER BY X.ID_DEPTO) ITEM";
        // echo($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getBudgetBalanceReport($request)
    {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 
        $id_depto = $request->id_depto;
        $id_depto_gasto = $request->id_depto_gasto;
        // $id_ctacte = $request->id_ctacte;
        $responsibles = $request->responsibles;

        $querySaldo = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_ANHO,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '23%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS SALDO_INICIAL,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '523%' OR vcd.ID_CUENTAAASI LIKE '3%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS INGRESO,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '531%' OR vcd.ID_CUENTAAASI LIKE '532%' OR vcd.ID_CUENTAAASI LIKE '4%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS GASTO
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
            WHERE 1 = 1
                AND vcd.ID_ANHO = $id_anho
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
                AND vcd.ID_FONDO = '10'
                AND vcd.ID_ENTIDAD = $id_entidad
                AND vcd.ID_DEPTO = '$id_depto'
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_DEPTO
        ";

        $queryGasto = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_CTACTE,
                (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE) AS RESPONSABLE,
                vcd.ID_ANHO,
                SUM(vcd.COS_VALOR) AS GASTO
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
            LEFT JOIN MOISES.PERSONA_DOCUMENTO pd ON vcd.ID_CTACTE = pd.NUM_DOCUMENTO
            LEFT JOIN MOISES.PERSONA p ON pd.ID_PERSONA = p.ID_PERSONA
            WHERE 1 = 1
                AND vcd.ID_ANHO = $id_anho
                AND vcd.ID_FONDO = '10'
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
                AND vcd.ID_ENTIDAD = $id_entidad";
        
        if (!empty($id_depto_gasto)) {
            $queryGasto .= "AND vcd.ID_DEPTO = '$id_depto_gasto'";
        } else {
            $queryGasto .= "AND vcd.ID_DEPTO = '$id_depto'";
        }

        if ($responsibles !== '*') {
            $queryGasto .= " AND vcd.ID_CTACTE IN ($responsibles)";
        }

        $queryGasto .= " AND vcd.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094')
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_CTACTE, (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE), vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_DEPTO";

        $oQuerySaldo = DB::select($querySaldo);
        $oQueryGasto = DB::select($queryGasto);
        
        return [
            "saldo" => $oQuerySaldo,
            "gastos" => $oQueryGasto,
        ];
    }
    public static function getBudgetBalanceResponsibles($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;

        $query = "SELECT 
                oa.ID_TIPOAREA, oa.NOMBRE, osa.ID_DEPTO, p.ID_PERSONA, pd.NUM_DOCUMENTO, (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' ' || p.NOMBRE) AS RESPONSABLE
            FROM ORG_AREA oa
            INNER JOIN ORG_SEDE_AREA osa ON oa.ID_AREA = osa.ID_AREA 
            INNER JOIN ORG_AREA_RESPONSABLE oar ON osa.ID_SEDEAREA = oar.ID_SEDEAREA 
            INNER JOIN MOISES.PERSONA p ON oar.ID_PERSONA = p.ID_PERSONA
            INNER JOIN MOISES.PERSONA_DOCUMENTO pd ON p.ID_PERSONA = pd.ID_PERSONA 
            WHERE 1 = 1
                AND oa.ID_ENTIDAD = $id_entidad
                AND oa.ID_TIPOAREA IN (10, 11)
                AND pd.ES_ACTIVO  = '1'
                -- AND osa.ID_SEDE = 1 -- SEDE LIMA
                AND osa.ESTADO = '1'
                AND oar.ID_NIVEL = 1
                AND oar.ACTIVO = '1'
                -- AND osa.ID_DEPTO IN ('111121', '111131')
                AND osa.ID_DEPTO = '$id_depto'
            ORDER BY oa.ID_TIPOAREA 
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getBudgetBalanceReportDetail($request)
    {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 
        $id_depto = $request->id_depto;
        $id_depto_gasto = $request->id_depto_gasto;
        // $id_ctacte = $request->id_ctacte;
        $responsibles = $request->responsibles;

        $query = DB::table(DB::raw('(SELECT
            vcd.ID_ENTIDAD,
            vcd.ID_DEPTO,
            ced.NOMBRE AS DEPARTAMENTO,
            vcd.ID_CTACTE,
            vcd.ID_ANHO,
            vcd.ID_MES,
            cm.NOMBRE AS MES,
            vcd.LOTE,
            vcd.COMENTARIO,
            vcd.COS_VALOR,
            vcd.ID_CUENTAAASI,
            vcd.FEC_CONTABILIZADO
        FROM VW_CONTA_DIARIO vcd
        INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
        INNER JOIN CONTA_MES cm ON cm.ID_MES = vcd.ID_MES
        WHERE 1 = 1
            AND ID_FONDO = \'10\'
            AND ID_CUENTAAASI IN (\'4113030\', \'4113091\', \'4113092\', \'4113093\', \'4113094\')
        ) SALDO_DATA'))
        ->select(
            'ID_ENTIDAD',
            'ID_DEPTO',
            'DEPARTAMENTO',
            'ID_CTACTE',
            'ID_ANHO',
            'ID_MES',
            'MES',
            'LOTE',
            'COMENTARIO',
            DB::raw("TO_CHAR(SUM(COS_VALOR), 'FM9999999990.00') AS SALDO")
        )
            ->where('ID_ANHO', $id_anho)
            ->whereDate('FEC_CONTABILIZADO', '<=', $fecha)
            ->where('ID_ENTIDAD', $id_entidad);
            // ->where('ID_DEPTO', $id_depto_gasto);

        if (!empty($id_depto_gasto)) {
            $query->where('ID_DEPTO', $id_depto_gasto);
        } else {
            $query->where('ID_DEPTO', $id_depto);
        }

        if ($responsibles !== '*') {
            $responsiblesArray = explode(',', $responsibles);
            $query->whereIn('ID_CTACTE', $responsiblesArray);
        }

        $query->groupBy(
            'ID_ENTIDAD',
            'ID_DEPTO',
            'DEPARTAMENTO',
            'ID_CTACTE',
            'ID_ANHO',
            'ID_MES',
            'MES',
            'LOTE',
            'COMENTARIO'
        )
        ->orderBy('ID_ENTIDAD')
        ->orderBy('ID_DEPTO')
        ->orderBy('ID_ANHO')
        ->orderBy('ID_MES')
        ->orderBy('LOTE');

        return $query->paginate($request->per_page);
    }
    public static function getBudgetBalanceReportDetailTotal($request) {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 
        $id_depto = $request->id_depto;
        $id_depto_gasto = $request->id_depto_gasto;
        // $id_ctacte = $request->id_ctacte;
        $responsibles = $request->responsibles;

        $query = DB::table('VW_CONTA_DIARIO as vcd')
            ->select(DB::raw("TO_CHAR(SUM(TO_NUMBER(vcd.COS_VALOR, '9999999990.00')), 'FM9999999990.00') AS TOTAL_SALDO"))
            ->where('vcd.ID_FONDO', '10')
            ->whereIn('vcd.ID_CUENTAAASI', ['4113030', '4113091', '4113092', '4113093', '4113094'])
            ->where('vcd.ID_ENTIDAD', $id_entidad)
            ->where('vcd.ID_ANHO', $id_anho)
            // ->where('vcd.ID_DEPTO', $id_depto_gasto)
            ->whereDate('vcd.FEC_CONTABILIZADO', '<=', $fecha);

        if (!empty($id_depto_gasto)) {
            $query->where('ID_DEPTO', $id_depto_gasto);
        } else {
            $query->where('ID_DEPTO', $id_depto);
        }

        if ($responsibles !== '*') {
            $responsiblesArray = explode(',', $responsibles);
            $query->whereIn('vcd.ID_CTACTE', $responsiblesArray);
        }

        return $query->first();
    }
    public static function getBudgetBalanceReportGeneral($request) {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 

        $query = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_ANHO,
                CASE WHEN vcd.ID_DEPTO IN ('114151', '114181', '114071', '114101', '114811', '114221', '114201', '114031') THEN '1'
                    WHEN vcd.ID_DEPTO IN ('399111', '118231', '399311', '118232', '399511', '118233') THEN '2'
                    WHEN vcd.ID_DEPTO IN ('111131', '111121', '1111311', '1111211', '397132', '396115', '396114', '393212' , '396121', '396129', '396112', '396113', '396131', '397161', '396111', '114224') THEN '3'
                    WHEN vcd.ID_DEPTO IN ('113111', '114813', '113611', '113812', '113132', '114092', '114812', '114182' , '114202', '114102', '114062', '115112', '399312', '114152', '115891') THEN '4'
                    WHEN vcd.ID_DEPTO IN ('116531', '116522', '325822', '116532', '325811', '325812', '325821', '114023' , '114153', '397163', '115811', '115613') THEN '5'
                    WHEN vcd.ID_DEPTO IN ('393221', '393222', '393223', '909211', '114021', '114022', '188120', '396125' , '3961271', '3961272', '396134', '114231', '992991', '396119', '396128') THEN '6'
                    WHEN vcd.ID_DEPTO IN ('118221', '113171', '117211', '158111', '117212', '117213') THEN '7'
                    WHEN vcd.ID_DEPTO IN ('129811', '118293', '325311', '325111') THEN '99'
                    ELSE '' END AS ID_TIPO_SALDO,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '23%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS SALDO_INICIAL,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '523%' OR vcd.ID_CUENTAAASI LIKE '3%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS INGRESO,
                TO_CHAR(SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '531%' OR vcd.ID_CUENTAAASI LIKE '532%' OR vcd.ID_CUENTAAASI LIKE '4%' THEN vcd.COS_VALOR ELSE 0 END), 'FM9999999990.00') AS GASTO,
                TO_CHAR(NVL(
                    (SELECT SUM(COS_VALOR) FROM CONTA_PRESUPUESTO cp 
                    WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                    AND cp.ID_DEPTO = vcd.ID_DEPTO 
                    AND cp.ID_ANHO = vcd.ID_ANHO 
                    AND cp.ID_CUENTAAASI = '6100001'), 0), 
                    'FM9999999990.00') AS PPTO_ANUAL,
                TO_CHAR(NVL(
                    (NVL((SELECT SUM(COS_VALOR) FROM CONTA_PRESUPUESTO cp 
                        WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI = '6100001'), 0) 
                    - SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '531%' OR vcd.ID_CUENTAAASI LIKE '532%' OR vcd.ID_CUENTAAASI LIKE '4%' THEN vcd.COS_VALOR ELSE 0 END) 
                    - SUM(CASE WHEN vcd.ID_CUENTAAASI LIKE '23%' THEN vcd.COS_VALOR ELSE 0 END)), 0), 
                    'FM9999999990.00') AS SALDO_ANUAL
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD 	
                                            AND vcd.ID_DEPTO = ced.ID_DEPTO 
            WHERE 1 = 1 
                AND (  vcd.ID_DEPTO IN ('114151', '114181', '114071', '114101', '114811', '114221', '114201', '114031') -- DEPARTAMENTALES
                    OR vcd.ID_DEPTO IN ('399111', '118231', '399311', '118232', '399511', '118233') -- ADMINISTRADORES
                    OR vcd.ID_DEPTO IN ('111131', '111121', '1111311', '1111211', '397132', '396115', '396114', '393212' , '396121', '396129', '396112', '396113', '396131', '397161', '396111', '114224') -- PLANILLA
                    OR vcd.ID_DEPTO IN ('113111', '114813', '113611', '113812', '113132', '114092', '114812', '114182' , '114202', '114102', '114062', '115112', '399312', '114152', '115891') -- EVANGELISMO
                    OR vcd.ID_DEPTO IN ('116531', '116522', '325822', '116532', '325811', '325812', '325821', '114023' , '114153', '397163', '115811', '115613') -- EVENTOS
                    OR vcd.ID_DEPTO IN ('393221', '393222', '393223', '909211', '114021', '114022', '188120', '396125' , '3961271', '3961272', '396134', '114231', '992991', '396119', '396128') -- OFICINA_EDIFICIOS
                    OR vcd.ID_DEPTO IN ('118221', '113171', '117211', '158111', '117212', '117213') -- RESERVAS
                    OR vcd.ID_DEPTO IN ('129811', '118293', '325311', '325111') -- OTROS
                )
                AND vcd.ID_FONDO = '10'
                AND vcd.ID_ENTIDAD = $id_entidad
                AND vcd.ID_ANHO = $id_anho
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, ID_TIPO_SALDO, vcd.ID_DEPTO
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getBudgetBalanceReportExpensesAdmin($request) {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 

        $query = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_CTACTE,
                (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE) AS RESPONSABLE,
                vcd.ID_ANHO,
                TO_CHAR(NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0),
                    'FM9999999990.00'
                ) AS PPTO_ANUAL,
                TO_CHAR(SUM(vcd.COS_VALOR), 'FM9999999990.00') AS GASTO,
                TO_CHAR((NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0) 
                    + NVL(SUM(vcd.COS_VALOR), 0)), 
                    'FM9999999990.00'
                ) AS SALDO_ANUAL
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
            LEFT JOIN MOISES.PERSONA_DOCUMENTO pd ON vcd.ID_CTACTE = pd.NUM_DOCUMENTO 
            LEFT JOIN MOISES.PERSONA p ON pd.ID_PERSONA = p.ID_PERSONA 
            WHERE 1 = 1
                AND vcd.ID_ENTIDAD = $id_entidad
                AND vcd.ID_ANHO = $id_anho
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
                AND vcd.ID_FONDO = '10'
                AND vcd.ID_DEPTO IN ('111131')
                AND vcd.ID_CTACTE IN (
                    SELECT 
                        pd.NUM_DOCUMENTO
                    FROM ORG_AREA oa
                    INNER JOIN ORG_SEDE_AREA osa ON oa.ID_AREA = osa.ID_AREA 
                    INNER JOIN ORG_AREA_RESPONSABLE oar ON osa.ID_SEDEAREA = oar.ID_SEDEAREA 
                    INNER JOIN MOISES.PERSONA p ON oar.ID_PERSONA = p.ID_PERSONA
                    INNER JOIN MOISES.PERSONA_DOCUMENTO pd ON p.ID_PERSONA = pd.ID_PERSONA 
                    WHERE 1 = 1
                        AND oa.ID_ENTIDAD = $id_entidad
                        AND oa.ID_TIPOAREA IN (10, 11)
                        AND pd.ES_ACTIVO  = '1'
                        AND osa.ESTADO = '1'
                        AND oar.ID_NIVEL = 1
                        AND oar.ACTIVO = '1'
                        AND osa.ID_DEPTO IN ('111131')
                )
                AND vcd.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094')
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_CTACTE, (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE), vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, vcd.ID_CTACTE 
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getBudgetBalanceReportExpensesDepto($request) {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 

        $query = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_CTACTE,
                (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE) AS RESPONSABLE,
                vcd.ID_ANHO,
                TO_CHAR(NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0),
                    'FM9999999990.00'
                ) AS PPTO_ANUAL,
                TO_CHAR(SUM(vcd.COS_VALOR), 'FM9999999990.00') AS GASTO,
                TO_CHAR((NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0) 
                    + NVL(SUM(vcd.COS_VALOR), 0)), 
                    'FM9999999990.00'
                ) AS SALDO_ANUAL
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
            LEFT JOIN MOISES.PERSONA_DOCUMENTO pd ON vcd.ID_CTACTE = pd.NUM_DOCUMENTO 
            LEFT JOIN MOISES.PERSONA p ON pd.ID_PERSONA = p.ID_PERSONA 
            WHERE 1 = 1
                AND vcd.ID_ENTIDAD = $id_entidad
                AND vcd.ID_ANHO = $id_anho
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
                AND vcd.ID_FONDO = '10'
                AND vcd.ID_DEPTO IN ('111121')
                AND vcd.ID_CTACTE IN (
                    SELECT 
                        pd.NUM_DOCUMENTO
                    FROM ORG_AREA oa
                    INNER JOIN ORG_SEDE_AREA osa ON oa.ID_AREA = osa.ID_AREA 
                    INNER JOIN ORG_AREA_RESPONSABLE oar ON osa.ID_SEDEAREA = oar.ID_SEDEAREA 
                    INNER JOIN MOISES.PERSONA p ON oar.ID_PERSONA = p.ID_PERSONA
                    INNER JOIN MOISES.PERSONA_DOCUMENTO pd ON p.ID_PERSONA = pd.ID_PERSONA 
                    WHERE 1 = 1
                        AND oa.ID_ENTIDAD = $id_entidad
                        AND oa.ID_TIPOAREA IN (10, 11)
                        AND pd.ES_ACTIVO  = '1'
                        AND osa.ESTADO = '1'
                        AND oar.ID_NIVEL = 1
                        AND oar.ACTIVO = '1'
                        AND osa.ID_DEPTO IN ('111121')
                )
                AND vcd.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094')
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_CTACTE, (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE), vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, vcd.ID_CTACTE 
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getBudgetBalanceReportExpensesSupport($request) {
        $id_entidad = $request->id_entidad;
        $fecha = new DateTime($request->fecha);
        $id_anho = $fecha->format('Y'); 

        $query = "SELECT 
                vcd.ID_ENTIDAD,
                vcd.ID_DEPTO,
                ced.NOMBRE AS DEPARTAMENTO,
                vcd.ID_CTACTE,
                (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE) AS RESPONSABLE,
                vcd.ID_ANHO,
                TO_CHAR(NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0),
                    'FM9999999990.00'
                ) AS PPTO_ANUAL,
                TO_CHAR(SUM(vcd.COS_VALOR), 'FM9999999990.00') AS GASTO,
                TO_CHAR((NVL((SELECT SUM(COS_VALOR) 
                    FROM CONTA_PRESUPUESTO cp WHERE cp.ID_ENTIDAD = vcd.ID_ENTIDAD 
                        AND cp.ID_DEPTO = vcd.ID_DEPTO 
                        AND cp.ID_ANHO = vcd.ID_ANHO 
                        AND cp.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094', '4111022') 
                        AND cp.ID_CTACTE = vcd.ID_CTACTE), 0) 
                    + NVL(SUM(vcd.COS_VALOR), 0)), 
                    'FM9999999990.00'
                ) AS SALDO_ANUAL
            FROM VW_CONTA_DIARIO vcd 
            INNER JOIN CONTA_ENTIDAD_DEPTO ced ON vcd.ID_ENTIDAD = ced.ID_ENTIDAD AND vcd.ID_DEPTO = ced.ID_DEPTO
            LEFT JOIN MOISES.PERSONA_DOCUMENTO pd ON vcd.ID_CTACTE = pd.NUM_DOCUMENTO 
            LEFT JOIN MOISES.PERSONA p ON pd.ID_PERSONA = p.ID_PERSONA 
            WHERE 1 = 1
                AND vcd.ID_ENTIDAD = $id_entidad
                AND vcd.ID_ANHO = $id_anho
                AND TO_CHAR(vcd.FEC_CONTABILIZADO ,'YYYY-MM-DD') <= '$request->fecha'
                AND vcd.ID_FONDO = '10'
                AND vcd.ID_CTACTE IN ('47137190', '45510592', '10173559')
                AND vcd.ID_CUENTAAASI IN ('4113030', '4113091', '4113092', '4113093', '4113094')
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, ced.NOMBRE, vcd.ID_CTACTE, (UPPER(p.PATERNO) || ' ' || p.MATERNO || ' '  || p.NOMBRE), vcd.ID_ANHO
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_DEPTO, vcd.ID_CTACTE 
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function editResponsibleBudgetBalance($request) {
        $res="OK";
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_person = $request->id_persona;
        $id_mes = $request->id_mes;
        $id_anho = $request->id_anho;
        $fec_inicio = $request->fec_inicio;
        $fec_termino = $request->fec_termino;

        $fecha = new DateTime($id_anho.'-'.$id_mes.'-10');
        $fecha->modify('first day of this month');

        $fecha_ = new DateTime($id_anho.'-'.$id_mes.'-10');
        $fecha_->modify('last day of this month');

        $fech_termino = date("d-m-Y",strtotime($fec_inicio."- 1 days"));

        $countRow = DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_RESP')
        ->where('ID_ENTIDAD','=', $id_entidad)
        ->where('ID_DEPTO','=', $id_depto)
        ->whereRaw("TO_CHAR(FEC_INICIO,'YYYYMM') <=  ($id_anho ||''|| LPAD($id_mes,2,0)) AND ((TO_CHAR(FEC_TERMINO,'YYYYMM') >= ($id_anho ||''|| LPAD($id_mes,2,0)) OR FEC_TERMINO IS  NULL)")->count();
        if($countRow > 0){
            DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_RESP')
            ->where('ID_ENTIDAD','=', $id_entidad)
            ->where('ID_DEPTO','=', $id_depto)
            ->whereRaw("TO_CHAR(FEC_INICIO,'YYYYMM') <=  ($id_anho ||''|| LPAD($id_mes,2,0)) AND (TO_CHAR(FEC_TERMINO,'YYYYMM')  >= ($id_anho ||''|| LPAD($id_mes,2,0)) OR FEC_TERMINO IS  NULL)")
            ->update(
                array(
                    'FEC_TERMINO' => $fech_termino,
                    )
            );
        } else {

            DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_RESP')->insert(
                array(
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_PERSONA'=> $id_person,
                    'FEC_INICIO' => $fecha_,
                    'FEC_TERMINO' => $fecha_,
                    )
            );
        }

        return $res;
    }

    public static function getResponsible($request) {

        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_depto = $request->id_depto;
        $id_persona = $request->id_persona;


        $query = DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_RESP')
                ->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_DEPTO',$id_depto)
                ->where('ID_PERSONA', $id_persona)
                ->whereRaw("TO_CHAR(FEC_INICIO,'YYYYMM') <=  ($id_anho ||''|| LPAD($id_mes,2,0)) AND (TO_CHAR(FEC_TERMINO,'YYYYMM') >= ($id_anho ||''|| LPAD($id_mes,2,0)) OR FEC_TERMINO IS  NULL)")
                ->select(
                    'ID_ENTIDAD',
                    'ID_DEPTO',
                    'ID_PERSONA',
                    'FEC_INICIO',
                    'FEC_TERMINO'
                    )->get();

        return $query;
    }

    public static function getDataPerson($request) {
        $id_persona = $request->id_persona;

        $query = "SELECT distinct pn.id_persona as ID_PERSONA,
        pn.NUM_DOCUMENTO as doc_number,
        pn.NOM_PERSONA as NOM_PERSONA,
        pn.fec_nacimiento as fecha_nac,
        trunc(months_between(sysdate,pn.fec_nacimiento)/12) as edad,
        pn.tipo_estado_civil as estado_civil,
        pn.tipo_pais as pais,
        pn.tipo_sexo as sexo,
        pn.telefono as telefono,
        pn.correo_inst as email,
        pn.direccion as direccion
        from  moises.vw_persona_natural_full pn
        where pn.id_persona = $id_persona";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listPersonalYearMonthSearch($entity,$year, $month, $search, $depto){

        $sqlSearch = '';
        $addMes = '';
        $addDepto = '';
        if ($search !==null and  $search !== '') {

          $sqlSearch = " and (UPPER(P.NOMBRE) like UPPER('%" . $search . "%')
                              OR UPPER(P.PATERNO) like UPPER('%" . $search . "%')
                              OR UPPER(P.MATERNO) like UPPER('%" . $search . "%')
                              OR PN.NUM_DOCUMENTO like '%" . $search . "%')";
        }
        if ($month!==null and $month !=='*') {
          $addMes =  " and  PL.ID_MES = " . $month;
        }
        if($depto !=='null' AND $depto !==null and $depto !=='*'){
          $addDepto =  " and  PL.ID_DEPTO IN (" . $depto .")";
        }
          $query = "SELECT DISTINCT PL.ID_PERSONA as ID_PERSONA,
                  pd.NUM_DOCUMENTO as DOC_NUMBER,
                  P.NOMBRE,
                  UPPER (NVL (P.PATERNO || ' ', '')|| NVL (P.MATERNO || ', ', ''))|| INITCAP(P.NOMBRE) AS NOM_PERSONA,
                  PL.ID_ENTIDAD as ENTITY,
                  PN.FEC_NACIMIENTO as  FECHA_NAC,
                  trunc(months_between(sysdate,PN.FEC_NACIMIENTO)/12) as EDAD,
                  (select nombre from moises.tipo_estado_civil where id_tipoestadocivil=pn.id_tipoestadocivil) ESTADO_CIVIL,
                  (select nombre from moises.tipo_pais where id_tipopais=pn.id_tipopais) PAIS,
                  (select nombre from moises.tipo_sexo where sexo=pn.sexo) SEXO,
                  pt.NUM_TELEFONO as TELEFONO,
                  pn.correo_inst as EMAIL,
                  pp.DIRECCION as DIRECCION,
                  'true' as EXPANDED
              FROM APS_PLANILLA PL
              INNER JOIN MOISES.PERSONA P ON (P.ID_PERSONA = PL.ID_PERSONA)
              INNER JOIN (select id_persona,
                      (select id_telefono from moises.persona_telefono where es_activo = 1 and id_tipotelefono = 5 and id_persona=pn.id_persona and rownum=1) id_telefono,
                      (select id_virtual from moises.persona_virtual where es_activo = 1 and id_tipovirtual = 1 and id_persona=pn.id_persona and rownum=1) id_virtual,
                      (select id_direccion from moises.persona_direccion where es_activo = 1 and id_tipodireccion = 4 and id_persona=pn.id_persona and rownum=1) id_direccion,
                      (select num_documento from moises.persona_documento where id_persona=pn.id_persona and rownum=1) num_documento,
                      id_tipoestadocivil,id_tipopais,sexo,fec_nacimiento, telefono,correo_inst,id_nacionalidad
                      from moises.persona_natural pn)PN ON PN.ID_PERSONA = PL.ID_PERSONA
              LEFT join moises.persona_documento pd on PN.id_persona=pd.id_persona and PN.num_documento=pd.num_documento
              LEFT join moises.persona_telefono pt on PN.id_persona=pt.id_persona and PN.id_telefono=pt.id_telefono
              LEFT join moises.persona_virtual pv on PN.id_persona=pv.id_persona and PN.id_virtual=pv.id_virtual
              LEFT join moises.persona_direccion pp on PN.id_persona=pp.id_persona and PN.id_direccion=pp.id_direccion
              WHERE PL.ID_ENTIDAD = $entity AND PL.ID_ANHO = $year
              $addMes
              $addDepto
              $sqlSearch
              order by P.NOMBRE ";
        // print($query);
          $oQuery = DB::select($query);
          return $oQuery;
    }

    public static function getTravelSummaryByFunctionary($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_persona = $request->id_persona;
        $id_grupo = $request->id_grupo;
        $depto = $request->deptos;

        $addMes = '';
        $addMesP = '';
        $addMesAc = '';
        $addPerson = '';
        $addMonth = '';
        $addSql = '';
        $addDepto = '';
        $addGrupo = '';
        if($id_mes!==null and $id_mes !=='*'){
            $addMes =  " AND  ID_MES = " . $id_mes;
            $addMesP =  " AND  ID_MES <= " . $id_mes;
            $addMesAc =  " AND  ID_MES < " . $id_mes;
            $addMonth =  " AND  CP.ID_MES = " . $id_mes;
        } else {
            $addMes =  " AND  ID_MES = CP.ID_MES ";
            $addMesP = " AND  ID_MES <= CP.ID_MES ";
        }

        if($depto !=='null' AND $depto!==null and $depto !=='*'){
            $addDepto =  " and  CP.ID_DEPTO IN (" . $depto .")";
        }

        if($id_grupo !==null and $id_grupo !== '*'){
            $addGrupo =  " AND  X.ID_GRUPO = " . $id_grupo;
        }

        if($id_mes!==null and $id_mes !=='*' AND  $id_mes > 1){
            $addSql = "
            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
                FROM VW_CONTA_DIARIO_ALL
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES = 12
                AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_TIPOASIENTO = 'EA'
                AND ID_DEPTO = CP.ID_DEPTO */
                0)
                +
                (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
                FROM vw_conta_Presupuesto
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES <= 12
                AND ID_CUENTAAASI IN (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO */ 0)
                +
                (SELECT NVL(-1*SUM(COS_VALOR), 0) from CONTA_PRESUPUESTO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = " . $id_anho . " ". $addMesAc ."
                AND ID_DEPTO not in ('0000','0001','0002','909211')
                AND ID_CUENTAAASI IN (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO)
                +
                (SELECT NVL(-1*SUM(COS_VALOR), 0)
                FROM  VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = " . $id_anho . " ". $addMesAc ."
                AND ID_DEPTO not in ('0000','0001','0002','909211')
                AND ID_CUENTAAASI IN (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO
                AND ID_TIPOASIENTO != 'EA')

            ), 0), 'fm999999999990.00') as SALDO_ANTERIOR,
            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_TIPOASIENTO = 'EA'
            AND ID_DEPTO = CP.ID_DEPTO */ 0)
            +
            (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_DEPTO = CP.ID_DEPTO */ 0)), 0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
                ";
        } else {
            $addSql = "
            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
                FROM VW_CONTA_DIARIO_ALL
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND  ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES = 12
                AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_TIPOASIENTO = 'EA'
                AND ID_DEPTO = CP.ID_DEPTO */ 0)
                +
                (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
                FROM vw_conta_Presupuesto
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = (" . $id_anho . " - 1)
                AND ID_MES <= 12
                AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                4113091, 4113092, 4113093, 4113094)
                AND ID_CTACTE = CP.ID_CTACTE
                AND ID_DEPTO = CP.ID_DEPTO */ 0)

                + (
                    SELECT NVL(-1*SUM(COS_VALOR), 0) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . "
                    AND ID_MES < CP.ID_MES
                    AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                    4113091, 4113092, 4113093, 4113094)
                    AND ID_CTACTE = CP.ID_CTACTE
                    AND ID_DEPTO = CP.ID_DEPTO
                )
                +
                (SELECT NVL(-1*SUM(COS_VALOR), 0)
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . "
                        AND ID_MES < CP.ID_MES
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                        4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA')
                ), 0), 'fm999999999990.00') AS SALDO_ANTERIOR,

            TO_CHAR(NVL(((/* select coalesce(SUM(COS_VALOR),0)
            FROM VW_CONTA_DIARIO_ALL
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND  ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES = 12
            AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_TIPOASIENTO = 'EA'
            AND ID_DEPTO = CP.ID_DEPTO */ 0)
            +
            (/* SELECT coalesce(SUM(COS_VALOR) *-1, 0)
            FROM vw_conta_Presupuesto
            WHERE ID_ENTIDAD = " . $id_entidad . "
            AND ID_ANHO = (" . $id_anho . " - 1)
            AND ID_MES <= 12
            AND ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
            4113091, 4113092, 4113093, 4113094)
            AND ID_CTACTE = CP.ID_CTACTE
            AND ID_DEPTO = CP.ID_DEPTO */ 0)), 0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
            ";
        }

        if($id_persona !== null) {
            $addPerson =  "AND  C.ID_PERSONA = " . $id_persona;
        }

        $query = "SELECT * FROM (SELECT
                        ITEM.*,
                        TO_CHAR((ITEM.PTO_GASTO + ITEM.EJE_GASTO), 'fm999999999990.00') AS SALDO,
                        TO_CHAR((ITEM.SALDO_ANTERIOR + ITEM.PTO_GASTO + ITEM.EJE_GASTO), 'fm999999999990.00') AS SALDO_ACUMULADO,
                        TO_CHAR((ITEM.SALDO_ANTERIOR_INI + ITEM.PTO_GASTO_ANUAL + ITEM.EJE_GASTO_ANUAL), 'fm999999999990.00') AS SALDO_PPTO_ANUAL,
                        (CASE WHEN ITEM.EJE_GASTO_ACT != 0 AND ITEM.PTO_GASTO_ANUAL != 0 THEN
                            TO_CHAR((((-1*ITEM.EJE_GASTO_ACT)*100)/ITEM.PTO_GASTO_ANUAL), 'fm999999999990.00')
                        ELSE
                            TO_CHAR(0,'fm999999999990.00')
                        END) AS PORCENTAJE
                FROM (
                SELECT
                    CP.ID_MES,
                    to_char(TO_DATE((CONCAT(CONCAT('2020-', CP.ID_MES),'-01')),'YYYY-MM-DD'), 'Month','nls_date_language=spanish') as mes,
                    CP.ID_DEPTO ,
                    CED.NOM_DEPARTAMENTO AS DEPTO,
                    COALESCE(X.ID_GRUPO, '*') ID_GRUPO,
                    COALESCE((SELECT NOMBRE FROM CONTA_ENTIDAD_GRUPO WHERE ID_GRUPO = X.ID_GRUPO), 'SIN GRUPO') N_GRUPO,
                    (SELECT COLOR FROM CONTA_ENTIDAD_GRUPO WHERE ID_GRUPO = X.ID_GRUPO) COLOR,
                    C.PATERNO ||' '|| C.MATERNO ||', '|| C.NOMBRE AS FUNCIONARIO,
                    CP.ID_CTACTE,
                    ".$addSql."
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . " ". $addMes ."
                    AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                    4113091, 4113092, 4113093, 4113094)
                    AND ID_CTACTE = CP.ID_CTACTE
                    AND ID_DEPTO = CP.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . "
                    AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                    4113091, 4113092, 4113093, 4113094)
                    AND ID_CTACTE = CP.ID_CTACTE
                    AND ID_DEPTO = CP.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO_ANUAL,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMes ."
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                        4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_GASTO,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMesP ."
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                        4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_GASTO_ANUAL,
                    TO_CHAR(NVL((SELECT (-1*SUM(COS_VALOR))
                        FROM  VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ". $addMesP ."
                        AND ID_CUENTAAASI in (4111022,4111023,4111070,4111071,4111073,4113023,4121029 ,4113030,
                        4113091, 4113092, 4113093, 4113094)
                        AND ID_CTACTE = CP.ID_CTACTE
                        AND ID_DEPTO = CP.ID_DEPTO
                        AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_GASTO_ACT
                FROM vw_conta_Presupuesto CP
                -- VW_CONTA_DIARIO
                INNER JOIN MOISES.PERSONA_DOCUMENTO B ON CP.ID_CTACTE=B.NUM_DOCUMENTO
                INNER JOIN MOISES.PERSONA C ON B.ID_PERSONA=C.ID_PERSONA
                INNER JOIN VW_CONTA_ENTIDAD_DEPTO CED ON (CED.ID_ENTIDAD = CP.ID_ENTIDAD AND CED.ID_DEPTO = CP.ID_DEPTO)
                LEFT JOIN CONTA_ENTIDAD_DEPTO_GRUPO_C X ON (X.ID_ENTIDAD = CP.ID_ENTIDAD AND X.ID_DEPTO = CP.ID_DEPTO)
                WHERE  CP.ID_ENTIDAD = " . $id_entidad . "
                AND CP.ID_DEPTO NOT IN ('0000', '0001', '0002', '909211')
                AND CP.ID_ANHO = " . $id_anho . " ". $addMonth ." ".$addPerson." ".$addDepto." ".$addGrupo."
                GROUP BY CP.ID_MES, CP.ID_DEPTO, CED.NOM_DEPARTAMENTO, X.ID_GRUPO,  CP.ID_CTACTE, C.PATERNO, C.MATERNO, C.NOMBRE
                ORDER BY CP.ID_MES, CED.NOM_DEPARTAMENTO, C.PATERNO
                )ITEM ORDER BY DEPTO, ID_MES) WHERE (PTO_GASTO > 0 OR EJE_GASTO > 0 ) ";
        // print($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getFinancialAnalysis($request) {
        $id_grupo = $request->id_grupo;
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_entidades = $request->id_entidades;
        $id_mes1 = $request->id_mes1;
        $id_anho1 = $request->id_anho1;
        $id_mes2 = $request->id_mes2;
        $id_anho2 = $request->id_anho2;
        $id_mes1_old = $request->id_mes1;
        $id_anho1_old = $request->id_anho1;
        $id_mes2_old = $request->id_mes2;
        $id_anho2_old = $request->id_anho2;
        $id_conector = $request->id_conector;
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_mes1===null or $id_mes1==='*'){
            $id_mes1="1";
        }
        if($id_anho1===null or $id_anho1==='*'){
            $id_anho1=date("Y");
        }
        if($id_mes2===null or $id_mes2==='*'){
            $id_mes2="12";
        }
        if($id_anho2===null or $id_anho2==='*'){
            $id_anho2=date("Y");
        }
        $id_tipoentidad=" IN (3,4)";

        if($id_grupo!==null and $id_grupo!=='*'){

            if($id_grupo==='0'){
                // print($id_grupo);
                $id_tipoentidad=" IN (3,4) ";
                if ($id_empresa == " IS NOT NULL") {
                    // echo 'ingrese aqui';
                    $string_id_empresa = self::getIdEmpresa($id_tipoentidad);

                    $id_empresa = "IN ($string_id_empresa)";
                }
            }else{
                $id_tipoentidad=" IN (5,7,9,12,13) ";
                if ($id_empresa == " IS NOT NULL") {
                    $string_id_empresa = self::getIdEmpresa($id_tipoentidad);
                    $id_empresa = "IN ($string_id_empresa)";
                }
            }
        }

        $query_conector_y1=" AND (TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN TO_NUMBER('$id_anho1'||'01') AND TO_NUMBER('$id_anho1'||LPAD($id_mes1,2,0))
                            OR TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN TO_NUMBER('$id_anho2'||'01') AND TO_NUMBER('$id_anho2'||LPAD($id_mes2,2,0))) ";
        $query_conector_y2=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) IN (TO_NUMBER('$id_anho1'||LPAD($id_mes1,2,0)),TO_NUMBER('$id_anho2'||LPAD($id_mes2,2,0))) ";
        $query_conector_y3="";
        $query_conector_hasta1=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes1')=1 THEN TO_NUMBER('$id_anho1')-1 ELSE TO_NUMBER('$id_anho1') END || '01')
                                AND TO_NUMBER($id_anho2||LPAD($id_mes2,2,0)) ";
        $query_conector_hasta2=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes1')=1 THEN TO_NUMBER('$id_anho1')-1 ELSE TO_NUMBER('$id_anho1') END || LPAD(CASE WHEN TO_NUMBER('$id_mes1')=1 THEN 12 ELSE TO_NUMBER('$id_mes1')-1 END,2,0)) ";
        // $query_conector_hasta3 change where why and because is concatec with other where
        $query_conector_hasta3=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))>=TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes1')=1 THEN TO_NUMBER('$id_anho1')-1 ELSE TO_NUMBER('$id_anho1') END || LPAD(CASE WHEN TO_NUMBER('$id_mes1')=1 THEN 12 ELSE TO_NUMBER('$id_mes1')-1 END,2,0)) ";

        $query_conector1=$query_conector_y1;
        $query_conector2=$query_conector_y2;
        $query_conector3=$query_conector_y3;
        if($id_conector==='1'){
            if($id_entidades===null or $id_entidades==='*'){
                $id_entidades=" IS NOT NULL";
            }else{
                $id_entidades=" IN ($id_entidades)";
            }
            $query_conector1=$query_conector_hasta1;
            $query_conector2=$query_conector_hasta2;
            $query_conector3=$query_conector_hasta3;
        }else{
            if($id_entidades===null or $id_entidades==='*'){
                $id_entidades=" IS NOT NULL";
            }else{
                $id_entidades=" IN ($id_entidades)";
            }
        }
        $query = "SELECT 1 AS ID_GRUPO,'DISPONIBLE Y APLICACIONES FINANCIERAS' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                     from CONTA_ENTIDAD  WHERE ID_EMPRESA $id_empresa
                     AND ID_ENTIDAD $id_entidades AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                            THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                            FROM (SELECT B0.ID_ENTIDAD,B0.ID_ANHO,B0.ID_MES,
                                    SUM(SUM(B0.COS_VALOR)) OVER (PARTITION BY B0.ID_ENTIDAD,B0.ID_ANHO ORDER BY MIN(B0.ID_MES)) AS COS_VALOR
                            FROM (SELECT * FROM VW_CONTA_DIARIO WHERE
                                    ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA') AND
                                    SUBSTR(ID_CUENTAAASI,0,3) IN (111,112)
                                    $query_conector1
                                    )B0
                            --WHERE

                            GROUP BY B0.ID_ENTIDAD,B0.ID_ANHO,B0.ID_MES)
                            where 1=1
                            $query_conector3 $query_conector2
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 2 AS ID_GRUPO,'CUENTAS POR COBRAR' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                     from CONTA_ENTIDAD  WHERE ID_EMPRESA $id_empresa
                     AND ID_ENTIDAD $id_entidades AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                            THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                            FROM (SELECT B0.ID_ENTIDAD,B0.ID_ANHO,B0.ID_MES,
                                        SUM(SUM(B0.COS_VALOR)) OVER (PARTITION BY B0.ID_ENTIDAD,B0.ID_ANHO ORDER BY MIN(B0.ID_MES)) AS COS_VALOR
                                FROM (SELECT B00.ID_ENTIDAD,B00.ID_ANHO,B00.ID_MES, (CASE WHEN CE.ID_TIPOENTIDAD = 5 AND B00.ID_CUENTAAASI = 1136080 THEN 0 ELSE B00.COS_VALOR END) AS COS_VALOR
                                        FROM (SELECT * FROM VW_CONTA_DIARIO WHERE ID_EMPRESA $id_empresa
                                                AND ID_ENTIDAD $id_entidades
                                                AND ID_TIPOASIENTO NOT IN ('EA')
                                                AND SUBSTR(ID_CUENTAAASI,0,3) IN (113)
                                                $query_conector1)B00
                                      INNER JOIN CONTA_ENTIDAD CE ON CE.ID_ENTIDAD = B00.ID_ENTIDAD
                                     )B0
                                GROUP BY B0.ID_ENTIDAD,B0.ID_ANHO,B0.ID_MES)
                                where 1 = 1 $query_conector3 $query_conector2
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 3 AS ID_GRUPO,'CUENTAS POR PAGAR' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                      FROM CONTA_ENTIDAD where ID_EMPRESA $id_empresa
                      AND ID_ENTIDAD $id_entidades
                      AND ID_TIPOENTIDAD $id_tipoentidad ) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                            THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                            FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS COS_VALOR
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND SUBSTR(ID_CUENTAAASI,0,3) IN (213)
                                    AND ID_CUENTAAASI != 2136080
                                    $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                                WHERE 1=1 $query_conector3 $query_conector2
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD ";
                if($id_conector==='1'){
                    $query=$query."
                        UNION ALL

                        SELECT 4 AS ID_GRUPO,'DIEZMO NETO' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                        FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                        FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidades
                        AND ID_TIPOENTIDAD $id_tipoentidad) A
                        INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                                THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                                FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,-SUM(COS_VALOR) AS COS_VALOR
                                    FROM VW_CONTA_DIARIO
                                    WHERE ID_EMPRESA $id_empresa
                                        AND ID_ENTIDAD $id_entidades
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,3) IN (311)
                                        $query_conector1
                                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                                WHERE 1=1 $query_conector3 $query_conector2
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD ";
                }else{
                    $query=$query."
                        UNION ALL
                        ---Para instituciones
                        SELECT 4 AS ID_GRUPO,'INGRESOS' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                        FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                        FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidades
                        AND ID_TIPOENTIDAD $id_tipoentidad) A
                        INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                                THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                                FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS COS_VALOR
                                    FROM VW_CONTA_DIARIO
                                    WHERE ID_EMPRESA $id_empresa
                                        AND ID_ENTIDAD $id_entidades
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND ID_CUENTAAASI LIKE '3%'
                                        $query_conector1
                                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                                    WHERE 1 = 1
                                    $query_conector3
                                    $query_conector2
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD ";
                }
                $query=$query."UNION ALL

                SELECT 5 AS ID_GRUPO,'OFRENDAS' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                        THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,-SUM(COS_VALOR) AS COS_VALOR
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,3) IN (312)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        WHERE 1=1
                            $query_conector3
                            $query_conector2
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                        ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD



                UNION ALL

                SELECT 7 AS ID_GRUPO,'RESULTADO DEL EJERCICIO' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                        THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,-SUM(COS_VALOR) AS COS_VALOR
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        WHERE 1=1
                            $query_conector3
                            $query_conector2
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                        ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 9 AS ID_GRUPO,'% SUSTENTACIÓN PROPIA - GENERAL' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                                0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '31%'
                                AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (313,319)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '41%'
                                AND SUBSTR(ID_CUENTAAASI,0,4) NOT IN (4191)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        WHERE 1=1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 10 AS ID_GRUPO,'% SUSTENTACIÓN PROPIA - OPERATIVO' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                                0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=1
                                AND ID_CUENTAAASI LIKE '31%'
                                AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (313,319)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=1
                                AND ID_CUENTAAASI LIKE '41%'
                                AND SUBSTR(ID_CUENTAAASI,0,4) NOT IN (4191)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                    WHERE 1=1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 11 AS ID_GRUPO,'% LIQUIDEZ INMEDIATA' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2),2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                                0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,3) IN (111,112)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (21)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '217%'
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            ------FALTA SOLUCIONAR ESTA CONSULTA
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                            CASE WHEN (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) <0 THEN 0 ELSE (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) END AS SALDO2
                            FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,0 AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '2317%'
                                    $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '3%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '4%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,0 AS SALDO3,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '6%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            ----------
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)>=3
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=2
                                AND ID_DEPTO=910111
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                    WHERE 1 = 1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 12 AS ID_GRUPO,'% LIQUIDEZ CORRIENTE' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                AND ID_ENTIDAD $id_entidades
                AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2),2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                            SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                            0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (11)
                                AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (117)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (21)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '217%'
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            ------FALTA SOLUCIONAR ESTA CONSULTA
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                            CASE WHEN (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) <0 THEN 0 ELSE (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) END AS SALDO2
                            FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,0 AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '2317%'
                                    $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '3%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '4%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,0 AS SALDO3,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '6%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            ----------
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)>=3
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=2
                                AND ID_DEPTO=910111
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                    WHERE 1 = 1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 14 AS ID_GRUPO,'% LIQUIDEZ GENERAL' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                        FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                        AND ID_ENTIDAD $id_entidades
                        AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2),2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                            SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                            0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND (SUBSTR(ID_CUENTAAASI,0,2) IN (11) OR SUBSTR(ID_CUENTAAASI,0,3) IN (121))
                                AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (117)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,2) IN (21)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '217%'
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '221%'
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            ------FALTA SOLUCIONAR ESTA CONSULTA
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                            CASE WHEN (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) <0 THEN 0 ELSE (SUM(SALDO1)+(SUM(SALDO2)-(SUM(SALDO3)-SUM(SALDO4)))) END AS SALDO2
                            FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,0 AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '2317%'
                                    $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2,0 AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '3%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO3,0 AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '4%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                UNION ALL
                                SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,0 AS SALDO2,0 AS SALDO3,
                                SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO4
                                FROM VW_CONTA_DIARIO
                                WHERE ID_EMPRESA $id_empresa
                                    AND ID_ENTIDAD $id_entidades
                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                    AND ID_FONDO=10
                                    AND ID_CUENTAAASI LIKE '6%'
                                    AND ID_DEPTO IN (SELECT ID_DEPTO FROM (SELECT DISTINCT ID_DEPTO,ES_GRUPO
                                        FROM CONTA_ENTIDAD_DEPTO
                                        START WITH ID_DEPTO='0D'
                                        CONNECT BY PRIOR ID_DEPTO = ID_PARENT)
                                        WHERE ES_GRUPO=0)
                                        $query_conector1
                                GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            ----------
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)>=3
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                CASE WHEN SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES))<0 THEN 0 ELSE SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) END AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=2
                                AND ID_DEPTO=910111
                                AND (ID_CUENTAAASI LIKE '2317%' OR SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,5,6,7))
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                    WHERE 1 = 1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD

                UNION ALL

                SELECT 15 AS ID_GRUPO,'% ACTIVO LÍQUIDO DISPONIBLE' AS GRUPO,A.ID_ENTIDAD,
                A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                      FROM CONTA_ENTIDAD WHERE ID_EMPRESA  $id_empresa
                      AND ID_ENTIDAD  $id_entidades
                      AND ID_TIPOENTIDAD  $id_tipoentidad
                     ) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES)
                                            )=0 THEN 0

                                    WHEN (LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES)
                                            )<0 AND SUM(COS_VALOR)>=0 THEN

                                            -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES))-1
                                            )

                                    ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,
                                                            ID_MES)
                                                        )-1 END *100,2
                                ) AS VARIACION
                            FROM (
                            /*Porcentaje activo liquido disponible*/
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES, (CASE WHEN NVL(SUM(SALDO2), 0)> 0 THEN ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) ELSE 0 END) COS_VALOR FROM (
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO1, 0 AS SALDO2
                                FROM (--TALD: Total Activo Líquido Disponible
                                        SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(SUM(SALDO)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (111)
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                            UNION ALL
                                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (112)
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                            UNION ALL
                                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND ID_CUENTAAASI = 1136006
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                        )TALD GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                        UNION ALL
                                        SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(SUM(SALDO)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,2) IN (21)
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                        )PAS GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                        UNION ALL
                                        SELECT ID_ENTIDAD, ID_ANHO, ID_MES,
                                        CASE WHEN ID_ANHO <=2020 THEN
                                        CASE WHEN SALDO <0 THEN 0 ELSE -SALDO END
                                        ELSE
                                        SALDO
                                        END AS SALDO
                                        FROM (SELECT CEP.ID_ENTIDAD,CEP.ID_ANHO, CEP.ID_MES,
                                            FC_SALDO_FONDO_ASIGNADO(CEP.ID_ENTIDAD,ID_ANHO ,ID_MES,10) AS SALDO
                                            FROM CONTA_ENTIDAD_PERIODO CEP
                                            INNER JOIN (SELECT * FROM CONTA_ENTIDAD CE1 WHERE CE1.ID_EMPRESA $id_empresa
                                            AND CE1.ID_ENTIDAD $id_entidades) CE ON CEP.ID_ENTIDAD = CE.ID_ENTIDAD
                                            WHERE 1 = 1 $query_conector1
                                            GROUP BY CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES
                                            )
                                    )GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                    UNION ALL
                                    /*90 dias del Total de los Gastos Esenciales (sin Depreciación) (25%) */
                                    SELECT NTGE.ID_ENTIDAD,NTGE.ID_ANHO,NTGE.ID_MES,0 AS SALDO1, ROUND((NTGE.SALDO*25)/100,2) AS SALDO2
                                    FROM(SELECT CEP.ID_ENTIDAD, CEP.ID_ANHO, CEP.ID_MES,
                                            PKG_MANAGEMENT_REPORTS.FC_GASTOS_ESENCIALES_SNDEPR(CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES) AS SALDO
                                            FROM CONTA_ENTIDAD_PERIODO CEP
                                            INNER JOIN (SELECT * FROM CONTA_ENTIDAD CE1 WHERE CE1.ID_EMPRESA $id_empresa
                                            AND CE1.ID_ENTIDAD $id_entidades) CE ON CEP.ID_ENTIDAD = CE.ID_ENTIDAD
                                            WHERE 1= 1 $query_conector1
                                            GROUP BY CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES
                                    )NTGE
                                    )PALD GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)

                                    WHERE 1 = 1
                                        $query_conector3
                                        $query_conector2
                                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD


                UNION ALL

                SELECT 16 AS ID_GRUPO,'ACTIVO LÍQUIDO DISPONIBLE (días)' AS GRUPO,A.ID_ENTIDAD,
                A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                    FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidades
                    AND ID_TIPOENTIDAD $id_tipoentidad
                    ) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES)
                                            )=0 THEN 0

                                    WHEN (LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES)
                                            )<0 AND SUM(COS_VALOR)>=0 THEN

                                            -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,
                                            ID_ANHO,ID_MES))-1
                                            )

                                    ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                                                            OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,
                                                            ID_MES)
                                                        )-1 END *100,2
                                ) AS VARIACION
                            FROM (
                            /*Porcentaje activo liquido disponible*/
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(SALDO1),SUM(SALDO2), (CASE WHEN NVL(SUM(SALDO2),0) > 0 THEN ROUND((SUM(SALDO1)/SUM(SALDO2))*365,0) ELSE 0 END) COS_VALOR FROM (
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO1, 0 AS SALDO2
                                FROM (--TALD: Total Activo Líquido Disponible
                                        SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(SUM(SALDO)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                            UNION ALL
                                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD  $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (112)
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                            UNION ALL
                                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND ID_CUENTAAASI = 1136006
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                        )TALD GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                        UNION ALL
                                        SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(SUM(SALDO)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES, SUM(COS_VALOR) AS SALDO
                                            FROM VW_CONTA_DIARIO_ALL
                                            WHERE ID_EMPRESA  $id_empresa
                                            AND ID_ENTIDAD $id_entidades
                                            AND ID_FONDO = 10
                                            AND ID_TIPOASIENTO NOT IN ('EA')
                                            AND SUBSTR(ID_CUENTAAASI,0,2) IN (21)
                                            $query_conector1
                                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                        )PAS GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                        UNION ALL
                                        SELECT ID_ENTIDAD, ID_ANHO, ID_MES,
                                        CASE WHEN ID_ANHO <=2020 THEN
                                        CASE WHEN SALDO <0 THEN 0 ELSE -SALDO END
                                        ELSE
                                        SALDO
                                        END AS SALDO
                                        FROM (SELECT CEP.ID_ENTIDAD,CEP.ID_ANHO, CEP.ID_MES,
                                              FC_SALDO_FONDO_ASIGNADO(CEP.ID_ENTIDAD,CEP.ID_ANHO ,CEP.ID_MES,10) AS SALDO
                                              FROM CONTA_ENTIDAD_PERIODO CEP
                                              INNER JOIN (SELECT * FROM CONTA_ENTIDAD CE1 WHERE CE1.ID_EMPRESA $id_empresa
                                              AND CE1.ID_ENTIDAD $id_entidades) CE ON CEP.ID_ENTIDAD = CE.ID_ENTIDAD
                                              WHERE 1 = 1 $query_conector1
                                              GROUP BY CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES
                                             )
                                    )GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES

                                    UNION ALL
                                    --90 dias del Total de los Gastos Esenciales (sin Depreciación) (25%)
                                    SELECT NTGE.ID_ENTIDAD,NTGE.ID_ANHO,NTGE.ID_MES,0 AS SALDO1, NTGE.SALDO AS SALDO2
                                    FROM(SELECT CEP.ID_ENTIDAD, CEP.ID_ANHO, CEP.ID_MES,
                                         PKG_MANAGEMENT_REPORTS.FC_GASTOS_ESENCIALES(CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES) AS SALDO
                                         FROM CONTA_ENTIDAD_PERIODO CEP
                                         INNER JOIN (SELECT * FROM CONTA_ENTIDAD CE1 WHERE CE1.ID_EMPRESA $id_empresa
                                         AND CE1.ID_ENTIDAD $id_entidades) CE ON CEP.ID_ENTIDAD = CE.ID_ENTIDAD
                                         WHERE 1 = 1 $query_conector1
                                         GROUP BY CEP.ID_ENTIDAD,CEP.ID_ANHO,CEP.ID_MES
                                    )NTGE
                                    )PALD GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)

                                    WHERE 1 = 1
                                        $query_conector3
                                        $query_conector2
                                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD


                UNION ALL

                SELECT 17 AS ID_GRUPO,'RENTABILIDAD DEL ACTIVO TOTAL' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                    FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidades
                    AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,
                            ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0 THEN 0
                            WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1)
                            ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) END AS COS_VALOR
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,
                            SUM(-SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,
                            0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ENTIDAD,ID_ANHO,ID_MES,0 AS SALDO1,
                                SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ENTIDAD,ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND ID_CUENTAAASI LIKE '1%'
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                    WHERE 1=1
                        $query_conector3
                        $query_conector2
                    GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                    ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD


                UNION ALL

                SELECT 18 AS ID_GRUPO,'GASTOS CON PERSONAL' AS GRUPO,A.ID_ENTIDAD,A.NOMBRE,B.ID_ANHO,B.ID_MES,B.SALDO,B.VARIACION
                FROM (SELECT ID_ENTIDAD,ID_EMPRESA,ID_TIPOENTIDAD,NOMBRE
                    FROM CONTA_ENTIDAD WHERE ID_EMPRESA $id_empresa
                    AND ID_ENTIDAD $id_entidades
                    AND ID_TIPOENTIDAD $id_tipoentidad) A
                INNER JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR)AS SALDO, ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))=0
                        THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))<0 AND SUM(COS_VALOR)>=0 THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1) ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL)
                        OVER (PARTITION BY ID_ENTIDAD ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES))-1 END *100,2) AS VARIACION
                        FROM (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,SUM(COS_VALOR) AS COS_VALOR
                            FROM VW_CONTA_DIARIO
                            WHERE ID_EMPRESA $id_empresa
                                AND ID_ENTIDAD $id_entidades
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_CUENTAAASI,0,3) IN (411)
                                $query_conector1
                            GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES)
                        WHERE 1=1
                            $query_conector3
                            $query_conector2
                        GROUP BY ID_ENTIDAD,ID_ANHO,ID_MES
                        ORDER BY ID_ENTIDAD,ID_ANHO,ID_MES) B ON A.ID_ENTIDAD=B.ID_ENTIDAD
                    ";
        // echo($query);
           // print($query);
        // print_r($query);
        $oQuery = DB::select($query);
        $result=[];
        $data_new=(object)array();
        $data_old=(object)array('id_grupo'=>null,'id_entidad'=>null);
        $series=[];
        $categories=[];
        $series_data1=[];
        $series_data2=[];
        $series_data_child=[];
        $series_spline=[];
        $series_spline_child=[];
        $i=0;
        foreach ($oQuery as $key => $value){
        //print($i.' ');
            $data_new=$value;
            if($i<count($oQuery)-1){
                if($id_conector==='0'){
                    if(($data_new->id_grupo!==$data_old->id_grupo or $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                        $saldo1=$series_data_child[0];
                        $saldo2=$series_data_child[0];
                        $spline=0;
                        if(count($series_data_child)<=1){
                            $spline=0;
                            if($id_anho2===$data_old->id_anho and $id_mes2===$data_old->id_mes){
                                $saldo1=0;
                            }else{
                                $saldo2=0;
                            }
                        }else{
                            $saldo2=$series_data_child[1];
                            $spline=$series_spline_child[1];
                        }
                        $categories[]=$data_old->nombre;
                        $series_data1[]=$saldo1;
                        $series_data2[]=$saldo2;
                        $series_spline[]=$spline;
                        $series_data_child=[];
                        $series_spline_child=[];
                    }
                    if($data_new->id_grupo!==$data_old->id_grupo and $i>0){
                        $series[]=(object)array('type'=>'column','name'=>$id_mes1.'/'.substr($id_anho1,2,4),'yAxis'=>1,'data'=>$series_data1,'color'=>'#586172');
                        $series[]=(object)array('type'=>'column','name'=>$id_mes2.'/'.substr($id_anho2,2,4),'yAxis'=>1,'data'=>$series_data2, 'color'=>'#023246');
                        $series[]=(object)array('type'=>'spline','name'=>'Variación','data'=>$series_spline, 'color'=>'#009245');
                        $result[]=(object)array('id'=>$data_old->id_grupo,'name'=>$data_old->grupo,'categories'=>$categories,'series'=>$series);
                        $series=[];
                        $categories=[];
                        $series_data1=[];
                        $series_data2=[];
                        $series_spline=[];
                        $series_data_child=[];
                        $series_spline_child=[];
                    }
                    $series_data_child[]=floatval($value->saldo);
                    $series_spline_child[]=floatval($value->variacion);
                }else{
                    if($data_new->id_grupo!==$data_old->id_grupo and $i>0){
                        //print(' grupo diff and i > 0 ');
                        $series[]=(object)array('type'=>'column','name'=>'Monto','yAxis'=>1,'data'=>$series_data1, 'color'=>'#023246');
                        $series[]=(object)array('type'=>'spline','name'=>'Variación','data'=>$series_spline, 'color'=>'#009245');
                        $result[]=(object)array('id'=>$data_old->id_grupo,'name'=>$data_old->grupo,'categories'=>$categories,'series'=>$series);
                        $series=[];
                        $categories=[];
                        $series_data1=[];
                        $series_data2=[];
                        $series_data1=[];
                        $series_spline=[];
                    }
                    // print(' grupo libre and i libre ');
                    $categories[]=$value->id_mes.'/'.substr($value->id_anho,2,4);
                    $series_data1[]=floatval($value->saldo);
                    $series_spline[]=floatval($value->variacion);
                }
            }
            if($i===count($oQuery)-1){
                if($id_conector==='0'){
                    $series_data_child[]=floatval($value->saldo);
                    $series_spline_child[]=floatval($value->variacion);
                    $saldo1=$series_data_child[0];
                    $saldo2=$series_data_child[0];
                    $spline=0;
                    if(count($series_data_child)<=1){
                        $spline=0;
                        if($id_anho2===$value->id_anho and $id_mes2===$value->id_mes){
                            $saldo1=0;
                        }else{
                            $saldo2=0;
                        }
                    }else{
                        $saldo2=$series_data_child[1];
                        $spline=$series_spline_child[1];
                    }
                    $categories[]=$value->nombre;
                    $series_data1[]=$saldo1;
                    $series_data2[]=$saldo2;
                    $series_spline[]=$spline;
                    $series_data_child=[];
                    $series_spline_child=[];
                    $series[]=(object)array('type'=>'column','name'=>$id_mes1.'/'.substr($id_anho1,2,4),'yAxis'=>1,'data'=>$series_data1, 'color'=>'#586172');
                    $series[]=(object)array('type'=>'column','name'=>$id_mes2.'/'.substr($id_anho2,2,4),'yAxis'=>1,'data'=>$series_data2, 'color'=>'#023246');
                    $series[]=(object)array('type'=>'spline','name'=>'Variación','data'=>$series_spline, 'color'=>'#009245');
                    $result[]=(object)array('id'=>$value->id_grupo,'name'=>$value->grupo,'categories'=>$categories,'series'=>$series);
                    $series=[];
                    $categories=[];
                    $series_data1=[];
                    $series_data2=[];
                    $series_spline=[];
                    $series_data_child=[];
                    $series_spline_child=[];
                }else{
                    // print('conector diff 0 ');
                    $categories[]=$value->id_mes.'/'.substr($value->id_anho,2,4);
                    $series_data1[]=floatval($value->saldo);
                    $series_spline[]=floatval($value->variacion);
                    $series[]=(object)array('type'=>'column','name'=>'Monto','yAxis'=>1,'data'=>$series_data1, 'color'=>'#023246');
                    $series[]=(object)array('type'=>'spline','name'=>'Variación','data'=>$series_spline, 'color'=>'#009245');
                    $result[]=(object)array('id'=>$value->id_grupo,'name'=>$value->grupo,'categories'=>$categories,'series'=>$series);

                }
            }
            $data_old=$value;
            $i++;
        }
        return $result;
    }

    public static function getIdEmpresa($id_tipoentidad){
        // echo "ingrese tipo entidad $id_tipoentidad";
        $query = "select LISTAGG (id_empresa, ',') WITHIN GROUP (ORDER BY id_empresa) id_empresa
        from (select distinct id_empresa from conta_entidad
        where id_tipoentidad $id_tipoentidad)";
        $oQuery = DB::select($query);
        //print_r($oQuery);
        return $oQuery[0]->id_empresa;
    }

    public static function build_sorter($clave) {
        // print($clave);
        return function ($a, $b) use ($clave) {
            // print_r($a[$clave]);
            return strnatcmp($a[$clave], $b[$clave]);
        };
    }

    public static function getMonthlyControlSummary($request) {
        $data_new=null;
        // $id_categoria = $request->id_categoria;
        $id_categorias = $request->id_categorias;
        $id_empresa = $request->id_empresa;
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $allMonths=false;
        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
/*         if($id_categoria===null or $id_categoria==='*'){
            $id_categoria=" IS NOT NULL";
        }else{
            $id_categoria=" = $id_categoria";
        } */

        if($id_categorias===null or $id_categorias==='*'){
            $id_categorias=" IS NOT NULL";
        }else{
            $id_categorias=" IN ($id_categorias)";
        }


        if($id_mes===null or $id_mes==='*'){
            $allMonths=true;
            $id_mes=' <=12 ';
        }else{
            $id_mes=" = $id_mes";
        }

        $query_types_documents="SELECT DISTINCT E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ANHO=$id_anho AND ID_MES $id_mes AND TIENE_PUNTAJE = '1' AND ACTIVO = '1') C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
        WHERE --C.ID_ANHO=$id_anho
        --AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        --AND
        D.ID_GRUPOARCHIVO $id_grupoarchivo
        AND VWCEC.ID_CATEGORIA $id_categorias
        ORDER BY 2,4";
        // echo($query_types_documents);
        $result_types_documents = DB::select($query_types_documents);

        $query="SELECT T1.*,T2.NOMBRE,T2.URL,T2.FECHA_CREACION,T2.FECHA_MODIFICACION,
        CASE WHEN T2.FECHA_MODIFICACION IS NOT NULL AND T2.NOMBRE IS NOT NULL THEN
                CASE WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=0 THEN '3'
                WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')<0
                AND  TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=-1 THEN '1'
                ELSE '0' END
            WHEN T2.NOMBRE IS NULL AND TO_DATE(SYSDATE) - TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY')>0 THEN '0' ELSE 'N' END AS PUNTAJE
        FROM
        (SELECT DISTINCT VWCEC.ID_CATEGORIA, VWCEC.CATEGORIA, A.ID_EMPRESA,A.NOM_EMPRESA,A.ID_ENTIDAD,A.NOMBRE AS NOM_ENTIDAD,C.ID_ARCHIVO_MENSUAL,E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,
        D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO,C.FECHA_LIMITE
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ANHO=$id_anho AND ID_MES $id_mes AND TIENE_PUNTAJE = '1' AND ACTIVO = '1') C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
        WHERE --C.ID_ANHO=$id_anho
        --AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        --AND
        D.ID_GRUPOARCHIVO $id_grupoarchivo) T1
        LEFT JOIN ARCHIVO_MENSUAL_DETALLE T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
        WHERE T1.ID_ENTIDAD NOT IN (9119,17125,17123)
        AND T1.ID_CATEGORIA $id_categorias
        --AND T1.ID_EMPRESA  $id_empresa
        ORDER BY T1.ID_CATEGORIA, T1.ID_EMPRESA DESC,T1.ID_ENTIDAD ASC,T1.GRUPOARCHIVO ASC,PUNTAJE DESC";
        //var_dump($query);
        // echo($query);
        if( $allMonths){
            $query="SELECT P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO, NULL ID_ARCHIVO_MENSUAL, NULL AS NOMBRE,NULL AS URL,NULL AS FECHA_CREACION,
            NULL AS FECHA_MODIFICACION,
            /*SUM(P.PUNTAJE) AS PUNTAJE*/
            SUM(case when regexp_like(P.PUNTAJE, '^[^a-zA-Z]*$')
                then TO_NUMBER(P.PUNTAJE)
                else 0
            END) AS PUNTAJE
            FROM (
                $query
            )P GROUP BY P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO
            ORDER BY P.ID_CATEGORIA, P.ID_EMPRESA DESC,P.ID_ENTIDAD ASC,P.GRUPOARCHIVO ASC ";
        }
        // print($query);
        $oQuery = DB::select($query);

        if( $allMonths){
            $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_categoria' => null];

            $result=[];
            $data=[];
            $data1=[];
            $data2=[];
            $children1=[];
            $children2=[];
            $array_types_documents=[];
            $array_types_documents1=[];
            $array_types_documents2=[];
            $array_types_documents3=[];
            $i = 0;


            foreach ($oQuery as $key => $value){


                $data_new=$value;
                if($i>0){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
                    'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
                }

                if(($data_new->id_categoria!==$data_old->id_categoria || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }

                        }
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);

                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){

                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;

                    }
                    // $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    // puntaje total de cada documento interno
                    $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'acumulado');
                    //$children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;
                    $array_types_documents2=[];
                    $array_types_documents3=[];
                }

                if($data_new->id_categoria!==$data_old->id_categoria and $i>0){
                     foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                              if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                        }
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'required'=>true);

                    }
                     $count=0;
                    $total=0;
                    $total_color=0;
                     foreach ($array_types_documents as $key => $value1){

                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;

                    }
                    //$data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                    $children1=[];
                    $array_types_documents=[];
                    $array_types_documents1=[];
                }

                // si la data es igual al total de registros
                if($i===count($oQuery)-1){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                    'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                               $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;

                        }
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                    }
                    /*$data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));*/
                    $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'acumulado');
                    $children1[]=$data1;
                    // $children1[]=(object)array('data'=>$data1);

                    // permite cargar los tipos de documentos para la cabecera
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){

                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }


                        }
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            //'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'puntaje'=>floatval(number_format($count!==0?$puntaje:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color:0,2)),
                            'required'=>true);

                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                   foreach ($array_types_documents as $key => $value1){

                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;

                    }
                    //$data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents);
                    $data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    // $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                }
                $data_old=$value;
                $i++;
            }
        }else{
            $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_categoria' => null];

            $result=[];
            $data=[];
            $data1=[];
            $data2=[];
            $datap=[];
            $children1=[];
            $children2=[];
            $array_types_documents=[];
            $array_types_documents1=[]; // cATEGORIAS (UNION .....)
            $array_types_documents2=[]; // ennTIDADES Y PUNTAJE TOTAL
            $array_types_documents3=[]; // NOMBRE DEL ARCHIVO
            $i = 0;


            foreach ($oQuery as $key => $value){


                $data_new=$value;
                if($i>0){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
                    'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
                }

                if(($data_new->id_categoria!==$data_old->id_categoria || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }
                        }
                        if($count>0){
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                        }else{
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                        if($value1->required){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                        }
                    }
                    // $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    // puntaje total de cada documento interno
                    $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'no acumulado');
                    // $children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;
                    $array_types_documents2=[];
                    $array_types_documents3=[];
                }

                if($data_new->id_categoria!==$data_old->id_categoria and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                            }
                        }
                          if($count>0){
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'required'=>true);
                        }else{
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                     $count=0;
                    $total=0;
                    $total_color=0;
                     foreach ($array_types_documents as $key => $value1){
                        if($value1->required){
                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;
                        }
                    }
                    //$data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                    $children1=[];
                    $array_types_documents=[];
                    $array_types_documents1=[];
                }

                // si la data es igual al total de registros
                if($i===count($oQuery)-1){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                    'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }
                        }
                        if($count>0){
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                        }else{
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                        if($value1->required){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                        }
                    }
                    /*$data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));*/
                    $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=> 'no acumulado');
                    //$children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;

                    // permite cargar los tipos de documentos para la cabecera
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                            }
                        }
                        if($count>0){
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            //'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'puntaje'=>floatval(number_format($count!==0?$puntaje:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color:0,2)),
                            'required'=>true);
                        }else{
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                       }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    // puntaje de la categoria
                    foreach ($array_types_documents as $key => $value1){
                        if($value1->required){
                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;
                        }
                    }
                    //$data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents);
                    $data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=> 'no acumulado');
                    // $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                }
                $data_old=$value;
                $i++;
            }

        }
        foreach ($result as $key => $valOrder){
            $array_new=array();
            foreach ($valOrder->children as $key1 => $valChildren){
                $array_new[$key1]=$valChildren->{'puntaje'};
            }
            $sorted=array_multisort( $array_new,SORT_DESC,$valOrder->children);
        }
        return $result;
    }


    public static function getMonthlyControlSummary_antP($request) {
        $data_new=null;
        $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_empresa' => null];
        $id_empresa = $request->id_empresa;
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;

        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes=" IS NOT NULL ";
        }else{
            $id_mes=" <= $id_mes";
        }
        $query_types_documents="SELECT DISTINCT E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        WHERE C.ID_ANHO=$id_anho
        AND  C.ID_MES=$id_mes
        --AND C.ID_DEPTO IS NULL
        AND D.ID_GRUPOARCHIVO $id_grupoarchivo
        ORDER BY 2,4";

        $result_types_documents = DB::select($query_types_documents);
        $query="SELECT T1.*,T2.NOMBRE,T2.URL,T2.FECHA_CREACION,T2.FECHA_MODIFICACION,
        CASE WHEN T2.FECHA_MODIFICACION IS NOT NULL AND T2.NOMBRE IS NOT NULL THEN CASE WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=0 THEN '3'
        WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')<0 AND  TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=-2 THEN '2'
        ELSE '0' END WHEN T2.NOMBRE IS NULL AND TO_DATE(SYSDATE) - TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY')>0 THEN '0' ELSE 'N' END AS PUNTAJE
        FROM
        (SELECT DISTINCT A.ID_EMPRESA,A.NOM_EMPRESA,A.ID_ENTIDAD,A.NOMBRE AS NOM_ENTIDAD,C.ID_ARCHIVO_MENSUAL,E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,
        D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO,C.FECHA_LIMITE
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        WHERE C.ID_ANHO=$id_anho
        AND  C.ID_MES=$id_mes
        --AND C.ID_DEPTO IS NULL
        AND D.ID_GRUPOARCHIVO $id_grupoarchivo) T1
        LEFT JOIN ARCHIVO_MENSUAL_DETALLE T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
        WHERE T1.ID_ENTIDAD NOT IN (9119,17125,17123)
        AND T1.ID_EMPRESA $id_empresa
        ORDER BY T1.ID_EMPRESA DESC,T1.ID_ENTIDAD ASC,T1.GRUPOARCHIVO ASC,PUNTAJE DESC";

        //var_dump($query);

        $oQuery = DB::select($query);
        $result=[];
        $data=[];
        $data1=[];
        $data2=[];
        $children1=[];
        $children2=[];
        $array_types_documents=[];
        $array_types_documents1=[];
        $array_types_documents2=[];
        $array_types_documents3=[];
        $i = 0;
        foreach ($oQuery as $key => $value){
            $data_new=$value;
            if($i>0){
            $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
            'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
            }
            if(($data_new->id_empresa!==$data_old->id_empresa || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                foreach ($result_types_documents as $key => $value1){
                    $puntaje=null;
                    $count=0;
                    $nombre=null;
                    $url=null;
                    foreach ($array_types_documents3 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            $count++;
                            $puntaje=$value2->puntaje;
                            $nombre=$value2->nombre;
                            $url=$value2->url;
                        }
                    }
                    if($count>0){
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>$puntaje,'required'=>true);
                    }else{
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents2 as $key => $value1){
                    if($value1->required){
                        if($value1->puntaje!=='N'){
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje;
                        }else{
                            $total_color=$total_color+3;
                        }
                        $count++;
                    }
                }
                $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                $children1[]=(object)array('data'=>$data1);
                $array_types_documents2=[];
                $array_types_documents3=[];
            }

            if($data_new->id_empresa!==$data_old->id_empresa and $i>0){
                foreach ($result_types_documents as $key => $value1){
                    $puntaje=0;
                    $puntaje_color=0;
                    $count=0;
                    foreach ($array_types_documents1 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            if($value2->required){
                                $count++;
                                if($value2->puntaje!=='N'){
                                    $puntaje=$puntaje+$value2->puntaje;
                                    $puntaje_color=$puntaje_color+$value2->puntaje;
                                }else{
                                    $puntaje_color=$puntaje_color+3;
                                }
                            }
                        }
                    }
                    if($count>0){
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                        'required'=>true);
                    }else{
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents as $key => $value1){
                    if($value1->required){
                        $count++;
                        $total=$total+$value1->puntaje;
                        $total_color=$total_color+$value1->puntaje_color;
                    }
                }
                $data=(object)array('nombre'=>$data_old->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                $result[]=(object)array('data'=>$data,'children'=>$children1);
                $children1=[];
                $array_types_documents=[];
                $array_types_documents1=[];
            }
            if($i===count($oQuery)-1){
                $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                foreach ($result_types_documents as $key => $value1){
                    $puntaje=null;
                    $count=0;
                    $nombre=null;
                    $url=null;
                    foreach ($array_types_documents3 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            $count++;
                            $puntaje=$value2->puntaje;
                            $nombre=$value2->nombre;
                            $url=$value2->url;
                        }
                    }
                    if($count>0){
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>$puntaje,'required'=>true);
                    }else{
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents2 as $key => $value1){
                    if($value1->required){
                        if($value1->puntaje!=='N'){
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje;
                        }else{
                            $total_color=$total_color+3;
                        }
                        $count++;
                    }
                }
                $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                $children1[]=(object)array('data'=>$data1);

                foreach ($result_types_documents as $key => $value1){
                    $puntaje=0;
                    $puntaje_color=0;
                    $count=0;
                    foreach ($array_types_documents1 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            if($value2->required){
                                $count++;
                                if($value2->puntaje!=='N'){
                                    $puntaje=$puntaje+$value2->puntaje;
                                    $puntaje_color=$puntaje_color+$value2->puntaje;
                                }else{
                                    $puntaje_color=$puntaje_color+3;
                                }
                            }
                        }
                    }
                    if($count>0){
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                        'required'=>true);
                    }else{
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents as $key => $value1){
                    if($value1->required){
                        $count++;
                        $total=$total+$value1->puntaje;
                        $total_color=$total_color+$value1->puntaje_color;
                    }
                }
                $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                $result[]=(object)array('data'=>$data,'children'=>$children1);
            }
            $data_old=$value;
            $i++;
        }
        return $result;
    }

    public static function getMonthlyControlSummaryCumulative($request) {
        $data_new=null;
        $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_categoria' => null];
        $id_categoria = $request->id_categoria;
        $id_empresa = $request->id_empresa;
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;

        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        if($id_categoria===null or $id_categoria==='*'){
            $id_categoria=" IS NOT NULL";
        }else{
            $id_categoria=" = $id_categoria";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes=" IS NOT NULL ";
        }else{
            $id_mes=" <= $id_mes";
        }

        $query_types_documents="SELECT DISTINCT E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        WHERE C.ID_ANHO=$id_anho
        AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        AND D.ID_GRUPOARCHIVO $id_grupoarchivo
        ORDER BY 2,4";

        $result_types_documents = DB::select($query_types_documents);

        $query="SELECT P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
        P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
        P.ID_TIPOARCHIVO, P.TIPOARCHIVO, SUM(P.PUNTAJE)
        FROM (SELECT T1.*,T2.NOMBRE,T2.URL,T2.FECHA_CREACION,T2.FECHA_MODIFICACION,
        CASE WHEN T2.FECHA_MODIFICACION IS NOT NULL AND T2.NOMBRE IS NOT NULL THEN
                CASE WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=0 THEN '3'
                WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')<0 AND  TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=-1 THEN '1'
                ELSE '0' END
            WHEN T2.NOMBRE IS NULL AND TO_DATE(SYSDATE) - TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY')>0 THEN '0' ELSE 'N' END AS PUNTAJE
        FROM
        (SELECT DISTINCT VWCEC.ID_CATEGORIA, VWCEC.CATEGORIA, A.ID_EMPRESA,A.NOM_EMPRESA,A.ID_ENTIDAD,A.NOMBRE AS NOM_ENTIDAD,C.ID_ARCHIVO_MENSUAL,E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,
        D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO,C.FECHA_LIMITE
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
        WHERE C.ID_ANHO=$id_anho
        AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        AND D.ID_GRUPOARCHIVO $id_grupoarchivo) T1
        LEFT JOIN ARCHIVO_MENSUAL_DETALLE T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
        WHERE T1.ID_ENTIDAD NOT IN (9119,17125,17123)
        AND T1.ID_CATEGORIA $id_categoria
        --AND T1.ID_EMPRESA  $id_empresa
        ORDER BY T1.ID_CATEGORIA, T1.ID_EMPRESA DESC,T1.ID_ENTIDAD ASC,T1.GRUPOARCHIVO ASC,PUNTAJE DESC)
        P GROUP BY P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
        P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO, P.ID_TIPOARCHIVO, P.TIPOARCHIVO ";
        //var_dump($query);
        // echo($query);
        $oQuery = DB::select($query);
        $result=[];
        $data=[];
        $data1=[];
        $data2=[];
        $datap=[];
        $children1=[];
        $children2=[];
        $array_types_documents=[];
        $array_types_documents1=[];
        $array_types_documents2=[];
        $array_types_documents3=[];
        $i = 0;


        foreach ($oQuery as $key => $value){


            $data_new=$value;
            if($i>0){
                $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
                'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
            }

            if(($data_new->id_categoria!==$data_old->id_categoria || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                foreach ($result_types_documents as $key => $value1){
                    $puntaje=null;
                    $count=0;
                    $nombre=null;
                    $url=null;
                    foreach ($array_types_documents3 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            $count++;
                            $puntaje=$value2->puntaje;
                            $nombre=$value2->nombre;
                            $url=$value2->url;
                        }
                    }
                    if($count>0){
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>$puntaje,'required'=>true);
                    }else{
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents2 as $key => $value1){
                    if($value1->required){
                        if($value1->puntaje!=='N'){
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje;
                        }else{
                            $total_color=$total_color+3;
                        }
                        $count++;
                    }
                }
                // $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                // puntaje total de cada documento interno
                $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                $children1[]=(object)array('data'=>$data1);
                $array_types_documents2=[];
                $array_types_documents3=[];
            }

            if($data_new->id_categoria!==$data_old->id_categoria and $i>0){
                 foreach ($result_types_documents as $key => $value1){
                    $puntaje=0;
                    $puntaje_color=0;
                    $count=0;
                    foreach ($array_types_documents1 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            if($value2->required){
                                $count++;
                                if($value2->puntaje!=='N'){
                                    $puntaje=$puntaje+$value2->puntaje;
                                    $puntaje_color=$puntaje_color+$value2->puntaje;
                                }else{
                                    $puntaje_color=$puntaje_color+3;
                                }
                            }
                        }
                    }
                      if($count>0){
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                        'required'=>true);
                    }else{
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                 $count=0;
                $total=0;
                $total_color=0;
                 foreach ($array_types_documents as $key => $value1){
                    if($value1->required){
                        $count++;
                        $total=$total+$value1->puntaje;
                        $total_color=$total_color+$value1->puntaje_color;
                    }
                }
                //$data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                $data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                $result[]=(object)array('data'=>$data,'children'=>$children1);
                $children1=[];
                $array_types_documents=[];
                $array_types_documents1=[];
            }

            // si la data es igual al total de registros
            if($i===count($oQuery)-1){
                $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                foreach ($result_types_documents as $key => $value1){
                    $puntaje=null;
                    $count=0;
                    $nombre=null;
                    $url=null;
                    foreach ($array_types_documents3 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            $count++;
                            $puntaje=$value2->puntaje;
                            $nombre=$value2->nombre;
                            $url=$value2->url;
                        }
                    }
                    if($count>0){
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        'puntaje'=>$puntaje,'required'=>true);
                    }else{
                        $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                        $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                    }
                }
                $count=0;
                $total=0;
                $total_color=0;
                foreach ($array_types_documents2 as $key => $value1){
                    if($value1->required){
                        if($value1->puntaje!=='N'){
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje;
                        }else{
                            $total_color=$total_color+3;
                        }
                        $count++;
                    }
                }
                /*$data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));*/
                $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                $children1[]=(object)array('data'=>$data1);

                // permite cargar los tipos de documentos para la cabecera
                foreach ($result_types_documents as $key => $value1){
                    $puntaje=0;
                    $puntaje_color=0;
                    $count=0;
                    foreach ($array_types_documents1 as $key => $value2){
                        if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                            if($value2->required){
                                $count++;
                                if($value2->puntaje!=='N'){
                                    $puntaje=$puntaje+$value2->puntaje;
                                    $puntaje_color=$puntaje_color+$value2->puntaje;
                                }else{
                                    $puntaje_color=$puntaje_color+3;
                                }
                            }
                        }
                    }
                    if($count>0){
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                        //'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                        'puntaje'=>floatval(number_format($count!==0?$puntaje:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color:0,2)),
                        'required'=>true);
                    }else{
                        $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                        ,'required'=>false);
                   }
                }
                $count=0;
                $total=0;
                $total_color=0;
               foreach ($array_types_documents as $key => $value1){
                    if($value1->required){
                        $count++;
                        $total=$total+$value1->puntaje;
                        $total_color=$total_color+$value1->puntaje_color;
                    }
                }
                //$data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents);
                $data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                // $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                $result[]=(object)array('data'=>$data,'children'=>$children1);
            }
            $data_old=$value;
            $i++;
        }
        return $result;
    }




    public static function getExpenseDetail($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $depto = $request->deptos;
        $type = $request->type;
        $pageSize = $request->pageSizeEx;
        $page = $request->pageEx;
        $from = 1;
        $to = ($pageSize * $page) - ($pageSize);
        $addSumDep="";
        $addDep_="";
        $addSumMes="";
        $addMes_="";
        $sqlSaldo="";
        $saldo_anterior = 0;


        if($depto !=='null' AND $depto!==null and $depto !=='*'){
            $addSumDep= " AND CD.ID_DEPTO IN (".$depto.")";
            $addDep_= " AND ID_DEPTO IN (".$depto.")";
        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*'){
            $addSumMes = " AND CD.ID_MES = $id_mes ";
            $addMes_ = " AND ID_MES = $id_mes ";
        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*' AND  $id_mes > 1) {
            $sqlSaldo="
            (SELECT NVL(SUM(CD.DEBE - CD.HABER), 0)
            FROM  VW_CONTA_DIARIO CD
            WHERE CD.ID_CUENTAAASI LIKE '4%' AND CD.ID_ENTIDAD = ".$id_entidad." AND CD.ID_ANHO = ".$id_anho." AND CD.ID_MES <  ".$id_mes." ".$addSumDep." AND CD.ID_TIPOASIENTO != 'EA') AS SALDO_ANT_MENSUAL
             ";
        } else {
            $sqlSaldo=" 0 AS SALDO_ANT_MENSUAL ";
        }


        $query_total = "SELECT count(*) as total
        FROM VW_CONTA_DIARIO CD
        INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
        WHERE CD.ID_CUENTAAASI like '4%' AND CD.ID_ENTIDAD = ".$id_entidad."
        AND CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND CD.ID_TIPOASIENTO != 'EA' ORDER BY CD.FEC_ASIENTO ASC,  CC.NOMBRE ASC";

        $exe_q_total = DB::select($query_total);
        $total = $exe_q_total[0]->total;


        if ($type == 'pdf'){
            $limit = 0;
            $offset = $total;
        }else{
            $limit = ($page - 1) * $pageSize + 1;
            $offset = $page * $pageSize;
        }

        $query= "SELECT item.* FROM (SELECT row_number() over (ORDER BY CD.FEC_ASIENTO ASC, CC.NOMBRE ASC, CD.CODIGO ASC) line_number,
                                    CD.ID_MES,
                                    TOTAL_DEBE_HABER.T_DEBE,
                                    TOTAL_DEBE_HABER.T_HABER,
                                    to_char((CD.FEC_ASIENTO), 'Month','nls_date_language=spanish') as MES, CD.ID_ENTIDAD,
                                    CD.FEC_ASIENTO, CD.CODIGO, CC.NOMBRE AS CUENTA, CD.COMENTARIO AS GLOSA, TO_CHAR(CD.DEBE, 'fm999999999990.00') AS DEBE, TO_CHAR(CD.HABER, 'fm999999999990.00') AS HABER,
                                    NVL((CD.DEBE - CD.HABER), 0) AS SALDO,
                                    $sqlSaldo
                                    FROM VW_CONTA_DIARIO CD
                                    INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
                                    INNER JOIN (SELECT ID_MES, SUM(DEBE) AS T_DEBE, SUM(HABER) AS T_HABER FROM VW_CONTA_DIARIO
                                                WHERE ID_CUENTAAASI like '4%' AND ID_ENTIDAD = ".$id_entidad." AND ID_ANHO = ".$id_anho."
                                                ".$addDep_." ".$addMes_." AND ID_TIPOASIENTO != 'EA' GROUP BY ID_MES) TOTAL_DEBE_HABER ON TOTAL_DEBE_HABER.ID_MES = CD.ID_MES
                                    WHERE CD.ID_CUENTAAASI like '4%' AND CD.ID_ENTIDAD = ".$id_entidad."
                                    AND CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND CD.ID_TIPOASIENTO != 'EA')item where  item.line_number BETWEEN $limit AND $offset";
        /// print($query);
        $executeQuery = DB::select($query);

        // print($page);
        if($page > 1 && $type != 'pdf'){
            $querySum = "SELECT SUM(ditem.DEBE - ditem.HABER) as saldo_anterior
            from (SELECT CD.DEBE, CD.HABER, row_number() over (ORDER BY CD.FEC_ASIENTO ASC, CC.NOMBRE ASC, CD.CODIGO ASC) line_number
            FROM VW_CONTA_DIARIO CD
            INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
            WHERE CD.ID_CUENTAAASI like '4%' AND CD.ID_ENTIDAD = ".$id_entidad." AND CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND CD.ID_TIPOASIENTO != 'EA') ditem where  ditem.line_number BETWEEN $from AND $to";

            $oQuery = DB::select($querySum);
            $saldo_anterior = $oQuery[0]->saldo_anterior;
        }

        $i= 0;

        foreach ($executeQuery as $item) {
            if($i == 0){
                $m_monto = $item->saldo + $saldo_anterior + $item->saldo_ant_mensual;
                $executeQuery[$i]->saldo = number_format($m_monto,2,'.','');
             } else  {
                $m_monto = $executeQuery[$i-1]->saldo + $item->debe - $item->haber;
                $executeQuery[$i]->saldo = number_format($m_monto,2,'.','');
            }

            $i++;
        }

        if($total> 0  and $offset > $total AND $type != 'pdf'){
            $executeQuery[] = (object) array(
                'line_number'=> $total + 1,
                'id_mes'=> $executeQuery[$i -1]->id_mes,
                't_debe'=> "",
                't_haber'=> "",
                'mes'=>  "",
                'id_entidad'=> $executeQuery[$i -1]->id_entidad,
                'fec_asiento'=> $executeQuery[$i -1]->fec_asiento,
                'codigo'=> "",
                'cuenta'=> "::Saldo Final",
                'glosa'=> "::Saldo Final",
                'debe'=> "",
                'haber'=> "",
                'saldo'=> $executeQuery[$i -1]->saldo,
            );
        }

       $array_resultante = (object) array(
        'current_page' => $page,
        'data' => $executeQuery,
        'from' => $limit,
        'per_page' => $pageSize,
        'to' => $offset,
        'total' => $total,
        );
        return $array_resultante;
    }


    public static function getPerformanceReport($request) {

        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_fondo = $request->id_fondo;
        $id_mes_init=0;

        if($id_anho===null or $id_anho==='*'){
            $id_anho=date('Y');
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes=5;
        }

        if($id_fondo===null or $id_fondo==='*'){
            $id_fondo=" IS NOT NULL";
        }else{
            $id_fondo=" IN ($id_fondo)";
        }



        $query_select=" ROUND(CASE WHEN (LAG(SUM(SALDO), 1, NULL) OVER (ORDER BY ID_ANHO,ID_MES))=0
        THEN 0 WHEN (LAG(SUM(SALDO), 1, NULL) OVER (ORDER BY ID_ANHO,ID_MES))<0 AND SUM(SALDO)>=0 THEN -(SUM(SALDO)/(LAG(SUM(SALDO), 1, NULL)
        OVER (ORDER BY ID_ANHO,ID_MES))-1) ELSE SUM(SALDO)/(LAG(SUM(SALDO), 1, NULL)
        OVER (ORDER BY ID_ANHO,ID_MES))-1 END *100,2) ";

        $query_where1=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                    TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                    TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || '01')
                    AND TO_NUMBER('$id_anho'||LPAD('$id_mes',2,0)) ";
        $query_where1_1=" AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                    TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                    TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END ||
                    LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<=0 THEN 1
                    ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                    AND TO_NUMBER('$id_anho'||LPAD('$id_mes',2,0)) ";
        $query_where2=" WHERE TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))>=TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0)) ";

        if ($id_anho <= 2020) {
            $query_capoperative = "
                    SELECT 3 AS ID_GRUPO,'CAPITAL OPERATIVO' AS GRUPO,
                    LPAD(ID_MES,2,'0')||'/'||SUBSTR(ID_ANHO,3,4) AS CAT1,ID_ANHO1 AS CAT2,ID_MES1 AS CAT3,SALDO,VARIACION
                    FROM (SELECT ID_ANHO,ID_MES,ID_ANHO AS ID_ANHO1,ID_MES AS ID_MES1,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                        FROM (SELECT ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2)*100,2) END AS SALDO
                            FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SALDO1,0 AS SALDO2
                                FROM (SELECT ID_ANHO,ID_MES,SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM VW_CONTA_DIARIO
                                        WHERE ID_ENTIDAD =$id_entidad
                                        AND ID_FONDO $id_fondo
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,2) IN (11,21)
                                        AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                        TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0
                                                THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD('01',2,0))
                                        AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                        GROUP BY ID_ANHO,ID_MES

                                        UNION ALL

                                        SELECT ID_ANHO, ID_MES,
                                        CASE WHEN SALDO <0 THEN 0 ELSE -SALDO END AS SALDO
                                        FROM (
                                            SELECT
                                                ID_ANHO, ID_MES,
                                                FC_SALDO_FONDO_ASIGNADO($id_entidad,ID_ANHO ,ID_MES,10)
                                                AS SALDO
                                            FROM
                                                CONTA_ENTIDAD_PERIODO
                                            WHERE
                                                TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0))
                                                BETWEEN TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0
                                                THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END ||
                                                LPAD('01', 2, 0) )
                                                AND TO_NUMBER('$id_anho' || LPAD(TO_NUMBER('$id_mes'), 2, 0))
                                            GROUP BY ID_ANHO, ID_MES
                                            ORDER BY ID_ANHO, ID_MES
                                        )
                                    )
                                GROUP BY ID_ANHO,ID_MES

                                UNION ALL

                                SELECT ID_ANHO,ID_MES,0 AS SALDO1,ROUND((SUM(SALDO)*SUM(IDI)*SUM(WC))/100,2) AS SALDO2
                                FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO,0 AS IDI,
                                        (SELECT cew.VALOR  FROM CONTA_ENTIDAD_WC cew
                                        INNER JOIN CONTA_ENTIDAD ce ON ce.ID_TIPOENTIDAD  = cew.ID_TIPOENTIDAD
                                        WHERE ID_ENTIDAD =$id_entidad AND cew.id_anho = $id_anho) AS WC
                                      FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                            LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                            LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                            LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                            LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                            LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                            LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                            LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                            LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                            LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                            LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                            LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                            FROM (SELECT ID_ANHO,ID_MES,CASE WHEN IDI=0 THEN 0 ELSE ROUND(SALDO/IDI,2) END AS SALDO
                                                FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SALDO, SUM(IDI) AS IDI
                                                    FROM (SELECT ID_ANHO,ID_MES,SUM(COS_VALOR)  AS SALDO,0 AS IDI
                                                        FROM VW_CONTA_DIARIO
                                                        WHERE ID_ENTIDAD =$id_entidad
                                                        AND ID_FONDO =10
                                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                                        AND ID_CUENTAAASI LIKE '41%'
                                                        AND ID_CUENTAAASI NOT LIKE '4191%'
                                                        AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                        TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                        AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                        GROUP BY ID_ANHO,ID_MES
                                                        UNION ALL
                                                        SELECT ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO,0 AS IDI
                                                        FROM VW_CONTA_DIARIO
                                                        WHERE ID_ENTIDAD =$id_entidad
                                                        AND ID_FONDO=25
                                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                                        AND ID_CUENTAAASI LIKE '4123%'
                                                        AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                        TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0
                                                                THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD('01',2,0))
                                                        AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                        GROUP BY ID_ANHO,ID_MES
                                                        UNION ALL
                                                        SELECT TO_NUMBER(TO_CHAR(FECHA, 'yyyy')) AS ID_ANHO,TO_NUMBER(TO_CHAR(FECHA, 'mm')) AS ID_MES,0 AS SALDO, COS_VENTA AS IDI
                                                        FROM TIPO_CAMBIO
                                                        WHERE TO_CHAR(FECHA, 'yyyy') BETWEEN TO_NUMBER('$id_anho')-1 AND TO_NUMBER('$id_anho')
                                                        AND ID_MONEDA = 20)
                                                    GROUP BY ID_ANHO,ID_MES))
                                            GROUP BY ID_ANHO,ID_MES)
                                            $query_where2
                                    UNION ALL
                                    SELECT TO_NUMBER(TO_CHAR(FECHA, 'yyyy')) AS ID_ANHO,TO_NUMBER(TO_CHAR(FECHA, 'mm')) AS ID_MES,0 AS SALDO, COS_VENTA AS IDI,0 AS WC
                                    FROM TIPO_CAMBIO
                                    WHERE TO_CHAR(FECHA, 'yyyy') BETWEEN TO_NUMBER('$id_anho')-1 AND TO_NUMBER('$id_anho')
                                    AND ID_MONEDA = 20)
                                    GROUP BY ID_ANHO,ID_MES)
                                GROUP BY ID_ANHO,ID_MES)
                            GROUP BY ID_ANHO,ID_MES
                            ORDER BY ID_ANHO,ID_MES)
                        WHERE TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                        AND TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))<=TO_NUMBER(TO_NUMBER('$id_anho') || LPAD(TO_NUMBER('$id_mes'),2,0))
            ";
        } elseif ($id_anho > 2020) {
            $query_capoperative = "SELECT 3 AS ID_GRUPO,'CAPITAL OPERATIVO' AS GRUPO,
                                   LPAD(ID_MES,2,'0')||'/'||SUBSTR(ID_ANHO,3,4) AS CAT1,ID_ANHO1 AS CAT2,ID_MES1 AS CAT3,SALDO,VARIACION
                                   FROM (SELECT ID_ANHO,ID_MES,ID_ANHO AS ID_ANHO1,ID_MES AS ID_MES1,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                                         FROM (SELECT ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2)*100,2) END AS SALDO
                                               FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SALDO1,0 AS SALDO2
                                                     FROM (SELECT ID_ANHO,ID_MES,SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                                           FROM VW_CONTA_DIARIO
                                                           WHERE ID_ENTIDAD =$id_entidad
                                                           AND ID_FONDO = 10
                                                           AND ID_TIPOASIENTO NOT IN ('EA')
                                                           AND SUBSTR(ID_CUENTAAASI,0,2) IN (11,21)
                                                           AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                           TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                                                                TO_NUMBER('$id_anho')-1
                                                                ELSE TO_NUMBER('$id_anho') END || LPAD('01',2,0)
                                                           )
                                                           AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                           GROUP BY ID_ANHO,ID_MES
                                                           UNION ALL
                                                           SELECT ID_ANHO, ID_MES,
                                                           CASE WHEN ID_ANHO <=2020 THEN
                                                            CASE WHEN SALDO <0 THEN 0 ELSE -SALDO END
                                                           ELSE
                                                            SALDO
                                                           END AS SALDO
                                                           FROM (SELECT ID_ANHO, ID_MES,
                                                                 FC_SALDO_FONDO_ASIGNADO($id_entidad,ID_ANHO ,ID_MES,10) AS SALDO
                                                                 FROM CONTA_ENTIDAD_PERIODO
                                                                 WHERE TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0))
                                                                 BETWEEN TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                                                                                        TO_NUMBER('$id_anho')-1
                                                                                   ELSE
                                                                                        TO_NUMBER('$id_anho') END || LPAD('01', 2, 0)
                                                                         )
                                                                 AND TO_NUMBER('$id_anho' || LPAD(TO_NUMBER('$id_mes'), 2, 0))
                                                                 GROUP BY ID_ANHO, ID_MES
                                                                 ORDER BY ID_ANHO, ID_MES
                                                                )
                                                          )
                                                     GROUP BY ID_ANHO,ID_MES

                                                     UNION ALL

                                                     SELECT ID_ANHO, ID_MES,SALDO1,((SALDO2*WC)/100) AS SALDO2
                                                     FROM (SELECT ID_ANHO,ID_MES,0 AS SALDO1,ROUND(SUM(SALDO),2) AS SALDO2,
                                                            (SELECT cew.VALOR  FROM CONTA_ENTIDAD_WC cew
                                                                            INNER JOIN CONTA_ENTIDAD ce ON ce.ID_TIPOENTIDAD  = cew.ID_TIPOENTIDAD
                                                                            WHERE ID_ENTIDAD =$id_entidad AND cew.ID_ANHO = $id_anho) AS WC

                                                            FROM (SELECT ID_ANHO,ID_MES, SALDO
                                                                FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                                        FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                                            LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                                            LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                                            LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                                            LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                                            LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                                            LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                                            LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                                            LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                                            LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                                            LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                                            LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                                            FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                                    FROM (SELECT GAOP.ID_ANHO,GAOP.ID_MES, SUM(GAOP.SALDO) AS SALDO
                                                                                        FROM (SELECT ID_ANHO,ID_MES,SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI LIKE '41%'
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                UNION ALL
                                                                                                SELECT ID_ANHO,ID_MES, -1*SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI LIKE '419%'
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                UNION ALL
                                                                                                SELECT ID_ANHO,ID_MES, -1*SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI LIKE '4123%'
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                )GAOP
                                                                                        GROUP BY GAOP.ID_ANHO,GAOP.ID_MES

                                                                                        UNION ALL

                                                                                        SELECT ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO
                                                                                        FROM VW_CONTA_DIARIO
                                                                                        WHERE ID_ENTIDAD =$id_entidad
                                                                                        AND ID_FONDO IN (10,25)
                                                                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                                                                        AND ID_CUENTAAASI LIKE '4123%'
                                                                                        AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                        TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                        AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                        GROUP BY ID_ANHO,ID_MES
                                                                                        )
                                                                                    GROUP BY ID_ANHO,ID_MES
                                                                                    )
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            )
                                                                        $query_where2
                                                                        )GODA

                                                                UNION ALL
                                                                    ----subvenciones netas otros fondos (suma los doce ultimos meses y si sale negativo su saldo sera = 0)
                                                                SELECT ID_ANHO,ID_MES, CASE WHEN SALDO <0 THEN 0 ELSE SALDO END SALDO
                                                                FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                                        FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                                            LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                                            LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                                            LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                                            LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                                            LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                                            LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                                            LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                                            LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                                            LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                                            LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                                            LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                                            FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                                    FROM (SELECT SN.ID_ANHO,SN.ID_MES, SUM(SN.SALDO) AS SALDO
                                                                                        FROM(SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI LIKE '419%'
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                UNION ALL
                                                                                                SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI LIKE '319%'
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                            )SN
                                                                                        GROUP BY SN.ID_ANHO,SN.ID_MES
                                                                                        )
                                                                                    GROUP BY ID_ANHO,ID_MES
                                                                                    )
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            )
                                                                        $query_where2
                                                                        )SN1

                                                                UNION ALL
                                                                    --TRANS netas otros fondos (suma los doce ultimos meses y si sale negativo su saldo sera = 0)
                                                                SELECT ID_ANHO,ID_MES, CASE WHEN SALDO <0 THEN 0 ELSE SALDO END SALDO
                                                                FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                                        FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                                            LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                                            LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                                            LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                                            LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                                            LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                                            LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                                            LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                                            LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                                            LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                                            LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                                            LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                                            FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                                    FROM (SELECT TNOF.ID_ANHO,TNOF.ID_MES, SUM(TNOF.SALDO) AS SALDO
                                                                                        FROM(SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI = 6400001
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                UNION ALL
                                                                                                SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                                                FROM VW_CONTA_DIARIO_ALL
                                                                                                WHERE ID_ENTIDAD =$id_entidad
                                                                                                AND ID_FONDO =10
                                                                                                AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                                                AND ID_CUENTAAASI = 6300001
                                                                                                AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                                                TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                                                AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                                                GROUP BY ID_ANHO,ID_MES
                                                                                                )TNOF
                                                                                        GROUP BY TNOF.ID_ANHO,TNOF.ID_MES
                                                                                        )
                                                                                    GROUP BY ID_ANHO,ID_MES
                                                                                    )
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            )
                                                                        $query_where2
                                                                        )TNOF1
                                                                )
                                                            GROUP BY ID_ANHO,ID_MES
                                                          )


                                                    )
                                                    GROUP BY ID_ANHO,ID_MES
                                              )
                                            GROUP BY ID_ANHO,ID_MES
                                            ORDER BY ID_ANHO,ID_MES)
                                        WHERE TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                                        AND TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))<=TO_NUMBER(TO_NUMBER('$id_anho') || LPAD(TO_NUMBER('$id_mes'),2,0))";
        }


        $query = "SELECT 1 AS ID_GRUPO,(SELECT DISTINCT LISTAGG(NOMBRE, ' y ') WITHIN GROUP (ORDER BY ID_CUENTAAASI) over () FROM CONTA_CTA_DENOMINACIONAL WHERE ID_CUENTAAASI IN (3110000)) AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (SELECT ID_ANHO,ID_MES,-SUM(COS_VALOR) AS SALDO
                        FROM VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD =$id_entidad
                            AND ID_TIPOASIENTO NOT IN ('EA')
                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (311)
                            $query_where1_1
                        GROUP BY ID_ANHO,ID_MES
                        ORDER BY ID_ANHO,ID_MES)
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))

                UNION ALL

                $query_capoperative

                UNION ALL

                SELECT 4 AS ID_GRUPO,'CAPITAL OPERATIVO (meses)' AS GRUPO,
                LPAD(ID_MES,2,'0')||'/'||SUBSTR(ID_ANHO,3,4) AS CAT1,ID_ANHO1 AS CAT2,ID_MES1 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO,ID_MES,ID_ANHO AS ID_ANHO1,ID_MES AS ID_MES1,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                        FROM (SELECT ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND(SUM(SALDO1)/SUM(SALDO2)*12,2) END AS SALDO
                            FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SALDO1,0 AS SALDO2
                                    FROM (SELECT ID_ANHO,ID_MES,SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                                        FROM VW_CONTA_DIARIO
                                        WHERE ID_ENTIDAD =$id_entidad
                                        AND ID_FONDO = 10
                                        AND ID_TIPOASIENTO NOT IN ('EA')
                                        AND SUBSTR(ID_CUENTAAASI,0,2) IN (11,21)
                                        AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                        TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                                            TO_NUMBER('$id_anho')-1
                                            ELSE TO_NUMBER('$id_anho') END || LPAD('01',2,0)
                                        )
                                        AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                        GROUP BY ID_ANHO,ID_MES
                                        UNION ALL
                                        SELECT ID_ANHO, ID_MES,
                                        CASE WHEN ID_ANHO <=2020 THEN
                                        CASE WHEN SALDO <0 THEN 0 ELSE -SALDO END
                                        ELSE
                                        SALDO
                                        END AS SALDO
                                        FROM (SELECT ID_ANHO, ID_MES,
                                                FC_SALDO_FONDO_ASIGNADO($id_entidad,ID_ANHO ,ID_MES,10) AS SALDO
                                                FROM CONTA_ENTIDAD_PERIODO
                                                WHERE TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0))
                                                BETWEEN TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN
                                                                    TO_NUMBER('$id_anho')-1
                                                                ELSE
                                                                    TO_NUMBER('$id_anho') END || LPAD('01', 2, 0)
                                                        )
                                                AND TO_NUMBER('$id_anho' || LPAD(TO_NUMBER('$id_mes'), 2, 0))
                                                GROUP BY ID_ANHO, ID_MES
                                                ORDER BY ID_ANHO, ID_MES
                                            )
                                        )
                                    GROUP BY ID_ANHO,ID_MES

                                    UNION ALL

                                    SELECT ID_ANHO, ID_MES,SALDO1,SALDO2 AS SALDO2
                                    FROM (SELECT ID_ANHO,ID_MES,0 AS SALDO1,ROUND(SUM(SALDO),2) AS SALDO2
                                        FROM (SELECT ID_ANHO,ID_MES, SALDO
                                            FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                    FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                        LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                        LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                        LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                        LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                        LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                        LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                        LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                        LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                        LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                        LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                        LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                        FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                FROM (SELECT GAOP.ID_ANHO,GAOP.ID_MES, SUM(GAOP.SALDO) AS SALDO
                                                                    FROM (SELECT ID_ANHO,ID_MES,SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI LIKE '41%'
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            UNION ALL
                                                                            SELECT ID_ANHO,ID_MES, -1*SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI LIKE '419%'
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            UNION ALL
                                                                            SELECT ID_ANHO,ID_MES, -1*SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI LIKE '4123%'
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            )GAOP
                                                                    GROUP BY GAOP.ID_ANHO,GAOP.ID_MES

                                                                    UNION ALL

                                                                    SELECT ID_ANHO,ID_MES,SUM(COS_VALOR) AS SALDO
                                                                    FROM VW_CONTA_DIARIO
                                                                    WHERE ID_ENTIDAD =$id_entidad
                                                                    AND ID_FONDO IN (10,25)
                                                                    AND ID_TIPOASIENTO NOT IN ('EA')
                                                                    AND ID_CUENTAAASI LIKE '4123%'
                                                                    AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                    TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                    AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                    GROUP BY ID_ANHO,ID_MES
                                                                    )
                                                                GROUP BY ID_ANHO,ID_MES
                                                                )
                                                        GROUP BY ID_ANHO,ID_MES
                                                        )
                                                    $query_where2
                                                    )GODA

                                            UNION ALL
                                                ----subvenciones netas otros fondos (suma los doce ultimos meses y si sale negativo su saldo sera = 0)
                                            SELECT ID_ANHO,ID_MES, CASE WHEN SALDO <0 THEN 0 ELSE SALDO END SALDO
                                            FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                    FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                        LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                        LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                        LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                        LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                        LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                        LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                        LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                        LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                        LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                        LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                        LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                        FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                FROM (SELECT SN.ID_ANHO,SN.ID_MES, SUM(SN.SALDO) AS SALDO
                                                                    FROM(SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI LIKE '419%'
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            UNION ALL
                                                                            SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI LIKE '319%'
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                        )SN
                                                                    GROUP BY SN.ID_ANHO,SN.ID_MES
                                                                    )
                                                                GROUP BY ID_ANHO,ID_MES
                                                                )
                                                        GROUP BY ID_ANHO,ID_MES
                                                        )
                                                    $query_where2
                                                    )SN1

                                            UNION ALL
                                                --TRANS netas otros fondos (suma los doce ultimos meses y si sale negativo su saldo sera = 0)
                                            SELECT ID_ANHO,ID_MES, CASE WHEN SALDO <0 THEN 0 ELSE SALDO END SALDO
                                            FROM (SELECT ID_ANHO,ID_MES,SAL1+SAL2+SAL3+SAL4+SAL5+SAL6+SAL7+SAL8+SAL9+SAL10+SAL11+SAL12 AS SALDO
                                                    FROM (SELECT ID_ANHO,ID_MES,SUM(SALDO) AS SAL1,
                                                        LAG(SUM(SALDO), 1, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL2,
                                                        LAG(SUM(SALDO), 2, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL3,
                                                        LAG(SUM(SALDO), 3, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL4,
                                                        LAG(SUM(SALDO), 4, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL5,
                                                        LAG(SUM(SALDO), 5, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL6,
                                                        LAG(SUM(SALDO), 6, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL7,
                                                        LAG(SUM(SALDO), 7, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL8,
                                                        LAG(SUM(SALDO), 8, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL9,
                                                        LAG(SUM(SALDO), 9, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL10,
                                                        LAG(SUM(SALDO), 10, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL11,
                                                        LAG(SUM(SALDO), 11, 0) OVER (ORDER BY ID_ANHO,ID_MES) AS SAL12
                                                        FROM (SELECT ID_ANHO,ID_MES,ROUND(SUM(SALDO),2) AS SALDO
                                                                FROM (SELECT TNOF.ID_ANHO,TNOF.ID_MES, SUM(TNOF.SALDO) AS SALDO
                                                                    FROM(SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI = 6400001
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            UNION ALL
                                                                            SELECT ID_ANHO,ID_MES, SUM(COS_VALOR)  AS SALDO
                                                                            FROM VW_CONTA_DIARIO_ALL
                                                                            WHERE ID_ENTIDAD =$id_entidad
                                                                            AND ID_FONDO =10
                                                                            AND ID_TIPOASIENTO NOT IN ('BB','EA')
                                                                            AND ID_CUENTAAASI = 6300001
                                                                            AND TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0)) BETWEEN
                                                                            TO_NUMBER(TO_NUMBER('$id_anho')-1 || LPAD('01',2,0))
                                                                            AND TO_NUMBER('$id_anho'||LPAD(TO_NUMBER('$id_mes'),2,0))
                                                                            GROUP BY ID_ANHO,ID_MES
                                                                            )TNOF
                                                                    GROUP BY TNOF.ID_ANHO,TNOF.ID_MES
                                                                    )
                                                                GROUP BY ID_ANHO,ID_MES
                                                                )
                                                        GROUP BY ID_ANHO,ID_MES
                                                        )
                                                    $query_where2
                                                    )TNOF1
                                            )
                                        GROUP BY ID_ANHO,ID_MES
                                        )


                                )
                                GROUP BY ID_ANHO,ID_MES
                            )
                        GROUP BY ID_ANHO,ID_MES
                        ORDER BY ID_ANHO,ID_MES)
                    WHERE TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                    AND TO_NUMBER(ID_ANHO1||LPAD(ID_MES1,2,0))<=TO_NUMBER(TO_NUMBER('$id_anho') || LPAD(TO_NUMBER('$id_mes'),2,0))

                UNION ALL

                SELECT 5 AS ID_GRUPO,'ENDEUDAMIENTO' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO,$query_select AS VARIACION
                    FROM (
                        SELECT ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) END AS SALDO
                        FROM (SELECT ID_ANHO,ID_MES,-SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_ENTIDAD =$id_entidad
                                AND ID_FONDO $id_fondo
                                AND ID_CUENTAAASI LIKE '2%'
                                AND ID_CUENTAAASI NOT LIKE '231%'
                                $query_where1
                            GROUP BY ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ANHO,ID_MES,0 AS SALDO1,SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_ENTIDAD =$id_entidad
                                AND ID_FONDO $id_fondo
                                AND ID_CUENTAAASI LIKE '1%'
                                $query_where1
                            GROUP BY ID_ANHO,ID_MES)
                        GROUP BY ID_ANHO,ID_MES)
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))

                UNION ALL

                SELECT 6 AS ID_GRUPO,'AUTOSUSTENTO' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (
                        SELECT ID_ANHO,ID_MES,CASE WHEN SUM(SALDO2)=0 THEN 0 ELSE ROUND((SUM(SALDO1)/SUM(SALDO2))*100,2) END AS SALDO
                        FROM (SELECT ID_ANHO,ID_MES,-SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO1,0 AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_ENTIDAD =$id_entidad
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=1
                                AND ID_CUENTAAASI LIKE '31%'
                                AND SUBSTR(ID_CUENTAAASI,0,3) NOT IN (313,319)
                                $query_where1
                            GROUP BY ID_ANHO,ID_MES
                            UNION ALL
                            SELECT ID_ANHO,ID_MES,0 AS SALDO1,SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO2
                            FROM VW_CONTA_DIARIO
                            WHERE ID_ENTIDAD =$id_entidad
                                AND ID_TIPOASIENTO NOT IN ('EA')
                                AND SUBSTR(ID_FONDO,0,1)=1
                                AND ID_CUENTAAASI LIKE '41%'
                                AND SUBSTR(ID_CUENTAAASI,0,4) NOT IN (4191)
                                $query_where1
                            GROUP BY ID_ANHO,ID_MES)
                        GROUP BY ID_ANHO,ID_MES)
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                UNION ALL
                SELECT 12 AS ID_GRUPO,'ANÁLISIS DE GASTOS ACUMULADOS' AS GRUPO,A.NOMBRE AS CAT1,
                    B.CAT2,B.CAT3,B.SALDO,B.VARIACION
                FROM CONTA_CTA_DENOMINACIONAL A
                INNER JOIN (SELECT ID_CUENTAAASI AS CAT1,ID_ANHO AS CAT2,12 AS CAT3,SUM(COS_VALOR)AS SALDO,
                        ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_CUENTAAASI ORDER BY ID_CUENTAAASI,ID_ANHO))=0
                        THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_CUENTAAASI ORDER BY ID_CUENTAAASI,ID_ANHO))<0 AND SUM(COS_VALOR)>=0
                        THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_CUENTAAASI ORDER BY ID_CUENTAAASI,ID_ANHO))-1)
                        ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (PARTITION BY ID_CUENTAAASI ORDER BY ID_CUENTAAASI,ID_ANHO))-1 END *100,2) AS VARIACION
                    FROM (SELECT SUBSTR(ID_CUENTAAASI,0,3) AS ID_CUENTAAASI,ID_ANHO,SUM(COS_VALOR)  AS COS_VALOR
                        FROM VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD =$id_entidad
                            AND ID_FONDO $id_fondo
                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (411,412)
                            AND ID_TIPOASIENTO NOT IN ('EA')
                            AND ID_ANHO BETWEEN  TO_NUMBER('$id_anho')-5 AND TO_NUMBER('$id_anho')
                        GROUP BY SUBSTR(ID_CUENTAAASI,0,3),ID_ANHO
                        ORDER BY SUBSTR(ID_CUENTAAASI,0,3),ID_ANHO)
                    GROUP BY ID_CUENTAAASI,ID_ANHO
                    ORDER BY ID_ANHO,ID_CUENTAAASI) B ON A.ID_CUENTAAASI=RPAD(B.CAT1,7,0)
                UNION ALL
                SELECT 12 AS ID_GRUPO,'ANÁLISIS DE GASTOS ACUMULADOS' AS GRUPO,'APOYO MISION' AS CAT1,
                    ID_ANHO AS CAT2,12 AS CAT3,SUM(COS_VALOR)AS SALDO,
                        ROUND(CASE WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (ORDER BY ID_ANHO))=0
                        THEN 0 WHEN (LAG(SUM(COS_VALOR), 1, NULL) OVER (ORDER BY ID_ANHO))<0 AND SUM(COS_VALOR)>=0
                        THEN -(SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (ORDER BY ID_ANHO))-1)
                        ELSE SUM(COS_VALOR)/(LAG(SUM(COS_VALOR), 1, NULL) OVER (ORDER BY ID_ANHO))-1 END *100,2) AS VARIACION
                    FROM (SELECT ID_ANHO,SUM(COS_VALOR)  AS COS_VALOR
                        FROM VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD =$id_entidad
                            AND ID_FONDO $id_fondo
                            AND ID_TIPOASIENTO NOT IN ('EA')
                            AND SUBSTR(ID_CUENTAAASI,0,3) IN (414,419)
                            AND ID_ANHO BETWEEN  TO_NUMBER('$id_anho')-5 AND TO_NUMBER('$id_anho')
                        GROUP BY ID_ANHO
                        ORDER BY ID_ANHO)
                    GROUP BY ID_ANHO

                UNION ALL

                SELECT 8 AS ID_GRUPO,'DIEZMO - NO ASIGNADO' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (
                        SELECT vcd.ID_ANHO, vcd.ID_MES,
				            TO_CHAR(NVL(sum(
                                    (
                                        SELECT NVL((-1*SUM(COS_VALOR)),0) FROM
                                        VW_CONTA_DIARIO_ALL
                                        WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                        AND ID_FONDO = vcd.ID_FONDO
                                        AND ID_DEPTO = vcd.ID_DEPTO
                                        AND ID_CUENTAAASI LIKE '3%'
                                        AND ID_TIPOASIENTO != 'EA'
                                    ) -
                                    (
                                        SELECT NVL((SUM(COS_VALOR)),0) FROM
                                        VW_CONTA_DIARIO_ALL
                                        WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                        AND ID_FONDO = vcd.ID_FONDO
                                        AND ID_DEPTO = vcd.ID_DEPTO
                                        AND ID_CUENTAAASI LIKE '4%'
                                        AND ID_TIPOASIENTO != 'EA'
                                    )
                                        -
                                    (
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                                VW_CONTA_DIARIO_ALL
                                                WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                                AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                                AND ID_FONDO = vcd.ID_FONDO
                                                AND ID_DEPTO = vcd.ID_DEPTO
                                                AND ID_CUENTAAASI IN (6100001)
                                                AND ID_TIPOASIENTO != 'EA'
                                        )
                                        +
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                                VW_CONTA_DIARIO_ALL
                                                WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                                AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                                AND ID_FONDO = vcd.ID_FONDO
                                                AND ID_DEPTO = vcd.ID_DEPTO
                                                AND ID_CUENTAAASI IN (6200001)
                                                AND ID_CTACTE !='118222'
                                                AND ID_TIPOASIENTO = 'IN'
                                                AND ID_TIPOASIENTO != 'EA'
                                        )
                                        +			            +
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                                VW_CONTA_DIARIO_ALL
                                                WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                                AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                                AND ID_FONDO = vcd.ID_FONDO
                                                AND ID_DEPTO = vcd.ID_DEPTO
                                                AND ID_CUENTAAASI IN (6200001)
                                                AND ID_CTACTE ='118222'
                                                AND ID_TIPOASIENTO != 'EA'
                                        )

                                    )
                                        -
                                    (
                                        SELECT NVL((SUM(COS_VALOR)),0) FROM
                                        VW_CONTA_DIARIO_ALL
                                        WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                        AND ID_FONDO = vcd.ID_FONDO
                                        AND ID_DEPTO = vcd.ID_DEPTO
                                        AND ID_CUENTAAASI IN (6300001,6400001)
                                        AND ID_TIPOASIENTO != 'EA'
                                    )
                                    -
                                    (

                                        (SELECT NVL((SUM(COS_VALOR)),0)
                                        FROM VW_CONTA_DIARIO
                                        WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO
                                        AND ID_MES <= vcd.ID_MES
                                        AND ID_FONDO = vcd.ID_FONDO
                                        AND ID_TIPOASIENTO = 'BB'
                                        AND ID_DEPTO = vcd.ID_DEPTO
                                        )
                                    )
                                    ),0), 'fm999999999990.00') AS SALDO
                        FROM (SELECT ID_ANHO, ID_MES, ID_ENTIDAD,ID_DEPTO,ID_FONDO
                              FROM VW_CONTA_DIARIO
                              WHERE ID_ENTIDAD = $id_entidad AND ID_FONDO $id_fondo
                                    AND ID_DEPTO = '0001'
                                $query_where1
                              GROUP BY ID_ANHO, ID_MES, ID_ENTIDAD,ID_DEPTO,ID_FONDO
                            ) vcd
                        GROUP BY vcd.ID_ANHO, vcd.ID_MES
                        ORDER BY vcd.ID_ANHO, vcd.ID_MES
                        )
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))

                UNION ALL

                SELECT 9 AS ID_GRUPO,'NO DIEZMO - NO ASIGNADO' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (SELECT vcd.ID_ANHO, vcd.ID_MES,
				            TO_CHAR(NVL(sum(
                                    (
                                            SELECT NVL((-1*SUM(COS_VALOR)),0) FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI LIKE '3%'
                                            AND ID_TIPOASIENTO != 'EA'
                                    ) -
                                    (
                                            SELECT NVL((SUM(COS_VALOR)),0) FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI LIKE '4%'
                                            AND ID_TIPOASIENTO != 'EA'
                                    )
                                        -
                                    (
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                            AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI IN (6100001)
                                            AND ID_TIPOASIENTO != 'EA'
                                        )
                                        +
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                            AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI IN (6200001)
                                            AND ID_CTACTE !='118222'
                                            AND ID_TIPOASIENTO = 'IN'
                                            AND ID_TIPOASIENTO != 'EA'
                                        )
                                        +			            +
                                        (SELECT  NVL((SUM(COS_VALOR)),0)  FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD
                                            AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <=  vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI IN (6200001)
                                            AND ID_CTACTE ='118222'
                                            AND ID_TIPOASIENTO != 'EA'
                                        )
                                    )
                                        -
                                    (
                                            SELECT NVL((SUM(COS_VALOR)),0) FROM
                                            VW_CONTA_DIARIO_ALL
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_CUENTAAASI IN (6300001,6400001)
                                            AND ID_TIPOASIENTO != 'EA'
                                    )
                                    -
                                    (
                                            SELECT NVL((SUM(COS_VALOR)),0)
                                            FROM VW_CONTA_DIARIO
                                            WHERE ID_ENTIDAD = vcd.ID_ENTIDAD AND ID_ANHO = vcd.ID_ANHO  AND ID_MES <= vcd.ID_MES
                                            AND ID_FONDO = vcd.ID_FONDO
                                            AND ID_DEPTO = vcd.ID_DEPTO
                                            AND ID_TIPOASIENTO = 'BB'
                                    )
                                    ),0), 'fm999999999990.00') AS SALDO
                        FROM (SELECT
                                    ID_ANHO, ID_MES, ID_ENTIDAD,ID_DEPTO,ID_FONDO
                                FROM VW_CONTA_DIARIO
                                WHERE
                                    ID_ENTIDAD = $id_entidad
                                    AND ID_FONDO $id_fondo
                                    AND ID_DEPTO = '0002'
                                $query_where1
                                GROUP BY ID_ANHO, ID_MES, ID_ENTIDAD,ID_DEPTO,ID_FONDO
                            ) vcd
                        GROUP BY vcd.ID_ANHO, vcd.ID_MES
                        ORDER BY vcd.ID_ANHO, vcd.ID_MES
                        )
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))

                UNION ALL

                SELECT 10 AS ID_GRUPO,'FONDOS ASIGNADOS' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (SELECT
                                ID_ANHO, ID_MES,
                                FC_SALDO_FONDO_ASIGNADO($id_entidad,ID_ANHO ,ID_MES,10)
                                AS SALDO
                            FROM
                                CONTA_ENTIDAD_PERIODO
                            WHERE
                                TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0))
                                BETWEEN TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0
                                THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END ||
                                LPAD('01', 2, 0) )
                                AND TO_NUMBER('$id_anho' || LPAD(TO_NUMBER('$id_mes'), 2, 0))
                            GROUP BY ID_ANHO, ID_MES
                            ORDER BY ID_ANHO, ID_MES
                        )
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))

                UNION ALL

                SELECT 11 AS ID_GRUPO,'RESERVA INMOVILIZADO' AS GRUPO,
                LPAD(CAT2,2,'0')||'/'||SUBSTR(CAT1,3,4) AS CAT1,CAT3 AS CAT2,CAT4 AS CAT3,SALDO,VARIACION
                FROM (SELECT ID_ANHO AS CAT1,ID_MES AS CAT2,ID_ANHO AS CAT3,ID_MES AS CAT4,SUM(SALDO)AS SALDO, $query_select AS VARIACION
                    FROM (SELECT ID_ANHO,ID_MES,-SUM(SUM(COS_VALOR)) OVER (PARTITION BY ID_ANHO ORDER BY MIN(ID_MES)) AS SALDO
                        FROM VW_CONTA_DIARIO
                        WHERE ID_ENTIDAD =$id_entidad
                            AND ID_DEPTO='910111'
                            AND SUBSTR(ID_FONDO,0,1)=2
                            AND ID_TIPOASIENTO NOT IN ('EA')
                            AND (ID_CUENTAAASI LIKE '2317%' OR
                            ID_CUENTAAASI IN (SELECT DISTINCT ID_CUENTAAASI
                            FROM CONTA_CTA_DENOMINACIONAL
                            START WITH ID_CUENTAAASI=98
                            CONNECT BY PRIOR ID_CUENTAAASI = ID_PARENT))
                            $query_where1
                        GROUP BY ID_ANHO,ID_MES
                        ORDER BY ID_ANHO,ID_MES)
                        $query_where2
                    GROUP BY ID_ANHO,ID_MES
                    ORDER BY ID_ANHO,ID_MES)
                WHERE TO_NUMBER(CAT3||LPAD(CAT4,2,0))>TO_NUMBER(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN TO_NUMBER('$id_anho')-1 ELSE TO_NUMBER('$id_anho') END || LPAD(CASE WHEN TO_NUMBER('$id_mes')-5<0 THEN 12-(-(TO_NUMBER('$id_mes')-5)) ELSE TO_NUMBER('$id_mes')-5 END,2,0))
                ORDER BY ID_GRUPO,CAT2,CAT1";
                // echo($query);
                $oQuery = DB::select($query);
            $result=[];
            $data_new=(object)array();
            $data_old=(object)array('id_grupo'=>0);
            $children=[];
            $categories=[];
            $series=[];
            $series_data=[];
            $series_data_spline=[];
            $i=0;
            foreach ($oQuery as $key => $value){
                $data_new=$value;
                //Es un grafico distinto a los demas con el id_grupo 6
                if($data_old->id_grupo==='12' and $data_new->cat2!==$data_old->cat2){
                $series[]=(object)array('type'=>'column','name'=>$data_old->cat2,'data'=>$series_data);
                $series_data=[];
                }
                if($data_new->id_grupo!==$data_old->id_grupo and $i>0){
                if($data_old->id_grupo!=='12'){
                    $series[]=(object)array('type'=>'column','name'=>'Monto','data'=>$series_data, 'color'=>'#023246');
                    $series[]=(object)array('type'=>'spline','name'=>'Variación','yAxis'=>1,'data'=>$series_data_spline, 'color'=>'#009245');
                    $result[]=(object)array('id'=>$data_old->id_grupo,'name'=>$data_old->grupo,'saldo'=>floatval($data_old->saldo),'variacion'=>floatval($data_old->variacion),'categories'=>$categories,'series'=>$series,'color'=>'#023246');
                }else{
                    $cats=array_unique($categories);
                    $result[]=(object)array('id'=>$data_old->id_grupo,'name'=>$data_old->grupo,'categories'=>$cats,'series'=>$series);
                }
                $series=[];
                $categories=[];
                $series_data=[];
                $series_data_spline=[];
                }

                $categories[]=$value->cat1;
                $series_data[]=floatval($value->saldo);
                $series_data_spline[]=floatval($value->variacion);

                if($i===count($oQuery)-1){
                    //Es un grafico distinto a los demas con el id_grupo 6
                    if($value->id_grupo==='12'){
                        $series[]=(object)array('type'=>'column','name'=>$value->cat2,'data'=>$series_data);
                    }
                        //$series_data=[];
                    if($value->id_grupo!=='12'){
                        $series[]=(object)array('type'=>'column','name'=>'Monto','data'=>$series_data, 'color'=>'#023246');
                        $series[]=(object)array('type'=>'spline','name'=>'Variación','yAxis'=>1,'data'=>$series_data_spline, 'color'=>'#009245');
                        $result[]=(object)array('id'=>$value->id_grupo,'name'=>$value->grupo,'saldo'=>floatval($value->saldo),'variacion'=>floatval($value->variacion),'categories'=>$categories,'series'=>$series);
                    }else{
                        $cats=array_unique($categories);
                        $result[]=(object)array('id'=>$value->id_grupo,'name'=>$value->grupo,'categories'=>$cats,'series'=>$series);
                    }

                }
            $data_old=$value;
            $i++;
        }

        return $result;
    }


    public static function getIncomeDetail($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $depto = $request->deptos;
        $type = $request->type;
        $pageSize = $request->pageSizeIn;
        $page = $request->pageIn;
        $from = 1;
        $to = ($pageSize * $page) - ($pageSize);
        $addSumDep="";
        $addDep_="";
        $addSumMes="";
        $addMes_="";
        $saldo_anterior = 0;
        $sqlSaldo="";


        if($depto !=='null' AND $depto!==null and $depto !=='*'){
            $addSumDep= " AND CD.ID_DEPTO IN (".$depto.")";
            $addDep_= " AND ID_DEPTO IN (".$depto.")";
        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*'){
            $addSumMes = " AND CD.ID_MES = $id_mes ";
            $addMes_ = " AND ID_MES = $id_mes ";
        }



        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*' AND  $id_mes > 1) {
            $sqlSaldo="
            (SELECT NVL(SUM(CD.DEBE - CD.HABER), 0)
            FROM  VW_CONTA_DIARIO_ALL CD
            WHERE CD.ID_CUENTAAASI LIKE '3%' AND CD.ID_ENTIDAD = ".$id_entidad." AND CD.ID_ANHO = ".$id_anho." AND CD.ID_MES <  ".$id_mes." ".$addSumDep." AND CD.ID_TIPOASIENTO != 'EA') AS SALDO_ANT_MENSUAL
             ";
        } else {
            $sqlSaldo=" 0 AS SALDO_ANT_MENSUAL ";
        }


        $query_total = "SELECT count(*) as total
        FROM VW_CONTA_DIARIO_ALL CD
        INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
        WHERE CD.ID_CUENTAAASI like '3%' AND CD.ID_ENTIDAD = ".$id_entidad."
        AND CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND CD.ID_TIPOASIENTO != 'EA' ORDER BY CD.FEC_ASIENTO ASC,  CC.NOMBRE ASC";

        $exe_q_total = DB::select($query_total);
        $total = $exe_q_total[0]->total;


        if ($type == 'pdf'){
            $limit = 0;
            $offset = $total;
        }else{
            $limit = ($page - 1) * $pageSize + 1;
            $offset = $page * $pageSize;
        }

        $query= "SELECT item.* FROM (SELECT row_number() over (ORDER BY ING.FEC_ASIENTO ASC, ING.CUENTA ASC, ING.CODIGO ASC) line_number,
                                        ING.ID_MES,ING.T_DEBE, ING.T_HABER,ING.MES,ING.ID_ENTIDAD,ING.FEC_ASIENTO,ING.CODIGO,
                                        ING.CUENTA, ING.GLOSA, ING.DEBE, ING.HABER, ING.SALDO, ING.SALDO_ANT_MENSUAL
                                    FROM (SELECT CD.ID_MES,TOTAL_DEBE_HABER.T_DEBE,
                                            TOTAL_DEBE_HABER.T_HABER,
                                            to_char((CD.FEC_ASIENTO), 'Month','nls_date_language=spanish') as MES,
                                            CD.ID_ENTIDAD,CD.FEC_ASIENTO, CD.CODIGO, CC.NOMBRE AS CUENTA, CD.COMENTARIO AS GLOSA,
                                            TO_CHAR(CD.DEBE, 'fm999999999990.00') AS DEBE,
                                            TO_CHAR(CD.HABER, 'fm999999999990.00') AS HABER,
                                            TO_CHAR(NVL((CD.DEBE - CD.HABER), 0), 'fm999999999990.00') AS SALDO,
                                            $sqlSaldo
                                        FROM VW_CONTA_DIARIO_ALL CD
                                        INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
                                        INNER JOIN (SELECT ID_MES, SUM(DEBE) AS T_DEBE, SUM(HABER) AS T_HABER FROM VW_CONTA_DIARIO
                                                        WHERE ID_CUENTAAASI like '3%' AND ID_ENTIDAD = ".$id_entidad." AND ID_ANHO = ".$id_anho."
                                                        ".$addDep_." ".$addMes_." AND ID_TIPOASIENTO != 'EA' GROUP BY ID_MES)TOTAL_DEBE_HABER
                                        ON TOTAL_DEBE_HABER.ID_MES = CD.ID_MES
                                        WHERE CD.ID_CUENTAAASI like '3%' AND CD.ID_ENTIDAD = ".$id_entidad." AND
                                        CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND
                                        CD.ID_TIPOASIENTO != 'EA'

                                        UNION ALL

                                        SELECT CD.ID_MES,TOTAL_DEBE_HABER.T_DEBE,
                                            TOTAL_DEBE_HABER.T_HABER,
                                            to_char((CD.FEC_ASIENTO), 'Month','nls_date_language=spanish') as MES,
                                            CD.ID_ENTIDAD,CD.FEC_ASIENTO, CD.CODIGO, CC.NOMBRE AS CUENTA, CD.COMENTARIO AS GLOSA,
                                            TO_CHAR(CD.DEBE, 'fm999999999990.00') AS DEBE,
                                            TO_CHAR(CD.HABER, 'fm999999999990.00') AS HABER,
                                            TO_CHAR(NVL((CD.DEBE - CD.HABER), 0), 'fm999999999990.00') AS SALDO,
                                            $sqlSaldo
                                        FROM VW_CONTA_DIARIO_ALL CD
                                        INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
                                        INNER JOIN (SELECT ID_MES, SUM(DEBE) AS T_DEBE, SUM(HABER) AS T_HABER FROM VW_CONTA_DIARIO
                                                        WHERE ID_CUENTAAASI = 6100001 AND ID_ENTIDAD = ".$id_entidad." AND ID_ANHO = ".$id_anho."
                                                        ".$addDep_." ".$addMes_." AND ID_TIPOASIENTO != 'IN' GROUP BY ID_MES)TOTAL_DEBE_HABER
                                        ON TOTAL_DEBE_HABER.ID_MES = CD.ID_MES
                                        WHERE CD.ID_CUENTAAASI = 6100001 AND CD.ID_ENTIDAD = ".$id_entidad." AND
                                        CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND
                                        CD.ID_TIPOASIENTO != 'IN'
                                        )ING
                                    )item where  item.line_number BETWEEN $limit AND $offset";
        // print($query);
        $executeQuery = DB::select($query);

        if($page > 1){
            $querySum = "SELECT SUM(ditem.DEBE - ditem.HABER) as saldo_anterior
                        FROM (SELECT ING1.DEBE, ING1.HABER, row_number() over (ORDER BY ING1.FEC_ASIENTO ASC,  ING1.NOMBRE ASC, ING1.CODIGO ASC) line_number
                            FROM (
                            SELECT CD.DEBE, CD.HABER, CD.FEC_ASIENTO,  CC.NOMBRE, CD.CODIGO
                            FROM VW_CONTA_DIARIO_ALL CD
                            INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
                            WHERE CD.ID_CUENTAAASI like '3%' AND
                            CD.ID_ENTIDAD = ".$id_entidad." AND
                            CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND
                            CD.ID_TIPOASIENTO != 'EA'
                            UNION ALL

                            SELECT CD.DEBE, CD.HABER, CD.FEC_ASIENTO,  CC.NOMBRE, CD.CODIGO
                            FROM VW_CONTA_DIARIO_ALL CD
                            INNER JOIN CONTA_CTA_DENOMINACIONAL CC ON CC.ID_CUENTAAASI = CD.ID_CUENTAAASI
                            WHERE CD.ID_CUENTAAASI = 6100001 AND
                            CD.ID_ENTIDAD = ".$id_entidad." AND
                            CD.ID_ANHO = ".$id_anho."  ".$addSumDep." ".$addSumMes." AND
                            CD.ID_TIPOASIENTO != 'IN'
                            )ING1) ditem where  ditem.line_number BETWEEN $from AND $to";
            // print($querySum);
            $oQuery = DB::select($querySum);
            $saldo_anterior = $oQuery[0]->saldo_anterior;
        }

        $i= 0;

        foreach ($executeQuery as $item) {
            if($i == 0){
                $m_monto = $item->saldo + $saldo_anterior + $item->saldo_ant_mensual;
                $executeQuery[$i]->saldo = number_format($m_monto,2,'.','');
             } else  {
                $m_monto = $executeQuery[$i-1]->saldo + $item->debe - $item->haber;
                $executeQuery[$i]->saldo = number_format($m_monto,2,'.','');
             }
             $i++;
        }

        if($total> 0  and $offset > $total AND $type != 'pdf'){
            $executeQuery[] = (object) array(
                'line_number'=> $total + 1,
                'id_mes'=> $executeQuery[$i -1]->id_mes,
                't_debe'=> "",
                't_haber'=> "",
                'mes'=>  "",
                'id_entidad'=> $executeQuery[$i -1]->id_entidad,
                'fec_asiento'=> $executeQuery[$i -1]->fec_asiento,
                'codigo'=> "",
                'cuenta'=> "::Saldo Final",
                'glosa'=> "::Saldo Final",
                'debe'=> "",
                'haber'=> "",
                'saldo'=> $executeQuery[$i -1]->saldo,
            );
        }
       $array_resultante = (object) array(
        'current_page' => $page,
        'data' => $executeQuery,
        'from' => $limit,
        'per_page' => $pageSize,
        'to' => $offset,
        'total' => $total,
        );
        return $array_resultante;
    }

    public static function getBudgetBalanceSummary($request) {
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_depto = $request->deptos;
        $fondo = $request->fondo;
        $addDepto = '';
        $addFondo = '';
        $addFondo_ = '';
        $addSql = '';
        $addMes = '';
        $addMess = '';
        $addMes_ = '';
        $addMes_p = '';
        $addMesP = '';
        $idMes = 12;
        $depto_pa = 'Todos los departamentos';
        $r_depto = '';
        $type = $request->type;
        $pageSize = $request->pageSize;
        $page = $request->page;
        $addprinid_ctacte = '';

        if($id_depto !== 'null' and $id_depto !== null and $id_depto !== '*'){
            $addDepto =  " AND  X.ID_DEPTO IN (" . $id_depto.")";
            $r_depto = " X.ID_CTACTE AS ID_DEPTO_PA, ELISEO.FC_NAMESDEPTO(".$id_entidad.", X.ID_CTACTE) AS DEPTO_PA, ";
            //$id_depto =  " AND  X.image.png IN (" . $id_depto.")";

            if ($id_depto == '118231' || $id_depto == '118232' || $id_depto == '118233') {
                $addprinid_ctacte = " AND X.ID_CTACTE IN ('0001','0002') ";
            } else {
                $addprinid_ctacte = " AND X.ID_CTACTE = '0001' ";
            }
        } else {
            $id_depto =  " AND  X.ID_DEPTO IS NOT NULL";
            $r_depto = " '*' AS ID_DEPTO_PA, 'Todos los departamentos' AS DEPTO_PA, ";
            $addprinid_ctacte = " AND X.ID_CTACTE = '0001' ";
        }

        if($id_mes !=='null' AND $id_mes!==null AND $id_mes!=='*'){
            $addMes = " AND X.ID_MES = $id_mes ";
            $addMess = " AND X.ID_MES <= $id_mes ";
            $addMes_ = " AND ID_MES = $id_mes ";
            $idMes = $id_mes;
            $addMes_p = " AND ID_MES < $id_mes ";
            $addMesP =  " AND  ID_MES <= " . $id_mes;
        } else {
            $idMes = 12;
        }

        if($fondo > 0){
            $addFondo =  " AND ID_FONDO =  " . $fondo;
            $addFondo_ =  " AND X.ID_FONDO =  " . $fondo;

        }

        $query_total = "SELECT COUNT(*) as total FROM (SELECT
                        X.ID_DEPTO
                        FROM CONTA_PRESUPUESTO X
                        WHERE X.ID_ENTIDAD = " .$id_entidad ." ".$addDepto."  AND
                        X.ID_ANHO = " .$id_anho. " ".$addMes."  ".$addFondo_." AND
                        X.ID_DEPTO not in ('0000','0002','909211')
                        GROUP BY X.ID_DEPTO ORDER BY ID_DEPTO)";
        // print($query_total);
        $exe_q_total = DB::select($query_total);
        $total = $exe_q_total[0]->total;


        if ($type == 'pdf'){
            $limit = 0;
            $offset = $total;
        }else{
            $limit = ($page - 1) * $pageSize + 1;
            $offset = $page * $pageSize;
        }

        if($id_mes !=='null' AND $id_mes!==null and $id_mes !=='*' AND  $id_mes > 1){
            $addSql = "
                TO_CHAR((
                (SELECT NVL(SUM(COS_VALOR), 0)
                FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . "
                ".$addMes_p." ".$addFondo."
                --AND ID_CUENTAAASI like '4%'
                AND ID_CTACTE = X.ID_CTACTE
                AND ID_DEPTO = X.ID_DEPTO)
                +
                (NVL(-1*(

                        (SELECT NVL(SUM(COS_VALOR),0) AS SALDO FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_p."
                        ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI LIKE '3%'
                        AND ID_TIPOASIENTO != 'EA')

                        +
                        (SELECT NVL(SUM(COS_VALOR),0) AS SALDO FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_p."  ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI = '6100001'
                        AND ID_TIPOASIENTO != 'IN')


                        ),0)

                )
                +
                (NVL((SELECT NVL((-1*SUM(COS_VALOR)), 0) FROM
                VW_CONTA_DIARIO_ALL
                WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_p."
                 ".$addFondo." --AND ID_CTACTE = X.ID_CTACTE
                 AND ID_DEPTO = X.ID_DEPTO
                 AND ID_CUENTAAASI LIKE '4%'
                 AND ID_TIPOASIENTO != 'EA'), 0))
                ), 'fm999999999990.00') as SALDO_ANTERIOR,
                TO_CHAR((0), 'fm999999999990.00') as SALDO_ANTERIOR_INI,
                ";
        } else {
            $addSql = "
            TO_CHAR((0), 'fm999999999990.00') as SALDO_ANTERIOR,
            TO_CHAR((0), 'fm999999999990.00') as SALDO_ANTERIOR_INI, ";
        }


        $query = "SELECT
                        ITEM.*,
                        TO_CHAR((ITEM.SALDO_ANTERIOR + ITEM.PTO_GASTO + ITEM.EJE_INGRESOS + ITEM.EJE_GASTOS), 'fm999999999990.00') AS SALDO,
                        (CASE WHEN (ITEM.EJE_GASTOS_ACT != 0 AND ITEM.PTO_GASTO_ANUAL != 0) THEN
                            TO_CHAR((((-1*ITEM.EJE_GASTOS_ACT) * 100) / ITEM.PTO_GASTO_ANUAL), 'fm999999999990.00')
                        ELSE
                        TO_CHAR(0, 'fm999999999990.00')
                        END) AS PORCENTAJE
                FROM (
                    SELECT row_number() over (ORDER BY X.ID_DEPTO) line_number,
                    X.ID_DEPTO,
                    CED.NOMBRE AS DEPTO,
                    X.ID_CTACTE,
                    FC_OBT_RESP_ENTIDAD_DEPTO(" . $id_entidad . ", X.ID_DEPTO, ".$idMes.", " . $id_anho . ") AS RESPONSABLE,
                    ".$r_depto."
                    ".$addSql."
                    TO_CHAR(NVL((SELECT (SUM(COS_VALOR))
                    FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . " ".$addMes_." ".$addFondo."
                    AND ID_DEPTO not in ('0000','0002','909211')
                    --AND ID_CUENTAAASI like '4%'
                    AND ID_CTACTE = X.ID_CTACTE
                    AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO,
                    TO_CHAR(NVL((SELECT (SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                    WHERE ID_ENTIDAD = " . $id_entidad . "
                    AND  ID_ANHO = " . $id_anho . " ".$addFondo."
                    --AND ID_CUENTAAASI like '4%'
                    AND ID_CTACTE = X.ID_CTACTE
                    AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO_ANUAL,
                    TO_CHAR(NVL(-1*(

                        (SELECT NVL(SUM(COS_VALOR),0) AS SALDO FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."  ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI LIKE '3%'
                        AND ID_TIPOASIENTO != 'EA')

                        +
                        (SELECT NVL(SUM(COS_VALOR),0) AS SALDO FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."  ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI = '6100001'
                        AND ID_TIPOASIENTO != 'IN')


                        ),0), 'fm999999999990.00') AS EJE_INGRESOS,
                    TO_CHAR(NVL((

                        (SELECT NVL((SUM(COS_VALOR)),0) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."
                        ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI LIKE '3%'
                        AND ID_TIPOASIENTO != 'EA')
                        +

                        (SELECT NVL((SUM(COS_VALOR)), 0) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."
                        ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI = '6100001'
                        AND ID_TIPOASIENTO != 'IN')

                            ),0), 'fm999999999990.00') AS EJE_INGRESOS_ACT,
	                TO_CHAR(NVL((SELECT NVL((-1*SUM(COS_VALOR)), 0) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."
                         ".$addFondo." --AND ID_CTACTE = X.ID_CTACTE
                         AND ID_DEPTO = X.ID_DEPTO
                         AND ID_CUENTAAASI LIKE '4%'
                         AND ID_TIPOASIENTO != 'EA'), 0), 'fm999999999990.00') AS EJE_GASTOS,
                    TO_CHAR(NVL((SELECT NVL((-1*SUM(COS_VALOR)),0) FROM
                        VW_CONTA_DIARIO_ALL
                        WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."
                        ".$addFondo."
                        --AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO
                        AND ID_CUENTAAASI LIKE '4%'
                        AND ID_TIPOASIENTO != 'EA'), 0), 'fm999999999990.00') AS EJE_GASTOS_ACT
                FROM VW_CONTA_DIARIO_ALL X
                INNER JOIN CONTA_ENTIDAD_DEPTO CED ON (CED.ID_ENTIDAD = X.ID_ENTIDAD AND CED.ID_DEPTO = X.ID_DEPTO)
                WHERE X.ID_ENTIDAD = " .$id_entidad ." ".$addDepto."  AND X.ID_ANHO = " .$id_anho. " ".$addMess."
                 ".$addFondo_."  ".$addprinid_ctacte." GROUP BY X.ID_DEPTO, CED.NOMBRE,X.ID_CTACTE ) ITEM
                 WHERE ITEM.line_number BETWEEN $limit AND $offset";
        // print($query);
        $oQuery = DB::select($query);

        if($total> 0  and $offset > $total AND $type != 'pdf'){
            $queryTotal = "SELECT
                            SUM(TG.SALDO_ANTERIOR) as SALDO_ANTERIOR,
                            SUM(TG.PTO_GASTO) as PTO_GASTO, SUM(TG.EJE_INGRESOS) as EJE_INGRESOS,
                            SUM(TG.EJE_GASTOS) as EJE_GASTOS, SUM(TG.SALDO) as SALDO
                    FROM (SELECT
                            ITEM.*,
                            TO_CHAR((ITEM.SALDO_ANTERIOR + ITEM.PTO_GASTO + ITEM.EJE_INGRESOS + ITEM.EJE_GASTOS), 'fm999999999990.00') AS SALDO,
                            (CASE WHEN (ITEM.EJE_GASTOS_ACT != 0 AND ITEM.PTO_GASTO_ANUAL != 0) THEN
                            TO_CHAR((((-1*ITEM.EJE_GASTOS_ACT) * 100) / ITEM.PTO_GASTO_ANUAL), 'fm999999999990.00')
                            ELSE
                            TO_CHAR(0, 'fm999999999990.00')
                            END) AS PORCENTAJE
                    FROM (
                    SELECT
                        X.ID_DEPTO,
                        CED.NOMBRE AS DEPTO,
                        ".$r_depto."
                        ".$addSql."
                        TO_CHAR(NVL((SELECT (SUM(COS_VALOR))
                        FROM CONTA_PRESUPUESTO WHERE ID_ENTIDAD = " . $id_entidad . " AND  ID_ANHO = " . $id_anho . " ".$addMes_." ".$addFondo."
                        AND ID_DEPTO not in ('0000','0001','0002','909211')
                        --AND ID_CUENTAAASI like '4%'
                        AND ID_CTACTE = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO,
                        TO_CHAR(NVL((SELECT (SUM(COS_VALOR)) from CONTA_PRESUPUESTO
                        WHERE ID_ENTIDAD = " . $id_entidad . "
                        AND  ID_ANHO = " . $id_anho . " ".$addFondo."
                        --AND ID_CUENTAAASI like '4%'
                        AND ID_DEPTO = X.ID_CTACTE
                        AND ID_DEPTO = X.ID_DEPTO),0), 'fm999999999990.00') AS PTO_GASTO_ANUAL,
                        TO_CHAR(NVL(-1*(SELECT SUM(COS_VALOR) FROM
                            VW_CONTA_DIARIO_ALL
                            WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."
                            ".$addFondo." --AND ID_DEPTO = X.ID_CTACTE
                            AND ID_DEPTO = X.ID_DEPTO
                            AND ID_CUENTAAASI LIKE '3%'
                            AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_INGRESOS,
                        TO_CHAR(NVL(-1*(SELECT (SUM(COS_VALOR)) FROM
                            VW_CONTA_DIARIO_ALL
                            WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."
                            ".$addFondo." --AND ID_DEPTO = X.ID_CTACTE
                            AND ID_DEPTO = X.ID_DEPTO
                            AND ID_CUENTAAASI LIKE '3%'
                            AND ID_TIPOASIENTO != 'EA'),0), 'fm999999999990.00') AS EJE_INGRESOS_ACT,

                            TO_CHAR(NVL((SELECT NVL((-1*SUM(COS_VALOR)),0) FROM
                            VW_CONTA_DIARIO_ALL
                            WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMes_."
                            ".$addFondo." --AND ID_DEPTO = X.ID_CTACTE
                            AND ID_DEPTO = X.ID_DEPTO
                            AND ID_CUENTAAASI LIKE '4%'
                            AND ID_TIPOASIENTO != 'EA'), 0), 'fm999999999990.00') AS EJE_GASTOS,

                        TO_CHAR(NVL((SELECT NVL((-1*SUM(COS_VALOR)),0) FROM
                            VW_CONTA_DIARIO_ALL
                            WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_ANHO = " . $id_anho . " ".$addMesP."
                            ".$addFondo." --AND ID_DEPTO = X.ID_CTACTE
                            AND ID_DEPTO = X.ID_DEPTO
                            AND ID_CUENTAAASI LIKE '4%'
                            AND ID_TIPOASIENTO != 'EA'), 0), 'fm999999999990.00') AS EJE_GASTOS_ACT
                        FROM VW_CONTA_DIARIO_ALL X
                        INNER JOIN CONTA_ENTIDAD_DEPTO CED ON (CED.ID_ENTIDAD = X.ID_ENTIDAD AND CED.ID_DEPTO = X.ID_DEPTO)
                        WHERE X.ID_ENTIDAD = " .$id_entidad ." ".$addDepto."  AND X.ID_ANHO = " .$id_anho. " ".$addMess."
                        ".$addFondo_." ".$addprinid_ctacte." GROUP BY X.ID_DEPTO, CED.NOMBRE,X.ID_CTACTE ORDER BY X.ID_DEPTO,X.ID_CTACTE) ITEM) TG";
                //print('estoy aqui');
                $oQueryTotal = DB::select($queryTotal);
                $oQuery[] = (object) array(
                    'line_number' => $total + 1,
                    'id_depto' => '',
                    'depto' => 'Totales',
                    'id_depto_pa' => '',
                    'depto_pa' => '',
                    'saldo_anterior' => $oQueryTotal[0]->saldo_anterior,
                    'pto_gasto' => $oQueryTotal[0]->pto_gasto,
                    'eje_ingresos' => $oQueryTotal[0]->eje_ingresos,
                    'eje_gastos' => $oQueryTotal[0]->eje_gastos,
                    'saldo' => $oQueryTotal[0]->saldo,
                    'porcentaje' => ''
                );
        }


        $array_resultante = (object) array(
            'current_page' => $page,
            'data' => $oQuery,
            'from' => $limit,
            'per_page' => $pageSize,
            'to' => $offset,
            'total' => $total,
        );
        return $array_resultante;
    }

    public static function getDeptoEntityGroup($request)
    {
        $id_entidad = $request->id_entidad;
        $id_grupo = $request->id_grupo;

        $query = DB::table('CONTA_ENTIDAD_DEPTO_GRUPO_C A')
        ->join('CONTA_ENTIDAD_GRUPO B', 'A.ID_GRUPO', '=', DB::raw("B.ID_GRUPO AND B.ID_ENTIDAD=A.ID_ENTIDAD AND B.OPCION= 'contabilidad'"))
        ->join('CONTA_ENTIDAD_DEPTO C', 'A.ID_DEPTO', '=', DB::raw("C.ID_DEPTO AND C.ID_ENTIDAD=A.ID_ENTIDAD"))
        ->select(
            'A.ID_ENTIDAD',
            'A.ID_DEPTO',
            DB::raw('C.NOMBRE AS DEPTO'),
            DB::raw("A.ID_DEPTO ||'-'|| C.NOMBRE AS NAME"),
            'A.ID_GRUPO',
            DB::raw("B.NOMBRE AS GRUPO")
            )
        ->where('A.ID_ENTIDAD',$id_entidad)
        ->where('A.ID_GRUPO', $id_grupo)
        ->get();
        $deptos = [];
        foreach($query as $item){
            $deptos [] = (object) array(
                        'id_depto' => $item->id_depto,
                        'depto' => $item->depto,
                        'name' => $item->name,
                            );
        }

        return $deptos;
    }

    public static function getEntityDeptoGroup($request) {
        $id_entidad = $request->id_entidad;

        $query = DB::table('CONTA_ENTIDAD_DEPTO_GRUPO_C A')
        ->join('CONTA_ENTIDAD_GRUPO B', 'A.ID_GRUPO', '=', DB::raw("B.ID_GRUPO AND B.ID_ENTIDAD=A.ID_ENTIDAD AND B.OPCION= 'contabilidad'"))
        ->join('CONTA_ENTIDAD_DEPTO C', 'A.ID_DEPTO', '=', DB::raw("C.ID_DEPTO AND C.ID_ENTIDAD=A.ID_ENTIDAD"))
        ->select(
            'A.ID_ENTIDAD',
            'A.ID_DEPTO',
            DB::raw('C.NOMBRE AS DEPTO'),
            'A.ID_GRUPO',
            DB::raw("B.NOMBRE AS GRUPO")
            )
        ->where('A.ID_ENTIDAD',$id_entidad);
        // print($query->toSql());
        $query = $query->get();


        return $query;
    }
    public static function addEntityDeptoGroup($request) {
        $res="OK";
        $deptos = $request->deptos;
        $id_entidad = $request->id_entidad;
        $id_grupo = $request->id_grupo;

        $count = DB::table('CONTA_ENTIDAD_DEPTO_GRUPO_C')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_GRUPO', $id_grupo)
        ->count();

        if($count > 0){
            $res = 'error';
        } else {
            foreach($deptos as $item){
                DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_GRUPO_C')
                ->insert(
                    array(
                        'ID_ENTIDAD' => $id_entidad,
                        'ID_DEPTO' => $item['id_depto'],
                        'ID_GRUPO'=> $id_grupo,
                        )
                );
            }
        }
        return $res;
    }

    public static function editEntityDeptoGroup($id_grupo, $request) {
        $res="OK";
        $deptos = $request->deptos;
        $id_entidad = $request->id_entidad;

        DB::table('CONTA_ENTIDAD_DEPTO_GRUPO_C')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_GRUPO', $id_grupo)
        ->delete();

        foreach($deptos as $item){
            DB::table('ELISEO.CONTA_ENTIDAD_DEPTO_GRUPO_C')
            ->insert(
                array(
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $item['id_depto'],
                    'ID_GRUPO'=> $id_grupo,
                    )
            );
        }

        return $res;
    }

    public static function deleteEntityDeptoGroup($id_grupo, $request) {
        $res="OK";
        $id_entidad = $request->id_entidad;
        // print('ok'.$id_entidad);
        DB::table('CONTA_ENTIDAD_DEPTO_GRUPO_C')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_GRUPO', $id_grupo)
        ->delete();
        return $res;
    }

    public static function getEntityGroup($request)
    {
        $id_entidad = $request->id_entidad;
        $opcion = $request->opcion;

        $query = DB::table('CONTA_ENTIDAD_GRUPO A')
        ->select(
            'A.ID_ENTIDAD',
            'A.ID_GRUPO',
            DB::raw("A.NOMBRE AS GRUPO"),
            DB::raw("A.OPCION AS OPCION"),
            DB::raw("(CASE WHEN (SELECT count(1)
            FROM ELISEO.CONTA_ENTIDAD_DEPTO_GRUPO_C cedg
            WHERE cedg.ID_GRUPO = A.ID_GRUPO)>0 THEN 1
            ELSE 0 END) AS disabled"),
            'COLOR'
            )
        ->where('A.ID_ENTIDAD',$id_entidad)
        ->where('A.OPCION',$opcion);
        // print($query->toSql());
        $query=$query->get();
        return $query;
    }


    public static function addEntityGroup($request) {
        $res="OK";
        $id_entidad = $request->id_entidad;
        $grupo = $request->grupo;
        $opcion = $request->opcion;
        $color = $request->color;

        DB::table('ELISEO.CONTA_ENTIDAD_GRUPO')
        ->insert(
            array(
                'ID_ENTIDAD' => $id_entidad,
                'NOMBRE' => $grupo,
                'OPCION' => $opcion,
                'COLOR' => $color,
                )
        );

        $query = DB::table('ELISEO.CONTA_ENTIDAD_GRUPO')
        ->select(
            'ID_ENTIDAD',
            'ID_GRUPO',
            DB::raw("NOMBRE AS GRUPO"),
            DB::raw("OPCION AS OPCION"),
            'COLOR'
            )
        ->where('ID_ENTIDAD',$id_entidad)
        ->where('NOMBRE',$grupo)
        ->where('OPCION',$opcion)
        ->first();

        return $query;
    }

    public static function editEntityGroup($id_grupo, $request) {
        $res="OK";
        $id_entidad = $request->id_entidad;
        $grupo = $request->grupo;
        $opcion = $request->opcion;
        $color = $request->color;
        $id_grupo = $request->id_grupo;

        DB::table('ELISEO.CONTA_ENTIDAD_GRUPO')
        ->where(array(
            'ID_GRUPO' => $id_grupo,
        ))
        ->update(
            array(
                'ID_ENTIDAD' => $id_entidad,
                'NOMBRE' => $grupo,
                'COLOR' => $color,
                )
        );
        return $res;
    }

    public static function deleteEntityGroup($id_grupo, $request) {
        $res="OK";
        $id_entidad = $request->id_entidad;
        $id_grupo = $request->id_grupo;
        DB::table('ELISEO.CONTA_ENTIDAD_GRUPO')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_GRUPO', $id_grupo)
        ->delete();
        return $res;
    }

    public static function getCorporateIncomeExpenses($request) {
        $data=[];
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;


        if($id_anho===null or $id_anho==='*'){
            $id_anho=" IS NOT NULL";
        }else{
            $id_anho=" = $id_anho";
        }
        if($id_mes===null or $id_mes==='*'){
            $id_mes="<=12";
        }else{
            $id_mes=" <= $id_mes";
        }


        $query="SELECT x.*, CASE WHEN x.item = 1 THEN 'fa-heartbeat'
        WHEN x.item = 2 THEN 'fa-user-circle-o'
        WHEN x.item = 3 THEN 'fa-money'
        WHEN x.item = 4 THEN 'fa-line-chart'
        END AS ICON from (
        SELECT 1 item, 'Ingreso Neto' AS cuenta, -SUM(COS_VALOR) AS SALDO
        FROM VW_CONTA_DIARIO WHERE
        ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes AND ID_FONDO IS NOT NULL
        AND ID_TIPOASIENTO <> 'EA'
        AND SUBSTR(ID_CUENTAAASI,0,1) = 3

        UNION ALL

        SELECT 2 item, 'Gastos de Personal' AS cuenta, SUM(COS_VALOR) AS SALDO
        FROM VW_CONTA_DIARIO WHERE
        ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes AND ID_FONDO IS NOT NULL
        AND ID_TIPOASIENTO <> 'EA'
        AND SUBSTR(ID_CUENTAAASI,0,3) = 411

        UNION ALL

        SELECT 3 item, 'Gastos Administrativos' AS cuenta, SUM(COS_VALOR) AS SALDO
        FROM VW_CONTA_DIARIO WHERE
        ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes AND ID_FONDO IS NOT NULL
        AND ID_TIPOASIENTO <> 'EA'
        AND SUBSTR(ID_CUENTAAASI,0,2) = 73

        UNION all

        SELECT 4 item, 'Resultado del Ejercicio' AS cuenta, -SUM(COS_VALOR) AS SALDO
        FROM VW_CONTA_DIARIO WHERE
        ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes AND ID_FONDO IS NOT NULL
        AND ID_TIPOASIENTO <> 'EA'
        AND SUBSTR(ID_CUENTAAASI,0,1) IN (3,4,6))x
        ";
        // echo $query;
        $income_expenses = DB::select($query);
        //var_dump($query);



        $query="SELECT Y.*, case when ((Y.SALDO * 100)/Y.TOTAL) > 0 then
        ((Y.SALDO * 100)/Y.TOTAL)
        else -((Y.SALDO * 100)/Y.TOTAL) end as porcentaje
         FROM (SELECT ID_GRUPO, GRUPO, sum(SALDO) AS SALDO,
        (SELECT  -SUM(COS_VALOR) AS SALDO
        FROM VW_CONTA_DIARIO WHERE
        ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes AND ID_FONDO IS NOT NULL
        AND ID_TIPOASIENTO <> 'EA'
        AND SUBSTR(ID_CUENTAAASI,0,1) = 3

        ) AS TOTAL
        FROM (
            SELECT 1 item, 'Ingreso Neto' AS cuenta, vceg.ID_GRUPO, vceg.GRUPO,
             -nvl(SUM(vcd.COS_VALOR),0) AS SALDO
            FROM VW_CONTA_ENTIDAD_GRUPO vceg
            LEFT JOIN (SELECT ID_EMPRESA,ID_ENTIDAD, COS_VALOR FROM  VW_CONTA_DIARIO WHERE
            ID_DEPTO IS NOT NULL AND ID_ANHO $id_anho AND ID_MES $id_mes
			AND ID_FONDO IS NOT NULL AND ID_TIPOASIENTO <> 'EA'
            AND SUBSTR(ID_CUENTAAASI,0,1) = 3)vcd
			ON vceg.ID_EMPRESA = vcd.ID_EMPRESA AND vceg.ID_ENTIDAD = vcd.ID_ENTIDAD
			GROUP BY vceg.ID_GRUPO, vceg.GRUPO


            )Z GROUP BY ID_GRUPO, GRUPO
            ORDER BY ID_GRUPO)Y";
        // echo($query);
        $results=DB::select($query);
        // var_dump($query);
        $z = 100.0;
        foreach($results as $item){
            $series_data[]= (object) array(
                'name'=> $item->grupo,
                'y'=>  floatval ($item->porcentaje),
                'x'=> number_format((float)$item->saldo, 2),
                'z'=> $z,
                'sliced'=> false,
                'selected'=> false,
            );

            $z = $z + 10.0;


        }
        $series[]=(object)array(
                'minPointSize'=> 60,
                'innerSize'=> '0%',
                'zMin'=> 0,
                'name'=>'Utilidad','data'=>$series_data);

        $cash_flow=null;
        $change_equity=null;
        $data['income_expenses']=$income_expenses;
        $data['series']=$series;
        return $data;
    }


    public static function getYearMonthControll() {
        $query="SELECT * FROM CONTA_ANHO ca
        WHERE ca.ID_ANHO >= (SELECT MIN(ID_ANHO) FROM ARCHIVO_MENSUAL)";
        $results=DB::select($query);
        return $results;
    }

    public static function cvMonthControll($request) {

        //print_r($request->items);
        $ids_archivo_mensual = $request->ids_archivo_mensual;
        $id_mes_ini = $request->mes_ini;
        $id_mes_fin = $request->mes_fin;
        $id_anho = $request->id_anho;
        $items = $request->items;

        $error = 0;
        $msg_error = str_repeat("0", 200);
        $pdo = DB::connection()->getPdo();

        $pdo = DB::getPdo();
        DB::beginTransaction();
        foreach ($items as $key => $value) {
            // print($value['id_archivo_mensual']);

            try {
                $stmt = $pdo->prepare("BEGIN ELISEO.PKG_MANAGEMENT_REPORTS.SP_CVP_SETTING_CTRL_MENSUAL(
                :P_ID_ARCHIVO_MENSUAL,
                :P_ID_MES_INI,
                :P_ID_MES_FIN,
                :P_ID_ANHO,
                :P_FECHA_LIMITE,
                :P_ERROR,
                :P_MSGERROR
                );
                END;"
                );

                $stmt->bindParam(':P_ID_ARCHIVO_MENSUAL', $value['id_archivo_mensual'], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MES_INI', $value['mes_ini'], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES_FIN', $value['mes_fin'], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $value['id_anho'], PDO::PARAM_INT);
                $stmt->bindParam(':P_FECHA_LIMITE', $value['fecha_limite'], PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error >= 1){
                    DB::rollback();
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollback();
                $error = 1;
                $msg_error=$e;
            }
        }
        $result = [
            'error' => $error,
            'message' => $msg_error
        ];

        return $result;
    }

    public static function listDepartments($entidades, $id_persona, $withAllOoption){
        // $query = "SELECT null as id_entidad, '*' AS id_depto, ' Todos' AS Nombre, 0 as ESTADO FROM dual
        //           WHERE (SELECT count(*) FROM CONTA_ENTIDAD_DEPTO CED INNER JOIN CONTA_ENTIDAD CE ON CE.ID_ENTIDAD = CED.ID_ENTIDAD
        //                 WHERE CED.ID_ENTIDAD IN ($entidades) AND CE.ID_TIPOENTIDAD IN (5,12) AND CED.ES_EMPRESA = '1' )=
        //             (SELECT count(*)
        //                     FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD B, LAMB_USERS_DEPTO C
        //                     WHERE A.ID_ENTIDAD = C.ID_ENTIDAD
        //                     AND A.ID_DEPTO = C.ID_DEPTO
        //                     AND A.ID_ENTIDAD = B.ID_ENTIDAD
        //                     AND C.ID_ENTIDAD IN ($entidades)
        //                     AND C.ID = $id_persona
        //                     AND B.ID_TIPOENTIDAD IN (5,12)
        //                     AND ES_EMPRESA = '1'
        //                     AND C.ACTIVO = '1')
        //             AND (SELECT count(*) FROM CONTA_ENTIDAD_DEPTO CED INNER JOIN CONTA_ENTIDAD CE ON CE.ID_ENTIDAD = CED.ID_ENTIDAD
        //                 WHERE CED.ID_ENTIDAD IN ($entidades) AND CE.ID_TIPOENTIDAD IN (5,12) AND CED.ES_EMPRESA = '1' ) >1
        //             and 1 = $withAllOoption
        //           UNION ALL
        //           SELECT
        //                     A.ID_ENTIDAD, A.ID_DEPTO, A.NOMBRE, C.ESTADO
        //                     FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD B, LAMB_USERS_DEPTO C
        //                     WHERE A.ID_ENTIDAD = C.ID_ENTIDAD
        //                     AND A.ID_DEPTO = C.ID_DEPTO
        //                     AND A.ID_ENTIDAD = B.ID_ENTIDAD
        //                     AND C.ID_ENTIDAD IN ($entidades)
        //                     AND B.ID_TIPOENTIDAD IN (5,12)
        //                     AND C.ID = $id_persona
        //                     AND ES_EMPRESA = '1'
        //                     AND C.ACTIVO = '1'
        //         UNION ALL
        //         SELECT A.ID_ENTIDAD, A.ID_DEPTO, A.NOMBRE, C.ESTADO
        //             FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD B, LAMB_USERS_DEPTO C
        //             WHERE A.ID_ENTIDAD = C.ID_ENTIDAD
        //                 AND A.ID_DEPTO = C.ID_DEPTO
        //                 AND A.ID_DEPTO = '0'
        //                 AND A.ID_ENTIDAD = B.ID_ENTIDAD
        //                 AND C.ID_ENTIDAD IN ($entidades)
        //             --	AND B.ID_TIPOENTIDAD IN (5,12)
        //             --	AND C.ID = $id_persona
        //                 AND ES_EMPRESA = '1'
        //                 AND C.ACTIVO = '1'
        //                 AND ROWNUM = 1
        //         ORDER BY ID_ENTIDAD, ID_DEPTO, NOMBRE";

        $query = "
            SELECT id_entidad, entidad, id_depto, nombre, estado
            FROM (
                SELECT id_entidad, entidad, id_depto, nombre, estado, ROW_NUMBER() OVER (PARTITION BY id_entidad, id_depto, nombre ORDER BY estado) as rn
                FROM (
                    SELECT NULL AS id_entidad, NULL AS entidad, '*' AS id_depto, ' Todos' AS Nombre, 0 as ESTADO
                    FROM dual
                    WHERE (SELECT count(*)
                        FROM CONTA_ENTIDAD_DEPTO CED
                        INNER JOIN CONTA_ENTIDAD CE ON CE.ID_ENTIDAD = CED.ID_ENTIDAD
                        WHERE CED.ID_ENTIDAD IN ($entidades) AND CE.ID_TIPOENTIDAD IN (5,12) AND CED.ES_EMPRESA = '1') =
                        (SELECT count(*)
                        FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD B, LAMB_USERS_DEPTO C
                        WHERE A.ID_ENTIDAD = C.ID_ENTIDAD
                            AND A.ID_DEPTO = C.ID_DEPTO
                            AND A.ID_ENTIDAD = B.ID_ENTIDAD
                            AND C.ID_ENTIDAD IN ($entidades)
                            AND C.ID = $id_persona
                            AND B.ID_TIPOENTIDAD IN (5,12)
                            AND ES_EMPRESA = '1'
                            AND C.ACTIVO = '1')
                    AND (SELECT count(*)
                        FROM CONTA_ENTIDAD_DEPTO CED
                        INNER JOIN CONTA_ENTIDAD CE ON CE.ID_ENTIDAD = CED.ID_ENTIDAD
                        WHERE CED.ID_ENTIDAD IN ($entidades)
                            AND CE.ID_TIPOENTIDAD IN (5,12)
                            AND CED.ES_EMPRESA = '1' ) > 1
                    AND 1 = $withAllOoption

                    UNION ALL

                    SELECT A.ID_ENTIDAD, B.NOMBRE AS ENTIDAD, A.ID_DEPTO, A.NOMBRE, C.ESTADO
                    FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD B, LAMB_USERS_DEPTO C
                    WHERE A.ID_ENTIDAD = C.ID_ENTIDAD
                    AND A.ID_DEPTO = C.ID_DEPTO
                    AND A.ID_ENTIDAD = B.ID_ENTIDAD
                    AND C.ID_ENTIDAD IN ($entidades)
                    AND B.ID_TIPOENTIDAD IN (5,12)
                    AND C.ID = $id_persona
                    AND ES_EMPRESA = '1'
                    AND C.ACTIVO = '1'

                    UNION ALL

                    SELECT * FROM (
                        WITH RankedResults AS (
                            SELECT A.ID_ENTIDAD,
                                    B.NOMBRE AS ENTIDAD,
                                    A.ID_DEPTO,
                                    A.NOMBRE,
                                    C.ESTADO,
                                    ROW_NUMBER() OVER (PARTITION BY A.ID_ENTIDAD ORDER BY C.ESTADO DESC) AS rn
                                FROM CONTA_ENTIDAD_DEPTO A
                                JOIN CONTA_ENTIDAD B ON A.ID_ENTIDAD = B.ID_ENTIDAD
                                JOIN LAMB_USERS_DEPTO C ON A.ID_ENTIDAD = C.ID_ENTIDAD
                                                        AND A.ID_DEPTO = C.ID_DEPTO
                            WHERE A.ID_DEPTO = '0'
                                AND C.ID_ENTIDAD IN ($entidades)
                                AND ES_EMPRESA = '1'
                                AND C.ACTIVO = '1'
                            )
                            SELECT ID_ENTIDAD, ENTIDAD, ID_DEPTO, NOMBRE, ESTADO
                            FROM RankedResults
                            WHERE rn = 1
                    )

                    ORDER BY ID_ENTIDAD, ID_DEPTO, NOMBRE
                )
            )
            WHERE rn = 1
        ";

        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function filter_by_value ($array, $index, $value){
        $newarray = [];
        if(is_array($array) && count($array)>0)
        {
            foreach(array_keys($array) as $key){
                $data_object_array = json_decode(json_encode($array[$key]), true);
                $temp[$key] = $data_object_array[$index]; //  $array[$key]["$index"];

                if ($temp[$key] == $value){
                    $newarray[$key] = $array[$key];
                }
            }
        }
      return $newarray;
    }

    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            // print_r(json_decode(json_encode($val), true));
            $data_object_array = json_decode(json_encode($val), true);
            if (!in_array( $data_object_array[$key], $key_array)) {

                //$key_array[$i] = $val[$key];
                $key_array[$i] = $data_object_array[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function getMonthlyControlSummaryRanking($request) {
        $result = [];
        $data_new=null;
        // $id_categoria = $request->id_categoria;
        $id_categorias = $request->id_categorias;
        $id_empresa = $request->id_empresa;
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $allMonths=false;
        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){

            $sqlGA = " ";
            $sqlGA1 = " ";
            $id_grupoarchivo=" IS NOT NULL";

        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
            $sqlGA = "Q.ID_GRUPOARCHIVO, Q.GRUPOARCHIVO, ";
            $sqlGA1 = ",Q.ID_GRUPOARCHIVO, Q.GRUPOARCHIVO ";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        /* if($id_categoria===null or $id_categoria==='*'){
            $id_categoria=" IS NOT NULL";
        }else{
            $id_categoria=" = $id_categoria";
        } */
        if($id_categorias===null or $id_categorias==='*'){
            $id_categorias=" IS NOT NULL";
        }else{
            $id_categorias=" IN ($id_categorias)";
        }
        if($id_mes===null or $id_mes==='*'){
            $allMonths=true;
            $id_mes=' <=12 ';
        }else{
            $id_mes=" = $id_mes";
        }

        $sqlCategoria = "SELECT vcec.ID_CATEGORIA,vcec.CATEGORIA,vcec.COLOR_BASE,vcec.COLOR_SECUNDARIO
        FROM VW_CONTA_ENTIDAD_CATEGORIA vcec WHERE vcec.ID_CATEGORIA $id_categorias
        GROUP BY ID_CATEGORIA, vcec.CATEGORIA,vcec.COLOR_BASE,vcec.COLOR_SECUNDARIO";
        $oCategoria = DB::select($sqlCategoria);

        $query="SELECT T1.*,T2.NOMBRE,T2.URL,T2.FECHA_CREACION,T2.FECHA_MODIFICACION,
                CASE WHEN T2.FECHA_MODIFICACION IS NOT NULL AND T2.NOMBRE IS NOT NULL THEN
                        CASE WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=0 THEN '3'
                        WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')<0
                        AND  TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=-1 THEN '1'
                        ELSE '0' END
                    WHEN T2.NOMBRE IS NULL AND TO_DATE(SYSDATE) - TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY')>0 THEN '0' ELSE 'N' END AS PUNTAJE,
                    3 AS PTS_GENERAL
                FROM
                (SELECT DISTINCT VWCEC.ID_CATEGORIA, VWCEC.CATEGORIA, A.ID_EMPRESA,
                A.NOM_EMPRESA,A.ID_ENTIDAD,A.NOMBRE AS NOM_ENTIDAD,C.ID_ARCHIVO_MENSUAL,
                E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,
                D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO,C.FECHA_LIMITE
                FROM VW_CONTA_ENTIDAD A
                INNER JOIN (SELECT * FROM ARCHIVO_MENSUAL WHERE ACTIVO = '1') C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
                INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
                INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
                INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC
                ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
                WHERE C.ID_ANHO=$id_anho
                AND  C.ID_MES  $id_mes
                --AND C.ID_DEPTO IS NULL
                AND D.ID_GRUPOARCHIVO  $id_grupoarchivo) T1
                LEFT JOIN ARCHIVO_MENSUAL_DETALLE T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
                WHERE T1.ID_ENTIDAD NOT IN (9119,17125,17123)
                AND T1.ID_CATEGORIA $id_categorias
                --AND T1.ID_EMPRESA   IS NOT NULL
                ORDER BY T1.ID_CATEGORIA, T1.ID_EMPRESA DESC,T1.ID_ENTIDAD ASC,T1.GRUPOARCHIVO ASC,PUNTAJE DESC";
        //var_dump($query);
        // echo($query);
        if( $allMonths){
            $query="SELECT P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO, NULL ID_ARCHIVO_MENSUAL, NULL AS NOMBRE,NULL AS URL,NULL AS FECHA_CREACION,NULL AS FECHA_MODIFICACION,
            SUM(case
            when regexp_like(P.PUNTAJE, '^[^a-zA-Z]*$') then TO_NUMBER(P.PUNTAJE)
            ELSE 0
            END) AS PUNTAJE,
            SUM(P.PTS_GENERAL) AS PTS_GENERAL
            FROM (
                $query
            )P GROUP BY P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO
            ORDER BY P.ID_CATEGORIA, P.ID_EMPRESA DESC,P.ID_ENTIDAD ASC,P.GRUPOARCHIVO ASC ";
        }

        $query = "SELECT Q.ID_CATEGORIA,Q.CATEGORIA, Q.ID_EMPRESA, Q.NOM_EMPRESA,Q.ID_ENTIDAD,
        Q.NOM_ENTIDAD, $sqlGA  SUM(case
            when regexp_like(Q.PUNTAJE, '^[^a-zA-Z]*$') then TO_NUMBER(Q.PUNTAJE)
            ELSE 0
            END) AS PTS_ALCANZADO, SUM(Q.PTS_GENERAL) AS PTS_GENERAL FROM (
            $query
        )Q GROUP BY Q.ID_CATEGORIA,Q.CATEGORIA, Q.ID_EMPRESA, Q.NOM_EMPRESA,Q.ID_ENTIDAD,
        Q.NOM_ENTIDAD $sqlGA1";
        // echo($query);
        $oQuery = DB::select($query);


        foreach($oCategoria as $item){
             $categories = [];
            if (count($oQuery)>0) {
                $nResults = self::filter_by_value($oQuery, 'id_categoria', $item->id_categoria);
                $details = self::unique_multidim_array($nResults,'id_entidad');

                $array_val = [];
                $series = [];
                $pts_general = [];
                $pts_alcanzado = [];


                foreach($details as $val) {
                    $categories[] = $val->nom_entidad;
                    $pts_general[] = floatval($val->pts_general);
                    $pts_alcanzado[] = floatval($val->pts_alcanzado);
                }

                $series[] = (object) array(
                    (object) array(
                        'type'=> 'column',
                        'name' => 'Puntaje Ideal',
                        'color' => $item->color_base,
                        'data'=> $pts_general,
                        'pointPadding'=> 0.2,
                        'pointPlacement'=> 0
                    ),
                    (object) array(
                        'type'=> 'column',
                        'name' => 'Puntaje Alcanzado',
                        'color'=> $item->color_secundario,
                        'data'=> $pts_alcanzado,
                        'pointPadding'=> 0.3,
                        'pointPlacement'=> 0
                    )
                );

                $result[] = (object) array(
                    'id_categoria'=> $item->id_categoria,
                    'categoria'=>  $item->categoria,
                    'categories' => $categories,
                    'series'=>  (array) $series[0]

                );




            } else {
                $result[] = (object) array(
                    'id_categoria'=> $item->id_categoria,
                    'categoria'=>  $item->categoria,
                    'categories' => $categories,
                    'series'=>  []

                );
            }

        }

        return $result;
    }


    public static function getMonthlyControlSummaryNoScore($request) {
        $data_new=null;
        // $id_categoria = $request->id_categoria;
        $id_categorias = $request->id_categorias;
        $id_empresa = $request->id_empresa;
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $allMonths=false;
        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }

        if($id_categorias===null or $id_categorias==='*'){
            $id_categorias=" IS NOT NULL";
        }else{
            $id_categorias=" IN ($id_categorias)";
        }


        if($id_mes===null or $id_mes==='*'){
            $allMonths=true;
            $id_mes=' <=12 ';
        }else{
            $id_mes=" = $id_mes";
        }

        $query_types_documents="SELECT DISTINCT E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ANHO=$id_anho AND ID_MES $id_mes AND TIENE_PUNTAJE = '0' AND ACTIVO = '1') C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
        WHERE --C.ID_ANHO=$id_anho
        -- AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        --AND
        D.ID_GRUPOARCHIVO $id_grupoarchivo
        AND VWCEC.ID_CATEGORIA $id_categorias
        ORDER BY 2,4";
        // echo($query_types_documents);
        $result_types_documents = DB::select($query_types_documents);

        $query="SELECT T1.*,T2.NOMBRE,T2.URL,T2.FECHA_CREACION,T2.FECHA_MODIFICACION,
        CASE WHEN T2.FECHA_MODIFICACION IS NOT NULL AND T2.NOMBRE IS NOT NULL THEN
                CASE WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')>=-1 THEN '3'
                WHEN TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY') - TO_DATE(TO_CHAR(T2.FECHA_MODIFICACION, 'DD/MM/YYYY'), 'DD/MM/YYYY')<-1 THEN '0'
                END
            WHEN T2.NOMBRE IS NULL AND TO_DATE(SYSDATE) - TO_DATE(TO_CHAR(T1.FECHA_LIMITE, 'DD/MM/YYYY'), 'DD/MM/YYYY')>0 THEN '0' ELSE 'N' END AS PUNTAJE
        FROM
        (SELECT DISTINCT VWCEC.ID_CATEGORIA, VWCEC.CATEGORIA, A.ID_EMPRESA,A.NOM_EMPRESA,A.ID_ENTIDAD,A.NOMBRE AS NOM_ENTIDAD,C.ID_ARCHIVO_MENSUAL,E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,
        D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO,C.FECHA_LIMITE
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN (SELECT * FROM ARCHIVO_MENSUAL WHERE ID_ANHO=$id_anho AND ID_MES $id_mes AND TIENE_PUNTAJE = '0' AND ACTIVO = '1') C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        INNER JOIN VW_CONTA_ENTIDAD_CATEGORIA VWCEC ON A.ID_EMPRESA = VWCEC.ID_EMPRESA AND A.ID_ENTIDAD = VWCEC.ID_ENTIDAD
        WHERE --C.ID_ANHO=$id_anho
        --AND  C.ID_MES $id_mes
        --AND C.ID_DEPTO IS NULL
        --AND
        D.ID_GRUPOARCHIVO $id_grupoarchivo) T1
        LEFT JOIN ARCHIVO_MENSUAL_DETALLE T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
        WHERE T1.ID_ENTIDAD NOT IN (9119,17125,17123)
        AND T1.ID_CATEGORIA $id_categorias
        --AND T1.ID_EMPRESA  $id_empresa
        ORDER BY T1.ID_CATEGORIA, T1.ID_EMPRESA DESC,T1.ID_ENTIDAD ASC,T1.GRUPOARCHIVO ASC,PUNTAJE DESC";
        //var_dump($query);
        // echo($query);
        if( $allMonths){
            $query="SELECT P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO, NULL ID_ARCHIVO_MENSUAL, NULL AS NOMBRE,NULL AS URL,NULL AS FECHA_CREACION,NULL AS FECHA_MODIFICACION,
            /*SUM(P.PUNTAJE) AS PUNTAJE*/
            SUM(case when regexp_like(P.PUNTAJE, '^[^a-zA-Z]*$')
                then TO_NUMBER(P.PUNTAJE)
                else 0
            END) AS PUNTAJE
            FROM (
                $query
            )P GROUP BY P.ID_CATEGORIA, P.CATEGORIA, P.ID_EMPRESA, P.NOM_EMPRESA, P.ID_ENTIDAD, P.NOM_ENTIDAD,
            P.ID_GRUPOARCHIVO, P.GRUPOARCHIVO,
            P.ID_TIPOARCHIVO, P.TIPOARCHIVO
            ORDER BY P.ID_CATEGORIA, P.ID_EMPRESA DESC,P.ID_ENTIDAD ASC,P.GRUPOARCHIVO ASC ";
        }
        // echo($query);
        $oQuery = DB::select($query);

        if( $allMonths){
            $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_categoria' => null];

            $result=[];
            $data=[];
            $data1=[];
            $data2=[];
            $children1=[];
            $children2=[];
            $array_types_documents=[];
            $array_types_documents1=[];
            $array_types_documents2=[];
            $array_types_documents3=[];
            $i = 0;


            foreach ($oQuery as $key => $value){


                $data_new=$value;
                if($i>0){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
                    'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
                }

                if(($data_new->id_categoria!==$data_old->id_categoria || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }

                        }
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);

                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){

                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;

                    }
                    // $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    // puntaje total de cada documento interno
                    $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'acumulado');
                    //$children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;
                    $array_types_documents2=[];
                    $array_types_documents3=[];
                }

                if($data_new->id_categoria!==$data_old->id_categoria and $i>0){
                     foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                              if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                        }
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'required'=>true);

                    }
                     $count=0;
                    $total=0;
                    $total_color=0;
                     foreach ($array_types_documents as $key => $value1){

                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;

                    }
                    //$data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                    $children1=[];
                    $array_types_documents=[];
                    $array_types_documents1=[];
                }

                // si la data es igual al total de registros
                if($i===count($oQuery)-1){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                    'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                               $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;

                        }
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                    }
                    /*$data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));*/
                    $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'acumulado');
                    $children1[]=$data1;
                    // $children1[]=(object)array('data'=>$data1);

                    // permite cargar los tipos de documentos para la cabecera
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){

                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }


                        }
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            //'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'puntaje'=>floatval(number_format($count!==0?$puntaje:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color:0,2)),
                            'required'=>true);

                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                   foreach ($array_types_documents as $key => $value1){

                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;

                    }
                    //$data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents);
                    $data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    // $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                }
                $data_old=$value;
                $i++;
            }
        }else{
            $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_categoria' => null];

            $result=[];
            $data=[];
            $data1=[];
            $data2=[];
            $datap=[];
            $children1=[];
            $children2=[];
            $array_types_documents=[];
            $array_types_documents1=[];
            $array_types_documents2=[];
            $array_types_documents3=[];
            $i = 0;


            foreach ($oQuery as $key => $value){


                $data_new=$value;
                if($i>0){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$data_old->id_grupoarchivo,'grupoarchivo'=>$data_old->grupoarchivo,'id_tipoarchivo'=>$data_old->id_tipoarchivo,
                    'tipoarchivo'=>$data_old->tipoarchivo,'nombre'=>$data_old->nombre,'url'=>$data_old->url,'puntaje'=>$data_old->puntaje);
                }

                if(($data_new->id_categoria!==$data_old->id_categoria || $data_new->id_entidad!==$data_old->id_entidad) and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }
                        }
                        if($count>0){
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                        }else{
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                        if($value1->required){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                        }
                    }
                    // $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));
                    // puntaje total de cada documento interno
                    $data1=(object)array('id_entidad'=>$data_old->id_entidad,'nombre'=>$data_old->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=>'no acumulado');
                    // $children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;
                    $array_types_documents2=[];
                    $array_types_documents3=[];
                }

                if($data_new->id_categoria!==$data_old->id_categoria and $i>0){
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                            }
                        }
                          if($count>0){
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'required'=>true);
                        }else{
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                     $count=0;
                    $total=0;
                    $total_color=0;
                     foreach ($array_types_documents as $key => $value1){
                        if($value1->required){
                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;
                        }
                    }
                    //$data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $data=(object)array('nombre'=>$data_old->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)));
                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                    $children1=[];
                    $array_types_documents=[];
                    $array_types_documents1=[];
                }

                // si la data es igual al total de registros
                if($i===count($oQuery)-1){
                    $array_types_documents3[]=(object)array('id_grupoarchivo'=>$value->id_grupoarchivo,'grupoarchivo'=>$value->grupoarchivo,'id_tipoarchivo'=>$value->id_tipoarchivo,
                    'tipoarchivo'=>$value->tipoarchivo,'nombre'=>$value->nombre,'url'=>$value->url,'puntaje'=>$value->puntaje);

                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=null;
                        $count=0;
                        $nombre=null;
                        $url=null;
                        foreach ($array_types_documents3 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                $count++;
                                $puntaje=$value2->puntaje;
                                $nombre=$value2->nombre;
                                $url=$value2->url;
                            }
                        }
                        if($count>0){
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'nombre'=>$nombre,'url'=>$url,'puntaje'=>$puntaje,'required'=>true);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            'puntaje'=>$puntaje,'required'=>true);
                        }else{
                            $array_types_documents2[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                            $array_types_documents1[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                        }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents2 as $key => $value1){
                        if($value1->required){
                            if($value1->puntaje!=='N'){
                                $total=$total+$value1->puntaje;
                                $total_color=$total_color+$value1->puntaje;
                            }else{
                                $total_color=$total_color+3;
                            }
                            $count++;
                        }
                    }
                    /*$data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));*/
                    $data1=(object)array('id_entidad'=>$value->id_entidad,'nombre'=>$value->nom_entidad,
                            'types_documents'=>$array_types_documents2,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=> 'no acumulado');
                    //$children1[]=(object)array('data'=>$data1);
                    $children1[]=$data1;

                    // permite cargar los tipos de documentos para la cabecera
                    foreach ($result_types_documents as $key => $value1){
                        $puntaje=0;
                        $puntaje_color=0;
                        $count=0;
                        foreach ($array_types_documents1 as $key => $value2){
                            if($value1->id_grupoarchivo===$value2->id_grupoarchivo and $value1->id_tipoarchivo===$value2->id_tipoarchivo){
                                if($value2->required){
                                    $count++;
                                    if($value2->puntaje!=='N'){
                                        $puntaje=$puntaje+$value2->puntaje;
                                        $puntaje_color=$puntaje_color+$value2->puntaje;
                                    }else{
                                        $puntaje_color=$puntaje_color+3;
                                    }
                                }
                            }
                        }
                        if($count>0){
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo,
                            //'puntaje'=>floatval(number_format($count!==0?$puntaje/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color/$count:0,2)),
                            'puntaje'=>floatval(number_format($count!==0?$puntaje:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$puntaje_color:0,2)),
                            'required'=>true);
                        }else{
                            $array_types_documents[]=(object)array('id_grupoarchivo'=>$value1->id_grupoarchivo,'grupoarchivo'=>$value1->grupoarchivo,'id_tipoarchivo'=>$value1->id_tipoarchivo,'tipoarchivo'=>$value1->tipoarchivo
                            ,'required'=>false);
                       }
                    }
                    $count=0;
                    $total=0;
                    $total_color=0;
                    foreach ($array_types_documents as $key => $value1){
                        if($value1->required){
                            $count++;
                            $total=$total+$value1->puntaje;
                            $total_color=$total_color+$value1->puntaje_color;
                        }
                    }
                    //$data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents);
                    $data=(object)array('nombre'=>$value->categoria,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color:0,2)), 'option'=> 'no acumulado');
                    // $data=(object)array('nombre'=>$value->nom_empresa,'types_documents'=>$array_types_documents,'puntaje'=>floatval(number_format($count!==0?$total/$count:0,2)),'puntaje_color'=>floatval(number_format($count!==0?$total_color/$count:0,2)));

                    $result[]=(object)array('data'=>$data,'children'=>$children1);
                }
                $data_old=$value;
                $i++;
            }
        }
        foreach ($result as $key => $valOrder){
            $array_new=array();
            foreach ($valOrder->children as $key1 => $valChildren){
                $array_new[$key1]=$valChildren->{'puntaje'};
            }
            $sorted=array_multisort( $array_new,SORT_DESC,$valOrder->children);
        }
        return $result;
    }

    public static function ctaWithoutEquivalences($request){
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_opcion = $request->id_opcion;
        $query = "";

        $cond_anho = "";
        $cond_mes = "";

        if ($id_anho != '*') {
            $cond_anho = "AND vcd.ID_ANHO = ".$id_anho;
        }

        if ($id_mes != '*') {
            $cond_mes = " AND vcd.id_mes = ".$id_mes;
        }


        if ($id_opcion == 1) {
            $query = "SELECT vcd.ID_ENTIDAD, vcd.ID_CUENTAAASI, vcd.ID_ANHO,  vcd.ID_MES, vcd.LOTE, SUM(COS_VALOR) TOTAL
            FROM VW_CONTA_DIARIO vcd
            WHERE vcd.id_entidad = ".$id_entidad." ".$cond_anho." ".$cond_mes." AND
            NOT EXISTS (SELECT 1 FROM CONTA_EMPRESA_CTA cec
            WHERE cec.ID_CUENTAAASI = vcd.ID_CUENTAAASI)
            GROUP BY vcd.ID_ENTIDAD, vcd.ID_CUENTAAASI, vcd.ID_ANHO, vcd.ID_MES, vcd.LOTE ";

        } elseif ($id_opcion == 2) {
            $query = "SELECT vcd.ID_ENTIDAD, vcd.ID_CUENTAAASI, vcd.ID_ANHO,  vcd.ID_MES, vcd.LOTE, vcd.debe, vcd.haber
            FROM VW_CONTA_DIARIO vcd
            WHERE vcd.id_entidad = ".$id_entidad." ".$cond_anho." ".$cond_mes." AND
            NOT EXISTS (SELECT 1 FROM CONTA_EMPRESA_CTA cec
            WHERE cec.ID_CUENTAAASI = vcd.ID_CUENTAAASI)
            ORDER BY vcd.ID_ENTIDAD, vcd.ID_CUENTAAASI, vcd.ID_ANHO, vcd.ID_MES, vcd.LOTE";
        }


        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function importCalendarMonthlyControl($request) {

        //print_r($request->items);
        $id_empresa = $request->id_empresa;
        $id_entidades = $request->id_entidades;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $tiene_puntaje = $request->tiene_puntaje;
        $data_documents = $request->data_documents;

        $error = 0;
        $msg_error = str_repeat("0", 200);
        $pdo = DB::connection()->getPdo();
        $pdo = DB::getPdo();
        DB::beginTransaction();
        foreach ($data_documents as $key => $val) {
            foreach ($val['documents'] as $key => $item) {
                try {
                    $query_tipoarchivo = "select id_tipoarchivo from eliseo.tipo_archivo where codigo =".$item['id_document']." and rownum = 1" ;
                    $query_result = DB::select($query_tipoarchivo);
                    $id_tipoarchivo = count($query_result)=== 1?$query_result[0]->id_tipoarchivo:null;
                    $stmt = $pdo->prepare("BEGIN ELISEO.PKG_MANAGEMENT_REPORTS.SP_IU_SETTING_CTRL_MENSUAL(
                        :P_ID_EMPRESA,
                        :P_ID_ENTIDAD,
                        :P_ID_DEPTO,
                        :P_ID_ANHO,
                        :P_ID_MES,
                        :P_ID_TIPOARCHIVO,
                        :P_FECHA_LIMITE,
                        :P_TIENE_PUNTAJE,
                        :P_ERROR,
                        :P_MSGERROR
                        );
                        END;"
                        );

                        $stmt->bindParam(':P_ID_EMPRESA', $id_empresa, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidades, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_MES', $val['id_mes'], PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_TIPOARCHIVO', $id_tipoarchivo, PDO::PARAM_INT);
                        $stmt->bindParam(':P_FECHA_LIMITE', $item['fecha'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_TIENE_PUNTAJE', $tiene_puntaje, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                        $stmt->execute();
                    if($error >= 1){
                        DB::rollback();
                    }
                    DB::commit();
                }catch(Exception $e){
                    DB::rollback();
                    $error = 1;
                    $msg_error=$e;
                }

            }

        }
        $result = [
            'error' => $error,
            'message' => $msg_error
        ];

        return $result;
    }

}
