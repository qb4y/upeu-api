<?php

namespace App\Http\Controllers\HumanTalentMgt;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\PayrollData;
use App\Http\Data\HumanTalentMgt\ComunData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use Excel;
use Session;
use DOMPDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function ListSalaryScale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_depto = $request->query('id_depto');
            $id_anho = $request->query('id_anho');
            $id_nivel = $request->query('id_nivel');
            $perpage = $request->query('perpage');

            $data = PayrollData::ListSalaryScale($id_entidad, $id_depto, $id_anho, $id_nivel, $perpage);

            $fmr = ComunData::getParameter('PARAM_FMR_ESCALA', $id_entidad, $id_anho);

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['data' => $data, 'frm' => $fmr];
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
    public function AddSalaryScale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::AddSalaryScale($request);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function UpdateSalaryScale($id, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::UpdateSalaryScale($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function DeleteSalaryScale($id_scale_group)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::DeleteSalaryScale($id_scale_group);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function ListsPensionScheme(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];

            $data = PayrollData::ListsPensionScheme();


            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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
    public function updatePensionScheme($id_regimen_pensionaria, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::updatePensionScheme($id_regimen_pensionaria, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getSearchPersonAssign(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_depto = $request->query('id_depto');
            $id_tipo_pago_mensual = $request->query('id_tipo_pago_mensual');
            $datos = $request->query('datos');
            $perpage = $request->query('perpage');

            $data = PayrollData::getSearchPersonAssign($id_entidad, $id_depto, $id_tipo_pago_mensual, $datos, $perpage);


            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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
    public function ListGeneralPayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_depto = $request->query('id_depto');
            $id_tipo_pago_mensual = $request->query('id_tipo_pago_mensual');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            $data = PayrollData::ListGeneralPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes);
            $dataMonth = PayrollData::ListGeneralMonthPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes);


            if ((count($data) > 0) or (count($dataMonth) > 0)) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['general' => $data, 'month' => $dataMonth];
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
    public function ListGeneralPaymentValid(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PayrollData::ListGeneralPaymentValid($request);


                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data,];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Formato Incorrecto';
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function ListGeneralPaymentXLS(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_tipo_pago_mensual = $request->query('id_tipo_pago_mensual');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $nombre = $request->query('nombre');
                $codigo = $request->query('codigo');
                $tipo = $request->query('tipo');
                $data = PayrollData::ListGeneralPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes);

                $filename = substr(str_replace(' ', '_', $nombre), 0, 25);

                $excel = Excel::create($filename, function ($excel) use ($data, $filename, $codigo, $tipo, $nombre) {

                    $excel->sheet($filename, function ($sheet) use ($data, $codigo, $tipo, $nombre) {
                        $sheet->loadView("xls.mgt.listgeneralpayment")
                            ->with('data', $data)
                            ->with('codigo', $codigo)
                            ->with('tipo', $tipo)
                            ->with('nombre', $nombre);
                        $sheet->setOrientation('landscape');
                    });
                });

                $archivo = ($excel->string('xls'));

                $doc  = base64_encode($archivo);
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
                ];

                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        }
        $excel = Excel::create('Error', function ($excel) use ($mensaje) {

            $excel->sheet('Error', function ($sheet) use ($mensaje) {
                $sheet->loadView("xls.error")->with('mensaje', $mensaje);
                $sheet->setOrientation('landscape');
            });
        });

        $file = $excel->string('xls');
        $doc  = base64_encode($file);

        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];

        return response()->json($jResponse);
    }
    public function addGeneralPayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::addGeneralPayment($request);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateGeneralPayment($id_persona, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::updateGeneralPayment($id_persona, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateGeneralPaymentAll($id_tipo_pago_mensual, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::updateGeneralPaymentAll($id_tipo_pago_mensual, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function DeleteGeneralPayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::DeleteGeneralPayment($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addMonthPayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::addMonthPayment($request);

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function DeleteMonthPayment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::DeleteMonthPayment($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function MonthPaymentPDF(Request $request)
    {


        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];

        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {

                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_tipo_pago_mensual = $request->query('id_tipo_pago_mensual');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $nombre = $request->query('nombre');
                $concepto = $request->query('concepto');
                $monthname = $request->query('monthname');
                $codigo = $request->query('codigo');
                $nomdepto = $request->query('nomdepto');
                $data = PayrollData::ListGeneralMonthPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes);

                $TypeMonth = reset($data);
                $TypeMonth = (object)$TypeMonth;


                $session = ['depto' => $nomdepto, 'user' => $username];

                Session::put('datosPrint', $session);


                $pdf = DOMPDF::loadView('pdf.mgt.monthpayment', [
                    'data' => $data,
                    'nombre' => $nombre,
                    'concepto' => $concepto,
                    'monthname' => $monthname,
                    'codigo' => $codigo,
                    'id_anho' => $id_anho,
                    'TypeMonth' => $TypeMonth
                ])->setPaper('a4', 'portrait');


                $doc =  base64_encode($pdf->stream('print.pdf'));

                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
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


        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];
        return response()->json($jResponse);
    }
    public function listTypePayMonth(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $nombre = $request->query('nombre');
            $per_page = $request->query('per_page');
            $data = PayrollData::listTypePayMonth($id_entidad, $nombre, $per_page);

            if ((count($data) > 0)) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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
    public function addTypePayMonth(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PayrollData::addTypePayMonth($request);
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
    public function deleteTypePayMonth($id_tipo_pago_mensual)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PayrollData::deleteTypePayMonth($id_tipo_pago_mensual);
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
    public function showTypePayMonth($id_tipo_pago_mensual)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // dd($id_tipo_horario);
                $data = PayrollData::showTypePayMonth($id_tipo_pago_mensual);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['object' => $data];
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateTypePayMonth($id_tipo_pago_mensual, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PayrollData::updateTypePayMonth($id_tipo_pago_mensual, $request);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addPayprolControl(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona   = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha = $date->format('Y-m-d H:i:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::addPayprolControl($id_persona, $fecha, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listPayprolControl(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_depto = $request->query('id_depto');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            $data = PayrollData::listPayprolControl($id_entidad, $id_depto, $id_anho, $id_mes);

            if ((count($data) > 0)) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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
    public function deletePayprolControl($id_proc_planilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = PayrollData::deletePayprolControl($id_proc_planilla);
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
    public function updatePayprolControl($id_proc_planilla, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::updatePayprolControl($id_proc_planilla, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updatePayprolControlClose($id_proc_planilla, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = PayrollData::updatePayprolControlClose($id_proc_planilla, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    //Escala Salarial Docente
    public function regSalaryScaleTeacher(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = PayrollData::regSalaryScaleTeacher($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    //$jResponse['asd'] = $response['as'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getSalaryScaleTeacher(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = PayrollData::getSalaryScaleTeacher($request);
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

    public function getSalaryScaleTeacherSp(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = PayrollData::getSalaryScaleTeacherSp($request);
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

    public function getSemestrePrograma(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = PayrollData::getSemestrePrograma($request);
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

    public function getCostAssigned(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = PayrollData::getCostAssigned($request);
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

    public function regCostxHour(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = PayrollData::regCostxHour(json_encode($request->data), $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['id_docente_costoxhora'] = $response['id_docente_costoxhora'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updCostxHour($id_costo, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = PayrollData::updCostxHour($id_costo, json_encode($request->data), $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
}
