<?php


namespace App\Http\Controllers\Report\FinancesStudent;


use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function dataStudentSemestersSQL($response_auth)
    {
        // Retorna el (id_contrato_alumno, fecha_inio, fecha_fin)
        $qcuenta = "";
        $qdepto = "";
        $ctaCte = "";
        $idEntidad = $response_auth['id_entidad'];
        $idDepto = $response_auth['id_depto'];
        $idAnio =  intval(date('Y')) ;

        $id_cuentaasi =  $this->request->input('id_cuentaaasi');
        $qpIdAnio =  $this->request->input('id_anho');
        $qpidMonth_de =  $this->request->input('id_mes');
        $qpidMonth_a =  $this->request->input('id_mes_a');
        $qpidDepto =  $this->request->input('id_depto');
        $id_cta_cte =  $this->request->input('cta_cte');
        $currentISoDate = explode('T', date(DATE_ISO8601, time()))[0];
        $month = intval(explode('-', $currentISoDate)[1]);
        if($id_cuentaasi) {
            $qcuenta = "AND C.CUENTA in ($id_cuentaasi)";
        }
        if($id_cta_cte) {
            // $json = json_decode($id_cta_cte);
            // $valor = implode(',', $json);
            // $ctaCte = "AND C.CUENTA_CTE in (".$valor.")";
            $ctaCte = "AND C.CUENTA_CTE in ($id_cta_cte)";
        }
        if($qpidDepto) {
            $qdepto = (strpos($qpidDepto, ',') != false)  ? "AND C.DEPTO in ($qpidDepto)" : "AND C.DEPTO  = '".$qpidDepto."'";
        }
        if($qpIdAnio) {
            $idAnio = $qpIdAnio;
        }
        if($qpidMonth_de) {
            $month = $qpidMonth_de;
        }
        if($qpidMonth_a) {
            $month_a = $qpidMonth_a;
        }

        if ($response_auth["valida"] == 'SI') {

            $query = "
                SELECT 
                        E.ID_PERSONA
                FROM VENTA A
                            JOIN VENTA_DETALLE B ON A.ID_VENTA = B.ID_VENTA
                            JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                    AND B.ID_VDETALLE = C.ID_ORIGEN
                            JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                            LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                            LEFT JOIN MOISES.PERSONA_DOCUMENTO F ON F.ID_PERSONA = A.ID_CLIENTE
                            JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                            JOIN USERS Z ON Z.ID = A.ID_PERSONA
                WHERE
                    A.ID_ENTIDAD = $idEntidad
                    AND A.ID_DEPTO = '".$idDepto."'
                    AND A.ID_ANHO = $idAnio 
                    AND A.ID_MES BETWEEN $month AND $month_a
                    $qcuenta
                    $qdepto
                    $ctaCte
                GROUP BY 
                    E.ID_PERSONA
                UNION ALL
                SELECT 
                    E.ID_PERSONA
                FROM VENTA_TRANSFERENCIA A
                JOIN VENTA_TRANSFERENCIA_DETALLE B ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND A.ID_TRANSFERENCIA = C.ID_ORIGEN
                JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                JOIN USERS Z ON Z.ID = A.ID_PERSONA
                WHERE
                    A.ID_ENTIDAD = $idEntidad
                    AND A.ID_DEPTO = '".$idDepto."'
                    AND A.ID_ANHO = $idAnio 
                    AND A.ID_MES BETWEEN $month AND $month_a
                    $qcuenta
                    $qdepto
                    $ctaCte
                GROUP BY 
		            E.ID_PERSONA
            ";
            
            $query_students_info = "
                SELECT 
                    vaac.ID_ALUMNO_CONTRATO,
                    TRUNC(TEMP.fecha_clase_min) FECHA_INICIO,
                    TRUNC(SYSDATE) FECHA_FIN,
                    TRUNC(TEMP.fecha_clase_max) FECHA_FIN_BASE,
                    vaac.id_persona,
                    vaac.grupo
                FROM david.VW_ACAD_ALUMNO_CONTRATO vaac
                INNER JOIN (
                    SELECT 
                        aacc.ID_ALUMNO_CONTRATO,
                        aca.ID_PERSONA,
                        --MIN(e.fecha_registro) AS fecha_clase_min,
                        --MAX(e.fecha_registro) AS fecha_clase_max,
                        MIN(f.fecha_clase) AS fecha_clase_min,
                        MAX(f.fecha_clase) AS fecha_clase_max
                    FROM david.ACAD_ALUMNO_CONTRATO_CURSO aacc
                    INNER JOIN DAVID.ACAD_CURSO_ALUMNO aca ON aca.ID_CURSO_ALUMNO = aacc.ID_CURSO_ALUMNO
                    INNER JOIN DAVID.ACAD_CARGA_CURSO_DOCENTE b ON b.id_carga_curso = aca.ID_CARGA_CURSO
                    LEFT JOIN DAVID.ACAD_ASISTENCIA f ON b.ID_CARGA_CURSO_DOCENTE = f.ID_CARGA_CURSO_DOCENTE
                    --LEFT JOIN DAVID.ACAD_ASISTENCIA_DET e ON f.ID_ASISTENCIA = e.ID_ASISTENCIA AND aca.ID_PERSONA = e.ID_PERSONA
                    GROUP BY 
                        aacc.ID_ALUMNO_CONTRATO,
                        aca.ID_PERSONA
                ) TEMP ON TEMP.ID_ALUMNO_CONTRATO = vaac.ID_ALUMNO_CONTRATO 
                WHERE 
                    vaac.ID_PERSONA IN ($query)
                    AND vaac.ESTADO = '1' 
                    AND vaac.ID_TIPO_CONTRATO = '1'
                ORDER BY vaac.id_persona, TEMP.fecha_clase_min
            ";

            $data = collect(DB::select($query_students_info));
            $grouped_data = $data->groupBy('id_persona');

            $grouped_data->each(function($group) {
                if ($group->count() > 1) {
                    for ($i = 1; $i < $group->count(); $i++) { 
                        $group[$i - 1]->fecha_fin = $group[$i]->fecha_inicio;
                    }
                }
            });
            $flattened_data = $grouped_data->flatten();

            $data_sql = $flattened_data->map(function($item) {
                return "
                    SELECT 
                        {$item->id_alumno_contrato} ID_ALUMNO_CONTRATO,
                        TO_TIMESTAMP('{$item->fecha_inicio}', 'YYYY-MM-DD HH24:MI:SS') AS FECHA_INICIO,
                        TO_TIMESTAMP('{$item->fecha_fin}', 'YYYY-MM-DD HH24:MI:SS') AS FECHA_FIN,
                        {$item->id_persona} ID_PERSONA
                    FROM dual
                ";
            })->implode(' UNION ALL ');
            return $data_sql;

        }
        return null;
    }

    public function accountMovementsV2()
    {
        $qcuenta = "";
        $qdepto = "";
        $ctaCte = "";
        $response = GlobalMethods::authorizationLamb($this->request);
        $idEntidad = $response['id_entidad'];
        $idDepto = $response['id_depto'];
        $idAnio =  intval(date('Y')) ;
        $username = $response["email"];
//        $respo = $this->request->all();
        $id_cuentaasi =  $this->request->input('id_cuentaaasi');
        $qpIdAnio =  $this->request->input('id_anho');
        $qpidMonth_de =  $this->request->input('id_mes');
        $qpidMonth_a =  $this->request->input('id_mes_a');
        $qpidDepto =  $this->request->input('id_depto');
        $id_cta_cte =  $this->request->input('cta_cte');
        $currentISoDate = explode('T', date(DATE_ISO8601, time()))[0];
        $month = intval(explode('-', $currentISoDate)[1]);
        if($id_cuentaasi) {
            $qcuenta = "AND C.CUENTA in ($id_cuentaasi)";
        }
        if($id_cta_cte) {
            // $json = json_decode($id_cta_cte);
            // $valor = implode(',', $json);
            // $ctaCte = "AND C.CUENTA_CTE in (".$valor.")";
            $ctaCte = "AND C.CUENTA_CTE in ($id_cta_cte)";
        }
        if($qpidDepto) {
            $qdepto = (strpos($qpidDepto, ',') != false)  ? "AND C.DEPTO in ($qpidDepto)" : "AND C.DEPTO  = '".$qpidDepto."'";
        }
        if($qpIdAnio) {
            $idAnio = $qpIdAnio;
        }
        if($qpidMonth_de) {
            $month = $qpidMonth_de;
        }
        if($qpidMonth_a) {
            $month_a = $qpidMonth_a;
        }

        if ($response["valida"] == 'SI') {

            $query = "SELECT 
                           A.ID_VENTA,
                           X.NUMERO                                         AS VOUCHER,
                           X.LOTE,
                           A.SERIE || '-' || A.NUMERO                       AS OPERACION,
                           TO_CHAR(A.FECHA, 'DD/MM/YYYY')                   AS FECHA,
                           TRUNC(A.FECHA)                                   AS FECHA_FORMAT_1,
                           C.CUENTA,
                           C.CUENTA_CTE,
                           C.DEPTO                                          AS NIVEL,
                           D.NOMBRE || ' ' || D.PATERNO || ' ' || D.MATERNO AS PERSONA,
                           NVL(E.CODIGO,F.NUM_DOCUMENTO) AS CODIGO,
                           E.ID_PERSONA,
                           A.TOTAL,
                           B.DETALLE,
                           B.IMPORTE,
                           C.IMPORTE                                        AS IMP_ASIENTO,
                           Z.EMAIL                                          AS USUARIO,
                           (SELECT COUNT(1) FROM CONTA_ASIENTO CA WHERE CA.ID_TIPOORIGEN = 1 AND CA.ID_ORIGEN IN (SELECT VD.ID_VDETALLE FROM VENTA_DETALLE VD WHERE VD.ID_VENTA = A.ID_VENTA) AND CA.CUENTA = C.CUENTA AND CA.DEPTO = C.DEPTO) cant
                    FROM VENTA A
                             JOIN VENTA_DETALLE B ON A.ID_VENTA = B.ID_VENTA
                             JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                        AND B.ID_VDETALLE = C.ID_ORIGEN
                             JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                             LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                             LEFT JOIN MOISES.PERSONA_DOCUMENTO F ON F.ID_PERSONA = A.ID_CLIENTE
                             JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                             JOIN USERS Z ON Z.ID = A.ID_PERSONA
                    WHERE A.ID_ENTIDAD = $idEntidad
                    AND A.ID_DEPTO = '".$idDepto."'
                    AND A.ID_ANHO = $idAnio 
                    AND A.ID_MES BETWEEN $month AND $month_a
                    $qcuenta
                    $qdepto
                    $ctaCte
                    group by a.ID_VENTA, X.NUMERO, X.LOTE, A.SERIE, A.NUMERO, A.FECHA, C.CUENTA, C.CUENTA_CTE, C.DEPTO, D.NOMBRE, D.PATERNO,
         D.MATERNO, E.CODIGO, F.NUM_DOCUMENTO, E.ID_PERSONA, A.TOTAL, B.DETALLE, B.IMPORTE, C.IMPORTE, Z.EMAIL 
                UNION ALL
                SELECT 
                    A.ID_TRANSFERENCIA,
                    X.NUMERO                                         AS VOUCHER,
                    X.LOTE,
                    A.SERIE || '-' || A.NUMERO                       AS OPERACION,
                    TO_CHAR(A.FECHA, 'DD/MM/YYYY')                   AS FECHA,
                    TRUNC(A.FECHA)                                   AS FECHA_FORMAT_1,
                    C.CUENTA,
                    C.CUENTA_CTE,
                    C.DEPTO                                          AS NIVEL,
                    D.NOMBRE || ' ' || D.PATERNO || ' ' || D.MATERNO AS PERSONA,
                    E.CODIGO,
                    E.ID_PERSONA,
                    A.IMPORTE AS TOTAL,
                    B.DETALLE,
                    B.IMPORTE,
                    C.IMPORTE                                        AS IMP_ASIENTO,
                    Z.EMAIL                                          AS USUARIO,
                    (SELECT COUNT(1) FROM CONTA_ASIENTO CA WHERE CA.ID_TIPOORIGEN = 2 AND CA.ID_ORIGEN = A.ID_TRANSFERENCIA AND CA.CUENTA = C.CUENTA AND CA.DEPTO = C.DEPTO) cant
                FROM VENTA_TRANSFERENCIA A
                JOIN VENTA_TRANSFERENCIA_DETALLE B ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND A.ID_TRANSFERENCIA = C.ID_ORIGEN
                JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                JOIN USERS Z ON Z.ID = A.ID_PERSONA
                WHERE A.ID_ENTIDAD = $idEntidad
                AND A.ID_DEPTO = '".$idDepto."'
                AND A.ID_ANHO = $idAnio 
                AND A.ID_MES BETWEEN $month AND $month_a
                $qcuenta
                $qdepto
                $ctaCte
                GROUP BY A.ID_TRANSFERENCIA, X.NUMERO, X.LOTE, A.SERIE, A.NUMERO, A.FECHA, C.CUENTA, C.CUENTA_CTE, C.DEPTO, D.NOMBRE, D.PATERNO,
                D.MATERNO, E.CODIGO, E.ID_PERSONA, A.IMPORTE, B.DETALLE, B.IMPORTE, C.IMPORTE, Z.EMAIL 
                ORDER BY ID_VENTA ";

            // Obtener semestre por la fecha de venta
            $query_estudiantes = $this->dataStudentSemestersSQL($response);
            $query_modif = "
                SELECT 
                    DISTINCT
                    MOVIMIENTOS.*,
                    vaac.ID_ALUMNO_CONTRATO,
                    vaac.id_semestre,
                    vaac.semestre,
                    vaac.id_facultad,
                    vaac.nombre_facultad,
                    vaac.id_programa_estudio,
                    ape.nombre AS nombre_programa_estudio,
                    vaac.ciclo,
                    vaac.grupo
                FROM (
                    SELECT rownum rn, MOVI.* FROM (
                        {$query}
                    ) MOVI
                ) MOVIMIENTOS
                LEFT JOIN (
                    SELECT
                        ESTUDIANTE_SEMESTRE.ID_ALUMNO_CONTRATO,
                        ESTUDIANTE_SEMESTRE.FECHA_INICIO,
                        ESTUDIANTE_SEMESTRE.FECHA_FIN,
                        ESTUDIANTE_SEMESTRE.ID_PERSONA
                    FROM (
                        {$query_estudiantes}
                    ) ESTUDIANTE_SEMESTRE
                ) ESTUDIANTE 
                    ON movimientos.id_persona = estudiante.id_persona AND
                    movimientos.fecha_format_1 >= estudiante.fecha_inicio AND movimientos.fecha_format_1 <= estudiante.fecha_fin
                LEFT JOIN DAVID.VW_ACAD_ALUMNO_CONTRATO vaac ON vaac.ID_ALUMNO_CONTRATO = ESTUDIANTE.ID_ALUMNO_CONTRATO
                LEFT JOIN DAVID.ACAD_PROGRAMA_ESTUDIO ape ON ape.ID_PROGRAMA_ESTUDIO = vaac.ID_PROGRAMA_ESTUDIO
                ORDER BY MOVIMIENTOS.RN
            ";

            $data = DB::select($query_modif); //try_catch?
            $response["code"] = 200;
            $response['success'] = $data? true: false;
            $response['message'] = $data? 'OK': 'no se encontraro datos relacionados';
            $response['data'] = $data;
        } else {
            $response["code"] = 500;
            $response['success'] = false;
            $response['message'] = 'credential errors';
            $response['data'] = null;
        }
        return response()->json($response, $response["code"]);
    }

    public function accountMovements()
    {
        $qcuenta = "";
        $qdepto = "";
        $ctaCte = "";
        $response = GlobalMethods::authorizationLamb($this->request);
        $idEntidad = $response['id_entidad'];
        $idDepto = $response['id_depto'];
        $idAnio =  intval(date('Y')) ;
        $username = $response["email"];
//        $respo = $this->request->all();
        $id_cuentaasi =  $this->request->input('id_cuentaaasi');
        $qpIdAnio =  $this->request->input('id_anho');
        $qpidMonth_de =  $this->request->input('id_mes');
        $qpidMonth_a =  $this->request->input('id_mes_a');
        $qpidDepto =  $this->request->input('id_depto');
        $id_cta_cte =  $this->request->input('cta_cte');
        $currentISoDate = explode('T', date(DATE_ISO8601, time()))[0];
        $month = intval(explode('-', $currentISoDate)[1]);
        if($id_cuentaasi) {
            $qcuenta = "AND C.CUENTA in ($id_cuentaasi)";
        }
        if($id_cta_cte) {
            // $json = json_decode($id_cta_cte);
            // $valor = implode(',', $json);
            // $ctaCte = "AND C.CUENTA_CTE in (".$valor.")";
            $ctaCte = "AND C.CUENTA_CTE in ($id_cta_cte)";
        }
        if($qpidDepto) {
            $qdepto = (strpos($qpidDepto, ',') != false)  ? "AND C.DEPTO in ($qpidDepto)" : "AND C.DEPTO  = '".$qpidDepto."'";
        }
        if($qpIdAnio) {
            $idAnio = $qpIdAnio;
        }
        if($qpidMonth_de) {
            $month = $qpidMonth_de;
        }
        if($qpidMonth_a) {
            $month_a = $qpidMonth_a;
        }

        if ($response["valida"] == 'SI') {

            $query = "SELECT 
                           A.ID_VENTA,
                           X.NUMERO                                         AS VOUCHER,
                           X.LOTE,
                           A.SERIE || '-' || A.NUMERO                       AS OPERACION,
                           TO_CHAR(A.FECHA, 'DD/MM/YYYY')                   AS FECHA,
                           C.CUENTA,
                           C.CUENTA_CTE,
                           C.DEPTO                                          AS NIVEL,
                           D.NOMBRE || ' ' || D.PATERNO || ' ' || D.MATERNO AS PERSONA,
                           NVL(E.CODIGO,F.NUM_DOCUMENTO) AS CODIGO,
                           A.TOTAL,
                           B.DETALLE,
                           B.IMPORTE,
                           C.IMPORTE                                        AS IMP_ASIENTO,
                           Z.EMAIL                                          AS USUARIO,
                           (SELECT COUNT(1) FROM CONTA_ASIENTO CA WHERE CA.ID_TIPOORIGEN = 1 AND CA.ID_ORIGEN IN (SELECT VD.ID_VDETALLE FROM VENTA_DETALLE VD WHERE VD.ID_VENTA = A.ID_VENTA) AND CA.CUENTA = C.CUENTA AND CA.DEPTO = C.DEPTO) cant
                    FROM VENTA A
                             JOIN VENTA_DETALLE B ON A.ID_VENTA = B.ID_VENTA
                             JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                        AND B.ID_VDETALLE = C.ID_ORIGEN
                             JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                             LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                             LEFT JOIN MOISES.PERSONA_DOCUMENTO F ON F.ID_PERSONA = A.ID_CLIENTE
                             JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                             JOIN USERS Z ON Z.ID = A.ID_PERSONA
                    WHERE A.ID_ENTIDAD = $idEntidad
                    AND A.ID_DEPTO = '".$idDepto."'
                    AND A.ID_ANHO = $idAnio 
                    AND A.ID_MES BETWEEN $month AND $month_a
                    $qcuenta
                    $qdepto
                    $ctaCte
                    group by a.ID_VENTA, X.NUMERO, X.LOTE, A.SERIE, A.NUMERO, A.FECHA, C.CUENTA, C.CUENTA_CTE, C.DEPTO, D.NOMBRE, D.PATERNO,
         D.MATERNO, E.CODIGO,F.NUM_DOCUMENTO, A.TOTAL, B.DETALLE, B.IMPORTE, C.IMPORTE, Z.EMAIL 
                UNION ALL
                SELECT 
                    A.ID_TRANSFERENCIA,
                    X.NUMERO                                         AS VOUCHER,
                    X.LOTE,
                    A.SERIE || '-' || A.NUMERO                       AS OPERACION,
                    TO_CHAR(A.FECHA, 'DD/MM/YYYY')                   AS FECHA,
                    C.CUENTA,
                    C.CUENTA_CTE,
                    C.DEPTO                                          AS NIVEL,
                    D.NOMBRE || ' ' || D.PATERNO || ' ' || D.MATERNO AS PERSONA,
                    E.CODIGO,
                    A.IMPORTE AS TOTAL,
                    B.DETALLE,
                    B.IMPORTE,
                    C.IMPORTE                                        AS IMP_ASIENTO,
                    Z.EMAIL                                          AS USUARIO,
                    (SELECT COUNT(1) FROM CONTA_ASIENTO CA WHERE CA.ID_TIPOORIGEN = 2 AND CA.ID_ORIGEN = A.ID_TRANSFERENCIA AND CA.CUENTA = C.CUENTA AND CA.DEPTO = C.DEPTO) cant
                FROM VENTA_TRANSFERENCIA A
                JOIN VENTA_TRANSFERENCIA_DETALLE B ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN CONTA_ASIENTO C ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND A.ID_TRANSFERENCIA = C.ID_ORIGEN
                JOIN MOISES.PERSONA D ON D.ID_PERSONA = A.ID_CLIENTE
                JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON E.ID_PERSONA = A.ID_CLIENTE
                JOIN CONTA_VOUCHER X ON X.ID_VOUCHER = A.ID_VOUCHER
                JOIN USERS Z ON Z.ID = A.ID_PERSONA
                WHERE A.ID_ENTIDAD = $idEntidad
                AND A.ID_DEPTO = '".$idDepto."'
                AND A.ID_ANHO = $idAnio 
                AND A.ID_MES BETWEEN $month AND $month_a
                $qcuenta
                $qdepto
                $ctaCte
                GROUP BY A.ID_TRANSFERENCIA, X.NUMERO, X.LOTE, A.SERIE, A.NUMERO, A.FECHA, C.CUENTA, C.CUENTA_CTE, C.DEPTO, D.NOMBRE, D.PATERNO,
                D.MATERNO, E.CODIGO, A.IMPORTE, B.DETALLE, B.IMPORTE, C.IMPORTE, Z.EMAIL 
                ORDER BY ID_VENTA ";
            $data = DB::select($query);
            if ($data) {
                $response["code"] = 200;
                $response['success'] = true;
                $response['message'] = 'OK';
                $response['data'] = $data;

            } else {
                $response["code"] = '202';
                $response['success'] = false;
                $response['message'] = 'no se encontraro datos relacionados';
                $response['data'] = [];
            }
        } else {
            $response["code"] = 500;
            $response['success'] = false;
            $response['message'] = 'credential errors';
            $response['data'] = null;
        }
        return response()->json($response, $response["code"]);
    }

    public function getRowSpan($data){
        $sal = array();
        $emp = array();
        for ($i = 0; $i < sizeof($data); $i++) {

            array_push($emp, $data[$i]->id_venta);
            array_push($sal, $data[$i]->persona);
        }
        $arr = array();

        # loop over all the sal array
        for ($i = 0; $i < sizeof($sal); $i++) {
            $empName = $emp[$i];

            # If there is no array for the employee
            # then create a elemnt.
            if (!isset($arr[$empName])) {
                $arr[$empName] = array();
                $arr[$empName]['rowspan'] = 0;
            }

            $arr[$empName]['printed'] = "no";

            # Increment the row span value.
            $arr[$empName]['rowspan'] += 1;
        }
        return $arr;
    }

}