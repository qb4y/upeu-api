<?php
namespace App\Http\Controllers\Payonline;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Pasarela\PasarelaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Session;

class PayonlineController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
}
