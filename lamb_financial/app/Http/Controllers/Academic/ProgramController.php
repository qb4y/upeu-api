<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 13/01/20
 * Time: 10:11 AM
 */

namespace App\Http\Controllers\Academic;
use App\Http\Data\Academic\ProgramData;
use App\Http\Data\GlobalMethods;
use Validator;
use Illuminate\Http\Request;


class ProgramController
{

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }


    public static function index(Request $request){

        $response = GlobalMethods::authorizationLamb($request);

        if($response["valida"]=='SI'){

            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($request->all(), [
                'id_sede' => 'required',
                'id_semestre' => 'required',
                'id_nivel_ensenanza' => 'required'
            ]);

            if (!$validator->fails()) {
                $response['data'] = ProgramData::programs($request);
            } else {
                $response['success'] = false;
                $response['message'] = 'Parametros requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);


    }

    public function listCampus(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::campus();
        }
        return response()->json($response, $response["code"]);

    }

    public function listSemester(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::semester();
        }
        return response()->json($response, $response["code"]);

    }

    public function listTeachingLevel(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::teachingLevel();
        }
        return response()->json($response, $response["code"]);
    }
    public function studyModality(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::studyModality();
        }
        return response()->json($response, $response["code"]);
    }
    public function contractMode(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::contractMode();
        }
        return response()->json($response, $response["code"]);
    }
    public function listRequstudentshipType(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::listRequstudentshipType();
        }
        return response()->json($response, $response["code"]);
    }

    public function listProgramsCriteriesSemester(Request $request){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ProgramData::listProgramsCriteriesSemester($request);
        }
        return response()->json($response, $response["code"]);
    }
    

    public function programsSemesterTree(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($this->request->all(), [
                'id_sede' => 'required',
                'id_semestre' => 'required',
                'id_nivel_ensenanza' => 'required'
            ]);

            if (!$validator->fails()) {
                $data = ProgramData::programs($this->request)->groupBy('nombre_facultad');
                $response['data'] = ProgramData::programsSemesterTree($data);
            } else {
                $response['success'] = false;
                $response['message'] = 'Parametros requeridos';
                $response['data'] = $validator->errors();
            }
        }
        return response()->json($response, $response["code"]);
    }
    public function programsSemesterContractTree(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = Validator::make($this->request->all(), [
                'id_sede' => 'required',
                'id_semestre' => 'required',
                'id_nivel_ensenanza' => 'required',
                'id_tipo_contrato' => 'required',
            ]);

            if (!$validator->fails()) {
                $data = ProgramData::programsContract($this->request)->groupBy('nombre_facultad');
                $response['data'] = ProgramData::programsSemesterTree($data);
            } else {
                $response['success'] = false;
                $response['message'] = 'Parametros requeridos';
                $response['data'] = $validator->errors();
            }
        }
        return response()->json($response, $response["code"]);
    }
    public function listTypeContract(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $validator = Validator::make($this->request->all(), [
                'id_nivel_ensenanza' => 'required'
            ]);

            if (!$validator->fails()) {
                $response['data'] = ProgramData::typeContract($this->request);
            } else {
                $response['success'] = false;
                $response['message'] = 'Parametros requeridos';
                $response['data'] = $validator->errors();
            }
        }
        return response()->json($response, $response["code"]);
    }


    public function listPlanPlagoSemestre(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            // dd($this->request->all());
            $validator = Validator::make($this->request->all(), [
                'id_semestre_programa' => 'required',
                'plan_pago' => 'required',
            ]);

            if (!$validator->fails()) {
                $response['data'] = ProgramData::listPlanPlagoSemestre($this->request->all());
            } else {
                $response['success'] = false;
                $response['message'] = 'Parametros requeridos';
                $response['data'] = $validator->errors();
            }
        }
        return response()->json($response, $response["code"]);
    }

    public function getPlanPagoCuota(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = ProgramData::getPlanPagoCuota($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getPlanPagoCuotaNew(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = ProgramData::getPlanPagoCuotaNew($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


}