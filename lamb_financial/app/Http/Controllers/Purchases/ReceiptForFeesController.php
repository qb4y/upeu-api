<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 11/03/2019
 * Time: 13:40
 */

namespace App\Http\Controllers\Purchases;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Purchases\Validations\ProvisionsValidation;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\SetupData;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Controllers\Accounting\Setup\AccountingController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use PDO;

class ReceiptForFeesController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index() {
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
                $fecha_doc = $this->request->query('fecha_doc');

                $id_voucher = $this->request->query('id_voucher');

                //dd($id_entidad, $id_depto, $id_user, $id_proveedor, $fecha_doc);

                if(($id_proveedor !== '' && $id_proveedor !== null) && ($fecha_doc !== '' && $fecha_doc !== null)) {
                    $data = PurchasesData::getRecibosHonorariosByIdProveedorAndFechaDoc($id_entidad, $id_depto, $id_user, $id_proveedor, $fecha_doc);
                } else if($id_voucher !== '' && $id_voucher !== null) {
                    $data = PurchasesData::getRecibosHonorariosByIdVoucher($id_entidad, $id_depto, $id_user, $id_voucher);
                } else {
                    $data = PurchasesData::getRecibosHonorarios($id_entidad, $id_depto, $id_user);
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

    public function show($id_receipt_for_fees) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = PurchasesData::getReciboHonorarioById($id_receipt_for_fees);
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

    public function update($id) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse =[];
            try {
                $intoData = Input::all();

                $jResponse = $this->saveOrUpdate($id,$intoData,$id_entidad, $id_depto , $id_user);
                
                $responseAasinet = $this->validateAndCreateOnAasinetSubAccount($intoData['id_dinamica'], $id_entidad, $intoData['ruc'], $intoData['rasonsocial']);
                if($responseAasinet['success'] === false) {
                    $jResponse = $responseAasinet;
                    goto end;
                }
            } catch (Exception $e) {
                $jResponse['error'] = 1;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return response()->json($jResponse, $jResponse['code']);
    }

    public function getSuspensionRenta() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if ($valida == 'SI') {
            $jResponse =[];
            try {
                $id_proveedor = $this->request->query('id_proveedor');
                // $id_anho = $this->request->query('id_anho');
                $data = PurchasesData::getSuspesionRenta($id_entidad, $id_proveedor);

                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                } else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['error'] = 1;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addSuspensionRenta() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse =[];
            try {

                $id_anho = Input::get("id_anho");
                $fecha_presentacion = Input::get("fecha_presentacion");
                $nro_operacion = Input::get("nro_operacion");
                $id_proveedor = Input::get("id_proveedor");
                // $id_suspencion = PurchasesData::getMax('compra_suspencion','id_suspension')+1;

                $entidads = SetupData::entityDetail($id_entidad);
                $id_empresa = null;
                foreach ($entidads as $value) {
                    $id_empresa = $value->id_empresa;
                }
                
                // Validar que solo exista uno por año.
                $existe = PurchasesData::existeSuspencionEnElAnho($id_entidad, $id_anho, $id_proveedor);
                if($existe) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Ya existe una suspensión registrada para el proveedor en el año '.$id_anho;
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }

                $dataSuspencion = array(
                    // "id_suspension"=>$id_suspencion,
                    "id_persona"=>$id_user,
                    "id_empresa"=>$id_empresa,
                    // "id_entidad"=>$id_entidad,
                    // "id_depto"=>$id_depto,
                    "id_anho"=>$id_anho,
                    "id_proveedor"=>$id_proveedor,
                    "fecha_emision"=>DB::raw('sysdate'),
                    "fecha_presentacion"=>$fecha_presentacion,
                    "nro_operacion"=>$nro_operacion,
                );
                // id_anho: 2020
                // fecha_presentacion: "2019-10-02"
                // nro_operacion: "11072794"
                // id_proveedor: "28204"
                DB::table('ELISEO.COMPRA_SUSPENCION')->insert($dataSuspencion);

                // $data = PurchasesData::addSuspesionRenta($dataSuspencion);
                // if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
                // } else {
                // $jResponse['success'] = false;
                // $jResponse['message'] = 'The item does not exist';
                // $jResponse['data'] = [];
                // $code = "202";
                // }
            } catch(Exception $e){
                $jResponse['error'] = 1;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $error = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function store()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $intoData = Input::all();
            $jResponse = $this->saveOrUpdate(0,$intoData,$id_entidad, $id_depto , $id_user);
            
            $responseAasinet = $this->validateAndCreateOnAasinetSubAccount($intoData['id_dinamica'], $id_entidad, $intoData['ruc'], $intoData['rasonsocial']);
            if($responseAasinet['success'] === false) {
                $jResponse = $responseAasinet;
                goto end;
            }
        }
        end:
        return response()->json($jResponse, $jResponse["code"]);
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

    public function saveOrUpdate($id_recibo_honorario, $intoData, $id_entidad, $id_depto, $id_user) {
        $jResponse =[];
        $validDocumento = ProvisionsValidation::validationCall("validateDocument".$intoData["id_comprobante"]);
        if($validDocumento->invalid)
        {
            $jResponse['success'] = false;
            $jResponse['message'] = $validDocumento->message;
            $jResponse['code'] = "202";
            // $code = "202";
            goto end;
        }
        $id_comprobante = $intoData["id_comprobante"];
        $id_proveedor = $intoData["id_proveedor"];
        $serie = $intoData["serie"];
        $numero = $intoData["numero"];
        $exist = PurchasesData::existsProviderDocument($serie,$numero,$id_comprobante,$id_proveedor,$id_recibo_honorario);
        if($exist->exists)
        {
            $jResponse['success'] = false;
            $jResponse['message'] = 'El comprobante ya esta registrado: '.$exist->info;
            $jResponse['data'] = null;
            $jResponse['code'] = "202";
            // $code = "202";
            goto end;
        }

        try {
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
        
        $data = [];
        $data["id_proveedor"] = $id_proveedor;
        $data["id_comprobante"] = $id_comprobante;
        $data["es_electronica"] = $intoData['es_electronica'];
        $data["serie"] = $intoData['serie'];
        $data["numero"] = $intoData['numero'];
        $data["fecha_doc"] = $intoData['fecha_doc'];
        $data["id_moneda"] = $intoData['id_moneda'];
        $data["importe"] = $intoData['importe'];
        $data["importe_retener"] = $intoData['importe_retener'];
        if($data["id_moneda"] == "9") { // Dolar Americano
            $fecha_doc = $data['fecha_doc'];
            $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
            $dataMoneda = $tipo_cambio[0];
            $data["tipocambio"] = $dataMoneda->cos_compra;
        } else if($data["id_moneda"] == "7") { // Soles
            $data["tipocambio"] = "0";
        }
        $data["id_voucher_compra"] = $intoData['id_voucher_compra'];
        $data["id_voucher_pago"] = $intoData['id_voucher_pago'];
        $data["tiene_suspencion"] = $intoData['tiene_suspencion'];
        $data["id_entidad"] = $id_entidad;
        $data["id_depto"] = $id_depto;
        $data["id_persona"] = $id_user;
        $data["id_dinamica"] = $intoData['id_dinamica'];
        $data["id_tipotransaccion"] = $intoData['id_tipotransaccion'];

        $voucher = AccountingData::showVoucher($data['id_voucher_compra']);
        if(count($voucher) === 1) {
            $data["id_anho"] = $voucher[0]->id_anho;
            $data["id_mes"] = $voucher[0]->id_mes;
        } else {
            $jResponse['success'] = false;
            $jResponse['message'] = "La operación no esta asignada a un voucher, revise tener un voucher asignado.";
            $jResponse['data'] = NULL;
            $jResponse['code'] = "202";
            goto end;
        }

        $error = 0;
        $msg_error = '';
        try {

            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_RECI_HONO_GUARDAR_ACTU(
            :P_ID_PROVEEDOR, 
            :P_ID_COMPROBANTE, 
            :P_ES_ELECTRONICA, 
            :P_SERIE, 
            :P_NUMERO, 
            :P_FECHA_DOC, 
            :P_ID_TIPOTRANSACCION, 
            :P_ID_DINAMICA, 
            :P_ID_MONEDA, 
            :P_IMPORTE, 
            :P_IMPORTE_RETENER, 
            :P_TIPOCAMBIO,
            :P_TIENE_SUSPENCION,
            
            :P_ID_VOUCHER_COMPRA,
            :P_ID_VOUCHER_PAGO,
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

            $stmt->bindParam(':P_ID_PROVEEDOR', $data["id_proveedor"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ID_COMPROBANTE', $data["id_comprobante"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_ELECTRONICA', $data["es_electronica"], PDO::PARAM_STR);
            $stmt->bindParam(':P_SERIE', $data["serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $data["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_DOC', $data["fecha_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOTRANSACCION', $data["id_tipotransaccion"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DINAMICA', $data["id_dinamica"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA', $data["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_IMPORTE', $data["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_RETENER', $data["importe_retener"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TIPOCAMBIO', $data["tipocambio"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TIENE_SUSPENCION', $data["tiene_suspencion"], PDO::PARAM_STR);

            $stmt->bindParam(':P_ID_VOUCHER_COMPRA', $data["id_voucher_compra"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VOUCHER_PAGO', $data["id_voucher_pago"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $data["id_depto"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ID_ANHO', $data["id_anho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $data["id_mes"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA',$id_recibo_honorario, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if($error === 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = $id_recibo_honorario;
                $jResponse['code'] = "202";
                // $code = "202";
                goto end;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = "Ok";
            $jResponse['data'] = $id_recibo_honorario;
            $jResponse['code'] = "200";
            // $code = "202";
            goto end;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "400";
        }
        end:
        return $jResponse;
    }

    public function storeFinalizar($id_receipt_for_fees)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $error = 0;
            $msg_error = "";
            try {

                $dataCompra = PurchasesData::getCompraById($id_receipt_for_fees);
                if($dataCompra[0]->estado === "1")
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La provisión ya esta finalizada.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }

                $dataAsientosCompra = PurchasesData::validarContrapartidaAsientosCompra($id_receipt_for_fees);

                if(intval($dataAsientosCompra[0]->cantidad_asientos) === 0)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ls provisión no tiene asientos contables.";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                } else if((intval($dataAsientosCompra[0]->cantidad_asientos) > 0)
                    && (intval($dataAsientosCompra[0]->totalizar_importe) !== 0)) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La suma del debe y haber no igualan en cero (0).";
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }

                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }

                // Terminar el registro de la compra.
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_FINALIZAR(:P_ID_COMPRA, :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_COMPRA', $id_receipt_for_fees, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    // Actualizar estado de la compra.
                    $dataCambiaEstado = ["estado"=>"1"];
                    PurchasesData::patchCompra($dataCambiaEstado,$id_receipt_for_fees);

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
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    
    
    public function addStore(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $intoData = Input::all();
            $jResponse = $this->addReceiptForFess(0,$intoData,$id_entidad,$id_depto,$id_user);
        }
        end:
        return response()->json($jResponse, $jResponse["code"]);
    }
    public function updateStore($id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse =[];
            try {
                $intoData = Input::all();
                $jResponse = $this->addReceiptForFess($id_compra,$intoData,$id_entidad,$id_depto,$id_user);
            } catch (Exception $e) {
                $jResponse['error'] = 1;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        return response()->json($jResponse, $jResponse['code']);
    }
    public function addReceiptForFess($id_recibo_honorario,$intoData,$id_entidad,$id_depto,$id_user) {
        $jResponse =[];
        $validDocumento = ProvisionsValidation::validationCall("validateDocumentRxH");
        if($validDocumento->invalid){
            $jResponse['success'] = false;
            $jResponse['message'] = $validDocumento->message;
            $jResponse['code'] = "202";
            goto end;
        }
        $id_comprobante = $intoData["id_comprobante"];
        $id_proveedor = $intoData["id_proveedor"];
        $serie = $intoData["serie"];
        $numero = $intoData["numero"];
        $exist = PurchasesData::existsProviderDocument($serie,$numero,$id_comprobante,$id_proveedor,$id_recibo_honorario);
        if($exist->exists){
            $jResponse['success'] = false;
            $jResponse['message'] = 'El comprobante ya esta registrado: '.$exist->info;
            $jResponse['data'] = null;
            $jResponse['code'] = "202";
            goto end;
        }
        
        ProvisionsValidation::validateRucInSunat($id_proveedor, $id_comprobante);
        // if($validSunat['success'] != true) {
        //     $jResponse = $validSunat;
        //     $jResponse['code'] = "202";
        //     goto end;
        // }
        $data = [];
        $data["id_pedido"] = $intoData['id_pedido'];
        $data["id_entidad"] = $id_entidad;
        $data["id_depto"] = $id_depto;
        $params = "";
        $tiene_params = "N";
        $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
        if($rpta["nerror"]==0){
            $data["id_anho"] = $rpta["id_anho"];
            $data["id_mes"] = $rpta["id_mes"];
        }
        $data["id_persona"] = $id_user;
        $data["id_proveedor"] = $id_proveedor;
        $data["id_comprobante"] = $id_comprobante;
        $data["id_moneda"] = $intoData['id_moneda'];
        $data["fecha_doc"] = $intoData['fecha_doc'];
        if($data["id_moneda"] == "9") { // Dolar Americano
            $fecha_doc = $data['fecha_doc'];
            $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
            $dataMoneda = $tipo_cambio[0];
            $data["tipocambio"] = $dataMoneda->cos_compra;
        } else if($data["id_moneda"] == "7") { // Soles
            $data["tipocambio"] = "0";
        }
        $data["id_tipotransaccion"] = $intoData['id_tipotransaccion'];
        $data["serie"] = $intoData['serie'];
        $data["numero"] = $intoData['numero'];
        $data["importe"] = $intoData['importe'];
        $data["importe_retener"] = $intoData['importe_retener'];
        $data["tiene_suspencion"] = $intoData['tiene_suspencion'];
        $data["es_electronica"] = $intoData['es_electronica'];

        $error = 0;
        $msg_error = '';
        try {

            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_CREAR_RECIBO_HONORARIO(
            :P_ID_PEDIDO,
            :P_ID_ENTIDAD, 
            :P_ID_DEPTO, 
            :P_ID_ANHO, 
            :P_ID_MES, 
            :P_ID_PERSONA, 
            :P_ID_PROVEEDOR, 
            :P_ID_COMPROBANTE, 
            :P_ID_MONEDA, 
            :P_ID_TIPOTRANSACCION, 
            :P_TIPOCAMBIO, 
            :P_FECHA_DOC, 
            :P_SERIE,
            :P_NUMERO,
            :P_IMPORTE,
            :P_IMPORTE_RETENER,
            :P_TIENE_SUSPENCION,
            :P_ES_ELECTRONICA,
            :P_ERROR,
            :P_ID_COMPRA,
            :P_MSGERROR
            );
            end;");
            $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $data["id_depto"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $data["id_anho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $data["id_mes"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PROVEEDOR', $data["id_proveedor"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $data["id_comprobante"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA', $data["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOTRANSACCION', $data["id_tipotransaccion"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TIPOCAMBIO', $data["tipocambio"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_DOC', $data["fecha_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':P_SERIE', $data["serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $data["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $data["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_RETENER', $data["importe_retener"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TIENE_SUSPENCION', $data["tiene_suspencion"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_ELECTRONICA', $data["es_electronica"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA',$id_recibo_honorario, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if($error === 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = $id_recibo_honorario;
                $jResponse['code'] = "202";
                goto end;
            }
            $jResponse['success'] = true;
            $jResponse['message'] = "Ok";
            $jResponse['data'] = $id_recibo_honorario;
            $jResponse['code'] = "200";
            goto end;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "400";
        }
        end:
        return $jResponse;
    }
    public function updateReceiptForFees($id_compra){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida == 'SI'){
            $jResponse = [];
            try{
                $entCompra = PurchasesData::showPurchases($id_entidad,$id_compra);
                $codigo = Input::get("codigo");
                if($entCompra){
                    $clientIP = \Request::ip();
                    $detalle = "Finalizar Provision Recibo x Honorarios";
                    $error = 0;
                    $msg_error = "";
                    for($x=1 ; $x <= 200 ; $x++){
                        $msg_error .= "0";
                        $code .= "0";
                    }
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("BEGIN PKG_PURCHASES.SP_FINALIZAR_RECIBO_HONORARIO(
                        :P_ID_COMPRA,
                        :P_CODIGO,
                        :P_ID_PERSONA,
                        :P_DETALLE,
                        :P_IP,
                        :P_CODE,
                        :P_ERROR,
                        :P_MSGERROR
                        );
                        end;");
                        $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                        $stmt->bindParam(':P_CODIGO', $codigo, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                        $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
                        $stmt->bindParam(':P_IP', $clientIP, PDO::PARAM_STR);
                        $stmt->bindParam(':P_CODE', $code, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
                    $stmt->execute();
                    $objReturn['error']   = $error;
                    $objReturn['code']   = $code;
                    $objReturn['message'] = $msg_error;
                    if($error == 0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "OK";
                        $jResponse['data']    = array("code" => $code);
                        $code                 = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;
                        $jResponse['data']    = [];
                        $code                 = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
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
