<?php
namespace App\Http\Controllers\Budget;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\ConfiguracionData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class ConfiguracionController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function listProject(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $data = ConfiguracionData::listProject($id_entidad,$id_depto);                                
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
    public  function showProject($id_proyecto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::showProject($id_proyecto);                                
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
    public  function addProject(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $nombre = $params->nombre;
                $comentario = $params->comentario;
                $fdesde = $params->fdesde;
                $fhasta = $params->fhasta;
                $estado = $params->estado;
                $id_depto= $params->id_depto;
                         
                $data = ConfiguracionData::addProject($id_entidad,$nombre,$comentario,$fdesde,$fhasta,$id_depto,$estado);                    
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
    public  function updateProject($id_proyecto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $nombre = $params->nombre;
                $comentario = $params->comentario;
                $fdesde = $params->fdesde;
                $fhasta = $params->fhasta;
                $estado = $params->estado;
                
                         
                $data = ConfiguracionData::updateProject($id_proyecto,$id_entidad,$nombre,$comentario,$fdesde,$fhasta,$estado);                    
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
    public  function deleteProject($id_proyecto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = ConfiguracionData::deleteProject($id_proyecto);                    
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
    
    public  function listEvent(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_proyecto = $request->query('id_proyecto');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_depto = $request->query('id_depto');
                $data = ConfiguracionData::listEvent($id_entidad,$id_depto,$id_proyecto,$id_auxiliar);                                
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
    public  function showEvent($id_evento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     

                $data = ConfiguracionData::showEvent($id_evento);                                
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
    public  function addEvent(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_proyecto = $params->id_proyecto;
                $nombre = $params->nombre;
                $id_auxiliar = $params->id_auxiliar;
                $descripcion = $params->descripcion;
                $cantidad = $params->cantidad;
                $punidad = $params->punidad;
                $a = $params->a;
                $b = $params->b;
                $c = $params->c;
                $d = $params->d;
                $e = $params->e;
                $f = $params->f;
                $g = $params->g;
                $formula = $params->formula;
                $estado = $params->estado;
                $tipo_asiento=$params->tipo_asiento;
                $id_depto = $params->id_depto;
                $data = ConfiguracionData::addEvent($id_proyecto,$id_entidad,$id_depto,$nombre,$id_auxiliar,$descripcion,$cantidad,$punidad,$a,$b,$c,$d,$e,$f,$g,$formula,$tipo_asiento,$estado);                    
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
    public  function updateEvent($id_evento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $nombre = $params->nombre;
                $id_auxiliar = $params->id_auxiliar;
                $descripcion = $params->descripcion;
                $cantidad = $params->cantidad;
                $punidad = $params->punidad;
                $a = $params->a;
                $b = $params->b;
                $c = $params->c;
                $d = $params->d;
                $e = $params->e;
                $f = $params->f;
                $g = $params->g;
                $formula = $params->formula;
                $estado = $params->estado;
                $tipo_asiento=$params->tipo_asiento;
                $data = ConfiguracionData::updateEvent($id_evento,$nombre,$id_auxiliar,$descripcion,$cantidad,$punidad,$a,$b,$c,$d,$e,$f,$g,$formula,$tipo_asiento,$estado);                    
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
    public  function deleteEvent($id_evento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = ConfiguracionData::deleteEvent($id_evento);                    
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
    
    public  function listActivity(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_evento = $request->query('id_evento');
                $data = ConfiguracionData::listActivity($id_evento);                                
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
    public  function showActivity($id_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
               
                $data = ConfiguracionData::showActivity($id_actividad);                                
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
    public  function addActivity(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_evento = $params->id_evento;  
                $tipo = $params->tipo;
                $nombre = $params->nombre;
                $esdescuento = $params->esdescuento;
                $descripcion = $params->descripcion;
                $id_tipoplan = $params->id_tipoplan;
                $id_cuentaaasi = $params->id_cuentaaasi;
                $id_restriccion = $params->id_restriccion;
                $id_entidad = $params->id_entidad;
                $id_ctacte = $params->id_ctacte;
                $id_tipoctacte = $params->id_tipoctacte;
                $importeunit1 = $params->importeunit1;
                $importeunit2 = $params->importeunit2;
                $id_depto = $params->id_depto;
                $estado = $params->estado;
                $tipo_dist=$params->tipo_dist;
                $data = ConfiguracionData::addActivity($id_evento,$tipo,$nombre,$esdescuento,$descripcion,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$id_entidad,$id_ctacte,$id_tipoctacte,$importeunit1,$importeunit2,$id_depto,$estado,$tipo_dist);                    
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
    public  function updateActivity($id_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $tipo = $params->tipo;
                $nombre = $params->nombre;
                $esdescuento = $params->esdescuento;
                $descripcion = $params->descripcion;
                $id_tipoplan = $params->id_tipoplan;
                $id_cuentaaasi = $params->id_cuentaaasi;
                $id_restriccion = $params->id_restriccion;
                $id_entidad = $params->id_entidad;
                $id_ctacte = $params->id_ctacte;
                $id_tipoctacte = $params->id_tipoctacte;
                $importeunit1 = $params->importeunit1;
                $importeunit2 = $params->importeunit2;
                $id_depto = $params->id_depto;
                $estado = $params->estado;
                $asigna = $params->asigna;
                $id_entidad_ctacte = $params->id_entidad_ctacte;
                $tipo_dist=$params->tipo_dist;
                $data = ConfiguracionData::updateActivity($id_actividad,$tipo,$nombre,$esdescuento,$descripcion,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$id_entidad,$id_ctacte,$id_tipoctacte,$importeunit1,$importeunit2,$id_depto,$estado,$asigna,$id_entidad_ctacte,$tipo_dist);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $jResponse['data'] = $data;
                $code = "200";
  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage()." ***********";
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function deleteActivity($id_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = ConfiguracionData::deleteActivity($id_actividad);                    
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
    public  function listActivityDist(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_actividad = $request->query('id_actividad');
                $data = ConfiguracionData::listActivityDist($id_actividad);                                
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
    public  function updateActivityDist($id_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $details=$params->details;
                $ret = ConfiguracionData::updateActivityDist($id_actividad,$details);  
                
                if($ret==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Porcentaje diferente a 100%";
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
    public  function listArea($id_entidad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $data = ConfiguracionData::listArea($id_entidad);                                
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
    public  function showArea($id_area){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::showArea($id_area);                                
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
    public  function addArea(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_depto = $params->id_depto;
                $nombre = $params->nombre;
                $estado = $params->estado;
                
                         
                $data = ConfiguracionData::addArea($id_entidad,$id_depto,$nombre,$estado);                    
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
    public  function updateArea($id_area){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $nombre = $params->nombre;
                $comentario = $params->comentario;
                $fdesde = $params->fdesde;
                $fhasta = $params->fhasta;
                $estado = $params->estado;
                
                         
                $data = ConfiguracionData::updateArea($id_area,$id_entidad,$id_depto,$nombre,$estado);                    
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
    public  function deleteArea($id_area){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = ConfiguracionData::deleteArea($id_area);                    
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
    
    public  function listEjeActivo(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listEjeActivo();                                
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
    public  function listUnitNegocioActivo(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listUnitNegocioActivo();                                
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
    public  function listAreaActivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_entidad = $request->query('id_entidad'); 
                $data = ConfiguracionData::listAreaActivo($id_entidad);                                
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
    public  function listProyectoActivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto'); 
                $data = ConfiguracionData::listProyectoActivo($id_entidad,$id_depto);                                
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
    public  function listAuxliarActivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto'); 
                $data = ConfiguracionData::listAuxliarActivo($id_entidad,$id_depto);                                
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
    public  function listCtaCte(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_entidad = $request->query('id_entidad'); 
                $id_tipoplan = $request->query('id_tipoplan'); 
                $id_cuentaaasi = $request->query('id_cuentaaasi'); 
                $id_restriccion = $request->query('id_restriccion'); 
                $data = ConfiguracionData::listCtaCte($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion);                                
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
    public  function listEventProyecto(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_proyecto = $request->query('id_proyecto'); 
                $data = ConfiguracionData::listEventProyecto($id_proyecto);   
                $departamentos = ConfiguracionData::listarDeptoActvo($id_proyecto);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'deptos'=>$departamentos];
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
    
     public  function listDeptoActivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                
                $buscar= $request->query('buscar'); 
                $id_entidad = $request->query('id_entidad'); 
                $data = ConfiguracionData::listDeptoActivo($id_entidad,$buscar);                                
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
     
     public  function listDeptoFac(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                
                $tipo= $request->query('tipo'); 
                $id_entidad = $request->query('id_entidad'); 
                $id_depto = $request->query('id_depto');
                $data = ConfiguracionData::listDeptoFac($id_entidad,$id_depto,$tipo);                                
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
     public  function listMenecion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                
                $tipo= $request->query('tipo'); 
                $id_entidad = $request->query('id_entidad'); 
                $id_depto = $request->query('id_depto');
                $data = ConfiguracionData::listMenecion($id_entidad,$id_depto,$tipo);                                
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
     
     
     public  function listDeptoPregrado(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                
                $tipo= $request->query('tipo'); 
                $id_entidad = $request->query('id_entidad'); 
                $id_depto = $request->query('id_depto'); 
                $tipo_depto = $request->query('tipo_depto'); 
                $data = ConfiguracionData::listDeptoPregrado($id_entidad,$id_depto,$tipo,$tipo_depto);                                
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
     public  function listAreaDepto(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];

            if($valida=='SI'){
                $jResponse=[];                        
                try{

                    $tipo= $request->query('tipo'); 
                    $id_entidad = $request->query('id_entidad'); 
                    $id_depto = $request->query('id_depto'); 
                    $id_param = $request->query('id_param'); 
                    $data = ConfiguracionData::listAreaDepto($id_entidad,$id_depto,$id_param,$tipo);                                
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
    public  function listarDeptoActvo(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];

            if($valida=='SI'){
                $jResponse=[];                        
                try{

                    $id_entidad = $request->query('id_entidad'); 
                    $id_depto = $request->query('id_depto'); 
      
                    $data = ConfiguracionData::listarDeptoActvo($id_entidad,$id_depto);                                
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
    public  function listRenovable(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listRenovable();                                
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
    public  function listSexo(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listSexo();                                
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
    public  function listEdad(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listEdad();                                
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
    public  function listNivelEducativo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $estado_psto=$request->query('estado_psto');
                $data = ConfiguracionData::listNivelEducativo($estado_psto);                                
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
    public  function listEstadoCivil(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $estado_psto=$request->query('estado_psto');
                $data = ConfiguracionData::listEstadoCivil($estado_psto);                                
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
    public  function listTiempoTrabajo(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listTiempoTrabajo();                                
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
    public  function listTemporada(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = ConfiguracionData::listTemporada();                                
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
    public  function listCondicionLaboral(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $id_cargo_proceso=0;
               
                if(!is_null($request->query('id_cargo_proceso'))){
                    $id_cargo_proceso=$request->query('id_cargo_proceso');
                }
                
                $data = ConfiguracionData::listCondicionLaboral($id_cargo_proceso);                                
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
    public  function listColumnas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $dato=$request->query('dato');
                $data = ConfiguracionData::listColumnas($dato);                                
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
    public  function listConceptoaps(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listConceptoaps();                                
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
    public  function listCargo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listCargo();                                
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
    public  function showCargo($id_cargo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::showCargo($id_cargo);                                
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
    
    public  function listCondicionEscala(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listCondicionEscala();                                
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
    public  function listProfesion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listProfesion();                                
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
    public  function listTipoContrato(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{  
                $id_cargo_proceso=0;
                if(!is_null($request->query('id_cargo_proceso'))){
                    $id_cargo_proceso=$request->query('id_cargo_proceso');
                }
                $data = ConfiguracionData::listTipoContrato($id_cargo_proceso);                                
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
    public  function listTipoEstatus(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listTipoEstatus();                                
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
    public  function listTipoPais(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = ConfiguracionData::listTipoPais();                                
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
    public  function listDepatamentoArea(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad=$request->query('id_entidad');
                $id_area=$request->query('id_area');
                $data = ConfiguracionData::listDepatamentoArea($id_entidad,$id_area);                                
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
}
?>
