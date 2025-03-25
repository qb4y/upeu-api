<?php
namespace App\Http\Data\Inventories;

use Illuminate\Support\Facades\DB;
use PDO;
class AlmacenCategoriaData{
    
    public static function listAlmacenCategorias($id_entidad, $estado){
        
        $query = DB::table('INVENTARIO_RUBRO_CATEGORIA')
                ->join('INVENTARIO_RUBRO', 'INVENTARIO_RUBRO_CATEGORIA.ID_RUBRO', '=', 'INVENTARIO_RUBRO.ID_RUBRO')
                ->select('INVENTARIO_RUBRO_CATEGORIA.ID_RCATEGORIA',
                'INVENTARIO_RUBRO_CATEGORIA.ID_RUBRO',
                'INVENTARIO_RUBRO_CATEGORIA.NOMBRE',
                'INVENTARIO_RUBRO_CATEGORIA.ALIAS',
                'INVENTARIO_RUBRO_CATEGORIA.ESTADO',
                'INVENTARIO_RUBRO.NOMBRE AS RUBRO_NOMBRE'
                )
                ->where('INVENTARIO_RUBRO_CATEGORIA.ID_ENTIDAD', '=', $id_entidad);
        if($estado) {
            $query->where('INVENTARIO_RUBRO_CATEGORIA.ESTADO', '=', $estado);
        }
        return $query->orderBy('INVENTARIO_RUBRO_CATEGORIA.ID_RUBRO')->get();
    }
    public static function showAlmacenCategorias($id_rcategoria){
        $query = DB::table('INVENTARIO_RUBRO_CATEGORIA')
        ->select('ID_RCATEGORIA','ID_RUBRO','NOMBRE','ALIAS','ESTADO')
        ->where('ID_RCATEGORIA', $id_rcategoria)->first();     
        return $query;
    }
    public static function addAlmacenCategorias($id_entidad,$id_rubro,$nombre,$alias,$estado){
        DB::table('INVENTARIO_RUBRO_CATEGORIA')->insert(
                        array('ID_ENTIDAD' =>$id_entidad,'ID_RUBRO' => $id_rubro,'NOMBRE' => $nombre,'ALIAS' =>$alias,'ESTADO' =>$estado)
                    );
        $query = "SELECT 
                        MAX(ID_RCATEGORIA) ID_RCATEGORIA
                FROM INVENTARIO_RUBRO_CATEGORIA";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_rcategoria = $id->id_rcategoria;
        }
        $sql = AlmacenCategoriaData::showAlmacenCategorias($id_rcategoria);
        return $sql;
    }
    public static function updateAlmacenCategorias($id_rcategoria, $id_rubro,$nombre,$alias,$estado){
        DB::table('INVENTARIO_RUBRO_CATEGORIA')
            ->where('ID_RCATEGORIA', $id_rcategoria)
            ->update([
                'ID_RUBRO' => $id_rubro,
                'NOMBRE' => $nombre,
                'ALIAS' => $alias,
                'ESTADO' => $estado
            ]);
        $sql = AlmacenCategoriaData::showAlmacenCategorias($id_rcategoria);
        return $sql;
    }
    public static function deleteAlmacenCategorias($id_rcategoria){
        DB::table('INVENTARIO_RUBRO_CATEGORIA')->where('ID_RCATEGORIA', '=', $id_rcategoria)->delete();
    }
    
}