<?php
namespace App\Http\Controllers\Inventories;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Inventories\PedidoRegistroData;
use App\Http\Data\Inventories\InventoriesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Inventories\WarehousesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
// use App\Models\PedidoRegistro as PedidoRegistro;
use App\Models\PedidoRegistro;
use App\Models\PedidoDetalle;
use App\Models\InventarioArticulo;

class PedidoRegistroController extends Controller{
   
    public function listTipoPedidos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                $items = PedidoRegistroData::listTipoPedidos();
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $items;
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

    public function listPedidoRegistroDetalle(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                // $pedidoDetalles = PedidoDetalle::where('id_pedido',$id_pedido)->get();
                $pedidoDetalles = PedidoRegistroData::pedidoDetallesByIdPedido($id_pedido);
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoDetalles;
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

    public function savePedidoRegistroDetalle(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_articulo = Input::get('id_articulo');

                $inventarioArticulo = InventarioArticulo::findOrFail($id_articulo);

                $existArticulo = PedidoDetalle::where('id_articulo', $id_articulo)
                                                ->where('id_pedido', $id_pedido)->first();
                if($existArticulo) {
                    $newCantidad = Input::get('cantidad')+$existArticulo->cantidad;
                    $newImporte = ($newCantidad * Input::get('precio'));
                    $pedidoDetalleData = [
                        'cantidad' => $newCantidad,
                        'importe' => $newImporte
                    ];
                    PedidoDetalle::where('id_articulo',$id_articulo)
                            ->where('id_pedido', $id_pedido)
                            ->update($pedidoDetalleData);
                    $pedidoDetalle = PedidoDetalle::where('id_articulo', $id_articulo)
                    ->where('id_pedido', $id_pedido)->first();

                } else {
                    $pedidoDetalleData = [
                        'id_pedido' => $id_pedido,
                        'id_almacen' => Input::get('id_almacen'),
                        'id_articulo' => $id_articulo,
                        'detalle' => $inventarioArticulo->nombre,
                        'cantidad' => Input::get('cantidad'),
                        'precio' => Input::get('precio'),
                        'importe' => Input::get('importe'),
                    ];
                    $pedidoDetalle = PedidoDetalle::create($pedidoDetalleData);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoDetalle;
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

    public function finalizarPedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{


                $pedidoDetalles = PedidoDetalle::where('id_pedido', $id_pedido)->get();
                if(count($pedidoDetalles) === 0) {
                    throw new Exception("Alto, El pedido no tiene detalles.", 1);
                }

                $pedidoReg = PedidoRegistro::findOrFail($id_pedido);
                if($pedidoReg->estado === '1') {
                    throw new Exception("Alto, El pedido ya fue enviado.", 1);
                }

                $pedidoRegistroData = [
                    // 'fecha_pedido' => date('Y-m-d h:i:sa'),
                    'fecha_pedido' => date('Y-m-d'),
                    'estado' => '1',
                ];
                PedidoRegistro::where('id_pedido', $id_pedido)
                    ->update($pedidoRegistroData);
                
                    
                $pedidoRegistros = PedidoRegistroData::showPedidoRegistro($id_pedido);
                $pedidoRegistro = null;
                foreach ($pedidoRegistros as $pr) {
                    $pedidoRegistro = $pr;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    public function changeStatePedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{

                $pedidoRegistroData = [
                    'estado' =>Input::get('estado'),
                    'comentario' => Input::get('comentario'),
                ];
                $pedidoRegistro = PedidoRegistro::where('id_pedido', $id_pedido)
                    ->update($pedidoRegistroData);

                $pedidoRegistros = PedidoRegistroData::showPedidoRegistro($id_pedido);
                $pedidoRegistro = null;
                foreach ($pedidoRegistros as $pr) {
                    $pedidoRegistro = $pr;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    public function salidaMercaderiaPedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();
            try{

                $id_anho = 0;
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($id_anho !== $id_anho_actual){
                    throw new Exception("Alto, No existe un año activo.", 1);
                }
                $id_mes = 0;
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item){
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;                
                }

                if($id_mes !== $id_mes_actual){
                    throw new Exception("Alto, No existe un mes activo.", 1);
                }

                $pedidoRegistros = PedidoRegistroData::showPedidoRegistro($id_pedido);
                $pedidoRegistro = null;
                foreach ($pedidoRegistros as $pr) {
                    $pedidoRegistro = $pr;
                }

                $id_tipooperacion = "11"; // SALIDA POR TRANSFERENCIA ENTRE ALMACENES 
                $data_s = InventoriesData::listDocuments($pedidoRegistro->id_almacen_origen,"11");//Salida por Transferencia de Almacenes
                if(count($data_s)==0){
                    throw new Exception("Alto, Almacen Origen No tiene configurado Documento: SALIDA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                }
                foreach ($data_s as $item){
                    $id_documento = $item->id_documento;               
                }

                $tipo = "S"; // SALIDA DE ALMACENES
                $ip = GlobalMethods::ipClient($request);
                $id_movimiento = 0;
                $id_receta = "";
                $cantidad = "";
                $num_movimiento = "";

                $error = 0;
                $msgerror = "";
                for($x=1;$x<=300;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO(:P_ID_ALMACEN, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, 
                :P_ID_RECETA, :P_ID_PERSONA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_IP, :P_CANTIDAD, :P_GUIA, :P_ID_MOVIMIENTO,
                :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $pedidoRegistro->id_almacen_destino, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_RECETA', $id_receta, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
                $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_GUIA', $num_movimiento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }

                $detalle = Input::get('detalle');
                $id_dinamica = Input::get('id_dinamica');

                foreach ($detalle as $value) {
                    $error = 0;
                    $msgerror = "";
                    for($x=1;$x<=300;$x++){
                        $msgerror .= "0";
                    }
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_INVENTARIO_DETALLE(:P_ID_MOVIMIENTO, :P_ID_ARTICULO, :P_ID_DINAMICA, :P_CANTIDAD, 
                            :P_COSTO, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ARTICULO', $value['id_articulo'], PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_CANTIDAD', $value['cantidad_despacho'], PDO::PARAM_STR);
                    $stmt->bindParam(':P_COSTO', $value['precio'], PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    }
                }
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=300;$x++){
                    $msgerror .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_FINISH_MOVIMIENTO(:P_ID_MOVIMIENTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }

                $pedidoRegistroData = [
                    'estado' =>Input::get('estado'),
                    // 'comentario' => Input::get('comentario'),
                ];
                PedidoRegistro::where('id_pedido', $id_pedido)
                    ->update($pedidoRegistroData);
                
                DB::table('ELISEO.INVENTARIO_MOVIMIENTO')
                    ->where('ID_MOVIMIENTO', $id_movimiento)
                    ->update(['ID_PEDIDO' => $id_pedido]);

                DB::commit();
                // DB::rollback();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
                $code = "200";
            }catch(Exception $e){ 
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }   

    public function ingresoMercaderiaPedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();
            try{

                $id_anho = 0;
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($id_anho !== $id_anho_actual){
                    throw new Exception("Alto, No existe un año activo.", 1);
                }
                $id_mes = 0;
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item){
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;                
                }

                if($id_mes !== $id_mes_actual){
                    throw new Exception("Alto, No existe un mes activo.", 1);
                }

                $pedidoRegistros = PedidoRegistroData::showPedidoRegistro($id_pedido);
                $pedidoRegistro = null;
                foreach ($pedidoRegistros as $pr) {
                    $pedidoRegistro = $pr;
                }

                $id_tipooperacion = "21"; // Entrada por Transferencia de Almacenes
                $data_s = InventoriesData::listDocuments($pedidoRegistro->id_almacen_origen,"21"); // Entrada por Transferencia de Almacenes
                if(count($data_s)==0){
                    throw new Exception("Alto, Almacen Destino No tiene configurado Documento: ENTRADA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                }
                foreach ($data_s as $item){
                    $id_documento = $item->id_documento;               
                }

                $tipo = "I"; // SALIDA DE ALMACENES
                $ip = GlobalMethods::ipClient($request);
                $id_movimiento = 0;
                $id_receta = "";
                $cantidad = "";
                $num_movimiento = "";

                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO(:P_ID_ALMACEN, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, 
                :P_ID_RECETA, :P_ID_PERSONA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_IP, :P_CANTIDAD, :P_GUIA, :P_ID_MOVIMIENTO,
                :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $pedidoRegistro->id_almacen_origen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_RECETA', $id_receta, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
                $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_GUIA', $num_movimiento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }

                // $detalle = Input::get('detalle');
                $id_dinamica = Input::get('id_dinamica');
                $id_movimiento_salida = Input::get('id_movimiento_salida');

                $movimiento_detalle = DB::table('ELISEO.INVENTARIO_DETALLE')
                                    ->where('ID_MOVIMIENTO', $id_movimiento_salida)->get();

                foreach ($movimiento_detalle as $value) {
                    $error = 0;
                    $msgerror = str_repeat("0", 300);
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_INVENTARIO_DETALLE(:P_ID_MOVIMIENTO, :P_ID_ARTICULO, :P_ID_DINAMICA, :P_CANTIDAD, 
                            :P_COSTO, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ARTICULO', $value->id_articulo, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_CANTIDAD', $value->cantidad, PDO::PARAM_STR);
                    $stmt->bindParam(':P_COSTO', $value->costo, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    }
                }
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_FINISH_MOVIMIENTO(:P_ID_MOVIMIENTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }

                $pedidoRegistroData = [
                    'estado' =>Input::get('estado'),
                    // 'comentario' => Input::get('comentario'),
                ];
                PedidoRegistro::where('id_pedido', $id_pedido)
                    ->update($pedidoRegistroData);
                
                DB::table('ELISEO.INVENTARIO_MOVIMIENTO')
                    ->where('ID_MOVIMIENTO', $id_movimiento)
                    ->update(['ID_PEDIDO' => $id_pedido]);

                DB::commit();
                // DB::rollback();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
                $code = "200";
            }catch(Exception $e){ 
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }   

    public function listMovimientosByIdPedido(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                // $movimientos = DB::table('ELISEO.INVENTARIO_MOVIMIENTO')
                //                     ->where('ID_PEDIDO', $id_pedido)->get();

                $movimientos = PedidoRegistroData::listMovimientosByIdPedido($id_pedido);


                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $movimientos;
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

    public function deletePedidoRegistroDetalle(Request $request, $id_detalle){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{

                $pedidoDetalle = PedidoDetalle::findOrFail($id_detalle);
                $pedidoDetalle->delete();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
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

    public function deletePedidoRegistroDetalleAll(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{

                PedidoDetalle::where('id_pedido', $id_pedido)->delete();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
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

    public function deletePedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                PedidoDetalle::where('id_pedido', $id_pedido)->delete();
                PedidoRegistro::where('id_pedido', $id_pedido)->delete();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
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

    public function showPedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        if($valida=='SI'){
            $jResponse=[];
            try{
                // $pedidoRegistro = PedidoRegistro::findOrFail($id_pedido);
                $pedidoRegistros = PedidoRegistroData::showPedidoRegistro($id_pedido);
                $pedidoRegistro = null;
                foreach ($pedidoRegistros as $pr) {
                    $pedidoRegistro = $pr;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
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

    public function savePedidoRegistro(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho = 0;
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;                
                }
                if($id_anho !== $id_anho_actual){
                    throw new Exception("Alto, No existe un año activo.", 1);
                }
                $id_mes = 0;
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item){
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;                
                }

                if($id_mes !== $id_mes_actual){
                    throw new Exception("Alto, No existe un mes activo.", 1);
                }

                $data = Input::all();
                $validador = Validator::make($data, ['id_almacen_origen' => 'required',
                                        'id_almacen_destino' => 'required','id_tipopedido' => 'required',
                                        'fecha' => 'required','motivo' => '']);
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }

                $latestRegister = PedidoRegistro::where('id_entidad',$id_entidad)
                                    ->whereNotNull('numero')->orderBy('numero','desc')->first();
                $newNumero = str_pad($latestRegister->numero+1,8,'0',STR_PAD_LEFT);

                $pedidoRegistroData = [
                    'id_entidad' => $id_entidad,
                    'id_depto' => $id_depto,
                    'id_anho' => $id_anho,
                    'id_mes' => $id_mes,
                    'id_persona' => $id_user,
                    'id_tipopedido' => Input::get('id_tipopedido'),
                    'fecha' => Input::get('fecha'),
                    'motivo' => Input::get('motivo'),
                    'estado' => '0',
                    'numero' => $newNumero,
                    'id_almacen_origen' => Input::get('id_almacen_origen'),
                    'id_almacen_destino' => Input::get('id_almacen_destino'),
                ];
                $pedidoRegistro = [];
                $pedidoRegistro = PedidoRegistro::create($pedidoRegistroData);
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
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

    public function updatePedidoRegistro(Request $request, $id_pedido){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = Input::all();
                $validador = Validator::make($data, ['id_almacen_origen' => 'required',
                                        'id_almacen_destino' => 'required','id_tipopedido' => 'required',
                                        'fecha' => 'required','motivo' => '']);
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }
                $pedidoRegistroData = [
                    // 'id_entidad' => $id_entidad,
                    // 'id_depto' => $id_depto,
                    // 'id_anho' => $id_anho,
                    // 'id_mes' => $id_mes,
                    // 'id_persona' => $id_user,
                    'id_tipopedido' => Input::get('id_tipopedido'),
                    'fecha' => Input::get('fecha'),
                    'motivo' => Input::get('motivo'),
                    'estado' => '0',
                    // 'id_almacen_origen' => Input::get('id_almacen_origen'),
                    'id_almacen_destino' => Input::get('id_almacen_destino'),
                ];
                $pedidoRegistro = PedidoRegistro::where('id_pedido', $id_pedido)
                                        ->update($pedidoRegistroData);

                $pedidoRegistro = PedidoRegistro::findOrFail($id_pedido);
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistro;
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

    public function listPedidoRegistro(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $id_almacen = 0;
            try{
                                
                $id_mes = $request->query('id_mes') ? $request->query('id_mes'): date('m');
                $tipo_operacion = $request->query('tipo_operacion') ? $request->query('tipo_operacion'): '1';
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $id_almacen = $item->id_almacen;
                }
                if($tipo_operacion ==='1') { // Enviados
                    $pedidoRegistros = PedidoRegistroData::pedidoRegistroByAlmacenOrigen($id_almacen, $id_mes);
                } else { // 2 = Destino
                    $pedidoRegistros = PedidoRegistroData::pedidoRegistroByAlmacenDestino($id_almacen, $id_mes);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = $pedidoRegistros;
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

}

