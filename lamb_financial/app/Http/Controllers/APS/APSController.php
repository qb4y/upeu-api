<?php
namespace App\Http\Controllers\APS;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\APS\APSData;
use App\Http\Controllers\cw\securityToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Data\GlobalMethods;
class APSController extends Controller {
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    public function test(){        
        $api_key = $this->request->header('Authorization');
        // $token = securityToken::validaToken($api_key);
        //if($token == true){
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];
            try {
                $datos = APSData::test();
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        /*}else{
            $jResponse = [
                'success' => false,
                'message' => 'ACCES DENIED',
                'data' => array()
            ];
        }*/
        return response()->json($jResponse);
    }
}