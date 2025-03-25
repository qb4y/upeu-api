<?php
namespace App\Http\Data\Inventories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class PedidoRegistroData extends Controller{
   
    
        public static function listTipoPedidos(){
                $sql = "SELECT ID_TIPOPEDIDO, NOMBRE, ESTADO, LLAVE FROM TIPO_PEDIDO";
                $query = DB::select($sql);
                return $query;
        }

        public static function pedidoDetallesByIdPedido($id_pedido){
                $sql = "SELECT A.id_detalle, A.ID_PEDIDO, A.cantidad,
                A.detalle, A.importe, A.precio, B.CODIGO ,
                C.ID_ALMACEN_DESTINO, C.ID_ANHO ,
                A.ID_PEDIDO ,A.ID_ARTICULO,
                (SELECT COALESCE(MAX(Y.STOCK_ACTUAL),-1) FROM INVENTARIO_ALMACEN_ARTICULO Y
                        WHERE Y.ID_ARTICULO = A.ID_ARTICULO AND 
                        Y.ID_ALMACEN = C.ID_ALMACEN_DESTINO AND 
                        Y.ID_ANHO  = C.ID_ANHO 
                        ) AS STOCK_DESTINO
                FROM PEDIDO_DETALLE A 
                        INNER JOIN INVENTARIO_ARTICULO B ON A.ID_ARTICULO =B.ID_ARTICULO 
                        INNER JOIN PEDIDO_REGISTRO C ON A.ID_PEDIDO = C.ID_PEDIDO 
                WHERE A.id_pedido =$id_pedido";
                $query = DB::select($sql);
                return $query;
        }

        public static function showPedidoRegistro($id_pedido){
                $sql = "SELECT A.ID_PEDIDO, A.ID_ENTIDAD, A.ID_DEPTO,A.ID_ANHO, A.ID_MES,
                A.ID_PERSONA, A.ID_TIPOPEDIDO, A.NUMERO,A.FECHA, 
                TO_CHAR(A.FECHA_PEDIDO,'DD/MM/YYYY') AS FECHA_PEDIDO,
                A.MOTIVO, A.ESTADO,
                DECODE(A.ESTADO,0,'Registrado',1,'Enviado',2,'Aceptado y despachado',3,'Rechazado',4,'Aceptado y recibido','-') AS ESTADO_LABEL,
                A.ID_ALMACEN_ORIGEN, A.ID_ALMACEN_DESTINO ,
                B.NOMBRE AS ORIGEN_NOMBRE, C.NOMBRE AS DESTINO_NOMBRE,
                D.NOMBRE AS TIPOPEDIDO_NOMBRE
                FROM PEDIDO_REGISTRO A
                LEFT JOIN INVENTARIO_ALMACEN B ON A.ID_ALMACEN_ORIGEN =B.ID_ALMACEN 
                LEFT JOIN INVENTARIO_ALMACEN C ON A.ID_ALMACEN_DESTINO =C.ID_ALMACEN 
                LEFT JOIN TIPO_PEDIDO D ON A.ID_TIPOPEDIDO =D.ID_TIPOPEDIDO 
                WHERE A.ID_PEDIDO =$id_pedido
                ";
                $query = DB::select($sql);
                return $query;
        }

        public static function listMovimientosByIdPedido($id_pedido){
                $sql = "SELECT 
                A.ID_MOVIMIENTO,A.ID_ALMACEN,A.ID_ENTIDAD,A.ID_DEPTO,
                A.ID_ALMACEN,A.ID_ANHO,
                A.ID_MES, A.ID_TIPOOPERACION,
                B.NOMBRE AS ALMACEN_NOMBRE,
                UPPER(C.NOMBRE) AS MES_NOMBRE,
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                A.SERIE,A.NUMERO,
                UPPER(DECODE(A.TIPO,'S','Salida','Ingreso')) AS TIPO_LABEL,
                A.TIPO AS TIPO
                FROM INVENTARIO_MOVIMIENTO A
                INNER JOIN INVENTARIO_ALMACEN B ON A.ID_ALMACEN=B.ID_ALMACEN 
                INNER JOIN CONTA_MES C ON A.ID_MES=C.ID_MES 
                WHERE A.ID_PEDIDO = $id_pedido
                ";
                $query = DB::select($sql);
                return $query;
        }

