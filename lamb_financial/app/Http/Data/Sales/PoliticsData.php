<?php
namespace App\Http\Data\Sales;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PoliticsData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listPrices($id_almacen,$id_anho,$id_parent){                
        $query = "SELECT 
                        B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,
                        D.DESCRIPCION AS TIPOIGV_DESCRIPCION
                FROM INVENTARIO_ARTICULO A 
                LEFT JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO 
                LEFT JOIN VENTA_PRECIO C ON B.ID_ALMACEN = C.ID_ALMACEN
                                        AND B.ID_ARTICULO = C.ID_ARTICULO
                                        AND B.ID_ANHO = C.ID_ANHO
                INNER JOIN TIPO_IGV D ON B.ID_TIPOIGV=D.ID_TIPOIGV

                WHERE B.ID_ALMACEN = ".$id_almacen."
                AND B.ID_ANHO = ".$id_anho."
                AND A.ID_PARENT = ".$id_parent."
                ORDER BY PRECIO,NOMBRE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPricesSearch($id_almacen,$id_anho,$search){                
        $query = "SELECT DISTINCT
                        B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,
                        D.DESCRIPCION AS TIPOIGV_DESCRIPCION
                FROM INVENTARIO_ARTICULO A 
                LEFT JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO 
                LEFT JOIN VENTA_PRECIO C ON B.ID_ALMACEN = C.ID_ALMACEN
                                        AND B.ID_ARTICULO = C.ID_ARTICULO
                                        AND B.ID_ANHO = C.ID_ANHO
                INNER JOIN TIPO_IGV D ON B.ID_TIPOIGV=D.ID_TIPOIGV
                LEFT JOIN INVENTARIO_ARTICULO_CODIGO X
                ON A.ID_ARTICULO = X.ID_ARTICULO
                WHERE B.ID_ALMACEN = ".$id_almacen."
                AND B.ID_ANHO = ".$id_anho."
                AND (   UPPER(replace(A.NOMBRE,' ','')) LIKE UPPER(replace('%".$search."%',' ',''))
                         OR UPPER (B.CODIGO) LIKE UPPER ('%".$search."%')
                        OR UPPER(X.CODIGO) LIKE UPPER('%".$search."%')
                        ) 
                ORDER BY PRECIO,NOMBRE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listArticleswithoutPrices($id_almacen,$id_anho,$id_parent){                
        $query = "SELECT 
                        B.ID_ARTICULO,A.NOMBRE,TO_CHAR(B.COSTO,'9999,999,999.99') AS COSTO,B.ID_TIPOIGV,
                        C.DESCRIPCION AS TIPOIGV_DESCRIPCION
                FROM INVENTARIO_ARTICULO A 
                    INNER JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
                    INNER JOIN TIPO_IGV C ON B.ID_TIPOIGV=C.ID_TIPOIGV
                WHERE B.ID_ALMACEN = ".$id_almacen."
                AND B.ID_ANHO = ".$id_anho."
                AND A.ID_PARENT = ".$id_parent."
                AND B.COSTO > 0
                AND B.ESTADO = '1'
                AND B.ID_ARTICULO NOT IN (SELECT ID_ARTICULO FROM VENTA_PRECIO WHERE ID_ALMACEN = ".$id_almacen." AND ID_ANHO = ".$id_anho.") ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPolitics($id_almacen){                
        $query = "SELECT 
                        ID_POLITICA,ID_ALMACEN,ID_TIPOPOLITICA,NOMBRE,FECHA,ESTADO 
                FROM VENTA_POLITICA 
                WHERE ID_ALMACEN = ".$id_almacen." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPolitics($id_politica){                
        $query = "SELECT 
                        ID_POLITICA,ID_ALMACEN,ID_TIPOPOLITICA,NOMBRE,FECHA,ESTADO 
                FROM VENTA_POLITICA 
                WHERE ID_POLITICA = ".$id_politica." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addPolitics($id_almacen,$id_tipopolitica,$nombre,$estado){
        DB::table('VENTA_POLITICA')->insert(
                        array('ID_ALMACEN' => $id_almacen,'ID_TIPOPOLITICA' => $id_tipopolitica, 'NOMBRE' => $nombre,'FECHA' => DB::raw('SYSDATE'), 'ESTADO' =>$estado)
                    );
        $query = "SELECT 
                        MAX(ID_POLITICA) ID_POLITICA
                FROM VENTA_POLITICA ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_politica = $id->id_politica;
        }
        $sql = PoliticsData::showPolitics($id_politica);
        return $sql;
    }
    public static function updatePolitics($id_politica,$id_tipopolitica,$nombre,$estado){
        DB::table('VENTA_POLITICA')
            ->where('ID_POLITICA', $id_politica)
            ->update([
                'ID_TIPOPOLITICA' => $id_tipopolitica, 
                'NOMBRE' => $nombre,
                'ESTADO' => $estado
            ]);
        $sql = PoliticsData::showPolitics($id_politica);
        return $sql;
    }
    public static function deletePolitics($id_politica){
        DB::table('VENTA_POLITICA')->where('ID_POLITICA', '=', $id_politica)->delete();
    }
    public static function listPoliticsArticles($id_politica){                
        $query = "SELECT 
                    A.ID_POLITICA,A.ID_ALMACEN,ID_TIPOPOLITICA,NOMBRE,FECHA,A.ESTADO 
                    FROM VENTA_POLITICA A, VENTA_POLITICA_ARTICULO B
                    WHERE A.ID_POLITICA = B.ID_POLITICA
                    AND A.ID_POLITICA = ".$id_politica." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPoliticsPrices($id_politica,$id_anho,$id_parent){                
        $query = "SELECT 
                        B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO 
                FROM INVENTARIO_ARTICULO A 
                    INNER JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO 
                    INNER JOIN VENTA_POLITICA_ARTICULO C ON B.ID_ALMACEN = C.ID_ALMACEN 
                                                        AND B.ID_ARTICULO = C.ID_ARTICULO
                                                        AND B.ID_ANHO = C.ID_ANHO
                WHERE C.ID_POLITICA = ".$id_politica."
                AND B.ID_ANHO = ".$id_anho."
                AND A.ID_PARENT = ".$id_parent."
                ORDER BY PRECIO,NOMBRE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPoliticsArticleswithoutPrices($id_politica,$id_anho,$id_parent){  
        $id_almacen = 0;
        $sql = PoliticsData::showPolitics($id_politica);
        foreach($sql as $id){
            $id_almacen = $id->id_almacen;
        }
        $query = "SELECT 
                        B.ID_ARTICULO,A.NOMBRE,TO_CHAR(B.COSTO,'9999,999,999.99') AS COSTO,B.ID_TIPOIGV,
                        C.DESCRIPCION AS TIPOIGV_DESCRIPCION
                FROM INVENTARIO_ARTICULO A 
                    INNER JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
                    INNER JOIN TIPO_IGV C ON B.ID_TIPOIGV=C.ID_TIPOIGV
                WHERE B.ID_ALMACEN = ".$id_almacen."
                AND B.ID_ANHO = ".$id_anho."
                AND A.ID_PARENT = ".$id_parent."
                AND B.COSTO > 0
                AND B.ESTADO = '1'
                AND B.ID_ARTICULO NOT IN (SELECT ID_ARTICULO FROM VENTA_POLITICA_ARTICULO WHERE ID_POLITICA = ".$id_politica." AND ID_ALMACEN = ".$id_almacen." AND ID_ANHO = ".$id_anho.") ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listTypePolitics(){
        $query = DB::table('TIPO_POLITICA')->select('ID_TIPOPOLITICA','NOMBRE','ESTADO')->orderBy('NOMBRE')->get();
        return $query;
    }
    public static function listPoliticsPersons($id_politica,$credito,$activo,$sin_credito){
        if($credito == "1" && $sin_credito == "1"){
            $s_credito = "AND C.CREDITO in ('1','0') ";
        }else{
            if($credito == "0" && $sin_credito == "0"){
                $s_credito = "";
            }else{
                if($sin_credito == "1"){
                    $s_credito = "AND C.CREDITO = '0' ";
                }else{
                    $s_credito = "AND C.CREDITO = '".$credito."' ";
                }
            }
        }
        $query = "SELECT
                        A.ID_PERSONA,A.NOMBRE,A.PATERNO,A.MATERNO,C.CREDITO,C.ACTIVO,
                        (SELECT MAX(X.NUM_DOCUMENTO) FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = A.ID_PERSONA AND X.ID_TIPODOCUMENTO  IN(1,4,7) ) NUM_DOCUMENTO
                FROM MOISES.PERSONA A, VENTA_POLITICA_PERSONA C
                WHERE A.ID_PERSONA = C.ID_PERSONA
                AND C.ID_POLITICA = ".$id_politica."
                AND C.ACTIVO = '".$activo."' 
                ".$s_credito." 
                ORDER BY A.PATERNO,A.MATERNO,A.NOMBRE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updatePoliticsPersons($id_politica,$id_persona,$credito){
        DB::table('VENTA_POLITICA_PERSONA')
            ->where('ID_POLITICA', $id_politica)
            ->where('ID_PERSONA', $id_persona)
            ->update([
                'CREDITO' => $credito
            ]);
        //$sql = PoliticsData::showPolitics($id_politica);
        //return $sql;
    }
    public static function updatePoliticsPersonsAll($id_politica, $activo){
        DB::table('VENTA_POLITICA_PERSONA')
            ->where('ID_POLITICA', $id_politica)
            ->update([
                'CREDITO' => '0',
                'ACTIVO' => $activo
            ]);
    }
    public static function showPoliticsPersons($id_persona){
        $query = DB::table('VENTA_POLITICA_PERSONA')->select('ID_POLITICA')->where('ID_PERSONA', $id_persona)->where('ACTIVO', '1')->get();
        return $query;
    }
}