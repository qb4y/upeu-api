<?php
namespace App\Http\Controllers\Process;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Process\ProcessData;
//use App\Http\Data\Modulo\ModuloData;
//use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class ProcessController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listProcess(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::listProcess($id_entidad,$id_depto);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function showProcess($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::showProcess($id_proceso);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function addProcess(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_modulo = $params->data->id_modulo;
            $id_tipotransaccion = $params->data->id_tipotransaccion;
            $nombre = $params->data->nombre;
            $estado = $params->data->estado;
            try{
                $data = ProcessData::addProcess($id_entidad,$id_depto,$id_modulo,$id_tipotransaccion,$nombre,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
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
    public function updateProcess($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_modulo = $params->data->id_modulo;
            $id_tipotransaccion = $params->data->id_tipotransaccion;
            $nombre = $params->data->nombre;
            $estado = $params->data->estado;
            try{
                $data = ProcessData::updateProcess($id_proceso,$id_modulo,$id_tipotransaccion,$nombre,$estado);
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
    public function deleteProcess($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ProcessData::deleteProcess($id_proceso);
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
    public function listProcessType(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::listProcessType();
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function listSteps($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::listSteps($id_proceso);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function showSteps($id_proceso,$id_paso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            $id_componente = "";
            $datas = ProcessData::showSteps($id_paso);
            $roles = ProcessData::listStepsRoles($id_paso);
            $component = ProcessData::showComponentsSteps($id_paso);
            foreach ($component as $item){
                $id_componente = $item->id_componente;
            }
            foreach ($datas as $key => $value){
                $data[] = ['id_paso' => $value->id_paso,
                            'id_proceso' => $value->id_proceso,
                            'id_tipopaso' => $value->id_tipopaso, 
                            'tipopaso' => $value->tipopaso,
                            'nombre' => $value->nombre,
                            'orden' => $value->orden,
                            'estado' => $value->estado,
                            'id_componente' => $value->id_componente,
                            'llave_componente' => $value->llave_componente,
                            'roles' => $roles,
                            'id_componente' => $id_componente
                        ];            
            }
            if ($datas) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function addSteps($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_tipopaso = $params->data->id_tipopaso;
            $nombre = $params->data->nombre;
            $orden = $params->data->orden;
            $estado = $params->data->estado;
            $roles = $params->data->roles;
            $id_componente = $params->data->id_componente;
            if($estado == false){
                $estado = "0";
            }else{
                $estado = "1";
            }
            try{
                $id_paso = 0;
                $data = ProcessData::addSteps($id_proceso,$id_tipopaso,$nombre,$orden,$estado);
                foreach ($data as $item){
                    $id_paso = $item->id_paso;
                }
                if($id_componente != ""){
                    ProcessData::addComponentsSteps($id_paso,$id_componente);
                }
                foreach ($roles as $item){
                    ProcessData::addStepsRoles($id_paso,$item->id_rol);  
                } 
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updateSteps($id_proceso,$id_paso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_tipopaso = $params->data->id_tipopaso;
            $nombre = $params->data->nombre;
            $orden = $params->data->orden;
            $estado = $params->data->estado;
            $roles = $params->data->roles;
            $id_componente = $params->data->id_componente;
            if($estado == false){
                $estado = "0";
            }else{
                $estado = "1";
            }
            try{
                $data = ProcessData::updateSteps($id_proceso,$id_paso,$id_tipopaso,$nombre,$orden,$estado);
                $component = ProcessData::showComponentsSteps($id_paso);
                if(count($component)>0){
                    ProcessData::deleteComponentsSteps($id_paso);
                    if($id_componente !== ""){
                        // ProcessData::deleteComponets($id_paso,$id_componente);
                        ProcessData::addComponentsSteps($id_paso,$id_componente);
                    }
                    // else{
                    // }
                }else{ 
                    if($id_componente !== ""){
                        ProcessData::addComponentsSteps($id_paso,$id_componente);
                    }
                }
                ProcessData::deleteStepsRoles($id_paso);
                foreach ($roles as $item){
                    ProcessData::addStepsRoles($id_paso,$item->id_rol);                   
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
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
    public function deleteSteps($id_proceso,$id_paso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                ProcessData::deleteComponentsSteps($id_paso);
                ProcessData::deleteStepsRoles($id_paso); 
                $data = ProcessData::deleteSteps($id_proceso,$id_paso);
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
    public function listFlows(Request $request,$id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_paso_de = $request->query('id_paso_de');
            $data = ProcessData::listFlows($id_proceso,$id_paso_de);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function addFlows($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_paso = $params->data->id_paso;
            $tag = $params->data->tag;
            $id_paso_next = $params->data->id_paso_next;
            $id_componente = $params->data->id_componente;
            try{
                ProcessData::addFlows($id_proceso,$id_paso,$tag,$id_paso_next,$id_componente);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updateFlows($id_proceso,$id_flujo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_paso = $params->data->id_paso;
            $tag = $params->data->tag;
            $id_paso_next = $params->data->id_paso_next;
            $id_componente = $params->data->id_componente;
            try{
                $data = ProcessData::updateFlows($id_proceso,$id_flujo,$id_paso,$tag,$id_paso_next,$id_componente);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteFlows($id_proceso,$id_flujo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ProcessData::deleteFlows($id_flujo);
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
    public function listComponents(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::listComponents();
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function showComponents($id_componente){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::showComponents($id_componente);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
    public function addComponents(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $llave = $params->data->llave;
            $estado = $params->data->estado;
            try{
                $data = ProcessData::addComponets($nombre,$llave,$estado);  
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updateComponents($id_componente){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $llave = $params->data->llave;
            $estado = $params->data->estado;
            try{
                $data = ProcessData::updateComponents($id_componente,$nombre,$llave,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-". $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteComponents($id_componente){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ProcessData::deleteComponets($id_componente);
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
    public function processRun(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $id_registro = 0;
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_proceso = $params->data->id_proceso;
                $id_operacion = $params->data->id_operacion;
                $data = ProcessData::showProcess($id_proceso);
                foreach ($data as $item){
                    $detalle = $item->nombre;                
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PROCESS.SP_PROCESO_RUN(:P_ID_PROCESO, :P_ID_OPERACION, :P_DETALLE, :P_ID_REGISTRO); end;");
                $stmt->bindParam(':P_ID_PROCESO', $id_proceso, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_OPERACION', $id_operacion, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_REGISTRO', $id_registro, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = $id_registro;
                $code = "200";  
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function processPasoRun(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_proceso = $params->data->id_proceso;
                $id_paso = $params->data->id_paso;
                $id_operacion = $params->data->id_operacion;
                $detalle = $params->data->detalle;
                /*$data = ProcessData::showSteps($id_paso);
                foreach ($data as $item){
                    $detalle = $item->nombre;                
                }*/
                $ip = GlobalMethods::ipClient($this->request);
                $id_registro = ProcessData::showProcessRun($id_proceso,$id_operacion);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PROCESS.SP_PROCESO_PASO_RUN(:P_ID_REGISTRO, :P_ID_PASO, :P_ID_PERSONA, :P_DETALLE, :P_NUMERO, :P_IP, :P_CLAVE, :P_VALOR); end;");
                $stmt->bindParam(':P_ID_REGISTRO', $id_registro, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PASO', $id_paso, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_STR);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_INT);
                $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':P_CLAVE', $id_proceso, PDO::PARAM_STR);
                $stmt->bindParam(':P_VALOR', $id_operacion, PDO::PARAM_STR);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = $id_registro;
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
    public function listStepsEjecutations($id_proceso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $datos = ProcessData::listStepsEjecucion($id_proceso,$id_entidad,$id_user);
            $lista = array();
            
            foreach($datos as $row){
                $data = array();
                $items = ProcessData::listStepsEvents($id_proceso,$row->id_paso);
                $data["id_proceso"]=$row->id_proceso;
                $data["id_paso"]=$row->id_paso;
                $data["nombre"]=$row->nombre;
                $data["orden"]=$row->orden;
                $data["nombre_proceso"]=$row->nombre_proceso;
                $data["llave_componente"]=$row->llave_componente;
                $data["tipo_paso"]=$row->tipo_paso;
                $data["init"]=$row->init;
                $data["dato_outputs"]=$items;
                $lista[]=$data;
            }
            if ($lista) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $lista;
                $code = "200";
            }else{
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showStepsEjecutations($id_proceso,$id_paso){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ProcessData::showStepsEjecucion($id_proceso,$id_paso,$id_entidad,$id_user);
            if ($data) {          
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
        }        
        return response()->json($jResponse,$code);
    }
}