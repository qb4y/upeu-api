<?php
namespace App\Http\Controllers\Payonline;
use App\Http\Data\GlobalMethods;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Payonline\VisapaymentData;
use App\Http\Data\Payonline\VisanetData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Session;


class VisapaymentController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public function shopping(Request $request){
        $dataVisa = VisapaymentData::listVisa();
        $dataAplicacion = VisapaymentData::listAplicacion();
        $dataPayonline = VisapaymentData::showpayonline(2);
        $dataOperacion = VisapaymentData::listTransaccion();
        $dataventa = VisapaymentData::listTipoventa();
        $gruta       = VisanetData::ruta_url(url('/'));
        return view('visapayment.shopping',[
            'dataVisa'=>$dataVisa,
            'dataAplicacion'=>$dataAplicacion,
            'dataPayonline'=>$dataPayonline,
            'dataOperacion'=>$dataOperacion,
            'dataventa'=>$dataventa,
            'gruta'=>$gruta
        ]);
    }
    private function datosPayOnline($id_payonline){
        
        $data = VisapaymentData::showPayonline($id_payonline);
        
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
    private function datosVisa($id_visa){
        
        $data = VisapaymentData::showVisa($id_visa);
        
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
    private function createToken($importe,$prod,$merchantId,$accessKey,$secretKey,$uuid){
            switch ($prod) {
                case 'S':
                    $url = "https://apice.vnforapps.com/api.ecommerce/api/v1/ecommerce/token/".$merchantId;
                    break;
                case 'N':
                    $url = "https://devapice.vnforapps.com/api.ecommerce/api/v1/ecommerce/token/".$merchantId;
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
    public function tokens(Request $request){
        $method = $request->method();

        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";
        
        if ($request->isMethod('post')) {
            if ($request->has('importe')) {
                
                $monto    = $request->importe;
                
                if (is_numeric($monto)){
                    if ( $monto > 0 ) {
                        try{

                            $tokens          = $request->tokens;

                            $datospayonline  = $this->datosPayOnline(2);

                            if($tokens == $datospayonline['tokens']){
                                $id_visa         = $request->id_negocio;
                                $datosvisa       = $this->datosVisa($id_visa);
                                $sessiontoken    = $this->getGUID();
                                Session::put('s_sessiontoken', $sessiontoken);
                                $merchantid      = $datosvisa['merchantid'];

                                if(strlen($merchantid)>0 ){
                                    $accesskeyid         = $datosvisa['accesskeyid'];
                                    $secretaccesskey     = $datosvisa['secretaccesskey'];
                                    $prod                = $datosvisa['prod'];
                                    $total               = $request->importe;
                                    $importe             = $total ;
                                    
   
                                    $respuesta        = VisapaymentData::verificapersona($request);


                                    if($respuesta['nerror']==0){
         
                                        $data            = $respuesta['data'];

                                        $id_tipoventa = '';
                                        if ($request->has('id_tipoventa')) {
                                            $id_tipoventa = $request->id_tipoventa;
                                        }

                                        $datosrecibidos = [
                                            'tipodoc'        => $data["tipodocumento"],
                                            'numdoc'         => $data["num_documento"],
                                            'id_persona'     => $data["id_persona"],
                                            'id_origen'      => $request->id_origen,
                                            'id_negocio'     => $request->id_negocio,
                                            'id_aplicacion'  => $request->id_aplicacion,
                                            'id_entidad'     => $request->id_entidad,
                                            'id_depto'       => $request->id_depto,
                                            'concepto'       => $data["glosa"],
                                            'id_operacion'   => $request->id_operacion, // $request->cod_transaccion(cambiado)
                                            'importe'        => $importe,
                                            'importe_dol'    => 0,
                                            'nombres'        => $data["nombres"],
                                            'apellidos'      => $data["apellidos"],
                                            'codigo'         => $data["codigo"],
                                            'eap'            => $data["eap"],
                                            'nombres_visa'   => $data["nombres_visa"],
                                            'apellidos_visa' => $data["apellidos_visa"],
                                            'email_visa'     => $data["email_visa"],
                                            'id_dinamica'    => $data["id_dinamica"],

                                            'ruc'            => $data["ruc"],
                                            'razonsocial'    => $data["razonsocial"],
                                            'id_cliente'     => $data["id_cliente"],
                                            'id_comprobante' => $data["id_comprobante"],
                                            'id_tipoventa'   => $id_tipoventa,

                                            'tokens'         => $tokens
                       
                                        ];

                                        Session::put('s_sessiondatosvisa',$datosrecibidos);

                                        $sessionKey  = $this->createToken($importe,$prod,$merchantid,$accesskeyid,$secretaccesskey,$sessiontoken);

                                        $numorden    = VisapaymentData::ordenVisa($id_visa);

                                        return view('visapayment.tokens',['sessionToken'=>$sessiontoken,'merchantid'=>$merchantid,
                                            'importe'=>$importe,'numorden'=>$numorden,'prod'=>$prod,'datosrecibidos'=>$datosrecibidos]);
                                      
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
            }else{
                $nerror = "";
                $mensaje = "No se envía el dato importe ";
            }
        }
        
        return  view('visapayment.error',['nerror'=>$nerror,'mensaje'=>$mensaje]);
    }
    public function imprimir(Request $request){
        
        $method = $request->method();
        
        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";
        $datosrecibidos   = Session::get('s_sessiondatosvisa');
        
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
        
        $ip  = GlobalMethods::ipClient($request);
        
        if ($request->isMethod('post')) {
            if ($request->has('transactionToken')) {
                
                try{
                                        
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
                        
                        $id_persona     = $datosrecibidos['id_persona'];
                        
                        $id_operacion  = $datosrecibidos['id_operacion'];
                        $id_aplicacion = $datosrecibidos['id_aplicacion'];
                            
                        $rpta_serv = [];
                        $id_resp='';
                        if ($errorCode == 0 and $RESPUESTA == '1' and $IMP_AUTORIZADO > 0) {
                            $mensaje ='0K';
                            
                            $cod_tarjeta = substr($PAN, 0, 1);
                       
                            if($cod_tarjeta=='3'){
                                if((substr($PAN, 0, 2)=='34') or (substr($PAN, 0, 2)=='37')){
                                    $cod_tarjeta = '34'; //substr($PAN, 0, 2);
                                }else{
                                    $cod_tarjeta = '36';// 36,38,39,30
                                }
                                
                            }
//                          $respuestaService = VisapaymentData::payUpeu($id_persona,$NUMORDEN,$datosrecibidos['id_entidad'],$datosrecibidos['id_depto'],$datosrecibidos['importe'],$datosrecibidos['id_dinamica'],$cod_tarjeta,$id_aplicacion, $ip, $datosrecibidos['tokens'],$datosrecibidos['id_origen']);  
                            $respuestaService = VisapaymentData::payUpeu($id_persona,$NUMORDEN,$datosrecibidos,$cod_tarjeta,$id_aplicacion, $ip,$IMP_AUTORIZADO);
//                            dd($respuestaService);
                            $nerrorservice    = $respuestaService['error'];
                            $rpta_serv        = $respuestaService;
                            $id_resp          = $respuestaService['id'];
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
                             
                            $rpta_rpta = VisapaymentData::envioRespuesta($datos);
                            
                            $q = json_decode($rpta_rpta);
                            
                            if(isset($q->nerror)){
                                $nerrorrpta    =  $q->nerror;
                            }else{
                                $nerrorrpta    =  -2;
                                $rpta_rpta     =  'Error de página de la respuesta';
                            }
                            

                            $repuesta_pta  =  $rpta_rpta;
                        }
                        VisapaymentData::visaLog($id_persona,$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$IMP_AUTORIZADO,$id_visa,$id_aplicacion,$id_operacion);
                        
                        
                        
                        $operacion = $datosrecibidos['concepto'];

                        
                        
                        $respuesta =[
                            'respuesta'=>$RESPUESTA,
                            'numorden'=>$NUMORDEN,
                            'dsc_cod_accion'=>$DSC_COD_ACCION,
                            'pan'=>$PAN,
                            'fechahora'=>$FECHAYHORA_TX,
                            'importe'=>$IMP_AUTORIZADO,
                            'operacion'=> $operacion,
                            'id_origen'=> $datosrecibidos['id_origen'],
                            'id_respuesta'=>$id_resp
                        ];

                        $datosdocumento=[
                            'id_respuesta'=>$id_resp,
                            'id_entidad'=>$datosrecibidos['id_entidad'],
                            'id_depto'=>$datosrecibidos['id_depto']
                        ];

                        Session::put('s_sessiondatosdocumento',$datosdocumento);
                        
                        return  view('visapayment.print',['respuesta'=>$respuesta, 'datosrecibidos'=>$datosrecibidos,'rpta_serv'=>$rpta_serv]);
                    }else{
                        $nerror = "";
                        $mensaje = "No hay respuesta de servicio VISA"; 
                    }
                }catch(Exception $e){   
                    $nerror = "";
                    //$mensaje = $e->getMessage();
                    // $mensaje = '<b>Error Interno</b><br>line: '.$e->getLine().' Msg: '.$e->getMessage();
                    $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile().'MSG: '.$e->getMessage();
                }
            }else{
                $nerror = "";
                $mensaje = "No se ha generado  Transacción Token VISA";
            }
        }
        VisapaymentData::visaLog($datosrecibidos['id_persona'],$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion);
        return  view('visapayment.error',['nerror'=>$nerror,'mensaje'=>$mensaje]);
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
    private function datosVisaError($id_visa_error){
        
        $data = VisapaymentData::showVisaError($id_visa_error);
        
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
    public function terminos(){
 
        return view('visapayment.partial.terminos');
    }

    public function obtenerDocumento(){

        $datosrecibidos   = Session::get('s_sessiondatosdocumento');

        $data = VisapaymentData::showventa($datosrecibidos);


        $tipo='';
        $file='';
        $legal=$datosrecibidos['id_respuesta'];
        $ruc='';
        $usuario='Wilder Marlo';
        if(count($data)>0){
            foreach($data as $row){
                $tipo=$row->id_comprobante;
                $ruc=$row->id_ruc;
            }
            $file=$ruc.'-'.$tipo.'-'.$legal.'.txt';
        }else{
            dd('No hay datos para descargar');
        }
 


        $url= 'http://efac.upeu.edu.pe:8080/rest/api/v1/document/pdf';
      
                  
        $params ='documentTypeCode='.$tipo.'&fileName='.$file.'&legalNumber='.$legal.'&ruc='.$ruc.'&usuario='.$usuario;

        /*$params ='documentTypeCode=03&fileName=20138122256-03-B102-00060754.txt&legalNumber=B102-00060754&ruc=20138122256&usuario=Wilder+Marlo';*/
  
        $url.='?'.$params;

      
        $cliente = curl_init();

        curl_setopt($cliente, CURLOPT_URL, $url);
        curl_setopt($cliente, CURLOPT_HEADER, false);
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);

        $respuesta = curl_exec($cliente);

        $err = curl_error($cliente);

        curl_close($cliente);


        $file=$legal.'.pdf';
        if(strlen($err)==0){
            $content_types='application/pdf';
                    return response($respuesta,200)
                    ->header('Content-Type',$content_types)
                    ->header('Content-disposition','attachment; filename="'.$file.'"');
        }else{
            dd($err);
        }
        
    }
    public function envioRespuesta($id_aplicacion,$id_origen){
        
       
        try{
        
        $datos['id_aplicacion'] = $id_aplicacion;
        $datos['importe']   = 1.0;
        $datos['id_origen'] = $id_origen;
        $datos['numorden']  = '10015341';
        $datos['id_respuesta']  = 'vvvvv';
        
        
  
        

        $rpta_rpta = VisapaymentData::envioRespuesta($datos);
        
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
