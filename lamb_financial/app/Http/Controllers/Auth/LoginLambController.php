<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\LambUsuario;
use App\Persona;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
//use Request;
use Exception;
use PDO;


class LoginLambController extends Controller{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    //use AuthenticatesUsers;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $redirectTo = '/home';
    protected $auth;
    protected $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;
        $this->middleware('guest')->except('logout');
    }
//     public function validTokensOauth()
//     {
//         $jResponse = [
//             'success' => false,
//             'message' => 'none'
//         ];

//         try{
//             $params       = json_decode(file_get_contents("php://input"));
//             $access_token = $params->access_token;
//             $username     = $params->username;

//             $context = stream_context_create(array(
//                 'http' => array(
//                     'header'  => "Authorization: ".$access_token 
//                 )
//             ));
//             $url='http://oauth.upeu.edu.pe/api/me';

       
//             $respuesta = file_get_contents($url, false, $context);

//             $users =  (object) json_decode($respuesta, true);

//             $username_resp="";

//             if(is_object($users)){
//                 $username_resp=$users->username;
//             }
//             $id_persona = 0;
//             if ($username_resp==$username){
//                 $query = "SELECT 
//                                 ID,
//                                 EMAIL
//                           FROM USERS
//                           WHERE EMAIL='".$username_resp."'" ;
//                 $oQuery = DB::select($query);
//                 foreach($oQuery as $row){
//                     $id_persona=$row->id;
//                 }
//             }
//             //if (Auth::guard('')->attempt($this->request->only('email', 'password'), $this->request->has('remember'))) {
//             //if (Auth::guard('')->attempt(['email' => $email, 'password' => $password], $this->request->has('remember'))) {
//             if (($username_resp==$username) and ($id_persona>0)){
//                 $jResponse['success'] = false;
//                 $jResponse['message'] = 'Usuario o Access Token del Oauth-UPEU incorrecto';
//                 $jResponse['data'] = [];
//                 $jResponse['url'] = '';
//                 goto end;
//             }

//                 //$id_persona = Auth::user()->id;

//                 $token=$this->getSession();
//                 $_SESSION['id_user']=$id_persona;
//                 $_SESSION['token']=$token.'2';

//                 //$_SESSION['token'] = GlobalMethods::setSecret($token.'|2',"L@MB");
//                 $token = $_SESSION['token'];

//                 /*$bindings = [
//                  'p_id_persona' => $id_persona,
//                  'p_token' => $token
//                 ];
//                 DB::executeProcedure('spc_user_session_login', $bindings);*/

//                     $error = 0;
//                     $pdo = DB::getPdo();
//                     $stmt = $pdo->prepare("begin spc_user_session_login(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR); end;");
//                     $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
//                     $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
//                     $stmt->bindParam(':P_TOKEN_OAUTH', $access_token, PDO::PARAM_STR);
//                     $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
//                     $stmt->execute(); 
//                     if($error==0){
//                         $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_persona)->first();
//                         $name = $oPerson->nombre;
//                         $first_name = $oPerson->paterno;
//                         $last_name = $oPerson->materno;
//                         $full_name = $name . ' ' . $first_name . ' ' . $last_name;

//                         $jResponse['success'] = true;
//                         $jResponse['message'] = 'Acceso Correcto';
//                         $jResponse['data'] = ['token' => $token/*'name' => $full_name, 'id' => $id_persona*/]; 
//                     }elseif($error==1){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'No tiene Entidad Asignada';
//                         $jResponse['data'] = [];
//                     }elseif($error==2){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'No Tiene Contrato Activo, o Terminó sus Contrato';
//                         $jResponse['data'] = [];
//                     }elseif($error==3){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'Entidad Asignada o abscrita, no tiene asignado Acceso';
//                         $jResponse['data'] = [];
//                     }elseif($error==4){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'No tiene Entidad(des) Asignada(s) por Default';
//                         $jResponse['data'] = [];
//                     }elseif($error==5){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'No tiene Departamento por Default';
//                         $jResponse['data'] = [];
//                     }elseif($error==6){
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'No Tiene Asignado ROl para esta Entidad';
//                         $jResponse['data'] = [];
//                     }
//                     else{
//                         $jResponse['success'] = false;
//                         $jResponse['message'] = 'Tiene varios departamentos por Defualt';
//                         $jResponse['data'] = [];
//                     } 

//             } else {

