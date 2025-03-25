<?php


namespace App\Http\Controllers\Financial;


use App\Http\Controllers\Controller;
use App\Http\Data\Financial\CriterionSemesterData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CriterionSemesterController  extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        $id_semestre = $this->request->id_semestre;
        $id_semestre_programa = $this->request->id_semestre_programa;
        $id_nivel_ensenanza = $this->request->id_nivel_ensenanza;
        $dc = $this->request->dc;
//        $nombre = $this->request->nombre;

        if ($response["valida"] == 'SI') {
//            dd('PASSSS');
            $response["code"] = 200;
            $response['success'] = true; 
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterData::index($id_semestre_programa, $id_nivel_ensenanza, $dc);
        }

        return response()->json($response, $response["code"]);
    }

    public function store(Request $request) {

        /*$this->validate($request,[
            'FORMULA' => 'required|unique:posts|max:8',
        ]);*/

        $response = GlobalMethods::authorizationLamb($this->request);

        $dataPost = $request->all();

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK'; 
            $response['data'] = CriterionSemesterData::add($dataPost);
        }

        return response()->json($response, $response["code"]);

    }

    public function show($id_criterio_semestre) {
        $response = GlobalMethods::authorizationLamb($this->request);
        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterData::show($id_criterio_semestre);
        }
        return response()->json($response, $response["code"]);
    }

    public function update(Request $request, $id)
    {
        $response = GlobalMethods::authorizationLamb($this->request);

        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = CriterionSemesterData::update($request->all(), $id);
        }

        return response()->json($response, $response["code"]);


    }
    public function destroy($id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];   
            DB::beginTransaction();
            try{   
                $return  =  CriterionSemesterData::delete($id);  
                  if ($return['nerror']==0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";  
                      $jResponse['data'] = [];
                      $code = "200";  
                      DB::commit();
                  } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                  }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            } 
            
        }        
        return response()->json($jResponse,$code);


    }

    public  function addCopyCriterioMatricula(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];   
            DB::beginTransaction();
            try{   
                $return  =  CriterionSemesterData::addCopyCriterioMatricula($request);  
                  if ($return['nerror']==0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";  
                      $jResponse['data'] = [];
                      $code = "200";  
                      DB::commit();
                  } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                  }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            } 
            
        }        
        return response()->json($jResponse,$code);
    }
}