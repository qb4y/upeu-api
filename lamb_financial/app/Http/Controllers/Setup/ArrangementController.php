<?php


namespace App\Http\Controllers\Setup;

// use App\Http\Data\Accounting\Setup\ArrangementsData;
use App\Http\Data\Financial\CriterionData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArrangementController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getArrangementEntries()
    {

        $response = GlobalMethods::authorizationLamb($this->request);

//        $id_nivel_ensenanza = $this->request->id_nivel_ensenanza;
//        $nombre = $this->request->nombre;
        $params = array();

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = DB::table('ARREGLO_ENTRADA')->get();
        }

        return response()->json($response, $response["code"]);
    }

    public function getArrangement()
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        $idOrigin = $this->request->input('id_origen');
//        dd('caras', $idOrigin);


        if ($response["valida"] == 'SI') {
            $data = DB::table('ARREGLO');
            if ($idOrigin) {
                $data = $data->where('ID_ORIGEN', $idOrigin);
            }
            $data = $data->get();
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = $data;
        }

        return response()->json($response, $response["code"]);
    }

    public function getArrangementDetails($idArraigment)
    {

        $response = GlobalMethods::authorizationLamb($this->request);


        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = DB::table('ARREGLO_DETALLE')->where('ARREGLO_DETALLE.ID_ARREGLO', $idArraigment)
                ->select(
                    'ARREGLO_DETALLE.ID_DARREGLO',
                    'ARREGLO_DETALLE.ID_REFERENCIA',
                    'ARREGLO_DETALLE.DICE',
                    'ARREGLO_DETALLE.DEBE_DECIR',
                    'ARREGLO_ENTRADA.ETIQUETA',
                    'ARREGLO_ENTRADA.IDENTIFICADOR',
                    'CONTA_ASIENTO.IMPORTE',
                    'CONTA_ASIENTO.DESCRIPCION'
                    )
                ->join('ARREGLO_ENTRADA', 'ARREGLO_DETALLE.ID_ARREGLO_ENTRADA', '=', 'ARREGLO_ENTRADA.ID_ARREGLO_ENTRADA')
                ->leftjoin('CONTA_ASIENTO', 'CONTA_ASIENTO.ID_ASIENTO', '=', 'ARREGLO_DETALLE.ID_REFERENCIA')
                ->get();
        }

        return response()->json($response, $response["code"]);
    }
}