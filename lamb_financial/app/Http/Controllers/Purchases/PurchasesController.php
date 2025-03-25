<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Data\Setup\LegalPersonData;
use App\Models\Purchase;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\SetupData;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Data\Orders\OrdersData;
use App\Models\Compra as EliseoCompra;
use App\Models\CompraDetalle as EliseoCompraDetalle;
use App\Models\CompraAsiento as EliseoCompraAsiento;
use App\Models\PedidoCompra as EliseoPedidoCompra;
use App\Models\ProcessRun as EliseoProcessRun;
use App\Models\ProcessPasoRun as EliseoProcessPasoRun;
use App\Models\PedidoRegistro as EliseoPedidoRegistro;
use App\Models\PedidoDetalle as EliseoPedidoDetalle;

use App\Http\Controllers\Purchases\Validations\PurchasesValidation;
use App\Http\Controllers\Purchases\Utils\PurchasesUtil;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Controllers\Setup\Provider\sunat;
use App\Http\Controllers\Storage\StorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Inventories\WarehousesData;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Data\FinancesStudent\ComunData;



use Carbon\Carbon;
use PDO;
use Response;
use Mail;
use Excel;
use DOMPDF;
use stdClass;

class PurchasesController extends Controller
{
    private $request;
    private $id_modulo = "11";
    private $id_tipotransaccion = "16";
    private $id_tipopaso_init = "3";
    private $id_proceso = 9;
    private $listFormUtil = [
        "F1" => "",
        "F2" => "",
        "F3" => "",
        "F4" => "",
    ];
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /* PEDIDO_REGISTRO */
    public function showOrders($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showOrders($id_entidad, $id_pedido);
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
    public function showMyOrders($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showMyOrders($id_entidad, $id_user, $id_pedido);
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
    public function listMyOrders()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $per_page = Input::get("per_page", "10");
                $searchs = Input::get("search");
                $id_anho = Input::get("id_anho");
                $id_mes = Input::get("id_mes");
                $estado = Input::get("estado");

                $dataPedidoCompra = PurchasesData::listMyOrders($id_entidad, $id_depto, $id_user, $codigo, $per_page, $searchs, $id_anho, $id_mes, $estado);


                if (!empty($dataPedidoCompra)) {
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
                $jResponse['message'] = "ORACLE-" . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listOrdersPending(Request $request)
    {
        $jResponse  = GlobalMethods::authorizationLamb($request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $tipo = Input::get("tipo"); // Provisionados o aùn no.
                if ($id_entidad == 7124) {
                    $codigo = Input::get("codigo");
                    $per_page = Input::get("per_page", "10");
                    $llave = Input::get("llave");
                    $dato = Input::get("dato");
                    $id_anho = Input::get("id_anho");
                    $id_mes = Input::get("id_mes");
                    $nllave = explode(',', $llave);
                    $llaves = [$llave]; // No se Usa
                    $dataIds = PurchasesData::listIdsProcessSteps($id_entidad, $codigo, $nllave);
                    $dataPedidoCompra = PurchasesData::listOrdersPending($id_entidad, $codigo, $id_user, $per_page, $dataIds, $tipo, $id_depto, $dato, $id_anho, $id_mes);
                } else {
                    if ($tipo === 'S') {
                        // Provisiones
                        $response = PurchasesData::listProvisionesFinalizadasSinProceso($id_entidad, $id_depto, $id_user, $request);
                    } else {
                        // Pedidos
                        $response = PurchasesData::listOrdersPendingSinProceso($id_entidad, $id_depto, $id_user);
                    }
                    $dataPedidoCompra = $this->arrayPaginator($response, $request);
                }

                if (!empty($dataPedidoCompra)) {
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
        end:
        return response()->json($jResponse, $code);
    }

    public function arrayPaginator($array, $request)
    {
        $page = Input::get('page', 1);
        $perPage = Input::get("per_page", 10);
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice($array, $offset, $perPage, true),
            count($array),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function saveOrdersRefused($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get("codigo");
                $detalle = Input::get("detalle");
                $data = PurchasesData::showOrdersProcessPasoRun($id_entidad, $id_pedido, $codigo);
                if ($data) {
                    $dataEstado = ["estado" => "3"];
                    $dataUpdata = ["estado" => "3"];
                    $dataUpdata = ["detalle" => $detalle];
                    $result1 = PurchasesData::updateOrdersRegisters($dataEstado, $data->id_pedido);
                    $result2 = PurchasesData::updateProcessRun($dataEstado, $data->id_registro);
                    $result3 = PurchasesData::updateProcessPasoRun($dataUpdata, $data->id_detalle);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Refused successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The service does not success';
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
    public function ordersRefusedAttach()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            // deleting
            try {
                $codigo = Input::get("codigo");
                $detalle = Input::get("detalle");
                $id_pedido = Input::get("id_pedido");
                $tipo_file = Input::get('tipo_file');
                $data = PurchasesData::showOrdersProcessPasoRun($id_entidad, $id_pedido, $codigo);
                $archivo = Input::file("adjunto");

                $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                if ($data) {
                    $dataEstado = ["estado" => "3"];
                    $dataUpdata = ["estado" => "3"];
                    $dataUpdata = ["detalle" => $detalle];
                    $result1 = PurchasesData::updateOrdersRegisters($dataEstado, $data->id_pedido);
                    $result2 = PurchasesData::updateProcessRun($dataEstado, $data->id_registro);
                    $result3 = PurchasesData::updateProcessPasoRun($dataUpdata, $data->id_detalle);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Refused successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The service does not success';
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
    public function editRequest($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = Input::get("id_voucher");
                $data = array("id_voucher" => $id_voucher);
                PurchasesData::updateRequestRegistry($data, $id_pedido);
                $listPedidoCompra = PurchasesData::listRequestPurchaseByIdPedido($id_pedido);
                foreach ($listPedidoCompra as $item) {
                    $id_compra = $item->id_compra;
                    if ($id_compra) {
                        PurchasesData::updatePurchase($data, $id_compra);
                        $id_operorigen = 3;
                        $data2 = array("voucher" => $id_voucher);
                        PurchasesData::updateAccountantSeat($data2, $id_operorigen, $id_compra);
                    }
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function listMyOperationsPending() /* borrar -- 2018-01-03 */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listMyOperationsPending();
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
    public function listMyOperations()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listMyOperations('1', $id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listOperationsPendingApproval()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listOperationsPendingApproval('1');
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
    public function listOperationsPendingVob()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listOperationsPendingVob('1');
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
    public function listProvisions()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listProvisions('1');
                $jResponse['success'] = true;
                $jResponse['message'] = 'Listado de Proviciones.';
                $jResponse['data'] = $data;
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
    public function listProvisions2()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listProvisions('2');
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
    public function showOperation($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPedido        = PurchasesData::showPedidoRegistro($id_pedido);
                $dataPedidoDetalle = PurchasesData::listPedidoDetalleP($id_pedido);
                $dataPedidoFile    = PurchasesData::listPedidoFileP($id_pedido);
                $dataPedidoCompra  = PurchasesData::listPedidoCompraP($id_pedido);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data']    = [
                    'pedido' => $dataPedido, 'pedido_detalle' => $dataPedidoDetalle, 'pedido_file' => $dataPedidoFile, 'pedido_compra' => $dataPedidoCompra
                ];
                $code                 = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function summaryOperationsPendingApproval()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listOperationsPendingApproval('2');
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
    public function summaryOperationsPendingVob()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listOperationsPendingVob('2');
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
    public function showProvision($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPedido = PurchasesData::showPedidoRegistro_provision($id_pedido);
                if (!$dataPedido) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Pedido no existe.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $listPedidoCompra = PurchasesData::listPedidoCompraJoinProveedor($id_pedido);
                foreach ($listPedidoCompra as $key => $value) {
                    $listPedidoFile         = PurchasesData::listPedidoFile_ids($id_pedido, $value->id_pcompra);
                    $value->pedido_file     = $listPedidoFile;
                    $listPedidoCompra[$key] = $value;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data']    = [
                    'pedido' => $dataPedido, 'pedido_compra' => $listPedidoCompra
                ];
                $code                 = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function summaryOperations()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listMyOperations('2', $id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addOrdersRegisters(Request $request)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {

            //if($id_entidad == 7124 &&   (Input::get('llave') == "FP" ||Input::get('llave') == "FPP3" || Input::get('llave') == "FPPI" || Input::get('llave') == null)){
            $jResponse = [];
            try {
                $msj = "";
                $listal = PurchasesUtil::dataOrdersRegistriesUso(Input::get('pasos')); // list($listPaso, $cantPasos, $llave)
                //dd($listal,Input::get('pasos'),Input::get('pasos[]'),$request->all());
                $listPaso = $listal["list_paso"];  // "list_paso" => $lista,
                $cantPasos = $listal["cant"]; // "cant" => $cant,
                $llave = $listal["llave"]; // "llave" => $llave

                if ($llave == "" || $llave == null) {
                    $llave = Input::get('llave');
                }

                $oData = PurchasesUtil::utilCall("dataOrdersRegistries" . $llave);
                $validData['odata'] = $oData;
                $validData['listPaso'] = $listPaso;
                $validData['cantPasos'] = $cantPasos;
                $validData['llave'] = $llave;
                if ($oData->valid == false || $cantPasos == 0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $oData->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endAddOrdersRegisters;
                }
                $data = $oData->data;
                $data["id_entidad"] = $id_entidad;
                $data["id_depto"] = $id_depto;
                $data["fecha"] = DB::raw('sysdate');
                $data["fecha_pedido"] = DB::raw('sysdate');
                $data["estado"] = 0;
                $data["id_persona"] = $id_user;

                $actividad = PurchasesData::showTypeActivityEconomic(Input::get('ciiu'));
                $validData['actividad'] = $actividad;
                foreach ($actividad as $key => $item) {
                    $data["id_tipoactividadeconomica"] = $item->id_tipoactividadeconomica;
                }
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', $tiene_params, $params);
                $validData['rpta'] = $rpta;
                if ($rpta["nerror"] == 0) {
                    $id_anho = $rpta["id_anho"];
                    $id_mes = $rpta["id_mes"];
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endAddOrdersRegisters;
                }
                $data["id_anho"] = $id_anho;
                $data["id_mes"] = $id_mes;

                //Elimina pedidos No terminados de Registrar
                $delt = OrdersData::deleteOrders($id_user);
                $validData['delt'] = $delt;
                //
                //$result = $this->privateAddOrdersRegistry($data);
                //   $result = PurchasesData::addRequestRegistry($data);
                $result = EliseoPedidoRegistro::create($data);
                // $id_pedido = $result->id_pedido;
                $validData['result_addRequestRegistry'] = $result;
                if ($result['error'] == 0) {
                    $id_pcompra = null;
                    $id_pedido = $result->id_pedido;
                    $dataUP["id_pbancaria"] = Input::get('id_pbancaria');
                    $dataUP["comentario"] = Input::get('comentario');
                    PurchasesData::updateRequestRegistry($dataUP, $id_pedido);
                    $mypedido = PurchasesData::showOrdersView($id_pedido);
                    $validData['mypedido'] = $mypedido;
                    foreach ($mypedido as $key => $value) {
                        $result["nombre_areaorigen"] = $value->nombre_areaorigen;
                        $result["nombre_areadestino"] = $value->nombre_areadestino;
                    }

                    if (isset($oData->dataDetalles)) {
                        $detalles = $oData->dataDetalles;
                        foreach ($detalles as $key => $value) {
                            $value["id_pedido"] = $id_pedido;
                            $resultOD = $this->privateAddOrdersDetails($value);
                            $validData['resultOD_this_privateaddORder'] = $resultOD;
                        }
                    }
                    if (isset($oData->dataPC)) {
                        $dataOrdersPurchases = $oData->dataPC;
                        $dataOrdersPurchases["id_pedido"] = $id_pedido;
                        $dataOrdersPurchases["estado"] = "0";
                        $dataOrdersPurchases["fecha"] = DB::raw('sysdate');
                        $dataOrdersPurchases["tramite_pago"] = Input::get('tramite_pago');
                        $dataOrdersPurchases["id_persona"] = Input::get('id_persona');
                        $dataOrdersPurchases["id_vale"] = Input::get('id_vale');
                        $resultOD = self::privateAddOrdersPurchases($dataOrdersPurchases);
                        $validData['$resultprivateaddordersPurchases'] = $resultOD;
                        // $id_pcompra = $resultOD["id_pcompra"];
                        $id_pcompra = $resultOD->id_pcompra;

                        //Para Registra los prroyectos que afecta la compra
                        $afecta_all = Input::get('afecta_all');
                        if ($afecta_all == 'S') {
                            $proyectos = json_decode($request->id_proyecto);
                            foreach ($proyectos as $key => $value) {
                                $objProyectoCompra = $this->privateAddProyectoCompra($value->id_proyecto, $id_pcompra, $value->importe, $afecta_all);
                                $validData['objproyectocompra'] = $objProyectoCompra;
                            }
                        } else {
                            $id_proyecto = $request->id_proyecto;
                            $objProyectoCompra = $this->privateAddProyectoCompra($id_proyecto, $id_pcompra, $dataOrdersPurchases['importe'], $afecta_all);
                            $validData['objproyectocompra'] = $objProyectoCompra;
                        }

                        $flujo = Input::get('flujo'); //para identificar pedido sim
                        if ($flujo == "FPS") {
                            $purchsd = PurchasesData::addOrdersPurchaseDetailsGenerate($id_pcompra, $id_pedido);
                            $validData['purchsd'] = $purchsd;
                        }
                    } else {
                        // dd('ELSEEEEEE--oData',$oData);
                    }
                    if (isset($oData->dataFiles)) {
                        $tipo_file = Input::get('tipo_file');
                        $files = $oData->dataFiles;
                        foreach ($files as $key => $value) {
                            //Solo para Codificar los Files de RxH
                            if ($value == "archivo_recibo") {
                                $tipo_file = "7";
                            }
                            if ($value == "archivo_sustento") {
                                $tipo_file = "9";
                            }
                            if ($value == "archivo_constancia") {
                                $tipo_file = "8";
                            }

                            // if ($value == 'archivo_img' || $value == 'archivo_pdf' || $value == 'archivo_xml') {
                            //     $directory = 'lamb-financial/purchases/comprobantes';
                            //     $tipo_file = 3;
                            // } else {
                            //     $directory = 'lamb-financial/purchases/proformas';
                            //     $tipo_file = 2;
                            // }

                            // $storage = new StorageController(); 
                            // $file_data = $storage->postFile(Input::file($value), $directory);

                            // print_r($filedata); 
                            // self::addFileData($id_pedido, $id_pcompra, $filedata);

                            // $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, $id_pcompra, $value, $tipo_file);
                            $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, $id_pcompra, $value, $tipo_file );
                            $validData['objpedidofile'] = $objPedidoFile;
                        }
                    }
                    if (isset($oData->dataPasos)) {
                        $codigo = Input::get('codigo');
                        $result2 = $this->privateAddsProcessRunsAndSteps($id_pedido, $listPaso, $id_user, $id_entidad, $codigo);
                        $validData['result2'] = $result2;
                    }
                    if (isset($oData->dataNext)) {
                        $codigo = Input::get('codigo');
                        // $result3 = $this->privateAddProcessStepRunNext($codigo, $id_pedido, $id_user,$id_entidad,null); -> ($codigo, $id_pedido, $id_user,$id_entidad,$detalle,$ip)
                        $clientIP = \Request::ip();

                        //OBTENGO EL PASO NEXT DEPENDIENDO DEL TIPO DE GASTO
                        //SI TIPO GASTO = A => CONSEJO U ORDEN DE COMPRA
                        //SI TIPO GASTO = C => TESORERIA O PRE-PROVISION ALMACEN
                        $codigo_gasto = "";
                        $result = PurchasesData::showTypesPurchasesExpenses($id_pedido);
                        $validData['resultshowTypesPurchasesExpense'] = $result;
                        foreach ($result as $key => $item) {
                            $codigo_gasto = $item->codigo;
                        }
                        $llave = "";
                        if ($codigo_gasto == "A") {
                            $llave = "FOC";
                        } elseif ($codigo_gasto == "C") {
                            $llave = "FAJ";
                        }
                        $data = [
                            "codigo"    => $codigo,
                            "id_pedido" => $id_pedido,
                            "id_persona" => $id_user,
                            "id_entidad" => $id_entidad,
                            "detalle"   => null,
                            "ip"        => $clientIP,
                            "llave"     => $llave
                        ];
                        //Genero el nro de pedido solo para proceso de Compras
                        $updateorder = OrdersData::updateOrdersNumber($id_pedido);
                        $validData['updateorder'] = $updateorder;
                        $result = PurchasesData::spProcessStepRunNext($data);
                        $validData['resultspproceesssteprunnext'] = $result;
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully" . $msj;
                    $jResponse['data'] = $result;
                    $jResponse['exceps'] = $validData;
                    $jResponse['id_pedido'] = $id_pedido;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
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
        /*}else{
            $jResponse['success'] = false;
            $jResponse['message'] = "Las Compras y/o Pedidos sólo se registrarán hasta el 18 de diciembre por cierre de año.";
            $jResponse['data'] = [];
            $code = "202";
        }*/

        endAddOrdersRegisters:
        return response()->json($jResponse, $code);
    }

    public function addFileData($id_pedido, $id_pcompra, $filedata) {
        print_r('ID_PEDIDO', $id_pedido);
        print_r('ID_COMPRA', $id_pcompra);
        print_r('URL', $filedata);
    }

    public function updateOrdersRegisters($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                /* $oData = PurchasesUtil::dataOrdersRegistriesF1();
                if($oData->invalid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $oData->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdateOrdersRegisters;
                }
                $data = $oData->data; */
                $data = ["id_areagasto" => Input::get("id_areagasto")];
                $result = PurchasesData::updateRequestRegistry($data, $id_pedido);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end_UpdateOrdersRegisters:
        return response()->json($jResponse, $code);
    }
    public function deleteOrdersRegisters($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deleteRequestRegistry($id_pedido);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddOrdersRegistry($data)
    {
        try {
            // $id_pedido = PurchasesData::getMax('pedido_registro', 'id_pedido') + 1;
            // $data = array_merge(array("id_pedido" => $id_pedido), $data);
            // $success = PurchasesData::addRequestRegistry($data);
            // if ($success) {
            //     return $data;
            // }
            // return false;
        } catch (Exception $e) {
            return false;
        }
    }
    private function privateAddRequestRegistry($data)
    {
        $result = new class
        {
        };
        try {
            // $id_do =pedi PurchasesData::getMax('pedido_registro', 'id_pedido') + 1;
            // $data = array_merge(array("id_pedido" => $id_pedido), $data);
            // $success = PurchasesData::addRequestRegistry($data);
            // if ($success) {
            //     $result->success = true;
            //     $result->data    = $data;
            //     $result->message   = "";
            // } else {
                $result->success = false;
                $result->data    = [];
                $result->message   = "No Registro.";
            // }
        } catch (Exception $e) {
            $result->success = false;
            $result->data    = [];
            $result->message = $e->getMessage();
        }
        return $result;
    }
    private function privateAddListRequestRegistry($listData)
    {
        $result = [];
        try {
            foreach ($listData as $key => $entity) {
                try {
                    // $id_pedido = PurchasesData::getMax('pedido_registro', 'id_pedido') + 1;
                    $data = [];
                    // $data["id_pedido"] = $id_pedido;
                    foreach ($entity as $key => $value) {
                        $data[$key] = $value;
                    }
                    $objPedido = EliseoPedidoRegistro::create($data);
                    // $objPedido = PurchasesData::addRequestRegistry($data);
                    $result[] = array(
                        "success" => true,
                        "entity" => $objPedido,
                        "error" => ""
                    );
                } catch (Exception $e) {
                    $result[] = array(
                        "success" => false,
                        "entity" => [],
                        "error" => $e->getMessage()
                    );
                }
            }
        } catch (Exception $e) {
            $result[] = array(
                "success" => false,
                "entity" => [],
                "error" => $e->getMessage()
            );
        }
    }
    /* PEDIDO_DETALLE */
    public function listOrdersDetailsToDispatches()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_pedido = Input::get("id_pedido");
                $data = PurchasesData::listOrdersDetailsToDispatches($id_pedido, $id_anho);
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
    public function listOrdersDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_pedido = Input::get("id_pedido");
                $data = PurchasesData::listOrdersDetails($id_pedido, $id_anho, $id_entidad);
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
    public function addOrdersDetails()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $oData = PurchasesUtil::dataOrdersDetails();
                if ($oData->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $oData->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddOrdersDetails;
                }
                $data = $oData->data;
                if ($oData->tipo == "D") {
                    $result = $this->privateAddOrdersDetails($data);
                } else {
                    $result = $this->privateAddOrdersMovilidad($data);
                }
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result;
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
        end_AddOrdersDetails:
        return response()->json($jResponse, $code);
    }

    public function updateOrdersDetails($id_pedido, $id_detalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $oData = PurchasesUtil::validationOrdersDetails();
                if ($oData->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $oData->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdateOrdersDetails;
                }
                $data = $oData->data;
                $result = PurchasesData::updateOrdersDetails($data, $id_detalle);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end_UpdateOrdersDetails:
        return response()->json($jResponse, $code);
    }
    public function deleteOrdersDetails($id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PurchasesData::deleteOrdersDetails($id_detalle);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
    public function updateOrdersMovilidad($id_detalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $oData = PurchasesUtil::dataOrdersMovilidad();
                if ($oData->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $oData->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdateOrdersDetails;
                }
                $data = $oData->data;
                $result = PurchasesData::updateOrdersMovilidad($data, $id_detalle);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end_UpdateOrdersDetails:
        return response()->json($jResponse, $code);
    }
    private function privateAddOrdersDetails($data)
    {
        $result = new class
        {
        };
        try {
            //$id_detalle = PurchasesData::getMax('pedido_detalle','id_detalle')+1;
            //$data = array_merge(array("id_detalle" => $id_detalle), $data);
            $result = PurchasesData::addOrdersDetails($data);
            if ($result) {
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            //dd($e->getMessage());
            return false;
        }
    }
    private function privateAddOrdersMovilidad($data)
    {
        $result = new class
        {
        };
        try {
            //$id_detalle = PurchasesData::getMax('pedido_movilidad','id_movilidad')+1;
            //$data = array_merge(array("id_movilidad" => $id_detalle), $data);
            $result = PurchasesData::addOrdersMovilidad($data);
            if ($result) {
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    /* PEDIDO_COMPRA */
    public function showRequestReceipt($id_pcompra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPedidoCompra = PurchasesData::showPedidoCompra_Especial($id_pcompra);
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
    public function listOrdersPurchases()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $data = PurchasesData::listOrdersPurchases($id_pedido);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was finded successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Not data.";
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
    public function showOrdersPurchases($id_pcompra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_pedido = Input::get("id_pedido");
                $data = PurchasesData::showOrdersPurchases($id_pcompra);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was finded successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Not data.";
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
    public function addOrdersPurchases()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPedidoCompra = PurchasesUtil::dataOrdersPurchases();
                if ($dataPedidoCompra->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataPedidoCompra->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_addOrdersPurchases;
                }
                $data = $dataPedidoCompra->data;
                $data["fecha"] = DB::raw('sysdate');
                $data["estado"] = 0;
                $result = self::privateAddOrdersPurchases($data);
                if ($result) {
                    $tipo_file = Input::get('tipo_file');
                    $archivos = Input::file("archivos");
                    if ($archivos) {
                        foreach ($archivos as $archivo) {
                            $resultFile = $this->privateUploadPedidoFile($result->id_pedido, $result->id_pcompra, $archivo, $tipo_file);
                        }
                    }
                    // Sube Files de Comprobantes en Pre-Provision
                    if (Input::hasFile('archivo_img')) {
                        $resultFile = $this->privateUploadPedidoFile($result->id_pedido, $result->id_pcompra, Input::file('archivo_img'), $tipo_file);
                    }
                    if (Input::hasFile('archivo_pdf')) {
                        $resultFile = $this->privateUploadPedidoFile($result->id_pedido, $result->id_pcompra, Input::file('archivo_pdf'), $tipo_file);
                    }
                    if (Input::hasFile('archivo_xml')) {
                        $resultFile = $this->privateUploadPedidoFile($result->id_pedido, $result->id_pcompra, Input::file('archivo_xml'), $tipo_file);
                    }
                    // Fin 

                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error: " . $result;
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
        end_addOrdersPurchases:
        return response()->json($jResponse, $code);
    }

    // public function saveFile() {
    //     if
    // }

    // public function  

    public function deleteOrdersPurchases($id_pcompra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PurchasesData::deleteOrdersPurchaseDetailsALL($id_pcompra);
                $result = PurchasesData::deleteOrdersPurchases($id_pcompra);
                if ($result["success"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error: " . $result["message"];
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
    private static function privateAddOrdersPurchases($data)
    {
        try {

            // $id_pcompra = PurchasesData::getMax('pedido_compra', 'id_pcompra') + 1;
            // $data = array_merge(array("id_pcompra" => $id_pcompra), $data);
            // $success = PurchasesData::addOrdersPurchases($data);
            $pedidoCompra = EliseoPedidoCompra::create($data);
            // if ($success) {
                //$data = PurchasesData::showPedidoCompra($id_pcompra);
                return $pedidoCompra;
            // }
            // return false;
        } catch (Exception $e) {
            return false;
        }
    }
    /* PROCESS_FLUJO */
    public function listProcessFlowByInit()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get('codigo');
                $dataP = PurchasesData::showProcessByCodigo($codigo, $id_entidad); // 9 -- new 8 // CODIGO[6,7] --> [PEDIDOS INTERDEPARTAMENTAS,purchases]
                if (is_null($dataP)) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Alto! No existe un proceso con código: ' . $codigo . ' en la entidad: ' . $id_entidad;
                    $jResponse['data'] = null;
                    $code = "400";
                    goto end;
                }

                $id_tipopaso = $this->id_tipopaso_init;
                $dataPF = PurchasesData::showProcessFlujoByProcesoTipoPaso($dataP->id_proceso, $id_tipopaso);
                $dProcessFlujo = new class
                {
                };
                $dProcessFlujo->id_paso = $dataPF->id_paso; // 139; // $dataPF->id_paso;
                $dProcessFlujo->id_proceso = $dataP->id_proceso;
                $data = $this->privateListProcessFlujoByParent(array($dProcessFlujo));
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
        end:
        return response()->json($jResponse, $code);
    }
    private function privateListProcessFlujoByParent($listProcessFlujo)
    {
        $lProcessFlujo = [];
        foreach ($listProcessFlujo as $key => $value) {
            $lProcessFlujo = PurchasesData::listFlujoX($value->id_proceso, $value->id_paso);
            foreach ($lProcessFlujo as $keyProcessFlujo => $processFlujo) {
                if ($processFlujo->id_tipopaso == "1" || $processFlujo->id_tipopaso == "3") {
                    $id_paso = $processFlujo->id_paso_next;
                    $id_proceso = $processFlujo->id_proceso;
                    $dProcessFlujo = new class
                    {
                    };
                    $dProcessFlujo->id_paso = $id_paso;
                    $dProcessFlujo->id_proceso = $id_proceso;
                    $items = $this->privateListProcessFlujoByParent(array($dProcessFlujo));
                    $lProcessFlujo[$keyProcessFlujo]->items =  $items;
                }
                /* else if($processFlujo->id_tipopaso == "5" || $processFlujo->id_tipopaso == "6")
                {
                    / * $id_paso = $processFlujo->id_paso_next;
                    $id_proceso = $processFlujo->id_proceso;
                    $dProcessFlujo = new class{};
                    $dProcessFlujo->id_paso = $id_paso;
                    $dProcessFlujo->id_proceso = $id_proceso;
                    $items = $this->privateListProcessFlujoByParent(array($dProcessFlujo));
                    $lProcessFlujo[$keyProcessFlujo]->items =  $items; * /
                    unset($processFlujo[$keyProcessFlujo]);
                } */
            }
        }
        return $lProcessFlujo;
    }
    /* PROCESS */
    private function privateAddProcess($data)
    {
        try {
            $id_proceso = PurchasesData::getMax('process', 'id_proceso') + 1;
            $data = array_merge(array("id_proceso" => $id_proceso), $data);
            $success = PurchasesData::addProcess($data);
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
    public function z() /* PRUEBAS */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $id_entidad = $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $id_depto = $jResponse["id_depto"];
        // dd($id_entidad,$id_user,$id_depto, 'JOSE CAMBIOS');
        // lamb
        $result = PurchasesData::zzz($id_depto, $id_entidad, $id_user);
        dd($result);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $id_entidad = $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $id_depto = $jResponse["id_depto"];
        dd($id_entidad, $id_depto, $id_user);
        //
        $id_entidad = $jResponse["id_entidad"];
        $dataYear = SetupData::yearActivo($id_entidad);
        // id_anho
        foreach ($dataYear as $key => $item) {
            // $entity_name = $item->nombre;
            // $departament_name = $item->depto;
            $id_anho = $item->id_anho;
            break;
        }
        // $data["id_anho"] = $id_anho;
        // $data["id_anho"] = $id_anho;
        $dataMonth = SetupData::monthEntity($id_entidad, $id_anho);
        foreach ($dataMonth as $key => $item) {
            // $entity_name = $item->nombre;
            // $departament_name = $item->depto;
            $id_mes = $item->id_mes;
        }
        // $data["id_mes"] = $id_mes;
        dd($id_entidad, $dataYear, $dataMonth);

        $result = PurchasesData::zzz();
        dd($result);
        $id_entidad = $jResponse["id_entidad"];
        $codigo = 7;
        $llaves = ["P1"];
        // dd($llaves);
        $result = PurchasesData::listIdsProcessSteps($id_entidad, $codigo, $llaves);
        dd($result);
        // return redirect('purchases/dashboard');
        // return redirect('purchases/my-orders/168?codigo=6');
        return redirect()->route('purchases/my-orders/168', ['codigo' => 6]);
        // purchases/my-orders/168?codigo=6
        return;
        // die();
        /* END-TEMP */
        PurchasesData::xxxaaa();
        dd();
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $id_entidad = $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $id_depto = $jResponse["id_depto"];
        /* privateListProcessFlujoByParent($listProcessFlujo); */
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $id_entidad = 7124; // $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $id_depto = $jResponse["id_depto"];
        $id_pedido = "30180118";
        $listPaso = [69, 89, 68];
        // $this->privateRegisterProcesoX($id_entidad,$id_pedido,$listPaso,$id_user);
        $id_modulo = $this->id_modulo;
        $id_tipotransaccion = 17; // $this->id_tipotransaccion;
        $dataP = PurchasesData::showProcessxxx(); // PurchasesData::showProcessByEstadoActivo($id_entidad,$id_modulo,$id_tipotransaccion);

        $id_tipopaso = 3; // $this->id_tipopaso_init;
        $dataPF = PurchasesData::showProcessFlujoByProcesoTipoPaso($dataP->id_proceso, $id_tipopaso);

        $dProcessFlujo = new class
        {
        };
        $dProcessFlujo->id_paso = $dataPF->id_paso;
        $dProcessFlujo->id_proceso = $dataP->id_proceso;

        $data = $this->privateListProcessFlujoByParent(array($dProcessFlujo));

        /* */
        /* */
        $dd = $this->privateListProcessFlujoByParent(array($dProcessFlujo));

        $id_pedido = "20180118";
    }
    public function z1($param1)
    {
        echo $param1;
        /* if(isset($param1))
            echo $param1;
        echo "<br>";
        if(isset($param2))
            echo $param2; */
    }
    public function z2($param1, $param2)
    {
        echo $param1 . "/" . $param2;
    }
    public function z3($param1, $param2, $param3)
    {
        echo $param1 . "/" . $param2 . "/" . $param3;
    }
    public function z4($param1, $param2, $param3, $param4)
    {
        echo $param1 . "/" . $param2 . "/" . $param3 . "/" . $param4;
    }
    /* PROCESS + PASO + RUN */
    private function privateRegisterProcesoX($id_entidad, $id_pedido, $listPaso, $id_user)
    {
        $jResponse = [];
        try {
            $id_modulo = $this->id_modulo;
            $id_tipotransaccion = $this->id_tipotransaccion;
            $id_tipopaso = $this->id_tipopaso_init;
            $dProcess = PurchasesData::showProcessByEstadoActivo($id_entidad, $id_modulo, $id_tipotransaccion);
            // $id_pedido = $this->request->input('id_pedido');
            // PurchasesData::deletePedidoFile($id_pfile);

            $dProcessRun = PurchasesData::showProcessRunByProcesoOperacion($dProcess->id_proceso, $id_pedido);
            if ($dProcessRun) {
                $dProcessPasoRun = PurchasesData::showProcessPasoRun($dProcessRun->id_registro, $dProcessRun->id_paso_actual);
                if ($dProcessPasoRun) {
                    $dataUpdate = array("estado" => "1");
                    PurchasesData::updateProcessPasoRun($dataUpdate, $dProcessPasoRun->id_detalle);
                }
            } else {
                $dataAdd = [
                    "id_proceso"    => $dProcess->id_proceso,
                    "id_operacion"  => $id_pedido,
                    "detalle"       => "Iniciado",
                    "estado"        => "0"
                ];
                $dProcessRun = EliseoProcessRun::create($dataAdd);
                // $dProcessRun = $this->privateAddProcessRun($dataAdd);
                $dProcessPasoRun = null;
            }
            if (count($listPaso) > 0) {
                $id_paso = "";
                foreach ($listPaso as $key => $value) {
                    $id_paso =  $value;
                    $dataPPR = [
                        "id_registro"   => $dProcessRun->id_registro,
                        "id_paso"       => $id_paso,
                        "id_persona"    => $id_user,
                        "fecha"         => DB::raw('sysdate'),
                        "detalle"       => "",
                        "numero"        => 0,
                        "revisado"      => "",
                        "ip"            => "localhost",
                        "estado"        => "0"
                        // "id_paso_next"  => 68
                    ];
                    EliseoProcessPasoRun::create($dataPPR);
                    // $this->privateAddProcessPasoRun($dataPPR);
                }
                $dataUpdate = array("id_paso_actual" => $id_paso);
                PurchasesData::updateProcessRun($dataUpdate, $dProcessRun->id_registro);
            } else {
                if ($dProcessPasoRun) {
                    $dataPF = PurchasesData::showProcessFlujoByProcesoPaso($dProcess->id_proceso, $dProcessPasoRun->id_paso);
                    $dataPPR = [
                        "id_registro"   => $dProcessRun->id_registro,
                        "id_paso"       => $dataPF->id_paso,
                        "id_persona"    => $id_user,
                        "fecha"         => DB::raw('sysdate'),
                        "detalle"       => "",
                        "numero"        => 0,
                        "revisado"      => "",
                        "ip"            => "localhost",
                        "estado"        => "0"
                        // "id_paso_next"  => 68
                    ];
                    EliseoProcessPasoRun::create($dataPPR);
                    // $this->privateAddProcessPasoRun($dataPPR);
                    $dataUpdate = array("id_paso_actual" => $dataPF->id_paso);
                    PurchasesData::updateProcessRun($dataUpdate, $dProcessRun->id_registro);
                } else {
                    $id_tipopaso = $this->id_tipopaso_init;
                    $dataPF = PurchasesData::showProcessFlujoByProcesoTipoPaso($dProcess->id_proceso, $id_tipopaso);
                    $dataPPR = [
                        "id_registro"   => $dProcessRun->id_registro,
                        "id_paso"       => $dataPF->id_paso,
                        "id_persona"    => $id_user,
                        "fecha"         => DB::raw('sysdate'),
                        "detalle"       => "",
                        "numero"        => 0,
                        "revisado"      => "",
                        "ip"            => "localhost",
                        "estado"        => "0"
                        // "id_paso_next"  => 68
                    ];
                    EliseoProcessPasoRun::create($dataPPR);
                    // $this->privateAddProcessPasoRun($dataPPR);
                    $dataUpdate = array("id_paso_actual" => $dataPF->id_paso);
                    PurchasesData::updateProcessRun($dataUpdate, $dProcessRun->id_registro);
                }
            }

            $jResponse['success'] = true;
            $jResponse['message'] = "The item was created successfully";
            $jResponse['data'] = []; // ['id_pfile'=>$result["id_pfile"], 'nerror'=>$result["nerror"], 'msgerror'=>$result["msgerror"]];
            $code = "200";
        } catch (Exception $e) {
            /* $jResponse['success'] = false;
            $jResponse['message'] = "ORA-".$e->getMessage();
            $jResponse['data'] = [];
            $code = "400"; */
            return false;
        }
    }
    /* PROCESS_RUN */
    private function privateAddProcessRun($data)
    {
        try {
            // $id_registro = PurchasesData::getMax('process_run', 'id_registro') + 1;
            // $data = array_merge(array("id_registro" => $id_registro), $data);

            // $success = PurchasesData::addProcessRun($data);
            // return $data;
        } catch (Exception $e) {
            // ($e->getMessage());
            return false;
        }
    }
    /* PROCESS_PASO_RUN */
    public function addProcessStepRunNext()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo     = Input::get('codigo');
                $id_pedido  = Input::get("id_pedido");
                $detalle    = Input::get("detalle");
                $clientIP = \Request::ip();
                $data = [
                    "codigo"    => $codigo,
                    "id_pedido" => $id_pedido,
                    "id_persona" => $id_user,
                    "id_entidad" => $id_entidad,
                    "detalle"   => $detalle,
                    "ip"        => $clientIP
                ];
                $result = PurchasesData::spProcessStepRunNext($data);
                if ($result["error"] == 0) {
                    if (Input::get("opedido")) {
                        $opedido = Input::get("opedido");
                        // $id_areagasto = $opedido["id_areagasto"];
                        $data   = ["id_areagasto" => $opedido["id_areagasto"]];
                        $result2 = PurchasesData::updateOrdersRegisters($data, $id_pedido);
                    }
                    //Genero el nro de pedido
                    $pedido_numero = OrdersData::showOrdersNumber($id_pedido);
                    if (count($pedido_numero) > 0) {
                        OrdersData::updateOrdersNumber($id_pedido);
                    }

                    $jResponse['success'] = true;
                    $jResponse['message'] = "The order continues with successful.";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result["message"];
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
    public function saveProcessStepRunByOrders($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = Input::get('codigo');
                $dataP = PurchasesData::showProcessByCodigo($codigo, $id_entidad);
                if (!$dataP) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item process don`t exits.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end_function;
                }
                $dataProcessRun = PurchasesData::showProcessRunByProcesoOperacion($dataP->id_proceso, $id_pedido);
                if (!$dataProcessRun) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item process run don`t exits.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end_function;
                }
                $dataProcessPasoRun = PurchasesData::showProcessPasoRunByRegistroPasoEstado($dataProcessRun->id_registro, $dataProcessRun->id_paso_actual, "0");
                if (!$dataProcessRun) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item step don`t exits.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end_function;
                }
                $data = ["revisado" => "1"];
                $result = PurchasesData::updateProcessPasoRun($data, $dataProcessPasoRun->id_detalle);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The reviewed step successful.";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end_function:
        return response()->json($jResponse, $code);
    }
    private function privateAddProcessStepRunNext($codigo, $id_pedido, $id_persona, $id_entidad, $detalle)/* BORRAR A FUTURO */
    {
        try {
            $dataP              = PurchasesData::showProcessByCodigo($codigo, $id_entidad);
            $dataProcessRun     = PurchasesData::showProcessRunByProcesoOperacion($dataP->id_proceso, $id_pedido);
            $dataProcessPasoRun = PurchasesData::showProcessPasoRunByRegistroPasoEstado($dataProcessRun->id_registro, $dataProcessRun->id_paso_actual, "0"); // dd($dataProcessPasoRun);
            // dd($dataProcessPasoRun);
            // $pedido = PurchasesData::showOrdersRegisters($id_entidad,$id_pedido);
            $exist              = PurchasesData::existsProcessPasoRunByLlave($dataProcessRun->id_registro, 'FPP3'); // $llave
            // $exist2 = PurchasesData::existsProcessPasoRunByLlave($dataProcessRun->id_registro,'FAT'); // $llave
            // dd($exist);
            // if($pedido->llave == '')
            if ($exist && $dataProcessRun->llave == 'FAT') {
                // return 1;
                $resultPF     = PurchasesData::showProcessFlujoByLlave($dataP->id_proceso, 'FP');
                // dd($result);
                $id_paso      = $resultPF->id_paso;
                $id_paso_next = $resultPF->id_paso_next;
            } else {
                $dataProcessFlujo = PurchasesData::showProcessFlujoByProcesoPaso($dataP->id_proceso, $dataProcessPasoRun->id_paso_next); // dd($dataProcessFlujo);
                $id_paso          = $dataProcessFlujo->id_paso;
                $id_paso_next     = $dataProcessFlujo->id_paso_next;
            }
            $clientIP = \Request::ip();
            $data     = array(
                // "id_detalle"    => $id_detalle,
                "id_registro"   => $dataProcessRun->id_registro,
                "id_paso"       => $id_paso, // $dataProcessFlujo->id_paso,
                // "id_persona"    => $id_persona,
                // "fecha"         => DB::raw('sysdate'),
                "detalle"       => $detalle,  // NOMBRE DEL STEP
                // "numero"        => xxx, ???. dejarlo
                "revisado"      => "0", // AL REVISAR EL PASO SE DEBE CAMBIAR A '1'. que es cuando esta revisado...
                // "ip"            => $clientIP,
                "estado"        => "0",
                "id_paso_next"  => $id_paso_next // $dataProcessFlujo->id_paso_next
            );
            // $result     = $this->privateAddProcessPasoRun($data);
            $result     = EliseoProcessPasoRun::create($data);

            $dataPR     = ["id_paso_actual" => $id_paso]; // ["id_paso_actual"=>$dataProcessFlujo->id_paso];
            $resultPR   = PurchasesData::updateProcessRun($dataPR, $dataProcessRun->id_registro);
            $dataEstado = [
                "estado"    => "1",
                "id_persona" => $id_persona,
                "fecha"     => DB::raw('sysdate'),
                "ip"        => $clientIP
            ];
            $resultPPR  = PurchasesData::updateProcessPasoRun($dataEstado, $dataProcessPasoRun->id_detalle);
            return $result;
        } catch (Exception $e) {
            // echo $e->getMessage();
            return false;
        }
    }
    private function privateAddProcessPasoRun($data)
    {
        try {
            // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
            // $data = array_merge(array("id_detalle" => $id_detalle), $data);
            // $success = PurchasesData::addProcessPasoRun($data);
            // if ($success)
            //     return $data;
            // else
            //     return false;
        } catch (Exception $e) {
            // echo $e->getMessage();
            return false;
        }
    }
    private function privateAddsProcessRunsAndSteps($id_pedido, $pasos, $id_persona, $id_entidad, $codigo)
    {
        try {
            // $id_proceso = $this->id_proceso;
            $dataP = PurchasesData::showProcessByCodigo($codigo, $id_entidad);
            $id_proceso = $dataP->id_proceso;
            $dataProcessRun = [
                "id_proceso" => $dataP->id_proceso, // $id_proceso,
                "id_operacion" => $id_pedido,
                "fecha" => DB::raw('sysdate'),
                "detalle" => "order",
                "estado" => "0"
            ];
            //INICIA EL PROCESO RUN VALIDA SI YA ESTA REGISTRADO
            /*$p_run = PurchasesData::showProcessRun($id_proceso,$id_pedido);
            if($p_run){
                $result["id_registro"] = $p_run->id_registro;
            }else{
                $result = $this->privateAddProcessRun($dataProcessRun);
            }*/
            $result = EliseoProcessRun::create($dataProcessRun);
            // $result = $this->privateAddProcessRun($dataProcessRun);
            if ($result) {
                $clientIP = \Request::ip();
                $cant = count($pasos);
                $i = 0;
                foreach ($pasos as $key => $value) {
                    // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                    $i++;
                    if ($i < $cant) {
                        $dataProcessPasoRun = array(
                            // "id_detalle"    => $id_detalle,
                            "id_registro"   => $result->id_registro,
                            "id_paso"       => $value["id_paso"],
                            "id_persona"    => $id_persona,
                            "fecha"         => DB::raw('sysdate'),
                            // "detalle"       => xxx,  NOMBRE DEL STEP
                            // "numero"        => xxx, ???. dejarlo
                            "revisado"      => "0", // AL REVISAR EL PASO SE DEBE CAMBIAR A '1'. que es cuando esta revisado...
                            "ip"            => $clientIP,
                            "estado"        => "1",
                            "id_paso_next"  => $value["id_paso_next"]
                        );
                        // $estado = "1";
                    } else {
                        $dataProcessPasoRun = array(
                            // "id_detalle"    => $id_detalle,
                            "id_registro"   => $result->id_registro,
                            "id_paso"       => $value["id_paso"],
                            // "id_persona"    => $id_persona,
                            // "fecha"         => DB::raw('sysdate'),
                            // "detalle"       => xxx,  NOMBRE DEL STEP
                            // "numero"        => xxx, ???. dejarlo
                            "revisado"      => "0", // AL REVISAR EL PASO SE DEBE CAMBIAR A '1'. que es cuando esta revisado...
                            // "ip"            => $clientIP,
                            "estado"        => "0",
                            "id_paso_next"  => $value["id_paso_next"]
                        );
                        // $estado = "0";
                    }
                    $id_paso_actual = $value["id_paso"];
                    $result2 = EliseoProcessPasoRun::create($dataProcessPasoRun);

                    // $result2 = $this->privateAddProcessPasoRun($dataProcessPasoRun);
                }
                $data = ["id_paso_actual" => $id_paso_actual];
                $result3 = PurchasesData::updateProcessRun($data, $result->id_registro);
                // updateProcessRun($data,$id_registro)
                return $result;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    /* EXTRA */
    public function showAllDetails() /* PENDIENTE-2019-03-14 10-24 */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                // PurchasesData::deletePedidoFile($id_pfile);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = []; // ['id_pfile'=>$result["id_pfile"], 'nerror'=>$result["nerror"], 'msgerror'=>$result["msgerror"]];
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
    public function listTypesPay()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listTypesPay();
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

    public function listExchangeRates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listExchangeRates();
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

    public function listPendingVouchers()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listPendingVouchers_tipCompra();
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

    public function listTypesReceipts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listTypesReceipts();
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
    public function lisTypesCurrency()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::lisTypesCurrency();
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
    public function lisBanksAccountBox()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::lisBanksAccountBox();
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
    public function listFunds()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listFunds();
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
    public function listPurchasesParents()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_proveedor = Input::get('id_proveedor');
                $data = PurchasesData::listPurchase_x($id_proveedor);
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
    /* CANDY-CANDY */
    public function vacio()
    {    // borrar¿?¿¿ -- BORRAR-SI
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                $envio_acuerdo = $this->request->input('envio_acuerdo');
                $acuerdo = $this->request->input('acuerdo');
                if ($envio_acuerdo == 'SI') {
                    $data = ['acuerdo' => $acuerdo];
                    PurchasesData::updatePedidoRegistro($data, $id_pedido);
                    $detalle = "ACUERDO";
                } else {
                    $detalle = "ACUERDO NO NECESARIO";
                }
                $data = PurchasesData::addprocNextPasoRun(
                    '6',
                    $id_pedido,
                    $id_user,
                    $detalle,
                    0,
                    '',
                    'localhost',
                    '1'
                );

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = ['id_detalle' => $data["id_detalle"], 'nerror' => $data["nerror"], 'msgerror' => $data["msgerror"]];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /* PASOS */
    public function addApproved()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                // $id_tipogasto = $this->request->input('id_tipogasto');
                $id_tipopago = $this->request->input('id_tipopago');
                $id_tipotransaccion = $this->request->input('id_tipotransaccion');
                $observacion = $this->request->input('observacion'); // donde guardar
                // REGLA
                if ($this->request->input('id_pedido')) {
                    $id_pedido = $this->request->input('id_pedido');
                    if ($id_pedido != '') {
                        // ... do some action
                    } // "id_pedido": "20180108",
                    else {
                        // echo "VACIO";
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Item pedido void.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item pedido don`t exits.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                    // echo
                }
                // id_tipopago
                if ($this->request->input('id_tipopago')) {
                    $id_tipopago = $this->request->input('id_tipopago');
                    if ($id_tipopago != '') {
                        // ... do some action
                    } // "id_pedido": "20180108",
                    else {
                        // echo "VACIO";
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Item tipo pago void.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item tipo pago don`t exits.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                    // echo
                }
                //
                if ($this->request->input('id_tipotransaccion')) {
                    $id_tipotransaccion = $this->request->input('id_tipotransaccion');
                    if ($id_tipotransaccion != '') {
                        // ... do some action
                    } // "id_pedido": "20180108",
                    else {
                        // echo "VACIO";
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Item tipo transaccion void.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Item tipo transaccion don`t exits.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                    // echo
                }

                // pedido file
                if ($this->request->input('id_pfile')) {
                    $id_pfile = $this->request->input('id_pfile');
                    $datapf = array("seleccionado" => "S");
                    PurchasesData::updatePedidoFile($datapf, $id_pfile);
                    /* if ($id_tipotransaccion != '')
                    {
                        // ... do some action
                    } // "id_pedido": "20180108",
                    else
                    {
                        // echo "VACIO";
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Item tipo transaccion void.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    } */
                }
                // pedido file end
                // FIN REGLA...
                // // "id_tipogasto"=>$id_tipogasto, // -- nunca existio
                $dataPedidoRegistro = array(
                    "id_tipopago" => $id_tipopago,
                    "id_tipotransaccion" => $id_tipotransaccion
                );
                PurchasesData::updatePedidoRegistro($dataPedidoRegistro, $id_pedido);
                //
                $objProcessRun = PurchasesData::showProcessRun('6', $id_pedido);
                //
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun, $objProcessPasoRun->id_detalle);
                //
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 84,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En tesoreria",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 68
                );
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun);
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                //
                $dataProcessRun = array("id_paso_actual" => 84);
                PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:

        return response()->json($jResponse, $code);
    }
    public function addApprovedConsejo()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                $id_pfile = $this->request->input('id_pfile'); // FALTA implemntar con update PEDIDO_FILE..cheka abajo
                //
                $objProcessRun = PurchasesData::showProcessRun('6', $id_pedido);
                // echo $id_pedido.'xzczxc';
                // print_r($objProcessRun);
                //
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun, $objProcessPasoRun->id_detalle);
                //
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun2 = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 84,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En tesoreria",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 68
                );
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun2);
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun2);

                //
                $dataProcessRun = array("id_paso_actual" => 84);
                PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);
                // FALTA UPDATA PEDIDO_FILE SELECIONADO... UPDATE CAMPO[seleccionado]='1'
                // campo ya hice
                if ($this->request->input('id_pfile')) {
                    $id_pfile = $this->request->input('id_pfile');
                    $datapf = array("seleccionado" => "S");
                    PurchasesData::updatePedidoFile($datapf, $id_pfile);
                    /* if ($id_tipotransaccion != '')
                    {
                        // ... do some action
                    } // "id_pedido": "20180108",
                    else
                    {
                        // echo "VACIO";
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Item tipo transaccion void.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    } */
                }
                // campo ya hice... end

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = []; // ['id_detalle'=>$data["id_detalle"], 'nerror'=>$data["nerror"], 'msgerror'=>$data["msgerror"]];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function endPurchases()/* NEW-2019-05-07 */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $count = PurchasesData::countPendingPedidoCompra($id_pedido);
                if ($count) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Provisiones pendientes.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataPedido = PurchasesData::showPedidoRegistro($id_pedido);
                if ($dataPedido->estado == "1") {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Operacion ya esta finalizada.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataCambiaEstado = ["estado" => "1"];
                PurchasesData::updatePedidoRegistro($dataCambiaEstado, $id_pedido);
                $objProcessRun = PurchasesData::showProcessRun(6, $id_pedido);
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun_update = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun_update, $objProcessPasoRun->id_detalle);
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 65,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Fin",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1"
                );
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);

                $dataAlfa = array("estado" => "1", "id_paso_actual" => "65");
                PurchasesData::updateProcessRun($dataAlfa, $objProcessRun->id_registro);
                $jResponse['success'] = true;
                $jResponse['message'] = "Finalizo el pedido.";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function addFinalizer($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $count = PurchasesData::countPendingPedidoCompra($id_pedido);
                if ($count) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Provisiones pendientes.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataPedido = PurchasesData::showPedidoRegistro($id_pedido);
                if ($dataPedido->estado == "1") {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Operacion ya esta finalizada.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataCambiaEstado = ["estado" => "1"];
                PurchasesData::updatePedidoRegistro($dataCambiaEstado, $id_pedido);
                $objProcessRun = PurchasesData::showProcessRun(6, $id_pedido);
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun_update = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun_update, $objProcessPasoRun->id_detalle);
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 65,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Fin",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1"
                );
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                $dataAlfa = array("estado" => "1", "id_paso_actual" => "65");
                PurchasesData::updateProcessRun($dataAlfa, $objProcessRun->id_registro);
                $jResponse['success'] = true;
                $jResponse['message'] = "Finalizo el pedido.";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function addRefused()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                $observacion = $this->request->input('observacion');

                $objProcessRun = PurchasesData::showProcessRun('6', $id_pedido);
                $dataProcessRun = array("estado" => "1");
                PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);
                $dataPedido = array("estado" => "1");
                PurchasesData::updatePedidoRegistro($dataPedido, $id_pedido);
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun, $objProcessPasoRun->id_detalle);
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun2 = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 65,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Rechazado",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1"
                );
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun2);
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun2);

                $jResponse['success'] = true;
                $jResponse['message'] = "Refused successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addStepB() /* BORRAR */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $params = json_decode(file_get_contents("php://input"));
                $id_tipopedido = $this->request->input('id_tipopedido'); // OK
                $motivo = $this->request->input('motivo'); // OK
                $id_deptoorigen = $this->request->input('id_deptoorigen'); // OK
                // $id_evento = $this->request->input('id_evento');// $id_evento
                // $result = PurchasesData::addStepB($id_entidad,$id_depto,$id_tipopedido,$motivo,$id_user,$id_deptoorigen,$id_evento);
                //
                // $id_pedido = PurchasesData::getMax('pedido_registro', 'id_pedido') + 1;
                $dataPedido = array(
                    // "id_pedido" => $id_pedido,
                    "id_entidad" => $id_entidad,
                    "id_depto" => $id_depto,
                    "id_tipopedido" => $id_tipopedido,
                    // "id_tipogasto"=>$xxx,
                    "id_deptoorigen" => $id_deptoorigen,
                    // "id_deptodestino"=>$xxx,
                    // "id_evento"=>$id_evento,
                    "fecha" => DB::raw('sysdate'),
                    "fecha_pedido" => DB::raw('sysdate'),
                    "motivo" => $motivo,
                    "estado" => "0",
                    "id_persona" => $id_user
                );
                $objPedido = EliseoPedidoRegistro::create($dataPedido);
                // $objPedido = PurchasesData::addPedidoRegistro($dataPedido);
                // Process run
                // $id_registro = PurchasesData::getMax('process_run', 'id_registro') + 1;
                $dataProcessRun = array(
                    // "id_registro" => $id_registro,
                    "id_proceso" => 6,
                    "id_operacion" => $objPedido->id_pedido,
                    "detalle" => "Iniciado",
                    "estado" => "0",
                    "id_paso_actual" => 82
                );
                $objProcessRun = EliseoProcessRun::create($dataProcessRun);
                // $objProcessRun = PurchasesData::addProcessRun($dataProcessRun);
                //
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 69,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Iniciado",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1",
                    "id_paso_next" => 67
                );
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                //
                // $id_detalle2 = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun2 = array(
                    // "id_detalle" => $id_detalle2,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 67,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Iniciado",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1",
                    "id_paso_next" => 82
                );
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun2);
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun2);

                // $id_detalle3 = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun3 = array(
                    // "id_detalle" => $id_detalle3,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 82,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En proceso",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 83
                );
                // $objProcessPasoRun3 = PurchasesData::addProcessPasoRun($dataProcessPasoRun3);
                $objProcessPasoRun3 = EliseoProcessPasoRun::create($dataProcessPasoRun3);

                //
                $detalles = $this->request->input('detalles');
                foreach ($detalles as $key => $value) {
                    // $resultDetalle = PurchasesData::addPedidoDetalle($result["id_pedido"], 0, 0, $value["detalle"], $value["cantidad"], 0, 0);
                    // $id_detalle_pd = PurchasesData::getMax('pedido_detalle', 'id_detalle') + 1;
                    $dataPedidoDetalle = array(
                        // "id_detalle" => $id_detalle_pd,
                        "id_pedido" => $objPedido->id_pedido,
                        // "id_almacen"=>xxx,
                        // "id_articulo"=>xxx,
                        "detalle" => $value["detalle"],
                        "cantidad" => $value["cantidad"]
                        // "precio"=>xx,
                        // "importe"=>xx
                    );
                    $objtDetalle = EliseoPedidoDetalle::create($dataPedidoDetalle);
                    // $objtDetalle = PurchasesData::addPedidoDetalle($dataPedidoDetalle);
                    //
                    $objPedido["detalles"][] = $objtDetalle;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = ["pedido" => $objPedido];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addStepE()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                $tiene_acuerdo = $this->request->input('tiene_acuerdo');
                $es_necesario = $this->request->input('es_necesario');

                if ($tiene_acuerdo == 'SI') {
                    // falta update pedido -- acuerdo -- YA ESTA -- OK
                    $acuerdo = $this->request->input('acuerdo');
                    $dataPedidoRegistro = array('acuerdo' => $acuerdo);
                    PurchasesData::updatePedidoRegistro($dataPedidoRegistro, $id_pedido);
                    $detalle = "En ejecucion";
                    $id_paso = 68;
                    $id_paso_next = 74;
                } else if ($tiene_acuerdo == 'NO' && $es_necesario == 'NO') {
                    $detalle = "En ejecucion";
                    $id_paso = 68;
                    $id_paso_next = 74;
                } else if ($tiene_acuerdo == 'NO' && $es_necesario == 'SI') {
                    $detalle = "En consejo";
                    $id_paso = 85;
                    $id_paso_next = 84;
                }

                $objProcessRun = PurchasesData::showProcessRun('6', $id_pedido);

                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);
                $dataProcessPasoRun = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun, $objProcessPasoRun->id_detalle);

                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun2 = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => $id_paso,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => $detalle,
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => $id_paso_next
                );
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun2);
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun2);

                $dataProcessRun = array("id_paso_actual" => $id_paso);
                PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);

                if ($id_paso == 68) {
                    $result = $this->privateSendEmailPedidoAprobado($id_pedido, $id_entidad);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Next step successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addStepPreProvision()   /* BORRAR */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');
                $id_moneda = $this->request->input('id_moneda');
                $id_proveedor = $this->request->input('id_proveedor');
                $importe = $this->request->input('importe');
                // VALIDA PROVEEDOR
                $dataProveedor = PurchasesData::showLegalPersonVW($id_proveedor);
                if ($dataProveedor == NULL) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Proveedor no encontrado.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $cliente = new sunat(true, true);
                $dataSunat = $cliente->search($dataProveedor->id_ruc, true);
                $isSunat = $dataSunat["success"];
                if ($isSunat != true) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No validado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                if ($dataSunat["Condicion"] != "HABIDO" || $dataSunat["Estado"] != "ACTIVO") {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Proveedor deshabilitado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                // VALIDA PROVEEDOR --
                // VALIDA FILE
                $file = $this->request->file("archivo_jpg");
                if ($file == null) {
                    // echo "NULO";
                    // return false;
                    $jResponse['success'] = false;
                    $jResponse['message'] = "File JPG, debe subirse.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                // VALIDA FILE --
                // $id_pcompra = PurchasesData::getMax('pedido_compra', 'id_pcompra') + 1;
                $dataPcompra = array(
                    // "id_pcompra" => $id_pcompra,
                    "id_pedido" => $id_pedido,
                    // "id_compra"=>$id_compra,
                    "id_moneda" => $id_moneda,
                    "id_proveedor" => $id_proveedor,
                    "importe" => $importe,
                    "estado" => "0",
                    "fecha" => DB::raw('sysdate')
                );
                $objPcompra = EliseoPedidoCompra::create($dataPcompra);
                // $objPcompra = PurchasesData::addPedidoCompra($dataPcompra);

                $objPedidoFile_jpg = $this->privateUploadPedidoFile($id_pedido, $objPcompra->id_pcompra, "archivo_jpg", "1");
                $objPedidoFile_pdf = $this->privateUploadPedidoFile($id_pedido, $objPcompra->id_pcompra, "archivo_pdf", "1");
                $objPedidoFile_xml = $this->privateUploadPedidoFile($id_pedido, $objPcompra->id_pcompra, "archivo_xml", "1");

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = ['pcompra' => $objPcompra];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function closePreprovision()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $this->request->input('id_pedido');

                $objProcessRun = PurchasesData::showProcessRun(6, $id_pedido);

                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);

                $dataProcessPasoRun_update = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun_update, $objProcessPasoRun->id_detalle);

                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 74,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En tesoreria",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 65
                );
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);

