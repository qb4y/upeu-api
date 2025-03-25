<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 29/01/20
 * Time: 07:14 PM
 */

namespace App\Http\Controllers\Financial;



use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriterionSemesterCostController extends Controller
{
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function detaile(Request $request) {

        $response = GlobalMethods::authorizationLamb($this->request);

        $id_alumno_contrato = $request->id_alumno_contrato;


        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';

            $data = DB::select(
                "SELECT (b.NOMBRE|| ': ' || a.descripcion) NOMBRE, a.IMPORTE, a.DC, b.TIENE_HIJO, a.IMP_CAL  FROM ELISEO.MAT_ALUMNO_CONTRATO_DET a
                    JOIN ELISEO.VW_MAT_CRITERIO_SEMESTRE b ON (a.ID_CRITERIO_SEMESTRE=b.ID_CRITERIO_SEMESTRE)
                    where a.ID_ALUMNO_CONTRATO=$id_alumno_contrato"
            );

            $response['data'] =  collect($data)->groupBy('dc');
        }

        return response()->json($response, $response["code"]);


    }
}