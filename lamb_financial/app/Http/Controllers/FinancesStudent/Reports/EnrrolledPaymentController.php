<?php


namespace App\Http\Controllers\FinancesStudent\Reports;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\reports\EnrrolledPaymentData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class EnrrolledPaymentController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getEnrrolledPaymentPlan()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        $this->validate($this->request, [
            'id_semestre' => 'required',
            'id_nivel_ensenanza' => 'required',
            'id_modo_contrato' => 'required',
            'id_modalidad_estudio' => 'required',
            'id_sede' => 'required'
        ]);
        $params = $this->request->all();
        if ($response["valida"] == 'SI') {
            $resp = EnrrolledPaymentData::getEnrrolledPaymentPlan($params);
            $keys = count($resp) > 0 ? EnrrolledPaymentData::getColumnsWithValue($resp) : [];
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK  A LAS PRUEBAS ME REMITO!!!!!!';
            $response['prueba'] = [$keys,'lalaa'];
            $response['data'] = [
                'lista' => $resp,
                'keys' => $keys,
                'summary' => EnrrolledPaymentData::getSummary($keys, $resp)
            ];
        }

        return response()->json($response, $response["code"]);
    }


}