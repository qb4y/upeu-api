<?php


namespace App\Http\Controllers\Report\FinancesStudent;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Report\FinantianStudentData;
use Illuminate\Http\Request;

class AmmountsController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function ammountsSummary()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {


            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = FinantianStudentData::ammountsSummary($this->request, $response);
        }
        return response()->json($response, $response["code"]);
    }
}