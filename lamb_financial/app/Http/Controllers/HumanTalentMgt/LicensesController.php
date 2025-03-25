<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\LicensesData;
// use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class LicensesController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }


public function listTypeSuspension(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $per_page = $request->per_page;
                    $nombre = $request->nombre;
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    $data = LicensesData::listTypeSuspension($nombre, $per_page);  
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
        public function showTypeSuspension($id_tipo_suspension){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $data = LicensesData::showTypeSuspension($id_tipo_suspension);  
                    if($data){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = ['object' => $data];
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
        public function addTypeSuspension(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = LicensesData::addTypeSuspension($request);  
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
        public function updateTypeSuspension($id_tipo_suspension,Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = LicensesData::updateTypeSuspension($id_tipo_suspension,$request);  
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
        public function deleteTypeSuspension($id_tipo_suspension) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $result = LicensesData::deleteTypeSuspension($id_tipo_suspension);
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
                        $jResponse['message'] = 'la suspension se encuentra relacionada';
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
        
        public  function addRegisterLisensesPermits(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if($valida=='SI'){
                $jResponse=[];   
                DB::beginTransaction();
                try{   
                    $return  =  LicensesData::addRegisterLisensesPermits($id_user, $request);  
                      if ($return['nerror']==0) {
                          $jResponse['success'] = true;
                          $jResponse['message'] = "The item was created successfully";  
                          $jResponse['id_licencia_permiso'] = $return['id_licencia_permiso'];
                          $jResponse['data'] = [];
                          $code = "200";  
                          DB::commit();
                      } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $return['msgerror'];
                        $jResponse['data'] = [];
                        $code = "202";
                        DB::rollback();
                      }
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                } 
                
            }        
            return response()->json($jResponse,$code);
        }
        public function listRegisterLisensesPermits(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $id_entidad = $request->id_entidad;
                    $id_depto = $request->id_depto;
                    $id_tipo_suspension = $request->id_tipo_suspension;
                    $id_sedearea = $request->id_sedearea;
                    $trabajador = $request->trabajador;
                    $id_estado_lica_per = $request->id_estado_lica_per;
                    $per_page = $request->per_page;
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    $data = LicensesData::listRegisterLisensesPermits($id_entidad, $id_depto, $id_tipo_suspension, $id_sedearea,  $trabajador, $id_estado_lica_per, $per_page);  
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
        public function updateRegisterLisensesPermits($id_licencia_permiso, Request $request) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $result = LicensesData:: updateRegisterLisensesPermits($id_licencia_permiso,$id_user, $request);
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
}
