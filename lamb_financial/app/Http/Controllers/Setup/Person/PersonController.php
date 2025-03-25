<?php
namespace App\Http\Controllers\Setup\Person;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Setup\PersonData;
use App\Http\Controllers\Setup\Person\curl;
use App\Http\Controllers\Setup\Person\solver;
use App\Http\Controllers\Setup\Person\reniec;
use App\Http\Controllers\Setup\Provider\sunat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

use App\Http\Controllers\Setup\Provider\sunatTemp;

class PersonController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listDocumentType(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{                         
                $data = PersonData::listDocumentType();                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listEconomicActivityType(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{                         
                $data = PersonData::listEconomicActivityType();                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listCivilStatustType(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $data = PersonData::listCivilStatustType();                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listCountry(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $data = PersonData::listCountry();                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addNaturalPerson(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $paterno = $params->data->paterno;
            $materno = $params->data->materno;            
            $id_tipodocumento = $params->data->id_tipodocumento;
            $id_tipoestadocivil = $params->data->id_tipoestadocivil;
            $id_tipopais = $params->data->id_tipopais;
            $dni = $params->data->dni; //= ruc
            $sexo = $params->data->sexo;
            $fec_nacimiento = $params->data->fec_nacimiento;

            $dniStr = substr($dni, 2,8);
            // $rucIni = substr($data->ruc, 0, 2);
            $ruc =  $dni;
            $data = "";
            try{                                           
                $personaRuc = PersonData::showNaturalPerson($ruc);

                $personaDNI = PersonData::showNaturalPersonDNI($dniStr);
                
                if(count($personaRuc)>0 and count($personaDNI)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Natural person already exists";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                  
                    if (count($personaRuc) == 0 and count($personaDNI) == 0) {

                        $max = PersonData::maxPersonId();
                        foreach ($max as $item){
                            $id_persona = $item->id_persona;                    
                        }

                        $data = PersonData::addNaturalPerson($id_persona,$nombre,$paterno,$materno,$dni,$id_tipodocumento,$id_tipoestadocivil,$id_tipopais,$sexo,$fec_nacimiento);  

                    } else if (count($personaDNI) > 0) {

                        foreach ($personaDNI as $ite){
                            $id_persona = $ite->id_persona;                 
                        }

                        $data = PersonData::addNaturalPersonDocumento($id_persona, $ruc, $id_tipodocumento);

                    }

                    $jResponse['success'] = $data ? true : false;
                    $jResponse['message'] = $data ? "The item was created successfully" : 'No se pudo crear';
                    $jResponse['data'] = $data ? $data[0] : [];
                    // Si es una persona peruana debe tener dni de lo contrario no listará
                    // Cuando es persona natural y tiene ruc. No retornará nada, es un detalle de la vista
                    // no preocuparse, SI GUARDA LA PERSONA.
                    $code = "201";
                }                               
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addLegalPerson(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $razonsocial = $params->data->razonsocial;
            $nombrecomercial = $params->data->nombrecomercial;
            $direccion = $params->data->direccion;
            $id_tipopais = $params->data->id_tipopais;
            $ruc = $params->data->ruc;
            $inscripcion = $params->data->inscripcion;
            $tipo = $params->data->tipo;
            $estado = $params->data->estado;
            $condicion = $params->data->condicion;
            $id_tipoactividadeconomica = $params->data->id_tipoactividadeconomica;
            try{
                $existe = "N";
                $persona = PersonData::showLegalPerson($razonsocial, $ruc);
                if(count($persona)>0){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Legal person already exists";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $dni = substr($ruc,2,31);
                    $person = PersonData::showNaturalPersonDNI($dni);
                    if(count($persona)>0){
                        $existe = "S";
                        foreach ($person as $item){
                            $id_persona = $item->id_persona;                    
                        }
                    }else{
                        $existe = "N";
                        $data = PersonData::maxPersonId();
                        foreach ($data as $item){
                            $id_persona = $item->id_persona;                    
                        }
                    }
                    $dtipo = PersonData::showTaxpayerType($tipo);
                    foreach ($dtipo as $item){
                        $id_tipocontribuyente = $item->id_tipocontribuyente;                    
                    }                    
                    $destado = PersonData::showStateType($estado);

                    if(count($destado) === 0) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "El proveedor $razonsocial está en $estado, el estado no está registrado en la base de datos.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }

                    foreach ($destado as $item){
                        $id_tipoestado = $item->id_tipoestado;
                    }
                    $dcondicion = PersonData::showConditionType($condicion);
                    foreach ($dcondicion as $item){
                        $id_tipocondicion = $item->id_tipocondicion;                    
                    }                    
                    $data = PersonData::addLegalPerson($id_persona,$razonsocial,$nombrecomercial,$direccion,$ruc,$id_tipopais,$inscripcion,$id_tipocontribuyente,$id_tipoestado,$id_tipocondicion,$id_tipoactividadeconomica,$existe);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data[0];
                    $code = "201";
                }                               
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    private function consultareniec($dni){
        
        $data = [    
                'nerror'=>1
           ];
        try{
            $consulta=file_get_contents('http://aplicaciones007.jne.gob.pe/srop_publico/Consulta/Afiliado/GetNombresCiudadano?DNI='.$dni);

            //$data = $cliente->search($dni,true); 
            $partes = explode("|",$consulta);
            //$msg = $data["success"];                      
            //if($msg==true){
            if(count($partes)==3){

                $data = [    
                    'nerror'=>0,
                    'nombre' =>$partes[2],
                    'paterno' =>$partes[0],
                    'materno' =>$partes[1]

                ]; 
            }
        }catch(Exception $e){ 
        
        }
        return $data;
    }
    public function getDataReniec(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"]; 
        //$valida='SI';
        if($valida=='SI'){
            try{
                $jResponse=[];
                $dni = $request->query('dni');

                $partes = $this->consultareniec($dni);
                //dd($partes );
                //$msg = $data["success"];                      
                //if($msg==true){
                if($partes['nerror']==0){

                    $data = [                       
                        'nombre' =>$partes['nombre'],
                        'paterno' =>$partes['paterno'],
                        'materno' =>$partes['materno'],
                        'dni' =>$dni,
                        'codverificacion' =>''                  
                    ]; 
                    /*$data = [                       
                        'nombre' =>$data["Nombre"],
                        'paterno' =>$data["Paterno"],
                        'materno' =>$data["Materno"],
                        'dni' =>$data["DNI"],
                        'codverificacion' =>$data["CodVerificacion"]                   
                    ];*/                
                    $jResponse['success'] = true;
                    $jResponse['message'] = "ok";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se puede encontrar";                    
                    $jResponse['data'] = [];
                    $code = "202";
                } 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "No se puede encontrar";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }   
   /* public function getDataReniec(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"]; 
        $valida='SI';
        if($valida=='SI'){
            try{
                $jResponse=[];
                $dni = $request->query('dni');
                $cliente = new Reniec();
                $data = $cliente->search($dni,true); 
   
                $msg = $data["success"];                      
                if($msg==true){


                   
                   $data = [                       
                        'nombre' =>$data["Nombre"],
                        'paterno' =>$data["Paterno"],
                        'materno' =>$data["Materno"],
                        'dni' =>$data["DNI"],
                        'codverificacion' =>$data["CodVerificacion"]                   
                    ];              
                    $jResponse['success'] = true;
                    $jResponse['message'] = "ok";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "false";                    
                    $jResponse['data'] = [];
                    $code = "204";
                } 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "No se puede encontrar";
                $jResponse['data'] = [];
                $code = "204";
            }
        }
        return response()->json($jResponse,$code);
    }    */

    public static function validaSunatOptionTwo($ruc){

        $url_sunat2 = 'https://api.apis.net.pe/v1/ruc?numero='.$ruc;
        $curl_sunat2 = curl_init($url_sunat2);
        curl_setopt($curl_sunat2, CURLOPT_RETURNTRANSFER, true);
        $sunat2 = curl_exec($curl_sunat2);
        $sunat2_status = curl_getinfo($curl_sunat2, CURLINFO_HTTP_CODE);

        if ($sunat2_status !== 200) {
            curl_close($curl_sunat2);
            return ['success' => false];
        }
        $response = json_decode($sunat2);
        // dd($response);
        $rucIni = substr($response->numeroDocumento, 0, 2);
        $tipo = '';
        if ($rucIni == '10') {
            $tipo = 'PERSONA NATURAL SIN NEGOCIO';
        } else {
            $tipo = 'EMPRESA INDIVIDUAL DE RESP. LTDA';
        }

        $data = [
            'RUC' => $response->numeroDocumento,
            'RazonSocial' => $response->nombre,
            'NombreComercial' => $response->nombre,
            'Direccion' => $response->direccion,
            'Condicion' => $response->condicion,
            'Tipo' => $tipo,
            'Estado' => $response->estado,
            'Inscripcion' => '',
            'ActividadEconomica' => '',
            'Comprobantes' => [],
            'success' => true,
        ];
        return $data;
    } 

    public static function dataSunat($ruc){
        $jResponse=[];
        $cliente = new sunat(true,true);
        $data = $cliente->search($ruc,true); 
        $success = $data["success"];
        // throw new Exception("No hay conexión con la SUNAT, no podemos validar el ruc.", 1);
        if($success != true){
            $response = PersonController::validaSunatOptionTwo($ruc);
            if($response['success'] === false) {
                throw new Exception("Alto! No hay conexión a la Sunat.", 1);
            } 
            $data = $response;
        }
        $jResponse = [
            'ruc' =>$data["RUC"],
            'razonsocial' =>$data["RazonSocial"],
            'nombrecomercial' =>$data["NombreComercial"],
            'direccion' =>$data["Direccion"],
            'condicion' =>$data["Condicion"],
            'tipo' =>$data["Tipo"],
            'estado' =>$data["Estado"],                
            'inscripcion' =>$data["Inscripcion"],
            'actividadeconomica'=>$data["ActividadEconomica"],
            'comprobantes'=>$data["Comprobantes"],
            // 'comprobantes_electronicos'=>$data["comprobante_electronico"],
        ];
        
        return $jResponse;
    } 

    public function getDataSunat(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        //$valida = 'SI';
        if($valida=='SI'){
            try{   
                $ruc = $request->query('ruc');
                $data = $this->dataSunat($ruc);
                $jResponse['success'] = true;
                $jResponse['message'] = "ok";                    
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();                    
                $jResponse['data'] = null;
                $code = "202";
            }
        }
            // $jResponse=[];
            // $ruc = $request->query('ruc');
            // // $cliente = new sunat(true,true);
            // // $data = $cliente->search($ruc,true); 
            // // $msg = $data["success"];
            // $cliente = new sunatTemp();
            // $data = $cliente->searchRuc($ruc); 
            // $msg = false;
            // // dd($data);
            // if (!empty($data) and !empty($data->ruc)) {
            // // if (!empty($data) and !empty($data["RUC"])) {

            //     $msg = true;
            // }
            // if($msg==true){
            //     $rucIni = substr($data->ruc, 0, 2);
            
            //     if ($rucIni == '10') {
            //         $tipo = 'PERSONA NATURAL SIN NEGOCIO';
            //     } else {
            //         $tipo = 'EMPRESA INDIVIDUAL DE RESP. LTDA';
            //     }
            //     $data = [
            //         'ruc' =>$data->ruc,
            //         'razonsocial' =>$data->rs,
            //         'nombrecomercial' =>'',
            //         'direccion' =>$data->direccion_string,
            //         'condicion' =>$data->condom,
            //         'tipo' =>$tipo,
            //         'estado' =>$data->estado,                
            //         'inscripcion' =>'',
            //         'actividadeconomica'=>'',
            //         'comprobantes'=>'',
            //     ];

            //     // $data = [
            //     //     'ruc' =>$data["RUC"],
            //     //     'razonsocial' =>$data["RazonSocial"],
            //     //     'nombrecomercial' =>$data["NombreComercial"],
            //     //     'direccion' =>$data["Direccion"],
            //     //     'condicion' =>$data["Condicion"],
            //     //     'tipo' =>$data["Tipo"],
            //     //     'estado' =>$data["Estado"],                
            //     //     'inscripcion' =>$data["Inscripcion"],
            //     //     'actividadeconomica'=>$data["ActividadEconomica"],
            //     //     'comprobantes'=>$data["Comprobantes"],
            //     //     // 'comprobantes_electronicos'=>$data["comprobante_electronico"],
            //     // ];

            //     $jResponse['success'] = true;
            //     $jResponse['message'] = "ok";                    
            //     $jResponse['data'] = $data;
            //     $code = "200";
            // }else{
            //     $jResponse['success'] = false;
            //     $jResponse['message'] = "false";                    
            //     $jResponse['data'] = null;
            //     $code = "200";
            // }
        return response()->json($jResponse,$code);
    }
    public function listNaturalPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{      
                $size_page = $request->query('size_page');
                $data = PersonData::listNaturalPersons($size_page);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listLegalPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                
                $all = $request->query('all'); 
                if($all == null){
                    $all = true;
                }
                //if($all== "true"){
                    //$size_page = 10000;
                //}else{
                    $size_page = $request->query('size_page');
                //}
                $text_search = $request->query('text_search');
                $data = PersonData::listLegalPersons($size_page,$text_search,$all);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listLegalPersonsAndNaturalWithRuc(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                $text_search = $request->query('text_search');
                $data = PersonData::listLegalPersonsAndNaturalWithRuc($text_search);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listNaturalPersonsWithRuc(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                
                $text_search = $request->query('text_search');
                $data = PersonData::searchNaturalPersons($text_search);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listStudentsPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{      
                $dato = $request->query('dato');
                $data = PersonData::listStudentsPersons($dato);                   
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function showWorkerPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];            
            try{      
                $documento = $request->query('num_documento');
                $data = PersonData::showWorkerPersons($id_entidad,$documento);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addPersonsBankAccount(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_persona = $params->id_persona;
            $id_banco = $params->id_banco;
            $id_tipoctabanco = $params->id_tipoctabanco;            
            $cuenta = $params->cuenta;
            $cci = $params->cci;
            try{                                           
                $data = PersonData::addPersonsBankAccount($id_persona, $id_banco, $id_tipoctabanco, $cuenta,$cci);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data[0];
                $code = "201";                        
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showPersonsBankAccount(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_persona = $params->id_persona;
            try{                                           
                $data = PersonData::showPersonsBankAccount($id_persona);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data[0];
                $code = "201";                        
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPersonsBankAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];
            $id_persona = $request->query('id_persona');
            try{                                           
                $data = PersonData::listPersonsBankAccount($id_persona);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
                $code = "201";                        
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPersonBankAccounts($idPerson){
//        dd(!queee);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PersonData::listPersonBankAccounts($idPerson);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addUsersImage(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $file = $request->file('file');
            try{     
                $datos = PersonData::showPersonNatural($id_user,'X');
                foreach($datos as $id){
                    $imagen = $id->imagen;
                }
                $objFile = $this->uploadImage($file,$id_user,$imagen);
                if ($objFile["success"]){
                    $url = $objFile["data"];
                }else{
                    $url = "";
                }
                if($url != ""){
                    $data = PersonData::updateUsersImage($id_user,$url);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The image has been successfully attached";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $objFile["message"];
                    $jResponse['data'] = [];
                    $code = "202";
                } 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function uploadImage($file,$id,$image_old){
        try{
            if($file != null){
                $fileName = $file->getClientOriginalName();
                $formato = strtoupper($file->getClientOriginalExtension());
                $size = ($file->getSize()/1024);
                if($size <= 768){
                    if(is_file($image_old)){
                        unlink($image_old);
                    }
                    
                    //$foto = $fileName;
                    $foto = $id.".".$formato;
                    $path = 'setup_files/users';
                    $url = $path."/".$foto;
                    $file->move($path,$foto);
                    return ["success"=>true,"message"=>"OK","data"=>$url,"path"=>$path,"formato"=>$formato,"size"=>$size];
                }else{
                    return ["success"=>false,"message"=>"Error: The image exceeds the allowed size (768 KB)"];
                }
            }else{
                return ["success"=>false,"message"=>"Error: The image was not attached"];
            }
        }catch(Exception $e){                    
            return ["success"=>false,"message"=>$e->getMessage()];
        }            
    }

    public function SearchGlobalPerson(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request-> query('search');
            if ($searchs) {
                $data = PersonData::SearchGlobalPerson($searchs);
            } else {
               $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function lisLegalPersonsAndNatural(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                $text_search = $request->query('text_search');
                $data = PersonData::lisLegalPersonsAndNatural($text_search);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }
}