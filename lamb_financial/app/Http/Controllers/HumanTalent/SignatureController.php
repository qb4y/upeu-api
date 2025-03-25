<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\SignatureData;
use App\Http\Data\APSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;
use App\qrcode;
use Mail;
use Response;
use Session;    

class SignatureController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listCertificate(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = SignatureData::listCertificatenew();  
                //$data = SignatureData::listCertificate(); 
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function editCertificate($id_certificado,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                  
                    $id_persona = $request->id_persona;
                    $id_depto = $request->id_depto;
                    $firma  = $request->file('firma');
                    $archivofirma = "";
                    
                    if($firma){
                        $ext    = $firma->getClientOriginalExtension();

                        $archivofirma = $id_depto.$id_persona.".".$ext;
                        $path = 'img';

                        if (file_exists($path.'/'.$archivofirma)) {

                            unlink($path.'/'.$archivofirma);
                        }

                        $firma->move($path,$archivofirma);

                    }
                                
                    
                   
                    $id_entidad = $request->id_entidad;
                    
                    $estado = $request->estado ;

 
                    $ok = SignatureData::editCertificate($id_certificado,$id_persona,$id_entidad,$id_depto,$archivofirma,$estado);

                    if($ok=="OK"){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $ok;
                        $jResponse['data'] = [];
                        $code = "202";
                    }

            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addCertificate(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if($valida=='SI'){
            $jResponse=[];
            try{
                  
                    
                    $firma  = $request->file('firma');
                    $id_persona = $request->id_persona;
                    $id_depto = $request->id_depto;
                    $archivofirma='';
                    if($firma){
                        $ext    = $firma->getClientOriginalExtension();

                        $archivofirma = $id_depto.$id_persona.".".$ext;
                        $path = 'img';

                        if (file_exists($path.'/'.$archivofirma)) {

                            unlink($path.'/'.$archivofirma);
                        }

                        $firma->move($path,$archivofirma);

                    }
                    


                    $descripcion     = '';

                    $desde           = '';
                    $hasta           = '';

                    $ubicacion       = '';
                    
                    $nombre_archivo = '';
                    
                    $archivo = '';
                    
                    
                    $id_entidad = $request->id_entidad;
                    $id_depto = $request->id_depto;
                    $clave='';

                                       
                    $ok = SignatureData::addCertificatenew($id_persona,$id_entidad,$id_depto,$archivofirma);

                    if($ok=="OK"){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $ok;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
    
                
                
                
                
                
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    /*
    public function addCertificate(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if($valida=='SI'){
            $jResponse=[];
            try{
                $file = $request->file('file');

                $extencion       = $file->getClientOriginalExtension();
                $nombre_archivo  = $file->getClientOriginalName();
                $archivo         = $file->getPathname();
                $archivo         = file_get_contents($archivo);
                
                $clave           = $request->clave;
                $certificado     = array();
                if (openssl_pkcs12_read($archivo, $certificado, $clave)) {
  
                    $data = (object) openssl_x509_parse($certificado['cert']); 
                    
            
                    $finicio    = $data->validFrom;
                    $ffinal    = $data->validTo;

                    $datos     = (object)$data->subject;

                    $distrito  = 'Lurigancho Chosica';
                    if(isset($datos->L)){
                        $distrito  = $datos->L;
                    }

                    $ciudad    = $datos->ST;
                    $dni       = $datos->serialNumber;

                    $adatos = explode(":", $dni);

                    if(count($adatos)>1){
                        $dni = $adatos[1];
                    }


                    $firma  = $request->file('firma');
                    $ext    = $firma->getClientOriginalExtension();

                    $archivofirma = $dni.".".$ext;
                    $path = 'img';
                    
                    if (file_exists($path.'/'.$archivofirma)) {
                
                        unlink($path.'/'.$archivofirma);
                    }
                    
                    $firma->move($path,$archivofirma);


                    $descripcion     = $request->descripcion;

                    $desde           = '20'.substr($finicio,0,2).'/'.substr($finicio,2,2).'/'.substr($finicio,4,2);  //$request->desde;
                    $hasta           = '20'.substr($ffinal,0,2).'/'.substr($ffinal,2,2).'/'.substr($ffinal,4,2); //$request->hasta;

                    $ubicacion       = $ciudad.' - '.$distrito;

                    $ok = SignatureData::addCertificate($descripcion,$nombre_archivo,$archivo,$dni,$desde,$hasta,$clave,$archivofirma,$ubicacion);

                    if($ok=="OK"){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $ok;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
    
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No se puede leer el almacén de certificados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
                
                
                
                
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }*/
    
    public  function deleteCertificate($id_certificado){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                
                
                SignatureData::deleteCertificate($id_certificado);
                
                                           
                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";                    
                $jResponse['data'] = [];
                $code = "200";                  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
     public  function addCertificateDepto($id_certificado){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params         = json_decode(file_get_contents("php://input"));        
                //$id_certificado = $params->id_certificado;
                $id_entidad     = $params->id_entidad;
                $id_depto       = $params->id_depto;
                
                $ret=SignatureData::addCertificateDepto($id_certificado,$id_entidad,$id_depto);    
                
                if($ret==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Departamento ya esta asignado';
                    $jResponse['data'] = [];
                    $code = "202"; 
                }
                
             
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function deleteCertificateDepto($id_certificado,$id_entidad,$id_depto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
       
        if($valida=='SI'){
            $jResponse=[];            
            try{
                SignatureData::deleteCertificateDepto($id_certificado,$id_entidad,$id_depto);
                      
                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";                    
                $jResponse['data'] = [];
                $code = "200";                  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function validarCertificado($id_certificado){
        
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
       
        if($valida=='SI'){
            
        
            $jResponse['success'] = false;
            $jResponse['message'] = 'No se puede validar';                   
            $jResponse['data'] = [];
            $jResponse['nerror'] = 1;
            $code = "202";
            try{
                //$id_certificado= $request->query('id_certificado');
                $rpta = SignatureData::validarCertificado($id_certificado);

                if($rpta["nerror"]==0  or $rpta["nerror"]==2){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta["mensaje"];                    
                    $jResponse['data'] = [];
                    $jResponse['nerror'] = $rpta["nerror"];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["mensaje"];                    
                    $jResponse['data'] = [];
                     $jResponse['nerror'] = 1;
                    $code = "202";
                }

            }catch(Exception $e){
                
                $jResponse['success'] = false;
                $jResponse['message'] = 'No se puede validar: '.$e->getMessage();                   
                $jResponse['data'] = [];
                 $jResponse['nerror'] = 1;
                $code = "202";

            }
            
        }
        return response()->json($jResponse,$code);
    }
    public function validarview(Request $request){
        return view('gth.view');
    }
    public function validarDocumento(Request $request){
        
        $tokens  = $request->tokens; 
        
        $tcapcha  = $request->tcapcha; 
        
        $capcha    = Session::get('snumcapcha','0');
        
        if($tcapcha!=$capcha){
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'Capcha Incorrecto';
            
            return json_encode($rpta);
        }
       
        if($tokens=='$2y$10$jfeV4ewDnkHjilscOvy5h.sAYBwEzfJgFbnl3Asd0FvUcTOs6EZxu'){
            
            $rpta["nerror"]=1;
            $rpta["mensaje"]='No se puede validar';
            try{
                
                $file = $request->file('file');
                
                if(isset($file)){

                    $archivo         = $file->getPathname();
                    
                    $archivo         = file_get_contents($archivo);

                    $id_certificado  = $request->docinterno;
                    
                    if($id_certificado==""){
                        $id_certificado=0;
                    }
                
                    if (is_numeric($id_certificado)){
                        
                        if($id_certificado>0){
                            
                            $dataFirma = SignatureData::showCertificate($id_certificado);
                            
                            if(count($dataFirma)>0){
                                $doc  = base64_encode($archivo);

                                $rpta = SignatureData::validarDocumento($doc,$id_certificado);
                            }else{
                                $rpta["nerror"] = 1;
                                $rpta["mensaje"] = 'Doc Interno incorrecto'; 
                            }
                        }else{
                            $rpta["nerror"] = 1;
                            $rpta["mensaje"] = 'Doc Interno debe ser mayo que cero(0)';  
                        }
                    }else{
                       $rpta["nerror"] = 1;
                        $rpta["mensaje"] = 'Doc Interno debe ser numerico';  
                    }
                }else{
                    $rpta["nerror"] = 1;
                    $rpta["mensaje"] = 'Falta adjuntar archivo';
                }

            }catch(Exception $e){
                $rpta["nerror"] = 1;
                $rpta["mensaje"] = 'No se puede validar: '.$e->getMessage();

            }
            
        }else{
            $rpta["nerror"] = 1;
            $rpta["mensaje"] = 'Acceso denegado';
        }
        return json_encode($rpta);
    }
    public function leer(Request $request){

       $id_certificado = $request->id_certificado;
       $dataFirma=SignatureData::showCertificate($id_certificado);
 
       $clave="";
       $file="";

       
       $ret["nerror"]=1;
       $ret["msgerror"]='No hay certificado';

       foreach($dataFirma as $row){
           $file      = $row->archivo;
           $clave     = SignatureData::desencriptar($row->clave,$row->numserie);
       }
       $data = array();
       if($file==""){
           $ret["nerror"]=1;
           $ret["msgerror"]='No existe certificado';
           $ret["data"]=$data; 
       }else{
            $certificado = array();

            if (openssl_pkcs12_read($file, $certificado, $clave)) {
                //dd($certificado['cert']);pkey
                $data =  openssl_x509_parse($certificado['cert'],0);
                dd($certificado);
                $ret["nerror"]=0;
                $ret["msgerror"]='Datos del certificado';
                $ret["data"]=$data;
            }else{
                $ret["nerror"]=1;
                $ret["msgerror"]='No se puede leer certificados';
                $ret["data"]=$data;
            }
       }
       //return json_encode($ret);
       return view('gth.partial.certificado',['ret'=>$ret]);
        
    }
    
    public function capcha(Request $request){
        $num = $request->num;
        $ranStr=substr( sha1( microtime() ),0,6);
        Session::put('snumcapcha',$ranStr);

        $response = function() use($ranStr,$num) {
            ob_get_clean();
            $im = imagecreatefromjpeg( "img/capchas.jpg" );
            $text_color = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 10, 10,  $ranStr, $text_color);
            imagejpeg($im);
        };
    
        return response()->stream($response, 200, array('Content-Type' => 'image/jpeg'));

    }
    
    public function capchaajax(Request $request){
        $num = $request->num;
        $ranStr=substr( sha1( microtime() ),0,6);
        Session::put('snumcapcha',$ranStr);

        $response = function() use($ranStr,$num) {
            ob_get_clean();
            $im = imagecreatefromjpeg( "img/capchas.jpg" );
            $text_color = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 10, 10,  $ranStr, $text_color);
            imagejpeg($im);
            
        };
        //return $response;
        return response()->stream($response, 200, array('Content-Type' => 'image/jpeg'));

    }
    public function vercertificado(Request $request){
       $id_certificado = $request->id_certificado;
       $dataFirma=SignatureData::showCertificate($id_certificado);
 
       $clave="";
       $file="";

       
       $ret["nerror"]=1;
       $ret["msgerror"]='No hay certificado';

       foreach($dataFirma as $row){
           $file      = $row->archivo;
           $clave     = SignatureData::desencriptar($row->clave,$row->numserie);
       }
       $data = array();
       if($file==""){
           $ret["nerror"]=1;
           $ret["msgerror"]='No existe certificado';
           $ret["data"]=$data;
       }else{
            $certificado = array();

            if (openssl_pkcs12_read($file, $certificado, $clave)) {
                //dd($certificado['cert']);
                $data =  openssl_x509_parse($certificado['cert'],0); 
                $ret["nerror"]=0;
                $ret["msgerror"]='Datos del certificado';
                $ret["data"]=$data;
            }else{
                $ret["nerror"]=1;
                $ret["msgerror"]='No se puede leer certificados';
                $ret["data"]=$data;
            }
       }
       //return json_encode($ret);
       return view('gth.partial.certificado',['ret'=>$ret]);
    }
    public function vercrl(Request $request){
        
            $rpta["nerror"]=1;
            $rpta["mensaje"]='No se puede validar';
            try{
                
                
                   $rpta = SignatureData::vercrl();
                            

            }catch(Exception $e){
                $rpta["nerror"] = 1;
                $rpta["mensaje"] = 'No se puede validar: '.$e->getMessage();

            }
            
       
        return json_encode($rpta);
    }
    
    public function certificadoactivos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad = $request->id_entidad;
                $id_depto_padre = $request->id_depto_padre;
                $data = SignatureData::certificadoactivos($id_entidad,$id_depto_padre);                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No esta asignado certificado para firmar ";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function personacertificado(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad = $request->id_entidad;
                $id_persona = $request->id_persona;
                $id_anho = $request->id_anho;
                $id_mes = $request->id_mes;
                $rpt = SignatureData::personacertificado($id_entidad,$id_persona,$id_anho,$id_mes);                                
                if($rpt['nerror']==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $rpt['persona'],'certificado' => $rpt['certificado']];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpt['mensaje'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function showCertificate($id_certificado){
        
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
      
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SignatureData::showCertificatenew($id_certificado);                                
                 
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
}