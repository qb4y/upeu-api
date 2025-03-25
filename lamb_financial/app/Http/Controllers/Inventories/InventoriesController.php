<?php

namespace App\Http\Controllers\Inventories;

use Exception;
use App\Http\Controllers\Controller;
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
use stdClass;

class InventoriesController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function listTypeOperations(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $tipo_mov = $request->query('tipo_mov');
            try {
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $data = InventoriesData::listTypeOperations($id_almacen, $tipo_mov);
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
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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
    public function listDocuments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_tipooperacion = $request->query('id_tipooperacion');
            try {
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $data = InventoriesData::listDocuments($id_almacen, $id_tipooperacion);
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
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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
    public function addInventoriesMovements()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
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

                $id_mes = 0;
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                /*if($id_almacen = 33){
                    $id_anho = 2021;// solo temporal logistica
                }*/

                if ($id_mes !== $id_mes_actual) {
                    throw new Exception("Alto, No existe un mes activo.", 1);
                }
                $id_almacen = 0;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) == 0) {
                    throw new Exception("Alto, El usuario no tiene un almacén asignado: " . $id_user, 1);
                }
                if (count($warehouse) > 1) {
                    throw new Exception("Alto, El usuario tiene mas de un almacén asignado: " . $id_user, 1);
                }

                $params = json_decode(file_get_contents("php://input"));
                $id_receta = $params->id_receta;
                $id_tipooperacion = $params->id_tipooperacion;
                $id_documento = $params->id_documento;
                $tipo = $params->tipo;
                $cantidad = $params->cantidad;
                $num_movimiento = $params->num_movimiento ?? null;
                $ip = GlobalMethods::ipClient($this->request);
                $tiene_dinamica = $params->tiene_dinamica ?? null;

                $id_movimiento = 0;
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO(:P_ID_ALMACEN, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, 
                        :P_ID_RECETA, :P_ID_PERSONA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_IP, :P_CANTIDAD, :P_GUIA, :P_ID_MOVIMIENTO,
                        :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
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
                if ($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }

                if ($tiene_dinamica == "N") {
                    $id_cuentaaasi = $params->id_cuentaaasi;
                    $id_restriccion = $params->id_restriccion;
                    $id_ctacte = $params->id_ctacte;
                    $id_depto = $params->id_depto;
                    $dc = "D";

                    $error = 0;
                    $msgerror = str_repeat("0", 300);
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO_ASIENTO(:P_ID_MOVIMIENTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION, :P_ID_CTACTE, :P_ID_DEPTO, :P_DC,
                    :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    }
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = ["id_movimiento" => $id_movimiento];
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addInventoriesDetails($id_movimiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_articulo = $params->id_articulo;
                $id_dinamica = $params->id_dinamica;
                $cantidad = $params->cantidad;
                $costo = $params->costo;
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_INVENTARIO_DETALLE(:P_ID_MOVIMIENTO, :P_ID_ARTICULO, :P_ID_DINAMICA, :P_CANTIDAD, 
                        :P_COSTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_COSTO', $costo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listInventoriesDetails($id_movimiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = InventoriesData::listInventoriesDetails($id_movimiento);
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
    public function showInventoriesDetails($id_movimiento, $id_movdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = InventoriesData::showInventoriesDetails($id_movimiento, $id_movdetalle);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function updateInventoriesDetails($id_movimiento, $id_movdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_articulo = $params->id_articulo;
                $id_dinamica = $params->id_dinamica;
                $cantidad = $params->cantidad;
                $costo = $params->costo;
                $error = 0;
                $msn_error = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_UPDATE_INVENTARIO_DETALLE(:P_ID_MOVDETALLE, :P_ID_MOVIMIENTO, :P_ID_ARTICULO, :P_ID_DINAMICA, :P_CANTIDAD, 
                        :P_COSTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVDETALLE', $id_movdetalle, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_COSTO', $costo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $msn_error;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
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
    public function deleteInventoriesDetails($id_movimiento, $id_movdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $error = 0;
                $msn_error = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_DELETE_INVENTARIO_DETALLE(:P_ID_MOVDETALLE, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVDETALLE', $id_movdetalle, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 1) {
                    DB::rollback();
                    throw new Exception($msn_error, 1);
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateInventoriesMovements($id_movimiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $pdo = DB::getPdo();
                $params = json_decode(file_get_contents("php://input"));
                $opc = $params->opc;
                if ($opc == "1") {
                    $id_receta = $params->id_receta;
                    $id_tipooperacion = $params->id_tipooperacion;
                    $id_documento = $params->id_documento;
                    $tipo = $params->tipo;
                    $cantidad = $params->cantidad;
                    $error = 0;
                    $msgerror = str_repeat("0", 300);
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_UPDATE_MOVIMIENTO(:P_ID_MOVIMIENTO,:P_ID_RECETA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_CANTIDAD, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_RECETA', $id_receta, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
                    $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    }
                    $tiene_dinamica = $params->tiene_dinamica;
                    if ($tiene_dinamica == "N") {
                        $id_cuentaaasi = $params->id_cuentaaasi;
                        $id_restriccion = $params->id_restriccion;
                        $id_ctacte = $params->id_ctacte;
                        $id_depto = $params->id_depto;
                        $dc = "D";

                        $error = 0;
                        $msgerror = str_repeat("0", 300);
                        $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO_ASIENTO(:P_ID_MOVIMIENTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION, :P_ID_CTACTE, :P_ID_DEPTO, :P_DC, :P_ERROR, :P_MSGERROR); end;");
                        $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                        $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                        $stmt->execute();
                        if ($error === 1) {
                            DB::rollback();
                            throw new Exception($msgerror, 1);
                        }
                    }
                } else if ($opc == "2") {
                    $traslado = $params->traslado ?? 'N';
                    $error = 0;
                    $msgerror = str_repeat("0", 300);
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_FINISH_MOVIMIENTO(:P_ID_MOVIMIENTO, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    } else {
                        if ($traslado == 'S') {
                            $data_traslado = json_decode($params->almacen);
                            $datos_insert = new stdClass();

                            $datos_insert->id_almacen = $data_traslado->id_almacen;
                            $datos_insert->id_movimiento_base = $id_movimiento;
                            $datos_insert->id_tipooperacion = $data_traslado->id_tipooperacion;
                            $datos_insert->id_dinamica = $data_traslado->id_dinamica;
                            $datos_insert->cantidad = $data_traslado->cantidad;
                            $datos_insert->costo = $data_traslado->costo;

                            $rptaTraslado = InventoriesData::trasladarArticulo($datos_insert);

                            if (!$rptaTraslado["success"]) {
                                DB::rollback();
                                throw new Exception($rptaTraslado["message"], 1);
                            }
                        }
                    }
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $id_movimiento;
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showWarehousesDocumentsOpertions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_almacen = $request->query('id_almacen');
                $tipo = $request->query('tipo');
                if ($tipo == "S") {
                    $id_tipooperacion = "11";
                } else {
                    $id_tipooperacion = "21";
                }
                $data = InventoriesData::listDocuments($id_almacen, $id_tipooperacion);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Almacen Origen No tiene Documento: SALIDA POR TRANSFERENCIA ENTRE ALMACENES, configurado';
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
    public function addInventoriesTransfers()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
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

                $id_mes = 0;
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }

                if ($id_mes !== $id_mes_actual) {
                    throw new Exception("Alto, No existe un mes activo.", 1);
                }

                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) == 0) {
                    throw new Exception("Alto, El usuario no tiene un almacén asignado: " . $id_user, 1);
                }
                if (count($warehouse) > 1) {
                    throw new Exception("Alto, El usuario tiene mas de un almacén asignado: " . $id_user, 1);
                }

                $data_s = InventoriesData::listDocuments($id_almacen, "11"); //Salida por Transferencia de Almacenes
                if (count($data_s) == 0) {
                    throw new Exception("Alto, Almacen Origen No tiene configurado Documento: SALIDA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                }
                foreach ($data_s as $item) {
                    $id_documento = $item->id_documento;
                }

                $id_almacen_destino = Input::get('id_almacen_destino');
                $data_i = InventoriesData::listDocuments($id_almacen_destino, "21"); //Entrada por Transferencia de Almacenes
                if (count($data_i) == 0) {
                    throw new Exception("Alto, Almacen Destino No tiene configurado Documento: ENTRADA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                }

                $id_receta = "";
                $id_tipooperacion = "11"; //SALIDA POR TRANSFERENCIA ENTRE ALMACENES 
                $tipo = "S"; //SALIDA DE ALMACENES
                $ip = GlobalMethods::ipClient($this->request);
                $id_movimiento = 0;
                $error = 0;
                $msgerror = "";
                for ($x = 1; $x <= 300; $x++) {
                    $msgerror .= "0";
                }

                $pdo = DB::getPdo();
                $cantidad = "";
                $num_movimiento = "";
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO(:P_ID_ALMACEN, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, 
                :P_ID_RECETA, :P_ID_PERSONA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_IP, :P_CANTIDAD, :P_GUIA, :P_ID_MOVIMIENTO,
                :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
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
                if ($error === 1) {
                    DB::rollback();
                    throw new Exception($msgerror, 1);
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = ["id_movimiento" => $id_movimiento];
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateInventoriesTransfers($id_movimiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $error = 0;
            $msn_error = "";
            DB::beginTransaction();
            try {
                $pdo = DB::getPdo();
                $params = json_decode(file_get_contents("php://input"));
                $opc = $params->opc;
                $id_almacen_destino = $params->id_almacen_destino;
                $id_dinamica = $params->id_dinamica;
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) == 0) {
                    throw new Exception("Alto, El usuario no tiene un almacén asignado: " . $id_user, 1);
                }
                if (count($warehouse) > 1) {
                    throw new Exception("Alto, El usuario tiene mas de un almacén asignado: " . $id_user, 1);
                }

                if ($opc == "1") {

                    $id_receta = "";
                    $id_tipooperacion = "11"; //SALIDA POR TRANSFERENCIA ENTRE ALMACENES 
                    $data_s = InventoriesData::listDocuments($id_almacen, $id_tipooperacion); //Salida por Transferencia de Almacenes
                    foreach ($data_s as $item) {
                        $id_documento = $item->id_documento;
                    }
                    if (count($data_s) == 0) {
                        throw new Exception("Alto, Almacen Origen No tiene configurado Documento: SALIDA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                    }

                    $data_i = InventoriesData::listDocuments($id_almacen_destino, "21"); //Entrada por Transferencia de Almacenes
                    if (count($data_i) == 0) {
                        throw new Exception("Alto, Almacen Destino No tiene configurado Documento: ENTRADA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                    }
                    $error = 0;
                    for ($x = 1; $x <= 300; $x++) {
                        $msgerror .= "0";
                    }
                    $tipo = "S";
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_UPDATE_MOVIMIENTO(:P_ID_MOVIMIENTO,:P_ID_RECETA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_RECETA', $id_receta, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 1) {
                        DB::rollback();
                        throw new Exception($msgerror, 1);
                    }
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $id_movimiento;
                    $code = "200";
                } else if ($opc == "2") {
                    $id_tipooperacion = "21"; // Entrada por Transferencia de Almacenes
                    $data_i = InventoriesData::listDocuments($id_almacen_destino, $id_tipooperacion); // Salida por Transferencia de Almacenes
                    foreach ($data_i as $item) {
                        $id_documento = $item->id_documento;
                    }
                    if (count($data_i) == 0) {
                        throw new Exception("Alto, Almacen Destino No tiene configurado Documento: ENTRADA POR TRANSFERENCIA ENTRE ALMACENES", 1);
                    }
                    $error = 0;
                    for ($x = 1; $x <= 300; $x++) {
                        $msn_error .= "0";
                    }
                    // $msn_error = "00000000000000000000000000000000000000000000000000";
                    $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_FINISH_MOVIMIENTO_TRANSF(:P_ID_MOVIMIENTO, :P_ID_ALMACEN, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_ID_DINAMICA, :P_ERROR, :P_MSN_ERROR); end;");
                    $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ALMACEN', $id_almacen_destino, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN_ERROR', $msn_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 1) {
                        DB::rollback();
                        throw new Exception($msn_error, 1);
                    }
                    // DB::rollback();
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was finished successfully";
                    $jResponse['data'] = $id_movimiento;
                    $code = "200";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listKardex(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_articulo = $request->query('id_articulo');
                $id_mes = $request->query('id_mes');
                $id_anho = $request->query('id_anho');
                $r_tipo = $request->query('r_tipo');
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }

                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {

                        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                        foreach ($data_anho as $item) {
                            if (empty($id_anho)) {
                                $id_anho = $item->id_anho;
                            }
                            $id_anho_actual = $item->id_anho_actual;
                        }

                        if ($r_tipo == "1") {
                            $kardex = InventoriesData::listKardex($id_anho, $id_almacen, $id_articulo, $id_mes);
                            $stock = InventoriesData::showStock($id_anho, $id_almacen, $id_articulo, $id_mes);
                        } else if ($r_tipo == "2") {
                            $kardex = InventoriesData::listKardexAll($id_anho, $id_almacen, $id_mes);
                            $stock = true;
                        }

                        if ($stock) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'OK';
                            $jResponse['data'] = ['kardex' => $kardex, 'total' => $stock[0]];
                            $code = "200";
                        } else {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'The item does not exist';
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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
    public function listCategoriesArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $per_page = "10";
                $dato = $request->query('dato');
                $per_page = $request->query('per_page');
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }

                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $data = InventoriesData::listCategoriesArticles($dato, $per_page);
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
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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
    public function listCategoriesWarehousesArticles($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                $data = InventoriesData::listCategoriesWarehousesArticles($id_almacen, $id_anho);
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
    public function addInventoriesDetailsRecipes($id_movimiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_dinamica = $params->id_dinamica;
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_DETALLE_RECETA(:P_ID_MOVIMIENTO, :P_ID_DINAMICA, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = [];
                    $code = "406";
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
    public function listStockArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_articulo = $request->query('id_articulo');
                $id_mes = $request->query('id_mes');
                $id_anho = $request->query('id_anho');
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }

                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {

                        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                        foreach ($data_anho as $item) {
                            if (empty($id_anho)) {
                                $id_anho = $item->id_anho;
                            }
                            $id_anho_actual = $item->id_anho_actual;
                        }

                        $stock = InventoriesData::listStockArticles($id_almacen, $id_anho, $id_mes, $id_articulo);
                        $datat = InventoriesData::listStockArticlesTotal($id_almacen, $id_anho, $id_mes, $id_articulo);
                        if ($stock) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'OK';
                            $jResponse['data'] = $stock;
                            $jResponse['data'] = ['items' => $stock, 'total' => $datat[0]];
                            $code = "200";
                        } else {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'The item does not exist';
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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
    public function listRecetaTipos(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $recetaTipos = InventoriesData::listRecetaTipos();
                if (count($recetaTipos) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $recetaTipos;
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


    public function listWerehouseType(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = InventoriesData::listWerehouseType();
                if (count($data) > 0) {
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

    public function listStockArticlesAll(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_articulo = $request->query('id_articulo');
                $id_mes = $request->query('id_mes');
                $id_anho = $request->query('id_anho');
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }

                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {

                        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                        foreach ($data_anho as $item) {
                            if (empty($id_anho)) {
                                $id_anho = $item->id_anho;
                            }
                            $id_anho_actual = $item->id_anho_actual;
                        }

                        $stock = InventoriesData::listStockArticlesAll($id_anho, $id_almacen, $id_articulo);
                        if ($stock) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'OK';
                            $jResponse['data'] = $stock;
                            $jResponse['data'] = ['items' => $stock];
                            $code = "200";
                        } else {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'The item does not exist';
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
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

    public function listMiscellaneousOutputs(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $almacen   = $request->query('almacen');
                $tipo   = $request->query('tipo');
                $s_all   = $request->query('s_all');
                $items = InventoriesData::listMiscellaneousOutputs($id_voucher, $almacen, $tipo, $s_all);
                $total = InventoriesData::listMiscellaneousOutputsTotal($id_voucher, $almacen, $tipo, $s_all);
                if (count($items) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $items, 'total' => $total[0]];
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

    // report pdf
    public function listMiscellaneousOutputspdf(Request $request)
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
                $almacen   = $request->query('id_almacen');
                $tipo   = $request->query('tipo');
                $s_all   = $request->query('s_all');

                $numero   = $request->query('numero');
                $lote   = $request->query('lote');
                $fecha   = $request->query('fecha');

                $items = InventoriesData::listMiscellaneousOutputs($id_voucher, $almacen, $tipo, $s_all);
                $total = InventoriesData::listMiscellaneousOutputsTotal($id_voucher, $almacen, $tipo, $s_all);
                if (count($items) > 0) {
                    $pdf = DOMPDF::loadView('pdf.inventories.outputs', [
                        'items' => $items,
                        'total' => $total[0],
                        'tipo' => $tipo,

                        'numero' => $numero,
                        'lote' => $lote,
                        'fecha' => $fecha,

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


    public function getALmacenReceta(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = InventoriesData::getALmacenReceta($request);
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
}
