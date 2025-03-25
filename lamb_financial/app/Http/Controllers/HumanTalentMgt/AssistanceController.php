<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\AssistanceData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use Excel;
use Session;    

class AssistanceController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
   public function addTypeShedule(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = AssistanceData::addTypeShedule($request); 
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

     public  function listTypeShedule(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $id_entidad = $request->id_entidad;
                    $id_depto = $request->id_depto;
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    if ($id_entidad and $id_depto) {
                    $data = AssistanceData::listTypeShedule($id_entidad, $id_depto);  
                      } else {
                              $data = []; 
                      }
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
        public  function listTypeSheduleDetails($id_tipo_horario){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    if ($id_tipo_horario) {
                    $data = AssistanceData::listTypeSheduleDetails($id_tipo_horario);  
                      } else {
                              $data = []; 
                      }
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
           public function addTypeSheduleDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = AssistanceData::addTypeSheduleDetails($request); 
                
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
    public function deleteTypeSheduleDetails($id_dias) {
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
 
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $result = AssistanceData::deleteTypeSheduleDetails($id_dias);
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
////////////// Recibe validacion de la data
    public function deleteTypeShedule($id_tipo_horario) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $response = AssistanceData::deleteTypeShedule($id_tipo_horario);
                    if ($response['success']) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";         
                        $code = "200";
                    } else {
                      $jResponse['success'] = false;
                      $jResponse['message'] = $response['message'];                        
                      $jResponse['data'] = [];
                      $code = "202";
                    }
                } catch(Exception $e){
                      $jResponse['success'] = false;
                      $jResponse['message'] = $e->getMessage();
                      $code = "202";
                          } 
            }
            return response()->json($jResponse,$code);
        }
/////////////////////////////////////////////////////////
      public function showTypeShedule($id_tipo_horario){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    // dd($id_tipo_horario);
                    $showTypeShedule = AssistanceData::showTypeShedule($id_tipo_horario);    
                    if(!empty($showTypeShedule)){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] = ['object' => $showTypeShedule];
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
    public function updateTypeShedule($id_tipo_horario,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                AssistanceData::updateTypeShedule($id_tipo_horario,$request);  
               
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
    public function listTypeSheduleDetailsShow(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                // dd(Request $request);
                $listDetailsShow= AssistanceData::listTypeSheduleDetailsShow($request);    
                if(!empty($listDetailsShow)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['object' => $listDetailsShow];
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
public function updateTypeSheduleDetails($id_dias,Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    
    if($valida=='SI'){
        $jResponse=[];                        
        try{   
            AssistanceData::updateTypeSheduleDetails($id_dias,$request);  
           
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

    public  function listControlAssist(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_entidad = $request->id_entidad;
                $id_persona = $request->id_persona;
                $id_anho    = $request->id_anho;
                $id_mes     = $request->id_mes;
                
                $data = AssistanceData::listControlAssist($id_entidad, $id_persona, $id_anho, $id_mes);  
                $sumdata = AssistanceData::sumControlAssist($id_entidad, $id_persona, $id_anho, $id_mes);
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'sumdata' => $sumdata];
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

    public  function listControlAssistShow($id_asistencia){
      
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                // dd($id_asistencia);
                if ($id_asistencia) {
                $data = AssistanceData::listControlAssistShow($id_asistencia); 
                // dd($data); 
                  } else {
                          $data = []; 
                  }
                  if($data){
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
    public  function listManualMarcation($id_asistencia){
      
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                // dd($id_asistencia);
                
                $data = AssistanceData::listManualMarcation($id_asistencia); 
                // dd($data); 
            
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
    public function updateControlAssist($id_asistencia,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                AssistanceData::updateControlAssist($id_asistencia, $request,$id_user);  
               
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
    
    public function copyManualMarcation(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $desde = $request->desde;
                $hasta = $request->hasta;
                $id_persona = $request->id_persona;
                AssistanceData::copyManualMarcation($desde,$hasta,$id_persona);  
               
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
    public function listTrabajadorAsistenceControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                $id_entidad =$request ->id_entidad;
                $id_depto = $request->id_depto;
                $id_sedearea = $request->id_sedearea;
                $persona = $request->persona;
                $per_page = $request->per_page;
                $data = AssistanceData::listTrabajadorAsistenceControl($id_entidad, $id_depto, $id_sedearea, $persona, $per_page);  
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
}
