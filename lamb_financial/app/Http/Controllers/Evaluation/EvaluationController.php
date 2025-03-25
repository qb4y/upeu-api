<?php
namespace App\Http\Controllers\Evaluation;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Evaluation\EvaluationData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class EvaluationController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listPeriod(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EvaluationData::listPeriod();
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listPeriodDetails($id_periodo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EvaluationData::listPeriodDetails($id_periodo);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listEvaluationDepartments(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EvaluationData::listEvaluationDepartments($id_entidad,$id_user);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listDepartmentsAssigneds(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EvaluationData::listDepartmentsAssigneds($id_entidad,$id_user);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listEvaluationRegisters(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EvaluationData::listEvaluationRegisters($id_entidad);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showTypeNotices($id_tipo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $parent = [];
                $data = EvaluationData::showTypeNotices($id_tipo);
                foreach ($data as $key => $value){                                          
                    $row = $data = EvaluationData::showTypeNoticesDetails($value->id_tipo);         
                    $parent[] = [
                                    'id_tipo' => $value->id_tipo, 
                                    'nombre' => $value->nombre,
                                    'minimo' => $value->minimo,
                                    'maximo' => $value->maximo,
                                    'tipo_campo' => $value->tipo_campo,
                                    'simbolo' => $value->simbolo,
                                    'children' => $row
                                ];            
                }
                
                if ($parent) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent[0];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function ListEvaluationIndicators(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_periodo = $request->query('id_periodo');
                $id_pdetalle = $request->query('id_periodo_detail');
                
                if($id_periodo == 1 || $id_periodo == 2){
                    $id_mes = $id_mes;
                }else{
                    $id_mes = "";
                }
                $data = EvaluationData::ShowEvaluationRegisters($id_entidad,$id_depto,$id_anho,$id_pdetalle,$id_periodo,$id_mes);
                if ($data) { 
                    
                    $datos = EvaluationData::ListEvaluationIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }else{
                    EvaluationData::addEvaluationRegisters($id_entidad,$id_depto,$id_user,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    $data = EvaluationData::ListEvaluationIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = $data;
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showEvaluationRegisters(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $id_depto = $request->query('id_depto');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            $id_periodo = $request->query('id_periodo');
            $id_pdetalle = $request->query('id_periodo_detail');
            try{
                if($id_periodo == 1 || $id_periodo == 2){
                    $id_mes = $id_mes;
                }else{
                    $id_mes = "";
                }
                $data = EvaluationData::ShowEvaluationRegisters($id_entidad,$id_depto,$id_anho,$id_pdetalle,$id_periodo,$id_mes);
                if ($data) { 
                    
                    $datos = EvaluationData::ListEvaluationRegistersIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }else{
                    //$data = EvaluationData::addEvaluationRegisters($id_entidad,$id_depto,$id_user,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    EvaluationData::addEvaluationRegisters($id_entidad,$id_depto,$id_user,$id_anho,$id_periodo,$id_pdetalle,$id_mes);
                    $data = EvaluationData::ListEvaluationRegistersIndicators($id_entidad,$id_depto,$id_anho,$id_periodo,$id_pdetalle);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = $data;
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function addEvaluationDetails(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_user = $jResponse["id_user"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_registro = $params->data->id_registro;
            $indicadores = $params->data->indicadores;
            try{
                foreach ($indicadores as $item){
                    //EVALUACION GENERAL 0 - 100 % Rango General
                    $valor = 0;
                    $valor_inversa = 0;
                    $ascendente = "";
                    // RANGOS GENERALES
                    $rg_minimo = 0;
                    $rg_maximo = 100;
                    //RANGOS de Indicador
                    $ri_minimo = 0;
                    $ri_maximo = 0;
                    //$indicador_ideal = 0; // Ideal del Indicador
                    //$indicador_error = 1000; // Supongamos
                    $nota = 0;
                    $formula = "";
                    $rangos = EvaluationData::listTypeIndicador($item->id_indicador);
                    foreach ($rangos as $items){
                        $ascendente = $items->ascendente;
                        $ri_minimo = $items->minimo;
                        $ri_maximo = $items->maximo;
                        //$indicador_ideal = $items->ideal;
                        $formula = $items->formula;
                        $formula_fecha = $items->formula_fecha;
                        $req_fecha = $items->req_fecha;
                    }
                    if($ascendente != "S"){
                        //$valor_inversa = $ri_maximo-(((($item->respuesta-$ri_minimo)/($ri_maximo-$ri_minimo))*($ri_maximo-$ri_minimo))+$ri_minimo);
                        //$nota = $valor_inversa;
                        
                        $nota = ($ri_maximo-$item->respuesta)+$ri_minimo;
                    }else{
                        $nota = $item->respuesta;
                    }
                    //$valor = round(((($rg_maximo-$rg_minimo)/($ri_minimo+$ri_maximo))*$nota)+($rg_minimo),2);
                    $valor = round(((($nota-$ri_minimo)/($ri_maximo-$ri_minimo))*($rg_maximo-$rg_minimo))+$rg_minimo,2);
                    EvaluationData::addEvaluationDetails($id_registro,$item->id_indicador,$item->id_pdetalle,$item->respuesta,$item->ideal,$item->comentario,$valor,$formula,$formula_fecha,$item->fecha,$req_fecha); 
                    
                }
                // SE COMPARA LA CANTIDAD DE PERIDOS REQUERIDOS Y LA CANTIDAD DE PERIDOS EVALUADOS
                $p_requeridos = EvaluationData::showPeriodRequerid($id_registro);
                foreach ($p_requeridos as $item){
                    $cant_requerido = $item->periodos;
                }
                $p_evaluados = EvaluationData::showPeriodEvaluated($id_registro);
                foreach ($p_evaluados as $item){
                    $cant_evaluado = $item->periodos;
                }
                if($cant_requerido == $cant_evaluado){
                    $p_requeridos = EvaluationData::updateEvaluationRegisters($id_registro,'1');
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
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
    public function addEvaluationLivelihoods(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_user = $jResponse["id_user"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $id_registro = $request->id_registro;
            $id_indicador = $request->id_indicador;
            $id_periodo_detail = $request->id_periodo_detail;
            
            try{
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $path = 'accounting_files';
                $upload_success = $file->move($path,$fileName);
                if($upload_success) {
                
                    $list_indi = EvaluationData::listTypeIndicador($id_registro);
                    foreach ($list_indi as $item){
                        $formula_fecha = $item->formula_fecha;
                    }
                    EvaluationData::addEvaluationLivelihoods($id_registro,$id_indicador,$id_periodo_detail,$formula_fecha,$fileName);

                    // SE COMPARA LA CANTIDAD DE PERIDOS REQUERIDOS Y LA CANTIDAD DE PERIDOS EVALUADOS
                    $p_requeridos = EvaluationData::showPeriodRequerid($id_registro);
                    foreach ($p_requeridos as $item){
                        $cant_requerido = $item->periodos;
                    }
                    $p_evaluados = EvaluationData::showPeriodEvaluated($id_registro);
                    foreach ($p_evaluados as $item){
                        $cant_evaluado = $item->periodos;
                    }
                    if($cant_requerido == $cant_evaluado){
                        $p_requeridos = EvaluationData::updateEvaluationRegisters($id_registro,'1');
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully and File uploaded successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was not inserted and the File was not loaded";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function listEvaluationLivelihoods(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_registro = $request->query('id_registro');
                $id_indicador = $request->query('id_indicador');
                $id_periodo_detail = $request->query('id_periodo_detail');
                $data = EvaluationData::listEvaluationLivelihoods($id_registro,$id_indicador,$id_periodo_detail);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteEvaluationLivelihoods($id_sustento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $list = EvaluationData::showEvaluationLivelihoods($id_sustento);
                foreach ($list as $items){
                    $id_registro = $items->id_registro;
                    $name_file = $items->name_file;
                }
                $path = 'accounting_files';
                unlink($path."/".$name_file);
                EvaluationData::deleteEvaluationLivelihoods($id_sustento);
                // SE COMPARA LA CANTIDAD DE PERIDOS REQUERIDOS Y LA CANTIDAD DE PERIDOS EVALUADOS
                $p_requeridos = EvaluationData::showPeriodRequerid($id_registro);
                foreach ($p_requeridos as $item){
                    $cant_requerido = $item->periodos;
                }
                $p_evaluados = EvaluationData::showPeriodEvaluated($id_registro);
                foreach ($p_evaluados as $item){
                    $cant_evaluado = $item->periodos;
                }
                if($cant_requerido == $cant_evaluado){
                    $p_requeridos = EvaluationData::updateEvaluationRegisters($id_registro,'0');
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
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
    public function listEvaluationReports(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $data = EvaluationData::listEvaluationReports($id_depto);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listEvaluationTrafficLight(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $id_periodo = $request->query('id_periodo');
                $data = EvaluationData::showTotalItems($id_depto);
                foreach ($data as $item){
                    $cant = $item->cant;
                }
                $data = EvaluationData::listEvaluationTrafficLight($id_depto,$id_periodo,$cant);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listReportDepartments(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho = $request->query('id_anho');
                /*$data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }*/
                $data = EvaluationData::listReportDepartments($id_entidad,$id_anho);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listReportDepartmentsDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho = $request->query('id_anho');
                $id_depto = $request->query('id_depto');
                $data = EvaluationData::listReportDepartmentsDetails($id_entidad,$id_anho,$id_depto);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listReportDepartmentsMonths(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho = $request->query('id_anho');
                $id_depto = $request->query('id_depto');
                $id_mes_end = $request->query('id_mes_end');
                $data = EvaluationData::listReportDepartmentsMonths($id_entidad,$id_anho,$id_depto,$id_mes_end);
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
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
}