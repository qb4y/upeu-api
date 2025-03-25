<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 4/03/2019
 * Time: 13:59
 */

namespace App\Http\Controllers\Purchases;

use Exception;

use App\Http\Controllers\Purchases\Utils\ProvisionsUtil;
use App\Http\Controllers\Purchases\Validations\ProvisionsValidation;
use App\Http\Controllers\Purchases\Validations\PurchasesValidation;
use App\Http\Controllers\Accounting\Setup\AccountingController;
// use App\Http\Controllers\Setup\Provider\sunat;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Data\Sales\SalesData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Http\Data\Inventories\WarehousesData;
use PDO;

class ProvisionsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        // $this->middleware('auth');
    }

    public function index()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_proveedor = $this->request->query('id_proveedor');
                $id_voucher = $this->request->query('id_voucher');
                if($id_proveedor !== '' && $id_proveedor !== null) {
                    $data = PurchasesData::getComprasByIdProveedor($id_entidad, $id_depto, $id_user, $id_proveedor);
                } else if($id_voucher !== '' && $id_voucher !== null) {
                    $data = PurchasesData::getComprasByIdVoucher($id_entidad, $id_depto, $id_user, $id_voucher);
                } else {
                    $data = PurchasesData::getCompras($id_entidad, $id_depto, $id_user);
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function indexInventarios()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_voucher = $this->request->query('id_voucher');
                $data = PurchasesData::getCompraInventariosByIdVoucher($id_entidad, $id_depto, $id_user, $id_voucher);
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function indexInventariosNC()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_voucher = $this->request->query('id_voucher');
                $data = PurchasesData::getCompraInventariosNCByIdVoucher($id_entidad, $id_depto, $id_user, $id_voucher);
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    // public function storeNota()
    // {
    //     $jResponse = GlobalMethods::authorizationLamb($this->request);
    //     $code = $jResponse["code"];
    //     $valida = $jResponse["valida"];
    //     $id_entidad = $jResponse["id_entidad"];
    //     $id_depto = $jResponse["id_depto"];
    //     $id_user = $jResponse["id_user"];

    //     if ($valida == 'SI') {
    //         $jResponse = [];
    //         // $inputAll = Input::all();
    //         // $jResponse = $this->saveOrUpdateNota(0,0,0, 0,$id_entidad, $id_depto, $id_user, $inputAll);
    //         $jResponse = $this->saveOrUpdateNota(0,0,0, 0,$id_entidad, $id_depto, $id_user, $inputAll);
    //         // $responseAasinet = $this->validateAndCreateOnAasinetSubAccount($inputAll['id_dinamica'], $id_entidad, $inputAll['ruc'], $inputAll['rasonsocial']);
    //         if($jResponse['success'] === false) {
    //             $jResponse = $jResponse;
    //             goto end;
    //         }
    //     }
    //     end:
    //     return response()->json($jResponse, $jResponse['code']);
    // }

    public function store()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $inputAll = Input::all();
            $jResponse = $this->saveOrUpdatePurchase(0,0,0, 0,$id_entidad, $id_depto, $id_user, $inputAll);
            $responseAasinet = $this->validateAndCreateOnAasinetSubAccount($inputAll['id_dinamica'], $id_entidad, $inputAll['ruc'], $inputAll['rasonsocial']);
            if($responseAasinet['success'] === false) {
                $jResponse = $responseAasinet;
                goto end;
            }
        }
        end:
        return response()->json($jResponse, $jResponse['code']);
    }

    public function update($id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $inputAll = Input::all();
            $id_detraccion = Input::get('detraccion_id');
            $id_retencion = Input::get('retencion_id');
            $id_retencion_detalle = Input::get('retencion_detalle_id');

            $jResponse = $this->saveOrUpdatePurchase($id_compra, $id_detraccion, $id_retencion, $id_retencion_detalle, $id_entidad, $id_depto, $id_user, $inputAll);
            $responseAasinet = $this->validateAndCreateOnAasinetSubAccount($inputAll['id_dinamica'], $id_entidad, $inputAll['ruc'], $inputAll['rasonsocial']);
            if($responseAasinet['success'] === false) {
                $jResponse = $responseAasinet;
                goto end;
            }
        }
        end:
        return response()->json($jResponse,  $jResponse['code']);
    }

    private function rulesProvisionInventario()
    {
        return [
            'id_voucher' => 'required',
            // 'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'es_credito' => 'required',
            // 'id_parent' => 'required',
            // 'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'serie' => ['required', 'min:4', 'max:4'],
            'numero' => 'required|max:8|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            // 'importe' => 'required|numeric',
            // 'id_dinamica' => 'required',
            // 'id_tipotransaccion' => 'required',
        ];
    }

    public function storeProvisionInventario(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        
        $error = 0;
        $jResponse = [];
        if ($valida == 'SI') {
            $msg_error = '';

            DB::beginTransaction();
            try {
                // Validar los campos
                $data = Input::all();
                $validador = Validator::make($data, $this->rulesProvisionInventario());
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }

                $id_comprobante = $data["id_comprobante"];
                $id_proveedor = $data["id_proveedor"];
                $serie = $data["serie"];
                $numero = $data["numero"];
                $id_compra = $data["id_compra"];

                // throw new Exception($id_compra , 1);
                // Validar que el comprobante no este duplicado
                $exist = PurchasesData::existsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, $id_compra);
                if($exist->exists) {
                    throw new Exception('El comprobante ya esta registrado: '.$exist->info, 1);
                }

                // Validar Sunat
                ProvisionsValidation::validateRucInSunat($id_proveedor, $id_comprobante);
                // if ($validSunat['success'] === false && $validSunat['code'] === 202) {
                //     $jResponse = $validSunat;
                //     goto end;
                // }

                $dataCompraGeneral = [];
                $voucher = AccountingData::showVoucher($data['id_voucher']);
                if(count($voucher) === 1) {
                    $dataCompraGeneral["id_anho"] = $voucher[0]->id_anho;
                    $dataCompraGeneral["id_mes"] = $voucher[0]->id_mes;
                } else {
                    throw new Exception('Alto! La operación no esta asignada a un voucher, revise tener un voucher asignado.', 1);
                }

                $dataCompraGeneral["id_proveedor"] = $data['id_proveedor'];
                $dataCompraGeneral["id_comprobante"] = $data['id_comprobante'];
                $dataCompraGeneral["serie"] = $data['serie'];
                $dataCompraGeneral["numero"] = $data['numero'];
                $dataCompraGeneral["fecha_doc"] = $data['fecha_doc'];
                $dataCompraGeneral["id_moneda"] = $data['id_moneda'];
                $dataCompraGeneral["es_credito"] = $data['es_credito'];
                $dataCompraGeneral["es_electronica"] = $data['es_electronica'];
                $dataCompraGeneral["tiene_kardex"] = 'S';
                // DATOS VACIOS
                $dataCompraGeneral["id_parent"] = NULL;
                $dataCompraGeneral["es_transporte_carga"] = 'N';
                $dataCompraGeneral["es_transporte_carga"] = 'N';
                $dataCompraGeneral["fecha_vencimiento"] = NULL;
                $dataCompraGeneral["id_dinamica"] = NULL;
                $dataCompraGeneral["id_tipotransaccion"] = NULL;
                $dataCompraGeneral["taxs"] = NULL;

                $dataCompraGeneral["es_ret_det"] = NULL;
                $dataCompraGeneral["importe"] = NULL;
                $dataCompraGeneral["importe_me"] = NULL;
                $dataCompraGeneral["base_gravada"] = NULL;
                $dataCompraGeneral["igv_gravado"] = NULL;
                $dataCompraGeneral["base_mixta"] = NULL;
                $dataCompraGeneral["igv_mixto"] = NULL;
                $dataCompraGeneral["base_nogravada"] = NULL;
                $dataCompraGeneral["igv_nogravado"] = NULL;
                $dataCompraGeneral["base_sincredito"] = NULL;
                $dataCompraGeneral["base_inafecta"] = NULL;
                $dataCompraGeneral["otros"] = NULL;
                $dataCompraGeneral["fecha_almacen"] = NULL;
                
                if ($dataCompraGeneral["id_moneda"] == "9") { // Dolar Americano
                    $fecha_doc = $dataCompraGeneral['fecha_doc'];
                    $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
                    $dataMoneda = $tipo_cambio[0];
                    $dataCompraGeneral["tipocambio"] = $dataMoneda->cos_venta;
                } else if ($dataCompraGeneral["id_moneda"] == "7") { // Soles
                    $dataCompraGeneral["tipocambio"] = "0";
                }

                $dataCompraGeneral["id_voucher"] = $data['id_voucher'];
                $dataCompraGeneral["id_entidad"] = $id_entidad;
                $dataCompraGeneral["id_depto"] = $id_depto;
                $dataCompraGeneral["id_persona"] = $id_user;

                if($id_compra !== 0) {
                    $compraAnterior = PurchasesData::getCompraFullById($id_compra);
                    PurchasesData::deleteContaAsientoByIdCompra($id_compra);
                    PurchasesData::deleteCompraAsientoCreditoMore($id_compra);
                }
                // throw new Exception('Estamos listos.', 1);
                // $objCompra = PurchasesData::storeCompraMain($id_compra, $dataCompraGeneral);
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_GUARDAR_ACTUALIZAR(
                                :P_ES_CREDITO,
                                :P_ID_PROVEEDOR,
                                :P_ID_COMPROBANTE,
                                :P_ES_ELECTRONICA,
                                :P_ES_TRANSPORTE_CARGA,
                                :P_ID_PARENT,
                                :P_SERIE,
                                :P_NUMERO,
                                :P_FECHA_DOC,
                                :P_FECHA_VENCIMIENTO,
                                :P_ID_DINAMICA,
                                :P_ID_TIPOTRANSACCION,
                                :P_ID_MONEDA,
                                :P_TAXS,

                                :P_ES_RET_DET,
                                :P_TIPOCAMBIO,
                                :P_IMPORTE,
                                :P_IMPORTE_ME,
                                :P_BASE_GRAVADA,
                                :P_IGV_GRAVADO,
                                :P_BASE_MIXTA,
                                :P_IGV_MIXTO,
                                :P_BASE_NOGRAVADA,
                                :P_IGV_NOGRAVADO,
                                :P_BASE_SINCREDITO,
                                :P_BASE_INAFECTA,
                                :P_OTROS,
                                :P_FECHA_ALMACEN,
                                :P_TIENE_KARDEX,

                                :P_ID_VOUCHER,
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO,
                                :P_ID_PERSONA,
                                :P_ID_ANHO,
                                :P_ID_MES,

                                :P_ERROR,
                                :P_ID_COMPRA,
                                :P_MSGERROR
                                );
                                end;");

                $stmt->bindParam(':P_ES_CREDITO', $dataCompraGeneral["es_credito"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PROVEEDOR', $dataCompraGeneral["id_proveedor"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $dataCompraGeneral["id_comprobante"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ES_ELECTRONICA', $dataCompraGeneral["es_electronica"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ES_TRANSPORTE_CARGA', $dataCompraGeneral["es_transporte_carga"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PARENT', $dataCompraGeneral["id_parent"], PDO::PARAM_INT);
                $stmt->bindParam(':P_SERIE', $dataCompraGeneral["serie"], PDO::PARAM_STR);
                $stmt->bindParam(':P_NUMERO', $dataCompraGeneral["numero"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_DOC', $dataCompraGeneral["fecha_doc"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_VENCIMIENTO', $dataCompraGeneral["fecha_vencimiento"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DINAMICA', $dataCompraGeneral["id_dinamica"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $dataCompraGeneral["id_tipotransaccion"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $dataCompraGeneral["id_moneda"], PDO::PARAM_INT);
                $stmt->bindParam(':P_TAXS', $dataCompraGeneral["taxs"], PDO::PARAM_STR);

                $stmt->bindParam(':P_ES_RET_DET', $dataCompraGeneral["es_ret_det"], PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPOCAMBIO', $dataCompraGeneral["tipocambio"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $dataCompraGeneral["importe"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $dataCompraGeneral["importe_me"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_GRAVADA', $dataCompraGeneral["base_gravada"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_GRAVADO', $dataCompraGeneral["igv_gravado"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_MIXTA', $dataCompraGeneral["base_mixta"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_MIXTO', $dataCompraGeneral["igv_mixto"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_NOGRAVADA', $dataCompraGeneral["base_nogravada"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_NOGRAVADO', $dataCompraGeneral["igv_nogravado"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_SINCREDITO', $dataCompraGeneral["base_sincredito"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_INAFECTA', $dataCompraGeneral["base_inafecta"], PDO::PARAM_STR);
                $stmt->bindParam(':P_OTROS', $dataCompraGeneral["otros"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_ALMACEN', $dataCompraGeneral["fecha_almacen"], PDO::PARAM_STR);
                $stmt->bindParam(':P_TIENE_KARDEX', $dataCompraGeneral["tiene_kardex"], PDO::PARAM_STR);
                
                $stmt->bindParam(':P_ID_VOUCHER', $dataCompraGeneral["id_voucher"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $dataCompraGeneral["id_entidad"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $dataCompraGeneral["id_depto"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $dataCompraGeneral["id_persona"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $dataCompraGeneral["id_anho"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $dataCompraGeneral["id_mes"], PDO::PARAM_INT);

                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 1) {
                    throw new Exception($msg_error, 1);
                }
                $data = PurchasesData::updateTotalCompra($id_compra);
                $data = PurchasesData::updateTotalImporteCompra($id_compra);
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["id_compra" => $id_compra];
                $jResponse['code'] = "200";
            }
            catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return $jResponse;
    }

    private function rulesNota()
    {
        return [
            'id_voucher' => 'required',
            // 'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            // 'es_electronica' => 'required',
            'id_parent' => 'required',
            // 'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'serie' => ['required', 'min:4', 'max:4'],
            'numero' => 'required|max:8|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',

            // 'importe' => 'required|numeric',
            // 'id_dinamica' => 'required',
            // 'id_tipotransaccion' => 'required',
        ];
    }

    public function storeNota(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        
        $error = 0;
        $jResponse = [];
        if ($valida == 'SI') {
            $msg_error = '';

            DB::beginTransaction();
            try {
                // Validar los campos
                $data = Input::all();
                $validador = Validator::make($data, $this->rulesNota());
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }

                $id_comprobante = $data["id_comprobante"];
                $id_proveedor = $data["id_proveedor"];
                $serie = $data["serie"];
                $numero = $data["numero"];
                $id_compra = $data["id_compra"];

                // throw new Exception($id_compra , 1);
                // Validar que el comprobante no este duplicado
                $exist = PurchasesData::existsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, $id_compra);
                if($exist->exists) {
                    throw new Exception('El comprobante ya esta registrado: '.$exist->info, 1);
                }

                // Validar Sunat
                ProvisionsValidation::validateRucInSunat($id_proveedor, $id_comprobante);
                // if ($validSunat['success'] === false && $validSunat['code'] === 202) {
                //     $jResponse = $validSunat;
                //     goto end;
                // }

                $dataCompraGeneral = [];
                $voucher = AccountingData::showVoucher($data['id_voucher']);
                if(count($voucher) === 1) {
                    $dataCompraGeneral["id_anho"] = $voucher[0]->id_anho;
                    $dataCompraGeneral["id_mes"] = $voucher[0]->id_mes;
                } else {
                    throw new Exception('Alto! La operación no esta asignada a un voucher, revise tener un voucher asignado.', 1);
                }

                $dataCompraGeneral["id_proveedor"] = $data['id_proveedor'];
                $dataCompraGeneral["id_comprobante"] = $data['id_comprobante'];
                $dataCompraGeneral["id_parent"] = $data['id_parent'];
                $dataCompraGeneral["serie"] = $data['serie'];
                $dataCompraGeneral["numero"] = $data['numero'];
                $dataCompraGeneral["fecha_doc"] = $data['fecha_doc'];
                $dataCompraGeneral["id_moneda"] = $data['id_moneda'];
                $dataCompraGeneral["tiene_kardex"] = 'S';
                // DATOS VACIOS
                $dataCompraGeneral["es_credito"] = 'N';
                $dataCompraGeneral["es_electronica"] = 'N';
                $dataCompraGeneral["es_transporte_carga"] = 'N';
                $dataCompraGeneral["es_transporte_carga"] = 'N';
                $dataCompraGeneral["fecha_vencimiento"] = NULL;
                $dataCompraGeneral["id_dinamica"] = NULL;
                $dataCompraGeneral["id_tipotransaccion"] = NULL;
                $dataCompraGeneral["taxs"] = NULL;

                $dataCompraGeneral["es_ret_det"] = NULL;
                $dataCompraGeneral["importe"] = NULL;
                $dataCompraGeneral["importe_me"] = NULL;
                $dataCompraGeneral["base_gravada"] = NULL;
                $dataCompraGeneral["igv_gravado"] = NULL;
                $dataCompraGeneral["base_mixta"] = NULL;
                $dataCompraGeneral["igv_mixto"] = NULL;
                $dataCompraGeneral["base_nogravada"] = NULL;
                $dataCompraGeneral["igv_nogravado"] = NULL;
                $dataCompraGeneral["base_sincredito"] = NULL;
                $dataCompraGeneral["base_inafecta"] = NULL;
                $dataCompraGeneral["otros"] = NULL;
                $dataCompraGeneral["fecha_almacen"] = NULL;

                if ($dataCompraGeneral["id_moneda"] == "9") { // Dolar Americano
                    $fecha_doc = $dataCompraGeneral['fecha_doc'];
                    $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
                    $dataMoneda = $tipo_cambio[0];
                    $dataCompraGeneral["tipocambio"] = $dataMoneda->cos_venta;
                } else if ($dataCompraGeneral["id_moneda"] == "7") { // Soles
                    $dataCompraGeneral["tipocambio"] = "0";
                }

                $dataCompraGeneral["id_voucher"] = $data['id_voucher'];
                $dataCompraGeneral["id_entidad"] = $id_entidad;
                $dataCompraGeneral["id_depto"] = $id_depto;
                $dataCompraGeneral["id_persona"] = $id_user;

                if($id_compra !== 0) {
                    $compraAnterior = PurchasesData::getCompraFullById($id_compra);
                    PurchasesData::deleteContaAsientoByIdCompra($id_compra);
                    PurchasesData::deleteCompraAsientoCreditoMore($id_compra);
                }
                // throw new Exception('Estamos listos.', 1);
                // $objCompra = PurchasesData::storeCompraMain($id_compra, $dataCompraGeneral);
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_GUARDAR_ACTUALIZAR(
                                :P_ES_CREDITO,
                                :P_ID_PROVEEDOR,
                                :P_ID_COMPROBANTE,
                                :P_ES_ELECTRONICA,
                                :P_ES_TRANSPORTE_CARGA,
                                :P_ID_PARENT,
                                :P_SERIE,
                                :P_NUMERO,
                                :P_FECHA_DOC,
                                :P_FECHA_VENCIMIENTO,
                                :P_ID_DINAMICA,
                                :P_ID_TIPOTRANSACCION,
                                :P_ID_MONEDA,
                                :P_TAXS,

                                :P_ES_RET_DET,
                                :P_TIPOCAMBIO,
                                :P_IMPORTE,
                                :P_IMPORTE_ME,
                                :P_BASE_GRAVADA,
                                :P_IGV_GRAVADO,
                                :P_BASE_MIXTA,
                                :P_IGV_MIXTO,
                                :P_BASE_NOGRAVADA,
                                :P_IGV_NOGRAVADO,
                                :P_BASE_SINCREDITO,
                                :P_BASE_INAFECTA,
                                :P_OTROS,
                                :P_FECHA_ALMACEN,
                                :P_TIENE_KARDEX,

                                :P_ID_VOUCHER,
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO,
                                :P_ID_PERSONA,
                                :P_ID_ANHO,
                                :P_ID_MES,

                                :P_ERROR,
                                :P_ID_COMPRA,
                                :P_MSGERROR
                                );
                                end;");

                $stmt->bindParam(':P_ES_CREDITO', $dataCompraGeneral["es_credito"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PROVEEDOR', $dataCompraGeneral["id_proveedor"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $dataCompraGeneral["id_comprobante"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ES_ELECTRONICA', $dataCompraGeneral["es_electronica"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ES_TRANSPORTE_CARGA', $dataCompraGeneral["es_transporte_carga"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PARENT', $dataCompraGeneral["id_parent"], PDO::PARAM_INT);
                $stmt->bindParam(':P_SERIE', $dataCompraGeneral["serie"], PDO::PARAM_STR);
                $stmt->bindParam(':P_NUMERO', $dataCompraGeneral["numero"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_DOC', $dataCompraGeneral["fecha_doc"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_VENCIMIENTO', $dataCompraGeneral["fecha_vencimiento"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DINAMICA', $dataCompraGeneral["id_dinamica"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $dataCompraGeneral["id_tipotransaccion"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $dataCompraGeneral["id_moneda"], PDO::PARAM_INT);
                $stmt->bindParam(':P_TAXS', $dataCompraGeneral["taxs"], PDO::PARAM_STR);

                $stmt->bindParam(':P_ES_RET_DET', $dataCompraGeneral["es_ret_det"], PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPOCAMBIO', $dataCompraGeneral["tipocambio"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $dataCompraGeneral["importe"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $dataCompraGeneral["importe_me"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_GRAVADA', $dataCompraGeneral["base_gravada"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_GRAVADO', $dataCompraGeneral["igv_gravado"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_MIXTA', $dataCompraGeneral["base_mixta"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_MIXTO', $dataCompraGeneral["igv_mixto"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_NOGRAVADA', $dataCompraGeneral["base_nogravada"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IGV_NOGRAVADO', $dataCompraGeneral["igv_nogravado"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_SINCREDITO', $dataCompraGeneral["base_sincredito"], PDO::PARAM_STR);
                $stmt->bindParam(':P_BASE_INAFECTA', $dataCompraGeneral["base_inafecta"], PDO::PARAM_STR);
                $stmt->bindParam(':P_OTROS', $dataCompraGeneral["otros"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_ALMACEN', $dataCompraGeneral["fecha_almacen"], PDO::PARAM_STR);
                $stmt->bindParam(':P_TIENE_KARDEX', $dataCompraGeneral["tiene_kardex"], PDO::PARAM_STR);

                $stmt->bindParam(':P_ID_VOUCHER', $dataCompraGeneral["id_voucher"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $dataCompraGeneral["id_entidad"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $dataCompraGeneral["id_depto"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $dataCompraGeneral["id_persona"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $dataCompraGeneral["id_anho"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $dataCompraGeneral["id_mes"], PDO::PARAM_INT);

                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 1) {
                    throw new Exception($msg_error, 1);
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["id_compra" => $id_compra];
                $jResponse['code'] = "200";
            }
            catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return $jResponse;
    }

    public function storeOrUpdateCompraDetalleLista(Request $request, $id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        
        $jResponse = [];
        if ($valida == 'SI') {
            // $error = 0;
            // $msg_error = '';
            DB::beginTransaction();
            try {
                // Validar los campos
                $data = Input::all();
                $validador = Validator::make($data,['detalles' => 'required|array|min:1']);
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }
            
                $detalles = $data['detalles'];
                
                foreach ($detalles as $value) {
                    PurchasesData::addPurchasesInvetarioDetailsUPN($id_compra, $value["id_almacen"],
                    $value["id_articulo"], $value["cantidad"],$value["importe"],$value["detalle"], $value["id_detalle"]);

                    // $error = 0;
                    // $msg_error = '';
                    // for($x=1;$x<=200;$x++){
                    //     $msg_error .= "0";
                    // }

                    // $pdo = DB::getPdo();
                    // $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_DETALLE_IUDP2(
                    //                 :P_ID_COMPRA,
                    //                 :P_ID_ALMACEN,
                    //                 :P_ID_ARTICULO,
                    //                 :P_CANTIDAD,
                    //                 :P_IMPORTE,
                    //                 :P_DETALLE,
                    //                 :P_ID_DETALLE,
                    //                 :P_ERROR,
                    //                 :P_MSN_ERROR
                    //                 );
                    //                 end;");
                    // $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ID_ALMACEN', $value["id_almacen"], PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ID_ARTICULO', $value["id_articulo"], PDO::PARAM_INT);
                    // $stmt->bindParam(':P_CANTIDAD', $value["cantidad"], PDO::PARAM_STR);
                    // $stmt->bindParam(':P_IMPORTE', $value["importe"], PDO::PARAM_STR);
                    // $stmt->bindParam(':P_DETALLE', $value["detalle"], PDO::PARAM_STR);

                    // $stmt->bindParam(':P_ID_DETALLE', $value["id_detalle"], PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
                    // $stmt->execute();
                    // if ($error === 1) {
                    //     throw new Exception($msg_error, 1);
                    // }
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["id_compra" => $id_compra];
                $jResponse['code'] = "200";
            }
            catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return $jResponse;
    }

    private function rulesDetalleNota()
    {
        return [
            'cantidad' => 'required',
            'importe' => 'required',
            'id_almacen' => 'required',
            'id_articulo' => 'required',
            'detalle' => 'required',
        ];
    }

    public function storeOrUpdateCompraDetalle(Request $request, $id_compra, $id_detalle) {
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        
        $jResponse = [];
        if ($valida == 'SI') {
            // $error = 0;
            // $msg_error = '';
            DB::beginTransaction();
            try {
                // Validar los campos
                $data = Input::all();
                $validador = Validator::make($data,$this->rulesDetalleNota());
                if ($validador->fails())
                {
                    throw new Exception($validador->errors()->first(), 1);
                }
                // if($id_compra !== 0) {
                //     $compraAnterior = PurchasesData::getCompraFullById($id_compra);
                //     PurchasesData::deleteContaAsientoByIdCompra($id_compra);
                //     PurchasesData::deleteCompraAsientoCreditoMore($id_compra);
                // }
                $id_almacen = $data["id_almacen"];
                $id_articulo = $data["id_articulo"];
                $detalle = $data["detalle"];

                $cantidad = $data["cantidad"];
                $importe = $data["importe"];
                
                // throw new Exception('holas', 1);
                PurchasesData::addPurchasesInvetarioDetailsUPN($id_compra, $id_almacen,$id_articulo,
                        $cantidad,$importe,$detalle, $id_detalle);
                // for($x=1;$x<=200;$x++){
                //     $msg_error .= "0";
                // }
                // $pdo = DB::getPdo();
                // $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_DETALLE_IUDP2(
                //                 :P_ID_COMPRA,
                //                 :P_ID_ALMACEN,
                //                 :P_ID_ARTICULO,
                //                 :P_CANTIDAD,
                //                 :P_IMPORTE,
                //                 :P_DETALLE,
                //                 :P_ID_DETALLE,
                //                 :P_ERROR,
                //                 :P_MSN_ERROR
                //                 );
                //                 end;");
                // $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                // $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                // $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
                // $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                // $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                // $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);

                // $stmt->bindParam(':P_ID_DETALLE', $id_detalle, PDO::PARAM_INT);
                // $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                // $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
                // $stmt->execute();
                // if ($error === 1) {
                //     throw new Exception($msg_error, 1);
                // }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = ["id_detalle" => $id_detalle];
                $jResponse['code'] = "200";
            }
            catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return $jResponse;
    }

    public function storeCompraDetalleImport(Request $request, $id_compra)
    {
        // throw new Exception("Alto, No existe un año activo.", 1);
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user   = $jResponse["id_user"];
        if($valida == 'SI'){
            $jResponse =[];
            // $error = 0;
            // $msg_error = '';
            // $id_detalle = 0;
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

                $jResponse=[];
                // $id_compra = $request->id_compra;
                $import_data = \Excel::load($request->excel, function($reader) use($request) {
                    $reader->select(array('codigo', 'detalle', 'cantidad', 'precio', 'base', 'igv','importe'))->get();
                })->get();

                $import_data_filter = array_filter($import_data->toArray(), function($row) {
                    return (!is_null($row['codigo']) && !empty($row['codigo']));
                });

                if(empty($import_data_filter && sizeOf($import_data_filter))) {
                    throw new Exception('Alto! La lista del excel esta vacía', 1);
                }

                $import_data_filter_validado = [];
                // VALIDAR
                // $id_ctipoigvInicial = null;
                // $importe_sumado_total = 0;
                foreach ($import_data_filter as $value) {
                    // $row = $value;
                    $articulos = WarehousesData::showArticleByAnhoAlmacenCodigo($id_anho,$id_almacen, $value['codigo']);
                    if(count($articulos) === 0) {
                        throw new Exception('Alto! No existe un artículo con: código '.$value['codigo'].'; año: '.$id_anho.'; almacén: '.$id_almacen.'.', 1);
                    }
                    if(count($articulos) > 1) {
                        throw new Exception('Alto! Existe más de un artículo con: código '.$value['codigo'].'; año: '.$id_anho.'; almacén: '.$id_almacen.'.', 1);
                    }
                    $row = new \stdClass();
                    $row->detalle = $value['detalle'];
                    $row->cantidad = $value['cantidad'];
                    $row->precio = $value['precio'];
                    $row->igv = $value['igv'];
                    $row->base = $value['base'];
                    $row->importe = $value['importe'];
                    $row->id_articulo = $articulos[0]->id_articulo;
                    // $row->id_ctipoigv = $articulos[0]->id_ctipoigv;
                    // $row->id_tipoigv = $articulos[0]->id_tipoigv;

                    // if(is_null($id_ctipoigvInicial)) {
                    //     $id_ctipoigvInicial = $articulos[0]->id_ctipoigv;
                    // } else if($id_ctipoigvInicial !== $articulos[0]->id_ctipoigv) {
                    //     throw new Exception('Alto! No se acepta diferente IGVs en una compra.', 1);
                    // }

                    // if($row->id_ctipoigv == 4 || $row->id_ctipoigv == 5 || $row->id_ctipoigv==6) {
                    //     // Corrección.
                    //     $row->igv = 0;
                    //     $row->base = $value['importe'];
                    // }

                    // $importe_sumado_total = $importe_sumado_total + ((float) $row->importe);
                    array_push($import_data_filter_validado,$row);
                }
                // $dcompra = PurchasesData::getCompraById($id_compra);

                // if($importe_sumado_total > $dcompra[0]->importe ){
                //     throw new Exception('Alto! El importe total del detalle de compra es mayor al del comprobante.', 1);
                // }

                // AGREGAR
                
                foreach ($import_data_filter_validado as $value) {
                    // for($x=1;$x<=200;$x++){
                    //     $msg_error .= "0";
                    // }
                    // PurchasesData::addPurchasesInvetarioDetailsUPN($id_compra, $id_almacen,$id_articulo,
                    // $cantidad,$importe,$detalle, $id_detalle);
                    
                    // $id_detalle = 0;
                    // $pdo = DB::getPdo();
                    // $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_DETALLE_IUDP2(
                    //             :P_ID_COMPRA,
                    //             :P_ID_ALMACEN,
                    //             :P_ID_ARTICULO,
                    //             :P_CANTIDAD,
                    //             :P_IMPORTE,
                    //             :P_DETALLE,
                    //             :P_ID_DETALLE,
                    //             :P_ERROR,
                    //             :P_MSN_ERROR
                    //             );
                    //             end;");
                    // $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ID_ARTICULO', $value->id_articulo, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_CANTIDAD', $value->cantidad, PDO::PARAM_STR);
                    // $stmt->bindParam(':P_IMPORTE', $value->importe, PDO::PARAM_STR);
                    // $stmt->bindParam(':P_DETALLE', $value->detalle, PDO::PARAM_STR);
    
                    // $stmt->bindParam(':P_ID_DETALLE', $id_detalle, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    // $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
                    // $stmt->execute();
                    // if ($error === 1) {
                    //     throw new Exception($msg_error, 1);
                    // }

                    // $newId = PurchasesData::getMax('COMPRA_DETALLE','ID_DETALLE')+1;
                    // $newData = [
                    //     'ID_COMPRA' => $id_compra,
                    //     'ID_DETALLE' => $newId,
                    //     'ID_CTIPOIGV' => $value->id_ctipoigv,
                    //     'ID_ARTICULO' => $value->id_articulo,
                    //     'ID_ALMACEN' => $id_almacen,

                    //     'ID_TIPOIGV' => $value->id_tipoigv,
                    //     'DETALLE' => $value->detalle,

                    //     'CANTIDAD' => $value->cantidad,
                    //     'PRECIO' => $value->precio,
                    //     'BASE' => $value->base,
                    //     'IGV' => $value->igv,
                    //     'IMPORTE' => $value->importe,
                    //     'ESTADO' => '1',
                    // ];
                    // $result = PurchasesData::addPurchasesDetails($newData);
                }
                // $data = PurchasesData::updateTotalCompra($id_compra);
                // $data = PurchasesData::updateTotalImporteCompra($id_compra);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = [];
                $code = "200";
                DB::commit();
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage().'::Line: '.$e->getLine();
                $jResponse['data'] = null;
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function deletePurchasesDetails($id_compra, $id_detalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida == 'SI')
        {
            DB::beginTransaction();
            $jResponse =[];
            try{
                $data = PurchasesData::showDetalle($id_detalle);
                foreach ($data as $item){
                    $id_compra = $item->id_compra;
                }
                $result = PurchasesData::deletePurchasesDetails($id_detalle);
                if($result){
                    $data = PurchasesData::updateTotalCompra($id_compra);
                    $data = PurchasesData::updateTotalImporteCompra($id_compra);
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                else
                {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            catch(Exception $e)
            {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function deletePurchasesDetailsAll($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida == 'SI')
        {
            DB::beginTransaction();
            $jResponse =[];
            try{
                $result = PurchasesData::deleteCompraDetalleMore($id_compra);
                if($result){
                    $data = PurchasesData::updateTotalCompra($id_compra);
                    $data = PurchasesData::updateTotalImporteCompra($id_compra);
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                else
                {
                    DB::commit();
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            catch(Exception $e)
            {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    // ===============================================================

    public function saveOrUpdatePurchase($id_compra, $id_detraccion, $id_retencion, $id_retencion_detalle, $id_entidad, $id_depto, $id_user, $data) {
        $jResponse = [];
        try {
            // Validar los campos
            $validDocumento = ProvisionsValidation::validationCall("validateDocument" . $data['id_comprobante']);
            if ($validDocumento->invalid) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validDocumento->message;
                $jResponse['data'] = NULL;
                $jResponse['code'] = "202";
                goto end;
            }

            $id_comprobante = $data["id_comprobante"];
            $id_proveedor = $data["id_proveedor"];
            $serie = $data["serie"];
            $numero = $data["numero"];

            // $id_compra = Input::get("id_compra");
            // Validar que el comprobante no este duplicado
            $exist = PurchasesData::existsProviderDocument($serie, $numero, $id_comprobante, $id_proveedor, $id_compra);
            if($exist->exists) {
                $jResponse['success'] = false;
                $jResponse['message'] = 'El comprobante ya esta registrado: '.$exist->info;
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
                goto end;
            }

            try {
                // Validar Sunat
                ProvisionsValidation::validateRucInSunat($id_proveedor, $id_comprobante);
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
                goto end;
            }

            // if ($validSunat['success'] === false && $validSunat['code'] === 202) {
            //     $jResponse = $validSunat;
            //     goto end;
            // }

            $dataCompraGeneral = [];
            $voucher = AccountingData::showVoucher($data['id_voucher']);
            if(count($voucher) === 1) {
                $dataCompraGeneral["id_anho"] = $voucher[0]->id_anho;
                $dataCompraGeneral["id_mes"] = $voucher[0]->id_mes;
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "La operación no esta asignada a un voucher, revise tener un voucher asignado.";
                $jResponse['data'] = NULL;
                $jResponse['code'] = "202";
                // $code = "202";
                goto end;
            }

            $dataDinamicaCompra  = null;
            $dataDinamicaDetraccion = null;
            $dataDinamicaRetencion  = null;
            $dataCompraGeneral["es_ret_det"] = $data['es_ret_det'];
            $dataCompraGeneral["es_ret_avanzada"] = $data['es_ret_avanzada'];

            if ($dataCompraGeneral["es_ret_det"] === 'D') {
                $id_modulo = 14; // Tesoreria
                //$id_tipotransaccion_detraccion = 23; // Detracciones
                $tipotransaccion_detraccion = SalesData::listTypeTransaccion('DE', $id_modulo, $id_entidad,  $dataCompraGeneral["id_anho"], $id_depto);
                if(count($tipotransaccion_detraccion)>1){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Existe mas de un tipo de transacción para detracciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                } else if(count($tipotransaccion_detraccion)===0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe tipo de transacción para detracciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                }
                $id_tipotransaccion_detraccion=$tipotransaccion_detraccion[0]->id_tipoventa;

                $dataDinamicaDetraccion = AccountingData::listAccountingEntryModule($id_entidad,$id_depto,$dataCompraGeneral["id_anho"],$id_modulo, $id_tipotransaccion_detraccion,null);
                if(count($dataDinamicaDetraccion)>1){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Existe mas de una dinámica contable para detracciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                } else if(count($dataDinamicaDetraccion)===0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe dinámica contable para detracciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                }

                $voucher_pago = AccountingData::showVoucher($data['detraccion_id_voucher_mb']);
                if(count($voucher_pago) !== 1) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La operación-detracción no esta asignado a un voucher, revise tener un voucher asignado.";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    goto end;
                }

                // Validar campos detracción.
                $validDetraccion = ProvisionsValidation::validateDetraccion($voucher_pago[0]->id_tipoasiento);
                if ($validDetraccion->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validDetraccion->message;
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    //$code = "202";
                    goto end;
                }

            }else if ($dataCompraGeneral["es_ret_det"] === 'R') {
                $id_modulo = 14; // Tesoreria
                //$id_tipotransaccion_retencion = 24; // Retenciones
                $tipotransaccion_retencion = SalesData::listTypeTransaccion('RE', $id_modulo, $id_entidad, $dataCompraGeneral["id_anho"], $id_depto);
                if(count($tipotransaccion_retencion)>1){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Existe mas de un tipo de transacción para retenciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                } else if(count($tipotransaccion_retencion)===0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe tipo de transacción para retenciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                }
                $id_tipotransaccion_retencion=$tipotransaccion_retencion[0]->id_tipoventa;

                $dataDinamicaRetencion = AccountingData::listAccountingEntryModule($id_entidad, $id_depto, $dataCompraGeneral["id_anho"], $id_modulo, $id_tipotransaccion_retencion,null);
                if (count($dataDinamicaRetencion) > 1) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Existe mas de una dinámica contable para retenciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                } else if (count($dataDinamicaRetencion) === 0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe dinámica contable para retenciones";
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    // $code = "202";
                    goto end;
                }
                // Validar campos retención.
                $validDetraccion = ProvisionsValidation::validateRetencion();
                if ($validDetraccion->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validDetraccion->message;
                    $jResponse['data'] = NULL;
                    $jResponse['code'] = "202";
                    //$code = "202";
                    goto end;
                }
            }

            $dataCompraGeneral["tipo"] = $data['tipo'];
            $dataCompraGeneral["es_credito"] = $data['es_credito'];
            $dataCompraGeneral["id_proveedor"] = $data['id_proveedor'];
            $dataCompraGeneral["id_comprobante"] = $data['id_comprobante'];
            $dataCompraGeneral["es_electronica"] = $data['es_electronica'];
            $dataCompraGeneral["es_transporte_carga"] = $data['es_transporte_carga'];
            $dataCompraGeneral["id_parent"] = $data['id_parent'];
            $dataCompraGeneral["serie"] = $data['serie'];
            $dataCompraGeneral["numero"] = $data['numero'];
            $dataCompraGeneral["fecha_doc"] = $data['fecha_doc'];
            $dataCompraGeneral["fecha_vencimiento"] = $data['fecha_vencimiento'];
            $dataCompraGeneral["id_moneda"] = $data['id_moneda'];
            $dataCompraGeneral["importe"] = $data['importe'];
            $dataCompraGeneral["taxs"] = $data['taxs'];
            $dataCompraGeneral["base_inafecta"] = $data['base_inafecta'];
            $dataCompraGeneral["otros"] = $data['otros'];
            $dataCompraGeneral["id_dinamica"] = $data['id_dinamica'];
            $dataCompraGeneral["id_tipotransaccion"] = $data['id_tipotransaccion'];

            if ($dataCompraGeneral["id_moneda"] == "9") { // Dolar Americano
                $fecha_doc = $dataCompraGeneral['fecha_doc'];
                $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
                $dataMoneda = $tipo_cambio[0];
                $dataCompraGeneral["tipocambio"] = $dataMoneda->cos_venta;
            } else if ($dataCompraGeneral["id_moneda"] == "7") { // Soles
                $dataCompraGeneral["tipocambio"] = "0";
            }

            $dataCompraGeneral["id_voucher"] = $data['id_voucher'];
            $dataCompraGeneral["id_entidad"] = $id_entidad;
            $dataCompraGeneral["id_depto"] = $id_depto;
            $dataCompraGeneral["id_persona"] = $id_user;

            $es_ret_det_anterior = null;
            if($id_compra !== 0) {
                $compraAnterior = PurchasesData::getCompraFullById($id_compra);
                $es_ret_det_anterior = $compraAnterior[0]->es_ret_det;
                // $dataReplace = array("id_compra"=>null);
                // PurchasesData::destroyDetraccionsByIdCompra($id_compra);
                // PurchasesData::updatePedidoCompra_nulls($dataReplace,$id_compra);
                // PurchasesData::deleteCompraDetalleMore($id_compra);
                PurchasesData::deleteContaAsientoByIdCompra($id_compra);
                // PurchasesData::deleteCompraAsientoMore($id_compra);
                //PurchasesData::deleteContaAsientoCreditoByIdCompra($id_compra);
                PurchasesData::deleteCompraAsientoCreditoMore($id_compra);
            }
            // Validamos que no aya cambiado si es ret o det
            if(($id_compra !== 0) && ($data["es_ret_det"] !== $es_ret_det_anterior)) {
                if($data["es_ret_det"] === '0') {
                    // Eliminar todos det and ret.
                    if($es_ret_det_anterior === 'D') {
                        // Eliminar detracciones.
                        PurchasesData::destroyDetraccionsByIdCompra($id_compra);
                        PurchasesData::destroyDetraccionCabeceraByIdDetraccion($id_detraccion);
                        PurchasesData::destroyContaAsiento(11, $id_detraccion );
                        $id_detraccion = 0;
                    } else if($es_ret_det_anterior === 'R') {
                        // Eliminar retenciones.
                        PurchasesData::destroyRetencionCompraByIdCompra($id_compra, $id_retencion);
                        $countDetails = PurchasesData::getRetencionCompra($id_retencion);
                        if($countDetails === 0) {
                            PurchasesData::destroyRetencionCabeceraByIdRetencion($id_retencion);
                        }
                        PurchasesData::destroyContaAsiento(12, $id_retencion );
                        $id_retencion = 0;
                        $id_retencion_detalle = 0;
                    }
                } else if($data["es_ret_det"] === 'D') {
                    // Eliminar retenciones.
                    PurchasesData::destroyRetencionCompraByIdCompra($id_compra, $id_retencion);
                    $countDetails = PurchasesData::getRetencionCompra($id_retencion);
                    if($countDetails === 0) {
                        PurchasesData::destroyRetencionCabeceraByIdRetencion($id_retencion);
                    }
                    PurchasesData::destroyContaAsiento(12, $id_retencion );
                    $id_retencion = 0;
                    $id_retencion_detalle = 0;
                } else if($data["es_ret_det"] === 'R') {
                    // Eliminar detracciones.
                    PurchasesData::destroyDetraccionsByIdCompra($id_compra);
                    PurchasesData::destroyDetraccionCabeceraByIdDetraccion($id_detraccion);
                    PurchasesData::destroyContaAsiento(11, $id_detraccion );
                    $id_detraccion = 0;
                }
            }

            $objCompra = PurchasesData::storeCompraMain($id_compra, $dataCompraGeneral);
            if ($objCompra['error'] === 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = $objCompra["message"];
                $jResponse['data'] = NULL;
                $jResponse['code'] = "202";
                // $code = "202";
                goto end;
            } else {
                $data["id_compra"] = $objCompra["data"];
            }
            if ($data["es_ret_det"] === 'D') {
               // $id_detraccion, $id_dinamica_detraccion, $id_entidad, $id_depto, $id_anho ,$id_mes, $id_user, $into_dataDetraccion
                $responseDetraccion =  $this->saveOrUpdateDetraccion($id_detraccion, $dataDinamicaDetraccion[0]->id_dinamica, $id_entidad, $id_depto, $dataCompraGeneral["id_anho"],
                    $dataCompraGeneral["id_mes"], $id_user, $data);

                if($responseDetraccion['success'] === false) {
                    $jResponse = $responseDetraccion;
                    goto end;
                } else {
                    $id_detraccion = $responseDetraccion["data"];
                }
            } else if($data["es_ret_det"] === 'R') {
                $responseRetencion = $this->saveOrUpdateRetencion($id_retencion, $id_retencion_detalle, $dataDinamicaRetencion[0]->id_dinamica,
                    $id_entidad, $id_depto, $dataCompraGeneral["id_anho"], $dataCompraGeneral["id_mes"], $id_user,$data);
                if($responseRetencion['success'] === false) {
                    $jResponse = $responseRetencion;
                    goto end;
                } else {
                    $id_retencion = $responseRetencion["id_retencion"];
                    $id_retencion_detalle = $responseRetencion["id_retencion_detalle"];
                }
            }
            $jResponse['success'] = true;
            $jResponse['message'] = "OK";
            $jResponse['data'] = [
                "id_compra" => $data["id_compra"],
                "detraccion_id" => $id_detraccion,
                "retencion_id" => $id_retencion,
                "retencion_detalle_id" => $id_retencion_detalle
            ];
            $jResponse['code'] = "200";
            // $code = "201";
        }
        catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getMessage().'Line: '.$e->getLine();
            $jResponse['data'] = [];
            $jResponse['code'] = "400";
            // $code = "400";
        }
        end:
        return $jResponse;
    }

    public function saveOrUpdateRetencion($id_retencion, $id_retencion_detalle, $id_dinamicaRetencion, $id_entidad, $id_depto,
                                          $id_anho, $id_mes, $id_user, $into_dataRetencion ) {
        $jResponse = [];
        try {
            // Guardar cabecera de la retencion
            $dataRetencion = [];
            $dataRetencion["id_entidad"] = $id_entidad;
            $dataRetencion["id_depto"] = $id_depto;
            $dataRetencion["id_persona"] = $id_user;
            $dataRetencion["id_mediopago"] = '001'; // DEPOSITO EN CUENTA, Table => MEDIO_PAGO.

            $dataRetencion["id_voucher"] = $into_dataRetencion['retencion_id_voucher_mb'];
            $dataRetencion["id_proveedor"] = $into_dataRetencion['id_proveedor'];
            $dataRetencion["id_anho"] = $id_anho;
            $dataRetencion["id_mes"] = $id_mes;
            $dataRetencion["id_checkera"] = null;
            $dataRetencion["id_ctabancaria" ] = null;

            $dataRetencion["id_moneda"] = $into_dataRetencion['id_moneda'];
            $dataRetencion["numero"] = null;

            $dataRetencion["retencion_nro"] = $into_dataRetencion['retencion_nro'];
            $dataRetencion["retencion_serie"] = $into_dataRetencion['retencion_serie'];
            $dataRetencion["retencion_fecha"] = $into_dataRetencion['retencion_fecha'];

            if($id_retencion === 0) {
                // Create new.
                $responseRetencionExiste = PurchasesData::getRetencionByNroAndSerie($into_dataRetencion["retencion_nro"], $into_dataRetencion["retencion_serie"]);
                if (count($responseRetencionExiste) > 0) {
                    $id_retencion = $responseRetencionExiste[0]->id_retencion;
                }
            }
            $responseRet = $this->guardarActualizarRetencion($id_retencion, $dataRetencion);

            if ($responseRet['success'] === false) {
                $jResponse = $responseRet;
                $jResponse['success'] = false;
                $jResponse["code"] = "202";
                //$code = "202";
                goto end;
            } else if($responseRet['success'] === true) {
                $id_retencion = $responseRet["data"];
            }

            // Guardar detalle
            $dataRetencionDetalle = [];
            $dataRetencionDetalle["id_retencion"] = $id_retencion;
            $dataRetencionDetalle["id_compra"] = $into_dataRetencion['id_compra'];
            $dataRetencionDetalle["id_dinamica"] = $id_dinamicaRetencion;
            $dataRetencionDetalle["importe_total"] = $into_dataRetencion['retencion_importe'];
            $dataRetencionDetalle["importe_ret"] = $into_dataRetencion['retencion_importe'];
            $dataRetencionDetalle["importe_ret_me"] = 0;

            $responseRetDet = $this->guardarActualizarRetencionDetalle($id_retencion_detalle, $dataRetencionDetalle);

            if ($responseRetDet['success'] === false) {
                $jResponse = $responseRetDet;
                $jResponse['success'] = false;
                $jResponse["code"] = "202";
                goto end;
            } else if($responseRetDet['success'] === true) {
                $id_retencion_detalle = $responseRetDet["data"];
            }
            $dataRetencionFinalizar = [];
            $dataRetencionFinalizar["id_retencion"] = $id_retencion;
            $responseRetDetFinalizar = $this->finalizarRetencion($dataRetencionFinalizar);
            if ($responseRetDetFinalizar['success'] === false) {
                $jResponse = $responseRetDetFinalizar;
                $jResponse['success'] = false;
                $jResponse["code"] = "202";
                // $code = "202";
                goto end;
            }

            $jResponse['success'] = true;
            $jResponse["id_retencion"] = $id_retencion;
            $jResponse["id_retencion_detalle"] = $id_retencion_detalle;
            $jResponse["code"] = "201";
        }
        catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $jResponse['code'] = "400";
                // $code = "400";
        }
        end:
        return $jResponse;
    }

    public function saveOrUpdateDetraccion($id_detraccion, $id_dinamica_detraccion, $id_entidad, $id_depto, $id_anho ,$id_mes, $id_user, $into_dataDetraccion) {

        $jResponse = [];
        $error = 0;
        $msg_error = "";
        try {
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $id_mediopago = '001'; // DEPOSITO EN CUENTA, Table => MEDIO_PAGO.
            $autodetraccion = 'N';
            $id_checkera = null;
            $numero = null;
            $detraccion_importe_me = 0;

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DETRACCION(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_MEDIOPAGO,:P_ID_VOUCHER,:P_ID_PERSONA,:P_ID_PROVEEDOR,:P_ID_ANHO,:P_ID_MES,:P_ID_CTABANCARIA,:P_ID_CHEQUERA,:P_ID_OPERACION,:P_ID_TIPOBIENSERVICIO,:P_ID_MONEDA,:P_AUTODETRACCION,:P_NUMERO,:P_NRO_CONSTANCIA,:P_NRO_OPERACION,:P_FECHA_EMISION,:P_ID_COMPRA,:P_ID_DINAMICA,:P_IMPORTE,:P_IMPORTE_ME,:P_ID_DETRACCION, :P_ERROR, :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_VOUCHER', $into_dataDetraccion['detraccion_id_voucher_mb'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PROVEEDOR', $into_dataDetraccion['id_proveedor'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO',$id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CTABANCARIA', $into_dataDetraccion['detraccion_cuenta_bancaria'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CHEQUERA', $id_checkera, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_OPERACION', $into_dataDetraccion['detraccion_tipo_operacion'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOBIENSERVICIO', $into_dataDetraccion['detraccion_tipo_bien_servicio'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA', $into_dataDetraccion['id_moneda'], PDO::PARAM_INT);
            $stmt->bindParam(':P_AUTODETRACCION', $autodetraccion, PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_STR);
            $stmt->bindParam(':P_NRO_CONSTANCIA', $into_dataDetraccion['detraccion_nro_constancia'], PDO::PARAM_STR);
            $stmt->bindParam(':P_NRO_OPERACION', $into_dataDetraccion['detraccion_nro_operacion'], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_EMISION', $into_dataDetraccion['detraccion_fecha'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_COMPRA', $into_dataDetraccion['id_compra'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica_detraccion, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $into_dataDetraccion['detraccion_importe'], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_ME', $detraccion_importe_me, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DETRACCION', $id_detraccion, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if ($error == 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                // $jResponse['data'] = ['id_detraccion' => $id_detraccion];
                $jResponse['data'] = $id_detraccion;
                $jResponse['code'] ="200";
                // $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = null;
                $jResponse['code'] ="202";
                // $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = null;
            $jResponse['code'] ="202";
            // $code = "202";
        }
        return $jResponse;
    }

    public function validateAndCreateOnAasinetSubAccount($id_dinamica, $id_entidad, $id_ruc, $razon_social) {
        $jResponse = [];
        $jResponse['success'] = true;
        $jResponse['message'] = "";
        $jResponse['data'] = NULL;
        $jResponse['code'] = "200";
        $asientosContables = PurchasesData::getListAsientosByIdDinamica($id_dinamica);
        foreach ($asientosContables as $value) {
            if($value->unico_ctacte === 'X') {
                $jResponse = AccountingController::createSubAccountOnAasinet($id_entidad, $id_ruc, $razon_social);
            }
        }
        return $jResponse;
    }

    public function listPaymentsByIdCompra($id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = PurchasesData::getPaymentsByCompraId($id_compra);
                if (count($data) > 0) {
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function show($id_provision) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $data = PurchasesData::getCompraFullById($id_provision);

                if (count($data) > 0) {
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function storeFinalizar($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $error = 0;
            $msg_error = "";
            try {
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                // Terminar el registro de la compra.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_FINALIZAR(:P_ID_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_compra;
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
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function storeFinalizarInventario($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $error = 0;
            $msg_error = "";
            try {
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                // Terminar el registro de la compra.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_FINALIZAR_INV(:P_ID_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_compra;
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
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function storeFinalizarInventarioNota($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $error = 0;
            $msg_error = "";
            try {
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                // Terminar el registro de la compra.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_FINALIZAR_INV_NOTA(:P_ID_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();

                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_compra;
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
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    private function updateRetencion($id_retencion, $dataRetencion) {
        $jResponse = [];
        // $id_retencion = 0;
        $error = 0;
        $msg_error = "";
        try {
            try {
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_ACTUALIZAR_RETENCION(
                :P_ID_RETENCION, :P_ID_CTABANCARIA, :P_ID_CHEQUERA, :P_ID_MONEDA, :P_NUMERO, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_ENTIDAD', $dataRetencion["id_entidad"], PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_DEPTO', $dataRetencion["id_depto"], PDO::PARAM_STR);
                //$stmt->bindParam(':P_ID_MEDIOPAGO', $dataRetencion["id_mediopago"], PDO::PARAM_STR);
                //$stmt->bindParam(':P_ID_VOUCHER', $dataRetencion["id_voucher"], PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_PERSONA', $dataRetencion["id_persona"], PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_PROVEEDOR', $dataRetencion["id_proveedor"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CTABANCARIA', $dataRetencion["id_ctabancaria"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CHEQUERA', $dataRetencion["id_checkera"], PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_ANHO', $dataRetencion["id_anho"], PDO::PARAM_INT);
                //$stmt->bindParam(':P_ID_MES', $dataRetencion["id_mes"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $dataRetencion["id_moneda"], PDO::PARAM_INT);
                $stmt->bindParam(':P_NUMERO', $dataRetencion["numero"], PDO::PARAM_STR);
                //$stmt->bindParam(':P_FECHA_EMISION', $dataRetencion["retencion_fecha"], PDO::PARAM_STR);
                //$stmt->bindParam(':P_SERIE', $dataRetencion["retencion_serie"], PDO::PARAM_STR);
                //$stmt->bindParam(':P_NRO_RETENCION', $dataRetencion["retencion_nro"], PDO::PARAM_STR);
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
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
        }
        return $jResponse;
    }

    private function guardarActualizarRetencion($id_retencion, $dataRetencion)
    {
        $jResponse = [];
        $msg_error = "";
        // $id_retencion = 0;
        $error = 0;
        try {
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_RETENCION(
            :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_MEDIOPAGO, :P_ID_VOUCHER,
            :P_ID_PERSONA, :P_ID_PROVEEDOR, :P_ID_CTABANCARIA, :P_ID_CHEQUERA,
            :P_ID_ANHO, :P_ID_MES, :P_ID_MONEDA, :P_NUMERO, :P_FECHA_EMISION,
            :P_SERIE, :P_NRO_RETENCION, :P_ID_RETENCION, :P_ERROR, :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $dataRetencion["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $dataRetencion["id_depto"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MEDIOPAGO', $dataRetencion["id_mediopago"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_VOUCHER', $dataRetencion["id_voucher"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $dataRetencion["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PROVEEDOR', $dataRetencion["id_proveedor"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CTABANCARIA', $dataRetencion["id_ctabancaria"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CHEQUERA', $dataRetencion["id_checkera"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $dataRetencion["id_anho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $dataRetencion["id_mes"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MONEDA', $dataRetencion["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_NUMERO', $dataRetencion["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_EMISION', $dataRetencion["retencion_fecha"], PDO::PARAM_STR);
            $stmt->bindParam(':P_SERIE', $dataRetencion["retencion_serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NRO_RETENCION', $dataRetencion["retencion_nro"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_RETENCION', $id_retencion, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();

            if ($error == 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $id_retencion;
                $jResponse['code'] = "200";
                // $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
                // $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "202";
            // $code = "202";
        }
        return $jResponse;
    }

    private function guardarActualizarRetencionDetalle($id_retencion_detalle, $dataRetencionDetalle) {
        $jResponse = [];
        $error = 0;
        $msg_error = "";
        try {
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_RETENCION_DETALLE(
            :P_ID_RETENCION, :P_ID_COMPRA, :P_ID_DINAMICA,
            :P_IMPORTE_TOTAL, :P_IMPORTE_RET, :P_IMPORTE_RET_ME, :P_ID_RETDETALLE,
            :P_ERROR, :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_RETENCION', $dataRetencionDetalle["id_retencion"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA', $dataRetencionDetalle["id_compra"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $dataRetencionDetalle["id_dinamica"], PDO::PARAM_INT);
            $stmt->bindParam(':P_IMPORTE_TOTAL', $dataRetencionDetalle["importe_total"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_RET', $dataRetencionDetalle["importe_ret"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_RET_ME', $dataRetencionDetalle["importe_ret_me"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_RETDETALLE', $id_retencion_detalle, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

            $stmt->execute();
            if ($error == 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $id_retencion_detalle;
                $jResponse['code'] = "200";
                // $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = [];
                $jResponse['code'] = "202";
                // $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['error'] = "202";
            // $error = "202";
        }
        end:
        return $jResponse;
    }

    private function finalizarRetencion($dataRetencion) {
        $jResponse = [];
        $error = 0;
        $msg_error = "";
        try {
            try {
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }

                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_FINALIZAR_RETENCION(
                :P_ID_RETENCION, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_RETENCION', $dataRetencion["id_retencion"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
        }
        return $jResponse;
    }

    public function destroy($id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $data = array("id_compra"=>null);
                // PurchasesData::destroyDetraccionsByIdCompra($id_compra);

                // CAJA_DETRACCION_COMPRA
                // CAJA_DETRACCION
                // CAJA_RETENCION_COMPRA
                // CAJA_RETENCION
                // CONTA_ASIENTO -- Detraccion
                // CONTA_ASIENTO -- Retención

                // CONTA_ASIENTO -- Compras
                // CONTA_ASIENTO -- Retención RH
                // COMPRA_ASIENTO
                // COMPRA

                $notas = PurchasesData::getCompraChildsById($id_compra);
                if(count($notas)) {
                    throw new Exception("Alto!. La compra tiene ".count($notas)." nota, eliminé primero la nota: [".$notas[0]->serie." - ".$notas[0]->numero."].", 1);
                }

                PurchasesData::updatePedidoCompra_nulls($data,$id_compra);

                PurchasesData::deleteCompraDetalleOfKardex($id_compra);  // Solo para compras de inventarios.

                PurchasesData::deleteCompraDetalleMore($id_compra);

                PurchasesData::deleteContaAsientoByIdCompra($id_compra);

                PurchasesData::deleteContaAsientoRetencionRHByIdCompra($id_compra);

                PurchasesData::deleteCompraAsientoMore($id_compra);

                PurchasesData::deleteCompra($id_compra);

                DB::commit();

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
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

    public  function getProvisionsForNotes() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_anho = 0;
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                    $id_anho_actual = $item->id_anho_actual;
                }
                // $rpta = AccountingData::AccountingYear($id_entidad);
                // if ($rpta["nerror"] == 0) {
                //     $id_anho = $rpta["id_anho"];
                // } else {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = $rpta["msgerror"];
                //     $jResponse['data'] = NULL;
                //     $code = "202";
                //     goto end;
                // }
                $id_proveedor = $this->request->query('id_proveedor');
                if ($id_entidad==7124) {
                    $data = PurchasesData::getComprasByIdProveedorForNotesUPEU($id_entidad, $id_depto, $id_anho, $id_proveedor);
                } else {
                    $data = PurchasesData::getComprasByIdProveedorForNotes($id_entidad, $id_depto, $id_anho,$id_proveedor);
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

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
}
