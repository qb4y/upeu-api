<?php
namespace App\Http\Data\Evaluation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class EvaluationData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listPeriod(){
        $query = DB::table('EVAL_PERIODO')->select('ID_PERIODO','NOMBRE','ESTADO')->where('ESTADO', 1)->orderBy('ID_PERIODO')->get();
        return $query;
    }
    public static function listPeriodDetails($id_periodo){
        $sql = "SELECT A.ID_PERIODO,A.NOMBRE AS NOMBRE_PERIODO,B.ID_PDETALLE,B.CODIGO,B.NOMBRE 
                FROM EVAL_PERIODO A, EVAL_PERIODO_DETALLE B
                WHERE A.ID_PERIODO = B.ID_PERIODO
                AND A.ID_PERIODO = ".$id_periodo."
                ORDER BY B.ID_PDETALLE,B.CODIGO  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listEvaluationDepartments($id_entidad,$id_persona){
        $sql = "SELECT 
                A.ID_ENTIDAD,A.ID_DEPTO,B.NOMBRE 
                FROM EVAL_INDICADORES A, CONTA_ENTIDAD_DEPTO B, EVAL_DEPTO_PERSONA C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_ENTIDAD = C.ID_ENTIDAD
                AND A.ID_DEPTO = C.ID_DEPTO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND C.ID_PERSONA = ".$id_persona."
                GROUP BY  A.ID_ENTIDAD,A.ID_DEPTO,B.NOMBRE
                ORDER BY ID_DEPTO ";
        $query = DB::select($sql);
        return $query;
    }
    
    public static function listDepartmentsAssigneds($id_entidad,$id_persona){
        $sql = "SELECT 
                A.ID_ENTIDAD,A.ID_DEPTO,B.NOMBRE 
                FROM LAMB_USERS_DEPTO A, CONTA_ENTIDAD_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID = ".$id_persona."
                AND B.ES_GRUPO = '0'
                ORDER BY A.ID_DEPTO ";
        $query = DB::select($sql);
        return $query;
    }
    
    public static function ShowEvaluationRegisters($id_entidad,$id_depto,$id_anho,$id_pdetalle,$id_periodo,$id_mes){
        if($id_periodo == 1 || $id_periodo == 2){
            $mes = "AND A.ID_MES = ".$id_mes." ";
        }else{
            $mes = "";
        }
        $sql = "SELECT 
                A.ID_REGISTRO,A.ID_ENTIDAD,A.ID_DEPTO,B.NOMBRE AS NOMBRE_DEPTO, C.ID_PERIODO,C.NOMBRE AS NOMBRE_PERIODO
                FROM EVAL_REGISTRO A, CONTA_ENTIDAD_DEPTO B, EVAL_PERIODO C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_PERIODO = C.ID_PERIODO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho." 
                AND A.ID_PERIODO = ".$id_periodo." 
                ".$mes."
                AND A.ESTADO = '0' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addEvaluationRegisters($id_entidad,$id_depto,$id_user,$id_anho,$id_periodo,$id_pdetalle,$id_mes){
        DB::table('EVAL_REGISTRO')->insert(
                        array('ID_ENTIDAD' => $id_entidad,'ID_DEPTO' => $id_depto, 'ID_PERSONA' =>$id_user,'ID_ANHO' =>$id_anho,'ID_MES' =>$id_mes,'ID_PERIODO' =>$id_periodo,'FECHA'=> DB::raw('SYSDATE'),'ESTADO' =>0)
                    );
        
        //$sql = EvaluationData::ListEvaluationRegistersIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle);
        return $sql;
    }
    public static function updateEvaluationRegisters($id_registro,$estado){
        DB::table('EVAL_REGISTRO')
            ->where('ID_REGISTRO', $id_registro)
            ->update([
                'ESTADO' => $estado
            ]);
    }
    public static function ListEvaluationRegistersIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle,$id_mes){
        if($id_periodo == 1 || $id_periodo == 2){
            $mes = "AND A.ID_MES = ".$id_mes." ";
        }else{
            $mes = "";
        }
        $sql = "SELECT 
                        A.ID_REGISTRO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES, A.ID_PERIODO, B.ID_INDICADOR, B.NOMBRE, B.IDEAL,C.ID_TIPO,C.NOMBRE AS NOMBRE_TIPO, C.SIMBOLO,
                        DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) AS REQUIRED_FECHA
                FROM EVAL_REGISTRO A, EVAL_INDICADORES B, EVAL_TIPO C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_PERIODO = B.ID_PERIODO
                AND B.ID_TIPO = C.ID_TIPO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_PERIODO = ".$id_periodo."
                AND A.ID_ANHO = ".$id_anho."
                ".$mes."
                AND B.ESTADO = '1' 
                AND B.ID_INDICADOR NOT IN (SELECT ID_INDICADOR FROM EVAL_DETALLE WHERE ID_PDETALLE = ".$id_pdetalle." ) ";
        $query = DB::select($sql);
        return $query;
    }
    public static function ListEvaluationIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle,$id_mes){
        if($id_periodo == 1 || $id_periodo == 2){
            $mes = "AND A.ID_MES = ".$id_mes." ";
        }else{
            $mes = "";
        }
        $sql = "SELECT A.ID_REGISTRO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES, A.ID_PERIODO, B.ID_INDICADOR, B.NOMBRE, B.IDEAL,C.ID_TIPO,C.NOMBRE AS NOMBRE_TIPO, C.SIMBOLO,
                        (SELECT COUNT(X.ID_SUSTENTO)||': '||MAX(TO_CHAR(X.FECHA,'DD/MM/YYYY')) FROM EVAL_SUSTENTOS X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR AND X.ID_PDETALLE = ".$id_pdetalle." ) N_FILES
                FROM EVAL_REGISTRO A, EVAL_INDICADORES B, EVAL_TIPO C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_PERIODO = B.ID_PERIODO
                AND B.ID_TIPO = C.ID_TIPO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_PERIODO = ".$id_periodo."
                AND A.ID_ANHO = ".$id_anho."
                ".$mes."
                AND B.ESTADO = '1' 
                AND B.SUSTENTO = 'S' ";
                //AND B.ID_INDICADOR NOT IN (SELECT ID_INDICADOR FROM EVAL_DETALLE WHERE ID_PDETALLE = ".$id_pdetalle." 
                //UNION ALL SELECT ID_INDICADOR FROM EVAL_SUSTENTOS WHERE ID_PDETALLE = ".$id_pdetalle." ) ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showEvaluationLivelihoods($id_sustento){
        $sql = "SELECT ID_SUSTENTO,ID_REGISTRO,NAME_FILE 
                FROM EVAL_SUSTENTOS
                WHERE ID_SUSTENTO  = ".$id_sustento." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listEvaluationLivelihoods($id_registro,$id_indicador,$id_periodo_detail){
        $sql = "SELECT
                A.ID_SUSTENTO, B.NOMBRE,A.FECHA,A.NAME_FILE 
                FROM EVAL_SUSTENTOS A, EVAL_INDICADORES B
                WHERE A.ID_INDICADOR = B.ID_INDICADOR
                AND A.ID_REGISTRO = ".$id_registro."
                AND A.ID_INDICADOR = ".$id_indicador."
                AND A.ID_PDETALLE = ".$id_periodo_detail."
                ORDER BY A.FECHA ";
        $query = DB::select($sql);
        return $query;
    }
    public static function deleteEvaluationLivelihoods($id_sustento){
        DB::table('EVAL_SUSTENTOS')->where('ID_SUSTENTO', '=', $id_sustento)->delete();
    }
    public static function showTypeNotices($id_tipo){
        $sql = "SELECT ID_TIPO,NOMBRE,MINIMO,MAXIMO,TIPO_CAMPO,SIMBOLO
                FROM EVAL_TIPO
                WHERE ID_TIPO = ".$id_tipo." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listTypeIndicador($id_indicador){
        $sql = "SELECT 
                B.NOMBRE,B.TIPO_CAMPO,B.MINIMO,B.MAXIMO,B.ASCENDENTE,A.IDEAL,nvl(A.FORMULA,0) as FORMULA,NVL(FORMULA_FECHA,0) AS FORMULA_FECHA,NVL(A.FECHA,'N') as REQ_FECHA
                FROM EVAL_INDICADORES A, EVAL_TIPO B
                WHERE A.ID_TIPO = B.ID_TIPO
                AND A.ID_INDICADOR = ".$id_indicador." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showTypeNoticesDetails($id_tipo){
        $sql = "SELECT 
                ID_TIPODETALLE,ID_TIPO,VALOR_MIN,VALOR_MAX,VALOR_DEFAULT,NOMBRE, GLOSA AS ALIAS,DETALLE
                FROM EVAL_TIPO_DETALLE
                WHERE ID_TIPO = ".$id_tipo." ORDER BY VALOR_MIN ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addEvaluationDetails($id_registro,$id_indicador,$pdetalle,$respuesta,$ideal,$comentario,$valor,$formula,$formula_fecha,$fecha,$req_fecha){
        DB::table('EVAL_DETALLE')->insert(
                        array('ID_INDICADOR' => $id_indicador,'ID_REGISTRO' => $id_registro, 'ID_PDETALLE' =>$pdetalle,'RESPUESTA' =>$respuesta,'IDEAL' =>$ideal,'COMENTARIO' =>$comentario,'FECHA'=> DB::raw('SYSDATE'),'ESTADO' =>1,'VALOR' =>$valor)
                    );
        
        $query = "SELECT 
                        MAX(ID_DETALLE) ID_DETALLE
                FROM EVAL_DETALLE ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_detalle = $id->id_detalle;
        }
        if($req_fecha == "S"){
            $query = "UPDATE EVAL_DETALLE SET SEMAFORO = nvl(".$formula.",0),
                                            FECHA_ENTREGA = TO_DATE('".$fecha."','YYYY-MM-DD'),
                                            DIA = FC_DIAS_HABILES(FECHA_ENTREGA),
                                            VALOR1 = nvl(".$formula_fecha.",0)
                WHERE ID_DETALLE = ".$id_detalle."  ";
        }else{
            $query = "UPDATE EVAL_DETALLE SET SEMAFORO = nvl(".$formula.",0)
                WHERE ID_DETALLE = ".$id_detalle."  ";
        }
        
        DB::update($query);
    }
    public static function addEvaluationLivelihoods($id_registro,$id_indicador,$id_pdetalle,$formula,$file){
        DB::table('EVAL_SUSTENTOS')->insert(
                        array('ID_INDICADOR' => $id_indicador,'ID_REGISTRO' => $id_registro, 'ID_PDETALLE' =>$id_pdetalle,'FECHA'=> DB::raw('SYSDATE'),'NAME_FILE' =>$file)
                    );
        
        $query = "SELECT 
                        MAX(ID_SUSTENTO) ID_SUSTENTO
                FROM EVAL_SUSTENTOS ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_sustento = $id->id_sustento;
        }
        $query = "UPDATE EVAL_SUSTENTOS SET DIA = FC_DIAS_HABILES(SYSDATE),
                                            VALOR = nvl(".$formula.",0)
                WHERE ID_SUSTENTO = ".$id_sustento."  ";
        DB::update($query);
    }
    public static function showPeriodRequerid($id_registro){
        $sql = "SELECT COUNT(B.ID_PDETALLE) AS PERIODOS
                FROM EVAL_REGISTRO A, EVAL_PERIODO_DETALLE B
                WHERE A.ID_PERIODO = B.ID_PERIODO
                AND A.ID_REGISTRO = ".$id_registro."  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showPeriodEvaluated($id_registro){
        $sql = "SELECT COUNT(ID_PERIODO) AS PERIODOS
                FROM (
                SELECT 
                B.ID_PERIODO
                FROM EVAL_DETALLE A, EVAL_PERIODO_DETALLE B
                WHERE A.ID_PDETALLE = B.ID_PDETALLE
                AND A.ID_REGISTRO = ".$id_registro."
                GROUP BY B.ID_PERIODO
                )  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listEvaluationReports($id_depto){
        $sql = "SELECT 
                B.ID_PERIODO,C.NOMBRE,ROUND(SUM(A.VALOR)/COUNT(A.ID_DETALLE)) NOTA
                FROM EVAL_DETALLE A, EVAL_INDICADORES B, EVAL_PERIODO C
                WHERE A.ID_INDICADOR = B.ID_INDICADOR
                AND B.ID_PERIODO = C.ID_PERIODO
                AND B.ID_DEPTO = '".$id_depto."'
                GROUP BY B.ID_PERIODO,C.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showTotalItems($id_depto){
        $sql = "SELECT 
                COUNT(A.ID_DETALLE) CANT
                FROM EVAL_DETALLE A, EVAL_INDICADORES B
                WHERE A.ID_INDICADOR = B.ID_INDICADOR
                AND B.ID_DEPTO = '".$id_depto."' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listEvaluationTrafficLight($id_depto,$id_periodo,$cant){
        $sql = "SELECT 
                        B.ID_PERIODO,C.NOMBRE,A.SEMAFORO,
                        ROUND(SUM(DECODE(A.SEMAFORO,'G',1,'A',1,'R',1,0))/".$cant.",2)*100 NOTA,
                        DECODE(A.SEMAFORO,'G','#009933','A','#ffff00','#ff0000') COLOR
                FROM EVAL_DETALLE A, EVAL_INDICADORES B, EVAL_PERIODO C
                WHERE A.ID_INDICADOR = B.ID_INDICADOR
                AND B.ID_PERIODO = C.ID_PERIODO
                AND B.ID_DEPTO = '".$id_depto."'
                AND B.ID_PERIODO = ".$id_periodo."
                GROUP BY B.ID_PERIODO,C.NOMBRE,A.SEMAFORO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listReportDepartmentsX($id_entidad,$id_anho){
        $sql = "SELECT 
                A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,C.NOMBRE,ROUND(SUM(B.VALOR)/COUNT(B.ID_DETALLE)) NOTA, '%' TIPO_NOTA
                FROM EVAL_REGISTRO A, EVAL_DETALLE B, CONTA_ENTIDAD_DEPTO C
                WHERE A.ID_REGISTRO = B.ID_REGISTRO
                AND A.ID_ENTIDAD = C.ID_ENTIDAD
                AND A.ID_DEPTO = C.ID_DEPTO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_ANHO = ".$id_anho."
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,C.NOMBRE
                ORDER BY NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listReportDepartments($id_entidad,$id_anho){
        $sql = "SELECT 
                        ID_DEPTO,DEPTO AS NOMBRE,'%' TIPO_NOTA,
                        ROUND(SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS NOTAS,
                        ROUND(SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS NOTA,
                        (CASE WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 0 AND 35 THEN '#F3A5A5'
                        WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 36 AND 75 THEN '#FFFBA1'
                        ELSE '#A5F3A5' END) AS SEMAFORO
                FROM (
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,D.VALOR AS NOTA1,D.VALOR1 AS NOTA0,0 NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_DETALLE D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        UNION ALL
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,0 AS NOTA1,0 AS NOTA0,ROUND(SUM(D.VALOR)/COUNT(D.ID_SUSTENTO)) AS NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_SUSTENTOS D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        GROUP BY A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE,E.CODIGO,E.NOMBRE,E.ID_PDETALLE,A.ID_PERIODO
                )
                GROUP BY ID_DEPTO,DEPTO
                ORDER BY ID_DEPTO ";
        /*$sql = "SELECT 
                        ID_DEPTO,NOMBRE,ROUND(SUM((NVL(NOTA1,0)+NVL(NOTA2,0))/2)/COUNT(ID_INDICADOR),0) NOTAS,'%' TIPO_NOTA,
                        ROUND(SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS NOTA,
                        (CASE WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 0 AND 35 THEN '#F3A5A5'
WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 36 AND 75 THEN '#FFFBA1'
ELSE '#A5F3A5' END) AS SEMAFORO
                        FROM (
                        SELECT 
                                A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,B.ID_INDICADOR,C.NOMBRE,B.NOMBRE AS INDICADOR,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD,
                                (SELECT ROUND(SUM(X.VALOR)/COUNT(X.ID_DETALLE)) FROM EVAL_DETALLE X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA1,
                                (SELECT ROUND(SUM(X.VALOR1)/COUNT(X.ID_DETALLE)) FROM EVAL_DETALLE X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA0,
                                (SELECT ROUND(SUM(X.VALOR)/COUNT(X.ID_SUSTENTO)) FROM EVAL_SUSTENTOS X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA2
                        FROM EVAL_REGISTRO A ,EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                )
                GROUP BY ID_DEPTO,NOMBRE ";*/
        $query = DB::select($sql);
        return $query;
    }
    public static function listReportDepartmentsDetails($id_entidad,$id_anho,$id_depto){
        $sql = "SELECT 
                        NOMBRE AS INDICADOR,
                        SUM(NOTA1) AS NOTA1,
                        ROUND(SUM(DECODE(REQUIREDD,'S',NVL(NOTA0,0),'X','',(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS NOTA2,
                        ROUND(SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS TOTAL,
                        (CASE WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 0 AND 35 THEN '#F3A5A5'
                        WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 36 AND 75 THEN '#FFFBA1'
                        ELSE '#A5F3A5' END) AS SEMAFORO
                FROM (
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,D.VALOR AS NOTA1,D.VALOR1 AS NOTA0,0 NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_DETALLE D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        UNION ALL
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,0 AS NOTA1,0 AS NOTA0,ROUND(SUM(D.VALOR)/COUNT(D.ID_SUSTENTO)) AS NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_SUSTENTOS D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        GROUP BY A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE,E.CODIGO,E.NOMBRE,E.ID_PDETALLE,A.ID_PERIODO
                )
                WHERE ID_DEPTO = '".$id_depto."'
                GROUP BY ID_DEPTO,NOMBRE
                ORDER BY ID_DEPTO ";
        /*$sql = "SELECT 
                        INDICADOR,
                        SUM(DECODE(REQUIREDD,'X',NOTA1,0))/COUNT(ID_INDICADOR) NOTA1,
                        SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,(NVL(NOTA1,0)+NVL(NOTA2,0)),'X',''))/COUNT(ID_INDICADOR) NOTA2,
                        SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) TOTAL 
                FROM (
                        SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,B.ID_INDICADOR,C.NOMBRE,B.NOMBRE AS INDICADOR,
                        (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                         ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD,
                        (SELECT ROUND(SUM(X.VALOR)/COUNT(X.ID_DETALLE)) FROM EVAL_DETALLE X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA1,
                        (SELECT ROUND(SUM(X.VALOR1)/COUNT(X.ID_DETALLE)) FROM EVAL_DETALLE X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA0,
                        (SELECT ROUND(SUM(X.VALOR)/COUNT(X.ID_SUSTENTO)) FROM EVAL_SUSTENTOS X WHERE X.ID_REGISTRO = A.ID_REGISTRO AND X.ID_INDICADOR = B.ID_INDICADOR) NOTA2
                        FROM EVAL_REGISTRO A ,EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        AND A.ID_DEPTO = '".$id_depto."'
                )
                GROUP BY INDICADOR ";*/
        $query = DB::select($sql);
        return $query;
    }
    public static function listReportDepartmentsMonths($id_entidad,$id_anho,$id_depto,$id_mes_end){
        $sql = "SELECT 
                        ID_DEPTO,MES,'%' TIPO_NOTA,
                        ROUND(SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR),2) AS NOTA,
                        (CASE WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 0 AND 35 THEN '#F3A5A5'
                        WHEN SUM(DECODE(REQUIREDD,'S',(NVL(NOTA0,0)+NVL(NOTA1,0))/2,'X',NOTA1,(NVL(NOTA1,0)+NVL(NOTA2,0))))/COUNT(ID_INDICADOR) BETWEEN 36 AND 75 THEN '#FFFBA1'
                        ELSE '#A5F3A5' END) AS SEMAFORO,
                        (CASE MES WHEN 1 THEN 'ENERO'
                        WHEN 1 THEN 'ENERO'
                        WHEN 2 THEN 'FEBRERO'
                        WHEN 3 THEN 'MARZO'
                        WHEN 4 THEN 'ABRIL'
                        WHEN 5 THEN 'MAYO'
                        WHEN 6 THEN 'JUNIO'
                        WHEN 7 THEN 'JULIO'
                        WHEN 8 THEN 'AGOSTO'
                        WHEN 9 THEN 'SETIEMBRE'
                        WHEN 10 THEN 'OCTUBRE'
                        WHEN 11 THEN 'NOVIEMBRE'
                        ELSE 'DIDICMBRE'
                        END ) MES_NAME
                FROM (
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,D.VALOR AS NOTA1,D.VALOR1 AS NOTA0,0 NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                NVL(A.ID_MES,
                                (CASE  
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 1 THEN 1 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 2 THEN 3 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 3 THEN 5 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 4 THEN 7
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 5 THEN 9
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 6 THEN 11
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 1 THEN 1
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 2 THEN 4
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 3 THEN 7
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 4 THEN 10
                                WHEN A.ID_PERIODO = 6 AND E.CODIGO = 1 THEN 1
                                WHEN A.ID_PERIODO = 6 AND E.CODIGO = 2 THEN 7
                                ELSE TO_NUMBER(CODIGO)
                                END)) MES,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_DETALLE D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        UNION ALL
                        SELECT 
                                A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE AS DEPTO,0 AS NOTA1,0 AS NOTA0,ROUND(SUM(D.VALOR)/COUNT(D.ID_SUSTENTO)) AS NOTA2,E.CODIGO,E.NOMBRE AS PERIODO,E.ID_PDETALLE,
                                NVL(A.ID_MES,
                                (CASE  
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 1 THEN 1 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 2 THEN 3 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 3 THEN 5 
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 4 THEN 7
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 5 THEN 9
                                WHEN A.ID_PERIODO = 4 AND E.CODIGO = 6 THEN 11
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 1 THEN 1
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 2 THEN 4
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 3 THEN 7
                                WHEN A.ID_PERIODO = 5 AND E.CODIGO = 4 THEN 10
                                WHEN A.ID_PERIODO = 6 AND E.CODIGO = 1 THEN 1
                                WHEN A.ID_PERIODO = 6 AND E.CODIGO = 2 THEN 7
                                ELSE TO_NUMBER(CODIGO)
                                END)) MES,
                                (CASE WHEN NVL(B.SUSTENTO,'N') = 'N' AND NVL(B.FECHA,'N') = 'N' THEN 'X'
                                 ELSE DECODE(NVL(B.SUSTENTO,'N'),'S','N',NVL(B.FECHA,'N')) END) AS REQUIREDD
                        FROM EVAL_REGISTRO A, EVAL_INDICADORES B, CONTA_ENTIDAD_DEPTO C, EVAL_SUSTENTOS D, EVAL_PERIODO_DETALLE E
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                        AND A.ID_DEPTO = B.ID_DEPTO
                        AND A.ID_PERIODO = B.ID_PERIODO
                        AND A.ID_ENTIDAD = C.ID_ENTIDAD
                        AND A.ID_DEPTO = C.ID_DEPTO
                        AND A.ID_REGISTRO = D.ID_REGISTRO 
                        AND B.ID_INDICADOR = D.ID_INDICADOR
                        AND A.ID_PERIODO = E.ID_PERIODO
                        AND D.ID_PDETALLE = E.ID_PDETALLE
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND A.ID_ANHO = ".$id_anho."
                        GROUP BY A.ID_DEPTO,A.ID_MES,B.ID_INDICADOR,B.NOMBRE,B.SUSTENTO,B.FECHA,C.NOMBRE,E.CODIGO,E.NOMBRE,E.ID_PDETALLE,A.ID_PERIODO
                )
                WHERE ID_DEPTO = '".$id_depto."'
                AND MES BETWEEN 1 AND ".$id_mes_end."
                GROUP BY ID_DEPTO,MES
                ORDER BY MES ";
        $query = DB::select($sql);
        return $query;
    }
}