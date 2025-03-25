<?php

namespace App\Http\Controllers\Report\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Data\TreasuryData;
use Illuminate\Http\Request;
use PDF;
use DOMPDF;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;

class TreasuryController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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

    public function budget()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
            
            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $department = $params->data->functions;

        $lstBudget = TreasuryData::budget($year, $month, $entity, $department);
        $totalBudget = TreasuryData::totalBudget($year, $month, $entity, $department);
        $detailExpenses = TreasuryData::detail($year, $month, $entity, $department);
        $totalDetail = TreasuryData::totalDetail($year, $month, $entity, $department);

        if ($lstBudget && $totalBudget) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstBudget, 'saldos' => $totalBudget, 'detalle' => $detailExpenses, 'total_detalle' => $totalDetail];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function budgetMain()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $group = $params->data->group;

        $lstBudgetMain = TreasuryData::budgetMain($year, $month, $entity, $group);

        if ($lstBudgetMain) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstBudgetMain];
        }

            }
        }
        return response()->json($jResponse);
    }

public function budgets_travels(Request $request){
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

                        $lstTravels = TreasuryData::travels($year, $month, $entity);

                        $data = [
                            'items' => $lstTravels,
                            'total' => [],
                        ];

                        $jResponse['success'] = true;
                    if(count($lstTravels)>0){
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
 public function budgets_travels_details(Request $request,$currentaccount){
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

                    $lstTravels_detail = TreasuryData::travels_detail($year, $month, $entity, $currentaccount);

                    $data = [
                        'items' => [],
                        'total' => [],
                    ];

                    $jResponse['success'] = true;
                    if(count($lstTravels_detail)>0){
                        $jResponse['message'] = "Success";  
                        $data['items'] = $lstTravels_detail;
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

 public function budgets_departments(Request $request){
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
                        $lstUser = TreasuryData::user($id_user);
                        $lstUserDetail = $lstUser[0];
                        $lstUserTypeAdmin = $lstUserDetail->id_typeadmin; 

                        if($lstUserTypeAdmin == 3 || $lstUserTypeAdmin == 2 ){
                            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN($year, $month, $entity);
                        }if($lstUserTypeAdmin == 1){
                            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN_summary($year, $month, $entity, $id_user);
                        }   
                        $data = [
                            'items' => $lstFinancialAnalysisDepartment,
                            'total' => [],
                        ];

                        $jResponse['success'] = true;
                    if(count($lstFinancialAnalysisDepartment)>0){
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

 public function budgets_departments_details(Request $request,$department){
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

                    $lstBudget_detail = TreasuryData::budget_detail($year, $month, $entity, $department);


                    $data = [
                        'items' => [],
                        'total' => [],
                    ];

                    $jResponse['success'] = true;
                    if(count($lstBudget_detail)>0){
                        $jResponse['message'] = "Success";  
                        $data['items'] = $lstBudget_detail;
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



    public function budgetUPN()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
            
            $id_user = $valida->id_user;
            $lstUser = TreasuryData::user($id_user);
            $lstUserDetail = $lstUser[0];
            $lstUserTypeAdmin = $lstUserDetail->id_typeadmin; 

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            //$option = $params->data->option;
            //$employees = $params->data->employees;

        if($lstUserTypeAdmin == 3 || $lstUserTypeAdmin == 2 ){
            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN($year, $month, $entity);
        }if($lstUserTypeAdmin == 1){
            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN_summary($year, $month, $entity, $id_user);
        }   
        /*
        foreach ($lstFinancialAnalysisDepartment as $key => $row) {
            $departamento = $row->departamento;
            $lstBudget_detail = TreasuryData::budget_detail($year, $month, $entity, $departamento);
            $row->Tasks = $lstBudget_detail;
            $lstBudget_detail_total = TreasuryData::budget_detail_total($year, $month, $entity, $departamento);
            $row->Totales = $lstBudget_detail_total;
        }
        */
        
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstFinancialAnalysisDepartment];
            
            }
        }
        return response()->json($jResponse);
    }

    public function pdfbudgetUPN(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));

        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
            
            $id_user = $valida->id_user;
            $lstUser = TreasuryData::user($id_user);
            $lstUserDetail = $lstUser[0];
            $lstUserTypeAdmin = $lstUserDetail->id_typeadmin; 

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;

            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN($year, $month, $entity);  
                foreach ($lstFinancialAnalysisDepartment as $key => $row) {
                    if ($lstFinancialAnalysisDepartment) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['items'=> $lstFinancialAnalysisDepartment];
                    }
                }
                $lstData = json_decode(json_encode($jResponse['data']['items']), true);
                return $this->generatePdf($lstData, 'budget_upn', 'budget_upn');
            }
        }
    }

    public function pdfbudgetUPN_detail(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        //$name = $request->input('name');
        
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];     
        if( $valida->active == 'SI'  ){   
            
            $id_user = $valida->id_user;
            $lstUser = TreasuryData::user($id_user);
            $lstUserDetail = $lstUser[0];
            $lstUserTypeAdmin = $lstUserDetail->id_typeadmin; 

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $department = $params->data->department;

            $lstBudget_detail = TreasuryData::budget_detail($year, $month, $entity, $department);
            foreach ($lstBudget_detail as $key => $row) {
                    if ($lstBudget_detail) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['items'=> $lstBudget_detail];
                    }
                }
                $lstData = json_decode(json_encode($jResponse['data']['items']), true);
                return $this->generatePdf($lstData, 'budget_detail', 'budget_detail');
             }
        }
    }


    public function budgetUPN_summary()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
            
            $id_user = $valida->id_user;
            $lstUser = TreasuryData::user($id_user);
            $lstUserDetail = $lstUser[0];
            $lstUserTypeAdmin = $lstUserDetail->id_typeadmin; 

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $option = $params->data->option;
            $employees = $params->data->employees;

        if($lstUserTypeAdmin == 3 || $lstUserTypeAdmin == 2 ){
            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN($year, $month, $entity);
        }if($lstUserTypeAdmin == 1 ){
            $lstFinancialAnalysisDepartment = TreasuryData::budgetUPN_summary($year, $month, $entity, $employees);
        }
        
        $jResponse['success'] = true;
        $jResponse['message'] = 'OK';
        $jResponse['data'] = ['items' => $lstFinancialAnalysisDepartment];

            }
        }
        return response()->json($jResponse);
    }

    public function travels_summary()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
            
            $id_user = $valida->id_user;
            $lstUser = TreasuryData::user($id_user);
            $lstUserDetail = $lstUser[0];
            $lstUserTypeAdmin = $lstUserDetail->id_typeadmin;

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $option = $params->data->option;
            $employees = $params->data->employees;

        if($lstUserTypeAdmin == 3 || $lstUserTypeAdmin == 2 ){
            $lstTravels = TreasuryData::travels($year, $month, $entity);
        }if($lstUserTypeAdmin == 1 ){
            $lstTravels = TreasuryData::travels_summary($year, $month, $entity, $id_user);
        }
        
        $jResponse['success'] = true;
        $jResponse['message'] = 'OK';
        $jResponse['data'] = ['items' => $lstTravels];

            }
        }
        return response()->json($jResponse);
    }

    public function travels()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
          
            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;

        $lstTravels = TreasuryData::travels($year, $month, $entity);

                if ($lstTravels) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstTravels];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function pdftravels()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
          
            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;

        $lstTravels = TreasuryData::travels($year, $month, $entity);
        foreach ($lstTravels as $key => $row) {
                if ($lstTravels) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items'=> $lstTravels];
                }
            }
            $lstData = json_decode(json_encode($jResponse['data']['items']), true);
            return $this->generatePdf($lstData, 'travels', 'travels');
            }
        }
    }
    public function travels_detail()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
          
            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $currentaccounts = $params->data->currentaccounts;

        $lstTravels_detail = TreasuryData::travels_detail($year, $month, $entity, $currentaccounts);

                if ($lstTravels_detail) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstTravels_detail];
                }

            }
        }
        return response()->json($jResponse);
    }
    public function pdftravels_detail()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   
          
            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $currentaccounts = $params->data->currentaccounts;

            $lstTravels_detail = TreasuryData::travels_detail($year, $month, $entity, $currentaccounts);
            foreach ($lstTravels_detail as $key => $row) {
                if ($lstTravels_detail) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items'=> $lstTravels_detail];
                }
            }
            $lstData = json_decode(json_encode($jResponse['data']['items']), true);
            return $this->generatePdf($lstData, 'travels_detail', 'travels_detail');
            }
        }
    }

    public function travels_detail_total()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $currentaccounts = $params->data->currentaccounts;

        $lstTravels_detail_total = TreasuryData::travels_detail_total($year, $month, $entity, $currentaccounts);

                if ($lstTravels_detail_total) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstTravels_detail_total];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function budget_detail_test(Request $request){
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
            $department = $request->query('department');

                try{

                    $lstBudget_detail = TreasuryData::budget_detail($year, $month, $entity, $department);
                    $jResponse['success'] = true;
                    if(count($lstBudget_detail)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = $lstBudget_detail;
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


    public function budget_detail()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        if( $valida->active == 'SI'  ){   

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $department = $params->data->department;
            //$departamento = $params->data->departamento;

            $lstBudget_detail = TreasuryData::budget_detail($year, $month, $entity, $department);

                if ($lstBudget_detail) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstBudget_detail];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function budget_detail_total()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $data = array();

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
        $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        $valida = $result[0];            
        
        if( $valida->active == 'SI'  ){   

            $year = $params->data->year;
            $month = $params->data->month;
            $entity = $params->data->entity;
            $department = $params->data->department;
            //$departamento = $params->data->departamento;

        $lstBudget_detail_total = TreasuryData::budget_detail_total($year, $month, $entity, $department);

                if ($lstBudget_detail_total) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstBudget_detail_total];
                }
            }
        }
        return response()->json($jResponse);
    }
    
}
