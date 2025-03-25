<?php

namespace App\Http\Controllers\Setup\Modulo;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Modulo\ModuloData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Support\Facades\Input;

class ModuloController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function listModules()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $shwModulo = ModuloData::getModuloChildrenToLambFinancial();
            // $shwModulo = ModuloData::get_modulo();

            if ($shwModulo) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $shwModulo;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist.';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listModulesRoot()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $shwModulo = ModuloData::getModuloRoot();
            if ($shwModulo) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $shwModulo;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist.';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listModulesChildrens($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = $this->recursiveModules($id_modulo,1);
                $jResponse['success'] = true;
                $jResponse['message'] = "ok";
                $jResponse['data'] =  $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function recursiveModules($id_modulo, $x)
    {
        $parent = [];
        if ($x == 1) {
            $data = ModuloData::showModules($id_modulo);
        } else {
            $data = ModuloData::listModulesChildrens($id_modulo);
        }
        $x++;
        foreach ($data as $key => $value) {
            $row = $this->recursiveModules($value->id_modulo, $x);
            $parent[] = [
                'id_modulo' => $value->id_modulo,
                'id_padre' => $value->id_padre,
                'nivel' => $value->nivel,
                'nombre' => $value->nombre,
                'url' => $value->url,
                'imagen' => $value->imagen,
                'orden' => $value->orden,
                'estado' => $value->estado,
                'es_activo' => $value->es_activo,
                'codigo' => $value->codigo,
                'is_group' => $value->is_group,
                'is_absolute' => $value->is_absolute,
                'id_tipoplataforma' => $value->id_tipoplataforma,
                'children'=>$row];
        }
        return $parent;
    }
    public function showModules($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $shwModulo = ModuloData::showModules($id_modulo);
            if ($shwModulo) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $shwModulo[0];
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addModules()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_padre = $params->data->id_padre;
            $nombre = $params->data->nombre;
            $nivel = $params->data->nivel;
            $url = $params->data->url;
            $imagen = $params->data->imagen;
            $orden = $params->data->orden;
            $es_activo = $params->data->es_activo;
            $is_group =  property_exists($params->data, 'is_group') ? $params->data->is_group : null;
            $id_tipoplataforma = property_exists($params->data, 'id_tipoplataforma') ? $params->data->id_tipoplataforma : '1';
            $is_absolute = property_exists($params->data, 'is_absolute') ? $params->data->is_absolute : null;
            $codigo = property_exists($params->data, 'codigo') ? $params->data->codigo : null;
            $accesoxnivel = property_exists($params->data, 'accesoxnivel') ? $params->data->accesoxnivel : null;

            try{
                $data = ModuloData::addModules($id_padre, $nombre, $nivel, $url, $imagen, $orden, $es_activo, $codigo, $accesoxnivel, $is_group,$is_absolute,$id_tipoplataforma);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully - test accesoxnivel";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateModules($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_padre = $params->id_padre;
            $nombre = $params->nombre;
            $nivel = $params->nivel;
            $url = $params->url;
            $imagen = $params->imagen;
            $orden = $params->orden;
            $es_activo = $params->es_activo;
            $codigo = $params->codigo;
            $is_group = property_exists($params, 'is_group') ? $params->is_group : null;
            $is_absolute = property_exists($params, 'is_group') ? $params->is_absolute : null;
            $id_tipoplataforma = property_exists($params, 'id_tipoplataforma') ? $params->id_tipoplataforma : null;
            $accesoxnivel = $params->accesoxnivel;
            try{
                $data = ModuloData::updateModules($id_modulo,$id_padre, $nombre, $nivel, $url, $imagen, $orden, $es_activo,$codigo, $accesoxnivel,$is_group, $is_absolute,$id_tipoplataforma);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteModules($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ModuloData::deleteModules($id_modulo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    // public function showModulobk($id){
    //     $jResponse = [
    //         'success' => false,
    //         'message' => 'ERROR'
    //     ];
    //     $listModulo = ModuloData::show_modulo($id);
    //     if ($listModulo) {
    //         $jResponse['success'] = true;
    //         $jResponse['message'] = 'OK';
    //         $jResponse['data'] = ['items' => $listModulo];

    //     }
    //     return response()->json($jResponse);
    // }
    public function showModulo(){
        $params = json_decode(file_get_contents("php://input"));

        $id_rol = $params->data->id_rol;
        $id_modulo = $params->data->id_modulo;
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        try {
            $listModulo = ModuloData::show_modulo($id_rol, $id_modulo);
            if ($listModulo) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $listModulo];
            }
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($jResponse);
    }

    public function addRolModulo()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        $params = json_decode(file_get_contents("php://input"));

        $id_rol = $params->data->id_rol;
        $modulo = $params->data->id_modulo;
        $id_grupo = $params->data->id_grupo;
        try {
            //DB::table('LAMB_ROL_MODULO')->where('ID_ROL', '=', $id_rol)->delete();

            $sql = "DELETE LAMB_ROL_MODULO
                    WHERE ID_ROL = ?
                    AND ID_MODULO IN (
                    SELECT
                            ID_MODULO
                    FROM LAMB_MODULO A
                    START WITH ID_MODULO = ?
                    CONNECT BY PRIOR ID_MODULO = ID_PADRE
                    ) ";
            DB::delete($sql, array($id_rol, $id_grupo));

            foreach ($modulo as $id) {
                DB::table('LAMB_ROL_MODULO')->insert(
                    array('ID_ROL' => $id_rol, 'ID_MODULO' => $id)
                );
            }
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = 'ERROR';
            $jResponse['message_error'] = 'DATO EXISTE';
            $jResponse['data'] = '';
        }
        return response()->json($jResponse);
    }
    public function updateRol()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR AL EDITAR',
            'data' => array()
        ];

        $params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;
        $name = $params->data->name;
        $state = $params->data->state;
        try {
            DB::table('LAMB_ROL')
                ->where('ID_ROL', $id)
                ->update([
                    'NOMBRE' => $name,
                    'ESTADO' => $state
                ]);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = 'ERROR AL EDITAR';
            $jResponse['data'] = '';
        }
        return response()->json($jResponse);
    }
    public function deleteRol()
    {
        $jResponse = [
            'success' => false,
            'message' => 'DELETE',
            'data' => array()
        ];

        /*$params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;

        $delete = DB::table('test')->where('id', '=', $id)->delete();

        if ($delete) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }*/

        return response()->json($jResponse);
    }
    public function searchPerson(Request $request)
    {
        $person = "";
        //$params = json_decode(file_get_contents("php://input"));
        $person   = $request->query('text_search');
        //$person = $params->data->person;
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        try {
            $datos = ModuloData::searchPerson($person);
            if ($datos) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $datos];
            }
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($jResponse);
    }
    public function createUser()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];

            $params = json_decode(file_get_contents("php://input"));
            $id = $params->data->id;
            $email = $params->data->email;
            $senha = "123456";
            $name = $params->data->name;
            $id_perfil = $params->data->id_perfil;
            /*$jResponse = [
                'success' => false,
                'message' => 'ERROR'
            ];*/
            try {
                $usuario = ModuloData::existUsuario($id);
                if (count($usuario) > 0) {
                    $jResponse['success'] = false;
                    $jResponse['data'] = [];
                    $jResponse['message'] = "User " . $name . " YA EXISTE";
                    $code = "202";
                } else {
                    DB::table('USERS')->insert(
                        array('ID' => $id, 'EMAIL' => $email, 'CONTRASENHA' => bcrypt($senha), 'NAME' => $name, 'ID_PERFIL' => $id_perfil, 'FECHA_CREATE' => DB::raw('SYSDATE'))
                    );
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = "User " . $name . " Registrado";
                    $code = "201";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    // public function addUsersEnterprises(Request $request){
    //     $jResponse = GlobalMethods::authorizationLamb($this->request);
    //     $code   = $jResponse["code"];
    //     $valida = $jResponse["valida"];
    //     $id_user = $jResponse["id_user"];
    //     dd($jResponse);
    //     $token = $jResponse["token"];
    //     if($valida=='SI'){
    //         try{
    //             $id_entidad = Input::get('id_entidad');
    //             $id_depto = Input::get('id_depto');
    //             $data = ModuloData::addUsersEnterprises($id_user,$id_entidad,$id_depto,$token);
    //             $jResponse['success'] = true;
    //             $jResponse['message'] = 'OK';
    //             $jResponse['data'] = $data;
    //             $code = "201";
    //         }catch(Exception $e){
    //             $jResponse['success'] = false;
    //             $jResponse['message'] = $e->getMessage();
    //             $jResponse['data'] = [];
    //             $code = "202";
    //         }
    //     }
    //     return response()->json($jResponse,$code);
    // }
    public function assignUserRol($id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                //dd($params);
                $id_entidad = $params->data->id_entidad;
                $rol = $params->data->rol;
                //$depto = $params->data->depto;
                //Roles
                $sql = "DELETE FROM LAMB_USUARIO_ROL WHERE ID_PERSONA = ? AND ID_ENTIDAD = ? ";
                DB::delete($sql, array($id, $id_entidad));

                foreach ($rol as $id_rol) {
                    DB::table('LAMB_USUARIO_ROL')->insert(
                        array('ID_ROL' => $id_rol, 'ID_PERSONA' => $id, 'ID_ENTIDAD' => $id_entidad, 'ID_USER' => $id_user)
                    );
                    $rolmodulo = ModuloData::listRoleModule($id_rol);
                    foreach ($rolmodulo as $item) {
                        $depto = ModuloData::listMyDepartment($id_entidad, $id);
                        foreach ($depto as $row) {
                            $themaUser = ModuloData::showUsersThemes($id, $id_entidad, $row->id_depto, $item->id_modulo);
                            if (count($themaUser) === 0) {
                                if ($row->es_empresa === '1') {
                                    ModuloData::addUsersThemes($id, $id_entidad, $row->id_depto, $item->id_modulo, 1, '');
                                }
                            }
                        }
                    }
                }
                //Deptos
                /*$sql = "DELETE FROM LAMB_USERS_DEPTO WHERE ID = ? AND ID_ENTIDAD = ? ";
                DB::delete($sql,array($id,$id_entidad));
                foreach ($depto as $id_depto){
                    DB::table('LAMB_USERS_DEPTO')->insert(
                        array('ID' => $id, 'ID_ENTIDAD' => $id_entidad,'ID_DEPTO' => $id_depto)
                    );
                }*/
                $jResponse['success'] = true;
                $jResponse['message'] = 'Roles asignados';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function assignUserDepto($id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $id_entidad = $params->data->id_entidad;
                $depto = $params->data->depto;
                // ENTIDAD
                $entidadusuario = ModuloData::existEntidadUsuario($id_entidad, $id);
                // dd(count($entidadusuario) === 0, count($depto) === 0);
                if (count($entidadusuario) === 0) {
                    if (count(ModuloData::showExistEntidadUsuario($id)) > 0) {
                        $estado = 0;
                    } else {
                        $estado = 1;
                    }
                    DB::table('CONTA_ENTIDAD_USUARIO')->insert(
                        array('ID_ENTIDAD' => $id_entidad, 'ID_PERSONA' => $id, 'ESTADO' => $estado)
                    );
                }

                // if(count($entidadusuario) === 0) {
                // Si existe la entidad usuario.
                if (count($depto) === 0) {
                    // 2 = Eliminado
                    DB::table('CONTA_ENTIDAD_USUARIO')->where('ID_ENTIDAD', $id_entidad)
                        ->where('ID_PERSONA', $id)->update([
                            'ESTADO' => 2
                        ]);

                    // Eliminar LAMB_USERS_DEPTO del usuario en una entidad; 3=Eliminado LAMB_USERS_DEPTO
                    DB::table('LAMB_USERS_DEPTO')->where('ID', $id)->where('ID_ENTIDAD', $id_entidad)
                        ->update([
                            'ESTADO' => 0,
                            'ACTIVO' => '3'
                        ]);
                } else {
                    // Asignar nuevamente entidad.
                    DB::table('CONTA_ENTIDAD_USUARIO')->where('ID_ENTIDAD', $id_entidad)
                        ->where('ID_PERSONA', $id)->update([
                            'ESTADO' => 0
                        ]);
                    // Eliminar LAMB_USERS_DEPTO del usuario en una entidad.
                    DB::table('LAMB_USERS_DEPTO')->where('ID', $id)->where('ID_ENTIDAD', $id_entidad)
                        ->update([
                            'ESTADO' => 0,
                            'ACTIVO' => '3'
                        ]);
                    foreach ($depto as $id_depto) {
                        $userdepto = ModuloData::showUsersDepto($id, $id_entidad, $id_depto);
                        if (count($userdepto) === 0) {
                            DB::table('LAMB_USERS_DEPTO')->insert(
                                array('ID' => $id, 'ID_ENTIDAD' =>
                                $id_entidad, 'ID_DEPTO' => $id_depto, 'ESTADO' => 0, 'ACTIVO' => '1')
                            );
                        } else {
                            DB::table('LAMB_USERS_DEPTO')
                                ->where('ID', $id)
                                ->where('ID_ENTIDAD', $id_entidad)
                                ->where('ID_DEPTO', $id_depto)
                                ->update([
                                    'ESTADO' => 0,
                                    'ACTIVO' => '1'
                                ]);
                        }
                    }
                }
                // }
                //Deptos --inserta depto pequeños
                /*$sql = "DELETE FROM LAMB_USERS_DEPTO WHERE ID = ? AND ID_ENTIDAD = ? AND LENGTH(ID_DEPTO) <> 1 ";
                DB::delete($sql,array($id,$id_entidad));
                foreach ($depto as $id_depto){
                    if(strlen($id_depto) > 1){
                        DB::table('LAMB_USERS_DEPTO')->insert(
                            array('ID' => $id, 'ID_ENTIDAD' => $id_entidad,'ID_DEPTO' => $id_depto)
                        );
                    }
                }
                $sql = "DELETE FROM LAMB_USERS_DEPTO WHERE ID = ? AND ID_ENTIDAD = ? AND LENGTH(ID_DEPTO) = 1 ";
                DB::delete($sql,array($id,$id_entidad));*/
                // $sql = "UPDATE LAMB_USERS_DEPTO SET ACTIVO = '3' WHERE ID = ? AND ID_ENTIDAD = ? AND LENGTH(ID_DEPTO) = 1 ";
                $jResponse['success'] = true;
                $jResponse['message'] = 'Departamentos Asignados';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function UserDeptoDefault(Request $request, $id, $id_depto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                $estado = $params->data->estado;
                $id_entidad   = $request->query('id_entidad');
                if ($estado == true) {
                    $datos = "1";
                    // Desactivar a todos de default.
                    DB::table('LAMB_USERS_DEPTO')->where('ID', $id)->where('ID_ENTIDAD', $id_entidad)->update([
                        'ESTADO' => 0
                    ]);
                } else {
                    $datos = "0";
                }
                $data = DB::table('LAMB_USERS_DEPTO')->select('ID_DEPTO')->where('ID', $id)
                    ->where('ID_ENTIDAD', $id_entidad)->where('ID_DEPTO', $id_depto)->get();
                if (count($data) > 0) {
                    DB::table('LAMB_USERS_DEPTO')->where('ID', $id)->where('ID_ENTIDAD', $id_entidad)
                        ->where('ID_DEPTO', $id_depto)->update([
                            'ESTADO' => $datos
                        ]);
                } else {
                    DB::table('LAMB_USERS_DEPTO')->insert(
                        array('ID' => $id, 'ID_ENTIDAD' => $id_entidad, 'ID_DEPTO' => $id_depto, 'ESTADO' => $datos)
                    );
                }
                DB::table('CONTA_ENTIDAD_USUARIO')->where('ID_ENTIDAD', $id_entidad)
                    ->where('ID_PERSONA', $id)->update([
                        'ESTADO' => 1
                    ]);
                /*DB::table('LAMB_USERS_DEPTO')->where('ID', $id)->where('ID_ENTIDAD', $id_entidad)->where('ID_DEPTO', $id_depto)->update([
                    'ESTADO' => $datos
                ]);*/

                $jResponse['success'] = true;
                $jResponse['message'] = 'Departamentos Asignado por Defualt';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showUserRol(Request $request, $id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad   = $request->query('id_entidad');
                $datos = ModuloData::showUserRol($id_entidad, $id);
                if ($datos) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $datos];
                    $code = "200";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showUser()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        try {
            $datos = ModuloData::showUser();
            if ($datos) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $datos];
            }
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($jResponse);
    }
    public function verUser($id)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $datos = ModuloData::verUser($id);
                $data = [];
                if ($datos) {
                    $data = $datos[0];
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function editUser($id)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $name = $params->data->name;
            $id_perfil = $params->data->id_perfil;
            $direccion = $params->data->direccion;
            $doc_number = $params->data->doc_number;
            $doc_type = $params->data->doc_type;
            try {
                $datos = ModuloData::editUser($id, $name, $id_perfil);
                if ($datos) {
                    $response_oauth = ModuloData::usersOauth($id, $params);
                    if ($response_oauth['success'] == true) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = $response_oauth['message'];
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $response_oauth['message'];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listUser(Request $request)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $size_page = $request->query('size_page');
                $text_search = $request->query('text_search');
                $datos = ModuloData::ListUser($size_page, $text_search);
                if ($datos) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function userDepto(Request $request, $id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad   = $request->query('id_entidad');
                $asignado   = $request->query('asignado');
                $data = $this->recursive_userDepto($id_entidad, "A", $id, $asignado);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $data[0]];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function userDeptoParentAsigando(Request $request, $id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad   = $request->query('id_entidad');
                //                dd('entidad', $id_entidad);
                $data = ModuloData::userDeptoParentAsigando($id, $id_entidad);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function recursive_userDepto($id_entidad, $id_parent, $per_id, $asignado)
    {
        $parent = [];
        $checked = false;
        $data = ModuloData::userDeptoParent($id_entidad, $id_parent, $per_id, $asignado);
        foreach ($data as $key => $value) {
            if ($value->asignado == 0) {
                $checked = false;
            } else {
                $checked = true;
            }
            $row = $this->recursive_userDepto($id_entidad, $value->id_depto, $per_id, $asignado);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre, 'checked' => $checked, 'children' => $row];
        }
        return $parent;
    }
    public function entityDepto()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR - no data',
            'data' => array()
        ];
        $params = json_decode(file_get_contents("php://input"));
        $id_entidad = $params->data->entity;
        try {
            $data = $this->recursive_EntityDepto($id_entidad, "A");
            $jResponse['message'] = "SUCCES";
            $jResponse['success'] = true;
            $jResponse['data'] = ['items' => $data[0]];
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($jResponse);
    }
    public function recursive_EntityDepto($id_entidad, $id_parent)
    {
        $parent = [];
        $data = ModuloData::entityDepto($id_entidad, $id_parent);
        $checked = false;
        foreach ($data as $key => $value) {

            $row = $this->recursive_EntityDepto($id_entidad, $value->id_depto);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre, 'checked' => $checked, 'children' => $row];
        }
        return $parent;
    }
    public function recursive_DinamicaAsientoDepto($id_entidad, $id_parent, $id_asiento)
    {
        $parent = [];
        $data = ModuloData::dinamicaAsientoDepto($id_entidad, $id_parent, $id_asiento);
        $checked = false;
        /*foreach ($data as $key => $value){

            $row = $this->recursive_DinamicaAsientoDepto($id_entidad,$value->id_depto,$id_asiento);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre, 'checked'=>$checked, 'children'=>$row];
        }*/
        foreach ($data as $key => $value) {
            if ($value->asignado == 0) {
                $checked = false;
            } else {
                $checked = true;
            }
            $row = $this->recursive_DinamicaAsientoDepto($id_entidad, $value->id_depto, $id_asiento);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre, 'checked' => $checked, 'children' => $row];
        }
        return $parent;
    }
    public function entityDepartamento(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_asiento  = $request->query('id_asiento');
                if (is_null($id_asiento)) {
                    $id_asiento = '0';
                }

                $data = $this->recursive_DinamicaAsientoDepto($id_entidad, "A", $id_asiento);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $data[0]];
            } catch (Exception $e) {
                dd($e);
            }
        }
        return response()->json($jResponse);
    }
    public function listPerfil()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $datos = ModuloData::ListPerfil();
                if ($datos) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listModulesActions($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $actions = ModuloData::listModulesActions($id_modulo);
            if ($actions) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $actions;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addModulesActions($id_modulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $clave = $params->data->clave;
            $valor = $params->data->valor;
            $estado = $params->data->estado;
            try {
                $data = ModuloData::addModulesActions($id_modulo, $nombre, $clave, $valor, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateModulesActions($id_modulo, $id_accion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $clave = $params->data->clave;
            $valor = $params->data->valor;
            $estado = $params->data->estado;
            try {
                $data = ModuloData::updateModulesActions($id_accion, $nombre, $clave, $valor, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteModulesActions($id_modulo, $id_accion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ModuloData::deleteModulesActions($id_accion);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listThemes(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $tipo_modulo = $request->query('tipo_modulo');
                $data = ModuloData::listThemes($tipo_modulo);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addUsersThemes()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = json_decode(file_get_contents("php://input"));
                //$id_entidad = $params->id_entidad;
                //$id_depto = $params->id_depto;
                $id_modulo = $params->id_modulo;
                $id_tema = $params->id_tema;
                $sidebar = $params->sidebar;
                $data = ModuloData::showUsersThemes($id_user, $id_entidad, $id_depto, $id_modulo);
                if (count($data) > 0) {
                    $datos = ModuloData::updateUsersThemes($id_user, $id_entidad, $id_depto, $id_modulo, $id_tema, $sidebar);
                } else {
                    $datos = ModuloData::addUsersThemes($id_user, $id_entidad, $id_depto, $id_modulo, $id_tema, $sidebar);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showUsersThemes(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_modulo = $request->query('id_modulo');

                if ($id_modulo == null) {
                    $data = ModuloData::listUsersThemes($id_user, $id_entidad, $id_depto);
                    if ($data) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $data = ModuloData::showUsersThemes($id_user, $id_entidad, $id_depto, $id_modulo);
                    if (count($data) > 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data[0];
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addUsersEnterprises()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $token = $jResponse["token"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = Input::get('id_entidad');
                $id_depto = Input::get('id_depto');
                $data = ModuloData::addUsersEnterprises($id_user, $id_entidad, $id_depto, $token);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'Entities and Department not asigned';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyEntities()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ModuloData::listMyEntities($id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listUsersEntities(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_persona = $request->query('id_persona');
                if ($id_persona != null) {
                    $data = ModuloData::listMyEntities($id_persona);
                    if ($data) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Attention: Check, missing parameters';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyDepartment(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $data = ModuloData::listMyDepartment($id_entidad, $id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyDepartmentsChildrens()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //$id_entidad = $request->query('id_entidad');
                $data = ModuloData::listMyDepartmentsChildrens($id_entidad, $id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listDepartmentsChildrens(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $depto = $request->query('depto');
                $data = ModuloData::listDepartmentsChildrens($id_entidad, $depto);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listEntityDepartments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        //        dd('holaa');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = array(
                    'text_search' => $request->query('text_search'),
                    'page_size' => $request->query('page_size') ? $request->query('page_size') : 10,
                    'id_depto' => $id_depto
                );
                $data = ModuloData::listEntityDepartments($id_entidad, $params);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showUsersEnterprises()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ModuloData::showUsersEnterprises($id_user, $id_depto);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function usersDeptosParents(Request $request, $id)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad   = $request->query('id_entidad');
            try {
                $data = ModuloData::usersDeptosParents($id, $id_entidad);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $data];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listServicesByUser(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        /*$id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];*/
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $servicios = ParameterData::getServicios();
                $asignados = ParameterData::getServicioByUser($request->id_usuario);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [
                    "servicios" => $servicios,
                    "asignados" => $asignados
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function servicesByUser(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        /*$id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];*/
        //-----------------//
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::registroServiciosUsuario($request);
                if ($data["success"]) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data['data'];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
}
