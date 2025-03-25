<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 08/01/20
 * Time: 02:57 PM
 */



namespace App\Http\Controllers\Financial;
use App\Http\Data\Financial\CriterionData;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;



class CriterionController extends Controller {

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function index(){

        $response = GlobalMethods::authorizationLamb($this->request);

        $id_nivel_ensenanza = $this->request->id_nivel_ensenanza;
        $nombre = $this->request->nombre;

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::index($id_nivel_ensenanza, $nombre);
        }

        return response()->json($response, $response["code"]);
    }

    public function store(Request $request) {

        $response = GlobalMethods::authorizationLamb($this->request);

        $dataPost = $request->all();

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::add($dataPost);
        }

        return response()->json($response, $response["code"]);

    }

    public function show($id_criterio) {

        $response = GlobalMethods::authorizationLamb($this->request);
        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::show($id_criterio);
        }

        return response()->json($response, $response["code"]);

    }


    public function update(Request $request, $id)
    {
        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::update($request->all(), $id);
        }

        return response()->json($response, $response["code"]);


    }



    public function listOpt(){

        $response = GlobalMethods::authorizationLamb($this->request);

        $id_nivel_ensenanza = $this->request->id_nivel_ensenanza;
        $params = $this->request;

        

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            //$response['data'] = ['data'=>CriterionData::criterionListOption($params),'crit'=>CriterionData::criterionListPend($params);
            if(empty($params->id_semestre_programa)){
                $d= [];
            }else{
                $d= CriterionData::criterionListPend($params);
            }
            $data_mode= CriterionData::getListContractMode();
            $response['data'] = ['data'=>CriterionData::criterionListOption($params),'crit'=>$d,'data_mod'=>$data_mode];
            
        }



        return response()->json($response, $response["code"]);
    }
    public function criterionLinenalHerarchy(){

        $response = GlobalMethods::authorizationLamb($this->request);

        $params = $this->request;
        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK00000';
            $response['data'] = CriterionData::criterionLinenalHerarchy($params);
        }



        return response()->json($response, $response["code"]);
    }
    public function listOptCriterieSemester(){

        $response = GlobalMethods::authorizationLamb($this->request);

        $id_nivel_ensenanza = $this->request->id_nivel_ensenanza;
        $id_semestre_programa = $this->request->id_semestre_programa;

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::criterionListCriterieSemestre($id_nivel_ensenanza, $id_semestre_programa);
        }



        return response()->json($response, $response["code"]);
    }
    public function getListCriteriaAfecta(){

        $response = GlobalMethods::authorizationLamb($this->request);

        $params = $this->request;

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionData::getListCriteriaAfecta($params);
        }



        return response()->json($response, $response["code"]);
    }

    public  function getListCriteriaSemester(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_semestre = $this->request->query('id_semestre');

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = CriterionData::getListCriteriaSemester($id_semestre);
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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
    }

    public function getTypeDiscount(Request $request)
    {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";

       
            try {
                $estado= $request->estado;
                $data = CriterionData::getTypeDiscount($estado);
                $sem =  CriterionData::semester();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['typedscto'=>$data,'semestre'=>$sem];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }




}