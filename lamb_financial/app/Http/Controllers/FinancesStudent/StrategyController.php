<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 5/29/19
 * Time: 6:59 PM
 */
namespace App\Http\Controllers\FinancesStudent;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\FinancesStudent\StrategyData;
class StrategyController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function saveStrategia(Request $request){
      $jResponse = GlobalMethods::authorizationLamb($this->request);
      $code   = $jResponse["code"];
      $valida = $jResponse["valida"];
      if($valida=='SI'){
          $jResponse=[];                        
          try{
              $response = StrategyData::saveStrategia($request);  
              if($response['success']){
                  $jResponse['success'] = true;
                  $jResponse['message'] = $response['message'];       
                  $jResponse['data'] = $response['data'];          
                  $code = "200";
              }else{
                  $jResponse['success'] = false;
                  $jResponse['message'] = $response['message'];                        
                  $jResponse['data'] = [];
                  $code = "202";
              }
          }catch(Exception $e){
              $jResponse['success'] = false;
              $jResponse['message'] = $e->getMessage();
              $code = "202";
          } 
      }        
      return response()->json($jResponse,$code);
  }
  public function listStrategia(Request $request)
  {
      $jResponse = GlobalMethods::authorizationLamb($this->request);
      $code = $jResponse["code"];
      $valida = $jResponse["valida"];
      if ($valida == 'SI') {
          $jResponse = [];
          $msn = "";
          try {
              $data = StrategyData::listStrategia($request);
              if (count($data) > 0) {
                  $jResponse['success'] = true;
                  $jResponse['message'] = 'OK';
                  $jResponse['data'] =  $data;
                  $code = "200";
              } else {
                  $jResponse['success'] = true;
                  $jResponse['message'] = 'The item does not exist';
                  $jResponse['data'] = [];
                  $code = "202";
              }

          } catch (Exception $e) {
              $jResponse['success'] = false;
              $jResponse['message'] = $msn;
              $jResponse['data'] = [];
              $code = "202";
          }
      }
      return response()->json($jResponse, $code);
  }
  public function deleteStrategia($id_estrategia){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[];                        
        try{
            $response = StrategyData::deleteStrategia($id_estrategia);  
            if($response['success']){
                $jResponse['success'] = true;
                $jResponse['message'] = $response['message'];       
                $jResponse['data'] = [];             
                $code = "200";
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $response['message'];                        
                $jResponse['data'] = [];
                $code = "202";
            }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        } 
    }        
    return response()->json($jResponse,$code);
}
public function updateStrategia($id_estrategia, Request $request){
  $jResponse = GlobalMethods::authorizationLamb($this->request);
  $code   = $jResponse["code"];
  $valida = $jResponse["valida"];
  if($valida=='SI'){
      $jResponse=[];                        
      try{
          $response = StrategyData::updateStrategia($id_estrategia, $request);  
          if($response['success']){
              $jResponse['success'] = true;
              $jResponse['message'] = $response['message'];       
              $jResponse['data'] = [];             
              $code = "200";
          }else{
              $jResponse['success'] = false;
              $jResponse['message'] = $response['message'];                        
              $jResponse['data'] = [];
              $code = "202";
          }
      }catch(Exception $e){
          $jResponse['success'] = false;
          $jResponse['message'] = $e->getMessage();
          $code = "202";
      } 
  }        
  return response()->json($jResponse,$code);
    }
    public function saveAsignarStrategia(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_financista = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StrategyData::saveAsignarStrategia($request, $id_financista);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = $response['data'];          
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listStrategiaAsignada(Request $request)
  {
      $jResponse = GlobalMethods::authorizationLamb($this->request);
      $code = $jResponse["code"];
      $valida = $jResponse["valida"];
      $id_entidad = $jResponse["id_entidad"];
      $id_depto = $jResponse["id_depto"];
      if ($valida == 'SI') {
          $jResponse = [];
          $msn = "";
          try {
              $data = StrategyData::listStrategiaAsignada($request, $id_entidad, $id_depto);
              if (count($data) > 0) {
                  $jResponse['success'] = true;
                  $jResponse['message'] = 'OK';
                  $jResponse['data'] =  $data;
                  $code = "200";
              } else {
                  $jResponse['success'] = true;
                  $jResponse['message'] = 'The item does not exist';
                  $jResponse['data'] = [];
                  $code = "202";
              }

          } catch (Exception $e) {
              $jResponse['success'] = false;
              $jResponse['message'] = $msn;
              $jResponse['data'] = [];
              $code = "202";
          }
      }
      return response()->json($jResponse, $code);
  }
  public function deleteStrategiaAsignada($id_estrategia_alumno){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[];                        
        try{
            $response = StrategyData::deleteStrategiaAsignada($id_estrategia_alumno);  
            if($response['success']){
                $jResponse['success'] = true;
                $jResponse['message'] = $response['message'];       
                $jResponse['data'] = [];             
                $code = "200";
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $response['message'];                        
                $jResponse['data'] = [];
                $code = "202";
            }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        } 
    }        
    return response()->json($jResponse,$code);
}
public function detailStrategiaAsignada(Request $request)
{
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code = $jResponse["code"];
    $valida = $jResponse["valida"];
    if ($valida == 'SI') {
        $jResponse = [];
        $msn = "";
        try {
            $data = StrategyData::detailStrategiaAsignada($request);
            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] =  $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }

        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $msn;
            $jResponse['data'] = [];
            $code = "202";
        }
    }
    return response()->json($jResponse, $code);
}

public function updateGandor($ganador, Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    if($valida=='SI'){
        $jResponse=[];                        
        try{
            $response = StrategyData::updateGandor($ganador, $request);  
            if($response['success']){
                $jResponse['success'] = true;
                $jResponse['message'] = $response['message'];       
                $jResponse['data'] = [];             
                $code = "200";
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $response['message'];                        
                $jResponse['data'] = [];
                $code = "202";
            }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        } 
    }        
    return response()->json($jResponse,$code);
 }
}