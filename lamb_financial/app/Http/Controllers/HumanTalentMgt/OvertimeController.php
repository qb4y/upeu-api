<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\OvertimeData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class OverTimeController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function addOvertimeRegister(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];   
            DB::beginTransaction();
//             dd($request);
            try{   
                $return  =  OvertimeData::addOvertimeRegister($id_user, $request);  
                  if ($return['nerror']==0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";  
//                       $jResponse['id_sobretiempo'] = $return['id_sobretiempo'];
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
  public function listRegisterOvertime(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $id_entidad = $request->id_entidad;
                    $id_depto = $request->id_depto;
                    $id_sedearea = $request->id_sedearea;
                    $trabajador = $request->trabajador;
                    $id_estado_sobretiempo = $request->id_estado_sobretiempo;
                    $per_page = $request->per_page;
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    if ($id_entidad and $id_depto) {
                    $data = OvertimeData::listRegisterOvertime($id_entidad, $id_depto, $id_sedearea,  $trabajador, $id_estado_sobretiempo, $per_page);  
                      } else {
                              $data = []; 
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
         public function updateRegisterOvertime($id_sobretiempo, Request $request) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
         
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $result = OvertimeData:: updateRegisterOvertime($id_sobretiempo, $request);
                    if ($result) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updating successfully";
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
        public function updateRefusedOvertime($id_sobretiempo, Request $request) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
         
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $result = OvertimeData:: updateRefusedOvertime($id_sobretiempo, $request);
                    if ($result) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was refused successfully";
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
