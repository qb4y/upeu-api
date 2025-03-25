<?php
namespace App\Http\Controllers\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Setup\RolData;
use App\Http\Data\Modulo\ModuloData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Carbon\Carbon;
class RolController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listRoles(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            $roles = RolData::listRoles();
            if ($roles) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $roles;
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
    public function showRoles($id_rol){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $rol = RolData::showRoles($id_rol);
            if ($rol) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $rol[0];
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
    public function addRoles(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $estado = $params->data->estado;
            try{
                $data = RolData::addRoles($nombre,$estado);
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
    public function updateRoles($id_rol){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $estado = $params->data->estado;
            try{
                $data = RolData::updateRoles($id_rol,$nombre,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
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
    public function deleteRoles($id_rol){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = RolData::deleteRoles($id_rol);
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
    public function listRolesModules($id_rol, $id_modulo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            //$id_rol = $request->query('id_rol');
            //$opc = $request->query('opc');
            $data = RolController::recursiveRolesModules($id_modulo,$id_rol,1);
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
    public function recursiveRolesModules($id_modulo,$id_rol,$x){
        $parent = [];
        /*if($x == 1){
            $roles = ModuloData::get_modulo();
        }else{*/
        $roles = RolData::listRolesModules($id_modulo,$id_rol);
        //}      
        $x++;
        foreach ($roles as $key => $value){
            $row = $this->recursiveRolesModules($value->id_modulo,$id_rol,$x); 
            if($value->asignado == 1){
                $chek = true;
            }else{
                $chek = false;
            }
            
            $parent[] = ['id_modulo' => $value->id_modulo,
                'id_padre' => $value->id_padre,
                'nivel' => $value->nivel, 
                'label' => $value->nombre,
                'checked' => $chek,
                'children'=>$row];            
        }
        return $parent;
    }
    public function recursiveRolesModulesBK($id_modulo,$id_rol,$opc,$x){
        $parent = [];
        if($x == 1){
            $roles = ModuloData::get_modulo();
        }else{
            if($opc == "1"){
                $roles = RolData::listRolesModulesAsingados($id_modulo,$id_rol);
            }else{
                $roles = RolData::listRolesNoAsigandos($id_modulo,$id_rol);
            }
        }      
        $x++;
        foreach ($roles as $key => $value){
            $row = $this->recursiveRolesModules($value->id_modulo,$id_rol,$opc,$x); 
            $parent[] = ['id_modulo' => $value->id_modulo,
                'id_padre' => $value->id_padre,
                'nivel' => $value->nivel, 
                'label' => $value->nombre,
                'children'=>$row];            
        }
        return $parent;
    }
    public function addRolModulo($id_rol){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $params = json_decode(file_get_contents("php://input"));
            $modulo = $params->data;
            try{
                foreach ($modulo as $item){
                    if($item->add == false){
                        RolData::deleteRolesModules($id_rol,$item->id_modulo);
                    }else{
                        /*$row = RolData::showRolesModules($id_rol,$item->id_modulo,10);
                        if(count($row) == 0){
                            RolData::addRolesModulesActions($id_rol,$item->id_modulo,10);
                        }*/
                        RolData::addRolesModules($id_rol,$item->id_modulo); 
                    }
                }           
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
    public function listRolesModulesActions($id_rol,$id_modulo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            try{
                $jResponse=[];
                $actions = [];
                $data = RolData::listRolesModulesActions($id_rol,$id_modulo);   
                foreach ($data as $key => $value){
                    if($value->cant == 1){
                        $chek = true;
                    }else{
                        $chek = false;
                    }
                    $actions[] = ['id_accion' => $value->id_accion,
                                'id_modulo' => $value->id_modulo,
                                'nombre' => $value->nombre, 
                                'clave' => $value->clave,
                                'valor' => $value->valor,
                                'estado'=>$value->estado,
                                'checked'=>$chek
                            ];
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item";
                $jResponse['data'] = $actions;
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
    public function addRolesModulesActions($id_rol,$id_modulo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $params = json_decode(file_get_contents("php://input"));
            $actions = $params->data;
            try{
                foreach ($actions as $item){
                    if($item->add == false){
                        RolData::deleteRolesModulesActions($id_rol,$id_modulo,$item->id_accion);
                    }else{
                        RolData::addRolesModulesActions($id_rol,$id_modulo,$item->id_accion);  
                    }
                }           
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
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
    public function listResources(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            $resourdes = RolData::listResources();
            if ($resourdes) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $resourdes;
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
    public function showResources($id_resource){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];
            $resourdes = RolData::showResources($id_resource);
            if ($resourdes) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $resourdes;
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
    public function addResources(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $ruta = $params->data->ruta;
            $detalle = $params->data->detalle;
            $estado = $params->data->estado;
            try{
                $data = RolData::addResources($nombre,$ruta,$detalle,$estado);
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
    public function updateResources($id_resource){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $ruta = $params->data->ruta;
            $detalle = $params->data->detalle;
            $estado = $params->data->estado;
            try{
                $data = RolData::updateResources($id_resource,$nombre,$ruta,$detalle,$estado);
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
    public function deleteResources($id_resource){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = RolData::deleteResources($id_resource);
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
    public function lisTbecas(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = RolData::lisTbecas($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function becaRol(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = RolData::becaRol($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function deleteBecaRol($id_tipo_requisito_beca, $id_rol){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = RolData::deleteBecaRol($id_tipo_requisito_beca, $id_rol);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addBecaRol(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $date = Carbon::now();
        $fecha_register = $date->format('Y/m/d H:m:s');
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = RolData::addBecaRol($request, $fecha_register);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
     }
     public function updateBecaRol($id_rol, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = RolData::updateBecaRol($id_rol, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
     }
}