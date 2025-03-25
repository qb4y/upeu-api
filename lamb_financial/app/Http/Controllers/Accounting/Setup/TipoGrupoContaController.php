<?php
namespace App\Http\Controllers\Accounting\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\TipoGrupoContaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class TipoGrupoContaController extends Controller{

    public function listTipoGrupoContas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){ 
            $jResponse=[];
            try{            
                $data = TipoGrupoContaData::listTipoGrupoContas();
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "200";
                }                    
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
}