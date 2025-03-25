<?php
namespace App\Http\Data\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class PayrollData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public static function listParametro($id_anho){
        
        $query = "SELECT 
                    A.ID_PARAMETRO,
                    A.NOMBRE,
                    A.FORMULA,
                    A.IMPORTE,
                    A.ESTADO,
                    A.ORDEN,
                    B.ID_ANHO,
                    B.EJE_FORMULA,
                    B.IMPORTE AS IMPORTE_EJE,
                    CASE WHEN A.FORMULA IS NULL THEN 0 ELSE 1 END AS INDICADOR
                FROM  PSTO_PLLA_PARAMETROS A,PSTO_PLLA_PARAMETROS_VALOR B
                WHERE A.ID_PARAMETRO=B.ID_PARAMETRO
                AND B.ID_ANHO=".$id_anho."
                ORDER BY A.ORDEN";
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function updateParametro($detail){
        
        $id_anho      = 0;
        foreach($detail as $items){
            $id_parametro = $items->id_parametro;
            $importe      = $items->importe_eje;
            if(strlen($importe)==0){
                $importe=0;
            }
                               
            $query="UPDATE PSTO_PLLA_PARAMETROS_VALOR SET
                         IMPORTE  = ".$importe."
                    WHERE ID_PARAMETRO = '".$id_parametro."' 
                    and id_anho=".$items->id_anho;
            //echo $query;
            DB::update($query);
            
            $id_anho      = $items->id_anho;
        }
        
                
        PayrollData::procParametro($id_anho); 

    }
    
