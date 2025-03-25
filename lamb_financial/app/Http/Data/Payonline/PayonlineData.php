<?php
namespace App\Http\Data\Payonline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;
class PayonlineData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public static function show($id_payonline){
        $sql = "select 
                id_payonline,
                tokens,
                tokens_proceso,
                url_visa,
                url_mc,
                url_visa_pr,
                url_mc_pr
            from payonline 
            where id_payonline=".$id_payonline;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function listAplicacion(){
        $sql = "select 
                id_aplicacion,
                nombre,
                descripcion,
                url_servicio,
                url_respuesta,
                url_verificacion,
                conexion,
                tabla_operacion,
                tabla_main
            from payonline_aplicacion 
            order by id_aplicacion";
        $query = DB::select($sql);
        
        return $query;
    }
     public static function showAplicacion($id_aplicacion){
        $sql = "select 
                id_aplicacion,
                nombre,
                descripcion,
                url_servicio,
                url_respuesta,
                url_verificacion,
                url_verificacion,
                conexion,
                tabla_operacion,
                tabla_main
            from payonline_aplicacion 
            where id_aplicacion=".$id_aplicacion;
        $query = DB::select($sql);
        
        return $query;
    }
    
    public static function verificapersona($id_personal,$dni,$id_aplicacion,$nombre,$paterno,$materno,$sexo,$correo,$id_origen){
        
        $data_apli = PayonlineData::showAplicacion($id_aplicacion);
        
        $sp='';
        $mensaje = "";
        $nerror  = 0;
        $cx      = '';
        foreach($data_apli as $row){
            $sp=$row->url_verificacion;
            $cx=$row->conexion;
        }
        
        $data =[];
        if(strlen($sp)>0) {  
            $paterno_id = PayonlineData::replacetexto($paterno);
            $bindings = [
                'P_ID_PERSONAL' => $id_personal,
                'P_DNI' => $dni,
                'P_NOMBRE' => $nombre,
                'P_PATERNO' => $paterno,
                'P_MATERNO' => $materno,
                'P_SEXO' => $sexo,
                'P_CORREO' => $correo,
                'P_PATERNO_ID'=>$paterno_id,
                'P_ID_ORIEGEN'=>$id_origen
            ];
            $result  = DB::connection($cx)->executeProcedureWithCursor($sp, $bindings);
            $datos   = $result[0];            
            if( $datos->valida == 'S'  ){ 
                $data =[
                    'id_personal'=>$datos->id_personal,
                    'id_personal_visa'=>$datos->id_personal_visa,
                    'nombres_visa'=>$datos->nombres_visa,
                    'apellidos_visa'=>$datos->apellidos_visa,
                    'email_visa'=>$datos->email_visa,
                    'eap'=>$datos->eap,
                    'codigo'=>$datos->codigo,
                    'dni'=>$datos->dni,
                    'apellidos'=>$datos->apellidos,
                    'nombres'=>$datos->nombres
                ];
            }else{
                $nerror  = 1;
                $mensaje = "No existe datos";
            }
        }else{
            $nerror  = 1;
            $mensaje = "No existe servicio de verificación ";
        }
        
        $respuesta=[
            'nerror'=>$nerror,
            'mensaje'=>$mensaje,
            'data'=>$data
        ];
        
        return $respuesta;
    }
    
    public static function listOperacion(){
        $sql="select id_venta from aron.upeu_main where estado='1'";
        $query = DB::connection('oracleapp')->select($sql);
        $id_venta = '';
        foreach($query as $row){
            $id_venta = $row->id_venta;
        }
        
        $sql = "
            
            select 
                id_venta,
                id_opcion,
                conv_char(replace(CONVERT(nombre,'US7ASCII','WE8ISO8859P1'),'?','n')) as nombre,
                conv_char(replace(CONVERT(glosa,'US7ASCII','WE8ISO8859P1'),'?','n')) as glosa,
                precio,
                2 as orden
            from upeu_opciones 
            where id_venta='".$id_venta."'
            and estado='1'
            order by orden,nombre";
        $query = DB::connection('oracleapp')->select($sql);
        
        return $query;
    }
    public static function showOperacion($id_operacion,$id_aplicacion){
        
        $data_apli = PayonlineData::showAplicacion($id_aplicacion);
        
        $tb_main='';
        $tb_operacion = "";
        $cx='';
        foreach($data_apli as $row){
            $tb_main      = $row->tabla_main;
            $tb_operacion = $row->tabla_operacion;
            $cx=$row->conexion;
        }
        
        $sql="select id_venta from ".$tb_main." where estado='1'";
        
        $query = DB::connection($cx)->select($sql);
        $id_venta = '';
        foreach($query as $row){
            $id_venta = $row->id_venta;
        }
        
        
        $data =[
            'id_venta'=>$id_venta,
            'id_opcion'=>$id_operacion,
            'nombre'=>'',
            'glosa'=>'',
            'precio'=>0,
        ];
        
        $rpta=[
            'nerror'=>0,
            'mensaje'=>'OK',
            'data'=>$data
            
        ];
        
       
        
        $sql = "select 
                    id_venta,
                    id_opcion,
                    conv_char(replace(CONVERT(nombre,'US7ASCII','WE8ISO8859P1'),'?','n')) as nombre,
                    conv_char(replace(CONVERT(glosa,'US7ASCII','WE8ISO8859P1'),'?','n')) as glosa,
                    precio
                from ".$tb_operacion."
                where id_venta='".$id_venta."'
                and id_opcion='".$id_operacion."'";
            $query = DB::connection($cx)->select($sql);
            
            if(count($query)>0){
                foreach($query as $row){
                    $data =[
                        'id_venta'=>$id_venta,
                        'id_opcion'=>$id_operacion,
                        'nombre'=>$row->nombre,
                        'glosa'=>$row->glosa,
                        'precio'=>$row->precio,
                    ];
                }
                $rpta=[
                    'nerror'=>0,
                    'mensaje'=>'OK',
                    'data'=>$data

                ];
            }else{
                $rpta=[
                    'nerror'=>1,
                    'mensaje'=>'No existe tipo de operación',
                    'data'=>[]

                ];
        }
        
        return $rpta;
    }
    public static function payUpeu($id_persona,$num_operacion,$importe,$id_operacion,$id_aplicacion, $ip,$tokens,$id_origen){
        
        $rpta = ['error'=>1,'msg'=>'No se ha agenerado transacción','codigo'=>$id_persona,'importe'=>$importe,'id'=>''];
        
        $s_id='';
        for($i=1;$i<=30;$i++){
            $s_id.='0';
        }

        try{
            $data_apli = PayonlineData::showAplicacion($id_aplicacion);

            $url_servicio = "";
            $cx='';
            foreach($data_apli as $row){
                $url_servicio = $row->url_servicio;
                $cx=$row->conexion;
            }

            $pdo = DB::connection($cx)->getPdo();
            $moneda = '0';

            $data_tok = PayonlineData::show(1);

            $tok='';
            foreach($data_tok as $row){
                $tok  =  $row->tokens;
            }

            if($tokens == $tok){
 
                $stmt = $pdo->prepare("begin ".$url_servicio."(
                                                :s_id_personal,
                                                :s_operacion,
                                                :s_moneda,
                                                :s_importe,
                                                :s_ip,
                                                :s_banco,
                                                :s_id_origen,
                                                :s_id
                                             ); end;");
                $stmt->bindParam(':s_id_personal', $id_persona, PDO::PARAM_STR);
                $stmt->bindParam(':s_operacion', $num_operacion, PDO::PARAM_STR);
                $stmt->bindParam(':s_moneda', $moneda, PDO::PARAM_STR);
                $stmt->bindParam(':s_importe', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':s_ip', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':s_banco', $id_operacion, PDO::PARAM_STR);
                $stmt->bindParam(':s_id_origen', $id_origen, PDO::PARAM_STR);
                $stmt->bindParam(':s_id', $s_id, PDO::PARAM_STR);
                
                $stmt->execute(); 
                
                $rpta = ['error'=>0,'msg'=>'Deposito registrado','codigo'=>$id_persona,'importe'=>$importe,'id'=>$s_id];
                
            }else{
                $rpta = ['error'=>2,'msg'=>'Deposito no registrado token incorrecto','codigo'=>$id_persona,'importe'=>$importe,'id'=>''];
            }
            
            
        }catch(Exception $e){   
            //dd($e->getMessage());
            
            $rpta = ['error'=>1,'msg'=>substr($e->getMessage(),0,1300).'Ruta: '.substr($e->getFile(),-150).' Linea: '.$e->getLine(),'codigo'=>$id_persona,'importe'=>$importe,'id'=>''];
            
           // $rpta = ['error'=>1,'msg'=>'Deposito no registrado','codigo'=>$id_persona,'importe'=>$importe,'id'=>''];

        }
        
        return $rpta;
       
    }
    public static function tipoCambio(){
        $sql="select id_venta from upeu_main where estado='1'";
        
        $query = DB::connection('oracleapp')->select($sql);
        $id_venta = '';
        foreach($query as $row){
            $id_venta = $row->id_venta;
        }
        
        $sql="select TIPO_CAMBIO from UPEU_TIPO_CAMBIO where ID_VENTA='".$id_venta."'";
        
        $query = DB::connection('oracleapp')->select($sql);
        $tc = 0;
        foreach($query as $row){
            $tc = $row->tipo_cambio;
        }
        return $tc;
    }
    
     public static function envioRespuesta($datos){
   
        $rpta["nerror"]=1;
        $rpta["mensaje"]='No se ha procesado el envío de respuesta';
        try{
            $id_aplicacion      = $datos['id_aplicacion'];
        
            $data = PayonlineData::show(1);

            $tokens = '';

            foreach($data as $row){
                $tokens = $row->tokens_proceso;
            }

            $data  = PayonlineData::showAplicacion($id_aplicacion);

            
            $url_respuesta = '';
            foreach($data as $row){
                $url_respuesta = $row->url_respuesta;
            }
            
            if(strlen($url_respuesta)>0){
                
                /*$values['tokens']     = $tokens;
                $values['importe']    = $datos['importe'];
                $values['id_origen']  = $datos['id_origen'];
                $values['numorden']   = $datos['numorden'];
                $values['modo_pago']  = 'VISA';

                

                $data = http_build_query($values);

                $options = array (
                'http' => array (
                    'method' => 'POST',
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                    )
                );

                $url= $url_respuesta;

                $context   = stream_context_create($options);
                $respuesta = file_get_contents($url, false, $context);
                return $respuesta;
                */
                //$url= 'http://app09.sotil.lamb-dev.upeu/api/lamb_financial/public/recepcion/';
                
                $url= $url_respuesta;
                
                
                
                $header = array("Content-type: application/x-www-form-urlencoded");
                $request_body=[
                    'tokens'=>$tokens,
                    'importe'=>$datos['importe'],
                    'id_origen'=>$datos['id_origen'],
                    'numorden'=>$datos['numorden'],
                    'modo_pago'=>'VISA',
                    'id_respuesta'=>$datos['id_respuesta']
                ];
                     
                $params ='tokens='.$tokens.'&id_origen='.$datos['id_origen'].'&importe='.$datos['importe'].'&numorden='.$datos['numorden'].'&modo_pago=VISA&id_respuesta='.$datos['id_respuesta'];
                
                $ch = curl_init();

                curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_HEADER =>false,
                    CURLOPT_POST => true,
                    CURLOPT_SSL_VERIFYPEER => FALSE,
                    CURLOPT_RETURNTRANSFER =>TRUE,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_HTTPHEADER => $header
                ));
                $response = curl_exec($ch);

                $err = curl_error($ch);
                curl_close($ch);
                
                return $response;
                
                
            }else{
                $rpta["nerror"] = -1;
                $rpta["mensaje"] = 'No tiene respuesta ';
            }

            
        }catch(Exception $e){
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'Error envío de respuesta '.$e->getMessage();
            //$rpta["context"] =$context ;
        }
        

        return json_encode($rpta);

    }   
    

    private static function replacetexto($texto)
    {

        $string = trim($texto);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        //Esta parte se encarga de eliminar cualquier caracter extraño



        return $string;
    }
}