                //
                $dataProcessRun = array("id_paso_actual" => 74);
                $objProcessRun2 = PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);

                $jResponse['success'] = true;
                $jResponse['message'] = "Next step successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function runReceiptStepA() /* addStepA *//* BORRAR */
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $collection = array(
                    array(
                        "function"      => "receiptStepAValidation",
                        "data"          => false,
                        "data_name"          => "receipt_step_a",
                        "type_function" => "validation"
                    ),
                    array(
                        "function"      => "typeRequestExist",
                        "data"          => Input::get('id_tipopedido'),
                        "type_function" => "exist",
                        "name"          => "Tipo pedido"
                    ),
                    array(
                        "function"      => "deptoExist",
                        "data"          => Input::get('id_deptoorigen'),
                        "type_function" => "exist",
                        "name"          => "Departamento"
                    )
                );
                $resultValida = PurchasesValidation::collectionValidation($collection);
                if ($resultValida->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultValida->message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto endFunction;
                }
                $dataRSA = $resultValida->data["receipt_step_a"];
                // CODIGO-FOCUS-INI
                /* $id_pedido      = PurchasesData::getMax('pedido_registro','id_pedido')+1;
                $dataPedido_sub     = array(
                    "id_pedido"     =>$id_pedido,
                    "id_entidad"    =>$id_entidad,
                    "id_depto"      =>$id_depto,
                    // -- "id_tipopedido" =>$id_tipopedido,
                    // "id_tipogasto"=>$xxx,
                    // -- "id_deptoorigen"=>$id_deptoorigen,
                    // "id_deptodestino"=>$xxx,
                    // "id_evento"=>$xxx,
                    "fecha"         =>DB::raw('sysdate'),
                    "fecha_pedido"  =>DB::raw('sysdate'),
                    // -- "motivo"        =>$motivo,
                    "estado"        =>"0",
                    "id_persona"    =>$id_user
                    );
                $dataPedido = array_merge($dataPedido_sub, $dataRSA);
                $objPedido = PurchasesData::addPedidoRegistro($dataPedido); */
                // CODIGO-FOCUS-FIN
                $dataPedido = array_merge(
                    array(
                        // "id_pedido"     =>$id_pedido,
                        "id_entidad"    => $id_entidad,
                        "id_depto"      => $id_depto,
                        "fecha"         => DB::raw('sysdate'),
                        "fecha_pedido"  => DB::raw('sysdate'),
                        "estado"        => "0",
                        "id_persona"    => $id_user
                    ),
                    $dataRSA
                );
                $resultPedido = EliseoPedidoRegistro::create($dataPedido);
                // $resultPedido = $this->privateAddRequestRegistry($dataPedido);
                // if (!$resultPedido->success) {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $resultPedido->message;
                //     $jResponse['data']    = [];
                //     $code                 = "202";
                //     goto endFunction;
                // }
                // Process run
                // $id_registro = PurchasesData::getMax('process_run', 'id_registro') + 1;
                $dataProcessRun = array(
                    // "id_registro" => $id_registro,
                    "id_proceso" => 6,
                    // "id_operacion"=>$id_pedido,
                    "id_operacion" => $resultPedido->id_pedido,
                    "detalle" => "Iniciado",
                    "estado" => "0",
                    "id_paso_actual" => 68
                );
                // $objProcessRun = PurchasesData::addProcessRun($dataProcessRun);
                $objProcessRun = EliseoProcessRun::create($dataProcessRun);

                //
                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 69,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Iniciado",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1",
                    "id_paso_next" => 89
                );
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);

                //
                // $id_detalle2 = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun2 = array(
                    // "id_detalle" => $id_detalle2,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 89,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "Iniciado",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "1",
                    "id_paso_next" => 68
                );
                $objProcessPasoRun2 = EliseoProcessPasoRun::create($dataProcessPasoRun2);
                // $objProcessPasoRun2 = PurchasesData::addProcessPasoRun($dataProcessPasoRun2);
                //
                // $id_detalle3 = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun3 = array(
                    // "id_detalle" => $id_detalle3,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 68,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En proceso",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 74
                );
                // $objProcessPasoRun3 = PurchasesData::addProcessPasoRun($dataProcessPasoRun3);
                $objProcessPasoRun3 = EliseoProcessPasoRun::create($dataProcessPasoRun3);
                //
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = []; // ['pedido'=>$resultPedido->data];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        endFunction:
        return response()->json($jResponse, $code);
    }
    /* PEDIDO_FILE */
    public function addPedidoFile() /* public function addStepC($id_pedido){ */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = $this->request->input('id_pedido');
                $tipo_file = $this->request->input('tipo_file');
                if ($tipo_file = null or $tipo_file = "") {
                    $tipo_file = "1";
                }
                $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, "archivo", $tipo_file);
                if ($objPedidoFile["success"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not created';
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
    public function deletePedidoFile($id_pfile)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deletePedidoFile($id_pfile);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function putPedidoFile($id_pedido) /* ES CAMBIO PASO */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $objProcessRun = PurchasesData::showProcessRun(6, $id_pedido);
                $objProcessPasoRun = PurchasesData::showProcessPasoRun($objProcessRun->id_registro, $objProcessRun->id_paso_actual);

                $dataProcessPasoRun_update = array("estado" => "1");
                PurchasesData::updateProcessPasoRun($dataProcessPasoRun_update, $objProcessPasoRun->id_detalle);

                // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                $dataProcessPasoRun = array(
                    // "id_detalle" => $id_detalle,
                    "id_registro" => $objProcessRun->id_registro,
                    "id_paso" => 83,
                    "id_persona" => $id_user,
                    "fecha" => DB::raw('sysdate'),
                    "detalle" => "En tesoreria",
                    "numero" => 0,
                    "revisado" => "",
                    "ip" => "localhost",
                    "estado" => "0",
                    "id_paso_next" => 84
                );
                // $objProcessPasoRun = PurchasesData::addProcessPasoRun($dataProcessPasoRun);
                $objProcessPasoRun = EliseoProcessPasoRun::create($dataProcessPasoRun);

                $dataProcessRun = array("id_paso_actual" => 83);
                $objProcessRun2 = PurchasesData::updateProcessRun($dataProcessRun, $objProcessRun->id_registro);

                $jResponse['success'] = true;
                $jResponse['message'] = "Next step successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /* COMPRA */
    public function showPurchases($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchases($id_entidad, $id_compra);
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
    public function addPurchases()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                /* $validAnhoMes = PurchasesValidation::validationAnhoMes($id_entidad);
                if($validAnhoMes->invalid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validAnhoMes->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                } */


                $validComprobante = PurchasesValidation::validationCall("validationComprobante" . Input::get('id_comprobante'));
                if ($validComprobante->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validComprobante->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }

                $dataUtil = PurchasesUtil::utilCall("dataComprobante" . Input::get('id_comprobante'));
                if ($dataUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }

                $serie = Input::get("serie");
                $numero = Input::get("numero");
                $id_igv = Input::get("id_igv");
                $id_comprobante = Input::get("id_comprobante");
                $id_proveedor = Input::get("id_proveedor");
                $exist = PurchasesData::showExistsProviderDocumentProv($serie, $numero, $id_comprobante, $id_proveedor);
                if ($exist) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ERROR: El Documento ya esta PROVISIONADO";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataProveedor = PurchasesData::showLegalPersonVW($id_proveedor);
                // $cliente = new sunat(true,true);
                /*$dataSunat = $cliente->search($dataProveedor->id_ruc,true); // Marlo comentado, por que ya verifica en el front atravez de otro servicio
                $isSunat = $dataSunat["success"];
                if($isSunat != true)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No validado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                     goto end;
                }
                if($dataSunat["Condicion"] != "HABIDO" || $dataSunat["Estado"] != "ACTIVO")
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Proveedor deshabilitado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }*/
                $id_pedido = Input::get("id_pedido");
                $dataPedido = PurchasesData::showPedidoRegistro($id_pedido);
                /* if($dataPedido)
                {
                    if($dataPedido->id_voucher == NULL)
                    {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Voucher no asignado.";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                    $id_voucher = $dataPedido->id_voucher;
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Pedido perdido.";
                    $jResponse['data'] = [];
                    $code = "202";
                     goto end;
                } */


                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', $tiene_params, $params);
                if ($rpta["nerror"] == 0) {
                    $id_anho = $rpta["id_anho"];
                    $id_mes = $rpta["id_mes"];
                }
                // VALIDAD VOUCHER SI ES AUTOMATICO O MANUAL
                // Voucher de Compras
                $id_tipovoucher = 2;

                $rpta = PurchasesData::validatedVoucher($id_entidad, $id_depto, $id_anho, $id_tipovoucher, $id_user);
                if ($rpta['error'] == 0) {
                    $datax = $dataUtil->data;
                    // Ahora se sacará de una secuencia
                    // $id_compra = PurchasesData::getMax('compra', 'id_compra') + 1;
                    $dataCompra2 = [];
                    // $dataCompra2["id_voucher"] = $id_voucher;
                    // $dataCompra2["id_compra"] = $id_compra;
                    $dataCompra2["id_entidad"] = $id_entidad;
                    $dataCompra2["id_depto"] = $id_depto;
                    $dataCompra2["id_persona"] = $id_user;
                    $dataCompra2["estado"] = "0";
                    /* $dataFechaActiva = PurchasesUtil::dataAnhoMesActivo($id_entidad);
                    $dataCompra2["id_anho"] = $dataFechaActiva->data["id_anho"];
                    $dataCompra2["id_mes"] = $dataFechaActiva->data["id_mes"]; */
                    // dd($dataCompra2);
                    $data = PurchasesData::showVoucherAutomatico($id_entidad, $id_depto, $id_anho, $id_tipovoucher);
                    foreach ($data as $key => $item) {
                        $voucher_automatico = $item->automatico;
                    }
                    if ($voucher_automatico == "N") { //Voucher manual
                        $id_voucher = $rpta['id_voucher'];
                        $dataCompra2["id_voucher"] = $id_voucher;
                        $data_voucher = AccountingData::showVoucher($id_voucher);
                        foreach ($data_voucher as $key => $item) {
                            $id_mes = $item->id_mes;
                        }
                    }
                    $dataCompra2["id_anho"] = $id_anho;
                    $dataCompra2["id_mes"] = $id_mes;
                    $dataCompra = array_merge($dataCompra2, $datax);
                    $legalPerson = LegalPersonData::updateDataById($this->request->ruc, [
                        'es_buen_contribuyente' => $this->request->es_buen_contribuyente,
                        'es_agente_retencion' => $this->request->es_agente_retencion,
                    ]);
                    $dataCompra['fecha_doc'] = $dataCompra['fecha_doc']->format('Y-m-d H:i:s');
                    $dataCompra['id_igv'] = $id_igv;
                    // dd($dataCompra);
                    $objCompra = EliseoCompra::create($dataCompra);
                    // $objCompra = PurchasesData::addCompra($dataCompra);
                    // dd($objCompra);
                    $dataPcompra = array('id_compra' => $objCompra->id_compra);
                    $id_pcompra = Input::get("id_pcompra");
                    $objPcompra = PurchasesData::updatePedidoCompra($dataPcompra, $id_pcompra);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = $objCompra;
                    $code = "200";

                    /*$jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";*/
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage() . '::Line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function addPurchasesXml()
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
                $id_pcompra = Input::get("id_pcompra");
                $tipo = "1";
                $formato = "XML";
                $pedidoFile = PurchasesData::showOrdersFilesByParams($id_pcompra, $tipo, $formato);
                if ($pedidoFile) {
                    $data = PurchasesUtil::getComprobanteFromXML($pedidoFile->nombre, $pedidoFile->url);
                    if ($data) {
                        $dataProveedor = PurchasesData::showVWPersonaJuridicaByRuc($data["extra"]["proveedor_numero"]);
                        if ($dataProveedor) {
                            $dataCompra = $data["cabecera"];
                            if ($dataCompra["id_comprobante"] == "07" || $dataCompra["id_comprobante"] == "08") {
                                $array_serie_numero = explode("-", $data["extra"]["documento_referencia_id"]);
                                $serie = $array_serie_numero[0];
                                $numero = $array_serie_numero[1];
                                $id_comprobante = $data["extra"]["documento_referencia_code"];
                                $id_proveedor = $dataProveedor->id_persona;
                                $exists = PurchasesData::showExistsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, "");
                                if (!$exists) {
                                    $message = "No existe Documento.";
                                    goto seguir_vacio;
                                }
                                $dataCompra["id_parent"] = $exists->id_compra;
                            }
                            $dataCompra["id_proveedor"] = $dataProveedor->id_persona;
                            $dataCompra["id_entidad"] = $id_entidad;
                            $dataCompra["id_depto"] = $id_depto;
                            list($id_anho, $id_mes) = PurchasesUtil::dataAnhoMesActivo2($id_entidad);
                            $dataCompra["id_anho"] = $id_anho;
                            $dataCompra["id_mes"] = $id_mes;
                            $dataCompra["id_persona"] = $id_user;
                            $dataCompra["base"] = DB::raw('(' . $dataCompra["importe"] . '-' . $dataCompra["igv"] . ')');;
                            $dataCompra["fecha_provision"] = DB::raw('sysdate');
                            $resultCompra = $this->privateAddPurchases($dataCompra);
                            if ($resultCompra) {
                                $id_compra = $resultCompra["id_compra"];
                                foreach ($data["detalles"] as $dataDetalle) {
                                    $dataDetalle["id_compra"] = $id_compra;
                                    $dataDetalle["estado"] = "1";
                                    $resultDet = $this->privateAddPurchasesDetails($dataDetalle);
                                    $listCompraDetalle[] = $resultDet;
                                }
                                PurchasesData::updateOrdersPurchases(['id_compra' => $id_compra], $id_pcompra);
                                $jResponse['success'] = true;
                                $jResponse['message'] = "OK";
                                $jResponse['data'] = ['id_compra' => $id_compra];
                                $code = "200";
                                goto end;
                            } else {
                                $message = "No se registro Compra.";
                            }
                        } else {
                            $message = "No existe el proveedor.";
                        }
                    } else {
                        $message = "No formato XML incorrecto.";
                    }
                } else {
                    $message = "No existe Documento XML.";
                }
                seguir_vacio:
                $jResponse['success'] = false;
                $jResponse['message'] = $message;
                $jResponse['data'] = [];
                $code = "400";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function cancelPurchases($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = array("id_compra" => null);
                PurchasesData::updatePedidoCompra_nulls($data, $id_compra);
                PurchasesData::deleteCompraAsientoMore($id_compra);
                PurchasesData::deleteCompraDetalleMore($id_compra);
                PurchasesData::deleteCompra($id_compra);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function updatePurchases($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                /* $validAnhoMes = PurchasesValidation::validationAnhoMes($id_entidad);
                if($validAnhoMes->invalid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validAnhoMes->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                } */
                $validComprobante = PurchasesValidation::validationCall("validationComprobante" . Input::get('id_comprobante'));
                if ($validComprobante->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validComprobante->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataUtil = PurchasesUtil::utilCall("dataComprobante" . Input::get('id_comprobante'));
                if ($dataUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $serie = Input::get("serie");
                $numero = Input::get("numero");
                $id_comprobante = Input::get("id_comprobante");
                $id_proveedor = Input::get("id_proveedor");
                $exist = PurchasesData::existsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, $id_compra);
                if($exist->exists) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'El comprobante ya esta registrado: '.$exist->info;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $dataProveedor = PurchasesData::showLegalPersonVW($id_proveedor);
                $cliente = new sunat(true, true);
                $dataSunat = $cliente->search($dataProveedor->id_ruc, true);
                $isSunat = $dataSunat["success"];
                if ($isSunat != true) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No validado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                if ($dataSunat["Condicion"] != "HABIDO" || $dataSunat["Estado"] != "ACTIVO") {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Proveedor deshabilitado, Sunat.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $datax = $dataUtil->data;
                $dataCompra2 = [];
                $dataCompra2["id_entidad"] = $id_entidad;
                $dataCompra2["id_depto"] = $id_depto;
                $dataCompra2["id_persona"] = $id_user;
                $dataCompra2["estado"] = "0";
                $dataFechaActiva = PurchasesUtil::dataAnhoMesActivo($id_entidad);
                $dataCompra2["id_anho"] = $dataFechaActiva->data["id_anho"];
                $dataCompra2["id_mes"] = $dataFechaActiva->data["id_mes"];
                $dataCompra = array_merge($dataCompra2, $datax);
                $this->privateReceiptVoid($id_compra);
                PurchasesData::updateCompra($dataCompra, $id_compra);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $dataCompra;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function updatePurchasesRetDetra($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        if ($jResponse["valida"] == 'SI') {
            $jResponse = [];
            try {
                $instance = Purchase::where('id_compra', $id_compra)
                    ->update($this->request->all());
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = Purchase::findOrFail($id_compra);
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
    public function execPurchasesEnd($id_compra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $entCompra = PurchasesData::showPurchases($id_entidad, $id_compra);
                $codigo = Input::get("codigo");
                if ($entCompra) {
                    // dd($entCompra);
                    $clientIP = \Request::ip();
                    $data = [
                        "id_compra" => $id_compra,
                        "codigo" => $codigo,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => " ",
                        "ip" => $clientIP
                    ];
                    $result = PurchasesData::execPurchasesEnd($data);
                    if ($result["error"] == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "OK";
                        $jResponse['data']    = array("code" => $result["code"]);
                        $code                 = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $result["message"];
                        $jResponse['data']    = [];
                        $code                 = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
                // die("!!!");
                // /* $validAnhoMes = PurchasesValidation::validationAnhoMes($id_entidad);
                // if($validAnhoMes->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $validAnhoMes->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end;
                // } */
                // $validComprobante = PurchasesValidation::validationCall("validationComprobante".Input::get('id_comprobante'));
                // if($validComprobante->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $validComprobante->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end;
                // }
                // $dataUtil = PurchasesUtil::utilCall("dataComprobante".Input::get('id_comprobante'));
                // if($dataUtil->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $dataUtil->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end;
                // }
                // $serie = Input::get("serie");
                // $numero = Input::get("numero");
                // $id_comprobante = Input::get("id_comprobante");
                // $id_proveedor = Input::get("id_proveedor");
                // $exist = PurchasesData::existsProviderDocument($serie,$numero,$id_comprobante,$id_proveedor,'');
                // if($exist)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "Documento proveedor duplicado.";
                //     $jResponse['data'] = [];
                //     $code = "202";
                //      goto end;
                // }
                // $dataProveedor = PurchasesData::showLegalPersonVW($id_proveedor);
                // $cliente = new sunat(true,true);
                // $dataSunat = $cliente->search($dataProveedor->id_ruc,true);
                // $isSunat = $dataSunat["success"];
                // if($isSunat != true)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "No validado, Sunat.";
                //     $jResponse['data'] = [];
                //     $code = "202";
                //      goto end;
                // }
                // if($dataSunat["Condicion"] != "HABIDO" || $dataSunat["Estado"] != "ACTIVO")
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "Proveedor deshabilitado, Sunat.";
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end;
                // }
                // $id_pedido = Input::get("id_pedido");
                // $dataPedido = PurchasesData::showPedidoRegistro($id_pedido);
                // /* if($dataPedido)
                // {
                //     if($dataPedido->id_voucher == NULL)
                //     {
                //         $jResponse['success'] = false;
                //         $jResponse['message'] = "Voucher no asignado.";
                //         $jResponse['data'] = [];
                //         $code = "202";
                //         goto end;
                //     }
                //     $id_voucher = $dataPedido->id_voucher;
                // }
                // else
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "Pedido perdido.";
                //     $jResponse['data'] = [];
                //     $code = "202";
                //      goto end;
                // } */
                // $datax = $dataUtil->data;
                // $id_compra = PurchasesData::getMax('compra','id_compra')+1;
                // $dataCompra2 = [];
                // // $dataCompra2["id_voucher"] = $id_voucher;
                // $dataCompra2["id_compra"] = $id_compra;
                // $dataCompra2["id_entidad"] = $id_entidad;
                // $dataCompra2["id_depto"] = $id_depto;
                // $dataCompra2["id_persona"] = $id_user;
                // $dataCompra2["estado"] = "0";
                // /* $dataFechaActiva = PurchasesUtil::dataAnhoMesActivo($id_entidad);
                // $dataCompra2["id_anho"] = $dataFechaActiva->data["id_anho"];
                // $dataCompra2["id_mes"] = $dataFechaActiva->data["id_mes"]; */
                // // dd($dataCompra2);
                // $params = "";
                // $tiene_params = "N";
                // $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                // if($rpta["nerror"]==0)
                // {
                //     $id_anho = $rpta["id_anho"];
                //     $id_mes = $rpta["id_mes"];
                // }
                // $dataCompra2["id_anho"] = $id_anho;
                // $dataCompra2["id_mes"] = $id_mes;
                // $dataCompra = array_merge($dataCompra2, $datax);
                // $objCompra = PurchasesData::addCompra($dataCompra);
                // // dd($objCompra);
                // $dataPcompra = array('id_compra'=>$id_compra);
                // $id_pcompra = Input::get("id_pcompra");
                // $objPcompra = PurchasesData::updatePedidoCompra($dataPcompra,$id_pcompra);
                // // xxxxx
                // $jResponse['success'] = true;
                // $jResponse['message'] = "OK";
                // $jResponse['data'] = $dataCompra;
                // $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        // end:
        return response()->json($jResponse, $code);
    }
    private function privateAddPurchases($data)
    {
        try {
            $id_compra = PurchasesData::getMax('compra', 'id_compra') + 1;
            $data = array_reverse($data, true);
            $data["id_compra"] = $id_compra;
            $data = array_reverse($data, true);
            $object = PurchasesData::addPurchases($data);
            return $object;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
    /* COMPRA_DETALLE */
    public function listPurchasesDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = Input::get("id_compra");
                $datos = PurchasesData::showMyPurchases($id_compra);
                foreach ($datos as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = PurchasesData::listPurchasesDetails($id_compra, $id_anho);
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

    public function addPurchasesDetailsImport(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user   = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {

                $id_anho = 0;
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                if ($id_anho !== $id_anho_actual) {
                    throw new Exception("Alto, No existe un año activo.", 1);
                }

                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) === 0) {
                    throw new Exception("Alto, El usuario no tiene asignado un almacén.", 1);
                }
                if (count($warehouse) > 1) {
                    throw new Exception("Alto, Hay mas de un almacén asignado al usuario.", 1);
                }

                $jResponse = [];
                $id_compra = $request->id_compra;
                $import_data = \Excel::load($request->excel, function ($reader) use ($request) {
                    $reader->select(array('codigo', 'detalle', 'cantidad', 'precio', 'base', 'igv', 'importe'))->get();
                })->get();

                $import_data_filter = array_filter($import_data->toArray(), function ($row) {
                    return (!is_null($row['codigo']) && !empty($row['codigo']));
                });

                if (empty($import_data_filter && sizeOf($import_data_filter))) {
                    throw new Exception('Alto! La lista del excel esta vacía', 1);
                }

                $import_data_filter_validado = [];
                // VALIDAR
                // $id_ctipoigvInicial = null;
                // $importe_sumado_total = 0;
                foreach ($import_data_filter as $value) {
                    // $row = $value;
                    $articulos = WarehousesData::showArticleByAnhoAlmacenCodigo($id_anho, $id_almacen, $value['codigo']);
                    if (count($articulos) === 0) {
                        throw new Exception('Alto! No existe un artículo con: código ' . $value['codigo'] . '; año: ' . $id_anho . '; almacén: ' . $id_almacen . '.', 1);
                    }
                    if (count($articulos) > 1) {
                        throw new Exception('Alto! Existe más de un artículo con: código ' . $value['codigo'] . '; año: ' . $id_anho . '; almacén: ' . $id_almacen . '.', 1);
                    }
                    $row = new \stdClass();
                    $row->detalle = $value['detalle'];
                    $row->cantidad = $value['cantidad'];
                    $row->precio = $value['precio'];
                    $row->igv = $value['igv'];
                    $row->base = $value['base'];
                    $row->importe = $value['importe'];
                    $row->id_articulo = $articulos[0]->id_articulo;
                    $row->id_ctipoigv = $articulos[0]->id_ctipoigv;
                    $row->id_tipoigv = $articulos[0]->id_tipoigv;

                    // if(is_null($id_ctipoigvInicial)) {
                    //     $id_ctipoigvInicial = $articulos[0]->id_ctipoigv;
                    // } else if($id_ctipoigvInicial !== $articulos[0]->id_ctipoigv) {
                    //     throw new Exception('Alto! No se acepta diferente IGVs en una compra.', 1);
                    // }

                    if ($row->id_ctipoigv == 4 || $row->id_ctipoigv == 5 || $row->id_ctipoigv == 6) {
                        // Corrección.
                        $row->igv = 0;
                        $row->base = $value['importe'];
                    }

                    // $importe_sumado_total = $importe_sumado_total + ((float) $row->importe);
                    array_push($import_data_filter_validado, $row);
                }
                // $dcompra = PurchasesData::getCompraById($id_compra);

                // if($importe_sumado_total > $dcompra[0]->importe ){
                //     throw new Exception('Alto! El importe total del detalle de compra es mayor al del comprobante.', 1);
                // }

                // AGREGAR
                foreach ($import_data_filter_validado as $value) {
                    // CORREGIR AQUI SQ DET
                    // $newId = PurchasesData::getMax('COMPRA_DETALLE', 'ID_DETALLE') + 1;
                    // 'ID_DETALLE' => $newId,
                    $newData = [
                        'ID_COMPRA' => $id_compra,
                        'ID_CTIPOIGV' => $value->id_ctipoigv,
                        'ID_ARTICULO' => $value->id_articulo,
                        'ID_ALMACEN' => $id_almacen,

                        'ID_TIPOIGV' => $value->id_tipoigv,
                        'DETALLE' => $value->detalle,

                        'CANTIDAD' => $value->cantidad,
                        'PRECIO' => $value->precio,
                        'BASE' => $value->base,
                        'IGV' => $value->igv,
                        'IMPORTE' => $value->importe,
                        'ESTADO' => '1',
                    ];
                    // $result = PurchasesData::addPurchasesDetails($newData);
                    $result = EliseoCompraDetalle::create($newData);
                }

                $data = PurchasesData::updateTotalCompra($id_compra);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data["data"];
                $code = "200";
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                // $jResponse['message'] = $e->getMessage().'::'.$e->getLine();
                $jResponse['message'] = $e->getMessage() . '::Line: ' . $e->getLine();
                $jResponse['data'] = null;
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }


    public function addPurchasesDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesDetails();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endAddPurchasesDetails;
                }
                $data = $resultUtil->data;
                $data["estado"] = "1";
                $signo = 1;
                $id_compra = Input::get('id_compra');
                $imp_det = Input::get('importe');

                $dif = PurchasesData::showPurchaseDif($id_compra, $imp_det);
                foreach ($dif as $key => $item) {
                    $signo = $item->signo;
                }
                $es_vinculado = Input::get('es_costo_vinculado');
                if ($es_vinculado != "") {
                    $signo = 1;
                }
                if ($signo == 1 || $signo == 0) {
                    $id_ctipoigv_reg = Input::get('id_ctipoigv');
                    $det = PurchasesData::listPurchasesDetailsIGV($id_compra);
                    $tipo_igv = "1";
                    foreach ($det as $key => $item) {
                        $id_ctipoigv = $item->id_ctipoigv;
                        if ($id_ctipoigv == 1 && ($id_ctipoigv_reg == 2 || $id_ctipoigv_reg == 3)) {
                            $tipo_igv = "1";
                        } elseif ($id_ctipoigv == 2 && ($id_ctipoigv_reg == 1 || $id_ctipoigv_reg == 3)) {
                            $tipo_igv = "1";
                        } elseif ($id_ctipoigv == 3 && ($id_ctipoigv_reg == 1 || $id_ctipoigv_reg == 2)) {
                            $tipo_igv = "1";
                        } else {
                            $tipo_igv = "1";
                        }
                    }
                    if ($tipo_igv == "1") {
                        $response = EliseoCompraDetalle::create($data);
                        // $result = $this->pAddAnyEntity($data, "COMPRA_DETALLE", "ID_DETALLE", "addPurchasesDetails");
                        $data = PurchasesData::updateTotalCompra($id_compra);
                        // if ($result["success"]) {

                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
                        $jResponse['data'] = $response;
                        $code = "200";
                        // } else {
                        //     $jResponse['success'] = false;
                        //     $jResponse['message'] = $result["message"];
                        //     $jResponse['data'] = [];
                        //     $code = "202";
                        // }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "No se Acepta Diferente IGV's para la COMPRA " . $id_ctipoigv . " " . $id_ctipoigv_reg;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Importe MAYOR al Total del Comprobante";
                    $jResponse['data'] = [];
                    $code = "202";
                }
                /* XXXXX */
                /*$resultValida = PurchasesValidation::validationCompraDetalle();
                if($resultValida->invalid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultValida->message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto endAddPurchasesDetails;
                }
                $detalle = Input::get('detalle');
                $cantidad = Input::get('cantidad');
                $precio = Input::get('precio');
                $importe = Input::get('importe');
                $orden = Input::get('orden');

                $id_detalle = PurchasesData::getMax('compra_detalle','id_detalle')+1;
                $dataCompraDetalle = [
                    'id_detalle'=>$id_detalle
                    ,'id_compra'=>$id_compra
                    / * ,'id_dinamica'=>? * /
                    ,'detalle'=>$detalle
                    ,'cantidad'=>$cantidad
                    ,'precio'=>$precio
                    ,'importe'=>$importe
                    ,'estado'=>"1"
                    ,'orden'=>$orden
                ];
                $objCompraDetalle = PurchasesData::addCompraDetalle($dataCompraDetalle); */
                /* $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["compra_detalle"=>$result];
                $code = "200"; */
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        endAddPurchasesDetails:
        return response()->json($jResponse, $code);
    }
    public function putPurchasesDetails($id_compra, $id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $orden = Input::get('orden');
                $dataCompraDetalle = ["orden" => $orden];
                PurchasesData::updateCompraDetalle($dataCompraDetalle, $id_detalle);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function patchPurchasesDetails($id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $orden = Input::get('orden');
                // $dataCompraDetalle = ["orden" => $orden];
                $data = [
                    'id_articulo' => Input::get('id_articulo'),
                    'fecha_vencimiento' => Input::get('fecha_vencimiento')
                ];
                PurchasesData::updateCompraDetalle($data, $id_detalle);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function deletePurchasesDetails($id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showDetalle($id_detalle);
                foreach ($data as $item) {
                    $id_compra = $item->id_compra;
                }
                $result = PurchasesData::deletePurchasesDetails($id_detalle);
                if ($result) {
                    $data = PurchasesData::updateTotalCompra($id_compra);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
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

    public function deletePurchasesDetailsAll($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PurchasesData::deleteCompraDetalleMore($id_compra);
                if ($result) {
                    $data = PurchasesData::updateTotalCompra($id_compra);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
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


    public function formProvisions($id_pcompra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataCompra = PurchasesData::showCompra_left_pcompra($id_pcompra);
                $listCompraDetalle = [];
                $listCompraAsiento = [];
                if ($dataCompra) /* if(false) -> cambiar */ {
                    $listCompraDetalle = PurchasesData::listCompraDetalleC($dataCompra->id_compra);
                    $listCompraAsiento = PurchasesData::listCompraAsientoC($dataCompra->id_compra);
                    $jResponse['data'] = [
                        'compra' => $dataCompra // 'https://xd.adobe.com/view/798bb172-00d9-4c8d-451a-21c8542f8e1b-0fba/?fullscreen' // $dataCompra
                        , 'compra_detalle' => $listCompraDetalle, 'compra_asiento' => $listCompraAsiento
                    ];
                } else {
                    /* **XML** */
                    /* 63[factura]  130[boleta] 131[credito]    132[debito] */
                    /* $id_pcompra     = 132;/* **BORRAR LINEA** */
                    $tipo           = "1";
                    $formato        = "XML";
                    $dataPedidoFile = PurchasesData::onePedidoFileByIdPcompraTipoFormato($id_pcompra, $tipo, $formato);
                    if ($dataPedidoFile) {
                        $data = PurchasesUtil::getComprobanteFromXML($dataPedidoFile->nombre, $dataPedidoFile->url);
                        if ($data) {
                            /* $data["extra"]["proveedor_numero"] = 20157036794; /* id = 6881 -> borrar esta linea */
                            $dataProveedor = PurchasesData::showVWPersonaJuridicaByRuc($data["extra"]["proveedor_numero"]);
                            if ($dataProveedor) {
                                $dataCompra = $data["cabecera"];
                                if ($dataCompra["id_comprobante"] == "07" || $dataCompra["id_comprobante"] == "08") {
                                    $array_serie_numero = explode("-", $data["extra"]["documento_referencia_id"]);
                                    $serie = $array_serie_numero[0];
                                    $numero = $array_serie_numero[1];
                                    $id_comprobante = $data["extra"]["documento_referencia_code"]; /* $dataCompra["id_comprobante"]; */
                                    $id_proveedor = $dataProveedor->id_persona;
                                    $exists = PurchasesData::showExistsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, "");
                                    if (!$exists) {
                                        goto seguir_vacio;
                                    }
                                    $dataCompra["id_parent"] = $exists->id_compra;
                                    /* $dataCompra["id_parent"] = "1"; /* cambiar arriba */
                                }
                                $dataCompra["id_proveedor"] = $dataProveedor->id_persona;

                                $dataCompra["id_entidad"] = $id_entidad;
                                $dataCompra["id_depto"] = $id_depto;
                                list($id_anho, $id_mes) = PurchasesUtil::dataAnhoMesActivo2($id_entidad);
                                $dataCompra["id_anho"] = $id_anho;
                                $dataCompra["id_mes"] = $id_mes;
                                $dataCompra["id_persona"] = $id_user;
                                $dataCompra["fecha_provision"] = DB::raw('sysdate');
                                $dataCompra["id_voucher"] = $dataPedidoFile->id_voucher;
                                $resultCompra = $this->privateAddPurchases($dataCompra);
                                if ($resultCompra) {
                                    $id_compra = $resultCompra["id_compra"];
                                    foreach ($data["detalles"] as $dataDetalle) {
                                        $dataDetalle["id_compra"] = $id_compra;
                                        $dataDetalle["estado"] = "1";
                                        $resultDet = $this->privateAddPurchasesDetails($dataDetalle);
                                        $listCompraDetalle[] = $resultDet;
                                    }
                                    $jResponse['data'] = [
                                        'compra' => $dataCompra, 'compra_detalle' => $listCompraDetalle, 'compra_asiento' => []
                                    ];
                                    goto endFormProvisions;
                                }
                            }
                        }
                    }
                    seguir_vacio:
                    /* **XML-end** */
                    $objPedidoCompra = PurchasesData::showPedidoCompra($id_pcompra);
                    $jResponse['data'] = ['pedido_compra' => $objPedidoCompra];
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        endFormProvisions:
        return response()->json($jResponse, $code);
    }
    private function privateAddPurchasesDetails($data)
    {
        try {
            // CORREGIR AQUI SQ DET
            // $id_detalle = PurchasesData::getMax('compra_detalle', 'id_detalle') + 1;
            // $data = array_reverse($data, true);
            // $data["id_detalle"] = $id_detalle;
            // $data = array_reverse($data, true);
            // $object = PurchasesData::addPurchasesDetails($data);
            $object = EliseoCompraDetalle::create($data);
            return $object;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
    /* COMPRA_TIPOIGV */
    public function listPurchasesTypeigv()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_compra = Input::get("id_compra");
                $data = PurchasesData::listPurchasesTypeigv();
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
    public function listIgv()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listIgv();
                if (count($data)>0) {
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
    
    /* COMPRA_ASIENTO */
    public function listPurchasesSeats()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_compra
                /* $listCompraAsiento = PurchasesData::listCompraAsiento_Especial($id_compra);
                $dataTotales = PurchasesData::validaImporteDCCompraAsiento($id_compra);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["lista"=>$listCompraAsiento, "totales"=>$dataTotales[0]];
                $code = "200"; */
                $id_compra = Input::get("id_compra");
                $data = PurchasesData::listPurchasesSeats($id_compra);
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
    public function addPurchasesSeats()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesSeats();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endAddPurchasesSeat;
                }
                $data = $resultUtil->data;
                $data["editable"] = "S";
                $data["id_tiporegistro"] = "D";
                // $data["dc"] = "D";
                $data["dc"] = Input::get("dc");
                $result = EliseoCompraAsiento::create($data);
                // $result = PurchasesData::addPurchasesSeats($data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        endAddPurchasesSeat:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesSeats($id_casiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // PurchasesData::deleteCompraAsiento($id_casiento);
                $result = PurchasesData::deletePurchasesSeats($id_casiento);
                // PurchasesData::deleteCompraAsiento_children($id_casiento);
                /* $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200"; */
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
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
    public function addCreateDynamicSeat()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_dinamica = $this->request->input('id_dinamica');
                $id_compra = $this->request->input('id_compra');
                $id_pedido = $this->request->input('id_pedido');

                $objPedidoRegistro = PurchasesData::showPedidoRegistro($id_pedido);

                $objCompra = PurchasesData::showCompra($id_compra);
                $arrayCompra = (array)$objCompra;

                PurchasesData::deleteCompraAsiento_All($id_compra);
                $data = AccountingData::listAccountingRecursivoEntryDetails($id_dinamica, 0);
                $padre = $data[0];
                $hijos = $padre["children"];
                foreach ($hijos as $key => $value) {
                    if ($value["id_cuentaaasi"] == "2130101") {
                        $editable = "N";
                        $id_tiporegistro = "C";
                    } else {
                        $editable = "S";
                        $id_tiporegistro = "D";
                    }
                    $importe_a = DB::raw('(' . $arrayCompra[strtolower($value["indicador"])] . '*' . $value["porcentaje"] . ')');
                    // $id_casiento = PurchasesData::getMax('compra_asiento', 'id_casiento') + 1;
                    $depto = $value["depto"];
                    if (!empty($depto)) {
                        $deptoExplode = explode(", ", $depto);
                        $depto = $deptoExplode[0];
                    }
                    $ctacte = $value["ctacte"];
                    if (!empty($ctacte)) {
                        $ctacteExplode = explode(", ", $ctacte);
                        $ctacte = $ctacteExplode[0];
                    }
                    if ($objCompra->id_comprobante == "07") {
                        if ($value["dc"] == "C") {
                            $value["dc"] = "D";
                        } else {
                            $value["dc"] = "C";
                        }
                    }
                    $dataCompraAsiento = [
                        // 'id_casiento' => $id_casiento,
                        'id_compra' => $id_compra,
                        'id_cuentaaasi' => $value["id_cuentaaasi"],
                        'id_restriccion' => $value["id_restriccion"],
                        'id_ctacte' => $ctacte,
                        'id_fondo' => 10,
                        'id_depto' => $depto,
                        'importe' => $importe_a,
                        'descripcion' => $objPedidoRegistro->motivo,
                        'editable' => $editable,
                        'id_tiporegistro' => $id_tiporegistro,
                        'dc' => $value["dc"]
                    ];
                    // $objCompraAsiento = PurchasesData::addCompraAsiento($dataCompraAsiento);
                    $objCompraAsiento = EliseoCompraAsiento::create($dataCompraAsiento);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $data;
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
    public function chooserAasinet()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_cuentaaasi = Input::get("id_cuentaaasi");
                $list = PurchasesData::chooserAasinet($id_entidad, $id_cuentaaasi);
                foreach ($list as $item) {
                    $requiere_cta_cte = $item->requiere_cta_cte;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["requiere_cta_cte" => $requiere_cta_cte];
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
    public function listAccountingSeat($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $listCompraAsiento = PurchasesData::listCompraAsiento_Especial($id_compra);
                $dataTotales = PurchasesData::validaImporteDCCompraAsiento($id_compra);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["lista" => $listCompraAsiento, "totales" => $dataTotales[0]];
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
    public function addAccountingSeat($id_compra)/* BORRAR */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = $this->request->input('id_compra');
                $id_cuentaaasi = $this->request->input('id_cuentaaasi');
                $id_restriccion = $this->request->input('id_restriccion');
                $id_ctacte = $this->request->input('id_ctacte');
                $id_fondo = $this->request->input('id_fondo');
                $id_depto = $this->request->input('id_depto');
                $importe = $this->request->input('importe');
                $descripcion = $this->request->input('descripcion');
                $editable = "S";
                $id_tiporegistro = "D";
                $objCompra = PurchasesData::showCompra($id_compra);
                if ($objCompra->id_comprobante == "07") {
                    $dc = "C";
                } else {
                    $dc = "D";
                }
                // $id_casiento = PurchasesData::getMax('compra_asiento', 'id_casiento') + 1;
                $dataCompraAsiento = [
                    // 'id_casiento' => $id_casiento,
                    'id_compra' => $id_compra,
                    'id_cuentaaasi' => $id_cuentaaasi,
                    'id_restriccion' => $id_restriccion,
                    'id_ctacte' => $id_ctacte,
                    'id_fondo' => $id_fondo,
                    'id_depto' => $id_depto,
                    'importe' => $importe,
                    'descripcion' => $descripcion,
                    'editable' => $editable,
                    'id_tiporegistro' => $id_tiporegistro,
                    'dc' => $dc
                ];
                // $objCompraAsiento = PurchasesData::addCompraAsiento($dataCompraAsiento);
                $objCompraAsiento = EliseoCompraAsiento::create($dataCompraAsiento);
                $this->privateAddCompraAsiento_children($objCompraAsiento);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function updateAccountingSeat($id_compra, $id_casiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = $this->request->input('id_compra');
                $id_cuentaaasi = $this->request->input('id_cuentaaasi');
                $id_restriccion = $this->request->input('id_restriccion');
                $id_ctacte = $this->request->input('id_ctacte');
                $id_fondo = $this->request->input('id_fondo');
                $id_depto = $this->request->input('id_depto');
                $importe = $this->request->input('importe');
                $descripcion = $this->request->input('descripcion');
                $dataCompraAsiento = [
                    'id_cuentaaasi' => $id_cuentaaasi,
                    'id_restriccion' => $id_restriccion,
                    'id_ctacte' => $id_ctacte,
                    'id_fondo' => $id_fondo,
                    'id_depto' => $id_depto,
                    'importe' => $importe,
                    'descripcion' => $descripcion
                ];
                PurchasesData::updateCompraAsiento($dataCompraAsiento, $id_casiento);
                PurchasesData::deleteCompraAsiento_children($id_casiento);
                $dataCompraAsiento["id_compra"] = $id_compra;
                $this->privateAddCompraAsiento_children($dataCompraAsiento);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function deleteAccountingSeat($id_compra, $id_casiento)/* BORRAR */
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deleteCompraAsiento($id_casiento);
                PurchasesData::deleteCompraAsiento_children($id_casiento);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    private function privateAddCompraAsiento_children($data)
    {
        try {
            if ($data["id_fondo"] == "69") {
                $id_cuentaaasi_dc_d = "2171045";
            } else if ($data["id_fondo"] == "68") {
                $id_cuentaaasi_dc_d = "2171060";
            } else if ($data["id_fondo"] == "66") {
                $id_cuentaaasi_dc_d = "2171020";
            } else if ($data["id_fondo"] == "25") // Inversión em Inmovilizado
            {
                $id_cuentaaasi_dc_d = "2171005";
            } else if ($data["id_fondo"] == "93") // Fondo Asistencial
            {
                $id_cuentaaasi_dc_d = "2171030";
            } else {
                return false;
            }
            $id_cuentaaasi_dc_c = "1171001";
            $dc1 = "D";
            $dc2 = "C";
            $objCompra = PurchasesData::showCompra($data["id_compra"]);
            if ($objCompra->id_comprobante == "07") {
                $dc1 = "C";
                $dc2 = "D";
            }
            $data["id_cuentaaasi"] = $id_cuentaaasi_dc_d;
            $data["dc"] = $dc1;
            $data["id_restriccion"] = "0A";
            $data["id_ctacte"] = "";
            $data["id_depto"] = "0000";
            $data["id_tiporegistro"] = "B";
            $data["id_parent"] = $data["id_casiento"];
            $data["importe"] = $data["importe"];
            $data["editable"] = "N";
            unset($data["id_casiento"]);
            // $object1 = $this->privateAddCompraAsiento($data);
            $object1 = EliseoCompraAsiento::create($data);
            
            if (!$object1) return false;
            $data["id_cuentaaasi"] = $id_cuentaaasi_dc_c;
            $data["dc"] = $dc2;
            $data["id_fondo"] = "10";
            // $object2 = $this->privateAddCompraAsiento($data);
            $object2 = EliseoCompraAsiento::create($data);
            if (!$object2) return false;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    // private function privateAddCompraAsiento($data)
    // {
    //     try {
    //         $id_casiento = PurchasesData::getMax('compra_asiento', 'id_casiento') + 1;
    //         $data = array_reverse($data, true);
    //         $data["id_casiento"] = $id_casiento;
    //         $data = array_reverse($data, true);
    //         $object = PurchasesData::addCompraAsiento($data);
    //         return $object;
    //     } catch (Exception $e) {
    //         return false;
    //     }
    // }
    /* COMPRA_ORDEN */
    public function listPurchasesOrders()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listPurchasesOrders($id_entidad);
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
    public function showPurchasesOrders($id_orden)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesOrders($id_entidad, $id_orden);
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
    public function showPurchasesOrdersByOrders($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesOrdersByOrders($id_entidad, $id_pedido);
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
    public function addPurchasesOrders()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesOrders();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endGoTo;
                }
                $data = $resultUtil->data;
                $data["id_entidad"] = $id_entidad;
                $data["id_depto"] = $id_depto;
                $data["estado"] = 0;
                $data["id_persona"] = $id_user;
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', $tiene_params, $params);
                if ($rpta["nerror"] > 0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Anho o mes no activos.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endGoTo;
                }
                $data["id_anho"] = $rpta["id_anho"];
                $data["id_mes"] = $rpta["id_mes"];
                $result = $this->pAddAnyEntity($data, "COMPRA_ORDEN", "ID_ORDEN", "addPurchasesOrders");
                if ($result["success"]) {
                    $id_orden = $result["data"]["ID_ORDEN"];
                    $result2 = PurchasesData::addAllPurchasesOrdersDetails($id_orden, Input::get("id_pedido"));
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result["data"];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result["message"];
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
        endGoTo:
        return response()->json($jResponse, $code);
    }
    public function updatePurchasesOrders($id_orden)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesOrders();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endGoTo;
                }
                $data = $resultUtil->data;
                // $data["id_entidad"] = $id_entidad;
                // $data["id_depto"] = $id_depto;
                // $data["estado"] = 0;
                // $data["id_persona"] = $id_user;
                // $params = "";
                // $tiene_params = "N";
                // $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                // if($rpta["nerror"] > 0)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "Anho o mes no activos.";
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto endGoTo;
                // }
                // $data["id_anho"] = $rpta["id_anho"];
                // $data["id_mes"] = $rpta["id_mes"];
                // $result = $this->pAddAnyEntity($data,"COMPRA_ORDEN","ID_ORDEN","addPurchasesOrders");
                $result = PurchasesData::updatePurchasesOrders($data, $id_orden);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = [];
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
        endGoTo:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesOrders($id_orden)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PurchasesData::deletePurchasesOrders($id_orden);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function patchPurchasesOrdersEnd($id_orden)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // PurchasesData::spProcessStepRunNext($data);
                //$result = true; // deletePurchasesOrders

                $resultUtil = PurchasesUtil::dataPurchasesOrdersU();
                $data = $resultUtil->data;
                $result = PurchasesData::updatePurchasesOrders($data, $id_orden);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Successfully";
                    $jResponse['data'] = [];
                    $code = "200";
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
    /* COMPRA_ORDEN_DETALLE */
    public function listPurchasesOrdersDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_orden = Input::get("id_orden");
                $data = PurchasesData::listPurchasesOrdersDetails($id_orden);
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
    public function showPurchasesOrdersDetails($id_odetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_orden = Input::get("id_orden");
                $data = PurchasesData::showPurchasesOrdersDetails($id_odetalle);
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
    public function addPurchasesOrdersDetails()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesOrdersDetails();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endGoTo;
                }
                $data = $resultUtil->data;
                $result = $this->pAddAnyEntity($data, "COMPRA_ORDEN_DETALLE", "ID_ODETALLE", "addPurchasesOrdersDetails");
                if ($result["success"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result["data"];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result["message"];
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
        endGoTo:
        return response()->json($jResponse, $code);
    }
    public function updatePurchasesOrdersDetails($id_odetalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataPurchasesOrdersDetailsUP();
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto endGoTo;
                }
                $data = $resultUtil->data;
                PurchasesData::updatePurchasesOrdersDetails($id_odetalle, $data);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was Updated successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        endGoTo:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesOrdersDetails($id_odetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PurchasesData::deletePurchasesOrdersDetails($id_odetalle);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
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
    /* CONTA_ASIENTO */
    public function execPurchasesSeatsGenerate() /* NEW 2019-05-07*/ // execPurchasesSeatsGenerate
    {
        // $jResponse = GlobalMethods::authorizationLamb($this->request);
        // $code   = $jResponse["code"];
        // $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        // if($valida=='SI')
        // {
        //     $jResponse =[];
        //     try
        //     {
        //         $id_compra = Input::get("id_compra");
        //         $dataTotales = PurchasesData::validaImporteDCCompraAsiento($id_compra);
        //         $valida = "NO";
        //         foreach ($dataTotales as $item)
        //         {
        //             $valida = $item->valida;
        //         }
        //         if($valida == "NO")
        //         {
        //             $jResponse['success'] = false;
        //             $jResponse['message'] = "Importe no coincide.";
        //             $jResponse['data'] = [];
        //             $code = "202";
        //             goto end;
        //         }
        //         $id_pedido = $this->request->input('id_pedido');
        //         $id_pcompra = $this->request->input('id_pcompra');
        //         $dataCompra = PurchasesData::showCompra($id_compra);
        //         $listCompraAsiento = PurchasesData::listCompraAsiento_Prepare($id_compra);
        //         foreach ($listCompraAsiento as $key => $value)
        //         {
        //             $id_asiento = PurchasesData::getMax('conta_asiento','id_asiento')+1;
        //             $dataContaAsiento = [
        //                 'id_asiento'=>$id_asiento
        //                 ,'id_operorigen'=>3
        //                 ,'id_origen'=>$id_compra
        //                 ,'fondo'=>$value->id_fondo
        //                 ,'depto'=>$value->id_depto
        //                 ,'cuenta'=>$value->id_cuentaaasi
        //                 ,'cuenta_cte'=>$value->id_ctacte
        //                 ,'restriccion'=>$value->id_restriccion
        //                 ,'importe'=>$value->importe
        //                 ,'descripcion'=>'{'.$dataCompra->serie.'-'.$dataCompra->numero.'} '.$value->descripcion
        //                 ,'memo'=>$id_compra
        //                 ,'voucher'=>"".$dataCompra->id_voucher.""
        //             ];
        //             $objContaAsiento = PurchasesData::addContaAsiento($dataContaAsiento);
        //         }
        //         $dataCambiaEstado = ["estado"=>"1"];
        //         PurchasesData::updateCompra($dataCambiaEstado,$id_compra);
        //         PurchasesData::updatePedidoCompra($dataCambiaEstado,$id_pcompra);
        //         $jResponse['success'] = true;
        //         $jResponse['message'] = "Asiento Creado.";
        //         $jResponse['data'] = [];
        //         $code = "200";
        //     }
        //     catch(Exception $e)
        //     {
        //         $jResponse['success'] = false;
        //         $jResponse['message'] = "ORA-".$e->getMessage();
        //         $jResponse['data'] = [];
        //         $code = "400";
        //     }
        // }
        // end:
        // return response()->json($jResponse,$code);
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // xxx
                // $id_pedido = Input::get("id_pedido");
                // $id_compra = Input::get("id_compra");
                $id_compra = Input::get("id_compra");
                $entCompra = PurchasesData::showPurchases($id_entidad, $id_compra); // addPurchasesSeats($data);
                if ($entCompra) {
                    $importe = $entCompra->importe;
                    $rows = OrdersData::listOrdersSeatsByIdCompra($id_compra);
                    // $listOrdersSeats = OrdersData::listOrdersSeatsByIdCompra($id_compra);
                    // dd($rows);
                    $errors = 0;
                    foreach ($rows as $row) // $key => $value
                    {
                        // xxx
                        $data = [
                            "id_compra" => $id_compra,
                            "id_cuentaaasi" => $row->id_cuentaaasi,
                            "id_restriccion" => $row->id_restriccion,
                            "id_ctacte" => $row->id_ctacte,
                            "id_fondo" => $row->id_fondo,
                            "id_depto" => $row->id_depto,
                            "descripcion" => $row->glosa,
                            // "editable" => ,
                            "editable" => "S",
                            "id_tiporegistro" => "D",
                            "dc" => $row->dc,
                            // "importe_me" => ,
                            "importe" => DB::raw("(" . $importe . "*(" . $row->porcentaje . "/100))"),
                            "fecha_actualizacion" => DB::raw('sysdate')
                        ];
                        // dd($data);
                        // $result = OrdersData::addOrdersSeats($data);
                        // $result = PurchasesData::addPurchasesSeats($data);
                        $result = EliseoCompraAsiento::create($data);
                        if (!$result) {
                            $errors++;
                        }
                    }
                    if ($errors == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "OK";
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Error.";
                        $jResponse['data']    = [];
                        $code                 = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
                // dd($id_compra);
                // $listOrdersSeats = OrdersData::listOrdersSeatsByIdCompra($id_compra);
                // dd($listOrdersSeats);
                // foreach($listOrdersSeats as $key => $value)
                // {
                //     // xxx
                // }
                // dd("FINAL");
                // /* xxxx */
                // /* xxxx */
                // /* xxxx */
                // /* xxxx */
                // $id_dinamica = $this->request->input('id_dinamica');
                // $id_compra = $this->request->input('id_compra');
                // $id_pedido = $this->request->input('id_pedido');

                // $objPedidoRegistro = PurchasesData::showPedidoRegistro($id_pedido);

                // $objCompra = PurchasesData::showCompra($id_compra);
                // $arrayCompra = (array)$objCompra;

                // PurchasesData::deleteCompraAsiento_All($id_compra);
                // $data = AccountingData::listAccountingRecursivoEntryDetails($id_dinamica,0);
                // $padre = $data[0];
                // $hijos = $padre["children"];
                // foreach($hijos as $key => $value)
                // {
                //     if($value["id_cuentaaasi"] == "2130101")
                //     {
                //         $editable = "N";
                //         $id_tiporegistro = "C";
                //     }
                //     else
                //     {
                //         $editable = "S";
                //         $id_tiporegistro = "D";
                //     }
                //     $importe_a = DB::raw('('.$arrayCompra[strtolower($value["indicador"])].'*'.$value["porcentaje"].')');
                //     $id_casiento = PurchasesData::getMax('compra_asiento','id_casiento')+1;
                //     $depto = $value["depto"];
                //     if (!empty($depto))
                //     {
                //         $deptoExplode = explode(", ", $depto);
                //         $depto = $deptoExplode[0];
                //     }
                //     $ctacte = $value["ctacte"];
                //     if (!empty($ctacte))
                //     {
                //         $ctacteExplode = explode(", ", $ctacte);
                //         $ctacte = $ctacteExplode[0];
                //     }
                //     if($objCompra->id_comprobante == "07")
                //     {
                //         if($value["dc"] == "C")
                //         {
                //             $value["dc"] = "D";
                //         }
                //         else
                //         {
                //             $value["dc"] = "C";
                //         }
                //     }
                //     $dataCompraAsiento = [
                //         'id_casiento'=>$id_casiento
                //         ,'id_compra'=>$id_compra
                //         ,'id_cuentaaasi'=>$value["id_cuentaaasi"]
                //         ,'id_restriccion'=>$value["id_restriccion"]
                //         ,'id_ctacte'=>$ctacte
                //         ,'id_fondo'=>10
                //         ,'id_depto'=>$depto
                //         ,'importe'=>$importe_a
                //         ,'descripcion'=>$objPedidoRegistro->motivo
                //         ,'editable'=>$editable
                //         ,'id_tiporegistro'=>$id_tiporegistro
                //         ,'dc'=>$value["dc"]
                //     ];
                //     $objCompraAsiento = PurchasesData::addCompraAsiento($dataCompraAsiento);

                // }

                // $jResponse['success'] = true;
                // $jResponse['message'] = "OK";
                // $jResponse['data'] = $data;
                // $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addAccountingSeatGenerate($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataTotales = PurchasesData::validaImporteDCCompraAsiento($id_compra);
                $valida = "NO";
                foreach ($dataTotales as $item) {
                    $valida = $item->valida;
                }
                if ($valida == "NO") {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Importe no coincide.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $id_pedido = $this->request->input('id_pedido');
                $id_pcompra = $this->request->input('id_pcompra');
                $dataCompra = PurchasesData::showCompra($id_compra);
                $listCompraAsiento = PurchasesData::listCompraAsiento_Prepare($id_compra);
                foreach ($listCompraAsiento as $key => $value) {
                    $id_asiento = PurchasesData::getMax('conta_asiento', 'id_asiento') + 1;
                    $dataContaAsiento = [
                        'id_asiento' => $id_asiento, 'id_operorigen' => 3, 'id_origen' => $id_compra, 'fondo' => $value->id_fondo, 'depto' => $value->id_depto, 'cuenta' => $value->id_cuentaaasi, 'cuenta_cte' => $value->id_ctacte, 'restriccion' => $value->id_restriccion, 'importe' => $value->importe, 'descripcion' => '{' . $dataCompra->serie . '-' . $dataCompra->numero . '} ' . $value->descripcion, 'memo' => $id_compra, 'voucher' => "" . $dataCompra->id_voucher . ""
                    ];
                    $objContaAsiento = PurchasesData::addContaAsiento($dataContaAsiento);
                }
                $dataCambiaEstado = ["estado" => "1"];
                PurchasesData::updateCompra($dataCambiaEstado, $id_compra);
                PurchasesData::updatePedidoCompra($dataCambiaEstado, $id_pcompra);
                $jResponse['success'] = true;
                $jResponse['message'] = "Asiento Creado.";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    /* PLANTILLA_DETALLE_COMPRA */ // MOVER - CHANGE
    /* PEDIDO_PLANTILLA_COMPRA */
    public function listOrdersTemplatesPurchases() // listTemplateDetailsPurchases()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pedido = Input::get("id_pedido");
                $data = PurchasesData::listOrdersTemplatesPurchases($id_pedido);
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
    public function showOrdersTemplatesPurchases($id_ppcompra) // showTemplateDetailsPurchases($id_pdcompra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showOrdersTemplatesPurchases($id_ppcompra);
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
    public function addOrdersTemplatesPurchases() // addTemplateDetailsPurchases()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $validTemplate = PurchasesValidation::validationTemplateDetailsPurchases();
                // if($validTemplate->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $validTemplate->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end_AddTemplateDetailsPurchases;
                // }
                // $dataTemplate = PurchasesUtil::dataTemplateDetailsPurchases();
                // if($dataTemplate->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $dataTemplate->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end_AddTemplateDetailsPurchases;
                // }
                // ---------------
                // $validComprobante = PurchasesValidation::validationCall("validationTemplateDetailsPurchases");
                // if($validComprobante->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $validComprobante->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end;
                // }
                // $data = PurchasesUtil::utilCall("dataComprobante".Input::get('id_comprobante'));
                // ---------------
                $result = PurchasesUtil::dataOrdersTemplatesPurchases(); // utilCall("dataTemplateDetailsPurchases");
                if ($result->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result->message;
                    $jResponse['data']   = [];
                    $code                = "202";
                    goto end;
                }
                $data               = $result->data;
                $data["id_entidad"] = $id_entidad;
                $data["id_depto"]   = $id_depto;
                $data["estado"]     = "1";
                // **************
                $result = $this->privateAddOrdersTemplatesPurchases($data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
                // $jResponse['success'] = true;
                // $jResponse['message'] = "OK";
                // $jResponse['data'] = [];
                // $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function updateTemplateDetailsPurchases($id_pdcompra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $validTemplate = PurchasesValidation::validationTemplateDetailsPurchases();
                // if($validTemplate->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $validTemplate->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end_UpdateTemplateDetailsPurchases;
                // }
                // $dataTemplate = PurchasesUtil::dataTemplateDetailsPurchases();
                // if($dataTemplate->invalid)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $dataTemplate->message;
                //     $jResponse['data'] = [];
                //     $code = "202";
                //     goto end_UpdateTemplateDetailsPurchases;
                // }
                $result = PurchasesUtil::dataTemplateDetailsPurchases(); // utilCall("dataTemplateDetailsPurchases");
                if ($result->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $data = $result->data;
                // ************************
                $result = PurchasesData::updateTemplateDetailsPurchases($data, $id_pdcompra);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
                // $jResponse['success'] = true;
                // $jResponse['message'] = "OK";
                // $jResponse['data'] = [];
                // $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function deleteOrdersTemplatesPurchases($id_ppcompra) // deleteTemplateDetailsPurchases($id_pdcompra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deleteOrdersTemplatesPurchases($id_ppcompra);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function execOrdersTemplatesPurchasesEnd($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // PurchasesData::spProcessStepRunNext($data);
                // $result = true; // PurchasesData::deletePurchasesOrders($id_orden);
                $sumImporte = PurchasesData::sumOrdTemPedImporteByIdPedido($id_pedido);
                if ($sumImporte > 0) {
                    $codigo = Input::get('codigo');
                    // $result3 = $this->privateAddProcessStepRunNext($codigo, $id_pedido, $id_user,$id_entidad,null); -> ($codigo, $id_pedido, $id_user,$id_entidad,$detalle,$ip)
                    // $entCompraOrden = PurchasesData::showPurchasesOrders($id_entidad,$id_orden);
                    $clientIP = \Request::ip();
                    $data = [
                        "codigo"    => $codigo,
                        "id_pedido" => $id_pedido,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle"   => null,
                        "ip"        => $clientIP
                    ];
                    // dd($data);
                    // dd($data);
                    $result = PurchasesData::spProcessStepRunNext($data);
                    if ($result['error'] == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Error..";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                    // ------
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data'] = [];
                    $code = "202";
                }
                // dd("OK");
                // PurchasesData::execOrdersTemplatesPurchasesEnd($id_ppcompra);
                // $jResponse['success'] = true;
                // $jResponse['message'] = "OK";
                // $jResponse['data'] = [];
                // $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddOrdersTemplatesPurchases($data) // privateAddTemplateDetailsPurchases($data)
    {
        try {
            $id_ppcompra = PurchasesData::getMax('PEDIDO_PLANTILLA_COMPRA', 'id_ppcompra') + 1;
            $data = array_merge(array("id_ppcompra" => $id_ppcompra), $data);
            $success = PurchasesData::addOrdersTemplatesPurchases($data);
            if ($success)
                return $data;
            else
                return false;
        } catch (Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }
    /* PEDIDO_FILE */
    public function listFilesReceipt($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listFilesReceipt($id_pedido);
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
    public function getFilesReceipt($id_pfile)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        if (true) {
            $jResponse = [];
            try {
                $objPedidoFile = PurchasesData::showPedidoFile($id_pfile);
                if ($objPedidoFile) {
                    if ($objPedidoFile->formato == "PDF") {
                        $file = public_path() . "/" . $objPedidoFile->url;
                        $headers = array(
                            'Content-Type: application/pdf',
                        );
                        return Response::download($file, 'documento.pdf', $headers);
                    } else if ($objPedidoFile->formato == "XML") {
                        $file = public_path() . "/" . $objPedidoFile->url;
                        $headers = array(
                            'Content-Type: text/xml',
                        );
                        return Response::download($file, 'documento.xml', $headers);
                    } else if ($objPedidoFile->formato == 'png' || 'PNG') {
                        $file = public_path() . "/" . $objPedidoFile->url;
                        $headers = array(
                            'Content-Type: image/png',
                        );
                        return Response::download($file, 'documento.png', $headers);
                    } else if ($objPedidoFile->formato == 'jpg' || 'jpeg' || 'JPEG' || 'JPG') {
                        $file = public_path() . "/" . $objPedidoFile->url;
                        $headers = array(
                            'Content-Type: image/jpeg',
                        );
                        return Response::download($file, 'documento.' . $objPedidoFile->formato, $headers);
                    }
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
    private function privateUploadPedidoFile($id_pedido, $id_pcompra, $archivo, $tipo)
    {
        try {
            if (is_object($archivo)) {
                $file = $archivo;
            } else {
                $file = Input::file($archivo);
            }

            if ($file == null) {
                return false;
            }

            if ($tipo == "3") {
                $directory = 'lamb-financial/purchases/comprobantes';
            } else if ($tipo == "15") // carpeta para evidencias de denegacion de pedidos
            {
                $directory = 'lamb-financial/purchases/rechaz-eviden';
            } else {
                $directory = 'lamb-financial/purchases/proformas';
            }

            // $estado = "1";
            // $file->move($destinationPath, $nombreDinamico);

            // Procedimiento
            
            // $result = $this->privateAddPedidoFile($id_pedido, $file->getClientOriginalName(), $formato, $url, $tipo, $estado, $id_pcompra, $size);
            // return $result;
            // return ["success" => $result["success"], "message" => $result["message"], "data" => $result["data"]];

            // Subidad de archivos

            $storage = new StorageController(); 
            $file_data = $storage->postFile(Input::file($archivo), $directory);

            $formato = strtoupper($file->getClientOriginalExtension());
            $size = $file->getSize();

            ComunData::insertDataFile($id_pedido, $id_pcompra, $file->getClientOriginalName(), $formato, $file_data['data'], '1', $tipo, $size, '1');
            return ["success" => true, "message" => 'Ok', "data" => []];
        } catch (Exception $e) {
            // echo $e->getMessage();
            // return false;
            // dd(["success"=>false,"message"=>$e->getMessage()]);
            return ["success" => false, "message" => $e->getMessage()];
        }
    }


    // private function privateAddPedidoFile($id_pedido, $nombre, $formato, $url, $tipo, $estado, $id_pcompra, $size)
    // {
    //     try {
    //         $id_pfile = PurchasesData::getMax('pedido_file', 'id_pfile') + 1;
    //         $dataPedidoFile = array(
    //             "id_pfile"  => $id_pfile,
    //             "id_pedido" => $id_pedido,
    //             "nombre"    => $nombre,
    //             "formato"   => $formato,
    //             "url"       => $url,
    //             "fecha"     => DB::raw('sysdate'),
    //             "tipo"      => $tipo,
    //             // "seleccionado"=> $tipo,
    //             "tamanho"   => $size,
    //             "estado"    => $estado,
    //             "id_pcompra" => $id_pcompra
    //         );
    //         $result = PurchasesData::addPedidoFile($dataPedidoFile);
    //         return ["success" => $result["success"], "message" => $result["message"], "data" => $result["data"]];
    //     } catch (Exception $e) {
    //         // echo $e->getMessage();
    //         return ["success" => false, "message" => $e->getMessage()];
    //     }
    // }



    /* TIPO_PEDIDO */
    public function listTypesOrders()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $estado = Input::get("estado");
                $dataPedidoCompra = PurchasesData::listTypesOrders($estado);
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
    /* PRIVATES */
    private function pAddAnyEntity($data, $table, $column, $function)
    {
        try {
            $id     = PurchasesData::getMax($table, $column) + 1;
            $data   = array_merge(array($column => $id), $data);
            $result = PurchasesData::$function($data);
            if ($result)
                return ["success" => true, "data" => $data, "message" => ""];
            else
                return ["success" => false, "data" => [], "message" => "Error"];
        } catch (Exception $e) {
            return ["success" => false, "data" => [], "message" => $e->getMessage()];
        }
    }
    /* REPORTE */
    public function listReportPurchases()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $data = [];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = Input::get('id_anho');
                $id_mes = Input::get('id_mes');
                $id_empresa = Input::get('id_empresa');
                $id_entidad = Input::get('id_entidad');
                $id_depto = Input::get('id_depto');
                if (is_null(Input::get('text_search'))) {
                    $text_search = "";
                } else {
                    $text_search = Input::get('text_search');
                }


                $items = PurchasesData::listReportPurchases($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto, $text_search);
                $items_sum = PurchasesData::listReportPurchases_total($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto);
                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
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

    public function listMyReportPurchases()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $data = [];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = Input::get('id_anho');
                $id_mes = Input::get('id_mes');
                $id_voucher = Input::get('id_voucher');
                $id_usua = Input::get('id_usua');
                if (is_null(Input::get('text_search'))) {
                    $text_search = "";
                } else {
                    $text_search = Input::get('text_search');
                }

                $items = PurchasesData::listMyReportPurchases($id_anho, $id_mes, $id_entidad, $text_search, $id_voucher, $id_usua);
                $items_sum = PurchasesData::listMyReportPurchasesTotal($id_anho, $id_mes, $id_entidad, $id_voucher, $id_usua);

                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
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

    public function listReportAccountingSeat()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = Input::get('id_anho');
                $id_mes = Input::get('id_mes');
                $id_entidad = Input::get('id_entidad');
                $id_empresa = Input::get('id_empresa');
                $id_operorigen = 3;
                $data = PurchasesData::listReportAccountingSeat($id_anho, $id_mes, $id_empresa, $id_entidad, $id_operorigen);
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
    public function listReportProvisions()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = Input::get('id_anho');
                $id_mes = Input::get('id_mes');
                $id_entidad = Input::get('id_entidad');
                $id_empresa = Input::get('id_empresa');
                $data = PurchasesData::listReportProvisions($id_anho, $id_mes, $id_empresa, $id_entidad);
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
    /* end. */
    /* SEND MAIL */
    public function privateSendEmailPedidoAprobado($id_pedido, $id_entidad)
    {
        $result = new class
        {
        };
        try {
            $dataResult = PurchasesData::dataRequest_by_Send($id_pedido, $id_entidad);
            $entidad = $dataResult->entidad_nombre;
            $departamento = $dataResult->departamento_nombre;
            $nombre = $dataResult->nombre_completo;
            $nropedido = $id_pedido;
            $fecha = $dataResult->fecha_pedido;
            $coreos_help = array(
                array(
                    "area"     => "Soporte Lamb", "cuenta" => "support@upeu.edu.pe"
                ),
                array(
                    "area"   => "Help Desc", "cuenta" => "help_desk@upeu.edu.pe"
                )
            );
            $correo = "miguel2700@hotmail.com"; // $dataResult->correo_virtual; // "miguel2700@hotmail.com";
            $asunto = "Pedido #" . $id_pedido . ":" . $dataResult->motivo . ", Aprobado.";
            $data = array(
                "entidad"      => $entidad,
                "departamento" => $departamento,
                "nombre"       => $nombre,
                "nropedido"    => $nropedido,
                "fecha"        => $fecha,
                "coreos_help"  => $coreos_help
            );
            $view = "emails.pedidoaceptado";
            $files = [];
            $dataPedidoFile = PurchasesData::getPedidoFile_elejido($id_pedido);
            if ($dataPedidoFile) {
                $files[] = array(
                    "path" => public_path() . "/" . $dataPedidoFile->url,
                    "name" => $dataPedidoFile->nombre
                );
            }
            $from_address = "noreply-gth@upeu.edu.pe";
            $from_name = "LAMB SUPPORT";
            $resultSendEmail = PurchasesUtil::sendEmail($correo, $asunto, $data, $files, $view, $from_address, $from_name);
            if ($resultSendEmail->valid) {
                $result->success = true;
                $result->message = "";
            } else {
                $result->success = false;
                $result->message = $resultSendEmail->message;
            }
        } catch (Exception $e) {
            $result->success = false;
            $result->message = $e->getMessage();
        }
        return $result;
    }

    public function purchasesBalancesToUpdate(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }

                $data = [];
                $id_voucher = $request->query('id_voucher');
                $id_pago = $request->query('id_pago');
                //dd($id_entidad,$id_depto,$id_anho, $id_voucher, $id_pago);
                $data = PurchasesData::purchasesBalancesByIdVoucherAnIdPago($id_entidad, $id_depto, $id_anho, $id_voucher, $id_pago);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        return response()->json($jResponse);
    }
    /* OTHERS */
    public function purchasesBalances(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                //temporal para obetner el año
                /*$anho = AccountingData::showVoucherModules($id_user,$id_entidad,$id_depto,'6');
                foreach ($anho as $item){
                    $id_anho = $item->id_anho;
                }*/

                $data = [];
                $id_proveedor = $request->query('id_proveedor');
                $id_voucher = $request->query('id_voucher');
                // dd($id_entidad,$id_depto);

                //Solo temporal para sacar año del voucher
                /*if ($id_depto == '4'){
                        $id_anho = 2022;
                }*/

                if ($id_voucher !== "" && $id_voucher !== null) {
                    $data = PurchasesData::purchasesBalancesByIdVoucher($id_entidad, $id_depto, $id_anho, $id_voucher);
                } else if ($id_proveedor !== "" && $id_proveedor !== null) {
                    // Para Obtener el Año
                    $id_tipovoucher = 4;  //  para TLC
                    $data_new_anho = AccountingData::showVoucherModules($id_user, $id_entidad, $id_depto, $id_tipovoucher);
                    foreach ($data_new_anho as $item) {
                        $id_anho = $item->id_anho;
                    }
                    $data = PurchasesData::purchasesBalances($id_entidad, $id_depto, $id_anho, $id_proveedor);
                }

                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        return response()->json($jResponse);
        /*
        return response()->json([
            'id_entidad' => $id_entidad,
            'id_depto' => $id_depto,
            'id_anho' => $id_anho,
            'id_proveedor' => $id_proveedor,
            ]);*/
    }
    public function purchasesValesRelacionados(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_vale = $request->query('id_vale');
                $id_tipovoucher = 4;
                $data_new_anho = AccountingData::showVoucherModules($id_user, $id_entidad, $id_depto, $id_tipovoucher);
                foreach ($data_new_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = PurchasesData::purchasesValesRelacionados($id_entidad, $id_depto, $id_anho, $id_vale);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
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
        return response()->json($jResponse);
    }


    public function purchasesBalancesOthersVouchers(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }

                $data = [];
                $id_voucher = $request->query('id_voucher');

                if ($id_voucher !== "" && $id_voucher !== null) {
                    $data = PurchasesData::purchasesBalancesOthersVouchersByIdVoucher($id_entidad, $id_depto, $id_anho, $id_user,  $id_voucher);
                } else {
                    $data = [];
                }
                /*
                else if ($id_proveedor !== "" && $id_proveedor !== null) {
                    $data = PurchasesData::purchasesBalances($id_entidad, $id_depto, $id_anho, $id_proveedor);
                }*/
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        return response()->json($jResponse);
    }

    public function purchasesBalancesOfRetencion(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = [];
                $id_proveedor = $request->query('id_proveedor');
                $data = Input::all();
                $validador = Validator::make($data,  ['id_proveedor' => 'required']);
                if ($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validador->errors()->first();
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                $data = PurchasesData::purchasesBalancesOfRetencions($id_entidad, $id_depto, $id_anho, $id_proveedor);

                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        end:
        return response()->json($jResponse, $code);
    }

    public function purchasesBalancesAll()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                $data = PurchasesData::purchasesBalancesAll($id_entidad, $id_depto, $id_anho);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        return response()->json($jResponse);
        /*
        return response()->json([
            'id_entidad' => $id_entidad,
            'id_depto' => $id_depto,
            'id_anho' => $id_anho,
            'id_proveedor' => $id_proveedor,
            ]);*/
    }

    // BY VITMAR J. ALIAGA
    public function getDocumentsByQuery(Request $request)
    {
    }
    public function viewFiles($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $url = secure_url(''); //Prod
                //$url = url('');//Dev
                $data = PurchasesData::viewFiles($id_pedido, $url);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
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
        return response()->json($jResponse);
    }
    public function addSeatsPurchases()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = Input::get("id_compra");
                $rpta = PurchasesData::addSeatsPurchases($id_compra);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addSeatsPurchasesInventories()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = Input::get("id_compra");
                $id_dinamica = Input::get("id_dinamica");
                $rpta = PurchasesData::addSeatsPurchasesInventories($id_compra, $id_dinamica);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listPurchasesExpenses()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_paso = Input::get("id_paso");
                $rpta = PurchasesData::listPurchasesExpenses($id_entidad, $id_paso);
                if (count($rpta) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listOrdersPurchaseDetails($id_pcompra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::listOrdersPurchaseDetails($id_pcompra);
                if (count($rpta) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The List";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addOrdersPurchaseDetailsGenerate($id_pcompra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //$id_pcompra = Input::get("id_pcompra");
                //$id_pedido = Input::get("id_pedido");
                $params = json_decode(file_get_contents("php://input"));
                $id_pedido = $params->id_pedido;
                $rpta = PurchasesData::addOrdersPurchaseDetailsGenerate($id_pcompra, $id_pedido);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addOrdersPurchaseDetails($id_pcompra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_almacen = Input::get("id_almacen");
                $id_articulo = Input::get("id_articulo");
                $id_tipoigv = Input::get("id_tipoigv");
                $cantidad = Input::get("cantidad");
                $precio = Input::get("precio");
                $rpta = PurchasesData::addOrdersPurchaseDetails($id_pcompra, $id_almacen, $id_articulo, $id_tipoigv, $cantidad, $precio);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function updateOrdersPurchaseDetails($id_pcompra, $id_cdetalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $cantidad = $params->cantidad;
                $precio = $params->precio;
                $rpta = PurchasesData::updateOrdersPurchaseDetails($id_cdetalle, $cantidad, $precio);
                if (count($rpta) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function deleteOrdersPurchaseDetails($id_pcompra, $id_cdetalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::deleteOrdersPurchaseDetails($id_cdetalle);
                if (is_array($rpta)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addOrdersPurchaseTemplate($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_plantilla = $params->id_plantilla;
                //$id_plantilla = Input::get("id_plantilla");
                $rpta = PurchasesData::addOrdersPurchaseTemplate($id_pedido, $id_plantilla);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addPurchasesDetailsGenerate($id_compra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_pcompra = $params->id_pcompra;
                $rpta = PurchasesData::addPurchasesDetailsGenerate($id_pcompra, $id_compra);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addPedidoFileCU($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $tipo_file = Input::get('tipo_file');
                $archivo = Input::file("archivo");
                $acuerdo = Input::get('acuerdo');
                $fecha = Input::get('fecha');
                $data = array(
                    "acuerdo"  => $acuerdo,
                    "fecha_entrega" => $fecha
                );
                $result = PurchasesData::updateOrdersRegisters($data, $id_pedido);
                if ($result) {
                    $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                    if ($objPedidoFile["success"]) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK,File Created';
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'The item does not created';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = $result;
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
    public function addPedidoFileAL($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $tipo_file = Input::get('tipo_file');
                $archivo = Input::file("archivo");
                $es_contrato = Input::get('es_contrato');
                $data = array(
                    "es_contrato"  => $es_contrato
                );
                $p_compra = PurchasesData::listPedidoCompraP($id_pedido);
                foreach ($p_compra as $key => $item) {
                    $id_pcompra = $item->id_pcompra;
                }
                PurchasesData::updatePedidoCompra($data, $id_pcompra);
                $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                // $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                if ($objPedidoFile["success"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK,File Created';
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not created';
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
    public function addPedidoFileQuotation($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_proveedor = Input::get('id_proveedor');
                $importe = Input::get('importe');
                $tipo_file = Input::get('tipo_file');
                $archivo = Input::file("cotizacion_file");
                // $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                $objPedidoFile = $this->privateUploadPedidoFile($id_pedido, null, $archivo, $tipo_file);
                if ($objPedidoFile["success"]) {
                    $id_cotizacion = PurchasesData::getMax('pedido_cotizacion', 'id_cotizacion') + 1;
                    $data = array(
                        "id_cotizacion"  => $id_cotizacion,
                        "id_pedido"  => $id_pedido,
                        "id_proveedor" => $id_proveedor,
                        "id_pfile" => $objPedidoFile["data"],
                        "importe" => $importe,
                        "es_elegido"  => 'N'
                    );
                    $result = PurchasesData::addOrdersCotizacion($data);
                    if ($result) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Error";
                        $jResponse['data'] = $result;
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not created';
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
    public function updatePedidoFileQuotation($id_pedido, $id_cotizacion)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $comentario = Input::get('comentario');
                $data = array(
                    "comentario"  => $comentario,
                    "es_elegido"  => 'S'
                );
                $result = PurchasesData::updateOrdersCotizacion($data, $id_cotizacion);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = $result;
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
    public function listPedidoFileQuotation($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $url = secure_url(''); //Prod
                //$url = url('');//Dev
                $rpta = PurchasesData::listPedidoFileQuotation($id_pedido, $url);
                if (count($rpta) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The List";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listPedidoFileQuotationSelected($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::listPedidoFileQuotationSelected($id_pedido);
                if (count($rpta) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The List";
                    $jResponse['data']    = $rpta;
                    $code                 = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listPurchasesSeatsAcounting($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $compra = PurchasesData::ShowCompraEmpresa($id_compra);
                foreach ($compra as $item) {
                    $id_anho = $item->id_anho;
                    $id_empresa = $item->id_empresa;
                }
                $data = PurchasesData::listPurchasesSeatsAcounting($id_compra, $id_anho, $id_empresa);
                $total = PurchasesData::listPurchasesSeatsAcountingTotal($id_compra);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ["data" => $data, "total" => $total];
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
    public function addSeatsPurchasesDynamic($id_compra)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_dinamica = Input::get("id_dinamica");

                if ($id_entidad == 7124 || $id_entidad == 9415) {
                    $rpta = PurchasesData::addSeatsPurchasesDynamic($id_compra, $id_dinamica);
                } else {
                    $rpta = PurchasesData::addSeatsPurchasesInventoriesUPN($id_compra, $id_dinamica);
                }

                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function addPurchasesSeatsImports(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $imp = 0;
        if ($valida == 'SI') {
            try {
                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        if ($row->dc == "C") {
                            $imp = abs($row->importe) * -1;
                        } else {
                            $imp = $row->importe;
                        }
                        $data = [
                            "id_compra" => $request->id_compra,
                            "id_fondo" => $row->fondo,
                            "id_cuentaaasi" => $row->cuenta,
                            "id_restriccion" => $row->restriccion,
                            "id_ctacte" => $row->cta_cte,
                            "id_depto" => $row->depto,
                            //"importe" => $row->importe,
                            "importe" => $imp,
                            "dc" => $row->dc,
                            "descripcion" => $row->glosa,
                            "editable" => 'S',
                            "agrupa" => 'N'
                        ];
                        $result = EliseoCompraAsiento::create($data);
                        // PurchasesData::addPurchasesSeats($data);
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
    public function updatePurchasesSeats($id_compra, $id_casiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = $this->request->input('id_compra');
                $id_cuentaaasi = $this->request->input('id_cuentaaasi');
                $id_restriccion = $this->request->input('id_restriccion');
                $id_ctacte = $this->request->input('id_ctacte');
                $id_fondo = $this->request->input('id_fondo');
                $id_depto = $this->request->input('id_depto');
                $importe = $this->request->input('importe');
                $descripcion = $this->request->input('descripcion');
                $dc = $this->request->input('dc');

                if ($dc == "C") {
                    $importe = abs($importe) * -1;
                } else {
                    $importe = abs($importe);
                }

                $dataCompraAsiento = [
                    'id_cuentaaasi' => $id_cuentaaasi, 'id_restriccion' => $id_restriccion, 'id_ctacte' => $id_ctacte, 'id_fondo' => $id_fondo, 'id_depto' => $id_depto, 'importe' => $importe, 'descripcion' => $descripcion, 'dc' => $dc
                ];
                PurchasesData::updateCompraAsiento($dataCompraAsiento, $id_casiento);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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
    public function getDetailOrderStatus($id_pedido)
    {

        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        //        dd($jResponse);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = $this->request->input('codigo');
                //                $dataPedido        = PurchasesData::showPedidoRegistro($id_pedido);
                $dataPedido = PurchasesData::getDetailOrderStatus($id_pedido, $codigo);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $dataPedido;
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
    public function getPurchasesStatus($id_pedido)
    {

        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = $this->request->input('codigo');
                $dataPedido = PurchasesData::getPurchasesStatus($id_pedido, $codigo);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $dataPedido;
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

    public function getAllOrderDetail($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        //        dd($jResponse);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //                $dataPedido        = PurchasesData::showPedidoRegistro($id_pedido);
                $url = secure_url(''); //Prod
                //$url = url('');//Dev
                $dataPedido = PurchasesData::getAllOrderDetail($id_pedido, $url);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = $dataPedido;
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
    public function addReceiptForFeesDetails($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultUtil = PurchasesUtil::dataReceiptForFeesDetails($id_compra);
                if ($resultUtil->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $resultUtil->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto addReceiptForFeesDetails;
                }
                $data = $resultUtil->data;
                $data["estado"] = "1";
                $signo = 1;
                $imp_det = Input::get('importe');
                $dif = PurchasesData::showPurchaseDif($id_compra, $imp_det);
                foreach ($dif as $key => $item) {
                    $signo = $item->signo;
                }
                if ($signo == 1 || $signo == 0) {
                    $result = EliseoCompraDetalle::create($data);
                    // $result = $this->pAddAnyEntity($data, "COMPRA_DETALLE", "ID_DETALLE", "addPurchasesDetails");
                    // if ($result["success"]) {
                    // REVISAR SI DEBE ACTUALIZAR EL DETALLE
                    // $data = PurchasesData::updateTotalCompra($id_compra);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $result;
                    $code = "200";
                    // } else {
                    //     $jResponse['success'] = false;
                    //     $jResponse['message'] = $result["message"];
                    //     $jResponse['data'] = [];
                    //     $code = "202";
                    // }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Importe MAYOR al Total del Comprobante";
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
        addReceiptForFeesDetails:
        return response()->json($jResponse, $code);
    }
    public function importPurchasesBalances(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                \Excel::load($request->excel, function ($reader) use ($id_user, $id_entidad, $id_depto, $id_anho) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($id_user, $id_entidad, $id_depto, $id_anho) {
                        $data = [
                            "id_entidad" => $id_entidad,
                            "id_depto" => $id_depto,
                            "id_anho" => $id_anho,
                            "id_compra" => $row->compra,
                            "id_moneda" => $row->moneda,
                            "id_persona" => $id_user,
                            "ruc" => $row->ruc,
                            "id_comprobante" => $row->tipo_comprobante,
                            "serie" => $row->serie,
                            "numero" => $row->numero,
                            "fecha_provision" => $row->fecha_provision,
                            "fecha_doc" => $row->fecha_doc,
                            "importe" => $row->importe,
                            "importe_me" => $row->importe_me
                        ];
                        $datos = PurchasesData::importPurchasesBalances($data);
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
    public function deleltePurchasesBalances($id_saldo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::deleltePurchasesBalances($id_saldo);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listPurchasesBalances(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');

                $data = PurchasesData::listPurchasesBalances($id_entidad, $id_depto, $id_anho);
                $total = 0; //PurchasesData::listPurchasesSeatsAcountingTotal($id_compra);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ["data" => $data, "total" => $total];
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
    public function showVoucherAutomatico(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_tipovoucher = $request->query('id_tipovoucher');
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', $tiene_params, $params);
                if ($rpta["nerror"] == 0) {
                    $id_anho = $rpta["id_anho"];
                }
                $data = PurchasesData::showVoucherAutomatico($id_entidad, $id_depto, $id_anho, $id_tipovoucher);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data[0];
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

    // trae la lista de arreglos por mes
    public function showArrangement(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $month = $request->query('month');
                if ($id_entidad == 7124) {
                    $data = PurchasesData::showArrangement($month);
                } else {
                    $data = PurchasesData::showArrangementByEntidadyDepto($id_entidad, $id_depto, $month);
                }
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
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

    // Eliminar arreglos
    public function deleteArrangement($id_arreglo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::deleteArrangement($id_arreglo);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function deleltePurchasesPending($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = PurchasesData::deleltePurchasesPending($id_pedido);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function showPurchasesPending($id_pedido)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesPending($id_pedido);
                foreach ($data as $key => $item) {
                    $id_compra = $item->id_compra;
                }
                if ($id_compra == 0) {
                    $eliminar = "N";
                } else {
                    $eliminar = "S";
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data']    = $eliminar;
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
    public function updatePurchasesDetails($id_compra, $id_detalle)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $cantidad = $params->cantidad;
                $importe = $params->importe;
                $rpta = PurchasesData::updatePurchasesDetails($id_compra, $id_detalle, $cantidad, $importe);
                if ($rpta['error'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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
    public function listMyPurchases(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = $request->query('id_compra');
                $compra = PurchasesData::listMyPurchases($id_compra);
                $detalle = PurchasesData::listMyPurchasesDetails($id_compra);
                $empresa = AccountingData::ShowEntidadEmpresa($id_entidad);
                foreach ($empresa as $item) {
                    $id_empresa = $item->id_empresa;
                }
                $asiento = PurchasesData::listMyPurchasesSeats($id_compra, $id_empresa);
                if ($compra) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ["compra" => $compra, "detalle" => $detalle, "asiento" => $asiento];
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
    public function showMyPurchases($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $detalle = PurchasesData::showMyPurchases($id_compra);
                if ($detalle) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $detalle[0];
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
    public function showPurchasesDetails($id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $detalle = PurchasesData::showDetalle($id_detalle);
                if ($detalle) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $detalle[0];
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
    public function lisPurchasesSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $detalle = PurchasesData::lisPurchasesSummary($id_voucher);
                $detalleD = PurchasesData::lisPurchasesSummaryD($id_voucher);

                $total = PurchasesData::lisPurchasesSummaryTotal($id_voucher);
                $totald = PurchasesData::lisPurchasesSummaryTotalD($id_voucher);
                if ($detalle) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ['items' => $detalle, 'total' => $total, 'itemsD' => $detalleD, 'totalD' => $totald];
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

    // para generar el pdf
    public function lisPurchasesSummaryPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');

                $n_vou      = $request->query('n_vou');
                $v_fecha    = $request->query('v_fecha');
                $lote       = $request->query('lote');

                $detalle = PurchasesData::lisPurchasesSummary($id_voucher);
                $detalleD = PurchasesData::lisPurchasesSummaryD($id_voucher);

                $total = PurchasesData::lisPurchasesSummaryTotal($id_voucher);
                $totald = PurchasesData::lisPurchasesSummaryTotalD($id_voucher);
                if ($detalle) {
                    $pdf = DOMPDF::loadView('pdf.purchases.summary', [
                        'detalle' => $detalle,
                        'total' => $total,
                        'detalleD' => $detalleD,
                        'totalD' => $totald,

                        'n_vou' => $n_vou,
                        'v_fecha' => $v_fecha,
                        'lote' => $lote,
                        'username' => $username // OBLIGATORIO
                    ])->setPaper('a4', 'portrait');

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);

        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];
        return response()->json($jResponse);
    }


    // pdf para shopping record
    public function listMyReportPurchasesPdf()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user    = $jResponse["id_user"];
        $username   =  $jResponse["email"];
        $data = [];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = Input::get('id_anho');
                $id_mes = Input::get('id_mes');
                $id_voucher = Input::get('id_voucher');
                $id_usua = Input::get('id_usua');

                $n_vou      = Input::get('n_vou');
                $v_fecha    = Input::get('v_fecha');
                $lote       = Input::get('lote');
                if (is_null(Input::get('text_search'))) {
                    $text_search = "";
                } else {
                    $text_search = Input::get('text_search');
                }
                $items = PurchasesData::listMyReportPurchases($id_anho, $id_mes, $id_entidad, $text_search, $id_voucher, $id_usua);
                $items_sum = PurchasesData::listMyReportPurchasesTotal($id_anho, $id_mes, $id_entidad, $id_voucher, $id_usua);
                if ($items) {
                    $customPaper = array(0, 0, 930, 1017);
                    $pdf = DOMPDF::loadView('pdf.purchases.shopping', [
                        'items' => $items,
                        'items_sum' => $items_sum,
                        'n_vou' => $n_vou,
                        'v_fecha' => $v_fecha,
                        'lote' => $lote,
                        'username' => $username // OBLIGATORIO
                        // ])->setPaper('a4', 'landscape'); 
                    ])->setPaper($customPaper, 'landscape');

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);

        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];

        return response()->json($jResponse);
    }


    public function lisPurchasesDetails(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $prov = PurchasesData::lisPurchasesDetailsProv($id_voucher);
                // $total = PurchasesData::lisPurchasesDetailsTotal($id_voucher);
                foreach ($prov as $key => $value) {
                    $details = PurchasesData::lisPurchasesDetails($id_voucher, $value->depto, $value->cuenta, $value->cuenta_cte);
                    $parent[] = [
                        'depto' => $value->depto,
                        'depto_n' => $value->depto_n,
                        'cuenta' => $value->cuenta,
                        'cuenta_n' => $value->cuenta_n,
                        'details' => $details
                    ];
                }

                if (count($prov) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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

    // para generar el pdf
    public function lisPurchasesDetailsPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');

                $mes = $request->query('mes');
                $fecha = $request->query('fecha');
                $n_vou      = $request->query('n_vou');
                $v_fecha    = $request->query('v_fecha');
                $lote       = $request->query('lote');

                /*$prov = PurchasesData::lisPurchasesDetailsProv($id_voucher);
                // $total = PurchasesData::lisPurchasesDetailsTotal($id_voucher);
                foreach ($prov as $key => $value) {
                    $details = PurchasesData::lisPurchasesDetails($id_voucher,$value->depto, $value->cuenta);
                    $parent[] = [
                        'depto' => $value->depto,
                        'depto_n' => $value->depto_n,
                        'cuenta' => $value->cuenta,
                        'cuenta_n' => $value->cuenta_n,
                        'details' => $details
                    ];
                }

                if ($parent){
                    $pdf = DOMPDF::loadView('pdf.purchases.details',[
                        'parent'=>$parent,
                        'mes'=>$mes,
                        'fecha'=>$fecha,
                        'n_vou'=>$n_vou,
                        'v_fecha'=>$v_fecha,
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
                }*/
                $data = PurchasesData::lisPurchasesDetails($id_voucher, '', '', '');

                $pdf = DOMPDF::loadView('pdf.purchases.details', [
                    'data' => $data,
                    'mes' => $mes,
                    'fecha' => $fecha,
                    'n_vou' => $n_vou,
                    'v_fecha' => $v_fecha,
                    'lote' => $lote,
                    'username' => $username // OBLIGATORIO
                ])->setPaper('a4', 'portrait');

                $doc =  base64_encode($pdf->stream('print.pdf'));

                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc],
                    'code' => "200",
                ];

                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);

        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];
        return response()->json($jResponse);
    }


    // para obtener los uusarios
    public function lisUsersVoucher(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $detalle = PurchasesData::lisUsersVoucher($id_voucher);
                if ($detalle) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $detalle;
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
    public function lisDebtsToPay(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $parent = [];
            try {

                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $tipo = $request->query('tipo');

                $cab = PurchasesData::DebtsToPay($id_entidad, $id_depto, $id_anho, $id_mes, $tipo);
                foreach ($cab as $key => $value) {
                    $row = PurchasesData::lisDebtsToPay($id_entidad, $id_depto, $id_anho, $id_mes, $value->id_proveedor, $tipo);
                    $parent[] = [
                        'ruc' => $value->proveedor,
                        'proveedor' => $value->documento,
                        'children' => $row
                    ];
                }
                if ($parent) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $parent;
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
    public function listWarehousesArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $cont = 0;
                //SE OBTIENE EL ALMACEN ASIGNADO
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    $url = secure_url(''); //Prod
                    //$url = url('');//Dev
                    $data = WarehousesData::listWarehousesArticlesFind($id_almacen, $id_anho, $dato, $url);
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
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listWarehousesArticlesAll(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            $per_page = $request->query('per_page');
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $cont = 0;
                //SE OBTIENE EL ALMACEN ASIGNADO
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    $url = secure_url(''); //Prod
                    //$url = url('');//Dev
                    $data = WarehousesData::listWarehousesArticlesFindPaginated($id_almacen, $id_anho, $dato, $url, $per_page);
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
    public function deleltePurchasesPreProvision($id_pedido)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //elimina registro sin provisionar
                $files = PurchasesData::getUrlByDeleteFile($id_pedido);
                $rpta = PurchasesData::deleltePurchasesPreProvision($id_entidad, $id_depto, $id_pedido);
                if ($rpta['error'] == 0) {  
                    foreach ($files as $file) {
                        $storage = new StorageController(); 
                        $storage->destroyFile($file -> url);
                    };
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data']    = [];
                    $code                 = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code                 = "202";
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

    public function addPurchasesDuplicated()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {


                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $code = "200";

                // $now = date('Y-m-d H:i:s');
                $now = date('Y-m-d');
                // $id_compra = null;
                $id_tipovoucher = 2;
                $compra = $this->request->compra;
                $detalle = $this->request->detalle;
                $asiento = $this->request->asiento;


                $serie = $compra['serie'];
                $numero = $compra['numero'];
                $id_comprobante = $compra['id_comprobante'];
                $id_proveedor = $compra['id_proveedor'];


                // verifica provisión
                $exist = PurchasesData::showExistsProviderDocumentProv($serie, $numero, $id_comprobante, $id_proveedor);
                if ($exist) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ERROR: El Documento ya esta PROVISIONADO";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }

                // obtiene año y mes
                $id_anho = null;
                $id_mes = null;
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', 'N', null);
                if ($rpta["nerror"] == 0) {
                    $id_anho = $rpta["id_anho"];
                    $id_mes = (int)$rpta["id_mes"];
                }

                $rpta = PurchasesData::validatedVoucher($id_entidad, $id_depto, $id_anho, $id_tipovoucher, $id_user);
                if ($rpta['error'] == 0) {
                    // nuevo ID de compra // Se generará con una secuencia.
                    // $id_compra = PurchasesData::getMax('compra', 'id_compra') + 1;

                    // $compra["id_compra"] = $id_compra;
                    $compra["id_entidad"] = $id_entidad;
                    $compra["id_depto"] = $id_depto;
                    $compra["id_persona"] = $id_user;
                    $compra["id_anho"] = $id_anho;
                    $compra["id_mes"] = $id_mes;
                    $compra["estado"] = "1";


                    $data = PurchasesData::showVoucherAutomatico($id_entidad, $id_depto, $id_anho, $id_tipovoucher);
                    foreach ($data as $key => $item) {
                        $id_tipoasiento = $item->id_tipoasiento;
                    }

                    //PKG_ACCOUNTING.SP_CREAR_VOUCHER(L_ID_ENTIDAD,L_ID_DEPTO,L_ID_ANHO,L_ID_MES,sysdate,L_ID_TIPOASIENTO,L_ID_TIPOVOUCHER,'','S',L_ID_PERSONA,L_ID_VOUCHER);
                    $rpta = PurchasesData::createdVoucher($id_entidad, $id_depto, $id_anho, $id_mes, $now, $id_tipoasiento, $id_tipovoucher, $id_user);
                    if ($rpta['error'] == 0) {
                        $compra["id_voucher"] = $rpta['id_voucher'];
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "ERROR: No se creo correctamente el voucher";
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }

                    //REGISTRA LA COMPRA
                    $compra = array_except($compra, ['nombre_doc', 'nombre_moneda', 'nombre_proveedor', 'nombre_ruc', 'nombre_simbolo']);
                    // $objCompra = PurchasesData::addCompra($compra);
                    $objCompra = EliseoCompra::create($compra);

                    //REGISTRA EL DETALLE
                    foreach ($detalle as $value) {
                        $value = array_except($value, ['tipo_igv', 'nombre_articulo', 'precio_venta', 'id_existencia']);
                        $value['id_compra'] = $objCompra->id_compra;
                        // CORREGIR AQUI SQ DET
                        // $value['id_detalle'] = PurchasesData::getMax('compra_detalle', 'id_detalle') + 1;
                        // PurchasesData::addCompraDetalle($value);
                        EliseoCompraDetalle::create($value);
                    }

                    //REGISTRAR EL ASIENTO
                    foreach ($asiento as $value) {
                        $value['id_asiento'] = null;
                        $value['id_origen'] = $objCompra->id_compra;
                        $value['voucher'] = $compra["id_voucher"];
                        PurchasesData::addContaAsiento($value);
                    }

                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = $objCompra;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data']    = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage() . '::Line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function getProyecto()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::getProyecto($id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
    public function getVale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $date = Carbon::now();
        $id_anho = $date->format('Y');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::getVale($id_entidad, $id_depto, $id_anho, $request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
    private function privateAddProyectoCompra($id_proyecto, $id_pcompra, $importe, $afecta_all)
    {
        try {
            $dataProyectoCompra = array(
                "id_proyecto"  => $id_proyecto,
                "id_pcompra" => $id_pcompra,
                "importe"    => $importe,
                "afecta_all"   => $afecta_all
            );
            $result = PurchasesData::addProyectoCompra($dataProyectoCompra);
            return ["success" => $result["success"], "message" => $result["message"], "data" => $result["data"]];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    public function getDetalleCompra(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::getDetalleCompra($id_entidad, $request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
    public function asientoCompra(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_compra = $request->id_compra;
                $data = PurchasesData::asientoCompra($id_compra);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
    public function showArrangementPurchases($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $purchases = PurchasesData::showArrangementPurchases($id_compra);
                $seats = PurchasesData::showArrangementSeatsPurchases($id_compra);
                $jResponse['success'] = true;
                if (count($purchases) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['purchases' => $purchases[0], 'seats' => $seats];
                    $code = "200";
                } else {
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

    public function getListAsientosByIdDinamica(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_dinamica = $request->id_dinamica;
                $data = PurchasesData::getListAsientosByIdDinamica($id_dinamica);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
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
}
