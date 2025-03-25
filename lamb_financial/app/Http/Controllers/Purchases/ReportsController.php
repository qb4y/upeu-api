<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 19/03/2019
 * Time: 20:30
 */

namespace App\Http\Controllers\Purchases;


use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\SetupData;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Controllers\Report\Accounting\ReportLegalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PDF;
use DOMPDF;

class ReportsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        // $this->middleware('auth');
    }

    public function getReportPleView() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                
                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }
                $p_tipo_libro = '81';

                $bindings = [
                    'p_id_empresa' => $id_empresa,
                    'p_id_entidad' => $id_entidad,
                    'p_id_depto' => $id_depto,
                    'p_id_mes' => $id_mes,
                    'p_id_anho' => $id_anho,
                    'p_tipo_libro' => $p_tipo_libro,
                ];
                $result = DB::executeProcedureWithCursor('PKG_PURCHASES.SP_COMPRA_PLE_SHOW', $bindings);
                //return $result;

                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
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

    public function getReportPleTxt() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $tipo_libro = $this->request->query('tipo_libro');

                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }

                if($tipo_libro === '82') {
                    $fileName = PurchasesData::getFileName82($id_empresa, $id_anho, $id_mes);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'datos' => [],
                        'filename' => $fileName[0]->filename
                    ];
                    $code = "200";
                    goto end;
                }
                // is 81
                $fileName = PurchasesData::getFileName81($id_empresa, $id_anho, $id_mes);
                $bindings = [
                    'p_id_empresa' => $id_empresa,
                    'p_id_entidad' => $id_entidad,
                    'p_id_depto' => $id_depto,
                    'p_id_mes' => $id_mes,
                    'p_id_anho' => $id_anho,
                ];
                $result = DB::executeProcedureWithCursor('PKG_PURCHASES.SP_COMPRA_PLE_TXT', $bindings);

                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'datos' => $result,
                        'filename' => $fileName[0]->filename
                    ];
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

    public function getReportComprasResumen() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                $details = PurchasesData::getComprasResumenDetalle($id_empresa, $id_anho, $id_mes);
                $totales = PurchasesData::getComprasResumenTotales($id_empresa, $id_anho, $id_mes);

                if ($details && $totales) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'saludo' => "Buenas tardes Oscar Sauñe",
                        'saludo2' => "Buenos dias Oscar Rafael Sauñe",
                        'details' => $details,
                        'totales' => $totales[0]
                    ];
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
    public function getCheckOfIssue() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                $items = PurchasesData::getCheckOfIssue($id_empresa, $id_entidad, $id_anho, $id_mes);

                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $items;
                        
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

    public function getPdfVoucherCoverPage(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_depto = $this->request->query('id_depto');
                $id_voucher = $this->request->query('id_voucher');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                $mes = AccountingData::getMonthById($id_mes);
                $datos['periodo'] = $mes->nombre." - ".$id_anho;
                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                $list_entidad = SetupData::entityDetail($id_entidad);
                $list_voucher = SetupData::entityVoucherDetail($id_voucher);
                $entidad_depto = "";
                $entidad_tipo = "2";

                foreach ($list_voucher as &$itemparent) {
                    $itemparent->iniciales = "";
                    if($itemparent->id_persona){
                        $inicialesUsers = AccountingData::getInicialesNombreUsuario($itemparent->id_persona);
                        foreach ($inicialesUsers as $item) {
                            $itemparent->iniciales = $item->iniciales;
                        }
                    }

                }

                foreach ($list_entidad as $item) {
                    $entidad_depto = $item->nombre;
                    $entidad_tipo = $item->id_tipoentidad;
                }
                if($entidad_tipo == "5"){
                    $list_entidad_depto = SetupData::entityDeptoDetail($id_entidad, $id_depto);
                    foreach ($list_entidad_depto as $item) {
                        $entidad_depto = $item->nombre;
                    }
                }
                $datos['entidad_dpto'] = $entidad_depto;
                $datos['logo'] = 'logo_'.$entidad_tipo.'.png';
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }
                $printer_details = SetupData::printerFooterDetail($id_user);
                foreach ($printer_details as $item) {
                    $datos['fechahora'] = $item->fechahora;
                    $datos['username'] = $item->username;
                }

                $items = PurchasesData::getComprasAndReceiptForFeesByIdVoucher($id_voucher);
                $items_sum = PurchasesData::getComprasAndReceiptForFeesByIdVoucherTotal($id_voucher);
                
                $data['datos'] = $datos;
                $data['voucher'] = $list_voucher;
                $data['items'] = [];
                $data['totales'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'voucher_cover_page', 'purchases.voucherCoverPage', 'proliant');
    }

    public function getShoppingRecordExportPdf()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_depto = $this->request->query('id_depto');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');


                ReportLegalController::libro_compras_8_1($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes, $id_user);

               
            } catch (Exception $e) {
                return response()->json($jResponse, $code);
            }
        }else {
            return response()->json($jResponse, $code);
        }


    }

    public function getPdfShoppingRecord(){

	ini_set('memory_limit',-1);
	set_time_limit(600);

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_depto = $this->request->query('id_depto');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                $mes = AccountingData::getMonthById($id_mes);
                $datos['periodo'] = $mes->nombre." - ".$id_anho;
                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }
                $printer_details = SetupData::printerFooterDetail($id_user);
                foreach ($printer_details as $item) {
                    $datos['fechahora'] = $item->fechahora;
                    $datos['username'] = $item->username;
                }

                $items = PurchasesData::listReportPurchases($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto);
                $items_sum = PurchasesData::listReportPurchases_total($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto);
                
                $data['datos'] = $datos;
                $data['items'] = [];
                $data['totales'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'registro_compras', 'purchases.shoppingRecord');
    }
    
    public function getFeesRecord() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                //$id_depto = $this->request->query('id_depto');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }

                $items = PurchasesData::getFeesRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                $items_sum = PurchasesData::getFeesRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                
                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'items' => $items,
                        'totales' => $items_sum[0]
                    ];
                        
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
    public function getPdfFeesRecord(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "Asociacion Iglesia Adventista";

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }

                $mes = AccountingData::getMonthById($id_mes);
                $datos['periodo'] = $mes->nombre." - ".$id_anho;

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }
                $printer_details = SetupData::printerFooterDetail($id_user);
                foreach ($printer_details as $item) {
                    $datos['fechahora'] = $item->fechahora;
                    $datos['username'] = $item->username;
                }

                $items = PurchasesData::getFeesRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                $items_sum = PurchasesData::getFeesRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                
                $data['datos'] = $datos;
                $data['items'] = [];
                $data['totales'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'registro_honorarios', 'purchases.feesRecord');
    }
    public function getWithholdingRecord() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');

                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }
                
                $items = PurchasesData::getWithholdingRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                $items_sum = PurchasesData::getWithholdingRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                
                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK2';
                    $jResponse['data'] = [
                        'items' => $items,
                        'totales' => $items_sum[0]
                    ];
                        
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
    public function getPdfWithholdingRecord(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "Asociacion Iglesia Adventista";
                $datos['periodo'] = "Abril - 2019";

                $id_empresa = $this->request->query('id_empresa');
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                if(is_null($this->request->query('id_depto'))){
                    $id_depto = "*";
                }else{
                    $id_depto = $this->request->query('id_depto');
                }
                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }
                $printer_details = SetupData::printerFooterDetail($id_user);
                foreach ($printer_details as $item) {
                    $datos['fechahora'] = $item->fechahora;
                    $datos['username'] = $item->username;
                }


                $items = PurchasesData::getWithholdingRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                $items_sum = PurchasesData::getWithholdingRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes);
                
                $data['datos'] = $datos;
                if ($items) {
                    $data['items'] = $items;
                    $data['totales'] = $items_sum;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'registro_retenciones', 'purchases.withholdingRecord');
    }
    public function getAccountStatus() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_persona = $this->request->query('id_persona');
                $id_anho = $this->request->query('id_anho');
                $items = PurchasesData::getAccountStatus($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto);
                $items_sum = PurchasesData::getAccountStatusTotal($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto);
                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'items' => $items,
                        'totales' => $items_sum[0]
                    ];
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

    // report pdf
    public function getAccountStatusPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_empresa = $this->request->query('id_empresa');
                $id_persona = $this->request->query('id_persona');
                $id_anho = $this->request->query('id_anho');

                $periodo = $this->request->query('periodo');
                $ruc = $this->request->query('ruc');
                $razon_social = $this->request->query('razon_social');

                $items = PurchasesData::getAccountStatus($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto);
                $items_sum = PurchasesData::getAccountStatusTotal($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto);

                if ($items){
                    $pdf = DOMPDF::loadView('pdf.purchases.accouting',[
                        'items'=>$items,
                        'items_sum'=>$items_sum[0]->importe,

                        'periodo'=>$periodo,
                        'ruc'=>$ruc,
                        'razon_social'=>$razon_social,

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

    ///


    public function getAccountStatusDetail() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_tipoorigen = $this->request->query('id_tipoorigen');
                $id_origen = $this->request->query('id_origen');
                $items = PurchasesData::getAccountStatusDetail($id_tipoorigen, $id_origen);
                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'items' => $items,
                        'totales' => $items
                    ];
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

    public function pdfTest(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];  
        $items = PurchasesData::getFeesRecord(207, 2019, 4);
        $items_sum = PurchasesData::getFeesRecordSum(207, 2019, 4);


        $datos['empresa'] = "Asociacion Iglesia Adventista";
        $datos['periodo'] = "Abril - 2019";

        $data['datos'] = $datos;
        $data['items'] = $items;
        $data['totales'] = $items_sum;
	    //if($valida=='SI') { 
                return $this->generatePdf($data, 'testt', 'purchases.test');
        //}
    }

    public function generatePdf($p_data, $namePdf, $nameView, $orientation = "landscape")
    {
        $data = $p_data;
        //para guardar en el storage
        //$path = storage_path() . '/app/public/report.pdf';
        //$pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', 'landscape')->save($path);
        $pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', $orientation);
        return $pdf->stream($namePdf . '.pdf');
    }
    public function getPdfAccountStatus() {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
	// para merge request
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {

                $id_empresa = $this->request->query('id_empresa');
                $id_persona = $this->request->query('id_persona');
                $id_anho = $this->request->query('id_anho');

                $items = PurchasesData::getAccountStatus($id_empresa, $id_anho, $id_persona);

                if ($items) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $items;
                        
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
    public function getProviderAccounts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $queryParams = $this->request->all();
        $valida = $jResponse["valida"]; // SI : NO
        $params = [
            'id_entidad' => $jResponse['id_entidad'], //session
            'id_depto' => $jResponse['id_depto'], //session
            'id_anho' => $queryParams['id_anho'], // queryParams del id_anho
            'id_mes' => $queryParams['id_mes'], // queryParams del id_mes
            'cuenta' => $queryParams['cuenta'], // queryParams del cliente
            'tipo' => $queryParams['tipo'], // queryParams del cliente
            'per_page' => isset($queryParams['per_page']) ? $queryParams['per_page'] : 10,// operador ternario -> if else
        ];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            if ($queryParams['tipo'] == 'T') {
                $jResponse['data'] = PurchasesData::getProviderAccountExport($params);
            } else {
                $jResponse['data'] = PurchasesData::getProviderAccount($params);
            }
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }
}

