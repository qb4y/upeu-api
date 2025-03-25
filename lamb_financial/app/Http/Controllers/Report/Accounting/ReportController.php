<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Report\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Data\ReportData;
use App\Http\Data\financialReportData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PDF;
use DOMPDF;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;

class ReportController extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function test(){
        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];
        $results = LambUsuario::select('ID_PERSONA', 'LOGIN', 'CONTRASENHA')->GET();
        $count = count($results);
        if ($results) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['total_count' => $count, 'items' => $results->toArray()];
        }
        return response()->json($jResponse);
    }

    public function generatePdf($p_data, $namePdf, $nameView)
    {
        $data = $p_data;
        //para guardar en el storage
        //$path = storage_path() . '/app/public/report.pdf';
        //$pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', 'landscape')->save($path);
        $pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', 'landscape');
        return $pdf->stream($namePdf . '.pdf');
    }

    public function generateTxt($data, $name_file)
    {
        $content = [];

        foreach ($data as $value) {
            $content[] = $value->contenido;
        }

        $fileText = implode("\r\n", $content);

        $contentLength = strlen($fileText);
        $myName = $name_file . ".txt";
        $headers = [
            'Content-type' => 'text/plain',
            'test' => 'YoYo',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $myName),
            'X-BooYAH' => 'WorkyWorky',
            'Content-Length' => $contentLength];

        return Response::make($fileText, 200, $headers);
    }

    public function pleDiary()
    {
        //$data = ReportData::exportTxt2();
        $data = ReportData::exportTxt();
        $name_file = 'pleDiary';

        return $this->generateTxt($data, $name_file);
    }

    public function debit(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $lstData = ReportData::debit();
                $lstData = json_decode(json_encode($lstData), true);

                if ($lstData) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstData];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function pdfStatementAccount(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode($this->request->get('data'));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $current_account = $params->data->current_account;
                $query_dpto = null;

                $lstAccount = ReportData::debit_account($entity, $current_account);
                foreach ($lstAccount as $key => $account) {
                    $lstDebit = ReportData::statementAccount($year, $month, $entity, $current_account, $account->id_cuentaaasi, $query_dpto);
                    if ($lstDebit) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['cuenta' => $account, 'items' => $lstDebit, 'saldos' => $lstDebit];
                    }
                }
                $lstData = json_decode(json_encode($jResponse['data']['items']), true);
                return $this->generatePdf($lstData, 'statementAccount', 'statementAccount');
            }
        }
    }

public function debitsReceive(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_moneda = 7;
            
            $year = $request->query('year');
            $month = $request->query('month');
            $entity = $request->query('entity');
            $ctacte = $request->query('ctacte');

            try{
                $lstItems = ReportData::debitsGeneral($year, $month, $entity, $ctacte);

                $data = [ 
                    'items' => $lstItems,
                    'total' => [],
                ];

                $jResponse['success'] = true;
                if(count($lstItems)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }

                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                }          
        }
        return response()->json($jResponse,$code);
    }

public function statementaccountEntities(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_moneda = 7;
            
            $year = $request->query('year');
            $month = $request->query('month');
            $entity = $request->query('entity');
            $ctacte = $request->query('ctacte');
            $depto = 0;
            $query_depto = "";
            $account = "";

                try{

            
                          $data = [
                                'items' => [],
                                'total' => [],
                            ];

                        $lstAccount = ReportData::debit_account($entity, $ctacte);

                        foreach ($lstAccount as $key => $account) {
                            if ($depto != 0) {
                                $query_depto = " AND ID_DEPTO = $depto ";
                            }
                            $lstItems = ReportData::statementAccount($year, $month, $entity, $ctacte, $account->id_cuentaaasi, $query_depto);
                            $data = [ 
                                'cuenta' => $account,
                                'items' => $lstItems,
                                'total' => [],
                            ];
                        }

                        $jResponse['success'] = true;
                    if(count($lstItems)>0){
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }

                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                }          
        }
        return response()->json($jResponse,$code);
    }
    public function statementAccount(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $current_account = $params->data->current_account;
                $depto = $params->data->depto;
                $query_depto = "";
                $account = "";


                $lstAccount = ReportData::debit_account($entity, $current_account);

                foreach ($lstAccount as $key => $account) {
                    if ($depto != 0) {
                        $query_depto = " AND ID_DEPTO = $depto ";
                    }
                    $lstDebit = ReportData::statementAccount($year, $month, $entity, $current_account, $account->id_cuentaaasi, $query_depto);

                    if ($lstDebit) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['cuenta' => $account, 'items' => $lstDebit];
                    }
                }
            }
        }

        return response()->json($jResponse);
    }

