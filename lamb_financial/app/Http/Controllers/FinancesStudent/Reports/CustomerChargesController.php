<?php


namespace App\Http\Controllers\FinancesStudent\Reports;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\reports\CustomerChargesData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class CustomerChargesController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getCustomerCharges()
    {

        $response = GlobalMethods::authorizationLamb($this->request);
//        dd($response);
        $params = array(
            'id_depto' => (isset($this->request->id_depto) && $this->request->id_depto) ? $this->request->id_depto : $response['id_depto'],
            'id_entidad' => $response['id_entidad'],
            'id_anho' => $this->request->id_anho,
            'id_mes' => $this->request->id_mes
        );
        $this->validate($this->request,[
            'id_mes' => 'required',
            //'fullname' => 'required'
        ]);
        $params = array_filter($params);
//        dd($params);

        if ($response["valida"] == 'SI') {
//            dd('PASSSS');
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CustomerChargesData::getCustomerCharges($params);
        }

        return response()->json($response, $response["code"]);
    }


}