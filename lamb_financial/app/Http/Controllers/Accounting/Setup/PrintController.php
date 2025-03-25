<?php
namespace App\Http\Controllers\Accounting\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\PrintData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class PrintController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function printDocument(Request $request,$id_docip){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $result = false;
        if($valida=='SI'){
            $etiq = false;
            $jResponse=[];  
            $address = GlobalMethods::ipClient($request);                                
            try{   
                PrintData::deletePrint($id_user);
                PrintData::deleteTemporal($id_user);
                $data = PrintData::showIPDocumentUser($id_user,$id_docip);
                foreach($data as $id){
                    $id_documento = $id->id_documento;
                    $address = $id->ip;
                }
                PrintData::addPrint($id_user,$id_documento,1,"x");
                $data = PrintData::listDocumentsPrints($id_user);
                $texto="";	
                $texto.=chr(27); 
                $texto.=chr(15);

                $x="\n";
                $y="~";
                $nueva_data=str_replace($y,$x,$data);

                //asigno el contenido del string a mi variable texto.
                $texto.=$nueva_data;

                //le contateno mas retornos de carro o enters y demas.
                $texto=$texto.""."\n";
                $texto.=chr(27);  // caracter de escape al final de la variable
                $texto.=chr(105);  // 

                //para mandar a imprimr a tiketera.
                
                //$address="192.168.0.46";
                $service_port = 7654;
                $body = $texto;

                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if ($socket < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($socket));

                $result = socket_connect($socket, $address, $service_port);
                $etiq = true;
                if ($result < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($result));
                socket_write($socket, $body, strlen($body));
                socket_close($socket);
                
                if($etiq){
                    if($result){
                        $jResponse['eitq'] = "Ok";
                    }else{
                        $jResponse['eitq'] = " OK pero no hay conexion a la ticketera ";
                    }
                }else{
                    $jResponse['eitq'] = "no hay ";
                }
                
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200";                
            }catch(Exception $e){
                
                if($etiq){
                    if($result){
                        $jResponse['message'] = "Ok per con problemas";
                    }else{
                       $jResponse['message'] = " OK pero no hay conexion a la ticketera "; 
                    }
                    
                }else{
                    $jResponse['message'] = "OK, pero no hay conexion a la ticketera (".$address.")";
                }
                $jResponse['success'] = false;
                $jResponse['data'] = $address;
                $code = "400";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function printONE($id_user){
        $texto="";
        $texto.=chr(27);
        $texto.=chr(15);
        PrintData::deletePrint($id_user);
        PrintData::deleteTemporal($id_user);
    }
    public function listDocumentsPointsPrints(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PrintData::listDocumentsPointsPrints($id_entidad,$id_depto,$id_user);
                if($data) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showDocumentsPointsPrints($id_docip){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PrintData::showDocumentsPointsPrints($id_docip);
                if(count($data) > 0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function addDocumentsPointsPrints(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_documento = $params->id_documento;
            $nombre = $params->nombre;
            $ip = $params->ip;
            $estado = $params->estado;
            $id_tipotransaccion = $params->id_tipotransaccion; // Dinámica para los depositos que se realizan con este documento.
            $id_dinamica = $params->id_dinamica; // Dinámica para los depositos que se realizan con este documento.ƒ
            try{
                $data = PrintData::addDocumentsPointsPrints($id_documento,$nombre,$ip,$estado,$id_tipotransaccion, $id_dinamica);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updateDocumentsPointsPrints($id_docip){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_documento = $params->id_documento;
            $nombre = $params->nombre;
            $ip = $params->ip;
            $estado = $params->estado;
            $id_tipotransaccion = $params->id_tipotransaccion; // Dinámica para los depositos que se realizan con este documento.
            $id_dinamica = $params->id_dinamica; // Dinámica para los depositos que se realizan con este documento.ƒ
            
            try{
                $data = PrintData::updateDocumentsPointsPrints($id_docip,$id_documento,$nombre,$ip,$estado, $id_tipotransaccion, $id_dinamica);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteDocumentsPointsPrints($id_docip){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PrintData::deleteDocumentsPointsPrints($id_docip);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function addDocumentsPointsPrintsUsers(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"]; 
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_docip = $params->id_docip;
            try{
                // Valida si el punto de impresión es nota de credito o nota de débito.
                $docIps = PrintData::showDataPuntoDeImpresion($id_entidad, $id_depto, $id_docip);

                $id_comprobante = '';
                foreach ($docIps as $value) {
                    $id_comprobante = $value->id_comprobante;
                }

                // 07 Credito
		        // 08 Debito
		        // 87 Credito especial
                // 88 Debito especial.
                if($id_comprobante === '07' || $id_comprobante === '08' || $id_comprobante === '87' || $id_comprobante === '88') {
                    $data = PrintData::listDocumentsPointsPrintsUsersNCD($id_docip,$id_user,$id_entidad,$id_depto);
                    if(count($data) > 0){ 
                        foreach($data as $id){
                            $id_docip_old = $id->id_docip;
                        } 
                        $data = PrintData::updateDocumentsPointsPrintsUsers($id_docip,$id_user,$id_docip_old);
                    } else {
                        $data = PrintData::addDocumentsPointsPrintsUsers($id_docip,$id_user);
                    }
                } else {
                    $data = PrintData::listDocumentsPointsPrintsUsers($id_docip,$id_user,$id_entidad,$id_depto);
                    if(count($data) > 0){
                        foreach($data as $id){
                            $id_docip_old = $id->id_docip;
                        }
                        $data = PrintData::updateDocumentsPointsPrintsUsers($id_docip,$id_user,$id_docip_old);
                    }else{
                        $data = PrintData::addDocumentsPointsPrintsUsers($id_docip,$id_user);
                    }
                }
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was assigned successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
}