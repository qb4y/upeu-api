<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Data\Purchases\PurchasesData;
use App\Http\Controllers\Purchases\Utils\PurchasesUtil;
use App\Http\Data\Setup\Process\Process;
use App\Http\Data\Treasury\IncomeData;
use App\Http\Data\Treasury\TreasuryData;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\Process\ProcessData;
use App\Http\Data\Treasury\ExpensesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Modulo\ModuloData;
use App\Models\CajaPagoFile as EliseoCajaPagoFile;
use App\Models\ProcessPasoRun as EliseoProcessPasoRun;
use App\Models\ProcessRun as EliseoProcessRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use PDO;
use DOMPDF;
use App\Http\Data\FinancesStudent\ComunData;
use function Sodium\add;
use App\Http\Data\Treasury\TaxDocumentsData;
use Carbon\Carbon;
// 

class ExpensesController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function addPago()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        $id_venta = 0;
                        try {
                            $params = json_decode(file_get_contents("php://input"));
                            $id_mediopago = 0; //validar si es cheque,tcl o efectivo
                            $id_chequera = 0; //validar
                            $id_voucher = $params->data->id_voucher;

                            $pdo = DB::getPdo();
                            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO(:ID_MEDIOPAGO, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_CTABANCARIA,:P_ID_CHEQUERA, :P_ID_VOUCHER, :P_ID_ANHO, :P_ID_MES, :P_ID_USER, :P_ID_PERSONA, :P_ID_TIPOTRANSACCION, :P_ID_MONEDA, :P_NUMERO, :P_FECHA, :P_GLOSA, :P_TIPOCAMBIO); end;");
                            $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_CTABANCARIA', $params->data->id_ctabancaria, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_ANHO', $params->data->id_articulo, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_MES', $params->data->id_articulo, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_USER', $id_venta, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_PERSONA', $params->data->id_articulo, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_TIPOTRANSACCION', $params->data->id_articulo, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_MONEDA', $params->data->id_almacen, PDO::PARAM_INT);
                            $stmt->bindParam(':P_NUMERO', $params->data->id_dinamica, PDO::PARAM_INT);
                            $stmt->bindParam(':P_FECHA', $params->data->detalle, PDO::PARAM_STR);
                            $stmt->bindParam(':P_GLOSA', $params->data->cantidad, PDO::PARAM_STR);
                            $stmt->bindParam(':P_TIPOCAMBIO', $params->data->precio, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                            $stmt->execute();

                            $jResponse['success'] = true;
                            $jResponse['message'] = "Succes";
                            $jResponse['data'] = $id_venta;
                            $code = "200";
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $error = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showPayments($idPayment)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $data = ExpensesData::listPayments($id_entidad,$id_depto,$id_mediopago,$id_voucher);
            $payment = ExpensesData::getPaymentToUpdateById($idPayment);
            $dataDetalle = ExpensesData::getPaymentCompraToUpdateById($idPayment);

            if (count($payment) > 0) {
                $data = [
                    "pago" => $payment[0],
                    "detalle" => $dataDetalle,
                ];

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

    public function showPaymentsExpenses($id_pgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $pago_gastos = ExpensesData::getPaymentExpencesById($id_pgasto);
            if (count($pago_gastos) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $pago_gastos[0];
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

    public function showPaymentsExpensesSeat($id_pgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $pago_gasto_asientos = ExpensesData::getPaymentExpencesSeatById($id_pgasto);
            if (count($pago_gasto_asientos) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $pago_gasto_asientos;
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

    public function deletePaymentsExpensesSeat($id_pgasto, $id_gasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $pago_gasto_asientos = ExpensesData::deletePaymentExpencesSeat($id_pgasto, $id_gasiento);
            // if (count($pago_gasto_asientos) > 0) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $pago_gasto_asientos;
            $code = "200";
            // } else {
            //     $jResponse['success'] = true;
            //     $jResponse['message'] = 'The item does not exist';
            //     $jResponse['data'] = [];
            //     $code = "202";
            // }
        }
        return response()->json($jResponse, $code);
    }

    public function listPayments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_mediopago = $request->query('id_mediopago');
            $id_voucher = $request->query('id_voucher');
            //ExpensesData::deletePagosIfEstadoIsCero($id_entidad, $id_depto, $id_voucher, $id_user);
            $data = ExpensesData::listPayments($id_entidad, $id_depto, $id_mediopago, $id_voucher);
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

    public function listPaymentsToVales(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $id_mediopago   = $request->query('id_mediopago');
            $id_voucher = $request->query('id_voucher');
            ExpensesData::deletePagosIfEstadoIsCero($id_entidad, $id_depto, $id_voucher, $id_user);
            $data = ExpensesData::listPaymentsToVales($id_entidad, $id_depto, $id_voucher);
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

    public function listPaymentsVoucher(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $parent = [];
            $id_mediopago = $request->query('id_mediopago');
            $id_voucher = $request->query('id_voucher');
            // dd($id_mediopago, $id_voucher);
            $data = ExpensesData::listPaymentsVoucher($id_mediopago, $id_voucher);
            foreach ($data as $key => $value) {
                $details = ExpensesData::listPaymentsDetailsVoucher($value->id_pago);
                $parent[] = [
                    'id_pago' => $value->id_pago,
                    'id_mediopago' => $value->id_mediopago,
                    'nombre' => $value->nombre,
                    'id_cuentaaasi' => $value->id_cuentaaasi,
                    'cuenta_corriente' => $value->cuenta_corriente,
                    'importe' => $value->importe,
                    'fecha' => $value->fecha,
                    'details' => $details
                ];
            }
            if (count($parent) > 0) {
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
        }
        return response()->json($jResponse, $code);
    }

    public function listPaymentsVoucherToVales(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $parent = [];
            // $id_mediopago   = $request->query('id_mediopago');
            $id_voucher = $request->query('id_voucher');
            $data = ExpensesData::listPaymentsVoucherToVales($id_voucher);
            foreach ($data as $key => $value) {
                $details = ExpensesData::listPaymentsDetailsVoucher($value->id_pago);
                $parent[] = [
                    'id_pago' => $value->id_pago,
                    'id_mediopago' => $value->id_mediopago,
                    'vale_numero' => $value->vale_numero,
                    'vale_detalle' => $value->vale_detalle,
                    'vale_fecha' => $value->vale_fecha,
                    'vale_nombre_empleado' => $value->vale_nombre_empleado,
                    'vale_importe' => $value->vale_importe,
                    'importe' => $value->importe,
                    'details' => $details
                ];
            }
            if (count($parent) > 0) {
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
        }
        return response()->json($jResponse, $code);
    }

    public function listPaymentsDetails($id_pago, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listPaymentsDetails($id_pago, $request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $jResponse['total'] = round(collect($data)->sum('importe'), 2);
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $jResponse['total'] = 0;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listPaymentsVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_voucher = $this->request->get('id_voucher');
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listPaymentsVale($id_vale, $id_voucher);
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
    public function listReportPaymentsVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listReportPaymentsVale($id_vale);
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

    public function addPayments()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                'id_voucher' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $params = json_decode(file_get_contents("php://input"));
            $id_mediopago = $params->id_mediopago;
            $id_ctabancaria = $params->id_ctabancaria;
            $id_chequera = $params->id_chequera;
            if (property_exists($params, 'id_vale')) {
                $id_vale = $params->id_vale;
            }
            $numero = $params->numero;
            $tipo_cambio = $params->tipo_cambio;
            $fecha = '';
            if ($id_mediopago === "001" || $id_mediopago === "008") { // 008=Tele Credito
                $fecha = property_exists($params, 'fecha') ? $params->fecha : date('y-m-d');
            }

            $id_tipotransaccion = property_exists($params, 'id_tipotransaccion') ? $params->id_tipotransaccion : null; // $params->id_tipotransaccion;
            $id_moneda = $params->id_moneda;
            $id_voucher = $params->id_voucher;

            //$rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,$tiene_params,$params);
            $voucher = AccountingData::showVoucher($id_voucher);
            if (count($voucher) > 0) {
                $id_anho = $voucher[0]->id_anho;
                $id_mes = $voucher[0]->id_mes;
                $id_pago = 0;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                try {
                    $pdo = DB::getPdo();
                    DB::beginTransaction();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO(:P_ID_MEDIOPAGO, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_VALE, :P_ID_VOUCHER, :P_ID_ANHO, :P_ID_MES, :P_ID_USER, :P_ID_TIPOTRANSACCION, :P_ID_MONEDA, :P_NUMERO, :P_FECHA, :P_TIPOCAMBIO, :P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TIPOCAMBIO', $tipo_cambio, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error == 0) {
                        DB::commit();
                        $pago = ExpensesData::showCajaPago($id_pago);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";
                        $jResponse['data'] = $pago[0];
                        $code = "200";
                    } else {
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = null;
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No se puede encontrar el voucher.";
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addPaymentsExpensesUPN()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $validador = Validator::make(Input::all(), [
                'fecha' => 'required',
                'id_pago' => 'required',
                'detalle' => 'required',
                'importe' => 'required|numeric',
                'importe_me' => 'numeric',
                'asientos_gastos' => 'required|array|min:1',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_pago = Input::get('id_pago');
            $id_dinamica = Input::get('id_dinamica');
            $id_persona = Input::get('id_persona');
            $detalle = Input::get('detalle');
            $importe = Input::get('importe');
            $importe_me = Input::get('importe_me');
            $fecha = Input::get('fecha');
            $asientos_gastos = Input::get('asientos_gastos');
            $id_pgasto = Input::get('id_pgasto');
            // $id_pgasto = 0;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                DB::beginTransaction();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_GASTO(:P_ID_PAGO, :P_ID_DINAMICA, :P_ID_PERSONA, :P_DETALLE,:P_IMPORTE,
                :P_IMPORTE_ME, :P_FECHA, :P_ID_PGASTO, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PGASTO', $id_pgasto, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    // $id_gasiento = 0;
                    foreach ($asientos_gastos as $asientos_gasto) {
                        // Guardar asientos
                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_GASTO_ASIENTO(
                        :P_ID_FONDO, :P_ID_DEPTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION,:P_ID_CTACTE,
                        :P_IMPORTE, :P_IMPORTE_ME, :P_IS_DC, :P_DESCRIPCION,:P_ID_PGASTO, :P_ERROR,
                        :P_ID_GASIENTO); end;");
                        $stmt->bindParam(':P_ID_FONDO', $asientos_gasto['id_fondo'], PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_DEPTO', $asientos_gasto['id_depto'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_CUENTAAASI', $asientos_gasto['id_cuentaaasi'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_RESTRICCION', $asientos_gasto['id_restriccion'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_CTACTE', $asientos_gasto['id_ctacte'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_IMPORTE', $asientos_gasto['importe'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_IMPORTE_ME', $asientos_gasto['importe_me'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_IS_DC', $asientos_gasto['is_dc'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_DESCRIPCION', $asientos_gasto['glosa'], PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_PGASTO', $id_pgasto, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_GASIENTO', $asientos_gasto['id_gasiento'], PDO::PARAM_INT);
                        $stmt->execute();

                        if ($error == 0) {
                            DB::commit();
                            $jResponse['success'] = true;
                            $jResponse['message'] = "Success";
                            $jResponse['data'] = $id_pago;
                            $code = "200";
                        } else {
                            DB::rollback();
                            $jResponse['success'] = false;
                            $jResponse['message'] = $msg_error;
                            $jResponse['data'] = null;
                            $code = "202";
                        }
                    }
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function listPaymentsToSmallBox()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            $text_search = Input::get('text_search');
            $id_voucher = Input::get('id_voucher');

            $data = ExpensesData::getPaymentsToSmallBox($id_voucher, $text_search);

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

    public function listPaymentsToSmallBoxSumary()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            // $text_search = Input::get('text_search');
            // $id_voucher = Input::get('id_voucher');

            $ingreso_total = ExpensesData::getPaymentsToSmallBoxIngresos($id_entidad, $id_depto);
            $egreso_total = ExpensesData::getPaymentsToSmallBoxEgresos($id_entidad, $id_depto);

            $data_r = [
                'ingreso_total' => $ingreso_total[0]->importe,
                'ingreso_total_me' => $ingreso_total[0]->importe_me,
                'egreso_total' => $egreso_total[0]->importe,
                'egreso_total_me' => $egreso_total[0]->importe_me,
                'saldo' => $ingreso_total[0]->importe - $egreso_total[0]->importe,
                'saldo_me' => $ingreso_total[0]->importe_me - $egreso_total[0]->importe_me,
            ];
            if ($data_r) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data_r;
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

    public function addPaymentsToSmallBox()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                'id_pago' => '',

                'id_voucher' => 'required',
                'id_tipotransaccion' => 'required',
                'id_dinamica' => 'required',
                // 'id_medio_pago' => 'required',
                'id_ctabancaria' => 'required',
                'numero' => 'required',
                'id_chequera' => 'required',
                'id_moneda' => 'required',
                'tipocambio' => 'required',
                'importe' => 'required|numeric',
                'importe_me' => 'numeric',

                'detalle' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_pago = Input::get('id_pago');
            $id_voucher = Input::get('id_voucher');
            $id_tipotransaccion = Input::get('id_tipotransaccion');
            $id_dinamica = Input::get('id_dinamica');
            // $id_medio_pago = Input::get('id_medio_pago');
            $id_ctabancaria = Input::get('id_ctabancaria');
            $numero = Input::get('numero');
            $id_chequera = Input::get('id_chequera');
            $id_moneda = Input::get('id_moneda');
            $tipocambio = Input::get('tipocambio');
            $detalle = Input::get('detalle');
            $importe = Input::get('importe');
            $importe_me = Input::get('importe_me');

            $voucher = AccountingData::showVoucher($id_voucher);
            if (count($voucher) > 0) {
                $id_anho = $voucher[0]->id_anho;
                $id_mes = $voucher[0]->id_mes;
            }
            // $fecha = Input::get('fecha');
            // $asientos_gastos = Input::get('asientos_gastos');
            $id_pago = 0;

            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                DB::beginTransaction();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_DEP_CAJACHICA(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_VOUCHER,
                                    :P_ID_ANHO, :P_ID_MES, :P_ID_USER, :P_ID_TIPOTRANSACCION,:P_ID_DINAMICA,:P_ID_CTABANCARIA,
                                    :P_NUMERO, :P_ID_CHEQUERA, :P_ID_MONEDA, :P_TIPOCAMBIO,
                                    :P_DETALLE,:P_IMPORTE,:P_IMPORTE_ME,
                                    :P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPOCAMBIO', $tipocambio, PDO::PARAM_STR);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_pago;
                    $code = "200";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addPaymentsExpenses()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];

            $params = (object)Input::get();
            // $file = Input::file('archivo_pdf');
            $id_pago = $params->id_pago;
            $id_dinamica = $params->id_dinamica == '0' ? '' : $params->id_dinamica;
            if (isset($params->id_persona)) {
                $id_persona = $params->id_persona;
            } else {
                $id_persona = null;
            }
            $detalle = $params->detalle;
            $importe = $params->importe;
            $importe_me = $params->importe_me;
            $fecha = $params->fecha;

            $id_pgasto = 0;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_GASTO(:P_ID_PAGO, :P_ID_DINAMICA,
                :P_ID_PERSONA, :P_DETALLE,:P_IMPORTE, :P_IMPORTE_ME, :P_FECHA, :P_ID_PGASTO, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PGASTO', $id_pgasto, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    $fileAdded = '';
                    if (!empty($params->id_vfile)) {
                        $update = DB::table('eliseo.caja_vale_file')->where('id_vfile', $params->id_vfile)->update(['id_pgasto' => $id_pgasto]);
                        if ($update) {
                            $fileAdded = 'Actualizado';
                        } else {
                            $fileAdded = 'Ocurrio un error al actualizar';
                        }
                    } else {
                        $archivo = Input::file('archivo_pdf');
                        if ($archivo) {
                            $params->id_pgasto = $id_pgasto;
                            $params->id_user = $id_user;
                            $fileAdded = $this->saveFileVale($params->id_vale, $params, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                        } else {
                            $fileAdded = 'Sin archivo';
                        }
                    }

                    // $fileAdded = null;
                    // if($file and isset($params->id_vale) and isset($params->tipo)){

                    //     $fileAdded = $this->privateUploadValeFile($params->id_vale, $file, $params->tipo, $params);
                    // }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_pgasto;
                    $jResponse['file'] = $fileAdded;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addPaymentsProviders()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_pago = $params->id_pago;
            $id_dinamica = $params->id_dinamica;
            $id_proveedor = '';
            if (!empty($params->id_proveedor)) {
                $id_proveedor = $params->id_proveedor;
            }
            $compras = $params->compras;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                foreach ($compras as $key => $item) {
                    $id_compra = $item->id_compra;
                    $importe = $item->importe;
                    $importe_me = $item->importe_me;
                    if (empty($params->id_proveedor)) {
                        $id_proveedor = $item->id_proveedor;
                    }
                    $id_detalle = 0; // *****???
                    //dd($id_compra);
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_COMPRA(:P_ID_PAGO, :P_ID_DINAMICA, :P_ID_PROVEEDOR, :P_ID_COMPRA, :P_IMPORTE, :P_IMPORTE_ME, :P_ID_PAGO_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_PAGO_COMPRA', $id_detalle, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                }

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $id_pago;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addPaymentsProvidersMany()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_pago = $params->id_pago;
            $id_dinamica = $params->id_dinamica;
            $compras = $params->compras;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                foreach ($compras as $key => $item) {
                    $id_compra = $item->id_compra;
                    $id_proveedor = $item->id_proveedor;
                    $importe = $item->importe;
                    $importe_me = $item->importe_me;

                    $id_detalle = $item->id_detalle;

                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_COMPRA(:P_ID_PAGO, :P_ID_DINAMICA, :P_ID_PROVEEDOR, :P_ID_COMPRA, :P_IMPORTE, :P_IMPORTE_ME, :P_ID_PAGO_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_PAGO_COMPRA', $id_detalle, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();

                    if ($error === 1) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    } else {
                        // Validar que no haya variación
                        $rspta = $this->addAjusteDiferenciaTipoPorTipoCambio($id_detalle, $id_user);
                        if ($rspta['success'] === false) {
                            $jResponse = $rspta;
                            $code = $rspta['code'];
                            goto end;
                        }
                    }
                }

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_pago;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
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

    private function addAjusteDiferenciaTipoPorTipoCambio($id_pago_compra, $id_user)
    {

        $jResponse = [];
        try {
            $error = 0;
            $msg_error = "";
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_VALIDAR_AJUSTE_PAGO_COMPRA(:P_ID_PAGO_COMPRA, :P_ID_PERSONA, :P_ERROR, :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_PAGO_COMPRA', $id_pago_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if ($error === 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
                goto end;
            }
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = [];
            $jResponse['code'] = "200";
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "202";
            goto end;
        }
        end:
        return $jResponse;
    }

    public function deletePayments($id_pago)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_PAGO(:P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function deletePaymentsDetails($id_pago, $id_detalle)
    {
        //        dd('deleting....');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $ps = [];
            $mesg = null;
            $id_pgasto = substr($id_detalle, 1); // obtener  el id_pgasto antes de que se elimine
            $object = DB::table('eliseo.caja_vale_file')->where('id_pgasto', $id_pgasto)->where('id_user', $id_user)->where('origen', '2')->select('nombre', 'tipo')->first(); // obtener 
            $resp = '';
            try {
                $tipo = 0; // Solo uno o muchos items
                $place = $this->request->query('place'); // de donde esta eliminar { 1 : del proceso, 2: de la lista}
                $codigo = $this->request->query('code'); // codigo del proceso
                $prov = $this->request->query('remProc'); // is quiere anulzar vale proces

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_PAGO_DETALLE(:P_ID_PAGO, :P_ID_DETALLE,:P_TIPO, :P_PLACE); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DETALLE', $id_detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->bindParam(':P_PLACE', $place, PDO::PARAM_INT);
                $stmt->execute();

                if (!empty($object)) { /// se eliminara el archivo si existe y el usuario sea el mismo quien lo registro
                    $directorio = 'data_api_treasury/vales/';
                    $carpeta = '';
                    if ($object->tipo == "1") {
                        $carpeta = $directorio . 'convenios';
                    } elseif ($object->tipo == "2") {
                        $carpeta = $directorio . 'constancia-depositos';
                    } else if ($object->tipo == "3") {
                        $carpeta = $directorio . 'sustentos-gastos';
                    } else if ($object->tipo == "4") {
                        $carpeta = $directorio . 'voucher-depositos';
                    }
                    $resp = ComunData::deleteFilesDirectorio($carpeta, $object->nombre, 'E'); // para eliminar el archivo del sevidor
                }

                $pago = collect(ExpensesData::showCajaPago($id_pago))->first();
                if ($pago and $prov and $mesg == 0) {
                    $ps = ProcessData::nullingStepOperation($pago->id_vale, $codigo);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Eliminado" . ', ' . $resp;
                $jResponse['data'] = $ps;
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

    public function deletePaymentsToSmallBox($id_pago)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $pdo = DB::getPdo();
            DB::beginTransaction();
            try {
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_PAGO_DEPOSITO(:P_ID_PAGO); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->execute();
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_pago;
                $code = "200";
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

    public function addPaymentsCustomers(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $params = json_decode(file_get_contents("php://input"));
            $id_pago = $request->id_pago;
            $id_dinamica = $request->id_dinamica;
            $id_cliente = $request->id_cliente;
            $ventas = $request->ventas;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                foreach ($ventas as $key => $it) {
                    $item = (object)$it;
                    $id_venta = $item->id_venta;
                    $importe = $item->importe;
                    $importe_me = $item->importe_me;

                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_VENTA(:P_ID_PAGO, :P_ID_DINAMICA, :P_ID_CLIENTE, :P_ID_VENTA, :P_IMPORTE, :P_IMPORTE_ME, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                }

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $id_pago;
                    // $jResponse['file'] = $resultFile;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    /*
    public function finalizarRendicionValeUPN($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {

            $validador = Validator::make(Input::all(),  [
                'id_vale' => 'required',
                'id_dinamica' => 'required',
                'id_voucher_ren' => 'required',
                'compras' => 'required|array|min:1',
            ]);
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['result'] = '';
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $jResponse = [];
            $id_dinamica = Input::get('id_dinamica');
            $compras = Input::get('compras');
            $id_voucher_ren = Input::get('id_voucher_ren');
            try {

                $error = 0;
                $msg_error = "";
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                foreach ($compras as $key => $item) {
                    $id_compra = $item['id_compra'];
                    $id_proveedor = $item['id_proveedor'];
                    $importe = $item['importe'];
                    $importe_me = $item['importe_me'];
                    $id_vale_compra = $item['id_detalle'];

                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_VALE_COMPRA(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_PROVEEDOR, :P_ID_COMPRA, :P_IMPORTE, :P_IMPORTE_ME, :P_ID_VALE_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_VALE_COMPRA', $id_vale_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();

                    if($error === 1) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                }


                $msg_error = "";
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }

                // Finalizar rendición de vale.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_RENDIR_VALE(:P_ID_VALE, :P_ID_VOUCHER_REN, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VOUCHER_REN', $id_voucher_ren, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();

                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_vale;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);

    }
    */
    public function finalizarRendicionValeUpn($id_pago)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                // Finalizar rendición de vale.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_RENDIR_VALE(:P_ID_PAGO,
                :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();

                if ($error === 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_pago;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function updatePayments($id_pago)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                /*
                $validador = Validator::make(Input::all(),  [
                    'id_tipotransaccion' => 'required',
                ]);
                if($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validador->errors()->first();
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                */

                $params = json_decode(file_get_contents("php://input"));
                $opc = $params->opc;
                $codigo = property_exists($params, 'codigo') ? $params->codigo : null;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                if ($opc == "1") {
                    $id_ctabancaria = $params->id_ctabancaria;
                    $id_chequera = $params->id_chequera;
                    $id_vale = property_exists($params, 'id_vale') ? $params->id_vale : null;
                    $numero = $params->numero;
                    $id_tipotransaccion = property_exists($params, 'id_tipotransaccion') ? $params->id_tipotransaccion : null;
                    $id_moneda = $params->id_moneda;
                    $tipo_cambio = $params->tipo_cambio;
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_ACTUALIZAR_PAGO(:P_ID_PAGO, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_VALE, :P_ID_TIPOTRANSACCION, :P_ID_MONEDA, :P_TIPO_CAMBIO, :P_NUMERO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_TIPO_CAMBIO', $tipo_cambio, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error == 0) {
                        $pago = ExpensesData::showCajaPago($id_pago);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";
                        $jResponse['data'] = $pago[0];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    if ($opc == "2") {
                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_FINALIZAR_PAGO(:P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                        $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                        $stmt->execute();
                        if ($error === 0) {
                            // para los documentos no fiscales
                            $documentos = DB::table('eliseo.caja_pago_gasto as a')
                                ->join('eliseo.caja_documento as b', 'a.id_pgasto', '=', 'b.id_pgasto')->where('a.id_pago', '=', $id_pago)->select('b.id_documento')->get();
                            if (count($documentos) > 0) {
                                foreach ($documentos as $ite) {
                                    $items = (object)$ite;
                                    $codigo_proceso = 'PRODOC';
                                    $update = DB::table('eliseo.caja_documento')->where('id_documento', '=', $items->id_documento)->update([
                                        'codigo' => $codigo_proceso,
                                    ]);
                                    $addProceso = TaxDocumentsData::insertProceso($items->id_documento, $id_user, $codigo_proceso, $fecha_reg);
                                }
                            }


                            $pago = ExpensesData::showCajaPago($id_pago)[0];
                            if (property_exists($pago, 'id_vale') and $pago->id_vale) {
                                $data = [
                                    "codigo" => $codigo,
                                    "id_pedido" => $pago->id_vale,
                                    "id_persona" => $id_user,
                                    "id_entidad" => $id_entidad,
                                    "detalle" => 'Vale rendido',
                                    "ip" => $clientIP
                                ];
                                $data_procces = [
                                    "estado" => '1',
                                ];
                                $result = PurchasesData::spProcessStepRunNext($data);
                                $process_run = Process::getProcessRunByIdOperation($pago->id_vale, $codigo);
                                if (property_exists($process_run, 'id_registro')) {
                                    $isUpdateProces = Process::updateProcessRun($process_run->id_registro, $data_procces);
                                }
                                $updateVale = DB::table('eliseo.caja_vale')->where('id_vale', '=', $pago->id_vale)->update([
                                    'rendido' => 'S',
                                ]);
                            }
                            $jResponse['success'] = true;
                            $jResponse['message'] = "Success";
                            $jResponse['data'] = [];
                            $code = "200";
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $msg_error;
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "ERROR: No se ha Finalizado el Pago";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
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

    public function listTypeVale()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listTypeVale();
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

    public function addValeUPN()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            // foreach ($data_anho as $item) {
            //     $id_anho = $item->id_anho;
            //     $id_anho_actual = $item->id_anho_actual;
            // }
            // if ($id_anho !== $id_anho_actual) {
            //     $jResponse['success'] = false;
            //     $jResponse['message'] = "Alto, no existe un año activo.";
            //     $jResponse['data'] = NULL;
            //     $code = "202";
            //     goto end;
            // }

            $validador = Validator::make(Input::all(), [
                'id_voucher' => 'required',
                'id_tipovale' => 'required',
                'id_dinamica' => 'required',
                'id_medio_pago' => 'required',
                'id_ctabancaria' => '',
                'id_chequera' => '',
                'id_persona' => 'required',
                'id_moneda' => 'required',
                'tipocambio' => '',
                'fecha' => 'required',
                'importe' => 'numeric',
                'importe_me' => 'numeric',
                'detalle' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_voucher = Input::get('id_voucher');
            $voucher = AccountingData::showVoucher($id_voucher);
            if (count($voucher) === 1) {
                $id_anho = $voucher[0]->id_anho;
                $id_mes = $voucher[0]->id_mes;
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "La operación no esta asignada a un voucher, revise tener un voucher asignado.";
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_vale = 0;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }

                $id_tipovale = Input::get('id_tipovale');
                $id_dinamica = Input::get('id_dinamica');
                $id_medio_pago = Input::get('id_medio_pago');
                $id_ctabancaria = Input::get('id_ctabancaria');
                $id_numero = Input::get('numero'); /////////.
                $id_chequera = Input::get('id_chequera'); /////////.
                $id_voucher = Input::get('id_voucher'); /////////.
                $id_persona = Input::get('id_persona');
                $id_cuentaaasi = Input::get('id_cuentaaasi');
                $id_moneda = Input::get('id_moneda');
                $tipocambio = Input::get('tipocambio');
                $fecha = Input::get('fecha');
                $importe = Input::get('importe');
                $importe_me = Input::get('importe_me');
                $detalle = Input::get('detalle');
                $celular = Input::get('celular');
                $email = Input::get('email');
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_ACTUALIZAR_VALE(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES,:P_ID_TIPOVALE, :P_ID_DINAMICA,
                            :P_ID_MEDIOPAGO, :P_ID_CTABANCARIA, :P_NUMERO, :P_ID_CHEQUERA,:P_ID_VOUCHER, :P_ID_PERSONA, :P_ID_EMPLEADO, :P_ID_CUENTAAASI,
                            :P_ID_MONEDA, :P_TIPOCAMBIO, :P_FECHA, :P_IMPORTE, :P_IMPORTE_ME,
                            :P_DETALLE, :P_CELULAR, :P_EMAIL, :P_ID_VALE, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOVALE', $id_tipovale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_medio_pago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_NUMERO', $id_numero, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_EMPLEADO', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPOCAMBIO', $tipocambio, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CELULAR', $celular, PDO::PARAM_STR);
                $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [];
                    $code = "201";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
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

    //my-vales----------

    public function addMyVale()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $cont = 0;
        if ($valida == 'SI') {
            $jResponse = [];
            // DB::beginTransaction();
            $params = (object)Input::get();

            if ($id_depto == '1') {
                $counValid = DB::table('enoc.vw_trabajador')
                ->where('id_persona', '=', $params->responsable)
                ->where('id_situacion_trabajador', '=', '1')
                ->whereIn('id_condicion_laboral', ['M', 'E'])
                ->count();
            } else {
                $counValid = 1;
            }

            if ($counValid > 0) {

                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                if ($id_anho == $id_anho_actual) {
                    $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                    foreach ($data_mes as $item) {
                        $id_mes = $item->id_mes;
                        $id_mes_actual = $item->id_mes_actual;
                    }
                    if ($id_mes == $id_mes_actual) {
                        $tipcam = GlobalMethods::verificaTipoCambio();
    
                        if ($params->coin == 7) {
                            $tipcam["tc"] = true;
                        }
    
                        if ($tipcam["tc"] == true) {
                            $id_vale = 0;
                            try {
                                $pdo = DB::getPdo();
                                $stmt = $pdo->prepare("begin PKG_CAJA.SP_REGISTRA_VALE(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES,:P_ID_TIPOVALE, :P_ID_PERSONA, :P_ID_EMPLEADO,
                                        :P_ID_MONEDA, :P_FECHA, :P_IMPORTE, :P_IMPORTE_ME, :P_DETALLE, :P_CELULAR, :P_EMAIL, :P_ID_VALE, :P_FECHA_VENCIMIENTO, :P_TERMINO_CONDICION); end;");
                                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_TIPOVALE', $params->type_voucher, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_EMPLEADO', $params->responsable, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MONEDA', $params->coin, PDO::PARAM_INT);
                                $stmt->bindParam(':P_FECHA', $params->fecha, PDO::PARAM_STR);
                                $stmt->bindParam(':P_IMPORTE', $params->importe, PDO::PARAM_STR);
                                $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                $stmt->bindParam(':P_DETALLE', $params->detalle, PDO::PARAM_STR);
                                $stmt->bindParam(':P_CELULAR', $params->celular, PDO::PARAM_STR);
                                $stmt->bindParam(':P_EMAIL', $params->email, PDO::PARAM_STR);
                                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                                $stmt->bindParam(':P_FECHA_VENCIMIENTO', $params->fecha_venc, PDO::PARAM_STR);
                                $stmt->bindParam(':P_TERMINO_CONDICION', $params->terminos_condiciones, PDO::PARAM_STR);
                                $stmt->execute();
    
                                /// Create by Cristian
                                $resultFile = 'Sin file';
                                $archivo = Input::file('archivo_pdf');
                                if ($archivo) {
                                    $resultFile = $this->saveFileVale($id_vale, $params, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                                }
                                /////////////////////////
                                //SUBE EL FILE
                                // $resultFile = $this->privateUploadValeFile($id_vale, Input::file('archivo_pdf'), $params->tipo, $params);
    
                                //REGISTRA EL PROCESO DE EJECUCION
                                //                            $proceso = ProcessData::showProcessByCode($params->codigo);
                                //                            $procesoFlujo = PurchasesData::showProcessFlujoByProcesoTipoPaso($proceso->id_proceso, 3);
                                //                            $dProcessFlujo = new class{};
                                //                            $dProcessFlujo->id_paso = $dataPF->id_paso; // 139; // $dataPF->id_paso;
                                //                            $dProcessFlujo->id_proceso = $dataP->id_proceso;
                                //                            $data = PurchasesData::privateListProcessFlujoByParent(array($dProcessFlujo));
                                //                            dd($data);
                                //REGISTRA PASOS DEL PROCESO
                                //                            ProcessData::addRegistroPaso($id_user,$id_registro,$proceso->id_proceso,$id_paso,$id_vale);
                                //                            dd($params);
                                //                            $rrrr = PurchasesUtil::dataOrdersRegistriesUso($params->pasos);
                                $steps = json_decode($params->pasos);
                                if ($steps) {
                                    $lastStep = end($steps);
                                    list($listPaso, $cantPasos, $llave) = array($steps, count($steps), $lastStep->llave_componente);
                                } else {
                                    list($listPaso, $cantPasos, $llave) = array(array(), 0, '');
                                }
    
                                //                            dd($params);
                                if ($params->codigo == '8') {
                                    $result2 = ExpensesController::privateAddsProcessRunsAndSteps($id_vale, $listPaso, $id_user, $id_entidad, $params->codigo);
                                }
                                $cont++;
                                //                            dd($rrrr,'-->', $listPaso, $cantPasos, $llave);
                                //                            $addProcess = PurchasesController::privateAddsProcessRunsAndSteps('234', $listPaso,$id_user,$id_entidad,$params->codigo)
                                $id_persona =$params->responsable;
                                /*
                                $pdf = ExpensesData::termCondVale($id_persona, 'S');
    
    
                                $value_pdf = $pdf->download()->getOriginalContent();
                                $folder           = $params->carpeta;
                                $carpeta = $folder . 'term-cond';
                                $name = $carpeta.'/' . date('mdYHis') . uniqid() . '.pdf';
                                $path2 = \Storage::cloud()->put($name, $value_pdf);
    
                                ExpensesData::editTermConVale($id_vale,$name);
                                */
                                $jResponse['success'] = true;
                                $jResponse['message'] = "Succes";
                                $jResponse['data'] = $id_vale;
                                $jResponse['contador'] = $cont;
                                $jResponse['file'] = $resultFile;
                                $code = "200";
                                // DB::commit();
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $error = "202";
                            }
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "No Existe Mes Activo!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Año Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "La condición laboral del trabajador debe ser de (Empleado o Misionero) y debe estar activo";
                $jResponse['data'] = [];
                $code = "202";
            }
            // dd($params);
        }
        //        dd('asdfa', $params, $jResponse);
        return response()->json($jResponse, $code);
    }

    public  function saveFileVale($id_vale, $params, $archivo)
    { //'// antes de cambiar recordar que se esta usando en varios lugares
        // print_r($archivo);
        $formato          = $archivo->getClientOriginalExtension();
        // $name_file             = $archivo->getClientOriginalName();
        $size             = filesize($archivo);
        $folder           = $params->carpeta; //data_api_treasury/vales/
        $carpeta = '';
        $fileAdjunto['nerror'] = 1;
        $tipo = $params->tipo;
        $estado = "1";
        if ($tipo == "1") {
            $carpeta = $folder . 'convenios';
        } elseif ($tipo == "2") {
            $carpeta = $folder . 'constancia-depositos';
        } else if ($tipo == "3") {
            $carpeta = $folder . 'sustentos-gastos';
        } else if ($tipo == "4") {
            $carpeta = $folder . 'voucher-depositos';
        }

        $storage = new StorageController();
        $fileAdjunto = $storage->postFile($archivo, $carpeta);
        $nombre = explode("/", $fileAdjunto['data'])[4];
        if ($fileAdjunto['success']) {
            $result = $this->privateAddValeFile($id_vale, $nombre, $formato, $fileAdjunto['data'], $tipo, $estado, $size, $params);
            if ($result['success']) {
                $fileAdjunto = [
                    'nerror' => 0,
                    'message' => 'Creado',
                    'data' => $result['data'],
                ];
            } else {
                $fileAdjunto = [
                    'nerror' => 1,
                    'message' => 'Fallo',
                    'data' => '',
                ];
            }
        } else {
            $fileAdjunto = [
                'nerror' => 1,
                'message' => 'No se pudo crear',
                'data' => '',
            ];
        }
        return $fileAdjunto;
    }

    private function privateAddsProcessRunsAndSteps($id_pedido, $pasos, $id_persona, $id_entidad, $codigo)
    {
        try {
            // $id_proceso = $this->id_proceso;

            $dataP = PurchasesData::showProcessByCodigo($codigo, $id_entidad);
            $dataProcessRun = [
                "id_proceso" => $dataP->id_proceso, // $id_proceso,
                "id_operacion" => $id_pedido,
                "fecha" => DB::raw('sysdate'),
                "detalle" => "order",
                "estado" => "0"
            ];
            //            dd('ww',$dataP, 'COD', $codigo);
            if ($dataP->codigo == '8') {
                $result = ExpensesController::privateAddProcessRun($dataProcessRun);
            }

            if ($result) {
                $clientIP = \Request::ip();
                $cant = count($pasos);
                $i = 0;

                foreach ($pasos as $key => $value) {
                    // $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
                    $i++;
                    if ($i < $cant) {
                        //                        dd('__',$value);
                        //                        dd('value', $id_detalle,$result["id_registro"], $value["id_paso"], $value,$id_persona,$clientIP);


                        $dataProcessPasoRun = array(
                            // "id_detalle" => $id_detalle,
                            "id_registro" => $result->id_registro,
                            "id_paso" => $value->id_paso,
                            "id_persona" => $id_persona,
                            "fecha" => DB::raw('sysdate'),
                            // "detalle"       => xxx,  NOMBRE DEL STEP
                            // "numero"        => xxx, ???. dejarlo
                            "revisado" => "0", // AL REVISAR EL PASO SE DEBE CAMBIAR A '1'. que es cuando esta revisado...
                            "ip" => $clientIP,
                            "estado" => "1",
                            "id_paso_next" => $value->id_paso_next
                        );
                        // $estado = "1";
                    } else {
                        //                        dd($dataP, 'ELSE');
                        $dataProcessPasoRun = array(
                            // "id_detalle" => $id_detalle,
                            "id_registro" => $result->id_registro,
                            "id_paso" => $value->id_paso,
                            "id_persona" => $id_persona,
                            "fecha" => DB::raw('sysdate'),
                            // "detalle"       => xxx,  NOMBRE DEL STEP
                            // "numero"        => xxx, ???. dejarlo
                            "revisado" => "0", // AL REVISAR EL PASO SE DEBE CAMBIAR A '1'. que es cuando esta revisado...
                            "ip" => $clientIP,
                            "estado" => "0",
                            "id_paso_next" => $value->id_paso_next
                        );
                        // $estado = "0";
                    }
                    $id_paso_actual = $value->id_paso;
                    $result2 = EliseoProcessPasoRun::create($dataProcessPasoRun);
                    // $result2 = ExpensesController::privateAddProcessPasoRun($dataProcessPasoRun);
                }
                $data = ["id_paso_actual" => $id_paso_actual];
                $result3 = PurchasesData::updateProcessRun($data, $result->id_registro);
                // updateProcessRun($data,$id_registro)
                //                dd('->>:(',$result);
                return $result;
            }

            return false;
        } catch (Exception $e) {
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
            $success = EliseoProcessRun::create($data);
            //            dd('RPR', $data, $success);
            return $success;
        } catch (Exception $e) {
            // ($e->getMessage());
            return false;
        }
    }

    /* PROCESS_PASO_RUN */

    // private function privateAddProcessPasoRun($data)
    // {
    //     try {
    //         $id_detalle = PurchasesData::getMax('process_paso_run', 'id_detalle') + 1;
    //         $data = array_merge(array("id_detalle" => $id_detalle), $data);
    //         $success = PurchasesData::addProcessPasoRun($data);
    //         if ($success)
    //             return $data;
    //         else
    //             return false;
    //     } catch (Exception $e) {
    //         // echo $e->getMessage();
    //         return false;
    //     }
    // }

    /*public function update1Vale($id_vale){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ACTUALIZA_VALE(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_MEDIOPAGO, :P_CTA_BANCARIA, :P_ID_CTABANCARIA, :ID_CHEQUERA); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $params->data->id_dinamica, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $params->data->id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_CTA_BANCARIA', $params->data->cta_bancaria, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTABANCARIA', $params->data->id_ctabancaria, PDO::PARAM_STR);
                $stmt->bindParam(':ID_CHEQUERA', $params->data->id_cheuqera, PDO::PARAM_STR);
                $stmt->execute();

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_vale;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse,$code);
    }*/
    public function autorizaMyVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $data = json_decode(json_encode($params), true);
                foreach ($data as $clave => $valor) {
                    if ($clave == "id_dinamica") {
                        $id_dinamica = $valor;
                    }
                    if ($clave == "id_mediopago") {
                        $id_mediopago = $valor;
                    }
                    if ($clave == "cta_bancaria") {
                        $cta_bancaria = $valor;
                    }
                    if ($clave == "id_cuentaaasi") {
                        $id_cuentaaasi = $valor;
                    }
                    if ($clave == "id_ctacte") {
                        $id_ctacte = $valor;
                    }
                    if ($clave == "id_depto") {
                        $id_depto = $valor;
                    }
                    if ($clave == "id_restriccion") {
                        $id_restriccion = $valor;
                    }
                    if ($clave == "codigo") {
                        $codigo = $valor;
                    }
                    if ($clave == "detalle") {
                        $detalle = $valor;
                    }
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_AUTORIZA_VALE(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_MEDIOPAGO, :P_CTA_BANCARIA, :P_ID_CUENTAAASI, :P_ID_CTACTE, :P_ID_DEPTO, :P_ID_RESTRICCION); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_CTA_BANCARIA', $cta_bancaria, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
                $stmt->execute();

                if ($id_vale) {
                    $data = [
                        "codigo" => $codigo,
                        "id_pedido" => $id_vale,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => $detalle,
                        "ip" => $clientIP
                    ];
                    $result = PurchasesData::spProcessStepRunNext($data);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_vale;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function provisionarMyVale($IdVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_chequera = Input::get('id_chequera');
                $objetRequest = (object)Input::get();
                $params = $this->request->all(); //getting client body params to update
                $constanciaFile = $params['archivo_pdf'];
                $constanciaType = $params['tipo'];
                $param = $objetRequest;
                unset($params['archivo_pdf']);
                unset($params['tipo']);
                unset($params['carpeta']);
                $codigo = 8; // codigo flujo 8 para gestion de vales;
                $datasteps = [
                    "codigo" => $codigo,
                    //"id_pedido" => $vale->id_vale,
                    "id_persona" => $id_user,
                    "id_entidad" => $id_entidad,
                    "ip" => $clientIP
                ];


                $vale = ExpensesData::getVale($IdVale);
                if ($vale) {
                    if ($vale->id_tipovale == '1') { // a rendir hacer junto a la funcionalidad de provisionado!!!!!!
                        //                        dd('hereee', $vale->expired);

                        // dd($params);
                        $data = array_filter($params);
                        $data["id_cajero"] = $id_user;
                        $vale = ExpensesData::updateVale($IdVale, $data);
                        $data = [
                            "codigo" => $codigo,
                            "id_pedido" => $vale->id_vale,
                            "id_persona" => $id_user,
                            "id_entidad" => $id_entidad,
                            "detalle" => 'Vale Provisionado',
                            "ip" => $clientIP
                        ];
                        $data_procces = [
                            "estado" => '1',
                        ];

                        // Create by Cristian 
                        $resultFile = 'Sin file';
                        $archivo = Input::file('archivo_pdf');
                        if ($archivo) {
                            // dd($IdVale, $param, $archivo);
                            $resultFile = $this->saveFileVale($IdVale, $param, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                        }
                        ///////////////////
                        // $resultFile = $this->privateUploadValeFile($IdVale, $constanciaFile, $constanciaType, $param);

                        $resultStep = PurchasesData::spProcessStepRunNext(array_merge($datasteps, [
                            "id_pedido" => $vale->id_vale,
                            "detalle" => 'Vale por rendir ',
                        ]));
                        $resultStep = PurchasesData::spProcessStepRunNext(array_merge($datasteps, [
                            "id_pedido" => $vale->id_vale,
                            "detalle" => 'Vale Provicionado'
                        ]));
                        //                            dd('vale vencido', $vale, $llave, 'res', $data);

                    } else { // a cuenta persona
                        //                        dd('herere');

                        // updating vale
                        $data = array_filter($params);
                        $data["id_cajero"] = $id_user;
                        $vale = ExpensesData::updateVale($IdVale, $data);

                        // Create by Cristian
                        $resultFile = 'Sin file';
                        $archivo = Input::file('archivo_pdf');
                        if ($archivo) {
                            // dd($IdVale, $params, $archivo);
                            $resultFile = $this->saveFileVale($IdVale, $param, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                        }
                        ///////////////////
                        // $resultFile = $this->privateUploadValeFile($IdVale, $constanciaFile, $constanciaType, $param);
                        $resultStep = PurchasesData::spProcessStepRunNext(array_merge($datasteps, [
                            "id_pedido" => $vale->id_vale,
                            "detalle" => 'Vale Provicionado',
                        ]));

                        $params = array(
                            'pagado' => '1',
                        );

                        // update pagado
                        $data = ExpensesData::updateVale($vale->id_vale, $params);


                        $data_procces = [
                            "estado" => '1',
                        ];
                        // update procesrundetal to end
                        $process_run = Process::getProcessRunByIdOperation($vale->id_vale, $codigo);

                        if (property_exists($process_run, 'id_registro')) {
                            //finalizar procesos
                            $isUpdateProces = Process::updateProcessRun($process_run->id_registro, $data_procces);
                            // finalizar paso
                            $resultStep = PurchasesData::spProcessStepRunNext(array_merge($datasteps, [
                                "id_pedido" => $vale->id_vale,
                                "detalle" => 'Vale Finalizado',
                                "llave" => 'FIN'
                            ]));
                        }
                    }
                    // Generar Asiento en Contablidad
                    //Llamar a un Procedure Stored
                    // verificar si vencido
                    //                    $asiento = ExpensesData::spContaAsientoVale($IdVale);
                    /*$datasteps = [
                        "codigo" => '8',
                        "id_pedido" => $vale->id_vale,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => 'Provisionado',
                        "ip" => $clientIP
                    ];
                    $resultStep = PurchasesData::spProcessStepRunNext($datasteps);*/

                    // Generar Asiento en Contablidad
                    //Llamar a un Procedure Stored
                    $asiento = ExpensesData::spContaAsientoVale($IdVale);
                }
                if ($resultStep) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = array('file' => $resultFile, 'step' => $resultStep);
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = null;
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


    public function listMyVales(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        $codigo = $request->query('codigo');
        $llave = $request->query('llave');
        $lillave = $llave ? explode(',', $llave) : [];
        $responsable = $request->query('responsable');
        $page_size = $request->query('page_size', 10);
        $mediospago = $request->query('mediospago');
        $month = $request->query('month');
        $year = $request->query('year');
        $wu = $request->query('wu');
        $all = $request->all;
        $q = $request->q;
        // $id_user = (isset($wu) and $wu = true) ? '' : $id_user;
        if (!empty($request->all) and $request->all == 'S') { // significa que mostrara todos los regsitros
            $id_user = '';
        }
        // dd($id_user);
        $limediospago = $mediospago ? explode(',', $mediospago) : [];

        $params = (object)array(
            'id_entidad' => $id_entidad,
            'id_depto' => $id_depto,
            'id_user' => $id_user,
            'codigo' => $codigo,
            'llave' => $lillave,
            'responsable' => $responsable,
            'mediospago' => $limediospago,
            'page_size' => $page_size,
            'month' => $month,
            'year' => $year,
            'q' => $q,
            'voucher' => $request->get('voucher'),
            'state' => $request->get('state')
        );
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listMyVale($params);
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

    public function listValesClientes()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listValesClientes($id_entidad, $id_depto);
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

    public function listValesCliente(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        $persona = $request->query('id_persona');
        $page_size = $request->query('page_size', 10);

        $params = (object)array(
            'id_entidad' => $id_entidad,
            'id_depto' => $id_depto,
            'id_user' => $id_user,
            'page_size' => $page_size,
            'id_persona' => $persona
        );
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listValesCliente($params);
            //            dd('->> jeje',$data);
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

    public function listValeFiles($idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listValeFiles($idVale);
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

    //my-vales----------
    public function addVale()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        $id_vale = 0;
                        try {
                            $params = json_decode(file_get_contents("php://input"));
                            $id_proceso = $params->data->id_proceso;
                            $id_paso = $params->data->id_paso;
                            $pdo = DB::getPdo();
                            $stmt = $pdo->prepare("begin PKG_CAJA.SP_REGISTRA_VALE(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES,:P_ID_TIPOVALE, :P_ID_PERSONA, :P_ID_EMPLEADO,
                                    :P_ID_MONEDA, :P_FECHA, :P_IMPORTE, :P_IMPORTE_ME, :P_DETALLE, :P_CELULAR, :P_EMAIL, :P_ID_VALE); end;");
                            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_TIPOVALE', $params->data->id_tipovale, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_EMPLEADO', $params->data->id_persona, PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_MONEDA', $params->data->id_moneda, PDO::PARAM_INT);
                            $stmt->bindParam(':P_FECHA', $params->data->fecha, PDO::PARAM_STR);
                            $stmt->bindParam(':P_IMPORTE', $params->data->importe, PDO::PARAM_STR);
                            $stmt->bindParam(':P_IMPORTE_ME', $params->data->importe_me, PDO::PARAM_STR);
                            $stmt->bindParam(':P_DETALLE', $params->data->detalle, PDO::PARAM_STR);
                            $stmt->bindParam(':P_CELULAR', $params->data->celular, PDO::PARAM_STR);
                            $stmt->bindParam(':P_EMAIL', $params->data->email, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                            $stmt->execute();
                            //REGISTRA EL PROCESO DE EJECUCION
                            //$id_registro = ProcessData::addRegistro($id_proceso,$id_vale);
                            //REGISTRA PASOS DEL PROCESO
                            //SProcessData::addRegistroPaso($id_user,$id_registro,$id_proceso,$id_paso,$id_vale);
                            $jResponse['success'] = true;
                            $jResponse['message'] = "Success";
                            $jResponse['data'] = $id_vale;
                            $code = "200";
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $error = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function update1Vale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ACTUALIZA_VALE(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_MEDIOPAGO, :P_CTA_BANCARIA, :P_ID_CTABANCARIA, :ID_CHEQUERA); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $params->data->id_dinamica, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $params->data->id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_CTA_BANCARIA', $params->data->cta_bancaria, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTABANCARIA', $params->data->id_ctabancaria, PDO::PARAM_STR);
                $stmt->bindParam(':ID_CHEQUERA', $params->data->id_cheuqera, PDO::PARAM_STR);
                $stmt->execute();

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_vale;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function autorizaVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        //        dd('stteasd');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $params = json_decode(file_get_contents("php://input"));
                /*$data = json_decode(json_encode($params),true);
                foreach($data as $clave => $valor){
                    if($clave == "id_dinamica"){
                        $id_dinamica = $valor;
                    }
                    if($clave == "id_mediopago"){
                        $id_mediopago = $valor;
                    }
                    if($clave == "cta_bancaria"){
                        $cta_bancaria = $valor;
                    }
                    if($clave == "id_cuentaaasi"){
                        $id_cuentaaasi = $valor;
                    }
                    if($clave == "id_ctacte"){
                        $id_ctacte = $valor;
                    }
                    if($clave == "id_depto"){
                        $id_depto = $valor;
                    }
                    if($clave == "id_restriccion"){
                        $id_restriccion = $valor;
                    }
                }*/
                $id_dinamica = $params->id_dinamica;
                $id_mediopago = $params->id_mediopago;
                $id_ctabancaria = $params->id_ctabancaria;
                $cta_bancaria = $params->cta_bancaria;
                $fecha_venc = $params->fecha_venc;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_AUTORIZA_VALE(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_PERSONA_AUTO, :P_ID_MEDIOPAGO,:P_ID_CTABANCARIA, :P_CTA_BANCARIA, :P_FECHA_VENC, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA_AUTO', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_CTA_BANCARIA', $cta_bancaria, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_VENC', $fecha_venc, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "ik";
                    $jResponse['data'] = $id_vale;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function executeOperationProcessRun()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $id_operacion = Input::get('id_operacion');
                $codigo = Input::get('codigo');
                $detalle = Input::get('detalle');
                $vale = ExpensesData::getVale($id_operacion);
                //dd('less', $vale);
                //if ($vale and $vale->id_tipovale === '1') {
                //  $llave = 'FVPA';
                //} else {
                $llave = 'FVPR';
                //}
                //                dd($vale);
                $dataReg = [
                    "codigo" => $codigo,
                    "id_pedido" => $id_operacion,
                    "id_persona" => $id_user,
                    "id_entidad" => $id_entidad,
                    "detalle" => $detalle,
                    "ip" => $clientIP,
                ];
                $dataAut = [
                    "codigo" => $codigo,
                    "id_pedido" => $id_operacion,
                    "id_persona" => $id_user,
                    "id_entidad" => $id_entidad,
                    "detalle" => $detalle,
                    "ip" => $clientIP,
                    "llave" => $llave,
                ];
                //                    dd($vale, 'data', $data);
                //                    if($vale and $vale->id_tipovale){
                //                        if($vale->id_tipovale == '1'){
                //                            array_push($data, (object)array('llave'=>'FVPA'));
                //                        }
                //                        if($vale->id_tipovale == '2'){
                //                            array_push($data, (object)['llave'=>'FVPR']);
                //                        }
                //                    }

                //                dd($data);

                //                    dd('AUTHOR', $data);
                $result = PurchasesData::spProcessStepRunNext($dataReg);
                $result = PurchasesData::spProcessStepRunNext($dataAut);

                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
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
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function payVale($idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $llave = Input::get('llave');
                $codigo = Input::get('codigo');
                $vale = ExpensesData::getVale($idVale);
                if ($vale->id_tipovale == '1') {
                    // a rendir hacer junto a la funcionalidad de provisionado!!!!!!
                    //                    dd($vale, $codigo);
                    if (!$vale->pagado or $vale->pagado == '0') { // PAGARR
                        //                        dd('pagando');
                        $data = [
                            "codigo" => $codigo,
                            "id_pedido" => $vale->id_vale,
                            "id_persona" => $id_user,
                            "id_entidad" => $id_entidad,
                            "detalle" => 'Vale pagado',
                            "ip" => $clientIP,
                        ];
                        /*$data_procces = [
                            "estado" => '1',
                        ];*/
                        //                    dd($data);
                        $steprun = PurchasesData::spProcessStepRunNext($data);
                        $params = array(
                            'pagado' => '1',
                        );
                        $data = ExpensesData::updateVale($idVale, $params);
                    } else { // ANALIZAR-
                        //                        dd('ya pagado', $vale);
                        //                        dd('ANALIZARRR!!!');
                        if ($vale->expired == '1') { // VALE EXPIRADO -> IR A PROVISIONAR
                            $data = [
                                "codigo" => $codigo,
                                "id_pedido" => $vale->id_vale,
                                "id_persona" => $id_user,
                                "id_entidad" => $id_entidad,
                                "detalle" => 'Vale pagado',
                                "llave" => 'FVPR',
                            ];
                            $steprun = PurchasesData::spProcessStepRunNext($data);
                        } else { //-> finalizar
                            //                            dd('holiss');
                            $data = [
                                "codigo" => $codigo,
                                "id_pedido" => $vale->id_vale,
                                "id_persona" => $id_user,
                                "id_entidad" => $id_entidad,
                                "detalle" => 'Vale provisionado',
                                "ip" => $clientIP,
                                //                                "llave" => 'FIN'
                            ];
                            $data_procces = [
                                "estado" => '1',
                            ];
                            $steprun = PurchasesData::spProcessStepRunNext($data);
                            $process_run = Process::getProcessRunByIdOperation($vale->id_vale, $codigo);
                            if (property_exists($process_run, 'id_registro')) {
                                $st = Process::updateProcessRun($process_run->id_registro, $data_procces);
                            } else {
                                $process_run = null;
                            }
                        }
                    }


                    /*if ($vale->expired == '1') { //vale vencido segun fecha de caducidad
                        /*$data = [
                            "codigo" => $codigo,
                            "id_pedido" => $vale->id_vale,
                            "id_persona" => $id_user,
                            "id_entidad" => $id_entidad,
                            "detalle" => 'Vale Expirado,Pagado',
                            "ip" => $clientIP,
                            "llave" => 'FVPR'
                        ];
                        /*$data_procces = [
                            "estado" => '1',
                        ];
                        $steprun = PurchasesData::spProcessStepRunNext($data);
                        $params = array(
                            'pagado' => '1',
                        );

                        $data = ExpensesData::updateVale($idVale, $params);*/
                    //                        $process_run = Process::getProcessRunByIdOperation($vale->id_vale, $codigo);
                    //                        if(property_exists($process_run, 'id_registro')){
                    //                            $isUpdateProces = Process::updateProcessRun($process_run->id_registro, $data_procces);
                    //                        }
                    //raul jonata tola


                    /*$dataAut = [
                        "codigo"    => $codigo,
                        "id_pedido" => $vale->id_vale,
                        "id_persona"=> $id_user,
                        "id_entidad"=> $id_entidad,
                        "detalle"   => 'Pagado, Provisionado',
                        "ip"        => $clientIP,
                        "llave"        => 'FIN',
                    ];

                    $fresult = PurchasesData::spProcessStepRunNext($dataAut);
//                        dd('Vale a rendir->vale vencido::IR AL PROVISIONAR', $vale, $llave, 'res', $data);
                } else { //vale activo segun fecha de caducidad

                    $dataAut = [
                        "codigo" => $codigo,
                        "id_pedido" => $vale->id_vale,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => 'Pagado, Provisionado',
                        "ip" => $clientIP,
//                            "llave" => 'FVPR',
                    ];
                    // excute stem run next
                    $steprun = PurchasesData::spProcessStepRunNext($dataAut);
                    $params = array(
                        'pagado' => '1',
                    );

                    // update pagado
                    $data = ExpensesData::updateVale($idVale, $params);
                    $data_procces = [
                        "estado" => '1',
                    ];

                    // update procesrundetal to end
                    $process_run = Process::getProcessRunByIdOperation($vale->id_vale, $codigo);
                    if (property_exists($process_run, 'id_registro')) {
                        $isUpdateProces = Process::updateProcessRun($process_run->id_registro, $data_procces);
                    }
//                        dd('Vale a rendir->vale permitido :: TERMINAR', $vale, $llave, 'res', $dataAut);


                }*/
                }
                if ($vale->id_tipovale == '2') {
                    // a cuenta persona A/C

                    $dataAut = [
                        "codigo" => $codigo,
                        "id_pedido" => $vale->id_vale,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => 'Pagado, Provisionado',
                        "ip" => $clientIP,
                        "llave" => 'FIN',
                    ];
                    //                    dd('Vale a cuenta personal', $vale, $llave, 'res', $dataAut);
                    //                    $fresult = PurchasesData::spProcessStepRunNext($dataAut);

                    $params = array(
                        'pagado' => '1',
                    );
                    //                    $data = ExpensesData::updateVale($idVale, $params);
                }


                /*if($llave=='FVPA'){
//                    $vencimiento = new DateTime($vale->fecha_vencimiento);
                    if($vale->expired =='0'){ //vale vencido segun fecha de caducidad
                        $dataAut = [
                            "codigo"    => $codigo,
                            "id_pedido" => $vale->id_vale,
                            "id_persona"=> $id_user,
                            "id_entidad"=> $id_entidad,
                            "detalle"   => 'Pagado, Provisionado',
                            "ip"        => $clientIP,
                            "llave"        => 'FIN',
                        ];
//                        dd('111',$vale, $llave, 'res',$dataAut);
                        $fresult = PurchasesData::spProcessStepRunNext($dataAut);
                        
                    }else{ //vale activo segun fecha de caducidad

                        $dataAut = [
                            "codigo"    => $codigo,
                            "id_pedido" => $vale->id_vale,
                            "id_persona"=> $id_user,
                            "id_entidad"=> $id_entidad,
                            "detalle"   => 'Pagado, Provisionado',
                            "ip"        => $clientIP,
                            "llave"        => 'FVPR',
                        ];
//                        dd('111',$vale, $llave, 'res',$dataAut);
                        $fresult = PurchasesData::spProcessStepRunNext($dataAut);
                        
                    }

//                    dd($vencimiento);

                }*/
                /*if($llave=='FVPR'){
                    $dataAut = [
                        "codigo"    => $codigo,
                        "id_pedido" => $vale->id_vale,
                        "id_persona"=> $id_user,
                        "id_entidad"=> $id_entidad,
                        "detalle"   => 'Pagado, Provisionado',
                        "ip"        => $clientIP,
                        "llave"        => 'FIN',
                    ];
                    dd('2222',$vale, $llave,'res',$dataAut);
                    $fresult = PurchasesData::spProcessStepRunNext($dataAut);

                    $params = array(
                        'pagado' => '1',
                    );
                    $data = ExpensesData::updateVale($idVale, $params);
                }*/

                // change pago in caja_vale to 1 PAGADO
                //                dd($vale, $llave);


                if ($steprun) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $steprun;
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

    public function autorizaMyValeWQ($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $data = json_decode(json_encode($params), true);
                foreach ($data as $clave => $valor) {
                    if ($clave == "id_dinamica") {
                        $id_dinamica = $valor;
                    }
                    if ($clave == "id_mediopago") {
                        $id_mediopago = $valor;
                    }
                    if ($clave == "cta_bancaria") {
                        $cta_bancaria = $valor;
                    }
                    if ($clave == "id_cuentaaasi") {
                        $id_cuentaaasi = $valor;
                    }
                    if ($clave == "id_ctacte") {
                        $id_ctacte = $valor;
                    }
                    if ($clave == "id_depto") {
                        $id_depto = $valor;
                    }
                    if ($clave == "id_restriccion") {
                        $id_restriccion = $valor;
                    }
                    if ($clave == "codigo") {
                        $codigo = $valor;
                    }
                    if ($clave == "detalle") {
                        $detalle = $valor;
                    }
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_AUTORIZA_VALE(:P_ID_VALE, :P_ID_DINAMICA, :P_ID_MEDIOPAGO, :P_CTA_BANCARIA, :P_ID_CUENTAAASI, :P_ID_CTACTE, :P_ID_DEPTO, :P_ID_RESTRICCION); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_CTA_BANCARIA', $cta_bancaria, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
                $stmt->execute();

                if ($id_vale) {
                    $data = [
                        "codigo" => $codigo,
                        "id_pedido" => $id_vale,
                        "id_persona" => $id_user,
                        "id_entidad" => $id_entidad,
                        "detalle" => $detalle,
                        "ip" => $clientIP
                    ];
                    $result = PurchasesData::spProcessStepRunNext($data);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_vale;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listVales(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $id_paso = $request->query('id_paso');
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
            }
            $data = ProcessData::showSteps($id_paso);
            foreach ($data as $item) {
                $id_tipopaso = $item->id_tipopaso;
            }
            if ($id_tipopaso == 5) { //VALIDA SI EL PASO ES DE SALIDA OUTPUT
                $data = ProcessData::showFlowsNext($id_paso);
                foreach ($data as $item) {
                    $id_proceso = $item->id_proceso;
                    $id_paso = $item->id_paso;
                }
            } else {
                $data = ExpensesData::verPaso($id_paso);
                foreach ($data as $item) {
                    $id_proceso = $item->id_proceso;
                    $id_paso = $item->id_paso;
                }
            }

            $data = ExpensesData::listVale($id_entidad, $id_depto, $id_anho, $id_proceso, $id_paso);
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

    public function listValesUPN(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $id_voucher = $request->query('id_voucher');
            $text_search = $request->query('text_search');
            $jResponse = [];
            $data = ExpensesData::listValeByIdvoucher($id_voucher, $text_search);
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

    public function listDepositsToValesUPN(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $data=[];
            $id_voucher = $request->query('id_voucher');
            // ExpensesData::deletePagosIfEstadoIsCero($id_entidad,$id_depto,$id_voucher);
            $data = ExpensesData::listDepositsToVales($id_entidad, $id_depto, $id_voucher);
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

    public function deleteDepositsToValesUPN(Request $request, $id_deposito)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // Eliminar asientos deposito
                ExpensesData::deleteContaAsiento(7, $id_deposito);
                // Eliminar deposito
                ExpensesData::deleteCajaDeposito($id_deposito);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listSaldoValesUPN(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $id_voucher = $request->query('id_voucher');
            $text_search = $request->query('text_search');
            $jResponse = [];
            $data = ExpensesData::listSaldoValeByIdvoucher($id_entidad, $id_depto, $id_user, $id_voucher, $text_search);
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

    public function listValesAccounting($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listValesAccounting($id_vale, $id_entidad);
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

    public function listValesARendirUPN(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            // $id_voucher = $request->query('id_voucher');
            $text_search = $request->query('text_search') || '';
            $jResponse = [];
            $data = ExpensesData::listValeARendir($id_entidad, $id_depto, $text_search);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
            }
        }
        return response()->json($jResponse, $code);
    }

    public function editVoucherAccountingEntries($IdVale)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = $this->request->all();
                $data = array_filter($params);
                $result = ExpensesData::editVoucherAccountingEntries($IdVale, $data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getValeUPN(Request $request, $idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::getValeById($idVale);
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
        }
        return response()->json($jResponse, $code);
    }

    public function showVoucherAccountingEntries($IdVale)
    {

        //        dd(0sadfasdfsdfasd);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //                $id_asiento = Input::get('id_asiento');
                $result = ExpensesData::showVoucherAccountingEntries($IdVale, $id_entidad);

                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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

    public function updateValeUPN(Request $request, $idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                // 'id_voucher' => 'required',
                'id_tipovale' => 'required',
                'id_dinamica' => 'required',
                // 'id_medio_pago' => 'required',
                'id_ctabancaria' => '',
                'id_chequera' => '',
                'id_persona' => 'required',
                'id_moneda' => 'required',
                'tipocambio' => '',
                'fecha' => 'required',
                'importe' => 'numeric',
                'importe_me' => 'numeric',
                'detalle' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }

                $id_anho = 0; // No se editará
                $id_mes = 0; // No se editará
                $id_tipovale = Input::get('id_tipovale');
                $id_dinamica = Input::get('id_dinamica');
                $id_medio_pago = Input::get('id_medio_pago');
                $id_ctabancaria = Input::get('id_ctabancaria');
                $id_numero = Input::get('numero'); /////////.
                $id_chequera = Input::get('id_chequera'); /////////.
                $id_voucher = Input::get('id_voucher'); /////////.
                $id_persona = Input::get('id_persona');
                $id_cuentaaasi = Input::get('id_cuentaaasi');
                $id_moneda = Input::get('id_moneda');
                $tipocambio = Input::get('tipocambio');
                $fecha = Input::get('fecha');
                $importe = Input::get('importe');
                $importe_me = Input::get('importe_me');
                $detalle = Input::get('detalle');
                $celular = Input::get('celular');
                $email = Input::get('email');
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_ACTUALIZAR_VALE(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES,:P_ID_TIPOVALE, :P_ID_DINAMICA,
                            :P_ID_MEDIOPAGO, :P_ID_CTABANCARIA, :P_NUMERO, :P_ID_CHEQUERA,:P_ID_VOUCHER, :P_ID_PERSONA, :P_ID_EMPLEADO, :P_ID_CUENTAAASI, 
                            :P_ID_MONEDA, :P_TIPOCAMBIO, :P_FECHA, :P_IMPORTE, :P_IMPORTE_ME,
                            :P_DETALLE, :P_CELULAR, :P_EMAIL, :P_ID_VALE, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOVALE', $id_tipovale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_medio_pago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_NUMERO', $id_numero, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_EMPLEADO', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPOCAMBIO', $tipocambio, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CELULAR', $celular, PDO::PARAM_STR);
                $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VALE', $idVale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
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

    public function deleteVoucherAccountingEntries($IdVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::deleteVoucherAccountingEntries($IdVale);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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
        end:
        return response()->json($jResponse, $code);
    }

    public function deleteValeUPN(Request $request, $idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $pdo = DB::getPdo();
            DB::beginTransaction();
            try {
                // Eliminar conta_asiento
                // AccountingData::deleteContaAsiento(15, $idVale);
                // // Eliminar caja_vale_asiento
                // ExpensesData::deleteCajaValeAsiento($idVale);
                // // Eliminar caja_vale
                // ExpensesData::deleteValeById($idVale);
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_VALE(:P_ID_VALE); end;");
                $stmt->bindParam(':P_ID_VALE', $idVale, PDO::PARAM_INT);
                $stmt->execute();
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = NULL;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function duplicateVoucherAccountingEntries()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = $this->request->all();
                $data = array_filter($params);
                $result = ExpensesData::dublicateVoucherAccountingEntries($data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listValesProceso(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $id_paso = $request->query('id_paso');
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
            }
            $data = ExpensesData::verPaso($id_paso);
            foreach ($data as $item) {
                $id_proceso = $item->id_proceso;
                $id_paso = $item->id_paso;
            }
            $data = ExpensesData::listValeProceso($id_entidad, $id_depto, $id_anho, $id_proceso, $id_paso);
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

    public function showVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $cp = $this->request->query('code_process');
        $codeProcess = $cp ? $cp : null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::showVale($id_vale, $codeProcess);
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

    public function showAllValeFile($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::showAllValeFile($id_vale);
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

    function gonnn($value)
    {
        return $value;
    }

    public function showValeState($id_vale)
    {
        //        dd('holioii');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $cp = '8';
        $codeProcess = $cp ? $cp : null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::showValeState($id_vale, $codeProcess);
            collect($data)->map(function ($item) {
                //                dd($item->activo,$item->activo === '1' ? true : false);
                $item->activo = (bool)$item->activo == '1' ? true : false;
                $item->terminado = (bool)$item->terminado == '1' ? true : false;
                $item->denied = (bool)$item->denied == '1' ? true : false;
                return $item;
            });
            //            dd($data);
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

    public function listAccountingPlan(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_vale = $request->query('id_vale');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            $data = ExpensesData::showVale($id_vale, null);
            foreach ($data as $item) {
                $id_tipovale = $item->id_tipovale;
            }
            $id_tipoplan = "1";
            if ($id_tipovale == 3) { //TIPO DE VALE = OTRO
                $data = AccountingData::listPlanAccountingDenominationalSearch($id_cuentaaasi, $id_tipoplan);
            } else { // TIPO DE VALE 1: A RENDIR, 2: CTA PERSONAL
                $data = ExpensesData::listAccountingPlan($id_entidad, $id_depto, $id_user, $id_tipovale);
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

    public function listCurrentAccount(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_dinamica = $request->query('id_dinamica');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            $id_restriccion = $request->query('id_restriccion');
            $id_tipoplan = $request->query('id_tipoplan');
            $nombre = $request->query('nombre');
            $all = $request->query('all');
            $id_vale = $request->query('id_vale');
            $data = ExpensesData::showVale($id_vale, null);
            foreach ($data as $item) {
                $id_tipovale = $item->id_tipovale;
            }
            if ($id_tipovale == 3) { //TIPO DE VALE = OTRO
                $data = AccountingData::listCtaCteAccounting($id_dinamica, $id_tipoplan, $id_cuentaaasi, $id_restriccion, $nombre, $all);
            } else { // TIPO DE VALE 1: A RENDIR, 2: CTA PERSONAL
                $data = ExpensesData::listCurrentAccount($id_entidad, $id_depto, $id_user);
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

    public function listDepartmentAccount(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $id_vale = $request->query('id_vale');
            $id_dinamica = $request->query('id_dinamica');
            //$id_depto = $request->query('id_depto');
            $data = ExpensesData::showVale($id_vale, null);
            foreach ($data as $item) {
                $id_tipovale = $item->id_tipovale;
            }
            if ($id_tipovale == 3) { //TIPO DE VALE = OTRO
                $data = ExpensesController::entityDepto();
            } else { // TIPO DE VALE 1: A RENDIR, 2: CTA PERSONAL
                $data = ExpensesData::listDepartmentAccount($id_entidad, $id_depto, $id_user);
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

    public function entityDepto()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesController::recursive_EntityDepto($id_entidad, "A");
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $data[0]];
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse);
    }

    public function recursive_EntityDepto($id_entidad, $id_parent)
    {
        $parent = [];
        $data = ModuloData::entityDepto($id_entidad, $id_parent);
        $checked = false;
        foreach ($data as $key => $value) {

            $row = $this->recursive_EntityDepto($id_entidad, $value->id_depto);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre, 'checked' => $checked, 'children' => $row];
        }
        return $parent;
    }

    public function addRetentions()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_mediopago = $params->id_mediopago;
            $id_voucher = $params->id_voucher;
            $id_proveedor = $params->id_proveedor;
            $id_ctabancaria = $params->id_ctabancaria;
            $id_chequera = $params->id_chequera;
            $id_moneda = $params->id_moneda;
            $numero = $params->numero;
            $fecha_emision = $params->fecha_emision;
            $serie = $params->serie;
            $nro_retencion = $params->nro_retencion;
            $tiene_params = "S";
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params);
            if ($rpta["nerror"] == 0) {
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $tc = $rpta["tc"];
                $id_retencion = 0;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $voucher = AccountingData::showVoucher($id_voucher);
                foreach ($voucher as $key => $value) {
                    $id_mes = $value->id_mes;
                }
                try {
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_RETENCION(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_MEDIOPAGO, :P_ID_VOUCHER, :P_ID_PERSONA, :P_ID_PROVEEDOR, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_ANHO, :P_ID_MES, :P_ID_MONEDA, :P_NUMERO, :P_FECHA_EMISION, :P_SERIE, :P_NRO_RETENCION, :P_ID_RETENCION, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_FECHA_EMISION', $fecha_emision, PDO::PARAM_STR);
                    $stmt->bindParam(':P_SERIE', $serie, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NRO_RETENCION', $nro_retencion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['id_retencion' => $id_retencion];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    private function rulesLeccion()
    {
        return [
            'nombre' => 'required|max:100|min:1',
            'activo' => 'required',
            'descripcion' => 'max:500',
        ];
    }


    public function addRetentionsByUPN()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = Input::all();
            $validador = Validator::make($data, [
                'id_mediopago' => 'required',
                'id_voucher' => 'required',
                'id_proveedor' => 'required',
                'id_ctabancaria' => '',
                'id_chequera' => '',
                'id_moneda' => 'required',
                'numero' => 'required|digits_between:0,8|numeric',
                'serie' => 'required|max:4',
                'fecha_emision' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
                'nro_retencion' => '',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_voucher = Input::get('id_voucher');
            $voucher = AccountingData::showVoucher($id_voucher);
            if (count($voucher) === 1) {
                $id_anho = $voucher[0]->id_anho;
                $id_mes = $voucher[0]->id_mes;
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "La operación no esta asignada a un voucher, revise tener un voucher asignado.";
                $jResponse['data'] = NULL;
                $jResponse['code'] = "202";
                // $code = "202";
                goto end;
            }

            $id_mediopago = Input::get('id_mediopago');
            $id_proveedor = Input::get('id_proveedor');
            $id_ctabancaria = Input::get('id_ctabancaria');
            $id_chequera = Input::get('id_chequera');
            $id_moneda = Input::get('id_moneda');
            $numero = Input::get('numero');
            $fecha_emision = Input::get('fecha_emision');
            $serie = Input::get('serie');
            $nro_retencion = Input::get('nro_retencion');

            $id_retencion = 0;
            $error = 0;
            $msg_error = "";
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            try {
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_RETENCION(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_MEDIOPAGO, :P_ID_VOUCHER, :P_ID_PERSONA, :P_ID_PROVEEDOR, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_ANHO, :P_ID_MES, :P_ID_MONEDA, :P_NUMERO, :P_FECHA_EMISION, :P_SERIE, :P_NRO_RETENCION, :P_ID_RETENCION, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_EMISION', $fecha_emision, PDO::PARAM_STR);
                $stmt->bindParam(':P_SERIE', $serie, PDO::PARAM_STR);
                $stmt->bindParam(':P_NRO_RETENCION', $nro_retencion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['id_retencion' => $id_retencion];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addRetentionsPurchases($id_retencion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_dinamica = $params->id_dinamica;
            $compras = $params->compras;
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                foreach ($compras as $key => $item) {
                    $id_compra = $item->id_compra;
                    $importe_total = $item->importe_total;
                    $importe_ret = $item->importe_ret;
                    $importe_ret_me = $item->importe_ret_me;
                    $id_retdetalle = 0;
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_RETENCION_DETALLE(:P_ID_RETENCION, :P_ID_COMPRA,:P_ID_DINAMICA,:P_IMPORTE_TOTAL,:P_IMPORTE_RET,:P_IMPORTE_RET_ME, :P_ID_RETDETALLE, :P_ERROR,:P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_IMPORTE_TOTAL', $importe_total, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_RET', $importe_ret, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_RET_ME', $importe_ret_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_RETDETALLE', $id_retdetalle, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                }

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $id_retencion;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listRetentionsPurchases($id_retencion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listRetentionsPurchases($id_retencion);
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

    public function deleteRetentionsPurchases($id_retencion, $id_retdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $tipo = 1;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_RETENCION_DETALLE(:P_ID_RETENCION, :P_ID_RETDETALLE,:P_TIPO); end;");
                $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_RETDETALLE', $id_retdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
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

    public function deleteDeductions($id_deduction)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_DETRACCION(:P_ID_DETRACCION); end;");
                $stmt->bindParam(':P_ID_DETRACCION', $id_deduction, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function deleteDeductionsAll($id_deduction)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_DETRACCION_ALL(:P_ID_DETRACCION); end;");
                $stmt->bindParam(':P_ID_DETRACCION', $id_deduction, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function deleteRetentions($id_retencion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_RETENCION(:P_ID_RETENCION); end;");
                $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                $stmt->execute();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
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

    public function updateRetentions($id_retencion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $opc = $params->opc;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                if ($opc == "1") {

                    $data = Input::all();
                    $validador = Validator::make($data, [
                        'id_ctabancaria' => '',
                        'id_chequera' => '',
                        'numero' => 'required|numeric|digits_between:0,8',
                        'id_moneda' => 'required',
                        // 'id_mediopago' => 'required',
                        // 'id_voucher' => 'required',
                        // 'id_proveedor' => 'required',
                        // 'serie' => 'required|max:4',
                        // 'fecha_emision' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
                        // 'nro_retencion' => '',
                    ]);
                    if ($validador->fails()) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $validador->errors()->first();
                        $jResponse['data'] = NULL;
                        $code = "202";
                        goto end;
                    }

                    $id_ctabancaria = $params->id_ctabancaria;
                    $id_chequera = $params->id_chequera;
                    $numero = $params->numero;
                    $id_moneda = $params->id_moneda;
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_ACTUALIZAR_RETENCION(:P_ID_RETENCION, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_MONEDA, :P_NUMERO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    //$stmt->execute();
                    if ($error == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    if ($opc == "2") {
                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_FINALIZAR_RETENCION(:P_ID_RETENCION, :P_ERROR, :P_MSGERROR); end;");
                        $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                        $stmt->execute();
                        if ($error == 0) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = "Succes";
                            $jResponse['data'] = [];
                            $code = "200";
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $msg_error;
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "ERROR: No se ha Finalizado la Retencion";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }
            } catch (Exception $e) {
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function getRetentionById(Request $request, $id_retencion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::getRetentionById($id_retencion);
            $detalleRetencion = ExpensesData::getRetentionComprasByIdRetencion($id_retencion);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [
                    'retencion' => $data[0],
                    'detalle' => $detalleRetencion,
                ];
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

    public function listRetentions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $text_search = $request->query('text_search');
            $text_search_off = $text_search ? str_replace($text_search, "'", "") : '';
            $id_voucher = $request->query('id_voucher');
            ExpensesData::deleteRetencionIfEstadoIsCero($id_entidad, $id_depto, $id_voucher, $id_user);
            $data = ExpensesData::listRetentions($id_entidad, $id_depto, $id_voucher, $text_search_off);
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

    public function listTypesGoodsServices(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $anexo = $request->query('anexo');
            $data = ExpensesData::listTypesGoodsServices($anexo);

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

    public function listTypesDetractionOperations()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listTypesDetractionOperations();
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

    public function addDeductions()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_mediopago = $params->id_mediopago;
            $id_voucher = $params->id_voucher;
            $id_proveedor = $params->id_proveedor;
            $id_ctabancaria = $params->id_ctabancaria;
            $id_chequera = $params->id_chequera;
            $id_operacion = $params->id_operacion;
            $id_tipobienservicio = $params->id_tipobienservicio;
            $id_moneda = $params->id_moneda;
            $autodetraccion = $params->autodetraccion;
            $numero = $params->numero;
            $nro_constancia = $params->nro_constancia;
            $nro_operacion = $params->nro_operacion;

            if ($params->autodetraccion == 'S' && ($params->tipo_autodetraccion == 'S' or $params->tipo_autodetraccion == 'C')) {
                $id_compra = $params->id_venta;
            } else {
                $id_compra = $params->id_compra;
            }


            $id_dinamica = $params->id_dinamica;
            $importe = $params->importe;
            $importe_me = $params->importe_me;
            $fecha_emision = $params->fecha_emision;
            $tiene_params = "S";
            $tipo_autodetraccion = $params->tipo_autodetraccion;


            $rpta = AccountingData::AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params);
            if ($rpta["nerror"] == 0) {
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $tc = $rpta["tc"];
                $id_detraccion = 0;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $voucher = AccountingData::showVoucher($id_voucher);
                foreach ($voucher as $key => $value) {
                    $id_mes = $value->id_mes;
                }
                try {
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DETRACCION(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_MEDIOPAGO,:P_ID_VOUCHER,:P_ID_PERSONA,:P_ID_PROVEEDOR,:P_ID_ANHO,:P_ID_MES,:P_ID_CTABANCARIA,:P_ID_CHEQUERA,:P_ID_OPERACION,:P_ID_TIPOBIENSERVICIO,:P_ID_MONEDA,:P_AUTODETRACCION,:P_NUMERO,:P_NRO_CONSTANCIA,:P_NRO_OPERACION,:P_FECHA_EMISION,:P_ID_COMPRA,:P_ID_DINAMICA,:P_IMPORTE,:P_IMPORTE_ME,:P_ID_DETRACCION, :P_ERROR, :P_MSGERROR, :P_TIPO_AUTO); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_OPERACION', $id_operacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPOBIENSERVICIO', $id_tipobienservicio, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_AUTODETRACCION', $autodetraccion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NRO_CONSTANCIA', $nro_constancia, PDO::PARAM_STR);
                    $stmt->bindParam(':P_NRO_OPERACION', $nro_operacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_FECHA_EMISION', $fecha_emision, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_DETRACCION', $id_detraccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TIPO_AUTO', $tipo_autodetraccion, PDO::PARAM_STR);

                    $stmt->execute();
                    if ($error == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['id_detraccion' => $id_detraccion];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listRetentionsSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $parent = [];
            $id_voucher = $request->query('id_voucher');

            $retentions = ExpensesData::listRetentions($id_entidad, $id_depto, $id_voucher, null);
            foreach ($retentions as $key => $value) {
                $details = ExpensesData::listRetentionsDetails($value->id_retencion);
                $parent[] = [
                    'id_retencion' => $value->id_retencion,
                    'id_entidad' => $value->id_entidad,
                    'id_depto' => $value->id_depto,
                    'id_voucher' => $value->id_voucher,
                    'ruc' => $value->ruc,
                    'nombre' => $value->nombre,
                    'serie' => $value->serie,
                    'nro_retencion' => $value->nro_retencion,
                    'fecha_emision' => $value->fecha_emision,
                    'importe' => $value->importe,
                    'details' => $details
                ];
            }
            if (count($parent) > 0) {
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
        }
        return response()->json($jResponse, $code);
    }

    public function listDeductions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_voucher = $request->query('id_voucher');
            $text_search = $request->query('text_search');
            $text_search_off = $text_search ? str_replace("'", "", $text_search) : '';
            $data = ExpensesData::listDeductions($id_entidad, $id_depto, $id_voucher, $text_search_off);
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
        end:
        return response()->json($jResponse, $code);
    }

    public function listDeductionsSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $parent = [];
            $id_voucher = $request->query('id_voucher');

            $deductions = ExpensesData::listDeductions($id_entidad, $id_depto, $id_voucher, null);
            foreach ($deductions as $key => $value) {
                $details = ExpensesData::listDeductionsDetails($value->id_detraccion);
                $parent[] = [
                    'id_detraccion' => $value->id_detraccion,
                    'id_entidad' => $value->id_entidad,
                    'id_depto' => $value->id_depto,
                    'id_voucher' => $value->id_voucher,
                    'ruc' => $value->ruc,
                    'nombre' => $value->nombre,
                    'nro_constancia' => $value->nro_constancia,
                    'nro_operacion' => $value->nro_operacion,
                    'fecha_emision' => $value->fecha_emision,
                    'importe' => $value->importe,
                    'importe_me' => $value->importe_me,
                    'details' => $details
                ];
            }
            if (count($parent) > 0) {
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
        }
        return response()->json($jResponse, $code);
    }

    public function addBankDeposits()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_dinamica = $params->id_dinamica;
            $id_moneda = $params->id_moneda;
            $id_vale = property_exists($params, 'id_vale') ? $params->id_vale : null;
            $id_ctabancaria = $params->id_ctabancaria;
            //$id_mediopago = $params->id_mediopago;
            $operacion = $params->operacion;
            $fecha = $params->fecha;
            $importe = $params->importe;
            $importe_me = $params->importe_me;
            $glosa = $params->glosa;
            $id_cierre = $params->id_cierre;
            $tiene_params = "S";
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params);
            if ($rpta["nerror"] == 0) {
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $tc = $rpta["tc"];
                $id_pago = 0;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                try {
                    DB::beginTransaction();
                    $id_deposito = null;
                    $id_mediopago = "001";
                    $id_tipotarjeta = null;
                    $id_tipoasiento = "MB";
                    $estado = "1";
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_BANCO(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_ANHO,:P_ID_MES,:P_ID_DEPOSITO,:P_ID_DINAMICA,:P_ID_MONEDA,:P_ID_CTABANCARIA,:P_ID_TIPOTARJETA,:P_ID_PERSONA,:P_ID_MEDIOPAGO,:P_OPERACION,:P_FECHA,:P_IMPORTE,:P_IMPORTE_ME,:P_TIPOCAMBIO,:P_GLOSA,:P_ID_TIPOASIENTO,:P_ESTADO,:P_ERROR,:P_MSGERROR,:P_ID_VALE,:P_ID_CIERRE); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                    $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                    $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                    $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_TIPOASIENTO', $id_tipoasiento, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CIERRE', $id_cierre, PDO::PARAM_INT);
                    $stmt->execute();
                    if ($error == 0) {
                        DB::commit();
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = [];
                        $code = "201";
                    } else {
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteBankDeposits($id_depbanco)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $data = ExpensesData::deleteBankDeposits($id_depbanco);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $data;
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

    public function addRendirValeWithSmallBoxDeposits($idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                'id_mediopago' => 'required',
                'id_voucher' => 'required',
                'id_cliente' => 'required',
                'id_tipotransaccion' => 'required',
                'id_moneda' => 'required',
                'id_dinamica' => 'required',
                'serie' => '', 'numero' => '',
                'fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
                'importe' => 'numeric', 'importe_me' => 'numeric', 'tipo_cambio' => 'numeric',
                'glosa' => ''
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_mediopago = Input::get('id_mediopago');
            $id_voucher = Input::get('id_voucher');
            $id_cliente = Input::get('id_cliente');
            $id_tipotransaccion = Input::get('id_tipotransaccion');
            $id_moneda = Input::get('id_moneda');
            $id_dinamica = Input::get('id_dinamica');
            $serie = Input::get('serie');
            $numero = Input::get('numero');
            $fecha = Input::get('fecha');
            $importe = Input::get('importe');
            $importe_me = Input::get('importe_me');
            $tipo_cambio = Input::get('tipo_cambio');
            $glosa = Input::get('glosa');

            $voucher = AccountingData::showVoucher($id_voucher);
            $id_anho = $voucher[0]->id_anho;
            $id_mes = $voucher[0]->id_mes;

            $id_deposito = 0;
            $error = 0;
            $msg_error = "";
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            try {
                $pdo = DB::getPdo();
                DB::beginTransaction();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_RENDIR_VALE_DEPOSITO(
                :P_ID_ENTIDAD,:P_ID_DEPTO,
                :P_ID_VALE,:P_ID_MEDIOPAGO,:P_ID_VOUCHER,:P_ID_PERSONA,:P_ID_CLIENTE,:P_ID_TIPOTRANSACCION,:P_ID_MONEDA,
                :P_ID_DINAMICA,:P_ID_ANHO,:P_ID_MES,:P_SERIE,:P_NUMERO,:P_FECHA,:P_IMPORTE,:P_IMPORTE_ME,
                :P_TIPO_CAMBIO,:P_GLOSA,:P_ID_DEPOSITO,:P_ERROR,:P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VALE', $idVale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_SERIE', $serie, PDO::PARAM_STR);
                $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO_CAMBIO', $tipo_cambio, PDO::PARAM_STR);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [];
                    $code = "201";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = NULL;
                    $code = "202";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = NULL;
                $error = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addRendirValeWithBanksDeposits($idVale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                'id_mediopago' => 'required',
                'id_voucher' => 'required',
                'id_cliente' => 'required',
                'id_tipotransaccion' => 'required',
                'id_dinamica' => 'required',
                // 'id_tipotarjeta' => 'required',
                'id_tipotarjeta' => '',
                'fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
                'id_ctabancaria' => 'required',
                'operacion' => 'required',
                'glosa' => '',
                'id_moneda' => 'required',
                'tipo_cambio' => 'numeric',
                'importe' => 'numeric',
                'importe_me' => 'numeric'
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $id_mediopago = Input::get('id_mediopago');
            $id_voucher = Input::get('id_voucher');
            $id_cliente = Input::get('id_cliente');
            $id_tipotransaccion = Input::get('id_tipotransaccion');
            $id_dinamica = Input::get('id_dinamica');
            $id_tipotarjeta = Input::get('id_tipotarjeta');
            $fecha = Input::get('fecha');
            $id_ctabancaria = Input::get('id_ctabancaria');
            $operacion = Input::get('operacion');
            $glosa = Input::get('glosa');
            $id_moneda = Input::get('id_moneda');
            $tipo_cambio = Input::get('tipo_cambio');
            $importe = Input::get('importe');
            $importe_me = Input::get('importe_me');

            $voucher = AccountingData::showVoucher($id_voucher);
            $id_anho = $voucher[0]->id_anho;
            $id_mes = $voucher[0]->id_mes;

            $id_depbanco = 0;
            $error = 0;
            $msg_error = "";
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            try {
                $pdo = DB::getPdo();
                DB::beginTransaction();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_RENDIR_VALE_DEPOSITO_BANK(
                :P_ID_ENTIDAD,:P_ID_DEPTO,
                :P_ID_VALE,:P_ID_MEDIOPAGO,:P_ID_VOUCHER,:P_ID_PERSONA,:P_ID_CLIENTE,:P_ID_TIPOTRANSACCION,
                :P_ID_TIPOTARJETA, :P_ID_CTABANCARIA, :P_OPERACION,
                :P_ID_MONEDA,:P_ID_DINAMICA,:P_ID_ANHO,:P_ID_MES,:P_FECHA,:P_IMPORTE,:P_IMPORTE_ME,
                :P_TIPO_CAMBIO,:P_GLOSA,:P_ID_DEPBANCO,:P_ERROR,:P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VALE', $idVale, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPO_CAMBIO', $tipo_cambio, PDO::PARAM_STR);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPBANCO', $id_depbanco, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [];
                    $code = "201";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = NULL;
                    $code = "202";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = NULL;
                $error = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function showExchangeRatePayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_mediopago = $request->query('id_mediopago');
            $id_chequera = $request->query('id_chequera');
            $fecha = $request->query('fecha');
            $data = ExpensesData::showExchangeRatePayment($id_mediopago, $id_chequera, $fecha);
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

    public function addPaymentsExpensesSeats($id_pgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $validador = Validator::make(Input::all(), [
                'id_cuentaaasi' => 'required',
                'id_depto' => 'required',
                'detalle' => 'required',
                'importe' => 'required|numeric',
                'importe_me' => 'numeric',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_fondo = Input::get('id_fondo');
            $id_depto = Input::get('id_depto');
            $id_cuentaaasi = Input::get('id_cuentaaasi');
            $id_restriccion = Input::get('id_restriccion');
            $id_ctacte = Input::get('id_ctacte');
            $is_dc = "D";
            $detalle = Input::get('detalle');
            $importe = Input::get('importe');
            $importe_me = Input::get('importe_me');
            try {
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                $id_gasiento = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_GASTO_ASIENTO(
                    :P_ID_FONDO, :P_ID_DEPTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION,:P_ID_CTACTE,
                    :P_IMPORTE, :P_IMPORTE_ME, :P_IS_DC, :P_DESCRIPCION,:P_ID_PGASTO, :P_ERROR,
                    :P_ID_GASIENTO); end;");
                $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_IS_DC', $is_dc, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCRIPCION', $detalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PGASTO', $id_pgasto, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_GASIENTO', $id_gasiento, PDO::PARAM_INT);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_gasiento;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function listValesDeposits($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ExpensesData::listValesDeposits($id_vale);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $jResponse['total'] = round(collect($data)->sum('importe'), 2);
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $jResponse['total'] = 0;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function myPayments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_voucher = $request->query('id_voucher');
            $items = ExpensesData::myPayments($id_voucher);
            $cash = ExpensesData::myPaymentsCash($id_voucher);
            $total = ExpensesData::myPaymentsTotal($id_voucher);
            if ($items) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $items, 'subtotal' => $cash, 'total' => $total];
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


    public function Detractions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if ($valida == 'SI') {

            $jResponse = [];
            $id_voucher = $request->query('id_voucher');

            $data = ExpensesData::Detractions($id_entidad, $id_voucher);

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

    public function myPaymentsSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $mainReport = ExpensesData::myPaymentsSummary($id_voucher);
                $total = ExpensesData::myPaymentsSummaryTotal($id_voucher);
                $jResponse['success'] = true;
                if (count($mainReport) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $mainReport, 'total' => $total];
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

    public function myPaymentsSummaryPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $username = $jResponse["email"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {
                $id_voucher = $request->query('id_voucher');
                $id_mes = $request->query('id_mes');
                $id_tipovoucher = $request->query('id_tipovoucher');

                $fecha = $request->query('fecha');
                $numero = $request->query('numero');
                $lote = $request->query('lote');

                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $mainReport = ExpensesData::myPaymentsSummary($id_voucher);
                $total = ExpensesData::myPaymentsSummaryTotal($id_voucher);

                // $voucherData = AccountingData::listVoucherModulesAllShow($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher, $id_voucher);

                if (count($mainReport) > 0) {
                    $pdf = DOMPDF::loadView('pdf.treasury.reports.myPatmentsSummary', [
                        'mainReport' => $mainReport,
                        // 'voucherDatas'=>$voucherData[0],
                        'total' => $total,


                        'fecha' => $fecha,
                        'numero' => $numero,
                        'lote' => $lote,


                        'username' => $username
                    ])->setPaper('a4', 'portrait');

                    $data = base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $data]
                    ];
                }
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error', ['mensaje' => $mensaje])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];

        return response()->json($jResponse);
    }

    public function addPaymentsSurrender()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $validador = Validator::make(Input::all(), [
                'id_voucher' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            $params = json_decode(file_get_contents("php://input"));
            $id_mediopago = $params->id_mediopago;

            $tipo_cambio = $params->tipo_cambio;
            $id_voucher = $params->id_voucher;
            $fecha = '';
            if ($id_mediopago === "001" || $id_mediopago === "008") { // 008=Tele Credito
                $fecha = property_exists($params, 'fecha') ? $params->fecha : date('y-m-d');
            }
            $id_moneda = $params->id_moneda;

            $id_cuenta = $params->id_cuenta;
            $numero = $id_cuenta;
            $id_restriccion = $params->id_restriccion;
            $id_ctacte = $params->id_ctacte;
            $id_fondo = $params->id_fondo;
            $id_depto_d = $params->id_depto;
            $descripcion = $params->descripcion;
            $_dc = "C";
            $agrupa = "S";
            $id_ctabancaria = null;
            $id_chequera = null;
            $id_vale = null;
            $id_tipotransaccion = null;
            $voucher = AccountingData::showVoucher($id_voucher);
            if (count($voucher) > 0) {
                $id_anho = $voucher[0]->id_anho;
                $id_mes = $voucher[0]->id_mes;
                $id_pago = 0;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                try {
                    $pdo = DB::getPdo();
                    DB::beginTransaction();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO(:P_ID_MEDIOPAGO, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_VALE, :P_ID_VOUCHER, :P_ID_ANHO, :P_ID_MES, :P_ID_USER, :P_ID_TIPOTRANSACCION, :P_ID_MONEDA, :P_NUMERO, :P_FECHA, :P_TIPOCAMBIO, :P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CHEQUERA', $id_chequera, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
                    $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TIPOCAMBIO', $tipo_cambio, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error == 0) {
                        DB::commit();

                        ExpensesData::addSeatsPayments($id_pago, $id_cuenta, $id_restriccion, $id_ctacte, $id_fondo, $id_depto_d, $descripcion, $_dc, $agrupa);
                        $pago = ExpensesData::showCajaPago($id_pago);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";
                        $jResponse['data'] = $pago[0];
                        $code = "200";
                    } else {
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = null;
                        $code = "202";
                    }
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No se puede encontrar el voucher.";
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function privateUploadValeFile($id_vale, $nombre, $tipo, $param)
    {
        try {
            if (is_object($nombre)) {
                $file = $nombre;
            } else {
                $file = Input::file($nombre);
            }
            if ($file == null) {
                return false;
            }
            if ($tipo == "1") {
                $destinationPath = 'treasury_files/convenios';
            } elseif ($tipo == "2" or $tipo == "3") {
                $destinationPath = 'treasury_files/sustentos';
            }
            $nameRandon = PurchasesUtil::getGenereNameRandom(17);
            $nombreDinamico = $id_vale . "_" . $nameRandon . "." . $file->getClientOriginalExtension();
            $formato = strtoupper($file->getClientOriginalExtension());
            $size = $file->getSize();
            $url = $destinationPath . "/" . $nombreDinamico;
            $estado = "1";
            $file->move($destinationPath, $nombreDinamico);
            $result = $this->privateAddValeFile($id_vale, $file->getClientOriginalName(), $formato, $url, $tipo, $estado, $size, $param);
            return ["success" => $result["success"], "message" => $result["message"], "data" => $result['data']];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    private function privateAddValeFile($id_vale, $nombre, $formato, $url, $tipo, $estado, $size, $params)
    {
        $idPgasto = '';
        $idUser = '';
        $origen = '';
        if (!empty($params->id_pgasto)) {
            $idPgasto = $params->id_pgasto;
            $idUser = $params->id_user;
        }
        if (!empty($params->origen)) {
            $origen = $params->origen;
        }
        try {
            $dataValeFile = array(
                "id_vale" => $id_vale,
                "nombre" => $nombre,
                "formato" => $formato,
                "url" => $url,
                "fecha" => DB::raw('sysdate'),
                "tipo" => $tipo,
                "tamanho" => $size,
                "estado" => $estado,
                "id_pgasto" => $idPgasto,
                "id_user" => $idUser,
                "origen" => $origen,
            );
            $result = ExpensesData::addValeFile($dataValeFile);
            return ["success" => $result["success"], "message" => $result["message"], "data" => $result["data"]];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    public function deleteProvisionVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $url_file = ExpensesData::showValeFile($id_vale, '2');
                $rpta = ExpensesData::deleteProvisionVale($id_vale);
                if ($rpta['error'] == 0) {
                    //ELimina el File Fisico
                    if (file_exists($url_file)) {
                        unlink($url_file);
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
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
    public function getPaymentEntrySeatVale()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = array('id_voucher' => $this->request->query('id_voucher'));
                $rpta = ExpensesData::getPaymentEntrySeatVale($params);
                if ($rpta) {
                    //ELimina el File Fisico
                    //unlink($url_file);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = $rpta;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta;
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
    public function saveNewFilesVale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                DB::beginTransaction();

                $archivo = Input::file('file_archivo');
                if ($archivo) {
                    $fileAdded = $this->saveFileVale($request->id_vale, $request, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                    $idVfile = $fileAdded['data'];

                    if ($request->tipo == '3') {

                        $id_vgasto = ComunData::correlativo('eliseo.caja_vale_gasto', 'id_vgasto');
                        $cvgasto = DB::table('eliseo.caja_vale_gasto')->insert([
                            'id_vgasto'      => $id_vgasto,
                            'id_vale'        => $request->id_vale,
                            'id_vfile'       => $idVfile,
                            'id_user'        => $id_user_reg,
                            'fecha'          => $fecha_reg,
                            'importe'        => $request->importe,
                            'importe_me'     => $request->importe_me,
                            'detalle'        => $request->detalle,
                            'autorizado'     => 'N',
                        ]);
                    }
                    if ($fileAdded['nerror'] == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = $fileAdded['message'];
                        $jResponse['data'] = $idVfile;
                        $code = "200";
                        DB::commit();
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $fileAdded['message'];
                        $jResponse['data'] = '';
                        $code = "202";
                        DB::rollback();
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe archivo';
                    $jResponse['data'] = '';
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
    public function fileVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesData::fileVale($id_vale);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Resultado exitoso';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteFilesVale($id_vfile)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                DB::beginTransaction();
                $result = ExpensesData::deleteFilesVale($id_vfile);
                if ($result['success']) {
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
    public function valeFilesList(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesData::valeFilesList($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Resultado exitoso';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function depositoFile($id_deposito)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ExpensesData::depositoFile($id_deposito);
                if (!empty($object)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Resultado exitoso';
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function pgastoFile($id_pgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ExpensesData::pgastoFile($id_pgasto);
                if (!empty($object)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Resultado exitoso';
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function finishPayments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $clientIP = \Request::ip();
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_pago = $request->id_pago;
                $opc = $request->opc;
                $codigo = property_exists($request, 'codigo') ? $request->codigo : null;
                $error = 0;
                $msg_error = "";
                for ($x = 1; $x <= 200; $x++) {
                    $msg_error .= "0";
                }
                // dd($request);
                $pdo = DB::getPdo();
                DB::beginTransaction();
                if ($opc == "2") {
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_FINALIZAR_PAGO(:P_ID_PAGO, :P_ERROR, :P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error === 0) {
                        // para los documentos
                        $documentos = DB::table('eliseo.caja_pago_gasto as a')
                            ->join('eliseo.caja_documento as b', 'a.id_pgasto', '=', 'b.id_pgasto')->where('a.id_pago', '=', $id_pago)->select('b.id_documento')->get();
                        if (count($documentos) > 0) {
                            foreach ($documentos as $ite) {
                                $items = (object)$ite;
                                $codigo_proceso = 'PRODOC';
                                $update = DB::table('eliseo.caja_documento')->where('id_documento', '=', $items->id_documento)->update([
                                    'codigo' => $codigo_proceso,
                                ]);
                                $addProceso = TaxDocumentsData::insertProceso($items->id_documento, $id_user, $codigo_proceso, $fecha_reg);
                            }
                        }

                        $fileAdded = 'Sin archivo';
                        $archivo = Input::file('file_archivo');
                        if ($archivo) {
                            $fileAdded = $this->saveFilePayment($id_pago, $request, $archivo);
                        }


                        $pago = ExpensesData::showCajaPago($id_pago)[0];
                        if (property_exists($pago, 'id_vale') and $pago->id_vale) {
                            $data = [
                                "codigo" => $codigo,
                                "id_pedido" => $pago->id_vale,
                                "id_persona" => $id_user,
                                "id_entidad" => $id_entidad,
                                "detalle" => 'Vale rendido',
                                "ip" => $clientIP
                            ];
                            $data_procces = [
                                "estado" => '1',
                            ];
                            $result = PurchasesData::spProcessStepRunNext($data);
                            $process_run = Process::getProcessRunByIdOperation($pago->id_vale, $codigo);
                            if (property_exists($process_run, 'id_registro')) {
                                $isUpdateProces = Process::updateProcessRun($process_run->id_registro, $data_procces);
                            }
                        }
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";
                        $jResponse['data'] =  $fileAdded['message'];
                        $code = "200";
                        DB::commit();
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data'] = [];
                        $code = "202";
                        DB::rollback();
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ERROR: No se ha Finalizado el Pago";
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
    public  function saveFilePayment($id_pago, $params, $archivo)
    { //'// antes de cambiar recordar que se esta usando en varios lugares

        $formato          = $archivo->getClientOriginalExtension();
        // $name_file             = $archivo->getClientOriginalName();
        $size             = filesize($archivo);
        $folder           = $params->carpeta; //data_api_treasury/pagos/
        $carpeta = '';
        $fileAdjunto['nerror'] = 1;

        $tipo = $params->tipo;
        $estado = "1";
        if ($tipo == "1") {
            $carpeta = $folder . 'constancia-pagos';
        } else {
            $carpeta = $folder . '';
        }
        // $fileAdjunto = ComunData::uploadFile($archivo, $carpeta);
        $storage = new StorageController();
        $fileAdjunto = $storage->postFile($archivo, $carpeta);
        $nombre = explode("/", $fileAdjunto['data'])[4];

        if ($fileAdjunto['success']) {
            // El id del pago file es por secuencias
            $data = [
                "id_pago" => $id_pago,
                "nombre" => $nombre,
                "formato" => $formato,
                "url" => $fileAdjunto['data'],
                "fecha" => DB::raw('sysdate'),
                "tipo" => $tipo,
                "tamanho" => $size,
                "estado" => $estado,
            ];
            $cajaPagoFile = EliseoCajaPagoFile::create($data);
            if(!$cajaPagoFile) {
                $fileAdjunto = ComunData::deleteFilesDirectorio($carpeta, $fileAdjunto['filename'], 'E');
            }
            $fileAdjunto = [
                'nerror' => 0,
                'message' => 'Creado',
            ];
            // $result = ExpensesData::addPaymentsFile($id_pago, $nombre, $formato, $fileAdjunto['data'], $tipo, $estado, $size, $params);
            // if ($result['success']) {
                // $fileAdjunto = [
                    // 'nerror' => 0,
                    // 'message' => 'Creado',
                // ];
            // } else {
                // $resp = ComunData::deleteFilesDirectorio($carpeta, $fileAdjunto['filename'], 'E');
                // $fileAdjunto = [
                    // 'nerror' => 1,
                    // 'message' => 'Fallo' . ' ' . $resp,
                // ];
            // }
        } else {
            $fileAdjunto = [
                'nerror' => 1,
                'message' => 'No se pudo crear',
            ];
        }
        return $fileAdjunto;
    }
    public function listValeGastoAuthorize()
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
                $object = ExpensesData::listValeGastoAuthorize($id_entidad, $id_depto);
                if (!empty($object)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Resultado exitoso';
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addSeatValeGastoFile(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::addSeatValeGastoFile($request);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addSeatsTransValeGastoFile(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::addSeatsTransValeGastoFile($request);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateSeatValeGastoFile(Request $request, $id_vasiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::updateSeatValeGastoFile($request, $id_vasiento);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listSeatValeGastoFile($id_vgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesData::listSeatValeGastoFile($id_vgasto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteSeatValeGastoFile($id_vgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::deleteSeatValeGastoFile($id_vgasto);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function autthorizeValeGastoFile($id_vgasto, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ExpensesData::autthorizeValeGastoFile($id_vgasto, $request);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function gastosValeAutorizados($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesData::gastosValeAutorizados($id_vale);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function importGastoComprobante(Request $request, $id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ExpensesData::importGastoComprobante($request, $id_vale);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function estadoDelProcesosDelVale(Request $request, $id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        if ($jResponse["valida"] != 'SI') {
            // Error token auth
            return response()->json(['success'=> false, 'message'=> "Token no valido", 'data'=> null], 401);
        }

        // Success token auth
        try {
            $vale_info = ExpensesData::procesoVale($id_vale);
            $vale_detalle_proceso = ExpensesData::showValeState($id_vale, '8');
            collect($vale_detalle_proceso)->map(function ($item) {
                $item->activo = (bool) ((int) $item->activo);
                $item->terminado = (bool) ((int) $item->terminado);
                $item->denied = (bool) ((int) $item->denied);
                return $item;
            });
            $vale_info->vale_detalle_proceso = $vale_detalle_proceso;

            return response()->json([
                'success'=> true,
                'message'=> "ok",
                'data'=> $vale_info
            ], 200);

        } catch (Exception $th) {
            return response()->json([
                'success'=> false,
                'message'=> $e->getMessage(),
                'data'=> null
            ], 500);
        }
    }

    public function addGastoVale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                DB::beginTransaction();
                $result = ExpensesData::addGastoVale($request, $fecha_reg, $id_entidad, $id_user);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
    public function denyVale($id_vale)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_RECHAZAR_VALE(:P_ID_VALE); end;");
                $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                $stmt->execute();

                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $id_vale;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $error = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function editSeatVale($id_asiento,$id_vale)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = $this->request->all();
                $data = array_filter($params);
                $result = ExpensesData::editSeatVale($id_asiento,$id_vale,$id_entidad,$data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showTermCond(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_persona =$request->id_persona;
                $pdf = ExpensesData::termCondVale($id_persona, 'N');
                if ($pdf) {
                    $doc = base64_encode($pdf->stream('print.pdf'));
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = ['doc'=>$doc];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code); 
    }

}
