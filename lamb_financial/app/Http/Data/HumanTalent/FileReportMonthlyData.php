<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/01/2019
 * Time: 9:12 AM
 */

namespace App\Http\Data\HumanTalent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use DateTime;

class FileReportMonthlyData extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function getTypeEntity() {
        $query = DB::table('TIPO_ENTIDAD')
        ->select('*')
        ->orderBy('ORDEN','ASC')
        ->get();
        return $query;
    }
    public static function getTipoArchivo($request) {
        $grupo_archivo = $request->grupo_archivo;
        $query = DB::table('TIPO_ARCHIVO')
        ->select('*')
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
    public static function getConfigMonthlyControl($request,$empr,$entities,$deptos) {
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_empresas=$empr;
        $id_entities=$entities;
        $id_deptos=$deptos;
        if($id_empresa==='all'){
            $id_empresa=null;
        }
        if($id_entidad==='all'){
            $id_entidad=null;
        }

        if($id_empresa===null || $id_empresa!=='*'){
            $id_empresas=$id_empresa;
        }
        if($id_entidad===null || $id_entidad!=='*'){
            $id_entities=$id_entidad;
        }
        if($id_depto==null || ($id_depto!=='*' && $id_depto!=='0')){
            $id_deptos=$id_depto;
        }
        $query="";
        if($id_empresa==='*'){
            $query="SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, A.FECHA_LIMITE
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IS NULL AND A.ID_ENTIDAD IS NULL AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            UNION ALL";
        }
        if($id_entidad==='*'){
        $query=$query."
            SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, A.FECHA_LIMITE
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IN ($id_empresas) AND A.ID_ENTIDAD IS NULL AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes
            UNION ALL";
        }
        if($id_depto===null || $id_depto==='*'){
            $query=$query."
            SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, A.FECHA_LIMITE
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_EMPRESA IN ($id_empresas) AND A.ID_ENTIDAD IN ($id_entities) AND A.ID_DEPTO IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes";
        }
        if($id_depto==='0'){
            $query=$query."
            SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, A.FECHA_LIMITE
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_ENTIDAD IN ($id_entities) AND A.ID_DEPTO IN ($id_deptos,0)
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes";

        }else if($id_depto!==null && $id_depto!=='*'){
            $query=$query."
            SELECT A.ID_ARCHIVO_MENSUAL, NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA, NVL(D.NOMBRE,'GENERAL') AS ENTIDAD, B.NOMBRE AS TIPODOCUMENTO, A.ID_EMPRESA, A.ID_ENTIDAD,
            A.ID_DEPTO, A.ID_TIPOARCHIVO, A.ID_ANHO, A.ID_MES, A.FECHA_LIMITE
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO = B.ID_TIPOARCHIVO
            LEFT JOIN VW_CONTA_EMPRESA C ON A.ID_EMPRESA = C.ID_EMPRESA
            LEFT JOIN VW_CONTA_ENTIDAD D ON A.ID_ENTIDAD = D.ID_ENTIDAD
            WHERE A.ID_ENTIDAD IN ($id_entities) AND A.ID_DEPTO IN ($id_deptos)
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES = $id_mes";
        }
        $query=$query." ORDER BY ID_EMPRESA DESC,ID_ENTIDAD DESC,ID_DEPTO DESC, ID_ANHO ASC, ID_MES ASC,FECHA_LIMITE ASC";
        $result=DB::select($query);
        return $result;
    }
    public static function addConfigMonthlyControl($request) {
        $res="OK";
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $fecha_limite = $request->fecha_limite;
        if($id_empresa===null || $id_empresa==='*' || $id_empresa==='all'){
            try{
                DB::table('REPORTE_ARCHIVO_MENSUAL')
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
                    DB::table('REPORTE_ARCHIVO_MENSUAL')
                    ->where('ID_ANHO', $id_anho)
                    ->where('ID_MES', $id_mes)
                    ->where('ID_EMPRESA', $id_empresa)
                    ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                    ->delete();
                }catch(Exception $e){}
            }
            $id_entidad=null;
        }
        if($id_depto===null || $id_depto==='*' || $id_depto==='all'){
            if($id_entidad!==null and $id_entidad!=='*' and  $id_entidad!=='all'){
                try{
                    DB::table('REPORTE_ARCHIVO_MENSUAL')
                    ->where('ID_ANHO', $id_anho)
                    ->where('ID_MES', $id_mes)
                    ->where('ID_ENTIDAD', $id_entidad)
                    ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                    ->delete();
                }catch(Exception $e){}
            }
            $id_depto=null;
        }
        DB::table('REPORTE_ARCHIVO_MENSUAL')
            ->updateOrInsert(
                array('ID_EMPRESA'=>$id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_ANHO' => $id_anho,
                    'ID_MES'=>$id_mes,
                    'ID_TIPOARCHIVO' => $id_tipoarchivo
            ),
                array('FECHA_LIMITE' => $fecha_limite
                )
            );  
        return $res;
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
        if($id_empresa===null || $id_empresa==='*' || $id_empresa==='all'){
            try{
                DB::table('REPORTE_ARCHIVO_MENSUAL')
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
                    DB::table('REPORTE_ARCHIVO_MENSUAL')
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
                    DB::table('REPORTE_ARCHIVO_MENSUAL')
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
        DB::table('REPORTE_ARCHIVO_MENSUAL')
            ->where('ID_ARCHIVO_MENSUAL', $id_archivo_mensual)
            ->update(
                array('ID_EMPRESA'=>$id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_ANHO' => $id_anho,
                    'ID_MES'=> $id_mes,
                    'ID_TIPOARCHIVO' => $id_tipoarchivo,
                    'FECHA_LIMITE' => $fecha_limite
                    )
            );  
        return $res;
    }
    public static function deleteConfigMonthlyControl($id) {
        $res="OK";
        DB::table('REPORTE_ARCHIVO_MENSUAL')
                ->where('ID_ARCHIVO_MENSUAL', $id)
                ->delete();
        return $res;
    }
    public static function getFileGroup() {
        $query = DB::table('GRUPO_ARCHIVO')
        ->select('*')
        ->get();
        return $query;
    }
    public static function getTipoArchivoAnhoMes($request) {
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $query = DB::select("SELECT TP.* FROM ELISEO.TIPO_ARCHIVO TP
        INNER JOIN ELISEO.REPORTE_ARCHIVO_MENSUAL RAM ON TP.ID_TIPOARCHIVO =RAM.ID_TIPOARCHIVO 
        WHERE ID_ANHO =$id_anho AND ID_MES=$id_mes");
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
    public static function getMonthlyControl($request,$id_empresa) {
        $id_tipoarchivo = $request->id_tipoarchivo;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        if($id_mes===null or $id_mes==='*'){
            $id_mes=" IS NOT NULL";
        }else{
            $id_mes=" = $id_mes";
        }
        if($id_tipoarchivo===null or $id_tipoarchivo==='*'){
            $id_tipoarchivo=" IS NOT NULL";
        }else{
            $id_tipoarchivo=" = $id_tipoarchivo";
        }
            $query="
            SELECT DISTINCT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION,D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,PN.NOM_PERSONA AS USER_NAME
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            LEFT JOIN REPORT_ARCHIV_MENSUAL_DET D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            LEFT JOIN MOISES.VW_PERSONA_NATURAL PN ON D.ID_USER=PN.ID_PERSONA
            WHERE A.ID_EMPRESA =$id_empresa 
            AND A.ID_ENTIDAD IS NULL
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND B.ID_TIPOARCHIVO $id_tipoarchivo
            UNION ALL 
            SELECT DISTINCT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_MENSUAL,
            A.ID_EMPRESA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,trunc(A.FECHA_LIMITE) + INTERVAL '0 23:59:59' DAY TO SECOND AS FECHA_LIMITE,D.ID_DETALLE,
            D.FECHA_CREACION,D.FECHA_MODIFICACION,D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,PN.NOM_PERSONA AS USER_NAME
            FROM REPORTE_ARCHIVO_MENSUAL A
            INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
            INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
            LEFT JOIN REPORT_ARCHIV_MENSUAL_DET D ON A.ID_ARCHIVO_MENSUAL=D.ID_ARCHIVO_MENSUAL AND D.ID_ENTIDAD=$id_entidad
            LEFT JOIN MOISES.VW_PERSONA_NATURAL PN ON D.ID_USER=PN.ID_PERSONA
            WHERE A.ID_ENTIDAD=$id_entidad
            AND A.ID_ANHO = $id_anho
            AND A.ID_MES $id_mes
            AND B.ID_TIPOARCHIVO $id_tipoarchivo
            ORDER BY GRUPOARCHIVO,TIPOARCHIVO,ID_ANHO,ID_MES,FECHA_LIMITE ";
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
        DB::table('REPORT_ARCHIV_MENSUAL_DET')
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
        DB::table('REPORT_ARCHIV_MENSUAL_DET')
        ->where('ID_DETALLE', $id_detalle)
        ->delete();
        
       /*  DB::table('REPORT_ARCHIV_MENSUAL_DET')
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
                ); */
        return $ret;   
    }
    public static function getCompanyById($id_empresa) {
        $query = DB::table('VW_CONTA_EMPRESA')
        ->where("ID_EMPRESA", "=",$id_empresa)
        ->select('ID_CORPORACION AS CORPORACION',
            'ID_TIPOEMPRESA AS TIPO_EMPRESA',
            'ID_EMPRESA',
            'ID_PERSONA',
            'LOGO',
            'ID_RUC AS RUC',
            'NOM_DENOMINACIONAL AS NOMBRE',
            'NOM_LEGAL AS NOMBRE_LEGAL')
        ->first();
        return $query;
    }
    public static function getMonthlyControlSummary($request) {
        $data_new=null;
        $data_old=(object) ['id_grupoarchivo' => null,'id_entidad' => null,'id_empresa' => null];
        $id_empresa = $request->id_empresa;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;

        if($id_tipoarchivo===null or $id_tipoarchivo==='*'){
            $id_tipoarchivo=" IS NOT NULL";
        }else{
            $id_tipoarchivo=" = $id_tipoarchivo";
        }
        if($id_empresa===null or $id_empresa==='*'){
            $id_empresa=" IS NOT NULL";
        }else{
            $id_empresa=" = $id_empresa";
        }
        $query_types_documents="SELECT DISTINCT E.ID_GRUPOARCHIVO,E.NOMBRE AS GRUPOARCHIVO,D.ID_TIPOARCHIVO,D.NOMBRE AS TIPOARCHIVO
        FROM VW_CONTA_ENTIDAD A
        INNER JOIN REPORTE_ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO 
        WHERE C.ID_ANHO=$id_anho 
        AND  C.ID_MES=$id_mes 
        --AND C.ID_DEPTO IS NULL 
        AND D.ID_TIPOARCHIVO $id_tipoarchivo 
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
        INNER JOIN REPORTE_ARCHIVO_MENSUAL C ON C.ID_ENTIDAD=A.ID_ENTIDAD OR (C.ID_EMPRESA=A.ID_EMPRESA AND C.ID_ENTIDAD IS NULL) OR (C.ID_EMPRESA IS NULL AND C.ID_ENTIDAD IS NULL)
        INNER JOIN TIPO_ARCHIVO D ON D.ID_TIPOARCHIVO=C.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO E ON E.ID_GRUPOARCHIVO=D.ID_GRUPOARCHIVO
        WHERE C.ID_ANHO=$id_anho 
        AND  C.ID_MES=$id_mes 
        --AND C.ID_DEPTO IS NULL 
        AND D.ID_TIPOARCHIVO $id_tipoarchivo) T1
        LEFT JOIN REPORT_ARCHIV_MENSUAL_DET T2 ON T1.ID_ARCHIVO_MENSUAL=T2.ID_ARCHIVO_MENSUAL AND T1.ID_ENTIDAD=T2.ID_ENTIDAD
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
}
