<?php

namespace App\Http\Data\Setup\Registry;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\Locks\ComunData;
use App\Http\Data\Modulo\ModuloData;
use PDO;

class RegistryData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getPersona($key, $init, $fin)
    {
        //INHABILITADO -- YA NO SE USA
        $query = "select a.r,a.t,a.id_persona,a.nombre,b.id_tipodocumento,b.num_documento,b.correo,b.celular,
        b.telefono,b.fec_nacimiento,b.id_tipopais,b.correo_inst,b.sexo,
        b.id_tipoestadocivil,a.paterno,a.materno,a.codigo,u.email,c.codigo uni,
        (select m.siglas from moises.tipo_documento m where m.id_tipodocumento=b.id_tipodocumento) siglas, 
        case when b.id_persona is null then 0 else 1 end as pn, 
        case when c.id_persona is null then 0 else 1 end as pna,
        case when do.id_persona is null then 0 else 1 end as pnd, 
        case when ap.id_persona is null then 0 else 1 end as aps 
        from (SELECT a.*, ROW_NUMBER() OVER (ORDER BY a.paterno asc) R,ROW_NUMBER() OVER (ORDER BY a.paterno desc) T  
        FROM moises.persona a join moises.persona_natural b on a.id_persona=b.id_persona left join moises.persona_natural_alumno c on a.id_persona = c.id_persona  where  
        (upper(replace(concat(concat(nombre,paterno),materno),' ','')) like upper(replace('%" . $key . "%',' ','')) 
        or upper(replace(concat(concat(regexp_substr(nombre ,'[^ ]+',1,1),paterno),materno),' ','')) like upper(replace('%" . $key . "%',' ',''))
        or upper(replace(concat(concat(regexp_substr(nombre ,'[^ ]+',1,2),paterno),materno),' ','')) like upper(replace('%" . $key . "%',' ',''))   
        or b.num_documento like '%" . $key . "%' or c.codigo like '%" . $key . "%') and rownum <=100) a 
        left join moises.persona_natural b on a.id_persona = b.id_persona 
        left join moises.persona_natural_alumno c on a.id_persona = c.id_persona 
        left join eliseo.users u on a.id_persona = u.id 
        left join moises.persona_natural_docente do on do.id_persona = a.id_persona 
        left join eliseo.aps_trabajador ap on ap.id_persona = a.id_persona 
        where a.r BETWEEN " . $init . " and " . $fin . " order by a.paterno asc";
        return collect(DB::select($query));
    }

    public static function getInfoPersonas($request, $per_page)
    {
        $key = $request->key;

        $sql = DB::table("moises.persona p");
        $sql->join("moises.persona_natural pn", "pn.id_persona", "=", "p.id_persona");
        $sql->leftJoin("moises.persona_natural_alumno pna", "pna.id_persona", "=", "p.id_persona");
        $sql->leftJoin("moises.persona_natural_docente pnd", "pnd.id_persona", "=", "p.id_persona");
        $sql->leftJoin("eliseo.aps_trabajador ap", "ap.id_persona", "=", "p.id_persona");
        $sql->leftJoin("eliseo.users u", "u.id", "=", "p.id_persona");
        $sql->leftJoin("moises.tipo_documento td", "td.id_tipodocumento", "=", "pn.id_tipodocumento");
        $sql->leftJoin("moises.tipo_religion tr", "tr.id_tiporeligion", "=", "pn.id_tiporeligion");
        $txt = "(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            (pn.num_documento like '%" . $key . "%'  or pna.codigo like '%" . $key . "%'))";
        $sql->whereraw($txt);
        $sql->select(
            "p.id_persona",
            DB::raw('upper(p.nombre) as nombre'),
            DB::raw('upper(p.paterno) as paterno'),
            DB::raw('upper(p.materno) as materno'),
            "pn.id_tipodocumento",
            "pn.num_documento",
            "pn.correo",
            "pn.celular",
            "pn.telefono",
            "pn.fec_nacimiento",
            "pn.id_tipopais",
            "pn.correo_inst",
            "pn.sexo",
            "pn.id_tipoestadocivil",
            "p.codigo",
            "u.email",
            "pna.codigo as uni",
            DB::raw("case when pn.id_persona is null then 0 else 1 end as pn"),
            DB::raw("case when pna.id_persona is null then 0 else 1 end as pna"),
            DB::raw("case when pnd.id_persona is null then 0 else 1 end as pnd"),
            DB::raw("case when ap.id_persona is null then 0 else 1 end as aps "),
            'td.siglas',
            'tr.nombre as religion'
        );
        $sql->orderByRaw("p.paterno");
        $data = $sql->paginate($per_page);
        return $data;
    }

    public static function getComprobateReg($key)
    {
        return DB::table('moises.persona p')
            ->join('moises.persona_documento d', 'p.id_persona', '=', 'd.id_persona')
            ->select('p.nombre', 'p.paterno', 'p.materno', 'd.num_documento', 'p.codigo')
            ->where('d.num_documento', '=', $key)
            ->distinct()->get();
    }

    public static function registrarPersona($request)
    {
        $response = [];
        $id_persona_new             =   0;
        $nerror                     =   0;
        $msgerror                   =   '';
        try {

            DB::beginTransaction();
            $id_persona                 =   $request->id_persona;
            $nombre                     =   $request->nombre;
            $paterno                    =   $request->paterno;
            $materno                    =   $request->materno;
            $id_tipopais                =   $request->id_tipopais;
            $id_tipodocumento           =   $request->id_tipodocumento;
            $num_documento              =   $request->num_documento;
            $sexo                       =   $request->sexo;
            $fec_nacimiento             =   $request->fec_nacimiento;
            $id_tipoestadocivil         =   $request->id_tipoestadocivil;
            $celular                    =   $request->celular;
            $telefono                   =   $request->telefono;
            $correo                     =   $request->correo;
            $a                          =   null;
            $type                       =   $request->type;
            $es_docente                 =   '0';
            if ($type == 'doce') {
                $es_docente = '1';
            }
            for ($x = 1; $x <= 200; $x++) {
                $msgerror .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin MOISES.REGISTRO_PERSONA.SP_REGISTRO_PERSONA(
                :P_ID_PERSONA,
                :P_NOMBRE, 
                :P_PATERNO, 
                :P_MATERNO,
                :P_SEXO,
                :P_FEC_NACIMIENTO,
                :P_ID_TIPOPAIS,
                :P_ID_TIPODOCUMENTO, 
                :P_NUM_DOCUMENTO, 
                :P_TELEFONO, 
                :P_CELULAR,
                :P_CORREO,
                :P_ID_SITUACION_EDUCATIVO,
                :P_ID_TIPOESTADOCIVIL,
                :P_ES_DOCENTE,
                :P_ERROR, 
                :P_MSGERROR,
                :P_ID_PERSONA_NEW
                ); end;");
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
            $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
            $stmt->bindParam(':P_SEXO', $sexo, PDO::PARAM_STR);
            $stmt->bindParam(':P_FEC_NACIMIENTO', $fec_nacimiento);
            $stmt->bindParam(':P_ID_TIPOPAIS', $id_tipopais, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPODOCUMENTO', $id_tipodocumento, PDO::PARAM_INT);
            $stmt->bindParam(':P_NUM_DOCUMENTO', $num_documento, PDO::PARAM_STR);
            $stmt->bindParam(':P_TELEFONO', $telefono);
            $stmt->bindParam(':P_CELULAR', $celular);
            $stmt->bindParam(':P_CORREO', $correo);
            $stmt->bindParam(':P_ID_SITUACION_EDUCATIVO', $a);
            $stmt->bindParam(':P_ID_TIPOESTADOCIVIL', $id_tipoestadocivil);
            $stmt->bindParam(':P_ES_DOCENTE', $es_docente);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PERSONA_NEW', $id_persona_new, PDO::PARAM_INT);
            $stmt->execute();
            DB::commit();
            $accept = ($nerror == 0);
            $response['id_persona'] = $id_persona_new;
            $response['success'] = $accept;
            if ($type != 'free') {
                $response['persona'] = RegistryData::identifyType($request, $type, $id_persona_new);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['success'] = false;
            $response['abc'] = $e->getMessage();
        }

        return $response;
    }

    public static function identifyType($request, $type, $id_persona)
    {
        $response = [];
        switch ($type) {
            case 'alum':
                $response = RegistryData::registrarAlumno($request, $id_persona);
                break;
            case 'doce':
                $response = RegistryData::registrarDocente($request, $id_persona);
                break;
            case 'free':
                $response = false;
                break;
        }
        return $response;
    }

    public static function registrarAlumno($request, $id_persona)
    {
        $response = [];
        $reg_alumno = 0;
        $cod_univ = '';
        $email = '';
        for ($i = 1; $i <= 200; $i++) {
            $email .= '0';
        }
        for ($i = 1; $i <= 12; $i++) {
            $cod_univ .= '0';
        }
        $nombre                     =   $request->nombre;
        $paterno                    =   $request->paterno;
        $materno                    =   $request->materno;
        try {
            DB::beginTransaction();
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin MOISES.REGISTRO_PERSONA.SP_REGISTRO_ALUMNO(
                    :P_ID_PERSONA,
                    :P_NOMBRE, 
                    :P_PATERNO, 
                    :P_MATERNO,
                    :P_CODIGO, 
                    :P_EMAIL,
                    :P_REG_ALUMNO
                    ); end;");
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
            $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
            $stmt->bindParam(':P_CODIGO', $cod_univ, PDO::PARAM_STR);
            $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);
            $stmt->bindParam(':P_REG_ALUMNO', $reg_alumno, PDO::PARAM_INT);
            $stmt->execute();
            DB::commit();
            if ($reg_alumno == 1) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
            }
            $response['pass'] = true;
            $response['usuario'] = $email;
            $response['codigo'] = $cod_univ;
            $res = RegistryData::registrarUsuario($id_persona, $email, $request->nombre);
            $response['user'] = json_decode($res);
            $rr = RegistryData::registrarOauth($id_persona, $email, $request->num_documento, $request->nombre);
            $response['oauth'] = json_decode($rr);
            $response['rol'] = RegistryData::setRol(env('ID_ROL_ALUMNO'), $id_persona, $request->id_entidad, $request->id_depto);
        } catch (Exception $e) {
            DB::rollBack();
            $response['error'] = $e->getMessage();
            $response['pass'] = false;
        }
        return $response;
    }

    public static function registrarDocente($request, $id_persona)
    {
        $response = [];
        $reg_docente = 0;
        $email = '';
        for ($i = 1; $i <= 200; $i++) {
            $email .= '0';
        }
        $nombre                     =   $request->nombre;
        $paterno                    =   $request->paterno;
        $materno                    =   $request->materno;
        try {
            DB::beginTransaction();
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin MOISES.REGISTRO_PERSONA.SP_REGISTRO_DOCENTE(
                    :P_ID_PERSONA,
                    :P_NOMBRE, 
                    :P_PATERNO, 
                    :P_MATERNO,
                    :P_EMAIL,
                    :P_REG_DOCENTE
                    ); end;");
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
            $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
            $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);
            $stmt->bindParam(':P_REG_DOCENTE', $reg_docente, PDO::PARAM_INT);
            $stmt->execute();
            DB::commit();
            if ($reg_docente == 1) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
            }
            $response['pass'] = true;
            $response['usuario'] = $email;
            $res = RegistryData::registrarUsuario($id_persona, $email, $nombre);
            $response['user'] = json_decode($res);
            $rr = RegistryData::registrarOauth($id_persona, $email, $request->num_documento, $request->nombre);
            $response['oauth'] = json_decode($rr);
            $response['rol'] = RegistryData::setRol(env('ID_ROL_DOCENTE'), $id_persona, $request->id_entidad, $request->id_depto);
        } catch (Exception $e) {
            DB::rollBack();
            $response['error'] = $e->getMessage();
            $response['pass'] = false;
        }
        return $response;
    }

    public static function registrarUsuario($id_persona, $email, $name)
    {
        $respuesta = null;
        $params = array('data' => array('id' => $id_persona, 'email' => $email, 'name' => $name, 'id_perfil' => 1));
        $headers = apache_request_headers();
        $headers = array_change_key_case($headers, CASE_LOWER);
        $access_token = null;
        if (isset($headers['authorization'])) {
            $access_token = $headers['authorization'];
            $results = DB::table('users_session us')
            ->select('us.token_oauth')
            ->where('us.token', '=', $access_token)
            ->get();
            $results[0]->token_oauth;

            foreach($results as $item){
                if (strpos($item->token_oauth, 'bearer') !== false or strpos($item->token_oauth, 'BEARER') !== false) {
                    $access_token = substr($item->token_oauth, 7);
                } else {
                    $access_token = $item->token_oauth;
                }
            }
        }
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Authorization: " . $access_token . "\r\n",
                'method'  => 'POST',
                'content' => json_encode($params)
            )
        ));
        $url = env('LAMB_FINANCIAL') . '/setup/createuser';
        try {
            $respuesta = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            $respuesta = $e->getMessage();
        }
        return $respuesta;
    }
    public static function registrarOauth($id_persona, $email, $num_documento, $name, $app = 'Lamb University', $password = 'Lamb2023')
    {
        $respuesta = null;
        $params = array('data' => array('password' => $password, 'app' => 'Lamb University', 'email' => $email, 'doc_number' => $num_documento, 'doc_type' => '1', 'direccion' => 'default@upeu.edu.pe', 'name' => $name, 'id_perfil' => '1'));
        $headers = apache_request_headers();
        $headers = array_change_key_case($headers, CASE_LOWER);
        $access_token = null;
        if (isset($headers['authorization'])) {
            $access_token = $headers['authorization'];
            $results = DB::table('users_session us')
            ->select('us.token_oauth')
            ->where('us.token', '=', $access_token)
            ->get();
            $results[0]->token_oauth;

            foreach($results as $item){
                if (strpos($item->token_oauth, 'bearer') !== false or strpos($item->token_oauth, 'BEARER') !== false) {
                    $access_token = substr($item->token_oauth, 7);
                } else {
                    $access_token = $item->token_oauth;
                }
            }
        }
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Authorization: " . $access_token . "\r\n",
                'method'  => 'PATCH',
                'content' => json_encode($params)
            )
        ));
        $url = env('LAMB_FINANCIAL') . '/setup/users/' . $id_persona;
        try {
            $respuesta = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            $respuesta = $e->getMessage();
        }
        return $respuesta;
    }

    public static function setRol($id_rol, $id_persona, $id_entidad, $id_depto)
    {
        $response = [];
        try {
            $response['ent_us'] = DB::table('eliseo.conta_entidad_usuario')->insertOrIgnore(
                [
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_PERSONA' => $id_persona,
                    'ESTADO' => '1'
                ]
            );

            $response['lamb_us_dpt'] = DB::table('eliseo.LAMB_USERS_DEPTO')->insertOrIgnore(
                [
                    'ID' => $id_persona,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ESTADO' => '1',
                    'ACTIVO' => '1'
                ]
            );
            //DB::table('eliseo.LAMB_USUARIO_ROL')->where('id_persona', $id_persona)->where('id_rol', $id_rol)->delete();

            $response['lamb_us_rol'] = DB::table('eliseo.LAMB_USUARIO_ROL')->insertOrIgnore(
                [
                    'ID_ROL' => $id_rol,
                    'ID_PERSONA' => $id_persona,
                    'ID_ENTIDAD' => $id_entidad
                ]
            );
        } catch (Exception $e) {
            $response['ent_us'] = false;
            $response['lamb_us_dpt'] = false;
            $response['lamb_us_rol'] = false;
        }
        return $response;
    }

    public static function resetPassword($request)
    {
        $jResponse['success'] = true;
        $jResponse['message'] = "Usuario Modificado en Lamb";
        $username = $request->username;
        $password = $request->password;

        $respuesta = null;
        $params = array('username' => $username, 'new_password1' => $password, 'new_password2' => $password);
        $headers = apache_request_headers();
        
        $headers = array_change_key_case($headers, CASE_LOWER);
        $access_token = null;
        if (isset($headers['authorization'])) {
            $access_token = $headers['authorization'];

            $results = DB::table('users_session us')
            ->select('us.token_oauth')
            ->where('us.token', '=', $access_token)
            ->get();
            $results[0]->token_oauth;

            foreach($results as $item){
                if (strpos($item->token_oauth, 'bearer') !== false or strpos($item->token_oauth, 'BEARER') !== false) {
                    $access_token = substr($item->token_oauth, 7);
                } else {
                    $access_token = $item->token_oauth;
                }
            }


        
        }


        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Bearer " . $access_token . "\r\n" .
                    "Content-Type: application/json\r\r",
                'method'  => 'POST',
                'content' => json_encode($params)
            )
        ));
        $url = env('OAUTH') . '/api/change_password_from_user/';
        try {
            $respuesta = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            //$respuesta = $e->getMessage();
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            // print_r($e->getMessage());
        }
        return $jResponse;
    }
}
