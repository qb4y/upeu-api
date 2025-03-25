<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 26/02/20
 * Time: 03:57 PM
 */

namespace App\Http\Controllers\FinancialEnrollment;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancialEnrollment\ProformaPaymentStudentData;
use App\Http\Data\GlobalMethods;
use Validator;
use Illuminate\Http\Request;


class ProformaCriteriaSemProg extends Controller
{

    public function discounts(Request $request, $id) {

        $response = GlobalMethods::authorizationLamb($request);

        $id_semestre_programa = $request->id_semestre_programa;

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($request->all(), [
                'id_semestre_programa' => 'required'
            ]);

            if (!$validator->fails()) {
                $response['data'] = ProformaPaymentStudentData::getCriteriaSemProgDisc($id, $id_semestre_programa);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }



        }
        return response()->json($response, $response["code"]);

    }


    public function programsPlanStudent(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);

        $id_persona = $request->id_persona;

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($request->all(), [
                'id_persona' => 'required'
            ]);

            if (!$validator->fails()) {
                $response['success'] = true;
                $response['message'] = 'planes programa de alumno segun modo';
                $response['data'] = ProformaPaymentStudentData::getProgramPlanStudent($id_persona);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }



        }
        return response()->json($response, $response["code"]);

    }

    public function checkDiscountRequest(Request $request) {

        $response = GlobalMethods::authorizationLamb($request);
        $params = $request->all();

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($request->all(), [
                'id_persona' => 'required',
                'id_semestre' => 'required'
            ]);

            if (!$validator->fails()) {
                $response['success'] = true;
                $response['message'] = 'solicitudes de becas';
                $response['data'] = ProformaPaymentStudentData::getCheckDiscountRequest($params);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }



        }
        return response()->json($response, $response["code"]);

    }
}