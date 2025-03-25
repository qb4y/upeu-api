<?php


namespace App\Http\Controllers\Report\FinancesStudent;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Report\FinantianStudentData;
use Illuminate\Http\Request;

class ReportsController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function paymentTrackingSummary()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {


            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = FinantianStudentData::getPaymenTrackingSummary($this->request);
        }
        return response()->json($response, $response["code"]);
    }

    public function paymentTracking()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = FinantianStudentData::getPaymenTracking($this->request);
        }
        return response()->json($response, $response["code"]);
    }

}