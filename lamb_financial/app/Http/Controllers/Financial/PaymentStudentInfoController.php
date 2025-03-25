<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 03/02/20
 * Time: 12:02 PM
 */



namespace App\Http\Controllers\Financial;
use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\StudentData;
use App\Http\Data\Financial\PaymentStudentInfoData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class PaymentStudentInfoController extends Controller {

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function show($id) {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = PaymentStudentInfoData::show($id, $this->request->id_anho, $this->request->isFinantialApp);
        }

        return response()->json($response, $response["code"]);

    }


    public function update($id) {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $data = PaymentStudentInfoData::update($id, $this->request);
            $isFinantialApp = $this->request->isFinantialApp;

            if ($data['nerror'] == 0) {

                $response['success'] = true;
                $response['message'] = 'calculo de costo correcto';
                $response['data'] = PaymentStudentInfoData::show($id, $this->request->id_anho, $isFinantialApp);

            } else {
                $response["code"] = 422;
                $response['success'] = false;
                $response['message'] = $data['msgerror'];
                $response['data'] = $data;

            }


        }

        return response()->json($response, $response["code"]);

    }

    // api de prueba de descuentos
    public function prueba() {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = StudentData::pagosDC(297195, null);

        }

        return response()->json($response, $response["code"]);

    }


}