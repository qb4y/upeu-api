<?php
namespace App\Http\Controllers\cw;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\cw\CWData;
use App\Http\Controllers\cw\securityToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use PDO;
class CWController extends Controller {
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    public function dataEstadoCuenta(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $per_id   = $request->query('per_id');
            try {
                $sede = "";
                $saldo = "";
                $lstEstadoCta = "";
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $data) {
                    $id_venta = $data->id_venta;
                }
                $sucursal = CWData::validaSede($per_id);
                foreach ($sucursal as $key => $data) {
                    $sede = $data->sede;
                }
                if($sede == "1" || $sede == "3" || $sede == "6"){
                    $lstEstadoCta = CWData::estadoCuenta($id_venta, $per_id);
                    $saldo = CWData::saldoAlumno($id_venta, $per_id);
                }elseif($sede == "5"){
                    $lstEstadoCta = CWData::estadoCuentaTPP($id_venta, $per_id);
                    $saldo = CWData::saldoAlumnoTPP($id_venta, $per_id);
                }elseif($sede == "2"){
                    $lstEstadoCta = CWData::estadoCuentaJU($id_venta, $per_id);
                    $saldo = CWData::saldoAlumnoJU($id_venta, $per_id);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => ['EstadoCta' => $saldo, 'lstEstadoCta' => $lstEstadoCta]];
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }    
    /*public function dataPlanAlumno() {
        $api_key = Request::header('Content-Type');
        $token = securityToken::validaToken($api_key);        

    }*/
    public function dataPlanAlumno(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $per_id   = $request->query('per_id');
            $id_plan = "";
            $dciclo = [];
            try {
                $plan = CWData::planAlumno($per_id);
                foreach ($plan as $key => $data) {
                    $id_plan = $data->plan_id;
                }
                $ciclos = CWData::ciclosAlumno($per_id, $id_plan);
                foreach ($ciclos as $key => $data) {
                    $lstPlan = CWData::planAcademico($per_id, $id_plan, $data->ciclo);
                    $dciclo[] = ['ciclo' => $data->ciclo, 'cursos' => $lstPlan
                    ];
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $dciclo];
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }    
    
    public function cargaAlumno(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $per_id   = $request->query('per_id');
            try {
                $carga = CWData::cargaAlumno($per_id);

               
                /*foreach ($cargau as $key => $data) {
                    $ciclo = $data->utlimo;
                }*/

                $cargau = CWData::cargaAlumnoMax($per_id); 
                //$cargau = CWData::cargaAlumnoMax($per_id);
                foreach ($cargau as $key => $data) {
                    $ciclo = $data->utlimo;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $carga,'ciclo' => $ciclo];
                //$jResponse['ciclo'] = $ciclo;
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }    
    
    public function cursosAlumno(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            //$params = json_decode(file_get_contents("php://input"));
            //$per_id = $params->data->per_id;
            //$carga_id = $params->data->carga_id;
            $per_id   = $request->query('per_id');
            $carga_id   = $request->query('carga_id');
            try {            
                /*$cursos = CWData::cursosAlumno($per_id, $carga_id);
                
                $horario='';
                for($i=1;$i<=112;$i++){
                    $horario.='0';
                }
                $horario='0AFCG000AFCG000AFCG000AEBG000DEB0000DEB0000000000000000000000000000000000000000000000000000000000000000000000000';
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $cursos];
                $jResponse['horario'] = $horario;      
            /*try {*/
                $cursos = CWData::cursosAlumno($per_id, $carga_id);


                $x_horario='';
                for($i=1;$i<=112;$i++){
                    $x_horario.='0';
                }

                $i=1;

                $s_horario   = "";
                $total_horas = 16;
                foreach($cursos as $row){
                    $s_horario=$row->horario_t;
                    $x_horario=$this->creaHorario($s_horario,$x_horario,$total_horas,$i);
                    $i++;
                }


                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $cursos,'horario' => $x_horario ];
                //$jResponse['horario'] = $x_horario;
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }    
    /*public function cursosNotasAlumno() {
        $api_key = Request::header('Content-Type');
        $token = securityToken::validaToken($api_key);        

    }*/
    function creaHorario($s_horario,$x_horario,$total_horas,$i){
        $xhorario="";
        $f_horario=array(1=>"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        $x_returna="";
        $xtemp="";
        $dia=7;
        $total_horas=$total_horas*$dia;
        if(strlen($x_horario)==0){
            for($k=0; $k<$total_horas; $k++ ){
                $xhorario.="0";
            }
        }else{
            $xhorario=$x_horario;
        }
        $shorario=$s_horario;
        for($j=0; $j<$total_horas; $j++){
            if(substr($shorario,$j,1)=="1"){
                if (substr($xhorario,$j,1)!="0" ){
                    if (substr($shorario,$j,1)=="1" ){
                        $xtemp.= "*";

                    }else{
                        $xtemp.= "".$f_horario[$i];
                    }
                }else{
                    $xtemp.= "".$f_horario[$i];
                }
            }else{
                $xtemp.= substr($xhorario,$j,1);
            }
        }
        $x_returna=$xtemp;
        $xtemp="";
        return $x_returna;
    }
    public function cursosNotasAlumno(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $per_id   = $request->query('per_id');
            $curso_id   = $request->query('curso_id');
            try {
                $notas = CWData::cursosNotasAlumno($per_id, $curso_id);
                $promedio = CWData::promedioCursoAlumno($per_id, $curso_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $notas,'promedio'=>$promedio];
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }    
    
    public function acceso() {        
        $params = json_decode(file_get_contents("php://input"));
        
        $usuario = $params->data->user;
        $clave = $params->data->pass;  
        if(isset($params->data->caduca)){
            $caduca = $params->data->caduca;
        }else{
            $caduca = false;
        }
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
                    $foto="";
                    $url_foto="";
                    $rutafoto="";
                
                    $id_persona = "";
                    foreach ($datos as $key => $datas) {
                        $id_persona = $datas->per_id; 
                        $foto=$datas->foto;
                        $url=$datas->url_foto;
                        //$rutafoto=$this->descargarfoto($url,$foto);
                    }   
                    if($caduca == true){
                        $caduca = "N";
                    }else{
                        $caduca = "S";
                    }
                    $token=$this->getSession();
                    $_SESSION['id_user']=$id_persona;
                    $_SESSION['token']=$token;
                    $error=0;
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin spc_user_session_login_app(:P_ID_PERSONA, :P_TOKEN, :P_CADUCA, :P_ERROR); end;");
                    $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
                    $stmt->bindParam(':P_CADUCA', $caduca, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->execute();
                    if($error==0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['token' => $token,'id_persona' => $id_persona];
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'Error al Loguearse';
                        $jResponse['data'] = [];
                    }
                    
                    
                }
            }                        
        } catch (Exception $e) {
            $e;
        }
        return response()->json($jResponse);
    }
    public function descargarfoto($url,$foto){
        
        $foto="foto/".$foto.".jpg";
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  

        $imagen = file_get_contents($url,false, stream_context_create($arrContextOptions));
        file_put_contents($foto, $imagen);
        
        return $this->urlCompleta().$foto;


    }
    
    public function urlCompleta($forwarded_host = false) {
        $ssl   = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
        $proto = strtolower($_SERVER['SERVER_PROTOCOL']);
        $proto = substr($proto, 0, strpos($proto, '/')) . ($ssl ? 's' : '' );
        if ($forwarded_host && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            if (isset($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            } else {
                $port = $_SERVER['SERVER_PORT'];
                $port = ((!$ssl && $port=='80') || ($ssl && $port=='443' )) ? '' : ':' . $port;
                $host = $_SERVER['SERVER_NAME'] . $port;
            }
        }
        $url = $_SERVER['PHP_SELF'];
        $url =  str_replace('index.php','', $url);
        return $proto . '://' . $host . $url;
    }
    public function login() {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
             
        $code = "401";
        $usuario = "wmarlo";//$request->query('user');
        $clave  = "C4milit4";//$request->query('pass');
        try {
            $userAcad = CWData::acceso($usuario, $clave);
            foreach ($userAcad as $key => $data) {
                $user = $data->usuario;
                $codigo = $data->codigo;
                if ($user) {
                    $datos = CWData::userAcad($codigo);
                    foreach ($datos as $key => $datas) {
                        $id_persona = $datas->per_id;
                    }
                    $token=$this->getSession();
                    $_SESSION['id_user']=$id_persona;
                    $_SESSION['token']=$token;

                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['token' => $token,'id_persona' => $id_persona];
                    $code = "200";
                }
            }

        } catch (Exception $e) {
            $jResponse['message'] = $e->getMessage();
        }
        return response()->json($jResponse,$code);
    }
    private function getSession(){
        session_start();
        $session_id = session_id();
        return $session_id;
    }    
    
    public function datosAlumno($per_id) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $ver = CWData::accesoEstadoCta($per_id);
                $datos = CWData::datosAlumno($per_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $datos,'ver'=>$ver];
                $code = "200";
            } catch (Exception $e) {
                //dd($e);
            }
        }
        return response()->json($jResponse,$code);
        /*
        //$api_key = Request::header('Content-Type');
        $api_key = $this->request->header('Authorization');
        $token = securityToken::validaToken($api_key);

        if($token == true){
            /*$params = json_decode(file_get_contents("php://input"));
            $per_id = $params->data->per_id;*
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];

            try {
                $ver = CWData::accesoEstadoCta($per_id);
                $datos = CWData::datosAlumno($per_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $datos,'ver'=>$ver];
            } catch (Exception $e) {
                dd($e);
            }
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'ACCES DENIED',
                'data' => array()
            ];
        }            
        return response()->json($jResponse);*/
    }
    
