<?php
namespace App\Http\Controllers\Budget;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\AuxiliarData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class AuxiliarController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function generarProcesoInicial(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre =$params->id_depto;
                $id_auxiliar = $params->id_auxiliar;
                $tipo = $params->tipo;
                AuxiliarData::generarProcesoInicial($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$tipo);  
                
                
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
    public  function listAuxiliar(Request $request){
         $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                        
                $id_entidad     = $request->query('id_entidad');
                $id_anho        = $request->query('id_anho');
                $id_depto       = $request->query('id_depto');
                $data = AuxiliarData::listAuxiliar($id_entidad,$id_anho,$id_depto);                                
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
    public  function addConcepto(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre = $params->id_depto;
                $id_auxiliar = $params->id_auxiliar;
                
                AuxiliarData::addConcepto($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);  
                
                
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
    public  function addProyeccion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre = substr($params->id_depto, 0, 1);
                $id_auxiliar = $params->id_auxiliar;
                $tipo=$params->tipo;
                
                if($tipo=="PR"){
                    AuxiliarData::addProesadProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
                }else{
                    AuxiliarData::addPregradoProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar); 
                }
                        
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
    public  function addPosgradoProyeccion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre = $params->id_depto_padre;
                $id_depto = $params->id_depto;
                $id_auxiliar = $params->id_auxiliar;
                $id_eap=$params->id_eap;
                
                
                $result=AuxiliarData::addPosgradoProyeccion($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$id_depto,$id_eap);
                
                if($result["error"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result["msgerror"];
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
    public  function addProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre = substr($params->id_depto, 0, 1);
                $id_auxiliar = $params->id_auxiliar;
                $tipo=$params->tipo;
                
                if($tipo=="PR"){
                    BudgetData::addProesadProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);
                }else{
                    BudgetData::addPregradoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar); 
                }
                        
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
     public  function addPresupuestoProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_depto_padre = substr($params->id_depto, 0, 1);
                $id_auxiliar = $params->id_auxiliar;
                
                BudgetData::addPresupuestoProceso($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar,$id_user);
  
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
    public  function listConceptoPregrado(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                
                        
                $id_entidad     = $request->query('id_entidad');
                $id_anho        = $request->query('id_anho');
                $id_depto_padre = $request->query('id_depto');
                $id_auxiliar    = $request->query('id_auxiliar');
                $data = AuxiliarData::listConceptoPregrado($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);  
                $data_estado = AuxiliarData::estadoPresupuesto($id_entidad,$id_anho,$id_depto_padre,$id_auxiliar);  
                
                $estado     = '-';
                $estadodesc = 'SIN PRESUPUESTO';
                
                foreach($data_estado as $row){
                    $estado     = $row->estado;
                    $estadodesc = 'CON PRESUPUESTO - '.$row->estadodesc;
                }
                
                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'state'=>$estado,'statedesc'=>$estadodesc];
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
    public  function updateConceptoPregrado(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $detail= $params->details;
                AuxiliarData::updateConceptoPregrado($detail);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function updatePregradoConceptoProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $detail= $params->details;
                AuxiliarData::updatePregradoConceptoProceso($detail);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    
    public function updatePregradoConceptoDescuento(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $detail= $params->details;
           
            try{
                AuxiliarData::updatePregradoConceptoDescuento($detail);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = [];
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
    public function updateConceptoPosgradoProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $detail= $params->details;
           
            try{
                AuxiliarData::updateConceptoPosgradoProceso($detail);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = [];
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
    public  function listPregradoProyeccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_area = $request->query('id_area');

                if(strlen($id_area)>0){
                    $data   = AuxiliarData::listPregradoProyeccion($id_entidad,$id_depto_padre,$id_anho,$id_area);

                }else{
                     $data = AuxiliarData::listProesadProyeccion($id_entidad,$id_depto_padre,$id_anho);
                }
                                               
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
    public  function listPosgradoProyeccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_area = $request->query('id_area');

                
                 $data   = AuxiliarData::listPosgradoProyeccion($id_entidad,$id_depto_padre,$id_anho,$id_area);

                                         
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
  
    public  function updatePregradoProyeccion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $detail= $params->details;
                $tipo="";
                if (isset($params->tipo)) {
                    $tipo=$params->tipo;
                }
                AuxiliarData::updatePregradoProyeccion($detail,$tipo);
                
                                           
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function updatePosgradoProyeccion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $detail= $params->details;
                
                AuxiliarData::updatePosgradoProyeccion($detail);
                
                                           
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function deletePosgradoProyeccion($id_proyeccion){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
              
                
                AuxiliarData::deletePosgradoProyeccion($id_proyeccion);
                
                                           
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function listPregradoProceso(Request $request ){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_area = $request->query('id_area');
                $pr="";
                if ($request->has('tipo')) {
                    $pr=$request->query('tipo');
                }
                switch ($pr) {
                    case "PR":
                        $data = AuxiliarData::listProesadProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);
                        break;
                    case "RE":
                        $data = AuxiliarData::listResidenciaProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);
                        break;
                    case "CS":
                        $data = AuxiliarData::listConservatorioProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);
                        break;
                    case "PG":
                        $data = AuxiliarData::listPosgradoProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);
                        break;
                    case "ID":
                        $data = AuxiliarData::listIdiomasProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);
                        break;
                    default:
                        $data = AuxiliarData::listPregradoProceso($id_entidad,$id_depto_padre,$id_anho,$id_auxiliar,$id_area);  
                }
                
                                              
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
     public  function updatePregradoProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));

                $detail= $params->details;
                $tipo="";
                if(isset($params->tipo)) {
                    $tipo=$params->tipo;
                }
                AuxiliarData::updatePregradoProceso($detail,$tipo);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
     public  function listPregradoProcesoConcepto($id_pregado_proceso,$tipo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = AuxiliarData::listPregradoProcesoConcepto($id_pregado_proceso,$tipo);                                
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
     
    /*public  function listResidenciaProceso(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto_padre');
                $id_anho = $request->query('id_anho');
                $data = AuxiliarData::listResidenciaProceso($id_entidad,$id_depto_padre,$id_anho);                                
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
    }*/
    public  function updateResidenciaProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $detail= $params->details;
                AuxiliarData::updateResidenciaProceso($detail);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function listConservatorioProceso(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto_padre');
                $id_anho = $request->query('id_anho');
                $data = AuxiliarData::listConservatorioProceso($id_entidad,$id_depto_padre,$id_anho);                                
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
    public  function updateConservatorioProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                //$id_conservatorio_proceso  = $params->$id_conservatorio_proceso;
                $detail= $params->details;
                AuxiliarData::updateConservatorioProceso($detail);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public  function updateIdiomasProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                //$id_conservatorio_proceso  = $params->$id_conservatorio_proceso;
                $detail= $params->details;
                AuxiliarData::updateIdiomasProceso($detail);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    
    public  function listTesisProceso(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto_padre');
                $id_anho = $request->query('id_anho');
                $data = AuxiliarData::listTesisProceso($id_entidad,$id_depto_padre,$id_anho);                                
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
    public  function listTesisEapDeptoConcepto(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto_padre');
                $id_anho = $request->query('id_anho');
                $id_eap_depto = $request->query('id_eap_depto');
                $data = AuxiliarData::listTesisEapDeptoConcepto($id_entidad,$id_depto_padre,$id_anho,$id_eap_depto);                                
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
    public  function updateTesisProceso(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_tesis_proceso  = $params->id_tesis_proceso;
                $costo     = $params->costo;
                $egresado   = $params->egresado;
                $porcentaje     = $params->porcentaje;
                $porccosto   = $params->porccosto;

                BudgetData::updateTesisProceso($id_tesis_proceso,$costo,$egresado,$porcentaje,$porccosto);
                              
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $data = AuxiliarData::listPlanilla($id_entidad,$id_depto_padre,$id_anho);                                
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
    public  function presupuestoPlanilla(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;
                $id_anho = $params->id_anho;
                $id_depto_padre = $params->id_depto;
                $id_auxiliar = $params->id_auxiliar;
                $id_pstonegocio=5;
                $id_eje=1;

                $return=AuxiliarData::presupuestoPlanilla($id_entidad,$id_depto_padre,$id_anho,$id_persona,$id_pstonegocio,$id_eje,$id_auxiliar);    
                
                if($return["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return["msgerror"];
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
    public  function listConsolPlanilla(Request $request){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad     = $request->query('id_entidad');
                $id_depto_padre = $request->query('id_depto_padre');
                $id_anho = $request->query('id_anho');
                $id_area_padre = $request->query('id_area_padre');
                $id_area = $request->query('id_area');
                $id_depto = $request->query('id_depto');

                $data = AuxiliarData::listConsolPlanilla($id_entidad,$id_depto_padre,$id_anho,$id_area_padre,$id_area,$id_depto);                                
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
