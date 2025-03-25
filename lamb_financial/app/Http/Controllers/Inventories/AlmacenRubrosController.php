<?php
namespace App\Http\Controllers\Inventories;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Inventories\AlmacenRubroData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use PDO;
use Excel;

class AlmacenRubrosController extends Controller{
    
    
    public function listAlmacenRubros(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AlmacenRubroData::listAlmacenRubros( $id_entidad);
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
    public function showAlmacenRubros(Request $request, $id_rubro){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AlmacenRubroData::showAlmacenRubros($id_rubro);
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
    public function addAlmacenRubros(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesAlmacenRubros());
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try{
                $data = AlmacenRubroData::addAlmacenRubros($id_entidad, $datax['nombre'],$datax['alias'],$datax['codigo'],$datax['estado']);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }   
        end:     
        return response()->json($jResponse,$code);
    }
    private function rulesAlmacenRubros() {
        return [
            'nombre' => 'required',
            'alias' => 'required',
            'codigo' => 'required',
            'estado' => 'required',
        ];
    }
    public function updateAlmacenRubros(Request $request, $id_rubro){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesAlmacenRubros());
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try{
                $data = AlmacenRubroData::updateAlmacenRubros($id_rubro,
                $datax['nombre'],$datax['alias'],$datax['codigo'], $datax['estado']);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }    
        end:     
        return response()->json($jResponse,$code);
    }
    public function deleteAlmacenRubros(Request $request, $id_Rubros){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AlmacenRubroData::deleteAlmacenRubros($id_Rubros);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    
}