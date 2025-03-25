<?php
namespace App\Http\Controllers\Payonline;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Payonline\McData;
use App\Http\Data\Payonline\PayonlineData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use PDO;
use Session;
use WebSocket\Client;

class McController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public function socket(){
        

        $client = new Client("ws://erpsockets-dev.upeu:9003/29184/lamb/");

        $payload['data'] = [array('token'=>'234787234','message'=>'hola desde php from php')];
        $data['stream'] = "lamb";
        $data['payload'] = $payload;
        $data = json_encode($data);
        $client->send($data);
        echo $client->receive();
    }
    public function shopping(Request $request){
        $dataMc = McData::listMc();
        $dataAplicacion = PayonlineData::listAplicacion();
        $dataPayonline = PayonlineData::show(1);
        $dataOperacion = PayonlineData::listOperacion();
        //dd($dataPayonline);
        return view('payonline.mc.shopping',['dataMc'=>$dataMc,'dataAplicacion'=>$dataAplicacion,'dataPayonline'=>$dataPayonline,'dataOperacion'=>$dataOperacion]);
    }
    
    private function hmacsha1($key,$data,$hex=false){
        $blocksize = 64;
        $hashfunc ='sha1';
        if(strlen($key)>$blocksize)
            $key=pack('H*',$hashfunc($key));
        $key = str_pad($key,$blocksize,chr(0x00));
        $ipad= str_repeat(chr(0x36), $blocksize);
        $opad= str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*',$hashfunc(($key^$opad).pack('H*',$hashfunc(($key^$ipad).$data))));
        if ($hex == false){
            return $hmac;
        }else{
            return bin2hex($hmac);
        }
    }
    private function datosMc($id_mc){
        
        $data = McData::showMc($id_mc);
        
        $respuesta =[
            'prod'=>'N',
            'codcomercio'=>'',
            'merchantkey'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'prod'=>$row->prod,
                'codcomercio'=>$row->codcomercio,
                'merchantkey'=>$row->merchantkey
            ];
        }
        
        return $respuesta;
    }
    private function datosMcError($codigo,$tipo){
        
        $data = McData::showMcError($codigo,$tipo);
        
        $respuesta =[
            'cod'=>$codigo,
            'descripcion'=>'',
            'accion1'=>'',
            'accion2'=>'',
            'accion3'=>''
        ];
        
        foreach($data as $row){
            $respuesta =[
                'cod'=>$codigo,
                'descripcion'=>$row->descripcion,
                'accion1'=>$row->accion1,
                'accion2'=>$row->accion2,
                'accion3'=>$row->accion3
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
                
                try{
                
                    $tokens          = $request->tokens;

                    $datospayonline  = $this->datosPayOnline(1);

                    if($tokens == $datospayonline['tokens']){
                        $id_mc        = $request->id_negocio;
                        $datosmc      = $this->datosMc($id_mc);
                        $merchantkey   = $datosmc['merchantkey'];
                          
                        if(strlen($merchantkey)>0 ){
                            $codcomercio     = $datosmc['codcomercio'];
                            $prod            = $datosmc['prod'];
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
                            $importe         = $total ;
                            $monedamc        = 'PEN';
                            if($moneda == '1'){
                                $tc       = PayonlineData::tipoCambio();
                                $importe  = round($total*$tc,2);
                                $monedamc = 'USD';
                            }

                            $respuesta       = PayonlineData::verificapersona($id_persona, $numdoc, $id_aplicacion,$nombre,$paterno,$materno,$sexo,$correo);


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
                                    
                                    $numorden    = McData::ordenMc($id_mc);
                                    
                                    $fechamc   = date('Ymd');
                                    $horamc    = date('His');
                                    $aleatorio = date('YmdHis');
                                    $pais      = 'PER';
                                    
                                    $datomcin[] = $codcomercio;
                                    $datomcin[] = $numorden;
                                    $datomcin[] = $importe;
                                    $datomcin[] = $monedamc;
                                    $datomcin[] = $fechamc;
                                    $datomcin[] = $horamc;
                                    $datomcin[] = $aleatorio;
                                    $datomcin[] = $id_personal;
                                    $datomcin[] = $pais;
                                    $datomcin[] = $merchantkey;
                                    
                                    $cadenafinal = implode("",$datomcin);
                                    $strHash  = urlencode(base64_encode($this->hmacsha1($merchantkey, $cadenafinal)));
                                    
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
                                        'tokens'         => $tokens,
                                        'moneda'         => $monedamc,
                                        'codcomercio'    => $codcomercio,
                                        'numorden'       => $numorden,
                                        'fechamc'        => $fechamc,
                                        'horamc'         => $horamc,
                                        'aleatorio'      => $aleatorio,
                                        'pais'           => $pais
            
                                    ];

                                    Session::put('s_sessiondatos',$datosrecibidos);

                                    return view('payonline.mc.tokens',['strHash'=>$strHash,'prod'=>$prod,'datosrecibidos'=>$datosrecibidos]);
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
                    $mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile();
                }
            }else{
                $nerror = "";
                $mensaje = "No se envía el dato importe ";
            }
        }
        
        return  view('payonline.mc.error',['nerror'=>$nerror,'mensaje'=>$mensaje]);
    }
    
    public function imprimir(Request $request){
        
        $method = $request->method();
        
        $nerror = "";
        $mensaje = "No se envía en método post desde formulario";
        
        if ($request->isMethod('post')) {
            
            $datosrecibidos   = Session::get('s_sessiondatos');
                    
            $id_mc          = $datosrecibidos['id_negocio'];

            $datosmc        = $this->datosMc($id_mc);
            $merchantkey    = $datosmc['merchantkey'];
            $codcomercio    = $datosmc['codcomercio'];
            $prod           = $datosmc['prod'];
            
            $resultado      =  $request->O1;
            
            
            if ($resultado =='A') {
                
                try{
                    $codautorizacion =  $request->O2;
                    $nroreferencia   =  $request->O3;
                    $moneda          =  $request->O8;
                    $importe         =  $request->O9;
                    $nroorden        =  $request->O10;
                    $codrespuesta    =  $request->O13;
                    $tarjeta         =  $request->O15;
                    $idpersonal      =  $request->O18;
                    $pais            =  $request->O19;
                    
                    $puntoweb        = $request->O20;
                    
                    $errorMessage    = $request->O17;
                    
                    $fecha           = $request->O11;
                    $hora            = $request->O12;
                    
                    $hashpuntoweb    = urldecode($puntoweb);

                    $datomcout[] = $resultado;
                    $datomcout[] = $codautorizacion;
                    $datomcout[] = $nroreferencia;
                    $datomcout[] = $moneda;
                    $datomcout[] = $importe;
                    $datomcout[] = $nroorden;
                    $datomcout[] = $codrespuesta;
                    $datomcout[] = $tarjeta;
                    $datomcout[] = $idpersonal;
                    $datomcout[] = $pais;
                    $datomcout[] = $merchantkey;
                    
                    $datosfrm['resultado']       = $resultado;
                    $datosfrm['codautorizacion'] = $codautorizacion;
                    $datosfrm['nroreferencia']   = $nroreferencia;
                    $datosfrm['moneda']          = $moneda;
                    $datosfrm['importe']         = $importe;
                    $datosfrm['nroorden']        = $nroorden;
                    $datosfrm['codrespuesta']    = $codrespuesta;
                    $datosfrm['tarjeta']         = $tarjeta;
                    $datosfrm['idpersonal']      = $idpersonal;
                    $datosfrm['pais']            = $pais;
                    $datosfrm['merchantkey']     = $merchantkey;
                    
                    $jrpta        =   json_decode($datosfrm);
                    
                    $cadenafinal = implode("",$datomcout);
                    
                    $strHash     = base64_encode($this->hmacsha1($merchantkey, $cadenafinal));
                    
                    $id_operacion  = $datosrecibidos['id_operacion'];
                    $id_aplicacion = $datosrecibidos['id_aplicacion'];
                    
                    $ip             = GlobalMethods::ipClient($request);
                    $respuestaService = [];
                    if($hashpuntoweb == $strHash){
                        $mensaje ='0K';
                        $respuestaService = PayonlineData::payUpeu($idpersonal,$nroorden,$importe,$id_operacion,$id_aplicacion, $ip, $datosrecibidos['tokens']);
                        $nerrorservice = $respuestaService['error'];
                        $rpta_serv     = $respuestaService;
                    }else{
                        $nerror = "";
                        $mensaje = "Hash Inválido"; 
                        
                        
                    }
                    
                    $jrpta         = (array)$jrpta;
                    $json_mc     = json_encode($jrpta);


                    $json_servicio = json_encode($respuestaService);

                    $nerrorrpta    = -1;

                    $repuesta_pta  = '';
                    
                    if ($codrespuesta!='O13'){
                        $datosvisaerror = $this->McError($codrespuesta,'O13');

                        if(strlen($datosvisaerror['descripcion'])>0){
                            $mensaje = $datosvisaerror['descripcion'];
                        }else{
                            $mensaje = $errorMessage;
                        }
                    }

                    McData::visaLog($idpersonal,$codrespuesta,$mensaje,$json_mc,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip);

                    $rpta          = PayonlineData::showOperacion($id_operacion,$id_aplicacion);

                    $operacion     = '';

                    if($rpta['nerror']==0){
                        $datos  = $rpta['data'];
                        $operacion = $datos['glosa'];
                    }

                    $respuesta =[
                        'respuesta'=>$codrespuesta,
                        'numorden'=>$nroorden,
                        'codautorizacion'=>$codautorizacion,
                        'fecha'=>$fecha,
                        'hora'=>$hora,
                        'importe'=>$importe,
                        'operacion'=> $operacion,
                        'mensaje'=> $errorMessage
                    ];
                        
                  
                    return  view('payonline.mc.print',['respuesta'=>$respuesta, 'datosrecibidos'=>$datosrecibidos,'rpta_serv'=>$rpta_serv]);
                    
                }catch(Exception $e){   
                    $nerror = "";
                    $mensaje = $e->getMessage();
                    //$mensaje = '<b>Error Interno</b><br>Code: '.$e->getCode().' Line: '.$e->getLine().' File: '.$e->getFile();
                }
            }else{
                $nerror = $resultado;
                $mensaje = "Denegado";
                if($resultado == 'E'){
                    $mensaje = "Error";
                }
            }
        }
        return  view('payonline.mc.error',['nerror'=>$nerror,'mensaje'=>$mensaje]);
    }
    
    public function terminos(){
 
        return view('payonline.mc.partial.terminos');
    }
}

