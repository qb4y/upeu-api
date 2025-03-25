<?php
namespace App\Http\Data\Report\Treasury;

use Illuminate\Support\Facades\DB;

class MyVouchersData
{
    public static function myValesList($request)
    {
        $id_voucher = $request->id_voucher;
        $da = DB::table('ELISEO.CAJA_PAGO A');
        $da->join('ELISEO.CAJA_VALE B', 'A.ID_VALE', '=', 'B.ID_VALE');
        $da->join('MOISES.PERSONA C', 'B.ID_EMPLEADO', '=', 'C.ID_PERSONA');
        $da->leftJoin('MOISES.PERSONA_NATURAL D', 'C.ID_PERSONA', '=', 'D.ID_PERSONA');
        $da->select('A.ID_PAGO','A.IMPORTE','A.IMPORTE_ME','A.FECHA','A.ESTADO',
        'B.ID_VALE','B.NRO_VALE','B.FECHA AS FECHA_VALE','B.IMPORTE AS IMPORTE_VALE','B.IMPORTE_ME AS IMPORTE_ME_VALE',
        'C.NOMBRE','C.PATERNO','C.MATERNO','D.NUM_DOCUMENTO');
        $da->where('A.ID_VOUCHER', '=', $id_voucher);
        $da->orderBy('A.ID_PAGO');
        $vales = $da->get();
        foreach ($vales as $value) {
            $item = (Object)$value;
            $item->vale_file = self::fileVales($item->id_vale);
            $gasCom = self::gastosCompras($id_voucher, $item->id_pago);
            $item->gasto_compra = $gasCom;
            $item->rowspan = count($gasCom);
            $subTotal = self::subTotal($id_voucher, $item->id_pago);
            $item->sub_total = $subTotal[0];
        }
        return $vales;
    }
    public static function fileVales($id_vale)
    {
        $da = DB::table('ELISEO.CAJA_VALE_FILE');
        $da->where('ID_VALE', '=', $id_vale);
        $da->where('TIPO', '=', '2');
        $file_vales = $da->get();
        return $file_vales;
    }
    public static function gastosCompras($id_voucher, $id_pago)
    {
        $query = "SELECT 
        A.ID_PAGO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VOUCHER,A.FECHA,A.IMPORTE AS TOTAL,A.IMPORTE_ME AS TOTAL_ME,
        D.ID_VALE,D.NRO_VALE,D.IMPORTE AS IMPORTE_VALE,D.IMPORTE_ME AS IMPORTE_ME_VALE,---D.DETALLE,
        B.ID_PGASTO,B.ID_PERSONA,B.ID_DINAMICA AS ID_COMPRA,B.IMPORTE,B.IMPORTE_ME,B.DETALLE,
        E.CUENTA,E.CUENTA_CTE,E.DEPTO,E.IMPORTE AS IMPORTE_ASIENTO,E.IMPORTE_ME AS IMPORTE_ME_ASIENTO ,E.DESCRIPCION,
        '0' AS ORDEN,
        'GASTO' AS FILE_COMPRA_GASTO
        FROM CAJA_PAGO A JOIN CAJA_PAGO_GASTO B ON A.ID_PAGO = B.ID_PAGO
        JOIN CAJA_VALE D ON A.ID_VALE = D.ID_VALE
        JOIN CONTA_ASIENTO E ON A.ID_VOUCHER = E.VOUCHER AND B.ID_TIPOORIGEN = E.ID_TIPOORIGEN AND B.ID_PGASTO = E.ID_ORIGEN
        WHERE A.ID_VOUCHER = ".$id_voucher."
        AND A.ID_PAGO = ".$id_pago."
        AND E.IMPORTE > 0
        UNION ALL
        SELECT 
        A.ID_PAGO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VOUCHER,A.FECHA,A.IMPORTE AS TOTAL,A.IMPORTE_ME AS TOTAL_ME,
        D.ID_VALE,D.NRO_VALE,D.IMPORTE AS IMPORTE_VALE,D.IMPORTE_ME AS IMPORTE_ME_VALE,---D.DETALLE,
        B.ID_PCOMPRA,B.ID_PROVEEDOR,B.ID_COMPRA,B.IMPORTE,B.IMPORTE_ME,B.DETALLE,
        E.CUENTA,E.CUENTA_CTE,E.DEPTO,E.IMPORTE AS IMPORTE_ASIENTO,E.IMPORTE_ME AS IMPORTE_ME_ASIENTO ,E.DESCRIPCION,
        '1' AS ORDEN,
        'COMPRA' AS FILE_COMPRA_GASTO
        FROM CAJA_PAGO A JOIN CAJA_PAGO_COMPRA B ON A.ID_PAGO = B.ID_PAGO
        JOIN CAJA_VALE D ON A.ID_VALE = D.ID_VALE
        JOIN CONTA_ASIENTO E ON A.ID_VOUCHER = E.VOUCHER AND B.ID_TIPOORIGEN = E.ID_TIPOORIGEN AND B.ID_PCOMPRA = E.ID_ORIGEN
        WHERE A.ID_VOUCHER = ".$id_voucher."
        AND A.ID_PAGO = ".$id_pago."
        AND E.IMPORTE > 0
        ORDER BY ID_PAGO,ID_VALE,ORDEN";
        $oQuery = DB::select($query);

        foreach ($oQuery as $value) {
            $item = (Object)$value;
            $item->gasto_compra_file = array();
            if ($item->file_compra_gasto == 'GASTO') {
                $item->gasto_compra_file = self::fileGastos($item->id_vale, $item->id_pgasto);
            }
            if ($item->file_compra_gasto == 'COMPRA') {
                $item->gasto_compra_file = self::fileCompras($item->id_compra);
            }
        }
        return $oQuery;
    }
    public static function fileGastos($id_vale, $id_pgasto)
    {
        $da = DB::table('ELISEO.CAJA_VALE_FILE');
        $da->where('ID_VALE', '=', $id_vale);
        $da->where('ID_PGASTO', '=', $id_pgasto);
        $file_vales = $da->get();
        return $file_vales;
    }
    public static function fileCompras($id_compra)
    {
        $da = DB::table('ELISEO.PEDIDO_FILE A');
        $da->join('ELISEO.PEDIDO_COMPRA B', 'A.ID_PEDIDO', '=', 'B.ID_PEDIDO');
        $da->where('B.ID_COMPRA', '=', $id_compra);
        $file_vales = $da->get();
        return $file_vales;
    }
    public static function subTotal($id_voucher, $id_pago)
    {
        $query = "SELECT SUM(IMPORTE_ASIENTO) AS IMPORTE_ASIENTO,SUM(IMPORTE_ME_ASIENTO) AS IMPORTE_ME_ASIENTO FROM (
            SELECT 
            E.IMPORTE AS IMPORTE_ASIENTO,E.IMPORTE_ME AS IMPORTE_ME_ASIENTO 
            FROM CAJA_PAGO A JOIN CAJA_PAGO_GASTO B ON A.ID_PAGO = B.ID_PAGO
            JOIN CAJA_VALE D ON A.ID_VALE = D.ID_VALE
            JOIN CONTA_ASIENTO E ON A.ID_VOUCHER = E.VOUCHER AND B.ID_TIPOORIGEN = E.ID_TIPOORIGEN AND B.ID_PGASTO = E.ID_ORIGEN
            WHERE A.ID_VOUCHER = ".$id_voucher."
            AND A.ID_PAGO = ".$id_pago."
            AND E.IMPORTE > 0
            UNION ALL
            SELECT 
            E.IMPORTE AS IMPORTE_ASIENTO,E.IMPORTE_ME AS IMPORTE_ME_ASIENTO
            FROM CAJA_PAGO A JOIN CAJA_PAGO_COMPRA B ON A.ID_PAGO = B.ID_PAGO
            JOIN CAJA_VALE D ON A.ID_VALE = D.ID_VALE
            JOIN CONTA_ASIENTO E ON A.ID_VOUCHER = E.VOUCHER AND B.ID_TIPOORIGEN = E.ID_TIPOORIGEN AND B.ID_PCOMPRA = E.ID_ORIGEN
            WHERE A.ID_VOUCHER = ".$id_voucher."
            AND A.ID_PAGO = ".$id_pago."
            AND E.IMPORTE > 0
            )";
        $oQuery = DB::select($query);
        
        return $oQuery;
    }
    
}       