<?php
namespace App\Http\Controllers\Inventories;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Inventories\AlmacenCategoriaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use PDO;
use Excel;

class AlmacenCategoriasController extends Controller{
    
    
    public function listAlmacenCategorias(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $estado = $request->query('estado');

                $data = AlmacenCategoriaData::listAlmacenCategorias($id_entidad, $estado);
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
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showAlmacenCategorias(Request $request, $id_rcategoria){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AlmacenCategoriaData::showAlmacenCategorias($id_rcategoria);
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
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function addAlmacenCategorias(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesAlmacenCategorias());
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try{
                $data = AlmacenCategoriaData::addAlmacenCategorias($id_entidad,$datax['id_rubro'], $datax['nombre'],$datax['alias'],$datax['estado']);

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
    private function rulesAlmacenCategorias() {
        return [
            'nombre' => 'required',
            'alias' => 'required',
            'estado' => 'required',
        ];
    }
    public function updateAlmacenCategorias(Request $request, $id_rcategoria){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesAlmacenCategorias());
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try{
                $data = AlmacenCategoriaData::updateAlmacenCategorias($id_rcategoria,
                    $datax['id_rubro'],$datax['nombre'],$datax['alias'],$datax['estado']);

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
    public function deleteAlmacenCategorias(Request $request, $id_rcategoria){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AlmacenCategoriaData::deleteAlmacenCategorias($id_rcategoria);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    
}