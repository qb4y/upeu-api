<?php
namespace App\Http\Data\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listRoles(){
        $getRoles = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->orderBy('ESTADO', 'desc')->orderBy('ID_ROL', 'asc')->get();        
        return $getRoles;
    }
    public static function showRoles($id_rol){
        $getRol = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->where('ID_ROL', $id_rol)->get();     
        return $getRol;
    }
    public static function addRoles($nombre,$estado){
        DB::table('LAMB_ROL')->insert(
                    array('NOMBRE' => $nombre,
                        'ESTADO' => $estado)
                );
        $query = "SELECT 
                        MAX(ID_ROL) ID_ROL
                FROM LAMB_ROL ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_rol = $id->id_rol;
        }
        $getRol = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->where('ID_ROL', $id_rol)->get(); 
        return $getRol;
    }
    public static function updateRoles($id_rol,$nombre,$estado){
        DB::table('LAMB_ROL')
            ->where('ID_ROL', $id_rol)
            ->update([
                'NOMBRE' => $nombre,
                'ESTADO' => $estado
            ]);
        $getRol = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->where('ID_ROL', $id_rol)->get(); 
        return $getRol;
    }
    public static function deleteRoles($id_rol){
        DB::table('LAMB_ROL')->where('ID_ROL', '=', $id_rol)->delete();
    }
    public static function listRolesModulesAsingados($id_modulo,$id_rol){
        $query = "SELECT 
                    A.ID_MODULO,A.ID_PADRE,A.NIVEL,A.NOMBRE,A.URL,A.IMAGEN,A.ORDEN,A.ESTADO,A.ES_ACTIVO
                    FROM LAMB_MODULO A
                    WHERE A.ID_PADRE = ".$id_modulo."
                    AND A.ID_MODULO IN (
                        SELECT ID_MODULO FROM LAMB_ROL_MODULO
                        WHERE ID_ROL = ".$id_rol."
                    )
                    ORDER BY A.ORDEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listRolesNoAsigandos($id_modulo,$id_rol){
        $query = "SELECT 
                    A.ID_MODULO,A.ID_PADRE,A.NIVEL,A.NOMBRE,A.URL,A.IMAGEN,A.ORDEN,A.ESTADO,A.ES_ACTIVO
                    FROM LAMB_MODULO A
                    WHERE A.ID_PADRE = ".$id_modulo."
                    AND A.ID_MODULO NOT IN (
                        SELECT ID_MODULO FROM LAMB_ROL_MODULO
                        WHERE ID_ROL = ".$id_rol."
                    )
                    ORDER BY A.ORDEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listRolesModules($id_modulo,$id_rol){
        $query = "SELECT 
                    A.ID_MODULO,A.ID_PADRE,A.NIVEL,A.NOMBRE,A.URL,A.IMAGEN,A.ORDEN,A.ESTADO,A.ES_ACTIVO,
                    (SELECT COUNT(B.ID_MODULO) FROM LAMB_ROL_MODULO B WHERE B.ID_MODULO = A.ID_MODULO AND B.ID_ROL = ".$id_rol.") AS ASIGNADO
                    FROM LAMB_MODULO A
                    WHERE A.ID_PADRE = ".$id_modulo." 
                    ORDER BY A.ORDEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addRolesModules($id_rol,$id_modulo){
        DB::table('LAMB_ROL_MODULO')->insert(
                        array('ID_ROL' => $id_rol, 'ID_MODULO' => $id_modulo)
                    );
    }
    public static function deleteRolesModules($id_rol,$id_modulo){
        DB::table('LAMB_ROL_MODULO')->where('ID_ROL', '=', $id_rol)->where('ID_MODULO', '=', $id_modulo)->delete();
    }
    public static function listRolesModulesActions($id_rol,$id_modulo){
        $query = "SELECT 
                    A.ID_ACCION,A.ID_MODULO,A.NOMBRE,A.METODO,A.CLAVE,A.VALOR,A.ESTADO,
                    (SELECT COUNT(X.ID_ACCION) FROM LAMB_ROL_MODULO_ACCION X WHERE X.ID_MODULO = A.ID_MODULO AND X.ID_ACCION = A.ID_ACCION AND X.ID_ROL = ".$id_rol.") CANT
                    FROM LAMB_ACCION A
                    WHERE A.ID_MODULO = ".$id_modulo." 
                    AND estado = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showRolModuloActions($id_rol,$id_modulo,$id_accion){
        $oQuery = DB::table('LAMB_ROL_MODULO_ACCION')->select('ID_ROL','ID_MODULO','ID_ACCION')->where('ID_ROL', $id_rol)->where('ID_MODULO', $id_modulo)->where('ID_ACCION', $id_accion)->get();        
        return $oQuery;
    }
    public static function addRolesModulesActions($id_rol,$id_modulo,$id_adccion){
        DB::table('LAMB_ROL_MODULO_ACCION')->insert(
                        array('ID_ROL' => $id_rol, 'ID_MODULO' => $id_modulo,'ID_ACCION' => $id_adccion)
                    );
    }
    public static function deleteRolesModulesActions($id_rol,$id_modulo,$id_adccion){
        DB::table('LAMB_ROL_MODULO_ACCION')->where('ID_ROL', '=', $id_rol)->where('ID_MODULO', '=', $id_modulo)->where('ID_ACCION', '=', $id_adccion)->delete();
    }
    public static function listResources(){
        $getResource = DB::table('LAMB_RESOURCE')->select('ID_RESOURCE','NOMBRE','RUTA','DETALLE','ESTADO')->orderBy('ID_RESOURCE')->get(); 
        return $getResource;
    }
    public static function showResources($id_resource){
        $getResource = DB::table('LAMB_RESOURCE')->select('ID_RESOURCE','NOMBRE','RUTA','DETALLE','ESTADO')->where('ID_RESOURCE', $id_resource)->get(); 
        return $getResource;
    }
    public static function addResources($nombre,$ruta,$detalle,$estado){
        DB::table('LAMB_RESOURCE')->insert(
                        array('NOMBRE' => $nombre, 'RUTA' => $ruta,'DETALLE' => $detalle,'ESTADO' => $estado)
                    );
        $query = "SELECT 
                        MAX(ID_RESOURCE) ID_RESOURCE
                FROM LAMB_RESOURCE ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_resource = $id->id_resource;
        }
        $getResource = RolData::showResources($id_resource);
        return $getResource;
    }
    public static function updateResources($id_resource,$nombre,$ruta,$detalle,$estado){
        DB::table('LAMB_RESOURCE')
            ->where('ID_RESOURCE', $id_resource)
            ->update([
                'NOMBRE' => $nombre,
                'RUTA' => $ruta,
                'DETALLE' => $detalle,
                'ESTADO' => $estado
            ]);
        $getResource = RolData::showResources($id_resource);
        return $getResource;
    }
    public static function deleteResources($id_resource){
        DB::table('LAMB_RESOURCE')->where('ID_RESOURCE', '=', $id_resource)->delete();
    }
    public static function lisTbecas($request) {
        $id_rol = $request->id_rol;
        $becasAsignadas = DB::table('eliseo.lamb_rol_beca')
                            ->where('id_rol', $id_rol)
                            ->pluck('id_tipo_requisito_beca');

        $query = DB::table('david.tipo_requisito_beca')
                    ->whereNotIn('id_tipo_requisito_beca', $becasAsignadas)
                    ->where('estado', 1)
                    ->select('id_tipo_requisito_beca', 'nombre', 'codigo', 'estado', 'modalidad')
                    ->get();
        return $query;
      }
      public static function becaRol($request) {
        $id_rol = $request->id_rol;
        $query = DB::table('eliseo.lamb_rol_beca as a')
                            ->join('david.tipo_requisito_beca as b', 'a.id_tipo_requisito_beca', '=', 'b.id_tipo_requisito_beca')
                            ->where('a.id_rol', $id_rol)
                            ->select('a.id_tipo_requisito_beca', 'a.id_rol', DB::raw("to_char(a.fecha, 'YYYY-MM-DD') AS fecha_asignada"), 'a.estado', 'b.nombre')
                            ->get();
        return $query;
      }
      public static function deleteBecaRol($id_tipo_requisito_beca, $id_rol) {

        $delete = DB::table('eliseo.lamb_rol_beca')
            ->where('id_rol', $id_rol)
            ->where('id_tipo_requisito_beca', $id_tipo_requisito_beca)
            ->delete();
        
        if($delete){
            $response=[
                'success'=> true,
                'message'=>'Se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        } 
        return $response;
      }
    public static function addBecaRol($request, $fecha_register) {
        $detail       = $request->details;
        $id_rol       = $request->id_rol;
        $estado       = $request->estado;

        foreach ($detail as $datos) {
            $items = (object)$datos;
            $save = DB::table('eliseo.lamb_rol_beca')
            ->insert(
            [
              'id_tipo_requisito_beca'              => $items->id_tipo_requisito_beca,
              'id_rol'                              => $id_rol,
              'fecha'                               => $fecha_register,
              'estado'                              => $estado,
            ]
        );
        if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se registro satisfactoriamente',
                ];
        }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede registrar',
                ];
        }
        }

        return $response;
    }
    public static function updateBecaRol($id_rol, $request) {
        $detail       = $request->details;
        foreach ($detail as $datos) {
            $items = (object)$datos;
            if ($items->checked) {
                $estado = '1';
            } else {
                $estado = '0';
            }
            $save = DB::table('eliseo.lamb_rol_beca')
            ->where('id_rol', $id_rol)
            ->where('id_tipo_requisito_beca', $items->id_tipo_requisito_beca)
            ->update(
            [
              'estado'                              => $estado,
            ]
        );
        if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se registro satisfactoriamente',
                ];
        }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede registrar',
                ];
        }
        }

        return $response;
    }
}