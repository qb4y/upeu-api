<?php

namespace App\Http\Data\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class ProcessData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function listProcess($id_entidad, $id_depto)
    {
        /*$sql = DB::table('PROCESS')->select('ID_PROCESO','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TIPOTRANSACCION','ID_PASO_INICIO','ID_PASO_FIN','NOMBRE','ESTADO')->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_DEPTO', $id_depto)->orderBy('ID_PROCESO')->get();*/
        $sql = "SELECT A.ID_PROCESO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_MODULO,B.NOMBRE AS MODULO,A.ID_TIPOTRANSACCION,
                        C.NOMBRE AS TRANSACCION,A.ID_PASO_INICIO,A.ID_PASO_FIN,A.NOMBRE,A.ESTADO,
                        (SELECT COUNT(X.ID_PASO) FROM PROCESS_PASO X WHERE X.ID_PROCESO = A.ID_PROCESO) AS CANTIDAD_PASOS
                FROM PROCESS A, LAMB_MODULO B, TIPO_TRANSACCION C
                WHERE A.ID_MODULO = B.ID_MODULO
                AND A.ID_TIPOTRANSACCION = C.ID_TIPOTRANSACCION 
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showProcess($id_proceso)
    {
        //$sql = DB::table('PROCESS')->select('ID_PROCESO','ID_ENTIDAD','ID_DEPTO','ID_MODULO','ID_TIPOTRANSACCION','ID_PASO_INICIO','ID_PASO_FIN','NOMBRE','ESTADO')->where('ID_PROCESO', $id_proceso)->get();     
        $sql = "SELECT A.ID_PROCESO,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_MODULO,B.NOMBRE AS MODULO,A.ID_TIPOTRANSACCION,
                        C.NOMBRE AS TRANSACCION,A.ID_PASO_INICIO,A.ID_PASO_FIN,A.NOMBRE,A.ESTADO 
                FROM PROCESS A, LAMB_MODULO B, TIPO_TRANSACCION C
                WHERE A.ID_MODULO = B.ID_MODULO
                AND A.ID_TIPOTRANSACCION = C.ID_TIPOTRANSACCION 
                AND A.ID_PROCESO = " . $id_proceso . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function addProcess($id_entidad, $id_depto, $id_modulo, $id_tipotransaccion, $nombre, $estado)
    {
        DB::table('PROCESS')->insert(
            array('ID_ENTIDAD' => $id_entidad, 'ID_DEPTO' => $id_depto, 'ID_MODULO' => $id_modulo,
                'ID_TIPOTRANSACCION' => $id_tipotransaccion, 'NOMBRE' => $nombre, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(ID_PROCESO) ID_PROCESO
                FROM PROCESS ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_proceso = $id->id_proceso;
        }
        $sql = ProcessData::showProcess($id_proceso);
        return $sql;
    }

    public static function updateProcess($id_proceso, $id_modulo, $id_tipotransaccion, $nombre, $estado)
    {
        DB::table('PROCESS')
            ->where('ID_PROCESO', $id_proceso)
            ->update([
                'ID_MODULO' => $id_modulo,
                'ID_TIPOTRANSACCION' => $id_tipotransaccion,
                'NOMBRE' => $nombre,
                'ESTADO' => $estado
            ]);
        $sql = ProcessData::showProcess($id_proceso);
        return $sql;
    }

    public static function deleteProcess($id_proceso)
    {
        DB::table('PROCESS')->where('ID_PROCESO', '=', $id_proceso)->delete();
    }

    public static function listProcessType()
    {
        $sql = "SELECT ID_TIPOPASO,NOMBRE FROM PROCESS_TIPOPASO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function listSteps($id_proceso)
    {
        $sql = "SELECT 
                A.ID_PASO,A.ID_PROCESO,A.ID_TIPOPASO,B.LLAVE AS LLAVE_TIPOPASO,A.NOMBRE,A.ORDEN,A.ESTADO,D.ID_COMPONENTE, D.LLAVE AS LLAVE_COMPONENTE
                FROM PROCESS_PASO A LEFT JOIN PROCESS_TIPOPASO B
                ON A.ID_TIPOPASO = B.ID_TIPOPASO
                LEFT JOIN PROCESS_COMPONENTE_PASO C
                ON A.ID_PASO = C.ID_PASO
                LEFT JOIN PROCESS_COMPONENTE D
                ON C.ID_COMPONENTE = D.ID_COMPONENTE
                --WHERE A.ID_TIPOPASO = B.ID_TIPOPASO
                --AND A.ID_PASO = C.ID_PASO
                --AND C.ID_COMPONENTE = D.ID_COMPONENTE
                WHERE A.ID_PROCESO = " . $id_proceso . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showSteps($id_paso)
    {
        $sql = "SELECT 
                A.ID_PASO,A.ID_PROCESO,A.ID_TIPOPASO,B.NOMBRE AS TIPOPASO,A.NOMBRE,A.ORDEN,A.ESTADO,D.ID_COMPONENTE, D.LLAVE AS LLAVE_COMPONENTE
                FROM PROCESS_PASO A LEFT JOIN PROCESS_TIPOPASO B
                ON A.ID_TIPOPASO = B.ID_TIPOPASO
                LEFT JOIN PROCESS_COMPONENTE_PASO C
                ON A.ID_PASO = C.ID_PASO
                LEFT JOIN PROCESS_COMPONENTE D
                ON C.ID_COMPONENTE = D.ID_COMPONENTE
                WHERE A.ID_PASO = " . $id_paso . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function addSteps($id_proceso, $id_tipopaso, $nombre, $orden, $estado)
    {
        DB::table('PROCESS_PASO')->insert(
            array('ID_PROCESO' => $id_proceso, 'ID_TIPOPASO' => $id_tipopaso, 'NOMBRE' => $nombre, 'ORDEN' => $orden, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(ID_PASO) ID_PASO
                FROM PROCESS_PASO ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_paso = $id->id_paso;
        }
        $sql = ProcessData::showSteps($id_paso);
        return $sql;
    }

    public static function updateSteps($id_proceso, $id_paso, $id_tipopaso, $nombre, $orden, $estado)
    {
        DB::table('PROCESS_PASO')
            ->where('ID_PASO', $id_paso)
            ->update([
                'ID_TIPOPASO' => $id_tipopaso,
                'NOMBRE' => $nombre,
                'ORDEN' => $orden,
                'ESTADO' => $estado
            ]);
        $sql = ProcessData::showSteps($id_paso);
        return $sql;
    }

    public static function deleteSteps($id_proceso, $id_paso)
    {
        DB::table('PROCESS_PASO')->where('ID_PASO', '=', $id_paso)->delete();
    }

    public static function addComponentsSteps($id_paso, $id_componente)
    {
        DB::table('PROCESS_COMPONENTE_PASO')->insert(
            array('ID_PASO' => $id_paso, 'ID_COMPONENTE' => $id_componente)
        );
    }

    public static function updateComponentsSteps($id_paso, $id_componente)
    {
        DB::table('PROCESS_COMPONENTE_PASO')
            ->where('ID_PASO', $id_paso)
            ->update([
                'ID_COMPONENTE' => $id_componente
            ]);
    }

    public static function deleteComponentsSteps($id_paso)
    {
        DB::table('PROCESS_COMPONENTE_PASO')->where('ID_PASO', '=', $id_paso)->delete();
    }

    public static function showComponentsSteps($id_paso)
    {
        $sql = DB::table('PROCESS_COMPONENTE_PASO')->select('ID_PASO', 'ID_COMPONENTE')->where('ID_PASO', $id_paso)->get();
        return $sql;
    }

    public static function showComponentsSteps1($id_paso, $id_componente)
    {
        $sql = DB::table('PROCESS_COMPONENTE_PASO')->select('ID_PASO', 'ID_COMPONENTE')->where('ID_PASO', $id_paso)->where('ID_COMPONENTE', $id_componente)->get();
        return $sql;
    }

    public static function listStepsRoles($id_paso)
    {
        $sql = DB::table('PROCESS_PASO_ROL')->select('ID_PASO', 'ID_ROL')->where('ID_PASO', $id_paso)->get();
        return $sql;
    }

    public static function addStepsRoles($id_paso, $id_rol)
    {
        DB::table('PROCESS_PASO_ROL')->insert(
            array('ID_PASO' => $id_paso, 'ID_ROL' => $id_rol)
        );
    }

    public static function deleteStepsRoles($id_paso)
    {
        DB::table('PROCESS_PASO_ROL')->where('ID_PASO', '=', $id_paso)->delete();
    }

    public static function listFlows($id_proceso, $id_paso_de)
    {
        $cond = "";
        if ($id_paso_de != "") {
            $cond = "AND A.ID_PASO = " . $id_paso_de . " ";
        }
        $sql = "SELECT 
                A.ID_FLUJO,A.ID_PROCESO,A.ID_PASO, 
                (SELECT X.NOMBRE FROM PROCESS_PASO X WHERE X.ID_PASO = A.ID_PASO) AS PASO_DE,
                A.TAG,
                A.ID_PASO_NEXT,
                (SELECT X.NOMBRE FROM PROCESS_PASO X WHERE X.ID_PASO = A.ID_PASO_NEXT) AS PASO_A,
                A.ID_COMPONENTE,
                (SELECT X.LLAVE FROM PROCESS_COMPONENTE X WHERE X.ID_COMPONENTE = A.ID_COMPONENTE ) LLAVE_COMPONENTE
                FROM PROCESS_FLUJO A
                WHERE A.ID_PROCESO = " . $id_proceso . " " . $cond . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showFlows($id_flujo)
    {
        $sql = "SELECT 
                A.ID_FLUJO,A.ID_PROCESO,A.ID_PASO,A.TAG,A.ID_PASO_NEXT, A.ID_COMPONENTE
                FROM PROCESS_FLUJO A
                WHERE A.ID_FLUJO = " . $id_flujo . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showFlowsNext($id_flujo)
    {
        $sql = "SELECT 
                A.ID_FLUJO,A.ID_PROCESO,A.ID_PASO,A.TAG,A.ID_PASO_NEXT, A.ID_COMPONENTE
                FROM PROCESS_FLUJO A
                WHERE A.ID_PASO_NEXT = " . $id_flujo . " ";
        $query = DB::select($sql);
        return $query;
    }

    public static function addFlows($id_proceso, $id_paso, $tag, $id_paso_next, $id_componente)
    {
        DB::table('PROCESS_FLUJO')->insert(
            array('ID_PROCESO' => $id_proceso, 'ID_PASO' => $id_paso, 'TAG' => $tag, 'ID_PASO_NEXT' => $id_paso_next, 'ID_COMPONENTE' => $id_componente)
        );
        // $query = "SELECT 
        //                 MAX(ID_FLUJO) ID_FLUJO
        //         FROM PROCESS_FLUJO ";
        // $oQuery = DB::select($query);
        // foreach($oQuery as $id){
        //     $id_flujo = $id->id_flujo;
        // }
        // $sql = ProcessData::showSteps($id_flujo);
        // return $sql;
    }

    public static function updateFlows($id_proceso, $id_flujo, $id_paso, $tag, $id_paso_next, $id_componente)
    {
        DB::table('PROCESS_FLUJO')
            ->where('ID_FLUJO', $id_flujo)
            ->update([
                'ID_PASO' => $id_paso,
                'ID_PASO_NEXT' => $id_paso_next,
                'TAG' => $tag,
                'ID_COMPONENTE' => $id_componente
            ]);
        $sql = ProcessData::showFlows($id_flujo);
        return $sql;
    }

    public static function deleteFlows($id_flujo)
    {
        DB::table('PROCESS_FLUJO')->where('ID_FLUJO', '=', $id_flujo)->delete();
    }

    public static function listComponents()
    {
        $sql = DB::table('PROCESS_COMPONENTE')->select('ID_COMPONENTE', 'NOMBRE', 'LLAVE', 'ESTADO')->orderBy('ID_COMPONENTE')->get();
        return $sql;
    }

    public static function showComponents($id_componente)
    {
        $sql = DB::table('PROCESS_COMPONENTE')->select('ID_COMPONENTE', 'NOMBRE', 'LLAVE', 'ESTADO')->where('ID_COMPONENTE', $id_componente)->get();
        return $sql;
    }

    public static function addComponets($nombre, $llave, $estado)
    {
        DB::table('PROCESS_COMPONENTE')->insert(
            array('NOMBRE' => $nombre, 'LLAVE' => $llave, 'ESTADO' => $estado)
        );
        $query = "SELECT 
                        MAX(ID_COMPONENTE) ID_COMPONENTE
                FROM PROCESS_COMPONENTE ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_componente = $id->id_componente;
        }
        $sql = ProcessData::showComponents($id_componente);
        return $sql;
    }

    public static function updateComponents($id_componente, $nombre, $llave, $estado)
    {
        DB::table('PROCESS_COMPONENTE')
            ->where('ID_COMPONENTE', $id_componente)
            ->update([
                'NOMBRE' => $nombre,
                'LLAVE' => $llave,
                'ESTADO' => $estado
            ]);
        $sql = ProcessData::showComponents($id_componente);
        return $sql;
    }

    public static function deleteComponets($id_componente)
    {
        DB::table('PROCESS_COMPONENTE')->where('ID_COMPONENTE', '=', $id_componente)->delete();
    }

    public static function listStepsEjecucion($id_proceso, $id_entidad, $id_user)
    {
        $sql = "SELECT 
                A.ID_PROCESO,A.ID_PASO,A.NOMBRE,A.ORDEN,FC_PROCESS_NAME(A.ID_PROCESO) AS NOMBRE_PROCESO,
                (SELECT Y.LLAVE FROM PROCESS_COMPONENTE_PASO X, PROCESS_COMPONENTE Y WHERE X.ID_COMPONENTE = Y.ID_COMPONENTE AND X.ID_PASO = A.ID_PASO ) LLAVE_COMPONENTE,
                C.NOMBRE AS TIPO_PASO,
                NVL((SELECT Y.NOMBRE FROM PROCESS_FLUJO X, PROCESS_PASO Y WHERE X.ID_PASO = Y.ID_PASO AND X.ID_PROCESO = A.ID_PROCESO AND X.ID_PASO_NEXT = A.ID_PASO AND Y.ID_TIPOPASO = 3),' ') INIT
                FROM PROCESS_PASO A, PROCESS_PASO_ROL B, PROCESS_TIPOPASO C
                WHERE A.ID_PASO = B.ID_PASO
                AND A.ID_TIPOPASO = C.ID_TIPOPASO
                AND ID_PROCESO = " . $id_proceso . "
                AND B.ID_ROL IN (SELECT ID_ROL FROM LAMB_USUARIO_ROL WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_PERSONA = " . $id_user . ")
                AND A.ID_TIPOPASO IN (2)
                ORDER BY A.ORDEN ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showStepsEjecucion($id_proceso, $id_paso, $id_entidad, $id_user)
    {
        $sql = "SELECT 
                A.ID_PROCESO,A.ID_PASO,A.NOMBRE,A.ORDEN,
                (SELECT Y.LLAVE FROM PROCESS_COMPONENTE_PASO X, PROCESS_COMPONENTE Y WHERE X.ID_COMPONENTE = Y.ID_COMPONENTE AND X.ID_PASO = A.ID_PASO ) LLAVE_COMPONENTE,
                C.NOMBRE AS TIPO_PASO
                FROM PROCESS_PASO A, PROCESS_PASO_ROL B, PROCESS_TIPOPASO C
                WHERE A.ID_PASO = B.ID_PASO
                AND A.ID_TIPOPASO = C.ID_TIPOPASO
                AND ID_PROCESO = " . $id_proceso . "
                AND A.ID_PASO = " . $id_paso . "
                AND B.ID_ROL IN (SELECT ID_ROL FROM LAMB_USUARIO_ROL WHERE ID_ENTIDAD = " . $id_entidad . " AND ID_PERSONA = " . $id_user . ")
                ORDER BY A.ORDEN ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showProcessByCode($codigo)
    {
        $sql = DB::table('PROCESS')
            ->where('NOMBRE', 'GESTION DE VALES')
//            ->where('CODIGO', $codigo)
            ->first();
        return $sql;
    }

    public static function nullingStepOperation($idOperation, $codeProcess)
    {
        $proceso_run_paso = null;
        $isDone = null;
        $pr_sql = "SELECT
                      PROCESS_RUN.*
                    FROM PROCESS_RUN
                      JOIN PROCESS ON PROCESS_RUN.ID_PROCESO = PROCESS.ID_PROCESO
                    WHERE PROCESS_RUN.ID_OPERACION = $idOperation
                          AND PROCESS.CODIGO = $codeProcess";
        $proceso_run = collect(DB::select($pr_sql))->first();
        if ($proceso_run) {
            $prd_sql = "SELECT
                      *
                    FROM PROCESS_PASO_RUN
                    WHERE PROCESS_PASO_RUN.ID_REGISTRO = $proceso_run->id_registro AND
                          FC_GET_LLAVE_COMPONENTE(PROCESS_PASO_RUN.ID_REGISTRO, PROCESS_PASO_RUN.ID_PASO, 1) =
                          'FVPR'";
            $proceso_run_paso = collect(DB::select($prd_sql))->first();
        }
        if ($proceso_run_paso) {
            $sql = "DELETE FROM PROCESS_PASO_RUN
                        WHERE PROCESS_PASO_RUN.ID_DETALLE = (
                          SELECT PROCESS_PASO_RUN.ID_DETALLE
                          FROM PROCESS_PASO_RUN
                          WHERE PROCESS_PASO_RUN.ID_REGISTRO = $proceso_run->id_registro 
                          AND PROCESS_PASO_RUN.ID_PASO = $proceso_run->id_paso_actual 
                          AND ROWNUM = 1
                        )
                ";
            $isDone = DB::select($sql);
            if ($isDone) {
                $updating = "UPDATE PROCESS_RUN
                    SET PROCESS_RUN.ID_PASO_ACTUAL = (
                      SELECT PROCESS_PASO_RUN.ID_PASO
                      FROM PROCESS_PASO_RUN
                      WHERE PROCESS_PASO_RUN.ID_REGISTRO = $proceso_run->id_registro
                            AND PROCESS_PASO_RUN.ID_PASO_NEXT = $proceso_run->id_paso_actual
                            AND ROWNUM = 1
                    )
                    WHERE PROCESS_RUN.ID_REGISTRO = $proceso_run->id_registro";
                $isDone = DB::select($updating);
            }
        }
        return $isDone;
    }

    public static function showProcessRun($id_proceso, $id_operacion)
    {
        $sql = "SELECT 
                        ID_REGISTRO,FECHA,DETALLE 
                FROM PROCESS_RUN
                WHERE ID_PROCESO = " . $id_proceso . "
                AND ID_OPERACION = " . $id_operacion . " ";
        $query = DB::select($sql);
        foreach ($query as $item) {
            $id_registro = $item->id_registro;
        }
        return $id_registro;
    }

    public static function listStepsEvents($id_proceso, $id_paso)
    {
        $sql = "SELECT 
                B.ID_PASO, B.NOMBRE, NVL(D.LLAVE,' ') AS LLAVE_COMPONENTE
                FROM PROCESS_FLUJO A LEFT JOIN PROCESS_PASO B
                ON A.ID_PASO_NEXT = B.ID_PASO
                LEFT JOIN PROCESS_COMPONENTE_PASO C
                ON B.ID_PASO = C.ID_PASO
                LEFT JOIN PROCESS_COMPONENTE D
                ON C.ID_COMPONENTE = D.ID_COMPONENTE
                WHERE A.ID_PROCESO = " . $id_proceso . "
                AND A.ID_PASO = " . $id_paso . "
                AND B.ID_TIPOPASO IN (5) ";
        $query = DB::select($sql);
        return $query;
    }

    public static function addRegistro($id_proceso, $id_vale)
    {
        $id_registro = 0;
        try {
            $data = ProcessData::showProcess($id_proceso);
            foreach ($data as $item) {
                $detalle = $item->nombre;
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PROCESS.SP_REGISTRA_PROCESO(:P_ID_PROCESO, :P_ID_OPERACION, :P_DETALLE, :P_ID_REGISTRO); end;");
            $stmt->bindParam(':P_ID_PROCESO', $id_proceso, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_OPERACION', $id_vale, PDO::PARAM_INT);
            $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_REGISTRO', $id_registro, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            $e->getCode();
        }
        return $id_registro;
    }

    public static function addRegistroPaso($id_user, $id_registro, $id_proceso, $id_paso, $id_vale)
    {
        $numero = 0;
        try {
            $data = ProcessData::showProcess($id_proceso);
            /*foreach ($data as $item){
                $detalle = $item->nombre;                
            }*/
            $data = ProcessData::showSteps($id_paso);
            foreach ($data as $item) {
                $detalle = $item->nombre;
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PROCESS.SP_REGISTRA_PASO(:P_ID_REGISTRO, :P_ID_PASO, :P_ID_PERSONA, :P_DETALLE, :P_NUMERO, :P_IP, :P_CLAVE, :P_VALOR); end;");
            $stmt->bindParam(':P_ID_REGISTRO', $id_registro, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PASO', $id_paso, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_STR);
            $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_INT);
            $stmt->bindParam(':P_IP', $detalle, PDO::PARAM_STR);
            $stmt->bindParam(':P_CLAVE', $id_proceso, PDO::PARAM_STR);
            $stmt->bindParam(':P_VALOR', $id_vale, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            $e->getCode();
        }
        return $id_registro;
    }

    public static function showVoucherCashProcessPasoRun($id, $id_entidad, $codigo)
    {
        $query = DB::table('CAJA_VALE')
            ->join('PROCESS_RUN', function ($join) use ($id_entidad) {
                $join->on('CAJA_VALE.ID_VALE', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PROCESS_RUN.ESTADO', '=', '0')
                    ->where('CAJA_VALE.ID_ENTIDAD', '=', $id_entidad);
                //->where('CAJA_VALE.ESTADO', '=', '0');
            })
            ->join('PROCESS', function ($join) use ($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->join('PROCESS_PASO_RUN', function ($join) {
                $join->on('PROCESS_RUN.ID_REGISTRO', '=', 'PROCESS_PASO_RUN.ID_REGISTRO')
                    ->on('PROCESS_RUN.ID_PASO_ACTUAL', '=', 'PROCESS_PASO_RUN.ID_PASO')
                    ->where('PROCESS_PASO_RUN.ESTADO', '=', "0");
            })
            ->select(
                'CAJA_VALE.ID_VALE', 'PROCESS_RUN.ID_REGISTRO', 'PROCESS_PASO_RUN.ID_DETALLE'
            )
            ->selectRaw("(select PROCESS_PASO.ID_PASO
                    from PROCESS_PASO
                             join PROCESS on PROCESS_PASO.ID_PROCESO = PROCESS.ID_PROCESO
                             join PROCESS_COMPONENTE_PASO on PROCESS_PASO.ID_PASO = PROCESS_COMPONENTE_PASO.ID_PASO
                             join PROCESS_COMPONENTE on PROCESS_COMPONENTE_PASO.ID_COMPONENTE = PROCESS_COMPONENTE.ID_COMPONENTE
                    where PROCESS.CODIGO = $codigo
                      and PROCESS_COMPONENTE.LLAVE = 'FIN' and ROWNUM = 1) as id_paso_fin")
            ->where("CAJA_VALE.ID_VALE", $id)
            ->first();
        return $query;
    }
}