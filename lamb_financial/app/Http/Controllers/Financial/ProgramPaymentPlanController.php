<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 13/01/20
 * Time: 09:20 AM
 */

namespace App\Http\Controllers\Financial;

use App\Http\Data\Academic\ProgramData;
use Illuminate\Http\Request;
use App\Http\Data\Financial\ProgramPaymentPlanData;
use App\Http\Data\GlobalMethods;

class ProgramPaymentPlanController
{

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }


    public function index(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::index($this->request);
        }

        return response()->json($response, $response["code"]);
    }


    public function destroy($id) {


        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::destroy($id);
        }

        return response()->json($response, $response["code"]);


    }


    public function listPaymentPlan(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::paymentPlanConfig();
        }
        return response()->json($response, $response["code"]);

    }


    public function store(Request $request) {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = ProgramPaymentPlanData::validators($this->request->all());

            if (!$validator->fails()) {
                $response['data'] = ProgramPaymentPlanData::create($this->request);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);

    }


    public function show($id) {


        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::show($id);
        }

        return response()->json($response, $response["code"]);


    }




    public function paymentPlanDetails() {


        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::getPaymentPlanDetails($this->request);
        }

        return response()->json($response, $response["code"]);


    }


    public function options(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $data = ProgramData::requiredOptions();
            $data['paymentPlan'] = ProgramPaymentPlanData::optionsPaymentPlan();
            $response['data'] = $data;
        }

        return response()->json($response, $response["code"]);

    }


    public function detailsPaymentPlan($id_planpago_semestre){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramPaymentPlanData::detailsPaymentPlan($id_planpago_semestre);
        }

        return response()->json($response, $response["code"]);

    }
    public function updateStatusMatPlanPagoSemestre(Request $request, $id_planpago_semestre) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $result = ProgramPaymentPlanData::updateStatusMatPlanPagoSemestre($request, $id_planpago_semestre);
                if ($result['success']) {
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['success'];
                    $code = "200";
                } else {
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

}