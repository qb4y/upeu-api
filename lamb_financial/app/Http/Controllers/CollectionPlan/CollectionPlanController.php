<?php


namespace App\Http\Controllers\CollectionPlan;


use App\Models\CollectionPlan;
use App\Http\Data\GlobalMethods;
use App\Models\CollectionPlanIndicators;
use Illuminate\Http\Request;
use Validator;

class CollectionPlanController
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
            $response = array_merge($response, CollectionPlan::with(['semester'])
                ->where('id_entidad', $global["id_entidad"])
                ->where('id_depto', $global["id_depto"])
                ->orderBy('updated_at', 'desc')
                ->paginate(100)
                ->toArray());
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
            $response['data'] = CollectionPlan::with(['collectionPlanDetails', 'semester'])->find($id);
        }
        return response()->json($response, $response["code"]);
    }

    public function createOrGetCollectionPlan()
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $mainParams = [
                'id_entidad' => $response['id_entidad'],
            ];
            $req = $this->request->all();
            $params = array_merge($req, [
                'id_entidad' => $response['id_entidad'],
                'id_depto' => $response['id_depto'],
                'estado' => '1',
            ]);

            $validator = self::validatorsCollectionPlan($params);
            // $resp = $this->request->all();
            if (!$validator->fails()) {
               // $params = array_merge($resp, $mainParams);
                $instance = CollectionPlan::where('id_semestre', $params['id_semestre'])
                    ->where('id_depto', $params['id_depto'])
                    ->first();
                if (!$instance) {
                    //$nInstance = CollectionPlan::insert($params);
                    // $nInstance = new CollectionPlan;
                    $nInstance = CollectionPlan::insert(
                        array('id_semestre' => $params['id_semestre'],
                            'id_depto' => $params['id_depto'],
                            'id_entidad' => $params['id_entidad'])
                    );
                    // dd($nInstance);
                    $instance = CollectionPlan::where('id_semestre', $params['id_semestre'])
                        ->where('id_depto', $params['id_depto'])
                        ->first();
                    if ($nInstance) {
                        $response['success'] = true;
                    }
                }

                $response['data'] = $instance;
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
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
            $req = $this->request->all();
            $params = array_merge($req, [
                'id_entidad' => $response['id_entidad'],
                'id_depto' => $response['id_depto'],
                'estado' => '1',
            ]);
            $validator = self::validatorsCollectionPlan($params);
            if (!$validator->fails()) {
                $semesterAlreadyExist = CollectionPlan::where(function ($q) use ($params) {
                        foreach ($params as $key => $value) {
                            if ($key != 'estado')
                                $q->where($key, $value);
                        }
                    })->count() > 0;
                if (!$semesterAlreadyExist) {
                    $nInstance = CollectionPlan::create($params);
                    $response['data'] = CollectionPlan::find($nInstance->ID_PLANCOBRO_SEMESTRE);
                } else {
                    $response['data'] = null;
                    $response['success'] = false;
                    $response['message'] = 'el semestre ya se encuentra asignado';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }
        return response()->json($response, $response["code"]);
    }

    public static function validatorsCollectionPlan($data)
    {

        $messages = [
            'id_semestre.required' => 'Debe elegir un Plan de cobros.',
            'id_depto.required' => 'Debe elegir un Plan de cobros.',
            'id_entidad.required' => 'Debe elegir un Plan de cobros.',
            'estado.required' => 'Debe elegir un Plan de cobros.',
        ];

        return Validator::make($data, [
            'id_semestre' => 'required',
            'id_depto' => 'required',
            'id_entidad' => 'required',
            'estado' => 'required',
        ], $messages);

    }

    public function update($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $params = $req = $this->request->all();
            $instance = CollectionPlan::where('id_plancobro_semestre', $id)
                ->update($params);
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CollectionPlan::find($id);
        }
        return response()->json($response, $response["code"]);
    }

}