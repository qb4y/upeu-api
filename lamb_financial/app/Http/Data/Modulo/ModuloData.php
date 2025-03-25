<?php
namespace App\Http\Data\Modulo;
use Exception;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuloData {
    // private $request;

    // public function __construct(Request $request){
    //     $this->request = $request;
    // }
    // public static function get_modulo(){
    public static function getModuloRoot(){
        $getModulo = DB::table('LAMB_MODULO')
                    ->select('ID_MODULO','ID_PADRE','NIVEL','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO','ID_TIPOPLATAFORMA')
                    // ->where('NIVEL', 1)
                    ->whereNull('ID_PADRE')
                    ->orderBy('orden')
                    ->get();        
        return $getModulo;
    }

    public static function getModuloChildrenToLambFinancial(){ // ID 2
        $getModulo = DB::table('LAMB_MODULO')
                    ->select('ID_MODULO','ID_PADRE','NIVEL','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO','ID_TIPOPLATAFORMA')
                    ->where('NIVEL', 1)
                    // ->whereNull('ID_PADRE')
                    ->where('ID_PADRE', 2)
                    ->orderBy('orden')
                    ->get();        
        return $getModulo;
    }
    
    public static function showModules($id_modulo){
        $getModulo = DB::table('LAMB_MODULO')
                    ->select('ID_MODULO','ID_PADRE','NIVEL','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO', 'ACCESOXNIVEL','IS_GROUP','IS_ABSOLUTE','ID_TIPOPLATAFORMA')
                    ->where('ID_MODULO', $id_modulo)
                    ->get();        
        return $getModulo;
    } 
    public static function listModulesChildrens($id_parent){
        $getModulo = DB::table('LAMB_MODULO')
                    ->select('ID_MODULO','ID_PADRE','NIVEL','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO','IS_GROUP','IS_ABSOLUTE','ID_TIPOPLATAFORMA')
                    ->where('ID_PADRE', $id_parent)
                    ->orderBy('orden')
                    ->get();        
        return $getModulo;
    }
    public static function addModules($id_padre,$nombre,$nivel,$url,$imagen,$orden,$es_activo,$codigo, $accesoxnivel, $is_group, $is_absolute,$id_tipoplataforma){
        DB::table('LAMB_MODULO')->insert(
                    array('ID_PADRE' => $id_padre, 
                        'ID_PRIVILEGIO' => 1,
                        'NOMBRE' => $nombre,
                        'NIVEL' => $nivel,
                        'URL' => $url,
                        'IMAGEN' => $imagen,
                        'ORDEN' => $orden,
                        'ESTADO' => 1,
                        'ES_ACTIVO' => $es_activo,
                        'CODIGO' => $codigo,
                        'ACCESOXNIVEL' => $accesoxnivel,
                        'IS_GROUP' => $is_group,
                        'ID_TIPOPLATAFORMA' => $id_tipoplataforma,
                        'IS_ABSOLUTE' => $is_absolute)
                );
        $query = "SELECT 
                        MAX(ID_MODULO) ID_MODULO
                FROM LAMB_MODULO ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_modulo = $id->id_modulo;
        }
        $getModulo = DB::table('LAMB_MODULO')->select('ID_MODULO','ID_PADRE','NIVEL','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO','IS_GROUP','IS_ABSOLUTE')->where('ID_MODULO', $id_modulo)->get();        
        return $getModulo;
    }
    public static function updateModules($id_modulo,$id_padre,$nombre,$nivel,$url,$imagen,$orden,$es_activo,$codigo, $accesoxnivel, $is_group,$is_absolute,$id_tipoplataforma){
        DB::table('LAMB_MODULO')
            ->where('ID_MODULO', $id_modulo)
            ->update([
                'ID_PADRE' => $id_padre,
                'NOMBRE' => $nombre,
                'NIVEL' => $nivel,
                'URL' => $url,
                'IMAGEN' => $imagen,
                'ORDEN' => $orden,
                'ES_ACTIVO' => $es_activo,
                'CODIGO' => $codigo,
                'ACCESOXNIVEL' => $accesoxnivel,
                'IS_GROUP' => $is_group,
                'ID_TIPOPLATAFORMA' => $id_tipoplataforma,
                'IS_ABSOLUTE' => $is_absolute
            ]);
        $getModulo = DB::table('LAMB_MODULO')->select('ID_MODULO','ID_PADRE','NOMBRE','URL','IMAGEN','ORDEN','ESTADO','ES_ACTIVO','CODIGO','IS_GROUP','IS_ABSOLUTE')->where('ID_MODULO', $id_modulo)->get();        
        return $getModulo;
    }
    public static function deleteModules($id_modulo){
        DB::table('LAMB_MODULO')->where('ID_MODULO', '=', $id_modulo)->delete();
    }
    public static function show_modulo($id_rol,$id_modulo){
        $query = "SELECT 
                        ID_MODULO,
                        ID_PADRE, 
                        NOMBRE,
                        LEVEL,
                        (SELECT COUNT(B.ID_MODULO) FROM LAMB_ROL_MODULO B WHERE B.ID_MODULO = A.ID_MODULO AND B.ID_ROL = $id_rol) AS ASIGNADO,
                        CODIGO
                FROM LAMB_MODULO A
                START WITH ID_MODULO = $id_modulo
                CONNECT BY PRIOR ID_MODULO = ID_PADRE
                ORDER SIBLINGS BY ID_MODULO,NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    } 
    public static function existUsuario($id){        
        $getUser = DB::table('USERS')->select('ID')->where('ID', $id)->get(); 
        return $getUser;
    }
    public static function searchPerson($person){
        $query = "SELECT A.ID_PERSONA,A.NOMBRE||' '||A.PATERNO||' '||A.MATERNO NOMBRES,B.NUM_DOCUMENTO
                    FROM MOISES.PERSONA A, MOISES.PERSONA_DOCUMENTO B
                  WHERE A.ID_PERSONA = B.ID_PERSONA
                  AND ( UPPER (NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE UPPER ('%".$person."%')
                  OR B.NUM_DOCUMENTO LIKE UPPER ('%".$person."%')) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function existEntidadUsuario($id_entidad,$id_persona){
        $query = "SELECT ID_ENTIDAD
                    FROM CONTA_ENTIDAD_USUARIO
                    WHERE ID_ENTIDAD = $id_entidad
                    AND ID_PERSONA = $id_persona ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showExistEntidadUsuario($id_persona){
        $query = "SELECT ID_ENTIDAD
                    FROM CONTA_ENTIDAD_USUARIO
                    WHERE ID_PERSONA = $id_persona 
                    AND ESTADO = 1 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showUserRol($id_entidad,$id){
        $datos=array();
        $query = "SELECT 
                        ID_ROL,         
                        NOMBRE,        
                        (SELECT COUNT(B.ID_ROL) FROM LAMB_USUARIO_ROL B WHERE B.ID_ROL = A.ID_ROL AND B.ID_PERSONA = $id AND ID_ENTIDAD = $id_entidad) AS ASIGNADO
                FROM LAMB_ROL A
                WHERE ESTADO = 1
                ORDER BY ID_ROL,NOMBRE ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $item = array();
            $item["id_rol"] = $id->id_rol;
            $item["nombre"] = $id->nombre;
            if($id->asignado==1){
                $item["asignado"] = true;
            }else{
                $item["asignado"] = false;
            }
            $datos[] = $item;
        }
        return $datos;
    }
    public static function showUser(){
        $query = "SELECT 
                            A.ID,
                            B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO NOMBRES,
                            A.EMAIL,
                            C.NOMBRE PERFIL
                FROM USERS A, MOISES.PERSONA B, LAMB_PERFIL C
                WHERE A.ID = B.ID_PERSONA
                AND A.ID_PERFIL = C.ID_PERFIL
                ORDER BY ID,NOMBRES ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function verUser($id){
        /*$query = "SELECT 
                            A.ID,
                            B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO NOMBRES,
                            A.EMAIL,
                            A.NAME,
                            C.NOMBRE PERFIL,
                            C.ID_PERFIL,
                            D.ID_ENTIDAD
                FROM USERS A, MOISES.PERSONA B, LAMB_PERFIL C, CONTA_ENTIDAD_USUARIO D
                WHERE A.ID = B.ID_PERSONA
                AND A.ID_PERFIL = C.ID_PERFIL
                AND A.ID = D.ID_PERSONA
                AND A.ID = $id
                ORDER BY ID,NOMBRES ";*/
        $query = "SELECT 
                            A.ID,
                            B.NOMBRE AS FIRST_NAME,
                            B.PATERNO||' '||B.MATERNO AS LAST_NAME,
                            B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO NOMBRES,
                            A.EMAIL,
                            A.NAME,
                            C.NOMBRE PERFIL,
                            C.ID_PERFIL,
                            D.DIRECCION as DIRECCION,
                            E.ID_TIPODOCUMENTO AS DOC_TYPE,
                            E.NUM_DOCUMENTO AS DOC_NUMBER,
                            F.SIGLAS
                FROM USERS A LEFT JOIN MOISES.PERSONA B
                ON A.ID = B.ID_PERSONA
                LEFT JOIN LAMB_PERFIL C
                ON A.ID_PERFIL = C.ID_PERFIL
                LEFT JOIN MOISES.PERSONA_VIRTUAL D
                ON A.ID = D.ID_PERSONA
                LEFT JOIN MOISES.PERSONA_DOCUMENTO E
                ON A.ID = E.ID_PERSONA
                LEFT JOIN MOISES.TIPO_DOCUMENTO F
                ON E.ID_TIPODOCUMENTO = F.ID_TIPODOCUMENTO
                WHERE A.ID = ".$id."
                AND E.ID_TIPODOCUMENTO IN (1,4,7)
                ORDER BY ID,NOMBRES, F.ID_TIPODOCUMENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listUser($size_page,$text_search){        
        /*$query = "SELECT 
                            A.ID,
                            B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO NOMBRES,
                            A.EMAIL,
                            A.NAME,
                            C.NOMBRE PERFIL,
                            C.ID_PERFIL,
                            --D.ID_ENTIDAD
                            FC_ENTIDADES_ASIGNADAS(a.id) ENTIDAD
                FROM USERS A LEFT JOIN MOISES.PERSONA B
                ON A.ID = B.ID_PERSONA
                LEFT JOIN LAMB_PERFIL C
                ON A.ID_PERFIL = C.ID_PERFIL
                --LEFT JOIN CONTA_ENTIDAD_USUARIO D
                --ON A.ID = D.ID_PERSONA
                ORDER BY ID,NOMBRES ";
        $oQuery = DB::select($query);*/
        
        $oQuery = DB::table('USERS A')
            ->leftjoin('MOISES.PERSONA B', 'A.ID', '=', 'B.ID_PERSONA')
            ->leftjoin('LAMB_PERFIL C', 'A.ID_PERFIL', '=', 'C.ID_PERFIL')
            ->where(DB::raw("UPPER(B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO)"), 'LIKE', '%'.$text_search.'%')
            //->orwhere(DB::raw('UPPER(B.PATERNO)'), 'LIKE', '%'.$text_search.'%') 
            //->orwhere(DB::raw('UPPER(B.MATERNO)'), 'LIKE', '%'.$text_search.'%') 
            //->orwhere(DB::raw("UPPER(A.EMAIL)"), 'LIKE', '%'.$text_search.'%')   
            //->where(DB::raw("B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES"), 'like', '%".$text_search."%')
            ->select('A.ID', 
            DB::raw("B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES"), 'A.EMAIL','A.NAME','C.NOMBRE AS PERFIL','C.ID_PERFIL',
            DB::raw("FC_ENTIDADES_ASIGNADAS(A.ID) AS ENTIDAD"))
            ->orderBy('NOMBRES')->paginate($size_page); 
        
        return $oQuery;
    }
    public static function editUser($id,$name,$id_perfil){
        $query = "UPDATE USERS SET NAME = '".$name."', ID_PERFIL = $id_perfil,FECHA_UPDATE = SYSDATE
                WHERE ID = $id  ";
        $oQuery = DB::update($query);
        return $oQuery;
    }
    public static function userDeptoParentAsigando($id,$id_entidad){
        $query = "SELECT 
                A.ID_DEPTO,A.NOMBRE,
                NVL((SELECT X.ESTADO FROM LAMB_USERS_DEPTO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO AND X.ID = $id),0) ESTADO 
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND ES_EMPRESA = '1'
                AND A.ID_DEPTO IN (
                SELECT SUBSTR(ID_DEPTO,1,1) FROM LAMB_USERS_DEPTO
                WHERE ID_ENTIDAD = $id_entidad
                AND ID = $id
                
                
                ) ";
                $qry = "SELECT
                A.ID_DEPTO, A.NOMBRE, NVL(B.ESTADO,0) estado
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_ENTIDAD = $id_entidad
                AND B.ID = $id
                AND B.ACTIVO = '1'
                AND ES_EMPRESA = '1' ";
        /*$query = "SELECT 
                A.ID_DEPTO,A.NOMBRE,NVL(B.ESTADO,0) ESTADO
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND LENGTH(A.ID_DEPTO) = 1
                AND B.ID_ENTIDAD = $id_entidad
                AND B.ID = $id  "; */       
        $oQuery = DB::select($qry);
        foreach($oQuery as $id){
            $item = array();
            $item["id_depto"] = $id->id_depto;
            $item["nombre"] = $id->nombre;
            if($id->estado==1){
                $item["estado"] = true;
            }else{
                $item["estado"] = false;
            }
            $datos[] = $item;
        }
        return $datos;
    }
    public static function userDeptoParent($id_entidad,$parent,$per_id,$asignado){
        if($parent == "A"){
            $dato = "AND A.ID_PARENT IS NULL" ;
        }else{
            $dato = "AND A.ID_PARENT = '$parent' ";
        } 
        /*
        if($asignado=="true"){
            $asig = "AND A.ID_DEPTO IN (SELECT ID_DEPTO FROM LAMB_USERS_DEPTO WHERE ID = $per_id) ";
            $as = 1;
        }else{
            $asig = "AND A.ID_DEPTO NOT IN (SELECT ID_DEPTO FROM LAMB_USERS_DEPTO WHERE ID = $per_id) ";
            $as = 0;
        }*/
        $query = "SELECT 
                        A.ID_DEPTO,
                        A.ID_PARENT,
                        A.NOMBRE,
                        A.ES_GRUPO, 
                        (SELECT COUNT(ID_DEPTO) FROM LAMB_USERS_DEPTO B WHERE B.ID_DEPTO = A.ID_DEPTO AND B.ID_ENTIDAD = A.ID_ENTIDAD AND B.ID = $per_id) AS ASIGNADO
                    FROM CONTA_ENTIDAD_DEPTO A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    $dato 
                    ORDER BY A.ID_DEPTO  ";
//        dd('asads',$query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function entityDepto($id_entidad,$parent){
        if($parent == "A"){
            $dato = "AND A.ID_PARENT IS NULL" ;
        }else{
            $dato = "AND A.ID_PARENT = '$parent' ";
        }                
        $query = "SELECT 
                        A.ID_DEPTO,
                        A.ID_PARENT,
                        A.NOMBRE,
                        A.ES_GRUPO                        
                    FROM CONTA_ENTIDAD_DEPTO A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    $dato                    
                    ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function userDeptoParentX($id_entidad,$per_id){
        $query = "SELECT A.ID_DEPTO ID_PARENT, A.NOMBRE,
                        (SELECT COUNT(ID_DEPTO) FROM LAMB_USERS_DEPTO B WHERE B.ID_DEPTO = A.ID_DEPTO AND B.ID_ENTIDAD = A.ID_ENTIDAD AND B.ID = $per_id) AS ASIGNADO
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ES_GRUPO = 1
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    
    public static function userDeptoChild($id_entidad,$per_id,$parent){
        $query = "SELECT 
                        A.ID_ENTIDAD,
                        A.ID_DEPTO ID_HIJO,B.ID_DEPTO ID_PARENT, A.NOMBRE,
                        (SELECT COUNT(B.ID_DEPTO) FROM LAMB_USERS_DEPTO B WHERE B.ID_DEPTO = A.ID_DEPTO AND B.ID_ENTIDAD = A.ID_ENTIDAD AND B.ID = $per_id) AS ASIGNADO
                FROM CONTA_ENTIDAD_DEPTO A, CONTA_ENTIDAD_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD 
                AND A.ID_PARENT = B.ID_DEPTO
                AND A.ID_ENTIDAD = $id_entidad
                AND B.ID_DEPTO = $parent
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function dinamicaAsientoDepto($id_entidad,$parent,$id_asiento){
        if($parent == "A"){
            $dato = "AND A.ID_PARENT IS NULL" ;
        }else{
            $dato = "AND A.ID_PARENT = '$parent' ";
        }                
        $query = "SELECT 
                        A.ID_DEPTO,
                        A.ID_PARENT,
                        COALESCE(A.NOMBRE,'-') NOMBRE,
                        A.ES_GRUPO, 
                        (SELECT COUNT(ID_DEPTO) FROM CONTA_DINAMICA_DEPTO B WHERE  B.ID_ENTIDAD = A.ID_ENTIDAD AND B.ID_DEPTO = A.ID_DEPTO AND B.ID_ASIENTO = $id_asiento) AS ASIGNADO
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = $id_entidad
                $dato                    
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPerfil(){                      
        $query = "SELECT 
                    ID_PERFIL,NOMBRE 
                    FROM LAMB_PERFIL
                    ORDER BY ID_PERFIL ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listModulesActions($id_modulo){
        $oQuery = DB::table('LAMB_ACCION')->select('ID_ACCION','ID_MODULO','NOMBRE','METODO','CLAVE','VALOR','ESTADO')->where('ID_MODULO', $id_modulo)->orderBy('NOMBRE')->get();        
        return $oQuery;
    }
    public static function showModulesActions($id_accion){
        $oQuery = DB::table('LAMB_ACCION')->select('ID_ACCION','ID_MODULO','NOMBRE','METODO','CLAVE','VALOR','ESTADO')->where('ID_ACCION', $id_accion)->orderBy('NOMBRE')->get();        
        return $oQuery;
    }
    public static function addModulesActions($id_modulo,$nombre,$clave,$valor,$estado){
        DB::table('LAMB_ACCION')->insert(
                        array('ID_MODULO' => $id_modulo, 'NOMBRE' => $nombre,'CLAVE' => $clave,'VALOR' => $valor,'ESTADO' => $estado)
                    );
        $query = "SELECT 
                        MAX(ID_ACCION) ID_ACCION
                FROM LAMB_ACCION ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_accion = $id->id_accion;
        }
        $getAccion = ModuloData::showModulesActions($id_accion);
        return $getAccion;
    }
    public static function updateModulesActions($id_accion,$nombre,$clave,$valor,$estado){
        DB::table('LAMB_ACCION')
            ->where('ID_ACCION', $id_accion)
            ->update([
                'NOMBRE' => $nombre,
                'CLAVE' => $clave,
                'VALOR' => $valor,
                'ESTADO' => $estado
            ]);
        $getAccion = ModuloData::showModulesActions($id_accion);
        return $getAccion;
    }
    public static function deleteModulesActions($id_accion){
        DB::table('LAMB_ACCION')->where('ID_ACCION', '=', $id_accion)->delete();
    }
    public static function getAccesTokenOauth(){   
        $acces_token = "";
        $client_id = "eO2ttqwbrU7j34Jv7488VNQcoxVf0RQzSioTBdAo";
        $client_secret = "jQG5MWxmGurxUNvg8eBaWA7C0skuQwQEvGDxmlzvdbGI5GFyUdJCg7fGBpCcPftMuFtj3Zdv2tvZLccsJxMM77l90CC5BZdINchIg3pd4ZS9wP7NELhjjy0yNviObIpx";
        $gran_type = "Client credentials";
        $fields = array(
            //'client_id' => $client_id,
            //'client_secret' => $client_secret,
            'grant_type' => "client_credentials"
        );

        $curl = curl_init('https://oauth.upeu.edu.pe/oauth/token/'); 
        curl_setopt($curl, CURLOPT_POST, true); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($curl, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public static function usersOauth($id,$params){  
        $jResponse['success'] = true;
        $jResponse['message'] = "Usuario Modificado en Lamb";
        $response = ModuloData::getAccesTokenOauth();
        $response = (object) json_decode($response, true);
        $access_token = "";
        if(is_object($response)){
            $access_token = $response->access_token;
        }
        //dd($access_token);
        //return $jResponse;
        if($access_token !== "") {
            $count = 0;
            $name = $params->data->email;
            $doc_number = $params->data->doc_number;
            $doc_type = $params->data->doc_type;
            $direccion = $params->data->direccion;
            $app = isset($params->data->app) ? $params->data->app : null;
            $password = isset($params->data->password) ? $params->data->password : null;
            $data = ModuloData::validateUsersOauth($access_token,"0".$doc_type,$doc_number,$name);
            // dd($data);
            if($data["nerror"] === 0){// Crear Usuario
                $correo = ModuloData::listPersonsVirtual($id,$direccion);
                if(count($correo) === 0){
                    ModuloData::addPersonsVirtual($id,$direccion);
                }
                $datos = ModuloData::verUser($id);
                foreach ($datos as $item){
                    $first_name = $item->first_name;
                    $last_name = $item->last_name;
                }
                ModuloData::updateUserspsw($id,$name);
                $values['username'] = $name;
                $values['password'] = isset($password) ? $password : "Lamb".date('Y');
                $values['first_name'] = $first_name;
                $values['last_name'] = $last_name;
                $values['doc_number'] = $doc_number;
                $values['doc_type'] = "0".$doc_type;
                $values['email'] = $direccion;
                $values['is_active'] = true;
                $values['app'] =  isset($app) ? $app : "Lamb Financial";
                
                $options = array(
                        'http' => array (
                                'header'  => "Authorization: Bearer " . $access_token . "\r\n" . 
                                                         "Content-Type: application/json\r\r",
                                'method'  => 'POST',
                                'content' => json_encode($values)
                        )
                );
                
                $url='https://oauth.upeu.edu.pe/api/users/';
                try{
                    $context  = stream_context_create($options);
                    $respuesta = file_get_contents($url, false, $context);
                }catch(Exception $e){
                    //dd($e->getMessage());
                }
                //dd($context);
                $count = ModuloData::validateUserOauth($access_token,$name);
                //dd($count);
                if($count == 1){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Usuario Creado con exito en Oauth";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "El usuario no ha sido Creado";
                }
                
            } else if($data["nerror"] === 1) {
                $correo = ModuloData::listPersonsVirtual($id,$direccion);
                if(count($correo) === 0){
                    ModuloData::addPersonsVirtual($id,$direccion);
                }
            }
            else{
                if($data["nerror"] == 2){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data["message"];
                }
            }
            
        }else{
            $jResponse['success'] = false;
            $jResponse['message'] = "Token Oauht no EXISTE";
        }
        return $jResponse;
        
    }
    public static function validateUsersOauth($access_token,$doc_type,$doc_number,$username){   
        $count = 0;
        $counta = 0;
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Bearer ".$access_token 
            )
        ));
        $url='https://oauth.upeu.edu.pe/api/users/?username='.$username;
        $respuesta = file_get_contents($url, false, $context);
        $response =  (object) json_decode($respuesta, true);
        if(is_object($response)){
            $count = $response->count;
        }
        
        $url='https://oauth.upeu.edu.pe/api/users/?doc_type='.trim($doc_type)."&doc_number=".trim($doc_number);
        $respuesta = file_get_contents($url, false, $context);
        $response =  (object) json_decode($respuesta, true);
        
        //dd($response);
        if(is_object($response)){
            $counta = $response->count;
        }
        if($count > 0 and $counta > 0){
            // User existe en Oauth
            $jResponse['nerror'] = 1;
            $jResponse['message'] = "";
        }else{
            if($count > 0 and $counta == 0){
                $jResponse['nerror'] = 2;
                $jResponse['message'] = "Usuario EXISTE para otra Persona";
            }else{
                if($count == 0 and $counta > 0){
                    $jResponse['nerror'] = 2;
                    $jResponse['message'] = "Esta itentando crear otro usuario para la misma Persona";
                }else{
                    $jResponse['nerror'] = 0;
                    $jResponse['message'] = "Crear Usuario";
                }
            }

        }     
        return $jResponse;
    }
    public static function validateUserOauth($access_token,$username){   
        $count = 0;
        $counta = 0;
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Bearer ".$access_token 
            )
        ));
        $url='https://oauth.upeu.edu.pe/api/users/?username='.$username;
        $respuesta = file_get_contents($url, false, $context);
        $response =  (object) json_decode($respuesta, true);
        if(is_object($response)){
            $count = $response->count;
        }
        
        return $count;
    }
    public static function listThemes($tipo_modulo){
        $getTema = DB::table('LAMB_TEMA')->select('ID_TEMA','NOMBRE','ESTADO','COLOR_PRIMARY','COLOR_SECONDARY')->where('TIPO_MODULO', $tipo_modulo)->orderBy('NOMBRE')->get();
        return $getTema;
    }
    public static function listUsersThemes($id_persona,$id_entidad,$id_depto){
        $query = "SELECT B.ID_ENTIDAD,B.ID_DEPTO,B.ID,A.ID_MODULO,A.NOMBRE,A.IMAGEN,B.FECHA,C.ID_TEMA,C.NOMBRE AS TEMA,B.SIDEBAR,
                C.COLOR_PRIMARY, A.TIPO_MODULO
                FROM LAMB_MODULO A LEFT JOIN LAMB_ENTIDAD_DEPTO_CONFIG B
                ON A.ID_MODULO = B.ID_MODULO
                AND B.ID = ".$id_persona."
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID_DEPTO = '".$id_depto."'
                LEFT JOIN LAMB_TEMA C
                ON B.ID_TEMA = C.ID_TEMA
                WHERE A.NIVEL = 1
                AND A.ID_MODULO IN (SELECT ID_MODULO FROM LAMB_ROL_MODULO A, LAMB_USUARIO_ROL B
                WHERE A.ID_ROL = B.ID_ROL AND B.ID_PERSONA = ".$id_persona." )
                ORDER BY A.ORDEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addUsersThemes($id_persona,$id_entidad,$id_depto,$id_modulo,$id_tema,$sidebar){
        DB::table('LAMB_ENTIDAD_DEPTO_CONFIG')->insert(
                        array('ID' => $id_persona, 'ID_ENTIDAD' => $id_entidad,'ID_DEPTO' => $id_depto,'ID_MODULO' => $id_modulo,'ID_TEMA' => $id_tema,'FECHA'=> DB::raw('SYSDATE'),'SIDEBAR' => $sidebar)
                    );
        
        $getAccion = ModuloData::listUsersThemes($id_persona,$id_entidad,$id_depto);
        return $getAccion;
    }
    public static function updateUsersThemes($id_persona,$id_entidad,$id_depto,$id_modulo,$id_tema,$sidebar){
        DB::table('LAMB_ENTIDAD_DEPTO_CONFIG')
            ->where('ID', $id_persona)
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID_MODULO', $id_modulo)
            ->update([
                'ID_TEMA' => $id_tema,
                'FECHA'=> DB::raw('SYSDATE'),
                'SIDEBAR'=> $sidebar
            ]);
        $getAccion = ModuloData::listUsersThemes($id_persona,$id_entidad,$id_depto);
        return $getAccion;
    }
    public static function showUsersThemes($id_persona,$id_entidad,$id_depto,$id_modulo){

        $getUserTheme = DB::table('LAMB_ENTIDAD_DEPTO_CONFIG')->select('ID','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TEMA','SIDEBAR',DB::raw("FC_TEMA(ID_TEMA) AS NOMBRE_TEMA"))
            ->where('ID', $id_persona)
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID_MODULO', $id_modulo)
            ->get();
        return $getUserTheme;
    }
    public static function addUsersEnterprises($id_user,$id_entidad,$id_depto,$token){
        DB::table('CONTA_ENTIDAD_USUARIO')
            ->where('ID_PERSONA', $id_user)
            ->update([
                'ESTADO' => 0
            ]);
        DB::table('CONTA_ENTIDAD_USUARIO')
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_PERSONA', $id_user)
            ->update([
                'ESTADO' => 1
            ]);
        
        DB::table('LAMB_USERS_DEPTO')
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID', $id_user)
            ->update([
                'ESTADO' => 0
            ]);
        DB::table('LAMB_USERS_DEPTO')
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID', $id_user)
            ->update([
                'ESTADO' => 1
            ]);
        $sql = "SELECT 
                A.ID_DEPTO, A.NOMBRE,B.ID AS ID_PERSONA
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND B.ID = ".$id_user." ";
        $oQuery = DB::select($sql);
        if(count($oQuery) > 0){
            DB::table('USERS_SESSION')
            ->where('TOKEN', $token)
            ->where('ID_USER', $id_user)
            ->update([
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto
            ]);
        }
        $oQuery = ModuloData::showUsersEnterprises($id_user,$id_depto);
        return $oQuery;
    }
    public static function showUsersEnterprises($id_persona,$id_depto){
        $query = "SELECT A.ID_PERSONA,A.ID_ENTIDAD,B.ID_DEPTO,FC_NAMEENTITY(A.ID_ENTIDAD) as ENTITY_NAME,FC_NAMESDEPTO(A.ID_ENTIDAD,B.ID_DEPTO) AS DEPARTMENT_NAME
                FROM CONTA_ENTIDAD_USUARIO A, LAMB_USERS_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_PERSONA = B.ID
                AND A.ID_PERSONA = ".$id_persona."
                AND B.ID_DEPTO = '".$id_depto."'
                AND A.ESTADO = 1
                AND B.ESTADO = 1 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyEntities($id_persona){
        $query = "SELECT A.ID_ENTIDAD,A.NOMBRE,B.ID_PERSONA,B.ESTADO 
                FROM CONTA_ENTIDAD A, CONTA_ENTIDAD_USUARIO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND B.ESTADO IN (0,1)
                AND B.ID_PERSONA = ".$id_persona."
                ORDER BY NOMBRE ";
        // ESTADO: 0=Asignado; 1=Asignado defaul; 2=Eliminado
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyDepartment($id_entidad,$id_persona){
        $query = "SELECT 
                A.ID_DEPTO, A.NOMBRE,B.ID AS ID_PERSONA,B.ESTADO, A.ES_EMPRESA
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID = ".$id_persona."
                AND ES_EMPRESA = '1'
                AND B.ACTIVO = '1'
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyDepartmentsChildrens($id_entidad,$id_persona){
        $query = "SELECT 
                A.ID_DEPTO, A.NOMBRE,B.ID AS ID_PERSONA,B.ESTADO
                FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID = ".$id_persona."
                AND A.ES_GRUPO = '0'
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listDepartmentsChildrens($id_entidad,$depto){
        $query = "SELECT 
                A.ID_DEPTO, A.NOMBRE
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND (A.ID_DEPTO LIKE '%".$depto."%' OR UPPER(A.NOMBRE) LIKE UPPER('%".$depto."%') )
                AND A.ES_GRUPO = '0'
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntityDepartments($idEntidad,$params){
        $id_parent = $params['id_depto'];
        $data = DB::table("CONTA_ENTIDAD_DEPTO")
            ->select('ID_DEPTO as ID', 'NOMBRE as NAME')
            ->where("ID_ENTIDAD", $idEntidad)
            ->whereRaw("SUBSTR(ID_PARENT,1,1) = '".$id_parent."'" )
            ->where("ES_GRUPO", '0')
            ->orderBy('ID_DEPTO');

//        AND SUBSTR(ID_PARENT,1,1) = 1

//        dd('kejeejje', $params['text_search']);
        if(isset($params['text_search']) and $params['text_search'] ){
            $txt = $params['text_search'];
            $data = $data
                ->whereraw("upper(NOMBRE ||''|| ID_DEPTO) like  upper('%".$txt."%')");
        }
        if(isset($params['page_size']) and $params['page_size'] > 0) {
            $data = $data->paginate($params['page_size']);
        } else {
            $data = $data->get();
        }

        return $data;
    }
    public static function listRoleModule($id_rol){
        $query = "SELECT B.ID_MODULO 
                FROM LAMB_ROL_MODULO A, LAMB_MODULO B
                WHERE A.ID_MODULO = B.ID_MODULO
                AND A.ID_ROL = ".$id_rol."
                AND B.NIVEL = '1'
                ORDER BY B.ORDEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showUsersRoles($id_persona,$id_entidad){
        $getUserTheme = DB::table('LAMB_USUARIO_ROL')->select('ID_ROL')
            ->where('ID_PERSONA', $id_persona)
            ->where('ID_ENTIDAD', $id_entidad)
            ->get();        
        return $getUserTheme;
    }
    public static function listPersonsVirtual($id_persona,$direccion){
        $query = DB::table('MOISES.PERSONA_VIRTUAL')->select('ID_PERSONA','DIRECCION')
            ->where('ID_PERSONA', $id_persona)
            ->where('ID_TIPOVIRTUAL', 1)
            ->where('DIRECCION', $direccion)
            ->get();        
        return $query;
    }
    public static function addPersonsVirtual($id_persona,$direccion){
        $query = "SELECT
                    NVL(MAX(ID_VIRTUAL),0)+1 as P_ID_VIRTUAL
                  FROM MOISES.PERSONA_VIRTUAL
                  WHERE ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_virtual = $id->p_id_virtual;
        }
        DB::table('MOISES.PERSONA_VIRTUAL')->insert(
                    array('ID_VIRTUAL' => $id_virtual, 
                        'ID_PERSONA' => $id_persona,
                        'ID_TIPOVIRTUAL' => 1,
                        'DIRECCION' => $direccion)
                );
    }
    public static function updateUserspsw($id_persona,$name){
        $senha = "Lamb".date('Y');
        DB::table('USERS')
            ->where('ID', $id_persona)
            ->update([
                'EMAIL' => $name,
                'CONTRASENHA' => bcrypt($senha)
            ]);
    }
    public static function usersDeptosParents($id,$id_entidad){
        $query = "SELECT 
                        A.ID_DEPTO,A.NOMBRE,
                        (SELECT COUNT(*) FROM LAMB_USERS_DEPTO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO AND X.ID = $id AND ACTIVO = '1') ESTADO 
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND ES_EMPRESA = '1'  ";       
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $item = array();
            $item["id_depto"] = $id->id_depto;
            $item["nombre"] = $id->nombre;
            if($id->estado==1){
                $item["estado"] = true;
            }else{
                $item["estado"] = false;
            }
            $datos[] = $item;
        }
        return $datos;
    }
    public static function showUsersDepto($id,$id_entidad,$id_depto){
        $query = "SELECT ID_DEPTO
                    FROM LAMB_USERS_DEPTO
                    WHERE ID = ".$id."
                    AND ID_ENTIDAD = ".$id_entidad."
                    AND ID_DEPTO = '".$id_depto."' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
}