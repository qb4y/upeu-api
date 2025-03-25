<?php
namespace App\Http\Data\Payonline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;
class VisanetData extends Controller{
    private $request; 

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function showVisa($id_visa){
        $sql = 'select 
                id_visa,
                merchantid,
                accesskey,
                secretkey,
                prod,
                descripcion,
                "USER",
                pwd,
                correo,
                dni
            from payonline_visa 
            where id_visa='.$id_visa;
        $query = DB::select($sql);
        
        return $query;
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
    public static function ruta_url($url){
        $dat = VisanetData::listConfigPlanilla("HTTP");
        $protocol =$dat["valor"];
        $url_ori = str_replace("http://",$protocol."://", $url);
        return $url_ori;
    }
    public static function listVisa(){
        $sql = "select 
                id_visa,
                merchantid,
                accesskey,
                secretkey,
                prod,
                descripcion
            from payonline_visa 
            order by id_visa";
        $query = DB::select($sql);
        
        return $query;
    }
    public static function listTipoventa(){
        $sql = "select 
                id_tipoventa,
                nombre
            from tipo_venta 
            order by id_tipoventa";
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
                tabla_main,
                campos
            from payonline_aplicacion 
            where id_aplicacion=".$id_aplicacion;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function showPayonline($id_payonline){
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
    
    public static function listTransaccion(){
                
        $sql = "
            
            select 
                id_tipotransaccion ,
                nombre                
            from tipo_transaccion 
            where estado='1'
            order by nombre";
        $query = DB::select($sql);
        
        return $query;
    }
    public static function ordenVisa($id_visa){
        $sql = "select 
                id_visa_orden,
                id_visa,
                num_orden
            from payonline_visa_orden 
            where vigencia=1
            and id_visa=".$id_visa;
        $query = DB::select($sql);
        
        $num_orden = 0;
        foreach($query as $row){
               $num_orden = $row->num_orden;
        }
        $num_orden++;
        
        $query = "update payonline_visa_orden SET 
                    num_orden =".$num_orden."
                where vigencia=1
                and id_visa=".$id_visa;
        
        DB::update($query);
        
        return $num_orden;
    }
    public static function verificapersona($request){
        
        $data_apli = VisapaymentData::showAplicacion($request->id_aplicacion);
        
        $sp='';
        $mensaje = "";
        $nerror  = 0;
        $campos  = '';
 
        if(count($data_apli)>0){
            foreach($data_apli as $row){
                $sp=$row->url_verificacion;
                $campos=$row->campos;
            }
            

            $data =[];
           
            if((strlen($sp)>0) and (strlen($campos)>0)) {  

                $columnas  = explode(",", $campos);
                $acolumna = array();
                      
                foreach($columnas as $col){
                     
                    
                    $acolumna["P_".strtoupper($col).""] = $request->input("".$col."");
    
                }
                
                $bindings = $acolumna;
                
                $result  = DB::executeProcedureWithCursor($sp, $bindings);
                $datos   = $result[0];   
                
                if( $datos->valida == 'S'){ 
                    $data =[
                        'id_persona'=>$datos->id_persona,
                        'id_persona_visa'=>$datos->id_persona_visa,
                        'nombres_visa'=>$datos->nombres_visa,
                        'apellidos_visa'=>$datos->apellidos_visa,
                        'email_visa'=>$datos->email_visa,
                        'eap'=>$datos->eap,
                        'codigo'=>$datos->codigo,
                        'num_documento'=>$datos->num_documento,
                        'apellidos'=>$datos->apellidos,
                        'nombres'=>$datos->nombres,
                        'tipodocumento'=>$datos->tipodocumento,
                        'glosa'=>$datos->glosa,
                        'id_dinamica'=>$datos->id_dinamica,

                        'ruc'=>$datos->ruc,
                        'razonsocial'=>$datos->razonsocial,
                        'id_cliente'=>$datos->id_cliente,
                        'id_comprobante'=>$datos->id_comprobante,

  
                    ];
                }else{
                    $nerror  = 1;
                    $mensaje = $datos->valida.'*'.$datos->msgerror ;
                }
            }else{
                $nerror  = 1;
                $mensaje = "No existe proceso de verificación ";
            }
        }else{
            $nerror  = 1;
            $mensaje = "No existe servicio de verificación para la aplicación ";
        }
        $respuesta=[
            'nerror'=>$nerror,
            'mensaje'=>$mensaje,
            'data'=>$data
        ];
        
        return $respuesta;
    }
    //public static function payUpeu($id_persona,$num_operacion,$id_entidad,$id_depto,$importe,$id_dinamica,$cod_tarjeta,$id_aplicacion, $ip,$tokens,$id_origen){
    public static function payUpeu($id_persona,$num_operacion,$datosrecibidos,$cod_tarjeta,$id_aplicacion, $ip,$IMP_AUTORIZADO){
        
        
        $s_id='';
        for($i=1;$i<=30;$i++){
            $s_id.='0';
        }

        $id_entidad     = $datosrecibidos['id_entidad'];
        $id_depto       = $datosrecibidos['id_depto'];
        $importe        = $IMP_AUTORIZADO; //$datosrecibidos['importe'];
        $id_dinamica    = $datosrecibidos['id_dinamica'];
        $tokens         = $datosrecibidos['tokens'];
        $id_origen      = $datosrecibidos['id_origen'];
        $id_cliente     = $datosrecibidos['id_cliente'];
        $id_comprobante = $datosrecibidos['id_comprobante'];
        $id_tipoventa = $datosrecibidos['id_tipoventa'];

        $rpta = ['error'=>1,'msg'=>'No se ha agenerado transacción','codigo'=>$id_persona,'importe'=>$importe,'id'=>''];
        

        try{
            $data_apli = VisapaymentData::showAplicacion($id_aplicacion);

            $url_servicio = "";
 
            foreach($data_apli as $row){
                $url_servicio = $row->url_servicio;
   
            }

            $pdo = DB::getPdo();
            $moneda = '0';
            
           
            $data_tok = VisapaymentData::show(2);
            
            $tok='';
            foreach($data_tok as $row){
                $tok  =  $row->tokens;
            }

            if($tokens == $tok){
                $nerror = 0;
                $msgerror = '';
                for ($i = 1; $i <= 200; $i++) {
                    $msgerror .= '0';
                }
//                dd($id_persona, $num_operacion, $id_entidad, $id_depto, $importe, $ip, $id_dinamica, $cod_tarjeta,$id_origen);
                $stmt = $pdo->prepare("begin ".$url_servicio."(
                                                :P_ID_PERSONA,
                                                :P_OPERACION,
                                                :P_ID_ENTIDAD,
                                                :P_ID_DEPTO,
                                                :P_IMPORTE,
                                                :P_IP,
                                                :P_ID_DINAMICA,
                                                :P_COD_TARJETA,
                                                :P_ID_ORIGEN,
                                                :P_ID_CLIENTE,
                                                :P_ID_COMPROBANTE,
                                                :P_ID_TIPOVENTA,
                                                :P_ID,
                                                :P_ERROR,
                                                :P_MSGERROR
                                             ); end;");
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_OPERACION', $num_operacion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_COD_TARJETA',  $cod_tarjeta, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ORIGEN', $id_origen, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID', $s_id, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR',  $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);

 
//                dd('.->',$stmt);
                $stmt->execute();
                if($nerror==0){
                    $rpta = ['error'=>0,'msg'=>'Deposito registrado','codigo'=>$id_persona,'importe'=>$importe,'id'=>$s_id];
                }else{
                    $rpta = ['error'=>3,'msg'=>$msgerror,'codigo'=>$id_persona,'importe'=>$importe,'id'=>''];
                }
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
    
    public static function showVisaError($id_visa_error){
        $sql = "select 
                id_visa_error,
                desapoyo,
                descliente
            from payonline_visa_error 
            where id_visa_error=".$id_visa_error;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function visaLog($id_persona,$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion){
        
        $sql = "select 
                coalesce(max(id_visa_log),0)+1 as id
            from PAYONLINE_VISA_LOG";
        $query = DB::select($sql);
        $id_visa_log = 0;
        foreach($query as $row){
            $id_visa_log = $row->id;
        }
        $sql = "select 
                TO_CHAR(sysdate, 'YYYY-MM-DD HH24:MI:SS') as fecha
            from dual";
        $query = DB::select($sql);
        $fecha_sys = date("Y-m-d H:i:s");
        foreach($query as $row){
            $fecha_sys = $row->fecha;
        }
    
        DB::table('PAYONLINE_VISA_LOG')->insert(
            array('id_visa_log' => $id_visa_log,
                'codigo_persona' =>$id_persona,
                'rpta_visa' =>substr($json_visa,0,1499),
                'id_visa_error' =>$errorCode,
                'mensaje' =>substr($mensaje,0,499),
                'rpta_servicio' =>substr($json_servicio,0,499),
                'nerror_servicio' =>$nerrorservice,
                'rpta_respuesta' =>substr($repuesta_pta,0,1499),
                'nerror_respuesta' =>$nerrorrpta,
                'ip'=>$ip,
                'fecha'=>$fecha_sys,
                'importe' =>$importe,
                'id_visa' =>$id_visa,
                'id_aplicacion' =>$id_aplicacion,
                'id_operacion' =>$id_operacion
                )
        );
    } 
    public static function envioRespuesta($datos){
   
        $rpta["nerror"]=1;
        $rpta["mensaje"]='No se ha procesado el envío de respuesta';
        try{
            $id_aplicacion      = $datos['id_aplicacion'];
        
            $data = VisanetData::showPayonline(4);

            $tokens = '';

            foreach($data as $row){
                $tokens = $row->tokens_proceso;
            }

            $data  = VisapaymentData::showAplicacion($id_aplicacion);

            
            $url_respuesta = '';
            foreach($data as $row){
                $url_respuesta = $row->url_respuesta;
            }
            
            if(strlen($url_respuesta)>0){
                
                
                
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

        }
        

        return json_encode($rpta);

    } 
    public static function showventa($datosrecibidos){
        $documento  = $datosrecibidos['id_respuesta'];
        $id_entidad = $datosrecibidos['id_entidad'];
        $id_depto   = $datosrecibidos['id_depto'];
        $sql = "select 
                a.id_comprobante,
                c.id_ruc
            from venta a,conta_entidad b, conta_empresa c 
            where a.id_entidad=b.id_entidad
            and b.id_empresa=c.id_empresa
            and a.serie||'-'||a.numero='".$documento."' 
            and a.id_entidad=".$id_entidad." 
            and a.id_depto='$id_depto'";
        $query = DB::select($sql);
        
        return $query;
    }


}

