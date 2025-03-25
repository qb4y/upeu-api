<?php
namespace App\Http\Controllers\Budget;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\ReportData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class ReportController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function listPregradoProyeccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_area = $request->query('id_area');
                $id_depto = $request->query('id_depto');
                $id_anho= $request->query('id_anho');
                $data = ReportData::listPregradoProyeccion($id_entidad,$id_depto,$id_auxiliar,$id_area,$id_anho);                                
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
    public  function listPregradoProceso(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_depto = $request->query('id_depto');
                $id_anho= $request->query('id_anho');
                $id_area= $request->query('id_area');
                $data = ReportData::listPregradoProceso($id_entidad,$id_auxiliar,$id_depto,$id_anho,$id_area);                                
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
    public  function listProesadProyeccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_depto = $request->query('id_depto');
                $id_anho= $request->query('id_anho');
                $data = ReportData::listProesadProyeccion($id_entidad,$id_auxiliar,$id_depto,$id_anho);                                
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
    public  function listProesadProceso(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_auxiliar = $request->query('id_auxiliar');
                $id_anho= $request->query('id_anho');
                $id_depto= $request->query('id_depto');
                $data = ReportData::listProesadProceso($id_entidad,$id_auxiliar,$id_depto,$id_anho);                                
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
    public  function listResultado(Request $request){
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
                $id_depto_padre= $request->query('id_depto_padre');
                $id_area_padre= $request->query('id_area_padre');
                $mes_ini= $request->query('mes_ini');
                $mes_fin= $request->query('mes_fin');
                $data = ReportData::listResultado($id_entidad,$id_anho,$id_area,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado,$id_depto,$id_depto_padre,$id_area_padre,$mes_ini,$mes_fin);  
                $datacab= ReportData::listResultadoCab($id_entidad,$id_anho,$id_area,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado,$id_depto,$id_depto_padre,$id_area_padre,$mes_ini,$mes_fin); 
                
                
               
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'header'=>$datacab];
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
    public  function listSummaryPayroll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_anho= $request->query('id_anho');
                $id_depto= $request->query('id_depto');
                $data = ReportData::listSummaryPayroll($id_entidad,$id_depto,$id_anho);                                
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
    public  function listSummaryEvent($id_proyecto,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_anho= $request->query('id_anho');
                $id_depto= $request->query('id_depto');
                $data = ReportData::listSummaryEvent($id_entidad,$id_depto,$id_anho,$id_proyecto);                                
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
    public  function listSummarySubArea($id_area_padre,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_anho= $request->query('id_anho');
                $id_depto= $request->query('id_depto');
                $data = ReportData::listSummarySubArea($id_entidad,$id_depto,$id_anho,$id_area_padre);                                
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
    public  function listSummaryDepartament($id_area,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $id_entidad = $request->query('id_entidad');
                $id_anho= $request->query('id_anho');
                $id_depto= $request->query('id_depto');
                $data = ReportData::listSummaryDepartament($id_entidad,$id_depto,$id_anho,$id_area);                                
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