//                 $jResponse['success'] = false;
//                 $jResponse['message'] = 'Usuario o Access Token incorrecto';
//                 $jResponse['url'] = '';
//             }
//          }catch(Exception $e){
//             $jResponse['success'] = false;
//             $jResponse['message'] = "ORA: ".$e->getMessage();
//             $jResponse['data'] = [];
//         }
// //      
//         return response()->json($jResponse);
//     }
public function validTokensOauth()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none'
        ];

        try{
            $params       = json_decode(file_get_contents("php://input"));
            $access_token = $params->access_token; // TOKEN OAUTH
            $username     = $params->username;
            /* $data = array('token' => $access_token);
            $context = stream_context_create(array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Authorization: Bearer " . $access_token . "\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                )
            ));
            $url = 'https://oauth.upeu.edu.pe/oauth/introspect/';
            $resp = file_get_contents($url, false, $context);
            $user = (object)json_decode($resp, true);

            if ($user) {
                if (!$user->active) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Token inválido, necesita refrescar token';
                    $jResponse['data'] = [];
                    $jResponse['url'] = '';
                    goto end;
                    // abort(403, 'Token inválido, necesita refrescar token');
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = 'Token inválido, necesita iniciar sesión';
                $jResponse['data'] = [];
                $jResponse['url'] = '';
                goto end;
                // abort(403, 'Token inválido, necesita iniciar sesión');
            } */
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: ".$access_token 
                )
            ));
            // $url='https://oauth.upeu.edu.pe/api/me';
            $url = env('oauth_instrospect');

            $respuesta = file_get_contents($url, false, $context);
            // print_r($respuesta);
            $users =  (object) json_decode($respuesta, true);

            $username_resp="";

            if(is_object($users)){
                $username_resp=$users->username;
            }

            $id_persona = 0;
            if ($username_resp==$username){
                $query = "SELECT 
                                ID,
                                EMAIL
                          FROM USERS
                          WHERE EMAIL='".$username_resp."'" ;
                $oQuery = DB::select($query);
                foreach($oQuery as $row){
                    $id_persona=$row->id;
                }
            }
            if (($username_resp !== $username) || ($id_persona <= 0)){ 
                $jResponse['success'] = false;
                $jResponse['message'] = 'Usuario o Access Token del Oauth-UPEU incorrecto';
                $jResponse['data'] = [];
                $jResponse['url'] = '';
                goto end;
            }

            $access_token = str_replace("bearer ", "", $access_token);
            
            // $token=$this->getSession();
            $_SESSION['id_user']=$id_persona;
            // $_SESSION['token']=$token.'2';

            // $token = $_SESSION['token'];

            $msg_error = '';
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $error = 0;
            $pid_modulo = 2;
            $pdo = DB::getPdo();

            $stmt = $pdo->prepare("begin spc_user_session_login(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR, :P_MSJERROR); end;");
            //$stmt = $pdo->prepare("begin spc_user_session_login_prueba(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ID_MODULO, :P_ERROR, :P_MSJERROR); end;");
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            // $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
            $stmt->bindParam(':P_TOKEN', $access_token, PDO::PARAM_STR);
            $stmt->bindParam(':P_TOKEN_OAUTH', $access_token, PDO::PARAM_STR);
            // $stmt->bindParam(':P_ID_MODULO', $pid_modulo, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSJERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute(); 

            if($error === 1) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msg_error;
                $jResponse['data'] = [];
                goto end;
            }

            $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_persona)->first();
            $name = $oPerson->nombre;
            $first_name = $oPerson->paterno;
            $last_name = $oPerson->materno;
            $full_name = $name . ' ' . $first_name . ' ' . $last_name;

            $jResponse['success'] = true;
            $jResponse['message'] = 'Acceso Correcto';
            $jResponse['data'] = ['token' => $access_token];
            // $jResponse['data'] = ['token' => $token]; 
                /*
                if($error === 0){
                    $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_persona)->first();
                    $name = $oPerson->nombre;
                    $first_name = $oPerson->paterno;
                    $last_name = $oPerson->materno;
                    $full_name = $name . ' ' . $first_name . ' ' . $last_name;

                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Acceso Correcto';
                    $jResponse['data'] = ['token' => $token]; 
                }elseif($error==1){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No tiene Entidad Asignada';
                    $jResponse['data'] = [];
                }elseif($error==2){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No Tiene Contrato Activo, o Terminó sus Contrato';
                    $jResponse['data'] = [];
                }elseif($error==3){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Entidad Asignada o abscrita, no tiene asignado Acceso';
                    $jResponse['data'] = [];
                }elseif($error==4){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No tiene Entidad(des) Asignada(s) por Default';
                    $jResponse['data'] = [];
                }elseif($error==5){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No tiene Departamento por Default';
                    $jResponse['data'] = [];
                }elseif($error==6){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No Tiene Asignado ROl para esta Entidad';
                    $jResponse['data'] = [];
                }
                else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Tiene varios departamentos por Defualt';
                    $jResponse['data'] = [];
                } 
                */
         }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA: ".$e->getMessage();
            $jResponse['data'] = [];
        }
//      
        end:
        return response()->json($jResponse);
    }
    public function login()
    {

        $jResponse = [
            'success' => false,
            'message' => 'none'
        ];

        $params = json_decode(file_get_contents("php://input"));
        $email = $params->data->email;
        $password = $params->data->password;

//        $this->validate($this->request, [
//            'email' => 'required|string',
//            'password' => 'required|string',
//        ]);

        //if (Auth::guard('')->attempt($this->request->only('email', 'password'), $this->request->has('remember'))) {
        if (Auth::guard('')->attempt(['email' => $email, 'password' => $password], $this->request->has('remember'))) {


            $id_persona = Auth::user()->id;

            $token=$this->getSession();
            $_SESSION['id_user']=$id_persona;
            $_SESSION['token']=$token.'2';
            
            //$_SESSION['token'] = GlobalMethods::setSecret($token.'|2',"L@MB");
            $token = $_SESSION['token'];
            
	    /*$bindings = [
             'p_id_persona' => $id_persona,
	     'p_token' => $token
            ];
            DB::executeProcedure('spc_user_session_login', $bindings);*/
            try{
                $error = 0;
                $pid_modulo = 2;
                $access_token='';
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin spc_user_session_login(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR ); end;");
                // $stmt = $pdo->prepare("begin spc_user_session_login_prueba(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ID_MODULO, :P_ERROR ); end;");
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
                $stmt->bindParam(':P_TOKEN_OAUTH', $access_token, PDO::PARAM_STR);
                // $stmt->bindParam(':P_ID_MODULO', $pid_modulo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                
                $stmt->execute(); 
                if($error==0){
                    $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_persona)->first();
                    $name = $oPerson->nombre;
                    $first_name = $oPerson->paterno;
                    $last_name = $oPerson->materno;
                    $full_name = $name . ' ' . $first_name . ' ' . $last_name;

                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Acceso Correcto';
                    $jResponse['data'] = ['token' => $token/*'name' => $full_name, 'id' => $id_persona*/]; 
                }elseif($error==1){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No tiene Entidad Asignada';
                    $jResponse['data'] = [];
                }elseif($error==2){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No Tiene Contrato Activo, o Terminó sus Contrato';
                     $jResponse['data'] = [];
                }elseif($error==3){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Entidad Asignada o abscrita, no tiene asignado Acceso';
                     $jResponse['data'] = [];
                }elseif($error==4){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No tiene Departamento por Default';
                     $jResponse['data'] = [];
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Tiene varios departamentos por Defualt';
                    $jResponse['data'] = [];
                } 
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA: ".$e->getCode();
                $jResponse['data'] = [];
            }
                                  
        } else {

            $jResponse['success'] = false;
            $jResponse['message'] = 'Usuario o Contraseña incorrecto';
            $jResponse['url'] = '';
        }

