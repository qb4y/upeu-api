<?php
namespace App\Http\Data\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class AuxiliarData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public static function generarProcesoInicial($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$tipo){
        
        $error=0;
        $msgerror=" ";
        
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_GENERAR_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_TIPO,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function listAuxiliar($id_entidad,$id_anho,$id_depto){                
        $query = "SELECT 
                    A.ID_AUXILIAR,
                    A.ID_ENTIDAD,
                    A.ID_DEPTO,
                    A.NOMBRE,
                    A.URL,
                    A.ESTADO,
                    A.LOGO,
                    B.ESTADO AS ESTADO_ANHO,
                    CASE WHEN B.ESTADO='01' THEN 'Registrado'
                         WHEN B.ESTADO='02' THEN 'Aprobado'
                         WHEN B.ESTADO='00' THEN 'Anulado'
                    ELSE
                    '-'
                    END AS ESTADO_DESC
                FROM PSTO_AUXILIAR A LEFT JOIN PSTO_AUXILIAR_ANHO B
                ON A.ID_AUXILIAR=B.ID_AUXILIAR
                AND B.ID_ANHO=".$id_anho."
                WHERE A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_DEPTO='".$id_depto."'
                AND A.ESTADO='1'
                ORDER BY A.ID_AUXILIAR ";
    
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    
    public static function addConcepto($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror=" ";
        
               
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PREGRADO_CONCEPTO_PRECIO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addPregradoProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror=" ";

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PREGRADO_PROYECCION(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addProesadProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror=" ";

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PROESAD_PROYECCION(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addPregradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror=" ";

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PREGRADO_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addProesadProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror=" ";

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PROESAD_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addPosgradoProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$id_depto,$id_eap){
        
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_POSGRADO_PROYECCION(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ID_DEPTO,
                                    :P_ID_EAP,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_EAP', $id_eap, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
        
        $validar=['error'=>$error,'msgerror'=>$msgerror];

        return $validar;
    }
    public static function addResidenciaProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_RESIDENCIA_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addConservatorioProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_CONSERVATORIO_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function addPresupuestoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$id_persona){
        
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PREGRADO_PRESUPUESTO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_PERSONA,
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
        
        $validar['error']=$error;
        $validar['msgerror']=$msgerror;

        return $validar;
    }
    
   
        public static function listConceptoPregrado($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){                
        $query = "SELECT 
                    C.ID_ACTIVIDAD, 
                    C.NOMBRE,
                    P.ID_CONCEPTO_PRECIO, 
                    P.ID_ENTIDAD, 
                    P.ID_DEPTO_PADRE, 
                    P.ID_ANHO, 
                    P.TIPO,
                    P.IMPORTE_I,
                    P.IMPORTE_II,
                    P.IMPORTE_BECA_I,
                    P.IMPORTE_BECA_II
                FROM PSTO_ACTIVIDAD C,PSTO_PREGRADO_CONCEPTO_PRECIO P,PSTO_EVENTO E
                WHERE P.ID_ACTIVIDAD=C.ID_ACTIVIDAD
                AND C.ID_EVENTO=E.ID_EVENTO
                AND P.ID_ENTIDAD=".$id_entidad."
                AND P.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                AND P.TIPO NOT IN('D')
                AND E.ID_AUXILIAR=".$id_auxiliar."
                ORDER BY C.ID_ACTIVIDAD ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updateConceptoPregrado($detail){
        
        $id_concepto_precio=0;
        foreach($detail as $items){
            $id_concepto_precio=$items->id_concepto_precio;
            $importe_i       = $items->importe_i;
            $importe_ii      = $items->importe_ii;
            $importe_beca_i  = $items->importe_beca_i;
            $importe_beca_ii = $items->importe_beca_ii;
            $tipo= $items->tipo;
            
            if(strlen($importe_i)==0){
                $importe_i =0;  
            }
            
            if(strlen($importe_ii)==0){
                $importe_ii =0;  
            }
            
            if(strlen($importe_beca_i)==0){
                $importe_beca_i =0;  
            }
            
            if(strlen($importe_beca_ii)==0){
                $importe_beca_ii =0;  
            }
                   
            $query="UPDATE PSTO_PREGRADO_CONCEPTO_PRECIO SET
                        IMPORTE_I       = ".$importe_i.",
                        IMPORTE_II      = ".$importe_ii.",
                        IMPORTE_BECA_I  = ".$importe_beca_i.",
                        IMPORTE_BECA_II = ".$importe_beca_ii.",
                        TIPO     = '".$tipo."'
                    WHERE ID_CONCEPTO_PRECIO=".$id_concepto_precio;

            DB::update($query);

        }
        
        
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }
        if ($id_concepto_precio>0){
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_ACTUALIZAR_TIPOCONCEPTO(
                                        :P_ID_CONCEPTO_PRECIO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                        ); end;");
            $stmt->bindParam(':P_ID_CONCEPTO_PRECIO', $id_concepto_precio, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->execute(); 
        }
    }
    
    public static function updatePregradoConceptoProceso($detail){
        

        foreach($detail as $items){
            $id_concepto_precio=$items->id_concepto_precio;
            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $total_i       = $items->total_i;
            $total_ii      = $items->total_ii;
            
            if(strlen($total_i)==0){
                $total_i =0;  
            }
            if(strlen($total_ii)==0){
                $total_ii =0;  
            }      
            $query="UPDATE PSTO_PREGRADO_PROCESO_CONCEPTO SET
                        TOTAL_I       = ".$total_i.",
                        TOTAL_II      = ".$total_ii."
                    WHERE ID_CONCEPTO_PRECIO=".$id_concepto_precio." 
                    AND ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

            DB::update($query);
            
            
        }
    }
    public static function listPregradoProyeccion($id_entidad,$id_depto_padre,$id_anho,$id_area){                
        $query = "SELECT
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO ORDER BY M.CICLO)=1  THEN M.ID_DEPTO ELSE '' END AS ID_DEPTO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO ORDER BY M.CICLO)=1  THEN M.NOMBRE ELSE '' END AS NOMBRE,
                    M.CICLO,
                    M.ID_PROYECCION,
                    M.CANTIDAD_I,
                    M.HORAS_I,
                    M.CREDITO_I,
                    M.TOTAL_CREDITO_I,
                    M.CANTIDAD_II,
                    M.HORAS_II,
                    M.CREDITO_II,
                    M.TOTAL_CREDITO_II,
                    M.DISERCION_I,
                    M.DISERCION_II
                FROM(
                        SELECT 
                            A.ID_DEPTO,
                            B.ID_AREA,
                            A.ID_PROYECCION,
                            B.DEPARTAMENTO AS NOMBRE,
                            A.CICLO,
                            A.AFECTA_INCREMENTO,
                            A.INCREMENTO,
                            A.CANTIDAD_I,
                            A.CANTIDAD_II,
                            A.CREDITO_I,
                            A.CREDITO_II,
                            A.DISERCION_I,
                            A.DISERCION_II,
                            COALESCE(A.CANTIDAD_I,0)*COALESCE(A.CREDITO_I,0) AS TOTAL_CREDITO_I,
                            COALESCE(A.CANTIDAD_II,0)*COALESCE(A.CREDITO_II,0) AS TOTAL_CREDITO_II,
                            A.HORAS_I,
                            A.HORAS_II
                    FROM PSTO_PREGRADO_PROYECCCION A, vw_area_depto B
                    WHERE A.ID_ENTIDAD=B.ID_ENTIDAD
                    AND A.ID_DEPTO=B.ID_DEPTO
                    AND A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_ANHO=".$id_anho."
                    AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
                    AND B.ID_AREA_PADRE='".$id_area."'
                )M
            ORDER BY M.ID_AREA,M.ID_DEPTO,M.CICLO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPosgradoProyeccion($id_entidad,$id_depto_padre,$id_anho,$id_area){                
        $query = "SELECT 
                        '' as ID_DEPTO,
                        B.ID_AREA,
                        0 as ID_PROYECCION,
                        C.NOMBRE AS NOMBRE,
                        0 as CANTIDAD_I,
                        0 as CANTIDAD_II,
                        0 as HORAS_I,
                        0 as HORAS_II,
                        C.ID_EAP,
                        'C' AS TIPO,
                        0 AS CREDITO_I,
                        0 AS CREDITO_II
                FROM PSTO_PREGRADO_PROYECCCION A, vw_area_depto B,PSTO_EAP C
                WHERE A.ID_ENTIDAD=B.ID_ENTIDAD
                AND A.ID_DEPTO=B.ID_DEPTO
                AND A.ID_EAP=C.ID_EAP
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
                AND B.ID_AREA='".$id_area."'
                GROUP BY C.NOMBRE,C.ID_EAP,B.ID_AREA
                UNION
                SELECT 
                        A.ID_DEPTO,
                        B.ID_AREA,
                        A.ID_PROYECCION,
                        A.NOM_DEPTO AS NOMBRE,
                        A.CANTIDAD_I,
                        A.CANTIDAD_II,
                        A.HORAS_I,
                        A.HORAS_II,
                        C.ID_EAP,
                        'D' AS TIPO,
                        0 AS CREDITO_I,
                        0 AS CREDITO_II
                FROM PSTO_PREGRADO_PROYECCCION A, vw_area_depto B,PSTO_EAP C
                WHERE A.ID_ENTIDAD=B.ID_ENTIDAD
                AND A.ID_DEPTO=B.ID_DEPTO
                AND A.ID_EAP=C.ID_EAP
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
                AND B.ID_AREA='".$id_area."'
                ORDER BY ID_AREA,ID_EAP,TIPO,ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listProesadProyeccion($id_entidad,$id_depto,$id_anho){  
        
                   
        $query = "SELECT
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA ORDER BY M.ID_EAP_DEPTO_AREA,M.CICLO)=1  THEN M.ID_DEPTO ELSE '' END AS ID_DEPTO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA ORDER BY M.ID_EAP_DEPTO_AREA,M.CICLO)=1  THEN M.NOMBRE ELSE '' END AS NOMBRE,
                    M.CICLO,
                    M.ID_EAP_DEPTO_AREA,
                    M.CANTIDAD_I,
                    M.HORAS_I,
                    M.CANTIDAD_II,
                    M.HORAS_II,
                    M.ID_PROYECCION,
                    M.CREDITO_I,
                    M.CREDITO_II
                FROM(
                    SELECT 
                        'R' AS TIPO,
                        P.ID_DEPTO,
                        P.ID_PROYECCION,
                        E.ID_EAP_DEPTO_AREA,
                        E.NOMBRE,
                        P.CICLO,
                        P.CANTIDAD_I,
                        P.CANTIDAD_II,
                        P.HORAS_I,
                        P.HORAS_II,
                        P.CREDITO_I,
                        P.CREDITO_II
                    FROM PSTO_PREGRADO_PROYECCCION P,CONTA_ENTIDAD_DEPTO N,VW_DPTO_EAP E
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_EAP_DEPTO_AREA=E.ID_EAP_DEPTO_AREA
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND P.ID_DEPTO='".$id_depto."'
            )M
            ORDER BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA,M.CICLO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updatePregradoProyeccion($detail,$tipo){
        
        $i=0;
        $id_proyeccion=0;
        foreach($detail as $items){

            $id_proyeccion = $items->id_proyeccion;
            $cantidad_i   = $items->cantidad_i;
            $cantidad_ii  = $items->cantidad_ii;
            
            $credito_i    = $items->credito_i;
            $credito_ii   = $items->credito_ii;
            $horas_i    = 0;//$items->horas_i;
            $horas_ii   = 0;//$items->horas_ii;
            $disercion_i  = 0;//$items->disercion_i;
            $disercion_ii = 0;//$items->disercion_ii;
            
            
            if(strlen($cantidad_i)==0){
                $cantidad_i =0;  
            }
            if(strlen($cantidad_ii)==0){
                $cantidad_ii =0;  
            }
            if(strlen($credito_i)==0){
                $credito_i =0;  
            }
            if(strlen($credito_ii)==0){
                $credito_ii =0;  
            }
            
            $query="UPDATE PSTO_PREGRADO_PROYECCCION SET
                        CANTIDAD_I  =".$cantidad_i.",
                        CANTIDAD_II =".$cantidad_ii.",
                        CREDITO_I   =".$credito_i.",
                        CREDITO_II  =".$credito_ii.",
                        HORAS_I   =".$horas_i.",
                        HORAS_II  =".$horas_ii.",
                        DISERCION_I =".$disercion_i.",
                        DISERCION_II=".$disercion_ii."
                    WHERE ID_PROYECCION=".$id_proyeccion;

            DB::update($query);
            
            $i++;
        }
        
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROYECCCION
                WHERE ID_PROYECCION=".$id_proyeccion;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        
        if($tipo=="PR"){
            AuxiliarData::addProesadProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
        }else{
            AuxiliarData::addPregradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
        }
        
    }
    public static function updatePosgradoProyeccion($detail){
        $i=0;
        $id_proyeccion=0;
        foreach($detail as $items){

            $id_proyeccion = $items->id_proyeccion;
            $cantidad_i   = $items->cantidad_i;
            $cantidad_ii  = $items->cantidad_ii;
            $horas_i    = 0;//$items->horas_i;
            $horas_ii   = 0;//$items->horas_ii;
            $disercion_i  = 0;//$items->disercion_i;
            $disercion_ii = 0;//$items->disercion_ii;
            
            if(strlen($cantidad_i)==0){
                $cantidad_i =0;  
            }
            if(strlen($cantidad_ii)==0){
                $cantidad_ii =0;  
            }

            $query="UPDATE PSTO_PREGRADO_PROYECCCION SET
                        CANTIDAD_I  =".$cantidad_i.",
                        CANTIDAD_II =".$cantidad_ii.",
                        HORAS_I   =".$horas_i.",
                        HORAS_II  =".$horas_ii.",
                        DISERCION_I =".$disercion_i.",
                        DISERCION_II=".$disercion_ii."
                    WHERE ID_PROYECCION=".$id_proyeccion;

            DB::update($query);
            
            $i++;
        }
        
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROYECCCION
                WHERE ID_PROYECCION=".$id_proyeccion;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        
        
        AuxiliarData::updatePosgradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
                
    }    
    public static function updatePosgradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar){
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_POSGRADO_PROCESO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function deletePosgradoProyeccion($id_proyeccion){
        $error=0;
        $msgerror="";
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_DELETE_POSGRADO_PROYECCCION(
                                    :P_ID_PROYECCION, 
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_PROYECCION', $id_proyeccion, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute(); 
    }
    public static function listPregradoProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
         $where=" ";
         if(strlen($id_area)>0){
            $where=" and N.ID_AREA_PADRE=".$id_area." ";
                            
        }
         $query="SELECT 
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.NOMBRE||' '||M.CONVOCA AS NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.CREDITO_2_5,
                    M.TOTALCREDITO_1_I,
                    M.TOTALCREDITO_2_5_I,
                    M.TOTAL_ALUMNO_I,
                    M.TOTALCREDITO_1_II,
                    M.TOTALCREDITO_2_5_II,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.ENSENANZA_I,
                    M.MAT_II,
                    M.ENSENANZA_II,
                    M.DESCUENTO_I,
                    M.DESCUENTO_II,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSENANZA_I+M.ENSENANZA_II AS ENSENANZA,
                    M.DESCUENTO_I+M.DESCUENTO_II AS DESCUENTO,
                    M.MAT_I+M.MAT_II+M.ENSENANZA_I+M.ENSENANZA_II-(M.DESCUENTO_I+M.DESCUENTO_II) AS TOTAL
                FROM(
                  SELECT
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.CREDITO_2_5,
                    P.TOTALCREDITO_1_I,
                    P.TOTALCREDITO_1_II,
                    P.TOTALCREDITO_2_5_I,
                    P.TOTALCREDITO_2_5_II,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.CONVOCA,
                    N.ID_AREA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSENANZA_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSENANZA_II,
                    SUM(CASE WHEN C.TIPO='D' THEN D.TOTAL_I ELSE 0 END) AS DESCUENTO_I,
                    SUM(CASE WHEN C.TIPO='D' THEN D.TOTAL_II ELSE 0 END) AS DESCUENTO_II
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,N.ID_AREA,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA,P.CREDITO_2_5,P.TOTALCREDITO_1_I,
                  P.TOTALCREDITO_1_II,
                  P.TOTALCREDITO_2_5_I,
                  P.TOTALCREDITO_2_5_II,
                  P.ID_PREGRADO_PROCESO
                )M
              ORDER BY M.ID_AREA,M.ID_DEPTO";
      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPosgradoProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
         $where=" ";
         if(strlen($id_area)>0){
            $where=" and N.ID_AREA=".$id_area." ";
                            
        }
         $query="SELECT 
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.ID_AREA,
                    M.NOMBRE,
                    M.NOM_DEPTO,
                    M.IMP_MAT,
                    M.TOTAL_HORAS_I,
                    M.TOTAL_HORAS_II,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.CREDITO_1,
                    M.CREDITO_2_5,
                    M.SUBTOTAL_I,
                    M.SUBTOTAL_II,
                    M.SUBTOTAL,
                    M.DESCUENTO_I,
                    M.DESCUENTO_II,
                    M.DESCUENTO,
                    M.SUBTOTAL_I-M.DESCUENTO_I AS TOTAL_I,
                    M.SUBTOTAL_II-M.DESCUENTO_II AS TOTAL_II,
                    M.SUBTOTAL - M.DESCUENTO AS TOTAL
                FROM(
                  SELECT
                      P.ID_PREGRADO_PROCESO,
                      P.ID_DEPTO,
                      N.DEPARTAMENTO AS NOMBRE,
                      P.NOM_DEPTO,
                      P.IMP_MAT,
                      N.ID_AREA,
                      P.TOTAL_HORAS_I,
                      P.TOTAL_HORAS_II,
                      P.TOTAL_ALUMNO_I,
                      P.TOTAL_ALUMNO_II,
                      P.CREDITO_1,
                      P.CREDITO_2_5,
                      SUM(case when D.tipo<>'D' then D.TOTAL_I else 0 end) AS SUBTOTAL_I,
                      SUM(case when D.tipo<>'D' then D.TOTAL_II else 0 end) AS SUBTOTAL_II,
                      SUM(case when D.tipo<>'D' then D.TOTAL_I else 0 end)+SUM(case when D.tipo<>'D' then D.TOTAL_II else 0 end) AS SUBTOTAL,
                      SUM(case when D.tipo='D' then D.TOTAL_I else 0 end) AS DESCUENTO_I,
                      SUM(case when D.tipo='D' then D.TOTAL_II else 0 end) AS DESCUENTO_II,
                      SUM(case when D.tipo='D' then D.TOTAL_I else 0 end)+SUM(case when D.tipo='D' then D.TOTAL_II else 0 end) AS DESCUENTO   
                    FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C
                    WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                    AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                    AND P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    ".$where."
                    AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                    group by P.ID_PREGRADO_PROCESO,
                      P.ID_DEPTO,
                      N.DEPARTAMENTO,
                      P.IMP_MAT,
                      P.CREDITO_1,
                      P.CREDITO_2_5,
                      P.TOTAL_ALUMNO_I,
                      P.TOTAL_ALUMNO_II,
                      P.NOM_DEPTO,
                      N.ID_AREA,
                      P.TOTAL_HORAS_I,
                      P.TOTAL_HORAS_II
                )M ORDER BY M.ID_AREA,M.ID_DEPTO";
      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listProesadProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
        $where=" ";
         if(strlen($id_area)>0){
            $where=" and P.ID_DEPTO='".$id_area."' ";
                            
        }
         $query="SELECT
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.ENSENANZA_I,
                    M.MAT_II,
                    M.ENSENANZA_II,
                    M.DESCUENTO_I,
                    M.DESCUENTO_II,
                    M.CREDITO_2_5,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSENANZA_I+M.ENSENANZA_II AS ENSENANZA,
                    M.DESCUENTO_I+M.DESCUENTO_II AS DESCUENTO,
                    M.MAT_I+M.MAT_II+M.ENSENANZA_I+M.ENSENANZA_II-(M.DESCUENTO_I+M.DESCUENTO_II) AS TOTAL
                FROM(
                  SELECT 
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.CREDITO_2_5,
                    P.CONVOCA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSENANZA_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSENANZA_II,
                    SUM(CASE WHEN C.TIPO='D' THEN D.TOTAL_I ELSE 0 END) AS DESCUENTO_I,
                    SUM(CASE WHEN C.TIPO='D' THEN D.TOTAL_II ELSE 0 END) AS DESCUENTO_II
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA,P.ID_PREGRADO_PROCESO,
                    P.CREDITO_2_5
                )M
              ORDER BY M.CONVOCA DESC,M.ID_DEPTO";
             

      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listResidenciaProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
        $where=" ";
         if(strlen($id_area)>0){
            $where=" and P.ID_DEPTO='".$id_area."' ";
                            
        }
         $query="SELECT 
                    0 AS ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    0 AS ARMADA,
                    0 AS IMP_MAT,
                    0 AS CREDITO_1,
                    0 AS TOTAL_ALUMNO_I,
                    0 AS TOTAL_ALUMNO_II,
                    0 AS TOTAL_ENSE_I,
                    0 AS TOTAL_ENSE_II,
                    0 AS CREDITO_2_5,
                    0 AS TOTAL_HORAS_I,
                    0 AS TOTAL_HORAS_II,
                    0 AS CUOTA_I,
                    0 AS CUOTA_II,
                    0 AS RESIDENCIA_I,
                    0 AS RESIDENCIA_II,
                    0 AS ALIMENTACION_I,
                    0 AS ALIMENTACION_II,
                    0 AS LAVANDERIA_I,
                    0 AS LAVANDERIA_II,
                    0 AS CUOTA,
                    0 AS RESIDENCIA,
                    0 AS ALIMENTACION,
                    0 AS LAVANDERIA,
                    0 AS TOTAL,
                    'C' AS TIPO,
                    0 AS ID_EAP_DEPTO_AREA
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N
                  WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_DEPTO, N.DEPARTAMENTO
                UNION
                SELECT
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.SECCION AS NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.TOTAL_ENSE_I,
                    M.TOTAL_ENSE_II,
                    M.CREDITO_2_5,
                    M.TOTAL_HORAS_I,
                    M.TOTAL_HORAS_II,
                    M.CUOTA_I,
                    M.CUOTA_II,
                    M.RESIDENCIA_I,
                    M.RESIDENCIA_II,
                    M.ALIMENTACION_I,
                    M.ALIMENTACION_II,
                    M.LAVANDERIA_I,
                    M.LAVANDERIA_II,
                    M.CUOTA_I+M.CUOTA_II AS CUOTA,
                    M.RESIDENCIA_I+M.RESIDENCIA_II AS RESIDENCIA,
                    M.ALIMENTACION_I+M.ALIMENTACION_II AS ALIMENTACION,
                    M.LAVANDERIA_I+M.LAVANDERIA_II AS LAVANDERIA,
                    (M.CUOTA_I+M.CUOTA_II)+(M.RESIDENCIA_I+M.RESIDENCIA_II)+(M.ALIMENTACION_I+M.ALIMENTACION_II)+(M.LAVANDERIA_I+M.LAVANDERIA_II) AS TOTAL,
                    'D' AS TIPO,
                    M.ID_EAP_DEPTO_AREA
                FROM(
                  SELECT 
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    S.NOMBRE AS SECCION,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.CONVOCA,
                    P.TOTAL_ENSE_I,
                    P.TOTAL_ENSE_II,
                    P.CREDITO_2_5,
                    P.TOTAL_HORAS_I,
                    P.TOTAL_HORAS_II,
                    P.ID_EAP_DEPTO_AREA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS CUOTA_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS CUOTA_II,
                    SUM(CASE WHEN C.TIPO='RE' THEN D.TOTAL_I ELSE 0 END) AS RESIDENCIA_I,
                    SUM(CASE WHEN C.TIPO='RE' THEN D.TOTAL_II ELSE 0 END) AS RESIDENCIA_II,
                    SUM(CASE WHEN C.TIPO='SA' THEN D.TOTAL_I ELSE 0 END) AS ALIMENTACION_I,
                    SUM(CASE WHEN C.TIPO='SA' THEN D.TOTAL_II ELSE 0 END) AS ALIMENTACION_II,
                    SUM(CASE WHEN C.TIPO='SL' THEN D.TOTAL_I ELSE 0 END) AS LAVANDERIA_I,
                    SUM(CASE WHEN C.TIPO='SL' THEN D.TOTAL_II ELSE 0 END) AS LAVANDERIA_II
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C,VW_DPTO_EAP S
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_EAP_DEPTO_AREA=S.ID_EAP_DEPTO_AREA
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA,P.ID_PREGRADO_PROCESO,
                  P.TOTAL_ENSE_I,P.TOTAL_ENSE_II,P.CREDITO_2_5,P.TOTAL_HORAS_I,P.TOTAL_HORAS_II,P.ID_EAP_DEPTO_AREA,S.NOMBRE
                )M
              ORDER BY ID_DEPTO,TIPO,ID_EAP_DEPTO_AREA";
             

      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listConservatorioProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
        $where=" ";
         if(strlen($id_area)>0){
            $where=" and P.ID_DEPTO='".$id_area."' ";
                            
        }
         $query="SELECT
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.SECCION AS NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.MAT_II,
                    M.ENSE_I,
                    M.ENSE_II,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSE_I+M.ENSE_II AS ENSE,
                    (M.MAT_I+M.MAT_II)+(M.ENSE_I+M.ENSE_II) AS TOTAL,
                    M.ID_EAP_DEPTO_AREA
                FROM(
                  SELECT 
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    S.NOMBRE AS SECCION,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.ID_EAP_DEPTO_AREA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSE_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSE_II                   
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C,VW_DPTO_EAP S
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_EAP_DEPTO_AREA=S.ID_EAP_DEPTO_AREA
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA,P.ID_PREGRADO_PROCESO,
                  P.TOTAL_ENSE_I,P.TOTAL_ENSE_II,P.CREDITO_2_5,P.TOTAL_HORAS_I,P.TOTAL_HORAS_II,P.ID_EAP_DEPTO_AREA,S.NOMBRE
                )M
              ORDER BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA";
             

      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listIdiomasProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area){
        $where=" ";
         if(strlen($id_area)>0){
            $where=" and P.ID_DEPTO='".$id_area."' ";
                            
        }
         $query="SELECT
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.SECCION AS NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.CREDITO_2_5,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.MAT_II,
                    M.ENSE_I,
                    M.ENSE_II,
                    M.CUOTA_I,
                    M.CUOTA_II,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSE_I+M.ENSE_II AS ENSE,
                    M.CUOTA_I+M.CUOTA_II AS CUOTA,
                    (M.MAT_I+M.MAT_II)+(M.ENSE_I+M.ENSE_II)+(M.CUOTA_I+M.CUOTA_II) AS TOTAL,
                    M.ID_EAP_DEPTO_AREA
                FROM(
                  SELECT 
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    S.NOMBRE AS SECCION,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.CREDITO_2_5,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.ID_EAP_DEPTO_AREA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSE_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSE_II ,
                    SUM(CASE WHEN C.TIPO='CA' THEN D.TOTAL_I ELSE 0 END) AS CUOTA_I,
                    SUM(CASE WHEN C.TIPO='CA' THEN D.TOTAL_II ELSE 0 END) AS CUOTA_II 
                  FROM PSTO_PREGRADO_PROCESO P,vw_area_depto N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C,VW_DPTO_EAP S
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_EAP_DEPTO_AREA=S.ID_EAP_DEPTO_AREA
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  ".$where."
                  AND P.ID_DEPTO_PADRE='".$id_depto_padre."'
                  GROUP BY P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO,
                    S.NOMBRE,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.CREDITO_2_5,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.ID_EAP_DEPTO_AREA
                )M
              ORDER BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA";
             

      
       
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updatePregradoProceso($detail,$tipo){
        
        $i=0;
        $id_pregrado_proceso=0;
        
        if($tipo<>"PG"){
            foreach($detail as $items){
                $id_pregrado_proceso=$items->id_pregrado_proceso;
                $credito_1   = $items->credito_1;
                $credito_2_5  = $items->credito_2_5;

                if(strlen($credito_1)==0){
                    $credito_1 =0;  
                }

                if(strlen($credito_2_5)==0){
                    $credito_2_5 =0;  
                }

                $query="UPDATE PSTO_PREGRADO_PROCESO SET
                            CREDITO_1    = ".$credito_1.",
                            CREDITO_2_5  = ".$credito_2_5."
                        WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

                DB::update($query);

                $i++;
            }
        }
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        
        if($tipo=="PR"){
            AuxiliarData::addProesadProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
        }elseif($tipo=="PG"){
            AuxiliarData::updatePosgradoProceso($id_entidad, $id_anho, $id_depto_padre, $id_auxiliar);
        }else{
            AuxiliarData::addPregradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
        }
        
        
    }
    public static function updatePregradoConceptoDescuento($detail){
        
        $id_pregrado_proceso=0;
        foreach($detail as $items){
            $id_concepto_precio=$items->id_concepto_precio;
            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $total_i       = $items->total_i;
            $total_ii      = $items->total_ii;
            
            if(strlen($total_i)==0){
                $total_i =0;  
            }
            if(strlen($total_ii)==0){
                $total_ii =0;  
            }
                   
            $query="UPDATE PSTO_PREGRADO_PROCESO_CONCEPTO SET
                        TOTAL_I       = ".$total_i.",
                        TOTAL_II      = ".$total_ii."
                    WHERE ID_CONCEPTO_PRECIO=".$id_concepto_precio." 
                    AND ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

            DB::update($query);
            
            
        }
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
         
         AuxiliarData::updatePosgradoProceso($id_entidad, $id_anho, $id_depto_padre, $id_auxiliar);
        
    }
    public static function updateConceptoPosgradoProceso($detail){
        
        $id_pregrado_proceso=0;
        foreach($detail as $items){
            
            $id_concepto_precio=$items->id_concepto_precio;
            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $total_i       = $items->total_i;
            $total_ii      = $items->total_ii;
            $importe_i       = $items->importe_i;
            $importe_ii      = $items->importe_ii;
            
            if(strlen($total_i)==0){
                $total_i =0;  
            }
            if(strlen($total_ii)==0){
                $total_ii =0;  
            }
            if(strlen($importe_i)==0){
                $importe_i =0;  
            }
            if(strlen($importe_ii)==0){
                $importe_ii =0;  
            }
            
            $query="SELECT 
                        TIPO
                    FROM PSTO_PREGRADO_PROCESO_CONCEPTO
                     WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso." AND ID_CONCEPTO_PRECIO=".$id_concepto_precio;

            $oQuery = DB::select($query); 
            $tipo="";
            foreach($oQuery  as $row){
                $tipo=$row->tipo;
            }

            if($tipo=="S"){
                $query="UPDATE PSTO_PREGRADO_PROCESO_CONCEPTO SET
                            TOTAL_I    = ".$total_i.",
                            TOTAL_II  = ".$total_ii."
                        WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso." 
                        AND TIPO='S'
                        AND ID_CONCEPTO_PRECIO=".$id_concepto_precio;
            }else{
                $query="UPDATE PSTO_PREGRADO_PROCESO_CONCEPTO SET
                            IMPORTE_I    = ".$importe_i.",
                            IMPORTE_II  = ".$importe_ii."
                        WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso." 
                        AND TIPO NOT IN('S','D')
                        AND ID_CONCEPTO_PRECIO=".$id_concepto_precio;
            }
            DB::update($query);
        } 
         
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
         
         AuxiliarData::updatePosgradoProceso($id_entidad, $id_anho, $id_depto_padre, $id_auxiliar);
        
    }
    
    public static function listPregradoProcesoConcepto($id_pregado_proceso,$tipo){
        
        if($tipo=='E'){
            $query = "SELECT 
                        A.ID_PREGRADO_PROCESO,
                        A.ID_CONCEPTO_PRECIO,
                        B.ID_ACTIVIDAD,
                        C.NOMBRE,
                        COALESCE(C.ID_DEPTO,P.ID_DEPTO) AS ID_DEPTO,
                        B.ID_ENTIDAD,
                        C.ID_TIPOPLAN,
                        C.ID_CUENTAAASI,
                        C.ID_RESTRICCION, 
                        C.ID_CTACTE,
                        C.ID_TIPOCTACTE,
                        A.IMPORTE_I,
                        A.IMPORTE_II,
                        A.TOTAL_I,
                        A.TOTAL_II,
                        B.TIPO
                    FROM PSTO_PREGRADO_PROCESO_CONCEPTO A, PSTO_PREGRADO_PROCESO P,PSTO_PREGRADO_CONCEPTO_PRECIO B, PSTO_ACTIVIDAD C
                    WHERE A.ID_PREGRADO_PROCESO=P.ID_PREGRADO_PROCESO
                    AND A.ID_CONCEPTO_PRECIO=B.ID_CONCEPTO_PRECIO
                    AND B.ID_ACTIVIDAD=C.ID_ACTIVIDAD
                    AND C.ESDESCUENTO='S'
                    AND A.ID_PREGRADO_PROCESO=".$id_pregado_proceso."
                    ORDER BY B.ID_ACTIVIDAD";
        }else{
            $query = "SELECT 
                        A.ID_PREGRADO_PROCESO,
                        A.ID_CONCEPTO_PRECIO,
                        B.ID_ACTIVIDAD,
                        C.NOMBRE,
                        COALESCE(C.ID_DEPTO,P.ID_DEPTO) AS ID_DEPTO,
                        B.ID_ENTIDAD,
                        C.ID_TIPOPLAN,
                        C.ID_CUENTAAASI,
                        C.ID_RESTRICCION, 
                        C.ID_CTACTE,
                        C.ID_TIPOCTACTE,
                        A.IMPORTE_I,
                        A.IMPORTE_II,
                        B.TIPO,
                        CASE WHEN B.TIPO='D' THEN (-1)*A.TOTAL_I ELSE A.TOTAL_I END AS TOTAL_I,
                        CASE WHEN B.TIPO='D' THEN (-1)*A.TOTAL_II ELSE A.TOTAL_II END AS TOTAL_II
                    FROM PSTO_PREGRADO_PROCESO_CONCEPTO A, PSTO_PREGRADO_PROCESO P,PSTO_PREGRADO_CONCEPTO_PRECIO B, PSTO_ACTIVIDAD C
                    WHERE A.ID_PREGRADO_PROCESO=P.ID_PREGRADO_PROCESO
                    AND A.ID_CONCEPTO_PRECIO=B.ID_CONCEPTO_PRECIO
                    AND B.ID_ACTIVIDAD=C.ID_ACTIVIDAD
                    AND A.ID_PREGRADO_PROCESO=".$id_pregado_proceso."
                    ORDER BY B.ID_ACTIVIDAD";
        }
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function updateResidenciaProceso($detail){
        
       
        $id_pregrado_proceso=0;
        foreach($detail as $items){

            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $residencia   = $items->imp_mat;
            $alimentacion  = $items->credito_1;
            $lavanderia   = $items->credito_2_5;
            $cuota_i  = $items->total_ense_i;
            $cuota_ii  = $items->total_ense_ii;
            $dia_i   = $items->total_horas_i;
            $dia_ii  = $items->total_horas_ii;
            $estudiante_i   = $items->total_alumno_i;
            $estudiante_ii  = $items->total_alumno_ii;
            
            
            if(strlen($residencia)==0){
                $residencia =0;  
            }
            if(strlen($alimentacion)==0){
                $alimentacion =0;  
            }
            if(strlen($lavanderia)==0){
                $lavanderia =0;  
            }
            if(strlen($cuota_i)==0){
                $cuota_i =0;  
            }
            if(strlen($cuota_ii)==0){
                $cuota_ii =0;  
            }
            if(strlen($dia_i)==0){
                $dia_i =0;  
            }
            if(strlen($estudiante_i)==0){
                $estudiante_i =0;  
            }
            if(strlen($estudiante_i)==0){
                $estudiante_i =0;  
            }
            $query="UPDATE PSTO_PREGRADO_PROCESO SET
                        IMP_MAT=".$residencia.",
                        CREDITO_1=".$alimentacion.",
                        CREDITO_2_5=".$lavanderia.",
                        TOTAL_ENSE_I=".$cuota_i.",
                        TOTAL_ENSE_II=".$cuota_ii.",
                        TOTAL_HORAS_I=".$dia_i.",
                        TOTAL_HORAS_II=".$dia_ii.",
                        TOTAL_ALUMNO_I=".$estudiante_i.",
                        TOTAL_ALUMNO_II=".$estudiante_ii."
                    WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

            DB::update($query);
            
            
        }
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        AuxiliarData::addResidenciaProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
        
    }
    
    public static function updateConservatorioProceso($detail){
        
        $id_pregrado_proceso=0;
        foreach($detail as $items){

            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $armada=$items->armada;
            $matricula   = $items->imp_mat;
            $esenanza = $items->credito_1;
            $estudiante_i   = $items->total_alumno_i;
            $estudiante_ii  = $items->total_alumno_ii;
            
            
            if(strlen($armada)==0){
                $armada =0;  
            }
            if(strlen($matricula)==0){
                $matricula =0;  
            }
            if(strlen($esenanza)==0){
                $esenanza =0;  
            }
            if(strlen($estudiante_i)==0){
                $estudiante_i =0;  
            }
            if(strlen($estudiante_ii)==0){
                $estudiante_ii =0;  
            }
            $query="UPDATE PSTO_PREGRADO_PROCESO SET
                        ARMADA=".$armada.",
                        IMP_MAT=".$matricula .",
                        CREDITO_1=".$esenanza.",
                        TOTAL_ALUMNO_I=".$estudiante_i.",
                        TOTAL_ALUMNO_II=".$estudiante_ii."
                    WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

            DB::update($query);
            
            
        }
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        AuxiliarData::addConservatorioProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
    }
    public static function updateIdiomasProceso($detail){
        
        $id_pregrado_proceso=0;
        foreach($detail as $items){

            $id_pregrado_proceso=$items->id_pregrado_proceso;
            $material  = $items->imp_mat;
            $esenanza = $items->credito_1;
            $cuotaalumno = $items->credito_2_5;
            $estudiante_i   = $items->total_alumno_i;
            $estudiante_ii  = $items->total_alumno_ii;
            
            if(strlen($material)==0){
                $material =0;  
            }
            if(strlen($esenanza)==0){
                $esenanza =0;  
            }
            if(strlen($cuotaalumno)==0){
                $cuotaalumno =0;  
            }
            if(strlen($estudiante_i)==0){
                $estudiante_i =0;  
            }
            if(strlen($estudiante_ii)==0){
                $estudiante_ii =0;  
            }
            
            $query="UPDATE PSTO_PREGRADO_PROCESO SET
                        CREDITO_2_5=".$cuotaalumno.",
                        IMP_MAT=".$material .",
                        CREDITO_1=".$esenanza.",
                        TOTAL_ALUMNO_I=".$estudiante_i.",
                        TOTAL_ALUMNO_II=".$estudiante_ii."
                    WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;

            DB::update($query);
            
            
        }
        $id_entidad=0;
        $id_anho=0;
        $id_depto_padre="-";
        $id_auxiliar=0;
        
        $query="SELECT 
                    ID_ENTIDAD,
                    ID_ANHO,
                    ID_DEPTO_PADRE,
                    ID_AUXILIAR
                FROM PSTO_PREGRADO_PROCESO
                WHERE ID_PREGRADO_PROCESO=".$id_pregrado_proceso;
        
        $oQuery = DB::select($query); 
        
        foreach($oQuery  as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto_padre=$row->id_depto_padre;
            $id_auxiliar=$row->id_auxiliar;
        }
        AuxiliarData::addConservatorioProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
    }
    public static function estadoPresupuesto($id_entidad,$id_anho,$id_depto,$id_auxiliar){
        $query = "SELECT 
                        P.ESTADO,
                        CASE WHEN P.ESTADO='1' THEN 'REGISTRADO' WHEN P.ESTADO='2' THEN 'APROBADO' WHEN P.ESTADO='0' THEN 'ANULADO' ELSE '' END AS ESTADODESC
                    FROM PSTO_PRESUPUESTO P,
                    PSTO_EVENTO E
                    WHERE P.ID_EVENTO=E.ID_EVENTO
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_DEPTO='".$id_depto."'
                    AND P.ID_ANHO=".$id_anho."
                    AND E.ID_AUXILIAR=".$id_auxiliar;
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listTesisProceso($id_entidad,$id_depto_padre,$id_anho){
        $query = "SELECT 
                    A.ID_TESIS_PROCESO,
                    A.ID_EAP_DEPTO,
                    B.ID_DEPTO,
                    C.NOMBRE,
                    A.COSTO,
                    A.EGRESADOS,
                    A.PORCENTAJE,
                    A.TESISTAS,
                    A.PRECIO,
                    A.PORCCOSTO,
                    A.TESISTAS*A.PRECIO AS TOTAL,
                    B.ID_ENTIDAD,
                    A.ID_ANHO,
                    B.ID_DEPTO_PADRE
            FROM PSTO_TESIS_PROCESO A, PSTO_TESIS_EAP_DEPTO B, PSTO_TESIS_EAP C
            WHERE A.ID_EAP_DEPTO=B.ID_EAP_DEPTO
            AND B.ID_EAP=C.ID_EAP
            AND B.ID_ENTIDAD=".$id_entidad."
            AND A.ID_ANHO=".$id_anho."
            AND B.ID_DEPTO_PADRE='".$id_depto_padre."'
            ORDER BY B.ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listTesisEapDeptoConcepto($id_entidad,$id_depto_padre,$id_anho,$id_eap_depto){
        $query = "SELECT 
                    A.ID_EAP_DEPTO_CONCEPTO,
                    B.NOMBRE,
                    A.IMPORTE
            FROM PSTO_TESIS_EAP_DEPTO_CONCEPTO A,PSTO_TESIS_CONCEPTO B
            WHERE A.ID_CONCEPTO=B.ID_CONCEPTO
            AND A.ID_ENTIDAD=".$id_entidad."
            AND A.ID_ANHO=".$id_anho."
            AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
            AND A.ID_EAP_DEPTO=".$id_eap_depto."
            ORDER BY A.ID_CONCEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updateTesisProceso($id_tesis_procesos,$costos,$egresados,$porcentajes,$porccostos){
        
        $i=0;
        foreach($id_tesis_procesos as $id_tesis_proceso){
            
            $costo   = $costos[$i];
            $egresado  = $egresados[$i];
            $porcentaje   = $porcentajes[$i];
            $porccosto   = $porccostos[$i];
   
            $tesista=ceil(($porcentaje/100)*$egresado);
            
            $query="UPDATE PSTO_TESIS_PROCESO SET
                        COSTO=".$costo.",
                        EGRESADOS=".$egresado.",
                        PORCENTAJE=".$porcentaje.",
                        TESISTAS=".$tesista.",
                        PORCCOSTO=".$porccosto."
                    WHERE ID_TESIS_PROCESO=".$id_tesis_proceso;

            DB::update($query);
            
            $i++;
        }
    }
    
    public static function listPlanilla($id_entidad,$id_depto_padre,$id_anho){
        $query = "SELECT 
                        A.ID_AREA_PADRE,
                        A.AREA_PADRE, 
                        COUNT(P.ID_PSTO_PLANILLA) AS CANTIDAD,
                        SUM(P.TOTAL) AS TOTAL
                    FROM  VW_AREA_DEPTO A LEFT JOIN
                    PSTO_PLLA_PLANILLA P
                    ON A.ID_DEPTO=P.ID_DEPTO
                    AND P.ID_ANHO=".$id_anho."
                    WHERE A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_DEPTO_AREA='".$id_depto_padre."'
                    GROUP BY A.ID_AREA_PADRE,A.AREA_PADRE
                    ORDER BY A.AREA_PADRE";
        $oQuery = DB::select($query); 
        
        $data=array();
        
        $cantidad=0;
        $total=0;
        $cantidads=0;
        $totals=0;
        
        foreach($oQuery as $row){
            
            $sql = "SELECT 
                        A.ID_AREA,
                        A.AREA, 
                        COUNT(P.ID_PSTO_PLANILLA) AS CANTIDAD,
                        SUM(P.TOTAL) AS TOTAL
                    FROM  VW_AREA_DEPTO A LEFT JOIN
                    PSTO_PLLA_PLANILLA P
                    ON A.ID_DEPTO=P.ID_DEPTO
                    AND P.ID_ANHO=".$id_anho."
                    WHERE A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_DEPTO_AREA='".$id_depto_padre."'
                    AND A.ID_AREA_PADRE=$row->id_area_padre  
                    GROUP BY A.ID_AREA,A.AREA
                    ORDER BY A.AREA";
            $Query = DB::select($sql); 
            $i=1;
            
            foreach($Query as $ro){
                $item=array();
                $item["num"]=$i;
                if($i==1){
                    $item["id_area_padre"]=$row->id_area_padre;
                    $item["area_padre"]=$row->area_padre;
                    $item["cantidad"]=$row->cantidad;
                    $item["total"]=$row->total;
                    $item["subarea"]=COUNT($Query); 
                    $cantidad=$cantidad + $row->cantidad;
                    $total= $total + $row->total;
                }else{
                    $item["id_area_padre"]='0';
                    $item["area_padre"]='0';
                    $item["cantidad"]='0';
                    $item["total"]='0';
                    $item["subarea"]=0;
                }
                
                $item["id_area"]=$ro->id_area;
                $item["area"]=$ro->area;
                $item["cantidads"]=$ro->cantidad;
                $item["totals"]=$ro->total;
                $i++;
                $data[]=$item;
                
                $cantidads=$cantidads + $ro->cantidad;
                $totals= $totals + $ro->total;
            }
            
        }
        $item["num"]=1;
        $item["id_area_padre"]='0';
        $item["area_padre"]='Total:';
        $item["cantidad"]=$cantidad;
        $item["total"]=$total;
        $item["subarea"]=1;
        $item["id_area"]='0';
        $item["area"]='Total:';
        $item["cantidads"]=$cantidads;
        $item["totals"]=$totals;
        $data[]=$item;
        return $data;
    }
    public static function presupuestoPlanilla($id_entidad,$id_depto_padre,$id_anho,$id_persona,$id_pstonegocio,$id_eje,$id_auxiliar){

        $nerror=0;
        $msgerror='';
        for($i=1;$i<=200;$i++){
            $msgerror.='0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_PLANILLA_PRESUPUESTO(
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO_PADRE,
                                        :P_ID_ANHO,
                                        :P_ID_PERSONA,
                                        :P_ID_PSTONEGOCIO,
                                        :P_ID_EJE,
                                        :P_ID_AUXILIAR,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PSTONEGOCIO', $id_pstonegocio, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_EJE', $id_eje, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute();  
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;

    }
    public static function listConsolPlanilla($id_entidad,$id_depto_padre,$id_anho,$id_area_padre,$id_area,$id_depto){
        
        $where='';
        if(strlen($id_area)>0){
            $where.="AND A.ID_AREA=".$id_area." ";
        }
        if(strlen($id_depto)>0){
            $where.="AND A.ID_DEPTO='".$id_depto."' ";
        }
        
        $query = "SELECT 
                        A.ID_AREA,
                        A.AREA, 
                        COUNT(P.ID_PSTO_PLANILLA) AS CANTIDAD,
                        SUM(P.TOTAL) AS TOTAL
                    FROM  VW_AREA_DEPTO A LEFT JOIN
                    PSTO_PLLA_PLANILLA P
                    ON A.ID_DEPTO=P.ID_DEPTO
                    AND P.ID_ANHO=".$id_anho."
                    WHERE A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_DEPTO_AREA='".$id_depto_padre."'
                    AND A.ID_AREA_PADRE='".$id_area_padre."'
                    ".$where."
                    GROUP BY A.ID_AREA,A.AREA
                    ORDER BY A.AREA";
        
       
        $oQuery = DB::select($query); 
        
        $data=array();
        
        $cantidad=0;
        $total=0;
        $cantidads=0;
        $totals=0;
      
        foreach($oQuery as $row){
            
            $sql = "SELECT 
                        A.ID_DEPTO,
                        A.DEPARTAMENTO, 
                        COUNT(P.ID_PSTO_PLANILLA) AS CANTIDAD,
                        SUM(P.TOTAL) AS TOTAL
                    FROM  VW_AREA_DEPTO A LEFT JOIN
                    PSTO_PLLA_PLANILLA P
                    ON A.ID_DEPTO=P.ID_DEPTO
                    AND P.ID_ANHO=".$id_anho."
                    WHERE A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_DEPTO_AREA='".$id_depto_padre."'
                    AND A.ID_AREA=$row->id_area 
                    AND A.ID_AREA_PADRE='".$id_area_padre."'
                    ".$where."
                    GROUP BY A.ID_DEPTO,A.DEPARTAMENTO
                    ORDER BY A.DEPARTAMENTO";
            $Query = DB::select($sql); 
            $i=1;
            
            foreach($Query as $ro){
                $item=array();
                $item["num"]=$i;
                if($i==1){
                    $item["id_area"]=$row->id_area;
                    $item["area"]=$row->area;
                    $item["cantidad"]=$row->cantidad;
                    $item["total"]=$row->total;
                    $item["ndep"]=COUNT($Query); 
                    $cantidad=$cantidad + $row->cantidad;
                    $total= $total + $row->total;
                }else{
                    $item["id_depto"]='0';
                    $item["departamento"]='0';
                    $item["cantidad"]='0';
                    $item["total"]='0';
                    $item["ndep"]=0;
                }
                
                $item["id_depto"]=$ro->id_depto;
                $item["departamento"]=$ro->departamento;
                $item["cantidads"]=$ro->cantidad;
                $item["totals"]=$ro->total;
                $i++;
                $data[]=$item;
                
                $cantidads=$cantidads + $ro->cantidad;
                $totals= $totals + $ro->total;
            }
            
        }
        $item["num"]=1;
        $item["id_area"]='0';
        $item["area"]='Total:';
        $item["cantidad"]=$cantidad;
        $item["total"]=$total;
        $item["ndep"]=1;
        $item["id_depto"]='0';
        $item["departamento"]='Total:';
        $item["cantidads"]=$cantidads;
        $item["totals"]=$totals;
        $data[]=$item;
        return $data;
    }
}
?>

