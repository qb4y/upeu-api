<?php


namespace App\Http\Controllers\CollectionPlan;


use App\Http\Data\FinancesStudent\ComunData;
use App\Models\ClientFinancier;
use App\Http\Data\GlobalMethods;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class ClientFinancierController
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
        $response = array();
        //$idDepto = $global['id_depto'];
        $idEntidad = $global['id_entidad'];
        $idDepto = $this->request->query('id_depto');
        $idSemeter = $this->request->query('id_semestre');
        $idFinancista = $this->request->query('id_financista');
        $pageSize = $this->request->query('page_size');
        $this->request->query('id_financista');
        if ($valid) {
            $data = ClientFinancier::where('id_entidad', $idEntidad);
            if ($idDepto) {
                $data = $data->where('id_depto', $idDepto);
            }
            if ($idSemeter) {
                $data = $data->where('id_semestre', $idSemeter);
            }
            if ($idFinancista) {
                $data = $data->where('id_financista', $idFinancista);
            }
            if($pageSize){
                $data = $data->paginate($pageSize);
                $response = array_merge($response, $data->toArray());
            }else {
                $response['data'] = $data->get();
            }

            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
        }
        return response()->json($response, $response["code"]);
    }

    public function show($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = ClientFinancier::find($id);
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
            $now = Carbon::now();
            $mainParams = [
                // 'id_asignacion' => $id_asignacion = ComunData::correlativo('eliseo.FIN_ASIGNACION', 'id_asignacion'),
                'id_entidad' => $response['id_entidad'],
                'id_anho' => $now->year,
                'estado' => 1,
                'fecha_create' => $now,
                'id_user' => $response['id_user'],
            ];


            $validator = self::validatorsFinancier($this->request->all());
            $resp = $this->request->all();
            if (!$validator->fails()) {
                $customers = $resp['cliente'];
                unset($resp['cliente']);
                $added = [];
                foreach ($customers as $item) {
                    if($item['id_asignacion']) {
                        $edit = \DB::table('FIN_ASIGNACION')->where('id_asignacion', $item['id_asignacion'])
                            ->update(array_merge($resp, $mainParams, ['id_cliente' => $item['id_cliente']]));
                        $added[] = array('id_asignacion' => $item['id_asignacion'], 'success' => $edit == 1, 'id_cliente'=> $item['id_cliente']);
                    }else {
                        $nId = ComunData::correlativo('eliseo.FIN_ASIGNACION', 'id_asignacion');
                        $add = ClientFinancier::insert(array_merge($resp, $mainParams, ['id_asignacion' => $nId, 'id_cliente' => $item['id_cliente']]));
                        $added[] = array('id_asignacion' => $nId, 'success' => $add, 'id_cliente'=> $item['id_cliente']);
                    }
                }

                $response['data'] = $added;
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);

    }

    public static function validatorsFinancier($data)
    {

        $messages = [
            'id_plancobro_semestre.required' => 'Debe elegir un Plan de cobros.',
            'id_depto.required' => 'Debe elegir un Plan de cobros.',
            'id_semestre.required' => 'Debe seleccionar un Valor Mínimo.',
            'id_financista.required' => 'Debe seleccionar un Valor Máximo.',
            'cliente.required' => 'Debe seleccionar un Valor Máximo.',
        ];

        return Validator::make($data, [
            'id_plancobro_semestre' => 'required',
            'id_depto' => 'required',
            'id_semestre' => 'required',
            'id_financista' => 'required',
            'cliente' => 'required',
        ], $messages);

    }

    public function checkCustomer()
    {
        $response = GlobalMethods::authorizationLamb($this->request);

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $content = $this->request->all();
            $validator = self::validatorsCheckCustomer($content);
            $resp = $this->request->all();
            $identificators = explode(",", $resp['customer']);
            if (!$validator->fails()) {
                $data = [];

                $response['data'] = array_map(function ($item) {
                    return self::getClientSummary($item);
                }, $identificators);
            } else {
                $response['success'] = false;
                $response['message'] = 'Campos requeridos';
                $response['data'] = $validator->errors();
            }
        }

        return response()->json($response, $response["code"]);
    }

    public static function validatorsCheckCustomer($data)
    {

        $messages = [
            'customer.required' => 'La entrada customer es requerido.'
        ];

        return Validator::make($data, [
            'customer' => 'required'
        ], $messages);

    }

    public static function getClientSummary($id)
    {
        $data = collect(\DB::select("select PERSONA.ID_PERSONA as                    id_cliente,
       PERSONA_NATURAL_ALUMNO.CODIGO,
       persona.NOMBRE || ' ' || PERSONA.PATERNO cliente,
       S.SALDO,
       (CASE
            WHEN (
                     select FIN_PLANCOBRO_SEMESTRE_DET.NOMBRE
                     from FIN_PLANCOBRO_SEMESTRE_DET
                              join FIN_PLANCOBRO_SEMESTRE
                                   on FIN_PLANCOBRO_SEMESTRE_DET.ID_PLANCOBRO_SEMESTRE =
                                      FIN_PLANCOBRO_SEMESTRE.ID_PLANCOBRO_SEMESTRE
                     where FIN_PLANCOBRO_SEMESTRE.ID_PLANCOBRO_SEMESTRE = 1
                       and S.saldo between FIN_PLANCOBRO_SEMESTRE_DET.MIN_VAL and FIN_PLANCOBRO_SEMESTRE_DET.Max_VAL
                 ) is not null THEN (
                select FIN_PLANCOBRO_SEMESTRE_DET.NOMBRE || ',' || FIN_PLANCOBRO_SEMESTRE_DET.COLOR
                from FIN_PLANCOBRO_SEMESTRE_DET
                         join FIN_PLANCOBRO_SEMESTRE
                              on FIN_PLANCOBRO_SEMESTRE_DET.ID_PLANCOBRO_SEMESTRE =
                                 FIN_PLANCOBRO_SEMESTRE.ID_PLANCOBRO_SEMESTRE
                where FIN_PLANCOBRO_SEMESTRE.ID_PLANCOBRO_SEMESTRE = 1
                  and S.saldo between FIN_PLANCOBRO_SEMESTRE_DET.MIN_VAL and FIN_PLANCOBRO_SEMESTRE_DET.Max_VAL
            )
            ELSE '' || S.ID_CLIENTE END)        parametro_deuda
from moises.Persona_Natural_Alumno
         join moises.PERSONA on PERSONA_NATURAL_ALUMNO.ID_PERSONA = PERSONA.ID_PERSONA
         left join (select A.ID_CLIENTE,
                           SUM(A.IMPORTE) SALDO
                    from (
                             SELECT ID_CLIENTE,
                                    TOTAL AS IMPORTE
                             FROM ELISEO.VW_SALES_MOV
                             where ID_TIPOVENTA IN (1, 2, 3, 4)
                                   --and ID_CLIENTE = persona.id
                             union all
                             SELECT ID_CLIENTE,
                                    SUM(IMPORTE) * DECODE(SIGN(SUM(IMPORTE)), 1, -1, 0) AS IMPORTE
                             FROM ELISEO.VW_SALES_ADVANCES
                                  --where ID_CLIENTE = persona.id
                             GROUP BY ID_CLIENTE
                         ) A
                    GROUP BY ID_CLIENTE
                    HAVING SUM(IMPORTE) > 0) S on S.ID_CLIENTE = persona.ID_PERSONA
where PERSONA_NATURAL_ALUMNO.CODIGO = ?", [$id]))->first();
        $valid = isset($data->codigo) and $data->codigo == $id;
        $saldoDetail = array(
            'label' => ($valid and $data->parametro_deuda) ? explode(',', $data->parametro_deuda)[0] : 'N/A',
            'color' => ($valid and $data->parametro_deuda) ? explode(',', $data->parametro_deuda)[1] : '#5e5e61');
        return collect($data)->merge([
            'identificador' => $id,
            'valid' => $valid,
            'saldo_detail' => $saldoDetail
        ]);
    }

}