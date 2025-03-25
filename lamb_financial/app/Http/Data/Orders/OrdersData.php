<?php

namespace App\Http\Data\Orders;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Orders\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use PDO;
use App\Http\Data\Orders\ComunData;

class OrdersData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function addOrdersSeatsGenerate($id_pedido, $id_dinamica)
    {
        $error = 0;
        $msg_error = '';
        // $objReturn  = [];
        try {

            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_PEDIDO_ASIENTO(
            :P_ID_PEDIDO,
            :P_ID_DINAMICA,
            :P_ERROR,
            :P_MSGERROR
            );
            end;");
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error'] = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            // $jResponse['data']    = [];
            return $jResponse;
        }
    }

    public static function showOrdersSeats($id_pasiento)
    {
        $query = DB::table('PEDIDO_ASIENTO')
            ->select(
                DB::raw(
                    "
                    ID_PASIENTO
                    , ID_PEDIDO
                    , ID_TIPOPLAN
                    , ID_CUENTAAASI
                    , ID_RESTRICCION
                    , ID_FONDO
                    , ID_CTACTE
                    , ID_DEPTO
                    , DC
                    , PORCENTAJE
                    , IMPORTE
                    , GLOSA
                    , INDICADOR
                    , ID_CUENTAAASI||'-'||FC_CUENTA(ID_CUENTAAASI) AS NOMBRE_CUENTAAASI
                    , ID_DEPTO||'-'||FC_DEPARTAMENTO(ID_DEPTO) AS NOMBRE_DEPTO
                    "
                )
            )
            ->where("PEDIDO_ASIENTO.ID_PASIENTO", $id_pasiento)
            ->first();
        return $query;
    }

    public static function listOrdersSeats($id_pedido,$id_anho,$id_empresa)
    {
        $query = "SELECT
                        A.ID_PASIENTO,
                        A.ID_PEDIDO,
                        A.ID_TIPOPLAN,
                        A.ID_CUENTAAASI,
                        A.ID_RESTRICCION,
                        A.ID_FONDO,
                        A.ID_CTACTE,
                        A.ID_DEPTO,
                        A.DC,
                        A.PORCENTAJE||' %' AS PORCENTAJE,
                        A.IMPORTE,
                        A.GLOSA,
                        A.INDICADOR,
                        A.NRO_ASIENTO,
                        (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = A.ID_CUENTAAASI AND X.ID_RESTRICCION = A.ID_RESTRICCION 
                        AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = ".$id_anho." AND X.ID_EMPRESA = ".$id_empresa.") AS CTA_EMPRESARIAL
                FROM PEDIDO_ASIENTO A
                WHERE A.ID_PEDIDO = ".$id_pedido."
                ORDER BY A.ID_PASIENTO ";
        $oQuery = DB::select($query);
        /*$query = DB::table('PEDIDO_ASIENTO')
            ->select(
                DB::raw(
                    "
                    ID_PASIENTO
                    , ID_PEDIDO
                    , ID_TIPOPLAN
                    , ID_CUENTAAASI
                    , ID_RESTRICCION
                    , ID_FONDO
                    , ID_CTACTE
                    , ID_DEPTO
                    , DC
                    , PORCENTAJE||' %' AS PORCENTAJE
                    , IMPORTE
                    , GLOSA
                    "
                )
            )
            ->where("PEDIDO_ASIENTO.ID_PEDIDO",$id_pedido)
            ->orderBy('PEDIDO_ASIENTO.INDICADOR','DC DESC')
            ->get();*/
        return $oQuery;
    }

    public static function listOrdersSeatsSum($id_pasiento)
    {
        $query = "SELECT 
                        NVL(SUM(DECODE(DC,'C',PORCENTAJE,0)),0) CREDITO,
                        NVL(SUM(DECODE(DC,'D',PORCENTAJE,0)),0) DEBITO
                    FROM PEDIDO_ASIENTO
                    WHERE ID_PEDIDO = " . $id_pasiento . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listOrdersSeatsByIdCompra($id_compra)
    {
        $query = DB::table('PEDIDO_ASIENTO')
            ->join('PEDIDO_COMPRA', 'PEDIDO_ASIENTO.ID_PEDIDO', '=', 'PEDIDO_COMPRA.ID_PEDIDO')
            ->select(
                DB::raw(
                    "
                    PEDIDO_ASIENTO.ID_PASIENTO
                    , PEDIDO_ASIENTO.ID_PEDIDO
                    , PEDIDO_ASIENTO.ID_TIPOPLAN
                    , PEDIDO_ASIENTO.ID_CUENTAAASI
                    , PEDIDO_ASIENTO.ID_RESTRICCION
                    , PEDIDO_ASIENTO.ID_FONDO
                    , PEDIDO_ASIENTO.ID_CTACTE
                    , PEDIDO_ASIENTO.ID_DEPTO
                    , PEDIDO_ASIENTO.DC
                    , PEDIDO_ASIENTO.PORCENTAJE
                    , PEDIDO_ASIENTO.IMPORTE
                    , PEDIDO_ASIENTO.GLOSA
                    "
                )
            )
            // ->where("PEDIDO_ASIENTO.ID_PEDIDO",$id_pedido)
            ->where("PEDIDO_COMPRA.ID_COMPRA", $id_compra)
            ->get();
        return $query;
    }

    public static function addOrdersSeats($data)
    {
        try {
            // $id_pasiento = self::getNextId("PEDIDO_ASIENTO","ID_PASIENTO");
            $nextval = DB::select(DB::raw("SELECT SQ_PEDIDO_ASIENTO_ID.NEXTVAL ID FROM DUAL"));
            $id_pasiento = $nextval[0]->id;
            $data = array_merge(array("ID_PASIENTO" => $id_pasiento), $data);
            $result = DB::table('PEDIDO_ASIENTO')->insert($data);
            if ($result) {
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function updateOrdersSeats($data, $id_pasiento)
    {
        try {
            $result = DB::table('PEDIDO_ASIENTO')
                ->where('ID_PASIENTO', $id_pasiento)
                ->update($data);
            if ($result) {
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function deleteOrdersSeats($id_pasiento)
    {
        try {
            $result = DB::table('PEDIDO_ASIENTO')
                ->where('ID_PASIENTO', $id_pasiento)
                ->delete();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function executeOrdersSeatsMatch($id_pasiento)
    {
        // $user = DB::select('call FC_PEDIDO_ASIENTO_MATCH($id_pasiento)');
        // return $user;
        // // $result = DB::raw('call FC_PEDIDO_ASIENTO_MATCH(?)',[$id_pasiento]);
        // // return $result;
        // $res = DB::select("select FC_PEDIDO_ASIENTO_MATCH($id_pasiento)");
        // return $res;
        $res = DB::select('SELECT FC_PEDIDO_ASIENTO_MATCH(?) match FROM DUAL', array($id_pasiento));
        // dd($res[0]->match);
        return $res[0]->match;
        // $result = DB::table('TAB')
        //     // ->where('ID_PASIENTO', $id_pasiento)
        //     ->select(
        //         DB::raw(
        //             "
        //             FC_PEDIDO_ASIENTO_MATCH($id_pasiento) tisa
        //             "
        //         )
        //     )
        //     ->get();
        // return $result;
        // $users = DB::select('select * from users where active = ?', [1]);
        // try
        // {
        //     $users = DB::select('select * from users where active = ?', [1]);
        //     foreach ($users as $user) {echo $user->name;}
        //     $result = DB::table('PEDIDO_ASIENTO')
        //         ->where('ID_PASIENTO', $id_pasiento)
        //         ->delete();
        //     return $result;
        // }
        // catch(Exception $e)
        // {
        //     return false;
        // }
    }

    public static function getNextId($tabla, $campo)
    {
        try {
            $valor = DB::table($tabla)->max($campo);
            return $valor + 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    public static function listOdersDispatches($id_pedido, $estado,$id_anho)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PEDIDO_DETALLE', function ($join) use ($id_pedido) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_DETALLE.ID_PEDIDO')
                    ->where('PEDIDO_REGISTRO.ID_PEDIDO', '=', $id_pedido);
            })
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE')
            ->select(
                DB::raw(
                    "
                    PEDIDO_DESPACHO.ID_DESPACHO
                    , PEDIDO_DESPACHO.ID_DETALLE
                    -- , PEDIDO_DESPACHO.ID_PERSONA
                    -- , PEDIDO_DESPACHO.ID_MOVILIDAD
                    , PEDIDO_DESPACHO.ID_ALMACEN
                    , (SELECT NOMBRE FROM INVENTARIO_ALMACEN WHERE INVENTARIO_ALMACEN.ID_ALMACEN=PEDIDO_DESPACHO.ID_ALMACEN) NOMBRE_ALMACEN
                    , PEDIDO_DESPACHO.ID_ARTICULO
                    , (SELECT NOMBRE FROM INVENTARIO_ARTICULO WHERE INVENTARIO_ARTICULO.ID_ARTICULO=PEDIDO_DESPACHO.ID_ARTICULO) NOMBRE_ARTICULO
                    -- , PEDIDO_DESPACHO.ID_VEHICULO
                    -- , PEDIDO_DESPACHO.ID_VOUCHER
                    , PEDIDO_DESPACHO.DETALLE
                    , PEDIDO_DESPACHO.CANTIDAD
                    , PEDIDO_DESPACHO.PRECIO
                    , PEDIDO_DESPACHO.IMPORTE
                    -- , PEDIDO_DESPACHO.ESTADO
                    , PKG_INVENTORIES.FC_ARTICULO_SERVICIO(PEDIDO_DESPACHO.ID_ALMACEN,PEDIDO_DESPACHO.ID_ARTICULO,$id_anho) as ES_SERVICIO
                    ,PEDIDO_DETALLE.FECHA_INICIO
                    ,PEDIDO_DETALLE.FECHA_FIN
                    ,PEDIDO_DETALLE.HORA_INICIO
                    ,PEDIDO_DETALLE.HORA_FIN
                    "
                )
            )
            ->where("PEDIDO_DESPACHO.ESTADO", $estado)
            ->get();
        return $query;
    }

    public static function addOdersDispatches($data)
    {
        $error = 0;
        $msg_error = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_INSERT_DESPACHO(
                :P_ID_DETALLE,
                :P_ID_PERSONA,
                :P_ID_ALMACEN,
                :P_ID_ARTICULO,
                :P_DETALLE,
                :P_CANTIDAD,
                :P_PRECIO,
                :P_ERROR,
                :P_NERROR
            );
            end;");
            $stmt->bindParam(':P_ID_DETALLE', $data["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ALMACEN', $data["id_almacen"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ARTICULO', $data["id_articulo"], PDO::PARAM_INT);
            $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(':P_CANTIDAD', $data["cantidad"], PDO::PARAM_STR);
            $stmt->bindParam(':P_PRECIO', $data["precio"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_NERROR', $msg_error, PDO::PARAM_STR);
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
    public static function updateOdersDispatches($data){
        $error = 0;
        $msg_error = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_UPDATE_DESPACHO(
                :P_ID_DESPACHO,
                :P_PRECIO,
                :P_ERROR,
                :P_NERROR
            );
            end;");
            $stmt->bindParam(':P_ID_DESPACHO', $data["id_despacho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_PRECIO', $data["precio"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_NERROR', $msg_error, PDO::PARAM_STR);
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
    public static function deleteOdersDispatches($id_despacho)
    {
        try {
            $result = DB::table('pedido_despacho')
                ->where('id_despacho', '=', $id_despacho)
                ->delete();
            return $result;
        } catch (Exception $e) {
            $result = false;
        }
    }

    public static function saveOrdersDispatchesOff($data)
    {
        $error = 0;
        $msg_error = '';
        $code = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
                $code .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_FINISH_DESPACHO(:P_ID_PEDIDO,:P_CODIGO,:P_ID_PERSONA,:P_DETALLE,:P_IP,:P_CODE,:P_ERROR,:P_MSN_ERROR);end;");
            $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
            $stmt->bindParam(':P_CODIGO', $data["codigo"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IP', $data["ip"], PDO::PARAM_STR);
            $stmt->bindParam(':P_CODE', $code, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error'] = $error;
            $objReturn['code'] = $code;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function listReportsAreas($id_entidad)
    {
        $queryReport = DB::table('PEDIDO_REGISTRO')
            ->select(
                'ID_AREAORIGEN',
                'ID_ANHO',
                'ID_MES',
                DB::raw(
                    "
                    COUNT(1) CANTIDAD
                    "
                )
            )
            ->where('PEDIDO_REGISTRO.ID_ENTIDAD', $id_entidad)
            ->whereNotNull('ID_AREAORIGEN')
            ->whereNotNull('ID_ANHO')
            ->whereNotNull('ID_MES')
            ->groupBy('ID_AREAORIGEN')
            ->groupBy('ID_ANHO')
            ->groupBy('ID_MES');
        $query = DB::table(DB::raw("(" . $queryReport->toSql() . ") TABLA1"))
            ->mergeBindings($queryReport)
            ->select(
                DB::raw(
                    "
                    ID_AREAORIGEN,
                    ID_ANHO,
                    ID_MES,
                    CANTIDAD,
                    ( SELECT D.NOMBRE
                        FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D 
                        WHERE A.ID_AREA = B.ID_AREA 
                            AND B.ID_SEDEAREA = C.ID_SEDEAREA 
                            AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                            AND B.ID_DEPTO = D.ID_DEPTO 
                            AND B.ID_ENTIDAD = 7124 
                            AND C.ACTIVO = '1' 
                            AND B.ID_SEDEAREA = TABLA1.ID_AREAORIGEN 
                    ) NOMBRE_AREAORIGEN
                    "
                )
            )
            ->orderBy('ID_ANHO', "ASC")
            ->orderBy('ID_MES', "ASC")
            ->orderBy('ID_AREAORIGEN', "ASC")
            ->get();
        return $query;
    }

    public static function listReportMyOrdersTable($id_entidad, $id_persona, $codigo, $id_anho, $id_mes, $proceso, $per_page, $page, $area)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad, $id_persona) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad);
//                    ->where('PEDIDO_REGISTRO.ID_PERSONA', '=', $id_persona);
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            });
            if($area==='D'){
                /*$query->whereIn('PEDIDO_REGISTRO.ID_AREADESTINO', function ($query) use ($id_persona) {
                    $query->select('ORG_AREA_RESPONSABLE.ID_SEDEAREA')
                        ->from('ORG_AREA_RESPONSABLE')
                        ->where('ORG_AREA_RESPONSABLE.ID_PERSONA', '=', $id_persona);
                });*/
                $query->whereraw("PEDIDO_REGISTRO.ID_AREADESTINO IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
            }else {

                /*$query->whereIn('PEDIDO_REGISTRO.ID_AREAORIGEN', function ($query) use ($id_persona) {
                    $query->select('ORG_AREA_RESPONSABLE.ID_SEDEAREA')
                        ->from('ORG_AREA_RESPONSABLE')
                        ->where('ORG_AREA_RESPONSABLE.ID_PERSONA', '=', $id_persona);
                });*/
                $query->whereraw("PEDIDO_REGISTRO.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
            }

             $query = $query->select(
                'PEDIDO_REGISTRO.ID_PEDIDO',
                'PEDIDO_REGISTRO.ID_ANHO',
                'PEDIDO_REGISTRO.ID_MES',
                'PEDIDO_REGISTRO.ESTADO',
                'PEDIDO_REGISTRO.NUMERO',
                'PEDIDO_REGISTRO.MOTIVO',
                DB::raw(
                    "
                    PROCESS_RUN.ESTADO as ps_estado,
                    FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) as llave,
                    TO_CHAR(PEDIDO_REGISTRO.FECHA_PEDIDO,'YYYY-MM-DD') FECHA_PEDIDO
                    ,TO_CHAR(PEDIDO_REGISTRO.FECHA_ENTREGA,'YYYY-MM-DD') FECHA_ENTREGA
                    ,(SELECT MP.NOMBRE||' '||MP.PATERNO||' '||MP.MATERNO FROM MOISES.PERSONA MP WHERE MP.ID_PERSONA =  PEDIDO_REGISTRO.ID_PERSONA) AS USUARIO
                    ,PKG_ORDERS.FC_NOMBRE_TIPOPEDIDO(PEDIDO_REGISTRO.ID_TIPOPEDIDO) AS NOMBRE_PEDIDO
                    -- ,FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as accion
                    ,CASE PEDIDO_REGISTRO.ESTADO
                        WHEN '0' THEN
                            'Registrado'
                        WHEN '1' THEN
                            --CASE (SELECT ID_TIPOPASO FROM PROCESS_PASO WHERE PROCESS_PASO.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL)
                            CASE 
                              WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOCP' THEN 'Aprobado'
                              WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOAP' THEN 'Autorizado'
                              WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOEP' THEN 'Ejecutado'
                              WHEN (SELECT CASE WHEN sum(ppr.estado) = count(ppr.estado) THEN 1 ELSE 0 END
                                     FROM PROCESS_PASO_RUN ppr
                                     WHERE ppr.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO) = 1 AND PROCESS_RUN.estado = 1 AND FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) <> 'FOCP'
                                     THEN 'Rechazado'
                                     
                               END
                        ELSE
                            'S/A'
                    END PROCESO
                    ,( SELECT CONTA_ENTIDAD_DEPTO.ID_DEPTO ||'-'|| CONTA_ENTIDAD_DEPTO.NOMBRE
                        FROM ORG_SEDE_AREA
                          JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO AND
                                                      ORG_SEDE_AREA.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
                        WHERE ORG_SEDE_AREA.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREADESTINO
                        AND CONTA_ENTIDAD_DEPTO.ID_ENTIDAD = PEDIDO_REGISTRO.ID_ENTIDAD
                    ) DEPTO_DESTINO
                   , ( SELECT CONTA_ENTIDAD_DEPTO.ID_DEPTO
                   FROM ORG_SEDE_AREA
                     JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO AND
                                                 ORG_SEDE_AREA.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
                   WHERE ORG_SEDE_AREA.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREADESTINO
                   AND CONTA_ENTIDAD_DEPTO.ID_ENTIDAD = PEDIDO_REGISTRO.ID_ENTIDAD
               ) ID_DEPTO_DESTINO
                    ,( SELECT CONTA_ENTIDAD_DEPTO.ID_DEPTO ||'-'|| CONTA_ENTIDAD_DEPTO.NOMBRE
                        FROM ORG_SEDE_AREA
                          JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO AND
                                                      ORG_SEDE_AREA.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
                        WHERE ORG_SEDE_AREA.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREAORIGEN
                        AND CONTA_ENTIDAD_DEPTO.ID_ENTIDAD = PEDIDO_REGISTRO.ID_ENTIDAD 
                    ) DEPTO_ORIGEN
                    ,( SELECT CONTA_ENTIDAD_DEPTO.ID_DEPTO
                    FROM ORG_SEDE_AREA
                      JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO AND
                                                  ORG_SEDE_AREA.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
                    WHERE ORG_SEDE_AREA.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREAORIGEN
                    AND CONTA_ENTIDAD_DEPTO.ID_ENTIDAD = PEDIDO_REGISTRO.ID_ENTIDAD 
                ) ID_DEPTO_ORIGEN
                    ,(SELECT nombre FROM CONTA_MES WHERE ID_MES = PEDIDO_REGISTRO.ID_MES) MESNOMBRE
                    "
                )
            );
        if ($id_anho) {
            $query->where('PEDIDO_REGISTRO.ID_ANHO', $id_anho);
        }
        if ($id_mes) {
            $query->where('PEDIDO_REGISTRO.ID_MES', $id_mes);
        }
        $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0  AND LLAVE = 'FORP'
                        THEN 1
                      ELSE 0 END)                          registrado
                    , sum(CASE
                      WHEN llave = 'FOCP'
                        THEN 1
                      ELSE 0 END)                            aprobado
                    , sum(CASE
                      WHEN llave = 'FOAP'
                        THEN 1
                      ELSE 0 END)                            autorizado
                    , sum(CASE
                      WHEN llave = 'FOEP'
                        THEN 1
                      ELSE 0 END)                            ejecutado
                    , sum(CASE
                      WHEN ESTADO = 3 AND ps_estado = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";
        if ($proceso == 'RE') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '0');
            $query->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FORP");
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND LLAVE = 'FORP'
                        THEN 1
                        ELSE 0 END)                          registrado";

        } else if ($proceso == 'AP') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOCP");
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND llave = 'FOCP'
                        THEN 1
                      ELSE 0 END)                            aprobado";
        } else if ($proceso == 'AU') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOAP");
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND llave = 'FOAP'
                        THEN 1
                      ELSE 0 END)                            autorizado";
        } else if ($proceso == 'EJ') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND llave = 'FOEP'
                        THEN 1
                      ELSE 0 END)                            ejecutado";
        } else if ($proceso == 'T') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND llave = 'FOEP'
                        THEN 1
                      ELSE 0 END)                            terminado";
        } else if ($proceso == 'R') {

            $query->where('PEDIDO_REGISTRO.ESTADO', '3');
            $query->where('PROCESS_RUN.ESTADO', '3');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 3 AND ps_estado = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";
//            $query->whereRaw(DB::raw("(SELECT CASE WHEN sum(PROCESS_PASO_RUN.estado) = count(PROCESS_PASO_RUN.ESTADO) THEN 1 ELSE 0 END FROM PROCESS_PASO_RUN WHERE PROCESS_PASO_RUN.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO)= '1'"));

        }
        $query->whereRaw("PEDIDO_REGISTRO.NUMERO IS NOT NULL");

        /*---->charData*/
        $dataSet = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                DB::raw("
                    mesnombre as mes,
                    id_mes
                ".$sumDataSet
                )
            )
            ->groupBy('id_mes', 'mesnombre')
            ->orderBy('id_mes')
            ->get();

        /*charData<----*/
        $query->orderBy('PEDIDO_REGISTRO.ID_PEDIDO', "DESC");
        $rst = $query->paginate((int)$per_page);
        $custom = collect([
            'dataset' => $dataSet,
        ]);

        return $custom->merge($rst);
    }

    public static function listReportMyOrdersStatistics($id_entidad, $id_persona, $codigo, $id_anho, $id_mes, $proceso)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad, $id_persona) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
                    ->where('PEDIDO_REGISTRO.ID_PERSONA', '=', $id_persona);
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->select(
                'PEDIDO_REGISTRO.ID_PEDIDO',
                'PEDIDO_REGISTRO.ID_ANHO',
                'PEDIDO_REGISTRO.ID_MES',
                'PEDIDO_REGISTRO.ESTADO',
                DB::raw(
                    "
                    TO_CHAR(PEDIDO_REGISTRO.FECHA,'YYYY-MM-DD') FECHA
                    -- ,FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as accion
                    ,CASE PEDIDO_REGISTRO.ESTADO
                        WHEN '0' THEN
                            'EN CURSO'
                        WHEN '1' THEN
                            CASE (SELECT ID_TIPOPASO FROM PROCESS_PASO WHERE PROCESS_PASO.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL)
                                WHEN 4 THEN
                                    'TERMINADO'
                                ELSE
                                    'RECHAZADO'
                            END
                        ELSE
                            ''
                    END PROCESO
                    ,( SELECT D.NOMBRE
                        FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D 
                        WHERE A.ID_AREA = B.ID_AREA 
                            AND B.ID_SEDEAREA = C.ID_SEDEAREA 
                            AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                            AND B.ID_DEPTO = D.ID_DEPTO 
                            AND B.ID_ENTIDAD = 7124 
                            AND C.ACTIVO = '1' 
                            AND B.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREAORIGEN 
                    ) NOMBRE_DEPTO
                    ,( SELECT D.ID_DEPTO
                        FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D 
                        WHERE A.ID_AREA = B.ID_AREA 
                            AND B.ID_SEDEAREA = C.ID_SEDEAREA 
                            AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                            AND B.ID_DEPTO = D.ID_DEPTO 
                            AND B.ID_ENTIDAD = 7124 
                            AND C.ACTIVO = '1' 
                            AND B.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREAORIGEN 
                    ) ID_DEPTO
                    "
                )
            );
        if ($id_anho) {
            $query->where('PEDIDO_REGISTRO.ID_ANHO', $id_anho);
        }
        if ($id_mes) {
            $query->where('PEDIDO_REGISTRO.ID_MES', $id_mes);
        }
        if ($proceso == 'EP') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '0');
        } else if ($proceso == 'T') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->whereExists(function ($query2) {
                $query2->select(DB::raw(1))
                    ->from('PROCESS_PASO')
                    ->whereRaw('PROCESS_PASO.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL')
                    ->whereRaw('PROCESS_PASO.ID_TIPOPASO = 4');
            });
        } else if ($proceso == 'R') {
            $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->whereNotExists(function ($query2) {
                $query2->select(DB::raw(1))
                    ->from('PROCESS_PASO')
                    ->whereRaw('PROCESS_PASO.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL')
                    ->whereRaw('PROCESS_PASO.ID_TIPOPASO = 4');
            });
        }
        $query->orderBy('PEDIDO_REGISTRO.ID_PEDIDO', "ASC");
        // $result = $query->get();
        $result = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                DB::raw(
                    "
                    ID_ANHO,
                    ID_MES,
                    PROCESO,
                    COUNT(1) CANTIDAD
                    "
                )
            )
            // ->orderBy('ID_ANHO',"ASC")
            // ->orderBy('ID_MES',"ASC")
            // ->orderBy('ID_AREAORIGEN',"ASC")
            ->groupBy('ID_ANHO')
            ->groupBy('ID_MES')
            ->groupBy('PROCESO')
            // ->groupBy('ID_AREAORIGEN',"ASC")
            ->get();
        // return $query;
        return $result;
    }

    public static function getDataSet($id_entidad, $id_persona, $codigo, $id_anho, $id_mes, $proceso)
    {
        $re = ", sum(CASE
                  WHEN PEDIDO_REGISTRO.ESTADO = 0
                    THEN 1
                  ELSE 0 END)                            registrado";
        $ap = ", sum(CASE
                  WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOCP' and PEDIDO_REGISTRO.estado = 1
                    THEN 1
                  ELSE 0 END)                            aprobado";
        $au = ", sum(CASE
                  WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOAP' and PEDIDO_REGISTRO.estado = 1
                    THEN 1
                  ELSE 0 END)                            autorizado";
        $term = ", sum(CASE
                  WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOEP' and PEDIDO_REGISTRO.estado = 1
                    THEN 1
                  ELSE 0 END)                            ejecutado";
        $rech = ", sum(CASE
                      WHEN (SELECT CASE WHEN sum(ppr.estado) = count(ppr.estado)
                        THEN 1
                        ELSE 0 END
                            FROM PROCESS_PASO_RUN ppr
                            WHERE ppr.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO) = 1 and PEDIDO_REGISTRO.estado = 1 and PROCESS_RUN.estado = 1
                        THEN 1
                      ELSE 0 END)                            rechazado";
        if ($id_mes) {
            $andM = "AND PEDIDO_REGISTRO.ID_MES = ($id_mes)";
        } else {
            $andM = "AND PEDIDO_REGISTRO.ID_MES IN (select DISTINCT p.ID_MES from PEDIDO_REGISTRO p WHERE p.ID_ANHO = $id_anho )";
        }
        if ($proceso == 'RE') {
            $columns = $re;
        }
        if ($proceso == 'AP') {
            $columns = $ap;
        }
        if ($proceso == 'AU') {
            $columns = $au;
        }
        if ($proceso == 'T') {
            $columns = $term;
        }
        if ($proceso == 'R') {
            $columns = $rech;
        }
        if (!$proceso) {
            $columns = $re . $ap . $au . $term . $rech;
//            dd($columns);
//            dd($columns);
        }
        $query = "SELECT
                  (select nombre  from CONTA_MES mm where mm.ID_MES = PEDIDO_REGISTRO.ID_MES) mes
                  $columns
                FROM PEDIDO_REGISTRO
                  JOIN PROCESS_RUN ON PEDIDO_REGISTRO.ID_PEDIDO = PROCESS_RUN.ID_OPERACION
                  INNER JOIN PROCESS ON PROCESS_RUN.ID_PROCESO = PROCESS.ID_PROCESO
                                      --AND PEDIDO_REGISTRO.ID_MES IN ()
                                      AND PEDIDO_REGISTRO.ID_ANHO = $id_anho
                                      AND PROCESS.CODIGO = $codigo
                                      AND PEDIDO_REGISTRO.ID_PERSONA = $id_persona
                                      AND PEDIDO_REGISTRO.ID_ENTIDAD = $id_entidad
                                      $andM
                GROUP BY PEDIDO_REGISTRO.ID_MES ORDER BY PEDIDO_REGISTRO.ID_MES";
        $result = DB::select($query);
        return $result;
    }

    public static function listOrderAreaTEMP($id_mes, $id_anho, $id_area_dest, $id_entidad, $id_depto, $codigo, $per_page)
    {
        $rr = DB::table('PEDIDO_DETALLE')
            ->select('PEDIDO_DETALLE.ID_PEDIDO')
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE')->pluck('id_pedido');
        $query = DB::table('PEDIDO_REGISTRO')->select(
            'PEDIDO_REGISTRO.ID_PEDIDO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.ESTADO',
            'PEDIDO_REGISTRO.ID_AREADESTINO',
//            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            DB::raw("TO_CHAR(PEDIDO_REGISTRO.FECHA,'YYYY-MM-DD') FECHA
            ,( SELECT D.NOMBRE ||' - '|| A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                    
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = $id_entidad 
                     
                    AND B.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREAORIGEN 
            ) area_origen
            ,( SELECT D.NOMBRE ||' - '|| A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                     
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = $id_entidad 
                     
                    AND B.ID_SEDEAREA = PEDIDO_REGISTRO.ID_AREADESTINO 
            ) area_destino
            ,CASE PEDIDO_REGISTRO.ESTADO
                WHEN '0' THEN
                    'Registrado'
                WHEN '1' THEN
                    --CASE (SELECT ID_TIPOPASO FROM PROCESS_PASO WHERE PROCESS_PASO.ID_PASO = PROCESS_RUN.ID_PASO_ACTUAL)
                    CASE 
                      WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOCP' THEN 'Aprobado'
                      WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOAP' THEN 'Autorizado'
                      WHEN FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) = 'FOEP' THEN 'Ejecutado'
                      WHEN (SELECT CASE WHEN sum(ppr.estado) = count(ppr.estado) THEN 1 ELSE 0 END
                             FROM PROCESS_PASO_RUN ppr
                             WHERE ppr.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO) = 1 AND PROCESS_RUN.estado = 1 AND FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) <> 'FOCP'
                             THEN 'Rechazado'
                       END
                ELSE
                    'S/A'
            END PROCESO
            ")
        )
            ->join('ORG_SEDE_AREA', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'PEDIDO_REGISTRO.ID_AREADESTINO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
                    ->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            /*->join('PEDIDO_DETALLE', function ($join) use($codigo) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_DETALLE.ID_PEDIDO');
//                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->join('PEDIDO_DESPACHO', function ($join) use($codigo) {
                $join->on('PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE');
//                    ->where('PROCESS.CODIGO', '=', $codigo);
            })*/


            ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
            ->where('PEDIDO_REGISTRO.ID_ANHO', '=', $id_anho)
//            dd($oQuery);
            ->whereIn('PEDIDO_REGISTRO.ID_PEDIDO', $rr);

        /*->whereIn('PEDIDO_REGISTRO.ID_PEDIDO', function ($qu){
            $qu->select('ID_PEDIDO')->from('PEDIDO_DETALLE')->where('PEDIDO_DETALLE.ID_PEDIDO', '=', 'PEDIDO_REGISTRO.ID_PEDIDO');
//                dd($qu);
        });*/
        /*->whereIn('PEDIDO_REGISTRO.ID_PEDIDO', DB::raw("SELECT DISTINCT ID_PEDIDO
FROM PEDIDO_DETALLE
INNER JOIN PEDIDO_DESPACHO
ON PEDIDO_DETALLE.ID_DETALLE = PEDIDO_DESPACHO.ID_DETALLE"));*/
        if ($id_mes) {
            $query->where('PEDIDO_REGISTRO.ID_MES', '=', $id_mes);
        }
        if ($id_area_dest) {
            $query->where('PEDIDO_REGISTRO.ID_AREADESTINO', '=', $id_area_dest);
        }
        $query->orderBy('ID_PEDIDO', "desc");
//            dd($query->get());
        $rst = $query->paginate((int)$per_page);
        return $rst;
    }

    public static function listOrderAreaDataSetTEMP($id_mes, $id_anho, $id_area_dest, $id_entidad, $codigo)
    {
        $rr = DB::table('PEDIDO_DETALLE')
            ->select('PEDIDO_DETALLE.ID_PEDIDO')
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE')->pluck('id_pedido');

        $selects = array(
            "PEDIDO_REGISTRO.ID_AREADESTINO",
            "PEDIDO_REGISTRO.ID_PEDIDO",
            "PEDIDO_DETALLE.ID_DETALLE",
            "sum(PEDIDO_DESPACHO.IMPORTE) AS importe",
            "count(PEDIDO_DESPACHO.ID_DETALLE) AS cantidad"
//            "select sum(det.PRECIO) from PEDIDO_DETALLE det where det.ID_PEDIDO = PEDIDO_REGISTRO.ID_PEDIDO"
//            ,"count(*) as cant"
        );
//        dd($selects);
        $query = DB::table('PEDIDO_REGISTRO')->selectRaw(implode(',', $selects))
//            'PEDIDO_REGISTRO.NUMERO'
//            'PEDIDO_REGISTRO.ESTADO',
//            DB::raw("TO_CHAR(PEDIDO_REGISTRO.FECHA,'YYYY-MM-DD') FECHA")

            ->join('ORG_SEDE_AREA', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'PEDIDO_REGISTRO.ID_AREADESTINO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad);
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->join('PEDIDO_DETALLE', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_DETALLE.ID_PEDIDO')
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE')
            ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
            ->where('PEDIDO_REGISTRO.ID_ANHO', '=', $id_anho)
            ->whereIn('PEDIDO_REGISTRO.ID_PEDIDO', $rr);

        if ($id_mes) {
            $query->where('PEDIDO_REGISTRO.ID_MES', '=', $id_mes);
        }
        if ($id_area_dest) {
            $query->where('PEDIDO_REGISTRO.ID_AREADESTINO', '=', $id_area_dest);
        }

        $grouper = array_slice($selects, 0, (count($selects) - 2));
//            dd($grouper);
        $qp = $query->groupBy(DB::raw(implode(',', $grouper)));
//            dd($qp->get());
        $datas = DB::table(DB::raw("(" . $qp->toSql() . ") TABLA1"))
            ->mergeBindings($qp)
            ->select(
                DB::raw("( SELECT D.NOMBRE ||' - '|| A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = 7124 
                     
                    AND B.ID_SEDEAREA = ID_AREADESTINO 
            ) area_origen, COUNT(ID_PEDIDO) as NUM_PEDIDOS, SUM(importe) as IMPORTE_TOTAL")
            )
            ->groupBy('ID_AREADESTINO')
            ->get();


        /*$datos = DB::table(DB::raw("($query->toSql()) as t1"))
            ->mergeBindings($query)
            ->where('ID_PEDIDO', '=', '182')
            ->get();*/
//            $query->groupBy('PEDIDO_REGISTRO.ID_AREADESTINO','PEDIDO_DETALLE.ID_PEDIDO','PEDIDO_DETALLE.ID_DETALLE');

//            dd('->', array_slice($selects, 0 , (count($selects) - 1)));
//            dd($query);
//        $rst = $query->paginate((int)$per_page);
//        $query->select('importe');
//        $areas = DB::table('ORG_SEDE_AREA')->select('ID_SEDEAREA', 'ID_AREA')
//        $datos = $query->get();
//        dd($datos->select('ID_AREADESTINO', 'ID_PEDIDO'));
        return $datas;
    }

    public static function listOrderArea($id_mes, $id_anho, $id_area_dest, $id_entidad, $id_depto, $codigo, $per_page)
    {
        // list mobilidad -> pedido
//        $id_depto = "5";
        $querym = DB::table('PEDIDO_REGISTRO')->select(
            'PEDIDO_REGISTRO.ID_AREADESTINO',
            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.FECHA',
            'PEDIDO_REGISTRO.ID_MES',
            DB::raw("(SELECT NOMBRE FROM CONTA_MES WHERE ID_MES = PEDIDO_REGISTRO.ID_MES) MES"),
            'PEDIDO_MOVILIDAD.ID_PEDIDO',
            DB::raw("SUM(PEDIDO_DESPACHO.IMPORTE) IMPORTE")
        )
            ->join('PEDIDO_MOVILIDAD', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_MOVILIDAD.ID_PEDIDO')
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DESPACHO.ID_MOVILIDAD', '=', 'PEDIDO_MOVILIDAD.ID_MOVILIDAD')
            ->join('ORG_SEDE_AREA', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'PEDIDO_REGISTRO.ID_AREADESTINO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad);
//                    ->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
            ->where('PEDIDO_REGISTRO.ID_DEPTO', '=', $id_depto)
            ->where('PEDIDO_REGISTRO.ID_ANHO', '=', $id_anho);

        if ($id_mes) {
            $querym->where('PEDIDO_REGISTRO.ID_MES', '=', $id_mes);
        }
        if ($id_area_dest) {
            $querym->where('PEDIDO_REGISTRO.ID_AREADESTINO', '=', $id_area_dest);
        }
        $querym->groupBy(
            'PEDIDO_REGISTRO.ID_AREADESTINO',
            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.FECHA',
            'PEDIDO_REGISTRO.ID_MES',
            'PEDIDO_MOVILIDAD.ID_PEDIDO');
        // list detail -> pedido and union with mobile
        $query = DB::table('PEDIDO_REGISTRO')->select(
            'PEDIDO_REGISTRO.ID_AREADESTINO',
            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.FECHA',
            'PEDIDO_REGISTRO.ID_MES',
            DB::raw("(SELECT NOMBRE FROM CONTA_MES WHERE ID_MES = PEDIDO_REGISTRO.ID_MES) MES"),
            'PEDIDO_DETALLE.ID_PEDIDO',
            DB::raw("SUM(PEDIDO_DESPACHO.IMPORTE) IMPORTE")
        )
            ->join('PEDIDO_DETALLE', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_DETALLE.ID_PEDIDO')
//            ->leftJoin('PEDIDO_MOVILIDAD','PEDIDO_REGISTRO.ID_PEDIDO','=','PEDIDO_DETALLE.ID_PEDIDO')
            ->join('PEDIDO_DESPACHO', 'PEDIDO_DETALLE.ID_DETALLE', '=', 'PEDIDO_DESPACHO.ID_DETALLE')
            ->join('ORG_SEDE_AREA', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'PEDIDO_REGISTRO.ID_AREADESTINO')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad);
//                    ->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
            ->where('PEDIDO_REGISTRO.ID_DEPTO', '=', $id_depto)
            ->where('PEDIDO_REGISTRO.ID_ANHO', '=', $id_anho)->union($querym); // UNION, tomar atencion para agregar fiiltros


        if ($id_mes) {
            $query->where('PEDIDO_REGISTRO.ID_MES', '=', $id_mes);
        }
        if ($id_area_dest) {
            $query->where('PEDIDO_REGISTRO.ID_AREADESTINO', '=', $id_area_dest);
        }
        /*if ($id_depto and $id_depto == "5") {
            $query->whereRaw("ORG_SEDE_AREA.ID_DEPTO like '".$id_depto."%'");
        }*/
        $query->groupBy(
            'PEDIDO_REGISTRO.ID_AREADESTINO',
            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.FECHA',
            'PEDIDO_REGISTRO.ID_MES',
            'PEDIDO_DETALLE.ID_PEDIDO');


        /*---->charData*/
        $dataSet = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                DB::raw("( SELECT A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = 7124                      
                    AND B.ID_SEDEAREA = ID_AREADESTINO 
            ) area_origen, COUNT(ID_PEDIDO) as NUM_PEDIDOS, SUM(importe) as IMPORTE_TOTAL")
            )
            ->groupBy('ID_AREADESTINO')
            ->get();
        /*charData<----*/

        /*---->mainList*/
        $dataList = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                'id_pedido',
                'importe',
                'MOTIVO',
                'NUMERO',
                'MES',
                DB::raw("( SELECT D.NOMBRE ||' - '|| A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = 7124                      
                    AND B.ID_SEDEAREA = ID_AREADESTINO 
            ) area_destino
            ,( SELECT D.NOMBRE ||' - '|| A.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D 
                WHERE A.ID_AREA = B.ID_AREA 
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD 
                    AND B.ID_DEPTO = D.ID_DEPTO 
                    AND B.ID_ENTIDAD = 7124                      
                    AND B.ID_SEDEAREA = ID_AREAORIGEN 
            ) area_origen
            , TO_CHAR(FECHA,'YYYY-MM-DD') FECHA
            --, (SELECT NOMBRE FROM CONTA_MES WHERE ID_MES = ID_MES) MES
            , 'ejecutado' PROCESO
            ")
            )
            ->orderBy('id_pedido', 'DESC')->paginate((int)$per_page);
        /*<----End mainList*/

        /*---->joinData*/
        $custom = collect([
            'dataset' => $dataSet,
//            'list'=>$dataList
        ]);
        /*<----End joinData*/

        return $custom->merge($dataList);
    }

    public static function listOrdersByAreaDestino($id_mes, $id_anho, $id_area_dest, $id_entidad, $id_depto, $codigo, $per_page)
    {
        // list detail -> pedido and union with mobile
        $query = DB::table('VW_ORDERS_PENDING')->select(
            'VW_ORDERS_PENDING.ID_AREADESTINO',
            'VW_ORDERS_PENDING.ID_PEDIDO',
//            'VW_ORDERS_PENDING.NOMBRE_AREADESTINO',
            'VW_ORDERS_PENDING.ID_DEPTO',
//        'PEDIDO_REGISTRO.MOTIVO',
        'VW_ORDERS_PENDING.NUMERO',
        'VW_ORDERS_PENDING.FECHA_PEDIDO',
//        'PEDIDO_REGISTRO.FECHA',
//        'PEDIDO_REGISTRO.ID_MES',
        DB::raw("
        (SELECT D.ID_DEPTO ||' - '|| D.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D
                WHERE A.ID_AREA = B.ID_AREA
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD
                    AND B.ID_DEPTO = D.ID_DEPTO
                    AND B.ID_ENTIDAD = VW_ORDERS_PENDING.ID_ENTIDAD
                    AND B.ID_SEDEAREA = VW_ORDERS_PENDING.ID_AREAORIGEN) NOMBRE_AREAORIGEN,
        (SELECT D.ID_DEPTO ||' - '|| D.NOMBRE
                FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D
                WHERE A.ID_AREA = B.ID_AREA
                    AND B.ID_ENTIDAD = D.ID_ENTIDAD
                    AND B.ID_DEPTO = D.ID_DEPTO
                    AND B.ID_ENTIDAD = VW_ORDERS_PENDING.ID_ENTIDAD
                    AND B.ID_SEDEAREA = VW_ORDERS_PENDING.ID_AREADESTINO) NOMBRE_AREADESTINO,
        (CASE FC_LLAVE_PROCESO_PREVIOUS(VW_ORDERS_PENDING.ID_REGISTRO)
               WHEN 'FORP'
                 THEN 'REGISTRADO'
               WHEN 'FOCP'
                 THEN 'APROBADO'
               WHEN 'FOAP'
                 THEN 'AUTORIZADO'
               WHEN 'FOAD'
                 THEN 'AUTORIZADO'
               WHEN 'FOEP'
                 THEN 'EJECUTADO'
               END) PROCESO,
        FC_LLAVE_PROCESO_PREVIOUS(VW_ORDERS_PENDING.ID_REGISTRO) LLAVE,
            trunc((SELECT MAX(X.FECHA)
             FROM PROCESS_PASO_RUN X
             WHERE X.ID_REGISTRO = VW_ORDERS_PENDING.ID_REGISTRO AND X.ID_PASO = VW_ORDERS_PENDING.ID_PASO_ACTUAL) -
            VW_ORDERS_PENDING.FECHA_REGISTRO) AS        param,
          trunc(sysdate - VW_ORDERS_PENDING.FECHA_REGISTRO) params
  ")
        )
//            ->join('PEDIDO_DETALLE','PEDIDO_REGISTRO.ID_PEDIDO','=','PEDIDO_DETALLE.ID_PEDIDO')
//            ->leftJoin('PEDIDO_MOVILIDAD','PEDIDO_REGISTRO.ID_PEDIDO','=','PEDIDO_DETALLE.ID_PEDIDO')
//            ->join('PEDIDO_DESPACHO','PEDIDO_DETALLE.ID_DETALLE','=','PEDIDO_DESPACHO.ID_DETALLE')
//            ->join('ORG_SEDE_AREA','ORG_SEDE_AREA.ID_SEDEAREA','=','PEDIDO_REGISTRO.ID_AREADESTINO')
//            ->join('PROCESS_RUN', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
//            ->join('PROCESS_PASO_RUN', 'PROCESS_RUN.ID_REGISTRO', '=', 'PROCESS_PASO_RUN.ID_REGISTRO')
//            ->join('MOISES.PERSONA', 'PEDIDO_REGISTRO.ID_PERSONA', '=', 'MOISES.PERSONA.ID_PERSONA')
            /*->join('PROCESS_RUN', function ($join) use($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad);
//                    ->where(DB::raw("FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)"), "FOEP");
            })*/
            /*->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })*/
//            ->whereIn('PROCESS_PASO_RUN.ID_PASO', [128,129,130,131])
//            ->whereNotNull('PROCESS_PASO_RUN.FECHA')
//            ->whereRaw("PROCESS_PASO_RUN.ID_DETALLE = (SELECT max(PROCESS_PASO_RUN.ID_DETALLE)
//                                        FROM PROCESS_PASO_RUN
//                                        WHERE PROCESS_PASO_RUN.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO)")
            ->whereRaw("FC_LLAVE_PROCESO_PREVIOUS(VW_ORDERS_PENDING.ID_REGISTRO) IN ('FORP', 'FOCP', 'FOAP', 'FOEP', 'FOAD')")
            ->where('VW_ORDERS_PENDING.ID_ENTIDAD', '=', $id_entidad)
            ->where('VW_ORDERS_PENDING.ID_ANHO', '=', $id_anho)
            ->where('VW_ORDERS_PENDING.ID_MES', '=', $id_mes)
            ->where('VW_ORDERS_PENDING.ID_AREADESTINO', '=', $id_area_dest);

//dd($query->get());
        /*if($id_mes){
            $query->where('PEDIDO_REGISTRO.ID_MES', '=', $id_mes);
        }
        if($id_area_dest){
            $query->where('PEDIDO_REGISTRO.ID_AREADESTINO', '=', $id_area_dest);
        }*/
        /*$query->groupBy(
            'PEDIDO_REGISTRO.ID_AREADESTINO',
            'PEDIDO_REGISTRO.ID_AREAORIGEN',
            'PEDIDO_REGISTRO.MOTIVO',
            'PEDIDO_REGISTRO.NUMERO',
            'PEDIDO_REGISTRO.FECHA',
            'PEDIDO_REGISTRO.ID_MES',
            'PEDIDO_DETALLE.ID_PEDIDO');*/

//        dd($query->toSql());
        /*---->charData*/
        $counterData = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
//                'params',
                DB::raw("
                param         param,
                  count(CASE WHEN LLAVE = 'FORP'
                    THEN 1 END) registrado,
                  count(CASE WHEN LLAVE = 'FOCP'
                    THEN 1 END) aprobado,
                  count(CASE WHEN (LLAVE = 'FOAP' or LLAVE = 'FOAD')
                    THEN 1 END) autorizado,
                  count(CASE WHEN LLAVE = 'FOEP'
                    THEN 1 END) ejecutado
            ")
            )
            ->groupBy('param')
            ->orderBy('param')
            ->get();
//        dd($counterData);
//        $dataSet = self::getGropedData($counterData);
        $dataSet = $counterData;
        /*charData<----*/



        /*---->mainList*/
        $dataList = $query->orderBy('ID_PEDIDO', 'DESC')
            ->paginate((int)$per_page);
        /*<----End mainList*/

//        dd($dataList);
        /*---->joinData*/
        $custom = collect([
            'dataset' => $dataSet,
//            'list'=>$dataList
        ]);
        /*<----End joinData*/

        return $custom->merge($dataList);
    }
    public static function getGropedData($data){
        $prm = "";
        $ndata = [];
        $refw = $apfw = $aufw = $ejfw = 0;
        $resw = $apsw = $ausw = $ejsw = 0;
        $retw = $aptw = $autw = $ejtw = 0;
        $remm = $apmm = $aumm = $ejmm = 0;
        foreach ($data as &$valor) {

            if($valor->param < 8){
                $temp = (object)array(
//                    'dias' => $valor->param,
                    'param' => $valor->param.' '.'dias',
                    'aprobado' => $valor->aprobado,
                    'registrado' => $valor->registrado,
                    'autorizado' => $valor->autorizado,
                    'ejecutado' => $valor->ejecutado
                );
                array_push($ndata, $temp);
            }
            if($valor->param > 7 and $valor->param < 15){
                $refw = $refw + $valor->registrado;
                $apfw = $apfw + $valor->aprobado;
                $aufw = $aufw + $valor->autorizado;
                $ejfw = $ejfw + $valor->ejecutado;
            }
            if($valor->param > 14 and $valor->param < 22){
                $resw = $resw + $valor->registrado;
                $apsw = $apsw + $valor->aprobado;
                $ausw = $ausw + $valor->autorizado;
                $ejsw = $ejsw + $valor->ejecutado;
            }
            if($valor->param > 21 and $valor->param < 29){
                $retw = $retw + $valor->registrado;
                $aptw = $aptw + $valor->aprobado;
                $autw = $autw + $valor->autorizado;
                $ejtw = $ejtw + $valor->ejecutado;
            }
            if($valor->param > 28 ){
                $remm = $remm + $valor->registrado;
                $apmm = $apmm + $valor->aprobado;
                $aumm = $aumm + $valor->autorizado;
                $ejmm = $ejmm + $valor->ejecutado;
            }


        }
        if($apfw + $aufw + $ejfw > 0){
            $firW = (object)array(
//                'dias' => "{$prm}{$valor->param}",
                'param' => '+1 sem.',
                'registrado' => $refw,
                'aprobado' => $apfw,
                'autorizado' => $aufw,
                'ejecutados' => $ejfw
            );
            array_push($ndata, $firW);
        }
        if($apsw + $ausw + $ejsw > 0){
            $secW = (object)array(
//                'dias' => "{$prm}{$valor->param}",
                'param' => '+2 sem.',
                'registrado' => $resw,
                'aprobado' => $apsw,
                'autorizado' => $ausw,
                'ejecutados' => $ejsw
            );
            array_push($ndata, $secW);
        }
        if($aptw + $autw + $ejtw > 0){
            $thrW = (object)array(
//                'dias' => "{$prm}{$valor->param}",
                'param' => '+3 sem.',
                'registrado' => $retw,
                'aprobado' => $aptw,
                'autorizado' => $autw,
                'ejecutados' => $ejtw
            );
            array_push($ndata, $thrW);
        }
        if($ejmm + $ejmm + $ejmm > 0){
            $mothW = (object)array(
//                'dias' => "{$prm}{$valor->param}",
                'param' => '+1 mes.',
                'registrado' => $remm,
                'aprobado' => $apmm,
                'autorizado' => $aumm,
                'ejecutados' => $ejmm
            );
            array_push($ndata, $mothW);
        }
//        array_push($ndata, $firW);
//        dd($data, collect($ndata));
        return collect($ndata);
    }

    public static function listOrdersPending($id_entidad, $id_depto, $codigo, $id_persona, $per_page, $ids_paso, $llave, $searchs)
    {

        $query = DB::table('VW_ORDERS_PENDING')
            ->where('ID_ENTIDAD', '=', $id_entidad)
            //->where('ID_DEPTO', '=', $id_depto)
            //->where('ID_ANHO', '=', $id_entidad)
            //->where('ID_PERSONA', '=', $id_persona)
            ->where('ID_PASO_ACTUAL', '=', $ids_paso)
            ->where('CODIGO', '=', $codigo)
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
          ->whereraw("(NUMERO like '%".$searchs."%' or UPPER(NOMBRE_AREAORIGEN) LIKE UPPER('%".$searchs."%'))");
        if ($llave == "FOCP") {//Aprobar
            /*$query->whereIn('ID_AREAORIGEN', function ($query) use ($id_persona) {
                $query->select('ID_SEDEAREA')
                    ->from('ORG_AREA_RESPONSABLE')
                    ->where('ID_PERSONA', '=', $id_persona)
                    ->where('ID_NIVEL', '=', '1');
            });*/
            
            $query->whereraw("ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." AND ID_NIVEL IN ('1','2') AND ACTIVO = '1' )");
            // $query->whereraw("(NUMERO like '%".$searchs."%')");
        } elseif ($llave == "FOAP") {//autorizar
            /*$query->whereIn('ID_AREAORIGEN', function ($query1) use ($id_entidad, $id_depto, $id_persona) {
                $query1->select('ID_SEDEAREA')
                    ->from('PEDIDO_AUTORIZA_AREA')
                    ->where('ID_ENTIDAD', '=', $id_entidad)
                    ->where('ID_PERSONA', '=', $id_persona)
                    ->where('ID_DEPTO', '=', $id_depto);
            });*/
            $query->whereraw("ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM PEDIDO_AUTORIZA_AREA WHERE ID_ENTIDAD = ".$id_entidad." AND ID_PERSONA = ".$id_persona." AND ID_DEPTO = '".$id_depto."')");
            // $query->whereraw("(NUMERO like '%".$searchs."%')");
        } elseif ($llave == "FOAD") {//Asignar
            /*$query->whereIn('ID_AREAORIGEN', function ($query1) use ($id_persona) {
                $query1->select('ID_SEDEAREA')
                    ->from('ORG_AREA_RESPONSABLE')
                    ->where('ID_PERSONA', '=', $id_persona)
                    ->where('ID_NIVEL', '=', '1');
            });*/
            $query->whereraw("ID_AREADESTINO IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." AND ID_NIVEL IN ('1','2'))");
            // $query->whereraw("(NUMERO like '%".$searchs."%')");
        } else {//EJECUTAR
            /*$query->whereIn('ID_AREADESTINO', function ($query1) use ($id_persona) {
                $query1->select('ID_SEDEAREA')
                    ->from('ORG_AREA_RESPONSABLE')
                    ->where('ID_PERSONA', '=', $id_persona)
                    ->whereIn('ID_NIVEL', ['1', '2']);
            });*/
            $query->whereraw("ID_AREADESTINO IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." AND ID_NIVEL IN ('1','2'))");
            // $query->whereraw("(NUMERO like '%".$searchs."%')");
        }
        $query->select('ID_PEDIDO',
            'PID_PEDIDO',
            'ID_TIPOPEDIDO',
            'NOMBRE_TIPOPEDIDO',
            'ID_ACTIVIDAD',
            'NOMBRE_ACTIVIDAD',
            'ID_AREAORIGEN',
            'NOMBRE_AREAORIGEN',
            'ID_AREADESTINO',
            'NOMBRE_AREADESTINO',
            'NUMERO',
            'ACUERDO',
            'CODIGO',
            'FECHA_PEDIDO',
            'FECHA_ENTREGA',
            'MOTIVO',
            'ESTADO_PORCENTAJE',
            'ACCION',
            'REVISADO',
            'ID_PASO_ACTUAL',
            'ESTADO',
            DB::raw("regexp_substr(NOMBRE_AREADESTINO,'[^ - ]+',  1) as id_depto_destino"),
            DB::raw("regexp_substr(NOMBRE_AREAORIGEN,'[^ - ]+',  1) as id_depto_origen"),
            'ESTADO_RUN')
            ->orderBy('ID_PEDIDO', "desc");
        $rst = $query->paginate((int)$per_page);
        return $rst;
    }

    public static function listTypesForms()
    {
        $query = "SELECT 
                    ID_FORMULARIO,NOMBRE 
                    FROM PEDIDO_FORM
                    WHERE ESTADO = '1'
                    ORDER BY ID_FORMULARIO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listOrdersTypesAreas($id_sedearea)
    {
        $query = "SELECT 
                A.ID_TIPOPEDIDO,A.NOMBRE,
                (SELECT COUNT(X.ID_SEDEAREA) FROM PEDIDO_AREA_TIPO X WHERE X.ID_TIPOPEDIDO = A.ID_TIPOPEDIDO AND X.ID_SEDEAREA = " . $id_sedearea . ") AS CHEK
                FROM TIPO_PEDIDO A
                WHERE A.ESTADO = '1'
                ORDER BY A.ID_TIPOPEDIDO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function deleteOrdersTypesAreas($id_sedearea)
    {
        $query = DB::table('PEDIDO_AREA_TIPO')->where('ID_SEDEAREA', $id_sedearea)->delete();
        return $query;
    }

    public static function addOrdersTypesAreas($id_sedearea, $id_tipopedido)
    {
        DB::table('PEDIDO_AREA_TIPO')->insert(
            array('ID_SEDEAREA' => $id_sedearea, 'ID_TIPOPEDIDO' => $id_tipopedido, 'FECHA' => DB::raw('SYSDATE'))
        );
    }

    public static function listTypesOrders($id_sedearea)
    {
        $query = "SELECT 
                        A.ID_TIPOPEDIDO,A.NOMBRE, B.ID_FORMULARIO
                FROM TIPO_PEDIDO A, PEDIDO_AREA_TIPO B
                WHERE A.ID_TIPOPEDIDO = B.ID_TIPOPEDIDO
                AND B.ID_SEDEAREA = " . $id_sedearea . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listCars($id_entidad, $id_depto)
    {
        $query = "SELECT 
                        ID_VEHICULO,PLACA,MARCA,MODELO,ASIENTOS,KM_PRECIO 
                FROM PEDIDO_VEHICULO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                AND ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDrivers()
    {
        $query = "SELECT 
                        A.ID_PERSONA, A.NOMBRE,A.PATERNO,A.MATERNO,B.LICENCIA,B.CATEGORIA
                FROM MOISES.PERSONA A, MOISES.PERSONA_CONDUCTOR B
                WHERE A.ID_PERSONA = B.ID_PERSONA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function ListTravelProgramming($id_entidad, $id_depto, $id_conductor, $fecha)
    {
        $query = "SELECT B.ID_MOVILIDAD,A.NUMERO,B.ORIGEN,B.DESTINO,B.HORA_P
                FROM PEDIDO_REGISTRO A, PEDIDO_MOVILIDAD B 
                WHERE A.ID_PEDIDO = B.ID_PEDIDO 
                AND A.ID_ENTIDAD = " . $id_entidad . "  
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND B.ID_CONDUCTOR = " . $id_conductor . "
                AND TO_CHAR(B.FECHA_P,'DDMMYYYY') = '" . $fecha . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listOdersDispatchesMovi($id_pedido, $estado)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PEDIDO_MOVILIDAD', function ($join) use ($id_pedido) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_MOVILIDAD.ID_PEDIDO')
                    ->where('PEDIDO_REGISTRO.ID_PEDIDO', '=', $id_pedido);
            })
            ->join('PEDIDO_DESPACHO', 'PEDIDO_MOVILIDAD.ID_MOVILIDAD', '=', 'PEDIDO_DESPACHO.ID_MOVILIDAD')
            ->select(
                DB::raw(
                    "
                    PEDIDO_DESPACHO.ID_DESPACHO
                    , PEDIDO_DESPACHO.ID_MOVILIDAD
                    , PEDIDO_DESPACHO.ID_ALMACEN
                    , PEDIDO_MOVILIDAD.ORIGEN
                    , PEDIDO_MOVILIDAD.DESTINO
                    , PEDIDO_MOVILIDAD.ID_VEHICULO
                    , PEDIDO_DESPACHO.ID_VOUCHER
                    , PEDIDO_DESPACHO.DETALLE
                    , PEDIDO_DESPACHO.CANTIDAD
                    , PEDIDO_DESPACHO.PRECIO
                    , PEDIDO_DESPACHO.IMPORTE
                    -- , PEDIDO_DESPACHO.ESTADO
                    , PEDIDO_MOVILIDAD.KM_INICIO
                    , PEDIDO_MOVILIDAD.KM_FIN
                    , PEDIDO_MOVILIDAD.HORA_SALIDA
                    , PEDIDO_MOVILIDAD.HORA_LLEGADA
                    , PEDIDO_MOVILIDAD.ORIGEN
                    "
                )
            )
            ->where("PEDIDO_DESPACHO.ESTADO", $estado)
            ->get();
        return $query;
    }

    public static function addOdersDispatchesMovi($data)
    {
        $error = 0;
        $msg_error = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_INSERT_DESPACHO_MOVILIDAD(
                :P_ID_MOVILIDAD,
                :P_ID_PERSONA,
                :P_KM_I,
                :P_KM_F,
                :P_HORA_S,
                :P_HORA_L,
                :P_IMPORTE,
                :P_CANTIDAD_R,
                :P_ERROR,
                :P_NERROR
            );
            end;");
            $stmt->bindParam(':P_ID_MOVILIDAD', $data["id_movilidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_KM_I', $data["km_inicio"], PDO::PARAM_INT);
            $stmt->bindParam(':P_KM_F', $data["km_fin"], PDO::PARAM_INT);
            $stmt->bindParam(':P_HORA_S', $data["hora_salida"], PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_L', $data["hora_llegada"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $data["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_CANTIDAD_R', $data["cantidad_r"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_NERROR', $msg_error, PDO::PARAM_STR);
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

    public static function saveOrdersDispatchesMoviOff($data)
    {
        $error = 0;
        $msg_error = '';
        $code = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
                $code .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_FINISH_DESPACHO_MOVI(:P_ID_PEDIDO,:P_CODIGO,:P_ID_PERSONA,:P_DETALLE,:P_IP,:P_FIN,:P_CODE,:P_ERROR,:P_MSN_ERROR);end;");
            $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
            $stmt->bindParam(':P_CODIGO', $data["codigo"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IP', $data["ip"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FIN', $data["fin"], PDO::PARAM_STR);
            $stmt->bindParam(':P_CODE', $code, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error'] = $error;
            $objReturn['code'] = $code;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function ListCarsProgramming($id_entidad, $id_depto, $id_vehiculo, $fecha)
    {
        $query = "SELECT B.ID_MOVILIDAD,A.NUMERO,B.ORIGEN,B.DESTINO,B.HORA_P
                FROM PEDIDO_REGISTRO A, PEDIDO_MOVILIDAD B 
                WHERE A.ID_PEDIDO = B.ID_PEDIDO 
                AND A.ID_ENTIDAD = " . $id_entidad . "  
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND B.ID_VEHICULO = " . $id_vehiculo . "
                AND TO_CHAR(B.FECHA_P,'DDMMYYYY') = '" . $fecha . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function deleteCars($id_movilidad)
    {
        DB::table('PEDIDO_MOVILIDAD')
            ->where('ID_MOVILIDAD', $id_movilidad)
            ->update([
                'ID_VEHICULO' => null
            ]);
    }

    public static function deleteDrivers($id_movilidad)
    {
        $error = 0;
        $msg_error = '';
        try {
            DB::table('PEDIDO_MOVILIDAD')
                ->where('ID_MOVILIDAD', $id_movilidad)
                ->update([
                    'ID_CONDUCTOR' => null
                ]);
            $jResponse['error'] = 0;
            $jResponse['message'] = "OK";
        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
        }
        return $jResponse;
    }

    public static function addOrdersSeatsGenerateTemplate($id_pedido, $id_dinamica)
    {
        $error = 0;
        $msg_error = '';
        // $objReturn  = [];
        try {

            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_PEDIDO_ASIENTO_PLANTILLA(
            :P_ID_PEDIDO,
            :P_ID_DINAMICA,
            :P_ERROR,
            :P_MSGERROR
            );
            end;");
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error'] = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        } catch (Exception $e) {
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            // $jResponse['data']    = [];
            return $jResponse;
        }
    }

    public static function ListValidadCant($id_pedido)
    {
        $query = "SELECT NVL(SUM(A.CANTIDAD),0) AS CANT_PED, NVL(SUM(B.CANTIDAD),0) AS CANT_DES
                FROM PEDIDO_DETALLE A, PEDIDO_DESPACHO B
                WHERE A.ID_DETALLE = B.ID_DETALLE
                AND A.ID_PEDIDO = " . $id_pedido . "
                AND B.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showTypesOrders($llave)
    {
        $query = "SELECT ID_TIPOPEDIDO 
                FROM TIPO_PEDIDO
                WHERE LLAVE = '" . $llave . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showOrdersNumber($id_pedido){
        $query = "SELECT NUMERO 
                FROM PEDIDO_REGISTRO
                WHERE ID_PEDIDO = ".$id_pedido." 
                AND NUMERO IS NULL ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateOrdersNumber($id_pedido)
    {
        $error = 0;
        $msg_error = '';
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_ORDERS_NUMBERS(:P_ID_PEDIDO);end;");
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
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

    public static function addOrdersSeatsGenerateBudget($id_pedido, $id_actividad)
    {
        $error = 0;
        $msg_error = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_PEDIDO_ASIENTO_PSPTO(
            :P_ID_PEDIDO,
            :P_ID_ACTIVIDAD,
            :P_ERROR,
            :P_MSGERROR
            );
            end;");
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ACTIVIDAD', $id_actividad, PDO::PARAM_INT);
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
    public static function deleteOrders($id_persona){
        $error = 0;
        $msg_error = 'OK';
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ORDERS.SP_ORDERS_DELETE(:P_ID_PERSONA);end;");
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
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
    public static function listOrdersDash($id_entidad,$id_depto,$id_anho,$id_mes,$id_user){
        $query = "SELECT 
                        COUNT(A.ID_AREADESTINO) CANTI,PKG_ORDERS.FC_NOMBRE_AREA(A.ID_AREADESTINO) AS AREA,
                        SUM(C.IMPORTE) as IMPORTE
                FROM PEDIDO_REGISTRO A JOIN PEDIDO_DETALLE B
                ON A.ID_PEDIDO = B.ID_PEDIDO
                JOIN PEDIDO_DESPACHO C
                ON B.ID_DETALLE = C.ID_DETALLE
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ESTADO = '1'
                AND A.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE  WHERE ID_PERSONA = ".$id_user.")
                AND C.ID_VOUCHER IN (SELECT ID_VOUCHER FROM CONTA_VOUCHER WHERE ID_ENTIDAD = ".$id_entidad." AND ID_DEPTO = '".$id_depto."' AND ID_ANHO = ".$id_anho." AND ID_MES = ".$id_mes.")
                GROUP BY A.ID_AREADESTINO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listOrdersDashTotal($id_entidad,$id_depto,$id_anho,$id_mes,$id_user){
        $query = "SELECT 
                        COUNT(A.ID_AREADESTINO) CANTI,
                        SUM(C.IMPORTE) TOTAL
                FROM PEDIDO_REGISTRO A JOIN PEDIDO_DETALLE B
                ON A.ID_PEDIDO = B.ID_PEDIDO
                JOIN PEDIDO_DESPACHO C
                ON B.ID_DETALLE = C.ID_DETALLE
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ESTADO = '1'
                AND A.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE  WHERE ID_PERSONA = ".$id_user.")
                AND C.ID_VOUCHER IN (SELECT ID_VOUCHER FROM CONTA_VOUCHER WHERE ID_ENTIDAD = ".$id_entidad." AND ID_DEPTO = '".$id_depto."' AND ID_ANHO = ".$id_anho." AND ID_MES = ".$id_mes.")
                 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listGrassSchedule($tipo,$id_almacen,$id_articulo,$fecha){
        // dd($tipo,$id_almacen,$id_articulo,$fecha);
        if($tipo == "1"){
            $dato = "<= 13";
        }else{
            $dato = "> 13 and ID_HORA <= 22";
        }
        
        //para las siguientes fechas actualizadas
        $query = "SELECT DISTINCT
        A.ID_HORA,
         A.DE,
         A.A,
         A.DE||' - '||A.A AS HORARIO,
         A.TURNO,
         (CASE WHEN A.TURNO = 'N' THEN '80' ELSE '40' END) AS PRECIO,
         TO_CHAR(SYSDATE,'HH24') HORA,
        (CASE WHEN trunc(to_date('".$fecha."', 'YYYY-MM-DD')) = trunc(sysdate) THEN (CASE WHEN (A.ID_HORA > TO_CHAR(SYSDATE, 'HH24')) THEN 'S' ELSE 'N' END)
         WHEN trunc(to_date('".$fecha."', 'YYYY-MM-DD')) > trunc(sysdate) THEN 'S' ELSE 'N' END ) HABILITADO,
        (CASE WHEN B.HORA_INICIO IS NOT NULL THEN 'S' ELSE
        (SELECT DECODE(COUNT(1),1,'S','N') FROM PEDIDO_RESERVA X
        WHERE A.DE = X.HORA_INICIO
        AND A.A = X.HORA_FIN
        AND X.ID_ALMACEN = ".$id_almacen." AND X.ID_ARTICULO = ".$id_articulo." AND TO_CHAR(X.FECHA,'YYYY-MM-DD') = '".$fecha."')
        END) AS RESERVADO
-- no contar los pedidos rechazadas
        FROM PEDIDO_HORA A LEFT JOIN 
        (SELECT B.ID_ALMACEN,B.ID_ARTICULO,B.HORA_INICIO,B.HORA_FIN,B.FECHA_INICIO 
        FROM PEDIDO_REGISTRO A JOIN PEDIDO_DETALLE B ON A.ID_PEDIDO = B.ID_PEDIDO WHERE A.ESTADO NOT IN (3)) B
-------- antes
        -- FROM PEDIDO_HORA A LEFT JOIN PEDIDO_DETALLE B
        ON A.DE = B.HORA_INICIO
        AND A.A = B.HORA_FIN
        AND B.ID_ALMACEN = ".$id_almacen." AND B.ID_ARTICULO = ".$id_articulo." AND TO_CHAR(B.FECHA_INICIO,'YYYY-MM-DD') = '".$fecha."'
        WHERE   ID_HORA ".$dato." AND A.ESTADO = 1 ORDER BY ID_HORA ";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function ShowPedidoEmpresa($id_pedido){
        $sql = "SELECT 
                    A.ID_ANHO, B.ID_EMPRESA 
                FROM PEDIDO_REGISTRO A JOIN CONTA_ENTIDAD B
                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_PEDIDO = ".$id_pedido." ";
        $query = DB::select($sql);
        return $query;
    }
        /* aagregar eserva sin sim */
        public static function addOrdersDetailsShedule($data)
        {
            $result = DB::table('PEDIDO_RESERVA')->insert($data);
            return $result;
        }
        public static function updateOrdersDetailsShedule($data, $id_reserva)
  
        {
                $result = DB::table('PEDIDO_RESERVA')
                    ->where('ID_RESERVA', $id_reserva)
                    ->update($data);
                    return $result;
        }
    

        public static function listOrdersDetailsShedule($per_page){
            // dd('hoals');
            $query = DB::table('PEDIDO_RESERVA')
            ->join('MOISES.PERSONA', 'PEDIDO_RESERVA.ID_PERSONA', '=', 'MOISES.PERSONA.ID_PERSONA')
            ->join('CONTA_MES', 'PEDIDO_RESERVA.ID_MES', '=', 'CONTA_MES.ID_MES')
            ->select('PEDIDO_RESERVA.ID_RESERVA',
            'PEDIDO_RESERVA.ID_ANHO',
            'CONTA_MES.NOMBRE AS MES',
            'PEDIDO_RESERVA.ID_PERSONA AS ID_PERSONA',
            DB::raw("(MOISES.PERSONA.NOMBRE || ' ' ||  MOISES.PERSONA.PATERNO || ' ' || MOISES.PERSONA.MATERNO) AS NOMBRES"),
            'PEDIDO_RESERVA.CLIENTE',
            'PEDIDO_RESERVA.DETALLE',
            'PEDIDO_RESERVA.CANTIDAD',
            'PEDIDO_RESERVA.PRECIO',
            'PEDIDO_RESERVA.IMPORTE',
            'PEDIDO_RESERVA.FECHA',
            'PEDIDO_RESERVA.HORA_INICIO',
            'PEDIDO_RESERVA.HORA_FIN',
            'PEDIDO_RESERVA.PAGADO',
            'PEDIDO_RESERVA.ESTADO')
            ->orderBy('PEDIDO_RESERVA.ID_RESERVA', 'DESC')->paginate((int)$per_page); 

    //         $query = "SELECT 
    //         A.ID_RESERVA,
    //         A.ID_ANHO,
    //         C.NOMBRE MES, 
    //         A.ID_PERSONA, 
    //         (B.NOMBRE || ' ' || B.PATERNO || ' ' || B.MATERNO) AS NOMBRES_PERSONA,
    //         A.CLIENTE, 
    //         A.DETALLE, 
    //         A.CANTIDAD, 
    //         A.PRECIO, 
    //         A.IMPORTE, 
    //         A.FECHA, 
    //         A.HORA_INICIO, 
    //         A.HORA_FIN,
    //         A.ESTADO
    //  FROM PEDIDO_RESERVA A, MOISES.PERSONA B, CONTA_MES C
    //  WHERE A.ID_PERSONA = B.ID_PERSONA
    //  AND A.ID_MES = C.ID_MES
    //  ORDER BY A.ID_RESERVA";
    //             $oQuery = DB::select($query);
    //             $rst = $oQuery->paginate((int)$per_page);
  
                return $query;
        }
        public static function deleteOrdersDetailsShedule($id_reserva)
        {
            
            $query = DB::table('PEDIDO_RESERVA')->where('ID_RESERVA', $id_reserva)->delete();
            return $query;
        }

        public static function listGrassScheduleReports($id_almacen,$id_articulo,$fecha){
            //para las siguientes fechas actualizadas
            // dd('ffff');
            $query = "SELECT null AS ID_PEDIDO, null AS NUMERO,
            (TO_CHAR(id_cliente) || ' ' || CLIENTE) CLIENTE, FECHA, DETALLE, CANTIDAD, PRECIO, HORA_INICIO, HORA_FIN, ID_PERSONA, ID_ARTICULO, ID_ALMACEN, ESTADO, PAGADO,
            (CASE WHEN ESTADO = 1 THEN 'E' ELSE 'I' END) AS TIPO
            FROM PEDIDO_RESERVA
            WHERE ID_ALMACEN = ".$id_almacen." AND ID_ARTICULO = ".$id_articulo." AND TO_CHAR(FECHA, 'YYYY-MM-DD') = '".$fecha."'
            UNION ALL
            SELECT B.ID_PEDIDO AS ID_PEDIDO, B.NUMERO AS NUMERO, (PKG_ORDERS.FC_NOMBRE_AREA(B.ID_AREAORIGEN)) AS CLIENTE, FECHA_INICIO, DETALLE, CANTIDAD, PRECIO, HORA_INICIO, HORA_FIN,
            null AS ID_PERSONA, ID_ARTICULO, ID_ALMACEN, null AS ESTADO , 'INTERNO' AS PAGADO,   'I'  AS TIPO
            FROM PEDIDO_DETALLE A, PEDIDO_REGISTRO B
            WHERE A.ID_PEDIDO = B.ID_PEDIDO
            AND A.ID_ALMACEN = ".$id_almacen." AND A.ID_ARTICULO = ".$id_articulo." AND TO_CHAR(FECHA_INICIO, 'YYYY-MM-DD') = '".$fecha."'
            AND B.ESTADO NOT IN (3)
            ORDER BY FECHA DESC";

            // $query = "SELECT TO_NUMBER('') AS ID_PEDIDO, TO_NUMBER('') AS NUMERO,
            // (TO_NUMBER('') || '' || CLIENTE) CLIENTE, FECHA, DETALLE, CANTIDAD, PRECIO, HORA_INICIO, HORA_FIN, ID_PERSONA, ID_ARTICULO, ID_ALMACEN, ESTADO, PAGADO,
            // (CASE WHEN ESTADO = 1 THEN 'E' ELSE 'I' END) AS TIPO
            // FROM PEDIDO_RESERVA
            // WHERE ID_ALMACEN = ".$id_almacen." AND ID_ARTICULO = ".$id_articulo." AND TO_CHAR(FECHA, 'YYYY-MM-DD') = '".$fecha."'
            // UNION ALL
            // SELECT B.ID_PEDIDO AS ID_PEDIDO, B.NUMERO AS NUMERO, (PKG_ORDERS.FC_NOMBRE_AREA(B.ID_AREAORIGEN)) AS CLIENTE, FECHA_INICIO, DETALLE, CANTIDAD, PRECIO, HORA_INICIO, HORA_FIN,
            // TO_NUMBER(''), ID_ARTICULO, ID_ALMACEN, '' , (CASE WHEN 'NULL' = 'NULL' THEN 'INTERNO' ELSE '' END) AS PAGADO,  (CASE WHEN 'NULL' = 'NULL' THEN 'I' ELSE 'I' END) AS TIPO
            // FROM PEDIDO_DETALLE A, PEDIDO_REGISTRO B
            // WHERE A.ID_PEDIDO = B.ID_PEDIDO
            // AND ID_ALMACEN = ".$id_almacen." AND ID_ARTICULO = ".$id_articulo." AND TO_CHAR(FECHA_INICIO, 'YYYY-MM-DD') = '".$fecha."'
            // ORDER BY FECHA DESC";

            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function showOrders($id_pedido)
  
        {
                $result = DB::table('PEDIDO_REGISTRO')
                    ->where('ID_PEDIDO', $id_pedido )
                    ->get()->shift();

                    // dd($result);
                    return $result;
        }
    public static function listTypesCars(){
        $sql = "SELECT 
                        DISTINCT A.ID_TIPOVEHICULO,B.NOMBRE, A.ASIENTOS
                FROM PEDIDO_VEHICULO A JOIN TIPO_VEHICULO B
                ON A.ID_TIPOVEHICULO = B.ID_TIPOVEHICULO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function getArticlesAlmacen($id_anho, $id_almacen){
        
        $sql = "SELECT  A.ID_ARTICULO,
        B.NOMBRE,
        A.CODIGO,
        A.COSTO,
        A.STOCK_ACTUAL,
        A.ID_ALMACEN,
        C.NOMBRE AS UNIDAD_MEDIDA,
        C.ES_DECIMAL,
        DECODE(A.ID_TIPOIGV,'10',1,4) as ID_CTIPOIGV,
        B.IMAGEN_URL as IMAGEN_URL
        FROM INVENTARIO_ALMACEN_ARTICULO A, INVENTARIO_ARTICULO B, INVENTARIO_UNIDAD_MEDIDA C
        WHERE A.ID_ARTICULO = B.ID_ARTICULO
        AND B.ID_UNIDADMEDIDA = C.ID_UNIDADMEDIDA
        AND A.ID_ALMACEN = ".$id_almacen."
        AND A.ID_ANHO = ".$id_anho."
        ORDER BY A.CODIGO, B.NOMBRE ";
        $query = DB::select($sql);
        // dd( $query);
        return $query;
    }
// para buscar trabajador en pedidos
    public static function searchPersonaTrabajador($searchs) {
        $sql = DB::table('moises.persona_natural_trabajador as pnt')
        ->join('moises.persona_natural as pn', 'pnt.id_persona', '=', 'pn.id_persona')
        ->join('moises.persona as p', 'pnt.id_persona', '=', 'p.id_persona')
        ->join('moises.condicion_laboral as cl', 'pnt.id_condicion_laboral', '=', 'cl.id_condicion_laboral')
        ->join('moises.situacion_trabajador as st', 'pnt.id_situacion_trabajador', '=', 'st.id_situacion_trabajador')
        ->whereraw("(upper(p.nombre) like upper('%".$searchs."%')
                    or upper(p.nombre ||' ' || p.paterno ) like upper('%".$searchs."%')
                    or upper(p.nombre ||' ' || p.materno ) like upper('%".$searchs."%')
                    or upper(p.paterno ||' ' || p.materno ) like upper('%".$searchs."%')
                    or pn.num_documento like '%".$searchs."%')")
        ->select('pnt.id_persona',
                 DB::raw("(p.nombre ||' '|| p.paterno ||' '||p.materno) nombres"),
                 'pn.num_documento',
                 'cl.nombre as estado_laboral',
                 DB::raw("(CASE WHEN cl.ID_CONDICION_LABORAL = 'M' THEN 'Misionero' WHEN cl.ID_CONDICION_LABORAL = 'C' THEN 'Contratado'  WHEN cl.ID_CONDICION_LABORAL = 'E' THEN 'Empleado' WHEN cl.ID_CONDICION_LABORAL = 'P' THEN 'Practicante' WHEN cl.ID_CONDICION_LABORAL = 'MFL' THEN 'Conv. Juv.' ELSE '' END) estado_laboral2"),
                 'st.nombre_corto as estado',
                 'pn.celular')
        ->get();
        return $sql;
    }
    public static function deptoGrasSintetico($id_entidad) {
        $id_depto = 12010112;
        $sql = DB::table('inventario_almacen as a')
        ->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea')
        ->where('a.id_entidad', $id_entidad)
        ->where('b.id_depto', $id_depto)
        ->select('a.id_sedearea', 'a.nombre', 'a.id_almacen')
        ->get()->shift();
        return $sql;
    }
    public static function searchPersonaTrabajadorVarios($searchs) {
        $sql = DB::table('moises.vw_persona_natural_trabajador as pnt')
        ->join('moises.persona_natural as pn', 'pnt.id_persona', '=', 'pn.id_persona')
        ->whereraw("(upper(pnt.nombre) like upper('%".$searchs."%')
                    or upper(pnt.nombre ||' ' || pnt.paterno ) like upper('%".$searchs."%')
                    or upper(pnt.nombre ||' ' || pnt.materno ) like upper('%".$searchs."%')
                    or upper(pnt.paterno ||' ' || pnt.materno ) like upper('%".$searchs."%')
                    or pn.num_documento like '%".$searchs."%')")
        ->select('pnt.id_persona',
                 DB::raw("(pnt.nombre ||' '|| pnt.paterno ||' '||pnt.materno) nombres"),
                 'pn.num_documento',
                 DB::raw(" ' ' as estado_laboral2"),
                 DB::raw(" ' ' as estado_laboral"),
                 DB::raw(" ' ' as estado"),
                 DB::raw("(select pt.num_telefono from moises.persona_telefono pt where pt.id_persona = pn.id_persona and pt.id_tipotelefono = 5 and pt.es_activo = 1 and ROWNUM = 1) as celular"))
        ->distinct()
        ->get();
        return $sql;
    }
    ///////////////////////////////////////////////////////////////////////////////////
    public static function deptoToIdDepto($id_entidad,  $id_depto) {
        // dd($id_depto);
        $sql = "SELECT a.ID_SEDEAREA, 
        a.nombre, a.ID_ALMACEN from INVENTARIO_ALMACEN a 
        join  ORG_SEDE_AREA b on a.ID_SEDEAREA=b.ID_SEDEAREA where a.ID_ENTIDAD = ".$id_entidad." and b.ID_DEPTO = '".$id_depto."' 
        and a.id_parent is null
        and a.id_existencia in (1,2,6)
        ORDER BY ID_EXISTENCIA ASC";
        $query = collect(DB::select($sql))->shift();
        // dd( $query);
        return $query;
        // DB::table('inventario_almacen as a')
        // ->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea')
        // ->where('a.id_entidad', $id_entidad)
        // ->where('b.id_depto', $id_depto)
        // ->select('a.id_sedearea', 'a.nombre', 'a.id_almacen')
        // ->orderBy('a.id_existencia')
        // ->get();
        // return $sql;
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    public static function searchsArticlesAlmacen($id_anho, $id_almacen, $searchs){
        $sql = DB::table('inventario_almacen_articulo as a')
        ->join('inventario_articulo as b', 'a.id_articulo', '=', 'b.id_articulo')
        ->join('inventario_unidad_medida as c', 'b.id_unidadmedida', '=', 'c.id_unidadmedida')
        ->leftJoin('venta_precio as d', 'a.id_almacen', '=', DB::raw("d.id_almacen and a.id_articulo = d.id_articulo and a.id_anho = d.id_anho"))
        ->where('a.id_almacen', $id_almacen)
        ->where('a.id_anho', $id_anho)
        ->whereraw(ComunData::fnBuscar('b.nombre').' like '.ComunData::fnBuscar("'%".$searchs."%'"))
        ->select('a.id_articulo',
                'a.id_tipoigv',
                'b.nombre',
                'a.codigo',
                'a.costo',
                'a.stock_actual',
                'a.id_almacen',
                'c.nombre as unidad_medida',
                'c.es_decimal',
                DB::raw("nvl(nvl(d.precio,a.costo),0) as precio"),
                DB::raw("decode(a.id_tipoigv,'10',1,4) as id_ctipogv"),
                'b.imagen_url as imagen')
        ->orderBy('b.nombre', 'a.codigo', 'desc')
        ->get();
        return $sql;
    }
    public static function updateDetailsAlmacen($id_detalle, $request)
    {
        $id_almacen = $request->id_almacen;
        $id_articulo = $request->id_articulo;
        $cantidad = $request->cantidad;
        $precio = $request->precio;
        $result = DB::table('pedido_detalle')
            ->where('id_detalle', $id_detalle)
            ->update([
                'id_almacen' => $id_almacen,
                'id_articulo' => $id_articulo,
                'cantidad' => $cantidad,
                'precio' => $precio,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }
    public static function OrdersId($id_pedido){
        
        $query = DB::table('VW_ORDERS_PENDING')
                ->where('ID_PEDIDO', '=', $id_pedido)
                ->select('ID_PEDIDO',
                    'PID_PEDIDO',
                    'ID_TIPOPEDIDO',
                    'NOMBRE_TIPOPEDIDO',
                    'ID_ACTIVIDAD',
                    'NOMBRE_ACTIVIDAD',
                    'ID_AREAORIGEN',
                    'NOMBRE_AREAORIGEN',
                    'ID_AREADESTINO',
                    'NOMBRE_AREADESTINO',
                    DB::raw("regexp_substr(NOMBRE_AREADESTINO,'[^ - ]+',  1) as id_depto_destino, (SELECT nombre FROM CONTA_MES WHERE ID_MES = VW_ORDERS_PENDING.ID_MES) MESNOMBRE"),
                    'NUMERO',
                    'ACUERDO',
                    'CODIGO',
                    'FECHA_PEDIDO',
                    'FECHA_ENTREGA',
                    'MOTIVO',
                    'ESTADO_PORCENTAJE',
                    'ACCION',
                    'REVISADO',
                    'ID_PASO_ACTUAL',
                    'ESTADO',
                    'ID_PERSONA',
                    'USUARIO',
                    DB::raw("(SELECT (P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO) AS USUARIOS
                    FROM MOISES.PERSONA  P WHERE  VW_ORDERS_PENDING.ID_PERSONA = P.ID_PERSONA ) AS USUARIOS"),
                    DB::raw("(CASE WHEN ESTADO_PORCENTAJE = '25' THEN 'Registrado'
                    WHEN ESTADO_PORCENTAJE = '50' THEN 'Aprobado'
                    WHEN ESTADO_PORCENTAJE = '75' THEN 'Autorizado' 
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '0' THEN 'En programacion'
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '1'  THEN 'Ejecutado'
                    WHEN ESTADO_PORCENTAJE = '100' AND ESTADO = '1' THEN 'Ejecutado'
                    WHEN ESTADO = '3' THEN 'Rechazado' ELSE '' END) as proceso"),
                    'ESTADO_RUN', 'ID_MES', 'ID_ANHO', 'LLAVE')
              
                ->first();

            
    
        return $query;
        // return $rst;
    }
    public static function listMyOrdersReportes($id_entidad,$id_persona,$codigo,$per_page, $proceso, $id_anho, $id_mes, $area, $numero){
        
        $query = DB::table('VW_ORDERS_PENDING')
            ->where('ID_ENTIDAD', '=', $id_entidad)
                // ->where('ID_PERSONA', '=', $id_persona)
                ->where('CODIGO', '=', $codigo)
                ->whereNotNull('NUMERO');
             
                // ->whereraw("ESTADO IN ('0','1')")
                // ->whereraw("ESTADO IN ('0','1')");
                // $query->whereraw("ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
                $query->select('ID_PEDIDO',
                    'PID_PEDIDO',
                    'ID_TIPOPEDIDO',
                    'NOMBRE_TIPOPEDIDO',
                    'ID_ACTIVIDAD',
                    'NOMBRE_ACTIVIDAD',
                    'ID_AREAORIGEN',
                    'NOMBRE_AREAORIGEN',
                    'ID_AREADESTINO',
                    'NOMBRE_AREADESTINO',
                    'TIPO_ARTICULO',
                    DB::raw("regexp_substr(NOMBRE_AREADESTINO,'[^ - ]+',  1) as id_depto_destino, (SELECT nombre FROM CONTA_MES WHERE ID_MES = VW_ORDERS_PENDING.ID_MES) MESNOMBRE"),
                    'NUMERO',
                    'ACUERDO',
                    'CODIGO',
                    'FECHA_PEDIDO',
                    'FECHA_ENTREGA',
                    'MOTIVO',
                    'ESTADO_PORCENTAJE',
                    'ACCION',
                    'REVISADO',
                    'ID_PASO_ACTUAL',
                    'ESTADO',
                    'ID_PERSONA',
                    'USUARIO',
                    DB::raw("(SELECT (P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO) AS USUARIOS
                    FROM MOISES.PERSONA  P WHERE  VW_ORDERS_PENDING.ID_PERSONA = P.ID_PERSONA ) AS USUARIOS"),
                    DB::raw("(CASE WHEN ESTADO_PORCENTAJE = '25' THEN 'Registrado'
                    WHEN ESTADO_PORCENTAJE = '50' THEN 'Aprobado'
                    WHEN ESTADO_PORCENTAJE = '75' THEN 'Autorizado' 
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '0' THEN 'En programacion'
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '1'  THEN 'Ejecutado'
                    WHEN ESTADO_PORCENTAJE = '100' AND ESTADO = '1' THEN 'Ejecutado'
                    WHEN ESTADO = '3' THEN 'Rechazado' ELSE '' END) as proceso"),
                    'ESTADO_RUN', 'ID_MES', 'ID_ANHO', 'LLAVE')
            ->orderBy('ID_PEDIDO',"desc");

            if ($id_anho) {
                $query->where('ID_ANHO', $id_anho);
            }
            if ($id_mes) {
                $query->where('ID_MES', $id_mes);
            }
            $sumDataSet = ", sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOCP'
                        THEN 1
                      ELSE 0 END)                          registrado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOAP' AND ESTADO_PORCENTAJE =  '50'
                        THEN 1
                      ELSE 0 END)                            aprobado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = '75'
                        THEN 1
                      ELSE 0 END)                            autorizado
                    , sum(CASE
                    WHEN ESTADO = 1 AND ESTADO_RUN = 1
                        THEN 1
                      ELSE 0 END)                            ejecutado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = 85
                      THEN 1
                    ELSE 0 END)                            programacion
                  ,sum(CASE
                  WHEN ESTADO = 3 AND ESTADO_RUN = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";
        if ($proceso == 'RE') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '0');
            // $query->where(DB::raw("FC_ACCION_PROCESO_PREVIOUS(VW_ORDERS_PENDING.ESTADO_PORCENTAJE)"), "25");
            $query
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ACCION', '=', 'FOCP');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOCP'
                        THEN 1
                        ELSE 0 END)                          registrado";

        } else if ($proceso == 'AP') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ACCION', '=', 'FOAP')
            ->where('ESTADO_PORCENTAJE', '=', '50');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOAP' AND ESTADO_PORCENTAJE =  '50'
                        THEN 1
                      ELSE 0 END)                            aprobado";
        } else if ($proceso == 'AU') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where('VW_ORDERS_PENDING.ESTADO_PORCENTAJE', '=', '75')
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ESTADO_PORCENTAJE', '=', '75');
            // ->where('VW_ORDERS_PENDING.ACCION', '=', 'FOAP');
            $sumDataSet = ", sum(CASE
                        WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = '75'
                        THEN 1
                      ELSE 0 END)                            autorizado";
        } else if ($proceso == 'EJ') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query
            ->where('ESTADO', '=', '1')
            ->where('ESTADO_RUN', '=', '1');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND ESTADO_RUN = 1
                        THEN 1
                      ELSE 0 END)                            ejecutado";
        } else if ($proceso == 'PR') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ESTADO_PORCENTAJE', '=', '85');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = 85
                        THEN 1
                      ELSE 0 END)                            programacion";
        } else if ($proceso == 'R') {

            $query->where('VW_ORDERS_PENDING.ESTADO', '3');
            $query->where('VW_ORDERS_PENDING.ESTADO_RUN', '3');
            
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 3 AND ESTADO_RUN = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";

        }
        if($area==='D'){
            $query->whereraw("VW_ORDERS_PENDING.ID_AREADESTINO IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
            $query->whereraw("(VW_ORDERS_PENDING.NUMERO LIKE '%".$numero."%')");
        }else if($area==='O') {
            $query->whereraw("VW_ORDERS_PENDING.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
            $query->whereraw("(VW_ORDERS_PENDING.NUMERO LIKE '%".$numero."%')");
        }
        if (!$proceso  or $proceso == '') {
            $query->whereraw("ESTADO IN ('0','1')");
        }


            $dataSet = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                DB::raw("
                    mesnombre as mes,
                    id_mes
                ".$sumDataSet
                )
            )
            ->groupBy('id_mes', 'mesnombre')
            ->orderBy('id_mes')
            ->get();

            $rst = $query->paginate((int)$per_page);

            // $dataSet = ['1','2'];
            $custom = collect([
                'dataset' => $dataSet,
            ]);
    
            return $custom->merge($rst);
        // return $rst;
    }
    public static function deleteOrdersDetailsMovilidad($id_movilidad)
    {
        try
        {
            $result = DB::table('pedido_movilidad')
                ->where('id_movilidad', '=', $id_movilidad)
                ->delete();
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function listMyOrdersReportesTotal($id_entidad,$id_depto, $codigo,$per_page, $proceso, $id_anho, $id_mes, $numero, $id_departamento_origen){
        $query = DB::table('VW_ORDERS_PENDING')
            ->where('ID_ENTIDAD', '=', $id_entidad)
                ->where('ID_DEPTO', $id_depto)
                ->where('CODIGO', '=', $codigo)
                ->whereraw("(NUMERO LIKE '%".$numero."%')")
                ->whereraw("(ID_DEPARTAMENTO_ORIGEN LIKE '%".$id_departamento_origen."%')")
                ->whereNotNull('NUMERO');
                // ->whereraw("ESTADO IN ('0','1')")
                // ->whereraw("ESTADO IN ('0','1')");
                // $query->whereraw("ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
                $query->select('ID_PEDIDO',
                    'PID_PEDIDO',
                    'ID_TIPOPEDIDO',
                    'NOMBRE_TIPOPEDIDO',
                    'ID_ACTIVIDAD',
                    'NOMBRE_ACTIVIDAD',
                    'ID_AREAORIGEN',
                    'NOMBRE_AREAORIGEN',
                    'ID_AREADESTINO',
                    'NOMBRE_AREADESTINO',
                    DB::raw("regexp_substr(NOMBRE_AREADESTINO,'[^ - ]+',  1) as id_depto_destino, (SELECT nombre FROM CONTA_MES WHERE ID_MES = VW_ORDERS_PENDING.ID_MES) MESNOMBRE"),
                    'NUMERO',
                    'ACUERDO',
                    'CODIGO',
                    'FECHA_PEDIDO',
                    'FECHA_ENTREGA',
                    'MOTIVO',
                    'ESTADO_PORCENTAJE',
                    'ACCION',
                    'REVISADO',
                    'ID_PASO_ACTUAL',
                    'ESTADO',
                    'ID_PERSONA',
                    'USUARIO',
                    'ID_DEPARTAMENTO_ORIGEN',
                    DB::raw("(SELECT (P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO) AS USUARIOS
                    FROM MOISES.PERSONA  P WHERE  VW_ORDERS_PENDING.ID_PERSONA = P.ID_PERSONA ) AS USUARIOS"),
                    DB::raw("(CASE WHEN ESTADO_PORCENTAJE = '25' THEN 'Registrado'
                    WHEN ESTADO_PORCENTAJE = '50' THEN 'Aprobado'
                    WHEN ESTADO_PORCENTAJE = '75' THEN 'Autorizado' 
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '0' THEN 'En programacion'
                    WHEN ESTADO_PORCENTAJE = '85' AND ESTADO = '1'  THEN 'Ejecutado'
                    WHEN ESTADO_PORCENTAJE = '100' AND ESTADO = '1' THEN 'Ejecutado'
                    WHEN ESTADO = '3' THEN 'Rechazado' ELSE '' END) as proceso"),
                    'ESTADO_RUN', 'ID_MES', 'ID_ANHO', 'LLAVE')
            ->orderBy('ID_PEDIDO',"desc");

            if ($id_anho) {
                $query->where('ID_ANHO', $id_anho);
            }
            if ($id_mes) {
                $query->where('ID_MES', $id_mes);
            }
            $sumDataSet = ", sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOCP'
                        THEN 1
                      ELSE 0 END)                          registrado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOAP' AND ESTADO_PORCENTAJE =  '50'
                        THEN 1
                      ELSE 0 END)                            aprobado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = '75'
                        THEN 1
                      ELSE 0 END)                            autorizado
                    , sum(CASE
                    WHEN ESTADO = 1 AND ESTADO_RUN = 1
                        THEN 1
                      ELSE 0 END)                            ejecutado
                    , sum(CASE
                    WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = 85
                      THEN 1
                    ELSE 0 END)                            programacion
                  ,sum(CASE
                  WHEN ESTADO = 3 AND ESTADO_RUN = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";
        if ($proceso == 'RE') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '0');
            // $query->where(DB::raw("FC_ACCION_PROCESO_PREVIOUS(VW_ORDERS_PENDING.ESTADO_PORCENTAJE)"), "25");
            $query
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ACCION', '=', 'FOCP');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOCP'
                        THEN 1
                        ELSE 0 END)                          registrado";

        } else if ($proceso == 'AP') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ACCION', '=', 'FOAP')
            ->where('ESTADO_PORCENTAJE', '=', '50');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ACCION = 'FOAP' AND ESTADO_PORCENTAJE =  '50'
                        THEN 1
                      ELSE 0 END)                            aprobado";
        } else if ($proceso == 'AU') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where('VW_ORDERS_PENDING.ESTADO_PORCENTAJE', '=', '75')
            ->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ESTADO_PORCENTAJE', '=', '75');
            // ->where('VW_ORDERS_PENDING.ACCION', '=', 'FOAP');
            $sumDataSet = ", sum(CASE
                        WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = '75'
                        THEN 1
                      ELSE 0 END)                            autorizado";
        } else if ($proceso == 'EJ') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query
            ->where('ESTADO', '=', '1')
            ->where('ESTADO_RUN', '=', '1');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 1 AND ESTADO_RUN = 1
                        THEN 1
                      ELSE 0 END)                            ejecutado";
        } else if ($proceso == 'PR') {
            // $query->where('PEDIDO_REGISTRO.ESTADO', '1');
            $query->where('ESTADO', '=', '0')
            ->where('ESTADO_RUN', '=', '0')
            ->where('ESTADO_PORCENTAJE', '=', '85');
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 0 AND ESTADO_RUN = 0 AND ESTADO_PORCENTAJE = 85
                        THEN 1
                      ELSE 0 END)                            programacion";
        } else if ($proceso == 'R') {

            $query->where('VW_ORDERS_PENDING.ESTADO', '3');
            $query->where('VW_ORDERS_PENDING.ESTADO_RUN', '3');
            
            $sumDataSet = ", sum(CASE
                      WHEN ESTADO = 3 AND ESTADO_RUN = '3'
                        THEN 1
                      ELSE 0 END)                           rechazado";

        }

        if (!$proceso  or $proceso == '') {
            $query->whereraw("ESTADO IN ('0','1', '3')");
        }


            $dataSet = DB::table(DB::raw("(" . $query->toSql() . ") TABLA1"))
            ->mergeBindings($query)
            ->select(
                DB::raw("
                    mesnombre as mes,
                    id_mes
                ".$sumDataSet
                )
            )
            ->groupBy('id_mes', 'mesnombre')
            ->orderBy('id_mes')
            ->get();

            $rst = $query->paginate((int)$per_page);

            // $dataSet = ['1','2'];
            $custom = collect([
                'dataset' => $dataSet,
            ]);
    
            return $custom->merge($rst);
        // return $rst;
    }
    public static function horaReservaDe() {
        $data = DB::table('pedido_hora')
                ->where('estado', 1)
                ->select('id_hora', 'de', 'turno')
                ->orderBy('id_hora', 'asc')
                ->get();
        return $data;
        }
        public static function horaReservaA() {
            $data = DB::table('pedido_hora')
                    ->where('estado', 1)
                    ->select('id_hora', 'a', 'turno')
                    ->orderBy('id_hora', 'asc')
                    ->get();
            return $data;
        }

        public static function addServiciosDetalle($request){
            $id_pedido=$request->id_pedido;
            $cantidad=$request->cantidad;
            $id_almacen=$request->id_almacen;
            $id_articulo=$request->id_articulo;
            $detalle=$request->detalle;
            $precio=$request->precio;
            $hora_inicio=$request->hora_inicio;
            $hora_fin=$request->hora_fin;
            $fecha_inicio=$request->fecha_inicio;

            
          

            if ($hora_inicio > $hora_fin) {

                $response=[
                    'success'=> false,
                    'message'=>'La hora fin debe ser mayor a la hora inicio',
                ];
                return $response;
            } else {
                $query = "SELECT DISTINCT
                A.ID_HORA,
                 A.DE,
                 A.A,
                 A.DE||' - '||A.A AS HORARIO,
                 A.TURNO,
                 (CASE WHEN A.TURNO = 'N' THEN '80' ELSE '50' END) AS PRECIO,
                 TO_CHAR(SYSDATE,'HH24') HORA,
                (CASE WHEN trunc(to_date('".$fecha_inicio."', 'YYYY-MM-DD')) = trunc(sysdate) THEN (CASE WHEN (A.ID_HORA > TO_CHAR(SYSDATE, 'HH24')) THEN 'S' ELSE 'N' END)
                 WHEN trunc(to_date('".$fecha_inicio."', 'YYYY-MM-DD')) > trunc(sysdate) THEN 'S' ELSE 'N' END ) HABILITADO,
                (CASE WHEN B.HORA_INICIO IS NOT NULL THEN 'S' ELSE
                (SELECT DECODE(COUNT(1),1,'S','N') FROM PEDIDO_RESERVA X
                WHERE A.DE = X.HORA_INICIO
                AND A.A = X.HORA_FIN
                AND X.ID_ALMACEN = ".$id_almacen." AND X.ID_ARTICULO = ".$id_articulo." AND TO_CHAR(X.FECHA,'YYYY-MM-DD') = '".$fecha_inicio."')
                END) AS RESERVADO
                FROM PEDIDO_HORA A LEFT JOIN PEDIDO_DETALLE B
                ON A.DE = B.HORA_INICIO
                AND A.A = B.HORA_FIN
                AND B.ID_ALMACEN = ".$id_almacen." AND B.ID_ARTICULO = ".$id_articulo." AND TO_CHAR(B.FECHA_INICIO,'YYYY-MM-DD') = '".$fecha_inicio."'
                WHERE   (A.ID_HORA BETWEEN ".$hora_inicio." AND ".$hora_fin.")  AND A.ESTADO = 1 ORDER BY ID_HORA ";
        
                $oQuery = DB::select($query);

                $reservado = 0;
                $habilitado = 0;
                foreach ($oQuery as $row) {
                    if ($row->habilitado == 'N') {
                        $habilitado ++;
                    }
                    if ($row->reservado == 'S') {
                        $reservado ++;
                    }

                }
                $error = 0;
                $insert = 0;
                if ($habilitado == 0 and $reservado == 0) {

            
                    foreach ($oQuery as $row) {

                        // $id_detalle = ComunData::correlativo('pedido_detalle', 'id_detalle');
                        // if($id_detalle>0){
                            $save = DB::table('pedido_detalle')->insert(
                                [
                                // 'id_detalle' =>  $id_detalle,
                                'id_pedido' =>  $id_pedido,
                                'cantidad' =>  $cantidad,
                                'id_almacen' => $id_almacen,
                                'id_articulo' => $id_articulo,
                                'detalle' =>  $detalle,
                                'precio' => $precio === null ? $row->precio : $precio,
                                'hora_inicio' => $row->de,
                                'hora_fin' =>  $row->a,
                                'fecha_inicio' => $fecha_inicio,
                                ]
                            );
                            $insert ++;
                            if(!$save){
                                $error ++;
                            }
                        // } else {
                            // $error ++;
                        // }
                    }

                    if($error == 0 and $insert > 0){
                            $response=[
                                'success'=> true,
                                'message'=>'',
                            ];
                       
                    
                    }else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado',
                        ];
                    }
                } else {
                    if ($habilitado > 0 and $reservado > 0) {
                        $response=[
                            'success'=> false,
                            'message'=> $habilitado. ' hora(s) no se encuentra disponible y '. $reservado. ' hora(s) se encuentran reservados' ,
                        ];
                    } else {
                         if ($habilitado > 0) {
                            $response=[
                                'success'=> false,
                                'message'=> $habilitado. ' hora(s) no se encuentra disponible' ,
                            ];
                         }
                         if ($reservado > 0) {
                            $response=[
                                'success'=> false,
                                'message'=> $reservado. ' hora(s) se encuentran reservados' ,
                            ];
                         }

                    }
                    

                }
            }
        
            return $response;
        }

        public static function pedidosEjecutadosReportFirts($id_persona,  $id_voucher) {
            $sql = DB::table('pedido_registro as a')
            ->join('pedido_detalle as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->join('pedido_despacho as c', 'b.id_detalle', '=', 'c.id_detalle')
            ->leftjoin('venta as d' , 'a.id_pedido', '=', 'd.id_pedido')
            ->whereraw("a.id_areadestino IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )")
            ->where('c.id_voucher', $id_voucher)
            ->where('c.estado', '=', '1')
            ->select('a.id_pedido', 'a.numero', 'a.motivo',
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREAORIGEN) NOMBRE_AREAORIGEN"),
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREADESTINO) NOMBRE_AREADESTINO"),
            DB::raw("TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA_PEDIDO"),
            DB::raw("FC_NOMBRE_PERSONA(D.ID_CLIENTE) CLIENTE"),
            DB::raw("TO_CHAR(D.FECHA,'DD/MM/YYYY') AS FECHA_VENTA"),
            'd.serie', 'd.numero as numero_venta', 
            DB::raw("coalesce(d.total,0) as total"),
            DB::raw("TO_CHAR(D.TOTAL,'999,999,999.99') AS IMPORTE"), 'c.id_voucher', DB::raw("coalesce(D.TOTAL, 0) as importe_sumar"))
            ->orderBy('a.id_pedido')
            ->distinct()
            ->get();
            return $sql;
            // dd();
        }
        public static function pedidosEjecutadosReport($id_persona, $id_pedido,  $id_voucher) {
            $sql = DB::table('pedido_registro as a')
            ->join('pedido_detalle as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->join('pedido_despacho as c', 'b.id_detalle', '=', 'c.id_detalle')
            ->leftjoin('venta as d' , 'a.id_pedido', '=', 'd.id_pedido')
            ->whereraw("a.id_areadestino IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )")
            ->where('a.id_pedido', $id_pedido)
            ->where('c.id_voucher', $id_voucher)
            ->where('c.estado', '=', '1')
            ->select('a.id_pedido', 'a.numero', 'a.motivo', 'a.id_areadestino', 'c.id_voucher',
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREAORIGEN) NOMBRE_AREAORIGEN"),
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREADESTINO) NOMBRE_AREADESTINO"),
            DB::raw("PKG_INVENTORIES.FC_ARTICULO(C.ID_ARTICULO) ARTICULO"),
            DB::raw("FC_NOMBRE_PERSONA(D.ID_CLIENTE) CLIENTE"),
            'b.cantidad as cantidad_solicitada', 'c.id_articulo', 'c.cantidad', 'c.precio', 'c.importe', 'd.serie', 'd.numero as numero_venta', 'd.total')
            ->orderBy('a.id_pedido', 'c.id_despacho')
            ->get();
            return $sql;
        }
        public static function orderSummaryParent($id_voucher,$id_depto, $id_mes) {
            $sql = DB::table('pedido_registro as a')
            ->join('VW_ORDERS_DETAILS as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->join('pedido_despacho as c', 'b.id_detalle', '=', DB::raw("(CASE b.TIPO WHEN 'D' THEN C.ID_DETALLE WHEN 'M' THEN C.ID_MOVILIDAD END)"))
            // ->join('pedido_despacho as c', 'b.id_detalle', '=', DB::raw("(CASE TIPO WHEN 'D' THEN C.ID_DETALLE WHEN 'M' THEN C.ID_MOVILIDAD END)"))
            ->join('conta_voucher as d', 'c.id_voucher', '=', 'd.id_voucher')
            ->join('org_sede_area as x', 'a.id_areadestino', '=', 'x.id_sedearea')
            ->whereraw("(c.id_voucher like '%".$id_voucher."%')")
            ->where('c.estado', '=', '1')
            ->where('d.id_mes', $id_mes)
            ->whereraw("a.id_tipopedido in (3,4,5,6)")
            ->whereNotNull('a.numero')
            ->whereraw("substr(x.id_depto,1,1) = '".$id_depto."'")
            ->select('a.id_pedido', 'a.numero', 'a.fecha', 'a.motivo', 'c.id_voucher',
            DB::raw("to_char(d.fecha, 'dd/MM/yyyy') fecha_voucher"),
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREAORIGEN) ORIGEN"),
            DB::raw("PKG_ORDERS.FC_NOMBRE_AREA (A.ID_AREADESTINO) DESTINO"),
             DB::raw("D.NUMERO||'-'||D.LOTE AS VOUCHER_LOTE"),
             DB::raw("SUM(C.IMPORTE) AS IMPORTE"), 'a.fecha_pedido', 'a.fecha_entrega', 
             DB::raw("(case when  a.id_tipopedido = '1' then 'Pedidos Interdepartamentales'
             when a.id_tipopedido = '3' then 'Servicios'
             when a.id_tipopedido = '4' then 'Articulos'
             when a.id_tipopedido = '5' then 'Movilidad'
             when a.id_tipopedido = '6' then 'Inversiones' else '' end) as tipo_pedido"))
            ->groupBy('a.id_pedido', 'a.numero', 'a.fecha', 'a.motivo', 'c.id_voucher', 'a.id_areaorigen', 'a.id_areadestino', 'd.fecha',
            DB::raw("D.NUMERO||'-'||D.LOTE"), 'a.fecha_pedido', 'a.fecha_entrega', 'a.id_tipopedido')
            ->orderBy('a.id_pedido')
            ->get();
            return $sql;
        }
        public static function asientoOrderDispaches($id_pedido) {
            $ql = DB::table('pedido_registro as a')
            ->join('pedido_asiento as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->where('a.id_pedido', $id_pedido)
            ->select('a.id_pedido', 'b.id_cuentaaasi', 'b.id_ctacte', 'b.id_depto', 'b.dc', 'b.porcentaje')
            ->get();
            return $ql;
        }
        public static function getDeptoOrigen($id_entidad, $departamento) {
            $ql = "SELECT * FROM (
                SELECT DISTINCT
                B.ID_DEPTO,(SELECT X.NOMBRE FROM CONTA_ENTIDAD_DEPTO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = B.ID_DEPTO) AS NOMBRE
                FROM
                PEDIDO_REGISTRO A JOIN ORG_SEDE_AREA B
                ON A.ID_AREAORIGEN = B.ID_SEDEAREA
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND ID_TIPOPEDIDO IN (3,4,5,6)
                ORDER BY ID_DEPTO
                ) WHERE
                (UPPER(NOMBRE) LIKE UPPER('%".$departamento."%') OR ID_DEPTO LIKE UPPER('%".$departamento."%'))
                ORDER BY ID_DEPTO";
               $query = DB::select($ql);
               // dd( $query);
               return $query;
        }

        public static function getOrderEjecutadoVoucherDetalle($id_pedido, $id_voucher, $estado) {
            $data = DB::table('pedido_registro as a')
            ->join('pedido_detalle as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->join('pedido_despacho as c', 'b.id_detalle', '=', 'c.id_detalle')
            // ->whereraw("a.id_areadestino IN (SELECT ID_SEDEAREA FROM eliseo.ORG_AREA_RESPONSABLE WHERE ID_PERSONA =  ".$id_persona.")")
            ->where('a.id_pedido', $id_pedido)
            ->where('c.id_voucher', $id_voucher)
            ->where('c.estado', $estado)
            ->select(
                'a.id_pedido',
                'b.id_detalle',
                'b.detalle as detalle_solicitado',
                'c.detalle as detalle_entregado',
                DB::raw("(SELECT NOMBRE FROM INVENTARIO_ARTICULO x WHERE x.ID_ARTICULO = b.ID_ARTICULO) nombre_articulo_solicitado"),
                'b.cantidad as cantidad_solicitada',
                'c.cantidad as cantidad_entregado',
                'c.precio', 'c.importe',
                DB::raw("(SELECT NOMBRE FROM INVENTARIO_ARTICULO x WHERE x.ID_ARTICULO = c.ID_ARTICULO) NOMBRE_ARTICULO_ENTREGADO"),
                DB::raw("(SELECT NOMBRE FROM INVENTARIO_ALMACEN y WHERE y.ID_ALMACEN = c.ID_ALMACEN)    NOMBRE_ALMACEN"))
            ->orderBy('b.id_detalle', 'desc')
            ->get();
            return $data;
        }
        public static function getOrderEjecutadoVoucherDetalleTotal($id_pedido, $id_voucher, $estado) {
            $total = DB::table('pedido_registro as a')
            ->join('pedido_detalle as b', 'a.id_pedido', '=', 'b.id_pedido')
            ->join('pedido_despacho as c', 'b.id_detalle', '=', 'c.id_detalle')
            // ->whereraw("a.id_areadestino IN (SELECT ID_SEDEAREA FROM eliseo.ORG_AREA_RESPONSABLE WHERE ID_PERSONA =  ".$id_persona.")")
            ->where('a.id_pedido', $id_pedido)
            ->where('c.id_voucher', $id_voucher)
            ->where('c.estado', $estado)
            ->select(
                DB::raw("SUM(c.importe) as total"))
            ->orderBy('a.id_pedido')
            ->get();
            return $total;
        }
        public static function updateCantidadPedidos($id_pedido, $request) {
            $id_detalle   =$request->id_detalle;
            $cantidad_anterior     =$request->cantidad_anterior;
            $cantidad_actual     =$request->cantidad_actual;

            $count = DB::table('eliseo.pedido_detalle')
            ->where('id_pedido', $id_pedido)
            ->where('id_detalle', $id_detalle)
            ->whereraw("cantidad_reg is null")
            ->count();

            if ($count == 1) {

                $result = DB::table('eliseo.pedido_detalle')
                ->where('id_pedido', $id_pedido)
                ->where('id_detalle', $id_detalle)
                ->update([
                    'cantidad' => $cantidad_actual, 
                    'cantidad_reg' => $cantidad_anterior]); 

            } else {
            $result = DB::table('eliseo.pedido_detalle')
                ->where('id_pedido', $id_pedido)
                ->where('id_detalle', $id_detalle)
                ->update([
                    'cantidad' => $cantidad_actual]); 
            }

        
            return $result;
        }
        public static function addPedidoDetalleAudio($request) {
            $recurso_file               =     $request->file('recurso_file');
            $recurso_name_file          =     $request->recurso_name_file;
            $recurso_ext_file           =     $request->recurso_ext_file;
            
            $boceto_file                =     $request->file('boceto_file');
            $boceto_name_file           =     $request->boceto_name_file;
            $boceto_ext_file            =     $request->boceto_ext_file;

            $referencia_file            =     $request->file('referencia_file');
            $referencia_name_file       =     $request->referencia_name_file;
            $referencia_ext_file        =     $request->referencia_ext_file;

            // dd($request, $boceto_file);

            $data = [
                // 'id_solicitud_mat_alum' => $id_solicitud_mat_alum,
                'id_pedido' => $request->id_pedido,
                'id_almacen' => $request->id_almacen,
                'id_articulo' => $request->id_articulo,
                'detalle' => $request->detalle,
                'cantidad' => $request->cantidad,
                'precio' => $request->precio,
                'importe' => $request->importe,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'objetivo' => $request->objetivo_evento,
                'publico' => $request->objetivo_publico,
                'tema' => $request->tema,
                'id_persona' => $request->id_persona_contacto,
                'ponente' => $request->ponente,
                'celular' => $request->celular,
                'plataforma' => $request->plataforma,
                'formato' => $request->formato,
                'descripcion' => $request->descripcion,
                'links' => $request->link,
            ];
        
             $idPedidoDetalle = DB::transaction(function() use($data) {
               DB::table('eliseo.pedido_detalle')->insert($data);
               return DB::getSequence()->currentValue('SQ_PEDIDO_DETALLE_ID');
             });
             if ($idPedidoDetalle) {
                 $type = '15';
                 if ($recurso_file) {
                    $recursoFile = OrdersData::saveFilePedidoDetalle($request->id_pedido, $idPedidoDetalle, $request, $recurso_file,  $recurso_name_file, $recurso_ext_file, $type);
                 }
                 if ($boceto_file) {
                    $bocetoFile = OrdersData::saveFilePedidoDetalle($request->id_pedido, $idPedidoDetalle, $request, $boceto_file, $boceto_name_file, $boceto_ext_file, $type);
                }
                if ($referencia_file) {
    
                    $referenciaFile = OrdersData::saveFilePedidoDetalle($request->id_pedido, $idPedidoDetalle, $request, $referencia_file, $referencia_name_file, $referencia_ext_file, $type);
                }
             }
             
     
            if ($idPedidoDetalle) {
                $result=[
                    'success'=> true,
                    'message'=>'Proceso completado exitosamente',
                    'data' => $idPedidoDetalle,
                ];
            } else {
                $result=[
                    'success'=> false,
                    'message'=>'No se completo el proceso',
                    'data' => '',
                ];
            }
        return $result;
        }
        public static function saveFilePedidoDetalle($id_pedido, $idPedidoDetalle, $request, $archivo, $name_file, $ext_file, $type) {
            // $formato          = $archivo->getClientOriginalExtension();
            // $name_file             = $archivo->getClientOriginalName();
            $size             = filesize($archivo);
            $folder           = $request->carpeta;
            $carpeta='';
            $fileAdjunto['nerror']=1;
            $tipo = $request->tipo;
            $estado = "1";
            if ($type == "15") {
                $carpeta = $folder.'audiovisuales';
            }
            // dd($formato,$name,$size, $carpeta);
            $fileAdjunto = ComunData::uploadFile($archivo, $carpeta); 
                if ($fileAdjunto['nerror']==0) { 
                    $url    = 'data_lamb_financial/'.$carpeta.'/'.$fileAdjunto['filename'];
                    $id_pfile = ComunData::correlativo('eliseo.pedido_file', 'id_pfile');
                    if ($id_pfile>0) {
                    $save = DB::table('eliseo.pedido_file')->insert([
                        "id_pfile" => $id_pfile,
                        "id_pedido" => $id_pedido,
                        "id_detalle" => $idPedidoDetalle,
                        "nombre" => $name_file,
                        "name_file" => $fileAdjunto['filename'],
                        "formato" => $ext_file,
                        "url" => $url,
                        "fecha" => DB::raw('sysdate'),
                        "tipo" => $type,
                        "tamanho" => $size,
                        "estado" => $estado,
                    ]);
                    if($save) {
                        $result = [
                            'nerror' => 0,
                            'message' => 'Creado',
                        ];
                    } else {
                        $resp = ComunData::deleteFilesDirectorio($carpeta, $fileAdjunto['filename'], 'E');
                        $result = [
                            'nerror' => 1,
                            'message' => 'Fallo',
                        ];
                    }
                } else {
                    $result=[
                        'nerror' => 1,
                        'message'=>'No se pudo generar correlativo',
                    ];
                }
                } else {
                    $result = [
                        'nerror' => 1,
                        'message' => $fileAdjunto['message'],
                    ];
                }
            
            return $result;
        }
        public static function filePedidoDetalle($id_detalle) {
            $ql = DB::table('eliseo.pedido_file as a')
            ->where('a.id_detalle', $id_detalle)
            ->select('a.*')
            ->get();
            return $ql;
        }
        public static function getTimeLimiteBlock($request, $id_entidad, $id_depto) {
            $modulo = DB::table('eliseo.lamb_modulo')->where('codigo', '=', $request->codigo_modulo)->select('id_modulo')->first(); 
            $ql = DB::table('eliseo.lamb_modulo_activo as a')
            ->where('a.id_entidad', '=', $id_entidad)
            ->where('a.id_depto', '=', $id_depto)
            ->where('a.id_anho', '=', $request->id_anho)
            ->where('a.id_mes', '=', $request->id_mes)
            ->where('a.id_modulo', '=', $modulo->id_modulo)
            ->where('a.estado', '=', '1')
            ->select(DB::raw("a.mensaje, a.estado, sysdate, a.fecha_limite,((a.fecha_limite - sysdate)*60*60*24) as segundos_restantes"))
            ->first();
            return $ql;
        }
}
