<?php
namespace App\Http\Controllers\PW_BI;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\PW_BI\BiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Excel;

class BiController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function BiData($procedure, $argv=''){
        /*$jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){*/
            $jResponse=[];
            $list = [];
            try{
                $variables =explode(";",$argv);
                $values = array();
                $bindings = [];
                foreach ($variables as $arg) { 
                    $e=explode("=",$arg); 
                    if(count($e)==2) 
                        $bindings[$e[0]] = $e[1];
                }

                $list = BiData::superProc($procedure, $bindings);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200"; 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        $data = $list;
        return view('ciclo',['data'=>$data,'response'=>$jResponse]);
    }
    public function BiDataAccount($procedure, $argv=''){
        $list = [];
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $list = [];
            try{
                $variables =explode(";",$argv);
                $values = array();
                $bindings = [];
                foreach ($variables as $arg) { 
                    $e=explode("=",$arg); 
                    if(count($e)==2) 
                        $bindings[$e[0]] = $e[1];
                }

                $list = BiData::superProcAccount($procedure, $bindings);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200"; 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        $data = $list;
        return view('ciclo',['data'=>$data,'response'=>$jResponse]);
    }
    public function BiDataPyD($procedure, $argv=''){
        $list = [];
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $list = [];
            try{
                $variables =explode(";",$argv);
                $values = array();
                $bindings = [];
                foreach ($variables as $arg) { 
                    $e=explode("=",$arg); 
                    if(count($e)==2) 
                        $bindings[$e[0]] = $e[1];
                }

                $list = BiData::superProcPyD($procedure, $bindings);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = [];
                $code = "200"; 
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        $data = $list;
        return view('ciclo',['data'=>$data,'response'=>$jResponse]);
    }
}