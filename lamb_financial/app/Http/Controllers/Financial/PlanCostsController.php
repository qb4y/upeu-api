<?php


namespace App\Http\Controllers\Financial;


use App\Http\Controllers\Controller;
use App\Http\Data\Financial\PlanCostsData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class PlanCostsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function planCostMain()
    {


        $response = GlobalMethods::authorizationLamb($this->request);

        $params = $this->request;

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = PlanCostsData::index($params);
        }

        return response()->json($response, $response["code"]);
    }

    public function addPlanCosts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $body = json_decode(file_get_contents("php://input"));
                $params = [];
                foreach ($body as $b) {
                    array_push($params, (array)$b);
                }

                
                $data = PlanCostsData::addPlanCost($params);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
                $code = "200";


            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updatePlanCosts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $body = json_decode(file_get_contents("php://input"));
                $params = [];
                $paramsId = [];

                foreach ($body as $b) {
                    $item = array(
                        'importe' => $b->importe
                    );
                    array_push($params, (array)$item);
                    array_push($paramsId, $b->id_plan_costo);
                }
                $data = PlanCostsData::updatePlanCost($body);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
                $code = "200";


            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function massivelyRegisterPlanCosts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $body = json_decode(file_get_contents("php://input"));
                $params = [];
                $paramsId = [];

                foreach ($body as $b) {
                    $item = array(
                        'importe' => $b->importe
                    );
                    array_push($params, (array)$item);
                    array_push($paramsId, $b->id_plan_costo);
                }
                $data = PlanCostsData::massivelyRegisterPlanCosts($body);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data;
                $code = "200";


            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    /*public  function planCostMains(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        dd('gettting', $valida);

        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_area = $this->request->id_nivel_ensenanza;
                $id_semestre = $this->request->nombre;
                $data = PlanCostsData::index($nombre);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }*/

}