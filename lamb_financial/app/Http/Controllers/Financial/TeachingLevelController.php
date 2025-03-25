<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 08/01/20
 * Time: 03:16 PM
 */


namespace App\Http\Controllers\Financial;
use App\Http\Controllers\Controller;
use App\Http\Data\Financial\TeachingLevelData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;



class TeachingLevelController extends Controller {

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function index(){

        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = TeachingLevelData::index();
        }

        return response()->json($response, $response["code"]);
    }


}