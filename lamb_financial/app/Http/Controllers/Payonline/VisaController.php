<?php
namespace App\Http\Controllers\Payonline;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Payonline\VisaData;
use App\Http\Data\Payonline\PayonlineData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Session;


class VisaController extends Controller{
    private $request;
    
    
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function shopping(Request $request){
        $dataVisa = VisaData::listVisa();
        $dataAplicacion = PayonlineData::listAplicacion();
        $dataPayonline = PayonlineData::show(1);
        $dataOperacion = PayonlineData::listOperacion();
        //dd($dataPayonline);
        return view('payonline.visa.shopping',['dataVisa'=>$dataVisa,'dataAplicacion'=>$dataAplicacion,'dataPayonline'=>$dataPayonline,'dataOperacion'=>$dataOperacion]);
    }
    
    private function getGUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12).$hyphen
                .chr(125);// "}"
            $uuid = substr($uuid, 1, 36);
            return $uuid; 
        }
    }
    private function datosVisa($id_visa){
        
        $data = VisaData::showVisa($id_visa);
        
        $respuesta =[
            'prod'=>'N',
            'merchantid'=>'',
            'accesskeyid'=>'',
            'secretaccesskey'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'prod'=>$row->prod,
                'merchantid'=>$row->merchantid,
                'accesskeyid'=>$row->accesskey,
                'secretaccesskey'=>$row->secretkey,
            ];
        }
        
        return $respuesta;
    }
    private function datosVisaError($id_visa_error){
        
        $data = VisaData::showVisaError($id_visa_error);
        
        $respuesta =[
            'cod'=>$id_visa_error,
            'desapoyo'=>'',
            'descliente'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'cod'=>$id_visa_error,
                'desapoyo'=>$row->desapoyo,
                'descliente'=>$row->descliente
            ];
        }
        
        return $respuesta;
    }
    private function datosPayOnline($id_payonline){
        
        $data = PayonlineData::show($id_payonline);
        
        $respuesta =[
            'tokens'=>'',
            'tokensproceso'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'tokens'=>$row->tokens,
                'tokensproceso'=>$row->tokens_proceso
            ];
        }
        
        return $respuesta;
    }
    public function tokens(Request $request){
        $method = $request->method();

        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";
        
        if ($request->isMethod('post')) {
            if ($request->has('importe')) {
                
                $monto    = $request->importe;
                
                $id_aplicacion   = $request->id_aplicacion;
                if($id_aplicacion!=1){
                    if (is_numeric($monto)){
                        if ( $monto > 0 ) {
                            try{
    
                                $tokens          = $request->tokens;
    
                                $datospayonline  = $this->datosPayOnline(1);
    
                                if($tokens == $datospayonline['tokens']){
                                    $id_visa         = $request->id_negocio;
                                    $datosvisa       = $this->datosVisa($id_visa);
                                    $sessiontoken    = $this->getGUID();
                                    Session::put('s_sessiontoken', $sessiontoken);
                                    $merchantid      = $datosvisa['merchantid'];
    
                                    if(strlen($merchantid)>0 ){
                                        $accesskeyid     = $datosvisa['accesskeyid'];
                                        $secretaccesskey = $datosvisa['secretaccesskey'];
                                        $prod            = $datosvisa['prod'];
                                        $tipodoc         = $request->tipodoc;
                                        $numdoc          = $request->numdoc;
                                        $id_persona      = $request->id_persona;
                                        $id_aplicacion   = $request->id_aplicacion;
    
                                        $nombre          = $request->nombre;
                                        $paterno         = $request->paterno;
                                        $materno         = $request->materno;
                                        $sexo            = $request->sexo;
                                        $correo          = $request->correo;
                                        $moneda          = $request->moneda;
                                        $total           = $request->importe;
                                        $id_origen       = $request->id_origen;
                                        $importe         = $total ;
                                        if($moneda == '1'){
                                            $tc       = PayonlineData::tipoCambio();
                                            $importe  = round($total*$tc,2);
                                        }
    
                                        $respuesta       = PayonlineData::verificapersona($id_persona, $numdoc, $id_aplicacion,$nombre,$paterno,$materno,$sexo,$correo,$id_origen);
    
    
                                        if($respuesta['nerror']==0){
    
                                            $data            = $respuesta['data'];
    
    
                                            $nombres         = $data["nombres"];
                                            $apellidos       = $data["apellidos"];
                                            $codigo          = $data["codigo"];
                                            $eap             = $data["eap"];
    
    
                                            $nombres_visa    = $data["nombres_visa"];
                                            $apellidos_visa  = $data["apellidos_visa"];
                                            $email_visa      = $data["email_visa"];
    
                                            $id_personal      = $data["id_personal"];
    
                                            $id_operacion     = $request->id_operacion;

                                            $rpta       = PayonlineData::showOperacion($id_operacion,$id_aplicacion);
    
                                            if($rpta['nerror']==0){
                                                $datos  = $rpta['data'];
                                                $datosrecibidos = [
                                                    'tipodoc'        => $tipodoc,
                                                    'numdoc'         => $numdoc,
                                                    'id_persona'     => $id_persona,
                                                    'id_personal'    => $id_personal,
                                                    'id_origen'      => $request->id_origen,
                                                    'id_negocio'     => $id_visa,
                                                    'id_aplicacion'  => $id_aplicacion,
                                                    'concepto'       => $datos['glosa'],
                                                    'id_operacion'   => $id_operacion,
                                                    'importe'        => $importe,
                                                    'importe_dol'    => $total,
                                                    'nombres'        => $nombres,
                                                    'apellidos'      => $apellidos,
                                                    'codigo'         => $codigo,
                                                    'eap'            => $eap,
                                                    'nombres_visa'   => $nombres_visa,
                                                    'apellidos_visa' => $apellidos_visa,
                                                    'email_visa'     => $email_visa,
                                                    'tokens'        => $tokens,
                                                    'moneda'         => $moneda
                                                ];
    
                                                Session::put('s_sessiondatos',$datosrecibidos);
    
                                                $sessionKey  = $this->createToken($importe,$prod,$merchantid,$accesskeyid,$secretaccesskey,$sessiontoken);
    
                                                $numorden    = VisaData::ordenVisa($id_visa);
    
                                                return view('payonline.visa.tokens',['sessionToken'=>$sessiontoken,'merchantid'=>$merchantid,
                                                    'importe'=>$importe,'numorden'=>$numorden,'prod'=>$prod,'datosrecibidos'=>$datosrecibidos]);
                                            }else{
                                                $nerror = "";
                                                $mensaje = $rpta['mensaje'];
                                            }
                                        }else{
                                            $nerror = "";
                                            $mensaje = $respuesta['mensaje'];
                                        }
                                    }else{
    
                                        $nerror = "";
                                        $mensaje = "Código de Comercio(merchantId) no existe";
                                    }
                                }else{
    
                                    $nerror = "";
                                    $mensaje = "Tokens Incorrecto";
                                }
                            }catch(Exception $e){   
                                $nerror = "";
                                //$mensaje = $e->getMessage();
                                $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile().'MSG: '.$e->getMessage();
                            }
                        }else{
                            $nerror = "";
                            $mensaje = "Importe debe ser mayor a 0(cero)";
                        }
                    }else{
                        $nerror = "";
                        $mensaje = "Importe debe ser numérico";
                    }
                } else{
                    $nerror = "";
                    $mensaje = "Realizar sus pagos desde el nuevo portal ";
                }

            }else{
                $nerror = "";
                $mensaje = "No se envía el dato importe ";
            }
        }
        
        return  view('payonline.visa.error',['nerror'=>$nerror,'mensaje'=>$mensaje,'data'=>'']);
    }
    public function tokensapp(Request $request){
        
        $method = $request->method();

        $nerror = "";
        $mensaje = "Tokens Incorrecto";
        
        
        $tokens   = $request->tokens;
        
        $datospayonline  = $this->datosPayOnline(1);
        
        if($tokens == $datospayonline['tokens']){
            if ($request->has('importe')) {
                
                $monto    = $request->importe;
                $id_aplicacion   = $request->id_aplicacion;
                if($id_aplicacion!=1){
                    if (is_numeric($monto)){
                        if ( $monto > 0 ) {
                            try{
    
                                $id_visa         = $request->id_negocio;
                                $datosvisa       = $this->datosVisa($id_visa);
                                $sessiontoken    = $this->getGUID();
                                Session::put('s_sessiontoken', $sessiontoken);
                                $merchantid      = $datosvisa['merchantid'];
    
                                if(strlen($merchantid)>0 ){
                                    $accesskeyid     = $datosvisa['accesskeyid'];
                                    $secretaccesskey = $datosvisa['secretaccesskey'];
                                    $prod            = $datosvisa['prod'];
                                    $tipodoc         = $request->tipodoc;
                                    $numdoc          = $request->numdoc;
                                    $id_persona      = $request->id_persona;
                                    $id_aplicacion   = $request->id_aplicacion;
    
                                    $nombre          = $request->nombre;
                                    $paterno         = $request->paterno;
                                    $materno         = $request->materno;
                                    $sexo            = $request->sexo;
                                    $correo          = $request->correo;
                                    $moneda          = $request->moneda;
                                    $total           = $request->importe;
                                    $id_origen       = $request->id_origen;
                                    $importe         = $total ;
                                    if($moneda == '1'){
                                        $tc       = PayonlineData::tipoCambio();
                                        $importe  = round($total*$tc,2);
                                    }
    
                                    $respuesta       = PayonlineData::verificapersona($id_persona, $numdoc, $id_aplicacion,$nombre,$paterno,$materno,$sexo,$correo,$id_origen);
    
    
                                    if($respuesta['nerror']==0){
    
                                        $data            = $respuesta['data'];
    
    
                                        $nombres         = $data["nombres"];
                                        $apellidos       = $data["apellidos"];
                                        $codigo          = $data["codigo"];
                                        $eap             = $data["eap"];
    
    
                                        $nombres_visa    = $data["nombres_visa"];
                                        $apellidos_visa  = $data["apellidos_visa"];
                                        $email_visa      = $data["email_visa"];
    
                                        $id_personal      = $data["id_personal"];
    
                                        $id_operacion     = $request->id_operacion;
    
                                        $rpta       = PayonlineData::showOperacion($id_operacion,$id_aplicacion);
    
                                        if($rpta['nerror']==0){
                                            $datos  = $rpta['data'];
                                            $datosrecibidos = [
                                                'tipodoc'        => $tipodoc,
                                                'numdoc'         => $numdoc,
                                                'id_persona'     => $id_persona,
                                                'id_personal'    => $id_personal,
                                                'id_origen'      => $request->id_origen,
                                                'id_negocio'     => $request->id_negocio,
                                                'id_aplicacion'  => $id_aplicacion,
                                                'concepto'       => $datos['glosa'],
                                                'id_operacion'   => $id_operacion,
                                                'importe'        => $importe,
                                                'importe_dol'    => $request->importe,
                                                'nombres'        => $nombres,
                                                'apellidos'      => $apellidos,
                                                'codigo'         => $codigo,
                                                'eap'            => $eap,
                                                'nombres_visa'   => $nombres_visa,
                                                'apellidos_visa' => $apellidos_visa,
                                                'email_visa'     => $email_visa,
                                                'tokens'        => $tokens,
                                                'moneda'         => $moneda
                                            ];
    
                                            Session::put('s_sessiondatos',$datosrecibidos);
    
                                            $sessionKey  = $this->createToken($importe,$prod,$merchantid,$accesskeyid,$secretaccesskey,$sessiontoken);
    
                                            $numorden    = VisaData::ordenVisa($id_visa);
    
                                            return view('payonline.visa.tokens',['sessionToken'=>$sessiontoken,'merchantid'=>$merchantid,
                                                'importe'=>$importe,'numorden'=>$numorden,'prod'=>$prod,'datosrecibidos'=>$datosrecibidos]);
                                        }else{
                                            $nerror = "";
                                            $mensaje = $rpta['mensaje'];
                                        }
                                    }else{
                                        $nerror = "";
                                        $mensaje = $respuesta['mensaje'];
                                    }
                                }else{
    
                                    $nerror = "";
                                    $mensaje = "Código de Comercio(merchantId) no existe";
                                }
                            }catch(Exception $e){   
                                $nerror = "";
                                //$mensaje = $e->getMessage();
                                $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile().'MSG: '.$e->getMessage();
                            }
                        }else{
                            $nerror = "";
                            $mensaje = "Importe debe ser mayor a 0(cero)";
                        }
                    }else{
                        $nerror = "";
                        $mensaje = "Importe debe ser numérico";
                    }
                } else{
                    $nerror = "";
                    $mensaje = "Realizar sus pagos desde el nuevo portal ";
                }
            }else{
                $nerror = "";
                $mensaje = "No se envía el dato importe ";
            }
        }
        
        return  view('payonline.visa.error',['nerror'=>$nerror,'mensaje'=>$mensaje,'data'=>'']);
    }
    
    private function createToken($importe,$prod,$merchantId,$accessKey,$secretKey,$uuid){
            switch ($prod) {
                case 'S':
                    $url = "https://apice.vnforapps.com/api.ecommerce/api/v1/ecommerce/token/".$merchantId;
                    break;
                case 'N':
                    $url = "https://devapice.vnforapps.com/api.ecommerce/api/v1/ecommerce/token/".$merchantId;
                    //$url = "https://apitestenv.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/".$merchantId;
                    break;
          
            }
            $header = array("Content-Type: application/json","VisaNet-Session-Key: ".$uuid);
            $request_body=[
                    'amount'=>$importe
            ];
            $ch = curl_init();
            
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => "$accessKey:$secretKey",
                CURLOPT_HEADER =>false,
                CURLOPT_POST => true,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_RETURNTRANSFER =>TRUE,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($request_body),
                CURLOPT_HTTPHEADER => $header
            ));
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            $json = json_decode($response);
            $dato = '';
            return $dato;
    }
    
    public function imprimir(Request $request){
        
        $method = $request->method();
        
        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";
        $datosrecibidos   = Session::get('s_sessiondatos');
        
        $json_visa = '';
        $nerrorservice = -1;
        $json_servicio = '';
        $nerrorrpta = -1;
        $repuesta_pta = '';
        $errorCode=-1;
        $importe=0;
        $id_visa=0;
        $id_aplicacion=0;
        $id_operacion='';
        
        $ip              = GlobalMethods::ipClient($request);

        //if ($datosrecibidos) {
        
            if ($request->isMethod('post')) {
                if ($request->has('transactionToken')) {
                    
                    try{
                        $datosrecibidos   = Session::get('s_sessiondatos');
                        
                        $id_visa          = $datosrecibidos['id_negocio'];
                        $importe          = $datosrecibidos['importe'];

                        $transactiontoken = $request->transactionToken;
                        $datosvisa        = $this->datosVisa($id_visa);
                        $merchantid       = $datosvisa['merchantid'];
                        $accesskeyid      = $datosvisa['accesskeyid'];
                        $secretaccesskey  = $datosvisa['secretaccesskey'];
                        $prod             = $datosvisa['prod'];

                        $sessiontoken     = Session::get('s_sessiontoken');

                        $respuesta        = $this->authorizationvisa($prod,$merchantid,$transactiontoken,$accesskeyid,$secretaccesskey,$sessiontoken);
                        $respuesta        = json_decode($respuesta);
                        if($respuesta){
                            $errorCode      = $respuesta->errorCode;
                            $errorMessage   = $respuesta->errorMessage;
                            $IMP_AUTORIZADO = $respuesta->data->IMP_AUTORIZADO;
                            $RESPUESTA      = $respuesta->data->RESPUESTA;
                            $NUMORDEN       = $respuesta->data->NUMORDEN;
                            $FECHAYHORA_TX  = $respuesta->data->FECHAYHORA_TX;
                            $DSC_COD_ACCION = $respuesta->data->DSC_COD_ACCION;
                            $CODACCION      = $respuesta->data->CODACCION;
                            $PAN            = $respuesta->data->PAN;
                            $COD_AUTORIZA   = $respuesta->data->COD_AUTORIZA;
                            $CODTIENDA      = $respuesta->data->CODTIENDA;
                            $respuestaService = [];
                            $nerrorservice   = -1;
                            
                            $id_personal     = $datosrecibidos['id_personal'];
                            
                            $id_operacion  = $datosrecibidos['id_operacion'];
                            $id_aplicacion = $datosrecibidos['id_aplicacion'];
                                
                            $rpta_serv = [];
                            $id_resp='';
                            if ($errorCode == 0 and $RESPUESTA == '1' and $IMP_AUTORIZADO > 0) {
                                $mensaje ='0K';
                                //$rp= ['error'=>0,'msg'=>'Deposito registrado','codigo'=>$id_personal,'importe'=>$IMP_AUTORIZADO];//solo para probar
                                $respuestaService = PayonlineData::payUpeu($id_personal,$NUMORDEN,$IMP_AUTORIZADO,$id_operacion,$id_aplicacion, $ip, $datosrecibidos['tokens'],$datosrecibidos['id_origen']);
                                $nerrorservice = $respuestaService['error'];
                                $rpta_serv     = $respuestaService;
                                $id_resp       = $respuestaService['id'];
                            }else{
                                $datosvisaerror = $this->datosVisaError($errorCode);
                                
                                if(strlen($datosvisaerror['descliente'])>0){
                                    $mensaje = $datosvisaerror['descliente'];
                                }else{
                                    $mensaje = $errorMessage.': '.$DSC_COD_ACCION;
                                }
                                
                            }
                            
                            $jrpta     = (array)$respuesta;
                            $json_visa = json_encode($jrpta);
                            
                            
                            $json_servicio = json_encode($respuestaService);
                            
                            $nerrorrpta    = -1;

                            $repuesta_pta  =  '-';
                            
                            if ($errorCode == 0 and $RESPUESTA == '1' and $IMP_AUTORIZADO > 0) {
                                $datos['id_aplicacion'] = $id_aplicacion;
                                $datos['importe']   = $IMP_AUTORIZADO;
                                $datos['id_origen'] = $datosrecibidos['id_origen'];
                                $datos['numorden']  = $NUMORDEN;
                                $datos['id_respuesta']  = $id_resp;
                                
                                $rpta_rpta = PayonlineData::envioRespuesta($datos);
                                
                                $q = json_decode($rpta_rpta);
                                
                                if(isset($q->nerror)){
                                    $nerrorrpta    =  $q->nerror;
                                }else{
                                    $nerrorrpta    =  -2;
                                    $rpta_rpta     =  'Error de página de la respuesta';
                                }
                                

                                $repuesta_pta  =  $rpta_rpta;
                            }
                            VisaData::visaLog($id_personal,$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion);
                            
                            $rpta  =  PayonlineData::showOperacion($id_operacion,$id_aplicacion);
                            
                            $operacion = '';

                            if($rpta['nerror']==0){
                                $datos  = $rpta['data'];
                                $operacion = $datos['glosa'];
                            }
                            
                            $respuesta =[
                                'respuesta'=>$RESPUESTA,
                                'numorden'=>$NUMORDEN,
                                'dsc_cod_accion'=>$DSC_COD_ACCION,
                                'pan'=>$PAN,
                                'fechahora'=>$FECHAYHORA_TX,
                                'importe'=>$IMP_AUTORIZADO,
                                'operacion'=> $operacion,
                                'id_origen'=> $datosrecibidos['id_origen']
                            ];
                            
                            return  view('payonline.visa.print',['respuesta'=>$respuesta, 'datosrecibidos'=>$datosrecibidos,'rpta_serv'=>$rpta_serv]);
                        }else{
                            $nerror = "";
                            $mensaje = "No hay respuesta de servicio VISA"; 
                        }
                    }catch(Exception $e){   
                        $nerror = "";
                        //$mensaje = $e->getMessage();
                        $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile().'MSG: '.$e->getMessage();
                        //$mensaje = '<b>Error Interno</b><br>line: '.$e->getLine().' Msg: '.$e->getMessage();
                    }
                }else{
                    $nerror = "";
                    $mensaje = "No se ha generado  Transacción Token VISA"; 
                }
            }
            
        /*}else {
            $nerror = "";
            $mensaje = "No hay datos en sesión";
        }*/

        VisaData::visaLog($datosrecibidos['id_personal'],$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion);
        return  view('payonline.visa.error',['nerror'=>$nerror,'mensaje'=>$mensaje,'data'=>'']);
    }
    private function authorizationvisa($prod,$merchantid,$transactiontoken,$accesskeyid,$secretaccesskey,$sessiontoken){
        switch ($prod) {
            case 'S':
                $url = "https://apice.vnforapps.com/api.authorization/api/v1/authorization/web/".$merchantid;
                break;
            case 'N':
                $url = "https://devapice.vnforapps.com/api.authorization/api/v1/authorization/web/".$merchantid;
                //$url = "https://apitestenv.vnforapps.com/api.authorization/v3/authorization/ecommerce/".$merchantid;
                break;
  
        }
        $header = array("Content-Type: application/json","VisaNet-Session-Key: ".$sessiontoken);
        $request_body=[
                    'transactionToken'=>$transactiontoken,
                    'sessionToken'=>$sessiontoken
        ];
  
        $ch = curl_init();
        curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => "$accesskeyid:$secretaccesskey",
                CURLOPT_HEADER =>FALSE,
                CURLOPT_POST => true,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_RETURNTRANSFER =>TRUE,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($request_body),
                CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        $json = json_decode($response);

        $json = json_encode($json, JSON_PRETTY_PRINT);	

        return $json;
    }
    
    public function terminos(){
 
        return view('payonline.visa.partial.terminos');
    }
    
    public function recepcion(Request $request){
        
        $rpta =['nerror' => 1,
             'mensaje' => "No se ha procesado "
            ];
    
        $token_val = 'fcae58bb8b959a24befc1f2e46a267f33153eb7980c1d7d4c9d4301786cb9fc5baf6cab346ee226f037918b431bfa8b6792b';  //token para validar la recepción fijo
        
        if ($request->isMethod('post')) {  //si es metodo post
            if ($request->has('tokens')) { // si viene el campo tokens
                $tokens = $request->tokens; //recibe el parametro
                if($token_val == $tokens){ //compara los tokens
 
                    //otros datos de recepcion
                    $importe   = $request->importe;
                    $id_origen = $request->id_origen;
                    $numorden  = $request->numorden;                   
                    
                   //ejecutar los procesos
                    
                   $rpta =['nerror' => 0,
                    'mensaje' => "Se ha procesado correctamente ccc"
                   ];
                    
                }else{
                    $rpta =['nerror' => 1,
                    'mensaje' => "No se ha procesado   ccc   xxxx"
                   ];
                }
            }
        }   
 
        return  json_encode($rpta); 
    }
    
    public function envioRespuesta($id_aplicacion,$id_origen){
        
        /*$datos['id_aplicacion'] = 1;
        $datos['importe']   = 25.3;
        $datos['id_origen'] = 2;
        $datos['numorden']  = '00000';
         */
       
        try{
        
        $datos['id_aplicacion'] = $id_aplicacion;
        $datos['importe']   = 1.0;
        $datos['id_origen'] = $id_origen;
        $datos['numorden']  = '10015341';
        $datos['id_respuesta']  = 'vvvvv';
        
        
  
        

        $rpta_rpta = PayonlineData::envioRespuesta($datos);
        
        dd($rpta_rpta);

        $q = json_decode($rpta_rpta);
        
        //return $q;
        //dd($q);
        $nerrorrpta    = $q->nerror;

            
        }catch(Exception $e){
            $mensaje=$e->getMessage();
            //dd( $mensaje);
        }
        
        
        //$repuesta_pta  =  $rpta_rpta;

    } 
    
    
}



