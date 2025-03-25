<?php


namespace App\Http\Controllers\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\FinancesStudent\SalesData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function createSale(Request $request)
    {

        $response = GlobalMethods::authorizationLamb($this->request);
//        dd($response);
        $dataPost = $request->all();
        $dataPost['id_entidad'] = $response['id_entidad'];
        $dataPost['id_depto'] = $response['id_depto'];
        $dataPost['id_persona'] = $response['id_user'];
//        dd('getting', $dataPost, $response);

        if ($response["valida"] == 'SI') {
            $anho = collect(AccountingData::showPeriodoActivo($response['id_entidad']))->first();
            $mes = collect(AccountingData::showMesActivo($response['id_entidad'], $anho->id_anho))->first();
            $dataPost['id_anho'] = $anho->id_anho;
            $dataPost['id_mes'] = $mes->id_mes;
//            dd($mes);
//            $data_mes =
//            dd($dataPost);
            $data = SalesData::add((object)$dataPost);
            $response = self::generateProcedureResponse($data);
        }

        return response()->json($response, $response["code"]);

    }

    public static function generateProcedureResponse($data)
    {
//        dd($data);
        $response = array(
            'code' => 500,
            'success' => false,
            'data' => null,
            'message' => ''
        );
        if ($data and $data['nerror'] == 0) {
            $response['success'] = true;
            $response['code'] = 200;
            $response['message'] = $data['msgerror'];
            $response['data'] = $data['id'];
         
        } else {
            $response['message'] = $data['msgerror'];
        }

        return $response;
    }
    public function saveUpdateDireccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = SalesData::saveUpdateDireccion($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];          
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
}