public function statementaccountEntitiesSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"]; 
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_moneda = 7;
            
            $year = $request->query('year');
            $month = $request->query('month');
            $entity = $request->query('entity');

                try{

                        $lstItems = ReportData::statementAccountSummary($year, $month, $entity);

                        $data = [
                            'items' => $lstItems,
                            'total' => [],
                        ];

                        $jResponse['success'] = true;
                    if(count($lstItems)>0){
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }

                }catch (Exception $e){                            
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";                            
                }          
        }
        return response()->json($jResponse,$code);
    }

    public function statementAccountSummary(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;

                $lstDebit = ReportData::statementAccountSummary($year, $month, $entity);

                if ($lstDebit) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstDebit];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function checkingBalance(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $fund = $params->data->fund;

                $lstBalance = ReportData::checkingBalance($year, $month, $entity, $fund);
                $totalBalance = ReportData::totalCheckingBalance($year, $month, $entity, $fund);

                if ($lstBalance && $totalBalance) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstBalance, 'total' => $totalBalance];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function ledger(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $current_account = $params->data->current_account;
                $account_aasi = $params->data->account_aasi;
                $department = $params->data->department;

                $lstLedger = ReportData::ledger($year, $month, $entity, $current_account, $account_aasi, $department);
                $totalLedger = ReportData::totalLedger($year, $month, $entity, $current_account, $account_aasi, $department);

                if ($lstLedger && $totalLedger) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstLedger, 'total' => $totalLedger];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function financialIndicators(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;

                $data = null;
                $count = 1;

                for ($i = 0; $i < $month; $i++) {
                    $data[] = $count++;
                }

                $rank = implode(",", $data);

                $lstAvailable = ReportData::available($year, $month, $entity, $rank);
                $lstCurrentAsset = ReportData::currentAsset($year, $month, $entity, $rank);
                $lstConsolidationProfit = ReportData::consolidationProfit($year, $month, $entity, $rank);
                $lstOperativeCapital = ReportData::operativeCapital($year, $month, $entity, $rank);
                $lstImmediateLiquidity = ReportData::immediateLiquidity($year, $month, $entity, $rank);
                $lstCurrentLiquidity = ReportData::currentLiquidity($year, $month, $entity, $rank);
                $lstDryLiquidity = ReportData::dryLiquidity($year, $month, $entity, $rank);

                $lstGeneralData[] = $lstAvailable;
                $lstGeneralData[] = $lstCurrentAsset;
                $lstGeneralData[] = $lstConsolidationProfit;
                $lstGeneralData[] = $lstOperativeCapital;
                $lstCurrentLiabilities[] = $lstImmediateLiquidity;
                $lstCurrentLiabilities[] = $lstCurrentLiquidity;
                $lstCurrentLiabilities[] = $lstDryLiquidity;

                $generalData = ['name' => 'Datos Generales', 'items' => $lstGeneralData];
                $financialIndicators = ['name' => 'Indicadores Financieros', 'items' => $lstCurrentLiabilities];

                if ($generalData) {

                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => [
                        $generalData,
                        $financialIndicators
                    ]];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function financialStatements(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode(file_get_contents("php://input"));

                $first_year = $params->data->first_year;
                $second_year = $params->data->second_year;
                $first_month = $params->data->first_month;
                $second_month = $params->data->second_month;
                $entity = $params->data->entity;

                $lstCabCurrentAsset = financialReportData::cabCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailCurrentAsset = financialReportData::detailCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity);
                $lstCabNonCurrentAsset = financialReportData::cabNonCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailNonCurrentAsset = financialReportData::detailNonCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTotalCurrentAsset = financialReportData::totalCurrentAsset($first_year, $second_year, $first_month, $second_month, $entity);

                $lstCabCurrentLiabilities = financialReportData::cabCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailCurrentLiabilities = financialReportData::detailCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity);
                $lstCabNonCurrentLiabilities = financialReportData::cabNonCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailNonCurrentLiabilities = financialReportData::detailNonCurrentLiabilities($first_year, $second_year, $first_month, $second_month, $entity);

                $lstNetWorth = financialReportData::netWorth($first_year, $second_year, $first_month, $second_month, $entity);
                $lstInitialBalanceNetWorth = financialReportData::initialBalanceNetWorth($first_year, $second_year, $first_month, $second_month, $entity);
                $lstAdjustmentWorth = financialReportData::adjustmentWorth($first_year, $second_year, $first_month, $second_month, $entity);
                $lstGrantsSubsidiesFixedAssets = financialReportData::grantsSubsidiesFixedAssets($first_year, $second_year, $first_month, $second_month, $entity);
                $lstVariationWorth = financialReportData::variationWorth($first_year, $second_year, $first_month, $second_month, $entity);
                $lstInicialBalanceFundsAvailable = financialReportData::inicialBalanceFundsAvailable($first_year, $second_year, $first_month, $second_month, $entity);
                $lstConstitutionReversionFunds = financialReportData::constitutionReversionFunds($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTotalLiabilitiesEquity = financialReportData::totalLiabilitiesEquity($first_year, $second_year, $first_month, $second_month, $entity);

                $lstTotalLiabilitiesAsset = financialReportData::totalLiabilitiesAsset($first_year, $second_year, $first_month, $second_month, $entity);
                //dd($lstTotalLiabilitiesAsset);
                $ca_first_year = null;
                $ca_second_year = null;
                $nca_first_year = null;
                $nca_second_year = null;
                $tca_first_year = null;
                $tca_second_year = null;
                $cl_first_year = null;
                $cl_second_year = null;
                $ncl_first_year = null;
                $ncl_second_year = null;
                $tcl_first_year = null;
                $tcl_second_year = null;
                $nw_first_year = null;
                $nw_second_year = null;
                $tlw_first_year = null;
                $tlw_second_year = null;
                $tle_first_year = null;
                $tle_second_year = null;

                if ($lstCabCurrentAsset) {
                    $ca_first_year = $lstCabCurrentAsset->first_year;
                    $ca_second_year = $lstCabCurrentAsset->second_year;
                }

                if ($lstCabNonCurrentAsset) {
                    $nca_first_year = $lstCabNonCurrentAsset->first_year;
                    $nca_second_year = $lstCabNonCurrentAsset->second_year;
                }

                if ($lstTotalCurrentAsset) {
                    $tca_first_year = $lstTotalCurrentAsset->first_year;
                    $tca_second_year = $lstTotalCurrentAsset->second_year;
                }

                if ($lstCabCurrentLiabilities) {
                    $cl_first_year = $lstCabCurrentLiabilities->first_year;
                    $cl_second_year = $lstCabCurrentLiabilities->second_year;
                }

                if ($lstCabNonCurrentLiabilities) {
                    $ncl_first_year = $lstCabNonCurrentLiabilities->first_year;
                    $ncl_second_year = $lstCabNonCurrentLiabilities->second_year;
                }

                if ($lstNetWorth) {
                    $nw_first_year = $lstNetWorth->first_year;
                    $nw_second_year = $lstNetWorth->second_year;
                }

                if ($lstTotalLiabilitiesEquity) {
                    $tlw_first_year = $lstTotalLiabilitiesEquity->first_year;
                    $tlw_second_year = $lstTotalLiabilitiesEquity->second_year;
                }

                if ($lstTotalLiabilitiesAsset) {
                    $tle_first_year = $lstTotalLiabilitiesAsset->first_year;
                    $tle_second_year = $lstTotalLiabilitiesAsset->second_year;
                }

                $currentAsset = [
                    'name' => 'ACTIVO CORRIENTE',
                    'first_year' => $ca_first_year,
                    'second_year' => $ca_second_year,
                    'detail' => $lstDetailCurrentAsset
                ];

                $nonCurrentAsset = [
                    'name' => 'ACTIVO NO CORRIENTE',
                    'first_year' => $nca_first_year,
                    'second_year' => $nca_second_year,
                    'detail' => $lstDetailNonCurrentAsset
                ];

                $asset = [
                    'name' => 'TOTAL DEL ACTIVO',
                    'first_year' => $tca_first_year,
                    'second_year' => $tca_second_year,
                    'items' => [$currentAsset, $nonCurrentAsset]
                ];

                $currentLiabilities = [
                    'name' => 'PASIVO CORRIENTE',
                    'first_year' => $cl_first_year,
                    'second_year' => $cl_second_year,
                    'detail' => $lstDetailCurrentLiabilities
                ];

                $nonCurrentLiabilities = [
                    'name' => 'PASIVO NO CORRIENTE',
                    'first_year' => $ncl_first_year,
                    'second_year' => $ncl_second_year,
                    'detail' => $lstDetailNonCurrentLiabilities
                ];

                $liabilities = [
                    'name' => 'TOTAL PASIVO',
                    'first_year' => $tcl_first_year,
                    'second_year' => $tcl_second_year,
                    'items' => [$currentLiabilities, $nonCurrentLiabilities]
                ];

                $worth = [
                    'name' => 'PATRIMONIO NETO',
                    'first_year' => $nw_first_year,
                    'second_year' => $nw_second_year,
                    'items' => [$lstInitialBalanceNetWorth,
                        $lstAdjustmentWorth,
                        $lstGrantsSubsidiesFixedAssets,
                        $lstVariationWorth,
                        $lstInicialBalanceFundsAvailable,
                        $lstConstitutionReversionFunds]
                ];

                $worth3 = [
                    'name' => 'TOTAL PASIVO Y PATRIMONIO',
                    'first_year' => $tlw_first_year,
                    'second_year' => $tlw_second_year
                ];
                $totalLiabilitiesAsset = [
                    'name' => 'TOTAL ACTIVO Y PASIVO',
                    'first_year' => $tle_first_year,
                    'second_year' => $tle_second_year
                ];

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [
                    'ACTIVO' => $asset,
                    'PASIVO' => $liabilities,
                    'PATRIMONIO' => [$worth, $worth3],
                    'TOTAL_ACTIVO_PASIVO' => $totalLiabilitiesAsset
                ];
            }
        }
        return response()->json($jResponse);
    }

    public function profitLossStatement(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode(file_get_contents("php://input"));

                $first_year = $params->data->first_year;
                $second_year = $params->data->second_year;
                $first_month = $params->data->first_month;
                $second_month = $params->data->second_month;
                $entity = $params->data->entity;

                $lstInputs = financialReportData::inputs($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTithe = financialReportData::tithe($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTitheAssignment = financialReportData::titheAssignment($first_year, $second_year, $first_month, $second_month, $entity);
                $lstNetTithe = financialReportData::netTithe($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailInput = financialReportData::detailInput($first_year, $second_year, $first_month, $second_month, $entity);
                $lstSalesCost = financialReportData::salesCost($first_year, $second_year, $first_month, $second_month, $entity);
                $lstOtherOperatingIncome = financialReportData::otherOperatingIncome($first_year, $second_year, $first_month, $second_month, $entity);
                $lstNetSales = financialReportData::netSales($first_year, $second_year, $first_month, $second_month, $entity);
                $lstResultSales = financialReportData::resultSales($first_year, $second_year, $first_month, $second_month, $entity);

                $lstOutputs = financialReportData::outputs($first_year, $second_year, $first_month, $second_month, $entity);
                $lstDetailOutput = financialReportData::detailOutput($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTotalOperatingWithoutSubsidies = financialReportData::totalOperatingWithoutSubsidies($first_year, $second_year, $first_month, $second_month, $entity);
                $lstNetGrantsReceived = financialReportData::netGrantsReceived($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTotalOperatingWithSubsidies = financialReportData::totalOperatingWithSubsidies($first_year, $second_year, $first_month, $second_month, $entity);
                $lstNotOperational = financialReportData::notOperational($first_year, $second_year, $first_month, $second_month, $entity);
                $lstNonOperatingResult = financialReportData::nonOperatingResult($first_year, $second_year, $first_month, $second_month, $entity);
                $lstTransfers = financialReportData::transfers($first_year, $second_year, $first_month, $second_month, $entity);
                $lstResultExercise = financialReportData::resultExercise($first_year, $second_year, $first_month, $second_month, $entity);

                $arrDetailInput[] = $lstTithe;
                $arrDetailInput[] = $lstTitheAssignment;
                $arrDetailInput[] = $lstNetTithe;
                //$arrDetailInput[] = $lstDetailInput;
                foreach ($lstDetailInput as $value) {
                    $data = $value;
                    $arrDetailInput[] = $data;
                    //dd($arrDetailInput2);
                }
                $arrDetailInput[] = $lstNetSales;
                $arrDetailInput[] = $lstSalesCost;
                $arrDetailInput[] = $lstResultSales;
                $arrDetailInput[] = $lstOtherOperatingIncome;


                $inputs = [
                    'name' => 'ENTRADAS',
                    'first_year' => $lstInputs->first_year,
                    'second_year' => $lstInputs->second_year,
                    'items' => $arrDetailInput
                ];

                $outputs = [
                    'name' => 'SALIDAS',
                    'first_year' => $lstOutputs->first_year,
                    'second_year' => $lstOutputs->second_year,
                    'items' => $lstDetailOutput
                ];

                $totalOperatingWithoutSubsidies = [
                    'name' => 'RESULTADO OPERATIVO SIN SUBVENCIONES',
                    'first_year' => $lstTotalOperatingWithoutSubsidies->first_year,
                    'second_year' => $lstTotalOperatingWithoutSubsidies->second_year,
                    'items' => $lstNetGrantsReceived
                ];

                $totalOperatingWithSubsidies = [
                    'name' => 'RESULTADO OPERATIVO CON SUBVENCIONES',
                    'first_year' => $lstTotalOperatingWithSubsidies->first_year,
                    'second_year' => $lstTotalOperatingWithSubsidies->second_year
                ];

                $notOperational = [
                    'name' => 'NO OPERATIVO',
                    'items' => $lstNotOperational
                ];

                $nonOperatingResult = [
                    'name' => 'RESULTADO NO OPERATIVO',
                    'first_year' => $lstNonOperatingResult->first_year,
                    'second_year' => $lstNonOperatingResult->second_year,
                    'items' => $lstTransfers
                ];

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [
                    'OPERATIVO' => [$inputs, $outputs],
                    'ROSS' => $totalOperatingWithoutSubsidies,
                    'ROCS' => $totalOperatingWithSubsidies,
                    'NOPERATIVO' => $notOperational,
                    'RNO' => $nonOperatingResult,
                    'RDE' => $lstResultExercise
                ];
            }
        }
        return response()->json($jResponse);
    }

    public function ledgeAssinet(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode(file_get_contents("php://input"));

                $period_range = $params->data->period_range;
                $entity = $params->data->entity;

                if ($period_range == 'P') {
                    $year = $params->data->year;
                    $month = $params->data->month;

                    $date = new \DateTime($year . '-' . $month . '-01');
                    $date_end = clone $date;
                    $date_end->add(new \DateInterval("P1M"));
                    $date_end->modify('-1 day');
                    $begin_date = $date->format('d/m/Y');
                    $end_date = $date_end->format('d/m/Y');
                } else {
                    $begin_date = $params->data->begin_date;
                    $end_date = $params->data->end_date;
                }

                $fund_all = $params->data->fund_all;
                $funds = $params->data->funds;
                $queryFunds = "  ";
                if ($fund_all != 1) {
                    $funds = implode(",", $funds);
                    $queryFunds = "AND CONTA_DIARIO_DETALLE.ID_FONDO IN (" . $funds . ")";
                }

                $function_all = $params->data->function_all;
                $functions = $params->data->functions;
                $queryFunctions = "  ";
                if ($function_all != 1) {
                    $functions = "'" . implode("','", $functions) . "'";
                    $queryFunctions = "AND CONTA_DIARIO_DETALLE.ID_DEPTO IN (" . $functions . ")";
                }

                $restriction_all = $params->data->restriction_all;
                $restrictions = $params->data->restrictions;
                $queryRestrictions = "  ";
                if ($restriction_all != 1) {
                    $restrictions = "'" . implode("','", $restrictions) . "'";
                    $queryRestrictions = "AND CONTA_DIARIO_DETALLE.ID_RESTRICCION IN (" . $restrictions . ")";
                }

                $account_all = $params->data->account_all;
                $accounts = $params->data->accounts;
                $queryAccounts = "  ";
                if ($account_all != 1) {
                    $accounts = "'" . implode("','", $accounts) . "'";
                    $queryAccounts = "AND CONTA_DIARIO_DETALLE.ID_CUENTAAASI IN (" . $accounts . ")";
                }

                $type_current_account_all = $params->data->type_current_account_all;
                $type_current_accounts = $params->data->type_current_accounts;
                $queryTypeCurrentAccounts = "  ";
                if ($type_current_account_all != 1) {
                    $type_current_accounts = "'" . implode("','", $type_current_accounts) . "'";
                    $queryTypeCurrentAccounts = "AND CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE IN (" . $type_current_accounts . ")";
                }

                $current_account_all = $params->data->current_account_all;
                $current_accounts = $params->data->current_accounts;
                $queryCurrentAccounts = "  ";
                if ($current_account_all != 1) {
                    $current_accounts = "'" . implode("','", $current_accounts) . "'";
                    $queryCurrentAccounts = "AND CONTA_DIARIO_DETALLE.ID_CTACTE IN (" . $current_accounts . ")";
                }

                $lstLedge = ReportData::ledgeAssinet($year, $month, $entity, $begin_date, $end_date, $queryFunds, $queryFunctions, $queryRestrictions, $queryAccounts, $queryTypeCurrentAccounts, $queryCurrentAccounts);
                $value1 = "1";
                $value2 = "1";
                $array_header = [];
                $array_body = [];
                $array_types = [];
                $unico = true;
                $con_data = false;
                $value_item = null;
                foreach ($lstLedge as $key => $value) {
                    $con_data = true;
                    $value2 = $value->id_fondo
                        . $value->id_depto
                        . $value->id_restriccion
                        . $value->id_cuentaaasi
                        . $value->id_tipoctacte
                        . $value->id_ctacte;
                    if ($value1 != $value2 && $value1 != "1") {
                        $array_types[] = ['id_fondo' => $value_item->id_fondo,
                            'id_depto' => $value_item->id_depto,
                            'id_restriccion' => $value_item->id_restriccion,
                            'id_cuentaaasi' => $value_item->id_cuentaaasi,
                            'id_tipoctacte' => $value_item->id_tipoctacte,
                            'id_ctacte' => $value_item->id_ctacte,
                            'nombre_fondo' => $value_item->nombre_fondo,
                            'nombre_depto' => $value_item->nombre_depto,
                            'nombre_restriccion' => $value_item->nombre_restriccion,
                            'nombre_cuentaaasi' => $value_item->nombre_cuentaaasi,
                            'nombre_tipoctacte' => $value_item->nombre_tipoctacte,
                            'nombre_cta_cte' => $value_item->nombre_cta_cte,
                            'items' => $array_current_account];
                        $array_current_account = [];
                    }
                    $array_current_account[] = [
                        'fec_view' => $value->fec_view,
                        'lote' => $value->lote,
                        'descripcion' => $value->descripcion,
                        'debe' => $value->debe,
                        'haber' => $value->haber,
                        'saldo' => $value->saldo,
                        'dc' => $value->dc
                    ];
                    $value_item = $value;
                    $value1 = $value2;
                }
                if ($con_data)
                    $array_types[] = ['id_fondo' => $value_item->id_fondo,
                        'id_depto' => $value_item->id_depto,
                        'id_restriccion' => $value_item->id_restriccion,
                        'id_cuentaaasi' => $value_item->id_cuentaaasi,
                        'id_tipoctacte' => $value_item->id_tipoctacte,
                        'id_ctacte' => $value_item->id_ctacte,
                        'nombre_fondo' => $value_item->nombre_fondo,
                        'nombre_depto' => $value_item->nombre_depto,
                        'nombre_restriccion' => $value_item->nombre_restriccion,
                        'nombre_cuentaaasi' => $value_item->nombre_cuentaaasi,
                        'nombre_tipoctacte' => $value_item->nombre_tipoctacte,
                        'nombre_cta_cte' => $value_item->nombre_cta_cte,
                        'items' => $array_current_account];
                // dd($lstLedge);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $array_types];
            }
        }
        return response()->json($jResponse);
    }

    public function ledgeAssinetPdf(){        
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode($this->request->get('data'));
                $period_range = $params->data->period_range;
                $entity = $params->data->entity;

                if ($period_range == 'P') {
                    $year = $params->data->year;
                    $month = $params->data->month;

                    $date = new \DateTime($year . '-' . $month . '-01');
                    $date_end = clone $date;
                    $date_end->add(new \DateInterval("P1M"));
                    $date_end->modify('-1 day');
                    $begin_date = $date->format('d/m/Y');
                    $end_date = $date_end->format('d/m/Y');
                } else {
                    $begin_date = $params->data->begin_date;
                    $end_date = $params->data->end_date;
                }

                $fund_all = $params->data->fund_all;
                $funds = $params->data->funds;
                $queryFunds = "  ";
                if ($fund_all != 1) {
                    $funds = implode(",", $funds);
                    $queryFunds = "AND CONTA_DIARIO_DETALLE.ID_FONDO IN (" . $funds . ")";
                }

                $function_all = $params->data->function_all;
                $functions = $params->data->functions;
                $queryFunctions = "  ";
                if ($function_all != 1) {
                    $functions = "'" . implode("','", $functions) . "'";
                    $queryFunctions = "AND CONTA_DIARIO_DETALLE.ID_DEPTO IN (" . $functions . ")";
                }

                $restriction_all = $params->data->restriction_all;
                $restrictions = $params->data->restrictions;
                $queryRestrictions = "  ";
                if ($restriction_all != 1) {
                    $restrictions = "'" . implode("','", $restrictions) . "'";
                    $queryRestrictions = "AND CONTA_DIARIO_DETALLE.ID_RESTRICCION IN (" . $restrictions . ")";
                }

                $account_all = $params->data->account_all;
                $accounts = $params->data->accounts;
                $queryAccounts = "  ";
                if ($account_all != 1) {
                    $accounts = "'" . implode("','", $accounts) . "'";
                    $queryAccounts = "AND CONTA_DIARIO_DETALLE.ID_CUENTAAASI IN (" . $accounts . ")";
                }

                $type_current_account_all = $params->data->type_current_account_all;
                $type_current_accounts = $params->data->type_current_accounts;
                $queryTypeCurrentAccounts = "  ";
                if ($type_current_account_all != 1) {
                    $type_current_accounts = "'" . implode("','", $type_current_accounts) . "'";
                    $queryTypeCurrentAccounts = "AND CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE IN (" . $type_current_accounts . ")";
                }

                $current_account_all = $params->data->current_account_all;
                $current_accounts = $params->data->current_accounts;
                $queryCurrentAccounts = "  ";
                if ($current_account_all != 1) {
                    $current_accounts = "'" . implode("','", $current_accounts) . "'";
                    $queryCurrentAccounts = "AND CONTA_DIARIO_DETALLE.ID_CTACTE IN (" . $current_accounts . ")";
                }

                $lstLedge = ReportData::ledgeAssinet($year, $month, $entity, $begin_date, $end_date, $queryFunds, $queryFunctions, $queryRestrictions, $queryAccounts, $queryTypeCurrentAccounts, $queryCurrentAccounts);
                $value1 = "1";
                $value2 = "1";
                $array_header = [];
                $array_body = [];
                $array_types = [];
                $unico = true;
                $con_data = false;
                $value_item = null;
                foreach ($lstLedge as $key => $value) {
                    $con_data = true;
                    $value2 = $value->id_fondo
                        . $value->id_depto
                        . $value->id_restriccion
                        . $value->id_cuentaaasi
                        . $value->id_tipoctacte
                        . $value->id_ctacte;
                    if ($value1 != $value2 && $value1 != "1") {
                        $array_types[] = ['id_fondo' => $value_item->id_fondo,
                            'id_depto' => $value_item->id_depto,
                            'id_restriccion' => $value_item->id_restriccion,
                            'id_cuentaaasi' => $value_item->id_cuentaaasi,
                            'id_tipoctacte' => $value_item->id_tipoctacte,
                            'id_ctacte' => $value_item->id_ctacte,
                            'nombre_fondo' => $value_item->nombre_fondo,
                            'nombre_depto' => $value_item->nombre_depto,
                            'nombre_restriccion' => $value_item->nombre_restriccion,
                            'nombre_cuentaaasi' => $value_item->nombre_cuentaaasi,
                            'nombre_tipoctacte' => $value_item->nombre_tipoctacte,
                            'nombre_cta_cte' => $value_item->nombre_cta_cte,
                            'items' => $array_current_account];
                        $array_current_account = [];
                    }
                    $array_current_account[] = [
                        'fec_view' => $value->fec_view,
                        'lote' => $value->lote,
                        'descripcion' => $value->descripcion,
                        'debe' => $value->debe,
                        'haber' => $value->haber,
                        'saldo' => $value->saldo,
                        'dc' => $value->dc
                    ];
                    $value_item = $value;
                    $value1 = $value2;
                }
                if ($con_data)
                    $array_types[] = ['id_fondo' => $value_item->id_fondo,
                        'id_depto' => $value_item->id_depto,
                        'id_restriccion' => $value_item->id_restriccion,
                        'id_cuentaaasi' => $value_item->id_cuentaaasi,
                        'id_tipoctacte' => $value_item->id_tipoctacte,
                        'id_ctacte' => $value_item->id_ctacte,
                        'nombre_fondo' => $value_item->nombre_fondo,
                        'nombre_depto' => $value_item->nombre_depto,
                        'nombre_restriccion' => $value_item->nombre_restriccion,
                        'nombre_cuentaaasi' => $value_item->nombre_cuentaaasi,
                        'nombre_tipoctacte' => $value_item->nombre_tipoctacte,
                        'nombre_cta_cte' => $value_item->nombre_cta_cte,
                        'items' => $array_current_account];
                // dd($lstLedge);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $array_types];

                return $this->generatePdf($array_types, "libro_mayor", "ledger");
            }
        }
    }

    public function financialAnalysisDepartment(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $lstFinancialAnalysisDepartment = ReportData::financialAnalysisDepartment($year, $month, $entity);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $lstFinancialAnalysisDepartment];                
            }
        }
        return response()->json($jResponse);
    }

    public function statementGroupLevel1(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->data->entity;
                $lstStatementGroupLevel1 = ReportData::statementGroupLevel1($entity);
                if ($lstStatementGroupLevel1) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstStatementGroupLevel1];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function statementGroupLevel2(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $id_group = $params->data->id_group;
                $lstStatementGroupLevel2 = ReportData::statementGroupLevel2($id_group);
                if ($lstStatementGroupLevel2) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstStatementGroupLevel2];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function statementGroupLevel3(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $id_group = $params->data->id_group;
                $lstStatementGroupLevel3 = ReportData::statementGroupLevel3($id_group);
                if ($lstStatementGroupLevel3) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstStatementGroupLevel3];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function statementGroup(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){

                $params = json_decode(file_get_contents("php://input"));

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $group = $params->data->group;

                $tingreso = ReportData::statementGroupI($year, $month, $entity, $group);
                foreach ($tingreso as $key => $data) {
                    $anho1 = $data->anho1;
                    $anho2 = $data->anho2;
                    $anho3 = $data->anho3;            
                }        
                $lstStatementGroup = ReportData::statementGroup($year, $month, $entity, $group,$anho1,$anho2,$anho3);
                //dd($lstStatementGroup);
                if ($lstStatementGroup) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstStatementGroup];
                }
            }
        }
        return response()->json($jResponse);
    }
    
    public function departmentalAnalysis(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $lstDepartmentalAnalysis = ReportData::departmentalAnalysis($year, $month, $entity);
                if ($lstDepartmentalAnalysis) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstDepartmentalAnalysis];
                }
            }
        }
        return response()->json($jResponse);
    }
    
    public function statementUPeULevel(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $id_entidad = $params->data->entity;
                $data = ReportData::levelParent($id_entidad);                
                foreach ($data as $key => $value) {            
                    $id_parent = $value->id_parent; 
                    $child = ReportData::levelChild($id_entidad,$id_parent);
                    foreach ($child as $key => $value1) { 
                        $id_parent1 = $value1->id_hijo;
                        $hijo = ReportData::levelChild($id_entidad,$id_parent1);
                        foreach ($hijo as $key => $value2) {
                            $id_parent2 = $value2->id_hijo;
                            $hijo2 = ReportData::levelChild2($id_entidad,$id_parent2);
                            $parent3[] = ['value' => $value2->id_parent, 'text' => $value2->nombre,'children'=>$hijo2];                    
                        }
                        $parent2[] = ['value' => $value1->id_parent, 'text' => $value1->nombre,'children'=>$parent3]; 
                        $parent3 = [];
                    }
                    $parent1[] = ['value' => $value->id_parent, 'text' => $value->nombre,'children'=>$parent2];
                    // $parent[]= $parent1[0];
                }
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $parent1[0]];
                //}
            }
        }
        return response()->json($jResponse);
    }

    public function dataStatement() {  
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
    
                //$api_key = Request::header('Content-Type');
                //$token = securityToken::validaToken($api_key);        
                //if($token == true){
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->data->entity;
                $year = $params->data->year;
                $month = $params->data->month;
                $group = $params->data->group;
                $jResponse = [
                    'success' => false,
                    'message' => 'ERROR',
                    'data' => array()
                ];
                try {            
                    $item1 = array();
                    $item2 = array();
                    $item3 = array();
                    $item4 = array();
                    $item5 = array();
                    $item6 = array();
                    $item7 = array();
                    $item8 = array();
                    $item9 = array();
                    $item10 = array();
                    $datos = ReportData::statementDatos($entity,$year,$month,$group);                
                    foreach ($datos as $key => $row) {
                        $grup = $row->grup;
                        $detalle = $row->detalle;
                        if($detalle == "TOTAL VENTAS"){
                            $total1 = $row->imp1;
                            $total2 = $row->imp2;
                            $total3 = $row->imp3;
                            array_push($item3,array("detalle" => "TOTAL VENTAS", "pct1" => 100,"pct2" => 100,"pct3" => 100));
                            array_push($item4,array("detalle" => "TOTAL VENTAS", "pct1" => round((($row->imp2-$row->imp1)/$row->imp1)*100),"pct2" => round((($row->imp3-$row->imp2)/$row->imp2)*100)));
                            array_push($item5,array("glosa" => "TOTAL VENTAS", "porct" => 100));
                            array_push($item7,array("glosa" => "INGRESOS NETOS", "imp1" => round($total1),"imp2" => round($total2),"imp3" => round($total3),"pspto" => round($row->ppto_m),"var"=>(round($total3-$row->ppto_m))));
                        }
                        array_push($item1, array("detalle" => $row->detalle, "imp1" => round($row->imp1),"imp2" => round($row->imp2),"imp3" => round($row->imp3)));
                        array_push($item2, array("detalle" => $row->detalle, "ppto_m" => round($row->ppto_m),"ppto_a" => round($row->ppto_a),"var_m" => round($row->var_m),
                            "var_a" => round($row->var_a),"varp_m" => $row->var_p,"indicador" => $row->indicador));
                        if($grup <> "A"){
                            array_push($item3, array("detalle" => $row->detalle, "pct1" => round(($row->imp1/$total1)*100),"pct2" => round(($row->imp2/$total2)*100),
                                "pct3" => round(($row->imp3/$total3)*100)));
                            array_push($item4, array("detalle" => $row->detalle, "pct1" => round(($row->imp2-$row->imp1)/$row->imp1*100),
                                "pct2" => round(($row->imp3-$row->imp2)/$row->imp2*100)));
                            array_push($item5, array("glosa" => $row->detalle, "porct" => round(($row->imp3/$total3)*100)));
                        }  
                        if($detalle == "UTILIDAD OPERATIVA"){
                            array_push($item8,array("glosa" => "UTILIDAD OPERATIVA", "imp1" => round($total1),"imp2" => round($total2),"imp3" => round($total3),"pspto" => round($row->ppto_m),"var"=>(round($total3-$row->ppto_m))));
                        }
                        if($detalle == "Gastos de Personal"){
                            array_push($item9,array("glosa" => "GASTOS DEL PERSONAL", "imp1" => round($total1),"imp2" => round($total2),"imp3" => round($total3),"pspto" => round($row->ppto_m),"var"=>(round($row->ppto_m-$total3))));
                        }
                        if($detalle == "Gastos Diversos"){
                            array_push($item10,array("glosa" => "GASTOS DIVERSOS", "imp1" => round($total1),"imp2" => round($total2),"imp3" => round($total3),"pspto" => round($row->ppto_m),"var"=>(round($row->ppto_m-$total3))));
                        }
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['item1' => $item1,'item2' => $item2,'item3' => $item3,'item4' => $item4,'item5' => $item5,'item7' => $item7,'item8' => $item8,'item9' => $item9,'item10' => $item10];
                } catch (Exception $e) {
                    dd($e);
                }
            /*}else{
                $jResponse = [
                    'success' => false,
                    'message' => 'ACCES DENIED',
                    'data' => array()
                ];
            } */  
            }
        }
        return response()->json($jResponse);
    }
       
    public function diaryBookSummary(Request $request){
        /*$jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $ruc = $params->data->ruc;
                $year = $params->data->year;
                $month = $params->data->month;*/
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];
            try{
                $ruc = $request->query('ruc');                
                $year = $request->query('year');
                $month = $request->query('month');
                $empresa = ReportData::idCompany($ruc);
                foreach ($empresa as $key => $data) {
                    $id_empresa = $data->id_empresa;
                }
                $data = ReportData::diaryBookSummary($id_empresa,  $year, $month);           
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];           
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
            //}
        }
        return response()->json($jResponse);
    }
    public function diaryBook(Request $request){
        /*$jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];
        $token = $this->request->header('Authorization');
        if($token){
            session_id($token);
            session_start();
            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
	    $valida = $result[0];   
	    if( $valida->active == 'SI'  ){                
                $ruc = $request->query('ruc');                
                $year = $request->query('year');
                $month = $request->query('month');
                try{
                    $empresa = ReportData::idCompany($ruc);
                    foreach ($empresa as $key => $data) {
                        $id_empresa = $data->id_empresa;
                    }
                    $data = ReportData::diaryBook($id_empresa,  $year, $month);           
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];           
                }catch(Exception $e){
                    dd($e);
                }
            }
        }
        return response()->json($jResponse);*/
        
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];
            $ruc = $request->query('ruc');                
            $year = $request->query('year');
            $month = $request->query('month');
            try{
                $empresa = ReportData::idCompany($ruc);
                foreach ($empresa as $key => $data) {
                    $id_empresa = $data->id_empresa;
                }
                $data = ReportData::diaryBook($id_empresa,  $year, $month);           
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];           
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
}