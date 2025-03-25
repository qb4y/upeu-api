<?php
namespace App\Http\Controllers\ProviderServices;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\cw\CWData;
use App\Http\Data\ProviderServices\ProviderData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class ProviderController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function accesoAcademico() {        
        $params = json_decode(file_get_contents("php://input"));
        $usuario = $params->data->user;
        $clave = $params->data->pass;        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];       
        try {            
            $userAcad = CWData::acceso($usuario, $clave);     
            foreach ($userAcad as $key => $data) {
                $user = $data->usuario;
                $codigo = $data->codigo;
                if ($user) {                    
                    $datos = CWData::userAcad($codigo,$user);
                    foreach ($datos as $key => $datas) {
                        $id_persona = $datas->per_id; 
                        $s_codigo = $datas->codigo;
                    }                    
                    $_SESSION['id_user']=$id_persona;
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['id_persona' => $id_persona,'codigo' => $s_codigo]; 
                }
            }                        
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($jResponse);
    }
    public function listEntidades() {        
            
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];       
        try {  
            $lstEntity = ProviderData::listEntidades();
                if ($lstEntity) {
                    $value1 = "1";
                    $value2 = "1";
                    $companies = [];
                    $entities = [];

                    foreach ($lstEntity as $key => $value) {
                        $value2 = $value->tipo;

                        if ($value1 != $value2 && $value1 != "1") {
                            $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1,'entidades' => $entities];
                            $entities = [];
                        }
                        $entities[] = $value;
                        $value1 = $value2;
                    }
                    $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1, 'entidades' => $entities];
                    if ($companies) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "OK";
                        $jResponse['data'] = $companies;
                        $code = "202";  
                    }else{
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }               
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getCode();
            $jResponse['data'] = [];
            $code = "400";
        }
        return response()->json($jResponse,$code);
    }
    public function listEntidadesDepartments($id_entidad) {        
            
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];       
        try {   
            $data = ProviderData::listEntidadesDepartments($id_entidad);  
            if(count($data)>0) {          
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
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse,$code);
    }
    public function datosPlanilla(Request $request) {        
            
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];       
        try {   
            $id_entidad   = $request->query('id_entidad');
            $id_anho   = $request->query('id_anho');
            $id_mes   = $request->query('id_mes');
            $id_depto   = $request->query('id_depto');
            $data = ProviderData::datosPlanilla($id_entidad,$id_anho, $id_mes,$id_depto);     
            
            if(count($data)>0) {          
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
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse,$code);
    }
    public function showPersonalInformation(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $nro_documento   = $request->query('nro_documento');
            $id_tipodocumento   = $request->query('id_tipodocumento');
            $nombre   = $request->query('nombre');
            $paterno   = $request->query('paterno');
            $materno   = $request->query('materno');
            $datos = ProviderData::showPersonalInformation($id_tipodocumento,$nro_documento,$nombre,$paterno,$materno);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $datos[0];
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => "ORA-".$e->getCode(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
}