        public static function pedidoRegistroByAlmacenOrigen($id_almacen, $id_mes){
                $sql = "SELECT A.ID_PEDIDO, A.ID_ENTIDAD, A.ID_DEPTO,
                A.ID_ANHO, A.ID_MES, UPPER(E.NOMBRE) AS NOMBRE_MES, A.ID_PERSONA, A.ID_TIPOPEDIDO, A.ID_GASTO,
                A.ID_TIPOTRANSACCION,A.NUMERO, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(A.FECHA_PEDIDO,'DD/MM/YYYY') AS FECHA_PEDIDO,
                A.MOTIVO, 
                A.ESTADO,
                DECODE(A.ESTADO,0,'Registrado',1,'Enviado',2,'Aceptado y despachado',3,'Rechazado',4,'Aceptado y recibido','-') AS ESTADO_LABEL,
                A.ID_ALMACEN_DESTINO, A.ID_ALMACEN_ORIGEN,
                B.NOMBRE AS ORIGEN_NOMBRE, C.NOMBRE AS DESTINO_NOMBRE,
                D.NOMBRE AS TIPOPEDIDO_NOMBRE,
                (SELECT MAX(X.EMAIL) FROM USERS X WHERE X.ID=A.ID_PERSONA) AS USER_CREATED,
                1 as can_update
                FROM PEDIDO_REGISTRO A
                LEFT JOIN INVENTARIO_ALMACEN B ON A.ID_ALMACEN_ORIGEN =B.ID_ALMACEN 
                LEFT JOIN INVENTARIO_ALMACEN C ON A.ID_ALMACEN_DESTINO =C.ID_ALMACEN 
                LEFT JOIN TIPO_PEDIDO D ON A.ID_TIPOPEDIDO =D.ID_TIPOPEDIDO 
                LEFT JOIN CONTA_MES E ON A.ID_MES = E.ID_MES 
                WHERE A.ID_ALMACEN_ORIGEN =$id_almacen
                AND A.ID_MES=$id_mes ORDER BY A.NUMERO DESC
                ";
                $query = DB::select($sql);
                return $query;
        }

        public static function pedidoRegistroByAlmacenDestino($id_almacen, $id_mes){
                $sql = "SELECT A.ID_PEDIDO, A.ID_ENTIDAD, A.ID_DEPTO,
                A.ID_ANHO, A.ID_MES, UPPER(E.NOMBRE) AS NOMBRE_MES, A.ID_PERSONA, A.ID_TIPOPEDIDO, A.ID_GASTO,
                A.ID_TIPOTRANSACCION,A.NUMERO, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(A.FECHA_PEDIDO,'DD/MM/YYYY') AS FECHA_PEDIDO,
                A.MOTIVO, 
                A.ESTADO,
                DECODE(A.ESTADO,0,'Registrado',1,'Recibido',2,'Aceptado y despachado',3,'Rechazado',4,'Aceptado y recibido','-') AS ESTADO_LABEL,
                A.ID_ALMACEN_DESTINO, A.ID_ALMACEN_ORIGEN,
                B.NOMBRE AS ORIGEN_NOMBRE, C.NOMBRE AS DESTINO_NOMBRE,
                D.NOMBRE AS TIPOPEDIDO_NOMBRE,
                (SELECT MAX(X.EMAIL) FROM USERS X WHERE X.ID=A.ID_PERSONA) AS USER_CREATED,
                0 as can_update
                FROM PEDIDO_REGISTRO A
                LEFT JOIN INVENTARIO_ALMACEN B ON A.ID_ALMACEN_ORIGEN =B.ID_ALMACEN 
                LEFT JOIN INVENTARIO_ALMACEN C ON A.ID_ALMACEN_DESTINO =C.ID_ALMACEN 
                LEFT JOIN TIPO_PEDIDO D ON A.ID_TIPOPEDIDO =D.ID_TIPOPEDIDO 
                LEFT JOIN CONTA_MES E ON A.ID_MES = E.ID_MES 
                WHERE A.ID_ALMACEN_DESTINO =$id_almacen
                AND A.ESTADO<>'0' --Menos los que recien se registraron
                AND A.ID_MES=$id_mes ORDER BY A.NUMERO DESC
                ";
                $query = DB::select($sql);
                return $query;
        }

}