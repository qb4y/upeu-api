<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 25/02/20
 * Time: 09:48 AM
 */

namespace App\Http\Controllers\FinancialEnrollment;



use App\Http\Controllers\Controller;
use App\Http\Data\FinancialEnrollment\ProformaPaymentStudentData;
use App\Http\Data\FinancialEnrollment\ProformaPaymentValidatorData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class ProformaPaymentStudent extends Controller
{

    public function show(Request $request, $id){

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $data = ProformaPaymentStudentData::show($id, $response);
            $response = array_merge($response, $data);
            $response['typeChange'] = GlobalMethods::verificaTipoCambio();
            $response['mode'] = ProformaPaymentStudentData::getModeContract($id);
            if ($response['mode'] == 'V') {
                $credito = ProformaPaymentStudentData::getCreditoVariation($id);
                if ($credito != null) {
                    $response['onlyCreditNote'] = floatval($credito) < 0;
                } else {
                    $response["code"] = 422;
                    $response['success'] = false;
                    $response['message'] = 'Esta en proceso de variacion y necesita creditos variados';
                    $response['onlyCreditNote'] = false;
                    return response()->json($response, $response["code"]);
                }

            } else {
                $response['onlyCreditNote'] = false;
            }

        }
        return response()->json($response, $response["code"]);

    }

    public function update(Request $request, $id){

        $response = GlobalMethods::authorizationLamb($request);
        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProformaPaymentStudentData::updateDiscounts($id, $request);
        }
        return response()->json($response, $response["code"]);
    }


    public function store(Request $request){

        $response = GlobalMethods::authorizationLamb($request);
        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $tipcam['tc'] = false;
            $tipoCambio = null;

            if ($request->id_moneda === '9') {
                $tipcam = GlobalMethods::verificaTipoCambio();
                $tipoCambio = $tipcam['denominacional'];
                $response['CAMBIO'] = $tipcam;
            } else {
                $tipcam['tc'] = true;
            }

            if ($tipcam['tc']) {
                $data = ProformaPaymentStudentData::generateContratc($request, $response, $tipoCambio);
                $response = array_merge($response, $data);

            } else {
                $response["code"] = 202;
                $response['success'] = false;
                $response['message'] = 'Alto, actualice el tipo de cambio';
            }

        }
        return response()->json($response, $response["code"]);
    }


    public function validationProforma(Request $request){

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProformaPaymentValidatorData::validator($request, $response);
        }
        return response()->json($response, $response["code"]);

    }


    public function cleanLegalClient(Request $request){

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            ProformaPaymentStudentData::cleanLegalClient($request->id_alumno_contrato);

        }
        return response()->json($response, $response["code"]);

    }


    public function ticket(Request $request){

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $id_comprobante = $request->id_comprobante;
            $id_venta = $request->id_venta;
            $response['data'] = ProformaPaymentStudentData::generateTicket($id_comprobante, $id_venta, $response);

        }
        return response()->json($response, $response["code"]);

    }

    public function changeMissionary(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            ProformaPaymentStudentData::changeMissionary($request->id_alumno_contrato, $request->misionero);

        }
        return response()->json($response, $response["code"]);

    }


    public function imprimeTicketContrato(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProformaPaymentStudentData::getImprimeTicketContrato($request, $response);

        }
        return response()->json($response, $response["code"]);

    }


    public function finishContract(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);

        $id_alumno_contrato = $request->id_alumno_contrato;

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $data = ProformaPaymentStudentData::finishContract($id_alumno_contrato, $response);
            $response = array_merge($response, $data);
        }
        return response()->json($response, $response["code"]);

    }


    public function finishContractVariation(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);

        $id_alumno_contrato = $request->id_alumno_contrato; // id_alumno_contrato clon

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $data = ProformaPaymentStudentData::finishContractVariation($id_alumno_contrato, $response['id_user'], $response);
            $response = array_merge($response, $data);
        }
        return response()->json($response, $response["code"]);

    }






}
