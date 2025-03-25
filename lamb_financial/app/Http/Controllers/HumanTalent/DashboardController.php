<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/10/2020
 * Time: 7:12 AM
 */
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalent\DashboardData;

class DashboardController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function dashboard(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = DashboardData::report($request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
                
    }
}