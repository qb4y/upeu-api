<?php

/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07/03/20
 * Time: 08:58 PM
 */

namespace App\Http\Data\FinancialEnrollment;

use App\Http\Data\Accounting\Setup\PrintData;
use App\Http\Data\Sales\SalesData;
use Illuminate\Support\Facades\DB;

class ProformaPaymentTicket
{

    public static function addDepositParametersHead($id_deposito, $id_user, $id_documento)
    {
        $query = "SELECT 
                    '      RECIBO DE PAGO' AS NOMBRE,
                    'NUMERO  : '||SERIE||'-'||NUMERO AS NUMERO,
                    'FECHA  : '||TO_CHAR(FECHA,'DD/MM/YYYY')||' '||TO_CHAR(FECHA,'HH24:MI:SS') AS FECHA,
                    'CLIENTE : '||FC_NOMBRE_PERSONA(ID_CLIENTE) AS CLIENTE,
                    'DNI   : '||FC_DOCUMENTO_CLIENTE(ID_CLIENTE) AS DOCUMENTO,
                    'DIRECCION: '|| pkg_sales.FC_CLIENTE_DIRECCION(ID_CLIENTE) AS DIRECCION,
                    '----------------------------------------' AS LINE
                    FROM CAJA_DEPOSITO
                    WHERE ID_DEPOSITO = " . $id_deposito . "
                    AND ESTADO = '1' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "NOMBRE" => $item->nombre,
                "NUMERO" => $item->numero,
                "FECHA" => $item->fecha,
                "CLIENTE" => $item->cliente,
                "DOCUMENTO" => $item->documento,
                "DIRECCION" => $item->direccion,
                "LINEA1" => $item->line,
                "LINEA2" => $item->line
            );
        }

        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user, $id_documento, '" . $clave . "','" . $valor . "',0)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }


    protected static function addDepositParametersBody($id_deposito, $id_user, $id_documento)
    {
        $cont = 0;
        $query = "SELECT
                '1' AS ITEM, 
                SUBSTR('Doc.',1,20)||' '||A.GLOSA AS DETALLE,
                '1' AS CANTIDAD,
                LPAD(TRIM(TO_CHAR(B.IMPORTE,'999,999,999.99')),10,' ') AS IMPORTE
                FROM CAJA_DEPOSITO A JOIN CAJA_DEPOSITO_DETALLE B
                ON A.ID_DEPOSITO = B.ID_DEPOSITO
                LEFT JOIN VW_SALES_SALDO C
                ON B.ID_VENTA = C.ID_VENTA
                WHERE A.ID_DEPOSITO = " . $id_deposito . "
                AND ESTADO = '1'
                ORDER BY B.ID_DDETALLE";

        $oQuery = DB::select($query);

        foreach ($oQuery as $item) {
            $params = array(
                "CANT" => $item->cantidad,
                "GLOSA" => $item->detalle,
                "IMPORTE" => $item->importe
            );
            foreach ($params as $clave => $valor) {
                $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user, $id_documento,'" . $clave . "','" . $valor . "',$cont)
                        WHERE ID_PERSONA = $id_user ";
                DB::update($sql);
            }
            $cont++;
            $params = [];
        }

        if ($cont == 0) {
            $query = "SELECT
                    '1' AS ITEM, 
                    trim(replace(replace(replace(a.glosa,'7124-6-',''),'7124-6:',''),'Oper','Op')) AS DETALLE,
                    '1' AS CANTIDAD,
                    LPAD(TRIM(TO_CHAR(A.IMPORTE,'999,999,999.99')),10,' ') AS IMPORTE
                    FROM CAJA_DEPOSITO A 
                    WHERE A.ID_DEPOSITO = " . $id_deposito . "
                    AND ESTADO = '1' ";

            $oQuery = DB::select($query);

            foreach ($oQuery as $item) {
                $params = array(
                    "CANT" => $item->cantidad,
                    "GLOSA" => $item->detalle,
                    "IMPORTE" => $item->importe
                );
                foreach ($params as $clave => $valor) {
                    $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user, $id_documento,'" . $clave . "','" . $valor . "',$cont)
                            WHERE ID_PERSONA = $id_user ";
                    DB::update($sql);
                }
                $cont++;
                $params = [];
            }
        }
        return $cont;
    }


    public static function addDepositParametersFoot($id_deposito, $id_user, $id_documento, $cont)
    {
        $query = "SELECT 
                lpad('IMPORTE DEPOSITO S/.',19,' ')||lpad(trim(to_char(IMPORTE,'999,999,990.99')),20,' ') AS TOTAL,
                'Son: '||FC_NUMERO_TEXTO(IMPORTE)||' Soles' AS NUMTXT,
                'Cajero : '||FC_NOMBRE_PERSONA(ID_PERSONA) AS CAJERO,
                '----------------------------------------' AS LINE
                FROM CAJA_DEPOSITO
                WHERE ID_DEPOSITO = " . $id_deposito . "
                AND ESTADO = '1'";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "LINEA3" => $item->line,
                "TOTAL" => $item->total,
                "LINEA4" => $item->line,
                "NUMTXT" => $item->numtxt,
                "CAJERO" => $item->cajero,
                "LINEA5" => $item->line
            );
        }

        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }



    protected static function addSalesParametersBody($id_venta, $id_user, $id_documento)
    {
        $cont = 0;
        /*$query = "SELECT '1' as item, a.GLOSA as detalle, '1' as cantidad,
                LPAD(TRIM(TO_CHAR(a.TOTAL,'999,999,999.99')),10,' ') AS IMPORTE
                FROM VENTA A where A.ID_VENTA = ".$id_venta." AND A.ESTADO = 1";*/

        $query = "SELECT '1' AS ITEM, A.DETALLE AS DETALLE, '1' AS CANTIDAD,
        LPAD(TRIM(TO_CHAR(A.IMPORTE,'999,999,999.99')),10,' ') AS IMPORTE
        FROM VENTA_DETALLE A WHERE A.ID_VENTA  = " . $id_venta . " ";

        $oQuery = DB::select($query);

        foreach ($oQuery as $item) {
            $params = array(
                "CANT" => $item->cantidad,
                "GLOSA" => $item->detalle,
                "IMPORTE" => $item->importe
            );
            foreach ($params as $clave => $valor) {
                $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user, $id_documento,'" . $clave . "','" . $valor . "',$cont)
                        WHERE ID_PERSONA = $id_user ";
                DB::update($sql);
            }
            $cont++;
            $params = [];
        }
        return $cont;
    }


    protected static function printTicket($id_user, $ip, $service_port)
    {
        $result = false;
        $etiq = false;
        try {
            $data = PrintData::listDocumentsPrints($id_user);
            $texto = "";
            $texto .= chr(27);
            $texto .= chr(15);
            $x = "\n";
            $y = "~";
            $nueva_data = str_replace($y, $x, $data);
            $texto .= $nueva_data;
            $texto = $texto . "" . "\n\n\n\n\n\n\n\n";
            $texto .= chr(27);
            $texto .= chr(105);
            $body = $texto;
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0) die("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: " . socket_strerror($socket));
            $result = socket_connect($socket, $ip, $service_port);
            $etiq = true;
            if ($result < 0) die("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: " . socket_strerror($result));
            socket_write($socket, $body, strlen($body));
            socket_close($socket);
            if ($etiq) {
                if ($result) {
                    $msn = "Ok";
                } else {
                    $msn = " OK pero no hay conexion a la ticketera ";
                }
            } else {
                $msn = "no hay cx";
            }
        } catch (\Exception $e) {
            if ($etiq) {
                if ($result) {
                    $msn = "Ok pero con problemas";
                } else {
                    $msn = " OK pero no hay conexion a la ticketera ";
                }
            } else {
                $msn = "Error en la Estrucutra de Impresion";
            }
        }
        return $msn;
    }

    // Genera ticket al genera contrato
    public static function ticket($params, $user)
    {
        $response = array();
        $id_comprobante = $params['id_comprobante'];
        $id_venta = $params['id_venta'];
        $id_depto = $user['id_depto'];
        $id_user = $user['id_user'];
        $id_entidad = $user['id_entidad'];

        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user, $id_comprobante, null);

        if (count($data) === 0) {
            $response['success'] = false;
            $response['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
            $response['data'] = null;
            $response['code'] = 202;
        } elseif (count($data) > 1) {
            $response['success'] = false;
            $response['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
            $response['data'] = NULL;
            $response['code'] = 202;
        } elseif (count($data) === 1) {
            $id_documento = $data[0]->id_documento;
            $ip = $data[0]->ip;
            $service_port = $data[0]->puerto;

            try {

                PrintData::deletePrint($id_user);
                PrintData::deleteTemporal($id_user);
                PrintData::addDocumentsPrints($id_user, 1, "x");
                PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento, 'H', 0);
                SalesData::addSalesParametersHead($id_venta, $id_user, $id_documento);
                $cont = self::addSalesParametersBody($id_venta, $id_user, $id_documento);
                SalesData::addSalesParametersFoot($id_venta, $id_user, $id_documento, $cont);
                $cont = PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento, 'F', $cont);
                $msn = self::printTicket($id_user, $ip, $service_port);
                $response['success'] = true;
                $response['message'] = "The item was updated successfully " . "(Impresion: " . $msn . ")";
                $response['data'] = $data[0];
                $response['code'] = 200;
            } catch (\Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
                $response['data'] = [];
                $response['code'] = 202;
            }
        }

        return $response;
    }


    // Genera ticket al realizar un deposito en matricula
    public static function ticketDeposito($params, $user)
    {
        $response = array();
        $id_comprobante = '00'; //  TIPO DEPOSITO
        $id_deposito = $params['id_deposito'];
        $id_depto = $user['id_depto'];
        $id_user = $user['id_user'];
        $id_entidad = $user['id_entidad'];


        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user, $id_comprobante, '');

        if (count($data) === 0) {
            $response['success'] = false;
            $response['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
            $response['data'] = null;
            $response['code'] = 202;
        } elseif (count($data) > 1) {
            $response['success'] = false;
            $response['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
            $response['data'] = NULL;
            $response['code'] = 202;
        } elseif (count($data) === 1) {
            $id_documento = $data[0]->id_documento;
            $ip = $data[0]->ip;
            $service_port = $data[0]->puerto;

            try {

                PrintData::deletePrint($id_user);
                PrintData::deleteTemporal($id_user);
                PrintData::addDocumentsPrints($id_user, 1, "x");
                PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento, 'H', 0);
                self::addDepositParametersHead($id_deposito, $id_user, $id_documento);
                $cont = self::addDepositParametersBody($id_deposito, $id_user, $id_documento);
                self::addDepositParametersFoot($id_deposito, $id_user, $id_documento, $cont);
                $cont = PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento, 'F', $cont);
                $msn = self::printTicket($id_user, $ip, $service_port);
                $response['success'] = true;
                $response['message'] = "The item was updated successfully " . "(Impresion: " . $msn . ")";
                $response['data'] = $data[0];
                $response['code'] = 200;
            } catch (\Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
                $response['data'] = [];
                $response['code'] = 202;
            }
        }

        return $response;
    }
}
