<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class SignatureData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listCertificatenew(){
        $sql = "SELECT 
                    ROW_NUMBER() OVER (PARTITION BY A.ID_CERTIFICADO ORDER BY A.ID_CERTIFICADO) AS CONTAR,
                    A.ID_CERTIFICADO,
                    A.DESCRIPCION,
                    A.NOMBRE_ARCHIVO,
                    A.CLAVE,
                    TO_CHAR(A.DESDE,'DD/MM/YYYY') AS DESDE,
                    TO_CHAR(A.HASTA,'DD/MM/YYYY') AS HASTA,
                    A.ID_PERSONA,
                    A.FIRMA,
                    '".asset('img')."' as urls,
                    A.UBICACION,
                    A.ESTADO,
                    C.ID_DEPTO,
                    A.ID_ENTIDAD,
                    CASE WHEN ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end ESTADO_DESC,
                    E.NOMBRE||' - '||C.NOMBRE AS NOMBRE,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE,
                    A.numserie
                FROM APS_CERTIFICADO A
                LEFT JOIN CONTA_ENTIDAD_DEPTO C
                ON A.ID_ENTIDAD=C.ID_ENTIDAD
                AND A.ID_DEPTO_PADRE=C.ID_DEPTO
                LEFT JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                LEFT JOIN CONTA_ENTIDAD E
                ON E.ID_ENTIDAD=C.ID_ENTIDAD
                ORDER BY A.ID_CERTIFICADO DESC,C.ID_ENTIDAD,C.ID_DEPTO";
        $query = DB::select($sql);
        return $query;
    }
    public static function listCertificate(){
        $sql = "SELECT 
                    ROW_NUMBER() OVER (PARTITION BY A.ID_CERTIFICADO ORDER BY A.ID_CERTIFICADO) AS CONTAR,
                    A.ID_CERTIFICADO,
                    A.DESCRIPCION,
                    A.NOMBRE_ARCHIVO,
                    A.CLAVE,
                    TO_CHAR(A.DESDE,'DD/MM/YYYY') AS DESDE,
                    TO_CHAR(A.HASTA,'DD/MM/YYYY') AS HASTA,
                    A.ID_PERSONA,
                    A.FIRMA,
                    '".asset('img')."' as urls,
                    A.UBICACION,
                    A.ESTADO,
                    B.ID_DEPTO,
                    B.ID_ENTIDAD,
                    CASE WHEN ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end ESTADO_DESC,
                    E.NOMBRE||' - '||C.NOMBRE AS NOMBRE,
                    CASE WHEN B.ID_DEPTO IS NULL THEN 0 ELSE 1 END ISDEPTO,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE,
                    A.numserie
                FROM APS_CERTIFICADO A
                LEFT JOIN APS_CERTIFICADO_DEPTO B
                ON A.ID_CERTIFICADO=B.ID_CERTIFICADO
                LEFT JOIN CONTA_ENTIDAD_DEPTO C
                ON B.ID_ENTIDAD=C.ID_ENTIDAD
                AND B.ID_DEPTO=C.ID_DEPTO
                LEFT JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                LEFT JOIN CONTA_ENTIDAD E
                ON E.ID_ENTIDAD=C.ID_ENTIDAD
                ORDER BY A.ID_CERTIFICADO DESC,C.ID_ENTIDAD,B.ID_DEPTO";
        $query = DB::select($sql);
        return $query;
    }
    
    public static function editCertificate($id_certificado,$id_persona,$id_entidad,$id_depto,$archivofirma,$estado){
        $ret='OK';
        $sql="SELECT
                ID_CERTIFICADO
              FROM  APS_CERTIFICADO
              WHERE ID_ENTIDAD=".$id_entidad."
              AND ID_DEPTO_PADRE='".$id_depto."' 
              AND ID_CERTIFICADO <> ".$id_certificado;
                
        $query = DB::select($sql);
        $contar = 0;
        foreach ($query as $row){
            $contar++;
        }
        if($contar==0){
            if(strlen($archivofirma)>0){
                $query = "UPDATE APS_CERTIFICADO SET 
                        ID_ENTIDAD = ".$id_entidad.",
                        ID_DEPTO_PADRE = '".$id_depto."',
                        FIRMA= '".$archivofirma."',
                        ID_PERSONA = ".$id_persona.",
                        ESTADO = '".$estado."'
                  WHERE ID_CERTIFICADO = ".$id_certificado;
                DB::update($query);
            }else{
                $query = "UPDATE APS_CERTIFICADO SET 
                        ID_ENTIDAD = ".$id_entidad.",
                        ID_DEPTO_PADRE = '".$id_depto."',
                        ID_PERSONA = ".$id_persona.",
                        ESTADO = '".$estado."'
                  WHERE ID_CERTIFICADO = ".$id_certificado;
                DB::update($query);
            }
        }else{
            $ret="Departamento ya esta registrado"; 
        }       
        return $ret;
    }
    public static function addCertificatenew($id_persona,$id_entidad,$id_depto,$archivofirma){
        $ret='OK';
        
        $sql="SELECT
                ID_CERTIFICADO
              FROM  APS_CERTIFICADO
              WHERE ID_ENTIDAD=".$id_entidad."
              AND ID_DEPTO_PADRE='".$id_depto."'";
                
        $query = DB::select($sql);
        $contar = 0;
        foreach ($query as $row){
            $contar++;
        }
        if($contar==0){
           $query = "SELECT 
                        COALESCE(MAX(ID_CERTIFICADO),0)+1 ID_CERTIFICADO
                FROM APS_CERTIFICADO ";  
            $oQuery = DB::select($query);
            $id=1;
            foreach ($oQuery as $item){
                $id = $item->id_certificado;                
            }

            $fecha_sys=date("Y-m-d H:i:s");
            $estado='1';

            $data= DB::table('APS_CERTIFICADO')->insert(
            array('ID_CERTIFICADO'=>$id,
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO_PADRE' => $id_depto,
                'ID_PERSONA'=> $id_persona,                
                'FIRMA'=> $archivofirma,
                'ESTADO'=> $estado,
                'FECHA'=> $fecha_sys
                )
            ); 
        }else{
            $ret="Departamento ya esta registrado"; 
        }
        
       
        return $ret;
    }
            
    public static function addCertificate($descripcion,$nombre_archivo,$archivo,$dni,$desde,$hasta,$clave,$firma,$ubicacion){ 
        //addCertificate($descripcion,$nombre_archivo,$archivo,$clave,$desde,$hasta,$id_persona,$firma)
        $ret='OK';
        $sql="SELECT
                ID_PERSONA
              FROM  MOISES.PERSONA_DOCUMENTO
              WHERE NUM_DOCUMENTO='".$dni."'
              AND ID_TIPODOCUMENTO=1";
                
        $query = DB::select($sql);
        $id_persona = 0;
        foreach ($query as $row){
            $id_persona = $row->id_persona;
        }
        if($id_persona==0){
            $id_persona=16727;
        }
        if($id_persona>0){
            
            $psw = SignatureData::encriptar($clave,$dni);

            $query = "SELECT 
                            COALESCE(MAX(ID_CERTIFICADO),0)+1 ID_CERTIFICADO
                    FROM APS_CERTIFICADO ";  
            $oQuery = DB::select($query);
            $id=1;
            foreach ($oQuery as $item){
                $id = $item->id_certificado;                
            }

            $fecha_sys=date("Y-m-d H:i:s");
            $estado='1';
            $pdo = DB::getPdo();
            $sql = "INSERT INTO APS_CERTIFICADO (
                        ID_CERTIFICADO, 
                        DESCRIPCION,
                        NOMBRE_ARCHIVO,
                        ARCHIVO,
                        CLAVE,
                        DESDE,
                        HASTA,
                        ID_PERSONA,
                        FIRMA,
                        UBICACION,
                        ESTADO,
                        FECHA,
                        NUMSERIE
                        ) VALUES (
                        :ID_CERTIFICADO,
                        :DESCRIPCION,
                        :NOMBRE_ARCHIVO,
                        EMPTY_BLOB(),
                        :CLAVE,
                        :DESDE,
                        :HASTA,
                        :ID_PERSONA,
                        :FIRMA,
                        :UBICACION,
                        :ESTADO,
                        :FECHA,
                        :NUMSERIE
                        )
                RETURNING ARCHIVO INTO :ARCHIVO";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ID_CERTIFICADO', $id, PDO::PARAM_INT);
            $stmt->bindParam(':DESCRIPCION', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':NOMBRE_ARCHIVO', $nombre_archivo, PDO::PARAM_STR);
            $stmt->bindParam(':ARCHIVO', $archivo, PDO::PARAM_LOB);
            $stmt->bindParam(':CLAVE', $psw, PDO::PARAM_STR);
            $stmt->bindParam(':DESDE', $desde, PDO::PARAM_STR);
            $stmt->bindParam(':HASTA', $hasta, PDO::PARAM_STR);
            $stmt->bindParam(':ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':FIRMA', $firma, PDO::PARAM_STR);
            $stmt->bindParam(':UBICACION', $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(':ESTADO', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':FECHA', $fecha_sys, PDO::PARAM_STR);
            $stmt->bindParam(':NUMSERIE', $dni, PDO::PARAM_STR);
            $stmt->execute();
            
            
            $rpta = SignatureData::validarCertificado($id);

            if($rpta["nerror"]!=0){
                 $query = "UPDATE APS_CERTIFICADO SET 
                    ESTADO = '0'
                WHERE ID_CERTIFICADO = ".$id;
                DB::update($query);   
            }else{
                if($rpta["cert"]=='1'){
                    $query = "UPDATE APS_CERTIFICADO SET 
                    ESTADO = '0'
                    WHERE ID_CERTIFICADO = ".$id;
                    DB::update($query);
                    
                    $ret=$rpta["mensaje"];
                }
            }
        }else{
           $ret="Numero DNI Incorrceto no encontrado (".$dni.")"; 
        }
        return $ret;
    }
    
    
    public static function deleteCertificate($id_certificado){
        
        $query = "DELETE FROM APS_CERTIFICADO_DEPTO
                WHERE ID_CERTIFICADO = ".$id_certificado;
        DB::delete($query);
        
        $query = "DELETE FROM APS_CERTIFICADO
                WHERE ID_CERTIFICADO = ".$id_certificado;
        DB::delete($query);
        
   
      
    }
    public static function addCertificateDepto($id_certificado,$id_entidad,$id_depto){
        $ret=0;
        $sql = "SELECT 
                    ID_CERTIFICADO
                FROM APS_CERTIFICADO_DEPTO
                WHERE ID_ENTIDAD=".$id_entidad."
                AND ID_DEPTO=".$id_depto."
                AND ID_CERTIFICADO=".$id_certificado;
        $query = DB::select($sql);
        
        if(count($query)==0){
        
            $fecha_sys=date("Y-m-d H:i:s");
            $data= DB::table('APS_CERTIFICADO_DEPTO')->insert(
                array('ID_CERTIFICADO'=>$id_certificado,
                        'ID_ENTIDAD'=>$id_entidad,
                        'ID_DEPTO'=>$id_depto,
                        'FECHA'=>$fecha_sys
                )
             );
        }else{
            $ret=1;
        }
        return  $ret;
    }
    public static function deleteCertificateDepto($id_certificado,$id_entidad,$id_depto){
        
        $query = "DELETE FROM APS_CERTIFICADO_DEPTO
                WHERE ID_CERTIFICADO = ".$id_certificado." 
                AND ID_ENTIDAD = ".$id_entidad."
                AND ID_DEPTO = '".$id_depto."'";
        DB::delete($query);
        
    }
    
    private static function encriptar($string, $key) {
	$result = '';
	for($i=0; $i<strlen($string); $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)+ord($keychar));
		$result.=$char;
	}	
	return base64_encode($result);
    }
    
    public static function showCertificatenew($id_certificado){
        $sql = "SELECT 
                    a.ID_CERTIFICADO, 
                    a.DESCRIPCION,
                    a.NOMBRE_ARCHIVO,
                    a.CLAVE,
                    a.DESDE,
                    a.HASTA,
                    '".asset('img')."' as urls,
                    a.ID_PERSONA,
                    a.FIRMA,
                    a.ESTADO,
                    p.paterno||' '||p.materno||' '||p.nombre as representante,
                    A.UBICACION,
                    A.NUMSERIE,
                    case when a.HASTA<current_date then '1' else '0' end as fincert,
                    to_char(a.HASTA,'DD/MM/YYYY') as FHASTA,
                    A.ID_DEPTO_PADRE,
                    A.ID_ENTIDAD
                FROM APS_CERTIFICADO a,persona p
                WHERE a.id_persona=p.id_persona
                and a.ID_CERTIFICADO=".$id_certificado;
        $query = DB::select($sql);
        return $query;
    }
    
    public static function showCertificate($id_certificado){
        $sql = "SELECT 
                    a.ID_CERTIFICADO, 
                    a.DESCRIPCION,
                    a.NOMBRE_ARCHIVO,
                    a.ARCHIVO,
                    a.CLAVE,
                    a.DESDE,
                    a.HASTA,
                    a.ID_PERSONA,
                    a.FIRMA,
                    a.ESTADO,
                    p.paterno||' '||p.materno||' '||p.nombre as representante,
                    pd.NUM_DOCUMENTO,
                    A.UBICACION,
                    A.NUMSERIE,
                    case when a.HASTA<current_date then '1' else '0' end as fincert,
                    to_char(a.HASTA,'DD/MM/YYYY') as FHASTA
                FROM APS_CERTIFICADO a,persona p,persona_documento pd
                WHERE a.id_persona=p.id_persona
                and p.id_persona=pd.id_persona
                and pd.ID_TIPODOCUMENTO=1
                and a.ID_CERTIFICADO=".$id_certificado;
        $query = DB::select($sql);
        return $query;
    }
   
    public static function validarCertificado($id_certificado){
        $rpta["nerror"]=1;
        $rpta["mensaje"]='No se puede validar';
        $rpta["cert"]='0';
        try{
                
                $dataFirma=SignatureData::showCertificate($id_certificado);
               
                
                $clave="";
                $file="";
                $archivo = "";
                $hasta = "";
                $fincert="";
                $descripcion = "";
                foreach($dataFirma as $row){
                    $file      = $row->archivo;
                    $clave     = SignatureData::desencriptar($row->clave,$row->numserie);
                    $archivo   = $row->nombre_archivo;
                    $hasta     = $row->fhasta;
                    $fincert   = $row->fincert;
                    $descripcion = $row->descripcion;
                 }
                 
                if($fincert == '0'){
                    $toke = SignatureData::listConfigPlanilla('TOKENS');
                    $values['tokens']     = $toke['valor'];
                    $values['certificado']   = base64_encode($file);
                    $values['clave']  = $clave;
                    $archivos = explode(".", $archivo);
                    $values['archivo']  = $archivos[0];

                    $ruta = SignatureData::listConfigPlanilla('DIR_VALIDAR');
                    $url = $ruta['valor'].'/Firma/validar';

                    $rpta["nerror"]=1;
                    $rpta["mensaje"]='No se puede validar '.$values['tokens'];
                    //return $rpta;

                    $data = http_build_query($values);

                    $options = array (
                    'http' => array (
                        'method' => 'POST',
                        'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                            . "Content-Length: " . strlen($data) . "\r\n",
                        'content' => $data
                        )
                    ); 

                    $context   = stream_context_create($options);
                    $respuesta = file_get_contents($url, false, $context);

                    $ret= json_decode($respuesta);
                    $rpta["nerror"] = $ret->nerror;
                    if($ret->nerror!=0){
                        $rpta["nerror"] = 2;
                    }
                    $rpta["mensaje"] = $ret->mensaje;
                    $rpta["cert"]='0';

                }else{
                    $rpta["nerror"] = 2;
                    $rpta["mensaje"] = 'Firma digital('.$id_certificado.'-'.$descripcion.') ha caducado  el '.$hasta;
                    $rpta["cert"]='1';
                }
        }catch(Exception $e){
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'No se puede validar: '.$e->getMessage();
            $rpta["cert"]='0';
        }

        return $rpta;
    }
    
    public static function validarDocumento($doc,$id_certificado){
        $rpta["nerror"]=1;
        $rpta["mensaje"]='No se puede validar';
        try{
                
                $dataFirma=SignatureData::showCertificate($id_certificado);
               
                $clave="";
                $file="";
                foreach($dataFirma as $row){
                    $file      = $row->archivo;
                    $clave     = SignatureData::desencriptar($row->clave,$row->numserie);
                 }
                $toke = SignatureData::listConfigPlanilla('TOKENS');
                $values['tokens']     = $toke['valor'];
                $values['documento']  = $doc;
                $values['certificado']   = base64_encode($file);
                $values['clave']  = $clave;
                
                $ruta = SignatureData::listConfigPlanilla('DIR_VALIDAR');
                $url = $ruta['valor'].'/Firma/validardocumento';
                

                $data = http_build_query($values);

                $options = array (
                'http' => array (
                    'method' => 'POST',
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                    )
                );

                $context   = stream_context_create($options);
                $respuesta = file_get_contents($url, false, $context);
                $ret= json_decode($respuesta);
                $rpta["nerror"] = $ret->nerror;
                $rpta["mensaje"] = $ret->mensaje;
                $rpta["cert"] = $id_certificado;
                $rpta["fecha"] = $ret->fecha;;
                return $rpta;
      
        }catch(Exception $e){
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'No se puede validar: '.$e->getMessage();

        }

        return $rpta;
    }
     public static function listConfigPlanilla($id_config){
        $sql = "SELECT
                    VALOR,
                    VALOR1
                FROM APS_CONFIG_PLANILLA
                WHERE ID_CONFIG='".$id_config."' ";
        $query = DB::select($sql);
        
        
        
        $return["valor"]='';
        $return["valor1"]='';
        
        foreach( $query as $row){
            $return["valor"]=$row->valor;
            $return["valor1"]=$row->valor1;
        }
        
        return $return;
        
    }
    
    public static function desencriptar($string, $key) {
	$result = '';
	$string = base64_decode($string);
	for($i=0; $i<strlen($string); $i++) {
 		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)-ord($keychar));
  		$result.=$char;
	}
	return $result;

    }
    
    public static function vercrl(){
        $rpta["nerror"]=1;
        $rpta["mensaje"]='No se puede validar';
        try{
                
                $toke = SignatureData::listConfigPlanilla('TOKENS');
                $values['tokens']     = '';
                $values['documento']  = '';
                $values['certificado']   = '';
                $values['clave']  = '';
                
                $ruta = SignatureData::listConfigPlanilla('DIR_VALIDAR');
                $url = $ruta['valor'].'/Firma/vercrl';
                

                $data = http_build_query($values);

                $options = array (
                'http' => array (
                    'method' => 'POST',
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                    )
                );

                $context   = stream_context_create($options);
                $respuesta = file_get_contents($url, false, $context);
                $ret= json_decode($respuesta);
                $rpta["nerror"] = $ret->nerror;
                $rpta["mensaje"] = $ret->mensaje;
                $rpta["fecha"] = $ret->fecha;;
                return $rpta;
      
        }catch(Exception $e){
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'No se puede validar: '.$e->getMessage();

        }

        return $rpta;
    }
    public static function certificadoactivos($id_entidad,$id_depto_padre){
        $sql = "select 
                a.id_certificado,
                a.descripcion 
                FROM APS_CERTIFICADO a ,
                APS_CERTIFICADO_DEPTO b
                where a.id_certificado=b.id_certificado
                and a.estado='1'
                and b.id_entidad = ".$id_entidad." 
                and b.id_depto ='".$id_depto_padre."'
                order by  a.id_certificado desc ";
        $query = DB::select($sql);
        return $query;
    }
    
    public static function personacertificado($id_entidad,$id_persona,$id_anho,$id_mes){
        
       $sql = "select Substr(ID_DEPTO,1,1)  as ID_DEPTO 
                from APS_PLANILLA
                where ID_ENTIDAD=".$id_entidad."
                and ID_ANHO=".$id_anho."
                and ID_MES=".$id_mes."
                and ID_PERSONA='".$id_persona."'";
       
        $query = DB::select($sql);
        
        $id_depto_padre = '';
        
        foreach($query as $row){
            $id_depto_padre = $row->id_depto;
        }
        
        if(count($query)>0){
            $sql = "select 
                    a.id_certificado,
                    a.descripcion 
                    FROM APS_CERTIFICADO a ,
                    APS_CERTIFICADO_DEPTO b
                    where a.id_certificado=b.id_certificado
                    and a.estado='1'
                    and b.id_entidad = ".$id_entidad." 
                    and b.id_depto ='".$id_depto_padre."'
                    order by  a.id_certificado desc ";
            $datcert = DB::select($sql);
            
            if(count($query)>0){
                
                $query = "SELECT 
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO,
                        FC_GTH_OBTENER_EMAIL(ID_PERSONA) AS EMAIL
                        FROM MOISES.VW_PERSONA_NATURAL_LIGHT
                        WHERE ID_PERSONA=".$id_persona;
                $oQuery = DB::select($query);        
        
                $rpta["nerror"] = 0;
                $rpta["mensaje"] = "ok";
                $rpta["certificado"] = $datcert ;
                $rpta["persona"] = $oQuery ;
            }else{
                $rpta["nerror"] = 1;
                $rpta["mensaje"] = "No esta asignado certificado digital a su departamento";
            }
        }else{
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = "No existe boleta generado para el periodo";
        }
        return $rpta;
    }
    
    public static function logfirmaboleta($tipo,$id_log,$items,$id_gestion,$id_persona,$id_anho,$id_mes,$origen,$id_user,$error,$nerror,$archivo,$tamano,$directorio,$firma){
        
        $id = 0;
        if($tipo==0){
            $id = date('YmdHis').$items;
            $data= DB::table('APS_PLANILLA_BOLETA_LOG')->insert(
            array('ID_LOG'=>$id,
                'ID_PERSONA' => $id_persona,
                'ID_ANHO' => $id_anho,
                'ID_MES'=> $id_mes,                
                'ORIGEN'=> $origen,
                'NERROR'=> 1,
                'ID_USER'=> $id_user,
                'FECHA'=> DB::raw("CURRENT_TIMESTAMP"),
                'ESTADO_FIRMA'=>'Firma Correcta',
                'ESTADO_PROCESO'=>'No se ha generado',
                'ERROR'=>$error,
                'FIRMA'=>$firma
                )
            ); 
        }else{
            $id = $id_log;
            
            if($nerror==0){
                $estado_proceso = $error;
                $error='';
            }else{
                $estado_proceso = '';
                
            }
            
                    
            $query = "UPDATE APS_PLANILLA_BOLETA_LOG SET
                        ID_GESTION =".$id_gestion.",
                        NERROR = ".$nerror.",
                        ERROR = '".$error."',
                        ARCHIVO = '".$archivo."',
                        TAMANO = '".$tamano."',
                        DIRECTORIO='".$directorio."',
                        ESTADO_PROCESO='".$estado_proceso."'
                  WHERE ID_LOG = ".$id;
                DB::update($query);
        }
        
       return  $id;
    }
    public static function logfirma($id_persona,$id_anho,$id_mes){
        $sql="select 
                a.ID_LOG,
                a.ID_PERSONA,
                p.paterno||' '||p.materno||' '||p.nombre as persona,
                a.ID_ANHO,
                m.nombre as mes,
                to_char(a.FECHA,'DD/MM/YYYY HH24:MI:SS') as fecha,
                a.ESTADO_FIRMA,
                a.ORIGEN,
                a.FIRMA,
                a.NERROR,
                a.ERROR,
                a.ARCHIVO,
                a.TAMANO,
                a.DIRECTORIO,
                a.ESTADO_PROCESO,
                a.ID_USER,
                u.paterno||' '||u.materno||' '||u.nombre as usuario
                from APS_PLANILLA_BOLETA_LOG a,persona p,conta_mes m,persona u
                where a.id_persona=p.id_persona
                and a.id_mes=m.id_mes
                and a.id_user=u.id_persona
                and a.id_persona=".$id_persona."
                and a.id_anho=".$id_anho."
                and a.id_mes=".$id_mes."
                order by a.id_log
        ";
        $query = DB::select($sql);
        return $query;
    }
}