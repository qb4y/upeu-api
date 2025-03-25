<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\ReportsData;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DOMPDF;
use Mail;
class ReportsController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function reportRegisterFirm(Request $request){
      $jResponse = GlobalMethods::authorizationLamb($this->request);
      $code   = $jResponse["code"];
      $valida = $jResponse["valida"];
      $id_user = $jResponse["id_user"];
      if($valida=='SI'){
          $jResponse=[];                        
          try{ 
             
              // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
              $data = ReportsData::reportRegisterFirm($request, $id_user);
             
              if(count($data)>0){
                  $jResponse['message'] = "Succes";                    
                  $jResponse['data'] = ['items' => $data['data1'], 'data' => $data['data2']];
                   $jResponse['success'] = true;
                  $code = "200";
              }else{
                  $jResponse['message'] = "The item does not exist";                        
                  $jResponse['data'] = [];
                  $jResponse['success'] = true;
                  $code = "202";
              }
          }catch(Exception $e){
              $jResponse['success'] = false;
              $jResponse['message'] = $e->getMessage();
              $jResponse['data'] = [];
              $code = "202";
          } 
      }        
      return response()->json($jResponse,$code);
  }
  public function reportOuthinMonth(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    if($valida=='SI'){
        $jResponse=[];                        
        try{ 
          
          $data = ReportsData::reportOuthinMonth($request, $id_user);
           
            if(count($data)>0){
              $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = ['items' => $data['data1'], 'data' => $data['data2']];
                $code = "200";
            }else{
              $jResponse['success'] = true;
                $jResponse['message'] = "The item does not exist";                        
                $jResponse['data'] = [];
                $code = "202";
            }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        } 
    }        
    return response()->json($jResponse,$code);
}
public  function calendarHolidays(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    // dd('hola', $request);
    if($valida=='SI'){
        $jResponse=[];                        
        try{   
           
            $return  =  ReportsData::calendarHolidays($request, $id_user); 
            // dd($return, 'ssss');
            if ($return['nerror']==0) {
                $header  =  ReportsData::calendarHeaders($id_user);
                $body  =  ReportsData::calendarBody($request, $id_user); 
                // dd($data);
              if (count($header)>0) {
                  $jResponse['success'] = true;
                  $jResponse['message'] = "The item was created successfully";                    
                  $jResponse['data'] = ['header' => $header, 'body' => $body['data'], 'items' =>$body['paginate']];
                  $code = "200";  
              } else {
                $jResponse['success'] = true;
                $jResponse['message'] = "No existe items";
                $jResponse['data'] = [];
                $code = "202";
              }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = $return['msgerror'];
                $jResponse['data'] = [];
                $code = "202";
              }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        } 
    }        
    return response()->json($jResponse,$code);
}
  }