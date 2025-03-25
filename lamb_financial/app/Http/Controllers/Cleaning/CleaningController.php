<?php
namespace App\Http\Controllers\Cleaning;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Cleaning\CleaningData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

class CleaningController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function listGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $data = CleaningData::listGroup($id_entidad,$id_depto);                                
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
    public  function showGroup($id_grupo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = CleaningData::showGroup($id_grupo);                                
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
    public  function addGroup(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $nombre = $params->nombre;
                $descripcion = $params->descripcion;
                $id_persona = $params->id_persona;
                $id_adjunto = $params->id_adjunto;
                $estado = '1';
                $id_depto= $params->id_depto;
                         
                $data = CleaningData::addGroup($id_entidad,$nombre,$descripcion,$id_persona,$id_adjunto,$id_depto,$estado);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
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
    public  function updateGroup($id_grupo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $nombre = $params->nombre;
                $descripcion = $params->descripcion;
                $id_persona = $params->id_persona;
                $id_adjunto = $params->id_adjunto;
                $estado = $params->estado;
                
                         
                $data = CleaningData::updateGroup($id_grupo,$nombre,$descripcion,$id_persona,$id_adjunto,$estado);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $jResponse['data'] = $data;
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
    public  function deleteGroup($id_grupo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = CleaningData::deleteGroup($id_grupo);                    
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
    public  function listPersona(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_depto = '12010102';
                $data = CleaningData::listPersona($id_entidad,$id_depto);                                
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
    //integrantes
    public  function listGrupoIntegrantes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_grupo = $request->query('id_grupo');
                $data = CleaningData::listGrupoIntegrantes($id_grupo);                                
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
    public  function showGrupoIntegrantes($id_grupo_integrante){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = CleaningData::showGrupoIntegrantes($id_grupo_integrante);                                
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
    public  function addGrupoIntegrantes($id_grupo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;  
                                        
                CleaningData::addGrupoIntegrantes($id_grupo,$details);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = [];
                $code = "200";
  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage().'linea: '.$e->getLine().' Archivo: '.$e->getFile();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function updateGrupoIntegrantes($id_grupo_integrante){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_persona = $params->id_persona;
                $hentrada = $params->hentrada;
                $hsalida = $params->hsalida;
                $estado = $params->estado;
                
                         
                $data = CleaningData::updateGrupoIntegrantes($id_grupo_integrante,$id_persona,$hentrada,$hsalida,$estado);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $jResponse['data'] = $data;
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
    public  function deleteGrupoIntegrantes($id_grupo_integrante){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = CleaningData::deleteGrupoIntegrantes($id_grupo_integrante);                    
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
    //servicios
    public  function listGrupoServicio(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_grupo = $request->query('id_grupo');
                $data = CleaningData::listGrupoServicio($id_grupo);                                
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
    public  function showGrupoServicio($id_grupo_servicio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = CleaningData::showGrupoServicio($id_grupo_servicio);                                
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
    public  function addGrupoServicio($id_grupo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;  
                                        
                $data = CleaningData::addGrupoServicio($id_grupo,$details);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
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
    public  function updateGrupoServicio($id_grupo_servicio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_persona = $params->id_persona;
                $descripcion = $params->descripcion;
                $comentario = $params->comentario;
                 
                         
                $data = CleaningData::updateGrupoServicio($id_grupo_servicio,$id_persona,$descripcion,$comentario);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $jResponse['data'] = $data;
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
    public  function deleteGrupoServicio($id_grupo_servicio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = CleaningData::deleteGrupoServicio($id_grupo_servicio);                    
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
    public function listAssistsControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                //$id_entidad = $request->query('id_entidad');
                $id_grupo = $request->query('id_grupo');
                $rpta = CleaningData::asistencia($id_grupo,$id_user);
                if($rpta["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $rpta["data"];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["mensaje"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
     public function updateAssistsControl($id_control){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $asistencia_culto = $params->asistencia_culto;
                CleaningData::actualizarControl($id_control,$asistencia_culto,$id_user);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
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
    public function listarGrupoUser(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                //$id_entidad = $request->query('id_entidad');

                $data = CleaningData::listarGrupoUser($id_user);                               
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
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listAssists(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_grupo = $request->query('id_grupo');
                $fecha = $request->query('fecha');
                $date = CleaningData::showFecha($fecha);
                
                
                foreach ($date as $item){
                    $fecha_a = $item->fecha;               
                }
                $data = CleaningData::listPersonal($id_entidad,$id_grupo);
                foreach ($data as $key => $value){  
                    $asisst = CleaningData::listPersonalAssists($value->id_persona,$id_grupo,$fecha,$fecha_a);
                    $parent[] = [
                                    'id_persona' => $value->id_persona, 
                                    'id_entidad' => $value->id_entidad,
                                    'id_grupo' => $value->id_grupo,
                                    'nombres' => $value->nombres,
                                    'letra' => $value->letra,
                                    'numero' => $value->numero,
                                    'confianza' => $value->confianza,
                                    'asisst' => $asisst
                                ];            
                }
                if(count($parent)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listSemana(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $fecha = $request->query('fecha');
                $data = explode("/", $fecha);
                $dia = intval($data[0]);
                $mes = intval($data[1]);
                $anho = intval($data[2]);
                $fecha_f= $anho."/".$mes."/".$dia;

                $date = CleaningData::showSemana($fecha_f);
                if(count($date)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $date;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listGroupActivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $data = CleaningData::listGroupActivo($id_entidad,$id_depto);
                if(count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
}
?>