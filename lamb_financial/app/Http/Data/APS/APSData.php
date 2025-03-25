<?php
namespace App\Http\Data\APS;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class APSData extends Controller {
    protected $connection = 'sqlsrv';
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    public static function test() {
        $query = "SELECT Id_bank FROM APS..Bank ";
        $id_venta = DB::connection('sqlsrv')->select($query);
        return $id_venta;
    }
}