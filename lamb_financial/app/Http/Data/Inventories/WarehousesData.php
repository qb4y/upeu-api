<?php

namespace App\Http\Data\Inventories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class WarehousesData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function listWarehouses($parent, $id_entidad, $id_depto)
    {
        /*$sql = DB::table('PROCESS')->select('ID_PROCESO','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TIPOTRANSACCION','ID_PASO_INICIO','ID_PASO_FIN','NOMBRE','ESTADO')->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_DEPTO', $id_depto)->orderBy('ID_PROCESO')->get();*/
        if ($parent == "A") {
            $dato = "WHERE ID_PARENT IS NULL";
        } else {
            $dato = "WHERE ID_PARENT = $parent";
        }
        $sql = "SELECT 
                        ID_ALMACEN,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ALMACEN
                " . $dato . "
                AND ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "' 
                ORDER BY ID_ALMACEN ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showWarehouses($id_almacen)
    {
        //$sql = DB::table('PROCESS')->select('ID_PROCESO','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TIPOTRANSACCION','ID_PASO_INICIO','ID_PASO_FIN','NOMBRE','ESTADO')->where('ID_PROCESO', $id_proceso)->get();     
        $sql = "SELECT 
                        ID_ALMACEN,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        ID_SEDEAREA,
                        NOMBRE,
                        ESTADO,
                        ID_EXISTENCIA,
                        (SELECT PKG_ORDERS.FC_NOMBRE_AREA(ID_SEDEAREA) AREA FROM ORG_SEDE_AREA X WHERE X.ID_SEDEAREA = A.ID_SEDEAREA) AS sede_area
                FROM INVENTARIO_ALMACEN A
                WHERE ID_ALMACEN = " . $id_almacen . " ";
        $query = DB::select($sql);
        return $query;
    }
    // DEPRECADO POR LA UPN
    public static function listWarehousesDestinations($id_almacen)
    {

        $id_parent = "";
        $query = "SELECT NVL(ID_PARENT,0) AS ID_PARENT
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_almacen . " ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_parent = $id->id_parent;
        }

        $sql = "SELECT 
                        ID_ALMACEN,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_parent . " 
                AND ESTADO = '1'
                UNION ALL
                SELECT 
                        ID_ALMACEN,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ALMACEN
                WHERE ID_PARENT = " . $id_almacen . " 
                AND ESTADO = '1'  
                AND ID_ALMACEN NOT IN (" . $id_almacen . ") 
                ORDER BY ID_ALMACEN ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listWarehousesDestinationsByEntidad($id_entidad, $id_almacen)
    {
        $sql = "SELECT 
                        ID_ALMACEN,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ALMACEN
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_ALMACEN <> $id_almacen
                AND ESTADO = '1' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addWarehouses($id_parent, $id_entidad, $id_depto, $nombre, $estado, $idSedeArea, $id_existencia)
    {
        DB::table('INVENTARIO_ALMACEN')->insert(
            array('ID_PARENT' => $id_parent, 'ID_ENTIDAD' => $id_entidad, 'ID_DEPTO' => $id_depto, 'NOMBRE' => $nombre, 'ESTADO' => $estado, 'ID_SEDEAREA' => $idSedeArea,  'ID_EXISTENCIA' => $id_existencia)
        );
        $query = "SELECT 
                        MAX(ID_ALMACEN) ID_ALMACEN
                FROM INVENTARIO_ALMACEN ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_almacen = $id->id_almacen;
        }
        $sql = WarehousesData::showWarehouses($id_almacen);
        return $sql;
    }
    public static function updateWarehouses($id_almacen, $id_parent, $nombre, $estado, $idSedeArea, $id_existencia)
    {
        DB::table('INVENTARIO_ALMACEN')
            ->where('ID_ALMACEN', $id_almacen)
            ->update([
                'ID_PARENT' => $id_parent,
                'NOMBRE' => $nombre,
                'ESTADO' => $estado,
                'ID_SEDEAREA' => $idSedeArea,
                'ID_EXISTENCIA' =>  $id_existencia,
            ]);
        $sql = WarehousesData::showWarehouses($id_almacen);
        return $sql;
    }
    public static function deleteWarehouses($id_almacen)
    {
        DB::table('INVENTARIO_ALMACEN')->where('ID_ALMACEN', '=', $id_almacen)->delete();
    }
    public static function listMyWarehousesUsers($id_user, $id_entidad, $id_depto)
    {
        /*$sql = "SELECT 
                        A.ID_ALMACEN,
                        A.ID_PARENT,
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        B.ID_PERSONA,
                        A.NOMBRE,
                        A.ESTADO,
                        NVL(B.ASIGNADO,'N') AS ASIGNADO
                FROM INVENTARIO_ALMACEN A, INVENTARIO_ALMACEN_USERS B
                WHERE A.ID_ALMACEN = B.ID_ALMACEN
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."' 
                AND B.ID_PERSONA = ".$id_user."
                AND A.ESTADO = '1'
                ORDER BY A.ID_ALMACEN ";*/
        $sql = "SELECT 
                        A.ID_ALMACEN,
                        A.ID_PARENT,
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        B.ID_PERSONA,
                        A.NOMBRE,
                        A.ESTADO,
                        NVL((SELECT X.ASIGNADO FROM INVENTARIO_ALMACEN_USERS X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_PERSONA = B.ID_PERSONA AND ASIGNADO = 'S'),'N') AS ASIGNADO
                FROM INVENTARIO_ALMACEN A, ORG_AREA_RESPONSABLE B
                WHERE A.ID_SEDEAREA = B.ID_SEDEAREA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'  
                AND B.ID_PERSONA = " . $id_user . "
                AND A.ESTADO = '1'
                ORDER BY A.ID_ALMACEN ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listWarehousesUsers($id_almacen)
    {
        $sql = "SELECT 
                A.ID_ALMACEN,A.ESTADO,B.ID AS ID_PERSONA,B.EMAIL,C.NOMBRE,C.PATERNO,C.MATERNO 
                FROM INVENTARIO_ALMACEN_USERS A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID
                AND B.ID = C.ID_PERSONA
                AND A.ID_ALMACEN = " . $id_almacen . "
                ORDER BY C.PATERNO,C.MATERNO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showWarehousesUsers($id_almacen, $id_persona)
    {
        $sql = "SELECT 
                A.ID_ALMACEN,A.ESTADO,B.ID AS ID_PERSONA,B.EMAIL,C.NOMBRE,C.PATERNO,C.MATERNO 
                FROM INVENTARIO_ALMACEN_USERS A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID
                AND B.ID = C.ID_PERSONA
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_PERSONA = " . $id_persona . "
                ORDER BY C.PATERNO,C.MATERNO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showWarehousesUsersAssign($id_entidad, $id_persona)
    {
        $sql = "SELECT 
                        X.ID_ENTIDAD,X.ID_ALMACEN,X.NOMBRE as NOMBRE_ALMACEN,A.ESTADO,B.ID AS ID_PERSONA,B.EMAIL,C.NOMBRE,C.PATERNO,C.MATERNO,A.ASIGNADO
                FROM INVENTARIO_ALMACEN X, INVENTARIO_ALMACEN_USERS A, USERS B, MOISES.PERSONA C, ORG_AREA_RESPONSABLE D
                WHERE X.ID_ALMACEN = A.ID_ALMACEN
                AND A.ID_PERSONA = B.ID
                AND B.ID = C.ID_PERSONA
                AND X.ID_SEDEAREA = D.ID_SEDEAREA
                AND A.ID_PERSONA = D.ID_PERSONA
                AND X.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_PERSONA = " . $id_persona . "
                AND A.ASIGNADO = 'S'
                ORDER BY C.PATERNO,C.MATERNO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addWarehousesUsers($id_almacen, $id_persona, $estado)
    {
        DB::table('INVENTARIO_ALMACEN_USERS')->insert(
            array('ID_ALMACEN' => $id_almacen, 'ID_PERSONA' => $id_persona, 'ESTADO' => $estado, 'ASIGNADO' => 'N')
        );
        $sql = WarehousesData::showWarehousesUsers($id_almacen, $id_persona);
        return $sql;
    }
    public static function updateWarehousesUsers($id_almacen, $id_persona, $estado)
    {
        DB::table('INVENTARIO_ALMACEN_USERS')
            ->where('ID_ALMACEN', $id_almacen)
            ->where('ID_PERSONA', $id_persona)
            ->update([
                'ESTADO' => $estado
            ]);
        $sql = WarehousesData::showWarehousesUsers($id_almacen, $id_persona);
        return $sql;
    }
    public static function updateWarehousesUsersAssign($id_entidad, $id_almacen, $id_persona, $asignado)
    {
        $query = "UPDATE INVENTARIO_ALMACEN_USERS SET ASIGNADO = 'N'
                WHERE ID_ALMACEN IN (SELECT ID_ALMACEN FROM INVENTARIO_ALMACEN WHERE ID_ENTIDAD = " . $id_entidad . ")
                AND ID_PERSONA = " . $id_persona . "
                AND  ASIGNADO = 'S' ";
        DB::update($query);

        DB::table('INVENTARIO_ALMACEN_USERS')
            ->where('ID_ALMACEN', $id_almacen)
            ->where('ID_PERSONA', $id_persona)
            ->update([
                'ASIGNADO' => $asignado
            ]);
        $sql = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_persona);
        return $sql;
    }
    public static function deleteWarehousesUsers($id_almacen, $id_persona)
    {
        DB::table('INVENTARIO_ALMACEN_USERS')->where('ID_ALMACEN', '=', $id_almacen)->where('ID_PERSONA', '=', $id_persona)->delete();
    }
    public static function listMeasurementUnits()
    {
        $query = DB::table('INVENTARIO_UNIDAD_MEDIDA')->select('ID_UNIDADMEDIDA', 'NOMBRE', 'CODIGO_SUNAT', 'ES_DECIMAL', 'ESTADO')->orderBy('CODIGO_SUNAT')->get();
        return $query;
    }
    public static function showMeasurementUnits($id_unidadmedida)
    {
        $query = DB::table('INVENTARIO_UNIDAD_MEDIDA')->select('ID_UNIDADMEDIDA', 'NOMBRE', 'CODIGO_SUNAT', 'ES_DECIMAL', 'ESTADO')->where('ID_UNIDADMEDIDA', $id_unidadmedida)->get();
        return $query;
    }
    public static function addMeasurementUnits($nombre, $codigo_sunat, $es_decimal, $estado)
    {
        DB::table('INVENTARIO_UNIDAD_MEDIDA')->insert(
            array('NOMBRE' => $nombre, 'CODIGO_SUNAT' => $codigo_sunat, 'ES_DECIMAL' => $es_decimal, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(ID_UNIDADMEDIDA) ID_UNIDADMEDIDA
                FROM INVENTARIO_UNIDAD_MEDIDA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_unidadmedida = $id->id_unidadmedida;
        }
        $sql = WarehousesData::showMeasurementUnits($id_unidadmedida);
        return $sql;
    }
    public static function updateMeasurementUnits($id_unidadmedida, $nombre, $codigo_sunat, $es_decimal, $estado)
    {
        DB::table('INVENTARIO_UNIDAD_MEDIDA')
            ->where('ID_UNIDADMEDIDA', $id_unidadmedida)
            ->update([
                'NOMBRE' => $nombre,
                'CODIGO_SUNAT' => $codigo_sunat,
                'ES_DECIMAL' => $es_decimal,
                'ESTADO' => $estado
            ]);
        $sql = WarehousesData::showMeasurementUnits($id_unidadmedida);
        return $sql;
    }
    public static function deleteMeasurementUnits($id_unidadmedida)
    {
        DB::table('INVENTARIO_UNIDAD_MEDIDA')->where('ID_UNIDADMEDIDA', '=', $id_unidadmedida)->delete();
    }
    public static function listArticles($parent)
    {
        if ($parent == "A") {
            $dato = "WHERE A.ID_PARENT IS NULL";
        } else {
            $dato = "WHERE A.ID_PARENT = $parent";
        }
        $query = "SELECT 
                        A.ID_ARTICULO,
                        A.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        B.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        D.ID_MARCA,
                        D.NOMBRE AS NOMBRE_MARCA,
                        A.ID_CODCUBSO,
                        C.ID_CLASE,
                        C.NOMBRE AS NOMBRE_CLASE,
                        A.NOMBRE,
                        A.CODIGO,
                        A.ESTADO,
                        '0' AS SQUARE
                FROM INVENTARIO_ARTICULO A LEFT JOIN INVENTARIO_UNIDAD_MEDIDA B
                ON A.ID_UNIDADMEDIDA = B.ID_UNIDADMEDIDA
                LEFT JOIN INVENTARIO_CLASE C
                ON A.ID_CLASE = C.ID_CLASE
                LEFT JOIN INVENTARIO_MARCA D
                ON A.ID_MARCA = D.ID_MARCA
                $dato
                ORDER BY A.ID_ARTICULO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listArticlesWarehouses($id_almacen, $parent, $id_anho)
    {
        $query = "SELECT 
                        A.ID_ARTICULO,
                        A.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        B.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        D.ID_MARCA,
                        D.NOMBRE AS NOMBRE_MARCA,
                        A.ID_CODCUBSO,
                        C.ID_CLASE,
                        C.NOMBRE AS NOMBRE_CLASE,
                        A.NOMBRE,
                        A.CODIGO,
                        E.ES_SERVICIO,
                        A.ESTADO
                FROM INVENTARIO_ARTICULO A LEFT JOIN INVENTARIO_UNIDAD_MEDIDA B
                ON A.ID_UNIDADMEDIDA = B.ID_UNIDADMEDIDA
                LEFT JOIN INVENTARIO_CLASE C
                ON A.ID_CLASE = C.ID_CLASE
                LEFT JOIN INVENTARIO_MARCA D
                ON A.ID_MARCA = D.ID_MARCA
                LEFT JOIN INVENTARIO_ALMACEN_ARTICULO E
                ON A.ID_ARTICULO = E.ID_ARTICULO
                WHERE E.ID_ALMACEN = " . $id_almacen . "
                AND E.ID_ANHO = " . $id_anho . "
                AND A.ID_PARENT = " . $parent . "
                ORDER BY A.ID_ARTICULO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArticlesWarehouses($id_almacen, $id_articulo, $id_anho)
    {
        $query = "SELECT 
                        A.ID_ARTICULO,
                        A.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        B.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        D.ID_MARCA,
                        D.NOMBRE AS NOMBRE_MARCA,
                        A.ID_CODCUBSO,
                        C.ID_CLASE,
                        C.NOMBRE AS NOMBRE_CLASE,
                        A.NOMBRE,
                        A.CODIGO,
                        E.ES_SERVICIO,
                        A.ESTADO,
                        E.ID_TIPOIGV
                FROM INVENTARIO_ARTICULO A LEFT JOIN INVENTARIO_UNIDAD_MEDIDA B
                ON A.ID_UNIDADMEDIDA = B.ID_UNIDADMEDIDA
                LEFT JOIN INVENTARIO_CLASE C
                ON A.ID_CLASE = C.ID_CLASE
                LEFT JOIN INVENTARIO_MARCA D
                ON A.ID_MARCA = D.ID_MARCA
                LEFT JOIN INVENTARIO_ALMACEN_ARTICULO E
                ON A.ID_ARTICULO = E.ID_ARTICULO
                WHERE A.ID_ARTICULO = " . $id_articulo . "
                AND E.ID_ALMACEN = " . $id_almacen . "
                AND E.ID_ANHO = " . $id_anho . "
                ORDER BY A.ID_ARTICULO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArticles($id_articulo)
    {
        //$sql = DB::table('PROCESS')->select('ID_PROCESO','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TIPOTRANSACCION','ID_PASO_INICIO','ID_PASO_FIN','NOMBRE','ESTADO')->where('ID_PROCESO', $id_proceso)->get();     
        $sql = "SELECT 
                        ID_ARTICULO,
                        ID_PARENT,
                        ID_UNIDADMEDIDA,
                        ID_MARCA,
                        ID_CODCUBSO,
                        ID_CLASE,
                        NOMBRE,
                        CODIGO,
                        ESTADO,
                        IMAGEN_URL
                FROM INVENTARIO_ARTICULO
                WHERE ID_ARTICULO = " . $id_articulo . " ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showArticlesName($nombre)
    {
        $sql = "SELECT 
                        ID_ARTICULO,
                        ID_PARENT,
                        ID_UNIDADMEDIDA,
                        ID_MARCA,
                        ID_CODCUBSO,
                        ID_CLASE,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ARTICULO
                WHERE UPPER(NOMBRE) = UPPER('" . $nombre . "') ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showArticleByAnhoAlmacenCodigo($id_anho, $id_almacen, $codigo)
    {

        $sql = "SELECT A.ID_ARTICULO,
                        C.ES_DECIMAL,
                        DECODE(A.ID_TIPOIGV,'10',1,4) as ID_CTIPOIGV,
                        A.ID_TIPOIGV
                FROM INVENTARIO_ALMACEN_ARTICULO A JOIN INVENTARIO_ARTICULO B
                ON A.ID_ARTICULO = B.ID_ARTICULO
                LEFT JOIN INVENTARIO_UNIDAD_MEDIDA C
                ON B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
                WHERE A.ID_ALMACEN IN ($id_almacen)
                AND A.ID_ANHO = $id_anho
                AND (UPPER (A.CODIGO) LIKE UPPER ('$codigo')) 
                ORDER BY A.CODIGO, B.NOMBRE";
        $query = DB::select($sql);
        return $query;

        // return DB::table('INVENTARIO_ARTICULO')
        //     ->select('ID_ARTICULO', 'ID_PARENT', 'ID_UNIDADMEDIDA', 'ID_MARCA', 'ID_CODCUBSO', 
        //     'ID_CLASE', 'NOMBRE', 'ESTADO' )
        //     ->where('CODIGO', $codigo)
        //     ->first();
    }

    // public static function showArticlesByLastestCodigo($id_almacen, $id_anho, $latest_codigo){
    //     $sql = "SELECT 
    //                     ID_ARTICULO,
    //             FROM INVENTARIO_ALMACEN_ARTICULO
    //             WHERE ID_ALMACEN=".$id_almacen."
    //             AND ID_ANHO=".$id_anho."
    //             AND UPPER(CODIGO)=UPPER('".$latest_codigo."')
    //             ";
    //     $query = DB::select($sql);
    //     return $query;
    // }

    public static function addArticles($id_parent, $id_unidadmedida, $id_marca, $id_codcubso, $id_clase, $nombre, $codigo, $estado, $imagen_url)
    {
        if ($id_parent == "" || $id_parent == null) {
            $id_parent = "null";
        }
        if ($id_unidadmedida == "" || $id_unidadmedida == null) {
            $id_unidadmedida = "null";
        }
        if ($id_marca == "" || $id_marca == null) {
            $id_marca = "null";
        }
        if ($id_clase == 6) {
            $query = "SELECT SUBSTR(CODIGO,0,8)||LPAD(NVL(MAX(SUBSTR(CODIGO,9,20)),0)+1,8,0) AS CODIGO
                    FROM INVENTARIO_ARTICULO
                    WHERE ID_PARENT = " . $id_parent . "
                    GROUP BY SUBSTR(CODIGO,0,8) ";
            $oQuery = DB::select($query);
            if (count($oQuery) == 0) {
                $query = "SELECT SUBSTR(CODIGO,0,8)||LPAD(NVL(MAX(SUBSTR(CODIGO,9,20)),0)+1,8,0) AS CODIGO
                        FROM INVENTARIO_ARTICULO
                        WHERE ID_ARTICULO = " . $id_parent . "
                        GROUP BY SUBSTR(CODIGO,0,8) ";
                $oQuery = DB::select($query);
            }
            foreach ($oQuery as $id) {
                $codigo = $id->codigo;
            }
        }
        /*DB::table('INVENTARIO_ARTICULO')->insert(
                        array('ID_PARENT' => $id_parent,
                            'ID_UNIDADMEDIDA' =>$id_unidadmedida,
                            //'ID_MARCA' => $id_marca,
                            //'ID_CODCUBSO' =>$id_codcubso,
                            'ID_CLASE' =>$id_clase,
                            'NOMBRE' =>$nombre,
                            'CODIGO' =>$codigo,
                            'ESTADO' =>$estado,
                            'IMAGEN_URL' =>$imagen_url)
                    );*/

        $sql = "INSERT INTO INVENTARIO_ARTICULO(ID_PARENT,ID_UNIDADMEDIDA,ID_MARCA,ID_CLASE,NOMBRE,CODIGO,ESTADO,IMAGEN_URL) 
                VALUES ($id_parent,$id_unidadmedida,$id_marca,$id_clase,'" . $nombre . "','" . $codigo . "','" . $estado . "','" . $imagen_url . "') ";
        DB::insert($sql);

        $query = "SELECT 
                        MAX(ID_ARTICULO) ID_ARTICULO
                FROM INVENTARIO_ARTICULO ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_articulo = $id->id_articulo;
        }
        $sql = WarehousesData::showArticles($id_articulo);
        return $sql;
    }
    public static function updateArticles(
        $id_articulo,
        $id_unidadmedida,
        $id_marca,
        $id_codcubso,
        $id_clase,
        $nombre,
        $codigo,
        $estado,
        $imagen_url
    ) {
        if ($id_unidadmedida == "" || $id_unidadmedida == null) {
            $id_unidadmedida = "null";
        }
        if ($id_marca == "" || $id_marca == null) {
            $id_marca = "null";
        }
        /*DB::table('INVENTARIO_ARTICULO')
            ->where('ID_ARTICULO', $id_articulo)
            ->update([
                //'ID_PARENT' => $id_parent,
                'ID_UNIDADMEDIDA' =>$id_unidadmedida,
                'ID_MARCA' =>$id_marca,
                'ID_CODCUBSO' =>$id_codcubso,
                'ID_CLASE' =>$id_clase,
                'NOMBRE' =>$nombre,
                //'CODIGO' =>$codigo,
                'ESTADO' =>$estado,
                'IMAGEN_URL' =>$imagen_url
            ]);*/
        $query = "UPDATE INVENTARIO_ARTICULO SET    ID_UNIDADMEDIDA = " . $id_unidadmedida . ",
                                                    ID_MARCA = " . $id_marca . ",
                                                    ID_CLASE = " . $id_clase . ",
                                                    NOMBRE = '" . $nombre . "',
                                                    ESTADO = '" . $estado . "',
                                                    IMAGEN_URL = '" . $imagen_url . "'
                WHERE ID_ARTICULO = $id_articulo ";
        DB::update($query);
        $sql = WarehousesData::showArticles($id_articulo);
        return $sql;
    }
    public static function deleteArticles($id_articulo)
    {
        DB::table('INVENTARIO_ARTICULO')->where('ID_ARTICULO', '=', $id_articulo)->delete();
    }

    public static function deleteArticlesAndChilds($id_articulo)
    {
        // getting article and childs excep exist in kardex table
        $deletes = 0;
        $sql = "SELECT
                        INVENTARIO_ARTICULO.ID_ARTICULO
                      FROM INVENTARIO_ARTICULO
                      WHERE INVENTARIO_ARTICULO.ID_PARENT IS NOT NULL
                            AND INVENTARIO_ARTICULO.ID_ARTICULO NOT IN (
                        SELECT DISTINCT INVENTARIO_KARDEX.ID_ARTICULO
                        FROM INVENTARIO_KARDEX
                        WHERE ID_ARTICULO IN (
                          SELECT INVENTARIO_ARTICULO.ID_ARTICULO
                          FROM INVENTARIO_ARTICULO
                          WHERE INVENTARIO_ARTICULO.ID_PARENT IS NOT NULL
                                AND INVENTARIO_ARTICULO.ID_CLASE IN (5, 6)
                          START WITH INVENTARIO_ARTICULO.ID_ARTICULO = $id_articulo
                          CONNECT BY INVENTARIO_ARTICULO.ID_PARENT = PRIOR INVENTARIO_ARTICULO.ID_ARTICULO
                        )
                      )
                      START WITH INVENTARIO_ARTICULO.ID_ARTICULO = $id_articulo
                      CONNECT BY INVENTARIO_ARTICULO.ID_PARENT = PRIOR INVENTARIO_ARTICULO.ID_ARTICULO
                      ORDER BY INVENTARIO_ARTICULO.ID_ARTICULO DESC
                   ";
        $res = collect(DB::select($sql));
        if ($res->isNotEmpty()) {
            $deletes = DB::table("INVENTARIO_ARTICULO")->whereIn('id_articulo', $res->pluck('id_articulo')->toArray())->delete();
        }
        return $deletes;
    }
    public static function listClass()
    {
        $sql = "SELECT 
                        ID_CLASE,NOMBRE
                FROM INVENTARIO_CLASE
                ORDER BY ID_CLASE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listClassArticles($id_articulo)
    {
        $sql = "SELECT 
                        A.ID_CLASE,A.NOMBRE,
                        NVL((SELECT COUNT(X.ID_CLASE) FROM INVENTARIO_ARTICULO X WHERE (X.ID_CLASE+1) = A.ID_CLASE AND X.ID_ARTICULO = " . $id_articulo . "),0) STATUS
                FROM INVENTARIO_CLASE A
                ORDER BY A.ID_CLASE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listArticlesCodes($id_articulo)
    {
        $query = DB::table('INVENTARIO_ARTICULO_CODIGO')->select('ID_CODARTICULO', 'ID_ARTICULO', 'CODIGO')->where('ID_ARTICULO', $id_articulo)->orderBy('ID_CODARTICULO')->get();
        return $query;
    }
    public static function addArticlesCodes($id_articulo, $codigo)
    {
        DB::table('INVENTARIO_ARTICULO_CODIGO')->insert(
            array(
                'ID_ARTICULO' => $id_articulo,
                'CODIGO' => $codigo
            )
        );
        $sql = WarehousesData::listArticlesCodes($id_articulo);
        return $sql;
    }
    public static function deleteArticlesCodes($id_codarticulo)
    {
        DB::table('INVENTARIO_ARTICULO_CODIGO')->where('ID_CODARTICULO', '=', $id_codarticulo)->delete();
    }
    public static function deleteArticlesCodesP($id_articulo)
    {
        DB::table('INVENTARIO_ARTICULO_CODIGO')->where('ID_ARTICULO', '=', $id_articulo)->delete();
    }
    public static function listWarehousesArticles($id_almacen, $id_anho, $parent)
    {
        if ($parent == "A") {
            $dato = "AND A.ID_PARENT IS NULL";
        } else {
            $dato = "AND A.ID_PARENT = $parent";
        }
        $query = "SELECT 
                        A.ID_ARTICULO,
                        A.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        B.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        C.ID_CLASE,
                        C.NOMBRE AS NOMBRE_CLASE,
                        A.NOMBRE,
                        A.ES_SERVICIO,
                        A.ESTADO
                FROM VW_INVENTARIO_ALMACEN_ARTICULO A LEFT JOIN INVENTARIO_UNIDAD_MEDIDA B
                ON A.ID_UNIDADMEDIDA = B.ID_UNIDADMEDIDA
                LEFT JOIN INVENTARIO_CLASE C
                ON A.ID_CLASE = C.ID_CLASE
                WHERE A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_ANHO = " . $id_anho . "
                " . $dato . "
                ORDER BY A.ID_PARENT,A.ID_ARTICULO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showWarehousesArticles($id_almacen, $id_articulo, $id_anho, $url)
    {

        $query = "SELECT
                        A.ID_ARTICULO,
                        B.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        C.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        D.ID_CLASE,
                        D.NOMBRE AS NOMBRE_CLASE,
                        A.ID_TIPOIGV,
                        B.NOMBRE,
                        B.CODIGO,
                        A.STOCK_MINIMO,
                        A.ES_SERVICIO,
                        A.ESTADO,
                        A.UBICACION,
                        A.CODIGO AS CODIGO_CW,
                        DECODE((SELECT COUNT(ID_ARTICULO) FROM INVENTARIO_KARDEX X WHERE X.ID_ARTICULO = B.ID_ARTICULO),0,'S','N') EDITA,
                        '" . $url . "/'||B.IMAGEN_URL as IMAGEN_URL
                FROM INVENTARIO_ALMACEN_ARTICULO A 
                FULL OUTER JOIN  INVENTARIO_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
                FULL OUTER JOIN INVENTARIO_UNIDAD_MEDIDA C ON B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
                FULL OUTER JOIN INVENTARIO_CLASE D ON B.ID_CLASE = D.ID_CLASE AND B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA AND  B.ID_CLASE = D.ID_CLASE
                WHERE  A.ID_ALMACEN = " . $id_almacen . " AND A.ID_ANHO = " . $id_anho . " AND A.ID_ARTICULO = " . $id_articulo . "";

        // $query = "SELECT 
        //                 A.ID_ARTICULO,
        //                 B.ID_PARENT,
        //                 B.ID_UNIDADMEDIDA,
        //                 C.NOMBRE AS NOMBRE_UNIDADMEDIDA,
        //                 D.ID_CLASE,
        //                 D.NOMBRE AS NOMBRE_CLASE,
        //                 A.ID_TIPOIGV,
        //                 B.NOMBRE,
        //                 B.CODIGO,
        //                 A.STOCK_MINIMO,
        //                 A.ES_SERVICIO,
        //                 A.ESTADO,
        //                 A.UBICACION,
        //                 A.CODIGO AS CODIGO_CW,
        //                 DECODE((SELECT COUNT(ID_ARTICULO) FROM INVENTARIO_KARDEX X WHERE X.ID_ARTICULO = B.ID_ARTICULO),0,'S','N') EDITA,
        //                 '".$url."/'||B.IMAGEN_URL as IMAGEN_URL
        //         FROM INVENTARIO_ALMACEN_ARTICULO A, INVENTARIO_ARTICULO B,INVENTARIO_UNIDAD_MEDIDA C, INVENTARIO_CLASE D
        //         WHERE A.ID_ARTICULO = B.ID_ARTICULO
        //         AND B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
        //         AND B.ID_CLASE = D.ID_CLASE
        //         AND A.ID_ALMACEN = ".$id_almacen."
        //         AND A.ID_ARTICULO = ".$id_articulo."
        //         AND A.ID_ANHO = ".$id_anho." ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listWarehousesArticlesAll($id_almacen, $id_anho, $and, $or)
    {
        $query = "SELECT  
                            A.ID_ARTICULO,A.ID_PARENT,A.ID_UNIDADMEDIDA,A.ID_MARCA,A.NOMBRE,A.CODIGO,A.ESTADO,
                            DECODE((SELECT COUNT(ID_ARTICULO) FROM INVENTARIO_KARDEX X WHERE X.ID_ARTICULO = A.ID_ARTICULO),0,'S','N') EDITA
                FROM INVENTARIO_ARTICULO A
                WHERE A.ID_CLASE = 6
                AND (UPPER(NOMBRE) LIKE UPPER('%" . $and . "%') " . $or . ")
                AND A.ID_ARTICULO NOT IN (
                    SELECT ID_ARTICULO FROM INVENTARIO_ALMACEN_ARTICULO WHERE ID_ALMACEN = " . $id_almacen . " AND ID_ANHO = " . $id_anho . "
                ) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listArticlesSearch($IdArticulo, $SearchParam)
    {
        $query = "SELECT
              INVENTARIO_ARTICULO.ID_ARTICULO,
              INVENTARIO_ARTICULO.NOMBRE,
              INVENTARIO_ARTICULO.CODIGO
            FROM INVENTARIO_ARTICULO
            WHERE INVENTARIO_ARTICULO.ID_PARENT IS NOT NULL
                  AND upper(INVENTARIO_ARTICULO.NOMBRE || ' ' || INVENTARIO_ARTICULO.CODIGO) LIKE UPPER('%$SearchParam%')
            START WITH INVENTARIO_ARTICULO.ID_ARTICULO = $IdArticulo
            CONNECT BY INVENTARIO_ARTICULO.ID_PARENT = PRIOR INVENTARIO_ARTICULO.ID_ARTICULO
            ORDER BY INVENTARIO_ARTICULO.NOMBRE";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArticuloEdit($id_articulo)
    {
        $query = "SELECT DECODE(COUNT(ID_ARTICULO),0,'S','N') AS EDITA FROM INVENTARIO_KARDEX X WHERE X.ID_ARTICULO = " . $id_articulo . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showWarehousesParent($id_almacen)
    {
        $id_parent = "";
        $query = "SELECT ID_PARENT
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_almacen . " ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_parent = $id->id_parent;
        }

        $query = "SELECT ID_ALMACEN FROM (
                SELECT ID_ALMACEN
                FROM INVENTARIO_ALMACEN
                WHERE ID_PARENT = " . $id_almacen . "
                UNION ALL 
                SELECT ID_ALMACEN
                FROM INVENTARIO_ALMACEN
                WHERE ID_PARENT = '" . $id_parent . "' 
                UNION ALL
                SELECT ID_ALMACEN
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = '" . $id_parent . "'
                UNION ALL
                SELECT ID_ALMACEN
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_almacen . "
                )
                GROUP BY ID_ALMACEN
                ORDER BY ID_ALMACEN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showWarehousesEntidad($id_almacen)
    {
        $id_parent = "";
        $query = "SELECT ID_ENTIDAD
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_almacen . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addWarehousesArticles($id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo, $stock_minimo, $es_servicio, $estado, $ubicacion, $id_ctacte)
    {
        DB::table('INVENTARIO_ALMACEN_ARTICULO')->insert(
            array(
                'ID_ALMACEN' => $id_almacen,
                'ID_ARTICULO' => $id_articulo,
                'ID_ANHO' => $id_anho,
                'ID_TIPOIGV' => $id_tipoigv,
                'CODIGO' => $codigo,
                'STOCK_MINIMO' => $stock_minimo,
                'STOCK_ACTUAL' => 0,
                'COSTO' => 0,
                'COSTO_TOTAL' => 0,
                'ES_SERVICIO' => $es_servicio,
                'ESTADO' => $estado,
                'UBICACION' => $ubicacion,
                'ID_CTACTE' => $id_ctacte
            )
        );
        $sql = WarehousesData::listWarehousesArticles($id_almacen, $id_anho, $id_articulo);
        return $sql;
    }

    public static function updateCtacteWarehousesArticles($id_almacen, $id_articulo, $id_anho, $id_ctacte)
    {
        $sql = DB::table('ELISEO.INVENTARIO_ALMACEN_ARTICULO')
            ->where('ID_ALMACEN', $id_almacen)
            ->where('ID_ARTICULO', $id_articulo)
            ->where('ID_ANHO', $id_anho)
            ->update([
                'ID_CTACTE' => $id_ctacte
            ]);
        // $sql = WarehousesData::listWarehousesArticles($id_almacen,$id_anho,$id_articulo);
        return $sql;
    }

    public static function getAlmacenRubroCategoriaByParams($id_rcategoria, $id_almacen)
    {
        $query = "SELECT *
                FROM ELISEO.INVENTARIO_ALMACEN_RUBRO_CAT
                WHERE ID_ALMACEN = $id_almacen and ID_RCATEGORIA = $id_rcategoria";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addAlmacenRubroCategoria($id_entidad, $id_depto, $id_rcategoria, $id_almacen, $data)
    {
        DB::table('ELISEO.INVENTARIO_ALMACEN_RUBRO_CAT')->insert(
            array(
                'ID_RCATEGORIA' => $id_rcategoria, // ID
                'ID_ALMACEN' => $id_almacen, // ID

                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto,
                'ID_DINAMICA' => $data['id_dinamica'],
                'ID_ANHO' => $data['id_anho'],
                'ID_MODULO' => $data['id_modulo'],
                'CHANGE_DEPTO' => $data['change_depto'],
                'NEW_DEPTO' => $data['new_depto'],
                'ESTADO' => $data['estado']
            )
        );
    }

    public static function updateAlmacenRubroCategoria($id_rcategoria, $id_almacen, $data)
    {
        DB::table('ELISEO.INVENTARIO_ALMACEN_RUBRO_CAT')
            ->where('ID_RCATEGORIA', $id_rcategoria)
            ->where('ID_ALMACEN', $id_almacen)
            ->update([
                // 'ID_ENTIDAD' =>$data['id_entidad'],
                // 'ID_DEPTO' =>$data['id_depto'],
                'ID_DINAMICA' => $data['id_dinamica'],
                'ID_ANHO' => $data['id_anho'],
                'ID_MODULO' => $data['id_modulo'],
                'CHANGE_DEPTO' => $data['change_depto'],
                'NEW_DEPTO' => $data['new_depto'],
                'ESTADO' => $data['estado']
            ]);
    }

    public static function updateWarehousesArticles($id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo, $stock_minimo, $es_servicio, $estado, $dir, $ubicacion)
    {
        // dd($id_almacen,$id_articulo,$id_anho,$id_tipoigv,$codigo,$stock_minimo,$es_servicio,$estado,$dir);
        DB::table('INVENTARIO_ALMACEN_ARTICULO')
            ->where('ID_ALMACEN', $id_almacen)
            ->where('ID_ARTICULO', $id_articulo)
            ->where('ID_ANHO', $id_anho)
            ->update([
                'ID_TIPOIGV' => $id_tipoigv,
                'CODIGO' => $codigo,
                'STOCK_MINIMO' => $stock_minimo,
                'ES_SERVICIO' => $es_servicio,
                'ESTADO' => $estado,
                'UBICACION' => $ubicacion
            ]);
        //$sql = WarehousesData::showWarehousesRecipes($id_receta);
        $sql = WarehousesData::showWarehousesArticles($id_almacen, $id_articulo, $id_anho, $dir);
        return $sql;
    }
    public static function deleteWarehousesArticles($id_almacen, $id_articulo)
    {
        $sql = "DELETE FROM INVENTARIO_ALMACEN_ARTICULO 
                WHERE ID_ALMACEN = " . $id_almacen . " 
                AND ID_ARTICULO = " . $id_articulo . " 
                AND ID_ARTICULO NOT IN (
                SELECT A.ID_ARTICULO FROM INVENTARIO_KARDEX A
                ) ";
        DB::delete($sql, array());
    }
    public static function deleteWarehousesArticlesAll($id_articulo)
    {
        $sql = "DELETE FROM INVENTARIO_ALMACEN_ARTICULO 
                WHERE ID_ARTICULO = " . $id_articulo . " ";
        DB::delete($sql, array());
    }
    public static function listWarehousesArticlesClass($id_almacen, $id_anho)
    {
        $query = "SELECT C.ID_ARTICULO,C.NOMBRE
                    FROM INVENTARIO_ARTICULO A, INVENTARIO_ALMACEN_ARTICULO B, INVENTARIO_ARTICULO C
                    WHERE A.ID_ARTICULO = B.ID_ARTICULO
                    AND A.ID_PARENT = C.ID_ARTICULO
                    AND B.ID_ALMACEN = " . $id_almacen . "
                    AND B.ID_ANHO = " . $id_anho . "
                    GROUP BY C.ID_ARTICULO,C.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listWarehousesArticlesItems($id_parent, $id_almacen, $id_anho)
    {
        $query = "SELECT 
                    A.ID_ARTICULO,A.NOMBRE,B.STOCK_ACTUAL,B.COSTO 
                    FROM INVENTARIO_ARTICULO A, INVENTARIO_ALMACEN_ARTICULO B
                    WHERE A.ID_ARTICULO = B.ID_ARTICULO
                    AND A.ID_PARENT = " . $id_parent . " 
                    AND B.ID_ALMACEN = " . $id_almacen . "
                    AND B.ID_ANHO = " . $id_anho . " 
                    AND B.STOCK_ACTUAL > 0 
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listWarehousesAlmacenCategorias($id_almacen)
    {
        $query = "SELECT 
                    A.ID_ENTIDAD,
                    A.ID_RCATEGORIA,
                    A.ID_DEPTO, 
                    A.ID_ALMACEN,
                    A.ID_ANHO,
                    A.NEW_DEPTO,
                    A.CHANGE_DEPTO,
                    coalesce(A.ESTADO,'0') as ESTADO,
                    B.NOMBRE AS CATEGORIA_NOMBRE,
                    C.NOMBRE AS RUBRO_NOMBRE,
                    E.ID_DINAMICA AS ID_DINAMICA,
                    COALESCE(E.NOMBRE,'None') AS DINAMICA_NOMBRE,
                    E.ID_ANHO AS DINAMICA_ANHO,
                    F.NOMBRE AS MODULO_NOMBRE
                    FROM INVENTARIO_ALMACEN_RUBRO_CAT A 
                    INNER JOIN INVENTARIO_RUBRO_CATEGORIA B ON A.ID_RCATEGORIA=B.ID_RCATEGORIA
                    INNER JOIN INVENTARIO_RUBRO C ON B.ID_RUBRO=C.ID_RUBRO
                    LEFT JOIN CONTA_DINAMICA E ON A.ID_DINAMICA=E.ID_DINAMICA
                    LEFT JOIN LAMB_MODULO F ON A.ID_MODULO=F.ID_MODULO
                    WHERE A.ID_ALMACEN=$id_almacen";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getWarehousesAlmacenCategorias($id_almacen, $id_rcategoria)
    {
        $query = "SELECT 
                    A.ID_ENTIDAD,
                    A.ID_RCATEGORIA,
                    A.ID_DEPTO, 
                    A.ID_ALMACEN,
                    A.ID_DINAMICA,
                    A.ID_ANHO,
                    A.ID_MODULO,
                    coalesce(A.CHANGE_DEPTO,'N') as CHANGE_DEPTO,
                    A.NEW_DEPTO,
                    coalesce(A.ESTADO,'0') as ESTADO
                    FROM INVENTARIO_ALMACEN_RUBRO_CAT A 
                    WHERE A.ID_ALMACEN=$id_almacen
                    AND A.ID_RCATEGORIA=$id_rcategoria
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function deleteWarehousesAlmacenCategorias($id_almacen, $id_rcategoria)
    {
        DB::table('INVENTARIO_ALMACEN_RUBRO_CAT')
            ->where('ID_ALMACEN', '=', $id_almacen)
            ->where('ID_RCATEGORIA', '=', $id_rcategoria)
            ->delete();
    }

    public static function listWarehousesRecipes($id_almacen)
    {
        $query = "SELECT 
                            A.ID_RECETA,A.ID_ALMACEN,B.NOMBRE AS NOMBRE_ALMACEN,A.NOMBRE,A.RENDIMIENTO,A.ESTADO 
                FROM INVENTARIO_RECETA A, INVENTARIO_ALMACEN B
                WHERE A.ID_ALMACEN = B.ID_ALMACEN
                AND A.ID_ALMACEN = " . $id_almacen . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showWarehousesRecipes($id_receta)
    {
        $query = "SELECT 
                            A.ID_RECETA,A.ID_ALMACEN,B.NOMBRE AS NOMBRE_ALMACEN,A.NOMBRE,A.RENDIMIENTO,A.ESTADO,
                            COALESCE(A.ID_RECETATIPO,'') AS ID_RECETATIPO
                FROM INVENTARIO_RECETA A, INVENTARIO_ALMACEN B
                WHERE A.ID_ALMACEN = B.ID_ALMACEN
                AND A.ID_RECETA = " . $id_receta . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addWarehousesRecipes($id_almacen, $nombre, $rendimiento, $estado, $id_recetatipo)
    {
        DB::table('INVENTARIO_RECETA')->insert(
            array(
                'ID_ALMACEN' => $id_almacen,
                'NOMBRE' => $nombre,
                'RENDIMIENTO' => $rendimiento,
                'ESTADO' => $estado,
                'ID_RECETATIPO' => $id_recetatipo
            )
        );
        $query = "SELECT 
                        MAX(ID_RECETA) ID_RECETA
                FROM INVENTARIO_RECETA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_receta = $id->id_receta;
        }
        $sql = WarehousesData::showWarehousesRecipes($id_receta);
        return $sql;
    }
    public static function updateWarehousesRecipes($id_receta, $id_almacen, $nombre, $rendimiento, $estado, $id_recetatipo)
    {
        DB::table('INVENTARIO_RECETA')
            ->where('ID_RECETA', $id_receta)
            ->update([
                'ID_ALMACEN' => $id_almacen,
                'NOMBRE' => $nombre,
                'RENDIMIENTO' => $rendimiento,
                'ESTADO' => $estado,
                'ID_RECETATIPO' => $id_recetatipo
            ]);
        $sql = WarehousesData::showWarehousesRecipes($id_receta);
        return $sql;
    }
    public static function deleteWarehousesRecipes($id_receta)
    {
        DB::table('INVENTARIO_RECETA')->where('ID_RECETA', '=', $id_receta)->delete();
    }
    public static function listRecipesArticles($id_receta)
    {
        $query = "SELECT 
                    A.ID_RECETA,A.ID_ARTICULO,B.NOMBRE,FC_UNIDAD_MEDIDA(A.ID_ARTICULO) UNIDAD_MEDIDA,A.CANTIDAD,A.ESTADO,
                    A.PRECIO_UNITARIO_REF, COALESCE(CANTIDAD, 0)*COALESCE(PRECIO_UNITARIO_REF, 0) AS PRECIO_TOTAL
                    FROM INVENTARIO_RECETA_ARTICULO A, INVENTARIO_ARTICULO B
                    WHERE A.ID_ARTICULO = B.ID_ARTICULO
                    AND A.ID_RECETA = " . $id_receta . "
                    ORDER BY A.ID_ARTICULO,B.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showRecipesArticles($id_receta, $id_articulo)
    {
        $query = "SELECT 
                    A.ID_RECETA,A.ID_ARTICULO,B.NOMBRE,FC_UNIDAD_MEDIDA(A.ID_ARTICULO) UNIDAD_MEDIDA,A.CANTIDAD,A.ESTADO
                    FROM INVENTARIO_RECETA_ARTICULO A, INVENTARIO_ARTICULO B
                    WHERE A.ID_ARTICULO = B.ID_ARTICULO
                    AND A.ID_RECETA = " . $id_receta . "
                    AND A.ID_ARTICULO = " . $id_articulo . "
                    ORDER BY A.ID_ARTICULO,B.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addRecipesArticles($id_receta, $id_articulo, $cantidad, $estado, $precio_unitario_ref)
    {
        DB::table('INVENTARIO_RECETA_ARTICULO')->insert(
            array(
                'ID_RECETA' => $id_receta,
                'ID_ARTICULO' => $id_articulo,
                'CANTIDAD' => $cantidad,
                'PRECIO_UNITARIO_REF' => $precio_unitario_ref,
                'ESTADO' => $estado
            )
        );
        $sql = WarehousesData::showRecipesArticles($id_receta, $id_articulo);
        return $sql;
    }
    public static function updateRecipesArticles($id_receta, $id_articulo, $cantidad, $estado, $precio_unitario_ref)
    {
        DB::table('INVENTARIO_RECETA_ARTICULO')
            ->where('ID_RECETA', $id_receta)
            ->where('ID_ARTICULO', $id_articulo)
            ->update([
                'CANTIDAD' => $cantidad,
                'PRECIO_UNITARIO_REF' => $precio_unitario_ref,
                'ESTADO' => $estado
            ]);
        $sql = WarehousesData::showRecipesArticles($id_receta, $id_articulo);
        return $sql;
    }
    public static function deleteRecipesArticles($id_receta, $id_articulo)
    {
        DB::table('INVENTARIO_RECETA_ARTICULO')->where('ID_RECETA', '=', $id_receta)->where('ID_ARTICULO', '=', $id_articulo)->delete();
    }
    public static function listTypeOperations()
    {
        $query = DB::table('TIPO_OPERACION')->select('ID_TIPOOPERACION', 'NOMBRE', 'TIPO_MOV', 'ESTADO')->orderBy('ID_TIPOOPERACION')->get();
        return $query;
    }
    public static function listWarehousesDocuments($id_almacen)
    {
        $sql = "SELECT 
                A.ID_DOC,A.ID_ALMACEN,A.ESTADO,B.ID_DOCUMENTO,B.NOMBRE AS NOMBRE_DOCUMENTO,B.SERIE,B.CONTADOR,C.NOMBRE
                FROM INVENTARIO_ALMACEN_DOCUMENTO A, CONTA_DOCUMENTO B, TIPO_OPERACION C
                WHERE A.ID_DOCUMENTO = B.ID_DOCUMENTO
                AND A.ID_TIPOOPERACION = C.ID_TIPOOPERACION
                AND A.ID_ALMACEN = " . $id_almacen . "
                ORDER BY A.ID_DOC,C.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showWarehousesDocuments($id_almacen, $id_doc)
    {
        $sql = "SELECT 
                A.ID_DOC,A.ID_ALMACEN,A.ID_TIPOOPERACION,A.ESTADO,B.ID_DOCUMENTO,B.SERIE,B.CONTADOR,C.NOMBRE
                FROM INVENTARIO_ALMACEN_DOCUMENTO A, CONTA_DOCUMENTO B, TIPO_OPERACION C
                WHERE A.ID_DOCUMENTO = B.ID_DOCUMENTO
                AND A.ID_TIPOOPERACION = C.ID_TIPOOPERACION
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_DOC = " . $id_doc . "
                ORDER BY A.ID_DOC,C.NOMBRE  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addWarehousesDocuments($id_almacen, $id_documento, $id_tipooperacion, $estado)
    {
        DB::table('INVENTARIO_ALMACEN_DOCUMENTO')->insert(
            array('ID_ALMACEN' => $id_almacen, 'ID_DOCUMENTO' => $id_documento, 'ID_TIPOOPERACION' => $id_tipooperacion, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(ID_DOC) ID_DOC
                FROM INVENTARIO_ALMACEN_DOCUMENTO ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_doc = $id->id_doc;
        }
        $sql = WarehousesData::showWarehousesDocuments($id_almacen, $id_doc);
        return $sql;
    }
    public static function updateWarehousesDocuments($id_almacen, $id_doc, $id_documento, $id_tipooperacion, $estado)
    {
        DB::table('INVENTARIO_ALMACEN_DOCUMENTO')
            ->where('ID_DOC', $id_doc)
            ->update([
                'ID_DOCUMENTO' => $id_documento,
                'ID_TIPOOPERACION' => $id_tipooperacion,
                'ESTADO' => $estado
            ]);
        $sql = WarehousesData::showWarehousesDocuments($id_almacen, $id_doc);
        return $sql;
    }
    public static function deleteWarehousesDocuments($id_doc)
    {
        DB::table('INVENTARIO_ALMACEN_DOCUMENTO')->where('ID_DOC', '=', $id_doc)->delete();
    }
    public static function listWarehousesArticlesFind($id_almacen, $id_anho, $dato, $url)
    {
        /*$sql = "SELECT  A.ID_ARTICULO,
                        B.NOMBRE,
                        A.CODIGO,
                        NVL(NVL(D.PRECIO,A.COSTO),0) AS COSTO,
                        A.STOCK_ACTUAL,
                        A.ID_ALMACEN,
                        C.NOMBRE AS UNIDAD_MEDIDA,
                        C.ES_DECIMAL,
                        DECODE(A.ID_TIPOIGV,'10',1,4) as ID_CTIPOIGV,
                        '".$url."/'||B.IMAGEN_URL as IMAGEN_URL,
                        SUBSTR(B.CODIGO,1,8) CODIGO_UNSPSC,
                        NVL(A.COSTO,0) COSTO_ALM
                FROM INVENTARIO_ALMACEN_ARTICULO A JOIN INVENTARIO_ARTICULO B
                ON A.ID_ARTICULO = B.ID_ARTICULO
                JOIN INVENTARIO_UNIDAD_MEDIDA C
                ON B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
                LEFT JOIN VENTA_PRECIO D
                ON A.ID_ALMACEN = D.ID_ALMACEN
                AND A.ID_ARTICULO = D.ID_ARTICULO
                AND A.ID_ANHO = D.ID_ANHO
                WHERE A.ID_ALMACEN IN (".$id_almacen.")
                AND A.ID_ANHO = ".$id_anho."
                AND (   UPPER(replace(B.NOMBRE,' ','')) LIKE UPPER(replace('%".$dato."%',' ',''))
                         OR UPPER (A.CODIGO) LIKE UPPER ('%".$dato."%')
                         OR UPPER (A.ID_ARTICULO) IN
                             (SELECT ID_ARTICULO
                             FROM INVENTARIO_ARTICULO_CODIGO
                             WHERE UPPER (A.CODIGO) LIKE UPPER ('%".$dato."%'))) 
                ORDER BY A.CODIGO, B.NOMBRE ";*/
        $sql = "SELECT DISTINCT A.ID_ARTICULO,
                B.NOMBRE,
                A.CODIGO,
                NVL(NVL(D.PRECIO,A.COSTO),0) AS COSTO,
                A.STOCK_ACTUAL,
                A.ID_ALMACEN,
                C.NOMBRE AS UNIDAD_MEDIDA,
                C.ES_DECIMAL,
                DECODE(A.ID_TIPOIGV,'10',1,4) as ID_CTIPOIGV,
                '" . $url . "/'||B.IMAGEN_URL as IMAGEN_URL,
                SUBSTR(B.CODIGO,1,8) CODIGO_UNSPSC,
                NVL(A.COSTO,0) COSTO_ALM
        FROM INVENTARIO_ALMACEN_ARTICULO A 
        JOIN INVENTARIO_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
        LEFT JOIN INVENTARIO_UNIDAD_MEDIDA C ON B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
        LEFT JOIN VENTA_PRECIO D ON A.ID_ALMACEN = D.ID_ALMACEN
                                AND A.ID_ARTICULO = D.ID_ARTICULO
                                AND A.ID_ANHO = D.ID_ANHO
        LEFT JOIN INVENTARIO_ARTICULO_CODIGO X ON B.ID_ARTICULO = X.ID_ARTICULO
        WHERE A.ID_ALMACEN IN (" . $id_almacen . ")
        AND A.ID_ANHO = " . $id_anho . "
        
        AND (   UPPER(replace(B.NOMBRE,' ','')) LIKE UPPER(replace('%" . $dato . "%',' ',''))
                 OR UPPER (A.CODIGO) LIKE UPPER ('%" . $dato . "%')
                 OR UPPER(X.CODIGO) LIKE UPPER('%" . $dato . "%')) 
        ORDER BY A.CODIGO, B.NOMBRE";

        $query = DB::select($sql);

        // se retiro de forma temportal esta condición porque esta afectando en varios lugares AND A.STOCK_ACTUAL > 0


        return $query;
    }
    public static function listWarehousesArticlesFindPaginated($id_almacen, $id_anho, $dato, $url, $per_page)
    {
        $query = DB::table('INVENTARIO_ALMACEN_ARTICULO')
            ->join('INVENTARIO_ARTICULO', 'INVENTARIO_ALMACEN_ARTICULO.ID_ARTICULO', '=', 'INVENTARIO_ARTICULO.ID_ARTICULO')
            ->join('INVENTARIO_UNIDAD_MEDIDA', 'INVENTARIO_ARTICULO.ID_UNIDADMEDIDA', '=', 'INVENTARIO_UNIDAD_MEDIDA.ID_UNIDADMEDIDA')
            ->leftJoin('VENTA_PRECIO', 'INVENTARIO_ALMACEN_ARTICULO.ID_ALMACEN', '=', DB::raw("VENTA_PRECIO.ID_ALMACEN AND INVENTARIO_ALMACEN_ARTICULO.ID_ARTICULO = VENTA_PRECIO.ID_ARTICULO AND INVENTARIO_ALMACEN_ARTICULO.ID_ANHO = VENTA_PRECIO.ID_ANHO  "))
            ->whereraw("INVENTARIO_ALMACEN_ARTICULO.ID_ALMACEN IN (" . $id_almacen . ") ")
            ->whereraw("INVENTARIO_ALMACEN_ARTICULO.ID_ANHO = $id_anho")
            ->whereraw("(   UPPER(replace(INVENTARIO_ARTICULO.NOMBRE,' ','')) LIKE UPPER(replace('%" . $dato . "%',' ',''))
                         OR UPPER (INVENTARIO_ARTICULO.CODIGO) LIKE UPPER ('%" . $dato . "%')
                         OR UPPER (INVENTARIO_ARTICULO.ID_ARTICULO) IN
                             (SELECT ID_ARTICULO
                             FROM INVENTARIO_ARTICULO_CODIGO
                             WHERE UPPER (INVENTARIO_ALMACEN_ARTICULO.CODIGO) LIKE UPPER ('%" . $dato . "%')))")
            ->select(
                'INVENTARIO_ALMACEN_ARTICULO.ID_ARTICULO',
                'INVENTARIO_ARTICULO.NOMBRE',
                'INVENTARIO_ALMACEN_ARTICULO.CODIGO',
                DB::raw("NVL(INVENTARIO_ALMACEN_ARTICULO.COSTO,0) AS COSTO"),
                'INVENTARIO_ALMACEN_ARTICULO.STOCK_ACTUAL',
                'INVENTARIO_ALMACEN_ARTICULO.ID_ALMACEN',
                DB::raw("INVENTARIO_UNIDAD_MEDIDA.NOMBRE AS UNIDAD_MEDIDA"),
                'INVENTARIO_UNIDAD_MEDIDA.ES_DECIMAL',
                DB::raw("DECODE(INVENTARIO_ALMACEN_ARTICULO.ID_TIPOIGV,'10',1,4) as ID_CTIPOIGV"),
                DB::raw("'" . $url . "/'||INVENTARIO_ARTICULO.IMAGEN_URL as IMAGEN_URL"),
                DB::raw("SUBSTR(INVENTARIO_ARTICULO.CODIGO,1,8) CODIGO_UNSPSC")
            )
            ->orderBy('INVENTARIO_ALMACEN_ARTICULO.CODIGO', "desc");
        $rst = $query->paginate((int)$per_page);
        return $rst;
    }
    public static function listArticlesClassParent()
    {
        $sql = "SELECT 
                A.ID_ARTICULO,B.ID_CLASE,B.NOMBRE AS NOMBRE_CLASE,A.NOMBRE,A.CODIGO
                FROM INVENTARIO_ARTICULO A, INVENTARIO_CLASE B
                WHERE A.ID_CLASE = B.ID_CLASE
                AND B.ID_CLASE = 1
                ORDER BY A.CODIGO,A.NOMBRE  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listArticlesClassChild($id_parent)
    {
        $sql = "SELECT 
                A.ID_ARTICULO,B.ID_CLASE,B.NOMBRE AS NOMBRE_CLASE,A.NOMBRE,A.CODIGO
                FROM INVENTARIO_ARTICULO A, INVENTARIO_CLASE B
                WHERE A.ID_CLASE = B.ID_CLASE
                AND A.ID_PARENT = " . $id_parent . "
                ORDER BY A.CODIGO,A.NOMBRE  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listMyWarehousesYears($id_anho, $id_entidad, $id_depto)
    {
        $sql = "SELECT ID_ALMACEN,ID_ENTIDAD,NOMBRE,ESTADO
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN NOT IN (SELECT ID_ALMACEN FROM INVENTARIO_ALMACEN_ARTICULO WHERE ID_ANHO = " . $id_anho . ")
                AND ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                ORDER BY NOMBRE  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listMyWarehousesStock($id_almacen, $id_anho)
    {
        $sql = "SELECT 
                        ID_ALMACEN,ID_ANHO,ID_ARTICULO,PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS ARTICULO,
                        PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(ID_ARTICULO) UNIDAD_MEDIDA,
                        CODIGO,STOCK_ACTUAL,COSTO,COSTO_TOTAL,ESTADO
                FROM INVENTARIO_ALMACEN_ARTICULO
                WHERE ID_ALMACEN = " . $id_almacen . "
                AND ID_ANHO = " . $id_anho . "-1
                HAVING STOCK_ACTUAL > 0
                GROUP BY ID_ALMACEN,ID_ARTICULO,ID_ANHO,CODIGO,STOCK_ACTUAL,COSTO,COSTO_TOTAL,ESTADO
                ORDER BY ARTICULO  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addStockWarehouses($id_almacen, $id_anho)
    {
        $sql = "INSERT INTO INVENTARIO_ALMACEN_ARTICULO
                SELECT 
                        ID_ALMACEN,ID_ARTICULO," . $id_anho . ",ID_TIPOIGV,CODIGO,STOCK_MINIMO,STOCK_ACTUAL,COSTO,COSTO_TOTAL,ESTADO,
                        ES_SERVICIO,UBICACION, ABREVIATURA  
                FROM INVENTARIO_ALMACEN_ARTICULO
                WHERE ID_ALMACEN = " . $id_almacen . "
                AND ID_ANHO = " . $id_anho . "-1
                HAVING STOCK_ACTUAL > 0
                GROUP BY ID_ALMACEN,ID_ARTICULO,ID_ANHO,ID_TIPOIGV,CODIGO,STOCK_MINIMO,STOCK_ACTUAL,COSTO,COSTO_TOTAL,ESTADO,ES_SERVICIO,UBICACION, ABREVIATURA 
                ORDER BY ID_ARTICULO ";
        $query = DB::insert($sql);
        return $query;
    }
    public static function listArticlesChildrens($id_parent)
    {
        $sql = "SELECT ID_ARTICULO 
                FROM INVENTARIO_ARTICULO
                WHERE ID_PARENT = " . $id_parent . "  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listMyWarehousesRecipes($id_almacen, $dato)
    {
        $query = "SELECT 
                            A.ID_RECETA,A.ID_ALMACEN,B.NOMBRE AS NOMBRE_ALMACEN,A.NOMBRE,A.RENDIMIENTO,A.ESTADO 
                FROM INVENTARIO_RECETA A, INVENTARIO_ALMACEN B
                WHERE A.ID_ALMACEN = B.ID_ALMACEN
                AND A.ID_ALMACEN = " . $id_almacen . " 
                AND upper(A.NOMBRE) like upper('%" . $dato . "%') ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showWarehousesByAreas($id_sedearea)
    {
        $query = "SELECT 
                        A.ID_ALMACEN
                FROM INVENTARIO_ALMACEN A 
                WHERE A.ID_SEDEAREA = " . $id_sedearea . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArticlesCodigo($codigo)
    {
        $sql = "SELECT 
                        ID_ARTICULO,
                        ID_PARENT,
                        ID_UNIDADMEDIDA,
                        ID_MARCA,
                        ID_CODCUBSO,
                        ID_CLASE,
                        NOMBRE,
                        ESTADO
                FROM INVENTARIO_ARTICULO
                WHERE CODIGO = '" . $codigo . "' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listArticlesTree($parent)
    {
        $sql = "SELECT 
                        A.ID_ARTICULO,
                        A.ID_PARENT,
                        B.ID_UNIDADMEDIDA,
                        B.NOMBRE AS NOMBRE_UNIDADMEDIDA,
                        D.ID_MARCA,
                        D.NOMBRE AS NOMBRE_MARCA,
                        A.ID_CODCUBSO,
                        C.ID_CLASE,
                        C.NOMBRE AS NOMBRE_CLASE,
                        A.NOMBRE,
                        A.NOMBRE AS TEXT,
                        A.CODIGO,
                        A.ESTADO,
                        '0' AS SQUARE
                FROM INVENTARIO_ARTICULO A LEFT JOIN INVENTARIO_UNIDAD_MEDIDA B
                ON A.ID_UNIDADMEDIDA = B.ID_UNIDADMEDIDA
                LEFT JOIN INVENTARIO_CLASE C
                ON A.ID_CLASE = C.ID_CLASE
                LEFT JOIN INVENTARIO_MARCA D
                ON A.ID_MARCA = D.ID_MARCA 
                WHERE A.ID_PARENT = $parent
                ORDER BY A.CODIGO ";
        //                WHERE C.ID_CLASE = $id_clase
        //                START WITH A.ID_PARENT = $parent
        //                CONNECT BY PRIOR A.ID_ARTICULO = A.ID_PARENT
        //                ORDER SIBLINGS BY A.ID_ARTICULO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addPriceSalesTemp($id_almacen, $id_articulo, $id_anho, $costo, $descuento, $precio, $estado)
    {
        DB::table('VENTA_PRECIO')->insert(
            array(
                'ID_ALMACEN' => $id_almacen,
                'ID_ARTICULO' => $id_articulo,
                'ID_ANHO' => $id_anho,
                'COSTO' => $costo,
                'DESCUENTO' => $descuento,
                'PRECIO' => $precio,
                'ESTADO' => $estado
            )
        );
    }
    public static function listWarehouseByExist($id_entidad, $id_depto)
    {
        $sql = "SELECT ID_ALMACEN, NOMBRE
                    FROM INVENTARIO_ALMACEN
                    WHERE ID_ENTIDAD = $id_entidad
                    AND ID_DEPTO = '" . $id_depto . "'
                    AND ID_EXISTENCIA IN (1,2,3,4,6)";
        $query = DB::select($sql);
        return $query;
    }
    public static function showWarehousesUserParent($id_almacen)
    {
        $query = "SELECT ID_PARENT
                FROM INVENTARIO_ALMACEN
                WHERE ID_ALMACEN = " . $id_almacen . "
                AND ID_PARENT IS NOT NULL  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function searchWarehouse($id_entidad, $id_depto, $key)
    {
        $query = "SELECT ID_ALMACEN,NOMBRE FROM INVENTARIO_ALMACEN 
            WHERE id_entidad = $id_entidad 
            AND id_depto = $id_depto 
            AND estado = 1
            AND (upper(nombre) LIKE upper('%$key%'))";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addWarehouseProduct($request)
    {
        $response = [];

        $id_receta = $request->id_receta;
        $id_almacen = $request->id_almacen;
        $id_articulo = $request->id_articulo;
        $cantidad = $request->cantidad;
        $id_dinamica = $request->id_dinamica;
        $estado = 1;

        $query = DB::table("INVENTARIO_RECETA_PRODUCTO")
            ->insert(
                [
                    "id_receta" => $id_receta,
                    "id_almacen" => $id_almacen,
                    "id_articulo" => $id_articulo,
                    "cantidad" => $cantidad,
                    "id_dinamica" => $id_dinamica,
                    "estado" => $estado,
                ]
            );

        if ($query) {
            $response = [
                "success" => true
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "Error al agregar"
            ];
        }

        return $response;
    }

    public static function getWarehouseProduct($id_receta)
    {

        $query = DB::table("INVENTARIO_RECETA_PRODUCTO rp");
        $query->join("inventario_articulo ar", "ar.id_articulo", "=", "rp.id_articulo");
        $query->join("inventario_almacen ia", "ia.id_almacen", "=", "rp.id_almacen");
        $query->where("rp.id_receta", $id_receta);

        $query->select(
            "ia.nombre as almacen",
            "ar.nombre as articulo",
            "rp.cantidad",
            "rp.estado",
            "rp.id_receta",
            'rp.id_almacen',
            'rp.id_articulo'
        );

        return $query->get();
    }

    public static function updateWarehouseProduct($request)
    {
        $estado = $request->estado;
        $cantidad = $request->cantidad;

        $id_almacen = $request->id_almacen;
        $id_articulo = $request->id_articulo;
        $id_receta = $request->id_receta;

        $query = DB::table("INVENTARIO_RECETA_PRODUCTO rp")
            ->where("id_receta", $id_receta)
            ->where("id_articulo", $id_articulo)
            ->where("id_almacen", $id_almacen)
            ->update(
                [
                    "cantidad" => $cantidad,
                    "estado" => $estado,
                ]
            );

        if ($query) {
            $response = [
                "success" => true
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "Error al actualizar"
            ];
        }

        return $response;
    }
}
