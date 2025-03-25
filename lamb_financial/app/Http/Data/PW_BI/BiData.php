<?php
namespace App\Http\Data\PW_BI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class BiData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function superProc($procedureName,$bindings){
        //$result = DB::executeProcedureWithCursor($procedureName, $bindings);
        $result = DB::connection('oracleapp')->executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
    public static function superProcAccount($procedureName,$bindings){
        //$result = DB::executeProcedureWithCursor($procedureName, $bindings);
        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
    public static function superProcPyD($procedureName,$bindings){
        $result = DB::connection('pyd')->executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
}