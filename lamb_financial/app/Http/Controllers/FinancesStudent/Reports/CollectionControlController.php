<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 2/06/21
 * Time: 18:29
 */

namespace App\Http\Controllers\FinancesStudent\Reports;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\reports\CollectionControlData;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Utils\Aggregates;
use Illuminate\Http\Request;

class CollectionControlController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getCollectionControl()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        $this->validate($this->request, [
            'id_anho' => 'required',
            'id_mes' => 'required',
            'meta' => 'required',
        ]);
        $params = $this->request->all();
        if ($response["valida"] == 'SI') {
            $currentYear = intval($params['id_anho']);
            $mainParams = [
                'id_entidad' => $response['id_entidad'],
                'id_depto' => $response['id_depto'],
                'currentYear' => $currentYear,
                'lastYear' => $currentYear - 1,
                'currentProperty' => 'current_year',
                'lastProperty' => 'last_year',
            ];
            $fac = CollectionControlData::getCollectionControlData(array_merge([], $params, $mainParams));

            $area = CollectionControlData::getCollectionControlDepart(array_merge([], $params, $mainParams));
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'Genial';
            $response['data'] = [
                'facultad' => [
                    'list' => $fac,
                    'sum' => Aggregates::getSumListObject([
                        $mainParams['currentProperty'],
                        $mainParams['lastProperty'],
                        'meta',
                        'ga',
                    ], $fac->toArray())
                ],
                'area' => [
                    'list' => $area,
                    'sum' => Aggregates::getSumListObject([
                        $mainParams['currentProperty'],
                        $mainParams['lastProperty'],
                        'meta',
                        'ga',
                        'porcentaje'
                    ], $area->toArray())
                ],
            ];
        }

        return response()->json($response, $response["code"]);
    }


}