<?php


namespace App\Http\Controllers\Financial;


use App\Http\Controllers\Controller;
use App\Http\Data\Financial\ContractEnrollmentData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class ContractEnrollmentController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function generateContract(Request $request, $idContract)
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $jResponse = [];
            $msn_error = "";
            try {
                $error = 0;
                $params = array(
                    'id_alumno_contrato' => $idContract,
                    'id_user' => $response['id_user']
                );
                $data = ContractEnrollmentData::generateContract($params, $response);
                $error = $data['nerror'];
                $msn_error = $data['msgerror'];
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['id_venta' => $data['id_venta']];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
}
