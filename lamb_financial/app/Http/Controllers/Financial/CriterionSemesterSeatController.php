<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21/01/20
 * Time: 11:32 AM
 */


namespace App\Http\Controllers\Financial;

use App\Http\Data\Financial\CriterionSemesterSeatData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

class CriterionSemesterSeatController
{
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function index(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterSeatData::getIndex($this->request);
        }

        return response()->json($response, $response["code"]);
    }


    public function store() {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $this->request['id_entidad'] = $response['id_entidad'];

            $validator = CriterionSemesterSeatData::validators($this->request->all());

            if (!$validator->fails()) {
                $response['data'] = CriterionSemesterSeatData::getStore($this->request);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);

    }


    public function destroy($id) {


        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterSeatData::getDestroy($id);
        }

        return response()->json($response, $response["code"]);


    }


    public function show($id) {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterSeatData::getShow($id);
        }

        return response()->json($response, $response["code"]);


    }

    public function update($id) {

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterSeatData::getUpdate($id, $this->request);
        }

        return response()->json($response, $response["code"]);


    }
    public function deleteseat($id,$id_criterio_semestre){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterSeatData::deleteseat($id,$id_criterio_semestre);
        }

        return response()->json($response, $response["code"]);
    }






}