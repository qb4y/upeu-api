<?php

namespace App\Http\Controllers\Orders;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Orders\OrdersData;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Excel;
use DOMPDF;

class OrdersController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listWarehousesArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_almacen = '';
            $id_sedearea = $request->query('id_sedearea');
            // $search = $request-> query('search'); // buscador
            $dato = $request->query('dato');
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $cont = 0;
                $warehouse = WarehousesData::showWarehousesByAreas($id_sedearea);
                foreach ($warehouse as $key => $item) {
                    if($cont ==0){// solo 1 registro
                        $id_almacen = $item->id_almacen;
                    }else{ //+ de 1 registro
                        $id_almacen = $id_almacen.",".$item->id_almacen;
                    }
                    $cont++;
                }
                if (count($warehouse) > 0) {
                    //if (count($warehouse) == 1) {
                        $url = secure_url('');//Prod
                        //$url = url('');//Dev
                        $data = WarehousesData::listWarehousesArticlesFind($id_almacen, $id_anho, $dato,$url);
                        if ($data) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'OK';
                            $jResponse['data'] = $data;
                            $code = "200";
                        } else {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'The item does not exist';
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    /*} else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
                        $jResponse['data'] = [];
                        $code = "202";
                    }*/
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The user does not have an assigned Warehouse: ' . $id_user;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listWarehousesArticlesAll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_almacen = '';
            $id_sedearea = $request->query('id_sedearea');
            $dato = $request->query('dato');
            $per_page = $request->query('per_page');
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $cont = 0;
                $warehouse = WarehousesData::showWarehousesByAreas($id_sedearea);
                foreach ($warehouse as $key => $item) {
                    if($cont ==0){// solo 1 registro
                        $id_almacen = $item->id_almacen;
                    }else{ //+ de 1 registro
                        $id_almacen = $id_almacen.",".$item->id_almacen;
                    }
                    $cont++;
                }
                if (count($warehouse) > 0) {
                    $url = secure_url('');//Prod
                    //$url = url('');//Dev
                    // dd($id_almacen);
                    $data = WarehousesData::listWarehousesArticlesFindPaginated($id_almacen, $id_anho, $dato,$url,$per_page);
                    if ($data) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The user does not have an assigned Warehouse: ' . $id_user;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addOrdersSeatsGenerate()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $id_pedido = Input::get("id_pedido");
                $id_dinamica = Input::get("id_dinamica");
                // dd( $id_pedido,  $id_dinamica);
                if (!empty($id_pedido) and !empty($id_dinamica)) {
                    $rpta = OrdersData::addOrdersSeatsGenerate($id_pedido, $id_dinamica);
                } else {
                    $rpta = null;
                }
                if ($rpta['error'] == 0) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showOrdersSeats($id_pasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_pedido = Input::get("id_pedido");
                $data = OrdersData::showOrdersSeats($id_pasiento);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data;
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

    public function listOrdersSeats()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $pedido = OrdersData::ShowPedidoEmpresa($id_pedido);
                foreach ($pedido as $item){
                    $id_anho = $item->id_anho;
                    $id_empresa = $item->id_empresa;
                }
                $data = OrdersData::listOrdersSeats($id_pedido,$id_anho,$id_empresa);
                $total = OrdersData::listOrdersSeatsSum($id_pedido);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data;
                    $jResponse['total'] = $total;
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

    public function addOrdersSeats()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $id_tipoplan = Input::get("id_tipoplan");
                $id_cuentaaasi = Input::get("id_cuentaaasi");
                $id_restriccion = Input::get("id_restriccion");
                $id_fondo = Input::get("id_fondo");
                $id_ctacte = Input::get("id_ctacte");
                $id_depto = Input::get("id_depto");
                $dc = Input::get("dc");
                $porcentaje = Input::get("porcentaje");
                // $importe = Input::get("importe");
                $glosa = Input::get("glosa");
                $indicador = Input::get("indicador");
                if ($indicador == null || $indicador == "") {
                    $indicador = "IMPORTE";
                }
                $data = [
                    "id_pedido" => $id_pedido,
                    "id_tipoplan" => $id_tipoplan,
                    "id_cuentaaasi" => $id_cuentaaasi,
                    "id_restriccion" => $id_restriccion,
                    "id_fondo" => $id_fondo,
                    "id_ctacte" => $id_ctacte,
                    "id_depto" => $id_depto,
                    "dc" => $dc,
                    "porcentaje" => $porcentaje,
                    "glosa" => $glosa,
                    "indicador" => $indicador,
                    "nro_asiento" => 1
                ];
                $dataResult = OrdersData::addOrdersSeats($data);
                if ($dataResult) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $dataResult;
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

    public function updateOrdersSeats($id_pasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $id_tipoplan = Input::get("id_tipoplan");
                $id_cuentaaasi = Input::get("id_cuentaaasi");
                $id_restriccion = Input::get("id_restriccion");
                $id_fondo = Input::get("id_fondo");
                $id_ctacte = Input::get("id_ctacte");
                $id_depto = Input::get("id_depto");
                $dc = Input::get("dc");
                $porcentaje = Input::get("porcentaje");
                // $importe = Input::get("importe");
                $glosa = Input::get("glosa");
                $data = [
                    "id_pedido" => $id_pedido,
                    "id_tipoplan" => $id_tipoplan,
                    "id_cuentaaasi" => $id_cuentaaasi,
                    "id_restriccion" => $id_restriccion,
                    "id_fondo" => $id_fondo,
                    "id_ctacte" => $id_ctacte,
                    "id_depto" => $id_depto,
                    "dc" => $dc,
                    "porcentaje" => $porcentaje,
                    "glosa" => $glosa
                ];
                $result = OrdersData::updateOrdersSeats($data, $id_pasiento);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
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

    public function deleteOrdersSeats($id_pasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::deleteOrdersSeats($id_pasiento);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
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

    public function executeOrdersSeatsMatch($id_pedido)// deleteOrdersSeats
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::executeOrdersSeatsMatch($id_pedido);
                // dd($result);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Event successfully";
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

    public function executeOrdersSeatsApproved($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::executeOrdersSeatsMatch($id_pedido);
                // ademas

                if($result == 'SI'){
                    $data_reg = [];
                    $codigo = Input::get('codigo');
                    $llave = Input::get('llave');
                    $data_reg["fecha_pago"] = Input::get('fecha_pago');
                    $clientIP = \Request::ip();
                    $data = [
                        "codigo" => $codigo,
                        "id_pedido" => $id_pedido,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => "Aprobado",
                        "ip" => $clientIP,
                        "llave" => $llave
                    ];
                    PurchasesData::updateRequestRegistry($data_reg,$id_pedido);
                    $result = PurchasesData::spProcessStepRunNext($data);
                    if ($result['error'] == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Event successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $result['message'];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else if ($result == "NO") {
                    // $jResponse['success'] = true;
                    // $jResponse['message'] = "Event successfully";
                    // $jResponse['data']    = [];
                    // $code                 = "200";
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Asientos Contables no Cuadrados";
                    $jResponse['data'] = [];
                    $code = "202";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
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

    public function listOdersDispatches()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $estado = Input::get("estado");
                $params = "";
                $id_anho = 0;  
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                if($rpta["nerror"]==0){
                    $id_anho = $rpta["id_anho"];
                }
                // dd($id_anho);
                $data = OrdersData::listOdersDispatches($id_pedido, $estado,$id_anho);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Ok.";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data."; // $message;
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

    public function addOdersDispatches()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_detalle = Input::get('id_detalle');
                // agregado ultimo
                $id_articulo = Input::get('id_articulo');
                $id_almacen = Input::get('id_almacen');
                $precio = Input::get('precio');
                $datax["id_articulo"] = $id_articulo;
                $datax2["id_almacen"] = $id_almacen;
                $datax3["precio"] = $precio;
                // $datax4["id_tipoigv"] = $id_tipoigv;
            //////////////////////////////
                $dataOrderDetail = PurchasesData::showOrdersDetails($id_detalle);
                $data = [
                    "id_detalle" => $id_detalle,
                    "id_persona" => $id_user,
                    "id_almacen" => $dataOrderDetail->id_almacen,
                    "id_articulo" => $dataOrderDetail->id_articulo,
                    "detalle" => Input::get("detalle"),
                    "cantidad" => Input::get("cantidad"),
                    "precio" => $dataOrderDetail->precio
                    // "id_tipoigv" => $dataOrderDetail->id_tipoigv
                ];
                ///////// agregado ultimo
                if ($id_articulo and $id_almacen and  $precio ) {
                    $data = array_merge($data, $datax);
                    $data = array_merge($data, $datax2);
                    $data = array_merge($data, $datax3);
                    // $data = array_merge($data, $datax4);
                    // $dataOrdersPurchases["id_pedido"] = $id_pedido;
                }
                //////////////

                // dd('dd',$data, $id_articulo, $id_almacen);
                $result = OrdersData::addOdersDispatches($data);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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
    public function updateOdersDispatches($id_despacho){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $precio = Input::get('precio');
                $data = [
                    "id_despacho" => $id_despacho,
                    "precio" => $precio
                ];
                $result = OrdersData::updateOdersDispatches($data);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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

    public function deleteOdersDispatches($id_despacho)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = "";
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::deleteOdersDispatches($id_despacho);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
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

    public function saveOrdersDispatchesOff($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get('codigo');
                $llave = "FIN";//Input::get('llave');
                $clientIP = \Request::ip();
                $data = [
                    "codigo" => $codigo,
                    "id_pedido" => $id_pedido,
                    "id_persona" => $id_user,
                    "id_entidad" => $id_entidad,
                    "detalle" => "Ejecutado",
                    "ip" => $clientIP,
                    "llave" => $llave
                ];
                $result = OrdersData::saveOrdersDispatchesOff($data);
                if ($result["error"] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = array("code" => $result["code"]);
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result["message"];
                    $jResponse['data'] = $result["code"];
                    $jResponse['error'] = $result["error"];
                    $code = "202";
                }
                /*if(!$result["error"]){
                    $cant = OrdersData::ListValidadCant($id_pedido);
                    foreach ($cant as $key => $item){
                        $cant_ped = $item->cant_ped;
                        $cant_des = $item->cant_des;
                    }
                    if($cant_ped == $cant_des){
                        
                        $result = PurchasesData::spProcessStepRunNext($data);
                        dd($result);
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                }*/
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listReportsAreas()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_pedido = Input::get("id_pedido");
                $data = OrdersData::listReportsAreas($id_entidad);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data;
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

    public function genereReportMyOrders()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $id_anho = Input::get("id_anho");
                $id_mes = Input::get("id_mes");
                $per_page = Input::get("per_page", "10");
                $page = Input::get("page", "1");
//                dd('recover', $id_mes);
                $proceso = Input::get("proceso");
                $area = Input::get("area");


                if ($codigo and $id_anho and $id_anho != 'undefined') {

                    $data = OrdersData::listReportMyOrdersTable($id_entidad, $id_user, $codigo, $id_anho, $id_mes, $proceso, $per_page, $page, $area);
//                    $dataStatistics = OrdersData::listReportMyOrdersStatistics($id_entidad, $id_user, $codigo, $id_anho, $id_mes, $proceso);
//                    $dataSet = OrdersData::getDataSet($id_entidad, $id_user, $codigo, $id_anho, $id_mes, $proceso);
                }

                if ($data) {
//                    $custom = collect(['dataset' => $dataSet]);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "";
                    $jResponse['data'] = $data;
//                    dd($jResponse);

                    /*$jResponse['data']    = [
                        "tabla"=>$data,
                        "estadistica"=>$dataStatistics,
                        "data"=>$dataSet];*/
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'ok';
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

    public function listOrderArea()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $per_page = Input::get("per_page", "10");
                $id_mes = Input::get("id_mes");
                $id_anho = Input::get("id_anho", date('Y'));
                $id_area_dest = Input::get("id_sedearea");

                if ($id_anho and $id_mes) {
                    $data = OrdersData::listOrderArea($id_mes, $id_anho, $id_area_dest, $id_entidad, $id_depto, $codigo, $per_page);
                    if ($data) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Search params incorrect or incomplete';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listOrdersByAreaDestino()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $per_page = Input::get("per_page", "10");
                $id_mes = Input::get("id_mes");
                $id_anho = Input::get("id_anho", date('Y'));
                $id_area_dest = Input::get("id_sedearea");
//                if ($id_anho and $id_area_dest) {
                if ($id_anho and $id_area_dest) {
                    $data = OrdersData::listOrdersByAreaDestino($id_mes, $id_anho, $id_area_dest, $id_entidad, $id_depto, $codigo, $per_page);
                    if ($data) {
                        /*$custom = collect([
                            'dataset'=>OrdersData::listOrderAreaDataSet($id_mes, $id_anho,$id_area_dest,$id_entidad,$codigo),
                            'rrrr'=>OrdersData::listOrderAreaUUU($id_mes, $id_anho,$id_area_dest,$id_entidad,$id_depto,$codigo,$per_page)
                        ]);*/
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Search params incorrect or incomplete';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listOrdersPending()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $per_page = Input::get("per_page", "10");
                $llave = Input::get("llave");
                $searchs = Input::get("search");
                $key = $llave;
                $llaves = [$llave];
                $dataIds = PurchasesData::listIdsProcessSteps($id_entidad, $codigo, $llaves);
                $dataPedidoCompra = OrdersData::listOrdersPending($id_entidad, $id_depto, $codigo, $id_user, $per_page, $dataIds, $key, $searchs);
                if ($dataPedidoCompra) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $dataPedidoCompra;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listTypesForms()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPedidoCompra = OrdersData::listTypesForms();
                if ($dataPedidoCompra) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $dataPedidoCompra;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listOrdersTypesAreas()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_sedearea = Input::get("id_sedearea");
                $data = OrdersData::listOrdersTypesAreas($id_sedearea);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addOrdersTypesAreas()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_sedearea = $params->id_sedearea;
            $pedido = $params->pedido;
            try {
                OrdersData::deleteOrdersTypesAreas($id_sedearea);
                foreach ($pedido as $key => $item) {
                    $id_tipopedido = $item->id_tipopedido;
                    $data = OrdersData::addOrdersTypesAreas($id_sedearea, $id_tipopedido);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listTypesOrders()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_sedearea = Input::get("id_sedearea");
                $data = OrdersData::listTypesOrders($id_sedearea);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listCars()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = OrdersData::listCars($id_entidad, $id_depto);

                $fecha = Input::get("fecha");
                //$data = OrdersData::listCars($id_entidad,$id_depto,$fecha);
                if ($data) {
                    foreach ($data as $key => $value) {
                        $row = OrdersData::ListCarsProgramming($id_entidad, $id_depto, $value->id_vehiculo, $fecha);
                        $parent[] = [
                            'id_vehiculo' => $value->id_vehiculo,
                            'placa' => $value->placa,
                            'marca' => $value->marca,
                            'modelo' => $value->modelo,
                            'asientos' => $value->asientos,
                            'km_precio' => $value->km_precio,
                            'items' => $row
                        ];
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listDrivers()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $fecha = Input::get("fecha");
                $data = OrdersData::listDrivers($id_entidad, $id_depto, $fecha);
                if ($data) {
                    foreach ($data as $key => $value) {
                        $row = OrdersData::ListTravelProgramming($id_entidad, $id_depto, $value->id_persona, $fecha);
                        $parent[] = [
                            'id_persona' => $value->id_persona,
                            'nombre' => $value->nombre,
                            'paterno' => $value->paterno,
                            'materno' => $value->materno,
                            'licencia' => $value->licencia,
                            'categoria' => $value->categoria,
                            'items' => $row
                        ];
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listOdersDispatchesMovi()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $estado = Input::get("estado");
                $data = OrdersData::listOdersDispatchesMovi($id_pedido, $estado);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Ok.";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data."; // $message;
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

    public function addOdersDispatchesMovi()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = [
                    "id_movilidad" => Input::get('id_detalle'),
                    "id_persona" => $id_user,
                    "km_inicio" => Input::get("km_inicio"),
                    "km_fin" => Input::get("km_fin"),
                    "hora_salida" => Input::get("hora_salida"),
                    "hora_llegada" => Input::get("hora_llegada"),
                    "importe" => Input::get("importe"),
                    "cantidad_r" => Input::get("cantidad_r")
                ];
                $result = OrdersData::addOdersDispatchesMovi($data);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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

    public function saveOrdersDispatchesMoviOff($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get('codigo');
                $fin = Input::get('fin');
                $llave = "FIN";//Input::get('llave');
                $clientIP = \Request::ip();
                $data = [
                    "codigo" => $codigo,
                    "id_pedido" => $id_pedido,
                    "id_persona" => $id_user,
                    "detalle" => "Ejecutado",
                    "ip" => $clientIP,
                    "llave" => $llave,
                    "fin" => $fin
                ];
                $result = OrdersData::saveOrdersDispatchesMoviOff($data);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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

    public function deleteCars($id_movilidad)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::deleteCars($id_movilidad);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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

    public function deleteDrivers($id_movilidad)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::deleteDrivers($id_movilidad);
                if (!$result["error"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message']; // $message;
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

    public function addOrdersSeatsGenerateTemplate()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $id_dinamica = Input::get("id_dinamica");
                $rpta = OrdersData::addOrdersSeatsGenerateTemplate($id_pedido, $id_dinamica);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
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

    public function addOrdersSeatsImports(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            try {
                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        $data = [
                            "id_pedido" => $request->id_pedido,
                            "id_tipoplan" => $row->plan,
                            "id_fondo" => $row->fondo,
                            "id_cuentaaasi" => $row->cuenta,
                            "id_restriccion" => $row->restriccion,
                            "id_ctacte" => $row->cta_cte,
                            "id_depto" => $row->depto,
                            "dc" => $row->dc,
                            "porcentaje" => $row->porcentaje,
                            "glosa" => $row->glosa,
                            "indicador" => 'IMPORTE'
                        ];
                        OrdersData::addOrdersSeats($data);
                    });
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showTypesOrders()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $llave = Input::get("llave");
                $data = OrdersData::showTypesOrders($llave);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
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

    public function updateOrdersNumber($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $llave = Input::get("llave");
                $data = OrdersData::updateOrdersNumber($id_pedido);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not Updated";
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

    public function addOrdersSeatsGenerateBudget()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $id_actividad = Input::get("id_actividad");
                $rpta = OrdersData::addOrdersSeatsGenerateBudget($id_pedido, $id_actividad);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
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
    public function listOrdersDash(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $data = OrdersData::listOrdersDash($id_entidad,$id_depto,$id_anho,$id_mes,$id_user);
                $total = OrdersData::listOrdersDashTotal($id_entidad,$id_depto,$id_anho,$id_mes,$id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data']    = $data;
                    $jResponse['total']   = $total[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
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
    public function listGrassSchedule(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_almacen = $request->query('id_almacen');
                $id_articulo = $request->query('id_articulo');
                $fecha = $request->query('fecha');
                $data = OrdersData::listGrassSchedule("1",$id_almacen,$id_articulo,$fecha);
                $data1 = OrdersData::listGrassSchedule("2",$id_almacen,$id_articulo,$fecha);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data']    = ["mañana" =>$data, "tarde" =>$data1];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
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
    /* pedido Reserva */
public function addOrdersDetailsShedule()
{
    // dd('holas');
    $jResponse  = GlobalMethods::authorizationLamb($this->request);
    $code       = $jResponse["code"];
    $valida     = $jResponse["valida"];
    $id_user    = $jResponse["id_user"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto   = $jResponse["id_depto"];
    if($valida == 'SI')
    {
        $jResponse = [];
        try
        {
            // $data['id_reserva']  = Input::get('id_reserva');
            $data['id_entidad'] = $id_entidad;
            $data['id_depto'] = $id_depto;
            $data['id_anho'] = Input::get('id_anho');
            $data["id_mes"] = Input::get('id_mes');
            $data['id_persona'] = $id_user;
            $data["id_cliente"] = Input::get('id_cliente');
            $data["id_almacen"] = Input::get('id_almacen');
            $data["id_articulo"] = Input::get('id_articulo');
            $data["cliente"] = Input::get('cliente');
            $data["detalle"] = Input::get('detalle');
            $data["cantidad"] = Input::get('cantidad');
            $data["precio"] = Input::get('precio');
            $data["importe"] = Input::get('importe');
            $data["fecha"] = Input::get('fecha');
            $data["hora_inicio"] = Input::get('hora_inicio');
            $data["hora_fin"] = Input::get('hora_fin');
            $data["estado"] = Input::get('estado');
            // dd($data);
            $result = OrdersData::addOrdersDetailsShedule($data);
            if($result)
            {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $result;
                $code = "200";
            }
            else
            {
                $jResponse['success'] = false;
                $jResponse['message'] = $result;
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
    // end_addOrdersDetailsReserva:
    return response()->json($jResponse,$code);
}
public function updateOrdersDetailsShedule($id_reserva){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $pagado = Input::get('pagado');
            $data = [
                "id_reserva" => $id_reserva,
                "pagado" => $pagado
            ];
            $result = OrdersData::updateOrdersDetailsShedule($data, $id_reserva);
            if ($result) {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = [];
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $result['message']; // $message;
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
public function listOrdersDetailsShedule (Request $request) {
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_pedido = Input::get("id_pedido");
                $per_page = Input::get("per_page", "10");
                $data = OrdersData::listOrdersDetailsShedule($per_page);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data;
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
public function deleteOrdersDetailsShedule($id_reserva)
{
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto = $jResponse["id_depto"];
    $message = '';
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $result = OrdersData::deleteOrdersDetailsShedule($id_reserva);
            if ($result) {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
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
public function listGrassScheduleReports(Request $request){
    // dd('data')
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto = $jResponse["id_depto"];
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            $id_almacen = $request->query('id_almacen');
            $id_articulo = $request->query('id_articulo');
            $fecha = $request->query('fecha');
      
            $data = OrdersData::listGrassScheduleReports($id_almacen,$id_articulo,$fecha);
        
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "The item does not exist";
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

// enviar a correo

    public function emailPedido(Request $request){
        // dd('ffff');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $email=$request->email;
                $id_pedido=$request->id_pedido;
                // $clave = $request->clave;
                // if (filter_var($email, FILTER_VALIDATE_EMAIL)) {


                    // $data = PaymentsData::showBoletaPDF($clave);

                    // $archivo="";
                    // $cod="";
                    // $persona  = "";
                    // $correo = "";
                    // $mes = "";
                    // $id_anho = 0;
                    // $id_mes  = 0;
                    // foreach($data as $row){
                    //     $archivo = $row->archivo;
                    //     $cod = $row->nombre.' - '.$row->id_anho;
                    //     $correo   = $row->correo;
                    //     $persona  = $row->persona;
                    //     $mes = $row->nombre;
                    //     $id_anho = $row->id_anho;
                    //     $id_mes = $row->id_mes;
                    // }

                    // $retdir  = PaymentsData::directorioBoleta($id_anho,$id_mes);

                    // if($retdir["nerror"]==0){
                        // if($archivo!=""){
                            //$file = realpath("boletas"). '/' . $archivo; 
                            // $file = $retdir["directorio"]. '/' . $archivo;
                            // dd('pedido', $id_pedido);
                            $pedido = OrdersData::showOrders($id_pedido);
                            // dd('pedido', $pedido->motivo);
                            if($pedido && $pedido->id_pedido) {

                                $data = array('id_anho'=>'id_anho','mes'=>'mes','persona'=>'persona','pedido'=>$pedido->motivo);
                                // $cod = $mes.'-'.$id_anho;
                                $email = 'armando_huarcaya@upeu.edu.pe';
                                Mail::send('emails.sendPedido', $data, function($message) use($email){
                                           $message->subject('Pedido Interno  - Universidad Peruana Unión');
                                           $message->to($email);
                                        });
                                        $jResponse['success'] = true;
                                        $jResponse['message'] = 'Succes';
                                        $jResponse['data'] = [];
                                        $code = "200";
                            } else {
                                $jResponse['success'] = false;
                            $jResponse['message'] = "No existe pedido";
                            $jResponse['data'] = [];
                            $code = "202";
                            }
        }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTypesCars(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $message = '';
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = OrdersData::listTypesCars();
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = $result;
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
    public function getArticlesAlmacen(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $id_almacen = $request->query('id_almacen');
                // dd($id_anho, $id_almacen);
                $result = OrdersData::getArticlesAlmacen($id_anho, $id_almacen);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se encontro registros";
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
    public function searchPersonaTrabajador(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request-> query('search');
            if ($searchs) {
                $data = OrdersData::searchPersonaTrabajador($searchs);
            } else {
               $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

public function deptoGrasSintetico(){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    if ($valida == 'SI') {
        $jResponse = [];
        if ($id_entidad) {
            $data = OrdersData::deptoGrasSintetico($id_entidad);
        } else {
           $data = null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function searchPersonaTrabajadorVarios(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    if ($valida == 'SI') {
        $jResponse = [];
        $searchs = $request-> query('search');
        if ($searchs) {
            $data = OrdersData::searchPersonaTrabajadorVarios($searchs);
        } else {
           $data = null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function deptoToIdDepto(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_depto=$request->id_depto;
        if ($id_entidad and $id_depto) {
            $data = OrdersData::deptoToIdDepto($id_entidad,  $id_depto);
        } else {
           $data = null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function searchsArticlesAlmacen(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_anho = $request-> query('id_anho');
        $id_almacen = $request-> query('id_almacen');
        $searchs = $request-> query('search');
        /*if($id_almacen == 33){
            $id_anho = 2020;//TEMPORAL LOGISITCA
        }*/
        if ($searchs) {
            $data = OrdersData::searchsArticlesAlmacen($id_anho, $id_almacen, $searchs);
        } else {
           $data = null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function updateDetailsAlmacen($id_detalle, Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    // dd('user', $id_user_apru, 'fecha', $fecha_apru, 'ids', $id_periodo_vac_trab, $request);
    if($valida=='SI'){
        $jResponse=[];                        
        try{     
             $response = OrdersData::updateDetailsAlmacen($id_detalle, $request);  
            if($response['success']){
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
                $code = "200";
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $response['message'];                        
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

public function myOrdersPdf(Request $request){
    
    
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code       = $jResponse["code"];
    $valida     = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto   = $jResponse["id_depto"];
    $username   =  $jResponse["email"];
 
    if($valida == 'SI')
    {
        $mensaje = '';
        $jResponse = [];
        try
        {
            $id_pedido = $request->id_pedido;
            $estado = $request->estado;
            $id_anho = 0;  
            $params = "";
            $tiene_params = "N";
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
            if($rpta["nerror"]==0){
                $id_anho = $rpta["id_anho"];
            }
            $datentrega = OrdersData::listOdersDispatches($id_pedido, $estado,$id_anho);
            
            $datdetalle = PurchasesData::listOrdersDetails($id_pedido, $id_anho, $id_entidad);
            
            $order = OrdersData::OrdersId($id_pedido);
            //dd($order);
                
            $pdf = DOMPDF::loadView('pdf.orders.order',[
                'datdetalle'=>$datdetalle,
                'datentrega'=>$datentrega,
                'pedido'=>$order,
                'username'=>$username // OBLIGATORIO
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
    // $pdf->save($ruta);
                    
    $doc = base64_encode($pdf->stream('print.pdf'));
    $jResponse = [
                'success' => true,
                'message' => "OK",
                'data' => ['items'=>$doc]
            ];
    return response()->json($jResponse);
        
}

public function listMyOrdersReportes(){
    $jResponse  = GlobalMethods::authorizationLamb($this->request);
    $code       = $jResponse["code"];
    $valida     = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto   = $jResponse["id_depto"];
    $id_persona   = $jResponse["id_user"];
    if($valida =='SI'){
        $jResponse =[];
        try{
            $codigo = Input::get("codigo");
            $per_page = Input::get("per_page", "10");
            $proceso = Input::get("proceso");
            $id_anho = Input::get("id_anho");
            $id_mes = Input::get("id_mes");
            $area = Input::get("area");
            $numero = Input::get("numero");

            $data = OrdersData::listMyOrdersReportes($id_entidad,$id_persona,$codigo,$per_page, $proceso, $id_anho, $id_mes, $area, $numero);
            if(!empty($data)){
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
            $jResponse['message'] = "ORACLE-".$e->getMessage().'. File: '.$e->getFile().' Line: '.$e->getLine();
            $jResponse['data'] = [];
            $code = "400";
        }
    }
    return response()->json($jResponse,$code);
}
public function deleteOrdersDetailsMovilidad($id_movilidad)
{
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];

    if($valida == 'SI')
    {
        $jResponse=[];
        try
        {
            $result = OrdersData::deleteOrdersDetailsMovilidad($id_movilidad);
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

public function listMyOrdersReportesTotal(){
    $jResponse  = GlobalMethods::authorizationLamb($this->request);
    $code       = $jResponse["code"];
    $valida     = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto   = $jResponse["id_depto"];
    $id_persona   = $jResponse["id_user"];
    if($valida =='SI'){
        $jResponse =[];
        try{
            $codigo = Input::get("codigo");
            $per_page = Input::get("per_page", "10");
            $proceso = Input::get("proceso");
            $id_anho = Input::get("id_anho");
            $id_mes = Input::get("id_mes");
            $numero = Input::get("numero");
            $id_departamento_origen = Input::get("id_departamento_origen");
            $data = OrdersData::listMyOrdersReportesTotal($id_entidad, $id_depto, $codigo,$per_page, $proceso, $id_anho, $id_mes, $numero, $id_departamento_origen);
            if(!empty($data)){
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
            $jResponse['message'] = "ORACLE-".$e->getMessage().'. File: '.$e->getFile().' Line: '.$e->getLine();
            $jResponse['data'] = [];
            $code = "400";
        }
    }
    return response()->json($jResponse,$code);
}
public function orderTimesReservation(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    // $id_entidad = $jResponse["id_entidad"];

    if ($valida == 'SI') {
        $jResponse = [];
        try{
        $de = OrdersData::horaReservaDe();
        $a = OrdersData::horaReservaA();
        if (count($de)>0) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['de' => $de, 'a' => $a];
            $code = "200";
        } else {
            $jResponse['success'] = true;
            $jResponse['message'] = 'The item does not exist';
            $jResponse['data'] = [];
            $code = "202";
        }
    }catch(Exception $e){
        $jResponse['success'] = false;
        $jResponse['message'] = "ORACLE-".$e->getMessage().'. File: '.$e->getFile().' Line: '.$e->getLine();
        $jResponse['data'] = [];
        $code = "400";
        }
    }
    return response()->json($jResponse, $code);
}
public function addServiciosDetalle(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    
    if($valida=='SI'){
        $jResponse=[];    
        DB::beginTransaction();                    
        try{   
            $response = OrdersData::addServiciosDetalle($request);  
            if($response['success']){
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";                    
                $code = "200";
            }else{
                DB::rollBack();
                $jResponse['success'] = false;
                $jResponse['message'] = $response['message'];                        
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
public function pedidosEjecutadosReportFirts(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_persona   = $jResponse["id_user"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_voucher=$request->id_voucher;
        if ($id_voucher) {
            $data = OrdersData::pedidosEjecutadosReportFirts($id_persona,  $id_voucher);
        } else {
            null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function pedidosEjecutadosReportFirtsXLS(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_persona   = $jResponse["id_user"];
    $mensaje = $jResponse["message"];
    $archivo = 'excel';
    $carpeta = 'excel';
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $id_voucher=$request->id_voucher;
        
            $data = OrdersData::pedidosEjecutadosReportFirts($id_persona,  $id_voucher);

            
            $excel = Excel::create($archivo, function($excel) use($data){

               $excel->sheet('Pedido', function($sheet) use($data) {
                   $sheet->loadView("xls.orders.order")->with('data',$data);
                   $sheet->setOrientation('landscape');
               });

           })->store('xls',storage_path($carpeta),false);
            //dd($excel);
            $file = $excel->storagePath.'/'.$excel->filename.'.'.$excel->ext;
           
           //$file = storage_path($carpeta).'/'.$archivo.'.xls';
            $archivo =file_get_contents($file);
            $doc  = base64_encode($archivo);
            $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc]
                    ];

            return response()->json($jResponse);
           
        } catch (Exception $e) {
            $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
        }
        
    }
    $excel = Excel::create($archivo, function($excel) use($mensaje){

         $excel->sheet('Error', function($sheet) use($mensaje) {
            $sheet->loadView("xls.error")->with('mensaje',$mensaje);
            $sheet->setOrientation('landscape');
        });

    })->store('xls',storage_path($carpeta),false);
    //dd($excel);
    $file = $excel->storagePath.'/'.$excel->filename.'.'.$excel->ext;
    //$file = storage_path($carpeta).'/'.$archivo.'.xls';
    //dd($file);
    $archivo =file_get_contents($file);
    //dd($archivo);
    $doc  = base64_encode($archivo);
    $jResponse = [
                'success' => true,
                'message' => "OK",
                'data' => ['items'=>$doc]
            ];
    
    return response()->json($jResponse);
}
public function pedidosEjecutadosReport(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_persona   = $jResponse["id_user"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_pedido=$request->id_pedido;
        $id_voucher=$request->id_voucher;
        
        if ($id_pedido) {
            $data = OrdersData::pedidosEjecutadosReport($id_persona, $id_pedido, $id_voucher);
        } else {
            null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function orderSummaryParent(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_depto   = $jResponse["id_depto"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_voucher=$request->id_voucher;
        $id_mes=$request->id_mes;
        if ($id_mes) {
            $data = OrdersData::orderSummaryParent($id_voucher,$id_depto, $id_mes);
        } else {
            null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'Success';
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
public function orderSummaryParentPDF(Request $request){
    
    
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code       = $jResponse["code"];
    $valida     = $jResponse["valida"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto   = $jResponse["id_depto"];
    $username   =  $jResponse["email"];
 
    if($valida == 'SI')
    {
        $mensaje = '';
        $jResponse = [];
        try
        {
            $id_anho=$request->id_anho;
            if (!$id_anho) {
             // $params = "";
            $id_anho = 0;  
            // $tiene_params = "N";
            $rpta = AccountingData::showPeriodoActivo($id_entidad);
            if ($rpta) {
                $id_anho = $rpta[0]->id_anho;
            }
            }
       
             
            $id_voucher=$request->id_voucher;
            $id_mes=$request->id_mes;
            $id_tipovoucher = $request->id_tipovoucher;

            //dd($order);
            $voucherData = AccountingData::listVoucherModulesAllShow($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher, $id_voucher);
            if ($id_voucher) {
                $dataExpor = OrdersData::orderSummaryParent($id_voucher,$id_depto, $id_mes);
            } else {
                null;
            }
            $pdf = DOMPDF::loadView('pdf.orders.summary',[
                'dataExpor'=>$dataExpor,
                'voucherData'=>$voucherData,
                'username'=>$username // OBLIGATORIO
                ])->setPaper('a4', 'portrait');
            
//   dd($voucherData, $dataExpor ,$pdf);
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
    // $pdf->save($ruta);
                    
    $doc = base64_encode($pdf->stream('print.pdf'));
    $jResponse = [
                'success' => true,
                'message' => "OK",
                'data' => ['items'=>$doc]
            ];
    return response()->json($jResponse);
        
}
public function asientoOrderDispaches(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_pedido=$request->id_pedido;
        if ($id_pedido) {
            $data = OrdersData::asientoOrderDispaches($id_pedido);
        } else {
            null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
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
public function getDeptoOrigen()
{
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    $id_entidad = $jResponse["id_entidad"];
    $id_depto = $jResponse["id_depto"];
    $message = '';
    if ($valida == 'SI') {
        $jResponse = [];
        try {
            $departamento = Input::get("departamento");
            $data = OrdersData::getDeptoOrigen($id_entidad, $departamento);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
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
public function getOrderEjecutadoVoucherDetalle(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_depto   = $jResponse["id_depto"];
    // $id_persona   = $jResponse["id_user"];
    if ($valida == 'SI') {
        $jResponse = [];
        $id_voucher=$request->id_voucher;
        $id_pedido=$request->id_pedido;
        $estado=$request->estado;
// dd($id_pedido , $id_voucher);
        if ($id_pedido and $id_voucher and $estado) {
            $data = OrdersData::getOrderEjecutadoVoucherDetalle($id_pedido, $id_voucher, $estado);
            $total = OrdersData::getOrderEjecutadoVoucherDetalleTotal($id_pedido, $id_voucher, $estado);
        } else {
            null;
        }
        if ($data) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'Success';
            $jResponse['data'] = ['items'=> $data, 'total' => $total];
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
public function updateCantidadPedidos($id_pedido,Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[];                        
        try{   
           $result = OrdersData::updateCantidadPedidos($id_pedido, $request);  
           if ($result) {
            $jResponse['success'] = true;
            $jResponse['message'] = "Se modifico satisfactoriamente";
            $jResponse['data'] = $result;
            $code = "200";
        } else {
            $jResponse['success'] = false;
            $jResponse['message'] = "No se pudo modificar";
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
    public function addPedidoDetalleAudio(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                DB::beginTransaction();
               $result = OrdersData::addPedidoDetalleAudio($request);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function filePedidoDetalle($id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = OrdersData::filePedidoDetalle($id_detalle);
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No se encontraron registros';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function getTimeLimiteBlock(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $result = OrdersData::getTimeLimiteBlock($request, $id_entidad, $id_depto);
                if  (!empty($result)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $result;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No se encontraron registros';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
}
