<?php
/**
 * User: Amelio Apaza
 * Date: 15/06/2019
 * Time: 11:17 AM
 */

namespace App\Http\Controllers\Bi01;

use App\Http\Controllers\Controller;
use App\Http\Data\MobileData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PDF;
use DOMPDF; 

class BiController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    
    public function bi_procedure($procedure, $argv)
    {
        $variables =explode(";",$argv);
        $values = array();
        $bindings = [];
        foreach ($variables as $arg) { 
            $e=explode("=",$arg); 
            if(count($e)==2) 
                $bindings[$e[0]] = $e[1];
        }

        $list = MobileData::superProc($procedure, $bindings);

        return response()->json($list);
    }

    public function bi_entidad()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - dep_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $bindings = [
        ];

        $list = MobileData::superProc('spc_bi_entidad', $bindings);

        return response()->json($list);
    }

    public function bi_saldo_departamentos()
    {

        $bindings = [
        	'p_id_entidad' => 17112,
        	'p_id_anho' => 2018
        ];

        $list = MobileData::superProc('spc_bi_saldo_departamentos', $bindings);

        return response()->json($list);
    }

    public function bi_mes()
    {

        $bindings = [
        ];

        $list = MobileData::superProc('spc_bi_mes', $bindings);

        return response()->json($list);
    }
    
       
}
