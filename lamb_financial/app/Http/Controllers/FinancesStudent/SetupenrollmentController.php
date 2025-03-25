<?php

namespace App\Http\Controllers\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\SetupenrollmentData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class SetupenrollmentController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public  function listTypeModality(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                
                $data = SetupenrollmentData::listTypeModality();                                
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
    public  function listPaymentPlan(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     

                $data = SetupenrollmentData::listPaymentPlan();                                
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
    public  function showPaymentPlan($id_planpago){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SetupenrollmentData::showPaymentPlan($id_planpago);                                
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
    public  function addPaymentPlan(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                                         
                $data = SetupenrollmentData::addPaymentPlan($params);                    
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
    public  function updatePaymentPlan($id_planpago){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                               
                         
                $data = SetupenrollmentData::updatePaymentPlan($id_planpago,$params);                    
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
    public  function deletePaymentPlan($id_planpago){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = SetupenrollmentData::deletePaymentPlan($id_planpago);                    
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
    
    //parametro configuración
   
    public  function listConfigParameter(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $nombre = $request->query('nombre');
                $data = SetupenrollmentData::listConfigParameter($nombre);                                
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
    public  function showConfigParameter($id_config_parametro){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SetupenrollmentData::showConfigParameter($id_config_parametro);                                
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
    public  function addConfigParameter(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                                         
                $data = SetupenrollmentData::addConfigParameter($params);                    
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
    public  function updateConfigParameter($id_config_parametro){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                               
                         
                $data = SetupenrollmentData::updateConfigParameter($id_config_parametro,$params);                    
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
    public  function deleteConfigParameter($id_config_parametro){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = SetupenrollmentData::deleteConfigParameter($id_config_parametro);                    
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
    //criterio de matricula
   
    public  function listEnrollmentCriterion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $nombre = $request->query('nombre');
                $id_modalidad = $request->query('id_modalidad');
                $data = SetupenrollmentData::listEnrollmentCriterion($id_modalidad,$nombre);                                
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
    public  function showEnrollmentCriterion($id_criterio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SetupenrollmentData::showEnrollmentCriterion($id_criterio);                                
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
    public  function addEnrollmentCriterion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                                         
                $data = SetupenrollmentData::addEnrollmentCriterion($params);                    
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
    public  function updateEnrollmentCriterion($id_criterio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                               
                         
                $data = SetupenrollmentData::updateEnrollmentCriterion($id_criterio,$params);                    
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
    public  function deleteEnrollmentCriterion($id_criterio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                                         
                $data = SetupenrollmentData::deleteEnrollmentCriterion($id_criterio);                    
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