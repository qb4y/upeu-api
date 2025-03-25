<?php
namespace App\Http\Data\Payonline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class InterconnectionData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
}