    public function datosAlumnoVisa(Request $request) {          
        //$api_key = Request::header('Content-Type');
        $api_key = $this->request->header('Authorization');
        // $token = securityToken::validaToken($api_key);        
        if(true){
        // if($token == true){
            //$params = json_decode(file_get_contents("php://input"));
            //$per_id = $params->data->per_id;
            $per_id   = $request->query('per_id');
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];
            try { 
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $data) {
                    $id_venta = $data->id_venta;
                }
                $datos = CWData::datosAlumnoVisa($per_id,$id_venta);
                
                $foto="";
                $url_foto="";
                $rutafoto="";
                foreach($datos as $row){
                    $foto=$row->foto;
                    $rutafoto=$this->urlCompleta()."foto/".$foto.".jpg";
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $datos, 'foto'=>$rutafoto];
            } catch (Exception $e) {
                dd($e);
            }
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'ACCES DENIED',
                'data' => array()
            ];
        }            
        return response()->json($jResponse);
    }
    public function dataAlumnoVisa(Request $request) { 
        $api_key = $this->request->header('Authorization');
        // $token = securityToken::validaToken($api_key);        
        // if($token == true){
        if(true){
            $per_id   = $request->query('per_id');
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];
            try { 
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $data) {
                    $id_venta = $data->id_venta;
                }
                $datos = CWData::dataAlumnoVisa($per_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos[0];
            } catch (Exception $e) {
                dd($e);
            }
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'ACCES DENIED',
                'data' => array()
            ];
        }            
        return response()->json($jResponse);
    }
    public function validaProrroga($per_id) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $data) {
                    $id_venta = $data->id_venta;
                }
                $prorroga = "S";
                $datos = CWData::prorrogaAlumno($id_venta,$per_id);
                foreach ($datos as $key => $data) {
                    $prorroga = $data->prorroga;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $prorroga;
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }         
        return response()->json($jResponse,$code);
    }
    
    public function closeSession() {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $tk = $params->data->token;
            $bindings = [
                'p_token' => $tk
            ];
            DB::executeProcedure('spc_user_session_finish_app', $bindings);
            session_id($tk);
            session_destroy();
            
            $jResponse = [
                'success' => true,
                'message' => 'Close Session'
            ];
            $code = "200";
        }            
        return response()->json($jResponse,$code);
    }
    /*public function alumnoMSN() {          
        $api_key = Request::header('Content-Type');
        $token = securityToken::validaToken($api_key);        
        //if($token == true){
            $params = json_decode(file_get_contents("php://input"));
            $per_id = $params->data->per_id;
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];
            $datos = "";
            $ciclo = "";
            //$dia = 0;
            $msn = "";
            //$mensaje = "";
            $nivel = "";
            $eap = "";
            try { 
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $row) {
                    $id_venta = $row->id_venta;
                }
                $sucursal = CWData::validaSede($per_id);
                foreach ($sucursal as $key => $row) {
                    $sede = $row->sede;
                }                               
                if($sede == "1"){//LIMA                    
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    } 
                    $datos = CWData::datosContrato($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    } 
                    $nivel = "1".$eap;
                }elseif($sede == "3"){
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    } 
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "3".$eap;
                }elseif($sede == "6"){
                    $carga = CWData::cargaEPG();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    } 
                    $datos = CWData::datosContratoEPG($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    } 
                    $nivel = "6".$eap;
                }elseif($sede == "5"){
                    $carga = CWData::cargaFilial();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    } 
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $eap = $data->codigo_eap;
                    } 
                    $nivel = "5".$eap;
                }
                

                $data = CWData::datosMensaje($per_id,$id_venta,$ciclo,$nivel);
                /*foreach ($data as $key => $row) {
                    $dia = $row->dias;
                    $msn = $row->msn;
                    $mensaje = $row->mensaje;
                } *
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                //$jResponse['dia'] = $dia;
                //$jResponse['msn'] = $msn;                

                $data = CWData::datosMensaje($per_id,$id_venta,$ciclo,$nivel); 
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =['data'=> $data[0]];
                }else{
                   $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = false;
                }                

            } catch (Exception $e) {
                dd($e);
            }

        }
        return response()->json($jResponse);
    }*/
    public function alumnoMSNBK(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $per_id   = $request->query('per_id');
            $datos = "";
            $ciclo = "";
            //$dia = 0;
            $msn = "";
            //$mensaje = "";
            $nivel = "";
            $eap = "";
            try {
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $row) {
                    $id_venta = $row->id_venta;
                }
                $sucursal = CWData::validaSede($per_id);
                foreach ($sucursal as $key => $row) {
                    $sede = $row->sede;
                }
                if($sede == "1"){//LIMA
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContrato($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "1".$eap;
                }elseif($sede == "3"){
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "3".$eap;
                }elseif($sede == "6"){
                    $carga = CWData::cargaEPG();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoEPG($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "6".$eap;
                }elseif($sede == "5"){
                    $carga = CWData::cargaFilial();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $eap = $data->codigo_eap;
                    }
                    $nivel = "5".$eap;
                }

                $data = CWData::datosMensaje($per_id,$id_venta,$ciclo,$nivel);
                /*foreach ($data as $key => $row) {
                    $dia = $row->dias;
                    $msn = $row->msn;
                    $mensaje = $row->mensaje;
                } */
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                //$jResponse['dia'] = $dia;
                $jResponse['data'] = $data;
                //$jResponse['msn'] = $msn;
                $data = CWData::datosMensaje($per_id,$id_venta,$ciclo,$nivel);
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =['data'=> $data[0]];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = false;
                    $code = "203";
                }
            } catch (Exception $e) {
                dd($e);
            }

        }       
        return response()->json($jResponse,$code);
    }
    public function alumnoMSN(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $datos = "";
            $ciclo = "";
            $msn = "";
            $nivel = "";
            $eap = "";
            $per_id   = $request->query('per_id');
            try {
                $anho_cw = CWData::anho_cw();
                foreach ($anho_cw as $key => $row) {
                    $id_venta = $row->id_venta;
                }
                $sucursal = CWData::validaSede($per_id);
                foreach ($sucursal as $key => $row) {
                    $sede = $row->sede;
                }
                if($sede == "1"){//LIMA
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContrato($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "1".$eap;
                }elseif($sede == "3"){
                    $carga = CWData::cargaLima();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "3".$eap;
                }elseif($sede == "6"){
                    $carga = CWData::cargaEPG();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoEPG($per_id,$ciclo);
                    foreach ($datos as $key => $row) {
                        $eap = $row->codigo_eap;
                    }
                    $nivel = "6".$eap;
                }elseif($sede == "5"){
                    $carga = CWData::cargaFilial();
                    foreach ($carga as $key => $row) {
                        $ciclo = $row->carga_id;
                    }
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $eap = $data->codigo_eap;
                    }
                    $nivel = "5".$eap;
                }

                $row = CWData::dataMensajeValida($per_id);
                if(count($row) > 0){
                    $data = [];
                }else{
                    if(count($datos)>0){
                        //esta Matriculado, mensajes solo para matriculados
                        $data = CWData::dataMensaje('M');
                    }else{
                        //mensajes para all
                        $data = CWData::dataMensaje('T');
                    }
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;;
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =$data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = false;
                    $code = "203";
                }
            } catch (Exception $e) {
                dd($e);
            }

        }
        return response()->json($jResponse,$code);
    }
    
    public function dataMensajeReg() {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            
            $params = json_decode(file_get_contents("php://input"));
            $per_id = $params->data->per_id;
            $id_mensaje = $params->data->id_mensaje;
            $data = "";
            try {
                $row = CWData::dataMensajeValida($per_id);
                if(count($row) == 0){
                    $data = CWData::dataMensajeReg($per_id,$id_mensaje);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }
    
    public function datosContrato(Request $request) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
                $per_id   = $request->query('per_id');
                $ciclo   = $request->query('ciclo');
            try {
                $sede = "";
                $datos=0;
                $datosCobros = 0;
                $datosDscto = 0;
                $totalContrato = 0;
                $contratoDebitado = 0;
                $contratoCuotas = 0;
                $sucursal = CWData::validaSede($per_id);
                foreach ($sucursal as $key => $data) {
                    $sede = $data->sede;
                }
                if($sede == "1"){
                    $datos = CWData::datosContrato($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $pago = $data->tipo_pago;
                        $eap = $data->codigo_eap;
                    }
                }elseif($sede == "3" || $sede == "5"){
                    $datos = CWData::datosContratoTPP($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $pago = $data->tipo_pago;
                        $eap = $data->codigo_eap;
                    }
                }elseif($sede == "6"){
                    $datos = CWData::datosContratoEPG($per_id,$ciclo);
                    foreach ($datos as $key => $data) {
                        $pago = $data->tipo_pago;
                        $eap = $data->codigo_eap;
                    }
                }

                if(count($datos)>0){
                    if($sede == "1"){
                        $datosCobros = CWData::datosCobros($per_id,$ciclo);
                        $datosDscto = CWData::datosDscto($per_id,$ciclo);
                        $totalContrato = CWData::totalContrato($per_id,$ciclo);
                        $contratoDebitado = CWData::contratoDebitado($per_id,$ciclo);
                        $contratoCuotas = CWData::contratoCuotas($ciclo,$pago,$eap);
                    }elseif($sede == "3"){
                        $datosCobros = CWData::datosCobrosProesad($per_id,$ciclo);
                        $datosDscto = CWData::datosDsctoProesad($per_id,$ciclo);
                        $totalContrato = CWData::totalContratoProesad($per_id,$ciclo);
                        $contratoDebitado = CWData::contratoDebitadoProesad($per_id,$ciclo);
                        $contratoCuotas = CWData::contratoCuotas($ciclo,$pago,$eap);
                    }elseif($sede == "5"){
                        $datosCobros = CWData::datosCobrosTPP($per_id,$ciclo);
                        $datosDscto = CWData::datosDsctoTPP($per_id,$ciclo);
                        $totalContrato = CWData::totalContratoTPP($per_id,$ciclo);
                        $contratoDebitado = CWData::contratoDebitadoTPP($per_id,$ciclo);
                        $contratoCuotas = CWData::contratoCuotas($ciclo,$pago,$eap);
                    }elseif($sede == "6"){
                        $datosCobros = CWData::datosCobrosEPG($per_id,$ciclo);
                        $datosDscto = CWData::datosDsctoEPG($per_id,$ciclo);
                        $totalContrato = CWData::totalContratoEPG($per_id,$ciclo);
                        $contratoDebitado = CWData::contratoDebitadoEPG($per_id,$ciclo);
                        $contratoCuotas = CWData::contratoCuotas($ciclo,$pago,$eap);
                    }
                    $datosper=CWData::datosAlumno($per_id);
                    $rutafoto="";
                    foreach($datosper as $row){
                        $foto=$row->foto;
                        $rutafoto=$this->urlCompleta()."foto/".$foto.".jpg";
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $datos,'cobros'=> $datosCobros,'dsctos'=>$datosDscto,'total'=>$totalContrato,'debitado'=>$contratoDebitado,'cuotas'=>$contratoCuotas,'foto'=>$rutafoto];
                    $code = "200";
                }else{
                    $jResponse = [
                        'success' => false,
                        'message' => 'There is no DATA',
                        'data' => array()
                    ];
                    $code = "202";
                }
            } catch (Exception $e) {
                dd($e);
            }
        }     
        return response()->json($jResponse,$code);
    }
    
  
    public function image(){

        $entry = Fileentry::where('filename', '=', '0380200110121.jpg')->firstOrFail();
        $file = Storage::disk('http://images.upeu.edu.pe/fotodb/')->get($entry->filename);

        return Response('http://images.upeu.edu.pe/fotodb/0380200110121.jpg', 200)->header('Content-Type', $entry->mime);
    }
    public function alumnoTest() {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {

                //$datos = CWData::datosAlumno($per_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => "Welcome APP UPeU"];
            } catch (Exception $e) {
                dd($e);
            }
        }           
        return response()->json($jResponse,$code);
    }
   
    public function periodoHorario(){
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $datos = CWData::periodoHorario();
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }

    public function showAlumno(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $codigo   = $request->query('codigo');
                $datos = CWData::showAlumno($codigo);
                if(count($datos)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse,$code);
    }
    public function cargaDocente(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $per_id   = $request->query('per_id');
                $datos = CWData::cargaDocente($per_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function cargaCursoDocente(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $per_id   = $request->query('per_id');
                $carga_id   = $request->query('carga_id');
                $datos = CWData::cargaCursoDocente($carga_id,$per_id);
                //dd($datos);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showCargaCursoDocente($curso_carga_id){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $datos = CWData::showCargaCursoDocente($curso_carga_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => 'Error Servidor',
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listRubroEvaluacion(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $curso_carga_id   = $request->query('curso_carga_id');
                $datos = CWData::listRubroEvaluacion($curso_carga_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => 'Error Servidor',
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showRubroEvaluacion($evaluacion_id){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $datos = CWData::showRubroEvaluacion($evaluacion_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => 'Error Servidor',
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listAlumnoEvaluacion(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $evaluacion_id  = $request->query('evaluacion_id');
                $datos = CWData::listAlumnoEvaluacion($evaluacion_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listaAlumnoNota(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $curso_carga_id  = $request->query('curso_carga_id');
                $datos = CWData::listaAlumnoNota($curso_carga_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addAlumnoEvaluacion(){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->data->details;  
                $curso_carga_id = $params->data->curso_carga_id;
                $evaluacion_id = $params->data->evaluacion_id;
                $login = $params->data->login;
                $profesor = $params->data->profesor_id;
                $ret=CWData::addAlumnoEvaluacion($profesor,$login,$evaluacion_id,$curso_carga_id,$details);
                if($ret["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse = [
                        'success' => false,
                        'message' => $ret["msgerror"],
                        'data' => array()
                    ];
                    $code = "202";
                }
                
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listaDiaHorario(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $curso_carga_id  = $request->query('curso_carga_id');
                $datos = CWData::listaDiaHorario($curso_carga_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listaAsistencia(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $curso_carga_id  = $request->query('curso_carga_id');
                $fecha  = $request->query('fecha');
                $datos = CWData::listaAsistencia($curso_carga_id,$fecha);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listaAsistenciaNew($curso_carga_id){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                //$curso_carga_id  = $request->query('curso_carga_id');
                $datos = CWData::listaAsistenciaNew($curso_carga_id);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listaAsistenciaEdit($id_asisetncia,Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                
                //$id_asisetncia  = $request->query('id_asisetncia');
                $num_veces  = $request->query('num_veces');
                $datos = CWData::listaAsistenciaEdit($id_asisetncia,$num_veces);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function procAsistencia(){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $params = json_decode(file_get_contents("php://input"));        
                $details = $params->data->details;  
                $curso_carga_id = $params->data->curso_carga_id;
                $fecha = $params->data->fecha;
                $num_veces = $params->data->num_veces;
                CWData::procAsistencia($curso_carga_id,$fecha,$num_veces,$details);
                
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
                
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteAsistencia($id_asistencia){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
               
                CWData::deleteAsistencia($id_asistencia);
                
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
                
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    
    public function precioAdmision(Request $request){        
        /*$api_key = $this->request->header('Authorization');
        $token = securityToken::validaToken($api_key);
        if($token == true){*/
            $jResponse = [
                'success' => false,
                'message' => 'ERROR',
                'data' => array()
            ];
            try {
                
                //$id_asisetncia  = $request->query('id_asisetncia');
                $sucursal         = $request->query('sucursal');
                $programa         = $request->query('programa');
                $m_estudio        = $request->query('m_estudio');
                $m_ingreso        = $request->query('m_ingreso');
                $nivel            = $request->query('nivel');
                $anho             = $request->query('anho');
                $tipo             = $request->query('tipo');
                $nacionalidad     = $request->query('nacionalidad');
                $sucursal_ofiseg  = $request->query('sucursal_ofiseg');
                $nro_postulacion  = $request->query('nro_postulacion');
                $descuento        = $request->query('descuento');
                $cambio_carrera        = $request->query('cambio_carrera');
                $datos = CWData::precioAdmision($sucursal,$programa,$m_estudio,$m_ingreso,$nivel,$anho,$tipo,$nacionalidad,$sucursal_ofiseg,$nro_postulacion,$descuento,$cambio_carrera);
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
    public function thesisAdmision(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $id_persona   = $request->query('id_persona');
            $paso         = $request->query('paso');
            $datos = CWData::ListStudentSede($id_persona);
            foreach ($datos as $row){
                $campus = $row->campus;
                $sector_id = $row->sector_id;
            }
            if($campus == "1"){//LIMA
                $id_negocio = "1";
                $id_aplicacion = "8";
                $datos = CWData::thesisAdmision($paso,$sector_id);
            }elseif($campus == "2"){//JULIACA
                $id_negocio = "2";
                $id_aplicacion = "15";
                $datos = CWData::thesisAdmisionJuliaca($paso,$sector_id);
            }else{//TARAPOTO
                $id_negocio = "3";
                $id_aplicacion = "9";
                $datos = CWData::thesisAdmisionTPP($paso,$sector_id);
            }
            foreach ($datos as $key => $value){
                $data[] = [
                            'id_negocio' => $id_negocio,
                            'id_aplicacion' => $id_aplicacion, 
                            'id' => $value->id, 
                            'nombre' => $value->nombre,
                            'glosa' => $value->glosa,
                            'precio' => $value->precio,
                            'moneda' => $value->moneda,
                            'simbolo' => $value->simbolo
                            //'tipo' => $value->tipo
                        ];            
            }
        
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $data;
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    
    public function listEscuelas(){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $sede = "1";
                $carga = CWData::cargaLima();
                foreach ($carga as $key => $row) {
                    $ciclo = $row->carga_id;
                }
                $datos = CWData::listEscuelas($sede,$ciclo);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listStudentsAssistControl(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $sede = "1";
                $carga = CWData::cargaLima();
                foreach ($carga as $key => $row) {
                    $ciclo = $row->carga_id;
                }
                $id_escuela   = $request->query('id_escuela');
                $fecha   = $request->query('fecha');
                $de  = $request->query('de');
                $a   = $request->query('a');
                $datos = CWData::listStudentsAssistControl($ciclo,$id_escuela,$fecha,$de,$a);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addStudentsAssistControl() {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $per_id = $params->per_id;
            $id_tipo= $params->id_tipo;
            $data = "";
            try {
                $sede = "1";
                $carga = CWData::cargaLima();
                foreach ($carga as $key => $row) {
                    $ciclo = $row->carga_id;
                }
                $contrato = CWData::datosContrato($per_id,$ciclo);
                if(count($contrato)>0){
                    $reg = CWData::showStudentsAssistControl($per_id,$ciclo,$id_tipo);
                    if(count($reg)>0){
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'El Alumno ya esta registrado';
                        $jResponse['data'] = $data;
                        $code = "202";
                    }else{
                        $data = CWData::addStudentsAssistControl($ciclo,$per_id,$id_tipo);
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    }    
                    
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'El Alumno no esta Matriculado';
                    $jResponse['data'] = $data;
                    $code = "202";
                }
                
            } catch (Exception $e) {
                dd($e);
            }
        }           
        return response()->json($jResponse,$code);
    }
    public function showDNIPerson(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $dni   = $request->query('dni');
            $datos = CWData::showDNIPerson($dni);
            if(count($datos)>0){
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = 'No Existe items';
                $jResponse['data'] = [];
            }
            
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    public function listTypesAssistance(){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                
                $datos = CWData::listTypesAssistance();
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteStudentsAssistControl($per_id,$ciclo,$fecha) {
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = "";
            try {
                $data = CWData::deleteStudentsAssistControl($per_id,$ciclo,$fecha);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data; 
                $code = "200";
                
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Error al Eliminar';
                $jResponse['data'] = $data;
                $code = "203";
            }
        }           
        return response()->json($jResponse,$code);
    }
    public function showMyAssists(Request $request){        
        $jResponse = GlobalMethods::authorizationLambAPP($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                $per_id   = $request->query('per_id');
                $curso_id   = $request->query('curso_id');
                $fecha   = $request->query('fecha');
                $datos = CWData::showMyAssists($per_id,$curso_id,$fecha);
                foreach ($datos as $item){
                    $asistio = $item->asistio;                
                }
                if($asistio == "0"){
                    $rpta = false;
                }else{
                    $rpta = true;
                }
                if(count($datos)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $rpta;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No Existen Items';
                    $jResponse['data'] = [];
                    $code = "202";
                }
                
            } catch (Exception $e) {
                $jResponse = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => array()
                ];
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showStudentsEPG(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $nro_documento   = $request->query('nro_documento');
            $datos = CWData::showStudentsEPG($nro_documento);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $datos[0];
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    public function showStudentsSemi(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $nro_documento   = $request->query('nro_documento');
            $datos = CWData::showStudentsSemi($nro_documento);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $datos[0];
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    public function showStudents(Request $request){        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $nro_documento   = $request->query('nro_documento');
            $datos = CWData::showStudents($nro_documento);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $datos[0];
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    public function addRegistroDatos(){  
        $params = json_decode(file_get_contents("php://input"));
        $nombre = $params->nombre;
        $paterno = $params->paterno;
        $materno = $params->materno;
        $tipo = $params->tipo;
        $doc = $params->doc;
        $pais = $params->pais;
        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $datos = CWData::addRegistroDatos($nombre,$paterno,$materno,$tipo,$doc,$pais);
            if(count($datos)>0){
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = 'Persona No Registrada';
                $jResponse['data'] =[] ;
            }
        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    public function saldosAlumno(Request $request){  
        
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        try {
            $tk='$2y$10$Utd1FXqY2qaSUz0KGBFxdeyxHumUJSex79ERhgqXWo.PGy995un4S';
            $token  = $request->header('Authorization');
            if($tk==$token){
               $opcion = $request->opcion;
                $codigo = $request->codigo;

                $datos  = CWData::saldoAlumnoCU($codigo);
                if($datos['nerror']==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos['data'];
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $datos['mensaje'];
                    $jResponse['data'] =[] ;
                } 
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = 'Acceso Denegado';
                $jResponse['data'] =[] ;
            }
            

        } catch (Exception $e) {
            $jResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => array()
            ];
        }
        return response()->json($jResponse);
    }
    
    public function depSalesEda(Request $request){
        // $jResponse = GlobalMethods::authorizationLamb($this->request);
        $jResponse =[];
        $code = '500';
        // if($valida=='SI'){
        try{
            $result = CWData::depSalesEda($request);
            if ($result['success']){
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];
                $jResponse['data']    = $result['data'];
                $code = "200";
            }else{
                $jResponse['success'] = $result['success'];
                $jResponse['message'] = $result['message'];
                $jResponse['data'] = $result['data'];
                $code = "202";
            }
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-".$e->getMessage();
            $jResponse['data'] = [];
            $code = "400";
        }
        // }
        return response()->json($jResponse,$code);
    }
}
