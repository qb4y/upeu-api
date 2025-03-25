<?php


namespace App\Http\Controllers\CollectionPlan;


use App\Models\ClientFinancier;
use App\Http\Data\GlobalMethods;
use App\Models\Financier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancierController
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
            $response = array_merge($response, Financier
                ::select('FIN_FINANCISTA.id_financista')
                ->selectRaw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO financista")
                ->join('MOISES.PERSONA', 'FIN_FINANCISTA.id_financista', '=', 'PERSONA.ID_PERSONA')
                ->paginate(5)->toArray());
        }
        return response()->json($response, $response["code"]);
    }

    public function financialAssigned()
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        $id_plancobro_semestre = $this->request->id_plancobro_semestre;
        if ($valid) {
            $data = DB::table('FIN_ASIGNACION')->select('FIN_FINANCISTA.id_financista')
                ->selectRaw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO financista,
                count(FIN_ASIGNACION.ID_CLIENTE) cant_asignado")
                ->join("FIN_FINANCISTA", "FIN_ASIGNACION.ID_FINANCISTA", "=", "FIN_FINANCISTA.ID_FINANCISTA")
                ->join('MOISES.PERSONA', 'FIN_FINANCISTA.id_financista', '=', 'PERSONA.ID_PERSONA');
            if (isset($id_plancobro_semestre) and $id_plancobro_semestre) {
                $data = $data->where('FIN_ASIGNACION.ID_PLANCOBRO_SEMESTRE', $id_plancobro_semestre);
            }
            $data = $data
                ->groupBy('FIN_FINANCISTA.id_financista')
                ->groupBy("PERSONA.NOMBRE")
                ->groupBy("PERSONA.PATERNO")
                ->groupBy("PERSONA.MATERNO")
                ->get();
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = $data;
        }
        return response()->json($response, $response["code"]);
    }

    public function getFinancier($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = Financier::find($id);
        }
        return response()->json($response, $response["code"]);
    }


}