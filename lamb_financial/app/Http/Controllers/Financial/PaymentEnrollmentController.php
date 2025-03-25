<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 31/01/20
 * Time: 10:03 AM
 */


namespace App\Http\Controllers\Financial;
use App\Http\Controllers\Controller;
use App\Http\Data\Financial\PaymentEnrollmentData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;



class PaymentEnrollmentController extends Controller {

    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }


    public function studentBalance() {

        $response = GlobalMethods::authorizationLamb($this->request);

        $response['id_anho'] = $this->request->id_anho;

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = PaymentEnrollmentData::studentBalance($response);
        }

        return response()->json($response, $response["code"]);


    }


}