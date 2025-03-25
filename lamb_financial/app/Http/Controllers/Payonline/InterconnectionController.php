<?php
namespace App\Http\Controllers\Payonline;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Payonline\InterconnectionData;
use App\Http\Data\Payonline\PayonlineData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Session;
use App\Http\Data\cw\CWData;


class InterconnectionController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listarDeuda(Request $request){
        $per_id='2012AL20121120211847';
        $id_venta='001-2018';
        $data = CWData::estadoCuenta($id_venta, $per_id);
        //return view('payonline.inter.deuda',['data'=>$data]);
        return response()->view('payonline.inter.deuda',['data'=>$data])->header('Content-Type', 'text/xml');
    }
}

