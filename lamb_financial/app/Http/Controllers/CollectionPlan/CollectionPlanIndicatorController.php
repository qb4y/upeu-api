<?php


namespace App\Http\Controllers\CollectionPlan;


use App\Models\CollectionPlan;
use App\Http\Data\GlobalMethods;
use App\Models\CollectionPlanIndicators;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;

class CollectionPlanIndicatorController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response = array_merge($response, CollectionPlan::with(['semester'])->paginate(4)->toArray());
        }
        return response()->json($response, $response["code"]);
    }

    public function show($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            //$coll = CollectionPlan::find($id);
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CollectionPlanIndicators::show($id);
        }
        return response()->json($response, $response["code"]);
    }

    public function store()
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $validator = self::validatorsCollectionPlanIndicators($this->request->all());
            //dd($this->request);
            $resp = $this->request->all();
            if (!$validator->fails()) {
                $response['data'] = CollectionPlanIndicators::insert($resp);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);

    }

    public function update($id)
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = DB::table('CAJA_PLANCOBRO_SEMESTRE_DET')->where('ID_PLANCOBRO_SEMESTRE_DET', $id)
                ->update($this->request->all());
        }
        return response()->json($response, $response["code"]);
    }

    public function destroy($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CollectionPlanIndicators::where('ID_PLANCOBRO_SEMESTRE_DET', $id)->delete();;
        }
        return response()->json($response, $response["code"]);
    }

    public static function validatorsCollectionPlanIndicators($data)
    {

        $messages = [
            'id_plancobro_semestre.required' => 'Debe elegir un Plan de cobros.',
            'min_val.required' => 'Debe seleccionar un Valor Mínimo.',
            'max_val.required' => 'Debe seleccionar un Valor Máximo.',
        ];

        return Validator::make($data, [
            'id_plancobro_semestre' => 'required',
            'min_val' => 'required',
            'max_val' => 'required',
        ], $messages);

    }

}