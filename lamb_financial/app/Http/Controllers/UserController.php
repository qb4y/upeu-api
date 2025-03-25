<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers;

use App\Http\Data\ReportData;
use App\LambUsuario;
use App\ORM\ContaAnho;
use App\ORM\LambModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function test()
    {
        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];

        //$url=route('alex.pepe');
        //return redirect()->route('alex.pepe');

        $results = LambUsuario::select('ID_PERSONA', 'LOGIN', 'CONTRASENHA')->GET();
        $count = count($results);

        if ($results) {

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['total_count' => $count, 'items' => $results->toArray()];
        }
        //dd($jResponse);

        return response()->json($jResponse);

    }

    public function app()
    {
        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];

        //$oModule=LambModulos::select('id_modulo','nombre','url','orden')->where('id_padre','2')->get();
        //$oModule= DB::select('SELECT ID_MODULO,NOMBRE,URL,ORDEN FROM LAMB_MODULO WHERE ID_PADRE=2');
        $oModule = DB::table('LAMB_MODULO')->select('ID_MODULO', 'NOMBRE', 'URL', 'ORDEN')->where('ID_PADRE', '2')->get();
        //dd($oModule);

        if ($oModule) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $oModule->toArray()];
        }

        return response()->json($jResponse);
    }

    public function app2()
    {
        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];

        $idFather = $this->request->get('mod_id');

        $oModule = DB::table('LAMB_MODULO')->select('ID_MODULO', 'NOMBRE', 'URL', 'ORDEN', 'TIPO', 'ESTADO', 'IMAGEN')->where('ID_PADRE', $idFather)->get();

        $data = array();
        $children = array();

        foreach ($oModule as $key => $item) {

            $children = null;

            $oChildren = DB::table('LAMB_MODULO')->select('ID_MODULO', 'NOMBRE', 'URL', 'ORDEN', 'TIPO', 'ESTADO')->where('ID_PADRE', $item->id_modulo)->get();

            foreach ($oChildren as $key_Ch => $item_Ch) {
                $children[] = ['name' => $item_Ch->nombre, 'type' => $item_Ch->tipo, 'state' => $item_Ch->url];
            }

            $data[] = ['name' => $item->nombre, 'icon' => $item->imagen, 'type' => $item->tipo, 'priority' => $item->orden, 'children' => $children];;
        }

        if ($oModule) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $data];
        }

        return response()->json($jResponse);
    }

    public function report()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $query = "SELECT
            ID_DIARIO,
            TO_CHAR(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
            ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS LOTE,
            COMENTARIO AS HISTORICO,
            ID_DEPTO AS DPTO,
            CASE
                WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS DEBITO,
            CASE
                WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS CREDITO
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = 2017
            AND ID_MES = 3
            AND ID_ENTIDAD = 7124
            AND ID_CTACTE = 17112
            AND ID_CUENTAAASI IN (1136001,2136001,2136010,1136010,1136080)
            ORDER BY FECHA";

        $oQuery = DB::select($query);

        if ($oQuery) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $oQuery];
        }

        return response()->json($jResponse);
    }

    public function report2()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $data = json_decode(file_get_contents("php://input"));

        $year = $data->data->year;
        $month = $data->data->month;
        $entity = $data->data->entity;
        $current_account = $data->data->current_account;

        $query = "SELECT
            ID_DIARIO,
            TO_CHAR(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
            --NOM_DIGITADOR AS Usuario,
            --NOM_CONTADOR,
            --ID_CUENTAAASI AS CTA,
            ID_TIPOASIENTO||' '||COD_AASI||' '||NUM_AASI AS LOTE,
            COMENTARIO AS HISTORICO,
            ID_DEPTO AS DPTO,
            CASE
                WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS DEBITO,
            CASE
                WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                ELSE 0
            END AS CREDITO
            --CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
            --ID_CTACTE AS CONTA_CORRENTE
            FROM VW_CONTA_DIARIO
            WHERE ID_ANHO = $year
            AND ID_MES = $month
            AND ID_ENTIDAD = $entity
            AND ID_CTACTE = $current_account
            AND ID_CUENTAAASI IN (1136001,2136001,2136010,1136010,1136080)
            ORDER BY FECHA";

        $oQuery = DB::select($query);

        if ($oQuery) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $oQuery];
        }

        return response()->json($jResponse);
    }
}
