<?php

namespace App\Http\Controllers\HumanTalent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use PDO;
use App\Http\Data\HumanTalent\DirectoryData;
use Excel;

class DirectoryController extends Controller{

    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }

    public function listDirectory(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];            
            try{                         
                $data = DirectoryData::listDirectory($this->request);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function getDirectory(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $query = $request->query('entity');
        if($valida=='SI'){
            $jResponse=[];            
            try{                         
                $data = DirectoryData::filterDirectory($query);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function exportXlsDirectory(Request $request){
        $respuesta = ['nerror'=> 0 ,'mensaje' => "ok"];
        $data = [];

        try{
            
            $data=DirectoryData::listDirectory($this->request);
            print_r($data);
        }catch(Exception $e){
           return 'Error';
        }
       
        
       Excel::create('lista', function($excel) use($data) {

 
            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.humanTalent.directory", array('data'=>$data));
    
                $sheet->setOrientation('landscape');

 
            $sheet->setOrientation('landscape');
            });
        })->download('xls');
    }


}