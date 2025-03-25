<?php

namespace App\Http\Data\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Data\Setup\Process\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\Accounting\Setup\AccountingData;
use PDO;
use App\Http\Data\FinancesStudent\ComunData;
use App\Http\Controllers\Storage\StorageController;
use DOMPDF;

class ExpensesData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function listPayments($id_entidad, $id_depto, $id_mediopago, $id_voucher)
    {
        $sql = "SELECT (SELECT 
                        B.NOMBRE||' - '||A.NOMBRE||' - '||A.CUENTA_CORRIENTE 
                        FROM CAJA_CUENTA_BANCARIA A, CAJA_ENTIDAD_FINANCIERA B
                        WHERE A.ID_BANCO = B.ID_BANCO
                        AND A.ID_CTABANCARIA = X.ID_CTABANCARIA) AS BANCO,

                        (SELECT A.ID_CUENTAAASI||'-'||A.ID_TIPOCTACTE FROM CAJA_CUENTA_BANCARIA A WHERE A.ID_CTABANCARIA = X.ID_CTABANCARIA) CTA,
                        NVL((SELECT A.NUMERO||' -S/. '||A.IMPORTE FROM CAJA_CHEQUERA A WHERE A.ID_CHEQUERA = X.ID_CHEQUERA),X.NUMERO) AS NUMERO,
                        SUM(X.IMPORTE) IMPORTE,
                        SUM(X.IMPORTE_ME) IMPORTE_ME,
                        TO_CHAR(X.FECHA_REG,'DD/MM/YYYY') AS FECHA_REG
                FROM CAJA_PAGO X
                WHERE X.ID_ENTIDAD = " . $id_entidad . "
                AND X.ID_DEPTO = '" . $id_depto . "'
                AND X.ID_VOUCHER = " . $id_voucher . "
                AND X.ID_MEDIOPAGO = '" . $id_mediopago . "'
                AND X.ESTADO = '1'
                AND X.ID_VALE IS NULL
                GROUP BY X.ID_CTABANCARIA,NUMERO,X.ID_CHEQUERA, FECHA_REG";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsToVales($id_entidad, $id_depto, $id_voucher)
    {
        $sql = "SELECT 
                        FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS NOMBRE_EMPLEADO,
                        TO_CHAR(X.FECHA_REG, 'DD/MM/YYYY') AS FECHA,
                        X.NUMERO,
                        X.IMPORTE,
                        A.NRO_VALE,
                        X.IMPORTE_ME
                FROM CAJA_PAGO X
                INNER JOIN CAJA_VALE A ON X.ID_VALE=A.ID_VALE
                WHERE X.ID_ENTIDAD = $id_entidad
                AND X.ID_DEPTO = '$id_depto'
                AND X.ID_VOUCHER = $id_voucher
                AND X.ESTADO = '1'
                AND X.ID_VALE IS NOT NULL
                ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listDepositsToVales($id_entidad, $id_depto, $id_voucher)
    {
        $sql = "SELECT 
                        FC_NOMBRE_CLIENTE(X.ID_CLIENTE) AS NOMBRE_CLIENTE,
                        TO_CHAR(X.FECHA, 'DD/MM/YYYY') AS FECHA,
                        DECODE(X.ID_MEDIOPAGO,'001','TLC','007','CHQ','008','EFEC') AS MEDIO_PAGO,
                        X.SERIE,
                        X.NUMERO,
                        X.GLOSA,
                        X.IMPORTE,
                        X.IMPORTE_ME,
                        Z.NRO_VALE,
                        X.ID_DEPOSITO,
                        X.NRO_OPERACION
                FROM CAJA_DEPOSITO X 
                INNER JOIN CAJA_VALE Z ON X.ID_VALE=Z.ID_VALE
                WHERE X.ID_ENTIDAD = $id_entidad
                AND X.ID_DEPTO = '$id_depto'
                AND X.ID_VOUCHER = $id_voucher
                AND X.ESTADO = '1'
                AND X.ID_VALE IS NOT NULL
                ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getPaymentToUpdateById($id_payment)
    {
        $sql = "SELECT X.ID_PAGO, X.ID_CHEQUERA, X.ID_TIPOTRANSACCION, X.ID_MONEDA, X.ID_VOUCHER, X.ID_CTABANCARIA, X.NUMERO, TO_CHAR(X.FECHA, 'DD/MM/YYYY') FECHA,
                X.TIPOCAMBIO
                FROM CAJA_PAGO X
                WHERE X.ID_PAGO = $id_payment
                AND X.ESTADO = '1' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getPaymentExpencesById($id_pgasto)
    {
        $sql = "SELECT ID_PGASTO, ID_DINAMICA, ID_PERSONA, DETALLE, IMPORTE,
         IMPORTE_ME, FECHA, ID_TIPOORIGEN, ID_MONEDA FROM CAJA_PAGO_GASTO WHERE ID_PGASTO =$id_pgasto";
        $query = DB::select($sql);
        return $query;
    }

    public static function getPaymentExpencesSeatById($id_pgasto)
    {
        $sql = "SELECT ID_GASIENTO, ID_PGASTO, ID_CUENTAAASI, 
        ID_RESTRICCION, ID_CTACTE, ID_FONDO, ID_DEPTO, IMPORTE,
         IMPORTE_ME, DESCRIPCION FROM CAJA_PAGO_GASTO_ASIENTO WHERE ID_PGASTO =$id_pgasto
        ";
        $query = DB::select($sql);
        return $query;
    }

    public static function deletePaymentExpencesSeat($id_pgasto, $id_gasiento)
    {
        $sql = "DELETE CAJA_PAGO_GASTO_ASIENTO WHERE ID_PGASTO = $id_pgasto AND ID_GASIENTO =$id_gasiento";
        $query = DB::delete($sql);
    }

    public static function getPaymentCompraToUpdateById($id_payment)
    {
        $sql = "SELECT P.ID_PCOMPRA AS ID_DETALLE, P.ID_PAGO, P.ID_PROVEEDOR AS  ID_PROVEEDOR, P.ID_COMPRA, P.ID_DINAMICA, P.IMPORTE, P.IMPORTE_ME, 
        C.SERIE, C.NUMERO, FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) AS NOMBRE, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC
                , (C.SERIE|| '-' ||C.NUMERO || ' | ' || FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) || ' ' ||FC_NOMBRE_PERSONA(C.ID_PROVEEDOR)) AS DETALLE, 'C' OPERACION
                , TO_CHAR(C.FECHA_DOC,'DD/MM/YYYY') AS FECHA
                FROM CAJA_PAGO_COMPRA P
                INNER JOIN COMPRA C ON P.ID_COMPRA = C.ID_COMPRA
                WHERE ID_PAGO=$id_payment
                UNION ALL
                SELECT ID_PGASTO AS ID_DETALLE, ID_PAGO, ID_PERSONA AS ID_PROVEEDOR,0 AS ID_COMPRA, ID_DINAMICA, IMPORTE, IMPORTE_ME,
                '' AS SERIE, '' AS NUMERO, '' AS NOMBRE, '' AS RUC
                , DETALLE, 'G' OPERACION
                , TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA
                FROM CAJA_PAGO_GASTO
                WHERE ID_PAGO=$id_payment
                UNION ALL
                SELECT ID_PVENTA AS ID_DETALLE, ID_PAGO, ID_CLIENTE AS ID_PROVEEDOR, ID_VENTA AS ID_COMPRA, ID_DINAMICA, IMPORTE, IMPORTE_ME,
                '' AS SERIE, '' AS NUMERO, '' AS NOMBRE, '' AS RUC
                , DETALLE, 'V' OPERACION
                ,'' AS FECHA
                FROM CAJA_PAGO_VENTA
                WHERE ID_PAGO=$id_payment
                 ";
        $query = DB::select($sql);
        /*

        SELECT
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'G'||B.ID_PGASTO AS ID_DETALLE,B.ID_DINAMICA,B.ID_PERSONA,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,B.DETALLE,B.IMPORTE,B.IMPORTE_ME
                FROM CAJA_PAGO A, CAJA_PAGO_GASTO B
                WHERE A.ID_PAGO = B.ID_PAGO
                AND A.ID_PAGO = ".$id_pago."
                AND A.ESTADO = '0'
                UNION ALL
                SELECT
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PCOMPRA AS ID_DETALLE,B.ID_DINAMICA,B.ID_PROVEEDOR,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA, C.ID_ENTIDAD || '-' || C.CORRELATIVO || ' PAGO PROVEEDORES' AS DETALLE,B.IMPORTE,B.IMPORTE_ME
                FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B, COMPRA C
                WHERE A.ID_PAGO = B.ID_PAGO
                AND B.ID_COMPRA = C.ID_COMPRA
                AND A.ID_PAGO = ".$id_pago."
                AND A.ESTADO = '0'
                UNION ALL
                SELECT
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PVENTA AS ID_DETALLE,B.ID_DINAMICA,B.ID_CLIENTE,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,'PAGO CLIENTES' AS DETALLE,B.IMPORTE,B.IMPORTE_ME
                FROM CAJA_PAGO A, CAJA_PAGO_VENTA B
                WHERE A.ID_PAGO = B.ID_PAGO
                AND A.ID_PAGO = ".$id_pago."
                AND A.ESTADO = '0'

         */


        return $query;
    }

    public static function listPaymentsDetails($id_pago, $request)
    {
        $estado = '0';
        if (!empty($request->estado)) {
            $estado = $request->estado;
        }
        $sql = "SELECT 
        TO_NUMBER('') AS ID_COMPRA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'G'||B.ID_PGASTO AS ID_DETALLE,B.ID_DINAMICA,B.ID_PERSONA,
        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,B.DETALLE,B.IMPORTE,B.IMPORTE_ME,
        (SELECT COUNT(X.ID_VFILE)  FROM eliseo.caja_vale_file X WHERE X.ID_PGASTO=B.ID_PGASTO) AS EXISTE
        FROM CAJA_PAGO A, CAJA_PAGO_GASTO B 
        WHERE A.ID_PAGO = B.ID_PAGO
        AND A.ID_PAGO = $id_pago
        AND A.ESTADO = $estado
        UNION ALL
        SELECT 
                C.ID_COMPRA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PCOMPRA AS ID_DETALLE,B.ID_DINAMICA,B.ID_PROVEEDOR,
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA, 
                C.ID_ENTIDAD || '-' || C.CORRELATIVO || '-(Doc:' || C.SERIE || '-' || C.NUMERO || ')-' || FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) || ' ' || FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) ||  ' PAGO PROVEEDORES' AS DETALLE,
                B.IMPORTE,B.IMPORTE_ME, 0 AS EXISTE
        FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B, COMPRA C
        WHERE A.ID_PAGO = B.ID_PAGO
        AND B.ID_COMPRA = C.ID_COMPRA
        AND A.ID_PAGO = $id_pago
        AND A.ESTADO = $estado 
        UNION ALL
        SELECT 
                TO_NUMBER('') AS ID_COMPRA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'V'||B.ID_PVENTA AS ID_DETALLE,B.ID_DINAMICA,B.ID_CLIENTE,
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,'PAGO CLIENTES' AS DETALLE,B.IMPORTE,B.IMPORTE_ME, 0 AS EXISTE
        FROM CAJA_PAGO A, CAJA_PAGO_VENTA B 
        WHERE A.ID_PAGO = B.ID_PAGO
        AND A.ID_PAGO = $id_pago
        AND A.ESTADO = $estado 
        UNION ALL
        SELECT 
                C.ID_SALDO AS ID_COMPRA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PCOMPRA AS ID_DETALLE,B.ID_DINAMICA,B.ID_PROVEEDOR,
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA, 
                C.ID_ENTIDAD || '-' || 0 || '-(Doc:' || C.SERIE || '-' || C.NUMERO || ')-' || FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) || ' ' || FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) ||  ' PAGO PROVEEDORES' AS DETALLE,
                B.IMPORTE,B.IMPORTE_ME, 0 AS EXISTE
        FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B, COMPRA_SALDO C
        WHERE A.ID_PAGO = B.ID_PAGO
        AND B.ID_SALDO = C.ID_SALDO
        AND A.ID_PAGO = $id_pago
        AND A.ESTADO = $estado  ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsVale($id_vale, $id_voucher)
    {
        $payment = DB::table('CAJA_PAGO')
            ->select('CAJA_PAGO.*', 'CONTA_MONEDA.SIMBOLO',
                'CAJA_CUENTA_BANCARIA.NOMBRE AS CUENTA_BANCARIA',
                'CAJA_CUENTA_BANCARIA.CUENTA_CORRIENTE AS CUENTA_CORRIENTE')
            ->join('CONTA_MONEDA', 'CAJA_PAGO.ID_MONEDA', '=', 'CONTA_MONEDA.ID_MONEDA')
            ->leftjoin('CAJA_CUENTA_BANCARIA', 'CAJA_PAGO.ID_CTABANCARIA', '=', 'CAJA_CUENTA_BANCARIA.ID_CTABANCARIA')
            ->where('CAJA_PAGO.ID_VALE', $id_vale)
            ->first();

        if ($payment and $id_voucher and $payment->id_voucher != $id_voucher) {
            DB::table('CAJA_PAGO')
                ->where('CAJA_PAGO.ID_VALE', $id_vale)
                ->update(['CAJA_PAGO.ID_VOUCHER' => $id_voucher]);
            $payment = DB::table('CAJA_PAGO')
                ->select('CAJA_PAGO.*', 'CONTA_MONEDA.SIMBOLO',
                    'CAJA_CUENTA_BANCARIA.NOMBRE AS CUENTA_BANCARIA',
                    'CAJA_CUENTA_BANCARIA.CUENTA_CORRIENTE AS CUENTA_CORRIENTE')
                ->join('CONTA_MONEDA', 'CAJA_PAGO.ID_MONEDA', '=', 'CONTA_MONEDA.ID_MONEDA')
                ->leftjoin('CAJA_CUENTA_BANCARIA', 'CAJA_PAGO.ID_CTABANCARIA', '=', 'CAJA_CUENTA_BANCARIA.ID_CTABANCARIA')
                ->where('CAJA_PAGO.ID_VALE', $id_vale)
                ->first();
        }
        return $payment;
    }

    public static function listReportPaymentsVale($id_vale)
    {
        $payment = DB::table('CAJA_PAGO')
            ->select('CAJA_PAGO.*', 'CONTA_MONEDA.SIMBOLO',
                'CAJA_CUENTA_BANCARIA.NOMBRE AS CUENTA_BANCARIA',
                'CAJA_CUENTA_BANCARIA.CUENTA_CORRIENTE AS CUENTA_CORRIENTE')
            ->join('CONTA_MONEDA', 'CAJA_PAGO.ID_MONEDA', '=', 'CONTA_MONEDA.ID_MONEDA')
            ->leftjoin('CAJA_CUENTA_BANCARIA', 'CAJA_PAGO.ID_CTABANCARIA', '=', 'CAJA_CUENTA_BANCARIA.ID_CTABANCARIA')
            ->where('CAJA_PAGO.ID_VALE', $id_vale)
            ->first();
        return $payment;
    }

    public static function getPaymentsToSmallBox($id_voucher, $text_search)
    {
        $text_search = str_replace("'", "", $text_search);

        $sql = "SELECT A.ID_PAGO, B.ID_DEPOSITO, TO_CHAR(A.FECHA_REG,'DD/MM/YYYY') AS FECHA,
                    DECODE(B.ID_MEDIOPAGO,'001','TLC','007','CHQ','008','EFEC') AS MEDIO_PAGO,
                    B.IMPORTE,B.IMPORTE_ME, B.CUENTA_CORRIENTE,
                    NVL((SELECT X.NUMERO FROM CAJA_CHEQUERA X 
                        WHERE X.ID_CHEQUERA = A.ID_CHEQUERA),A.NUMERO) AS NUMERO_CHEQUE

                FROM CAJA_PAGO A 
                INNER JOIN CAJA_DEPOSITO B ON A.ID_PAGO=B.ID_PAGO
                LEFT JOIN CAJA_CUENTA_BANCARIA B ON A.ID_CTABANCARIA = B.ID_CTABANCARIA
                WHERE A.ID_VOUCHER=$id_voucher
                AND B.ID_VOUCHER=$id_voucher
                AND (UPPER(A.NUMERO) LIKE UPPER('%$text_search%') 
                OR UPPER(B.GLOSA) LIKE UPPER('%$text_search%')
                OR UPPER(B.NUMERO) LIKE UPPER('%$text_search%')
                OR UPPER(B.IMPORTE) LIKE UPPER('%$text_search%')
        )
        ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getPaymentsToSmallBoxIngresos($id_entidad, $id_depto)
    {

        $sql = "SELECT  SUM(NVL(IMPORTE, 0)) as IMPORTE, SUM(NVL(IMPORTE_ME,0)) AS IMPORTE_ME FROM CAJA_DEPOSITO 
        where ID_ENTIDAD=$id_entidad and ID_DEPTO= '$id_depto'
        AND ID_PAGO IS NOT NULL
        AND ESTADO='1'
        ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getPaymentsToSmallBoxEgresos($id_entidad, $id_depto)
    {

        $sql = "SELECT SUM(IMPORTE) as IMPORTE, SUM(IMPORTE_ME) AS IMPORTE_ME FROM
            (
            SELECT SUM(NVL(IMPORTE, 0)) as IMPORTE, SUM(NVL(IMPORTE_ME,0)) AS IMPORTE_ME FROM CAJA_VALE 
            WHERE ID_ENTIDAD=$id_entidad
            and ID_DEPTO= '$id_depto'
            AND ID_MEDIOPAGO='008'
            AND ESTADO='1'
            UNION ALL
            SELECT SUM(NVL(IMPORTE, 0)) as IMPORTE, SUM(NVL(IMPORTE_ME,0)) AS IMPORTE_ME FROM CAJA_PAGO 
            WHERE ID_ENTIDAD=$id_entidad
            and ID_DEPTO= '$id_depto'
            AND ID_MEDIOPAGO='008'
            AND ESTADO='1'
            ) X
            
        ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsVoucherSum($id_mediopago, $id_voucher)
    {
        $sql = "SELECT 
                        A.ID_MEDIOPAGO,C.NOMBRE,B.ID_CUENTAAASI,B.CUENTA_CORRIENTE, SUM(A.IMPORTE) IMPORTE
                FROM CAJA_PAGO A, CAJA_CUENTA_BANCARIA B, CAJA_ENTIDAD_FINANCIERA C
                WHERE A.ID_CTABANCARIA = B.ID_CTABANCARIA
                AND B.ID_BANCO = C.ID_BANCO
                AND A.ID_MEDIOPAGO = '" . $id_mediopago . "'
                AND A.ID_VOUCHER = " . $id_voucher . " 
                AND A.ESTADO = '1'
                GROUP BY A.ID_MEDIOPAGO,C.NOMBRE,B.ID_CUENTAAASI,B.CUENTA_CORRIENTE ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsVoucher($id_mediopago, $id_voucher)
    {
        $sql = "SELECT 
                        A.ID_PAGO,A.ID_MEDIOPAGO,C.NOMBRE,B.ID_CUENTAAASI,B.CUENTA_CORRIENTE, A.IMPORTE,
                        TO_CHAR(A.FECHA_REG,'DD/MM/YYYY') AS FECHA
                FROM CAJA_PAGO A LEFT JOIN CAJA_CUENTA_BANCARIA B
                ON A.ID_CTABANCARIA = B.ID_CTABANCARIA
                LEFT JOIN CAJA_ENTIDAD_FINANCIERA C
                ON B.ID_BANCO = C.ID_BANCO
                WHERE A.ID_MEDIOPAGO = '" . $id_mediopago . "'
                AND A.ID_VOUCHER = " . $id_voucher . " 
                AND A.ESTADO = '1' 
                UNION ALL
                SELECT 
                        A.ID_VALE,A.ID_MEDIOPAGO,C.NOMBRE,B.ID_CUENTAAASI,B.CUENTA_CORRIENTE, A.IMPORTE,
                        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA
                FROM CAJA_VALE A LEFT JOIN CAJA_CUENTA_BANCARIA B
                ON A.ID_CTABANCARIA = B.ID_CTABANCARIA
                LEFT JOIN CAJA_ENTIDAD_FINANCIERA C
                ON B.ID_BANCO = C.ID_BANCO
                LEFT JOIN PROCESS_RUN D
                ON D.ID_OPERACION = A.ID_VALE
                AND ID_PROCESO = 10
                WHERE A.ID_MEDIOPAGO = '" . $id_mediopago . "'
                AND A.ID_VOUCHER = " . $id_voucher . " 
                AND A.ESTADO = '1' 
                AND FC_GET_LLAVE_COMPONENTE(D.ID_REGISTRO,D.ID_PASO_ACTUAL,0) = 'FVPR' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsVoucherToVales($id_voucher)
    {
        $sql = "SELECT 
                    A.ID_PAGO,
                    A.ID_MEDIOPAGO,
                    B.NUMERO AS VALE_NUMERO,
                    B.DETALLE AS VALE_DETALLE,
                    TO_CHAR(B.FECHA,'DD/MM/YYYY') AS VALE_FECHA, 
                    FC_NOMBRE_CLIENTE(B.ID_EMPLEADO) AS VALE_NOMBRE_EMPLEADO,
                    B.IMPORTE AS VALE_IMPORTE,
                    A.IMPORTE
                    B.TERM_COND_URL
                FROM CAJA_PAGO A
                INNER JOIN CAJA_VALE B ON A.ID_VALE=B.ID_VALE
                WHERE
                A.ID_VOUCHER = $id_voucher
                AND A.ID_VALE IS NOT NULL
                AND A.ESTADO = '1' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listPaymentsDetailsVoucher($id_pago)
    {
        $sql = "SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'G'||B.ID_PGASTO AS ID_DETALLE,B.ID_DINAMICA,B.ID_PERSONA,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                        B.FECHA,B.DETALLE,B.IMPORTE,B.IMPORTE_ME, '' as NOMBRE_PROVEEDOR, '' as RUC_PROVEEDOR
                FROM CAJA_PAGO A, CAJA_PAGO_GASTO B 
                WHERE A.ID_PAGO = B.ID_PAGO
                AND A.ID_PAGO = " . $id_pago . "
                AND A.ESTADO = '1'
                UNION ALL
                SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PCOMPRA AS ID_DETALLE,B.ID_DINAMICA,B.ID_PROVEEDOR,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                        A.FECHA, 
                        --C.ID_ENTIDAD || '-' || C.CORRELATIVO || ' PAGO PROVEEDORES' AS DETALLE,
                        B.DETALLE,
                        B.IMPORTE,B.IMPORTE_ME,
                        FC_NOMBRE_PERSONA(B.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(B.ID_PROVEEDOR) AS RUC_PROVEEDOR
                FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B, COMPRA C
                WHERE A.ID_PAGO = B.ID_PAGO
                AND B.ID_COMPRA = C.ID_COMPRA
                AND A.ID_PAGO = " . $id_pago . "
                AND A.ESTADO = '1' 
                UNION ALL
                SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_VALE,'X'||A.ID_VALE AS ID_DETALLE,A.ID_DINAMICA,A.ID_EMPLEADO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                        A.FECHA, 
                        A.DETALLE,
                        A.IMPORTE,A.IMPORTE_ME,
                        FC_NOMBRE_PERSONA(A.ID_EMPLEADO) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(A.ID_EMPLEADO) AS RUC_PROVEEDOR
                FROM CAJA_VALE A
                WHERE A.ID_VALE = " . $id_pago . "
                AND A.ESTADO = '1'
                
                UNION ALL
                SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'D'||B.ID_PCOMPRA AS ID_DETALLE,B.ID_DINAMICA,B.ID_PROVEEDOR,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                        A.FECHA, 
                        --C.ID_ENTIDAD || '-' || C.CORRELATIVO || ' PAGO PROVEEDORES' AS DETALLE,
                        B.DETALLE,
                        B.IMPORTE,B.IMPORTE_ME,
                        FC_NOMBRE_PERSONA(B.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(B.ID_PROVEEDOR) AS RUC_PROVEEDOR
                FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B, COMPRA_SALDO C
                WHERE A.ID_PAGO = B.ID_PAGO
                AND B.ID_SALDO = C.ID_SALDO
                AND A.ID_PAGO = " . $id_pago . "
                AND A.ESTADO = '1' 
                UNION ALL
                SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_PAGO,'V'||B.ID_PVENTA AS ID_DETALLE,B.ID_DINAMICA,B.ID_CLIENTE,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                        A.FECHA, 
                        B.DETALLE,
                        B.IMPORTE,B.IMPORTE_ME,
                        FC_NOMBRE_PERSONA(B.ID_CLIENTE) as NOMBRE_PROVEEDOR, DAVID.FT_CODIGO_UNIV(B.ID_CLIENTE) AS RUC_PROVEEDOR
                FROM CAJA_PAGO A, CAJA_PAGO_VENTA B
                WHERE A.ID_PAGO = B.ID_PAGO
                AND A.ID_PAGO = " . $id_pago . "
                AND A.ESTADO = '1' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listTypeVale()
    {
        $sql = DB::table('CAJA_TIPOVALE')->select('ID_TIPOVALE', 'NOMBRE', 'REQUIRE_FILE')->where("ESTADO", '1')->orderBy('ID_TIPOVALE')->get();
        return $sql;
    }

    public static function verPaso($id_paso)
    {
        $sql = "SELECT id_proceso,orden 
                FROM PROCESS_PASO
                WHERE ID_PASO = " . $id_paso . " ";
        $query = DB::select($sql);
        foreach ($query as $item) {
            $id_proceso = $item->id_proceso;
            $orden = $item->orden;
        }
        $sql = "SELECT ID_PROCESO,ID_PASO FROM PROCESS_PASO
                WHERE ID_PROCESO = " . $id_proceso . "
                AND ORDEN IN (
                SELECT MAX(ORDEN) 
                FROM PROCESS_PASO
                WHERE ID_PROCESO = " . $id_proceso . "
                AND ORDEN < " . $orden . "
                ) ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listVale($id_entidad, $id_depto, $id_anho, $id_proceso, $id_paso)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_DINAMICA,A.ID_PERSONA,
                A.ID_EMPLEADO,B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES,
                A.ID_MEDIOPAGO, C.NOMBRE,
                A.ID_MONEDA,A.FECHA,A.FECHA_VENCIMIENTO,A.IMPORTE,A.IMPORTE_ME,A.TIPO_CAMBIO,
                A.DETALLE,A.CTA_BANCARIA,A.CELULAR,A.EMAIL,A.ESTADO,A.TERM_COND_URL
                FROM CAJA_VALE A 
                LEFT JOIN MOISES.PERSONA B
                ON A.ID_EMPLEADO = B.ID_PERSONA
                LEFT JOIN MEDIO_PAGO C
                ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . " 
                AND A.ID_VALE IN (
                SELECT X.ID_OPERACION FROM PROCESS_RUN X, PROCESS_PASO_RUN Y, PROCESS_PASO Z
                WHERE X.ID_REGISTRO = Y.ID_REGISTRO
                AND Y.ID_PASO = Z.ID_PASO
                AND X.ID_PROCESO = " . $id_proceso . "
                AND Y.ID_PASO = " . $id_paso . "
                AND Y.ID_REGISTRO NOT IN (SELECT A.ID_REGISTRO FROM PROCESS_PASO_RUN A, PROCESS_PASO B WHERE A.ID_PASO = B.ID_PASO AND B.ORDEN > Z.ORDEN )
                ) ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listMyVale($params)
    {


        $query = DB::table('CAJA_VALE')
            ->select(
                "CAJA_VALE.ID_VALE",
                "CAJA_TIPOVALE.NOMBRE AS TIPO_VALE",
                "PROCESS_COMPONENTE.LLAVE",
                "CAJA_VALE.ID_TIPOVALE",
                "CAJA_VALE.IMPORTE",
                "CAJA_VALE.IMPORTE_ME",
                "PROCESS_RUN.ID_PASO_ACTUAL",
                "CAJA_VALE.NUMERO",
                "CAJA_VALE.ID_MEDIOPAGO",
                "CAJA_VALE.PAGADO",
                "CAJA_VALE.FECHA_VENCIMIENTO",
                "CAJA_VALE.FECHA",
                "CAJA_VALE.FECHA_REG",
                "CAJA_VALE.NRO_VALE",
                "PROCESS_RUN.ESTADO",
                "CAJA_VALE.ID_PERSONA",
                "CAJA_VALE.CELULAR",
                "CAJA_VALE.EMAIL",
                "CAJA_VALE.TERM_COND_URL",
                "CAJA_VALE.ID_MONEDA",
                DB::raw("PKG_CAJA.FC_VALE_COMPROBANTE(CAJA_VALE.ID_VALE) AS IMP_COMP"),
                DB::raw("PKG_CAJA.FC_VALE_GASTO(CAJA_VALE.ID_VALE) AS IMP_GASTO"),
                DB::raw("(SELECT SUM(PC.IMPORTE_ME) FROM ELISEO.PEDIDO_COMPRA PC WHERE PC.ID_VALE = CAJA_VALE.ID_VALE) AS IMP_COMP_ME"),
                DB::raw("(SELECT SUM(C2.IMPORTE_ME) FROM ELISEO.CAJA_VALE_GASTO C2 WHERE C2.ID_VALE = CAJA_VALE.ID_VALE AND C2.AUTORIZADO <> 'R') as IMP_GASTO_ME"),
                DB::raw("(SELECT PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO FROM MOISES.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = CAJA_VALE.ID_PERSONA_AUTO) AS AUTORIZACION"),
                DB::raw("(SELECT COUNT(CAJA_VALE_FILE.ID_VFILE) FROM CAJA_VALE_FILE WHERE CAJA_VALE_FILE.ID_VALE = CAJA_VALE.ID_VALE) AS CANT_FILES"),
                DB::raw("
                (PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO) AS RESPONSABLE
                --, CAJA_VALE.FECHA
                -- , MEDIO_PAGO.NOMBRE AS MEDIO_PAGO_NOMBRE
                ,CASE 
                  WHEN PROCESS_RUN.ESTADO = '0'
                    THEN 
                    (CASE PROCESS_COMPONENTE.LLAVE
                   WHEN 'FVRE'
                     THEN 'Registrado'
                   WHEN 'FVAU'
                     THEN 'Autorizado'
                   WHEN 'FVPA'
                     THEN 'Pagado'
                   WHEN 'FVPR'
                     THEN 'Provisionado'
                   WHEN 'FVRV'
                     THEN 'Rendido'
                   ELSE 
                        'N/I' END)
                  WHEN PROCESS_RUN.ESTADO = '1'
                    THEN 'Finalizado'
                  WHEN PROCESS_RUN.ESTADO = '3'
                    THEN 'Rechazado'
                  ELSE 'N/I' end as proceso
                ,CASE 
                  WHEN PROCESS_RUN.ESTADO = '1'
                    THEN 100
                  ELSE
                    CASE PROCESS_COMPONENTE.LLAVE
                      WHEN 'FVRE'
                        THEN 25
                      WHEN 'FVAU'
                        THEN 50
                      WHEN 'FVPA'
                        THEN 60
                      WHEN 'FVPR'
                        THEN 75
                      WHEN 'FVRV' 
                        THEN 80
                      ELSE 0  END 
                   END
                   AS PROGRESO,
          (CASE WHEN trunc(sysdate) BETWEEN trunc(CAJA_VALE.FECHA_VENCIMIENTO) - 3 AND trunc(CAJA_VALE.FECHA_VENCIMIENTO)
              THEN '0'
                   ELSE
                     CASE WHEN trunc(sysdate) > trunc(CAJA_VALE.FECHA_VENCIMIENTO)
                       THEN '1'
                     ELSE '0' END
                   END)              expired")
            )
            ->join("MOISES.PERSONA", "CAJA_VALE.ID_EMPLEADO", "=", "MOISES.PERSONA.ID_PERSONA")
            ->join("MOISES.PERSONA_NATURAL pn", "pn.ID_PERSONA", "=", "MOISES.PERSONA.ID_PERSONA")
            ->join("CAJA_TIPOVALE", "CAJA_VALE.ID_TIPOVALE", "CAJA_TIPOVALE.ID_TIPOVALE")
            ->join("PROCESS_RUN", "CAJA_VALE.ID_VALE", "=", "PROCESS_RUN.ID_OPERACION")
            ->join("PROCESS", "PROCESS_RUN.ID_PROCESO", "=", "PROCESS.ID_PROCESO")
            ->join("PROCESS_COMPONENTE_PASO", "PROCESS_COMPONENTE_PASO.ID_PASO", "=", "PROCESS_RUN.ID_PASO_ACTUAL")
            ->join("PROCESS_COMPONENTE", "PROCESS_COMPONENTE_PASO.ID_COMPONENTE", "=", "PROCESS_COMPONENTE.ID_COMPONENTE")
            ->where("CAJA_VALE.ID_ENTIDAD", $params->id_entidad)
            //->where("CAJA_VALE.ID_PERSONA", $params->id_user)
            ->where("CAJA_VALE.ID_DEPTO", $params->id_depto)
            ->where("PROCESS.CODIGO", $params->codigo);
        $q = $params->q;

        if (!empty($q)) {
            $query->whereRaw("(upper(replace(concat(concat(MOISES.PERSONA.nombre,MOISES.PERSONA.paterno),MOISES.PERSONA.materno),' ','')) like upper(replace('%" . $q . "%',' ',''))
        or upper(replace(concat(concat(regexp_substr(MOISES.PERSONA.nombre ,'[^ ]+',1,1),MOISES.PERSONA.paterno),MOISES.PERSONA.materno),' ','')) like upper(replace('%" . $q . "%',' ',''))
        or upper(replace(concat(concat(regexp_substr(MOISES.PERSONA.nombre ,'[^ ]+',1,2),MOISES.PERSONA.paterno),MOISES.PERSONA.materno),' ','')) like upper(replace('%" . $q . "%',' ',''))
        or (pn.num_documento like '%" . $q . "%'))");
        }

        //dd($params->llave);
        if (!empty($params->llave)) {
            if ($params->llave[0] == 'FIN') {
                $query->where("PROCESS_RUN.ESTADO", '1');
            } else if ($params->llave[0] == 'DENY') {
                $query->where("PROCESS_RUN.ESTADO", '3');
            } else {
                $query->whereIn("PROCESS_COMPONENTE.LLAVE", $params->llave);
                $query->where("PROCESS_RUN.ESTADO", '0');
            }
        }
        if ($params->month) {
            $query->where("CAJA_VALE.ID_MES", $params->month);
        }
        if ($params->id_user) {
            $query->where("CAJA_VALE.ID_PERSONA", $params->id_user);
        }
        if ($params->year) {
            $query->where("CAJA_VALE.ID_ANHO", $params->year);
        }

        if (!empty($params->mediospago)) {
            $query->whereIn("CAJA_VALE.ID_MEDIOPAGO", $params->mediospago);
        }

        if (!empty($params->voucher)) {
            $query->where("CAJA_VALE.NRO_VALE", $params->voucher);
        }

        if (!empty($params->state)) {

            $query->when($params->state == 1, function ($q) {
                $q->whereRaw('NVL(TO_NUMBER(CAJA_VALE.IMPORTE), 0) - (NVL(TO_NUMBER(PKG_CAJA.FC_VALE_COMPROBANTE(CAJA_VALE.ID_VALE)), 0) + NVL(TO_NUMBER(PKG_CAJA.FC_VALE_GASTO(CAJA_VALE.ID_VALE)), 0)) > 0');
            });
            $query->when($params->state == 2, function ($q) {
                $q->whereRaw('NVL(TO_NUMBER(CAJA_VALE.IMPORTE), 0) - (NVL(TO_NUMBER(PKG_CAJA.FC_VALE_COMPROBANTE(CAJA_VALE.ID_VALE)), 0) + NVL(TO_NUMBER(PKG_CAJA.FC_VALE_GASTO(CAJA_VALE.ID_VALE)), 0)) <= 0');
            });
        }


        /*crear paginacion para esta seccion*/
        $query = $query
            ->orderBy("CAJA_VALE.ID_VALE", "DESC")
            ->paginate((int)$params->page_size);
        return $query;
    }

    public static function listValesClientes($id_entidad, $id_depto)
    {
        $sql = "SELECT A.ID_VALE,B.ID_VFILE,A.ID_EMPLEADO AS ID_PERSONA,
                FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS EMPLEADO,
                A.NRO_VALE, A.FECHA, A.IMPORTE, A.IMPORTE_ME, B.NOMBRE, B.TIPO, B.URL, B.FORMATO,A.TERM_COND_URL
                FROM CAJA_VALE A 
                JOIN CAJA_VALE_FILE B
                ON A.ID_VALE = B.ID_VALE
                WHERE A.ID_ENTIDAD = " . $id_entidad . " 
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND B.TIPO = '4' 
                AND B.ID_DEPOSITO IS NULL";
        $query = DB::select($sql);
        return $query;
    }

    public static function listValesCliente($params)
    {
        /*$query = DB::table('VW_VALES_MOV')
            ->select(
                "VW_VALES_MOV.*"
            )
            ->join("VW_VALES_SALDO", "VW_VALES_MOV.ID_VALE", "=", "VW_VALES_SALDO.ID_VALE")
            ->where("VW_VALES_MOV.ID_EMPLEADO", $params->id_persona)
            ->where("VW_VALES_SALDO.IMPORTE", ">", "0")->orderBy("VW_VALES_MOV.ID_VALE", "DESC");
        $query = $query
            ->paginate((int)$params->page_size);*/
        $sql = "SELECT A.ID_VALE,A.NRO_VALE,SUM(A.IMPORTE) AS IMPORTE,SUM(A.IMPORTE_ME) AS IMPORTE_ME,
                     (SELECT TO_CHAR(X.FECHA,'DD/MM/YYYY') FROM CAJA_VALE X WHERE X.ID_VALE = A.ID_VALE) AS FECHA
                FROM VW_VALES_MOV A 
                WHERE A.ID_ENTIDAD = $params->id_entidad 
                AND A.ID_DEPTO = $params->id_depto
                -- AND ID_ANHO = 2021
                AND A.ID_EMPLEADO = $params->id_persona 
                AND A.ID_VOUCHER IS NOT NULL
                HAVING SUM(A.IMPORTE)+NVL(SUM(A.IMPORTE_ME),0) <> 0
                GROUP BY A.ID_VALE,A.NRO_VALE
                ORDER BY NRO_VALE ASC  ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listValeFiles($idVale)
    {
        $sql = "SELECT A.ID_VALE,B.ID_VFILE, A.NRO_VALE, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA, 
                A.IMPORTE, A.IMPORTE_ME, B.NOMBRE, B.URL, B.FORMATO, B.ID_DEPOSITO 
                FROM CAJA_VALE A 
                JOIN CAJA_VALE_FILE B
                ON A.ID_VALE = B.ID_VALE
                WHERE B.TIPO = '4' 
                AND A.ID_VALE = $idVale
                AND B.ID_DEPOSITO IS NULL";
        $query = DB::select($sql);
        return $query;
    }

    public static function updateMyVales($IdVale, $data)
    {
//        dd('savinf', $IdVale, $data);
//        $query = RB::table("CAJA_VALE")->where("ID_VALE", $IdVale)->update($data);
        $query = DB::table('CAJA_VALE')
            ->where('ID_VALE', $IdVale)
            ->update($data);
        if ($query) {
            $vquery = ExpensesData::showVale($IdVale, null);
        }
        return $vquery;
    }

    public static function editVoucherAccountingEntries($idAccountingSeat, $data)
    {
        $query = DB::table('CAJA_VALE_ASIENTO')
            ->where('ID_ASIENTO', $idAccountingSeat)
            ->update($data);
        if ($query) {
            $vquery = ExpensesData::showVoucherAccountingEntries($idAccountingSeat, '');
        }
        return $vquery;
    }

    public static function showVoucherAccountingEntries($idAccountingSeat, $id_entidad)
    {
        // $query = DB::table('CAJA_VALE_ASIENTO')
        //     ->where('ID_ASIENTO', $idAccountingSeat)
        //     ->first();
        // return $query;
        $sql = "SELECT 
                ID_ASIENTO,
                ID_VALE,
                ID_CUENTAAASI,
                FC_CUENTA(ID_CUENTAAASI) AS CUENTA_ASSI,
                ID_RESTRICCION,
                ID_CTACTE,
                FC_NAMECUENTA(ID_CUENTAAASI,ID_CTACTE, '" . $id_entidad . "') CTACTE_NOMBRE,
                ID_FONDO,
                ID_DEPTO,
                FC_NAMESDEPTO('" . $id_entidad . "',ID_DEPTO) DEPTO_NOMBRE,
                IMPORTE,
                IMPORTE_ME,
                DESCRIPCION,
                DC,
                AGRUPA,
                NRO_ASIENTO 
                FROM CAJA_VALE_ASIENTO
                WHERE ID_ASIENTO = " . $idAccountingSeat . "
                ORDER BY IMPORTE DESC ";
        // dd($sql);
        $query = DB::select($sql);
        return $query;
    }

    public static function deleteVoucherAccountingEntries($idAccountingEntry)
    {
        $query = DB::table('CAJA_VALE_ASIENTO')
            ->where('ID_ASIENTO', $idAccountingEntry)
            ->delete();
        return $query;
    }

    public static function dublicateVoucherAccountingEntries($data)
    {
        $accountEntry = DB::table('CAJA_VALE_ASIENTO')
            ->where('ID_ASIENTO', $data['id_asiento'])
            ->first();
        unset($accountEntry->id_asiento);
        $query = DB::table("CAJA_VALE_ASIENTO")->insert(get_object_vars($accountEntry));
        if ($query) {
            $result = self::showVoucherAccountingEntries($data['id_asiento'], '');
        } else {
            $result = null;
        }
        return $result;
    }

    public static function listValeProceso($id_entidad, $id_depto, $id_anho, $id_proceso, $id_paso)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_DINAMICA,A.ID_PERSONA,
                A.ID_EMPLEADO,B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES,
                A.ID_MEDIOPAGO, C.NOMBRE,
                A.ID_MONEDA,A.FECHA,A.FECHA_VENCIMIENTO,A.IMPORTE,A.IMPORTE_ME,A.TIPO_CAMBIO,
                A.DETALLE,A.CTA_BANCARIA,A.CELULAR,A.EMAIL,A.ESTADO,A.TERM_COND_URL
                FROM CAJA_VALE A 
                LEFT JOIN MOISES.PERSONA B
                ON A.ID_EMPLEADO = B.ID_PERSONA
                LEFT JOIN MEDIO_PAGO C
                ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . " 
                AND A.ID_VALE IN (
                SELECT X.ID_OPERACION FROM PROCESS_RUN X, PROCESS_PASO_RUN Y
                WHERE X.ID_REGISTRO = Y.ID_REGISTRO
                AND X.ID_PROCESO = " . $id_proceso . "
                AND Y.ID_PASO = " . $id_paso . "
                ) ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listValeByIdvoucher($id_voucher, $text_search)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,
        A.ID_EMPLEADO,
        FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS NOMBRE_EMPLEADO,
        A.ID_MEDIOPAGO,
        A.NRO_VALE,
        C.NOMBRE,
        D.NOMBRE AS TIPOVALE_NOMBRE,
        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
        A.IMPORTE,A.IMPORTE_ME, A.TERM_COND_URL
        FROM CAJA_VALE A 
        LEFT JOIN MOISES.PERSONA B
        ON A.ID_EMPLEADO = B.ID_PERSONA
        LEFT JOIN MEDIO_PAGO C
        ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
        LEFT JOIN CAJA_TIPOVALE D
        ON A.ID_TIPOVALE = D.ID_TIPOVALE
        WHERE A.ID_VOUCHER = $id_voucher
        AND (UPPER(A.IMPORTE) LIKE UPPER('%$text_search%')
        OR UPPER(coalesce(B.NOMBRE,'') || coalesce(B.PATERNO,'') || coalesce(B.MATERNO,'')) LIKE  replace(UPPER('%$text_search%'), ' ', '%'))
        ORDER BY ID_VALE DESC";
        $query = DB::select($sql);
        return $query;
    }


    public static function listSaldoValeByIdvoucher($id_entidad, $id_depto, $id_user, $id_voucher, $text_search)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,
        A.ID_EMPLEADO,
        FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS NOMBRE_EMPLEADO,
        A.ID_MEDIOPAGO,
        A.NRO_VALE,
        C.NOMBRE,
        D.NOMBRE AS TIPOVALE_NOMBRE,
        TO_CHAR(E.FECHA,'DD/MM/YYYY') AS FECHA,
        A.IMPORTE,A.IMPORTE_ME
        FROM VW_VALES_SALDO A 
        LEFT JOIN MOISES.PERSONA B ON A.ID_EMPLEADO = B.ID_PERSONA
        LEFT JOIN MEDIO_PAGO C
        ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
        LEFT JOIN CAJA_TIPOVALE D
        ON A.ID_TIPOVALE = D.ID_TIPOVALE
        INNER JOIN CAJA_VALE E ON A.ID_VALE=E.ID_VALE
        WHERE 
        A.ID_ENTIDAD = $id_entidad
        AND A.ID_DEPTO = '$id_depto'
        AND (E.ID_PERSONA = $id_user OR E.ID_VOUCHER = $id_voucher)
        AND (A.IMPORTE+NVL(A.IMPORTE_ME,0)) <> 0
        AND (UPPER(A.IMPORTE) LIKE UPPER('%$text_search%')
        OR UPPER(coalesce(B.NOMBRE,'') || coalesce(B.PATERNO,'') || coalesce(B.MATERNO,'')) LIKE  replace(UPPER('%$text_search%'), ' ', '%'))
        ORDER BY ID_VALE DESC";
        $query = DB::select($sql);
        // Listará los vales que el usuario a emitido en cualquier comento 
        // Y los vales de los vouchers que tiene asignado.
        return $query;
    }

    public static function listValeARendir($id_entidad, $id_depto, $text_search)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_DINAMICA,A.ID_PERSONA,
                A.ID_EMPLEADO,
                FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS NOMBRE_EMPLEADO,
                B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES,
                A.ID_MEDIOPAGO, C.NOMBRE,
                A.ID_MONEDA,
                
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(A.FECHA_VENCIMIENTO,'DD/MM/YYYY') AS FECHA_VENCIMIENTO,
                D.NOMBRE AS TIPOVALE_NOMBRE,
                
                A.IMPORTE,A.IMPORTE_ME,A.TIPO_CAMBIO,
                A.DETALLE,A.CTA_BANCARIA,A.CELULAR,A.EMAIL,A.ESTADO,A.TERM_COND_URL
                FROM CAJA_VALE A 
                LEFT JOIN MOISES.PERSONA B
                ON A.ID_EMPLEADO = B.ID_PERSONA
                LEFT JOIN MEDIO_PAGO C
                ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
                LEFT JOIN CAJA_TIPOVALE D
                ON A.ID_TIPOVALE = D.ID_TIPOVALE
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = $id_depto
                AND A.ESTADO = 1
                AND A.ID_TIPOVALE = 1
                AND (UPPER(A.IMPORTE) LIKE UPPER('%$text_search%')
                OR UPPER(coalesce(B.NOMBRE,'') || coalesce(B.PATERNO,'') || coalesce(B.MATERNO,'') || coalesce (A.DETALLE,'' ))  LIKE  UPPER(replace('%$text_search%', ' ', '%')))
                ORDER BY ID_VALE DESC
                ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getValeById($id_vale)
    {
        $sql = "SELECT A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_DINAMICA,A.ID_PERSONA,
                A.ID_EMPLEADO,
                FC_NOMBRE_CLIENTE(A.ID_EMPLEADO) AS NOMBRE_EMPLEADO,
                A.ID_EMPLEADO AS ID_EMPLEADO,
                A.ID_MEDIOPAGO,
                A.ID_MONEDA,
                A.ID_TIPOVALE,
                A.ID_CHEQUERA,
                A.ID_CTABANCARIA,
                A.NUMERO,
                A.NRO_VALE,
                A.TERM_COND_URL,
                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(A.FECHA_VENCIMIENTO,'DD/MM/YYYY') AS FECHA_VENCIMIENTO,
                
                A.IMPORTE,A.IMPORTE_ME,A.TIPO_CAMBIO,
                NVL(B.IMPORTE,0) AS SALDO_IMPORTE,
                NVL(B.IMPORTE_ME,0) AS SALDO_IMPORTE_ME,
                A.DETALLE,A.CTA_BANCARIA,A.CELULAR,A.EMAIL,A.ESTADO,
                (
                select caja_vale_asiento.id_cuentaaasi from caja_vale_asiento where caja_vale_asiento.id_vale=a.id_vale and caja_vale_asiento.id_ctacte IN(
                select NUM_DOCUMENTO FROM MOISES.VW_PERSONA_NATURAL_TRABAJADOR WHERE MOISES.VW_PERSONA_NATURAL_TRABAJADOR.ID_PERSONA=A.ID_EMPLEADO) AND rownum = 1
                ) as id_cuentaaasi
                FROM CAJA_VALE A 
                LEFT JOIN VW_VALES_SALDO B ON A.ID_VALE=B.ID_VALE 
                WHERE A.ID_VALE = $id_vale
                ";
        $query = DB::select($sql);
        return $query;
    }

    public static function deleteValeById($id_vale)
    {
        DB::table('CAJA_VALE')->where('ID_VALE', '=', $id_vale)->delete();
    }

    public static function deleteContaAsiento($id_tipoorigen, $id_origen)
    {
        DB::table('CONTA_ASIENTO')->where('ID_TIPOORIGEN', '=', $id_tipoorigen)->where('ID_ORIGEN', '=', $id_origen)->delete();
    }

    public static function deleteCajaDeposito($id_deposito)
    {
        DB::table('CAJA_DEPOSITO')->where('ID_DEPOSITO', '=', $id_deposito)->delete();
    }

    public static function deleteCajaValeAsiento($id_vale)
    {
        DB::table('CAJA_VALE_ASIENTO')->where('ID_VALE', '=', $id_vale)->delete();
    }

    public static function showVale($id_vale, $proceCode)
    {
        $userPersonAction = '';
        if ($proceCode) {
            $userPersonAction = ", (
                SELECT (SELECT (SELECT MP.NOMBRE || ' ' || MP.PATERNO || ' ' || MP.MATERNO
                FROM MOISES.PERSONA MP
                WHERE MP.ID_PERSONA = PROCESS_PASO_RUN.ID_PERSONA)
                            FROM PROCESS_PASO_RUN
                            WHERE PROCESS_PASO_RUN.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO AND
                                  PROCESS_PASO_RUN.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL)
                    FROM PROCESS_RUN
                    WHERE PROCESS_RUN.ID_OPERACION = A.ID_VALE
                          AND PROCESS_RUN.ID_PROCESO = (SELECT ID_PROCESO
                                                        FROM PROCESS
                                                        WHERE PROCESS.CODIGO = $proceCode AND PROCESS.ID_ENTIDAD = A.ID_ENTIDAD)
                                    ) AS PERSONA_ACCION
                                    , (
                SELECT (SELECT PROCESS_PASO_RUN.FECHA
                            FROM PROCESS_PASO_RUN
                            WHERE PROCESS_PASO_RUN.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO AND
                                  PROCESS_PASO_RUN.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL)
                    FROM PROCESS_RUN
                    WHERE PROCESS_RUN.ID_OPERACION = A.ID_VALE
                          AND PROCESS_RUN.ID_PROCESO = (SELECT ID_PROCESO
                                                        FROM PROCESS
                                                        WHERE PROCESS.CODIGO = $proceCode AND PROCESS.ID_ENTIDAD = A.ID_ENTIDAD)
                                    ) AS FECHA_ACCION";
        }
        $sql = "SELECT A.ID_CTABANCARIA, A.ID_VALE,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_TIPOVALE,A.ID_DINAMICA,A.ID_PERSONA,
                A.ID_EMPLEADO,B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS NOMBRES,                
                A.ID_MEDIOPAGO, C.NOMBRE,
                A.ID_MONEDA,A.FECHA,A.FECHA_VENCIMIENTO,A.IMPORTE,A.IMPORTE_ME,A.TIPO_CAMBIO,A.TERM_COND_URL,
                A.DETALLE,A.CTA_BANCARIA,A.CELULAR,A.EMAIL,A.ESTADO, A.NUMERO, A.TIPO_CAMBIO, A.ID_MONEDA,A.ID_CTABANCARIA, A.ID_CHEQUERA, A.ID_VOUCHER,
                (select ctv.NOMBRE from CAJA_TIPOVALE ctv where ctv.ID_TIPOVALE = A.ID_TIPOVALE)  as tipo_vale, A.pagado, A.ID_EMPLEADO, A.CTA_BANCARIA
                $userPersonAction
                FROM CAJA_VALE A 
                LEFT JOIN MOISES.PERSONA B
                ON A.ID_EMPLEADO = B.ID_PERSONA
                LEFT JOIN MEDIO_PAGO C
                ON A.ID_MEDIOPAGO = C.ID_MEDIOPAGO
                WHERE A.ID_VALE = $id_vale ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getVale($id_vale)
    {
        return DB::table('CAJA_VALE')->select('CAJA_VALE.*', DB::raw("
        CAJA_VALE.FECHA_VENCIMIENTO venc,
          (CASE WHEN trunc(sysdate) BETWEEN trunc(CAJA_VALE.FECHA_VENCIMIENTO) - 3 AND trunc(CAJA_VALE.FECHA_VENCIMIENTO)
              THEN '0'
                   ELSE
                     CASE WHEN trunc(sysdate) > trunc(CAJA_VALE.FECHA_VENCIMIENTO)
                       THEN '1'
                     ELSE '0' END
                   END)              expired
        "))->where('ID_VALE', '=', $id_vale)->get()->first();
    }

    public static function updateVale($IdVale, $data)
    {


        $vquery = null;
        $query = DB::table('CAJA_VALE')
            ->where('ID_VALE', $IdVale)
            ->update($data);
        if ($query) {
            $vquery = ExpensesData::getVale($IdVale);
        }
        return $vquery;
    }

    public static function procesoVale($id_vale)
    {
        $sql = "
            SELECT 
                CV.ID_VALE,
                CTV.NOMBRE AS TIPO_VALE,
                PC.LLAVE,
                CV.ID_TIPOVALE,
                CV.IMPORTE,
                CV.IMPORTE_ME,
                PR.ID_PASO_ACTUAL,
                CV.NUMERO,
                CV.ID_MEDIOPAGO,
                CV.PAGADO,
                CV.FECHA_VENCIMIENTO,
                CV.FECHA,
                CV.FECHA_REG,
                CV.NRO_VALE,
                PR.ESTADO,
                CV.ID_PERSONA,
                CV.CELULAR,
                CV.EMAIL,
                CV.TERM_COND_URL,
                CV.ID_MONEDA,
                PKG_CAJA.FC_VALE_COMPROBANTE(CV.ID_VALE) AS IMP_COMP,
                PKG_CAJA.FC_VALE_GASTO(CV.ID_VALE) AS IMP_GASTO,
                (SELECT SUM(PC.IMPORTE_ME) 
                FROM ELISEO.PEDIDO_COMPRA PC 
                WHERE PC.ID_VALE = CV.ID_VALE) AS IMP_COMP_ME,
                (SELECT SUM(C2.IMPORTE_ME) 
                FROM ELISEO.CAJA_VALE_GASTO C2 
                WHERE C2.ID_VALE = CV.ID_VALE 
                AND C2.AUTORIZADO <> 'R') AS IMP_GASTO_ME,
                (SELECT P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO 
                FROM MOISES.PERSONA P 
                WHERE P.ID_PERSONA = CV.ID_PERSONA_AUTO) AS AUTORIZACION,
                (SELECT COUNT(CVF.ID_VFILE) 
                FROM CAJA_VALE_FILE CVF 
                WHERE CVF.ID_VALE = CV.ID_VALE) AS CANT_FILES,
                P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO AS RESPONSABLE,
                CASE 
                    WHEN PR.ESTADO = '0' THEN 
                        CASE PC.LLAVE
                            WHEN 'FVRE' THEN 'Registrado'
                            WHEN 'FVAU' THEN 'Autorizado'
                            WHEN 'FVPA' THEN 'Pagado'
                            WHEN 'FVPR' THEN 'Provisionado'
                            WHEN 'FVRV' THEN 'Rendido'
                            ELSE 'N/I'
                        END
                    WHEN PR.ESTADO = '1' THEN 'Finalizado'
                    WHEN PR.ESTADO = '3' THEN 'Rechazado'
                    ELSE 'N/I'
                END AS PROCESO,
                CASE 
                    WHEN PR.ESTADO = '1' THEN 100
                    ELSE
                        CASE PC.LLAVE
                            WHEN 'FVRE' THEN 25
                            WHEN 'FVAU' THEN 50
                            WHEN 'FVPA' THEN 60
                            WHEN 'FVPR' THEN 75
                            WHEN 'FVRV' THEN 80
                            ELSE 0
                        END
                END AS PROGRESO,
                CASE 
                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(CV.FECHA_VENCIMIENTO) - 3 AND TRUNC(CV.FECHA_VENCIMIENTO)
                    THEN '0'
                    ELSE
                        CASE 
                            WHEN TRUNC(SYSDATE) > TRUNC(CV.FECHA_VENCIMIENTO) THEN '1'
                            ELSE '0'
                        END
                END AS EXPIRED
            FROM CAJA_VALE CV
            JOIN MOISES.PERSONA P ON CV.ID_EMPLEADO = P.ID_PERSONA
            JOIN MOISES.PERSONA_NATURAL PN ON PN.ID_PERSONA = P.ID_PERSONA
            JOIN CAJA_TIPOVALE CTV ON CV.ID_TIPOVALE = CTV.ID_TIPOVALE
            JOIN PROCESS_RUN PR ON CV.ID_VALE = PR.ID_OPERACION
            JOIN PROCESS PROC ON PR.ID_PROCESO = PROC.ID_PROCESO
            JOIN PROCESS_COMPONENTE_PASO PCP ON PCP.ID_PASO = PR.ID_PASO_ACTUAL
            JOIN PROCESS_COMPONENTE PC ON PCP.ID_COMPONENTE = PC.ID_COMPONENTE
            WHERE CV.ID_VALE = $id_vale 
        ";

        $query = DB::selectOne($sql);
        return $query;
    }

    public static function showValeState($id_vale, $proceCode)
    {
        $sql = "SELECT
                  --   D.ID_DETALLE,
                  B.NOMBRE                             AS paso_nombre,
                  C.ESTADO                             AS estado,
                  V.FECHA                              AS fecha_registro,
                  D.FECHA                              AS fecha_accion,
                  (SELECT PV.NOMBRE
                   FROM MOISES.PERSONA PV
                   WHERE PV.ID_PERSONA = V.ID_PERSONA) AS persona_registro,
                  (SELECT P.NOMBRE
                   FROM MOISES.PERSONA P
                   WHERE P.ID_PERSONA = D.ID_PERSONA)  AS persona_accion,
                  D.DETALLE,
                  (CASE WHEN c.ID_PASO_ACTUAL = D.ID_PASO
                    THEN '1'
                   ELSE '0' END)                       AS activo,
                  (CASE WHEN D.ID_DETALLE IS NOT NULL
                    THEN '1'
                   ELSE '0' END)                       AS terminado,
                (CASE
                     WHEN C.ESTADO = '3' and D.ID_DETALLE IS NULL
                         THEN '1'
                     ELSE '0' END)                   AS denied
                FROM PROCESS A
                  JOIN PROCESS_PASO B
                    ON A.ID_PROCESO = B.ID_PROCESO
                
                  LEFT JOIN PROCESS_RUN C
                    ON A.ID_PROCESO = C.ID_PROCESO
                  LEFT JOIN CAJA_VALE v ON C.ID_OPERACION = v.ID_VALE
                  LEFT JOIN PROCESS_PASO_RUN D
                    ON C.ID_REGISTRO = D.ID_REGISTRO
                       AND B.ID_PASO = D.ID_PASO
                WHERE C.ID_OPERACION = $id_vale
                      AND A.CODIGO = $proceCode
                      AND B.ID_TIPOPASO = 2
                ORDER BY B.ORDEN DESC";
        $query = DB::select($sql);
        return $query;
    }

    public static function listValesAccounting($id_vale, $id_entidad)
    {
        $sql = "SELECT 
                ID_ASIENTO,
                ID_VALE,
                ID_CUENTAAASI,
                FC_CUENTA(ID_CUENTAAASI) AS CUENTA_ASSI,
                ID_RESTRICCION,
                ID_CTACTE,
                FC_NAMECUENTA(ID_CUENTAAASI,ID_CTACTE, '" . $id_entidad . "') CTACTE_NOMBRE,
                ID_FONDO,
                ID_DEPTO,
                FC_NAMESDEPTO('" . $id_entidad . "',ID_DEPTO) DEPTO_NOMBRE,
                IMPORTE,
                IMPORTE_ME,
                DESCRIPCION,
                DC,
                AGRUPA,
                NRO_ASIENTO 
                FROM CAJA_VALE_ASIENTO
                WHERE ID_VALE = " . $id_vale . "
                ORDER BY IMPORTE DESC ";
        // dd($sql);
        $query = DB::select($sql);
        return $query;
    }

    public static function showCajaPago($id_pago)
    {
        $sql = "SELECT
                  CAJA_PAGO.ID_PAGO,
                  CAJA_PAGO.ID_MEDIOPAGO,
                  CAJA_PAGO.ID_ENTIDAD,
                  CAJA_PAGO.ID_DEPTO,
                  CAJA_PAGO.ID_CTABANCARIA,
                  CAJA_PAGO.ID_CHEQUERA,
                  CAJA_PAGO.ID_VOUCHER,
                  CAJA_PAGO.ID_ANHO,
                  CAJA_PAGO.ID_MES,
                  CAJA_PAGO.ID_USER,
                  CAJA_PAGO.ID_PERSONA,
                  CAJA_PAGO.ID_PROVEEDOR,
                  CAJA_PAGO.ID_TIPOTRANSACCION,
                  CAJA_PAGO.ID_MONEDA,
                  CAJA_PAGO.NUMERO,
                  CAJA_PAGO.FECHA_REG,
                  CAJA_PAGO.TIPOCAMBIO,
                  CAJA_PAGO.IMPORTE,
                  CAJA_PAGO.IMPORTE_ME,
                  CAJA_PAGO.ESTADO,
                  CAJA_PAGO.ID_VALE,
                  CAJA_CUENTA_BANCARIA.NOMBRE           AS                  CUENTA_BANCARIA,
                  CAJA_CUENTA_BANCARIA.CUENTA_CORRIENTE AS                  CUENTA_CORRIENTE,
                  (SELECT
                     CAJA_CHEQUERA.NUMERO || ' - ' || CAJA_CHEQUERA.IMPORTE
                   FROM CAJA_CHEQUERA
                   WHERE CAJA_CHEQUERA.ID_CHEQUERA = CAJA_PAGO.ID_CHEQUERA) CHEQUERA,
                  (SELECT CONTA_MONEDA.NOMBRE
                FROM CONTA_MONEDA
                WHERE CONTA_MONEDA.ID_MONEDA = CAJA_PAGO.ID_MONEDA AND CONTA_MONEDA.ESTADO = 1) AS MONEDA
                FROM CAJA_PAGO
                LEFT JOIN CAJA_CUENTA_BANCARIA ON CAJA_PAGO.ID_CTABANCARIA = CAJA_CUENTA_BANCARIA.ID_CTABANCARIA
                WHERE ID_PAGO = $id_pago";
        $query = DB::select($sql);
        return $query;
    }

    public static function listAccountingPlan($id_entidad, $id_depto, $id_persona, $id_tipovale)
    {
        $sql = "SELECT 
                A.ID_CUENTAAASI,A.ID_RESTRICCION,B.NOMBRE,A.ID_TIPOPLAN
                FROM CAJA_PERSONA_CTA A ,CONTA_CTA_DENOMINACIONAL B
                WHERE A.ID_CUENTAAASI = B.ID_CUENTAAASI
                AND A.ID_CUENTAAASI = B.ID_CUENTAAASI
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_PERSONA = " . $id_persona . "
                AND A.ID_TIPOVALE = " . $id_tipovale . "
                AND A.ID_CUENTAAASI IS NOT NULL ";
        $Query = DB::select($sql);
        /*if(count($Query) == 0){
            $Query = AccountingData::listPlanAccountingDenominational($id_tipoplan);
        }*/
        return $Query;
    }

    public static function listCurrentAccount($id_entidad, $id_depto, $id_persona)
    {
        $sql = "SELECT 
                A.ID_CTACTE,A.ID_TIPOCATE,B.NOMBRE
                FROM CAJA_PERSONA_CTA A, CONTA_ENTIDAD_CTA_CTE B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_CTACTE = B.ID_CTACTE
                AND A.ID_TIPOCATE = B.ID_TIPOCTACTE
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_PERSONA = " . $id_persona . "
                AND A.ID_CTACTE IS NOT NULL ";
        $Query = DB::select($sql);
        /*if(count($Query) == 0){
            $Query = AccountingData::listCtaCteAccounting($id_dinamica,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$nombre,$all);
        }*/
        return $Query;
    }

    public static function listDepartmentAccount($id_entidad, $id_depto, $id_persona)
    {
        $sql = "SELECT 
                A.ID_DEPARTAMENTO ID_DEPTO,B.NOMBRE
                FROM CAJA_PERSONA_CTA A, CONTA_ENTIDAD_DEPTO B
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPARTAMENTO = B.ID_DEPTO
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_PERSONA = " . $id_persona . "
                AND A.ID_DEPARTAMENTO IS NOT NULL ";
        $Query = DB::select($sql);
        /*if(count($Query) == 0){
            $Query = AccountingData::listCtaCteAccounting($id_dinamica,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$nombre,$all);
        }*/
        return $Query;
    }

    public static function listRetentionsPurchases($id_retencion)
    {
        $sql = "SELECT 
                        A.ID_RETDETALLE,
                        B.ID_COMPROBANTE,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA_DOC,'DD/MM/YYYY') AS FECHA,B.IMPORTE,
                        A.IMPORTE_RET,A.IMPORTE_RET_ME
                FROM CAJA_RETENCION_COMPRA A, COMPRA B
                WHERE A.ID_COMPRA = B.ID_COMPRA
                AND A.ID_RETENCION = " . $id_retencion . "
                    UNION ALL
                    SELECT 
                        A.ID_RETDETALLE,
                        B.ID_COMPROBANTE,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA_DOC,'DD/MM/YYYY') AS FECHA,B.IMPORTE,
                        A.IMPORTE_RET,A.IMPORTE_RET_ME
                FROM CAJA_RETENCION_COMPRA A, COMPRA_SALDO B
                WHERE A.ID_SALDO= B.ID_SALDO
                AND A.ID_RETENCION = " . $id_retencion . "";
        $query = DB::select($sql);
        return $query;
    }

    public static function deletePagosIfEstadoIsCero($id_entidad, $id_depto, $id_voucher, $id_persona)
    {
        $sql = "DELETE CAJA_PAGO_COMPRA CPC
                WHERE CPC.ID_PAGO IN (
                    SELECT ID_PAGO FROM
                    CAJA_PAGO A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '$id_depto'
                    AND A.ID_VOUCHER = $id_voucher
                    AND A.ID_PERSONA = $id_persona
                    AND A.ESTADO = '0'
                )
                ";
        $query = DB::delete($sql);

        $sql = "DELETE CAJA_PAGO_GASTO CPG
                WHERE CPG.ID_PAGO IN (
                    SELECT ID_PAGO FROM
                    CAJA_PAGO A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '$id_depto'
                    AND A.ID_VOUCHER = $id_voucher
                    AND A.ID_PERSONA = $id_persona
                    AND A.ESTADO = '0'
                )
                ";
        $query = DB::delete($sql);

        $sql = "DELETE CAJA_PAGO_VENTA CPV
                WHERE CPV.ID_PAGO IN (
                    SELECT ID_PAGO FROM
                    CAJA_PAGO A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '$id_depto'
                    AND A.ID_VOUCHER = $id_voucher
                    AND A.ID_PERSONA = $id_persona
                    AND A.ESTADO = '0'
                )
                ";
        $query = DB::delete($sql);

        $sql2 = "
                DELETE CAJA_PAGO CP
                WHERE CP.ID_ENTIDAD = $id_entidad
                AND CP.ID_DEPTO = '$id_depto'
                AND CP.ID_VOUCHER = $id_voucher
                AND CP.ID_PERSONA = $id_persona
                AND CP.ESTADO = '0'
                ";
        $query2 = DB::delete($sql2);
        return $query2;
    }

    public static function deleteRetencionIfEstadoIsCero($id_entidad, $id_depto, $id_voucher, $id_persona)
    {
        $sql = "DELETE CAJA_RETENCION_COMPRA CRC
                WHERE CRC.ID_RETENCION IN (
                    SELECT ID_RETENCION FROM
                    CAJA_RETENCION A
                    WHERE A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '$id_depto'
                    AND A.ID_VOUCHER = $id_voucher
                    AND A.ID_PERSONA = $id_persona
                    AND A.ESTADO = '0'
                )
                ";
        $query = DB::delete($sql);
        $sql2 = "
                DELETE CAJA_RETENCION CR
                WHERE CR.ID_ENTIDAD = $id_entidad
                AND CR.ID_DEPTO = '$id_depto'
                AND CR.ID_VOUCHER = $id_voucher
                AND CR.ID_PERSONA = $id_persona
                AND CR.ESTADO = '0'
                ";
        $query2 = DB::delete($sql2);
        return $query2;
    }

    public static function listRetentions($id_entidad, $id_depto, $id_voucher, $text_search)
    {
        $searchQuery = "";
        if ($text_search) {
            $searchQuery = "AND (UPPER(A.SERIE) LIKE UPPER('%$text_search%') OR UPPER(A.NRO_RETENCION) LIKE UPPER('%$text_search%')
                OR UPPER(B.ID_RUC) LIKE UPPER('%$text_search%') OR UPPER(B.NOMBRE) LIKE UPPER('%$text_search%'))
                ";
        }
        $sql = "SELECT  A.ID_RETENCION,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VOUCHER,
                        -- B.ID_RUC AS RUC,B.NOMBRE,
                        NVL(B.ID_RUC,PKG_CAJA.FC_RUC(A.ID_PROVEEDOR)) AS RUC,
                        NVL(B.NOMBRE,FC_NOMBRE_PERSONA(A.ID_PROVEEDOR)) AS NOMBRE,
                        A.SERIE,A.NRO_RETENCION,TO_CHAR(A.FECHA_EMISION,'DD/MM/YYYY') AS FECHA_EMISION,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE
                FROM CAJA_RETENCION A LEFT JOIN  MOISES.VW_PERSONA_JURIDICA B
                ON A.ID_PROVEEDOR = B.ID_PERSONA
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ESTADO = '1'
                $searchQuery
                ORDER BY A.SERIE,A.NRO_RETENCION,B.NOMBRE
                 ";

        $query = DB::select($sql);
        return $query;
    }

    public static function getRetentionById($id_retencion)
    {
        $sql = "SELECT  A.ID_RETENCION,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VOUCHER,
                        B.ID_RUC AS RUC,B.NOMBRE,
                        A.SERIE,A.NRO_RETENCION,TO_CHAR(A.FECHA_EMISION,'DD/MM/YYYY') AS FECHA_EMISION,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE
                FROM CAJA_RETENCION A, MOISES.VW_PERSONA_JURIDICA B
                WHERE A.ID_PROVEEDOR = B.ID_PERSONA
                AND A.ID_RETENCION = $id_retencion";
        $query = DB::select($sql);
        return $query;
    }

    public static function getRetentionComprasByIdRetencion($id_retencion)
    {
        $sql = "SELECT CRC.ID_RETDETALLE, CRC.ID_COMPRA, CRC.ID_DINAMICA, 
                CRC.IMPORTE_TOTAL, CRC.IMPORTE_RET, CRC.IMPORTE_RET_ME,
                CRC.ESTADO,
                (C.ID_ENTIDAD || '-' || C.CORRELATIVO) AS COMPRA_CORRELATIVO,
                C.SERIE AS COMPRA_SERIE, C.NUMERO AS COMPRA_NUMERO,
                C.IMPORTE AS COMPRA_IMPORTE
                FROM CAJA_RETENCION_COMPRA CRC
                INNER JOIN COMPRA C ON CRC.ID_COMPRA=C.ID_COMPRA
                WHERE ID_RETENCION= $id_retencion";
        $query = DB::select($sql);
        return $query;
    }

    public static function listRetentionsDetails($id_retencion)
    {
        $sql = "SELECT 
                B.ID_COMPROBANTE,B.SERIE,B.NUMERO, B.FECHA_DOC,B.IMPORTE,A.IMPORTE_RET
                FROM CAJA_RETENCION_COMPRA A, COMPRA B
                WHERE A.ID_COMPRA = B.ID_COMPRA
                AND A.ID_RETENCION = " . $id_retencion . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listTypesGoodsServices($anexo)
    {
        $dato = "";
        if ($anexo != "") {
            $dato = "AND ANEXO = '" . $anexo . "' ";
        }
        $sql = "SELECT 
                ID_TIPOBIENSERVICIO,NOMBRE,TASA 
                FROM TIPO_BIEN_SERVICIO
                WHERE TASA <> 0
                " . $dato . "
                ORDER BY ID_TIPOBIENSERVICIO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showTypeGoodServiceById($id_type_good_service)
    {
        $sql = "SELECT 
                ID_TIPOBIENSERVICIO,NOMBRE,TASA 
                FROM TIPO_BIEN_SERVICIO
                WHERE TASA <> 0
                AND ID_TIPOBIENSERVICIO=" . $id_type_good_service;
        $query = DB::select($sql);
        return $query;
    }

    public static function updateDetraccion($data, $id_detraccion)
    {
        DB::table('caja_detraccion')
            ->where('id_detraccion', $id_detraccion)
            ->update($data);
    }

    public static function updateDetraccionSeatContaAsiento($data, $id_tipoorigen, $id_origen)
    {
        DB::table('conta_asiento')
            ->where('id_tipoorigen', $id_tipoorigen)
            ->where('id_origen', $id_origen)
            ->update($data);
    }

    public static function updateRetencion($data, $id_retencion)
    {
        DB::table('caja_retencion')
            ->where('id_retencion', $id_retencion)
            ->update($data);
    }

    public static function updateRetencionSeatContaAsiento($data, $id_tipoorigen, $id_origen)
    {
        DB::table('conta_asiento')
            ->where('id_tipoorigen', $id_tipoorigen)
            ->where('id_origen', $id_origen)
            ->update($data);
    }

    public static function getPagoCompraByIdCompra($idCompra)
    {
        $query = "
            SELECT 
            ID_PCOMPRA,
            ID_PAGO, 
            ID_PROVEEDOR,
            ID_COMPRA, 
            ID_DINAMICA,
            ID_TIPOORIGEN, 
            IMPORTE, 
            IMPORTE_ME,
            DETALLE
            FROM CAJA_PAGO_COMPRA
        WHERE ID_COMPRA = $idCompra";
        $getDetail = DB::select($query);
        return $getDetail;
    }

    public static function getRetencionCompraByIdCompra($idCompra)
    {
        $query = "
            SELECT 
            ID_RETDETALLE, 
            ID_RETENCION, 
            ID_COMPRA, 
            ID_DINAMICA, 
            IMPORTE_TOTAL, 
            IMPORTE_RET, 
            IMPORTE_RET_ME, 
            DETALLE, 
            ESTADO, ID_TIPOORIGEN
            FROM CAJA_RETENCION_COMPRA
        WHERE ID_COMPRA = $idCompra";
        $getDetail = DB::select($query);
        return $getDetail;
    }

    public static function getRetencionComprasByIdRetencion($idRetencion)
    {
        $query = "
            SELECT 
            ID_RETDETALLE, 
            ID_RETENCION, 
            ID_COMPRA, 
            ID_DINAMICA, 
            IMPORTE_TOTAL, 
            IMPORTE_RET, 
            IMPORTE_RET_ME, 
            DETALLE, 
            ESTADO, ID_TIPOORIGEN
            FROM CAJA_RETENCION_COMPRA
        WHERE ID_RETENCION = $idRetencion";
        $getDetail = DB::select($query);
        return $getDetail;
    }

    public static function getDetraccionCompraByIdCompra($idCompra)
    {
        $query = "
            SELECT 
            ID_DETDETALLE, 
            ID_DETRACCION,  
            ID_COMPRA, 
            ID_DINAMICA, 
            IMPORTE, 
            IMPORTE_ME, ID_TIPOORIGEN
            FROM CAJA_DETRACCION_COMPRA
        WHERE ID_COMPRA = $idCompra";
        $getDetail = DB::select($query);
        return $getDetail;
    }


    public static function listTypesDetractionOperations()
    {
        $sql = "SELECT 
                ID_OPERACION,NOMBRE
                FROM TIPO_OPERACION_DETRACCION
                ORDER BY ID_OPERACION ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listDeductions($id_entidad, $id_depto, $id_voucher, $text_search)
    {
        $searchQuery = "";
        if ($text_search) {
            $searchQuery = "AND (UPPER(A.NRO_OPERACION) LIKE UPPER('%$text_search%') OR UPPER(A.NRO_CONSTANCIA) LIKE UPPER('%$text_search%')
                OR UPPER(B.ID_RUC) LIKE UPPER('%$text_search%') OR UPPER(B.NOMBRE) LIKE UPPER('%$text_search%'))
                ";
        }
        $sql = "SELECT  A.ID_DETRACCION,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VOUCHER,
                        --B.ID_RUC AS RUC,B.NOMBRE,
                        NVL(B.ID_RUC,C.NUM_DOCUMENTO) AS RUC,NVL(B.NOMBRE,C.NOM_PERSONA) AS NOMBRE,
                        A.NRO_CONSTANCIA,A.NRO_OPERACION,TO_CHAR(A.FECHA_EMISION,'DD/MM/YYYY') AS FECHA_EMISION,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,
                        TO_CHAR(A.IMPORTE_ME,'999,999,999.99') AS IMPORTE_ME,
                        C.ID_TIPOBIENSERVICIO,
                        C.NOMBRE AS NOMBRE_TIPOBIENSERVICIO,
                        D.ID_OPERACION,
                        D.NOMBRE AS NOMBRE_OPERACION,
                        (SELECT 
                        X.NOMBRE||' - '||X.CUENTA_CORRIENTE 
                        FROM CAJA_CUENTA_BANCARIA X WHERE X.ID_CTABANCARIA = A.ID_CTABANCARIA) AS BANCO 
                FROM CAJA_DETRACCION A 
                --INNER JOIN MOISES.VW_PERSONA_JURIDICA B ON A.ID_PROVEEDOR = B.ID_PERSONA
                LEFT JOIN MOISES.VW_PERSONA_JURIDICA B ON A.ID_PROVEEDOR = B.ID_PERSONA
                LEFT JOIN MOISES.VW_PERSONA_NATURAL C ON A.ID_PROVEEDOR = C.ID_PERSONA AND C.ID_TIPODOCUMENTO = 6
                INNER JOIN TIPO_BIEN_SERVICIO C ON A.ID_TIPOBIENSERVICIO=C.ID_TIPOBIENSERVICIO
                INNER JOIN TIPO_OPERACION_DETRACCION D ON A.ID_OPERACION=D.ID_OPERACION
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ESTADO = '1'
                $searchQuery
                ORDER BY A.NRO_CONSTANCIA,A.NRO_OPERACION,B.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listDeductionsDetails($id_detraccion)
    {
        $sql = "SELECT 
                B.ID_COMPROBANTE,B.SERIE,B.NUMERO, B.FECHA_DOC,TO_CHAR(B.IMPORTE,'999,999,999.99') AS IMPORTE,TO_CHAR(B.IMPORTE_ME,'999,999,999.99') AS IMPORTE_ME
                FROM CAJA_DETRACCION_COMPRA A, COMPRA B
                WHERE A.ID_COMPRA = B.ID_COMPRA
                AND A.ID_DETRACCION = " . $id_detraccion . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showExchangeRatePayment($id_mediopago, $id_chequera, $fecha)
    {
        if ($id_mediopago == "007") {
            $sql = "SELECT 
                            FECHA 
                    FROM CAJA_CHEQUERA
                    WHERE ID_CHEQUERA = '" . $id_chequera . "' ";
            $query = DB::select($sql);
            foreach ($query as $item) {
                $fecha = $item->fecha;
            }
        }
        $sql = "SELECT FC_TIPOCAMBIO(9,'" . $fecha . "','V') as TC
                FROM DUAL ";
        $query = DB::select($sql);
        return $query;
    }

    public static function spContaAsientoVale($id_vale)
    {
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CONTA_ASIENTO_VALE(:P_ID_VALE,:P_ERROR,:P_MSGERROR);end;");
            $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $oreturn['error'] = $error;
            $oreturn['message'] = $msg_error;
            return $oreturn;
        } catch (Exception $e) {
            $oreturn['error'] = 1;
            $oreturn['message'] = $e->getMessage();
            return $oreturn;
        }
    }

    public static function listValesDeposits($id_vale)
    {
        $sql = "SELECT 
                        A.ID_DEPOSITO,A.ID_VALE,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.GLOSA,A.IMPORTE,
                        (SELECT COUNT(X.ID_VFILE)  FROM eliseo.caja_vale_file X WHERE X.ID_DEPOSITO=A.ID_DEPOSITO) AS EXISTE
                FROM CAJA_DEPOSITO A
                WHERE A.ID_VALE = " . $id_vale . "
                UNION ALL
                SELECT 
                B.ID_DEPBANCO,B.ID_VALE,B.OPERACION,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,B.GLOSA,B.IMPORTE, 0 AS EXISTE
                FROM CAJA_DEPOSITO_BANCO B
                WHERE ID_VALE = " . $id_vale . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getAccountSeatByOrigenTypeOrigen($id_origen, $id_tipoorigen, $id_voucher)
    {
//        dd('asdfad', $id_voucher);
        $query = DB::table('CONTA_ASIENTO')
            ->where('CONTA_ASIENTO.ID_ORIGEN', $id_origen)
            ->where('CONTA_ASIENTO.ID_TIPOORIGEN', $id_tipoorigen)
            ->where('CONTA_ASIENTO.VOUCHER', $id_voucher)
            ->get();
        return $query;
    }

    public static function deleteBankDeposits($id_depbanco)
    {
        $depos = DB::table('CAJA_DEPOSITO_BANCO')
            ->where('CAJA_DEPOSITO_BANCO.ID_DEPBANCO', $id_depbanco)
            ->get()->first();
        $asientos = self::getAccountSeatByOrigenTypeOrigen($depos->id_depbanco, $depos->id_tipoorigen, $depos->id_voucher);

        $delSeat = DB::table('CONTA_ASIENTO')->whereIn('id_asiento', $asientos->pluck('id_asiento'))->delete();
        $delDepos = DB::table('CAJA_DEPOSITO_BANCO')->where('CAJA_DEPOSITO_BANCO.ID_DEPBANCO', $id_depbanco)->delete();

        $data['deposito'] = $depos;
        $data['asientos'] = $asientos;
        $data['delete'] = $delSeat;
        $data['deleteDepos'] = $delDepos;
        return $data;
    }

    public static function myPayments($id_voucher)
    {
        $sql = "SELECT 
                        ID_PAGO,ID_VOUCHER,ID_TIPOORIGEN,ID_ORIGEN,
                        --ID_CTABANCARIA,
                        NVL(TO_CHAR(ID_CTABANCARIA),PKG_CAJA.FC_CTA_CTE_RENDICION(ID_PAGO)) AS ID_CTABANCARIA,
                        --PKG_CAJA.FC_NOMBRE_CTA_BANCARIA(ID_CTABANCARIA) CUENTA_BANCARIA,
                        NVL(PKG_CAJA.FC_NAME_CTA_BANCARIA(ID_CTABANCARIA),PKG_CAJA.FC_CTA_CTE_RENDICION(ID_PAGO)) CUENTA_BANCARIA,
                        NUMERO,
                        PKG_ACCOUNTING.FC_CUENTA_ASIENTO(ID_ENTIDAD,ID_TIPOORIGEN,ID_ORIGEN,ID_VOUCHER,'D') CUENTA,
                        PKG_ACCOUNTING.FC_DEPARTAMENTO_ASIENTO(ID_ENTIDAD,ID_TIPOORIGEN,ID_ORIGEN,ID_VOUCHER,'D') NIVEL,
                        TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                        TO_CHAR(IMPORTE,'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(IMPORTE_ME,0),'999,999,999.99') AS IMPORTE_ME,
                        coalesce(IMPORTE,0) AS IMPORTE_PDF,
                        coalesce(IMPORTE_ME,0) AS IMPORTE_ME_PDF,
                        DETALLE 
                FROM VW_PAYMENTS_MOV
                WHERE ID_VOUCHER = " . $id_voucher . "
                UNION ALL
                SELECT DISTINCT
                        A.ID_VALE,A.ID_VOUCHER,A.ID_TIPOORIGEN,A.ID_VALE,TO_CHAR(A.ID_CTABANCARIA) AS ID_CTABANCARIA,
                        PKG_CAJA.FC_NAME_CTA_BANCARIA(A.ID_CTABANCARIA) AS CTA_BANCARIA,A.NRO_VALE,
                        PKG_ACCOUNTING.FC_CUENTA_ASIENTO(A.ID_ENTIDAD,A.ID_TIPOORIGEN,A.ID_VALE,A.ID_VOUCHER,'D') CUENTA,
                        PKG_ACCOUNTING.FC_DEPARTAMENTO_ASIENTO(A.ID_ENTIDAD,A.ID_TIPOORIGEN,A.ID_VALE,A.ID_VOUCHER,'D') NIVEL,
                        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                        TO_CHAR(B.IMPORTE,'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(B.IMPORTE_ME,0),'999,999,999.99') AS IMPORTE_ME,
                        COALESCE(B.IMPORTE,0) AS IMPORTE_PDF,
                        COALESCE(B.IMPORTE_ME,0) AS IMPORTE_ME_PDF,
                        B.DESCRIPCION
                FROM CAJA_VALE A JOIN CONTA_ASIENTO B 
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN 
                AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER AND B.IMPORTE > 0
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                ORDER BY ID_CTABANCARIA,NUMERO,ID_PAGO,ID_ORIGEN,NUMERO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function myPaymentsCash($id_voucher)
    {
        $sql = "SELECT FC_NOMBRE_PERSONA(ID_USER) AS CAJERO,
                        TO_CHAR(NVL(SUM(IMPORTE),0),'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(SUM(IMPORTE_ME),0),'999,999,999.99') AS IMPORTE_ME,
                        NVL(SUM(IMPORTE),0) AS IMPORTE_PDF,
                        NVL(SUM(IMPORTE_ME),0) AS IMPORTE_ME_PDF
                FROM VW_PAYMENTS_MOV
                WHERE ID_VOUCHER = " . $id_voucher . "
                GROUP BY ID_USER
                UNION ALL
                SELECT FC_NOMBRE_PERSONA(ID_CAJERO) AS CAJERO,
                        TO_CHAR(SUM(IMPORTE),'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(SUM(IMPORTE_ME),0),'999,999,999.99') AS IMPORTE_ME,
                        COALESCE(SUM(IMPORTE),0) AS IMPORTE_PDF,
                        COALESCE(SUM(IMPORTE_ME),0) AS IMPORTE_ME_PDF
                FROM (
                        SELECT DISTINCT A.ID_VALE,A.ID_CAJERO,
                        A.IMPORTE,
                        A.IMPORTE_ME
                        FROM CAJA_VALE A JOIN CONTA_ASIENTO B 
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN 
                        AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER 
                        WHERE A.ID_VOUCHER = " . $id_voucher . "
                ) GROUP BY ID_CAJERO
                ORDER BY CAJERO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function myPaymentsTotal($id_voucher)
    {
        /*$sql = "SELECT TO_CHAR(SUM(IMPORTE),'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(SUM(IMPORTE_ME),0),'999,999,999.99') AS IMPORTE_ME
                FROM VW_PAYMENTS_MOV
                WHERE ID_VOUCHER = " . $id_voucher . " ";*/
        $sql = "SELECT  TO_CHAR(SUM(IMPORTE),'999,999,999.99') AS IMPORTE,
                        TO_CHAR(NVL(SUM(IMPORTE_ME),0),'999,999,999.99') AS IMPORTE_ME
                FROM (
                        SELECT ID_PAGO,IMPORTE,
                                IMPORTE_ME
                        FROM VW_PAYMENTS_MOV
                        WHERE ID_VOUCHER = " . $id_voucher . "
                        UNION ALL
                        SELECT DISTINCT A.ID_VALE,
                                A.IMPORTE,
                                A.IMPORTE_ME
                        FROM CAJA_VALE A JOIN CONTA_ASIENTO B 
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN 
                        AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER 
                        WHERE A.ID_VOUCHER = " . $id_voucher . "
                ) ";
        $query = DB::select($sql);
        return $query;
    }


    public static function Detractions($id_entidad, $id_voucher)
    {
        $sql = "SELECT    
                   A.ID_DETRACCION,
                   A.ID_VOUCHER,
                   --PKG_CAJA.FC_NOMBRE_CTA_BANCARIA(A.ID_CTABANCARIA) CUENTA_BANCARIA,
                   PKG_CAJA.FC_NAME_CTA_BANCARIA(ID_CTABANCARIA) CUENTA_BANCARIA,
                   PKG_ACCOUNTING.FC_CUENTA_ASIENTO(A.ID_ENTIDAD,A.ID_TIPOORIGEN,A.ID_DETRACCION,A.ID_VOUCHER,'D') CUENTA,
                   PKG_ACCOUNTING.FC_DEPARTAMENTO_ASIENTO(A.ID_ENTIDAD,A.ID_TIPOORIGEN,A.ID_DETRACCION,A.ID_VOUCHER,'D') NIVEL,
                   --D.NOMBRE,
                   --G.ID_RUC,
                   NVL(G.NOMBRE,K.NOM_PERSONA) AS NOMBRE,
                   NVL(G.ID_RUC,K.NUM_DOCUMENTO) AS ID_RUC,
                   A.NRO_CONSTANCIA,
                   A.NRO_OPERACION,
                   TO_CHAR(A.FECHA_EMISION,'DD/MM/YYYY') AS FECHA_EMISION,
                   (CASE WHEN A.TIPO_AUTODETRACCION IN('S','C') THEN TO_CHAR(F.TOTAL, '999,999,999.99') ELSE TO_CHAR(nvl(E.IMPORTE,i.importe), '999,999,999.99') END) IMPORTE_REF,
                   (CASE WHEN A.TIPO_AUTODETRACCION IN('S','C') THEN TO_CHAR(F.TOTAL_ME, '999,999,999.99') ELSE TO_CHAR(nvl(e.importe_me,i.importe_me), '999,999,999.99') END) IMPORTE_ME_REF,
                   (CASE A.TIPO_AUTODETRACCION WHEN 'S' THEN 'V' ELSE 'C' END) TIPO_REF,
                   H.SIMBOLO AS MONEDA,
                   (CASE WHEN A.TIPO_AUTODETRACCION IN('S','C') THEN F.SERIE||'-'||F.NUMERO ELSE decode(E.id_compra,null,(I.SERIE||'-'||I.NUMERO),E.serie||'-'||E.numero) END) SERIE_REF,
                   A.IMPORTE,
                   A.IMPORTE_ME,
                   (CASE WHEN A.TIPO_AUTODETRACCION IN('S','C') THEN C.DETALLE ELSE B.DETALLE END) DETALLE_REF
                FROM CAJA_DETRACCION A LEFT JOIN CAJA_DETRACCION_COMPRA B
                ON A.ID_DETRACCION = B.ID_DETRACCION
                LEFT JOIN CAJA_DETRACCION_VENTA C
                ON A.ID_DETRACCION = C.ID_DETRACCION
                LEFT JOIN MOISES.PERSONA D
                ON A.ID_PROVEEDOR = D.ID_PERSONA
                LEFT JOIN COMPRA E
                ON B.ID_COMPRA = E.ID_COMPRA AND A.id_proveedor = E.id_proveedor
                LEFT JOIN VENTA F
                ON C.ID_VENTA = F.ID_VENTA
                --LEFT JOIN MOISES.VW_PERSONA_JURIDICA G ON A.ID_PROVEEDOR = G.ID_PERSONA
                LEFT JOIN MOISES.VW_PERSONA_JURIDICA G ON A.ID_PROVEEDOR = G.ID_PERSONA
                LEFT JOIN MOISES.VW_PERSONA_NATURAL K ON A.ID_PROVEEDOR = K.ID_PERSONA AND K.ID_TIPODOCUMENTO = 6
                LEFT JOIN CONTA_MONEDA H
                ON A.ID_MONEDA = H.ID_MONEDA
                LEFT JOIN compra_saldo I
                ON I.id_saldo = B.id_compra
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ESTADO = '1'
                AND A.ID_VOUCHER = $id_voucher
                ORDER BY A.NRO_CONSTANCIA,A.NRO_OPERACION,D.NOMBRE";
        return DB::select($sql);
    }

    public static function myPaymentsSummary($id_voucher)
    {
        $sql = "SELECT 
                        FC_CUENTA_DENOMINACIONAL(B.CUENTA) AS CUENTA_N,
                        B.CUENTA,
                        FC_DEPARTAMENTO(DEPTO) AS DEPTO_N,
                        B.DEPTO,
                        SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) ASDEBITO,
                        ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VW_PAYMENTS_DET A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_ORIGEN = B.ID_ORIGEN
                AND A.ID_VOUCHER = B.VOUCHER
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                GROUP BY B.CUENTA, B.DEPTO
                UNION ALL
                SELECT  
                        FC_CUENTA_DENOMINACIONAL(B.CUENTA) AS CUENTA_N,
                        B.CUENTA,
                        FC_DEPARTAMENTO(DEPTO) AS DEPTO_N,
                        B.DEPTO,
                SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) ASDEBITO,
                ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM CAJA_VALE A JOIN CONTA_ASIENTO B 
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN 
                AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER 
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                GROUP BY B.CUENTA, B.DEPTO
                ORDER BY CUENTA, DEPTO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function myPaymentsSummaryTotal($id_voucher)
    {
        /*$sql = "SELECT 
                        SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) ASDEBITO,
                        ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VW_PAYMENTS_DET A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_ORIGEN = B.ID_ORIGEN
                AND A.ID_VOUCHER = B.VOUCHER
                WHERE A.ID_VOUCHER = " . $id_voucher . " ";*/
        $sql = "SELECT 
                        SUM(DEBITO) ASDEBITO,
                        ABS(SUM(CREDITO)) AS CREDITO
                FROM (
                        SELECT 
                                (CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) AS DEBITO,
                                (CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END) AS CREDITO
                        FROM VW_PAYMENTS_DET A JOIN CONTA_ASIENTO B
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                        AND A.ID_ORIGEN = B.ID_ORIGEN
                        AND A.ID_VOUCHER = B.VOUCHER
                        WHERE A.ID_VOUCHER = " . $id_voucher . "
                        UNION ALL
                        SELECT
                                (CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) AS DEBITO,
                                (CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END) AS CREDITO
                        FROM CAJA_VALE A JOIN CONTA_ASIENTO B 
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN 
                        AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER 
                        WHERE A.ID_VOUCHER = " . $id_voucher . "
                ) ";
        $query = DB::select($sql);
        return $query;
    }

    public static function addSeatsPayments($id_pago, $id_cuenta, $id_restriccion, $id_ctacte, $id_fondo, $id_depto, $id_descripcion, $_dc, $agrupa)
    {
        $error = 0;
        $msg_error = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_PAGO_ASIENTO(:P_ID_PAGO,:P_ID_CUENTAAASI,:P_ID_RESTRICCION,:P_ID_CTACTE,:P_ID_FONDO,:P_ID_DEPTO,:P_DESCRIPCION,:P_DC,:P_AGRUPA,:P_ERROR,:P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_PAGO', $id_pago, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuenta, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_DESCRIPCION', $id_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':P_DC', $_dc, PDO::PARAM_STR);
            $stmt->bindParam(':P_AGRUPA', $agrupa, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error'] = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function addValeFile($data)
    {
        try {
            $id_vfile = DB::transaction(function () use ($data) {
                DB::table('eliseo.caja_vale_file')->insert($data);
                return DB::getSequence()->currentValue('CAJA_VALE_FILE_ID_SEQ');
            });
            // $result = DB::table('caja_vale_file')->insert($data);
            if ($id_vfile) {
                $result = [
                    "success" => true,
                    "data" => $id_vfile,
                    "message" => "OK"
                ];
            } else {
                $result = [
                    "success" => false,
                    "data" => '',
                    "message" => "Error."
                ];
            }
        } catch (Exception $e) {
            $result = [
                "success" => false,
                "data" => '',
                "message" => $e->getMessage()
            ];
        }
        return $result;
    }

    public static function updateValeFile($IdVFile, $data)
    {
        DB::table('CAJA_VALE_FILE')
            ->where('ID_VFILE', $IdVFile)
            ->update($data);
    }

    public static function deleteProvisionVale($id_vale)
    {
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_PROVISION_VALE(:P_ID_VALE,:P_ERROR,:P_MSGERROR);end;");
            $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $oreturn['error'] = $error;
            $oreturn['message'] = $msg_error;
            return $oreturn;
        } catch (Exception $e) {
            $oreturn['error'] = 1;
            $oreturn['message'] = $e->getMessage();
            return $oreturn;
        }
    }

    public static function showValeFile($id_vale, $tipo)
    {
        $files = DB::table('CAJA_VALE_FILE')
            ->where('CAJA_VALE_FILE.ID_VALE', $id_vale)
            ->where('CAJA_VALE_FILE.TIPO', $tipo)
            ->get()->first();
        return $files ? $files->url : null;
    }

    public static function showAllValeFile($id_vale)
    {
        $files = DB::table('CAJA_VALE_FILE')
            ->where('CAJA_VALE_FILE.ID_VALE', $id_vale)
            ->where('CAJA_VALE_FILE.ESTADO', '1')
            ->orderby('CAJA_VALE_FILE.TIPO')
            ->get();
        return $files;
    }

    public static function getPaymentEntrySeatVale($params)
    {
        return DB::select("SELECT DISTINCT
                A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_VALE,PKG_CAJA.FC_NAME_CTA_BANCARIA(A.ID_CTABANCARIA) AS CTA_BANCARIA,A.ID_DINAMICA,A.ID_EMPLEADO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_REG,
                A.FECHA,
                A.DETALLE,
                A.IMPORTE,A.IMPORTE_ME,
                FC_NOMBRE_PERSONA(A.ID_EMPLEADO) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(A.ID_EMPLEADO) AS RUC_PROVEEDOR
                FROM CAJA_VALE A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_VALE = B.ID_ORIGEN AND A.ID_VOUCHER = B.VOUCHER
                WHERE A.ID_VOUCHER = ?
                ", [$params['id_voucher']]);
    }

    public static function fileVale($id_vale)
    {
        $data = DB::table('eliseo.caja_vale_file as a')
            ->leftjoin('eliseo.caja_vale_gasto as c', 'a.id_vfile', '=', 'c.id_vfile')
            ->where('a.id_vale', $id_vale)
            ->where('a.estado', '1')
            ->whereNull('a.id_pgasto')
            ->whereIn('a.tipo', ['3', '4'])
            ->select('a.id_vfile', 'a.id_vale', 'a.nombre', 'a.formato', 'a.url', 'a.fecha', 'a.tipo', 'a.tamanho', 'a.estado', 'a.id_pgasto', 'a.id_deposito', 'a.id_user', 'c.autorizado',
                'c.importe', 'c.importe_me')
            ->orderBy('a.fecha')
            ->get();
        return $data;
    }

    public static function deleteFilesVale($id_vfile)
    {
        $object = DB::table('eliseo.caja_vale_file')->where('id_vfile', $id_vfile)->select('nombre', 'tipo')->first();
        $directorio = 'lamb-financial/treasury/vales/';
        $carpeta = '';
        if ($object->tipo == "1") {
            $carpeta = $directorio . 'convenios';
        } elseif ($object->tipo == "2") {
            $carpeta = $directorio . 'constancia-depositos';
        } else if ($object->tipo == "3") {
            $carpeta = $directorio . 'sustentos-gastos';
        } else if ($object->tipo == "4") {
            $carpeta = $directorio . 'voucher-depositos';
        }

        if ($object->tipo == "3") {
            $contar = DB::table('eliseo.caja_vale_gasto')->where('id_vfile', $id_vfile)->count();
            if ($contar > 0) {
                $cvg = DB::table('eliseo.caja_vale_gasto')->where('id_vfile', $id_vfile)->select('id_vgasto')->first();
                if (!empty($cvg)) {
                    $count_cvga = DB::table('eliseo.caja_vale_gasto_asiento')->where('id_vgasto', $cvg->id_vgasto)->count();
                    if ($count_cvga > 0) {
                        $cvga = DB::table('eliseo.caja_vale_gasto_asiento')->where('id_vgasto', $cvg->id_vgasto)->delete();
                    }
                }
                $cvp = DB::table('eliseo.caja_vale_gasto')->where('id_vfile', $id_vfile)->delete();
            }
        }

        $delete = DB::table('eliseo.caja_vale_file')->where('id_vfile', $id_vfile)->delete();
        if ($delete) {
            $resp = ComunData::deleteFilesDirectorio($carpeta, $object->nombre, 'E');
            $response = [
                'success' => true,
                'message' => 'Se elimino satisfactoriamente' . ', ' . $resp,
                'data' => $delete,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede eliminar',
                'data' => $delete,
            ];
        }
        return $response;
    }

    public static function valeFilesList($request)
    {
        $data = DB::table('ELISEO.CAJA_VALE_FILE A')
            ->where('A.ID_VALE', $request->id_vale)
            ->where('A.TIPO', $request->tipo)
            ->where('A.ESTADO', '1')
            ->whereNull('A.ID_PGASTO')
            ->select('*')
            ->orderby('A.tipo')
            ->get();
        return $data;
    }

    public static function depositoFile($id_deposito)
    {
        $data = DB::table('eliseo.caja_vale_file a')
            ->where('a.id_deposito', $id_deposito)
            ->select('*')
            ->first();
        return $data;
    }

    public static function pgastoFile($id_pgasto)
    {
        $data = DB::table('eliseo.caja_vale_file a')
            ->where('a.id_pgasto', $id_pgasto)
            ->select('*')
            ->first();
        return $data;
    }

    //  DEPRECATED
    public static function addPaymentsFile($id_pago, $nombre, $formato, $url, $tipo, $estado, $size, $params)
    {
        //  $id_cfile = ComunData::correlativo('eliseo.caja_pago_file', 'id_cfile');
        //  if ($id_cfile > 0) {

        // $dataValeFile = array(
        //     // "id_cfile" => $id_cfile,
        //     "id_pago" => $id_pago,
        //     "nombre" => $nombre,
        //     "formato" => $formato,
        //     "url" => $url,
        //     "fecha" => DB::raw('sysdate'),
        //     "tipo" => $tipo,
        //     "tamanho" => $size,
        //     "estado" => $estado,
        // );

        // $result = DB::table('eliseo.caja_pago_file')->insert($dataValeFile);
        // if ($result) {
        //     $findVFile = DB::table('caja_pago_file')
        //         ->where('id_pago', $id_pago)
        //         ->where('nombre', $nombre)->first();
        //     $result = [
        //         "success" => true,
        //         "data" => $findVFile->id_cfile,
        //         "message" => "OK"
        //     ];
        // } else {
        //     $result = [
        //         "success" => false,
        //         "data" => [],
        //         "message" => "Error."
        //     ];
        // }
        //  } else {
        //      $result = [
        //          "success" => false,
        //          "data" => [],
        //          "message" => "No se genero correlativo"
        //      ];
        //  }

        // return $result;
    }

    public static function listValeGastoAuthorize($id_entidad, $id_depto)
    {
        $query = DB::table('eliseo.caja_vale a')
            ->join('moises.persona as b', 'a.id_empleado', '=', 'b.id_persona')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_depto', $id_depto)
            ->whereIn('a.id_vale', [DB::raw("select x.id_vale from eliseo.caja_vale_gasto x where x.id_vale=a.id_vale and x.autorizado='N'")])
            ->whereNull('a.rendido')
            ->select(
                'a.id_vale', 'a.id_anho', 'a.id_empleado',
                'a.id_entidad', 'a.id_mediopago',
                'a.id_mes', 'a.id_moneda', 'a.id_persona',
                'a.id_persona_auto', 'a.importe', 'a.id_moneda',
                'a.importe_me', 'a.motivo', 'a.detalle', 'a.estado', 'a.fecha', 'a.nro_vale',
                DB::raw("(b.nombre||' '||b.paterno||' '||b.materno) as responsable"))
            ->get();
        $data = array();
        foreach ($query as $key => $value) {
            $row = (object)$value;
            $item = array();
            $item['id_vale'] = $row->id_vale;
            $item['id_anho'] = $row->id_anho;
            $item['id_empleado'] = $row->id_empleado;
            $item['id_entidad'] = $row->id_entidad;
            $item['id_mediopago'] = $row->id_mediopago;
            $item['id_mes'] = $row->id_mes;
            $item['id_moneda'] = $row->id_moneda;
            $item['id_persona'] = $row->id_persona;
            $item['id_persona_auto'] = $row->id_persona_auto;
            $item['importe'] = $row->importe;
            $item['importe_me'] = $row->importe_me;
            $item['motivo'] = $row->motivo;
            $item['responsable'] = $row->responsable;
            $item['detalle'] = $row->detalle;
            $item['estado'] = $row->estado;
            $item['fecha'] = $row->fecha;
            $item['nro_vale'] = $row->nro_vale;
            $item['details'] = ExpensesData::childGastoVale($row->id_vale);
            // $data[] = $item;
            array_push($data, $item);
        }
        return $data;
    }

    public static function childGastoVale($id_vale)
    {
        $data = DB::table('eliseo.caja_vale_gasto as a')
            ->join('eliseo.caja_vale_file as b', 'a.id_vfile', '=', 'b.id_vfile')
            ->where('a.id_vale', $id_vale)
            ->select('a.*', 'b.nombre as nombre_file', 'b.formato', 'b.tipo as tipo_file', 'b.url')->get();
        return $data;
    }

    public static function addSeatValeGastoFile($request)
    { //  metodo usado par crear y duplicar asiento
        $id_vgasto = $request->id_vgasto;
        $id_fondo = $request->id_fondo;
        $id_depto = $request->id_depto;
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_restriccion = $request->id_restriccion;
        $id_ctacte = $request->id_ctacte;
        $importe = $request->importe;
        $importe_me = $request->importe_me;
        $dc = $request->dc;
        $agrupa = $request->agrupa;
        if ($dc === 'C' and $importe > 0) {
            $importe = $importe * (-1);
        }
        if ($dc === 'C' and $importe_me > 0) {
            $importe_me = $importe_me * (-1);
        }
        $id_vasiento = ComunData::correlativo('eliseo.caja_vale_gasto_asiento', 'id_vasiento');
        if ($id_vasiento > 0) {
            $asiento = DB::table('eliseo.caja_vale_gasto_asiento')->insert([
                'id_vasiento' => $id_vasiento,
                'id_vgasto' => $id_vgasto,
                'id_fondo' => $id_fondo,
                'id_depto' => $id_depto,
                'id_cuentaaasi' => $id_cuentaaasi,
                'id_restriccion' => $id_restriccion,
                'id_ctacte' => $id_ctacte,
                'importe' => $importe,
                'importe_me' => $importe_me,
                'dc' => $dc,
                'agrupa' => $agrupa,
            ]);
            if ($asiento) {
                $result = [
                    'success' => true,
                    'message' => 'Se registro el asiento',
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo registrar',
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se genero correlativo',
            ];
        }
        return $result;
    }

    public static function addSeatsTransValeGastoFile($request)
    {
        $id_vgasto = $request->id_vgasto;
        $id_dinamica = $request->id_dinamica;
        $error = 0;
        $msg_error = "";
        for ($x = 1; $x <= 200; $x++) {
            $msg_error .= "0";
        }
        $pdo = DB::getPdo();
        DB::beginTransaction();
        $stmt = $pdo->prepare("begin PKG_CAJA.SP_VALE_GASTO_ASIENTO(
            :P_ID_VGASTO,
            :P_ID_DINAMICA,
            :P_ERROR,
            :P_MSGERROR); end;");
        $stmt->bindParam(':P_ID_VGASTO', $id_vgasto, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        if ($error == 0) {
            DB::commit();
            $result = [
                'success' => true,
                'message' => $msg_error,
            ];
        } else {
            DB::rollback();
            $result = [
                'success' => false,
                'message' => $msg_error,
            ];
        }
        return $result;
    }

    public static function updateSeatValeGastoFile($request, $id_vasiento)
    { //  metodo usado par crear y duplicar asiento
        $id_fondo = $request->id_fondo;
        $id_depto = $request->id_depto;
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_restriccion = $request->id_restriccion;
        $id_ctacte = $request->id_ctacte;
        $importe = $request->importe;
        $importe_me = $request->importe_me;
        $dc = $request->dc;
        $agrupa = $request->agrupa;
        if ($dc === 'C' and $importe > 0) {
            $importe = $importe * (-1);
        }
        if ($dc === 'C' and $importe_me > 0) {
            $importe_me = $importe_me * (-1);
        }
        $asiento = DB::table('eliseo.caja_vale_gasto_asiento')->where('id_vasiento', $id_vasiento)->update([
            'id_fondo' => $id_fondo,
            'id_depto' => $id_depto,
            'id_cuentaaasi' => $id_cuentaaasi,
            'id_restriccion' => $id_restriccion,
            'id_ctacte' => $id_ctacte,
            'importe' => $importe,
            'importe_me' => $importe_me,
            'dc' => $dc,
            'agrupa' => $agrupa,
        ]);
        if ($asiento) {
            $result = [
                'success' => true,
                'message' => 'Se modifico el asiento',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo modificar',
            ];
        }
        return $result;
    }

    public static function listSeatValeGastoFile($id_vgasto)
    {
        $data = DB::table('eliseo.caja_vale_gasto_asiento')
            ->where('id_vgasto', $id_vgasto)
            ->select('id_vasiento', 'id_vgasto', 'id_cuentaaasi', 'id_restriccion', 'id_ctacte', 'id_fondo', 'id_depto',
                DB::raw("case when dc = 'C' then abs(importe) else importe end as importe"),
                DB::raw("case when dc = 'C' then abs(importe_me) else importe_me end as importe_me"),
                'dc', 'agrupa')
            ->orderBy('id_vasiento', 'asc')
            ->get();
        return $data;
    }

    public static function deleteSeatValeGastoFile($id_vgasto)
    {
        $asiento = DB::table('eliseo.caja_vale_gasto_asiento')->where('id_vgasto', $id_vgasto)->delete();
        if ($asiento) {
            $result = [
                'success' => true,
                'message' => 'Se elimino el asiento',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo eliminar',
            ];
        }
        return $result;
    }

    public static function autthorizeValeGastoFile($id_vgasto, $request)
    {
        $motivo = '';
        if (!empty($request->autorizaRechaza)) {
            $motivo = $request->motivo;
        }
        $importe = $request->importe;
        $importe_me = $request->importe_me;
        $detalle = $request->detalle;

        if (!empty($importe)) {
            $updates = [
                'autorizado' => $request->autorizaRechaza,
                'motivo' => $motivo,
                'detalle' => $detalle,
                'importe' => $importe,
                'importe_me' => $importe_me,
            ];
        } else {
            $updates = ['autorizado' => $request->autorizaRechaza, 'motivo' => $motivo];
        }

        $gasto = DB::table('eliseo.caja_vale_gasto')
            ->where('id_vgasto', $id_vgasto)
            ->update($updates);
        if ($gasto) {
            $result = [
                'success' => true,
                'message' => $request->autorizaRechaza == 'S' ? 'Se autorizo con éxito' : 'Se rechazo con éxito',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $request->autorizaRechaza == 'S' ? 'No se pudo autorizar' : 'No se pudo rechazar',
            ];
        }
        return $result;
    }

    public static function gastosValeAutorizados($id_vale)
    {
        $data = DB::table('eliseo.caja_vale_gasto as a')
            ->join('eliseo.caja_vale_file as b', 'a.id_vfile', '=', 'b.id_vfile')
            ->where('a.id_vale', $id_vale)
            ->whereNull('a.id_pgasto')
            ->where('a.autorizado', 'S')
            ->select('a.id_vgasto', 'a.id_vale', 'a.id_vfile', 'a.id_user', DB::raw("(to_char(a.fecha, 'YYYY-MM-DD')) as fecha"),
                'a.importe', 'a.importe_me', 'a.detalle', 'a.autorizado', 'a.motivo',
                'b.nombre as nombre_file', 'b.formato', 'b.tipo as tipo_file', 'b.url')->get();
        return $data;
    }

    public static function importGastoComprobante($request, $id_vale)
    {
        $tipo = $request->tipo;
        $data = array();

        if ($tipo == 'COMP') {
            $d = DB::table('ELISEO.PEDIDO_COMPRA A')
                ->leftJoin('ELISEO.COMPRA B', 'A.ID_COMPRA', '=', 'B.ID_COMPRA')
                ->join('MOISES.PERSONA C', 'A.ID_PROVEEDOR', '=', 'C.ID_PERSONA')
                ->leftJoin('MOISES.PERSONA_CONTRIBUYENTE PC', 'C.ID_PERSONA', '=', 'PC.ID_PERSONA')
                ->leftJoin('MOISES.PERSONA_JURIDICA PJ', 'C.ID_PERSONA', '=', 'PJ.ID_PERSONA')
                ->leftJoin('MOISES.VW_PERSONA_JURIDICA VPJ', 'C.ID_PERSONA', '=', 'VPJ.ID_PERSONA')
                ->leftJoin('MOISES.VW_PERSONA_NATURAL_LEGAL VPNL', 'C.ID_PERSONA', '=', 'VPNL.ID_PERSONA')
                ->select(
                    'C.NOMBRE',
                    DB::raw("COALESCE(B.SERIE, A.SERIE) AS SERIE"),
                    DB::raw("COALESCE(B.NUMERO, A.NUMERO) AS NUMERO"),
                    DB::raw("COALESCE(B.IMPORTE, A.IMPORTE) AS IMPORTE"),
                    DB::raw("
                        CASE 
                            WHEN COALESCE(B.ID_MONEDA, A.ID_MONEDA) = 7 THEN 'Soles' 
                            ELSE 'Dolares' 
                        END AS MONEDA"),
                    DB::raw("
                        CASE 
                            WHEN COALESCE(B.ESTADO, 0) = 1 THEN 'Provisionado' 
                            ELSE 'Pendiente' 
                        END AS PROCESO"),
                    DB::raw("COALESCE(B.ESTADO, 0) AS ESTADO"),
                    DB::raw("COALESCE(PJ.ID_RUC, PC.ID_RUC, VPJ.ID_RUC, VPNL.ID_RUC) AS RUC")
                )
                ->where('A.ID_VALE', '=', $id_vale);

            $data = $d->get();
        }

        if ($tipo == 'GAST') {
            $d = DB::table('ELISEO.CAJA_VALE_GASTO')
                ->select(
                    'DETALLE',
                    'IMPORTE',
                    'AUTORIZADO',
                    DB::raw("TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA")
                )
                ->where('ID_VALE', '=', $id_vale)
                ->whereIn('AUTORIZADO', ['S', 'N']);

            $data = $d->get();
        }

        return $data;
    }

    public static function addGastoVale($request, $fecha_reg, $id_entidad, $id_user)
    {
        $id_pago = $request->id_pago;
        $numero = $request->numero;
        $id_persona = $request->id_persona;
        $id_moneda = $request->id_moneda;
        $medio_pago = $request->medio_pago;
        $detalle = $request->details;
        $fecha = $fecha_reg;


        foreach ($detalle as $value) {
            $items = (object)$value;
            $person = DB::table('moises.persona')->where('id_persona', $id_persona)->select('nombre', 'paterno')->first();
            $prefix = '';
            if (!empty($person)) {
                $prefix = $person->nombre[0] . '.' . $person->paterno;
            }
            if (!empty($items->fecha)) { //Acceso de cheque o telecredito
                $fecha = $items->fecha;
            }
            $data = [
                // 'id_solicitud_mat_alum' => $id_solicitud_mat_alum,
                'id_pago' => $id_pago,
                'id_dinamica' => '',
                'id_persona' => $id_persona,
                'id_tipoorigen' => '5',
                'id_moneda' => $id_moneda,
                'detalle' => $id_entidad . '-' . $prefix . '-' . $medio_pago . '-' . $numero . '-' . $fecha . '-' . $items->detalle,
                'importe' => $items->importe,
                'importe_me' => $items->importe_me,
                'fecha' => $fecha_reg,
            ];

            $idCajapgasto = DB::transaction(function () use ($data) {
                DB::table('eliseo.caja_pago_gasto')->insert($data);
                return DB::getSequence()->currentValue('SQ_CAJA_PAGO_GASTO_ID');
            });

            $asiento = DB::table('eliseo.caja_vale_gasto_asiento')->where('id_vgasto', '=', $items->id_vgasto)->select('*')->get();

            foreach ($asiento as $asie) {
                $val = (object)$asie;
                // $id_gasiento = ComunData::correlativo('eliseo.caja_pago_gasto_asiento', 'id_gasiento');
                $delta = [
                    // 'id_gasiento' => $id_gasiento,
                    'id_pgasto' => $idCajapgasto,
                    'id_cuentaaasi' => $val->id_cuentaaasi,
                    'id_restriccion' => $val->id_restriccion,
                    'id_ctacte' => $val->id_ctacte,
                    'id_fondo' => $val->id_fondo,
                    'id_depto' => $val->id_depto,
                    'importe' => $val->importe,
                    'importe_me' => $val->importe_me,
                    'descripcion' => $items->detalle,
                    'editable' => 'S',
                    'id_parent' => '',
                    'id_tiporegistro' => 'D',
                    'dc' => $val->dc,
                    'agrupa' => $val->agrupa,
                ];
                # code...
                $cajapgasto = DB::table('eliseo.caja_pago_gasto_asiento')->insert($delta);
            }

            $documento = DB::table('eliseo.caja_vale_gasto')->where('id_vgasto', '=', $items->id_vgasto)->update(['id_pgasto' => $idCajapgasto]);

            $cajaValeFile = DB::table('eliseo.caja_vale_file')->where('id_vfile', '=', $items->id_vfile)
                ->update(['id_pgasto' => $idCajapgasto, 'id_user' => $id_user]);

            # code...
        }
        $agregados = DB::table('eliseo.caja_pago_gasto')->where('id_pago', $id_pago)->select('*')->get();

        if (count($agregados) >= count($detalle)) {
            $result = [
                'success' => true,
                'message' => 'Proceso completado exitosamente',
                'data' => $agregados,
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se completo el proceso',
                'data' => [],
            ];
        }
        return $result;
    }

    public static function editSeatVale($id_asiento, $id_vale, $id_entidad, $data)
    {
        $query = DB::table('CAJA_VALE_ASIENTO')
            ->where('ID_ASIENTO', $id_asiento)
            ->update($data);
        if ($query) {
            $vquery = ExpensesData::listValesAccounting($id_vale, $id_entidad);
        }
        return $vquery;
    }

    public static function termCondVale($id_persona, $tipo)
    {
        $objP = DB::table('moises.persona as p')
            ->join('moises.persona_natural as pn', 'pn.id_persona', '=', 'p.id_persona')
            ->join('moises.tipo_documento as td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento')
            ->where('p.id_persona', $id_persona)
            ->select(
                'p.paterno',
                'p.materno',
                'p.nombre',
                'pn.firma',
                'pn.num_documento',
                'td.siglas'
            )->first();

        $firma_trabajador = '';
        if ($tipo == 'F') {
            $storage = new StorageController();
            $urlf = 'contract/personalinformation/' . $objP->firma;
            $firma_trabajador = ''; //$storage->getUrlByName($urlf);
        }
        $trabajdor = $objP->nombre . ' ' . $objP->paterno . ' ' . $objP->materno;
        $documento = $objP->siglas . ': ' . $objP->num_documento;
        $firma_responsable = '';
        $documentoresp = '';
        $responsable = '';
        $pdf = DOMPDF::loadView('pdf.treasury.term_cond_vale', [
            'firma_trabajador' => $firma_trabajador,
            'trabajdor' => $trabajdor,
            'documento' => $documento,
            'responsable' => $responsable,
            'firma_responsable' => $firma_responsable,
            'documentoresp' => $documentoresp
        ])->setPaper('a4', 'portrait');

        return $pdf;
    }

    public static function editTermConVale($id_vale, $file)
    {
        $query = DB::table('CAJA_VALE')
            ->where('id_vale', $id_vale)
            ->update(['term_cond_url' => $file]);

        return $query;
    }

}