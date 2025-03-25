<?php
/**
 * Created by PhpStorm.
 * User: amelio
 * Date: 25/05/$second_year
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;



class TestData extends Controller
{ 
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public static function superProc($procedureName,$bindings)
    {
        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
    
    public static function balance($procedureName,$entity,$year,$month)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }

    public static function atest()
    {
        $p1 = 8;
        $p2 = 0;

        $procedureName = 'spc_balance';

        $bindings = [
            'p_id_entidad'  => 17112,
            'p_id_anho'  => 2017,
            'p_id_mes'  => 4
        ];


        $result = DB::executeProcedureWithCursor($procedureName, $bindings);

        return $result;

    }



}
