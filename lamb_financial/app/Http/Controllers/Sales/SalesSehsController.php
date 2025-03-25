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
use App\Http\Data\Sales\SalesSehsData;
use Carbon\Carbon;

class SalesSehsController extends Controller{

    // addAndUpdateSales();
    // listSales();
    // finalizarSales();
    // addSalesDetails();
    // updateSalesDetails();
    
    // addAndUpdateSalesWithoutStock();
    // // listSalesWithoutStock();
    // finalizarSalesWithoutStock();
    // addSalesWithoutStockDetails();
    // updateSalesWithoutStockDetails();

    // listSalesDetailsDeVentaFinalizada();
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    // success
    public function searchArticulosWithoutStock(Request $request){
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
                if($id_venta === null || $producto === null){
                    throw new Exception('Alto! Faltan parámetros de ingreso.', 1);
                }
                $id_cliente = 0;
                $venta = SalesData::showSales($id_venta);
                foreach ($venta as $key => $item){
                    $id_cliente = $item->id_cliente;
                    $id_anho = $item->id_anho;
                }
                if($id_cliente === 0 || $id_cliente === null){
                    throw new Exception('Alto! La venta no tiene un cliente.', 1);
                }
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                if($id_almacen === 0){
                    throw new Exception('Alto! El usuario no tiene asignado un almacén.', 1);
                }

                $id_politica = 0;
                $politica = PoliticsData::showPoliticsPersons($id_cliente);
                foreach ($politica as $key => $item){
                    $id_politica = $item->id_politica;
                }

                $data=[];
                // Por ahora no trabajaremos con políticas
                // if($id_politica !== 0 || $id_politica !== null) {
                //     $data = SalesData::listSalesProductsPolitics($id_politica,$id_anho,$producto);
                // }
                
                if(count($data) === 0) { 
                    $data = SalesData::listSalesProductsWithoutStock($id_almacen,$id_anho,$producto);
                }

                if($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    throw new Exception("Alto! Los artículos buscados si tienen stock, o no existen en el almacén.", 1);
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    // success
    public function searchArticulos(Request $request){
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
                if($id_venta === null || $producto === null){
                    throw new Exception('Alto! Faltan parámetros de ingreso.', 1);
                }
                $id_cliente = 0;
                $venta = SalesData::showSales($id_venta);
                foreach ($venta as $key => $item){
                    $id_cliente = $item->id_cliente;
                    $id_anho = $item->id_anho;
                }
                // if($id_cliente === 0 || $id_cliente === null){
                //     throw new Exception('Alto! La venta no tiene un cliente.', 1);
                // }
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                if($id_almacen === 0){
                    throw new Exception('Alto! El usuario no tiene asignado un almacén.', 1);
                }

                $id_politica = 0;
                $politica = PoliticsData::showPoliticsPersons($id_cliente);
                foreach ($politica as $key => $item){
                    $id_politica = $item->id_politica;
                }

                $data=[];
                // Por ahora no trabajaremos con políticas
                // if($id_politica !== 0 || $id_politica !== null) {
                //     $data = SalesData::listSalesProductsPolitics($id_politica,$id_anho,$producto);
                // }
                
                if(count($data) === 0) { 
                    $data = SalesData::listSalesProducts($id_almacen,$id_anho,$producto);
                }

                if($data){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    throw new Exception("Alto! Los artículos buscados no tienen stock, o no existen en el almacén.", 1);
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    // success
    public function addAndUpdateSales($id_venta = 0){
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
            $id_cliente = Input::get('id_cliente');
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

            $msgerror = "";   
            DB::beginTransaction();
            try{
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_VENTA(:P_ID_PERSONA, :P_ID_CLIENTE,
                :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES,:P_ID_COMPROBANTE, :P_ID_MONEDA, :P_ES_AUTOENTREGA,
                :P_ID_VENTA,:P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
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
    // success
    public function addSalesDetails($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];   
            $msn_error = "";
            $id_vdetalle = 0;      
            try{
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                $id_articulo = Input::get('id_articulo');
                $cantidad = Input::get('cantidad');

                $error = 0;
                for($x=1;$x<=200;$x++){
                    $msn_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_VENTA_DETALLE(
                    :P_ID_VENTA, :P_ID_ARTICULO, :P_ID_ALMACEN, :P_CANTIDAD, :P_ID_VDETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();  
                if($error === 0){
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
    // success
    public function updateSalesDetails($id_venta,$id_vdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];      
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_articulo = 0; // En los procedimientos se actualizarán
                $id_almacen = 0; // En los procedimientos se actualizarán
                $params = json_decode(file_get_contents("php://input"));
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                // $stmt = $pdo->prepare("begin PKG_SALES.SP_UPDATE_VENTA_DET_OUTSTOCK(:P_ID_VENTA, :P_ID_VDETALLE, :P_CANTIDAD, :P_ERROR, :P_MSN_ERROR); end;");
                // $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                // $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                // $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                // $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                // $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);  
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_VENTA_DETALLE(
                    :P_ID_VENTA, :P_ID_ARTICULO, :P_ID_ALMACEN, :P_CANTIDAD, :P_ID_VDETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $params->cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_INT);
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
    // success
    // public function deleteSalesDetails($id_venta,$id_vdetalle){
    //     $jResponse = GlobalMethods::authorizationLamb($this->request);
    //     $code   = $jResponse["code"];
    //     $valida = $jResponse["valida"];
    //     $id_user = $jResponse["id_user"];        
    //     if($valida=='SI'){
    //         $jResponse=[];            
    //         try{
    //             $count = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->count();
    //             if ($count>0) {
    //                 $delete = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->delete();
    //             } 
    //             $tipo = 0;
    //             $pdo = DB::getPdo();
    //             $stmt = $pdo->prepare("begin PKG_SALES.SP_ELIMINAR_DETALLE(:P_ID_VENTA, :P_ID_VDETALLE, :P_TIPO); end;");
    //             $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
    //             $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
    //             $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
    //             $stmt->execute();
    //             $jResponse['success'] = true;
    //             $jResponse['message'] = "Success";                    
    //             $jResponse['data'] = [];
    //             $code = "200";                
    //         }catch(Exception $e){
    //             $jResponse['success'] = true;
    //             $jResponse['message'] = $e->getMessage();
    //             $jResponse['data'] = [];
    //             $code = "202"; 
    //         } 
    //     }        
    //     return response()->json($jResponse,$code);
    // }
    // success
    public function finalizarSales($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"]; 
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            // $opc = $params->opc;
            DB::beginTransaction();
            try{   
                $pdo = DB::getPdo();
                
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
                    $error = 0;
                    $msgerror = "";   
                    for($x=1;$x<=200;$x++){
                        $msgerror .= "0";
                    }

                    $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_CREDITO, :P_EFECTIVO, :P_CREDITO, :P_TARJETA, :P_ID_TIPOTARJETA, :P_OPERACION, :P_ERROR, :P_MSGERROR); end;");
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
                        DB::commit();
                        $msn = "";
                        try{
                            usleep(2500000); //Dormir 2 segundos

                            if($id_entidad === "7124") {
                                SalesData::UpdateSalesHash($id_venta);
                            } else {
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
                                // $msn = SalesController::print($id_user,$ip,$service_port); // comentado por vitmar
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
    // success
    public function listSalesDetailsDeVentaFinalizada($id_venta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];        
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{          
                // DDDDDDDDDDDDDDD   
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                if(count($warehouse) === 0){
                    throw new Exception("Alto, El usuario no tiene asignado un almacén.", 1);
                }
                if(count($warehouse) > 1){
                    throw new Exception("Alto, Hay mas de un almacén asignado al usuario.", 1);
                }
                $id_anho = 0;
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($id_anho !== $id_anho_actual){
                    throw new Exception("Alto, No existe un año activo.", 1);
                }

                $data = SalesSehsData::listSalesDetailsVentaFinalizada($id_almacen, $id_anho, $id_venta);
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
                $jResponse['data'] = NULL;
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }  
    /// ======================


    
    public static function rulesSalesDispatch(){

        return [
            'id_venta' => 'required',
            'fecha_emision' => 'required',
            'fecha_inicio_traslado' => 'required',
            'direccion_partida' => '',
            'direccion_llegada' => '',
            'id_motivotraslado' => 'required',
            'id_transportista' => '',
            'vehiculo_marca' => '',
            'vehiculo_placa' => '',
            'licencia_transportista' => '',
            'despacho_detalles' => 'required|array|min:1',
            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',

            // 'licencia_transportistass' => 'required',
        ];
    }

    public function addSalesDispatchs(){
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


            $data = Input::all();
            $validador = Validator::make($data,  $this->rulesSalesDispatch());
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            // $id_comprobante = Input::get('id_comprobante');
            // $id_moneda = Input::get('id_moneda');
            // $es_autoentrega = Input::get('es_autoentrega');
            $id_venta = Input::get('id_venta');
            $fecha_emision = Input::get('fecha_emision');
            $fecha_inicio_traslado = Input::get('fecha_inicio_traslado');
            $direccion_partida = Input::get('direccion_partida');
            $direccion_llegada = Input::get('direccion_llegada');
            $id_motivotraslado = Input::get('id_motivotraslado');
            $id_transportista = Input::get('id_transportista');
            $vehiculo_marca = Input::get('vehiculo_marca');
            $vehiculo_placa = Input::get('vehiculo_placa');
            $licencia_transportista = Input::get('licencia_transportista');

            $id_dinamica = Input::get('id_dinamica');
            $id_tipotransaccion = Input::get('id_tipotransaccion');

            $despacho_detalles = Input::get('despacho_detalles');
            // $id_venta = Input::get('id_venta');
            
            $id_vdespacho = 0;
            $msgerror = "";   
            DB::beginTransaction();
            try{
                for($x=1;$x<=300;$x++){
                    $msgerror .= "0";
                }
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_VENTA_DESPACHO(
                    :P_ID_ENTIDAD,:P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_ALMACEN, :P_ID_VENTA, :P_ID_TIPOTRANSACCION,:P_ID_PERSONA,
                    :P_FECHA_EMISION,:P_FECHA_INICIO_TRASLADO,:P_DIRECCION_PARTIDA,:P_DIRECCION_LLEGADA,
                    :P_ID_MOTIVOTRASLADO,:P_ID_TRANSPORTISTA,:P_VEHICULO_MARCA,:P_VEHICULO_PLACA,:P_LICENCIA_TRANSPORTISTA,
                    :P_ID_VDESPACHO,:P_ERROR, :P_MSGERROR
                ); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);

                $stmt->bindParam(':P_FECHA_EMISION', $fecha_emision, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_INICIO_TRASLADO', $fecha_inicio_traslado, PDO::PARAM_STR);
                $stmt->bindParam(':P_DIRECCION_PARTIDA', $direccion_partida, PDO::PARAM_STR);
                $stmt->bindParam(':P_DIRECCION_LLEGADA', $direccion_llegada, PDO::PARAM_STR);

                $stmt->bindParam(':P_ID_MOTIVOTRASLADO', $id_motivotraslado, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TRANSPORTISTA', $id_transportista, PDO::PARAM_INT);
                $stmt->bindParam(':P_VEHICULO_MARCA', $vehiculo_marca, PDO::PARAM_STR);
                $stmt->bindParam(':P_VEHICULO_PLACA', $vehiculo_placa, PDO::PARAM_STR);
                $stmt->bindParam(':P_LICENCIA_TRANSPORTISTA', $licencia_transportista, PDO::PARAM_STR);

                $stmt->bindParam(':P_ID_VDESPACHO', $id_vdespacho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();                            
                if($error === 1){ 
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;                    
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                foreach ($despacho_detalles as $item){
                    $id_ddetalle = 0;
                    for($x=1;$x<=300;$x++){
                        $msgerror .= "0";
                    }
                    $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_DESPACHO_DETALLE(
                        :P_ID_VDESPACHO, :P_ID_VDETALLE, :P_ID_ARTICULO, :P_ID_DINAMICA,
                        :P_CANTIDAD, :P_DETALLE, :P_ID_DDETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_VDESPACHO', $id_vdespacho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VDETALLE', $item['id_vdetalle'], PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ARTICULO', $item['id_articulo'], PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_CANTIDAD', $item['faltante'], PDO::PARAM_STR);
                    $stmt->bindParam(':P_DETALLE', $item['detalle'], PDO::PARAM_STR);

                    $stmt->bindParam(':P_ID_DDETALLE', $id_ddetalle, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if($error === 1){
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msgerror;                    
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                }
                for($x=1;$x<=300;$x++){
                    $msgerror .= "0";
                }
                // finalizar el despacho
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_FINALIZAR_VENTA_DESPACHO(
                    :P_ID_VDESPACHO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_VDESPACHO', $id_vdespacho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1){
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;                    
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";                    
                $jResponse['data'] = $id_vdespacho;
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

    public function listMySalesSehs(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $text_search = $request->query('text_search');
                $id_voucher = $request->query('id_voucher');
                $data = SalesSehsData::listMySalesSehs($id_voucher, $text_search);
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

    public function listMySalesAnticipadas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $text_search = $request->query('text_search');
                $id_voucher = $request->query('id_voucher');
                $data = SalesSehsData::listMySalesAnticipadas($id_voucher, $text_search);
                // $data = SalesData::listMySalesAnticipadasToSearch($id_entidad,$id_depto,$id_user, $text_search);
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


    public function listMySalesAnticipadasToSearch(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];  
            $id_almacen=0;          
            try{
                $text_search = $request->query('text_search');
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
                $data = SalesSehsData::listMySalesAnticipadasToSearch($id_almacen, $text_search);
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
        end:    
        return response()->json($jResponse,$code);
    }

    public function listSaldoVentasAnticipadas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];    
            $id_almacen = 0;        
            try{

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

                $data = SalesSehsData::listSaldoVentasAnticipadas($id_almacen);
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
        end:   
        return response()->json($jResponse,$code);
    }

    public function listSalesDispatchs(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_voucher = $request->query('id_voucher');
                $data = SalesSehsData::listSalesDispatchs($id_voucher);
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
}

