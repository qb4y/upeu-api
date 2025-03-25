<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\ConfigurationData;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

use Session;    
use Illuminate\Support\Facades\DB;
class ConfigurationController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    //grupo planilla
    public function ListScaleGroups(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $nombre = $request->nombre;
                $id_nivel = $request->id_nivel;
                $perpage= $request->perpage;
                $data = ConfigurationData::ListScaleGroups($id_nivel,$nombre,$perpage);  
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
    public function ShowScaleGroup($id_escala_group){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $scalegroup = ConfigurationData::ShowScaleGroup($id_escala_group);  
                if(!empty($scalegroup)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['object' => $scalegroup];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function AddScaleGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::AddScaleGroup($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function UpdateScaleGroup($id_scale_group,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::UpdateScaleGroup($id_scale_group,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
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
    public function DeleteScaleGroup($id_scale_group){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::DeleteScaleGroup($id_scale_group);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
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
    
    //puesto
    public function ListPositions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $per_page = $request->per_page;
                $nombre = $request->nombre;
                // dd('SSS')
                $data = ConfigurationData::ListPositions($nombre, $per_page);  
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
    public function ShowPosition($id_puesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                // dd('sss');
                $position = ConfigurationData::ShowPosition($id_puesto);  
                    //    dd('sss', $position);
                if(!empty($position)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['object' => $position];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function AddPosition(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::AddPosition($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function UpdatePosition($id_puesto,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::UpdatePosition($id_puesto,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
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
    public function DeletePosition($id_puesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::DeletePosition($id_puesto);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
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
    
        //puesto
    public function ListParameters(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        $valida ='SI';
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_entidad = $request->id_entidad;
                $id_anho = $request->id_anho;
                $per_page = $request->per_page;
                // dd('SSS')
                $data = ConfigurationData::ListParameters($id_entidad, $id_anho, $per_page);  
                // dd( $data->parameter->nombre);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
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
    public  function procParametro(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $params = json_decode(file_get_contents("php://input"));        
                $id_anho = $params->id_anho;
                $id_entidad = $params->id_entidad;
                ConfigurationData::procParametro($id_anho, $id_entidad);    
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
    public function updateParametro(Request $request, $id_parametro_valor){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $importe = $request->importe;
                $data = [
                    "importe" => $importe
                ];
                $result = ConfigurationData::updateParametro($data, $id_parametro_valor);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No se actualizo'; // $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function AddProfilePosition(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::AddProfilePosition($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function ListProfilePositions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $data = ConfigurationData::ListProfilePositions($request);  
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

    public function DeleteProfilePositions($id_perfil_puesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::DeleteProfilePositions($id_perfil_puesto);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
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
    public function ShowListProfilePositions($id_perfil_puesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                // dd($id_perfil_puesto);
                $showperfil = ConfigurationData::ShowListProfilePositions($id_perfil_puesto);  
                $controlPerson = ParameterData::getControlsPersons();
                $ubication = ParameterData::getUbicationFADM();  
                $autonomia = ParameterData::getAutonomyPosition($id_perfil_puesto);
                $responsability = ParameterData::getResponsability($id_perfil_puesto);
                $nivelResponsability = ParameterData::getLevelResponsability();
                $supervisaa = ParameterData::listSupervisionTo($id_perfil_puesto);  
                if(!empty($showperfil)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = [
                        'object' => $showperfil,
                        'controlPerson' => $controlPerson,
                        'ubication' => $ubication,
                        'autonomia' => $autonomia,
                        'responsability' => $responsability,
                        'nivelResponsability' => $nivelResponsability,
                        'supervisaa' => $supervisaa,
                    ];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function ListStaffPositions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
            
                $data = ConfigurationData::ListStaffPositions($request);  
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
    public function SaveAfterStaffPosition(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
             ConfigurationData::SaveAfterStaffPosition($request);  
                $jResponse['success'] = true;
                // if(count($data)>0){
                    $jResponse['message'] = "Success";                    
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

    public function saveProfilePositionDatos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::saveProfilePositionDatos($request);  
           
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addResponsabilityes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addResponsabilityes($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function updateResponsability($perfil_puesto_resp,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateResponsability($perfil_puesto_resp,$request);  
               
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteResponsabilityes($perfil_puesto_resp) {
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
 
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $result = ConfigurationData::deleteResponsabilityes($perfil_puesto_resp);
            if ($result) {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $message;
                $jResponse['data'] = [];
                $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
    }
    return response()->json($jResponse, $code);
}

    public function addFuntions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addFuntions($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function updateFuntions($perfil_puesto_func,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateFuntions($perfil_puesto_func,$request);  
               
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteFuntions($perfil_puesto_func) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData:: deleteFuntions($perfil_puesto_func);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function ListResponsFuncion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $responsabilities = ConfigurationData::listResponsabilityes($id_perfil_puesto);  
                $funciones = ConfigurationData::listFuntions($id_perfil_puesto);
                $jResponse['success'] = true;
                if(!empty($responsabilities)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = [
                        'responsabilities' => $responsabilities,
                        'funciones' => $funciones,
                    ];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateSituationEducation($id_perfil_puesto,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateSituationEducation($id_perfil_puesto,$request);  
               
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfullyssfdfs";                    
                    $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addDiplomations(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addDiplomations($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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

    public function listDiplomations(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $diplomations = ConfigurationData::listDiplomations($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($diplomations)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['diplomations' => $diplomations];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteDiplomations($perfil_puesto_espdip) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteDiplomations($perfil_puesto_espdip);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addProfesionOcupation(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addProfesionOcupation($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listProfesionOcupation(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $profesionesOc = ConfigurationData::listProfesionOcupation($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($profesionesOc)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['profesionesOc' => $profesionesOc];
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deletProfesionOcupation($id_perfil_puesto_prof) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deletProfesionOcupation($id_perfil_puesto_prof);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateExperence($id_perfil_puesto,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateExperence($id_perfil_puesto,$request);  
               
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function addLenguagesLevel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addLenguagesLevel($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listLenguagesLevel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listLenguagesLevel($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteLenguagesLevel($id_tipoidioma) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteLenguagesLevel($id_tipoidioma);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addOffimaticaLevel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addOffimaticaLevel($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listOffimaticaLevel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listOffimaticaLevel($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteOffimaticaLevel($id_conoci_inform) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteOffimaticaLevel($id_conoci_inform);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addRequiremnts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addRequiremnts($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listRequiremnts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listRequiremnts($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteRequiremnts($id_requisitos) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteRequiremnts($id_requisitos);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listTrabajadorHolidays(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                
                $data = ParameterData::listTrabajadorHolidays($request);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addGroupCompetences(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addGroupCompetences($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listGroupCompetences(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                // $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listGroupCompetences();  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteGroupCompetences($id_grupo_compentencia) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteGroupCompetences($id_grupo_compentencia);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateGroupCompetences($id_grupo_compentencia,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateGroupCompetences($id_grupo_compentencia,$request);  
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addCompetencesGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addCompetencesGroup($request);  
                // dd($request);
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listCompetencesGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_grupo_compentencia = $request->id_grupo_compentencia;
                $data = ConfigurationData::listCompetencesGroup($id_grupo_compentencia);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function deleteCompetencesGroup($id_grupo_compentencia) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteCompetencesGroup($id_grupo_compentencia);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateCompetencesGroup($id_grupo_compentencia_nivel,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::updateCompetencesGroup($id_grupo_compentencia_nivel,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = 'on';//$e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addCompetenciasLb(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addCompetenciasLb($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function deleteCompetenciasLb($id_competencia) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // dd('dd', $id_competencia);
                $result = ConfigurationData::deleteCompetenciasLb($id_competencia);

            
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
                
            } catch (Exception $e) {
                $error = $e->getcode();
                if($error == '2292') {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'la competencia se encuentra relacionada en el grupo de competencias';
                    $jResponse['data'] = [];
                    $code = "202";
                } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateCompetenciasLb($id_grupo_compentencia,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                ConfigurationData::updateCompetenciasLb($id_grupo_compentencia,$request);  
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addComitions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::addComitions($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listComitions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listComitions($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteComitions($id_perfil_puesto_comis_dir) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteComitions($id_perfil_puesto_comis_dir);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saveProcess(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::saveProcess($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listProcess(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listProcess($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteProcess($id_perfil_puesto_proc) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteProcess($id_perfil_puesto_proc);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saveJefeFun(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::saveJefeFun($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listJefeFun(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listJefeFun($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteJefeFun($id_perfil_puesto_jefe_fun) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteJefeFun($id_perfil_puesto_jefe_fun);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saveSuperFunc(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConfigurationData::saveSuperFunc($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
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
    public function listSuperFunc(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_perfil_puesto = $request->id_perfil_puesto;
                $data = ConfigurationData::listSuperFunc($id_perfil_puesto);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteSuperFunc($id_perfil_puesto_sup_fun) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ConfigurationData::deleteSuperFunc($id_perfil_puesto_sup_fun);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showTrabajadorHolidays(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_entidad =$request ->id_entidad;
                $id_depto = $request->id_depto;
                $id_periodo_vac = $request->id_periodo_vac;
                $id_persona = $request->id_persona;
                $data = ParameterData::showTrabajadorHolidays($id_entidad, $id_depto, $id_periodo_vac, $id_persona);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    // dd($data);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] =$data;
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
                $code = "202";
            } 
        }  
        return response()->json($jResponse, $code);
}
        public function saveOrUpdateNivelesAcceso(Request $request){
           $jResponse = GlobalMethods::authorizationLamb($this->request);
           $code   = $jResponse["code"];
           $valida = $jResponse["valida"];
        
           if($valida=='SI'){
               $jResponse=[];    
               DB::beginTransaction();                      
               try{   
                   $response = ConfigurationData::saveOrUpdateNivelesAcceso($request);  
                   if($response['success']){
                       $jResponse['success'] = true;
                       $jResponse['message'] = $response['message'];                    
                       $code = "200";
                       DB::commit();  
                   }else{
                       $jResponse['success'] = false;
                       $jResponse['message'] = $response['message'];                        
                       $jResponse['data'] = [];
                       $code = "202";
                       DB::rollback();
                   }
               }catch(Exception $e){
                   $jResponse['success'] = false;
                   $jResponse['message'] = $e->getMessage();
                   $code = "202";
                   DB::rollback();
               } 
           }        
           return response()->json($jResponse,$code);
}
        public function deleteNivelesAcceso($id_acceso_nivel){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
        
            if($valida=='SI'){
                $jResponse=[];                      
                try{   
                    $response = ConfigurationData::deleteNivelesAcceso($id_acceso_nivel);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = $response['message'];                    
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
        public function moduloList(Request $request) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $data = ConfigurationData::moduloList($request);
                    if (count($data) > 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            return response()->json($jResponse, $code);
        }
        public function showModuleList($id_acceso_nivel) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $object = ConfigurationData::showModuleList($id_acceso_nivel);
                    if ($object) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $object;
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = '';
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }
            return response()->json($jResponse, $code);
        }
        public function saveDetailsAccesoNivel(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
         
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = ConfigurationData::saveDetailsAccesoNivel($request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = $response['message'];                    
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
            public function listAccesoNivelDetalle($id_acceso_nivel) {
               $jResponse = GlobalMethods::authorizationLamb($this->request);
               $code   = $jResponse["code"];
               $valida = $jResponse["valida"];
               if ($valida == 'SI') {
                   $jResponse = [];
                   try {
                
                       $data = ConfigurationData::listAccesoNivelDetalle($id_acceso_nivel);
                       if (count($data) > 0) {
                           $jResponse['success'] = true;
                           $jResponse['message'] = "Succes";
                           $jResponse['data'] = $data;
                           $code = "200";
                       } else {
                           $jResponse['success'] = false;
                           $jResponse['message'] = "The item does not exist";
                           $jResponse['data'] = [];
                           $code = "202";
                       }
                   } catch (Exception $e) {
                       $jResponse['success'] = false;
                       $jResponse['message'] = $e->getMessage();
                       $jResponse['data'] = [];
                       $code = "202";
                   }
               }
               return response()->json($jResponse, $code);
               }
        public function deleteDetalleAccesLevel($id_acceso_nivel){
                $jResponse = GlobalMethods::authorizationLamb($this->request);
                $code   = $jResponse["code"];
                $valida = $jResponse["valida"];
            
                if($valida=='SI'){
                    $jResponse=[];                      
                    try{   
                        $response = ConfigurationData::deleteDetalleAccesLevel($id_acceso_nivel);  
                        if($response['success']){
                            $jResponse['success'] = true;
                            $jResponse['message'] = $response['message'];                    
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
            public function updateDetalisAcceso($id_acceso_nivel_det, Request $request){
                $jResponse = GlobalMethods::authorizationLamb($this->request);
                $code   = $jResponse["code"];
                $valida = $jResponse["valida"];
             
                if($valida=='SI'){
                    $jResponse=[];                        
                    try{   
                        $response = ConfigurationData::updateDetalisAcceso($id_acceso_nivel_det, $request);  
                        if($response['success']){
                            $jResponse['success'] = true;
                            $jResponse['message'] = $response['message'];                    
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

