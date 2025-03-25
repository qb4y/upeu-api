<?php


namespace App\Http\Controllers\FinancesStudent;


use App\Http\Controllers\Controller;
//use App\Http\Data\Financial\CriterionSemesterData;
use App\Http\Data\FinancesStudent\ReportData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getStudentBalance()
    {

        $response = GlobalMethods::authorizationLamb($this->request);
//        dd($response);
        $params = array(
            'id_mes' => $this->request->id_mes,
            'id_anho' => $this->request->id_anho,
            'id_depto' => $response['id_depto'],
            'id_entidad' => $response['id_entidad'],
            'id_nivel_ensenanza' => $this->request->id_nivel_ensenanza,
            'codigo' => $this->request->codigo
        );
        $params = array_filter($params);
//        dd($params);

        if ($response["valida"] == 'SI') {
//            dd('PASSSS');
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ReportData::getStudentBalance((object)$params);
        }

        return response()->json($response, $response["code"]);
    }


    public function getSummaryBalance()
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        $params = array(
            'id_mes' => $this->request->id_mes,
            'id_anho' => $this->request->id_anho,
            'id_depto' => $response['id_depto'],
            'id_entidad' => $response['id_entidad'],
            'id_nivel_ensenanza' => $this->request->id_nivel_ensenanza,
        );

        $params = (object)$params;

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data']['facultad'] = ReportData::getSummaryBalanceFaculty($params);
            $response['data']['resumen'] = ReportData::getSummaryBalance($params);

        }

        return response()->json($response, $response["code"]);
    }

}