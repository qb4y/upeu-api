<?php
namespace App\Http\Controllers\Budget;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\BudgetData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;

use PDO;

class BudgetController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function listActivityEvento(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_evento = $request->query('id_evento');
                $id_presupuesto = $request->query('id_presupuesto');
                $id_proyecto=$request->query('id_proyecto');
                $msg  = BudgetData::validateBudget($id_evento);
                $data = BudgetData::listActivityEvento($id_proyecto,$id_evento,$id_presupuesto);  
                
                
                
                $jResponse['success'] = true;
                if(count($data)>0){
                                        
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'nerror'=>$msg["nerror"],'merror'=>$msg["merror"]];
                    //$jResponse['nerror'] = $msg["nerror"];
                    //$jResponse['merror'] = $msg["merror"];
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
    public  function listBudget(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_area = $request->query('id_area');
                $id_evento = $request->query('id_evento');
                $id_proyecto = $request->query('id_proyecto');
                $id_pstonegocio = $request->query('id_pstonegocio');
                $id_eje = $request->query('id_eje');
                $estado = $request->query('estado');
                $id_depto= $request->query('id_depto');
                $id_area_padre= $request->query('id_area_padre');
                $id_depto_padre= $request->query('id_depto_padre');
                $data = BudgetData::listBudget($id_entidad,$id_depto_padre,$id_anho,$id_area_padre,$id_area,$id_depto,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado);                                
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
    public  function showBudget($id_presupuesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = BudgetData::showBudget($id_presupuesto);                                
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
    public  function addBudget(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();
            try{    
                $params = json_decode(file_get_contents("php://input"));        
                $id_entidad = $params->id_entidad;  
                $id_anho = $params->id_anho;
                $id_area =0;//$params->id_area;
                $id_evento = $params->id_evento;
                $id_pstonegocio = $params->id_pstonegocio;
                $id_eje = $params->id_eje;
                $id_depto = $params->id_depto;
                $detail= $params->details;
                $descripcion = $params->descripcion;
                //$estado = $params->estado;
                $ret=BudgetData::validGenBudget($id_evento,$detail) ;  
                
                if($ret["nerror"]==0){
                    $data = BudgetData::addBudget($id_entidad,$id_anho,$id_area,$id_evento,$id_pstonegocio,$id_eje,$id_depto,$id_user,$descripcion);  

                    if(count($data)==0){
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'No se ha generado presupuesto';
                        $jResponse['data'] = [];
                        $code = "202";
                        DB::rollback();
                    }else{
                        $id_presupuesto=0;

                        foreach($data as $row){
                            $id_presupuesto=$row->id_presupuesto;
                        }

                        BudgetData::deleteBudgetDetail($id_presupuesto);

                        BudgetData::addBudgetDetail($id_presupuesto,$id_entidad,$detail);

                        BudgetData::addPresupuestoAsieto($id_presupuesto,$id_user);

                        $id_presupuesto=0;

                        foreach($data as $row){
                            $id_presupuesto=$row->id_presupuesto;
                        }

                        $ingreso=0;
                        $gasto=0;
                        $resultado=0;
                        if($id_presupuesto>0){
                            $data_total = BudgetData::listTotalPresupuesto($id_presupuesto); 

                            foreach($data_total as $row){
                                $ingreso=$row->ingreso;
                                $gasto=$row->gasto;
                                $resultado=$row->resultado;
                            }
                        }

                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
                        $jResponse['data'] = ['items' => $data,'id_presupuesto'=>$id_presupuesto,'ingreso'=>$ingreso,'gasto'=>$gasto,'resultado'=>$resultado];
                        $code = "200";
                        DB::commit();
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret["msgerror"];
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
    public  function updateBudget($id_presupuesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];  
            DB::beginTransaction();
            try{    
                
                $params = json_decode(file_get_contents("php://input"));        
                $id_area = $params->id_area;
                $id_pstonegocio = $params->id_pstonegocio;
                $id_eje = $params->id_eje;
                $id_depto = $params->id_depto;
                $descripcion = $params->descripcion;
                $estado = $params->estado;
                
                $msg = BudgetData::validBudget($id_presupuesto);
                
                if($msg=="0"){
                    $data = BudgetData::updateBudget($id_area,$id_pstonegocio,$id_eje,$id_depto,$descripcion,$estado);  

                    if(count($data)==0){
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'No se ha generado presupuesto';
                        $jResponse['data'] = [];
                        $code = "202";
                        DB::rollback();
                    }else{

                        BudgetData::deleteBudgetDetail($id_presupuesto);

                        $id_actividads=$params->id_actividad;
                        $id_deptos=$params->id_depto;
                        $id_depto_asientos=$params->id_depto_asiento;
                        $descripcions=$params->descripcion;
                        $cantidads=$params->cantidad;
                        $punidads=$params->punidad;
                        $as=$params->a;
                        $bs=$params->b;
                        $cs=$params->c;
                        $ds=$params->d;
                        $es=$params->e;
                        $fs=$params->f;
                        $gs=$params->g;

                        $k=0;
                        foreach($id_actividads as $id_actividad){

                            BudgetData::addBudgetDetail($id_presupuesto,$id_actividad,$id_entidad,$id_deptos[$k],$id_depto_asientos[$k],$descripcions[$k],$cantidads[$k],$punidads[$k],$as[$k],$bs[$k],$cs[$k],$ds[$k],$es[$k],$fs[$k],$gs[$k]);
                            $k++;
                        }
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was update successfully";
                        $jResponse['data'] = $data;
                        $code = "200";
                        DB::commit();
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No se ha generado presupuesto';
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $msg;
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function estadoBudget($id_presupuesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];  
            try{    
                
                $params = json_decode(file_get_contents("php://input"));        
                $estado = $params->estado;
                $motivo = $params->motivo;
                              
                BudgetData::estadoBudget($id_presupuesto,$estado,$motivo,$id_user);  

                    
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
    public  function deleteBudget($id_presupuesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"]; 
        
        if($valida=='SI'){
            $jResponse=[]; 
            
            DB::beginTransaction(); 
            try{    
                                        
                BudgetData::deleteBudget($id_presupuesto,$id_user);                    
                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
                DB::commit();
                
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
    public  function listBudgetDetail(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                
                $id_presupuesto = $request->query('id_presupuesto');
                $data = BudgetData::listBudgetDetail($id_presupuesto);                                
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
    public  function listBudgetDetailRep(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                
                $id_presupuesto = $request->query('id_presupuesto');
                $id_area = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = BudgetData::listBudgetDetailRep($id_presupuesto,$id_area,$id_area_padre,$id_depto);                                
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
    public  function listBudgetDetailTotal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                
                $id_presupuesto = $request->query('id_presupuesto');
                $id_area = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = BudgetData::listBudgetDetailTotal($id_presupuesto,$id_area,$id_area_padre,$id_depto);                                
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
    public  function listBudgetDetailDist(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                
                $id_presupuesto = $request->query('id_presupuesto');
                $id_area = $request->query('id_area');
                $id_area_padre = $request->query('id_area_padre');
                $id_depto = $request->query('id_depto');
                $data = BudgetData::listBudgetDetailDist($id_presupuesto,$id_area,$id_area_padre,$id_depto);                                
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
    public  function addPresupuestoAsieto($id_presupuesto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            try{    
                $params = json_decode(file_get_contents("php://input"));        
 
                $id_presupuesto = $params->id_presupuesto;
                
                BudgetData::addPresupuestoAsieto($id_presupuesto,$id_user);
  
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
    public  function addBudgetOfAuxiliar(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
     
        if($valida=='SI'){
            $jResponse=[];
            
            try{   
                DB::beginTransaction();
                
                $params = json_decode(file_get_contents("php://input"));        
 
                $id_entidad = $params->id_entidad;
                $id_depto_padre = $params->id_depto;
                $id_anho = $params->id_anho;
                $id_pstnegocio = $params->id_pstnegocio;
                $id_eje = $params->id_eje;
                $id_auxiliar = $params->id_auxiliar;
                   
                $data = BudgetData::addBudgetByAuxiliar($id_entidad,$id_depto_padre,$id_anho,$id_user,$id_pstnegocio,$id_eje,$id_auxiliar);
                
                if($data["nerror"]==0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data["msgerror"];
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
    public  function listMyEvents(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;               
                }
                $id_depto = $request->query('id_depto');
                $data = BudgetData::listMyEvents($id_entidad,$id_anho,$id_depto);                                
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
    public  function listMyEventsActivities(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;               
                }
                $id_depto = $request->query('id_depto');
                $id_evento = $request->query('id_evento');
                $data = BudgetData::listMyEventsActivities($id_entidad,$id_anho,$id_evento,$id_depto);                                
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
    public  function listMyEventsActivitiesSearch(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{    
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;   
                    $id_anho = "2018"; // Solo Pruebas
                }
                $id_depto = $request->query('id_depto');
                $actividad = $request->query('actividad');
                $data = BudgetData::listMyEventsActivitiesSearch($id_entidad,$id_anho,$id_depto,$actividad);                                
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
