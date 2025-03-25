<?php

namespace App\Http\Data\Inventories;

use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class InventoriesData extends Controller
{
        private $request;

        public function __construct(Request $request)
        {
                $this->request = $request;
        }
        public static function listTypeOperations($id_almacen, $tipo_mov)
        {
                $sql = "SELECT A.ID_TIPOOPERACION, A.NOMBRE 
                FROM TIPO_OPERACION A, INVENTARIO_ALMACEN_DOCUMENTO B
                WHERE A.ID_TIPOOPERACION = B.ID_TIPOOPERACION
                AND B.ID_ALMACEN = " . $id_almacen . "
                AND A.TIPO_MOV IN ('" . $tipo_mov . "','T')
                AND B.ESTADO = '1'
                ORDER BY A.ID_TIPOOPERACION ";
                // dd($sql);
                $query = DB::select($sql);
                return $query;
        }
        public static function listDocuments($id_almacen, $id_tipoopersacion)
        {
                $sql = "SELECT 
                A.ID_DOCUMENTO, A.NOMBRE,A.SERIE,A.CONTADOR 
                FROM CONTA_DOCUMENTO A, INVENTARIO_ALMACEN_DOCUMENTO B
                WHERE A.ID_DOCUMENTO = B.ID_DOCUMENTO
                AND B.ID_ALMACEN = " . $id_almacen . "
                AND B.ID_TIPOOPERACION = '" . $id_tipoopersacion . "' ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listRecetaTipos()
        {
                $sql = "SELECT ID_RECETATIPO, NOMBRE FROM INVENTARIO_RECETA_TIPO ORDER BY ID_RECETATIPO ";
                $query = DB::select($sql);
                return $query;
        }

        public static function listInventoriesDetails($id_movimiento)
        {
                /*$sql = "SELECT 
                A.ID_MOVIMIENTO,A.ID_MOVDETALLE,A.ID_DINAMICA,C.ID_ARTICULO,B.NOMBRE,C.CODIGO,C.STOCK_ACTUAL,A.CANTIDAD,A.COSTO,A.IMPORTE,A.ESTADO 
                FROM INVENTARIO_DETALLE A, INVENTARIO_ARTICULO B, INVENTARIO_ALMACEN_ARTICULO C
                WHERE A.ID_ARTICULO = B.ID_ARTICULO
                AND B.ID_ARTICULO = C.ID_ARTICULO
                AND A.ID_MOVIMIENTO = ".$id_movimiento."
                AND A.ESTADO = '0' ";*/
                $sql = "SELECT 
                B.ID_MOVIMIENTO,B.ID_MOVDETALLE,B.ID_DINAMICA,D.ID_ARTICULO,C.NOMBRE,D.CODIGO,D.STOCK_ACTUAL,B.CANTIDAD,B.COSTO,B.IMPORTE,B.ESTADO 
                FROM INVENTARIO_MOVIMIENTO A,INVENTARIO_DETALLE B, INVENTARIO_ARTICULO C, INVENTARIO_ALMACEN_ARTICULO D
                WHERE A.ID_MOVIMIENTO = B.ID_MOVIMIENTO
                AND B.ID_ARTICULO = C.ID_ARTICULO
                AND C.ID_ARTICULO = D.ID_ARTICULO
                AND A.ID_ALMACEN = D.ID_ALMACEN
                AND A.ID_ANHO = D.ID_ANHO
                AND B.ID_MOVIMIENTO = " . $id_movimiento . "
                AND B.ESTADO = '0' ";
                $query = DB::select($sql);
                return $query;
        }
        public static function showInventoriesDetails($id_movimiento, $id_movdetalle)
        {
                $sql = "SELECT 
                A.ID_MOVIMIENTO,A.ID_MOVDETALLE,A.ID_DINAMICA,,C.ID_ARTICULO,B.NOMBRE,C.CODIGO,C.STOCK_ACTUAL,A.CANTIDAD,A.COSTO,A.IMPORTE,A.ESTADO 
                FROM INVENTARIO_DETALLE A, INVENTARIO_ARTICULO B, INVENTARIO_ALMACEN_ARTICULO C
                WHERE A.ID_ARTICULO = B.ID_ARTICULO
                AND B.ID_ARTICULO = C.ID_ARTICULO
                AND A.ID_MOVDETALLE = " . $id_movdetalle . "
                AND A.ESTADO = '0' ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listKardex($id_anho, $id_almacen, $id_articulo, $id_mes)
        {
                $sql = "SELECT
                        0 ID_KARDEX,0 ID_ARTICULO,
                        
                        PKG_INVENTORIES.FC_ARTICULO(A.ID_ARTICULO) AS ARTICULO,

       (SELECT IA.CODIGO
                               FROM INVENTARIO_ARTICULO IA
                               WHERE IA.ID_ARTICULO = A.ID_ARTICULO
                                 AND ROWNUM <= 1)                                             AS CODIGO_INTERNO,

                              (SELECT IAA.CODIGO
                               FROM INVENTARIO_ALMACEN_ARTICULO IAA
                               WHERE IAA.ID_ALMACEN = A.ID_ALMACEN
                                 AND IAA.ID_ARTICULO = A.ID_ARTICULO
                                 AND IAA.ID_ANHO = A.ID_ANHO
                                 AND ROWNUM <= 1)                                             AS CODIGO,

                              PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(A.ID_ARTICULO)           UNIDAD_MEDIDA,
                        
                        'SALDO ANTERIOR' AS OPERACION, TO_CHAR('','DD/MM/YYYY') AS FECHA,
                        SUM(A.CANTIDAD) AS I_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS I_COSTO,
                        SUM(COSTO_TOTAL) AS I_COSTO_TOTAL,
                        --DECODE(SUM(A.CANTIDAD),0,0,SUM(COSTO_TOTAL)) AS I_COSTO_TOTAL,
                        0 S_CANTIDAD,0 S_COSTO,0 S_COSTO_TOTAL,
                        SUM(A.CANTIDAD) AS E_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS E_COSTO,
                        SUM(COSTO_TOTAL) AS E_COSTO_TOTAL
                        --DECODE(SUM(A.CANTIDAD),0,0,SUM(COSTO_TOTAL)) AS E_COSTO_TOTAL
                FROM INVENTARIO_KARDEX A 
                WHERE A.ID_ANHO = " . $id_anho . "
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_ARTICULO = " . $id_articulo . "
                AND TO_CHAR(A.FECHA,'MM') < LPAD(" . $id_mes . ",2,0)
                group by A.ID_ARTICULO, A.ID_ALMACEN, A.ID_ANHO
                UNION ALL
                SELECT 
                        A.ID_KARDEX,A.ID_ARTICULO,
                        PKG_INVENTORIES.FC_ARTICULO(A.ID_ARTICULO) AS ARTICULO,


       (SELECT IA.CODIGO
                               FROM INVENTARIO_ARTICULO IA
                               WHERE IA.ID_ARTICULO = A.ID_ARTICULO
                                 AND ROWNUM <= 1)                                             AS CODIGO_INTERNO,

                              (SELECT IAA.CODIGO
                               FROM INVENTARIO_ALMACEN_ARTICULO IAA
                               WHERE IAA.ID_ALMACEN = A.ID_ALMACEN
                                 AND IAA.ID_ARTICULO = A.ID_ARTICULO
                                 AND IAA.ID_ANHO = A.ID_ANHO
                                 AND ROWNUM <= 1)                                             AS CODIGO,

                              PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(A.ID_ARTICULO)           UNIDAD_MEDIDA,
                        PKG_INVENTORIES.FC_TIPO_OPERACION(A.ID_TIPOORIGEN,A.ID_ORIGEN) AS OPERACION,--B.TIPO,B.SERIE,B.NUMERO,
                        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,      
                        (CASE WHEN A.TIPO = 'I' THEN A.CANTIDAD ELSE 0 END) AS I_CANTIDAD,
                        (CASE WHEN A.TIPO = 'I' THEN A.COSTO_UNITARIO ELSE 0 END) AS I_COSTO,
                        (CASE WHEN A.TIPO = 'I' THEN A.COSTO_TOTAL ELSE 0 END) AS I_COSTO_TOTAL,
                        (CASE WHEN A.TIPO = 'S' THEN A.CANTIDAD ELSE 0 END) AS S_CANTIDAD,
                        (CASE WHEN A.TIPO = 'S' THEN A.COSTO_UNITARIO ELSE 0 END) AS S_COSTO,
                        (CASE WHEN A.TIPO = 'S' THEN A.COSTO_TOTAL ELSE 0 END) AS S_COSTO_TOTAL,       
                        SUM(B.CANTIDAD) AS CANT,
                        (CASE SUM(B.CANTIDAD) WHEN 0 THEN A.COSTO_UNITARIO ELSE ROUND(SUM(B.COSTO_TOTAL)/SUM(B.CANTIDAD),2) END) AS COSTO,
                        -- A.COSTO_UNITARIO AS COSTO,
                        SUM(B.COSTO_TOTAL) AS TOTAL
                        --(CASE SUM(B.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(B.COSTO_TOTAL) END) AS TOTAL
                FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_KARDEX B
                ON (A.ID_KARDEX >= B.ID_KARDEX)
                AND A.ID_ANHO = B.ID_ANHO
                AND A.ID_ALMACEN = B.ID_ALMACEN
                AND A.ID_ARTICULO = B.ID_ARTICULO
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_ARTICULO = " . $id_articulo . "
                AND TO_CHAR(A.FECHA,'MM') = LPAD(" . $id_mes . ",2,0)
                GROUP BY A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL,A.TIPO,A.FECHA,A.ID_TIPOORIGEN, A.ID_ANHO
                ORDER BY ID_KARDEX ";
                /*$sql = "SELECT 
                        0 ID_KARDEX,0 ID_ARTICULO,'SALDO ANTERIOR' AS OPERACION,'' AS TIPO,''SERIE,''NUMERO, TO_CHAR('','DD/MM/YYYY') AS FECHA,
                        SUM(A.CANTIDAD) AS I_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS I_COSTO,SUM(COSTO_TOTAL) AS I_COSTO_TOTAL,
                        0 S_CANTIDAD,0 S_COSTO,0 S_COSTO_TOTAL,
                        SUM(A.CANTIDAD) AS E_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS E_COSTO,SUM(COSTO_TOTAL) AS E_COSTO_TOTAL
                FROM INVENTARIO_KARDEX A, INVENTARIO_MOVIMIENTO B
                WHERE A.ID_ORIGEN = B.ID_MOVIMIENTO
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_ALMACEN = ".$id_almacen."
                AND A.ID_ARTICULO = ".$id_articulo."
                AND TO_CHAR(B.FECHA,'MM') < LPAD(".$id_mes.",2,0)
                UNION ALL
                SELECT 
                        A.ID_KARDEX,A.ID_ARTICULO,
                        PKG_INVENTORIES.FC_TIPO_OPERACION(B.ID_TIPOOPERACION) AS OPERACION,B.TIPO,B.SERIE,B.NUMERO,to_char(B.FECHA,'DD/MM/YYYY') AS FECHA,
                        (CASE WHEN B.TIPO = 'I' THEN A.CANTIDAD ELSE 0 END) AS I_CANTIDAD,
                        (CASE WHEN B.TIPO = 'I' THEN A.COSTO_UNITARIO ELSE 0 END) AS I_COSTO,
                        (CASE WHEN B.TIPO = 'I' THEN A.COSTO_TOTAL ELSE 0 END) AS I_COSTO_TOTAL,
                        (CASE WHEN B.TIPO = 'S' THEN A.CANTIDAD ELSE 0 END) AS S_CANTIDAD,
                        (CASE WHEN B.TIPO = 'S' THEN A.COSTO_UNITARIO ELSE 0 END) AS S_COSTO,
                        (CASE WHEN B.TIPO = 'S' THEN A.COSTO_TOTAL ELSE 0 END) AS S_COSTO_TOTAL,
                        A.CANT AS E_CANTIDAD,A.COSTO AS E_COSTO,A.TOTAL AS E_COSTO_TOTAL
                FROM (
                        SELECT 
                                A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,
                                A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL, 
                                SUM(B.CANTIDAD) AS CANT,
                                (CASE SUM(B.CANTIDAD) WHEN 0 THEN A.COSTO_UNITARIO ELSE SUM(B.COSTO_TOTAL)/SUM(B.CANTIDAD) END) AS COSTO,SUM(B.COSTO_TOTAL) AS TOTAL
                        FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_KARDEX B
                        ON (A.ID_KARDEX >= B.ID_KARDEX)
                        AND A.ID_ANHO = B.ID_ANHO
                        AND A.ID_ALMACEN = B.ID_ALMACEN
                        AND A.ID_ARTICULO = B.ID_ARTICULO
                        AND A.ID_ANHO = ".$id_anho."
                        AND A.ID_ALMACEN = ".$id_almacen."
                        AND A.ID_ARTICULO = ".$id_articulo."
                        GROUP BY A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL
                        ORDER BY A.ID_KARDEX
                ) A, INVENTARIO_MOVIMIENTO B
                WHERE A.ID_ORIGEN = B.ID_MOVIMIENTO
                AND TO_CHAR(B.FECHA,'MM') = LPAD(".$id_mes.",2,0)
                ORDER BY ID_KARDEX,FECHA ";*/
                $query = DB::select($sql);
                return $query;
        }

        public static function listKardexAll($id_anho, $id_almacen, $id_mes)
        {
                $sql = "WITH ft_kardex_all as (SELECT
        0 ID_KARDEX,
        ID_ARTICULO, 
        PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) ARTICULO,
        (SELECT IA.CODIGO
                               FROM INVENTARIO_ARTICULO IA
                               WHERE IA.ID_ARTICULO = A.ID_ARTICULO
                                 AND ROWNUM <= 1)                                 AS   CODIGO_INTERNO,

                              (SELECT IAA.CODIGO
                               FROM INVENTARIO_ALMACEN_ARTICULO IAA
                               WHERE IAA.ID_ALMACEN = A.ID_ALMACEN
                                 AND IAA.ID_ARTICULO = A.ID_ARTICULO
                                 AND IAA.ID_ANHO = A.ID_ANHO
                                 AND ROWNUM <= 1)                                 AS   CODIGO,

                              PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(A.ID_ARTICULO) UNIDAD_MEDIDA,
        'SALDO ANTERIOR' AS OPERACION, 
        '00/00/00' AS FECHA,
        SUM(A.CANTIDAD) AS I_CANTIDAD,
        (CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS I_COSTO,
        SUM(COSTO_TOTAL) AS I_COSTO_TOTAL ,
        0 S_CANTIDAD,0 S_COSTO,0 S_COSTO_TOTAL,
        SUM(A.CANTIDAD) AS E_CANTIDAD,
        (CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS E_COSTO,
        SUM(COSTO_TOTAL) AS E_COSTO_TOTAL
FROM INVENTARIO_KARDEX A 
WHERE A.ID_ANHO = " . $id_anho . "
AND A.ID_ALMACEN = " . $id_almacen . " 
AND TO_CHAR(A.FECHA,'MM') < LPAD(" . $id_mes . ",2,0)
group by A.ID_ARTICULO , A.ID_ALMACEN, A.ID_ANHO
 
union all

SELECT 
        A.ID_KARDEX,
        A.ID_ARTICULO ,
        PKG_INVENTORIES.FC_ARTICULO(A.ID_ARTICULO) ARTICULO, 
        (SELECT IA.CODIGO
                               FROM INVENTARIO_ARTICULO IA
                               WHERE IA.ID_ARTICULO = A.ID_ARTICULO
                                 AND ROWNUM <= 1)                                             AS CODIGO_INTERNO,

                              (SELECT IAA.CODIGO
                               FROM INVENTARIO_ALMACEN_ARTICULO IAA
                               WHERE IAA.ID_ALMACEN = A.ID_ALMACEN
                                 AND IAA.ID_ARTICULO = A.ID_ARTICULO
                                 AND IAA.ID_ANHO = A.ID_ANHO
                                 AND ROWNUM <= 1)                                             AS CODIGO,

                              PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(A.ID_ARTICULO)           UNIDAD_MEDIDA,
                              
        PKG_INVENTORIES.FC_TIPO_OPERACION(A.ID_TIPOORIGEN,A.ID_ORIGEN) AS OPERACION ,
        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,      
        (CASE WHEN A.TIPO = 'I' THEN A.CANTIDAD ELSE 0 END) AS I_CANTIDAD,
        (CASE WHEN A.TIPO = 'I' THEN A.COSTO_UNITARIO ELSE 0 END) AS I_COSTO,
        (CASE WHEN A.TIPO = 'I' THEN A.COSTO_TOTAL ELSE 0 END) AS I_COSTO_TOTAL,
        (CASE WHEN A.TIPO = 'S' THEN A.CANTIDAD ELSE 0 END) AS S_CANTIDAD,
        (CASE WHEN A.TIPO = 'S' THEN A.COSTO_UNITARIO ELSE 0 END) AS S_COSTO,
        (CASE WHEN A.TIPO = 'S' THEN A.COSTO_TOTAL ELSE 0 END) AS S_COSTO_TOTAL,       
        SUM(B.CANTIDAD) AS CANT,
        (CASE SUM(B.CANTIDAD) WHEN 0 THEN A.COSTO_UNITARIO ELSE ROUND(SUM(B.COSTO_TOTAL)/SUM(B.CANTIDAD),2) END) AS COSTO,
        SUM(B.COSTO_TOTAL) AS TOTAL
FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_KARDEX B
ON (A.ID_KARDEX >= B.ID_KARDEX)
AND A.ID_ANHO = B.ID_ANHO
AND A.ID_ALMACEN = B.ID_ALMACEN
AND A.ID_ARTICULO = B.ID_ARTICULO
AND A.ID_ANHO = " . $id_anho . "
AND A.ID_ALMACEN = " . $id_almacen . " 
AND TO_CHAR(A.FECHA,'MM') = LPAD(" . $id_mes . ",2,0)
GROUP BY A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL,A.TIPO,A.FECHA,A.ID_TIPOORIGEN,A.ID_ARTICULO, A.ID_ANHO
ORDER BY ID_KARDEX)
select * from ft_kardex_all
order by id_articulo
";
                $query = DB::select($sql);
                return $query;
        }

        public static function showStock($id_anho, $id_almacen, $id_articulo)
        {
                $sql = "SELECT 
                        ID_ARTICULO,PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS ARTICULO,STOCK_ACTUAL,TO_CHAR(COSTO,'9990D99') AS COSTO,TO_CHAR(COSTO_TOTAL,'9999999990D99') AS COSTO_TOTAL
                FROM INVENTARIO_ALMACEN_ARTICULO
                WHERE ID_ALMACEN = " . $id_almacen . "
                AND ID_ANHO = " . $id_anho . "
                AND ID_ARTICULO = " . $id_articulo . " ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listStock($id_anho, $id_almacen, $id_articulo, $id_mes)
        {
                $sql = "SELECT 
                        0 ID_KARDEX,0 ID_ARTICULO,'SALDO ANTERIOR' AS OPERACION,'' AS TIPO,''SERIE,''NUMERO, TO_CHAR('','DD/MM/YYYY') AS FECHA,
                        SUM(A.CANTIDAD) AS I_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS I_COSTO,SUM(COSTO_TOTAL) AS I_COSTO_TOTAL,
                        0 S_CANTIDAD,0 S_COSTO,0 S_COSTO_TOTAL,
                        SUM(A.CANTIDAD) AS E_CANTIDAD,(CASE SUM(A.CANTIDAD) WHEN 0 THEN 0 ELSE SUM(A.COSTO_TOTAL)/SUM(A.CANTIDAD) END) AS E_COSTO,SUM(COSTO_TOTAL) AS E_COSTO_TOTAL
                FROM INVENTARIO_KARDEX A, INVENTARIO_MOVIMIENTO B
                WHERE A.ID_ORIGEN = B.ID_MOVIMIENTO
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_ARTICULO = " . $id_articulo . "
                AND TO_CHAR(B.FECHA,'MM') < LPAD(" . $id_mes . ",2,0)
                UNION ALL
                SELECT 
                        A.ID_KARDEX,A.ID_ARTICULO,
                        PKG_INVENTORIES.FC_TIPO_OPERACION(B.ID_TIPOOPERACION) AS OPERACION,B.TIPO,B.SERIE,B.NUMERO,to_char(B.FECHA,'DD/MM/YYYY') AS FECHA,
                        (CASE WHEN B.TIPO = 'I' THEN A.CANTIDAD ELSE 0 END) AS I_CANTIDAD,
                        (CASE WHEN B.TIPO = 'I' THEN A.COSTO_UNITARIO ELSE 0 END) AS I_COSTO,
                        (CASE WHEN B.TIPO = 'I' THEN A.COSTO_TOTAL ELSE 0 END) AS I_COSTO_TOTAL,
                        (CASE WHEN B.TIPO = 'S' THEN A.CANTIDAD ELSE 0 END) AS S_CANTIDAD,
                        (CASE WHEN B.TIPO = 'S' THEN A.COSTO_UNITARIO ELSE 0 END) AS S_COSTO,
                        (CASE WHEN B.TIPO = 'S' THEN A.COSTO_TOTAL ELSE 0 END) AS S_COSTO_TOTAL,
                        A.CANT AS E_CANTIDAD,A.COSTO AS E_COSTO,A.TOTAL AS E_COSTO_TOTAL
                FROM (
                        SELECT 
                                A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,
                                A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL, 
                                SUM(B.CANTIDAD) AS CANT,
                                (CASE SUM(B.CANTIDAD) WHEN 0 THEN A.COSTO_UNITARIO ELSE SUM(B.COSTO_TOTAL)/SUM(B.CANTIDAD) END) AS COSTO,SUM(B.COSTO_TOTAL) AS TOTAL
                        FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_KARDEX B
                        ON (A.ID_KARDEX >= B.ID_KARDEX)
                        AND A.ID_ANHO = B.ID_ANHO
                        AND A.ID_ALMACEN = B.ID_ALMACEN
                        AND A.ID_ARTICULO = B.ID_ARTICULO
                        AND A.ID_ANHO = " . $id_anho . "
                        AND A.ID_ALMACEN = " . $id_almacen . "
                        AND A.ID_ARTICULO = " . $id_articulo . "
                        GROUP BY A.ID_KARDEX,A.ID_ALMACEN,A.ID_ORIGEN,A.ID_ARTICULO,A.CANTIDAD,A.COSTO_UNITARIO,A.COSTO_TOTAL
                        ORDER BY A.ID_KARDEX
                ) A, INVENTARIO_MOVIMIENTO B
                WHERE A.ID_ORIGEN = B.ID_MOVIMIENTO
                AND TO_CHAR(B.FECHA,'MM') = LPAD(" . $id_mes . ",2,0)
                ORDER BY ID_KARDEX,FECHA ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listCategoriesArticlesBK($dato)
        {
                $sql = "SELECT E.ID_ARTICULO,A.CODIGO GRUPO,A.NOMBRE AS PARENTS,B.CODIGO AS SEGMENTO,C.CODIGO AS FAMILIA,D.CODIGO AS CLASE,E.CODIGO AS PRODUCTO,E.NOMBRE
                FROM INVENTARIO_ARTICULO A, INVENTARIO_ARTICULO B, INVENTARIO_ARTICULO C,INVENTARIO_ARTICULO D, INVENTARIO_ARTICULO E--,INVENTARIO_ARTICULO F
                WHERE A.ID_ARTICULO = B.ID_PARENT
                AND B.ID_ARTICULO = C.ID_PARENT
                AND C.ID_ARTICULO = D.ID_PARENT
                AND D.ID_ARTICULO = E.ID_PARENT
                --AND E.ID_ARTICULO = F.ID_PARENT
                AND E.ID_CLASE = 5
                AND (   UPPER (E.NOMBRE) LIKE UPPER ('%" . $dato . "%')
                        OR UPPER (A.CODIGO) LIKE UPPER ('%" . $dato . "%') )
                GROUP BY E.ID_ARTICULO,A.CODIGO,A.NOMBRE,B.CODIGO,C.CODIGO,D.CODIGO,E.CODIGO,E.NOMBRE
                ORDER BY GRUPO,NOMBRE ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listCategoriesArticles($dato, $per_page)
        {
                $query = DB::table('VW_INVENTARIO_ARTICULO_FAMILIA')
                        ->whereraw("(UPPER (NOMBRE) LIKE UPPER ('%" . $dato . "%') OR UPPER (PRODUCTO) LIKE UPPER ('%" . $dato . "%') )");
                $query->select('ID_ARTICULO', 'GRUPO', 'PARENTS', 'SEGMENTO', 'FAMILIA', 'CLASE', 'PRODUCTO', 'NOMBRE');
                $rst = $query->paginate((int)$per_page);
                return $rst;
        }
        public static function listCategoriesWarehousesArticles($id_almacen, $id_anho)
        {
                $sql = "SELECT E.ID_ARTICULO,A.CODIGO GRUPO,A.NOMBRE AS PARENTS,B.CODIGO AS SEGMENTO,C.CODIGO AS FAMILIA,D.CODIGO AS CLASE,E.CODIGO AS PRODUCTO,E.NOMBRE
                FROM INVENTARIO_ARTICULO A, INVENTARIO_ARTICULO B, INVENTARIO_ARTICULO C,INVENTARIO_ARTICULO D, INVENTARIO_ARTICULO E,INVENTARIO_ARTICULO F
                WHERE A.ID_ARTICULO = B.ID_PARENT
                AND B.ID_ARTICULO = C.ID_PARENT
                AND C.ID_ARTICULO = D.ID_PARENT
                AND D.ID_ARTICULO = E.ID_PARENT
                AND E.ID_ARTICULO = F.ID_PARENT
                AND E.ID_ARTICULO IN (SELECT A.ID_PARENT FROM INVENTARIO_ARTICULO A, INVENTARIO_ALMACEN_ARTICULO B
                WHERE A.ID_ARTICULO = B.ID_ARTICULO AND B.ID_ALMACEN = " . $id_almacen . " AND B.ID_ANHO = " . $id_anho . " )
                GROUP BY E.ID_ARTICULO,A.CODIGO,A.NOMBRE,B.CODIGO,C.CODIGO,D.CODIGO,E.CODIGO,E.NOMBRE
                ORDER BY GRUPO,NOMBRE ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listStockArticles($id_almacen, $id_anho, $id_mes, $id_articulo)
        {
                $sql = "SELECT 
                        B.ID_PARENT,PKG_INVENTORIES.FC_ARTICULO(B.ID_PARENT) NAME_PARENT,
                        B.ID_ARTICULO,B.NOMBRE,
                        B.CODIGO,
                        PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(B.ID_ARTICULO) UNIDAD_MEDIDA,
                        (SELECT X.CODIGO FROM INVENTARIO_ALMACEN_ARTICULO X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_ARTICULO = B.ID_ARTICULO AND X.ID_ANHO = A.ID_ANHO) AS COD_CW,
                        --(SELECT X.UBICACION FROM INVENTARIO_ALMACEN_ARTICULO X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_ARTICULO = B.ID_ARTICULO AND X.ID_ANHO = A.ID_ANHO) AS UBICACION,
                        (CASE A.ID_ALMACEN WHEN 8 THEN (SELECT DISTINCT X.ID_CTACTE FROM INVENTARIO_ALMACEN_ARTICULO X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_ARTICULO = B.ID_ARTICULO AND X.ID_ANHO = A.ID_ANHO)
                        ELSE (SELECT X.UBICACION FROM INVENTARIO_ALMACEN_ARTICULO X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_ARTICULO = B.ID_ARTICULO AND X.ID_ANHO = A.ID_ANHO) END) AS UBICACION,
                        (SELECT X.ABREVIATURA FROM INVENTARIO_ALMACEN_ARTICULO X WHERE X.ID_ALMACEN = A.ID_ALMACEN AND X.ID_ARTICULO = B.ID_ARTICULO AND X.ID_ANHO = A.ID_ANHO) AS ABREV,
                        SUM(A.CANTIDAD) CANTIDAD,
                        DECODE(SUM(A.CANTIDAD),0,0,ROUND(SUM(A.COSTO_TOTAL)/DECODE(SUM(A.CANTIDAD),0,1,SUM(A.CANTIDAD)),2)) COSTO_UNITARIO,
                        -- ROUND(SUM(A.COSTO_UNITARIO)/DECODE(SUM(A.CANTIDAD),0,1,SUM(A.CANTIDAD)),2) COSTO_UNITARIO,
                        ROUND(SUM(A.CANTIDAD)*(SUM(A.COSTO_TOTAL)/DECODE(SUM(A.CANTIDAD),0,1,SUM(A.CANTIDAD))),2) AS COSTO_TOTAL
                FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_ARTICULO B
                ON A.ID_ARTICULO = B.ID_ARTICULO
                AND A.ID_ALMACEN = " . $id_almacen . "
                AND A.ID_ANHO = " . $id_anho . "
                AND TO_CHAR(A.FECHA,'MM') <= LPAD(" . $id_mes . ",2,0)
                GROUP BY A.ID_ALMACEN,B.ID_PARENT,B.ID_ARTICULO,B.NOMBRE,B.CODIGO,A.ID_ANHO
                ORDER BY B.CODIGO,B.NOMBRE ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listStockArticlesTotal($id_almacen, $id_anho, $id_mes, $id_articulo)
        {
                $sql = "SELECT SUM(CANTIDAD) AS CANTIDAD,TO_CHAR(SUM(COSTO_TOTAL),'999,999,999,999.99') AS COSTO_TOTAL  
                FROM ( 
                        SELECT  A.ID_ARTICULO,
                                SUM(A.CANTIDAD) CANTIDAD,
                                ROUND(SUM(A.CANTIDAD)*(SUM(A.COSTO_TOTAL)/DECODE(SUM(A.CANTIDAD),0,1,SUM(A.CANTIDAD))),2) AS COSTO_TOTAL
                        FROM INVENTARIO_KARDEX A INNER JOIN INVENTARIO_ARTICULO B
                        ON A.ID_ARTICULO = B.ID_ARTICULO
                        AND A.ID_ALMACEN = " . $id_almacen . "
                        AND A.ID_ANHO = " . $id_anho . "
                        AND TO_CHAR(A.FECHA,'MM') <= LPAD(" . $id_mes . ",2,0)
                        GROUP BY A.ID_ALMACEN,A.ID_ARTICULO 
                ) ";
                $query = DB::select($sql);
                return $query;
        }

        public static function listWerehouseType()
        {
                $sql = "SELECT te.id_existencia, te.codigo, te.nombre
                FROM TIPO_EXISTENCIA  TE
                WHERE TE.ESTADO = 1";
                $query = DB::select($sql);
                return $query;
        }
        public static function listStockArticlesAll($id_anho, $id_almacen, $id_articulo)
        {
                $sql = "SELECT B.ID_PARENT,PKG_INVENTORIES.FC_ARTICULO(B.ID_PARENT) NAME_PARENT,
        B.ID_ARTICULO, A.ID_ALMACEN,
               B.NOMBRE,B.CODIGO,
               A.ID_ANHO,
        PKG_INVENTORIES.FC_ARTICULO_UNIDAD_MEDIDA(B.ID_ARTICULO) UNIDAD_MEDIDA,
        A.CODIGO AS COD_CW,
        A.UBICACION AS UBICACION,
        A.STOCK_ACTUAL AS CANTIDAD,
        A.ABREVIATURA AS ABREV,
        ROUND((A.COSTO_TOTAL)/DECODE((A.STOCK_ACTUAL),0,1,(A.STOCK_ACTUAL)),2) COSTO_UNITARIO,
        A.COSTO_TOTAL
        FROM INVENTARIO_ALMACEN_ARTICULO A, INVENTARIO_ARTICULO B WHERE A.ID_ARTICULO=B.ID_ARTICULO
        AND A.ID_ANHO = " . $id_anho . "
        AND A.ID_ALMACEN = " . $id_almacen . "
        ORDER BY A.ID_ARTICULO, A.ID_ALMACEN";
                $query = DB::select($sql);
                return $query;
        }
        public static function listMiscellaneousOutputs($id_voucher, $almacen, $tipo, $s_all)
        {
                $prQ = "";
                if ($s_all === "SI") {
                        $prQ = "IN (" . $id_voucher . ")";
                } else {
                        $prQ = "= " . $id_voucher;
                }
                $sql = "SELECT 
                        A.ID_MOVIMIENTO,NVL(A.GUIA,'') AS GUIA,A.SERIE,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,C.NOMBRE,B.CANTIDAD,B.COSTO,B.IMPORTE,
                        A.ID_VOUCHER ||' - '|| A.FECHA as VOUCHER
                FROM INVENTARIO_MOVIMIENTO A JOIN INVENTARIO_DETALLE B
                ON A.ID_MOVIMIENTO = B.ID_MOVIMIENTO
                JOIN INVENTARIO_ARTICULO C
                ON B.ID_ARTICULO = C.ID_ARTICULO
                WHERE  A.ID_ALMACEN = $almacen
                AND A.ID_VOUCHER $prQ 
                AND A.TIPO = '" . $tipo . "'
                AND A.ESTADO = '1'
                ORDER BY A.ID_MOVIMIENTO,B.ID_MOVDETALLE ";
                $query = DB::select($sql);
                return $query;
        }
        public static function listMiscellaneousOutputsTotal($id_voucher, $almacen, $tipo,  $s_all)
        {
                $prQ = '';
                if ($s_all === "SI") {
                        $prQ = "IN (" . $id_voucher . ")";
                } else {
                        $prQ = "= " . $id_voucher;
                }
                $sql = "SELECT 
                        SUM(B.CANTIDAD) AS CANTIDAD,SUM(B.IMPORTE) AS IMPORTE
                FROM INVENTARIO_MOVIMIENTO A JOIN INVENTARIO_DETALLE B
                ON A.ID_MOVIMIENTO = B.ID_MOVIMIENTO
                JOIN INVENTARIO_ARTICULO C
                ON B.ID_ARTICULO = C.ID_ARTICULO
                WHERE A.ID_ALMACEN = $almacen 
                AND A.ID_VOUCHER $prQ 
                AND A.TIPO = '" . $tipo . "'
                AND A.ESTADO = '1' ";
                $query = DB::select($sql);
                return $query;
        }

        //traslado de receta

        public static function getALmacenReceta($request)
        {
                $id_receta = $request->id_receta;
                $sql = "SELECT 
                irp.id_receta,
                irp.id_almacen,
                irp.id_articulo,
                ia.nombre as almacen,
                iar.nombre as articulo,
                irp.cantidad,
                irp.cantidad as cantidad_base,
                irp.id_dinamica
                FROM INVENTARIO_RECETA_PRODUCTO irp
                JOIN inventario_almacen ia on ia.id_almacen = irp.id_almacen
                JOIN inventario_articulo iar on iar.id_articulo = irp.id_articulo
                WHERE irp.id_receta = $id_receta";
                $query = DB::select($sql);
                return $query;
        }

        public static function trasladarArticulo($data)
        {
                $params = self::startInsert($data);
                $rpta = self::insertMovimiento($params);
                if ($rpta['success']) {
                        $id_movimiento = $rpta['id_movimiento'];

                        $paramsDet = self::startInsertDetail($id_movimiento, $data);
                        $rptaDet = self::insertDetailMovimiento($paramsDet);
                        if ($rptaDet['success']) {
                                $rptaFinish = self::finishMovimiento($id_movimiento);
                                if ($rptaFinish['success']) {
                                        return [
                                                "success" => true,
                                        ];
                                } else {
                                        return [
                                                "success" => false,
                                                "message" => $rptaFinish["message"]
                                        ];
                                }
                        } else {
                                return [
                                        "success" => false,
                                        "message" => $rptaDet["message"]
                                ];
                        }
                } else {
                        return [
                                "success" => false,
                                "message" => $rpta["message"]
                        ];
                }
        }

        public static function startInsert($data)
        {
                $id_movimiento_base = $data->id_movimiento_base;

                $dat = DB::table("inventario_movimiento");
                $dat->where("id_movimiento", $id_movimiento_base);
                $movimiento = $dat->first();

                $params = new stdClass();
                $params->tipo = 'I';
                $params->id_almacen = $data->id_almacen;
                $params->id_entidad = $movimiento->id_entidad;
                $params->id_depto = $movimiento->id_depto;
                $params->id_user = $movimiento->id_persona;
                $params->id_anho = $movimiento->id_anho;
                $params->id_mes = $movimiento->id_mes;
                $params->id_receta = $movimiento->id_receta;
                $params->id_tipooperacion = $data->id_tipooperacion;
                $params->id_documento = $movimiento->id_documento;
                $params->cantidad = $data->cantidad;
                $params->num_movimiento = null;


                return $params;
        }

        public static function insertMovimiento($params)
        {
                $id_almacen = $params->id_almacen;
                $id_entidad = $params->id_entidad;
                $id_user = $params->id_user;
                $id_depto = $params->id_depto;
                $id_anho = $params->id_anho;
                $id_mes = $params->id_mes;
                $id_receta = $params->id_receta;
                $id_tipooperacion = $params->id_tipooperacion;
                $id_documento = $params->id_documento;
                $tipo = $params->tipo;
                $cantidad = $params->cantidad;
                $num_movimiento = $params->num_movimiento ?? null;
                $ip = '';
                $id_movimiento = 0;
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_MOVIMIENTO(:P_ID_ALMACEN, :P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, 
                        :P_ID_RECETA, :P_ID_PERSONA, :P_ID_TIPOOPERACION, :P_ID_DOCUMENTO, :P_TIPO, :P_IP, :P_CANTIDAD, :P_GUIA, :P_ID_MOVIMIENTO,
                        :P_ERROR, :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_RECETA', $id_receta, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOOPERACION', $id_tipooperacion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
                $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
                $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_GUIA', $num_movimiento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 1) {
                        return [
                                "success" => false,
                                "message" => "IM1- " . $msgerror
                        ];
                } else {
                        return [
                                "success" => true,
                                "id_movimiento" => $id_movimiento
                        ];
                }
        }

        public static function startInsertDetail($id_movimiento, $data)
        {
                $params = new stdClass();
                $params->id_movimiento = $id_movimiento;
                $params->id_dinamica = $data->id_dinamica;
                $params->cantidad = $data->cantidad;
                $params->costo = $data->costo;
                return $params;
        }

        public static function insertDetailMovimiento($params)
        {
                $id_dinamica = $params->id_dinamica;
                $id_movimiento = $params->id_movimiento;
                $cantidad = $params->cantidad;
                $costo = $params->costo;
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_INVENTORIES.SP_INSERT_DETALLE_RECETA_TR(:P_ID_MOVIMIENTO, :P_ID_DINAMICA,:P_CANTIDAD,:P_COSTO, :P_ERROR, :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_COSTO', $costo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();

                if ($error === 1) {
                        return ["success" => false, "message" => "IM2- " . $msgerror];
                } else {
                        return [
                                "success" => true,
                        ];
                }
        }

        public static function finishMovimiento($id_movimiento)
        {
                $pdo = DB::getPdo();
                $error = 0;
                $msgerror = str_repeat("0", 300);
                $stmt = $pdo->prepare("begin 
                PKG_INVENTORIES.SP_FINISH_MOVIMIENTO(:P_ID_MOVIMIENTO, 
                :P_ERROR, 
                :P_MSN_ERROR); end;");
                $stmt->bindParam(':P_ID_MOVIMIENTO', $id_movimiento, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN_ERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if ($error === 1) {
                        return ["success" => false, "message" => "IM3- " . $msgerror];
                } else {
                        return [
                                "success" => true,
                        ];
                }
        }
}