    public static function procParametro($id_anho){

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_GENERAR_PARAMETROS(
                                    :P_ID_ANHO 
                                     ); end;");
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->execute(); 

    }
    public static function listProcesoCargo($id_entidad,$id_depto,$id_anho){
        
        $query ="SELECT
                ROW_NUMBER() OVER (PARTITION BY X.ID_CARGO,X.ID_CONDICION_ESCALA ORDER BY X.ID_CARGO,X.ID_CONDICION_ESCALA) AS CONTAR,
                COALESCE(A.ID_CARGO_PROCESO,0) AS ID_CARGO_PROCESO,
                A.ID_RENOVABLE,
                A.ID_SEXO,
                A.ID_EDAD,
                A.ID_NIVEL_EDU,
                A.ID_TIPOESTADOCIVIL,
                A.ID_TIEMPOTRABAJO,
                A.ID_TEMPORADA,
                A.CANTIDAD,
                B.NOMBRE AS CARGO,
                C.NOMBRE AS RENOVABLE,
                D.NOMBRE AS SEXO,
                E.NOMBRE AS EDAD,
                F.NOMBRE AS NIVEL_EDU,
                G.NOMBRE AS TIPOESTADOCIVIL,
                H.NOMBRE AS TIEMPOTRABAJO,
                I.NOMBRE AS TEMPORADA,
                FC_PSTO_PROFESION(A.ID_CARGO_PROCESO) AS PROFESION,
                FC_PSTO_TIPOCONTRATO(A.ID_CARGO_PROCESO) AS TIPOCONTRATO,
                FC_PSTO_COND_LABORAL(A.ID_CARGO_PROCESO) AS CONDICION_LABORAL,
                case when I.INICIO1 is not null then FC_PSTO_MES_NOMBRE(I.INICIO,'C')||' o '||FC_PSTO_MES_NOMBRE(I.INICIO1,'C') else FC_PSTO_MES_NOMBRE(I.INICIO,'C') end as finicio,
                case when I.FIN1 is not null then FC_PSTO_MES_NOMBRE(I.FIN,'C')||' o '||FC_PSTO_MES_NOMBRE(I.FIN1,'C') else   FC_PSTO_MES_NOMBRE(I.FIN,'C') end  as ffinal,
                I.INICIO,
                I.FIN,
                I.INICIO1,
                I.FIN1,
                COALESCE(A.ID_CARGOSUELDO_ESCALA,0) AS EXISTE,
                X.ID_CARGOSUELDO_ESCALA,
                X.ID_ANHO,
                X.ID_ENTIDAD,
                A.ID_DEPTO,
                X.ID_DEPTO_PADRE,
                X.ID_CARGO,
                X.ID_CONDICION_ESCALA,
                X.OBSERVACION,
                X.MINIMO,
                X.MAXIMO,
                X.TIPO_MIN,
                X.TIPO_MAX,
                X.BONO_MIN,
                X.BONO_MAX,
                X.VIA_MIN,
                X.VIA_MAX,
                Y.NOMBRE AS CONDICION_ESCALA
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA X 
            INNER JOIN APS_CARGO B
            ON X.ID_CARGO=B.ID_CARGO
            INNER JOIN PSTO_PLLA_CONDICION_ESCALA Y
            ON Y.ID_CONDICION_ESCALA=X.ID_CONDICION_ESCALA
            INNER JOIN PSTO_PLLA_CARGO_PROCESO A  
            ON A.ID_CARGOSUELDO_ESCALA=X.ID_CARGOSUELDO_ESCALA
            LEFT JOIN PSTO_PLLA_RENOVABLE C
            ON A.ID_RENOVABLE=C.ID_RENOVABLE
            LEFT JOIN APS_SEXO D
            ON A.ID_SEXO=D.ID_SEXO
            LEFT JOIN PSTO_PLLA_EDAD E
            ON A.ID_EDAD=E.ID_EDAD
            LEFT JOIN APS_NIVEL_EDUCATIVO F
            ON A.ID_NIVEL_EDU=F.ID_NIVEL_EDU
            LEFT JOIN TIPO_ESTADO_CIVIL G
            ON A.ID_TIPOESTADOCIVIL=G.ID_TIPOESTADOCIVIL
            LEFT JOIN APS_TIEMPO_TRABAJO H
            ON A.ID_TIEMPOTRABAJO=H.ID_TIEMPOTRABAJO
            LEFT JOIN PSTO_PLLA_TEMPORADA I
            ON A.ID_TEMPORADA=I.ID_TEMPORADA
            WHERE X.ID_ENTIDAD=".$id_entidad."
            AND X.ID_ANHO=".$id_anho."
            AND A.ID_DEPTO='".$id_depto."'
            ORDER BY B.NOMBRE,
            X.ID_CONDICION_ESCALA,
            A.ID_RENOVABLE,
            A.ID_SEXO,
            A.ID_EDAD,
            A.ID_NIVEL_EDU,
            A.ID_TIPOESTADOCIVIL,
            A.ID_TIEMPOTRABAJO,
            A.ID_TEMPORADA";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showProcesoCargo($id_cargo_proceso){
        
        $query ="SELECT
                A.ID_CARGO_PROCESO,
                A.ID_ANHO,
                A.ID_ENTIDAD,
                A.ID_DEPTO,
                A.ID_DEPTO_PADRE,
                A.ID_CARGO,
                A.ID_RENOVABLE,
                A.ID_SEXO,
                A.ID_EDAD,
                A.ID_NIVEL_EDU,
                A.ID_TIPOESTADOCIVIL,
                A.ID_TIEMPOTRABAJO,
                A.ID_TEMPORADA,
                A.CANTIDAD,
                A.ID_CARGOSUELDO_ESCALA,
                X.ID_CONDICION_ESCALA,
                X.OBSERVACION,
                X.MINIMO,
                X.MAXIMO,
                X.TIPO_MIN,
                X.TIPO_MAX,
                X.BONO_MIN,
                X.BONO_MAX,
                X.VIA_MIN,
                X.VIA_MAX,
                Y.NOMBRE AS CONDICION_ESCALA
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA X 
            INNER JOIN PSTO_PLLA_CARGO_PROCESO A  
            ON A.ID_CARGOSUELDO_ESCALA=X.ID_CARGOSUELDO_ESCALA
            INNER JOIN PSTO_PLLA_CONDICION_ESCALA Y
            ON Y.ID_CONDICION_ESCALA=X.ID_CONDICION_ESCALA
            WHERE A.ID_CARGO_PROCESO=".$id_cargo_proceso;
        
        $proccargo = DB::select($query);   
        $query="SELECT
                A.ID_COND_LAB AS ID,
                B.NOMBRE
                FROM PSTO_PLLA_CARGO_PROCESO_CONLAB A,APS_CONDICION_LABORAL B
                WHERE A.ID_COND_LAB=B.ID_COND_LAB
                AND A.ID_CARGO_PROCESO=".$id_cargo_proceso;
        
        $proccargoconlab = DB::select($query); 
        $query="SELECT
                A.ID_PROFESION AS ID,
                B.NOMBRE
                FROM PSTO_PLLA_CARGO_PROCESO_PROF A,APS_PROFESION B
                WHERE A.ID_PROFESION=B.ID_PROFESION
                AND A.ID_CARGO_PROCESO=".$id_cargo_proceso;
        
        $proccargoprof = DB::select($query); 
        $query="SELECT
                A.ID_TIPOCONTRATO AS ID,
                B.NOMBRE
                FROM PSTO_PLLA_CARGO_PROCESO_TIPCON A,TIPO_CONTRATO B
                WHERE A.ID_TIPOCONTRATO=B.ID_TIPOCONTRATO
                AND A.ID_CARGO_PROCESO=".$id_cargo_proceso;
        $proccargotipcon = DB::select($query); 
        
        $ret=[
           'proccargo'=> $proccargo,
           'proccargoconlab'=> $proccargoconlab,
           'proccargoprof'=> $proccargoprof,
           'proccargotipcon'=> $proccargotipcon,
        ];
        
        return $ret;
    }
    public static function addProcesoCargo($param){
        
        $nerror=0;
        $id_cargo_proceso=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
              
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_AGREGAR_PLANILLA_CARGO(
                                        :P_ID_CARGOSUELDO_ESCALA,
                                        :P_ID_DEPTO,
                                        :P_ID_RENOVABLE,
                                        :P_ID_SEXO,
                                        :P_ID_EDAD,
                                        :P_ID_NIVEL_EDU,
                                        :P_ID_TIPOESTADOCIVIL,
                                        :P_ID_TIEMPOTRABAJO,
                                        :P_ID_TEMPORADA,
                                        :P_CANTIDAD,
                                        :P_ID_CARGO_PROCESO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_CARGOSUELDO_ESCALA', $param->id_cargosueldo_escala, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $param->id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_RENOVABLE', $param->id_renovable, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_SEXO', $param->id_sexo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_EDAD', $param->id_edad, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_NIVEL_EDU', $param->id_nivel_edu, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOESTADOCIVIL', $param->id_tipoestadocivil, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIEMPOTRABAJO', $param->id_tiempotrabajo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TEMPORADA', $param->id_temporada, PDO::PARAM_STR);
        $stmt->bindParam(':P_CANTIDAD', $param->cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CARGO_PROCESO', $id_cargo_proceso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute();  
        
        if($nerror==0){
            $paramprof = $param->profesion;
            foreach($paramprof as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_PROF')->insert(
                        array('ID_CARGO_PROCESO'=>$id_cargo_proceso,
                            'ID_PROFESION' => $item->id
                            )
                    );
            }
            $paramcondlab = $param->condlab;
            foreach($paramcondlab as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_CONLAB')->insert(
                        array('ID_CARGO_PROCESO'=>$id_cargo_proceso,
                            'ID_COND_LAB' => $item->id
                            )
                    );
            }
            $paramtipcont = $param->tipcont;
            foreach($paramtipcont as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_TIPCON')->insert(
                        array('ID_CARGO_PROCESO'=>$id_cargo_proceso,
                            'ID_TIPOCONTRATO' => $item->id
                            )
                    );
            }
        }
               
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;
        
                
    }
    public static function updateProcesoCargo($param){
        
        
            
        $nerror=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_ACTUALIZAR_PLANILLA_CARGO(
                                        :P_ID_CARGO_PROCESO,
                                        :P_ID_RENOVABLE,
                                        :P_ID_SEXO,
                                        :P_ID_EDAD,
                                        :P_ID_NIVEL_EDU,
                                        :P_ID_TIPOESTADOCIVIL,
                                        :P_ID_TIEMPOTRABAJO,
                                        :P_ID_TEMPORADA,
                                        :P_CANTIDAD,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_CARGO_PROCESO', $param->id_cargo_proceso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_RENOVABLE', $param->id_renovable, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_SEXO', $param->id_sexo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_EDAD', $param->id_edad, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_NIVEL_EDU', $param->id_nivel_edu, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOESTADOCIVIL', $param->id_tipoestadocivil, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIEMPOTRABAJO', $param->id_tiempotrabajo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TEMPORADA', $param->id_temporada, PDO::PARAM_STR);
        $stmt->bindParam(':P_CANTIDAD', $param->cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);

        $stmt->execute();  
        
        if($nerror==0){
            $paramprof = $param->profesion;
            $query="Delete from PSTO_PLLA_CARGO_PROCESO_PROF 
                           where ID_CARGO_PROCESO = ".$param->id_cargo_proceso; 
   
            DB::delete($query);
            
            foreach($paramprof as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_PROF')->insert(
                        array('ID_CARGO_PROCESO'=>$param->id_cargo_proceso,
                            'ID_PROFESION' => $item->id
                            )
                    );
            }
            $paramcondlab = $param->condlab;
            $query="Delete from PSTO_PLLA_CARGO_PROCESO_CONLAB 
                           where ID_CARGO_PROCESO = ".$param->id_cargo_proceso; 
   
            DB::delete($query);
            foreach($paramcondlab as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_CONLAB')->insert(
                        array('ID_CARGO_PROCESO'=>$param->id_cargo_proceso,
                            'ID_COND_LAB' => $item->id
                            )
                    );
            }
            $paramtipcont = $param->tipcont;
            $query="Delete from PSTO_PLLA_CARGO_PROCESO_TIPCON 
                           where ID_CARGO_PROCESO = ".$param->id_cargo_proceso; 
   
            DB::delete($query);
            foreach($paramtipcont as $item){
                $data= DB::table('PSTO_PLLA_CARGO_PROCESO_TIPCON')->insert(
                        array('ID_CARGO_PROCESO'=>$param->id_cargo_proceso,
                            'ID_TIPOCONTRATO' => $item->id
                            )
                    );
            }
        }
        $return=[
                'nerror'=>$nerror,
                'msgerror'=>$msgerror
        ];

        return $return;

    } 
    public static function deleteProcesoCargo($id_cargo_proceso){
        
        $query ="SELECT
                    count(*) as contar
                FROM PSTO_PLLA_PLANILLA
                WHERE ID_CARGO_PROCESO=".$id_cargo_proceso;
        
        $oQuery = DB::select($query);   
        
        $contar=0;

        foreach($oQuery as $row){
            $contar=$row->contar;
        }
        
        $return=[
                'nerror'=>0,
                'msgerror'=>''
        ];
        if($contar==0){
            $query = "DELETE FROM PSTO_PLLA_CARGO_PROCESO
                WHERE ID_CARGO_PROCESO = ".$id_cargo_proceso;
            DB::delete($query);
        }else{
           $return=[
                    'nerror'=>1,
                    'msgerror'=>'Existen personas asignadas'
            ]; 
        }
        
        return $return;
      
    }
    public static function listCargoSueldoEscala($id_entidad,$id_depto,$id_anho,$id_cargo,$id_condicion_escala){
        
        $where ="";
        
        if(strlen($id_cargo)>0){
             $where =" and P.ID_CARGO = ".$id_cargo;
        }
        
        if(strlen($id_condicion_escala)>0){
             $where.= " and P.ID_CONDICION_ESCALA = ".$id_condicion_escala;
        }
        
        $query ="SELECT 
                    P.ID_CARGOSUELDO_ESCALA,
                    P.ID_ANHO,
                    P.ID_ENTIDAD,
                    P.ID_DEPTO_PADRE,
                    P.ID_CARGO,
                    P.OBSERVACION,
                    P.MINIMO,
                    P.MAXIMO,
                    P.TIPO_MIN,
                    P.TIPO_MAX,
                    CASE WHEN P.TIPO_MIN='1' THEN 'RMV' ELSE '' END AS TIPO_MIN_DESC,
                    CASE WHEN P.TIPO_MAX='1' THEN 'RMV' ELSE '' END AS TIPO_MAX_DESC,
                    P.BONO_MIN,
                    P.BONO_MAX,
                    P.VIA_MIN,
                    P.VIA_MAX,
                    C.NOMBRE,
                    P.ID_CONDICION_ESCALA,
                    E.NOMBRE as CONDICION_ESCALA
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA P,APS_CARGO C,PSTO_PLLA_CONDICION_ESCALA E
            WHERE P.ID_CARGO=C.ID_CARGO
            AND P.ID_CONDICION_ESCALA=E.ID_CONDICION_ESCALA
            AND P.ID_ENTIDAD=".$id_entidad."
            AND P.ID_ANHO=".$id_anho."
            AND P.ID_DEPTO_PADRE='".$id_depto."'
            ".$where."
            ORDER BY C.NOMBRE,P.ID_CONDICION_ESCALA";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showCargoSueldoEscala($id_cargosueldo_escala){
  
        
        $query ="SELECT 
                    case when A.ID_CONDICION_ESCALA='M' then 'R3' else null end as id_renovable,
                    case when A.ID_CONDICION_ESCALA='M' then 'T1' else null end as id_temporada,
                    case when A.ID_CONDICION_ESCALA='M' then '1' else null end as ID_TIEMPOTRABAJO,
                    null as id_cargo_proceso,
                    null as id_sexo,
                    null as id_edad,
                    null as id_nivel_edu,
                    null as id_tipoestadocivil,                    
                    A.ID_CARGOSUELDO_ESCALA,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_DEPTO_PADRE,
                    A.ID_CARGO,
                    A.ID_CONDICION_ESCALA,
                    A.OBSERVACION,
                    A.MINIMO,
                    A.MAXIMO,
                    A.TIPO_MIN,
                    A.TIPO_MAX,
                    A.BONO_MIN,
                    A.BONO_MAX,
                    A.VIA_MIN,
                    A.VIA_MAX,
                    E.NOMBRE AS CONDICION_ESCALA
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA A,
            PSTO_PLLA_CONDICION_ESCALA E
            WHERE A.ID_CONDICION_ESCALA=E.ID_CONDICION_ESCALA
            AND A.ID_CARGOSUELDO_ESCALA=".$id_cargosueldo_escala;

        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function cargoSueldoEscalaAll($id_entidad,$id_anho,$id_depto_padre,$id_depto,$id_cargosueldo_escala){
  
        $where="";
        
        if($id_cargosueldo_escala=="0"){
            $where=" AND A.ID_CARGOSUELDO_ESCALA NOT IN(
                    SELECT X.ID_CARGOSUELDO_ESCALA FROM PSTO_PLLA_CARGO_PROCESO X
                    WHERE X.ID_ANHO=A.ID_ANHO
                    AND X.ID_ENTIDAD=A.ID_ENTIDAD
                    AND X.ID_DEPTO_PADRE=A.ID_DEPTO_PADRE
                    AND X.ID_DEPTO='".$id_depto."'
            ) ";
        }else{
            $where=" AND A.ID_CARGOSUELDO_ESCALA=".$id_cargosueldo_escala." ";
            
        }
        
        $query ="SELECT 
                    A.ID_CARGOSUELDO_ESCALA,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_DEPTO_PADRE,
                    A.ID_CARGO,
                    A.ID_CONDICION_ESCALA,
                    A.OBSERVACION,
                    A.MINIMO,
                    A.MAXIMO,
                    A.TIPO_MIN,
                    A.TIPO_MAX,
                    A.BONO_MIN,
                    A.BONO_MAX,
                    A.VIA_MIN,
                    A.VIA_MAX,
                    E.NOMBRE AS CONDICION_ESCALA,
                    C.NOMBRE AS CARGO
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA A,
            PSTO_PLLA_CONDICION_ESCALA E,
            APS_CARGO C
            WHERE A.ID_CONDICION_ESCALA=E.ID_CONDICION_ESCALA
            AND A.ID_CARGO=C.ID_CARGO
            AND A.ID_ANHO=".$id_anho."
            AND A.ID_ENTIDAD=".$id_entidad."
            AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
            ".$where."
            ORDER BY CARGO,CONDICION_ESCALA";

        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function procCargoSueldoEscala($id_entidad,$id_depto_padre,$id_anho){

 
        
        
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_GENERAR_ESCALA_SUELDO(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO_PADRE,
                                    :P_ID_ANHO 
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->execute();  
        

    }
    public static function addCargoSueldoEscala($param){
        
            $id_anho=$param->id_anho;
            
            $minimo = $param->minimo;
            $maximo = $param->maximo;
            $bono=  $param->bono_min;
            if((strlen($minimo)==0) or (is_null($minimo))) {
                $minimo =0;
            }
           if((strlen($maximo)==0) or (is_null($maximo))) {
                $maximo =0;
            }
            if((strlen($bono)==0) or (is_null($bono))) {
                $bono =0;
            }
            
            if(($param->tipo_min=="1") or ($param->tipo_max=="1")) {
                
                $query ="SELECT
                    IMPORTE
                FROM PSTO_PLLA_PARAMETROS_VALOR
                WHERE ID_PARAMETRO='PARAM_RMV'
                AND ID_ANHO=".$id_anho;
        
                $oQuery = DB::select($query);   
                
                $rmv=0;
                foreach($oQuery as $row){
                    $rmv=$row->importe;
                }
                if($param->tipo_min=="1"){
                    $minimo = $rmv;
                }
                if($param->tipo_max=="1"){
                    $maximo = $rmv;
                }
            }

            $query ="SELECT 
                    count(*) as contar
            FROM PSTO_PLLA_CARGOSUELDO_ESCALA
            WHERE ID_ENTIDAD=".$param->id_entidad."
            AND ID_ANHO=".$param->id_anho." 
            AND ID_DEPTO_PADRE='".$param->id_depto_padre."'
            AND ID_CARGO=".$param->id_cargo."
            AND ID_CONDICION_ESCALA='".$param->id_condicion_escala."'";
        
            $oQuery = DB::select($query);
            
            $contar=0;
            foreach($oQuery as $row){
                $contar=$row->contar;
            }
            
            $ret=0;

            if($contar==0){
                
                $query = "SELECT 
                        COALESCE(MAX(ID_CARGOSUELDO_ESCALA),0)+1 ID_CARGOSUELDO_ESCALA
                FROM PSTO_PLLA_CARGOSUELDO_ESCALA ";  
                $oQuery = DB::select($query);
                $id_cargosueldo_escala = 0;
                foreach ($oQuery as $key => $item){
                    $id_cargosueldo_escala = $item->id_cargosueldo_escala ;                
                }
                
                
                $data= DB::table('PSTO_PLLA_CARGOSUELDO_ESCALA')->insert(
                        array('ID_CARGOSUELDO_ESCALA'=>$id_cargosueldo_escala,
                                'ID_ENTIDAD'=>$param->id_entidad,
                                'ID_ANHO'=>$param->id_anho, 
                                'ID_DEPTO_PADRE'=>$param->id_depto_padre,
                                'ID_CARGO'=>$param->id_cargo,
                                'ID_CONDICION_ESCALA'=>$param->id_condicion_escala,
                                'MINIMO'=>$minimo,
                                'MAXIMO'=>$maximo,
                                'TIPO_MIN'=>$param->tipo_min,
                                'TIPO_MAX'=>$param->tipo_max,
                                'BONO_MIN'=>$bono
                        )
                    );
            }else{
                $ret=1;
            }
            return $ret;
    }
    public static function updateCargoSueldoEscala($details){
        
        
        foreach($details as $param){
            
            $id_anho=$param->id_anho;
            
            $minimo = $param->minimo;
            $maximo = $param->maximo;
            $bono=  $param->bono_min;
            if((strlen($minimo)==0) or (is_null($minimo))) {
                $minimo =0;
            }
           if((strlen($maximo)==0) or (is_null($maximo))) {
                $maximo =0;
            }
            if((strlen($bono)==0) or (is_null($bono))) {
                $bono =0;
            }
            if(($param->tipo_min=="1") or ($param->tipo_max=="1")) {
                
                $query ="SELECT
                    IMPORTE
                FROM PSTO_PLLA_PARAMETROS_VALOR
                WHERE ID_PARAMETRO='PARAM_RMV'
                AND ID_ANHO=".$id_anho;
        
                $oQuery = DB::select($query);   
                
                $rmv=0;
                foreach($oQuery as $row){
                    $rmv=$row->importe;
                }
                if($param->tipo_min=="1"){
                    $minimo = $rmv;
                }
                if($param->tipo_max=="1"){
                    $maximo = $rmv;
                }
            }

            $query = "UPDATE PSTO_PLLA_CARGOSUELDO_ESCALA SET 
                        OBSERVACION= '".$param->observacion."',
                        MINIMO= ".$minimo.",
                        MAXIMO= '".$maximo."',
                        TIPO_MIN= '".$param->tipo_min."',
                        TIPO_MAX= '".$param->tipo_max."',
                        BONO_MIN= ".$bono.",
                        BONO_MAX= 0,
                        VIA_MIN= 0,
                        VIA_MAX= 0
              WHERE ID_CARGOSUELDO_ESCALA= ".$param->id_cargosueldo_escala;
            DB::update($query);
            
            $query = "UPDATE PSTO_PLLA_CARGOSUELDO_ESCALA SET 
                        MINIMO=case when coalesce(MINIMO,0)=0 then null else MINIMO end,
                        MAXIMO= case when coalesce(MAXIMO,0)=0 then null else MAXIMO end,
                        BONO_MIN= case when coalesce(BONO_MIN,0)=0 then null else BONO_MIN end
              WHERE ID_CARGOSUELDO_ESCALA= ".$param->id_cargosueldo_escala;
            DB::update($query);
   
        }

    }
    public static function deleteCargoSueldoEscala($id_cargosueldo_escala){

        $return=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        
        
        $query="SELECT COUNT(*) as CONTAR FROM PSTO_PLLA_CARGO_PROCESO 
                WHERE ID_CARGOSUELDO_ESCALA= ".$id_cargosueldo_escala;

        $oQuery = DB::select($query); 
        $contar=0;
        foreach($oQuery as $row){
            $contar=$row->contar;
        }
        
        if($contar==0){
            $query ="DELETE  FROM PSTO_PLLA_CARGOSUELDO_ESCALA
                    WHERE ID_CARGOSUELDO_ESCALA= ".$id_cargosueldo_escala;
        
            DB::delete($query);
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'No se puede eliminar, Esta asignado a presupuesto por cargo'
            ];
        }

        return $return;

    } 
    public static function listConceptoPlanillaAnt(){
        
        $query ="SELECT 
                    a.ID_CONCEPTOAPS,
                    a.COLUMNA_IMP,
                    a.ESTADO,
                    b.NOMBRE 
            FROM PSTO_PLLA_CONCE_PLANI_ANT a,APS_CONCEPTO_PLANILLA b
            WHERE a.ID_CONCEPTOAPS=b.ID_CONCEPTOAPS
            ORDER BY a.ID_CONCEPTOAPS";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addConceptoPlanillaAnt($id_concepto_aps,$columna_imp){
        
        $query ="SELECT 
                    ID_CONCEPTOAPS
            FROM PSTO_PLLA_CONCE_PLANI_ANT
            WHERE ID_CONCEPTOAPS='".$id_concepto_aps."'
            AND COLUMNA_IMP='".$columna_imp."'";
        
        $oQuery = DB::select($query);
        
        if(count($oQuery)==0){
            $data= DB::table('PSTO_PLLA_CONCE_PLANI_ANT')->insert(
                    array('ID_CONCEPTOAPS'=>$id_concepto_aps,
                        'COLUMNA_IMP' =>$columna_imp,
                        'ESTADO'=> '1'
                    )
                );
        }

    } 
    public static function deleteConceptoPlanillaAnt($id_concepto_aps,$columna_imp){

        $query ="DELETE
            FROM PSTO_PLLA_CONCE_PLANI_ANT
            WHERE ID_CONCEPTOAPS=".$id_concepto_aps."
            AND COLUMNA_IMP='".$columna_imp."'";
        
        DB::delete($query);

    } 
    public static function listConceptoActividad($id_entidad,$id_depto_padre){
        
        $query ="SELECT 
                    a.ID_CONCEPTO_ACTIVIDAD,
                    a.ID_ACTIVIDAD,
                    a.ID_CONCEPTOAPS,
                    a.COLUMNA_IMP,
                    a.FORMULA,
                    a.ID_ENTIDAD,
                    a.ID_DEPTO_PADRE,
                    a.AUXI_IDENTIFICADOR,
                    a.ESTADO,
                    c.NOMBRE AS CONCEPTOAPS,
                    b.NOMBRE AS ACTIVIDAD,
                    a.DISTRIBUIDO,
                    CASE WHEN COALESCE(a.DISTRIBUIDO,'S')='S' THEN 'SI' ELSE 'NO' END AS DISTRIBUIDO_DESC
            FROM PSTO_PLLA_CONCEPTO_ACTIVIDAD a INNER JOIN PSTO_ACTIVIDAD b
            ON a.ID_ACTIVIDAD=b.ID_ACTIVIDAD
            LEFT JOIN APS_CONCEPTO_PLANILLA c
            ON a.ID_CONCEPTOAPS=c.ID_CONCEPTOAPS
            WHERE a.ID_ENTIDAD=".$id_entidad."
            AND a.ID_DEPTO_PADRE=".$id_depto_padre."
            AND a.AUXI_IDENTIFICADOR='PL'
            ORDER BY a.ID_ACTIVIDAD";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showConceptoActividad($id_concepto_actividad){
        
        $query ="SELECT 
                    a.ID_CONCEPTO_ACTIVIDAD,
                    a.ID_ACTIVIDAD,
                    a.ID_ENTIDAD,
                    a.ID_DEPTO_PADRE,
                    a.AUXI_IDENTIFICADOR,
                    a.ID_CONCEPTOAPS,
                    a.COLUMNA_IMP,
                    a.FORMULA,
                    a.ESTADO,
                    coalesce(a.DISTRIBUIDO,'S') as DISTRIBUIDO,
                    b.NOMBRE AS ACTIVIDAD
            FROM PSTO_PLLA_CONCEPTO_ACTIVIDAD a,PSTO_ACTIVIDAD b
            WHERE a.ID_ACTIVIDAD=b.ID_ACTIVIDAD
            AND a.ID_CONCEPTO_ACTIVIDAD=".$id_concepto_actividad."";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function procConceptoActividad($id_depto_padre){

       
        $pdo = DB::getPdo();
        $auxiliar='PL';
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_CONCEPTO_ACTVIDAD(
                                        :P_AUXI_IDENTIFICADOR,
                                        :P_ID_DEPTO_PADRE
                                     ); end;");
        $stmt->bindParam(':P_AUXI_IDENTIFICADOR', $auxiliar, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->execute();  
        

    }
    public static function updateConceptoActividad($id_concepto_actividad,$param){
        
        
        //foreach($details as $param){
            
            
            $query = "UPDATE PSTO_PLLA_CONCEPTO_ACTIVIDAD SET 
                        ID_CONCEPTOAPS = ".$param->id_conceptoaps.",
                        COLUMNA_IMP = '".$param->columna_imp."',
                        ESTADO = '".$param->estado."',
                        DISTRIBUIDO = '".$param->distribuido."'
              WHERE ID_CONCEPTO_ACTIVIDAD= ".$id_concepto_actividad;
            DB::update($query);
        
            
        //}

    } 
    public static function deleteConceptoActividad($id_concepto_actividad){

        $query ="DELETE
            FROM PSTO_PLLA_CONCEPTO_ACTIVIDAD
            WHERE ID_CONCEPTO_ACTIVIDAD=".$id_concepto_actividad;
        
        DB::delete($query);

    }
    
    //planilla presupuesto  personal
    public static function listPlanilla($id_entidad,$id_anho,$id_depto_padre,$id_depto){
        
        $query ="SELECT
                    ROW_NUMBER() OVER (PARTITION BY A.ID_CARGO_PROCESO ORDER BY S.ID_PSTO_PLANILLA) AS CONTAR,
                    A.ID_CARGO_PROCESO,
                    A.ID_RENOVABLE,
                    A.ID_SEXO,
                    A.ID_EDAD,
                    A.ID_NIVEL_EDU,
                    A.ID_TIPOESTADOCIVIL,
                    A.ID_TIEMPOTRABAJO,
                    A.ID_TEMPORADA,
                    A.CANTIDAD,
                    B.NOMBRE AS CARGO,
                    C.NOMBRE AS RENOVABLE,
                    D.NOMBRE AS SEXO,
                    E.NOMBRE AS EDAD,
                    F.NOMBRE AS NIVEL_EDU,
                    G.NOMBRE AS TIPOESTADOCIVIL,
                    H.NOMBRE AS TIEMPOTRABAJO,
                    I.NOMBRE AS TEMPORADA,
                    (SELECT COUNT(*) FROM PSTO_PLLA_PLANILLA V WHERE V.ID_CARGO_PROCESO=A.ID_CARGO_PROCESO ) AS CANT_ASIG,
                    FC_PSTO_PROFESION(A.ID_CARGO_PROCESO) AS PROFESION,
                    FC_PSTO_TIPOCONTRATO(A.ID_CARGO_PROCESO) AS TIPOCONTRATO,
                    FC_PSTO_COND_LABORAL(A.ID_CARGO_PROCESO) AS CONDICION_LABORAL,
                    case when I.INICIO1 is not null then FC_PSTO_MES_NOMBRE(I.INICIO,'C')||' o '||FC_PSTO_MES_NOMBRE(I.INICIO1,'C') else FC_PSTO_MES_NOMBRE(I.INICIO,'C') end as finicio,
                    case when I.FIN1 is not null then FC_PSTO_MES_NOMBRE(I.FIN,'C')||' o '||FC_PSTO_MES_NOMBRE(I.FIN1,'C') else   FC_PSTO_MES_NOMBRE(I.FIN,'C') end  as ffinal,
                    I.INICIO,
                    I.FIN,
                    I.INICIO1,
                    I.FIN1,
                    X.ID_CARGOSUELDO_ESCALA,
                    X.ID_ANHO,
                    X.ID_ENTIDAD,
                    A.ID_DEPTO,
                    X.ID_DEPTO_PADRE,
                    X.ID_CARGO,
                    X.ID_CONDICION_ESCALA,
                    X.OBSERVACION,
                    X.MINIMO,
                    X.MAXIMO,
                    X.TIPO_MIN,
                    X.TIPO_MAX,
                    X.BONO_MIN,
                    X.BONO_MAX,
                    X.VIA_MIN,
                    X.VIA_MAX,
                    Y.NOMBRE AS CONDICION_ESCALA,
                    COALESCE(S.ID_PSTO_PLANILLA,0) AS ID_PSTO_PLANILLA,
                    coalesce(S.ID_PERSONA,0) as ID_PERSONA,
                    S.ID_CATEGORIA,
                    S.ID_TIPOCONTRATO,
                    S.UNIFORME,
                    S.DOCENTE_TC,
                    S.TIPO_AREA,
                    S.ID_COND_LAB,
                    S.ANT_BASICO,
                    S.ANT_PRIMA_INF,
                    S.ANT_MODAL_FORMATIVA,
                    S.ANT_BONI_CARGO,
                    S.ANT_ASIG_FAM,
                    S.ANT_VIV_REMUN,
                    S.ANT_SODEXO,
                    S.IMP_BONI_CARGO,
                    S.IMP_ASIG_FAM,
                    S.IMP_MOVI_LIB_DIS,
                    S.IMP_HEXTRAS25,
                    S.IMP_HEXTRAS35,
                    S.IMP_HNOCTURNA,
                    S.IMP_HFERIADO,
                    S.IMP_VIV_REMUN,
                    S.IMP_SODEXO,
                    S.IMP_AYUDAANUAL,
                    S.IMP_REINT5TACAT,
                    S.CAL_MESES,
                    S.CAL_PRIMA_INF,
                    S.CAL_SUELDO,
                    S.IMP_BASICO,
                    S.IMP_MODAL_FORMATIVA,
                    S.IMP_PRIMA_INF,
                    S.IMP_GRATIFICACION,
                    S.IMP_BONO_MIS,
                    S.IMP_ESSALUD,
                    S.IMP_UNIFORME,
                    S.IMP_BONI_EXTRA,
                    S.IMP_CTS,
                    S.IMP_VAC_TRUNCA,
                    S.IMP_PPG,
                    S.IMP_SEGURO_VIDA,
                    S.TOTAL,
                    S.NOMBRE,
                    S.PATERNO,
                    S.MATERNO,
                    S.TIPOCONTRATO AS TIPOCONTRATO_P,
                    S.COND_LAB,
                    S.CATEGORIA,
                    S.DESC_UNIFORME,
                    S.DESC_DOCENTE_TC,
                    S.DESC_TIPO_AREA,
                    S.ESHEXTRAS25,
                    S.ESHEXTRAS35,
                    S.ESHNOCTURNA,
                    S.ESHFERIADO,
                    S.FMR,
                    D.departamento
                FROM PSTO_PLLA_CARGOSUELDO_ESCALA X 
                INNER JOIN APS_CARGO B
                ON X.ID_CARGO=B.ID_CARGO
                INNER JOIN PSTO_PLLA_CONDICION_ESCALA Y
                ON Y.ID_CONDICION_ESCALA=X.ID_CONDICION_ESCALA
                INNER JOIN PSTO_PLLA_CARGO_PROCESO A  
                ON A.ID_CARGOSUELDO_ESCALA=X.ID_CARGOSUELDO_ESCALA
                INNER JOIN VW_AREA_DEPTO D
                ON A.ID_ENTIDAD=D.ID_ENTIDAD
                AND A.ID_DEPTO=D.ID_DEPTO
                LEFT JOIN PSTO_PLLA_RENOVABLE C
                ON A.ID_RENOVABLE=C.ID_RENOVABLE
                LEFT JOIN APS_SEXO D
                ON A.ID_SEXO=D.ID_SEXO
                LEFT JOIN PSTO_PLLA_EDAD E
                ON A.ID_EDAD=E.ID_EDAD
                LEFT JOIN APS_NIVEL_EDUCATIVO F
                ON A.ID_NIVEL_EDU=F.ID_NIVEL_EDU
                LEFT JOIN TIPO_ESTADO_CIVIL G
                ON A.ID_TIPOESTADOCIVIL=G.ID_TIPOESTADOCIVIL
                LEFT JOIN APS_TIEMPO_TRABAJO H
                ON A.ID_TIEMPOTRABAJO=H.ID_TIEMPOTRABAJO
                LEFT JOIN PSTO_PLLA_TEMPORADA I
                ON A.ID_TEMPORADA=I.ID_TEMPORADA
                LEFT JOIN (SELECT 
                        A.ID_PSTO_PLANILLA,
                        A.ID_PERSONA,
                        A.ID_CATEGORIA,
                        A.ID_TIPOCONTRATO,
                        A.UNIFORME,
                        A.DOCENTE_TC,
                        A.TIPO_AREA,
                        A.ID_COND_LAB,
                        A.ANT_BASICO,
                        A.ANT_PRIMA_INF,
                        A.ANT_MODAL_FORMATIVA,
                        A.ANT_BONI_CARGO,
                        A.ANT_ASIG_FAM,
                        A.ANT_VIV_REMUN,
                        A.ANT_SODEXO,
                        A.IMP_BONI_CARGO,
                        A.IMP_ASIG_FAM,
                        A.IMP_MOVI_LIB_DIS,
                        A.IMP_HEXTRAS25,
                        A.IMP_HEXTRAS35,
                        A.IMP_HNOCTURNA,
                        A.IMP_HFERIADO,
                        A.IMP_VIV_REMUN,
                        A.IMP_SODEXO,
                        A.IMP_AYUDAANUAL,
                        A.IMP_REINT5TACAT,
                        A.CAL_MESES,
                        A.CAL_PRIMA_INF,
                        A.CAL_SUELDO,
                        A.IMP_BASICO,
                        A.IMP_MODAL_FORMATIVA,
                        A.IMP_PRIMA_INF,
                        A.IMP_GRATIFICACION,
                        A.IMP_BONO_MIS,
                        A.IMP_ESSALUD,
                        A.IMP_UNIFORME,
                        A.IMP_BONI_EXTRA,
                        A.IMP_CTS,
                        A.IMP_VAC_TRUNCA,
                        A.IMP_PPG,
                        A.IMP_SEGURO_VIDA,
                        A.TOTAL,
                        B.NOMBRE,
                        B.PATERNO,
                        B.MATERNO,
                        D.NOMBRE_CORTO AS TIPOCONTRATO,
                        E.NOMBRE AS COND_LAB,
                        G.NOMBRE AS CATEGORIA,
                        CASE WHEN A.UNIFORME='S' THEN 'SI' ELSE 'NO' END AS DESC_UNIFORME,
                        CASE WHEN A.DOCENTE_TC='S' THEN 'SI' ELSE '' END AS DESC_DOCENTE_TC,
                        CASE WHEN A.TIPO_AREA='A' THEN 'APOYO' WHEN A.TIPO_AREA='P' THEN 'PRODUCTIVO' ELSE '' END AS DESC_TIPO_AREA,
                        A.ID_CARGO_PROCESO,
                        A.ESHEXTRAS25,
                        A.ESHEXTRAS35,
                        A.ESHNOCTURNA,
                        A.ESHFERIADO,
                        A.FMR
                    FROM PSTO_PLLA_PLANILLA A 
                    INNER JOIN TIPO_CONTRATO D
                    ON A.ID_TIPOCONTRATO=D.ID_TIPOCONTRATO
                    INNER JOIN APS_CONDICION_LABORAL E
                    ON A.ID_COND_LAB=E.ID_COND_LAB
                    LEFT JOIN MOISES.PERSONA B
                    ON A.ID_PERSONA=B.ID_PERSONA
                    LEFT JOIN APS_CATEGORIA G
                    ON A.ID_CATEGORIA=G.ID_CATEGORIA
                    WHERE A.ID_ENTIDAD=".$id_entidad." 
                    AND A.ID_DEPTO='".$id_depto."'
                    AND A.ID_DEPTO_PADRE='".$id_depto_padre."'
                    AND A.ID_ANHO=".$id_anho."
                )S ON S.ID_CARGO_PROCESO=A.ID_CARGO_PROCESO
                WHERE X.ID_ENTIDAD=".$id_entidad."
                AND X.ID_ANHO=".$id_anho."
                AND X.ID_DEPTO_PADRE='".$id_depto_padre."'
                AND A.ID_DEPTO='".$id_depto."'
                ORDER BY A.ID_CARGO_PROCESO,S.ID_PSTO_PLANILLA,S.PATERNO,S.MATERNO,S.NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPlanilla($id_psto_planilla){
        $query="SELECT
                A.ID_ENTIDAD,
                P.NOMBRE,
                P.PATERNO,
                P.MATERNO,
                F.ID_DEPTO AS ID_DEPTO_PER,
                F.NOMBRE AS DEPTO_PER,
                '' AS DEPTO,
                TE.NOMBRE AS TEMPORADA_PER,
                0 AS ID_PLLA_PLANILLA_DIST,
                '' AS ID_DEPTO,
                A.ID_PSTO_PLANILLA,
                0 AS PORCENTAJE,
                '' AS ID_TEMPORADA,
                '0' AS TIPO
            FROM  PSTO_PLLA_PLANILLA A
            INNER JOIN CONTA_ENTIDAD_DEPTO F
            ON F.ID_ENTIDAD=A.ID_ENTIDAD
            AND F.ID_DEPTO=A.ID_DEPTO
            INNER JOIN PSTO_PLLA_TEMPORADA TE
            ON TE.ID_TEMPORADA=A.ID_TEMPORADA
            LEFT JOIN MOISES.PERSONA P
            ON P.ID_PERSONA=A.ID_PERSONA
            WHERE A.ID_PSTO_PLANILLA=".$id_psto_planilla." ";

        $oQuery = DB::select($query);        
        return $oQuery;
    }
    /*public static function procPlanilla($id_entidad,$id_anho,$id_depto,$id_depto_padre,$id_auxiliar){

        $error=0;
        $msgerror='';
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_GENERAR_PLANILLA(
                                        :P_ID_ENTIDAD,
                                        :P_ID_ANHO,
                                        :P_ID_DEPTO,
                                        :P_ID_DEPTO_PADRE,
                                        :P_ID_AUXILIAR,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute();  
        

    }*/
    public static function addPlanilla($id_cargo_proceso,$id_persona,$id_tipocontrato,$id_cond_lab,$docente_tc,$eshextras25,$eshextras35,$eshnocturna,$eshferiado){

        $nerror=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        if(strlen($id_persona)==0){
            $id_persona=0;
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_AGREGAR_EMPLEADO(
                                        :P_ID_CARGO_PROCESO,
                                        :P_ID_PERSONA,
                                        :P_ID_TIPOCONTRATO,
                                        :P_ID_COND_LAB,
                                        :P_DOCENTE_TC,
                                        :P_ESHEXTRAS25,
                                        :P_ESHEXTRAS35,
                                        :P_ESHNOCTURNA,
                                        :P_ESHFERIADO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_CARGO_PROCESO', $id_cargo_proceso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TIPOCONTRATO', $id_tipocontrato, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_COND_LAB', $id_cond_lab, PDO::PARAM_STR);
        $stmt->bindParam(':P_DOCENTE_TC', $docente_tc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ESHEXTRAS25', $eshextras25, PDO::PARAM_INT);
        $stmt->bindParam(':P_ESHEXTRAS35', $eshextras35, PDO::PARAM_INT);
        $stmt->bindParam(':P_ESHNOCTURNA', $eshnocturna, PDO::PARAM_INT);
        $stmt->bindParam(':P_ESHFERIADO', $eshferiado, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute(); 
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;
        

    }
    public static function updatePlanilla($details){
        
        $nerror=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        foreach($details as $param){
    
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_ACTUALIZAR_EMPLEADO(
                                            :P_ID_PSTO_PLANILLA,
                                            :P_ID_CARGO_PROCESO,
                                            :P_ID_TIPOCONTRATO,
                                            :P_ID_COND_LAB,
                                            :P_DOCENTE_TC,
                                            :P_ESHEXTRAS25,
                                            :P_ESHEXTRAS35,
                                            :P_ESHNOCTURNA,
                                            :P_ESHFERIADO,
                                            :P_ANT_BASICO,
                                            :P_ERROR,
                                            :P_MSGERROR
                                         ); end;");
            $stmt->bindParam(':P_ID_PSTO_PLANILLA', $param->id_psto_planilla, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CARGO_PROCESO', $param->id_cargo_proceso, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOCONTRATO', $param->id_tipocontrato, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_COND_LAB', $param->id_cond_lab, PDO::PARAM_STR);
            $stmt->bindParam(':P_DOCENTE_TC', $param->docente_tc, PDO::PARAM_STR);
            $stmt->bindParam(':P_ESHEXTRAS25', $param->eshextras25, PDO::PARAM_INT);
            $stmt->bindParam(':P_ESHEXTRAS35', $param->eshextras35, PDO::PARAM_INT);
            $stmt->bindParam(':P_ESHNOCTURNA', $param->eshnocturna, PDO::PARAM_INT);
            $stmt->bindParam(':P_ESHFERIADO', $param->eshferiado, PDO::PARAM_INT);
            $stmt->bindParam(':P_ANT_BASICO', $param->ant_basico, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);

            $stmt->execute(); 
            

                    
        }
     
    }
    public static function UpdatePlanillaPersona($id_psto_planilla,$id_persona){
        
        $query = "SELECT 
                    ID_ENTIDAD, 
                    ID_ANHO,
                    id_depto
                 FROM PSTO_PLLA_PLANILLA 
                 WHERE ID_PSTO_PLANILLA=".$id_psto_planilla;
       
         $oQuery = DB::select($query); 
         $id_entidad=0;
         $id_anho=0;
         $id_depto="";
         foreach($oQuery as $row){
            $id_entidad=$row->id_entidad;
            $id_anho=$row->id_anho;
            $id_depto=$row->id_depto;
         }                  
      
        
         $query = "SELECT COUNT(*) as contar
                        FROM PSTO_PLLA_PLANILLA 
                        WHERE ID_ENTIDAD=".$id_entidad." 
                        AND ID_PERSONA=".$id_persona." 
                        AND ID_ANHO=".$id_anho ;
         $contar=0;
         $oQuery = DB::select($query); 
         foreach($oQuery as $row){
            $contar=$row->contar;
         }
         
         $ret=0;
         $return=[
            'nerror'=>0,
            'msgerror'=>""
         ];
         if($contar==0){
            $query = "UPDATE PSTO_PLLA_PLANILLA SET 
                        ID_PERSONA = ".$id_persona."
                    WHERE ID_PSTO_PLANILLA= ".$id_psto_planilla;
            DB::update($query);  
        
            PayrollData::procUpdatePlanilla($id_psto_planilla); 
         }else{
             $query = "SELECT 
                    NOMBRE,
                    id_depto
                 FROM CONTA_ENTIDAD_DEPTO 
                 WHERE ID_DEPTO='".$id_depto."' ";
       
                $oQuery = DB::select($query); 
                $nombre="";
                foreach($oQuery as $row){
                   $nombre=$row->nombre;

                }
             $return=[
                    'nerror'=>1,
                    'msgerror'=>"Esta asignado a departamento ".$id_depto.": ".$nombre
                ];
         }
         
         return $return;
    }
    public static function procUpdatePlanilla($id_psto_planilla){

        $error=0;
        $msgerror='';
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_ACTUALIZAR_PLANILLA_EMP(
                                        :P_ID_PSTO_PLANILLA,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_PSTO_PLANILLA', $id_psto_planilla, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute();  
        

    }
    
    public static function deletePlanilla($id_psto_planilla){

        $query ="DELETE
            FROM PSTO_PLLA_PLANILLA_DIST_DET
            WHERE ID_PLLA_PLANILLA_DIST IN(
                SELECT ID_PLLA_PLANILLA_DIST
                FROM PSTO_PLLA_PLANILLA_DIST
                WHERE ID_PSTO_PLANILLA=".$id_psto_planilla." 
            )";
        
        DB::delete($query);
        
        $query ="DELETE
            FROM PSTO_PLLA_PLANILLA_DIST
            WHERE ID_PSTO_PLANILLA=".$id_psto_planilla;
        
        DB::delete($query);
        
        
        $query ="DELETE
            FROM PSTO_PLLA_PLANILLA
            WHERE ID_PSTO_PLANILLA=".$id_psto_planilla;
        
        DB::delete($query);

    }
    public static function listPlanillaDist($id_depto,$id_anho,$id_depto_padre){
        $query="SELECT
                    ROW_NUMBER() OVER (PARTITION BY A.ID_PSTO_PLANILLA ORDER BY V.ID_PLLA_PLANILLA_DIST) AS CONTAR,
                    V.ID_PLLA_PLANILLA_DIST,
                    V.ID_ENTIDAD,
                    V.ID_DEPTO,
                    V.ID_PSTO_PLANILLA,
                    V.TIPO,
                    CASE WHEN V.TIPO='1' THEN 'Pricipal' ELSE 'Apoyo' END AS TIPO_DESC,
                    CASE WHEN F.ID_DEPTO='".$id_depto."' OR V.ID_DEPTO='".$id_depto."' THEN '0' ELSE '1' END EDITAR,
                    CASE WHEN F.ID_DEPTO='".$id_depto."' OR V.ID_DEPTO='".$id_depto."' THEN V.TIPO ELSE '1' END ELIMINAR,
                    V.ID_TEMPORADA,
                    V.PORCENTAJE,
                    V.MES1,
                    V.MES2,
                    V.MES3,
                    V.MES4,
                    V.MES5,
                    V.MES6,
                    V.MES7,
                    V.MES8,
                    V.MES9,
                    V.MES10,
                    V.MES11,
                    V.MES12,
                    F.ID_DEPTO AS ID_DEPTO_PER,
                    F.NOMBRE AS DEPTO_PER,
                    E.NOMBRE AS DEPTO,
                    T.NOMBRE AS TEMPORADA,
                    A.FMR,
                    A.IMP_BONI_CARGO,
                    A.IMP_ASIG_FAM,
                    A.IMP_MOVI_LIB_DIS,
                    A.IMP_HEXTRAS25,
                    A.IMP_HEXTRAS35,
                    A.IMP_HNOCTURNA,
                    A.IMP_HFERIADO,
                    A.IMP_VIV_REMUN,
                    A.IMP_SODEXO,
                    A.IMP_AYUDAANUAL,
                    A.IMP_REINT5TACAT,
                    A.CAL_MESES,
                    A.IMP_BASICO,
                    A.IMP_MODAL_FORMATIVA,
                    A.IMP_PRIMA_INF,
                    A.IMP_GRATIFICACION,
                    A.IMP_BONO_MIS,
                    A.IMP_ESSALUD,
                    A.IMP_UNIFORME,
                    A.IMP_BONI_EXTRA,
                    A.IMP_CTS,
                    A.IMP_VAC_TRUNCA,
                    A.IMP_PPG,
                    A.IMP_SEGURO_VIDA,
                    A.TOTAL,
                    P.NOMBRE,
                    P.PATERNO,
                    P.MATERNO,
                    CA.NOMBRE AS CARGO,
                    TC.NOMBRE_CORTO AS TIPOCONTRATO,
                    CL.NOMBRE AS COND_LAB,
                    CASE WHEN A.DOCENTE_TC='S' THEN 'SI' ELSE '' END AS DOCENTE_TC,
                    TT.NOMBRE AS TIEMPOTRABAJO,
                    TE.NOMBRE AS TEMPORADA_PER
                FROM PSTO_PLLA_PLANILLA A
                INNER JOIN PSTO_PLLA_PLANILLA_DIST V  
                ON V.ID_PSTO_PLANILLA=A.ID_PSTO_PLANILLA
                INNER JOIN CONTA_ENTIDAD_DEPTO F
                ON F.ID_ENTIDAD=A.ID_ENTIDAD
                AND F.ID_DEPTO=A.ID_DEPTO
                INNER JOIN CONTA_ENTIDAD_DEPTO E
                ON E.ID_ENTIDAD=V.ID_ENTIDAD
                AND E.ID_DEPTO=V.ID_DEPTO
                INNER JOIN PSTO_PLLA_TEMPORADA T
                ON T.ID_TEMPORADA=V.ID_TEMPORADA
                INNER JOIN APS_CARGO CA
                ON CA.ID_CARGO=A.ID_CARGO
                INNER JOIN TIPO_CONTRATO TC
                ON TC.ID_TIPOCONTRATO=A.ID_TIPOCONTRATO
                INNER JOIN APS_CONDICION_LABORAL CL
                ON CL.ID_COND_LAB=A.ID_COND_LAB
                INNER JOIN APS_TIEMPO_TRABAJO TT
                ON TT.ID_TIEMPOTRABAJO=A.ID_TIEMPOTRABAJO
                INNER JOIN PSTO_PLLA_TEMPORADA TE
                ON TE.ID_TEMPORADA=A.ID_TEMPORADA
                LEFT JOIN MOISES.PERSONA P
                ON P.ID_PERSONA=A.ID_PERSONA
                WHERE V.ID_PSTO_PLANILLA IN(
                    SELECT M.ID_PSTO_PLANILLA FROM PSTO_PLLA_PLANILLA_DIST M, PSTO_PLLA_PLANILLA N
                    WHERE M.ID_PSTO_PLANILLA=N.ID_PSTO_PLANILLA
                    AND M.ID_DEPTO='".$id_depto."'
                    AND N.ID_ANHO=".$id_anho."
                    AND N.ID_DEPTO_PADRE='".$id_depto_padre."'
                )
                ORDER BY V.ID_PSTO_PLANILLA,V.TIPO DESC,V.ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPlanillaDist($id_plla_planilla_dist){
        $query="SELECT
                        V.ID_ENTIDAD,
                        P.NOMBRE,
                        P.PATERNO,
                        P.MATERNO,
                        F.ID_DEPTO AS ID_DEPTO_PER,
                        F.NOMBRE AS DEPTO_PER,
                        J.NOMBRE AS DEPTO,
                        TE.NOMBRE AS TEMPORADA_PER,
                        V.ID_PLLA_PLANILLA_DIST,
                        V.ID_DEPTO,
                        V.ID_PSTO_PLANILLA,
                        V.PORCENTAJE,
                        V.ID_TEMPORADA,
                        V.TIPO
                    FROM  PSTO_PLLA_PLANILLA A
                    INNER JOIN PSTO_PLLA_PLANILLA_DIST V  
                    ON V.ID_PSTO_PLANILLA=A.ID_PSTO_PLANILLA
                    INNER JOIN CONTA_ENTIDAD_DEPTO F
                    ON F.ID_ENTIDAD=A.ID_ENTIDAD
                    AND F.ID_DEPTO=A.ID_DEPTO
                    INNER JOIN CONTA_ENTIDAD_DEPTO J
                    ON J.ID_ENTIDAD=V.ID_ENTIDAD
                    AND J.ID_DEPTO=V.ID_DEPTO
                    INNER JOIN PSTO_PLLA_TEMPORADA TE
                    ON TE.ID_TEMPORADA=A.ID_TEMPORADA
                    LEFT JOIN MOISES.PERSONA P
                    ON P.ID_PERSONA=A.ID_PERSONA
                    WHERE V.ID_PLLA_PLANILLA_DIST=".$id_plla_planilla_dist." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addPlanillaDist($id_psto_planilla,$id_depto,$id_temporada,$porcentaje){

        $nerror=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        $pdo = DB::getPdo();
        $tipo='0';
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_GENERAR_PLANILLA_DIST(
                                        :P_ID_PSTO_PLANILLA,
                                        :P_ID_DEPTO,
                                        :P_ID_TEMPORADA,
                                        :P_PORCENTAJE,
                                        :P_TIPO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_PSTO_PLANILLA', $id_psto_planilla, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TEMPORADA', $id_temporada, PDO::PARAM_STR);
        $stmt->bindParam(':P_PORCENTAJE', $porcentaje, PDO::PARAM_STR);
        $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute(); 
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;
        

    }
    public static function updatePlanillaDist($id_psto_planilla_dist,$id_depto,$id_temporada,$porcentaje){

        $nerror=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        $pdo = DB::getPdo();
        $tipo='0';
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_ACTUALIZAR_PLANILLA_DIST(
                                        :P_ID_PLLA_PLANILLA_DIST,
                                        :P_ID_DEPTO,
                                        :P_ID_TEMPORADA,
                                        :P_PORCENTAJE,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_PLLA_PLANILLA_DIST', $id_psto_planilla_dist, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TEMPORADA', $id_temporada, PDO::PARAM_STR);
        $stmt->bindParam(':P_PORCENTAJE', $porcentaje, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute(); 
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;
    }
    public static function deletePlanillaDist($id_psto_planilla_dist){
        
        $pdo = DB::getPdo();
        
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_ELIMINAR_PLANILLA_DIST(
                                        :P_ID_PLLA_PLANILLA_DIST
                                     ); end;");
        $stmt->bindParam(':P_ID_PLLA_PLANILLA_DIST', $id_psto_planilla_dist, PDO::PARAM_INT);
  
        
        $stmt->execute();

    }

    public static function listPlanillaDistDet($id_psto_planilla){
        $query="SELECT
                    A.ID_PLLA_PLANILLA_DIST,
                    A.ID_CONCEPTO_ACTIVIDAD,
                    A.IMPORTE,
                    B.ID_ACTIVIDAD,
                    B.ID_ENTIDAD,
                    B.ID_DEPTO_PADRE,
                    D.ID_CONCEPTOAPS,
                    B.COLUMNA_IMP,
                    B.FORMULA,
                    C.NOMBRE AS ACTIVIDAD,
                    D.NOMBRE AS CONCEPTOAPS,
                    A.MES1,
                    A.MES2,
                    A.MES3,
                    A.MES4,
                    A.MES5,
                    A.MES6,
                    A.MES7,
                    A.MES8,
                    A.MES9,
                    A.MES10,
                    A.MES11,
                    A.MES12,
                    V.PORCENTAJE,
                    V.ID_DEPTO,
                    E.NOMBRE AS DEPTO
                FROM PSTO_PLLA_PLANILLA_DIST V INNER JOIN PSTO_PLLA_PLANILLA_DIST_DET A
                ON V.ID_PLLA_PLANILLA_DIST=A.ID_PLLA_PLANILLA_DIST
                INNER JOIN PSTO_PLLA_CONCEPTO_ACTIVIDAD B
                ON A.ID_CONCEPTO_ACTIVIDAD=B.ID_CONCEPTO_ACTIVIDAD
                INNER JOIN PSTO_ACTIVIDAD C
                ON B.ID_ACTIVIDAD=C.ID_ACTIVIDAD
                INNER JOIN CONTA_ENTIDAD_DEPTO E
                ON E.ID_ENTIDAD=V.ID_ENTIDAD
                AND E.ID_DEPTO=V.ID_DEPTO
                LEFT JOIN APS_CONCEPTO_PLANILLA D
                ON D.ID_CONCEPTOAPS=B.ID_CONCEPTOAPS
                WHERE V.ID_PSTO_PLANILLA=".$id_psto_planilla." 
                ORDER BY V.ID_DEPTO,B.ID_ACTIVIDAD ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    
    public static function listAyuda($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto){
        
        $where='';
        if(strlen($id_area)>0){
            $where.="AND D.ID_AREA=".$id_area." ";
        }
        if(strlen($id_depto)>0){
            $where.="AND D.ID_DEPTO='".$id_depto."' ";
        }
        
        $query ="SELECT 
                    A.ID_PSTO_AYUDAS,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_PERSONA,
                    A.ID_DEPTO,
                    A.ID_CARGO,
                    A.MES1,
                    A.MES2,
                    A.MES3,
                    A.MES4,
                    A.MES5,
                    A.MES6,
                    A.MES7,
                    A.MES8,
                    A.MES9,
                    A.IMP_TOTAL_MESES,
                    A.IMP_ANUAL_REG,
                    A.IMP_TOTAL,
                    A.IMP_ENS_UNIV,
                    B.NOMBRE,
                    B.PATERNO,
                    B.MATERNO,
                    C.NOMBRE AS CARGO,
                    D.DEPARTAMENTO AS DEPTO
            FROM PSTO_PLLA_AYUDAS A INNER JOIN MOISES.PERSONA B
            ON A.ID_PERSONA=b.ID_PERSONA
            INNER JOIN APS_CARGO C 
            ON A.ID_CARGO=C.ID_CARGO
            INNER JOIN VW_AREA_DEPTO D
            ON A.ID_ENTIDAD=D.ID_ENTIDAD
            AND A.ID_DEPTO=D.ID_DEPTO
            WHERE A.ID_ENTIDAD=".$id_entidad." 
            AND D.ID_AREA_PADRE=".$id_area_padre."
            AND A.ID_ANHO=".$id_anho."
            ".$where." 
            ORDER BY B.PATERNO,B.MATERNO,B.NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function addAyuda($param){
        
        $query="SELECT ID_DEPTO FROM PSTO_PLLA_AYUDAS 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$param->id_anho."
                AND ID_PERSONA=".$param->id_persona;

        $oQuery = DB::select($query); 
        $contar=0;
        $id_depto='';
        foreach($oQuery as $row){
            $id_depto=$row->id_depto;
            $contar=1;
        }
        
               
        $return=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        if($contar==0){
            
            
            $query="SELECT ID_CARGO FROM APS_TRABAJADOR 
                WHERE  ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $id_cargo=0;
            foreach($oQuery as $row){
                $id_cargo=$row->id_cargo;
            }
            
            $query="SELECT COALESCE(MAX(ID_PSTO_AYUDAS),0) + 1  as ID_PSTO_AYUDAS FROM PSTO_PLLA_AYUDAS ";

            $oQuery = DB::select($query); 
            $id_psto_ayudas=0;
            foreach($oQuery as $row){
                $id_psto_ayudas=$row->id_psto_ayudas;
            }
                
            $data= DB::table('PSTO_PLLA_AYUDAS')->insert(
                array('ID_PSTO_AYUDAS'=>$id_psto_ayudas,
                        'ID_PERSONA'=>$param->id_persona,
                        'ID_ENTIDAD'=>$param->id_entidad,
                        'ID_ANHO'=>$param->id_anho, 
                        'ID_DEPTO'=>$param->id_depto,
                        'ID_CARGO'=>$param->id_cargo
                )
            );
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'Ya esta registrado para el eperiodo '.$param->id_anho." Departamnto: ".$id_depto
            ];
        }
        return $return;

    }
    public static function updateAyuda($details){
        
        
        foreach($details as $param){
            $imp_enero = $param->mes1;
            $imp_febrero = $param->mes2;
            $imp_marzo = $param->mes3;
            $imp_abril =$param->mes4;
            $imp_mayo = $param->mes5;
            $imp_junio = $param->mes6;
            $imp_julio = $param->mes7;
            $imp_agosto = $param->mes8;
            $imp_setiembre = $param->mes9;
            $imp_ens_univ=$param->imp_ens_univ;
            
            $mes1=$imp_enero;
            if(is_null($imp_enero)){
                $imp_enero="null";
                $mes1=0;
            }
            $mes2=$imp_febrero;
            if(is_null($imp_febrero)){
                $imp_febrero="null";
                $mes2=0;
            }
            $mes3=$imp_marzo;
            if(is_null($imp_marzo)){
                $imp_marzo="null";
                $mes3=0;
            }
            $mes4=$imp_abril;
            if(is_null($imp_abril)){
                $imp_abril="null";
                $mes4=0;
            }
            $mes5=$imp_mayo;
            if(is_null($imp_mayo)){
                $imp_mayo="null";
                $mes5=0;
            }
            $mes6=$imp_junio;
            if(is_null($imp_junio)){
                $imp_junio="null";
                $mes6=0;
            }
            $mes7=$imp_julio;
            if(is_null($imp_julio)){
                $imp_julio="null";
                $mes7=0;
            }
            $mes8=$imp_agosto;
            if(is_null($imp_agosto)){
                $imp_agosto="null";
                $mes8=0;
            }
            $mes9=$imp_setiembre;
            if(is_null($imp_setiembre)){
                $imp_setiembre="null";
                $mes9=0;
            }
            $ens_univ=$imp_ens_univ;
            if(is_null($imp_ens_univ)){
                $imp_ens_univ="null";
                $ens_univ=0;
            }
            $imp_total_meses=$mes1+$mes2+$mes3+$mes4+$mes5+$mes6+$mes7+$mes8+$mes9;
            $imp_anual_reg=round(($imp_total_meses/9)*12,2);
            $imp_total=$imp_anual_reg+$ens_univ;
            
            $query = "UPDATE PSTO_PLLA_AYUDAS SET 
                        MES1 = ".$imp_enero.",
                        MES2 = ".$imp_febrero.",
                        MES3 = ".$imp_marzo.",
                        MES4 = ".$imp_abril.",
                        MES5 = ".$imp_mayo.",
                        MES6 = ".$imp_junio.",
                        MES7 = ".$imp_julio.",
                        MES8 = ".$imp_agosto.",
                        MES9 = ".$imp_setiembre.",
                        IMP_TOTAL_MESES =".$imp_total_meses.",
                        IMP_ANUAL_REG =".$imp_anual_reg.",
                        IMP_TOTAL =".$imp_total.",
                        IMP_ENS_UNIV =".$imp_ens_univ."
                    WHERE ID_PSTO_AYUDAS= ".$param->id_psto_ayudas;
            
            //dd($query);
            DB::update($query);
        
            
        }
    }
    public static function deleteAyuda($id_psto_ayuda){

 
        $query ="DELETE
            FROM PSTO_PLLA_AYUDAS
            WHERE ID_PSTO_AYUDAS=".$id_psto_ayuda;
        
        DB::delete($query);

    }
   
    public static function listMobLibDis($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto){
        
        $where='';
        if(strlen($id_area)>0){
            $where.="AND D.ID_AREA=".$id_area." ";
        }
        if(strlen($id_depto)>0){
            $where.="AND D.ID_DEPTO='".$id_depto."' ";
        }    
        $query ="SELECT 
                    A.ID_PSTO_MOVLIBDIS,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_PERSONA,
                    A.ID_DEPTO,
                    A.ID_CARGO,
                    A.ID_TIPOESTATUS,
                    A.ID_TIPOPAIS,
                    A.KILOMETRAJE,
                    A.IMP_PTO_VIAJE,
                    A.TRAMO1,
                    A.IMP_TRAMO1,
                    A.TRAMO2,
                    A.IMP_TRAMO2,
                    A.IMP_TOTAL,
                    A.ACTIVIDAD,
                    B.NOMBRE,
                    B.PATERNO,
                    B.MATERNO,
                    C.NOMBRE AS CARGO,
                    D.DEPARTAMENTO AS DEPTO,
                    E.SIGLAS,
                    F.NOMBRE AS PAIS,
                    (SELECT Z.PUNT_APROBADO FROM PSTO_PLLA_PUNTAJE_MIS Z
                    WHERE Z.ID_ENTIDAD=A.ID_ENTIDAD
                    AND Z.ID_ANHO=A.ID_ANHO
                    AND Z.ID_PERSONA=A.ID_PERSONA) as PUNTAJE
            FROM PSTO_PLLA_MOVLIBDIS A INNER JOIN MOISES.PERSONA B
            ON A.ID_PERSONA=b.ID_PERSONA
            INNER JOIN APS_CARGO C 
            ON A.ID_CARGO=C.ID_CARGO
            INNER JOIN VW_AREA_DEPTO D
            ON A.ID_ENTIDAD=D.ID_ENTIDAD
            AND A.ID_DEPTO=D.ID_DEPTO
            LEFT JOIN TIPO_ESTATUS E
            ON A.ID_TIPOESTATUS=E.ID_TIPOESTATUS
            LEFT JOIN TIPO_PAIS F
            ON A.ID_TIPOPAIS=F.ID_TIPOPAIS
            WHERE A.ID_ENTIDAD=".$id_entidad." 
            AND D.ID_AREA_PADRE=".$id_area_padre."
            AND A.ID_ANHO=".$id_anho."
            ".$where."
            ORDER BY B.PATERNO,B.MATERNO,B.NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addMobLibDis($param){
        $query="SELECT ID_DEPTO FROM PSTO_PLLA_MOVLIBDIS 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$param->id_anho."
                AND ID_PERSONA=".$param->id_persona;

        $oQuery = DB::select($query); 
        $contar=0;
        $id_depto='';
        foreach($oQuery as $row){
            $id_depto=$row->id_depto;
            $contar=1;
        }
        
                
        $return=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        if($contar==0){
            
                      
            
            $query="SELECT ID_TIPOESTATUS,ID_TIPOPAIS,ID_CARGO FROM PSTO_PLLA_PUNTAJE_MIS 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$param->id_anho."
                AND ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $id_tipoestatus=null;
            $id_tipopais=null;
            $id_cargo=null;
            foreach($oQuery as $row){
                $id_tipoestatus=$row->id_tipoestatus;
                $id_tipopais=$row->id_tipopais;
                $id_cargo=$row->id_cargo;
            }
            
            if(is_null($id_cargo)){
                $query="SELECT ID_CARGO FROM APS_TRABAJADOR 
                    WHERE  ID_PERSONA=".$param->id_persona;

                $oQuery = DB::select($query); 
                $id_cargo=0;
                foreach($oQuery as $row){
                    $id_cargo=$row->id_cargo;
                }
            }
            $query="SELECT COALESCE(MAX(ID_PSTO_MOVLIBDIS),0) + 1  as  ID_PSTO_MOVLIBDIS FROM PSTO_PLLA_MOVLIBDIS ";

            $oQuery = DB::select($query); 
            $id_psto_movlibdis=0;
            foreach($oQuery as $row){
                $id_psto_movlibdis=$row->id_psto_movlibdis;
            }
            
            $kilometraje= $param->kilometraje;
            $kilo=$kilometraje;
            if(is_null($kilometraje)){
                $kilometraje="null";
                $kilo=0;
            }
              
            $id_anho = $param->id_anho;
            
            
            $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS' ";
            $oQuery = DB::select($sql); 
            foreach($oQuery as $row){
                $param_movlibdis=$row->importe;
            }


            $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS_GL' ";
            $oQuery = DB::select($sql); 
            foreach($oQuery as $row){
                $param_movlibdis_gl=$row->importe;
            }

            $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS_TR' ";
            $oQuery = DB::select($sql); 
            foreach($oQuery as $row){
                $param_movlibdis_tr=$row->importe;
            }
           
            
            $tramo1 =0;
            $imp_tramo1 =0;
            $tramo2 = 0;
            $imp_tramo2 =0;
            $imp_total = 0;
                
            if($kilo>0){
                
                $tramo1 = $param_movlibdis;
                if($kilometraje<=$param_movlibdis){
                    $tramo1 = $kilometraje;
                }
 
                $imp_tramo1 = round($tramo1*$param_movlibdis_tr*$param_movlibdis_gl,2);

                $tramo2 = $kilo-$tramo1;

                $imp_tramo2 = round($tramo2*($param_movlibdis_tr/2)*$param_movlibdis_gl,2);

                $imp_total=$imp_tramo1+$imp_tramo2;
            }                    
            $data= DB::table('PSTO_PLLA_MOVLIBDIS')->insert(
                array('ID_PSTO_MOVLIBDIS'=>$id_psto_movlibdis,
                        'ID_ENTIDAD'=>$param->id_entidad,
                        'ID_PERSONA'=>$param->id_persona,
                        'ID_ANHO'=>$param->id_anho, 
                        'ID_DEPTO'=>$param->id_depto,
                        'KILOMETRAJE'=>$param->kilometraje,
                        'ID_CARGO'=>$id_cargo,
                        'ID_TIPOESTATUS'=>$id_tipoestatus,
                        'ID_TIPOPAIS'=>$id_tipopais,
                        'TRAMO1' => $tramo1,
                        'IMP_TRAMO1' => $imp_tramo1,
                        'TRAMO2' =>$tramo2,
                        'IMP_TRAMO2' =>$imp_tramo2,
                        'IMP_TOTAL' =>$imp_total
                )
            );
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'Ya esta registrado para el periodo '.$param->id_anho." Departamento: ".$id_depto
            ];
        }
        return $return;

    }
    public static function updateMobLibDis($details){
        
        
        $param_movlibdis=0;
        $param_movlibdis_gl=0;
        $param_movlibdis_tr=0;
        
        foreach($details as $param){
            
            $kilometraje = $param->kilometraje;
            $imp_pto_viaje = $param->imp_pto_viaje;
            
            $kilo=$kilometraje;
            if(is_null($kilometraje)){
                $kilometraje="null";
                $kilo=0;
            }
            $psto=$imp_pto_viaje;
            if(is_null($imp_pto_viaje)){
                $imp_pto_viaje="null";
                $psto=0;
            }
            
            $id_anho = $param->id_anho;
            
            if($param_movlibdis==0){
                $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS' ";
                $oQuery = DB::select($sql); 
                foreach($oQuery as $row){
                    $param_movlibdis=$row->importe;
                }
            }
            if($param_movlibdis_gl==0){
                $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS_GL' ";
                $oQuery = DB::select($sql); 
                foreach($oQuery as $row){
                    $param_movlibdis_gl=$row->importe;
                }
            }
            if($param_movlibdis_tr==0){
                $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MOVLIBDIS_TR' ";
                $oQuery = DB::select($sql); 
                foreach($oQuery as $row){
                    $param_movlibdis_tr=$row->importe;
                }
            }
            
            $tramo1 =0;
            $imp_tramo1 =0;
            $tramo2 = 0;
            $imp_tramo2 =0;
            $imp_total = 0;
                
            if($kilo>0){
                
                $tramo1 = $param_movlibdis;
                if($kilometraje<=$param_movlibdis){
                    $tramo1 = $kilometraje;
                }
 
                $imp_tramo1 = round($tramo1*$param_movlibdis_tr*$param_movlibdis_gl,2);

                $tramo2 = $kilo-$tramo1;

                $imp_tramo2 = round($tramo2*($param_movlibdis_tr/2)*$param_movlibdis_gl,2);

                $imp_total=$imp_tramo1+$imp_tramo2;
            }
            $query = "UPDATE PSTO_PLLA_MOVLIBDIS SET 
                        KILOMETRAJE = ".$kilometraje.",
                        IMP_PTO_VIAJE = ".$imp_pto_viaje.",
                        TRAMO1 = ".$tramo1.",
                        IMP_TRAMO1 = ".$imp_tramo1.",
                        TRAMO2 =".$tramo2.",
                        IMP_TRAMO2 =".$imp_tramo2.",
                        IMP_TOTAL =".$imp_total."
                    WHERE ID_PSTO_MOVLIBDIS= ".$param->id_psto_movlibdis;
            DB::update($query);
        
            
        }

    } 
    
    public static function deleteMobLibDis($id_psto_movlibdis){

 
        $query ="DELETE
            FROM PSTO_PLLA_MOVLIBDIS
            WHERE ID_PSTO_MOVLIBDIS=".$id_psto_movlibdis;
        
        DB::delete($query);

    }
    public static function listPuntajeMis($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto){
        
        $where='';
        if(strlen($id_area)>0){
            $where.="AND D.ID_AREA=".$id_area." ";
        }
        if(strlen($id_depto)>0){
            $where.="AND D.ID_DEPTO='".$id_depto."' ";
        }
        $query ="SELECT 
                    A.ID_PSTO_PUNTAJE_MIS,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_PERSONA,
                    A.ID_DEPTO,
                    A.ID_CARGO,
                    A.ID_TIPOESTATUS,
                    A.ID_TIPOPAIS,
                    A.PUNT_MIN,
                    A.PUNT_MAX,
                    A.PUNT_ANT,
                    A.PUNT_SUJERIDO,
                    A.PUNT_APROBADO,
                    A.ACTIVIDAD,
                    B.NOMBRE,
                    B.PATERNO,
                    B.MATERNO,
                    C.NOMBRE AS CARGO,
                    D.DEPARTAMENTO AS DEPTO,
                    E.SIGLAS,
                    F.NOMBRE AS PAIS
            FROM PSTO_PLLA_PUNTAJE_MIS A INNER JOIN MOISES.PERSONA B
            ON A.ID_PERSONA=b.ID_PERSONA
            INNER JOIN APS_CARGO C 
            ON A.ID_CARGO=C.ID_CARGO
            INNER JOIN VW_AREA_DEPTO D
            ON A.ID_ENTIDAD=D.ID_ENTIDAD
            AND A.ID_DEPTO=D.ID_DEPTO
            LEFT JOIN TIPO_ESTATUS E
            ON A.ID_TIPOESTATUS=E.ID_TIPOESTATUS
            LEFT JOIN TIPO_PAIS F
            ON A.ID_TIPOPAIS=F.ID_TIPOPAIS
            WHERE A.ID_ENTIDAD=".$id_entidad." 
            AND D.ID_AREA_PADRE=".$id_area_padre."
            AND A.ID_ANHO=".$id_anho."
            ".$where." 
            ORDER BY B.PATERNO,B.MATERNO,B.NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function procPuntajeMis($id_entidad,$id_anho){

        $error=0;
        $msgerror='';
        for($i=1;$i>=200;$i++){
            $msgerror.='0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PSTOPLANILLA.SP_GENERAR_PROCESO_MISIONERO(
                                        :P_ID_ENTIDAD,
                                        :P_ID_ANHO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        
        $stmt->execute();  


    }
    public static function valPuntajeMis($id_entidad,$id_anho){
        
        $query="SELECT COUNT(*) as CONTAR FROM PSTO_PLLA_PUNTAJE_MIS 
                WHERE ID_ENTIDAD=".$id_entidad." 
                AND ID_ANHO=".$id_anho;
        $oQuery = DB::select($query); 
        return $oQuery;
    }
    public static function addPuntajeMis($param){
        
        $query="SELECT ID_DEPTO FROM PSTO_PLLA_PUNTAJE_MIS 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$param->id_anho."
                AND ID_PERSONA=".$param->id_persona;

        $oQuery = DB::select($query); 
        $contar=0;
        $id_depto='';
        foreach($oQuery as $row){
            $id_depto=$row->id_depto;
            $contar=1;
        }
        
        $return=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        if($contar==0){
            
            
            $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$param->id_anho." AND ID_PARAMETRO='PARAM_ANHOPROC' ";
            $oQuery = DB::select($sql); 
            $anho_proc=0;
            foreach($oQuery as $row){
                $anho_proc=$row->importe;
            }
            
            $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$param->id_anho." AND ID_PARAMETRO='PARAM_MESPROC' ";
            $oQuery = DB::select($sql); 
            $mes_proc=0;
            foreach($oQuery as $row){
                $mes_proc=$row->importe;
            }
            
            /*$query="SELECT FMR  FROM APS_PLANILLA 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$anho_proc." 
                AND ID_MES=".$mes_proc." 
                AND ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $frm_ant=0;
   
            foreach($oQuery as $row){
                $frm_ant=$row->frm;
         
            }
            */
            /*$query="SELECT ID_CARGO FROM APS_TRABAJADOR WHERE  ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $id_cargo=0;
            foreach($oQuery as $row){
                $id_cargo=$row->id_cargo;
            }
            
            $query="SELECT ID_TIPOPAIS FROM MOISES.PERSONA_NATURAL 
                WHERE ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $id_tipopais=null;
   
            foreach($oQuery as $row){
                $id_tipopais=$row->id_tipopais;
         
            }*/
            
            $query="SELECT COALESCE(MAX(ID_PSTO_PUNTAJE_MIS),0) + 1  as  ID_PSTO_PUNTAJE_MIS FROM PSTO_PLLA_PUNTAJE_MIS ";

            $oQuery = DB::select($query); 
            $id_psto_puntaje_mis=0;
            foreach($oQuery as $row){
                $id_psto_puntaje_mis=$row->id_psto_puntaje_mis;
            }
               
            $punt_min = $param->punt_min;
            $punt_max = $param->punt_max;
            $punt_sujerido =$param->punt_sujerido;
            $punt_aprobado = $param->punt_aprobado;
            
            /*
            if(is_null($punt_min)){
                $punt_min="null";
            }
            if(is_null($punt_max)){
                $punt_max="null";
            }
            if(is_null($punt_sujerido)){
                $punt_sujerido="null";
            }
            if(is_null($punt_aprobado)){
                $punt_aprobado="null";
            }*/                    
            $data= DB::table('PSTO_PLLA_PUNTAJE_MIS')->insert(
                array('ID_PSTO_PUNTAJE_MIS'=>$id_psto_puntaje_mis,
                        'ID_ENTIDAD'=>$param->id_entidad,
                        'ID_PERSONA'=>$param->id_persona,
                        'ID_ANHO'=>$param->id_anho, 
                        'ID_DEPTO'=>$param->id_depto,
                        'ID_CARGO'=>$param->id_cargo,
                        'ID_TIPOESTATUS'=>$param->id_tipoestatus,
                        'ID_TIPOPAIS'=>$param->id_tipopais,
                        'PUNT_ANT'=>$param->punt_ant,
                        'PUNT_MIN' => $punt_min,
                        'PUNT_MAX' => $punt_max,
                        'PUNT_SUJERIDO' => $punt_sujerido,
                        'PUNT_APROBADO' => $punt_aprobado
                )
            );
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'Ya esta registrado departamento '.$id_depto
            ];
        }
        return $return;

    }
    public static function validarDatosMis($id_entidad,$id_persona,$id_anho){
        $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_ANHOPROC' ";
        $oQuery = DB::select($sql); 
        $anho_proc=0;
        foreach($oQuery as $row){
            $anho_proc=$row->importe;
        }

        $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MESPROC' ";
        $oQuery = DB::select($sql); 
        $mes_proc=0;
        foreach($oQuery as $row){
            $mes_proc=$row->importe;
        }
        $query="SELECT A.ID_DEPTO,A.ID_TIPOESTATUS,A.FMR,A.ID_CARGO,ID_TIPOPAIS FROM APS_PLANILLA A,PERSONA_NATURAL P
                WHERE A.ID_PERSONA=P.ID_PERSONA
                AND A.ID_ENTIDAD=".$id_entidad." 
                AND A.ID_ANHO=".$anho_proc." 
                AND A.ID_MES=".$mes_proc." 
                AND A.ID_PERSONA=".$id_persona;
        
        $oQuery = DB::select($query); 
        
        $id_depto='';
        $id_cargo='';
        $id_tipoestatus='';
        $id_tipopais='';
        $ipunt_ant='';

        foreach($oQuery as $row){
            $id_depto=$row->id_depto;
            $id_cargo=$row->id_cargo;
            $id_tipoestatus=$row->id_tipoestatus;
            $id_tipopais=$row->id_tipopais;
            $ipunt_ant=$row->fmr;

        }
        
        $ret=[
            'id_depto'=>$id_depto,
            'id_cargo'=>$id_cargo,
            'id_tipoestatus'=>$id_tipoestatus,
            'id_tipopais'=>$id_tipopais,
            'punt_ant'=>$ipunt_ant
        ];
        
        return $ret;
        
    }
    public static function validarDatosAnt($id_entidad,$id_persona,$id_anho){
        $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_ANHOPROC' ";
        $oQuery = DB::select($sql); 
        $anho_proc=0;
        foreach($oQuery as $row){
            $anho_proc=$row->importe;
        }

        $sql="SELECT IMPORTE FROM PSTO_PLLA_PARAMETROS_VALOR WHERE ID_ANHO=".$id_anho." AND ID_PARAMETRO='PARAM_MESPROC' ";
        $oQuery = DB::select($sql); 
        $mes_proc=0;
        foreach($oQuery as $row){
            $mes_proc=$row->importe;
        }
        $query="SELECT COS_VALOR FROM APS_PLANILLA_DETALLE 
                WHERE ID_ENTIDAD=".$id_entidad." 
                AND ID_ANHO=".$anho_proc." 
                AND ID_MES=".$mes_proc." 
                AND ID_PERSONA=".$id_persona." 
                AND ID_CONCEPTOAPS IN(
                    SELECT ID_CONCEPTOAPS FROM PSTO_PLLA_CONCE_PLANI_ANT
                    WHERE COLUMNA_IMP='ANT_BASICO'
                    AND ESTADO='1'
                )";
        
        $oQuery = DB::select($query); 
        $cos_valor=0;

        foreach($oQuery as $row){
            $cos_valor=$row->cos_valor;

        }

        $ret=[
            'ant_basico'=>$cos_valor
        ];
        
        return $ret;
        
    }

    public static function updatePuntajeMis($details){
        
        
        foreach($details as $param){
            $punt_min = $param->punt_min;
            $punt_max = $param->punt_max;
            $punt_sujerido =$param->punt_sujerido;
            $punt_aprobado = $param->punt_aprobado;
            
            if(is_null($punt_min)){
                $punt_min="null";
            }
            if(is_null($punt_max)){
                $punt_max="null";
            }
            if(is_null($punt_sujerido)){
                $punt_sujerido="null";
            }
            if(is_null($punt_aprobado)){
                $punt_aprobado="null";
            }
            
            $query = "UPDATE PSTO_PLLA_PUNTAJE_MIS SET 
                        PUNT_MIN = ".$punt_min.",
                        PUNT_MAX = ".$punt_max.",
                        PUNT_SUJERIDO = ".$punt_sujerido.",
                        PUNT_APROBADO = ".$punt_aprobado."
                    WHERE ID_PSTO_PUNTAJE_MIS= ".$param->id_psto_puntaje_mis;
            DB::update($query);
        
            
        }

    } 
    public static function deletePuntajeMis($id_psto_puntaje_mis){

 
        $query ="DELETE
            FROM PSTO_PLLA_PUNTAJE_MIS
            WHERE ID_PSTO_PUNTAJE_MIS=".$id_psto_puntaje_mis;
        
        DB::delete($query);

    }
    
    //vivienda
    
    public static function listVivienda($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto){
        
        $where='';
        if(strlen($id_area)>0){
            $where.="AND D.ID_AREA=".$id_area." ";
        }
        if(strlen($id_depto)>0){
            $where.="AND D.ID_DEPTO='".$id_depto."' ";
        }
        
        $query ="SELECT 
                    A.ID_PSTO_VIVIENDA,
                    A.ID_ANHO,
                    A.ID_ENTIDAD,
                    A.ID_PERSONA,
                    A.ID_DEPTO,
                    A.ID_CARGO,
                    A.IMP_IMPORTE,
                    B.NOMBRE,
                    B.PATERNO,
                    B.MATERNO,
                    C.NOMBRE AS CARGO,
                    D.DEPARTAMENTO AS DEPTO
              FROM PSTO_PLLA_VIVIENDA A INNER JOIN MOISES.PERSONA B
            ON A.ID_PERSONA=b.ID_PERSONA
            INNER JOIN APS_CARGO C 
            ON A.ID_CARGO=C.ID_CARGO
            INNER JOIN VW_AREA_DEPTO D
            ON A.ID_ENTIDAD=D.ID_ENTIDAD
            AND A.ID_DEPTO=D.ID_DEPTO
            WHERE A.ID_ENTIDAD=".$id_entidad." 
            AND D.ID_AREA_PADRE=".$id_area_padre."
            AND A.ID_ANHO=".$id_anho."
            ".$where." 
            ORDER BY B.PATERNO,B.MATERNO,B.NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addVivienda($param){
        
        $query="SELECT ID_DEPTO FROM PSTO_PLLA_VIVIENDA 
                WHERE ID_ENTIDAD=".$param->id_entidad." 
                AND ID_ANHO=".$param->id_anho."
                AND ID_PERSONA=".$param->id_persona;

        $oQuery = DB::select($query); 
        $contar=0;
        $id_depto='';
        foreach($oQuery as $row){
            $id_depto=$row->id_depto;
            $contar=1;
        }
        
             
        $return=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        if($contar==0){
            
            $query="SELECT ID_CARGO FROM APS_TRABAJADOR 
                WHERE  ID_PERSONA=".$param->id_persona;

            $oQuery = DB::select($query); 
            $id_cargo=0;
            foreach($oQuery as $row){
                $id_cargo=$row->id_cargo;
            }            
            $query="SELECT COALESCE(MAX(ID_PSTO_VIVIENDA),0) + 1  as  ID_PSTO_VIVIENDA FROM PSTO_PLLA_VIVIENDA ";

            $oQuery = DB::select($query); 
            $id_psto_vivienda=0;
            foreach($oQuery as $row){
                $id_psto_vivienda=$row->id_psto_vivienda;
            }
               
                                
            $data= DB::table('PSTO_PLLA_VIVIENDA')->insert(
                array('ID_PSTO_VIVIENDA'=>$id_psto_vivienda,
                        'ID_ENTIDAD'=>$param->id_entidad,
                        'ID_PERSONA'=>$param->id_persona,
                        'ID_ANHO'=>$param->id_anho, 
                        'ID_DEPTO'=>$param->id_depto,
                        'ID_CARGO'=>$id_cargo,
                        'IMP_IMPORTE'=>$param->imp_importe
                )
            );
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'Ya esta registrado en el periodo '.$param->id_anho." Departamento: ".$id_depto
            ];
        }
        return $return;

    }
    public static function updateVivienda($details){
        
        
        foreach($details as $param){
            $importe=$param->imp_importe;
            if(is_null($importe)){
                $importe="null";
            }
            $query = "UPDATE PSTO_PLLA_VIVIENDA SET 
                         IMP_IMPORTE=".$importe." 
                    WHERE ID_PSTO_VIVIENDA= ".$param->id_psto_vivienda;
            DB::update($query);
        
            
        }

    } 
    public static function deleteVivienda($id_psto_vivienda){

 
        $query ="DELETE
            FROM PSTO_PLLA_VIVIENDA
            WHERE ID_PSTO_VIVIENDA=".$id_psto_vivienda;
        
        DB::delete($query);

    }
}