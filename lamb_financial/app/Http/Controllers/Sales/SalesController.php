<?php
namespace App\Http\Controllers\Sales;
use App\Http\Data\FinancialEnrollment\ProceduresDiscounts;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Sales\SalesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Accounting\Setup\TypeTransactionData;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Data\Accounting\Setup\PrintData;
use App\Http\Data\Setup\PersonData;
use App\Http\Data\Sales\PoliticsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use DOMPDF;
use App\Http\Data\FinancesStudent\StudentData;
use Carbon\Carbon;

class SalesController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function getDireccion($idPersona){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];   
        $id_entidad = $jResponse["id_entidad"];     
        if($valida=='SI'){
            $jResponse=[];   
            try{  
                
                $data = SalesData::getDireccionPersona($idPersona); 

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = $data;
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }else{
            abort(422,"Error: Recurso no Autorizado");
        }        
        return response()->json($jResponse,$code);
    }
    public function addDireccionPersona(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $jResponse=[];   
            try{   
                $data = SalesData::addDireccionPersona($request);  
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = $data;
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
             
        return response()->json($jResponse,$code);
    }
    public function getTipoDireccion(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];   
        if($valida=='SI'){
            $jResponse=[];   
            try{   
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = SalesData::getTipoDireccion();
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }else{
            abort(422,"Error: Recurso no Autorizado");
        }        
        return response()->json($jResponse,$code);
    }
    public function getTipoVia(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];    
        if($valida=='SI'){
            $jResponse=[];   
            try{    
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = SalesData::getTipoVia(); 
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }else{
            abort(422,"Error: Recurso no Autorizado");
        }        
        return response()->json($jResponse,$code);
    }
    public function getTipoZona(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];    
        if($valida=='SI'){
            $jResponse=[];   
            try{  
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = SalesData::getTipoZona();
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }else{
            abort(422,"Error: Recurso no Autorizado");
        }        
        return response()->json($jResponse,$code);
    }
    public function listPerson(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];   
        $id_entidad = $jResponse["id_entidad"];     
        if($valida=='SI'){
            $jResponse=[];            
            $dato = $request->query('dato');
            $id_comprobante = $request->query('id_comprobante');

            $id_almacen = null;

            try{   
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }  
                if($id_comprobante=="01"){
                    $data = SalesData::listLegalPerson($dato, $id_almacen);
                } elseif ($id_comprobante=="03") {
                    $data = SalesData::searchNaturalPerson($dato, $id_almacen);
                } else{
                    if($id_comprobante=="00"){
                        $data = SalesData::listPerson($dato, $id_almacen);
                    }else{
                        $data = SalesData::listNaturalPerson($dato, $id_almacen);
                        // $data = SalesData::listNaturalPerson($dato, $id_almacen);
                    }
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
        }else{
            abort(422,"Error: Recurso no Autorizado");
        }        
        return response()->json($jResponse,$code);
    }

    
    public function showNaturalPerson($id_persona){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SalesData::showNaturalPerson($id_persona);                                
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
    public function listPersonSucursal($id_persona){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SalesData::listLegalPersonSucursal($id_persona);                                
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
    public function addSales(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $token = $jResponse["token"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $es_autoentrega = Input::get('es_autoentrega');
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if($id_anho !== $id_anho_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto. No exíste un año activo.";                    
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item){
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;                
            }
            if($id_mes !== $id_mes_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto. No existe un mes activo.";                        
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }

            $tipcam = GlobalMethods::verificaTipoCambio();
            if($tipcam["tc"] !== true){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto. Actualize el tipo de cambio.";                        
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }

            $id_venta = 0;
            $es_autoentrega = 1;
            try{           
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_VENTA(:P_ID_PERSONA, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO,
                :P_ID_MES, :P_ES_AUTOENTREGA, :P_ID_VENTA, :P_ERROR); end;");
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ES_AUTOENTREGA', $es_autoentrega, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->execute();                            
                if($error !== 0){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Alto. No se tiene asignado un documento de impresión";                    
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                    
                $jResponse['data'] = $id_venta;
                $code = "200";
                
            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }      
        end:  
        return response()->json($jResponse,$code);
    }
    public function addSalesSeat($id_venta) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
//                $qry = "INSERT INTO VENTA_ASIENTO(ID_VENTA,ID_CUENTAAASI,ID_RESTRICCION,ID_CTATCE,ID_FONDO,ID_DEPTO,IMPORTE,DESCRIPCION,EDITABLE,DC,AGRUPA)"
                $params = (array) $params;
//                dd($params);
                $data = DB::table('VENTA_ASIENTO')
                    ->insert($params);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function showSalesSeat($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = DB::table('VENTA_ASIENTO')->where('id_venta', $id_venta)->get()->first();
                $jResponse['success'] = true;
                if($data){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = null;
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
    public function addSalesDetails($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $count = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->count();
                if ($count>0) {
                    $delete = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->delete();
                }
                $params = json_decode(file_get_contents("php://input"));
                // $id_venta = GlobalMethods::getSecret($id_venta);
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_VENTA_DETALLE(:P_ID_VENTA, :P_ID_ARTICULO, :P_ID_ALMACEN, :P_ID_DINAMICA, :P_DETALLE, :P_CANTIDAD, :P_PRECIO, :P_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $params->id_articulo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ALMACEN', $params->id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $params->detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_PRECIO', $params->precio, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->execute();                
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                    
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

    public function addSalesDetailsSeat($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                // $id_venta = GlobalMethods::getSecret($id_venta);
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $error = 0;

//                dd($params);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_VENTA_DETALLE_ASIENTO(
                :P_ID_VENTA, 
                :P_DETALLE, 
                :P_DC, 
                :P_IMPORTE, 
                :P_ID_CUENTAAASI, 
                :P_ID_RESTRICCION, 
                :P_ID_CTACTE, 
                :P_ID_FONDO, 
                :P_ID_DEPTO, 
                :P_ERROR, 
                :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $params->glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_DC', $params->dc, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $params->importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CUENTAAASI', $params->id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $params->id_restriccion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $params->id_ctate, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_FONDO', $params->id_fondo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPTO', $params->id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function deleteSalesDetails($id_venta,$id_vdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $count = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->count();
                if ($count>0) {
                    $delete = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->delete();
                } 
                $tipo = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_DETALLE(:P_ID_VENTA, :P_ID_VDETALLE, :P_TIPO); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200";                
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteSalesDetailsAll($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{       
                $id_vdetalle = 0;
                $tipo = 1;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_DETALLE(:P_ID_VENTA, :P_ID_VDETALLE,:P_TIPO); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public function deleteDetailSalesSeat($idSaleDetail){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $codeError = 1;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_VENTA_ASIENTO(:P_ID_VDETALLE, :P_ERROR,:P_MSN); end;");
                $stmt->bindParam(':P_ID_VDETALLE', $idSaleDetail, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $codeError, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
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
    public function listSalesDetails($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{             
                $data = SalesData::listSalesDetails($id_venta);
                $datat = SalesData::listSalesDetailsTotal($id_venta);
                $tres = SalesData::listSalesDetailsResume($id_venta);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'total'=>$datat,'resume'=>$tres];
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
    public function listSaleDetails($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SalesData::listSaleDetails($id_venta);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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

    public function finishSalesStudent($idSale) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
//            dd('->>>>');
            DB::beginTransaction();
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
//                dd('finissssshii', $idSale);
//                 var_dump($params, $id_transferencia);
                $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA_FA(:P_ID_VENTA,
                    :P_ERROR,
                    :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $idSale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Venta realizada";
                    $jResponse['data'] = $idSale;
                    $code = "200";
                }else{
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] =  $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
                $jResponse['data'] = [];
                $error = "202";
            }

        }
        return response()->json($jResponse,$code);
    }

                                       
    public function listTypeSales(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $data = SalesData::listTypeSales();                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function saveSales($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];  
            $params = json_decode(file_get_contents("php://input"));
            $id_entidad = $params->id_entidad;
            $id_venta = 0;
            try{      
                
                $bindings = [                            
                    'P_ID_PERSONA' => $id_user,
                    'P_ID_ENTIDAD' => $id_entidad,
                    'P_ID_DEPTO' => $params->id_depto,
                    'P_ID_ANHO' => $id_anho,
                    'P_ID_MES' => $id_mes,
                    'P_ID_VENTA' => $id_venta
                ];                        
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_TIPONOTA, :P_ID_CREDITO, :P_ID_TIPOVENTA, :P_GLOSA, :P_ID_PARENT, :P_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPONOTA', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CREDITO', $params->id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_TIPOVENTA', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_GLOSA', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PARENT', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $id_venta, PDO::PARAM_INT);
                $stmt->execute();                                            
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                    
                $jResponse['data'] = ['items' => $id_venta];
                $code = "200";                       
            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function showSales($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = SalesData::showSales($id_venta);                
                // $data = [];                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La venta ha sido generada.";                        
                    $jResponse['data'] = [];
                    $code = "410";
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
    public function showSale($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SalesData::showSale($id_venta);
                // $data = [];
                if($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item no encontrado";
                    $jResponse['data'] = [];
                    $code = "410";
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
    public function updateSales($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];    
        $id_entidad = $jResponse["id_entidad"];    
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_cliente = $params->id_cliente;
            $id_sucursal = $params->id_sucursal;
            $id_comprobante = $params->id_comprobante;
            $id_moneda = $params->id_moneda;
            $id_tipoventa = $params->id_tipoventa;
            $agrupado = $params->agrupado;
            $glosa = $params->glosa;
            $opc = $params->opc;
            try{   
                $pdo = DB::getPdo();
                if($opc == "1"){
                    /*if($id_moneda = 9){
                        $tipcam["tc"] = false;
                    }
                    if($tipcam["tc"] == true){*/
                        $error = 0;
                        $stmt = $pdo->prepare("begin PKG_SALES.SP_ACTUALIZAR_VENTA(:P_ID_VENTA, :P_ID_CLIENTE, :P_ID_SUCURSAL, :P_ID_COMPROBANTE, :P_ID_MONEDA, :P_ID_TIPOVENTA, :P_AGRUPADO, :P_GLOSA, :P_ERROR); end;");
                        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_SUCURSAL', $id_sucursal, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_INT);
                        $stmt->bindParam(':P_AGRUPADO', $agrupado, PDO::PARAM_STR);
                        $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->execute();                            
                        if($error === 0){
                            $jResponse['success'] = true;
                            $jResponse['message'] = "The item was updated successfully";                    
                            $jResponse['data'] = $id_venta;
                            $code = "200";
                        }else{
                            $jResponse['success'] = false;
                            $jResponse['message'] = $error;                    
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    /*}else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }*/
                }else{
                    if($opc == "2"){                         
                        DB::beginTransaction();
                        try{
                            $id_credito =$params->id_credito;
                            $efectivo = $params->efectivo;
                            $credito = $params->credito;
                            $tarjeta = $params->tarjeta;
                            $id_tipotarjeta = $params->id_tipotarjeta;
                            $operacion = $params->operacion;
                            $tarjeta2 = $params->tarjeta2;
                            $id_tipotarjeta2 = $params->id_tipotarjeta2;
                            $operacion2 = $params->operacion2;

                            $id_ctabancaria = $params->id_ctabancaria;
                            $id_ctabancaria2 = $params->id_ctabancaria2;
                            $tipo = $params->tipo;
                            $fecha_pago = $params->fecha_pago;
                            $direccion = $params->direccion ?? ' ';
                            $error = 0;
                            $msgerror = "";   
                            $es_autoconsumo = "N";
                            for($x=1;$x<=200;$x++){
                                $msgerror .= "0";
                            }
                            if($id_credito == "2"){
                                $efectivo = 0;
                                $credito = 0;
                                $tarjeta = 0;
                            }
                            DB::table('VENTA')->where('id_venta', $id_venta)->update(['id_paciente' => $params->id_paciente, 'direccion_cli' =>$direccion]);
                            DB::commit();
                            $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_CREDITO, :P_EFECTIVO, :P_CREDITO, :P_TARJETA, :P_ID_TIPO_TARJETA, :P_OPERACION, :P_ERROR, :P_MSGERROR, :P_ES_AUTOCONSUMO, :P_TARJETA2, :P_ID_TIPO_TARJETA2, :P_OPERACION2, :P_ID_CTABANCARIA, :P_ID_CTABANCARIA2,:P_FECHA_PAGO); end;");
                            $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);                        
                            $stmt->bindParam(':P_ID_CREDITO', $id_credito, PDO::PARAM_INT);
                            $stmt->bindParam(':P_EFECTIVO', $efectivo, PDO::PARAM_STR);
                            $stmt->bindParam(':P_CREDITO', $credito, PDO::PARAM_STR);
                            $stmt->bindParam(':P_TARJETA', $tarjeta, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_TIPO_TARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                            $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ES_AUTOCONSUMO', $es_autoconsumo, PDO::PARAM_STR);
                            $stmt->bindParam(':P_TARJETA2', $tarjeta2, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_TIPO_TARJETA2', $id_tipotarjeta2, PDO::PARAM_INT);
                            $stmt->bindParam(':P_OPERACION2', $operacion2, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_CTABANCARIA2', $id_ctabancaria2, PDO::PARAM_INT);
                            $stmt->bindParam(':P_FECHA_PAGO', $fecha_pago, PDO::PARAM_STR);
                            $stmt->execute();                           
                            if($error == 0){
                                DB::commit();

                                usleep(2500000); //Dormir 2 segundos

                                if($id_entidad === "7124") {
                                    SalesData::UpdateSalesHash($id_venta);
                                }else{
                                    if($id_entidad === "9415") {
                                        SalesData::UpdateSalesHashAces($id_venta);
                                    }else{
                                        SalesData::UpdateSalesHashUPN($id_venta);
                                    }
                                }
                                
                                $jResponse['success'] = true;
                                $jResponse['message'] = "The item was updated successfully";
                                $jResponse['data'] = [];
                                $code = "200";
                            }else{
                                DB::rollback();
                                $jResponse['success'] = false;
                                $jResponse['message'] = $msgerror;
                                $jResponse['data'] = [];
                                $code = "406";
                            }                            
                        }catch (Exception $e){                            
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "500";                            
                            DB::rollback();
                        }                                             
                    }                                        
                }                                                             
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }            
        }
        return response()->json($jResponse,$code);
    }
    public function updateSalesDetails($id_venta,$id_vdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                //$id_venta = GlobalMethods::getSecret($id_venta);
                
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ACTUALIZAR_CANTIDAD_DETALLE(:P_ID_VENTA, :P_ID_VDETALLE, :P_CANTIDAD); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                $stmt->execute();                
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
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
    public function salesBalances(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{    
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                $id_cliente = $request->query('id_cliente');
                $data = SalesData::salesBalances($id_entidad,$id_depto,$id_anho,$id_cliente);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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

    public function salesBalancesAdvances(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_anho = $request->query('id_anho');
                
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                        $id_anho_actual = $item->id_anho_actual;                
                    }
                }
                
                $id_cliente = $request->query('id_cliente');
                $data = SalesData::salesBalancesAdvances($id_entidad,$id_depto,$id_anho,$id_cliente);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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

    public function salesBalancesMov(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                $id_cliente = $request->query('id_cliente');
                // dd($id_cliente);
                $data = SalesData::salesBalancesMov($id_entidad,$id_depto,$id_anho,$id_cliente);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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

    public function salesBalancesMovAlumns(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                $id_cliente = $request->query('id_cliente');
                $id_tipoventa = $request->query('id_tipoventa');
                // dd($id_cliente);
                $data = SalesData::salesBalancesMovAlumns($id_entidad,$id_depto,$id_anho,$id_cliente, $id_tipoventa);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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

    public function listTypeTransaccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                $id_modulo = "";
                $tipo = $request->query('tipo');
                $id_modulo = $request->query('id_modulo');
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;                
                }
                $data = SalesData::listTypeTransaccion($tipo,$id_modulo,$id_entidad,$id_anho,$id_depto);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
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
    public function listTipeMotivoTraslados(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{   
                $data = SalesData::listTipeMotivoTraslados();                                
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                    
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }    
        end:    
        return response()->json($jResponse,$code);
    }
    public function deleteContaEntidadTransactions(Request $request, $id_entidad, $id_tipotransaccion){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $typeTransaccion = TypeTransactionData::deleteContaEntidadTransactions($id_entidad, $id_tipotransaccion);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                        
                $jResponse['data'] = $typeTransaccion;
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        end:
        return response()->json($jResponse,$code);
    }
    public function addContaEntidadTransactions(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = Input::all();
                $validador = Validator::make($data,  ['id_tipotransaccion' => 'required',
                'id_entidad' => 'required','estado' => 'required']);
                if($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validador->errors()->first();
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                $typeTransaccion = TypeTransactionData::addContaEntidadTransactions($data);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                        
                $jResponse['data'] = $typeTransaccion;
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        end:
        return response()->json($jResponse,$code);
    }
    public function addTypeTransaccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = Input::all();
                $validador = Validator::make($data,  ['id_parent' => '',
                'id_modulo' => 'required','nombre' => 'required','modo' => 'required','estado' => 'required',
                'id_tipogrupoconta' => 'required']);
                if($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validador->errors()->first();
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                $typeTransaccion = TypeTransactionData::addTypeTransaction($data);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                        
                $jResponse['data'] = $typeTransaccion;
                $code = "201";
                // }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        end:
        return response()->json($jResponse,$code);
    }
    public function updateTypeTransaccion(Request $request, $id_tipotransaccion){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = Input::all();
                $validador = Validator::make($data,  ['id_tipotransaccion' => 'required','id_parent' => '',
                'id_modulo' => 'required','nombre' => 'required','modo' => 'required','estado' => 'required',
                'id_tipogrupoconta' => 'required']);
                if($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validador->errors()->first();
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                $typeTransaccion = TypeTransactionData::updateTypeTransaction($id_tipotransaccion, $data);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                        
                $jResponse['data'] = $typeTransaccion;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteTypeTransaccion(Request $request, $id_tipotransaccion){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $typeTransaccion = TypeTransactionData::deleteTypeTransaction($id_tipotransaccion);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                        
                $jResponse['data'] = $typeTransaccion;
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
    public function listMantenimientoTypeTransaccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_entidad = $request->query('entity');
                $data = SalesData::listTypeTransaccionByEntidad($id_entidad);                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
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
    
    public function listMantenimientoAllTypeTransaccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = SalesData::listAllTypeTransaccion();                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
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

    public function getMantenimientoTypeTransaccion(Request $request, $id_tipotransaccion){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = SalesData::getTypeTransaccionById($id_tipotransaccion);                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
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
    

    public function salesRecord(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_anho = $request->query('id_anho');
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;                
                    }
                }

            //  dd($id_anho);
                $data = SalesData::salesRecord($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";              
                    $jResponse['data'] = $data;
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
    public function salesAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');

                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;                
                    }
                }
                // dd($id_anho);
                $pedido = SalesData::ShowEntidadEmpresa($id_entidad);
                foreach ($pedido as $item){
                    $id_empresa = $item->id_empresa;
                }
                $data = SalesData::salesAcountingEntry($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher,$id_empresa);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function salesDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $empresa = AccountingData::ShowEntidadEmpresa($id_entidad);
                foreach ($empresa as $item){
                    $id_empresa = $item->id_empresa;
                }
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;                
                    }

                }
                // dd($id_anho);
                $datos = SalesData::salesDetailsCab($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher);   
                $item = array();
                $venta = array();
                //$id_venta = "";
                $id_vdetalle = "";
                //$detalle = "";
                $j=1;
                $i=0;
                foreach($datos as $row){
                    $item[$i]=$j;
                    $id_venta[$i]=$row->id_venta;
                    $id_comprobante[$i]=$row->id_comprobante;
                    $serie[$i]=$row->serie;
                    $numero[$i]=$row->numero;
                    $documento[$i]=$row->documento;
                    $cliente[$i]=$row->cliente;
                    $importe[$i]=$row->total;
                    $detalle[$i]="";
                    $imp_detalle[$i]="";
                    $cuenta[$i]="";
                    $depto[$i]="";
                    $descripcion[$i]="";
                    $imp_asiento[$i]="";                    
                    
                    $objdetalle = SalesData::salesDetailsDet($row->id_venta);
                    $objasiento = SalesData::salesDetailsAsiento($row->id_venta,$id_empresa,$id_anho);
                    
                    $y=0;
                    foreach($objdetalle as $det){
                        if($y>0){
                            $item[$y]="";
                            $id_venta[$y]="";
                            $id_comprobante[$y]="";
                            $serie[$y]="";
                            $numero[$y]="";
                            $documento[$y]="";
                            $cliente[$y]="";
                            $importe[$y]="";
                        }
                        $detalle[$y]=$det->detalle;
                        $imp_detalle[$y]=$det->imp_detalle;
                        $cuenta[$y]="";
                        $depto[$y]="";
                        $descripcion[$y]="";
                        $imp_asiento[$y]="";
                        
                        $y++;
                    }
                    $n=count($item);
                    $z=0;
                    foreach($objasiento as $asi){
                        if($z>=$n){
                            $item[$z]="";
                            $id_venta[$z]="";
                            $id_comprobante[$z]="";
                            $serie[$z]="";
                            $numero[$z]="";
                            $documento[$z]="";
                            $cliente[$z]="";
                            $importe[$z]="";
                            $detalle[$z]="";;
                            $imp_detalle[$z]="";
                        }
                        $cuenta[$z]=$asi->cuenta;
                        $depto[$z]=$asi->depto;
                        $descripcion[$z]=$asi->descripcion;
                        $imp_asiento[$z]=$asi->imp_asiento; 
                       $z++; 
                    }
                    
                    $r=0;
                    $deta=array();
                    foreach($id_venta as $id){
                        $deta["num"]=$item[$r];
                        $deta["id_venta"]=$id;
                        $deta["id_comprobante"]=$id_comprobante[$r];
                        $deta["serie"]=$serie[$r];
                        $deta["numero"]=$numero[$r];
                        $deta["documento"]=$documento[$r];
                        $deta["cliente"]=$cliente[$r];
                        $deta["importe"]=$importe[$r];
                        $deta["detalle"]=$detalle[$r];
                        $deta["imp_detalle"]=$imp_detalle[$r];
                        $deta["cuenta"]=$cuenta[$r];
                        $deta["depto"]=$depto[$r];
                        $deta["descripcion"]=$descripcion[$r];
                        $deta["imp_asiento"]=$imp_asiento[$r];
                        $deta["cant"]=count($id_venta);
                        $venta[] = $deta;
                        $r++;
                    }
                    $j++;
                    $i=0;
                }
                $jResponse['success'] = true;
                if(count($venta)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $venta;
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
    public function addSalesProducts(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];              
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;                
            }
            if($id_anho !== $id_anho_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un año activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item){
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;                
            }
            if($id_mes !== $id_mes_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un mes activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            if(count($warehouse) === 0){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, El usuario no tiene asignado un almacén.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
             }
            if(count($warehouse) > 1){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, Hay mas de un almacén asignado al usuario.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_comprobante = Input::get('id_comprobante');
            $id_moneda = Input::get('id_moneda');
            $es_autoentrega = Input::get('es_autoentrega');
            
            $tipcam["tc"] = false;
            if($id_moneda === '9') {
                $tipcam = GlobalMethods::verificaTipoCambio();
            } else {
                $tipcam["tc"] = true;
            }
            if($tipcam["tc"] === false){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, actualice el tipo de cambio.";                        
                $jResponse['data'] = [];
                $code = "202";
                GOTO end;
            }

            $id_venta = 0;
            $msgerror = "";   
            DB::beginTransaction();
            try{
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_VENTA_INVENTARIO(:P_ID_PERSONA, :P_ID_ENTIDAD,
                :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_COMPROBANTE,:P_ID_MONEDA, :P_ES_AUTOENTREGA, :P_ID_VENTA,:P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_ES_AUTOENTREGA', $es_autoentrega, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();                            
                if($error === 0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";                    
                    $jResponse['data'] = $id_venta;
                    $code = "200";
                }else{
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;                    
                    $jResponse['data'] = [];
                    $code = "202";
                }                      
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }      
        end:  
        return response()->json($jResponse,$code);
    }

    public function updateSalesProducts($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"]; 
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $opc = $params->opc;
            DB::beginTransaction();
            try{   
                $pdo = DB::getPdo();
                if($opc == "1"){
                    $tipcam["tc"] = false;
                    $id_moneda = $params->id_moneda;
                    if($id_moneda === '9') {
                        $tipcam = GlobalMethods::verificaTipoCambio();
                    } else {
                        $tipcam["tc"] = true;
                    }
                    if($tipcam["tc"] == false){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Alto, actualice el tipo de cambio.";                        
                        $jResponse['data'] = [];
                        $code = "202";
                        GOTO end;
                    }
                    $msgerror = "";   
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }

                    $error = 0;
                    $id_cliente = $params->id_cliente;
                    $id_sucursal = $params->id_sucursal;
                    $id_comprobante = $params->id_comprobante;
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_ACTUALIZAR_VENTA_INVENTARIO(
                        :P_ID_VENTA, :P_ID_CLIENTE, :P_ID_SUCURSAL, :P_ID_COMPROBANTE, :P_ID_MONEDA,:P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_SUCURSAL', $id_sucursal, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();                            
                    if($error == 0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";                    
                        $jResponse['data'] = $id_venta;
                        $code = "200";
                        DB::commit();
                        GOTO end;
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msgerror;                    
                        $jResponse['data'] = [];
                        $code = "202";
                        DB::rollback();
                        GOTO end;
                    } 
                }else if($opc == "2") {  
                    $datos = SalesData::showSales($id_venta);
                    foreach ($datos as $item){
                        $id_comprobante = $item->id_comprobante;
                    }
                    $id_comprobante_afecto = null; // Solo para notas de C/D.
                    $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
                    if(count($data) === 0) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";                    
                        $jResponse['data'] = NULL;
                        $code = "202";
                        DB::rollback();
                        GOTO end;
                    }
                    if(count($data) > 1) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";                    
                        $jResponse['data'] = NULL;
                        $code = "202";
                        DB::rollback();
                        GOTO end;
                    }

                    foreach ($data as $item){
                        $id_documento = $item->id_documento;
                        $ip = $item->ip;
                        $service_port = $item->puerto;
                    }

                    try{
                        $id_credito =$params->id_credito;
                        $efectivo = $params->efectivo;
                        $credito = $params->credito;
                        $tarjeta = $params->tarjeta;
                        $id_tipotarjeta = $params->id_tipotarjeta;
                        $operacion = $params->operacion;
                        $fecha_pago = $params->fecha_pago;//Fecha de pago de la Factura
                        $error = 0;
                        $msgerror = "";   
                        for($x=1;$x<=200;$x++){
                            $msgerror .= "0";
                        }

                        $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_CREDITO, :P_EFECTIVO, :P_CREDITO, :P_TARJETA, :P_ID_TIPOTARJETA, :P_OPERACION, :P_ERROR, :P_MSGERROR,:P_FECHA_PAGO); end;");
                        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);                        
                        $stmt->bindParam(':P_ID_CREDITO', $id_credito, PDO::PARAM_INT);
                        $stmt->bindParam(':P_EFECTIVO', $efectivo, PDO::PARAM_STR);
                        $stmt->bindParam(':P_CREDITO', $credito, PDO::PARAM_STR);
                        $stmt->bindParam(':P_TARJETA', $tarjeta, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                        $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                        $stmt->bindParam(':P_FECHA_PAGO', $fecha_pago, PDO::PARAM_STR);
                        $stmt->execute();                             
                        if($error == 0){
                            DB::commit();
                            $msn = "";
                            try{
                                usleep(2500000); //Dormir 2 segundos

                                if($id_entidad === "7124") {
                                    SalesData::UpdateSalesHash($id_venta);
                                }else{
                                    if($id_entidad === "9415") {
                                        SalesData::UpdateSalesHashAces($id_venta);
                                    }else{
                                        SalesData::UpdateSalesHashUPN($id_venta);
                                    }
                                }
                                
                                PrintData::deletePrint($id_user);//TODOS
                                PrintData::deleteTemporal($id_user);//TODOS
                                PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
                                PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
                                SalesData::addSalesParametersHead($id_venta,$id_user,$id_documento);
                                $cont = SalesData::addSalesParametersBody($id_venta,$id_user,$id_documento);
                                SalesData::addSalesParametersFoot($id_venta,$id_user,$id_documento,$cont);
                                $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
                                //SalesController::addPrintSales($id_user,$id_venta,$id_documento);
                                //SalesController::PrintSales($id_user,$id_comprobante);
                                $credit = SalesData::ShowSalesToCredit($id_venta);
                                if($credit === "0"){
                                    $cant = 1;
                                }else{
                                    $cant = 2;
                                }
                                for ($i = 1; $i <= $cant; $i++) {
                                    $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
                                }
                                $jResponse['success'] = true;
                                $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
                                $jResponse['data'] = [];
                                $code = "200";
                            }catch (Exception $e){
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = null;
                                $code = "202";
                            }
                        }else{
                            DB::rollback();
                            $jResponse['success'] = false;
                            $jResponse['message'] = $msgerror;
                            $jResponse['data'] = null;
                            $code = "202";
                        }                            
                    }catch (Exception $e){                            
                        $jResponse['success'] = false;
                        $jResponse['message'] = $e->getMessage();
                        $jResponse['data'] = null;
                        $code = "500";                            
                        DB::rollback();
                    }
                }                                        
            }catch(Exception $e){     
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }            
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function listSalesProducts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_venta = $request->query('id_venta');
                $producto = $request->query('producto');
                if($id_venta !== null && $producto !== null){
                    $venta = SalesData::showSales($id_venta);
                    foreach ($venta as $key => $item){
                        $id_cliente = $item->id_cliente;
                        $id_anho = $item->id_anho;
                    }
                    $id_almacen = 0;
                    $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                    foreach ($warehouse as $key => $item){
                        $id_almacen = $item->id_almacen;
                    }
                    // dd($id_cliente);
                    $data_precio = 0;
                    $data_precio_poli = 0;
                    $data=[];
                    if($id_cliente !== null){
                        $id_politica = 0;
                        $politica = PoliticsData::showPoliticsPersons($id_cliente);
                        foreach ($politica as $key => $item){
                            $id_politica = $item->id_politica;
                        }
                        $data = SalesData::listSalesProductsPolitics($id_politica,$id_anho,$producto);
                    }
                    if(count($data) === 0){
                        // dd($id_almacen, $id_anho, $producto);
                        $data = SalesData::listSalesProducts($id_almacen,$id_anho,$producto);
                    }
                    /*
                    if($id_cliente == null){
                        $data = SalesData::listSalesProducts($id_almacen,$id_anho,$producto);
                    }else{
                        $politica = PoliticsData::showPoliticsPersons($id_cliente);
                        foreach ($politica as $key => $item){
                            $id_politica = $item->id_politica;
                        }
                        $data = SalesData::listSalesProductsPolitics($id_politica,$id_anho,$producto);
                    }*/
                    if($data){
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'Product with Stock = 0 o The Product does not exist';
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

    


    public function addSalesDetailsProducts($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];   
            $msn_error = "";         
            try{
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                for($x=1;$x<=200;$x++){
                    $msn_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_VENTA_DETALLE_INVEN(:P_ID_VENTA, :P_ID_ARTICULO, :P_ID_ALMACEN, :P_CANTIDAD, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $params->id_articulo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();  
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
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
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }

   

    public function updateSalesDetailsProducts($id_venta,$id_vdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];      
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_UPDATE_VENTA_DETALLE_INVEN(:P_ID_VENTA, :P_ID_VDETALLE, :P_CANTIDAD, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);    
                $stmt->execute();                
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
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
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }

    

    public function printSales($id_user,$id_comprobante){
        
        
        PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento);//TODOS
        
    }
    public function print($id_user,$ip,$service_port){
        $result = false;
        $etiq = false;
        try{
            //$service_port = 7654;
            $data = PrintData::listDocumentsPrints($id_user);
            $texto="";	
            $texto.=chr(27); 
            $texto.=chr(15);
            $x="\n";
            $y="~";
            $nueva_data=str_replace($y,$x,$data);
            $texto.=$nueva_data;
            $texto=$texto.""."\n\n\n\n\n\n\n\n";
            $texto.=chr(27);
            $texto.=chr(105);
            $body = $texto;
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($socket));
            $result = socket_connect($socket, $ip, $service_port);
            $etiq = true;
            if ($result < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($result));
            socket_write($socket, $body, strlen($body));
            socket_close($socket);
            if($etiq){
                if($result){
                    $msn = "Ok";
                }else{
                    $msn = " OK pero no hay conexion a la ticketera ";
                }
            }else{
                $msn = "no hay cx";
            }
        }catch(Exception $e){
            if($etiq){
                if($result){
                    $msn = "Ok pero con problemas";
                }else{
                   $msn = " OK pero no hay conexion a la ticketera "; 
                }
            }else{
                $msn = "Error en la Estrucutra de Impresion";
            }
        }
        return $msn;
    }
    
    public function addTransfers(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $glosa = $params->glosa;
            $id_cliente = $params->id_cliente;
            $id_tipoventa = $params->id_tipoventa;
            $dc = $params->dc;
            $importe = $params->importe;

            $id_tipotransaccion = $params->id_tipotransaccion;
            $id_dinamica = $params->id_dinamica;
            $id_moneda = $params->id_moneda;
            $id_empleado = $params->id_empleado;
            $tiene_params = "S";

            $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,$tiene_params,$params);
            if($rpta["nerror"]==0){
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $id_transferencia = 0;
                try{   
                    $error = 0;
                    $msgerror = "";   
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA, :P_ID_EMPLEADO, :P_ID_CLIENTE, :P_ID_TIPOTRANSACCION, :P_ID_DINAMICA, :P_ID_MONEDA, :P_GLOSA, :P_ID_TIPOVENTA, :P_DC, :P_IMPORTE,  :P_ID_TRANSFERENCIA, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_EMPLEADO', $id_empleado, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);

                    $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_STR);
                    $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);


                    $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();                            
                    if($error == 0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Transferencia Registrada";                    
                        $jResponse['data'] = $id_transferencia;
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        if($error == 1){
                            $jResponse['message'] = "No Tiene Asignado Documento de Impresion";  
                        }else{
                            $jResponse['message'] = "Error al Registrar";  
                        }
                        $jResponse['data'] = [];
                        $code = "202";
                    }

                }catch(Exception $e){
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }     
        }        
        return response()->json($jResponse,$code);
    }
    public function addTransfersDetails($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $pdo = DB::getPdo();
                $params = json_decode(file_get_contents("php://input"));
                $venta = $params->venta;
                $opc = $params->opc;
                $detalle = "Documentos";
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
                if($opc == "1"){
                    foreach ($venta as $key => $item){
                        // dd($id_transferencia, $item->id_venta,  $item->importe, $item->dc, $item->detalle );
                        $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_DETALLE(:P_ID_TRANSFERENCIA, :P_ID_VENTA, :P_IMPORTE, :P_DC, :P_DETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                        $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_VENTA', $item->id_venta, PDO::PARAM_INT);
                        $stmt->bindParam(':P_IMPORTE', $item->importe, PDO::PARAM_STR);
                        $stmt->bindParam(':P_DC', $item->dc, PDO::PARAM_STR);
                        $stmt->bindParam(':P_DETALLE', $item->detalle, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }else{
                    $importe = $params->importe;
                    $dc = $params->dc;
                    $detalle = $params->detalle;
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_DETALLE(:P_ID_TRANSFERENCIA, :P_ID_VENTA, :P_IMPORTE, :P_DC, :P_DETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VENTA', null, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                    $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                    $stmt->execute();
                }
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Transferencia Registrada";                    
                    $jResponse['data'] = $id_transferencia;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
                    
        }        
        return response()->json($jResponse,$code);
    }
    public function listTransfersDetails($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{             
                //$id_venta = GlobalMethods::getSecret($id_venta);
                $data = SalesData::listTransfersDetails($id_transferencia);
                $datat = SalesData::listTransfersDetailsTotal($id_transferencia);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'total'=>$datat[0]];
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
    public function deleteTransfersDetails($id_transferencia,$id_tdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];            
            try{        
                $tipo = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_TRANSF_DETALLE(:P_ID_TRANSFERENCIA, :P_ID_TDETALLE, :P_TIPO); end;");
                $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TDETALLE', $id_tdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200";                
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateTransfers($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_user = $jResponse["id_user"]; 
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];      
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];
            $transf = SalesData::showTransfers($id_transferencia);
            $id_comprobante = '99';
            foreach ($transf as $key => $value){
                $id_dinamica = $value->id_dinamica;
            }
            $id_comprobante_afecto = null;
            // tener en cuenta
            $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
            if(count($data) === 0) {
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
                $jResponse['data'] = NULL;
                $code = "202";
                DB::rollback();
                GOTO end;
            }
            if(count($data) > 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
                $jResponse['data'] = NULL;
                $code = "202";
                DB::rollback();
                GOTO end;
            }
            foreach ($data as $item){
                $id_documento = $item->id_documento;
                $ip = $item->ip;
                $service_port = $item->puerto;
            }
            // find de tener en cuenta

            $edit="S";
            if($edit == "S"){
                $asiento = SalesData::listTransfersEntry($id_transferencia);
                if(count($asiento) > 0){
                    $valida = 0;
                }else{
                    $valida = 1;
                }
            }
            if($valida == 0){
                try{
                    $error = 0;
                    $msgerror = "";   
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }
                     
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_TRANSFERENCIA(:P_ID_TRANSFERENCIA, :P_ERROR, :P_MSNERROR); end;");
                    $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSNERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if($error == 0){
                        DB::commit();
                        PrintData::deletePrint($id_user);//TODOS
                        PrintData::deleteTemporal($id_user);//TODOS
                        PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
                        PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
                        SalesData::addTransfParametersHead($id_transferencia,$id_user,$id_documento); // new X
                        $cont = SalesData::addTransfParametersBody($id_transferencia,$id_user,$id_documento); // new X
                        SalesData::addTransfParametersFoot($id_transferencia,$id_user,$id_documento,$cont); //new
                        $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
                        $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msgerror;
                        $jResponse['data'] = [];
                        $code = "202";
                    }   
                   
                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                    DB::rollback();
                }
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = "Asiento Contable incompleto";
                $jResponse['data'] = [];
                $code = "202"; 
            }          
        }
        end:
        return response()->json($jResponse,$code);
    }


    public function addTransfersEntry($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{   
                $params = json_decode(file_get_contents("php://input"));
                $id_asiento = $params->id_asiento;
                $id_depto = $params->id_depto;
                $id_ctacte = $params->id_ctacte;
                $porcentaje = $params->porcentaje;
                $error = 0;
                $msn_error = "000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_ASIENTO(:P_ID_TRANSFERENCIA, :P_ID_ASIENTO, :P_ID_DEPTO, :P_ID_CTACTE, :P_PORCENTAJE, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ASIENTO', $id_asiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte , PDO::PARAM_STR);
                $stmt->bindParam(':P_PORCENTAJE', $porcentaje , PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Asiento de Transferencia Registrado";                    
                    $jResponse['data'] = $id_transferencia;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
                    
        }        
        return response()->json($jResponse,$code);
    }

    public function addTransfersEntryAs($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];
            try{   
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msgerror = "";   
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
                // var_dump($params, $id_transferencia);
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_ASIENTO(:P_ID_TRANSFERENCIA, :P_ID_DINAMICA, :P_ID_CUENTAAASI, :P_ID_RESTRICCION, :P_ID_CTACTE, :P_ID_FONDO, :P_ID_DEPTO, :P_IMPORTE, :P_IMPORTE_ME, :P_DESCRIPCION, :P_EDITABLE, :P_DC, :P_AGRUPA, :P_MODO,
                 :P_ERROR, 
                 :P_MSN); end;");
                $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI', $params->id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $params->id_restriccion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $params->id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_FONDO', $params->id_fondo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $params->id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $params->importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCRIPCION', $params->descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':P_EDITABLE', $params->editable, PDO::PARAM_STR);
                $stmt->bindParam(':P_DC', $params->dc, PDO::PARAM_STR);
                $stmt->bindParam(':P_AGRUPA', $params->agrupa, PDO::PARAM_STR);
                $stmt->bindParam(':P_MODO', $params->modo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Asiento de Transferencia Registrado";                    
                    $jResponse['data'] = $id_transferencia;
                    $code = "200";
                }else{
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;                    
                $jResponse['message'] =  $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
                $jResponse['data'] = [];
                $error = "202";
            }
                    
        }        
        return response()->json($jResponse,$code);
    }
    public function addTransfersEntryAsVnt($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
//            dd('->>>>');
            DB::beginTransaction();
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
//                 var_dump($params, $id_transferencia);
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_ASIENTO_VNT(:P_ID_VENTA,
                    :P_ID_DINAMICA,
                    :P_ID_CUENTAAASI,
                    :P_ID_RESTRICCION,
                    :P_ID_CTACTE,
                    :P_ID_FONDO,
                    :P_ID_DEPTO,
                    :P_IMPORTE,
                    :P_IMPORTE_ME,
                    :P_DESCRIPCION,
                    :P_EDITABLE,
                    :P_DC,
                    :P_AGRUPA,
                    :P_MODO,
                    :P_ERROR,
                    :P_MSN); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI', $params->id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $params->id_restriccion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $params->id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_FONDO', $params->id_fondo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $params->id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $params->importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCRIPCION', $params->descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':P_EDITABLE', $params->editable, PDO::PARAM_STR);
                $stmt->bindParam(':P_DC', $params->dc, PDO::PARAM_STR);
                $stmt->bindParam(':P_AGRUPA', $params->agrupa, PDO::PARAM_STR);
                $stmt->bindParam(':P_MODO', $params->modo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Asiento de Transferencia Registrado";
                    $jResponse['data'] = DB::table('VENTA_DETALLE')->where('ID_VENTA', $id_transferencia)->get();
                    $code = "200";
                }else{
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] =  $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
                $jResponse['data'] = [];
                $error = "202";
            }

        }
        return response()->json($jResponse,$code);
    }
    public function addTransfersEntryAsVntImport($id_transferencia, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
//            dd('->>>>');
//            DB::beginTransaction();
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $inserted = array();
                \Excel::load($request->excel, function($reader) use($id_transferencia, $error, $msgerror, &$inserted) {
                    $excel = $reader->get();
                    $data = array();
                    $reader->each(function($row)use($id_transferencia, $error, $msgerror, &$data) {
//                        var_export($row->cuentaaasi);
                        $params = [];
                            $params['id_venta'] = $id_transferencia;
                            $params['id_dinamica'] = 0;
                            $params['id_cuentaaasi'] = intval($row->cuentaaasi);
                            $params['id_restriccion'] = $row->restriccion;
                            $params['id_ctacte'] = $row->cta_cte;
                            $params['id_fondo'] = $row->fondo;
                            $params['id_depto'] = $row->depto;
                            $params['importe'] = $row->importe;
                            $params['importe_me'] = '';
                            $params['glosa'] = $row->glosa;
                            $params['editable'] = 'S';
                            $params['dc'] = $row->dc;
                            $params['agrupa'] = 'N';
                            $params['modo'] = '2';
                        $data[] = $result = SalesData::insertAsientoVenta((object)$params);
                    });
                    $inserted = $data;
                });

                $res = count(collect($inserted)->pluck('nerror')->filter()->all());
                $tot = count($inserted);

                    $jResponse['success'] = true;
                    $jResponse['message'] = "(".strval($res == 0 ? $tot : $res)."/".strval($tot)."), asientos insertados";
                    $jResponse['data'] = $inserted;
                    $code = "200";

            }catch(Exception $e){
//                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] =  $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listTransfersEntry($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = SalesData::listTransfersEntry($id_transferencia);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function deleteTransfersEntry($id_transferencia,$id_tasiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];            
            try{        
                $tipo = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_TRANSF_ASIENTO(:P_ID_TRANSFERENCIA, :P_ID_TASIENTO, :P_TIPO); end;");
                $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TASIENTO', $id_tasiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200";                
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listTransfers(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                if($id_entidad != null && $id_depto != null && $id_anho != null && $id_mes != null){
                    $data = SalesData::listTransfers($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Attention: Check, missing parameters";                        
                    $jResponse['data'] = [];
                    $code = "203";
                    
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
    public function listMyTransfers(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                if($id_mes != null){
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                        $id_anho_actual = $item->id_anho_actual;                
                    }
                    $data = SalesData::listMyTransfers($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher,$id_user);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Attention: Check, missing parameters";                        
                    $jResponse['data'] = [];
                    $code = "203";
                    
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
    public function addTransfersImports(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                //$params = json_decode(file_get_contents("php://input"));
                $id_tipotransaccion = $request->id_tipotransaccion;
                $id_dinamica = $request->id_dinamica;
                $id_moneda = $request->id_moneda;
                $tiene_params = "S";
                //VALIDA AÑO, MES, TC, Y PARAMETROS
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,$tiene_params,$request);
                
                if($rpta["nerror"]==0){
                    $dato["id_entidad"] = $id_entidad;
                    $dato["id_depto"]   = $id_depto;
                    $dato["id_anho"]    = $rpta["id_anho"];
                    $dato["id_mes"]     = $rpta["id_mes"];
                    $dato["id_user"]    = $id_user;
                    $dato["id_moneda"]  = $id_moneda;
                    $dato["id_tipotransaccion"] = $id_tipotransaccion;
                    $dato["id_dinamica"]         = $id_dinamica;
                    //SE ELIMINA REGISTROS INCOMPLETOS
                    SalesData::deleteTransfersImports($id_entidad,$id_depto,$rpta["id_anho"],$id_user);
                    \Excel::load($request->excel, function($reader) use($dato) {
                        $excel = $reader->get();
                        $reader->each(function($row)use($dato) {
                            $documento = $row->codigo;
                            $id_cliente = PersonData::showStudentsPersons($documento);                
                            if($id_cliente != 0){
                                $glosa = $row->glosa;
                                $dc   = $row->dc;
                                $importe   = $row->importe;
                                $error = 0;
                                $id_transferencia = 0;
                                $pdo = DB::getPdo();
                                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_IMP(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA, :P_ID_CLIENTE, :P_ID_TIPOTRANSACCION, :P_ID_DINAMICA, :P_ID_MONEDA, :P_GLOSA, :P_IMPORTE, :P_DC, :P_ID_TRANSFERENCIA, :P_ERROR); end;");
                                $stmt->bindParam(':P_ID_ENTIDAD', $dato["id_entidad"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_DEPTO', $dato["id_depto"], PDO::PARAM_STR);
                                $stmt->bindParam(':P_ID_ANHO', $dato["id_anho"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MES', $dato["id_mes"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_PERSONA', $dato["id_user"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $dato["id_tipotransaccion"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_DINAMICA', $dato["id_dinamica"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MONEDA', $dato["id_moneda"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                                $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                $stmt->execute();
                            }
                         });
                    });
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }    
        }        
        return response()->json($jResponse,$code);
    }
    public function listTransfersImports(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_moneda = 7;
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,'N',null);
                if($rpta["nerror"]==0){
                    $data = SalesData::listTransfersImports($id_entidad,$id_depto,$rpta["id_anho"],$rpta["id_mes"],$id_user);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
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
    public function updateTransfersImports(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_moneda = 7;
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,'N',null);
            if($rpta["nerror"]==0){
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
            }

                DB::beginTransaction();
                try{
                    $error = 0;
                    $msgerror = "";   
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }
                     
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_TRANSFERENCIA_IMP(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA, :P_ERROR, :P_MSNERROR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSNERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if($error == 0){
                        DB::commit();
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msgerror;
                        $jResponse['data'] = [];
                        $code = "202";
                    }   
                   
                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                    DB::rollback();
                }          
        }
        return response()->json($jResponse,$code);
    }
    public function listMySales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_anho = $request->query('id_anho');
                $admin = $request->query('admin');
                if($id_mes != null){
                    if (empty($id_anho)) {
                        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                        foreach ($data_anho as $item){
                            $id_anho = $item->id_anho;
                            $id_anho_actual = $item->id_anho_actual;                
                        }
                    }
                    $data = SalesData::listMySales($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher,$id_user, $admin);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Attention: Check, missing parameters";                        
                    $jResponse['data'] = [];
                    $code = "203";
                    
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

    

    public function listMyNotes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                if($id_mes != null){
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                        $id_anho_actual = $item->id_anho_actual;
                    }
                    $data = SalesData::listMyNotes($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher,$id_user);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Attention: Check, missing parameters";
                    $jResponse['data'] = [];
                    $code = "203";

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
    public function listTypesNotes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_comprobante = $request->query('id_comprobante');
                if ($id_comprobante == '87') {
                    $id_comprobante = '07' ;
                }
                if ($id_comprobante == '88') {
                    $id_comprobante = '08' ;
                }
                $data = SalesData::listTypesNotes($id_comprobante);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function addNotes(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_cliente = $params->id_cliente;
            $id_venta = $params->id_venta;
            $id_comprobante = $params->id_comprobante;
            $id_tiponota = $params->id_tiponota;
            $id_tipotransaccion = $params->id_tipotransaccion;
            $id_moneda = 7;
            $serie_ref = $params->serie_ref;
            $numero_ref = $params->numero_ref;
            $glosa = $params->glosa;
            $id_comprobante_ref = $params->id_comprobante_ref;
            $tiene_params = "S";
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,$tiene_params,$params);
            if($rpta["nerror"]==0){
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $id_nota = 0;
                try{   
                    $error = 0;
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_NOTAS(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_ANHO,:P_ID_MES,:P_ID_VENTA,:P_ID_PERSONA,:P_ID_CLIENTE,:P_ID_COMPROBANTE,:P_ID_TIPONOTA,:P_ID_TIPOTRANSACCION,:P_SERIE_REF,:P_NUMERO_REF,:P_GLOSA,:P_ID_COMPROBANTE_REF,:P_ID_NOTA,:P_ERROR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPONOTA', $id_tiponota, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_SERIE_REF', $serie_ref, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NUMERO_REF', $numero_ref, PDO::PARAM_STR);
                    $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_COMPROBANTE_REF', $id_comprobante_ref, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_NOTA', $id_nota, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->execute();                            
                    if($error == 0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Nota Registrada";                    
                        $jResponse['data'] = $id_nota;
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        if($error == 1){
                            $jResponse['message'] = "No Tiene Asignado Documento de Impresion";  
                        }else{
                            $jResponse['message'] = "Error al Registrar";  
                        }
                        $jResponse['data'] = [];
                        $code = "202";
                    }

                }catch(Exception $e){
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }     
        }        
        return response()->json($jResponse,$code);
    }
    public function addNotasDetails($id_nota){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_dinamica = $params->id_dinamica;
                $detalle = $params->detalle;
                $cantidad = $params->cantidad;
                $precio = $params->precio;
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_NOTAS_DETALLE(:P_ID_VENTA, :P_ID_DINAMICA, :P_DETALLE, :P_CANTIDAD, :P_PRECIO, :P_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_nota, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_PRECIO', $precio, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    if($error == 1){
                        $jResponse['message'] = "Importe de la Nota Mayor al SALDO DEL DOCUMENTO";  
                    }else{
                        $jResponse['message'] = "El Comprobante NO ES UNA NOTA DE DEBITO O CREDITO";  
                    }
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
    public function updateNotes($id_nota){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            //$params = json_decode(file_get_contents("php://input"));
            try{   
                $error = 0;
                $msgerror = "";   
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_NOTAS(:P_ID_VENTA, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_nota, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();                            
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Nota Finzalizada con exito";                    
                    $jResponse['data'] = $id_nota;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;                    
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }    
        }        
        return response()->json($jResponse,$code);
    }
    public function listNotasDetails($id_nota){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{             
                //$id_venta = GlobalMethods::getSecret($id_venta);
                $data = SalesData::listSalesDetails($id_nota);
                $datat = SalesData::listSalesDetailsTotal($id_nota);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data,'total'=>$datat];
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
    public function deleteNotesDetails($id_nota,$id_vdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{        
                $tipo = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_DETALLE(:P_ID_VENTA, :P_ID_VDETALLE, :P_TIPO); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_nota, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200";                
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listMySalesArrangements(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_anho = 0;  
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                if($rpta["nerror"]==0){
                    $id_anho = $rpta["id_anho"];
                }
                $id_voucher = $request->query('id_voucher');
                $id_mes = $request->query('id_mes');
                $data = SalesData::listMySalesArrangements($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function spCancelSales($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $estado = "";
                $sales = SalesData::showSalesStatus($id_venta);
                foreach ($sales as $key => $value){
                    $id_comprobante = $value->id_comprobante;
                    $numero_legal = $value->numero_legal;
                }
                //Valida si ES ENTIDAD 7124
                if($id_entidad == 7124){
                    //Usar cx a efac: 192.168.13.235, para ver el estado del comprobante
                    $sale_efac = SalesData::showSaleEfac($id_comprobante,$numero_legal,'T');
                    foreach ($sale_efac as $key => $value){
                        $estado = $value->estado;
                    }
                    if($estado == "" || $estado == null){
                        $estado = "X";
                    }
                }else{//CONSULTAR A OTRO ORIGEN
                    $estado = "X";//POR AHORA
                }
                

                if($estado == "PD" || $estado == "ER" || $estado == "PB" || $estado == "DB" || $estado == "RH" || $estado == "X" || $estado == "AN"){
                    $rpta = SalesData::spCancelSales($id_venta);
                    if ($rpta['error'] == 0) {
                        if($id_entidad == 7124){
                            if($estado == "PD"  || $estado == "ER"){ // SE TIENE QUE ANALIZAR PARA ANULAR DIREECTAMENTE , POR AHORA SE ANULA EN EL EFAC
                                //ANULA EN EL EFAC
                                SalesData::updateSaleEfac($id_comprobante,$numero_legal,$estado);
                            }
                        }
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was canceled successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $rpta['message'];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ALTO: NO SE PUEDE ANULAR LA VENTA, SU ESTADO ES: " .$estado;
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
    public function creditPersonal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            $id_depto = $request->id_depto;
            //  $id_mes = $request->id_mes;
             $id_voucher = $request->id_voucher;
            try{
                $data = SalesData::creditPersonal($id_depto, $id_voucher);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public function creditPersonalPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        // $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
     
        if($valida == 'SI')
        {
            $mensaje = '';
            $jResponse = [];
            try
            {
                $id_depto = $request->id_depto;
                $id_voucher = $request->id_voucher;
                $id_anho = 0;  
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                if($rpta["nerror"]==0){
                    $id_anho = $rpta["id_anho"];
                }
                $data = SalesData::creditPersonal($id_depto, $id_voucher);
                
                //dd($order);
                    
                $pdf = DOMPDF::loadView('pdf.sales.sale',[
                    'data'=>$data,
                    'username'=>$username // OBLIGATORIO
                    // ])->setPaper('a4', 'landscape');
                    ])->setPaper('a4', 'portrait');
                

                $doc =  base64_encode($pdf->stream('print.pdf'));
           
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
                ];
        
                return response()->json($jResponse);
            }
            catch(Exception $e)
            {
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
    
            }
        }else{
            $mensaje=$jResponse["message"];
        }
            
        $pdf = DOMPDF::loadView('pdf.error',[
                    'mensaje'=>$mensaje
                    ])->setPaper('a4', 'portrait');
                    // dd($pdf);
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
                ];
        return response()->json($jResponse);
            
    }
    public function familiasVendidas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
    

        if($valida=='SI'){
            $jResponse =[];
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            // $id_almacen = $request->id_almacen;
             $fecha_de = $request->fecha_de;
             $fecha_a = $request->fecha_a;
            try{
                $data = SalesData::familiasVendidas($id_almacen, $fecha_de, $fecha_a);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public function productosFamiliasVendidas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse =[];

            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            // $id_almacen = $request->id_almacen;
             $fecha_de = $request->fecha_de;
             $fecha_a = $request->fecha_a;
            try{
                $data = SalesData::productosFamiliasVendidas($id_almacen, $fecha_de, $fecha_a);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public function productosVendidasPDF(Request $request){
    
    
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];
     
        if($valida == 'SI')
        {
            $mensaje = '';
            $jResponse = [];
            
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            try
            {
                $fecha_de = $request->fecha_de;
                $fecha_a = $request->fecha_a;
                $id_anho = 0;  
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                if($rpta["nerror"]==0){
                    $id_anho = $rpta["id_anho"];
                }
              
                $dataProducto = SalesData::productosFamiliasVendidas($id_almacen, $fecha_de, $fecha_a);
                
                // dd($dataProducto);
                    
                $pdf = DOMPDF::loadView('pdf.sales.producto',[
                    'dataProducto'=>$dataProducto,
                    'username'=>$username // OBLIGATORIO
                    // ])->setPaper('a4', 'landscape');
                    ])->setPaper('a4', 'portrait');
                    // dd(  $pdf);

                $doc =  base64_encode($pdf->stream('print.pdf'));
           
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
                ];
        
                return response()->json($jResponse);
            }
            catch(Exception $e)
            {
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
    
            }
        }else{
            $mensaje=$jResponse["message"];
        }
            
        $pdf = DOMPDF::loadView('pdf.error',[
                    'mensaje'=>$mensaje
                    ])->setPaper('a4', 'portrait');
                    // dd($pdf);
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
                ];
        return response()->json($jResponse);
            
    }
    public function voucheDinamico(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse =[];
            $id_anho = $request->id_anho;
             $id_mes = $request->id_mes;
             $id_depto = $request->id_depto;
            try{
                $data = SalesData::voucheDinamico($id_entidad, $id_anho, $id_mes, $id_depto);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public function listMyDepartmentSales(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SalesData::listMyDepartmentSales($id_entidad,$id_persona);
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
        public function mesConMasVentas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse =[];
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
             $id_anho = $request->id_anho;
            try{
                $data = SalesData::mesConMasVentas($id_anho, $id_almacen);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    /// METODO PARA GENERAR PDF DE SALES RECORD
    public function salesRecordPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $username   =  $jResponse["email"];

        if($valida=='SI'){
            $jResponse=[];            
            try{                
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_anho = $request->query('id_anho');
                
                if (empty($id_anho)) {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;                
                }
                 }
                $data = SalesData::salesRecord($id_entidad,$id_depto,$id_anho,$id_mes,$id_voucher);
                $voucher = collect(AccountingData::showVoucher($id_voucher))->first();
                $jResponse['success'] = true;
                if(count($data)>0){
                    // $customPaper = array(0,0,720,1000); // horizontal
                    $customPaper = array(0,0,1050,770); // verti

                    $pdf = DOMPDF::loadView('pdf.sales.record',[
                        'data'=>$data,
                        'voucher'=>$voucher,
                        'username'=>$username // OBLIGATORIO
                        // ])->setPaper($customPaper, 'landscape');
                        ])->setPaper($customPaper, 'landscape');

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            } 
        } else {
            $mensaje=$jResponse["message"];
        }        
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }
    public function cajeroTop(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            try{
               
                $fecha_de = $request->fecha_de;
                $fecha_a = $request->fecha_a;
                $data = SalesData::cajeroTop($id_entidad, $id_almacen, $fecha_de, $fecha_a);
                // dd($data);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Success';
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
  
    public function clienteTop(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            try{
               
                $fecha_de = $request->fecha_de;
                $fecha_a = $request->fecha_a;
                $data = SalesData::clienteTop($id_entidad, $id_almacen, $fecha_de, $fecha_a);
                // dd($data);
                if ($data) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'Success';
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
    public function listInventoriesCosts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_voucher = $request->query('id_voucher');
                $data = SalesData::listInventoriesCosts($id_voucher);
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
    // para generar el pdf
    public function listInventoriesCostsPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];

        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $data = SalesData::listInventoriesCosts($id_voucher);

                if ($data){
                    $pdf = DOMPDF::loadView('pdf.sales.costs',[
                        'data'=>$data,
                        'username'=>$username // OBLIGATORIO
                        ])->setPaper('a4', 'portrait'); 

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        } else {
            $mensaje=$jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }
    public function lisSalesSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');

                $resumen = SalesData::lisSalesSummary($id_voucher);
                $resumenD = SalesData::lisSalesSummaryCost($id_voucher);
                $total = SalesData::lisSalesSummaryTotal($id_voucher); 
                $totalD = SalesData::lisSalesSummaryTotalcost($id_voucher); 
                if ($resumen){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [
                        'items' => $resumen,
                        'total'=>$total,
                        'itemsD' => $resumenD,
                        'totalD'=>$totalD,
                    ];
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

    // para generar el pdf
    public function lisSalesSummaryPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $numero = $request->query('numero');
                $lote = $request->query('lote');
                $fecha = $request->query('fecha');

 
                $resumen = SalesData::lisSalesSummary($id_voucher);
                $resumenD = SalesData::lisSalesSummaryCost($id_voucher);
                $totalD = SalesData::lisSalesSummaryTotalcost($id_voucher); 
                $total = SalesData::lisSalesSummaryTotal($id_voucher);
                if ($resumen){
                    $pdf = DOMPDF::loadView('pdf.sales.summarySales',[
                        'resumen'=>$resumen,
                        'id_depto'=>$id_depto,
                        'total'=>$total,
                        'resumenD'=>$resumenD,
                        'totalD'=>$totalD,
                        'numero'=>$numero,
                        'fecha'=>$fecha,
                        'lote'=>$lote,
                        'username'=>$username // OBLIGATORIO
                        ])->setPaper('a4', 'portrait'); 

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        } else {
            $mensaje=$jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }
    
    // lista normal
    public function lisSalesDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $items = SalesData::lisSalesDetails($id_voucher);
                $total = SalesData::lisSalesSummaryTotal($id_voucher); 
                if ($items){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ['items' => $items,'total'=>$total];
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
        return response()->json($jResponse);
    }

    // para generar el pdf
    public function lisSalesDetailsPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];

        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $numero = $request->query('numero');
                $lote = $request->query('lote');
                $fecha = $request->query('fecha');


                $items = SalesData::lisSalesDetails($id_voucher);
                $total = SalesData::lisSalesSummaryTotal($id_voucher); 
                if ($items){
                    $pdf = DOMPDF::loadView('pdf.sales.detailsSales',[
                        'items'=>$items,
                        'total'=>$total,
                        'numero'=>$numero,
                        'fecha'=>$fecha,
                        'lote'=>$lote,
                        'username'=>$username // OBLIGATORIO
                        ])->setPaper('a4', 'portrait'); 

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        } else {
            $mensaje=$jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }
    public function searchSerie(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidadSession = $jResponse["id_entidad"];
        $id_persona = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $search = $request-> query('search');
            // dd($search);
            // $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_persona);
            // foreach ($warehouse as $key => $item){
            //     $id_almacen = $item->id_almacen;
            // }
            if ($search) {
                $data = SalesData::searchSerie($id_entidadSession, $search);
            } else {
               $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The search is succesfull';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchSerieNumero(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            // dd($request);
            $serie = $request-> query('serie');
            $search = $request-> query('search');
            if ($search) {
                $data = SalesData::searchSerieNumero($serie, $search);
            } else {
               $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The search is succesfull';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listDetalleVenta(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                // dd($request->id_venta);
                $id_venta = $request->id_venta;
                $data = SalesData::listDetalleVenta($id_venta);  
                // dd($data);
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
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

    public function addNotesSales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];


        $id_entidad = $request->id_entidad;
        // dd($id_entidad);
        if($valida=='SI'){
            $jResponse=[];              
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;                
            }
            if($id_anho !== $id_anho_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un año activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item){
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;                
            }
            if($id_mes !== $id_mes_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un mes activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_persona);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }
            // dd($id_entidad,$id_persona);
            if(count($warehouse) === 0){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, El usuario no tiene asignado un almacén.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
             }
            if(count($warehouse) > 1){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, Hay mas de un almacén asignado al usuario.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            
            $tipcam["tc"] = false;
            $id_moneda = 9;
            if($id_moneda === '9') {
                $tipcam = GlobalMethods::verificaTipoCambio();
            } else {
                $tipcam["tc"] = true;
            }
            if($tipcam["tc"] === false){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, actualice el tipo de cambio.";                        
                $jResponse['data'] = [];
                $code = "202";
                GOTO end;
            }
            $id_venta            =    $request->id_venta;
            $id_comprobante      =    $request->id_comprobante;
            $id_tiponota         =    $request->id_tiponota;
            $glosa               =    $request->glosa;
            $detail              =    $request->detail;

            $aid_vdetalle        =    array();
            $acantidad           =    array();

            foreach ($request->detail as $datos) {

                $items           =    (object)$datos;
                $aid_vdetalle[]  =    $items->id_vdetalle;
                $acantidad[]     =    $items->cantidad2;

            }

            $id_vdetalle         =    implode("|", $aid_vdetalle);
            $cantidad            =    implode("|", $acantidad);
            // dd($id_vdetalle, $cantidad, $id_anho, $id_mes);
            $sid_venta = 0;
            $msgerror = "";   
            DB::beginTransaction();
            try{
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES.SP_CREAR_NOTA_INV(
                    :P_ID_VENTA_ORI,
                    :P_ID_COMPROBANTE,
                    :P_ID_TIPONOTA,
                    :P_GLOSA,
                    :P_ID_ANHO,
                    :P_ID_MES,
                    :P_ID_PERSONA,
                    :P_ID_VDETALLE,
                    :P_CANTIDAD,
                    :P_ID_VENTA,
                    :P_ERROR,
                    :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VENTA_ORI', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_TIPONOTA', $id_tiponota, PDO::PARAM_STR);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VENTA', $sid_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();                            
                if($error !== 0){
                    throw new Exception($msgerror, 1);
                }
                    
                $response = $this->finishNotesSales($sid_venta,$id_comprobante,$id_persona,$id_entidad);
                if(!$response['success']){
                    throw new Exception($response['message'], 1);
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "Se guardo con exito";                    
                $jResponse['data'] = $sid_venta;
                $code = "200";
                                    
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }      
        end:  
        return response()->json($jResponse,$code);
    }
    
    private function finishNotesSales($id_venta,$id_comprobante,$id_user,$id_entidad) {
    
          
        $pdo = DB::getPdo();
                
        $datos = SalesData::showSales($id_venta);
        $id_depto='';
        $id_parent='';
        foreach ($datos as $item){
            $id_depto = $item->id_depto;
            $id_parent = $item->id_parent;
        }

        // venta origen.
        $datosParent = SalesData::showSalesStatus($id_parent);
        $id_comprobante_afecto='';
        foreach ($datosParent as $item2){
            $id_comprobante_afecto = $item2->id_comprobante;
        }

        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
        if(count($data) === 0) {
            $jResponse['success'] = false;
            $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante] que afecta a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
            $jResponse['data'] = NULL;

            GOTO end;
        }
        if(count($data) > 1) {
            $jResponse['success'] = false;
            $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante] que afecte a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
            $jResponse['data'] = NULL;

            GOTO end;
        }

        foreach ($data as $item){
            $id_documento = $item->id_documento;
            $ip = $item->ip;
            $service_port = $item->puerto;
        }

        
        $id_credito = 2; //$params->id_credito;
        $efectivo = 0;
        $credito = 0;
        $tarjeta = 0;
        $id_tipotarjeta = '';
        $operacion = '';
        $error = 0;
        $msgerror = "";   
        for($x=1;$x<=200;$x++){
            $msgerror .= "0";
        }

        $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_CREDITO, :P_EFECTIVO, :P_CREDITO, :P_TARJETA, :P_ID_TIPOTARJETA, :P_OPERACION, :P_ERROR, :P_MSGERROR); end;");
        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);                        
        $stmt->bindParam(':P_ID_CREDITO', $id_credito, PDO::PARAM_INT);
        $stmt->bindParam(':P_EFECTIVO', $efectivo, PDO::PARAM_STR);
        $stmt->bindParam(':P_CREDITO', $credito, PDO::PARAM_STR);
        $stmt->bindParam(':P_TARJETA', $tarjeta, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
        $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();                             
        if($error == 0){
         
            $msn = "";
           ////////////////
            usleep(2500000); //Dormir 2 segundos

            if($id_entidad === "7124") {
                SalesData::UpdateSalesHash($id_venta);
            }else{
                if($id_entidad === "9415") {
                    SalesData::UpdateSalesHashAces($id_venta);
                }else{
                    SalesData::UpdateSalesHashUPN($id_venta);
                }

                PrintData::deletePrint($id_user);//TODOS
                PrintData::deleteTemporal($id_user);//TODOS
                PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
                PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
                SalesData::addSalesParametersHead($id_venta,$id_user,$id_documento);
                $cont = SalesData::addSalesParametersBody($id_venta,$id_user,$id_documento);
                SalesData::addSalesParametersFoot($id_venta,$id_user,$id_documento,$cont);
                $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
                //SalesController::addPrintSales($id_user,$id_venta,$id_documento);
                //SalesController::PrintSales($id_user,$id_comprobante);
                $credit = SalesData::ShowSalesToCredit($id_venta);
                if($credit === "0"){
                    $cant = 1;
                }else{
                    $cant = 2;
                }
                for ($i = 1; $i <= $cant; $i++) {
                    $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
                }
            }/////////////////////////
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
            $jResponse['data'] = [];
        }else{
    
            $jResponse['success'] = false;
            $jResponse['message'] = $msgerror;
            $jResponse['data'] = null;
 
        }                            
        end:
        return $jResponse; 
    }
    public function showSaleEfac(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $tipo_documento = $request-> query('tipo_documento');
            $numero_legal = $request-> query('numero_legal');
            if ($numero_legal) {
                $data = SalesData::showSaleEfac($tipo_documento,$numero_legal);
            } else {
               $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The search is succesfull';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listSalesSeries(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');

                $serie = SalesData::listSalesSeries($id_voucher); 
                if ($serie){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $serie;
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
    public function listSalesEfac(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $serie = $request->query('serie');
                $estado = $request->query('estado');
                $parent = [];
                // dd($estado);
                $sales = SalesData::listSalesEfac($id_voucher,$serie);
                foreach ($sales as $key => $value){
                    $efac = SalesData::showSaleEfac($value->id_comprobante,$value->numero_legal,$estado);
                    if(count($efac)>0){//ESTA EN EFAC
                        foreach ($efac as $row){
                            $parent[] = $row;
                        }
                    }else{//NO ESTA EN EFAC
                        $parent[] = [
                                    'id_venta' => $value->id_venta, 
                                    'id_comprobante' => $value->id_comprobante, 
                                    'fecha' => $value->fecha,
                                    'numero_legal' => $value->numero_legal,
                                    'total' => $value->total,
                                    'estado' => $value->estado
                                ]; 
                    }         
                }
                if ($parent){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $parent;
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
    public function sendSalesEfac(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_venta = $params->id_venta;
                $id_comprobante = $params->id_comprobante;
                
                $sales = SalesData::showSalesStatus($id_venta);
                if(count($sales)>0){
                    $rpta = SalesData::sendSalesEfac($id_venta,$id_comprobante);
                    if ($rpta['error'] == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The Sales was send successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $rpta['message'];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe la Venta";
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
    public function listAccountStatus(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{       
                $id_persona = $request->query('id_persona');
                $id_anho = $request->query('id_anho');
                $id_mesDe = $request->query('id_mes_de');
                $id_mesA = $request->query('id_mes_a');
                $data = SalesData::listAccountStatus($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
                $datat = SalesData::listAccountStatusTotal($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
                $saldoFinal=  SalesData::statusSaldoFinalAlumno($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
                $jResponse['success'] = true;
                if($jResponse['success']){
                    $jResponse['message'] = "Succes";  
                    $da = collect($data)->groupBy('tipo_venta'); 
                    $datar = array();
                    foreach ($da as $key => $value) {
                        array_push($datar, ['tipo' => $key, 'data' => $value]);
                    }          
                    $jResponse['data'] = [
                    'data' => $datar,
                    'items' => collect($data)->groupBy('tipo_venta'),
                    'total'=> collect($datat)->groupBy('tipo_venta'),
                    'saldo_final' => $saldoFinal];
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


    public function advancesStaff(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            //  $id_depto = $request->id_depto;
             $id_anho = $request->id_anho;
             $id_mes = $request->id_mes;
            //  $id_voucher = $request->id_voucher;
            try{
                $data = SalesData::advancesStaff($id_anho, $id_mes);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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

    public function listTypesSales(Request $request){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){

            try{
                $data = SalesData::listTipesSales();


                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public function studenAcademic(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            //  $id_depto = $request->id_depto;
            $nombre = $request->nombre;
            //  $id_voucher = $request->id_voucher;
            try{
                $data = SalesData::studenAcademic($nombre);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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
    public  function insertNotesStudent(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $persona_reg = $jResponse["id_user"];
        $entidad = $jResponse["id_entidad"];
        $depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];

            $data_anho = AccountingData::showPeriodoActivo($entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if($id_anho !== $id_anho_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un año activo.";
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $data_mes = AccountingData::showMesActivo($entidad, $id_anho);
            foreach ($data_mes as $item){
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;
            }
            if($id_mes !== $id_mes_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un mes activo.";
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            try{
                $return  =  SalesData::insertNotesStudent($request, $persona_reg, $entidad, $depto, $id_anho, $id_mes);
                if ($return['nerror']==0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $return['id_nota'];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
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
        end:
        return response()->json($jResponse,$code);
    }
    public  function insertAsiento(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                      
            try{   
                $return  =  SalesData::insertAsiento($request);  
                if ($return['nerror']==0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";  
                } else {
                  $jResponse['success'] = false;
                  $jResponse['message'] = $return['msgerror'];
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
    public function getAsientoAlumnoNotas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            //  $id_depto = $request->id_depto;
            $id_venta = $request->id_venta;
            //  $id_voucher = $request->id_voucher;
            try{
                $data = SalesData::getAsientoAlumnoNotas($id_venta);
                $sumaD = SalesData::sumAsientoD($id_venta);
                $sumaC = SalesData::sumAsientoC($id_venta);
                if ($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [
                        'items' => $data,
                        'sumD' => collect($sumaD)->sum('importe'),
                        'sumC' => collect($sumaC)->sum('importe'),
                        'resultDC' => collect($data)->sum('importe'),
                    ];
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
    public  function finalizarDCNotasAlumnos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();
            try{
                // dd($id_persona);
                $id_venta                       = $request->id_venta;
                $id_comprobante                 = $request->id_comprobante;

                $return  =  SalesData::finalizarDCNotasAlumnos($request);
                if ($return['nerror']==0) {
                    $response = SalesController::printTicketeraNoteCredit($id_venta,$id_comprobante,$id_persona,$id_entidad, $id_depto);
                    // dd($id_venta,$id_comprobante,$id_persona,$id_entidad);

                    if($response['success']){
                        DB::commit();
                        $jResponse['success'] = true;
                        $jResponse['message'] = $response['message'];                    
                        $jResponse['data'] = $id_venta;
                        $code = "200";
                    }else{
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $response['message'];                    
                        $jResponse['data'] = [];
                        $code = "202";
                    }

                    // $jResponse['success'] = true;
                    // $jResponse['message'] =$return['nerror'];
                    // $jResponse['data'] = [];
                    // $code = "200";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
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
    public function deleteAsientoAlumnoNotas($id_vasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse=[];
            try
            {
                $result = SalesData::deleteAsientoAlumnoNotas($id_vasiento);
                if($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateAsientoAlumnoNotas($id_vasiento, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse=[];
            try
            {
                $result = SalesData::updateAsientoAlumnoNotas($id_vasiento, $request);
                if($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateSaleSeat($id_vasiento, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
//        dd('');
        if($valida == 'SI')
        {
            $jResponse=[];
            try
            {
                $result = SalesData::updateSaleSeat($id_vasiento, $request);
                if($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

public function addAsientosImportsExcel(Request $request){
    // dd($request->excel);
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[]; 
        // dd( $request);
        try{
            $datin =  \Excel::load($request->excel);
            // dd($datin);
            $data = $datin->toArray();
            $listaValid = array();
            $i = 0;
            foreach($data as $d){
                if($i>=0){
                    $obj = [
                        'id_venta'                  =>    $request->id_venta,
                        'id_dinamica'               =>    '',
                        'id_cuentaaasi'             =>    $d['cuentaaasi'],
                        'id_restriccion'            =>    $d['restriccion'],
                        'id_ctacte'                 =>    $d['cta_cte'],
                        'id_fondo'                  =>    $d['fondo'],
                        'id_depto'                  =>    $d['depto'],
                        'importe'                   =>    $d['importe'],
                        'importe_me'                =>    '',
                        'descripcion'               =>    $d['glosa'],
                        'editable'                  =>    'S',
                        'dc'                        =>    $d['dc'],
                        'agrupa'                    =>    'N',
                        'modo'                      =>    '2',
                    ];
                    $listaValid[] =$obj;
                }
                $i++;
            }
           
            $return = SalesData::insertAsientoExcel($listaValid);
            if ($return['nerror']==0) {
                $jResponse['success'] = true;
                $jResponse['message'] = $return['msgerror'];                 
                $jResponse['data'] = [];
                $code = "200";  
            } else {
              $jResponse['success'] = false;
              $jResponse['message'] = $return['msgerror'];
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
private function printTicketeraNoteCredit($id_venta,$id_comprobante,$id_user,$id_entidad, $id_depto) {
    // dd($id_venta,$id_comprobante,$id_user,$id_entidad);
    $datosParent = SalesData::showSalesStatus($id_venta);
    $id_parent='';
    foreach ($datosParent as $item2){
        $id_parent = $item2->id_parent;
    }
    if (empty($id_parent)) {
        $id_parent = 0;
    }
    // venta origen.
    $datosParent = SalesData::showSalesStatus($id_parent);
    $id_comprobante_afecto='';
    foreach ($datosParent as $item2){
        $id_comprobante_afecto = $item2->id_comprobante;
    }
    if ($id_comprobante_afecto == '' && $id_entidad == "7124") {
        $id_comprobante_afecto = '03';
    }

    $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
        // dd($id_venta,$id_comprobante,$id_user,$id_entidad);
    if(count($data) === 0) {
        $jResponse['success'] = false;
        $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante] que afecte a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
        $jResponse['data'] = NULL;

        GOTO end;
    }
    if(count($data) > 1) {
        $jResponse['success'] = false;
        $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante] que afecta a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
        $jResponse['data'] = NULL;

        GOTO end;
    }

    foreach ($data as $item){
        $id_documento = $item->id_documento;
        $ip = $item->ip;
        $service_port = $item->puerto;
    }
                
        $msn = "";
       ////////////////
        usleep(2500000); //Dormir 2 segundos

        ///Habilitar has para subir a produccion

        if($id_entidad === "7124") {
            SalesData::UpdateSalesHash($id_venta);
        }else{
            if($id_entidad === "9415") {
                SalesData::UpdateSalesHashAces($id_venta);
            }else{
                SalesData::UpdateSalesHashUPN($id_venta);
            }
        }
        /////////////////////////////////////////////

        PrintData::deletePrint($id_user);//TODOS
        PrintData::deleteTemporal($id_user);//TODOS
        PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
        PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
        SalesData::addNotasParametersHead($id_venta,$id_user,$id_documento);
        $cont = SalesData::addNotasParametersBody($id_venta,$id_user,$id_documento);
        SalesData::addNotasParametersFoot($id_venta,$id_user,$id_documento,$cont);
        $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
        //SalesController::addPrintSales($id_user,$id_venta,$id_documento);
        //SalesController::PrintSales($id_user,$id_comprobante);
        
        $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
            
        $jResponse['success'] = true;
        $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
        $jResponse['data'] = [];

    end:
    return $jResponse; 
}

public function showMyDeposits(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto = $jResponse["id_depto"];
    if($valida=='SI'){
        $jResponse=[];            
        try{       
            $id_persona = $request->query('id_persona');
            $id_anho = $request->query('id_anho');
            $id_mesDe = $request->query('id_mes_de');
            $id_mesA = $request->query('id_mes_a');
            $data = SalesData::showMyDeposits($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
            $jResponse['success'] = true;
            if(count($data)>0){
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = $data;
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

public function addAsientosImportsExcelTranf(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[]; 
        // dd( $request);
        try{
            $datin =  \Excel::load($request->excel);
            // dd($datin);
            $data = $datin->toArray();
            $listaValid = array();
            $i = 0;
            foreach($data as $d){
                if($i>=0){
                    $obj = [
                        'id_transferencia'          =>    $request->id_transferencia,
                        'id_dinamica'               =>    '',
                        'id_cuentaaasi'             =>    $d['cuentaaasi'],
                        'id_restriccion'            =>    $d['restriccion'],
                        'id_ctacte'                 =>    $d['cta_cte'],
                        'id_fondo'                  =>    $d['fondo'],
                        'id_depto'                  =>    $d['depto'],
                        'importe'                   =>    $d['importe'],
                        'importe_me'                =>    '',
                        'descripcion'               =>    $d['glosa'],
                        'editable'                  =>    'S',
                        'dc'                        =>    $d['dc'],
                        'agrupa'                    =>    'N',
                        'modo'                      =>    '2',
                    ];
                    $listaValid[] =$obj;
                }
                $i++;
            }
            $return = SalesData::insertAsientoExcelTranf($listaValid);
            if ($return['nerror']==0) {
                $jResponse['success'] = true;
                $jResponse['message'] = $return['msgerror'];                 
                $jResponse['data'] = [];
                $code = "200";  
            } else {
              $jResponse['success'] = false;
              $jResponse['message'] = $return['msgerror'];
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

public function listTransfersEntryAs($id_transferencia){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];       
    if($valida=='SI'){
        $jResponse=[];            
        try{
            $data = SalesData::listTransfersEntryAs($id_transferencia);
            $sumaD = SalesData::sumAsientoDEntryAs($id_transferencia);
            $sumaC = SalesData::sumAsientoCEntryAs($id_transferencia);

            $jResponse['success'] = true;
            if(count($data)>0){
                $jResponse['message'] = "Succes";                    
                $jResponse['data']    = [
                    'items' => $data,
                    'sumD' => collect($sumaD)->sum('importe'),
                    'sumC' => collect($sumaC)->sum('importe'),
                    'resultDC' => collect($data)->sum('importe'),
                ];
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
public function listTransfersEntryAsVnt($id_transferencia){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[];
        try{
            $data = SalesData::saleSeats($id_transferencia);

            $jResponse['success'] = true;
            if(count($data)>0){
                $jResponse['message'] = "Succes";
                $jResponse['data']    = [
                    'items' => $data,
                    'sumD' => collect($data)->filter(function ($item) {
                        return $item->dc == 'D';
                    })->sum('importe'),
                    'sumC' => collect($data)->filter(function ($item) {
                        return $item->dc == 'C';
                    })->sum('importe'),
                    'resultDC' => collect($data)->sum('importe'),
                ];
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

public function deleteAsientoTranf($id_vasiento){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida == 'SI')
    {
        $jResponse=[];
        try
        {
            $result = SalesData::deleteAsientoTranf($id_vasiento);
            if($result)
            {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            }
            else
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "Error";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        catch(Exception $e)
        {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
    }
    return response()->json($jResponse,$code);
}

public function deleteAsientoTranfVnt($id_vasiento){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida == 'SI')
    {
        $jResponse=[];
        try
        {
            $result = SalesData::deleteAsientoTranfVnt($id_vasiento);
            if($result)
            {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            }
            else
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "Error";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        catch(Exception $e)
        {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
    }
    return response()->json($jResponse,$code);
}

public function updateAsientoTranf($id_vasiento, Request $request)
{
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida == 'SI')
    {
        $jResponse=[];
        try
        {
            $result = SalesData::updateAsientoTranf($id_vasiento, $request);
            if($result)
            {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            }
            else
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "Error";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        catch(Exception $e)
        {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
    }
    return response()->json($jResponse,$code);
}

public function salesMovOtros(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto = $jResponse["id_depto"];
    if($valida=='SI'){
        $jResponse=[];
        try{
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            $id_cliente = $request->query('id_cliente');
            // dd($id_cliente);
            $data = SalesData::salesMovOtros($id_entidad,$id_depto,$id_anho,$id_cliente);
            $jResponse['success'] = true;
            if(count($data)>0){
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $data;
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


    public function imprimeVenta(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_comprobante = $request->id_comprobante;
        $id_venta = $request->id_venta;

        if($valida=='SI'){
            $code = 200;
            try{
                $data = ProceduresDiscounts::generateTicket($id_comprobante, $id_venta, $jResponse);
                $jResponse = array_merge($jResponse, $data);
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = 500;
            }
        }
        return response()->json($jResponse,$code);
    }

    public function imprimeVentaV2(Request $request){
        $jResponse = [];
        $id_comprobante = $request->id_comprobante;
        $id_venta = $request->id_venta;
        $code = 200;

        $user_data = [
            'id_depto' => $request->id_depto,
            'id_user' => $request->id_user,
            'id_entidad' => $request->id_entidad,
        ];
        try{
            $data = ProceduresDiscounts::generateTicket($id_comprobante, $id_venta,  $user_data);
            $code = $data['code'];
            $jResponse = $data;
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = 500;
        }
        return response()->json($jResponse,$code);
    }


    public function reprintTransfer($id_transferencia){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_user = $jResponse["id_user"]; 
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];      
        if($valida=='SI'){
         
            $jResponse=[];
            $transf = SalesData::showTransfers($id_transferencia);
            $id_comprobante = '99';
            foreach ($transf as $key => $value){
                $id_dinamica = $value->id_dinamica;
            }
            $id_comprobante_afecto = null;
            // tener en cuenta
            $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
            if(count($data) === 0) {
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
                $jResponse['data'] = NULL;
                $code = "202";
                DB::rollback();
                
            }
            if(count($data) > 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
                $jResponse['data'] = NULL;
                $code = "202";
                DB::rollback();
               
            }
            foreach ($data as $item){
                $id_documento = $item->id_documento;
                $ip = $item->ip;
                $service_port = $item->puerto;
            }
            // find de tener en cuenta

            #$ip = '10.50.10.101';
            $ip = '192.168.0.104'; 
            // $ip = '172.19.0.1';
           
                try{
                    $error = 0;
                    $msgerror = "";    
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }
                      
                   
                    if($error == 0){
                       
                        PrintData::deletePrint($id_user);//TODOS
                        PrintData::deleteTemporal($id_user);//TODOS
                        PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
                        PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
                        SalesData::addTransfParametersHead($id_transferencia,$id_user,$id_documento); // new X
                        $cont = SalesData::addTransfParametersBody($id_transferencia,$id_user,$id_documento); // new X
                        SalesData::addTransfParametersFoot($id_transferencia,$id_user,$id_documento,$cont); //new
                        $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
                        $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msgerror;
                        $jResponse['data'] = [];
                        $code = "202";
                    }   
                   
                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                    DB::rollback();
                }
                
        }
     
        return response()->json($jResponse,$code);
    }



    public function getDirecionVenta() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_venta = $this->request->id_venta;

        if($valida=='SI'){
            $jResponse=[];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = SalesData::getDireccionVenta($id_venta);
            $code = "200";
        }
        return response()->json($jResponse,$code);
    }
    public function addSeatGlobalSales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $result = SalesData::addSeatGlobalSales($request);
            if ($result['success']) {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
                $code = "200";
            } else {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
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
    public function duplicarSeatGlobalSales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $result = SalesData::duplicarSeatGlobalSales($request);
            if ($result['success']) {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
                $code = "200";
            } else {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
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
    
    public function listSeatsGlobal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $data = SalesData::listSeatsGlobal($request);
            if (count($data)>0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'Respuesta exitosa';                        
                $jResponse['data'] = $data;
                $code = "201";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Sin resultados';                        
                $jResponse['data'] = [];
                $code = "202";
            }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollBack();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteSeatsGlobal($id_vasiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $result = SalesData::deleteSeatsGlobal($id_vasiento);
            if ($result['success']) {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
                $code = "200";
            } else {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
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
    public function updateSeatsGlobal($id_vasiento, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $result = SalesData::updateSeatsGlobal($id_vasiento, $request);
            if ($result['success']) {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
                $code = "200";
            } else {
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];                        
                $jResponse['data'] = $result['data'];
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
    public function statusAccouentClients(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{       
                $id_persona = $request->query('id_persona');
                $id_anho = $request->query('id_anho');
                $data = SalesData::statusAccouentClients($id_entidad,$id_depto,$id_anho, $id_persona);
                $datat = SalesData::statusAccouentClientsTotal($id_entidad,$id_depto,$id_anho, $id_persona);
                $saldoFinal=  SalesData::statusAccouentClientsSaldoFinal($id_entidad,$id_depto,$id_anho,  $id_persona);
                $jResponse['success'] = true;
                if($jResponse['success']){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => collect($data)->groupBy('tipo_venta'),
                    'total'=> collect($datat)->groupBy('tipo_venta'),
                    'saldo_final' => $saldoFinal];
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
    public function seatStatus(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $data = SalesData::seatStatus($request);
            if (count($data)>0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'Respuesta exitosa';                        
                $jResponse['data'] = $data;
                $code = "201";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Sin resultados';                        
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
    public function seatStatusTipoCero(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];  
            try{        
                $data = SalesData::seatStatusTipoCero($request);
            if (count($data)>0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'Respuesta exitosa';                        
                $jResponse['data'] = $data;
                $code = "201";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Sin resultados';                        
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
    public function estadoCuentaPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_persona = $request->id_persona;
                $id_anho = $request->id_anho;
                $id_mesDe = $request->id_mes_de;
                $id_mesA = $request->id_mes_a;
                $perfil = $request->perfil;
                $data = SalesData::listAccountStatus($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
                $saldoFinal=  SalesData::statusSaldoFinalAlumno($id_entidad,$id_depto,$id_anho, $id_mesDe, $id_mesA, $id_persona);
                $saldoFiliales = StudentData::saldoSedes($request, $id_entidad);
                $values = collect($data)->groupBy('tipo_venta');
                $datar = array();
                foreach ($values as $key => $value) {
                    array_push($datar, ['tipo' => $key, 'data' => $value]);
                }
     
                // dd($total_academic);
                // dd($mov_academic, ' aa ', $mov_ingles, ' cc ', $mov_musica, ' bb ', $mov_cepre);
                if (count($data)>0) {
                $pdf = DOMPDF::loadView('pdf.finances-student.estadoCuenta',[
                    'data' => $datar,
                    'saldo_final'=>$saldoFinal,
                    'perfil' => $perfil,
                    'saldo_filiales' => $saldoFiliales,
                    ])->setPaper('a3', 'portrait'); 

                    $doc =  base64_encode($pdf->stream('print.pdf'));


                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                } else {
                    $pdf = DOMPDF::loadView('pdf.finances-student.estadoCuenta',[
                        'items'=>[],
                        'total'=>[],
                        'saldo_final'=>[],
                        'saldo_filiales'=>[],
                        ])->setPaper('a4', 'portrait'); 
    
                        $doc =  base64_encode($pdf->stream('print.pdf'));
                        $jResponse = [
                            'success' => true,
                            'message' => "OK",
                            'data' => ['items'=>$doc],
                            'code' => "201",
                        ];
                }
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
                $pdf = DOMPDF::loadView('pdf.error',[
                    'mensaje'=>$mensaje
                    ])->setPaper('a4', 'portrait');
                // $pdf->save($ruta);
                                
                $doc = base64_encode($pdf->stream('print.pdf'));
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc],
                    'code' => "202",
                ];
            }
        }
        return response()->json($jResponse);
    }
    public function showMySalesArrangements($id_venta,$id_tipoorigen){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $sales = SalesData::showMySalesArrangements($id_venta,$id_tipoorigen);
                $seats = SalesData::showMySeatSalesArrangements($id_venta,$id_tipoorigen);
                $jResponse['success'] = true;
                if(count($sales)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['sales' => $sales[0], 'seats' => $seats];
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

    public function expCodBank(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_sede = '';
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_banco           = $request->query('id_banco'); // 02:BCP, 09:SCOTIABANK, 11:BBVA
                $cuenta_corriente   = $request->query('cuenta_corriente');
                $inicio             = $request->query('anio_inicio');
                $fin                = $request->query('anio_fin');
                $sede = SalesData::getSedeByDepto($id_depto);
                if(!empty($sede)){
                    $id_sede = $sede[0]->id_sede;
                }
                if($id_banco=='02'){
                    $data = SalesData::expCodBankBCP($inicio, $fin, $id_sede, $cuenta_corriente);
                } else if($id_banco=='09') {
                    $data = SalesData::expCodBankScotiabank($inicio, $fin, $id_sede, $cuenta_corriente);
                } else if($id_banco=='11'){
                    $data = SalesData::expCodBankBBVA($inicio, $fin, $id_sede);
                }
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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
    public function lisAccountsReceivable(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            $parent = [];
            try{
                
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $tipo = $request->query('tipo');
                
                $cab = SalesData::AccountsReceivable($id_entidad,$id_depto,$id_anho,$id_mes,$tipo);
//                foreach ($cab as $key => $value){
//                    $row = SalesData::lisAccountsReceivable($id_entidad,$id_depto,$id_anho,$id_mes,$value->id_cliente,$tipo);
//                    $parent[] = [
//                                    'ruc' => $value->documento,
//                                    'ruc' => $value->codigo,
//                                    'cliente' => $value->cliente,
//                                    'children' => $row
//                                ];
//                }
                if ($cab){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $cab ;
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
    public function lisAccountsReceivableChildren(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            $parent = [];
            try{

                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $tipo = $request->query('tipo');
                $id_cliente = $request->query('id_cliente');
                $row = SalesData::lisAccountsReceivable($id_entidad,$id_depto,$id_anho,$id_mes,$id_cliente,$tipo);
                if ($row){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $row ;
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
    public function anticipos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            $parent = [];
            try{

                $id_anho = $request->query('id_anho');
                $tipo = $request->query('tipo');
                $row = SalesData::getAnticipos($id_entidad,$id_depto,$id_anho,$tipo);
                if ($row){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $row ;
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
}

