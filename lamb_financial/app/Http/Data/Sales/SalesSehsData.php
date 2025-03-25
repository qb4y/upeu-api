<?php
namespace App\Http\Data\Sales;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
// use App\Http\Data\Sales\ComunData;
// use App\Http\Data\Accounting\Setup\AccountingData;
use Exception;

class SalesSehsData extends Controller{

    public static function listMySalesAnticipadas($id_voucher, $text_search){
        $query = "SELECT 
                A.ID_VENTA,
                FC_NOMBRE_CLIENTE(A.ID_CLIENTE) CLIENTE,
                A.ID_VOUCHER,
                A.SERIE,
                A.NUMERO,
                TO_CHAR(A.FECHA, 'DD/MM/YYY') AS FECHA,
                A.GLOSA,
                A.TOTAL,
                (SELECT C.NUMERO || '-' || TO_CHAR(C.FECHA,'DD/MM/YYYY') FROM CONTA_VOUCHER C WHERE A.ID_VOUCHER=C.ID_VOUCHER ) AS NOMBRE_VOUCHER,
                (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS EDIT,

                NVL((SELECT DECODE(X.ESTADO,'1','S','2','OK','N') FROM ARREGLO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO 
                AND X.ID_ORIGEN = A.ID_VENTA AND X.ID_TIPOORIGEN = 1 GROUP BY X.ESTADO),'N') SOLICITADO

                FROM VENTA A
                WHERE 
                A.ID_VOUCHER = ".$id_voucher."
                AND A.ES_AUTOENTREGA=0
                AND A.ESTADO = '1'
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VENTA, A.ID_CLIENTE,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL 
                ORDER BY A.ID_VENTA ";
            $oQuery = DB::select($query);
            // AND ( UPPER(A.NUMERO) || '-' || UPPER(A.SERIE) LIKE UPPER('%$text_search%') OR
            //     UPPER(A.GLOSA) LIKE UPPER('%$text_search%') OR
            //     UPPER(FC_NOMBRE_CLIENTE(A.ID_CLIENTE)) LIKE UPPER('%$text_search%')
            //     )
        return $oQuery;
    }

    public static function listMySalesAnticipadasToSearch($id_almacen, $text_search){
        // FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,
        // A.ID_ENTIDAD = ".$id_entidad."
        // AND A.ID_DEPTO = '".$id_depto."'
        // AND A.ID_PERSONA = ".$id_user."

        $query = "SELECT 
                    A.ID_VENTA,
                    FC_NOMBRE_CLIENTE(A.ID_CLIENTE) CLIENTE,
                    A.ID_VOUCHER,
                    A.SERIE,
                    A.NUMERO,
                    TO_CHAR(A.FECHA, 'DD/MM/YYY') AS FECHA,
                    A.GLOSA,
                    A.TOTAL,
                    (SELECT C.NUMERO || '-' || TO_CHAR(C.FECHA,'DD/MM/YYYY') FROM CONTA_VOUCHER C WHERE A.ID_VOUCHER=C.ID_VOUCHER ) AS NOMBRE_VOUCHER,
                    (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS EDIT,
                    NVL((SELECT DECODE(X.ESTADO,'1','S','2','OK','N') FROM ARREGLO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO 
                    AND X.ID_ORIGEN = A.ID_VENTA AND X.ID_TIPOORIGEN = 1 GROUP BY X.ESTADO),'N') SOLICITADO,
                    (SELECT EMAIL FROM USERS WHERE ID=A.ID_PERSONA) AS USER_REGISTERED
                FROM VENTA A
                WHERE EXISTS (SELECT 1 FROM VENTA_DETALLE X WHERE X.ID_ALMACEN=$id_almacen
                                            AND X.ID_VENTA=A.ID_VENTA)
                --AND A.ES_AUTOENTREGA=0
                AND A.ESTADO = '1'
                AND ( (UPPER(A.SERIE||A.NUMERO)) LIKE UPPER(REPLACE(REPLACE('%$text_search%',' ',''),'-',''))
                    OR UPPER(A.SERIE) LIKE UPPER('%$text_search%') OR
                    UPPER(A.NUMERO) LIKE UPPER('%$text_search%') OR
                    UPPER(A.GLOSA) LIKE UPPER('%$text_search%') OR
                    UPPER(REPLACE(REPLACE(FC_NOMBRE_CLIENTE(A.ID_CLIENTE),' ',''),'-','')) LIKE UPPER(REPLACE(REPLACE('%$text_search%',' ',''),'-',''))
                )
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VENTA, A.ID_CLIENTE,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL,A.ID_PERSONA
                ORDER BY A.ID_VENTA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listMySalesSehs($id_voucher, $text_search){
        $query = "SELECT 
                A.ID_VENTA,
                FC_NOMBRE_CLIENTE(A.ID_CLIENTE) CLIENTE,
                A.ID_VOUCHER,
                A.SERIE,
                A.NUMERO,
                TO_CHAR(A.FECHA, 'DD/MM/YYY') AS FECHA,
                A.GLOSA,
                A.TOTAL,
                A.ES_AUTOENTREGA,
                (SELECT MAX(X.NOMBRE_CORTO) FROM TIPO_COMPROBANTE X
                    WHERE A.ID_COMPROBANTE=X.ID_COMPROBANTE) AS LABEL_COMPROBANTE,
                to_char(A.FECHA,'HH:MI am') AS HORA,
                (SELECT C.NUMERO || '-' || TO_CHAR(C.FECHA,'DD/MM/YYYY') FROM CONTA_VOUCHER C WHERE A.ID_VOUCHER=C.ID_VOUCHER ) AS NOMBRE_VOUCHER,
                (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS EDIT,
                NVL((SELECT DECODE(X.ESTADO,'1','S','2','OK','N') FROM ARREGLO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO 
                AND X.ID_ORIGEN = A.ID_VENTA AND X.ID_TIPOORIGEN = 1 GROUP BY X.ESTADO),'N') SOLICITADO
                FROM VENTA A
                WHERE A.ID_VOUCHER = $id_voucher
                -- AND A.ES_AUTOENTREGA=1
                AND A.ESTADO = '1'
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VENTA, A.ID_CLIENTE,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL
                ,A.ID_COMPROBANTE,A.ES_AUTOENTREGA
                ORDER BY A.ID_VENTA";
            $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listSalesDetailsVentaFinalizada($id_almacen, $id_anho, $id_venta){
       
        $query = "SELECT A.ID_VDETALLE, A.ID_VENTA, A.ID_ARTICULO
        , A.DETALLE
        , A.CANTIDAD_INICIAL
        , A.CANTIDAD
        , A.PRECIO
        , A.IMPORTE
        ,(SELECT max(X.STOCK_ACTUAL) FROM INVENTARIO_ALMACEN_ARTICULO X 
                        WHERE X.ID_ARTICULO=A.ID_ARTICULO AND X.ID_ANHO=$id_anho AND
                        X.ID_ALMACEN=$id_almacen) AS STOCK_ACTUAL
                ,D.ES_DECIMAL AS UNIDADMEDIDA_ES_DECIMAL
        FROM VW_VENTA_DESPACHO_SALDO A 
        INNER JOIN INVENTARIO_ARTICULO C ON A.ID_ARTICULO=C.ID_ARTICULO
        INNER JOIN INVENTARIO_UNIDAD_MEDIDA D ON C.ID_UNIDADMEDIDA=D.ID_UNIDADMEDIDA
        WHERE A.ID_VENTA=$id_venta
        ORDER BY A.ID_VDETALLE DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listSaldoVentasAnticipadas($id_almacen){
       
        $query = "SELECT ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_VENTA,
        CLIENTE,usuario, SERIE,NUMERO,CANTIDAD,IMPORTE FROM (
            SELECT
            A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_VENTA,
            FC_NOMBRE_CLIENTE(B.ID_CLIENTE) CLIENTE,
            (SELECT email FROM USERS WHERE ID=b.ID_PERSONA) AS usuario,
            B.SERIE,B.NUMERO,SUM(A.CANTIDAD) CANTIDAD,
            SUM(A.IMPORTE) IMPORTE
            FROM VW_VENTA_DESPACHO_SALDO A 
            INNER JOIN VENTA B ON A.ID_VENTA=B.ID_VENTA 
            WHERE a.ID_ALMACEN =$id_almacen
            GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_VENTA,
            B.ID_CLIENTE,b.ID_PERSONA,
            B.SERIE,B.NUMERO
            ) X WHERE X.CANTIDAD>0";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listSalesDispatchs($id_voucher){
       
        $query = "SELECT
        --FC_NOMBRE_CLIENTE(A.ID_CLIENTE) CLIENTE,
        TO_CHAR(A.FECHA_EMISION, 'DD/MM/YYY') AS FECHA_EMISION,
         A.ID_VDESPACHO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,
        A.ID_MES,A.ID_ALMACEN,A.ID_VENTA,B.SERIE,B.NUMERO,
        (SELECT SUM(CANTIDAD) FROM VENTA_DESPACHO_DETALLE X 
        WHERE X.ID_VDESPACHO=A.ID_VDESPACHO) AS CANTIDAD_DESPACHADA,
        (SELECT SUM(IMPORTE) FROM VENTA_DESPACHO_DETALLE X
        WHERE X.ID_VDESPACHO=A.ID_VDESPACHO) AS IMPORTE_DESPACHADA,
        (SELECT email FROM USERS WHERE ID=A.ID_PERSONA) AS usuario,
        (SELECT C.ID_TIPOASIENTO || '-' || C.NUMERO || '-' || TO_CHAR(C.FECHA,'DD/MM/YYYY') FROM CONTA_VOUCHER C WHERE A.ID_VOUCHER=C.ID_VOUCHER ) AS NOMBRE_VOUCHER
        FROM VENTA_DESPACHO A INNER JOIN VENTA B ON A.ID_VENTA =B.ID_VENTA 
        WHERE A.ESTADO ='1'
        AND a.ID_VOUCHER =$id_voucher";
        $oQuery = DB::select($query);
        return $oQuery;
    }
}
