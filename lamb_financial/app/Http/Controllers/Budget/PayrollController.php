<?php
namespace App\Http\Controllers\Budget;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\PayrollData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class PayrollController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public  function listParametro(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho=$request->query('id_anho');
                $data = PayrollData::listParametro($id_anho);                                
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
    public  function updateParametro(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $detail = $params->details;
                
                PayrollData::updateParametro($detail);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function procParametro(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_anho = $params->id_anho;
                
                PayrollData::procParametro($id_anho);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
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
    public  function listProcesoCargo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad  = $request->query('id_entidad');
                $id_anho     = $request->query('id_anho');
                $id_depto    = $request->query('id_depto');
                $data = PayrollData::listProcesoCargo($id_entidad,$id_depto,$id_anho);                                
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
    public  function showProcesoCargo($id_cargo_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $ret = PayrollData::showProcesoCargo($id_cargo_proceso);                                
                $jResponse['success'] = true;
                           
                if(count($ret["proccargo"])>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $ret["proccargo"],'conlab'=>$ret["proccargoconlab"],'prof'=>$ret["proccargoprof"],'tipcon'=>$ret["proccargotipcon"]];
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
    public  function addProcesoCargo(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $datos=array();
                $params = json_decode(file_get_contents("php://input"));        
                $datos["id_cargosueldo_escala"] = $params->id_cargosueldo_escala;
                $datos["id_renovable"] = $params->id_renovable;
                $datos["id_sexo"] = $params->id_sexo;
                $datos["id_depto"] = $params->id_depto;
                if(is_null($params->id_sexo)){
                     $datos["id_sexo"] = "0";
                }
                $datos["id_edad"] = $params->id_edad;
                if(is_null($params->id_edad)){
                     $datos["id_edad"] = "-";
                }
                $datos["id_nivel_edu"] = $params->id_nivel_edu;
                if(is_null($params->id_nivel_edu)){
                     $datos["id_nivel_edu"] = "-";
                }
                $datos["id_tipoestadocivil"] = $params->id_tipoestadocivil;
                if(is_null($params->id_tipoestadocivil)){
                     $datos["id_tipoestadocivil"] = "0";
                }
                $datos["id_tiempotrabajo"] = $params->id_tiempotrabajo;
                $datos["id_temporada"] = $params->id_temporada;
                $datos["cantidad"] = $params->cantidad;
                
                $datos["profesion"] = $params->profesion;
                $datos["condlab"] = $params->condlab;
                $datos["tipcont"] = $params->tipcont;
                
                $param=(object)$datos;
                
                $ret=PayrollData::addProcesoCargo($param);    
                
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret["msgerror"];
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
    public  function updateProcesoCargo($id_cargo_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $datos=array();
                $params = json_decode(file_get_contents("php://input"));        
                $datos["id_cargo_proceso"] = $id_cargo_proceso;
                $datos["id_renovable"] = $params->id_renovable;
                $datos["id_sexo"] = $params->id_sexo;
                if(is_null($params->id_sexo)){
                     $datos["id_sexo"] = "0";
                }
                $datos["id_edad"] = $params->id_edad;
                if(is_null($params->id_edad)){
                     $datos["id_edad"] = "-";
                }
                $datos["id_nivel_edu"] = $params->id_nivel_edu;
                if(is_null($params->id_nivel_edu)){
                     $datos["id_nivel_edu"] = "-";
                }
                $datos["id_tipoestadocivil"] = $params->id_tipoestadocivil;
                if(is_null($params->id_tipoestadocivil)){
                     $datos["id_tipoestadocivil"] = "0";
                }
                $datos["id_tiempotrabajo"] = $params->id_tiempotrabajo;
                $datos["id_temporada"] = $params->id_temporada;
                $datos["cantidad"] = $params->cantidad;
                
                $datos["profesion"] = $params->profesion;
                $datos["condlab"] = $params->condlab;
                $datos["tipcont"] = $params->tipcont;
                
                $param=(object)$datos;
                
                $ret=PayrollData::updateProcesoCargo($param);    
                
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret["msgerror"];
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
    public  function deleteProcesoCargo($id_cargo_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                
                
                PayrollData::deleteProcesoCargo($id_cargo_proceso);
                
                                           
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
    
    public  function listCargoSueldoEscala(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad  = $request->query('id_entidad');
                $id_anho     = $request->query('id_anho');
                $id_depto    = $request->query('id_depto');
                $id_cargo    = $request->query('id_cargo');
                $id_condicion_escala = $request->query('id_condicion_escala');
                $data = PayrollData::listCargoSueldoEscala($id_entidad,$id_depto,$id_anho,$id_cargo,$id_condicion_escala);                                
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
    public  function showCargoSueldoEscala($id_cargosueldo_escala){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::showCargoSueldoEscala($id_cargosueldo_escala);                                
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
    public  function cargoSueldoEscalaAll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad            = $request->query('id_entidad');
                $id_anho               = $request->query('id_anho');
                $id_depto              = $request->query('id_depto');
                $id_depto_padre        = $request->query('id_depto_padre');
                $id_cargosueldo_escala = $request->query('id_cargosueldo_escala');
                $data = PayrollData::cargoSueldoEscalaAll($id_entidad,$id_anho,$id_depto_padre,$id_depto,$id_cargosueldo_escala);                                
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
    public  function procCargoSueldoEscala(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_anho = $params->id_anho;
                $id_entidad = $params->id_entidad;
                $id_depto_padre = $params->id_depto_padre;
                PayrollData::procCargoSueldoEscala($id_entidad,$id_depto_padre,$id_anho);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
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
    public  function addCargoSueldoEscala(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                
                $datos["id_anho"] = $params->id_anho;
                $datos["id_entidad"] = $params->id_entidad;
                $datos["id_depto_padre"] = $params->id_depto_padre;
                $datos["id_condicion_escala"] = $params->id_condicion_escala;
                $datos["id_cargo"] = $params->id_cargo;
                $datos["tipo_min"] = $params->tipo_min;
                $datos["tipo_max"] = $params->tipo_max;
                $datos["minimo"] = $params->minimo;
                $datos["maximo"] = $params->maximo;
                $datos["bono_min"] = $params->bono_min;
                $datos=(object)$datos;
                $ret=PayrollData::addCargoSueldoEscala($datos);    
                
                if($ret==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Ya esta registradado escala salarial';
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
    public  function updateCargoSueldoEscala(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updateCargoSueldoEscala($details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function deleteCargoSueldoEscala($id_cargosueldo_escala){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{

                $ret= PayrollData::deleteCargoSueldoEscala($id_cargosueldo_escala);
                
                if($ret["nerror"]==0){                           
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";                  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret["msgerror"];
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
    public  function listConceptoPlanillaAnt(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                $data = PayrollData::listConceptoPlanillaAnt();                                
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
    public  function addConceptoPlanillaAnt(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_concepto_aps = $params->id_concepto_aps;
                $columna_imp = $params->columna_imp;
                PayrollData::addConceptoPlanillaAnt($id_concepto_aps,$columna_imp);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
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
    public  function deleteConceptoPlanillaAnt($id_concepto_aps,$columna_imp){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deleteConceptoPlanillaAnt($id_concepto_aps,$columna_imp);    
                
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
    public  function listConceptoActividad(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad  = $request->query('id_entidad');
                $id_depto_padre     = $request->query('id_depto');
                $data = PayrollData::listConceptoActividad($id_entidad,$id_depto_padre);                                
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
    public  function showConceptoActividad($id_concepto_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::showConceptoActividad($id_concepto_actividad);                                
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
    public  function procConceptoActividad(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_depto_padre = $params->id_depto;
                PayrollData::procConceptoActividad($id_depto_padre);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
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
    public  function updateConceptoActividad($id_concepto_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->data;
                PayrollData::updateConceptoActividad($id_concepto_actividad,$details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function deleteConceptoActividad($id_concepto_actividad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deleteConceptoActividad($id_concepto_actividad);    
                
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
    public  function listPlanilla(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_entidad     = $request->query('id_entidad');
                $id_depto     = $request->query('id_depto');
                $id_depto_padre     = $request->query('id_depto_padre');
                $data = PayrollData::listPlanilla($id_entidad,$id_anho,$id_depto_padre,$id_depto);                                
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
    public  function showPlanilla($id_psto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::showPlanilla($id_psto_planilla);                                
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
    /*public  function procPlanilla(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;
                $id_anho = $params->id_anho;
                $id_depto = $params->id_depto;
                $id_depto_padre = $params->id_depto_padre;
                $id_auxiliar = $params->id_auxiliar;

                PayrollData::procPlanilla($id_entidad,$id_anho,$id_depto,$id_depto_padre,$id_auxiliar);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
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
    }*/
    public  function validarDatosAnt($id_entidad,$id_anho,$id_persona){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::validarDatosAnt($id_entidad,$id_persona,$id_anho);                                
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
    public  function addPlanilla(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];  
            DB::beginTransaction();
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_cargo_proceso = $params->id_cargo_proceso;
                $id_persona = $params->id_persona;
                $id_tipocontrato = $params->id_tipocontrato;
                $id_cond_lab = $params->id_cond_lab;
                $docente_tc = $params->docente_tc;
                $eshextras25 = $params->eshextras25;
                $eshextras35 = $params->eshextras35;
                $eshnocturna = $params->eshnocturna;
                $eshferiado = $params->eshferiado;

                $ret=PayrollData::addPlanilla($id_cargo_proceso,$id_persona,$id_tipocontrato,$id_cond_lab,$docente_tc,$eshextras25,$eshextras35,$eshnocturna,$eshferiado);    
                
                if($ret["nerror"]==0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   DB::rollback();
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $jResponse['data'] = $ret["msgerror"];
                   $code = "202"; 
                }

            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function UpdatePlanillaPersona($id_psto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];

            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_persona = $params->id_persona;
                $ret = PayrollData::UpdatePlanillaPersona($id_psto_planilla,$id_persona);    
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret["msgerror"];
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
    public  function updatePlanilla(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updatePlanilla($details);    
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
                $jResponse['data'] = [];
                $code = "200";
                
                
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function deletePlanilla($id_psto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deletePlanilla($id_psto_planilla);    
                
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
    //Planilla distribuido
    public  function listPlanillaDist(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_depto     = $request->query('id_depto');
                $id_depto_padre     = $request->query('id_depto_padre');
                $data = PayrollData::listPlanillaDist($id_depto,$id_anho,$id_depto_padre);                                
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
    public  function showPlanillaDist($id_plla_planilla_dist){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::showPlanillaDist($id_plla_planilla_dist);                                
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
    public  function addPlanillaDist(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_psto_planilla = $params->id_psto_planilla;
                $id_depto = $params->id_depto;
                $id_temporada = $params->id_temporada;
                $porcentaje = $params->porcentaje;

                $ret=PayrollData::addPlanillaDist($id_psto_planilla,$id_depto,$id_temporada,$porcentaje);    
                
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $jResponse['data'] = $ret["msgerror"];
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
    public  function updatePlanillaDist($id_psto_planilla_dist){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_depto = $params->id_depto;
                $id_temporada = $params->id_temporada;
                $porcentaje = $params->porcentaje;

                $ret=PayrollData::updatePlanillaDist($id_psto_planilla_dist,$id_depto,$id_temporada,$porcentaje);    
                
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $jResponse['data'] = $ret["msgerror"];
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
    public  function deletePlanillaDist($id_psto_planilla_dist){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deletePlanillaDist($id_psto_planilla_dist);    
                
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
    public  function listPlanillaDistDet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_psto_planilla     = $request->query('id_psto_planilla');
                $data = PayrollData::listPlanillaDistDet($id_psto_planilla);                                
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
    
    public  function listAyuda(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_entidad    = $request->query('id_entidad');
                $id_area       = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = PayrollData::listAyuda($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto); 
              
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
    public  function addAyuda(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));
                $param["id_entidad"]  = $params->id_entidad;
                $param["id_anho"]  = $params->id_anho;
                $param["id_depto"] = $params->id_depto;
                $param["id_depto_padre"] = $params->id_depto_padre;
                $param["id_persona"] = $params->id_persona;
                $param["id_cargo"] = $params->id_cargo;
                $datos=(object)$param;
                
                $ret=PayrollData::addAyuda($datos);    
                
                 if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $ret["msgerror"];
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
    public  function updateAyuda(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updateAyuda($details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
     public  function deleteAyuda($id_psto_ayuda){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deleteAyuda($id_psto_ayuda);    
                
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
    public  function listMobLibDis(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_entidad    = $request->query('id_entidad');
                $id_area       = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                
                $data = PayrollData::listMobLibDis($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto);                                
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
    public  function addMobLibDis(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));
                
                $param["id_entidad"]  = $params->id_entidad;
                $param["id_persona"]  = $params->id_persona;
                $param["id_anho"]  = $params->id_anho;
                $param["id_depto"] = $params->id_depto;
                $param["id_cargo"] = $params->id_cargo;
                $param["kilometraje"] = $params->kilometraje;
        
                $datos=(object)$param;
                
                $ret=PayrollData::addMobLibDis($datos);    
                
                 if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $jResponse['data'] = $ret["msgerror"];
                   $code = "202"; 
                }
                $jResponse['success'] = true;
                             
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function updateMobLibDis(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updateMobLibDis($details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function deleteMobLibDis($id_psto_movlibdis){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deleteMobLibDis($id_psto_movlibdis);    
                
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
    public  function listPuntajeMis(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_entidad    = $request->query('id_entidad');
                $id_area       = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = PayrollData::listPuntajeMis($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto);                                
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
     public  function valPuntajeMis($id_entidad,$id_anho){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                //$id_anho  = $request->query('id_anho');
                //$id_entidad    = $request->query('id_entidad');
                $data = PayrollData::valPuntajeMis($id_entidad,$id_anho);                                
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
    public  function validarDatosMis($id_entidad,$id_anho,$id_persona){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = PayrollData::validarDatosMis($id_entidad,$id_persona,$id_anho);                                
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
    public  function procPuntajeMis(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));
                
                $id_anho  = $params->data->id_anho;
                $id_entidad = $params->data->id_entidad;
                
                $data=PayrollData::valPuntajeMis($id_entidad,$id_anho); 
                $contar=0;
                foreach($data as $row){
                    $contar=$row->contar;
                }
                             
                if($contar==0){
                    PayrollData::procPuntajeMis($id_entidad,$id_anho); 
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item does not exist";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = "Ya se ha procesado para el año ".$id_anho;
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
    public  function addPuntajeMis(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));
 
                $param["id_entidad"]  = $params->id_entidad;
                $param["id_persona"]  = $params->id_persona;
                $param["id_anho"]  = $params->id_anho;
                $param["id_depto"] = $params->id_depto;
                $param["id_cargo"] = $params->id_cargo;
                $param["id_tipoestatus"] = $params->id_tipoestatus;
                $param["id_tipopais"]  = $params->id_tipopais;
                $param["punt_ant"] = $params->punt_ant;
                $param["punt_min"] = $params->punt_min;
                $param["punt_max"] = $params->punt_max;
                $param["punt_sujerido"] = $params->punt_sujerido;
                $param["punt_aprobado"] = $params->punt_aprobado;
                
                $datos=(object)$param;
                
                $ret=PayrollData::addPuntajeMis($datos);    
                
                 if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $jResponse['data'] = $ret["msgerror"];
                   $code = "202"; 
                }
                $jResponse['success'] = true;
                             
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function updatePuntajeMis(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updatePuntajeMis($details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function deletePuntajeMis($id_psto_puntaje_mis){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deletePuntajeMis($id_psto_puntaje_mis);    
                
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
    //ayuda vivienda
    public  function listVivienda(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_anho  = $request->query('id_anho');
                $id_entidad    = $request->query('id_entidad');
                $id_area       = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = PayrollData::listVivienda($id_entidad,$id_anho,$id_area_padre,$id_area,$id_depto);  
                   
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
    public  function addVivienda(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));
                
                $param["id_anho"]  = $params->id_anho;
                $param["id_depto"] = $params->id_depto;
                $param["id_entidad"]  = $params->id_entidad;
                $param["id_persona"]  = $params->id_persona;
                $param["id_cargo"]  = $params->id_cargo;
                $param["imp_importe"] = $params->imp_importe;
        
                $datos=(object)$param;
                
                $ret=PayrollData::addVivienda($datos);    
                
                 if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                   $jResponse['success'] = false;
                   $jResponse['message'] = $ret["msgerror"];
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
    public  function updateVivienda(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->details;
                PayrollData::updateVivienda($details);    
                
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";                    
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
    public  function deleteVivienda($id_psto_vivienda){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                PayrollData::deleteVivienda($id_psto_vivienda);    
                
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
}
?>

