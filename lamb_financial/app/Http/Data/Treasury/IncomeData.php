<?php

namespace App\Http\Data\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\Utils\StorageData;


class IncomeData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function listMedioPago($id_modulo)
    {
        $and_modulo = "";
        if ($id_modulo) {
            $and_modulo = "AND CODIGO_MODULO = $id_modulo";
        }
        $query = "SELECT 
                ID_MEDIOPAGO,NOMBRE,ACTIVO 
                FROM MEDIO_PAGO
                WHERE ACTIVO = '1' 
                $and_modulo
                ORDER BY ID_MEDIOPAGO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listFinancialEntities($estado)
    {
        if ($estado == "*") {
            $esta = "'1','0'";
        } else {
            $esta = "'" . $estado . "'";
        }
        $query = "SELECT 
                ID_BANCO,NOMBRE,SIGLA,CODIGO,ESTADO
                FROM CAJA_ENTIDAD_FINANCIERA
                WHERE ESTADO in (" . $esta . ") 
                ORDER BY ID_BANCO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showFinancialEntities($id_banco)
    {
        $query = "SELECT 
                            ID_BANCO,NOMBRE,SIGLA,ESTADO
                    FROM CAJA_ENTIDAD_FINANCIERA 
                    WHERE ID_BANCO = " . $id_banco . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addFinancialEntities($nombre, $sigla, $estado)
    {
        DB::table('CAJA_ENTIDAD_FINANCIERA')->insert(
            array('NOMBRE' => $nombre, 'SIGLA' => $sigla, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(TO_NUMBER(ID_BANCO)) ID_BANCO
                FROM CAJA_ENTIDAD_FINANCIERA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_banco = $item->id_banco;
        }
        $sql = IncomeData::showFinancialEntities($id_banco);
        return $sql;
    }

    public static function updateFinancialEntities($id_banco, $nombre, $sigla, $estado)
    {
        DB::table('CAJA_ENTIDAD_FINANCIERA')
            ->where('ID_BANCO', $id_banco)
            ->update([
                'NOMBRE' => $nombre,
                'SIGLA' => $sigla,
                'ESTADO' => $estado
            ]);
        $sql = IncomeData::showFinancialEntities($id_banco);
        return $sql;
    }

    public static function deleteFinancialEntities($id_banco)
    {
        DB::table('CAJA_ENTIDAD_FINANCIERA')->where('ID_BANCO', '=', $id_banco)->delete();
    }

    /*public static function listBankAccount($id_banco,$id_entidad,$id_depto){
        $query = "SELECT 
                ID_CTABANCARIA,ID_BANCO,ID_ENTIDAD,ID_ENTIDAD,ID_DEPTO,NOMBRE,CUENTA_CORRIENTE,ESTADO 
                FROM CAJA_CUENTA_BANCARIA
                WHERE ID_BANCO = '".$id_banco."'
                AND ID_ENTIDAD = ".$id_entidad."
                AND ID_DEPTO = '".$id_depto."'
                AND ESTADO = '1' ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }*/
    public static function listCardType()
    {
        $query = "SELECT 
                ID_TIPOTARJETA,NOMBRE,ESTADO 
                FROM CAJA_TIPOTARJETA
                WHERE ESTADO = '1'
                ORDER BY ID_TIPOTARJETA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDepositType()
    {
        $query = "SELECT 
                    ID_TIPOTRANSACCION AS ID_TIPOVENTA,NOMBRE 
                    FROM TIPO_TRANSACCION
                    WHERE TIPO = 'D'
                    AND ESTADO = '1'
                    ORDER BY ID_TIPOTRANSACCION ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function cashRegister($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona)
    {
        if ($id_mediopago == "000") {
            $pago = "";
        } else {
            $pago = "AND A.ID_MEDIOPAGO = '" . $id_mediopago . "' ";
        }
        if ($id_persona) {
            $person = "AND A.ID_PERSONA = $id_persona";
        } else {
            $person = "";
        }
        $query = "SELECT 
                        A.ID_DEPOSITO,D.NOMBRE as MEDIO_PAGO,A.ID_TIPOORIGEN,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_MEDIOPAGO,A.ID_PERSONA,A.ID_CLIENTE,A.ID_SUCURSAL,A.ID_VOUCHER,FC_CONTA_VOUCHER(A.ID_VOUCHER) AS VOUCHER,A.SERIE,A.NUMERO, to_char(a.fecha,'DD/MM/YYYY') FECHA ,
                        TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,A.IMPORTE as IMP_PDF,
                        A.GLOSA,A.ESTADO, 
                        B.EMAIL, 
                        C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO AS CLIENTE,

                        FC_CAJA_DEPOSITO_VENTA(A.ID_DEPOSITO) AS NUMERO_VENTA,
                        (CASE WHEN (SELECT count(A.id_deposito)
                                      FROM ARREGLO
                                      WHERE ARREGLO.ID_ORIGEN = A.ID_DEPOSITO AND ID_TIPOORIGEN = 7) > 0
                            THEN '1'
                           ELSE '0' END) AS ischangerequest,
                           DAVID.FT_CODIGO_UNIV(A.ID_CLIENTE) AS CODIGO,
                        NVL((SELECT DAVID.APELLIDO_PERSONA(MAX(X.ID_FINANCISTA)) FROM FIN_ASIGNACION X WHERE X.ID_ANHO = A.ID_ANHO AND X.ID_CLIENTE = A.ID_CLIENTE AND X.ESTADO = '1'),' ') AS FINANCISTA,
                        DAVID.FT_ESCUELA_ALUMNO(A.ID_CLIENTE) AS EP,
                        to_char(a.fecha,'hh24:mm:ss') hora
                        ,E.nombre CTA_BNK
                /*FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C, MEDIO_PAGO D
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_CLIENTE = C.ID_PERSONA
                AND A.ID_MEDIOPAGO = D.ID_MEDIOPAGO
                AND*/  
                FROM CAJA_DEPOSITO A
                inner join USERS B on A.ID_PERSONA = B.ID 
                inner join MOISES.PERSONA C on  A.ID_CLIENTE = C.ID_PERSONA
                inner join MEDIO_PAGO D on  A.ID_MEDIOPAGO = D.ID_MEDIOPAGO
                left join caja_cuenta_bancaria E on E.id_ctabancaria = A.id_ctabancaria
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = $id_voucher
                $pago
                $person
                ORDER BY A.SERIE,A.NUMERO,A.FECHA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function cashRegisterCajaPdf($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_persona)
    {         
        $query = "SELECT 
                        A.ID_DEPOSITO,
                        A.SERIE||'-'||A.NUMERO AS DEPOSITO, '138122203' AS COD_COMERCIO,A.GLOSA, E.CODIGO,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,A.IMPORTE as IMP_PDF,
                        C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO AS NOMBRE,
                        A.NRO_OPERACION,TO_CHAR(A.FECHA_OPERACION,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.FECHA,'HH12:MI:SS') AS HORA,
                        X.CUENTA,
                        B.EMAIL
                FROM CAJA_DEPOSITO A JOIN USERS B ON A.ID_PERSONA = B.ID 
                JOIN MOISES.PERSONA C ON A.ID_CLIENTE = C.ID_PERSONA
                JOIN MEDIO_PAGO D ON A.ID_MEDIOPAGO = D.ID_MEDIOPAGO
                LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON A.ID_CLIENTE = E.ID_PERSONA
                JOIN CONTA_ASIENTO X ON X.ID_TIPOORIGEN = A.ID_TIPOORIGEN AND X.ID_ORIGEN = A.ID_DEPOSITO AND X.VOUCHER = A.ID_VOUCHER AND X.IMPORTE < 0
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '". $id_depto ."'
                AND A.ID_ANHO =  $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_PERSONA = $id_persona
                AND A.ID_VOUCHER = $id_voucher
                ORDER BY A.SERIE,A.NUMERO,A.FECHA ";
        /*$query = "SELECT 
                        A.ID_DEPOSITO,
                        A.SERIE||'-'||A.NUMERO AS DEPOSITO, '138122203' AS COD_COMERCIO,A.GLOSA, E.CODIGO,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,A.IMPORTE as IMP_PDF,
                        C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO AS NOMBRE,
                        A.NRO_OPERACION,TO_CHAR(A.FECHA_OPERACION,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.FECHA,'HH12:MI:SS') AS HORA,
                        X.CUENTA,
                        B.EMAIL
                    FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C, MEDIO_PAGO D, MOISES.PERSONA_NATURAL_ALUMNO E, CONTA_ASIENTO X
                    WHERE A.ID_PERSONA = B.ID 
                    AND A.ID_CLIENTE = C.ID_PERSONA
                    AND A.ID_MEDIOPAGO = D.ID_MEDIOPAGO
                    AND A.ID_CLIENTE = E.ID_PERSONA
                    AND X.ID_TIPOORIGEN = A.ID_TIPOORIGEN 
                    AND X.ID_ORIGEN = A.ID_DEPOSITO 
                    AND X.VOUCHER = A.ID_VOUCHER 
                    AND X.IMPORTE < 0
                    AND A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '". $id_depto ."'
                    AND A.ID_ANHO =  $id_anho
                    AND A.ID_MES = $id_mes
                    AND A.ID_PERSONA = $id_persona
                    AND A.ID_VOUCHER = $id_voucher
                    ORDER BY A.SERIE,A.NUMERO,A.FECHA";*/
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function depositsCorrect($params)
    {
//        dd('get');
        $result = DB::table("CAJA_DEPOSITO")
            ->select(
                "CAJA_DEPOSITO.ID_DEPOSITO",
                "CAJA_DEPOSITO.SERIE",
                "CAJA_DEPOSITO.NUMERO",
                "CAJA_DEPOSITO.FECHA",
                "CAJA_DEPOSITO.GLOSA",
                "CAJA_DEPOSITO.ID_CLIENTE",
                "CAJA_DEPOSITO.IMPORTE",
                "ARREGLO.ID_ARREGLO",
                "ARREGLO.FECHA AS FECHA_MOD",
                "ARREGLO.MOTIVO AS MOTIVO_MOD",
                "ARREGLO.ID_PERSONA"
            )
            ->join("ARREGLO", "CAJA_DEPOSITO.ID_DEPOSITO", "ARREGLO.ID_ORIGEN")
//            ->join("PERSONA", "CAJA_DEPOSITO.ID_PERSONA", "PERSONA.ID_PERSONA")
            ->where("ARREGLO.ESTADO", "1")
            ->where("ARREGLO.ID_MODULO", "62")
            ->paginate(100);
        return $result;
    }


    public static function showVoucherDeposits($id_voucher, $id_mediopago)
    {
        if ($id_mediopago == "000" || !$id_mediopago) {

            $pago = "";
        } else {
            $pago = "AND A.ID_MEDIOPAGO = '" . $id_mediopago . "' ";
        }
//        dd('gett', $id_voucher); tambien debe filtrar por deposito
        $query = "SELECT CUENTA_N,CUENTA,DEPTO_N,DEPTO,SUM(DEBITO) AS DEBITO,SUM(CREDITO) AS CREDITO FROM (
                        SELECT FC_CUENTA_DENOMINACIONAL(B.CUENTA)                          AS CUENTA_N,
                               B.CUENTA,
                               FC_DEPARTAMENTO(DEPTO)                                      AS DEPTO_N,
                               B.DEPTO,
                               SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END)         DEBITO,
                               ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                        FROM CAJA_DEPOSITO A
                                 JOIN CONTA_ASIENTO B
                                      ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                                          AND A.ID_DEPOSITO = B.ID_ORIGEN
                        WHERE A.ID_VOUCHER = $id_voucher
                        $pago
                        GROUP BY B.CUENTA, B.DEPTO
                        UNION ALL
                        SELECT 
                                FC_CUENTA_DENOMINACIONAL(B.CUENTA)                          AS CUENTA_N,
                                B.CUENTA,
                                FC_DEPARTAMENTO(DEPTO)                                      AS DEPTO_N,
                                B.DEPTO,
                                SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END)         DEBITO,
                        ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                        FROM CAJA_CIERRE A JOIN CONTA_ASIENTO B
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                        AND A.ID_CIERRE = B.ID_ORIGEN
                        WHERE A.ID_VOUCHER = $id_voucher
                        GROUP BY B.CUENTA, B.DEPTO
                        UNION ALL
                        SELECT 
                                FC_CUENTA_DENOMINACIONAL(B.CUENTA)                          AS CUENTA_N,
                                B.CUENTA,
                                FC_DEPARTAMENTO(DEPTO)                                      AS DEPTO_N,
                                B.DEPTO,
                                SUM(CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END)         DEBITO,
                        ABS(SUM(CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                        FROM CAJA_DEPOSITO_BANCO A JOIN CONTA_ASIENTO B
                        ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                        AND A.ID_DEPBANCO = B.ID_ORIGEN
                        WHERE A.ID_VOUCHER = $id_voucher
                        $pago
                        GROUP BY B.CUENTA, B.DEPTO
                )
                GROUP BY CUENTA_N,CUENTA,DEPTO_N,DEPTO
                ORDER BY CUENTA, DEPTO ";
        $oQuery = DB::select($query);
//        dd($oQuery);
        return $oQuery;
    }

    public static function showVoucherDepositsCouting($id_voucher, $id_mediopago)
    {
        if ($id_mediopago == "000") {
            $pago = "";
        } else {
            $pago = "AND A.ID_MEDIOPAGO = '" . $id_mediopago . "' ";
        }
        $query = "SELECT SUM (DEBITO) AS DEBITO, SUM (CREDITO) AS CREDITO
                FROM (SELECT SUM (CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO,
                   ABS (SUM (CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END))
                      AS CREDITO
                FROM CAJA_DEPOSITO A
                   JOIN CONTA_ASIENTO B
                      ON     A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                         AND A.ID_DEPOSITO = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = $id_voucher
                UNION ALL
                SELECT SUM (CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO,
                   ABS (SUM (CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END))
                      AS CREDITO
                FROM CAJA_CIERRE A
                   JOIN CONTA_ASIENTO B
                      ON     A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                         AND A.ID_CIERRE = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = $id_voucher
                UNION ALL
                SELECT SUM (CASE WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO,
                   ABS (SUM (CASE WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END))
                      AS CREDITO
                FROM CAJA_DEPOSITO_BANCO A
                   JOIN CONTA_ASIENTO B
                      ON     A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                         AND A.ID_DEPBANCO = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = $id_voucher) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function depositsCorrectConfirm($idArreglo, $params)
    {
        $isCorrectProces = null;
        $deposits = DB::table("CAJA_DEPOSITO")
            ->select(
                "CAJA_DEPOSITO.ID_DEPOSITO",
                "CAJA_DEPOSITO.ID_TIPOORIGEN",
                "CAJA_DEPOSITO.SERIE",
                "CAJA_DEPOSITO.NUMERO", 
                "CAJA_DEPOSITO.IMPORTE",
                "CAJA_DEPOSITO.IMPORTE_ME",
                "CAJA_DEPOSITO.GLOSA",
                "ARREGLO.ID_ARREGLO"
            )
            ->join("ARREGLO", "CAJA_DEPOSITO.ID_DEPOSITO", "ARREGLO.ID_ORIGEN")
            ->where("ARREGLO.ESTADO", "1")
            ->where("ARREGLO.ID_ARREGLO", $idArreglo)
            ->get()->first();

        $idAsientos = DB::table("CONTA_ASIENTO")
            ->where("ID_ORIGEN", $deposits->id_deposito)
            ->where("ID_TIPOORIGEN", $deposits->id_tipoorigen)->pluck("id_asiento");
        $idDeposDetails = DB::table("CAJA_DEPOSITO_DETALLE")
            ->where("ID_DEPOSITO", $deposits->id_deposito)
            ->pluck("id_ddetalle");
        $idSalesTransfer = DB::table("VENTA_TRANSFERENCIA")
            ->where('id_deposito', $deposits->id_deposito)
            ->pluck('id_transferencia');
        $idSalesTransferDetail = DB::table('VENTA_TRANSFERENCIA_DETALLE')
            ->whereIn('ID_TRANSFERENCIA', $idSalesTransfer->toArray())
            ->pluck('id_tdetalle');

        if ($deposits and property_exists($deposits, 'id_deposito')) {
            if ($idAsientos->isNotEmpty()) {
                DB::table('CONTA_ASIENTO')
                    ->whereIn('ID_ASIENTO', $idAsientos->toArray())
                    ->update([
                        'IMPORTE' => 0,
                        'IMPORTE_ME' => 0,
                        'DESCRIPCION' => "<< ANULADO >>"
                    ]);
            }
            if ($idDeposDetails->isNotEmpty()) {
                DB::table('CAJA_DEPOSITO_DETALLE')
                    ->whereIn('ID_DDETALLE', $idDeposDetails->toArray())
                    ->update([
                        'IMPORTE' => 0,
                        'IMPORTE_ME' => 0
                    ]);
            }
            if ($idSalesTransfer->isNotEmpty()) {
                DB::table('VENTA_TRANSFERENCIA')
                    ->whereIn('ID_TRANSFERENCIA', $idSalesTransfer->toArray())
                    ->update([
                        'IMPORTE' => 0,
                        'IMPORTE_ME' => 0,
                        'GLOSA' => "<< ANULADO >>"
                    ]);
            }
            if ($idSalesTransferDetail->isNotEmpty()) {
                DB::table('VENTA_TRANSFERENCIA_DETALLE')
                    ->whereIn('ID_TDETALLE', $idSalesTransferDetail->toArray())
                    ->update([
                        'IMPORTE' => 0,
                        'IMPORTE_ME' => 0
                    ]);
            }

            DB::table('CAJA_DEPOSITO')
                ->where('ID_DEPOSITO', $deposits->id_deposito)
                ->update([
                    'IMPORTE' => 0,
                    'IMPORTE_ME' => 0,
                    'GLOSA' => "<< ANULADO >>"
                ]);


            DB::table('ARREGLO')
                ->where('ID_ARREGLO', $deposits->id_arreglo)
                ->update([
                    'ESTADO' => '2',
                    'INFO_BACKUP' => self::backupString($deposits, ['serie', 'numero','importe', 'glosa']),
                    'ID_PERSONA_UPDATE' => $params->id_user,
                    'FECHA_UPDATE' => DB::raw('sysdate')
                ]);
            $isCorrectProces = DB::table("ARREGLO")
                ->where("ARREGLO.ID_ARREGLO", $idArreglo)
                ->get()->first();
        }

        return $isCorrectProces;
    }

    public static function backupString($data, $properties)
    {
        $str = "";
        foreach ($properties as $prop) {
            $str .=strtoupper($prop).": ".$data->$prop.", ";
        }
        return $str;
    }

    public static function cashRegisterTotal($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona)
    {
        if ($id_mediopago == "000") {
            $pago = "";
        } else {
            $pago = "AND A.ID_MEDIOPAGO = '" . $id_mediopago . "' ";
        }
        if ($id_persona) {
            $person = "AND A.ID_PERSONA = $id_persona";
        } else {
            $person = "";
        }
        $query = "SELECT NVL((select NOMBRE from MEDIO_PAGO where MEDIO_PAGO.ID_MEDIOPAGO = A.ID_MEDIOPAGO),'TOTAL') AS MEDIO_PAGO,
                        A.ID_MEDIOPAGO,TO_CHAR(SUM(A.IMPORTE),'999,999,999.99') IMPORTE
                FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_CLIENTE = C.ID_PERSONA
                AND A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = $id_voucher
                $pago
                $person
                GROUP BY ROLLUP(A.ID_MEDIOPAGO)
                ORDER BY IMPORTE ASC,MEDIO_PAGO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function cashRegisterCajero($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona)
    {
        if ($id_mediopago == "000") {
            $pago = "";
        } else {
            $pago = "AND A.ID_MEDIOPAGO = '" . $id_mediopago . "' ";
        }
        if ($id_persona) {
            $person = "AND A.ID_PERSONA = $id_persona";
        } else {
            $person = "";
        }
        $query = "SELECT 
                        A.ID_PERSONA,
                        NVL(C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO,'TOTAL') CAJERO,
                        TO_CHAR(SUM(A.IMPORTE),'999,999,999.99') IMPORTE
                FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_PERSONA = C.ID_PERSONA
                AND A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = $id_voucher
                $pago
                $person
                GROUP BY ROLLUP(C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO), A.ID_PERSONA
                ORDER BY IMPORTE ASC,CAJERO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listArching($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        $query = "SELECT 
                NVL(CAJERO,'TOTAL') AS CAJERO,TO_CHAR(SUM(EFECTIVO),'999,999,999.99') AS EFECTIVO,TO_CHAR(SUM(BANCO),'999,999,999.99') AS BANCO,TO_CHAR(SUM(CREDITO),'999,999,999.99') AS CREDITO,TO_CHAR(SUM(IMPORTE),'999,999,999.99') AS TOTAL
                FROM (
                SELECT  
                            A.ID_PERSONA,C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO CAJERO,SUM(A.IMPORTE) IMPORTE,
                            DECODE(A.ID_MEDIOPAGO,'008',SUM(A.IMPORTE), 0) AS EFECTIVO,
                            DECODE(A.ID_MEDIOPAGO,'999',SUM(A.IMPORTE), 0) AS CREDITO,
                            DECODE(A.ID_MEDIOPAGO,'008',0,'999',0,SUM(A.IMPORTE)) AS BANCO
                FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_PERSONA = C.ID_PERSONA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                AND A.ID_VOUCHER = " . $id_voucher . "
                GROUP BY A.ID_PERSONA,C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO,A.ID_MEDIOPAGO
                )
                GROUP BY ROLLUP(CAJERO)
                ORDER BY TOTAL ASC,CAJERO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDepositImports($id_entidad, $id_depto, $id_user, $id_anho)
    {
        $query = "SELECT 
                        A.ID_DEPOSITO,C.NOM_PERSONA,C.NUM_DOCUMENTO,C.CODIGO,A.IMPORTE,A.GLOSA,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA, B.OPERACION
                FROM CAJA_DEPOSITO A, CAJA_DEPOSITO_BANCO B, MOISES.VW_PERSONA_NATURAL_ALUMNO C
                WHERE A.ID_DEPOSITO = B.ID_DEPOSITO
                AND A.ID_CLIENTE = C.ID_PERSONA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_PERSONA = " . $id_user . "
                AND A.ESTADO = 'X' 
                AND B.ESTADO = 'X'
                ORDER BY A.ID_DEPOSITO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function deleteDepositImports($id_entidad, $id_depto, $id_anho, $id_user)
    {
        DB::table('CAJA_DEPOSITO')
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->where('ID_DEPTO', '=', $id_depto)
            ->where('ID_ANHO', '=', $id_anho)
            ->where('ID_PERSONA', '=', $id_user)
            ->where('ESTADO', '=', 'X')->delete();

        DB::table('CAJA_DEPOSITO_BANCO')
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->where('ID_DEPTO', '=', $id_depto)
            ->where('ID_ANHO', '=', $id_anho)
            ->where('ID_PERSONA', '=', $id_user)
            ->where('ESTADO', '=', 'X')->delete();
    }

    public static function listBanKAccountType()
    {
        $query = "SELECT 
                          ID_TIPOCTABANCO,NOMBRE,CODIGO 
                    FROM TIPO_CTA_BANCO 
                    ORDER BY ID_TIPOCTABANCO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherCashClosing($id_entidad, $id_depto, $id_anho, $id_mes, $cerrado, $id_tipoasiento)
    {
        $having = "";
//        dd($cerrado);
        if ($cerrado and $cerrado === 'true') {
            $having = "HAVING A.IMPORTE = NVL(SUM(B.IMPORTE),0)";
        }
        if ($cerrado and $cerrado === 'false') {
            $having = "HAVING A.IMPORTE <> NVL(SUM(B.IMPORTE),0)";
        }
        $query = "SELECT 
                        A.ID_ENTIDAD,A.ID_DEPTO,A.ID_CIERRE,
                        A.ID_VOUCHER,A.NUMERO,A.LOTE,A.FECHA,
                        A.IMPORTE, A.IMPORTE_ME,

                        NVL(SUM(B.IMPORTE),0) AS IMP_BANK, 
                        NVL(SUM(B.IMPORTE_ME),0) AS IMP_BANK_ME
                FROM (
                        SELECT 
                                A.ID_ENTIDAD,A.ID_DEPTO,C.ID_CIERRE,
                                A.ID_VOUCHER,A.NUMERO,A.LOTE,
                                TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                                SUM(B.IMPORTE) AS IMPORTE, 
                                NVL(SUM(B.IMPORTE_ME),0) AS IMPORTE_ME
                        FROM CONTA_VOUCHER A 
                        LEFT JOIN CAJA_DEPOSITO B ON A.ID_VOUCHER = B.ID_VOUCHER
                        LEFT JOIN CAJA_CIERRE C ON A.ID_VOUCHER = C.ID_VOUCHER
                        WHERE A.ID_ENTIDAD = " . $id_entidad . "
                        AND A.ID_DEPTO = '" . $id_depto . "'
                        AND A.ID_ANHO = " . $id_anho . "
                        AND A.ID_MES = " . $id_mes . "
                        AND A.ID_TIPOASIENTO = '" . $id_tipoasiento . "' 
                        AND A.ID_TIPOVOUCHER = 5
                        --AND A.ACTIVO = 'S'
                        AND B.ID_MEDIOPAGO = '008' 
                        GROUP BY  A.ID_ENTIDAD,A.ID_DEPTO,C.ID_CIERRE,A.ID_VOUCHER,A.NUMERO,A.LOTE,A.FECHA
                ) A LEFT JOIN CAJA_DEPOSITO_BANCO B
                ON A.ID_CIERRE = B.ID_CIERRE
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_CIERRE,A.ID_VOUCHER,A.NUMERO,A.LOTE,A.FECHA,A.IMPORTE, A.IMPORTE_ME
                $having
                ORDER BY A.NUMERO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherCashClosingByIdCierre($id_cierre, $id_tipoasiento)
    {
        $query = "SELECT A.ID_ENTIDAD,
                       A.ID_DEPTO,
                       A.ID_CIERRE,
                       A.ID_VOUCHER,
                       A.NUMERO,
                       A.LOTE,
                       A.FECHA,
                       A.IMPORTE,
                       A.IMPORTE_ME,
                       NVL(SUM(B.IMPORTE), 0)    AS IMP_BANK,
                       NVL(SUM(B.IMPORTE_ME), 0) AS IMP_BANK_ME
                FROM (
                         SELECT A.ID_ENTIDAD,
                                A.ID_DEPTO,
                                C.ID_CIERRE,
                                A.ID_VOUCHER,
                                A.NUMERO,
                                A.LOTE,
                                TO_CHAR(A.FECHA, 'DD/MM/YYYY') AS FECHA,
                                SUM(B.IMPORTE)                 AS IMPORTE,
                                NVL(SUM(B.IMPORTE_ME), 0)      AS IMPORTE_ME
                         FROM CONTA_VOUCHER A
                                  LEFT JOIN CAJA_DEPOSITO B
                                            ON A.ID_VOUCHER = B.ID_VOUCHER
                                  LEFT JOIN CAJA_CIERRE C
                                            ON A.ID_VOUCHER = C.ID_VOUCHER
                         WHERE A.ID_TIPOASIENTO = '" . $id_tipoasiento . "' 
                        AND A.ID_TIPOVOUCHER = 5
                        --AND A.ACTIVO = 'S'
                        AND B.ID_MEDIOPAGO = '008' 
                        AND C.ID_CIERRE = $id_cierre
                         GROUP BY A.ID_ENTIDAD, A.ID_DEPTO, C.ID_CIERRE, A.ID_VOUCHER, A.NUMERO, A.LOTE, A.FECHA
                     ) A
                         LEFT JOIN CAJA_DEPOSITO_BANCO B
                                   ON A.ID_CIERRE = B.ID_CIERRE
                GROUP BY A.ID_ENTIDAD, A.ID_DEPTO, A.ID_CIERRE, A.ID_VOUCHER, A.NUMERO, A.LOTE, A.FECHA, A.IMPORTE, A.IMPORTE_ME";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showVoucherCashClosingDeposits($id_cierre)
    {
        $query = DB::table('CAJA_CIERRE')
            ->select(
                'CAJA_DEPOSITO_BANCO.ID_DEPBANCO',
                'CAJA_DEPOSITO_BANCO.FECHA',
                'CAJA_DEPOSITO_BANCO.OPERACION',
                'CAJA_DEPOSITO_BANCO.GLOSA',
                'CAJA_DEPOSITO_BANCO.ID_VOUCHER',
                'CAJA_DEPOSITO_BANCO.IMPORTE',
                'CAJA_DEPOSITO_BANCO.IMPORTE_ME'
            )
            ->join('CAJA_DEPOSITO_BANCO', 'CAJA_CIERRE.ID_CIERRE', '=', 'CAJA_DEPOSITO_BANCO.ID_CIERRE')
            ->where('CAJA_CIERRE.ID_CIERRE', $id_cierre)
            ->get();
        return $query;
    }
    public static function myCashRegister($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_persona)
    {
        $query = "SELECT 
                        A.ID_DEPOSITO,A.ID_MEDIOPAGO,A.SERIE||'-'||A.NUMERO AS DEPOSITO,
                        B.NOMBRE||' '||B.PATERNO||' '||B.MATERNO AS CLIENTE,
                        A.GLOSA,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.NRO_OPERACION,TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,TO_CHAR(A.IMPORTE_ME,'999,999,999.99') AS IMPORTE_ME
                FROM CAJA_DEPOSITO A LEFT JOIN MOISES.PERSONA B
                ON A.ID_CLIENTE = B.ID_PERSONA
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ID_MEDIOPAGO IN ('001','005','006')
                AND A.ID_PERSONA = " . $id_persona . "
                ORDER BY A.ID_DEPOSITO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function myCashRegisterTotal($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_persona)
    {
        $query = "SELECT 
                            A.ID_PERSONA,C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO CAJERO,TO_CHAR(SUM(A.IMPORTE),'999,999,999.99') IMPORTE
                FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_PERSONA = C.ID_PERSONA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ID_MEDIOPAGO IN ('001','005','006')
                AND A.ID_PERSONA = " . $id_persona . "
                GROUP BY A.ID_PERSONA,C.NOMBRE||' '||C.PATERNO||' '||C.MATERNO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function myCashRegisterPago($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_persona)
    {
        $query = "SELECT (SELECT NOMBRE FROM MEDIO_PAGO WHERE MEDIO_PAGO.ID_MEDIOPAGO = A.ID_MEDIOPAGO) AS MEDIO_PAGO,
                        A.ID_MEDIOPAGO,SUM(A.IMPORTE) IMPORTE
                FROM CAJA_DEPOSITO A, USERS B, MOISES.PERSONA C
                WHERE A.ID_PERSONA = B.ID 
                AND A.ID_CLIENTE = C.ID_PERSONA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ID_PERSONA = " . $id_persona . "
                GROUP BY A.ID_MEDIOPAGO
                ORDER BY MEDIO_PAGO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showDepositId($id_areglo){
        $query = "SELECT B.ID_DEPOSITO,B.IMPORTE,B.ID_CLIENTE,B.ID_ENTIDAD,B.ID_DEPTO,B.ID_ANHO
                FROM ARREGLO A JOIN CAJA_DEPOSITO B ON A.ID_ORIGEN = B.ID_DEPOSITO 
                WHERE A.ID_ARREGLO = ".$id_areglo."
                AND A.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAdvanceId($id_deposito){
        $query = "SELECT ID_TRANSFERENCIA FROM VENTA_TRANSFERENCIA WHERE ID_DEPOSITO = ".$id_deposito." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAdvanceTotal($id_entidad,$id_depto,$id_anho,$id_cliente){
        $query = "SELECT ID_CLIENTE, SUM(IMPORTE) AS TOTAL 
                    FROM VW_SALES_ADVANCES
                    WHERE ID_ENTIDAD = ".$id_entidad."
                    AND ID_DEPTO = '".$id_depto."'
                    AND ID_ANHO = ".$id_anho."
                    AND ID_CLIENTE = ".$id_cliente."
                    GROUP BY ID_CLIENTE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showSalesPayment($id_deposito){
        $query = "SELECT NVL(SUM(IMPORTE),0) AS PAGO 
        FROM CAJA_DEPOSITO_DETALLE 
        WHERE ID_DEPOSITO = ".$id_deposito." 
        AND ID_VENTA IS NOT NULL ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAdmissionByCode($codigo){
        $id_cliente = 0;
        $query = DB::table('CALEB.ADM_CODIGO_PAGO CP ')
        ->join('CALEB.ADM_PERSONA_INSCRIPCION PI', 'CP.ID_PERSONA_INSCRIPCION', '=', 'PI.ID_PERSONA_INSCRIPCION')
        ->join('CALEB.ADM_CONV_PLAN_PROGRAMA ACPP', 'PI.ID_CONV_PLAN_PROGRAMA', '=', 'ACPP.ID_CONV_PLAN_PROGRAMA')
        ->join('DAVID.ACAD_PLAN_PROGRAMA APP', 'ACPP.ID_PLAN_PROGRAMA', '=', 'APP.ID_PLAN_PROGRAMA')
        ->join('DAVID.VW_ACAD_PROGRAMA_ESTUDIO VAPE', 'APP.ID_PROGRAMA_ESTUDIO', '=', 'VAPE.ID_PROGRAMA_ESTUDIO')
        ->join('CALEB.ADM_MODALIDAD_INGRESO MI', 'PI.ID_MODALIDAD_INGRESO', '=', 'MI.ID_MODALIDAD_INGRESO')
        ->join('GENESIS.PERSONA P', 'PI.ID_PERSONA', '=', 'P.ID_PERSONA')
        ->join('CALEB.ADM_TIPO_CODIGO_PAGO ATCP', 'ATCP.ID_TIPO_CODIGO_PAGO', '=', 'CP.ID_TIPO_CODIGO_PAGO')
        ->leftJoin('CALEB.CATALOGO_ADMISION CA', 'CP.ID_CATALOGO', '=', 'CA.ID_CATALOGO')
        ->leftJoin('ELISEO.CONTA_DINAMICA CDI', 'CA.ID_DINAMICA', '=', 'CDI.ID_DINAMICA')
        ->leftJoin('GENESIS.PERSONA_DOCUMENTO PD', 'P.ID_PERSONA', '=', 'PD.ID_PERSONA')
        ->select(
            DB::raw(
                "
                CP.ID_CODIGO_PAGO,
                PI.ID_PERSONA_INSCRIPCION , 
                P.ID_PERSONA_LAMB AS ID_PERSONA,
                P.NOMBRE||' '||P.PATERNO|| ' '||P.MATERNO AS PERSONA,
                PD.NUM_DOCUMENTO,
                CP.VALOR AS CODIGO_PAGO,
                ATCP.NOMBRE  AS TIPO_CODIGO_PAGO,
                PI.CODIGO_POSTULANTE,
                CA.ID_CATALOGO,
                CA.NOMBRE AS NOMBRE_CATALOGO,
                CA.ID_DINAMICA,
                CDI.NOMBRE AS NOMBRE_DINAMICA,
                CDI.IMPORTE,
                CASE NVL(CP.ESTADO , '0') WHEN '0' THEN 'Sin pagar' WHEN '1' THEN 'Aprobado' WHEN '4' THEN 'Rechazado'  WHEN '2' THEN 'Pendiente de validación' WHEN '3' THEN 'Anulado' 
                WHEN '5' THEN 'Pagado'
                ELSE '' END AS ESTADO_PAGO,
                PI.ESTADO 
                "
            )
        )
        ->where("CP.VALOR", $codigo)->first();
        return $query;
    }
    public static function voucherDepositsStudentsPortal($request, $id_entidad, $id_depto)
    {
        $dd = DB::table('eliseo.caja_deposito_file as a');
        $dd->leftJoin('eliseo.medio_pago as b', 'a.id_mediopago', '=', 'b.id_mediopago');
        $dd->leftJoin('eliseo.CAJA_CUENTA_BANCARIA as c', 'c.id_ctabancaria', '=', 'a.id_ctabancaria');
        $dd->leftJoin('moises.persona as d', 'd.id_persona', '=', 'a.id_cliente');
        if (!empty($request->id_voucher)) {
            $dd->leftJoin('eliseo.caja_deposito as e', 'e.id_deposito', '=', 'a.id_deposito');
            $dd->leftJoin('eliseo.conta_voucher as f', 'f.id_voucher', '=', 'e.id_voucher');
            $dd->where('e.id_voucher', '=', $request->id_voucher);
        
        }
        $dd->where([
            'a.id_entidad' => $id_entidad,
            'a.id_depto' => $id_depto,
            'a.id_anho' => $request->id_anho,
            'a.estado' => $request->estado,
        ]);
        if (!empty($request->id_banco)) {
            $dd->where('a.id_banco', '=', $request->id_banco);
        }
        if (!empty($request->es_ctapuente)) {
            $dd->where('a.es_ctapuente', '=', $request->es_ctapuente);
        }
        $dd->select('a.id_dfile', 'a.id_anho', 'a.id_deposito', 'a.id_mediopago', 'a.id_ctabancaria', 'a.fecha', 'a.id_cliente',
        'a.nro_operacion', 'a.fecha_operacion', 'a.importe', 'a.nombre_file', 'a.url', 'a.estado', 'b.nombre as nombre_mediopago',
        'c.nombre as nombre_ctabancaria', 'c.cuenta_corriente', 'a.id_banco', DB::raw("eliseo.FC_CODIGO_ALUMNO(a.id_cliente) as codigo_alumno"),
        'es_ctapuente', DB::raw("ELISEO.FC_DOCUMENTO_CLIENTE(a.id_cliente) as num_documento, (d.paterno|| ' ' ||d.materno|| ' ' ||d.nombre) as nom_persona"));
        $dd->orderBy('a.id_dfile', 'asc');
        $data = $dd->get();
        return $data;
    }
    // public static function accountsBank($request, $id_entidad, $id_depto){
    //     $query = "SELECT 
    //                     A.ID_CTABANCARIA,
    //                     A.ID_BANCO,
    //                     A.ID_ENTIDAD,
    //                     A.ID_DEPTO,
    //                     A.ID_MONEDA,
    //                     A.ID_TIPOPLAN,
    //                     A.ID_CUENTAAASI,
    //                     A.ID_TIPOCTACTE,
    //                     A.NOMBRE,
    //                     A.CUENTA_CORRIENTE,
    //                     A.ESTADO,
    //                     B.NOMBRE AS BANCO,
    //                     B.SIGLA AS SIGLA
    //             FROM CAJA_CUENTA_BANCARIA A, CAJA_ENTIDAD_FINANCIERA B
    //             WHERE A.ID_BANCO = B.ID_BANCO
    //             AND A.ID_ENTIDAD = ".$id_entidad."
    //             AND A.ID_DEPTO = ".$id_depto."
    //             AND A.ESTADO = '1' ";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }
    public static function viewFileDepositos($request){
        $directory = $request->directory;
        $result = StorageData::viewFileMinio($directory);
        return $result;
    }
    public static function updateDepositsVouchesEstudents($id_dfile, $request, $id_user, $fecha_reg){
        $data = DB::table('ELISEO.CAJA_DEPOSITO_FILE')->where('id_dfile', '=', $id_dfile)->update([
            'estado' => $request->estado,
            'id_mediopago' => $request->id_mediopago,
            'id_ctabancaria' => $request->id_ctabancaria,
            'nro_operacion' => $request->nro_operacion,
            'fecha_operacion' => $request->fecha_operacion,
            'importe' => $request->importe,
            'id_banco' => $request->id_banco,
            'es_ctapuente' => $request->es_ctapuente
        ]);
        if ($data) {
    
            $dataProcess = [
                'id_dfile' => $id_dfile,
                'id_persona' => $id_user,
                'fecha' => $fecha_reg,
                'comentario' => $request->cometario,
                'estado' => $request->estado
            ];
    
            $save = DB::table('eliseo.caja_deposito_file_proceso')->insert($dataProcess);

            if ($save) {
                $response = [
                    'success' => true,
                    'message' => 'Registrado',
                    'data' => $save
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se pudo registrar',
                    'data' => $save
                ];
            }
           
        } else {
            $response = [
                'success' => false,
                'message' => 'No se pudo registrar',
                'data' => ''
            ];
        }
        return $response;
    }
    public static function listBank($request, $id_entidad, $id_depto){
        $query = "SELECT DISTINCT A.ID_BANCO, A.NOMBRE
        FROM ELISEO.CAJA_ENTIDAD_FINANCIERA A JOIN ELISEO.CAJA_CUENTA_BANCARIA B ON A.ID_BANCO = B.ID_BANCO
        WHERE B.ID_ENTIDAD = ".$id_entidad." AND B.ID_DEPTO = '".$id_depto."'
        AND A.ESTADO = '1'
       -- AND A.GTH = '1'
        ORDER BY A.NOMBRE";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getPrrocess($id_dfile){
        $arrayEstados = array(
            array('nombre' => 'Registrado', 'estado' => '0'),
            array('nombre' => 'Verificado', 'estado' => '1'),
            array('nombre' => 'Ejecutado', 'estado' => '2'),
            array('nombre' => 'Rechazado', 'estado' => '3')
        );
        $array = array();
        foreach ($arrayEstados as $value) {
           $items = (object)$value;
           $por = self::proceso($id_dfile, $items->estado);
           $items->id_dproceso = $por ? $por->id_dproceso : '';
           $items->id_persona = $por ? $por->id_persona : '';
           $items->fecha = $por ? $por->fecha : '';
           $items->comentario = $por ? $por->comentario : '';
           $items->estado = $por ? $por->estado : $items->estado;
           $items->email = $por ? $por->email : '';
           array_push($array, $items);
        }
        return $array;
    }
    public static function proceso($id_dfile, $estado){
        $objet = DB::table('eliseo.caja_deposito_file_proceso as a')->join('eliseo.users as b', 'a.id_persona', '=', 'b.id')
        ->where('a.id_dfile', '=', $id_dfile)
        ->where('a.estado', '=', $estado)
        ->select('a.id_dproceso', 'a.id_dfile', 'a.id_persona', DB::raw("to_char(a.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha"), 'a.comentario', 'a.estado',
            'b.email')
        ->first();
        return $objet;
    }
    public static function updateFinishDepositsVouchesEstudents($id_dfile, $estado, $id_deposito, $comentario, $id_user, $fecha_reg)
    {
        $data = DB::table('ELISEO.CAJA_DEPOSITO_FILE')->where('id_dfile', '=', $id_dfile)->update([
            'estado' => $estado,
            'id_deposito' => $id_deposito
        ]);
        if ($data) {
    
            $dataProcess = [
                'id_dfile' => $id_dfile,
                'id_persona' => $id_user,
                'fecha' => $fecha_reg,
                'comentario' => $comentario,
                'estado' => $estado
            ];
    
            $save = DB::table('eliseo.caja_deposito_file_proceso')->insert($dataProcess);

            if ($save) {
                $response = [
                    'success' => true,
                    'message' => 'Registrado',
                    'data' => $save
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se pudo registrar',
                    'data' => $save
                ];
            }
           
        } else {
            $response = [
                'success' => false,
                'message' => 'No se pudo registrar',
                'data' => ''
            ];
        }
        return $response;
    }
    public static function getCajeroVouchers($id_user){
        $sql = "SELECT 
        DISTINCT C.ID_VOUCHER, C.ID_TIPOASIENTO,C.LOTE,C.NUMERO,C.FECHA 
        FROM CAJA_DEPOSITO_FILE A 
        JOIN CAJA_DEPOSITO B ON A.ID_DEPOSITO = B.ID_DEPOSITO
        JOIN CONTA_VOUCHER C ON B.ID_VOUCHER = C.ID_VOUCHER
        WHERE B.ID_PERSONA = ".$id_user."";
        $query = DB::select($sql);
        return $query;
    }
    public static function nextOrRefusedPaso($id_dfile, $request, $id_user, $fecha_reg)
    {
        $data = DB::table('ELISEO.CAJA_DEPOSITO_FILE')->where('id_dfile', '=', $id_dfile)->update([
            'estado' => $request->estado
        ]);
        if ($data) {
    
            $dataProcess = [
                'id_dfile' => $id_dfile,
                'id_persona' => $id_user,
                'fecha' => $fecha_reg,
                'comentario' => $request->comentario,
                'estado' => $request->estado
            ];
    
            $save = DB::table('eliseo.caja_deposito_file_proceso')->insert($dataProcess);

            if ($save) {
                $response = [
                    'success' => true,
                    'message' => 'Registrado',
                    'data' => $save
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se pudo registrar',
                    'data' => $save
                ];
            }
           
        } else {
            $response = [
                'success' => false,
                'message' => 'No se pudo registrar',
                'data' => ''
            ];
        }
        return $response;
    }
}
