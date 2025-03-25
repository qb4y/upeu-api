<?php
namespace App\Http\Controllers\Sales;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Sales\PoliticsData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;

use PDO;
use Excel;

class PoliticsController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listPolitics(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_almacen = $request->query('id_almacen');
                $data = PoliticsData::listPolitics($id_almacen);
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
    public function showPolitics($id_politica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PoliticsData::showPolitics($id_politica);
                if ($data) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function listTypePolitics(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PoliticsData::listTypePolitics();
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
    public function addPolitics(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_almacen = $params->id_almacen;
            $id_tipopolitica = $params->id_tipopolitica;
            $nombre = $params->nombre;
            $estado = $params->estado;
            try{
                $data = PoliticsData::addPolitics($id_almacen,$id_tipopolitica,$nombre,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updatePolitics($id_politica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_almacen = $params->id_almacen;
            $id_tipopolitica = $params->id_tipopolitica;
            $nombre = $params->nombre;
            $estado = $params->estado;
            try{
                $data = PoliticsData::updatePolitics($id_politica,$id_tipopolitica,$nombre,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deletePolitics($id_politica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $childrens = PoliticsData::listPoliticsArticles($id_politica);
                if(count($childrens) == 0){
                    $data = PoliticsData::deletePolitics($id_politica);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Can not be eliminated, the Politics has Articles';
                    $jResponse['data'] = [];
                    $code = "202"; 
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function listPoliticsPrices(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_politica = $request->query('id_politica');
                $id_parent = $request->query('id_parent');
                $dato = $request->query('dato');
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($dato == "1"){
                    $data = PoliticsData::listPoliticsPrices($id_politica,$id_anho,$id_parent);
                }else{
                    $data = PoliticsData::listPoliticsArticleswithoutPrices($id_politica,$id_anho,$id_parent);
                }
                
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
    public function addPoliticsPrices(){
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
                    $id_anho_actual = $item->id_anho_actual;                
                }
                $params = json_decode(file_get_contents("php://input"));
                $id_politica = $params->id_politica;
                $id_parent = $params->id_parent;
                $porcentaje_venta = $params->porcentaje_venta;
                $porcentaje_descuento = $params->porcentaje_descuento;
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_INSERT_POLI_PRECIOS(:P_ID_POLITICA, :P_ID_ANHO, :P_ID_PARENT, :P_POR_VENTA, :P_POR_DESCUENTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_POLITICA', $id_politica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PARENT', $id_parent, PDO::PARAM_INT);
                $stmt->bindParam(':P_POR_VENTA', $porcentaje_venta, PDO::PARAM_STR);
                $stmt->bindParam(':P_POR_DESCUENTO', $porcentaje_descuento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
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
    public function updatePoliticsPrices(){
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
                    $id_anho_actual = $item->id_anho_actual;                
                }
                $params = json_decode(file_get_contents("php://input"));
                $id_politica = $params->id_politica;
                $id_articulo = $params->id_articulo;
                $precio = $params->precio;
                $descuento = $params->descuento;
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_UPDATE_POLI_PRECIOS(:P_ID_POLITICA, :P_ID_ANHO, :P_ID_ARTICULO, :P_PRECIO, :P_DESCUENTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_POLITICA', $id_politica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_PRECIO', $precio, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCUENTO', $descuento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
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
    public function listPrices(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_almacen = $request->query('id_almacen');
                $id_parent = $request->query('id_parent');
                $dato = $request->query('dato');
                $opc = $request->query('opc');
                $search = $request->query('search');
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                //dd($id_almacen, $id_parent, $dato, $id_anho, $data_anho);
                
                if($dato == "1"){
                    if($opc == "1"){
                        $data = PoliticsData::listPrices($id_almacen,$id_anho,$id_parent);
                    }else{
                        $data = PoliticsData::listPricesSearch($id_almacen,$id_anho,$search);
                    }
                }else{
                    $data = PoliticsData::listArticleswithoutPrices($id_almacen,$id_anho,$id_parent);
                }
                
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
    public function addPrices(){
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
                    $id_anho_actual = $item->id_anho_actual;                
                }
                $params = json_decode(file_get_contents("php://input"));
                $id_almacen = $params->id_almacen;
                $id_parent = $params->id_parent;
                $porcentaje_venta = $params->porcentaje_venta;
                $porcentaje_descuento = $params->porcentaje_descuento;
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_INSERT_PRECIOS(:P_ID_ALMACEN, :P_ID_ANHO, :P_ID_PARENT, :P_POR_VENTA, :P_POR_DESCUENTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PARENT', $id_parent, PDO::PARAM_INT);
                $stmt->bindParam(':P_POR_VENTA', $porcentaje_venta, PDO::PARAM_STR);
                $stmt->bindParam(':P_POR_DESCUENTO', $porcentaje_descuento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
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
    public function updatePrices(){
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
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($id_anho == $id_anho_actual) {
                    $params = json_decode(file_get_contents("php://input"));
                    $id_almacen = $params->id_almacen;
                    $id_articulo = $params->id_articulo;
                    $precio = $params->precio;
                    $descuento = $params->descuento;
                    $estado = $params->estado;
                    $error = 0;
                    $msn_error = "00000000000000000000000000000000000000000000000000";
                    $pdo = DB::getPdo();
                    if ($estado == 1) {
                        $stmt = $pdo->prepare("begin PKG_SALES.SP_UPDATE_PRECIOS(:P_ID_ALMACEN, :P_ID_ANHO,
                            :P_ID_ARTICULO, :P_PRECIO, :P_DESCUENTO, :P_ERROR, :P_MSN_ERROR); end;");
                        $stmt->bindParam(':P_ID_ALMACEN',   $id_almacen, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_ANHO',      $id_anho, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_ARTICULO',  $id_articulo, PDO::PARAM_INT);
                        $stmt->bindParam(':P_PRECIO',       $precio, PDO::PARAM_STR);
                        $stmt->bindParam(':P_DESCUENTO',    $descuento, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR',        $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSN_ERROR',    $msn_error, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        $stmt = $pdo->prepare("begin PKG_SALES.SP_INSERT_PRECIOS_ARTICULO(:P_ID_ALMACEN, :P_ID_ANHO,
                            :P_ID_ARTICULO, :P_PRECIO, :P_DESCUENTO, :P_ERROR, :P_MSN_ERROR); end;");
                        $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                        $stmt->bindParam(':P_PRECIO', $precio, PDO::PARAM_INT);
                        $stmt->bindParam(':P_DESCUENTO', $descuento, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    if($error == 0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msn_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    } 
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Año Activo!!!";                        
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
    public function listPoliticsPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_politica = $request->query('id_politica');
                $credito = $request->query('credito');
                $activo = $request->query('activo');
                $sin_credito = $request->query('sin_credito');
                if($id_politica != null && $credito != null && $activo != null && $sin_credito != null){
                    $data = PoliticsData::listPoliticsPersons($id_politica,$credito,$activo,$sin_credito);
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
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Attention: Check, missing parameters';
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
    public function addPoliticsPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_politica = $request->id_politica;
                \Excel::load($request->excel, function($reader) use($id_politica) {
                    $excel = $reader->get();
                    $reader->each(function($row)use($id_politica) {
                        $documento = $row->documento;
                        $credito = $row->credito;
                        $error = 0;
                        $msn_error = "00000000000000000000000000000000000000000000000000";
                        $pdo = DB::getPdo();
                        $stmt = $pdo->prepare("begin PKG_SALES.SP_GESTION_POLI_PERSONA(:P_ID_POLITICA, :P_DOCUMENTO, :P_CREDITO, :P_ERROR, :P_MSN_ERROR); end;");
                        $stmt->bindParam(':P_ID_POLITICA', $id_politica, PDO::PARAM_INT);
                        $stmt->bindParam(':P_DOCUMENTO', $documento, PDO::PARAM_STR);
                        $stmt->bindParam(':P_CREDITO', $credito, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                        $stmt->execute();
                     });
                });
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
    public function updatePoliticsPersons(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_politica = $params->id_politica;
                $id_persona = $params->id_persona;
                $credito = $params->credito;
                $data = PoliticsData::updatePoliticsPersons($id_politica,$id_persona,$credito);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function updatePoliticsPersonsAll($id_politica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $activo = Input::get('activo');
                $data = PoliticsData::updatePoliticsPersonsAll($id_politica, $activo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was disabled successfully";
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function addPricesAll(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_almacen = $params->id_almacen;
                $id_articulo = $params->id_articulo;
                $id_anho = $params->id_anho;
                $precio = $params->precio;
                $descuento = $params->descuento;
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_INSERT_PRECIOS_ALL(:P_ID_ALMACEN, :P_ID_ANHO, :P_ID_ARTICULO, :P_PRECIO, :P_DESCUENTO); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_PRECIO', $precio, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCUENTO', $descuento, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
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
}