//        $oUser = LambUsuario::where('login',$request->get('username'))->first();
//        //$oUser= DB::table('LAMB_USUARIO')->where('login',$request->get('email'))->first();
//
//        if($oUser) {
//            if ($oUser->contrasenha == $this->request->get('password')) {
//
//                //$oPerson = Persona::where('id_persona', $oUser->id_persona)->first();
//                $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $oUser->id_persona)->first();
//                $name = $oPerson->nombre;
//                $first_name = $oPerson->paterno;
//                $last_name = $oPerson->materno;
//                $full_name = $name . ' ' . $first_name . ' ' . $last_name;
//                $authUser = new GenericUser($oUser->toArray());
//                $this->auth->login($authUser);
//
//                $jResponse['success'] = true;
//                $jResponse['message'] = 'Ganaste';
//                $jResponse['url'] = 'admin';
//                $jResponse['data'] = ['name' => $full_name,'token'=>''];
//                return response()->json($jResponse);
//                //return view('welcome');
//            }else{
//                $jResponse['success'] = false;
//                $jResponse['message'] = 'password incorrecto';
//                $jResponse['url'] = 'none';
//            }
//        }else{
//            $jResponse['success'] = false;
//            $jResponse['message'] = 'No existe uduario';
//            $jResponse['url'] = 'none';
//        }
        return response()->json($jResponse);
    }
    public function login_mobile()
    {

        $jResponse = [
            'success' => false,
            'message' => 'none'
        ];

        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $email = $params->data->email;
        $password = $params->data->password;

        if (Auth::guard('')->attempt(['email' => $email, 'password' => $password], $this->request->has('remember'))) {

            $id_persona = Auth::user()->id;

            $token=$this->getSession();
            $_SESSION['id_user']=$id_persona;
            $_SESSION['token']=$token;

            $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_persona)->first();
            $name = $oPerson->nombre;
            $first_name = $oPerson->paterno;
            $last_name = $oPerson->materno;
            $full_name = $name . ' ' . $first_name . ' ' . $last_name;

            $jResponse['success'] = true;
            $jResponse['message'] = 'Acceso Correcto';
            $jResponse['data'] = ['name' => $full_name, 'token' => $token, 'id' => $id_persona];

        } else {

            $jResponse['success'] = false;
            $jResponse['message'] = 'Usuario o Contraseña incorrecto';
            $jResponse['url'] = '';
        }
        return response()->json($jResponse);
    }
    
    private function getSession()
    {
        session_start();
        $session_id = session_id();

        return $session_id;
    }
    public function userModuleOld() {
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
            'data' => []
        ];

        $token = $this->request->header('Content-Type');

        if ($token) {
            session_id($token);
            session_start();

            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);

            $valida = $result[0];


            if ($valida->active == 'SI') {
                $id_user = $valida->id_user;
                $query = "SELECT 
                            DISTINCT D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                    FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                    WHERE A.ID_ROL = B.ID_ROL 
                    AND B.ID_ROL = C.ID_ROL
                    AND C.ID_MODULO = D.ID_MODULO
                    AND A.ID_PERSONA = $id_user
                    AND D.ID_PADRE = 2 ";
                $oQuery = DB::select($query);

                foreach ($oQuery as $key => $item_padre) {
                    $query = "SELECT DISTINCT
                       		     D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                    		FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                    		WHERE A.ID_ROL = B.ID_ROL
                    		AND B.ID_ROL = C.ID_ROL
                    		AND C.ID_MODULO = D.ID_MODULO
                    		AND A.ID_PERSONA = $id_user
                    		AND D.ID_PADRE = $item_padre->id ";
                    $oModule = DB::select($query);
                    $data = array();
                    $children = array();

                    foreach ($oModule as $key => $item) {

                        $children = null;

                        //$oChildren = DB::table('LAMB_MODULO')->select('ID_MODULO', 'NOMBRE', 'URL', 'ORDEN', 'TIPO', 'ESTADO','IMAGEN')->where('ID_PADRE', $item->id_modulo)->get();
                        $query = "SELECT DISTINCT
                            D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                        FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                        WHERE A.ID_ROL = B.ID_ROL
                        AND B.ID_ROL = C.ID_ROL
                        AND C.ID_MODULO = D.ID_MODULO
                        AND A.ID_PERSONA = $id_user
                        AND D.ID_PADRE = $item->id
                        ORDER BY ORDEN ";
                        $oChildren = DB::select($query);
                        foreach ($oChildren as $key_Ch => $item_Ch) {
                            $children[] = ['id' => $item_Ch->id, 'title' => $item_Ch->title, 'link' => $item_Ch->link, 'order' => $item_Ch->priority, 'icon' => $item_Ch->icon];
                        }

                        $data[] = ['id' => $item->id, 'title' => $item->title, 'link' => $item_Ch->link, 'order' => $item->priority, 'icon' => $item->icon, 'children' => $children];
                    }

                    $item_padre->children = $data;
                }


                if ($oQuery) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $id_user;
                    $jResponse['data'] = ['items' => $oQuery];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function userModuless(){//no usar
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $query = "SELECT 
                            DISTINCT D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                    FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                    WHERE A.ID_ROL = B.ID_ROL 
                    AND B.ID_ROL = C.ID_ROL
                    AND C.ID_MODULO = D.ID_MODULO
                    AND A.ID_PERSONA = $id_user
                    AND D.ID_PADRE = 2 ORDER BY D.ORDEN ";            
                    $oQuery = DB::select($query);

            foreach ($oQuery as $key => $item_padre) {
                $query = "SELECT DISTINCT
                                    D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                                FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                                WHERE A.ID_ROL = B.ID_ROL
                                AND B.ID_ROL = C.ID_ROL
                                AND C.ID_MODULO = D.ID_MODULO
                                AND A.ID_PERSONA = $id_user
                                AND D.ID_PADRE = $item_padre->id ";
                            $oModule = DB::select($query);
                            $data = array();
                            $children = array();

                foreach ($oModule as $key => $item) {
                    $children = null;
                    $query = "SELECT DISTINCT
                                D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon
                            FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                            WHERE A.ID_ROL = B.ID_ROL
                            AND B.ID_ROL = C.ID_ROL
                            AND C.ID_MODULO = D.ID_MODULO
                            AND A.ID_PERSONA = $id_user
                            AND D.ID_PADRE = $item->id
                            ORDER BY ORDEN ";
                    $oChildren = DB::select($query);
                    foreach ($oChildren as $key_Ch => $item_Ch) {
                        $children[] = ['id' => $item_Ch->id, 'title' => $item_Ch->title, 'link' => $item_Ch->link, 'order' => $item_Ch->priority, 'icon' => $item_Ch->icon];
                    }
                    $data[] = ['id' => $item->id, 'title' => $item->title, 'link' => $item->link, 'order' => $item->priority, 'icon' => $item->icon, 'children' => $children];
                }
                $item_padre->children = $data;
            }

            if ($oQuery) {
                $jResponse['success'] = true;
                $jResponse['message'] = $id_user;
                $jResponse['data'] = ['items' => $oQuery];
            }
	    }
        return response()->json($jResponse);
    }
    public function userModule(){
        $jResponse = [
            'success' => false,
            'message' => 'Resource No Authorizated',
	    'data' => []
        ];

	$token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        //$token = $params->token;

        if ($token) {
            session_id($token);
            session_start();

            $bindings = [
             'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);

	    $valida = $result[0];

	    
	    if( $valida->active == 'SI'  ){
                $id_user = $valida->id_user;
                $id_entidad = $valida->id_entidad;
                $query = "SELECT 
                                DISTINCT D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon,D.COMENTARIO, D.TIPO_MODULO
                        FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                        WHERE A.ID_ROL = B.ID_ROL 
                        AND B.ID_ROL = C.ID_ROL
                        AND C.ID_MODULO = D.ID_MODULO
                        AND A.ID_PERSONA = $id_user
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND D.ID_PADRE = 2 ORDER BY D.ORDEN ";            
                $oQuery = DB::select($query);
                if ($oQuery) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $id_user;
                    $jResponse['data'] = $oQuery;
                    $jResponse['entidad'] = $id_entidad;
                    
                }
	    }
        }

        return response()->json($jResponse);
    }

    public function userModuleBK(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida === 'SI'){
                $codigo_padre = $request->codigo_padre;
                $query = "SELECT 
                                DISTINCT D.ID_MODULO as id, D.NOMBRE as title, D.URL as link, D.ORDEN as priority, D.IMAGEN as icon,D.COMENTARIO, D.TIPO_MODULO
                        FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                        WHERE A.ID_ROL = B.ID_ROL 
                        AND B.ID_ROL = C.ID_ROL
                        AND C.ID_MODULO = D.ID_MODULO
                        AND A.ID_PERSONA = $id_user
                        AND A.ID_ENTIDAD = ".$id_entidad."
                        AND D.ID_PADRE = (SELECT X.ID_MODULO FROM ELISEO.LAMB_MODULO X WHERE X.CODIGO='$codigo_padre')
                        ORDER BY D.ORDEN ";            
                $oQuery = DB::select($query);
                if ($oQuery) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $id_user;
                    $jResponse['data'] = $oQuery;
                    $jResponse['entidad'] = $id_entidad;
                }
	    }
        return response()->json($jResponse);
    }
    public function userModuleChildren(Request $request, $id_parent){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida === 'SI'){
            // $id_user = $valida->id_user;
            $query = "SELECT DISTINCT
                        D.ID_MODULO, D.NOMBRE, D.URL, D.ORDEN,D.TIPO,D.ESTADO, D.IMAGEN
                FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                WHERE A.ID_ROL = B.ID_ROL 
                AND B.ID_ROL = C.ID_ROL
                AND C.ID_MODULO = D.ID_MODULO
                AND A.ID_PERSONA = $id_user
                AND A.ID_ENTIDAD = $id_entidad
                AND D.ID_PADRE = $id_parent 
                ORDER BY D.ORDEN " ;
            $oModule = DB::select($query); 
            $data = array();
            $children = array();

            // $jResponse['success'] = true;
            // $jResponse['message'] = 'OK';
            // $jResponse['data'] = ['items' => $data];
            foreach ($oModule as $key => $item) {

                $children = null;

                //$oChildren = DB::table('LAMB_MODULO')->select('ID_MODULO', 'NOMBRE', 'URL', 'ORDEN', 'TIPO', 'ESTADO','IMAGEN')->where('ID_PADRE', $item->id_modulo)->get();
                $query = "SELECT DISTINCT
                            D.ID_MODULO, D.NOMBRE, D.URL, D.ORDEN,D.TIPO,D.ESTADO, D.IMAGEN
                        FROM LAMB_USUARIO_ROL A, LAMB_ROL B, LAMB_ROL_MODULO C, LAMB_MODULO D
                        WHERE A.ID_ROL = B.ID_ROL 
                        AND B.ID_ROL = C.ID_ROL
                        AND C.ID_MODULO = D.ID_MODULO
                        AND A.ID_ENTIDAD = $id_entidad
                        AND A.ID_PERSONA = $id_user
                        AND D.ID_PADRE = $item->id_modulo 
                        ORDER BY ORDEN ";
                $oChildren = DB::select($query);
                foreach ($oChildren as $key_Ch => $item_Ch) {
                    $children[] = ['id_modulo'=> $item_Ch->id_modulo, 'title' => $item_Ch->nombre, 'type' => $item_Ch->tipo, 'link' => $item_Ch->url, 'priority' => $item_Ch->orden, 'icon' => $item_Ch->imagen];
                }
 
                $data[] = ['id_modulo'=> $item->id_modulo, 'title' => $item->nombre, 'icon' => $item->imagen, 'url' => $item->url, 'type' => $item->tipo, 'priority' => $item->orden, 'children' => $children];
            }

            if ($oModule) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
            }
        }
        return response()->json($jResponse);
    }
    public function prueba(){

        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];


        return response()->json($jResponse);

    }
    public function resetPasswordSendMail($email){
        $jResponse = [
            'success' => false,
            'message' => '',
            'data' => []
        ];
        $code = "401";        
        $data = DB::table('MOISES.PERSONA_VIRTUAL')
                ->join('USERS', 'USERS.ID', '=', 'MOISES.PERSONA_VIRTUAL.ID_PERSONA')
                ->select('MOISES.PERSONA_VIRTUAL.id_persona')->where('MOISES.PERSONA_VIRTUAL.DIRECCION', $email)->where('MOISES.PERSONA_VIRTUAL.ID_TIPOVIRTUAL',1)->get();
                
        foreach ($data as $key => $item){
            $id_persona = $item->id_persona;
        }        
        if(count($data)>0){   
            $token = str_random(64);
            $sql = "INSERT INTO USERS_RESET_PASSWORD (TOKEN,ID,FECHA) VALUES ('$token',$id_persona,sysdate) ";    
            DB::insert($sql);                                  
            $output = shell_exec("php /public/utils/envia_mail_2.php mail=$email token=$token");            
            if($output == "success"){  
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";                    
                $jResponse['data'] = ['email_sended' => true];
                $code = "200";  
            }else{
                $jResponse['success'] = true;
                $jResponse['message'] = "email not send";                        
                $jResponse['data'] = ['email_sended' => false];
                $code = "404";
            }
        }else{
            $jResponse['message'] = "The item does not exist";                        
            $jResponse['data'] = [];
            $code = "404";
        }
        
        return response()->json($jResponse,$code);
    }
    public function resetPasswordValidaToken($token){
        $jResponse = [
            'success' => false,
            'message' => '',
            'data' => []
        ];
        $code = "401";
        $hh = "0";        
        try{            
            $sql = "SELECT 
                count(*) as cant
                FROM USERS_RESET_PASSWORD
                WHERE FECHA > systimestamp - INTERVAL '12' HOUR
                AND token = '$token' ";        
            $data = DB::select($sql);
            foreach ($data as $key => $item){
                $hh = $item->cant;                
            }
            if($hh == 1){
                $jResponse['success'] = true;
                $jResponse['message'] = "Token Ok";                    
                $jResponse['data'] = [];
                $code = "200"; 
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = "Token Expirado";                    
                $jResponse['data'] = [];
                $code = "401"; 
            }
        }catch(Exception $e){
            dd($e->getMessage());
        }
        return response()->json($jResponse,$code);
    }
    public function resetPassword(){
        $jResponse = [
            'success' => false,
            'message' => '',
            'data' => []
        ];
        $code = "401";
        $hh = "0";
        $params = json_decode(file_get_contents("php://input"));        
        $token = $params->data->token;
        $senha = $params->data->senha;
        $sql = "SELECT 
                count(*) as cant, nvl(max(id),0) id
                FROM USERS_RESET_PASSWORD
                WHERE 
                FECHA > systimestamp - INTERVAL '12' HOUR
                AND token = '$token' ";
        $data = DB::select($sql);
        foreach ($data as $key => $item){
            $id = $item->id;
            $hh = $item->cant;
        }
        if($hh == 1){
            try{
                DB::table('USERS')
                ->where('ID', $id)
                ->update([
                    'CONTRASENHA' => bcrypt($senha)
                ]); 
                $jResponse['message'] = "The item was updated successfully";
            }catch(Exception $e){                
                $jResponse['message'] = $e->getMessage();
            }
            $jResponse['success'] = true;            
            $jResponse['data'] = [];
            $code = "200";
        }else{
            $jResponse['success'] = true;
            $jResponse['message'] = "Token Expired";
            $jResponse['data'] = [];
            $code = "404";
        } 
        return response()->json($jResponse,$code);
    }
    public function userModuleActions($id_modulo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];            
            try{    
                $id_rol = "0";
                $sql = "SELECT ID_ROL 
                        FROM LAMB_USUARIO_ROL
                        WHERE ID_ENTIDAD = $id_entidad
                        AND ID_PERSONA = $id_user ";
                $data = DB::select($sql);
                foreach ($data as $key => $item){
                    $id_rol = $id_rol.",".$item->id_rol;                    
                }
                $sql = "SELECT 
                                A.ID_MODULO, B.ID_ROL,X.NOMBRE ROL,A.NOMBRE ACCESO,D.CLAVE,D.METODO,A.CODIGO
                        FROM LAMB_MODULO A, LAMB_ROL_MODULO B, LAMB_ROL X, LAMB_ROL_MODULO_ACCION C, LAMB_ACCION D
                        WHERE A.ID_MODULO = B.ID_MODULO
                        AND B.ID_ROL = X.ID_ROL
                        AND B.ID_ROL = C.ID_ROL
                        AND A.ID_MODULO = C.ID_MODULO
                        AND A.ID_MODULO = D.ID_MODULO
                        AND C.ID_ACCION = D.ID_ACCION
                        -- AND A.ID_MODULO = ".$id_modulo."
                        AND A.CODIGO = '$id_modulo'
                        AND B.ID_ROL in ($id_rol)";
                $data = DB::select($sql);                                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
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



    public function LoginOauthdjMovil(Request $r) {

        $caduca = 'S';
        $url = 'https://oauth.upeu.edu.pe/oauth/token/';
        $url_me = 'https://oauth.upeu.edu.pe/api/me/';
        $grant_type = 'password';
        $client_id = 'b2UPtgiYi3j057sSbU80wHsHKkyNNeblhd1dQfrq';
        $client_secret = 'qkrsu679Wx1dxAnIaABJ2JxP68c2jIv8REMt1QSzOPn5SGsBGX3YmaBLo6FmiCmoQ8l3PQLNXc4ZuAsVDCaDB3I4IG5PycMzS0ApPhJL2I3XCDdIRpC0ymQcWK4DKI4p';

        $username = $r->input('username');
        $password = $r->input('password');
        $no_caduca = $r->input('no_caduca');

        if ($no_caduca) {
            $caduca = 'N';
        }

        $response = [
            'success' => false
        ];

        $data = array(
            'grant_type'=>$grant_type,
            'client_id'=>$client_id,
            'client_secret'=>$client_secret,
            'username'=>$username,
            'password'=>$password);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $login_oauth = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $response['status'] = $http_status;


        if ($http_status == 200) {

            $access_token = json_decode($login_oauth)->access_token;

            $curl_me = curl_init($url_me);
            curl_setopt($curl_me, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token.''));
            curl_setopt($curl_me, CURLOPT_RETURNTRANSFER, true);

            $me = curl_exec($curl_me);

            $me_status = curl_getinfo($curl_me, CURLINFO_HTTP_CODE);

            if ($me_status == 200) {

                try {

                    $get_username = json_decode($me)->username;
                    $persona_id = null;

                    $dir = url('');
                    $user = DB::table('USERS')->select('USERS.ID','USERS.IMAGEN_URL','USERS.acceso_firma')->where('USERS.EMAIL','=',$get_username)->first();

                    if ($user) {
                        $persona_id = $user->id;

                        if (($get_username==$username) and $persona_id){

                            $token=$this->getSession();
                            
                            $token = $token.'2';
                            
                            $msg_error = '';
                            for($x=1;$x<=300;$x++){
                                $msg_error .= "0";
                            }

                            $error = 0;
                            $pdo = DB::getPdo();
                            $stmt = $pdo->prepare("begin spc_user_session_login(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR, :P_MSJERROR, :P_CADUCA); end;");
                            $stmt->bindParam(':P_ID_PERSONA', $persona_id, PDO::PARAM_INT);
                            $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
                            $stmt->bindParam(':P_TOKEN_OAUTH', $access_token, PDO::PARAM_STR);
                            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                            $stmt->bindParam(':P_MSJERROR', $msg_error, PDO::PARAM_STR);
                            $stmt->bindParam(':P_CADUCA', $caduca, PDO::PARAM_STR);
                            $stmt->execute(); 


                            if($error==0){
                                $objEnt = DB::table('CONTA_ENTIDAD_USUARIO')
                                ->select('id_entidad')
                                ->where('ID_PERSONA', $persona_id)
                                ->where('ESTADO', 1)
                                ->first();
                                $id_entidad = "";
                                $id_depto = "";
                                if($objEnt){
                                    $id_entidad = $objEnt->id_entidad;
                                    $objDep = DB::table('LAMB_USERS_DEPTO as A')
                                            ->select('A.ID_DEPTO')
                                            ->join('CONTA_ENTIDAD_DEPTO as B', function ($join) {
                                                $join->on('A.ID_ENTIDAD', '=', 'B.ID_ENTIDAD')
                                                    ->on('A.ID_DEPTO', '=', 'B.ID_DEPTO');
                                            })
                                            ->where('A.ID', '=', $persona_id)
                                            ->where('A.ID_ENTIDAD', '=', $objEnt->id_entidad)
                                            ->where('A.ESTADO', '=', 1)
                                            ->where('B.ES_EMPRESA', '=', '1')
                                            ->first();
                                    if($objDep){
                                        $id_depto= $objDep->id_depto;
                                    }

                                }

                                $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $persona_id)->first();
                                $name = $oPerson->nombre;
                                $first_name = $oPerson->paterno;
                                $last_name = $oPerson->materno;
                                $full_name = $name . ' ' . $first_name . ' ' . $last_name;

                                $response['success'] = true;
                                $response['message'] = 'Acceso Correcto';
                                $response['data'] = [
                                    'token' => $token,
                                    'id_persona'=>$persona_id,
                                    'fullname'=>$full_name,
                                    'access_token'=>$access_token,
                                    'imagen_url'=>$dir.'/'.$user->imagen_url,
                                    'id_entidad'=>$id_entidad,
                                    'id_depto'=>$id_depto,
                                    'acceso_firma'=>$user->acceso_firma
                                ];
                            }else{
                                $response['success'] = false;
                                $response['message'] = $msg_error;
                                $response['data'] = [];
                            }
                            /*elseif($error==2){
                                $response['success'] = false;
                                $response['message'] = 'No Tiene Contrato Activo, o Terminó sus Contrato';
                                $response['data'] = [];
                            }elseif($error==3){
                                $response['success'] = false;
                                $response['message'] = 'Entidad Asignada o abscrita, no tiene asignado Acceso';
                                $response['data'] = [];
                            }elseif($error==4){
                                $response['success'] = false;
                                $response['message'] = 'No tiene Entidad(des) Asignada(s) por Default';
                                $response['data'] = [];
                            }elseif($error==5){
                                $response['success'] = false;
                                $response['message'] = 'No tiene Departamento por Default';
                                $response['data'] = [];
                            }elseif($error==6){
                                $response['success'] = false;
                                $response['message'] = 'No Tiene Asignado ROl para esta Entidad';
                                $response['data'] = [];
                            }
                            else{
                                $response['success'] = false;
                                $response['message'] = 'Tiene varios departamentos por Defualt';
                                $response['data'] = [];
                            }*/
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Error al validar usuario y persona id en lamb upeu';
                            $response['data'] = [];
                        }

                    } else {
                        $response['success'] = false;
                        $response['message'] = 'No se encontro el usuario en lamb upeu';
                        $response['data'] = [];
                    }

                } catch(Exception $e){
                    $response['success'] = false;
                    $response['message'] = "ORA: ".$e->getMessage();
                    $response['data'] = [];

                }

            } else {
                $response['success'] = false;
                $response['message'] = 'No se encontro el usuario en oauth';
                $response['data'] = [];
            }

            curl_close($curl_me);

        }else {
            $response['success'] = false;
            $response['message'] = 'Usuario o Access Token incorrecto !! ';
            $response['data'] = json_decode($login_oauth);

        }

        curl_close($curl);        

        return response()->json($response, 200);
    }




    public function UserModuleMenuMovil() {

        // Metodo para listar el menu y modulos de usuario para aplicacion movil

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'lista de modulos para movil';
            $id_user = $resp["id_user"];
            $id_entidad = $resp["id_entidad"];


            $documento = DB::table('MOISES.PERSONA_NATURAL')
            ->select('MOISES.PERSONA_NATURAL.NUM_DOCUMENTO')
            ->where('MOISES.PERSONA_NATURAL.ID_PERSONA','=',$id_user)
            ->first();


            $sql = "
            with dataFull as (
              select distinct D.ID_MODULO, D.ID_PADRE from LAMB_MODULO D
              WHERE LEVEL=3
              start with D.TIPO = '1'
              connect by nocycle prior D.ID_PADRE = D.ID_MODULO
              UNION
              select distinct D.ID_MODULO, (select distinct m.ID_MODULO from LAMB_MODULO m
              WHERE LEVEL=3
              start with m.TIPO = '1' AND m.ID_MODULO=D.ID_MODULO
              connect by nocycle prior m.ID_PADRE = m.ID_MODULO) ID_PADRE  from LAMB_MODULO D
              WHERE D.TIPO = '1'
              )
              select DISTINCT A.ID_MODULO as id, A.NOMBRE as title, A.URL as link, A.ORDEN, A.ICON, A.COMPONENT, F.ID_PADRE as ROOT_ID
              from LAMB_MODULO A
                INNER JOIN dataFull F on (A.ID_MODULO = F.ID_MODULO)
                INNER JOIN LAMB_ROL_MODULO B ON (A.ID_MODULO = B.ID_MODULO)
                INNER JOIN LAMB_ROL C ON (C.ID_ROL = B.ID_ROL)
                INNER JOIN LAMB_USUARIO_ROL D ON (D.ID_ROL = C.ID_ROL)
              WHERE D.ID_PERSONA = $id_user
                AND D.ID_ENTIDAD = $id_entidad";

            $resp['data'] = DB::select($sql);
            $resp['documento'] = $documento;
        }

        return response()->json($resp, $resp["code"]);
    }


    public function VersionAppMovil($code) {
        $resp = null;
        $platform = $this->request->query('platform');

        if ($code) {
            $resp = DB::table('LAMB_APPVERSION')
            ->where('LAMB_APPVERSION.CODE','=',$code)
            ->where('LAMB_APPVERSION.PLATFORM','LIKE',$platform)
            ->get()
            ->first();            
        }
        return response()->json($resp, 200);

    }


    public function UserInfoDevice() {

        /*
        Root principal de app
        Datos usuario para movil lamb
        Permisos

        - verifica si puede guardar la informacion del dispositivo
        - trae el UUID del dispositivo del usuario para su control de asistencia y como token para conexion socket
        - lista de responsebles del area al cual pertenece el usuario (para notificar al responsable de area su asistencia)
        - TODO (PERMISOS)      


        */



        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('get')){

            $uuid = $this->request->query('uuid');

            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Datos user device';


            // DEVICE

            $data['can_save_device'] = true;
            $data['area_resp'] = [];

            $device = DB::table('APS_USER_DEVICE')
            ->select(
                'APS_USER_DEVICE.ID_PERSONA',
                'APS_USER_DEVICE.UUID',
                'APS_USER_DEVICE.CAN_RESET_TOUCH_ID',
                'APS_USER_DEVICE.NOT_ASSIT')
            ->where('APS_USER_DEVICE.UUID','=',$uuid)
            ->get()
            ->first();


            // Verifica si el dispositivo le pertenece a otra persona

            if ($device == null) {

                $deviceLost = DB::table('APS_USER_DEVICE')
                ->select('APS_USER_DEVICE.ID_PERSONA')
                ->where('APS_USER_DEVICE.UUID','=',$uuid)
                ->get()
                ->first();

                if ($deviceLost) {

                    if ($deviceLost->id_persona!=$resp['id_user']) {
                        $data['can_save_device'] = false;
                        $resp['message'] = 'El dispositivo no puede ser asociado a su cuenta para su control de asistencia. Ésto sucede porque el dispositivo ya esta asociado a otra cuenta.';
                    }

                } else {
                    $resp['message'] = 'Desea asociar el dispositivo  móvil para su control de asistencia ?';
                }                

            } else {
                $data['can_save_device'] = false;
            }

            $data['device'] = $device;


            // END

            // RESPONSABLES

            if ($device) {

                $responsables = DB::table('APS_TRABAJADOR')
                ->select(
                    'ORG_AREA_RESPONSABLE.ID_PERSONA',
                    'APS_USER_DEVICE.UUID')
                ->join('ORG_SEDE_AREA', 'APS_TRABAJADOR.ID_DEPTO', '=', 'ORG_SEDE_AREA.ID_DEPTO')
                ->join('ORG_AREA_RESPONSABLE', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'ORG_AREA_RESPONSABLE.ID_SEDEAREA')
                ->leftJoin('APS_USER_DEVICE', 'ORG_AREA_RESPONSABLE.ID_PERSONA', '=', 'APS_USER_DEVICE.ID_PERSONA')
                ->where('APS_TRABAJADOR.ID_PERSONA','=',$resp['id_user'])
                ->where('APS_TRABAJADOR.ID_ENTIDAD','=',$resp['id_entidad'])
                ->where('ORG_SEDE_AREA.ID_ENTIDAD','=',$resp['id_entidad'])
                ->get();

                $data['area_resp'] = $responsables;

            }

            // END
            

            $resp['data'] = $data;

        }

        return response()->json($resp, $resp["code"]);
    }




    public function DatosPersona() {

        // Datos personales en Movil (APP LAMB)

        $resp = GlobalMethods::authorizationLamb($this->request);
        if($resp["valida"]=='SI' && $this->request->isMethod('get')){
            $resp['message'] = 'Ok';
            $resp['code'] = '200';
            $resp['success'] = true;

            $resp['data'] = DB::table('MOISES.PERSONA')->select(
                'MOISES.PERSONA.ID_PERSONA',
                'MOISES.PERSONA.NOMBRE',
                'MOISES.PERSONA.PATERNO',
                'MOISES.PERSONA.MATERNO')
            ->where('MOISES.PERSONA.ID_PERSONA','=',$resp['id_user'])
            ->first();

            $resp['device'] =DB::table('APS_USER_DEVICE')
                ->select(
                    'APS_USER_DEVICE.ID_PERSONA',
                    'APS_USER_DEVICE.UUID',
                    'APS_USER_DEVICE.CAN_RESET_TOUCH_ID',
                    'APS_USER_DEVICE.NOT_ASSIT',
                    'APS_USER_DEVICE.RE_ASIGN')
                ->where('APS_USER_DEVICE.ID_PERSONA','=',$resp['id_user'])
                ->where('APS_USER_DEVICE.STATE','=',1)
                ->first();

        }

        return response()->json($resp, $resp["code"]);
    }


    public function ChangePasswordMovil() {

        // Cambio de contraseña de oauthdj (APP LAMB)


        $url = 'https://oauth.upeu.edu.pe/api/change_password/';

        $resp = GlobalMethods::authorizationLamb($this->request);
        if($resp["valida"]=='SI' && $this->request->isMethod('post')){

            $data = $this->request->input();
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->request->input('access_token').''));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            $result = json_decode($result, true);
            $resp['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($resp['code'] == 200) {

                $id_persona = $resp['id_user'];
                $uuid = $this->request->input('uuid');

                if ($id_persona && $uuid) {
                    DB::table('APS_USER_DEVICE')
                    ->where('APS_USER_DEVICE.UUID','=',$uuid)
                    ->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
                    ->update(['CHANGE_PASSWORD_DEFAULT'=>1]);
                }

                $resp['success'] = true;
                $resp['message'] = 'Se cambio correctamente su nueva contraseña.';
            } else {
                $resp['success'] = false;
                $resp['message'] = $result['detail'];
            }

            curl_close($curl);
        }

        return response()->json($resp, $resp["code"]);
    }


    public function LogoutSession() {

        $url = 'https://oauth.upeu.edu.pe/accounts/logout/';

        // Cierra sesion de OAUTH Y LAMB (Puede ser usado desde la web o tambien de la app)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('post')){

            // ELIMINA TOKEN DE LAMB
            DB::table('users_session')
            ->where('users_session.token','=',$resp['token'])
            ->where('users_session.id_user','=',$resp['id_user'])
            ->where('users_session.id_entidad','=',$resp['id_entidad'])
            ->where('users_session.id_depto','=',$resp['id_depto'])
            ->delete();
            session_id($resp['token']);
            session_destroy();


            // ELIMINA TOKEN DE OAUTH
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->request->input('access_token').''));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $logout = curl_exec($curl);
            $logout_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($logout_status == 200) {
                $resp["code"] = '200';
                $resp['success'] = true;
                $resp['message'] = 'Se cerro la sesion correctamente de oauth / lamb.';
            }


        }

        return response()->json($resp, $resp["code"]);
    }




    public function validTokenCallback() {
        $url = env('oauth_instrospect');
        // $url='https://oauth.upeu.edu.pe/api/me';

        $resp = [
            'success' => false,
            'message' => 'none'
        ];

        try{
            $params = json_decode(file_get_contents("php://input"));
            $access_token = $params->access_token;

            $context = stream_context_create(
                array('http' => array('header'  => "Authorization: Bearer $access_token"))
            );

            $respuesta = file_get_contents($url, false, $context);
            $user =  (object) json_decode($respuesta, true);

            $person = DB::table('USERS')->select('ID')->where('USERS.EMAIL','=', $user->username);

            if ($person->exists()) {

                $person = $person->first();

                $person_id = $person->id;

                $token = $this->getSession();

                $token = $token.'2';

                $error = 0;
                $pid_modulo = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin spc_user_session_login(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR); end;");
                // $stmt = $pdo->prepare("begin spc_user_session_login_prueba(:P_ID_PERSONA, :P_TOKEN, :P_TOKEN_OAUTH, :P_ERROR, :P_ID_MODULO); end;");
                $stmt->bindParam(':P_ID_PERSONA', $person_id, PDO::PARAM_INT);
                $stmt->bindParam(':P_TOKEN', $token, PDO::PARAM_STR);
                $stmt->bindParam(':P_TOKEN_OAUTH', $access_token, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                // $stmt->bindParam(':P_ID_MODULO', $pid_modulo, PDO::PARAM_INT);
                $stmt->execute();

                if($error==0){
                    $resp['success'] = true;
                    $resp['message'] = 'Acceso Correcto';
                    $resp['data'] = ['token' => $token]; 
                }elseif($error==1){
                    $resp['success'] = false;
                    $resp['message'] = 'No tiene Entidad Asignada';
                    $resp['data'] = [];
                }elseif($error==2){
                    $resp['success'] = false;
                    $resp['message'] = 'No Tiene Contrato Activo, o Terminó sus Contrato';
                    $resp['data'] = [];
                }elseif($error==3){
                    $resp['success'] = false;
                    $resp['message'] = 'Entidad Asignada o abscrita, no tiene asignado Acceso';
                    $resp['data'] = [];
                }elseif($error==4){
                    $resp['success'] = false;
                    $resp['message'] = 'No tiene Entidad(des) Asignada(s) por Default';
                    $resp['data'] = [];
                }elseif($error==5){
                    $resp['success'] = false;
                    $resp['message'] = 'No tiene Departamento por Default';
                    $resp['data'] = [];
                }elseif($error==6){
                    $resp['success'] = false;
                    $resp['message'] = 'No Tiene Asignado ROl para esta Entidad';
                    $resp['data'] = [];
                }
                else{
                    $resp['success'] = false;
                    $resp['message'] = 'Tiene varios departamentos por Defualt';
                    $resp['data'] = [];
                }


            }else {

                $resp['success'] = false;
                $resp['message'] = "Usuario o Access Token incorrecto.";
                $resp['data'] = [];

            }

             
         }catch(Exception $e){
            $resp['success'] = false;
            $resp['message'] = "ORA: ".$e->getMessage();
            $resp['data'] = [];
        }
    
        return response()->json($resp);
    }


}