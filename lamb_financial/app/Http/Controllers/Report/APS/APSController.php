<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Report\APS;

use App\Http\Controllers\Controller;
use App\Http\Data\APSData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class APSController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function payroll()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');

        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if ($valida->active == 'SI') {

                $lstPayroll = APSData::payroll();

                if ($lstPayroll) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $lstPayroll];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function detailFinancialStatement(Request $request)
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
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
            if ($valida->active == 'SI') {

                $month = $request->query('month');
                $year = $request->query('year');
                $entity = $request->query('entity');

                try {
                    $data = APSData::detailFinancialStatement($year, $month, $entity);
                    //$dataTotal = APSData::totalFinancialStatement($year, $month, $entity);
                    $jResponse['success'] = true;
                    if (count($data) > 0) {
                        $jResponse['message'] = "Success";
                        //$jResponse['data'] = ['items' => $data];
                        $jResponse['data'] = [$data];
                        $error = "200";
                    } else {
                        $jResponse['message'] = "The item does not exist";
                        $error = "404";
                    }
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
        }
        return response()->json($jResponse, $error);
    }

    public function summaryFinancialStatement(Request $request)
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
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
            if ($valida->active == 'SI') {

                $month = $request->query('month');
                $year = $request->query('year');
                $entity = $request->query('entity');

                try {
                    $data = APSData::summaryFinancialStatement($year, $month, $entity);
                    $dataTotal = APSData::totalFinancialStatement($year, $month, $entity);
                    $jResponse['success'] = true;
                    if (count($data) > 0) {
                        $jResponse['message'] = "Success";
                        $jResponse['data'] = ['items' => $data, 'total' => $dataTotal];
                        //$jResponse['datatotal'] = $dataTotal[0];
                        $error = "200";
                    } else {
                        $jResponse['message'] = "The item does not exist";
                        $error = "404";
                    }
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
        }
        return response()->json($jResponse, $error);
    }

    public function personalAccountStatement()
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
            if ($valida->active == 'SI') {

                $entity = $params->data->entity;
                $year = $params->data->year;
                $month = $params->data->month;
                $id_persona = $params->data->id_persona;


                $lstHeader = APSData::pas_header($entity);
                $lstPersonalData = APSData::pas_personal_data($entity, $year, $month, $id_persona);
                $lstPayrollEarnings = APSData::pas_payroll_earnings($entity, $year, $month, $id_persona);
                $lstPayrollContribute = APSData::pas_payroll_contribute($entity, $year, $month, $id_persona);
                $lstPayrollDiscount = APSData::pas_payroll_discount($entity, $year, $month, $id_persona);

                $lstAMPersonalAccounts = APSData::pas_account_movement($entity, $year, $month, $lstPersonalData[0]->num_documento, '1135001');
                $lstAMPendingSurrenders = APSData::pas_account_movement($entity, $year, $month, $lstPersonalData[0]->num_documento, '1135012');
                $lstAMAdvanceGratuities = APSData::pas_account_movement($entity, $year, $month, $lstPersonalData[0]->num_documento, '1135016');
                $lstAMInstitutionalLoan = APSData::pas_account_movement($entity, $year, $month, $lstPersonalData[0]->num_documento, '1135060');

                if ($lstHeader) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'header' => $lstHeader[0],
                        'personal_data' => $lstPersonalData[0],
                        'payroll' => [
                            'earnings' => $lstPayrollEarnings,
                            'contributes' => $lstPayrollContribute,
                            'discounts' => $lstPayrollDiscount
                        ],
                        'account_movements' => [
                            'personal_accounts' => [
                                'account' => '1135001',
                                'account_name' => 'Cuenta Personal',
                                'items' => $lstAMPersonalAccounts
                            ],
                            'pending_surrenders' => [
                                'account' => '1135012',
                                'account_name' => 'Rendiciones Pendientes',
                                'items' => $lstAMPendingSurrenders
                            ],
                            'advance_gratuities' => [
                                'account' => '1135016',
                                'account_name' => 'Adelanto Gratificaciones',
                                'items' => $lstAMAdvanceGratuities
                            ],
                            'institutional_loal' => [
                                'account' => '1135060',
                                'account_name' => 'Préstamo Institucional',
                                'items' => $lstAMInstitutionalLoan
                            ],
                        ]

                    ];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function listPersonalAPS()
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
            if ($valida->active == 'SI') {

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;

                $lstPersonal = APSData::pas_list_personal($entity, $year, $month);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $lstPersonal];
            }
        }
        return response()->json($jResponse);
    }

    public function summaryHelp()
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
            if ($valida->active == 'SI') {

                $entity = $params->data->entity;
                $year = $params->data->year;
                $month = $params->data->month;

                $data = APSData::summaryHelp($entity, $year, $month);
                $dataT = APSData::summaryHelpTotal($entity, $year, $month);
                $resumen = array();
                foreach ($dataT as $key => $row) {
                    array_push($resumen, array("name" => "MEDICA", "value" => $row->medica));
                    array_push($resumen, array("name" => "ODONTOLOGICA", "value" => $row->odonto));
                    array_push($resumen, array("name" => "OFTALMOLOGICA", "value" => $row->oftal));
                    array_push($resumen, array("name" => "EDUCACION", "value" => $row->educ));
                    array_push($resumen, array("name" => "CASA", "value" => $row->casa));
                    array_push($resumen, array("name" => "E.PROFESIONAL", "value" => $row->profe));
                    array_push($resumen, array("name" => "DEPRECIACION", "value" => $row->deprec));
                    array_push($resumen, array("name" => "OTROS", "value" => $row->otros));
                    array_push($resumen, array("name" => "TOTAL", "value" => $row->otros));
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data, 'resumen' => $resumen];
            }
        }
        return response()->json($jResponse);
    }

    public function previsionSocial()
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
            if ($valida->active == 'SI') {

                $entity = $params->data->entity;
                $year = $params->data->year;
                $month = $params->data->month;
                $option = $params->data->option;

                if ($option == 1) {
                    $data = APSData::previsionAFP($entity, $year, $month);
                } elseif ($option == 2) {
                    $data = APSData::previsionONP($entity, $year, $month);
                } elseif ($option == 5) {
                    $data = APSData::previsionAFPNET($entity, $year, $month);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
            }
        }
        return response()->json($jResponse);
    }

    public function paymentTicket()
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
            if ($valida->active == 'SI') {

                $entity = $params->data->entity;
                $year = $params->data->year;
                $month = $params->data->month;
                $option = $params->data->option;
                $dni = $params->data->dni;

                try {
                    //    dd($remuneration);
                    //$jResponse['data'] = ['items' => $empresa,'employee' => $employee];
                    /*
                    if($option == 1){
                        $data = APSData::previsionAFP($entity, $year, $month);
                    }else{
                        $data = APSData::previsionONP($entity, $year, $month);
                    } */
                    $entidad = APSData::entidadPersona($entity);
                    foreach ($entidad as $key => $data) {
                        $id_persona = $data->id_persona;
                    }
                    $empresa = APSData::entidadEmpresa($id_persona);

                    if ($option == true) {
                        $data_planilla = APSData::person($dni);
                    } else {
                        $data_planilla = APSData::entidadPlanilla($entity, $year, $month);
                    }
                    $planilla = [];
                    foreach ($data_planilla as $key => $data) {
                        $id_employe = $data->id_persona;

                        $employee = APSData::employee($entity, $id_employe, $year, $month);
                        $remuneration = APSData::remuneration($entity, $id_employe, $year, $month);
                        $retention = APSData::retention($entity, $id_employe, $year, $month);
                        $contribution = APSData::contribution($entity, $id_employe, $year, $month);
                        $diezmo = APSData::diezmo($entity, $id_employe, $year, $month);
                        $tremu = APSData::tRemuneration($entity, $id_employe, $year, $month);
                        $treten = APSData::tRetention($entity, $id_employe, $year, $month);
                        $tcontri = APSData::tContribution($entity, $id_employe, $year, $month);
                        $tneto = APSData::Neto($entity, $id_employe, $year, $month);


                        $employee1 = [];
                        foreach ($employee as $key => $data_employee) {
                            $employee1[] = ['nom_persona' => $data_employee->nom_persona,
                                'nom_cargo' => $data_employee->nom_cargo,
                                'mes' => $data_employee->mes,
                                'essalud' => $data_employee->essalud,
                                'cuss' => $data_employee->cuss,
                                'fec_nacimiento' => $data_employee->fec_nacimiento,
                                'num_documento' => $data_employee->num_documento,
                                'fec_inicio' => $data_employee->fec_inicio,
                                'fec_termino' => $data_employee->fec_termino,
                                'dh' => $data_employee->dh,
                                'vacaciones' => $data_employee->vacaciones,
                                'afp' => $data_employee->afp,
                                'mes_name' => $data_employee->mes_name];
                        }
                        $remuneration_item = [];
                        foreach ($remuneration as $key => $data_remun) {
                            $remuneration_item[] = ['nombre' => $data_remun->nombre, 'importe' => $data_remun->cos_valor];
                        }
                        $retention_item = [];
                        foreach ($retention as $key => $data_reten) {
                            $retention_item[] = ['nombre' => $data_reten->nombre, 'importe' => $data_reten->cos_valor];
                        }
                        $contribution_item = [];
                        foreach ($contribution as $key => $data_contr) {
                            $contribution_item[] = ['nombre' => $data_contr->nombre, 'importe' => $data_contr->cos_valor];
                        }
                        $diezmo_item = [];
                        foreach ($diezmo as $key => $data_diezmo) {
                            $diezmo_item[] = ['nombre' => $data_diezmo->nombre, 'importe' => $data_diezmo->cos_valor];
                        }
                        $tremu_item = [];
                        foreach ($tremu as $key => $tremu) {
                            $tremu_item[] = ['imp' => $tremu->imp];
                        }
                        $treten_item = [];
                        foreach ($treten as $key => $treten) {
                            $treten_item[] = ['imp' => $treten->imp];
                        }
                        $tcontri_item = [];
                        foreach ($tcontri as $key => $tcontri) {
                            $tcontri_item[] = ['imp' => $tcontri->imp];
                        }
                        $tneto_item = [];
                        foreach ($tneto as $key => $tneto) {
                            $tneto_item[] = ['imp' => $tneto->imp];
                        }

                        $company = [];
                        foreach ($empresa as $key => $data) {

                            $company[] = ['id_ruc' => $data->id_ruc, 'nombre' => $data->nombre, 'employee' => $employee1[0],
                                'remuneration' => $remuneration_item,
                                'retention' => $retention_item,
                                'contribution' => $contribution_item,
                                'diezmo' => $diezmo_item,
                                't_remu' => $tremu_item,
                                't_reten' => $treten_item,
                                't_contri' => $tcontri_item,
                                't_neto' => $tneto_item,
                                'entity' => $entity];

                            $company[] = ['id_ruc' => $data->id_ruc, 'nombre' => $data->nombre, 'employee' => $employee1[0],
                                'remuneration' => $remuneration_item,
                                'retention' => $retention_item,
                                'contribution' => $contribution_item,
                                'diezmo' => $diezmo_item,
                                't_remu' => $tremu_item,
                                't_reten' => $treten_item,
                                't_contri' => $tcontri_item,
                                't_neto' => $tneto_item,
                                'entity' => $entity];

                        }

                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['items' => $company];
                        array_push($planilla, array("datos" => $company));
//dd($company);
                    }

                } catch (Exception $e) {
                    dd($e);
                }
                //dd($planilla);
                return $this->generatePdf($planilla, 'ticketPayment', 'ticketPayment');
            }
        }
    }

    public function generatePdf($p_data, $namePdf, $nameView)
    {
        $data = $p_data;
        //para guardar en el storage
        //$path = storage_path() . '/app/public/report.pdf';
        //$pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', 'landscape')->save($path);
        $pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4');
        return $pdf->stream($namePdf . '.pdf');
    }


    public function generatePdfQuinta($p_data, $namePdf, $nameView)
    {
        $data = $p_data;
        //para guardar en el storage
        //$path = storage_path() . '/app/public/report.pdf';
        //$pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4', 'landscape')->save($path);
        $pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('a4');
        return $pdf->stream($namePdf . '.pdf');
    }

    public function quintaCategoria()
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
            if ($valida->active == 'SI') {

                $entity = $params->data->entity;
                $year = $params->data->year;
                $option = $params->data->option;
                $dni = $params->data->dni;
                $report = [];
                try {
                    $entidad = APSData::entidadPersona($entity);
                    foreach ($entidad as $key => $data) {
                        //$id_persona = $data->id_persona;
                        $id_empresa = $data->id_empresa;
                    }
                    //$empresa = APSData::entidadEmpresa($id_persona);

                    if ($option == true) {
                        $data_person = APSData::person($dni);
                        foreach ($data_person as $key => $data) {
                            $id_employe = $data->id_persona;
                        }
                        $opc = "AND APS_Planilla_Detalle.ID_PERSONA = " . $id_employe;
                        $category = APSData::quintaCategoria($entity, $year, $id_empresa, $opc);
                    } else {
                        $opc = "";
                        $category = APSData::quintaCategoria($entity, $year, $id_empresa, $opc);
                    }
                    //dd($category);
                    $category_item = [];
                    foreach ($category as $key => $data) {

                        $category_item[] = ['nombre' => $data->nom_persona,
                            'documento' => $data->num_documento,
                            'a' => number_format($data->a, 2),
                            'b' => number_format($data->b, 2),
                            'c' => number_format($data->c, 2),
                            'd' => number_format($data->d, 2),
                            'e' => number_format($data->e, 2),
                            'f' => number_format($data->f, 2),
                            'g' => number_format($data->g, 2),
                            'h' => number_format($data->h, 2),
                            'i' => number_format($data->i, 2),
                            'j' => number_format($data->j, 2),
                            'retenciones' => number_format($data->retenciones, 2),
                            'uit' => number_format($data->uit_7, 2),
                            'renta_bruta' => number_format($data->renta_bruta, 2),
                            'renta_neta' => number_format($data->renta_neta, 2),
                            'imp_renta' => number_format($data->imp_renta, 2),
                            'saldo' => number_format($data->saldo, 2),
                            'year' => $year
                        ];

                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        // $jResponse['data'] = ['items' => $category_item];
                        array_push($report, array("datos" => $category_item));
                        $category_item = [];
                    }
                } catch (Exception $e) {
                    dd($e);
                }
                return $this->generatePdfQuinta($report, 'quinta', 'quinta');
            }
        }
    }


    public function exel(Request $request)
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $data = array();
        $token = $this->request->header('Authorization');
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if ($valida->active == 'SI') {

                $file = $request->file('upload-file');
                $csvData = file_get_contents($file);
                $rows = array_map("str_getcsv", explode("\n", $csvData));
                $header = array_shift($rows);
                //dd($header);
                $data = [];
                foreach ($rows as $row) {

                    //$row = array_combine($header, $row);
                    array_push($data, array('name' => $row[0]));

                }

                $jResponse['data'] = $rows;
                return response()->json($jResponse);
            }
        }
    }

    public function plameJOR(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            try {
                $data = APSData::plameJOR($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK Jesus';
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

    public function plameSNL(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            try {
                $data = APSData::plameSNL($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

    public function plameREM(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            try {
                $data = APSData::plameREM($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

    public function plameHonorarios(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = APSData::plameHonorarios($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

    public function plamePS4(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = APSData::plamePS4($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

    public function plameTOC(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = APSData::plameTOC($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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

    public function plameFOR(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = APSData::plameFOR($id_empresa, $id_entidad, $id_anho, $id_mes);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
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
}