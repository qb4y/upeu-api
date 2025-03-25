<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 27/02/20
 * Time: 03:43 PM
 */

namespace App\Http\Data\FinancialEnrollment;

use App\Http\Data\Accounting\Setup\PrintData;
use App\Http\Data\Sales\SalesData;
use Illuminate\Support\Facades\DB;
use PDO;
class ProceduresDiscounts
{

    protected static function printTranferenc($id_user,$ip,$service_port){
        $result = false;
        $etiq = false;
        try{
            //$service_port = 7654;
            $data = PrintData::listDocumentsPrints($id_user);
            $texto="";
            $texto.=chr(27);
            $texto.=chr(15);
            $x="\n";
            $y="~";
            $nueva_data=str_replace($y,$x,$data);
            $texto.=$nueva_data;
            $texto=$texto.""."\n\n\n\n\n\n\n\n";
            $texto.=chr(27);
            $texto.=chr(105);
            $body = $texto;
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($socket));
            $result = socket_connect($socket, $ip, $service_port);
            $etiq = true;
            if ($result < 0) die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . "Reason: ". socket_strerror($result));
            socket_write($socket, $body, strlen($body));
            socket_close($socket);
            if($etiq){
                if($result){
                    $msn = "Ok";
                }else{
                    $msn = " OK pero no hay conexion a la ticketera ";
                }
            }else{
                $msn = "no hay cx";
            }
        }catch(\Exception $e){
            if($etiq){
                if($result){
                    $msn = "Ok pero con problemas";
                }else{
                    $msn = " OK pero no hay conexion a la ticketera ";
                }
            }else{
                $msn = "Error en la Estrucutra de Impresion";
            }
        }
        return $msn;
    }

    protected static function imprimeTransferencias($params) {

        $response = array('success' => false, 'message'=> null);
        $id_transferencia = $params['id_transferencia'];
        $id_user = $params['id_user'];
        $id_documento = $params['id_documento'];
        $ip = $params['ip'];
        $service_port = $params['port'];

        try{
            PrintData::deletePrint($id_user);//TODOS
            PrintData::deleteTemporal($id_user);//TODOS
            PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
            PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento,'H',0);//TODOS
            SalesData::addTransfParametersHead($id_transferencia, $id_user, $id_documento); // new X
            $cont = SalesData::addTransfParametersBody($id_transferencia, $id_user, $id_documento); // new X
            SalesData::addTransfParametersFoot($id_transferencia, $id_user, $id_documento, $cont); //new
            PrintData::addDocumentsPrintsFixedParameters($id_user, $id_documento,'F', $cont);//TODOS
            $msn = self::printTranferenc($id_user, $ip, $service_port); // TODOS

            $response['success'] = true;
            $response['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";

        }catch (\Exception $e){
            $response['success'] = false;
            $response['message'] = $e->getMessage().' - (Error en imprime transferencia)';
        }

        return $response;

    }

    protected static function runTransferencias($user , $id_alumno_contrato) {

        $response = array('success' => false, 'message'=> null);
        $id_entidad = $user['id_entidad'];
        $id_depto = $user['id_depto'];
        $id_user = $user['id_user'];
        $id_comprobante = '99';
        $id_documento = null;
        $ip = null;
        $service_port = null;


        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user, $id_comprobante, '');

        if (count($data) === 0) {
            $response['success'] = false;
            $response['message'] = "Alto: Debe asignarse un punto de impresion para el documento [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";

        } elseif (count($data) > 1) {
            $response['success'] = false;
            $response['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante]. En la entidad: $id_entidad y depto: $id_depto";
        } elseif (count($data) == 1) {
            $id_documento = $data[0]->id_documento;
            $ip = $data[0]->ip;
            $service_port = $data[0]->puerto;

            $transferencias = self::getTransferencia($id_alumno_contrato);

            $error = false;
            $errormsg = null;

            foreach ($transferencias as $key => $id_transferencia) {

                $params = array(
                    'id_transferencia' => $id_transferencia,
                    'id_user' => $id_user,
                    'id_documento' => $id_documento,
                    'ip' => $ip,
                    'port' => $service_port);

                $x = self::imprimeTransferencias($params);

                if ($x['success'] == false) {
                    $error = true;
                    $errormsg = $x['message'];
                    break;
                }

            }

            if ($error) {
                $response['success'] = false;
                $response['message'] = $errormsg;
            } else {
                $response['success'] = true;
                $response['message'] = 'Impresion correcta !! transferencias';
            }


        } else {
            $response['success'] = false;
            $response['message'] = 'Error en showIPDocumentUserPrint';
        }

        return $response;
    }

    protected static function getCursosAplazados($id_alumno_contrato) {
        $query = "select distinct a.ID_VENTA from VENTA_DETALLE a
                where a.ID_ALUMNO_CONTRATO_DET in (
                    select y.ID_ALUMNO_CONTRATO_DET
                    from MAT_ALUMNO_CONTRATO_DET y where y.ID_ALUMNO_CONTRATO=$id_alumno_contrato and y.APLAZADO = 'S')
                and a.ID_ALUMNO_CONTRATO = $id_alumno_contrato";
        $q = DB::select($query);
        return collect($q)->pluck('id_venta')->toArray();
    }

    protected static function getTransferencia($id_alumno_contrato) {
        $query = "select distinct ID_TRANSFERENCIA
              from VENTA_TRANSFERENCIA_DETALLE where ID_ALUMNO_CONTRATO=$id_alumno_contrato";
        $q = DB::select($query);
        return collect($q)->pluck('id_transferencia')->toArray();
    }

    protected static function isBeca18($id_persona) {
        return DB::table('DAVID.ACAD_ALUMNO_BECA a')
            ->where([
                ['a.ID_PERSONA','=', $id_persona],
                ['a.ESTADO','=', 1],
            ])
            ->whereNotNull('a.ID_TIPO_BECA_ESTATAL')
            ->exists();
    }

    public static function generateTicket($id_comprobante, $id_venta, $user) {
        $request = array();
        $request['id_comprobante'] = $id_comprobante;
        $request['id_venta'] = $id_venta;
        return ProformaPaymentTicket::ticket($request, $user);
    }

    public static function generateTicketDeposito($id_deposito, $user) {
        $request = array();
        $request['id_deposito'] = $id_deposito;
        return ProformaPaymentTicket::ticketDeposito($request, $user);
    }

    protected static function beca() {
        $tipo_alumno = null;
        $nerror=0;
        $msgerror="";
        $return = [
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        return $return;
    }


    // IMPRIME CURSOS APLAZADOS CASO BECA 18
    protected static function imprimeCursosAplazados($id_alumno_contrato, $id_comprobante, $user) {

        $data = array('success' => false, 'message'=> null);
        $cursosAplazados = self::getCursosAplazados($id_alumno_contrato);
        $error = false;
        $errormsg = null;

        foreach ($cursosAplazados as $key => $id_venta) {
            $ticket = self::generateTicket($id_comprobante, $id_venta, $user);
            if ($ticket['success'] == false) {
                $error = true;
                $errormsg = $ticket['message'];
                break;
            }
        }

        if ($error == true) {
            $data['success'] = false;
            $data['message'] = $errormsg;
        } else {
            $data['success'] = true;
            $data['message'] = 'Impreme cursos aplazados correcto';
        }


        return $data;
    }

    // IMPRIME BECA 18
    protected static function chunkScholarship($user, $id_venta, $id_alumno_contrato, $id_cliente, $id_comprobante) {

        $data = array('success'=> false, 'message'=> null);
        $isBeca = self::isBeca18($id_cliente);

        if ($isBeca == true) {

            // imprime transferencias
            $transf = self::runTransferencias($user, $id_alumno_contrato);

            // imprime cursos aplazados
            $cursosApla = self::imprimeCursosAplazados($id_alumno_contrato, $id_comprobante, $user);

            if ($transf['success'] == true && $cursosApla['success']) {
                $data['success'] = true;
                $data['message'] = 'Imprimir transferencias y cursos aplazados correcto';
            } else {
                $data['success'] = false;
                $data['message'] = 'Error al imprimir ('.$transf['message'].' y '.$cursosApla['message'].')';
            }
            // FIN


        } else {

            if ($id_comprobante == '99') { // ES TRANSFERENCIA

                // imprime transferencias
                $transf = self::runTransferencias($user, $id_alumno_contrato);

                if ($transf['success'] == true) {
                    $data['success'] = true;
                    $data['message'] = $transf['message'];
                } else {
                    $data['success'] = false;
                    $data['message'] = $transf['message'];
                }

                // FIN

            } else {
                $ticket =  self::generateTicket($id_comprobante, $id_venta, $user);
                if ($ticket['success'] == true) {
                    $data['success'] = true;
                    $data['message'] = $ticket['message'];
                } else {
                    $data['success'] = false;
                    $data['message'] = $ticket['message'];
                }

                // FIN
            }
        }

        return $data;

    }

    protected static function generarDeposito($data) {

        $nerror=0;
        $msgerror="";
        $id_deposito = null;

        if ($data) {

            for($x=1;$x<=200;$x++){
                $msgerror .= "0";
            }
            try {

                $id_entidad = $data['id_entidad']; //ok*
                $id_depto = $data['id_depto']; //ok*
                $id_anio = $data['id_anio'];//ok*
                $id_mes = $data['id_mes']; //ok*
                $id_mediopago = $data['id_mediopago']; //ok se select*
                $id_persona = $data['id_persona']; //ok*
                $id_cliente = $data['id_cliente']; //ok getEntDepStudent*
                $automatico = 'S';
                $ventas = null; // *
                $imp_ventas = null; // *
                $id_tipotransaccion = $data['id_tipotransaccion']; //ok de query*
                $id_moneda = $data['id_moneda']; // ok select*
                $id_dinamica = $data['id_dinamica']; //ok de query*
                $id_tipotarjeta = $data['id_tipotarjeta']; //ok de select*
                $id_ctabancaria = $data['id_ctabancaria']; //ok de query*
                $operacion = $data['operacion']; //ok input*
                $fecha_op = $data['fecha_op']; // ok input*
                $importe = $data['importe']; //*
                $importe_tarjeta = $data['importe_tarjeta']; // por validar por el tipo de pago*
                $importe_me = $data['importe_me']; //
                $tipocambio = $data['tipocambio']; //*
                $glosa = $data['glosa']; //ok de query*
                $nombre_dep = $data['nombre_dep']; //ok nombre alumno*
                $documento_dep = $data['documento_dep']; //ok documento del alumno*
                $id_tipoasiento = $data['id_tipoasiento']; //*

                $stmt = DB::getPdo()->prepare("BEGIN PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO(
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_MEDIOPAGO,
                                        :P_ID_PERSONA,
                                        :P_ID_CLIENTE,
                                        :P_AUTOMATICO,
                                        :P_VENTAS,
                                        :P_IMP_VENTAS,
                                        :P_ID_TIPOTRANSACCION,
                                        :P_ID_MONEDA,
                                        :P_ID_DINAMICA,
                                        :P_ID_TIPOTARJETA,
                                        :P_ID_CTABANCARIA,
                                        :P_OPERACION,
                                        :P_FECHA_OP,
                                        :P_IMPORTE,
                                        :P_IMPORTE_TARJETA,
                                        :P_IMPORTE_ME,
                                        :P_TIPOCAMBIO,
                                        :P_GLOSA,
                                        :P_NOMBRE_DEP,
                                        :P_DOCUMENTO_DEP,
                                        :P_ID_TIPOASIENTO,
                                        :P_ERROR,
                                        :P_MSGERROR,
                                        :P_ID_DEPOSITO
                                     ); END;");

                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ANHO', $id_anio, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(':P_AUTOMATICO', $automatico, PDO::PARAM_STR);
                $stmt->bindParam(':P_VENTAS', $ventas, PDO::PARAM_INT);
                $stmt->bindParam(':P_IMP_VENTAS', $imp_ventas, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_INT);
                $stmt->bindParam(':P_FECHA_OP', $fecha_op, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_tarjeta, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_TIPOCAMBIO', $tipocambio, PDO::PARAM_INT);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_INT);
                $stmt->bindParam(':P_NOMBRE_DEP', $nombre_dep, PDO::PARAM_INT);
                $stmt->bindParam(':P_DOCUMENTO_DEP', $documento_dep, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOASIENTO', $id_tipoasiento, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR',$msgerror, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPOSITO',$id_deposito, PDO::PARAM_INT);
                $stmt->execute();

            } catch(\Exception $e) {
                $nerror=1;
                $msgerror=$e->getMessage();
                $id_deposito = null;
            }

        } else {
            $nerror = 1;
            $msgerror = "Accion no disponible, error en parametros para realizar deposito";
            $id_deposito = null;
        }

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_deposito' => $id_deposito
        ];
        return $return;
    }







    // PROCEDIMIENTO PRINCIPAL - GENERAR MATRICULA FINANCIERA
    protected static function generarContrato($data, $dataPost, $rol, $user) {

        $dataImprime = array();

        $nerror=0;
        $msgerror="";
        for($x=1;$x<=200;$x++){
            $msgerror .= "0";
        }

        $id_venta = null;
        $id_deposito = null;

        DB::beginTransaction();

        try {

            $dataImprime['rol'] = $rol;
            $dataImprime['importe'] = $dataPost['importe'];
            $dataImprime['id_comprobante'] = $dataPost['id_comprobante'];
            $dataImprime['id_alumno_contrato'] = $data->id_alumno_contrato;
            $dataImprime['id_cliente'] = $dataPost['id_cliente'];

            $id_contrato_alumno = $data->id_alumno_contrato;
            $id_entidad = $user['id_entidad'];
            $id_depto = $user['id_depto'];
            $id_anio = date("Y");
            $id_mes = date("m");
            $id_persona = $user['id_user'];
            $es_virtual = 'N';
            $id_tipo_venta = 1;

            $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_VENTA_MATRICULA(
                                        :P_ID_ALUMNO_CONTRATO,
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_PERSONA,
                                        :P_ES_VIRTUAL,
                                        :P_ID_TIPOVENTA,
                                        :P_ID_VENTA,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); END;");

            $stmt->bindParam(':P_ID_ALUMNO_CONTRATO', $id_contrato_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $id_anio, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_ES_VIRTUAL', $es_virtual, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipo_venta, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR',$msgerror, PDO::PARAM_STR);
            $stmt->execute();


            if ($nerror == 0) {

                if ($rol == 'CAJERO') {

                    if ($dataPost['importe'] > 0) {
                        $deposito = self::generarDeposito($dataPost);
                        if ($deposito['nerror'] == 0) {
                            $nerror = 0;
                            $msgerror = "Se genero venta matricula y deposito correctamente. (cajero).";
                            DB::commit();
                            $dataImprime['id_deposito'] = $deposito['id_deposito'];
                            $dataImprime['id_venta'] = $id_venta;

                        } else {
                            $nerror = 1;
                            $msgerror = 'Error Back:Deposito: '.$deposito['msgerror'];
                            DB::rollBack();
                        }

                    } elseif ($dataPost['importe'] == 0) {
                        $nerror = 0;
                        $msgerror = "Se genero venta matricula correctamente, sin ningun deposito (cajero).";
                        DB::commit();
                        $dataImprime['id_deposito'] = null;
                        $dataImprime['id_venta'] = $id_venta;
                    } else {
                        $nerror = 1;
                        $msgerror = 'Error en valor de importe';
                        DB::rollBack();
                    }

                } else {
                    $nerror = 0;
                    $msgerror = "Se genero venta matricula correctamente";
                    DB::commit();
                    $dataImprime['id_deposito'] = null;
                    $dataImprime['id_venta'] = $id_venta;
                }

            } else {
                $nerror = 1;
                $msgerror = $msgerror.' (Error en ps venta matricula)';
                DB::rollBack();
            }

        } catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = 'Error Back:PS Venta matricula: '.$e->getMessage();
            DB::rollBack();
        } catch(\Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }

        $dataImprime['id_venta'] = $id_venta;

        $return = [
            'nerror'=>$nerror,
            'msgerror'=>$msgerror,
            'id_venta'=>$id_venta,
            'dataImprime'=>$dataImprime,
        ];
        return $return;


    }


    // FUNCION PRINCIPAL - IMPRIME CONTRATO ALUMNO Y DEPOSITOS DESDE FINANCIERO
    protected static function imprimeTicketContrato($request, $user) {

        $response = array('success' => false, 'message' => null);
        $rol = $request->rol;
        $importe = $request->importe;
        $id_comprobante = $request->id_comprobante;
        $id_deposito = $request->id_deposito;
        $id_venta = $request->id_venta;
        $id_alumno_contrato = $request->id_alumno_contrato;
        $id_cliente = $request->id_cliente;

        if ($rol == 'CAJERO') {
            // IMPRIME PARA CAJERO
            if ($importe > 0) {
                $ticket =  self::generateTicket($id_comprobante, $id_venta, $user);
                $ticketDeposito = self::generateTicketDeposito($id_deposito, $user);
                if ($ticket['success'] == true && $ticketDeposito['success'] == true) {
                    $response['success'] = true;
                    $response['message'] = "Impresion de ticket y ticket deposito correcto";
                } else {
                    $response['success'] = false;
                    $response['message'] = $ticket['message'].' Error en ticket deposito: '.$ticketDeposito['message'] ;
                }

            } elseif ($importe == 0) {
                $ticket =  self::generateTicket($id_comprobante, $id_venta, $user);
                if ($ticket['success'] == true) {
                    $response['success'] = true;
                    $response['message'] = "Impresion de ticket correcto.";
                } else {
                    $response['success'] = false;
                    $response['message'] = $ticket['message'];
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Error en importe';
            }
        } else {
            // IMPRIME PARA OTROS ROLES DIFERENTE A CAJERO

            $rp = self::chunkScholarship($user, $id_venta, $id_alumno_contrato, $id_cliente, $id_comprobante);

            if ($rp['success'] == true) {
                $response['success'] = true;
                $response['message'] = $rp['message'];
            } else {
                $response['success'] = false;
                $response['message'] = $rp['message'];
            }
        }


    }






}
