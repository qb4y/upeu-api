<?php
namespace App\Http\Controllers\Payonline;
use App\Http\Data\GlobalMethods;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Payonline\VisanetData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Session;


class VisanetController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    private function datosPayOnline($id_payonline){
        
        $data = VisanetData::showPayonline($id_payonline);
        
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
        
        $data = VisanetData::showVisa($id_visa);
        
        $respuesta =[
            'prod'=>'N',
            'merchantid'=>'',
            'accesskeyid'=>'',
            'secretaccesskey'=>'',
            'user'=>'',
            'pwd'=>'',
            'correo'=>'',
            'dni'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'prod'=>$row->prod,
                'merchantid'=>$row->merchantid,
                'accesskeyid'=>$row->accesskey,
                'secretaccesskey'=>$row->secretkey,
                'user'=>$row->user,
                'pwd'=>$row->pwd,
                'correo'=>$row->correo,
                'dni'=>$row->dni
            ];
        }
        
        return $respuesta;
    }
    public function shopping(Request $request){
        $dataVisa = VisanetData::listVisa();
        $dataAplicacion = VisanetData::listAplicacion();
        $dataPayonline = VisanetData::showpayonline(4);
        $dataOperacion = VisanetData::listTransaccion();
        $dataventa = VisanetData::listTipoventa();
        $gruta       = VisanetData::ruta_url(url('/'));
        return view('visanet.show',[
            'dataVisa'=>$dataVisa,
            'dataAplicacion'=>$dataAplicacion,
            'dataPayonline'=>$dataPayonline,
            'dataOperacion'=>$dataOperacion,
            'dataventa'=>$dataventa,
            'gruta'=>$gruta
        ]);
    }
    
    private function generateToken($prod,$visauser,$visa_pwd) {
        switch ($prod) {
            case 'S':
                $visa_url_security = "https://apiprod.vnforapps.com/api.security/v1/security";
                break;
            case 'N':
                $visa_url_security = "https://apitestenv.vnforapps.com/api.security/v1/security";
                break;
      
        }
      
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $visa_url_security,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            'Authorization: '.'Basic '.base64_encode($visauser.":".$visa_pwd)
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    
    private function postRequest($url, $postData, $token) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$token,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => $postData
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    private function generateSesion($amount, $token, $prod,$merchantId,$correo,$dni) {
        switch ($prod) {
            case 'S':
                $url = "https://apiprod.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/".$merchantId;
                break;
            case 'N':
                $url = "https://apitestenv.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/".$merchantId;
                break;
      
        }
        $session = array(
            'amount' => $amount,
            'antifraud' => array(
                'clientIp' => $_SERVER['REMOTE_ADDR'],
                'merchantDefineData' => array(
                    'MDD4' => $correo,
                    'MDD33' => "DNI",
                    'MDD34' => $dni
                ),
            ),
            'channel' => 'web',
        );
        $json = json_encode($session);

        $response = json_decode($this->postRequest($url, $json, $token));
        return $response->sessionKey;
    }
    public function tokens(Request $request){
        $method = $request->method();

        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";

        $gruta       = VisanetData::ruta_url(url('/'));
        
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

                                $merchantid      = $datosvisa['merchantid'];

                                if(strlen($merchantid)>0 ){
                                    
                                    
                                    $total               = $request->importe;
                                    $importe             = $total ;
                                    
   
                                    $respuesta        = VisanetData::verificapersona($request);


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

                                            'tokens'         => $tokens,
                                            'urlexpira'      => $request->urlexpira
                       
                                        ];

                                        Session::put('s_sessiondatos',$datosrecibidos);

                                        //$sessiontoken    = $this->getGUID();

                                        //Session::put('s_sessiontoken', $sessiontoken);

                                        $user    = $datosvisa['user'];
                                        $pwd     = $datosvisa['pwd'];
                                        $prod    = $datosvisa['prod'];
                                        $correo  = $datosvisa['correo'];
                                        $dni     = $datosvisa['dni'];

                                        $token   = $this->generateToken($prod,$user,$pwd);

                                        //$sessiontoken  = $this->generateSesion($importe , $token,$prod,$merchantid,$correo,$dni);

                                        $sessiontoken  = $this->generateSesion($importe , $token,$prod,$merchantid,$data["email_visa"],$data["num_documento"]);

                                        Session::put('s_sessiontoken', $sessiontoken);

                                        //$sessionKey  = $this->createToken($importe,$prod,$merchantid,$accesskeyid,$secretaccesskey,$sessiontoken);

                                        $numorden    = VisanetData::ordenVisa($id_visa);

                                        

                                        return view('visanet.tokens',['sessionToken'=>$sessiontoken,'merchantid'=>$merchantid,
                                            'importe'=>$importe,'numorden'=>$numorden,'prod'=>$prod,'datosrecibidos'=>$datosrecibidos,'gruta'=>$gruta]);
                                      
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
        
        return  view('visanet.error',['gruta'=>$gruta,'nerror'=>$nerror,'mensaje'=>$mensaje]);
    }
    public function expirado(){
        $gruta       = VisanetData::ruta_url(url('/'));
        return view('visanet.expirado',['gruta'=>$gruta]);
    }
    public function imprimir(Request $request){
        $method = $request->method();
        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";

        $datosrecibidos   = Session::get('s_sessiondatos');

        $ip  = GlobalMethods::ipClient($request);

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
        $rpta_serv='';

        $gruta       = VisanetData::ruta_url(url('/'));

        if ($request->isMethod('post')) {
            if ($request->has('transactionToken')) {
                
                try{
                    $transactionToken = $request->transactionToken;
                    $amount           = $request->amount;
                    $purchaseNumber   = $request->purchaseNumber;
                    $id_visa          = $datosrecibidos['id_negocio'];
                    $importe          = $datosrecibidos['importe'];
                    $operacion        = $datosrecibidos['concepto'];

                    $datosvisa        = $this->datosVisa($id_visa);

                    $merchantid       = $datosvisa['merchantid'];
                    $user             = $datosvisa['user'];
                    $pwd              = $datosvisa['pwd'];
                    $prod             = $datosvisa['prod'];
                    $id_aplicacion  = $datosrecibidos['id_aplicacion'];

                    $token = $this->generateToken($prod,$user,$pwd);

                    $data = $this->generateAuthorization($amount, $purchaseNumber, $transactionToken, $token,$prod,$merchantid);

                    $PAN ='';
                    $id_resp='';

                    $RESPUESTA='';
                    $FEC='';

                    if (isset($data->dataMap)) {
                        $errorCode       = $data->dataMap->ACTION_CODE;
                        $RESPUESTA       = $errorCode;
                        $errorMessage    = $data->dataMap->ACTION_DESCRIPTION;
                        $IMP_AUTORIZADO  = $data->order->amount;
                        $tarjeta         = $data->dataMap->BRAND;
                        $PAN             = $data->dataMap->CARD." (".$data->dataMap->BRAND.")";
                        $FEC            = preg_split('//', $data->dataMap->TRANSACTION_DATE, -1, PREG_SPLIT_NO_EMPTY);

                        $respuestaService = [];
                        $nerrorservice   = -1;

                        $id_persona     = $datosrecibidos['id_persona'];
                        
                        $id_operacion   = $datosrecibidos['id_operacion'];
                        

                        $NUMORDEN       =  $purchaseNumber;//$data->dataMap->TRACE_NUMBER;

                        $rpta_serv = [];
                        

                        if ($errorCode == "000" and $IMP_AUTORIZADO>0) {
                            $mensaje ='0K';
                            $cod_tarjeta  = '4';
                            switch ($tarjeta ) {
                                case 'amex':
                                    $cod_tarjeta='34';
                                    break;
                                case 'mastercard':
                                    $cod_tarjeta='5';
                                    break;
                                case 'dinersclub':
                                    $cod_tarjeta='36';
                                    break;
                            }

                            $respuestaService = VisanetData::payUpeu($id_persona,$NUMORDEN,$datosrecibidos,$cod_tarjeta,$id_aplicacion, $ip, $IMP_AUTORIZADO);

                            $nerrorservice    = $respuestaService['error'];
                            $rpta_serv        = $respuestaService;
                            $id_resp          = $respuestaService['id'];

                        }else{
                            $datosvisaerror = $this->datosVisaError($errorCode);
                            
                            if(strlen($datosvisaerror['descliente'])>0){
                                $mensaje = $datosvisaerror['descliente'];
                            }else{
                                $mensaje = $errorMessage.': '.$errorCode;
                            }
                        }

                        $jrpta     = ['order'=>(array)$data->order,'dataMap'=>(array)$data->dataMap];
                        $json_visa = json_encode($jrpta);
                        
                        
                        $json_servicio = json_encode($respuestaService);
                        
                        $nerrorrpta    = -1;

                        $repuesta_pta  =  '-';
                        
                        if ($errorCode == "000" and $IMP_AUTORIZADO>0) {
                            $datos['id_aplicacion'] = $id_aplicacion;
                            $datos['importe']       = $IMP_AUTORIZADO;
                            $datos['id_origen']     = $datosrecibidos['id_origen'];
                            $datos['numorden']      = $NUMORDEN;
                            $datos['id_respuesta']  = $id_resp;
                             
                            $rpta_rpta = VisanetData::envioRespuesta($datos);
                            
                            $q = json_decode($rpta_rpta);
                            
                            if(isset($q->nerror)){
                                $nerrorrpta    =  $q->nerror;
                            }else{
                                $nerrorrpta    =  -2;
                                $rpta_rpta     =  'Error de página de la respuesta';
                            }

                            $repuesta_pta  =  $rpta_rpta;
                        }
                        VisanetData::visaLog($id_persona,$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$IMP_AUTORIZADO,$id_visa,$id_aplicacion,$id_operacion);


                    }else{
                        $errorMessage   = $data->data->ACTION_DESCRIPTION;
                        $PAN            = $data->data->CARD." (".$data->data->BRAND.")";
                        $NUMORDEN       = $data->data->TRACE_NUMBER;
                        $IMP_AUTORIZADO = $data->data->AMOUNT;
                        $RESPUESTA      = $data->data->ACTION_CODE;
                        $FEC            = preg_split('//', $data->data->TRANSACTION_DATE, -1, PREG_SPLIT_NO_EMPTY);
                        $nerror = "";
                        $mensaje = "No se ha generado  Transacción Token VISA";
                    }
                    $FECHAYHORA_TX= $FEC[4].$FEC[5]."/".$FEC[2].$FEC[3]."/".$FEC[0].$FEC[1]." ".$FEC[6].$FEC[7].":".$FEC[8].$FEC[9].":".$FEC[10].$FEC[11];
                    
                    $respuesta =[
                        'respuesta'=>$RESPUESTA,
                        'numorden'=>$NUMORDEN,
                        'dsc_cod_accion'=>$errorMessage,
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

                    return  view('visanet.print',['respuesta'=>$respuesta, 'datosrecibidos'=>$datosrecibidos,'rpta_serv'=>$rpta_serv,'gruta'=>$gruta]);

                }catch(Exception $e){ 
                    $nerror = "";
                    // $mensaje = '<b>Error Interno</b><br>Página: '.$e->getFile(). '<br>Linea: '.$e->getLine().' <br>Msg: '.$e->getMessage();
                    $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile().'MSG: '.$e->getMessage();
                } 
            }else{
                $nerror = "";
                $mensaje = "No se ha generado  Transacción Token VISA";
            }
        }
        VisanetData::visaLog($datosrecibidos['id_persona'],$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion);
        return  view('visanet.error',['nerror'=>$nerror,'mensaje'=>$mensaje,'gruta'=>$gruta]);
    }
    
    private function generateAuthorization($amount, $purchaseNumber, $transactionToken, $token,$prod,$merchantid) {
        switch ($prod) {
            case 'S':
                $url = "https://apiprod.vnforapps.com/api.authorization/v3/authorization/ecommerce/".$merchantid;
                break;
            case 'N':
                $url = "https://apitestenv.vnforapps.com/api.authorization/v3/authorization/ecommerce/".$merchantid;
                break;
      
        }

        $data = array(
            'antifraud' => null,
            'captureType' => 'manual',
            'channel' => 'web',
            'countable' => true,
            'order' => array(
                'amount' => $amount,
                'currency' => 'PEN',
                'purchaseNumber' => $purchaseNumber,
                'tokenId' => $transactionToken
            ),
            'recurrence' => null,
            'sponsored' => null
        );
        $json = json_encode($data);
        $session = json_decode($this->postRequest($url, $json, $token));
        return $session;
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
 
        return view('visanet.partial.terminos');
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
