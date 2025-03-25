<?php
namespace App\Http\Data\Inventory\Article;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listArticle($parent){
        if($parent == "A"){
            $dato = "WHERE ID_PARENT IS NULL" ;
        }else{
            $dato = "WHERE ID_PARENT = $parent";
        }                
        $query = "SELECT 
                        ID_ARTICULO,
                        ID_PARENT,
                        NOMBRE 
                FROM INVENTARIO_ARTICULO
                $dato
                ORDER BY ID_ARTICULO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArticle($id_articulo){                
        $query = "SELECT 
                        ID_ARTICULO,
                        ID_PARENT,
                        ID_UNIDADMEDIDA,
                        ID_MARCA,
                        ID_CUBSO,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ARTICULO
                WHERE ID_ARTICULO = '".$id_articulo."'";
        $oQuery = DB::select($query);
        return $oQuery;
    }
}