<?php

namespace App\Http\Controllers\HumanTalentMgt;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\ContractData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalentMgt\ParameterData;
use DOMPDF;

use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function estado_cont(Request $request)
    {
        // dd('sss');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = ContractData::estado_cont($request);
                //    dd('sss', $data);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listEstadoContDepto(Request $request)
    {
        // dd('sss');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = ContractData::listEstadoContDepto($request);
                //    dd('sss', $data);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $query  = collect($data)->groupBy('tipo');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['tipo' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = $datar;
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
    public function deleteListEstadoContDepto($id_estado_cont_dept)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = ContractData::deleteListEstadoContDepto($id_estado_cont_dept);
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
    public function updateEstadoCont($id_estado_cont_dept, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = ContractData::updateEstadoCont($id_estado_cont_dept, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
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
    public function addEstadoContDept(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $response = ContractData::addEstadoContDept($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
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

    public function getPlantillaContrato(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ContractData::getPlantillaContrato($request);
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

    public function getPlantilla(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ContractData::getPlantilla($request);
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

    public function createPlantilla(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = ContractData::createPlantilla(json_encode($request->data), $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['id_contrato_plantilla'] = $response['id_contrato_plantilla'];
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

    public function updatePlantilla($id_contrato_plantilla, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = ContractData::updatePlantilla($id_contrato_plantilla, json_encode($request->data), $id_user);
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

    //parametros

    public function getParametrosByTipo(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $jResponse = [];
        if ($valida == 'SI') {
            try {
                $data = ContractData::getParametrosByTipo($request);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getParametros()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ContractData::getParametros();
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

    public function createParametros(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];, $id_user
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = ContractData::createParametros(json_encode($request->data));
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
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

    public function updateParametros($id_contrato_parametro, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];, $id_user
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = ContractData::updateParametros($id_contrato_parametro, json_encode($request->data));
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

    public static function testPDF($request)
    {
        $input_vista = $request;
        $resultado = [];
        $aaa = ContractData::getPlantillaContrato($request);
        $texto = $aaa->plantilla;
        $plantilla_parametro = ContractData::getParametros();
        $abc = [];
        $lista_patametro = [];
        for ($i = 0; $i < count($plantilla_parametro); $i++) {
            $element = $plantilla_parametro[$i];
            $input = explode(',', $element->input);
            $p_v = explode(',', $element->p_input);
            $query = $element->sql;
            $nn = array();
            foreach ($p_v as $key => $value) {
                $nn[$value] = $input_vista[$input[$key]];
            }
            $abc = DB::select($query, $nn);
            array_push($resultado, $abc);
        }
        for ($i = 0; $i < count($plantilla_parametro); $i++) {
            $element = $plantilla_parametro[$i];
            $param = explode(',', $element->parametro);
            $campo = explode(',', $element->campo);
            for ($j = 0; $j < count($param); $j++) {
                array_push($lista_patametro, ["param" => $param[$j], "campo" => $campo[$j]]);
            }
        }
        for ($k = 0; $k < count($resultado); $k++) {
            $element = $resultado[$k];
            for ($l = 0; $l < count($element); $l++) {
                $item = $element[$l];
                foreach ($item as $key => $value) {
                    $find = array_search($key, array_column($lista_patametro, 'campo'));
                    $param = $lista_patametro[$find]['param'];
                    $texto = str_replace($param, $value, $texto);
                }
            }
        }
        $texto = str_replace("color:windowtext;", "", $texto);
        return $texto;
    }

    public function testPDFRENDER(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {
                $data = ContractController::testPDF($request);
                $pdf = DOMPDF::loadView('pdf.mgt.plantilla_contrato', [
                    'data' => $data
                ])->setPaper('a4', 'portrait');

                $doc =  base64_encode($pdf->stream('print.pdf'));
                if ($doc) {
                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $doc]
                    ];
                } else {
                    $jResponse = [
                        'success' => false,
                        'message' => "Sin resultados",
                        'data' => ['items' => '']
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
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => false,
            'message' => $mensaje,
            'data' => ['items' => '']
        ];

        return response()->json($jResponse);
    }

    //Orden de contratacion

    public function getGenerated(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $per_page = $request->per_page;
                $object = ContractData::getGenerated($request, $id_user, $per_page);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function createOrderContract(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = ContractData::createOrderContract($request, $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['id_contrato'] = $response['id_contrato'];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['info'] = $response;
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

    public function updateOrderContract(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //$response = ContractData::createOrderContract(json_encode($request->data), $id_user);
                $response = ContractData::updateOrderContract($request, $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['info'] = $response;
                    //$jResponse['id_contrato_plantilla'] = $response['id_contrato_plantilla'];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['info'] = $response;
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

    public function changestatus($id_contrato, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $id_estado_cont_depto = $request->id_estado_cont_depto;
                $comentario = $request->comentario;
                $response = ContractData::changestatus($id_estado_cont_depto, $id_contrato, $id_user, $comentario);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
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

    public function firstDataGenerate()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $tip_reg = ParameterData::getTipoRegimenLaboral();
                $tip_ctr = ParameterData::getTipoSCTRPension();
                $sit_tra = ParameterData::getSituacionTrabajador();
                $tip_pag = ParameterData::getTipoPago();
                $tip_cat = ParameterData::getTipoCategoriaOcupa();
                $tip_ses = ParameterData::getSituacionEspecial();
                $tip_dtr = ParameterData::getTipoDobleTrib();
                $tip_con = ParameterData::getTipoContrato();
                $per_rem = ParameterData::getPerRemuneracion();
                $tip_sta = ParameterData::getTipoStatus();
                $gru_pla = ParameterData::getGrupoPlanilla();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'tip_reg' => $tip_reg,
                    'tip_ctr' => $tip_ctr,
                    'sit_tra' => $sit_tra,
                    'tip_pag' => $tip_pag,
                    'tip_cat' => $tip_cat,
                    'tip_ses' => $tip_ses,
                    'tip_dtr' => $tip_dtr,
                    'tip_con' => $tip_con,
                    'per_rem' => $per_rem,
                    'tip_sta' => $tip_sta,
                    'gru_pla' => $gru_pla
                ];
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

    public function getContractToGen(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ContractData::getContractToGen($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function getContract(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $per_page = $request->per_page;
                $object = ContractData::getContract($request, $id_user, $per_page);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function getContractActiveWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $per_page = $request->per_page;
                $object = ContractData::getContractActiveWorker($request, $per_page);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function getContractActive(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_contrato = $request->id_contrato;
                $object = ContractData::getContractActive($id_contrato);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function generateTxt(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ContractData::generateTxt($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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

    public function getInfoContractExplicit(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ContractData::getInfoContractExplicit($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
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
}
