<?php
namespace App\Http\Data\Inventories;

use Illuminate\Support\Facades\DB;
use PDO;
class AlmacenRubroData {
    
    public static function listAlmacenRubros($id_entidad){
        $query = DB::table('INVENTARIO_RUBRO')
                ->select('ID_RUBRO','NOMBRE','ALIAS','CODIGO','ESTADO')
                ->where('ID_ENTIDAD', '=', $id_entidad)                
                ->orderBy('ID_RUBRO')->get();
        return $query;
    }
    public static function showAlmacenRubros($id_rubro){
        $query = DB::table('INVENTARIO_RUBRO')->select('ID_RUBRO','NOMBRE','ALIAS','CODIGO','ESTADO')->where('ID_RUBRO', $id_rubro)->first();     
        return $query;
    }
    public static function addAlmacenRubros($id_entidad,$nombre,$alias,$codigo, $estado){
        DB::table('INVENTARIO_RUBRO')->insert(
                        array('ID_ENTIDAD' =>$id_entidad,'NOMBRE' => $nombre,'ALIAS' =>$alias,'CODIGO' =>$codigo,'ESTADO' =>$estado)
                    );
        $query = "SELECT 
                        MAX(ID_RUBRO) ID_RUBRO
                FROM INVENTARIO_RUBRO";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_rubro = $id->id_rubro;
        }
        $sql = AlmacenRubroData::showAlmacenRubros($id_rubro);
        return $sql;
    }
    public static function updateAlmacenRubros($id_rubro, $nombre,$alias,$codigo,$estado){
        DB::table('INVENTARIO_RUBRO')
            ->where('ID_RUBRO', $id_rubro)
            ->update([
                'NOMBRE' => $nombre,
                'ALIAS' => $alias,
                'CODIGO' => $codigo,
                'ESTADO' => $estado
            ]);
        $sql = AlmacenRubroData::showAlmacenRubros($id_rubro);
        return $sql;
    }
    public static function deleteAlmacenRubros($id_rubro){
        DB::table('INVENTARIO_RUBRO')->where('ID_RUBRO', '=', $id_rubro)->delete();
    }
    
}