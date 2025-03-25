<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 13/04/21
 * Time: 17:04
 */

namespace App\Http\Controllers\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\CollectionFeesData;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Utils\FilterUtil;
use App\Http\Requests\CollectionFeesRequest;
use Illuminate\Http\Request;

class CollectionFeesController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        $fields = ['id_semestre',
            'id_modo_contrato',
            'plan_pago',
            'id_nivel_ensenanza',
            'id_modalidad_estudio',
            'id_programa_estudio',
             'id_planpago',
            'id_sede'];
        $this->validate($this->request, FilterUtil::getFieldsLikeRequired($fields)); 
        if ($response["valida"] == 'SI') {
            $data = CollectionFeesData::getCollectionFees($this->request->all());
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = $data;
            $response['cobSum'] = round(array_reduce($data, function ($carry, $item) {
                return $carry + $item->mensual;
            }), 2);
        }
        return response()->json($response, $response["code"]);
    }

    public function store(CollectionFeesRequest $request)
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            // this  should be validate parameters
            $resp = array_merge([],$this->request->all(), ['id_entidad'=> $response['id_entidad'], 'id_depto'=> $response['id_depto'], 'id_persona'=> $response['id_user']]);
            $data = CollectionFeesData::insert($resp);
            if($data['nerror'] == 0) {
                $response["code"] = 200;
                $response['success'] = true;
                $response['message'] = $data['msgerror'];
                $response['data'] = $data;
            } else {
                $response["code"] = 203;
                $response['success'] = false;
                $response['message'] = $data['msgerror'];
                $response['data'] = $data;
                // dd($response);
            }


        }

        return response()->json($response, $response["code"]);

    }

}