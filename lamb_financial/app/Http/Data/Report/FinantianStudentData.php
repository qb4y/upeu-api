<?php


namespace App\Http\Data\Report;


use Illuminate\Support\Facades\DB;
use PDO;

class FinantianStudentData
{
    public static function getActiveSemester()
    {
        return DB::table('david.ACAD_SEMESTRE')->select('ID_SEMESTRE')->where('estado', 1)->get()->first();
    }

    public static function getPaymenTrackingSummary($request)
    {
        $dateRange = $request->input('date_range');
        $idSemester = $request->input('id_semestre');
        $qrSemester = "";
        $qryDate = "";
        if ($idSemester) {
            $qrSemester = "where ELISEO.FIN_EVIDENCIA.ID_SEMESTRE = '" . $idSemester . "'";
        } else {
            $semester = self::getActiveSemester();
            if ($semester) {
                $qrSemester = "where ELISEO.FIN_EVIDENCIA.ID_SEMESTRE = '" . $semester->id_semestre . "'";
            }
        }
        if ($dateRange) {
            $range = explode(',', $dateRange);
            $qryDate = "AND FECHA between trunc(TO_DATE('" . $range[0] . "', 'YYYY-MM-DD HH24:MI:SS')) and trunc(TO_DATE('" . $range[1] . "', 'YYYY-MM-DD HH24:MI:SS')+1) - INTERVAL '1' SECOND";
        }
        $qry = "select A.ID_USER,
       A.EMAIL,
       count(A.ID_CLIENTE) CANTIDAD
        from (
                 select ELISEO.FIN_EVIDENCIA.ID_CLIENTE, ELISEO.FIN_EVIDENCIA.ID_USER, ELISEO.USERS.EMAIL
                 from ELISEO.FIN_EVIDENCIA
                          join ELISEO.USERS on ELISEO.USERS.ID = FIN_EVIDENCIA.ID_USER
                 $qrSemester
                 $qryDate
                 group by ELISEO.FIN_EVIDENCIA.ID_CLIENTE, ELISEO.FIN_EVIDENCIA.ID_USER, ELISEO.USERS.EMAIL
             ) A
        group by A.ID_USER, A.EMAIL";
        return DB::select($qry);
    }

    public static function getPaymenTracking($request)
    {
        $dateRange = $request->input('date_range');
        $idSemester = $request->input('id_semestre');
        $idUser = $request->input('id_user');
        $strSearch = $request->input('search');

        $resp = DB::table('ELISEO.FIN_EVIDENCIA')
            ->select(
                'ELISEO.FIN_EVIDENCIA.ID_CLIENTE',
                'ELISEO.FIN_EVIDENCIA.ID_SEMESTRE',
                'VW_PERSONA_NATURAL_ALUMNO.NOMBRE AS CLIENTE',
                'VW_PERSONA_NATURAL_ALUMNO.CODIGO',
                'VW_PERSONA_NATURAL_ALUMNO.NUM_DOCUMENTO'
            )
            ->join('moises.VW_PERSONA_NATURAL_ALUMNO', 'VW_PERSONA_NATURAL_ALUMNO.ID_PERSONA', '=', 'FIN_EVIDENCIA.ID_CLIENTE');
//            ->join('MOISES.PERSONA', 'MOISES.PERSONA.ID_PERSONA', '=', 'ELISEO.FIN_EVIDENCIA.ID_CLIENTE')
//            ->join('MOISES.PERSONA_NATURAL', 'MOISES.PERSONA_NATURAL.ID_PERSONA', '=', 'ELISEO.FIN_EVIDENCIA.ID_CLIENTE');

        if ($idSemester) {
            $resp = $resp->where('FIN_EVIDENCIA.ID_SEMESTRE', $idSemester);
        } else {
            $semester = self::getActiveSemester();
            if ($semester) {
                $resp = $resp->where('FIN_EVIDENCIA.ID_SEMESTRE', $semester->id_semestre);
            }
        }
        if ($idUser) {
            $resp = $resp->where('FIN_EVIDENCIA.ID_USER', $idUser);
        }
        if ($dateRange) {
            $range = explode(',', $dateRange);
            $resp = $resp
                ->whereRaw("FECHA between trunc(TO_DATE('" . $range[0] . "', 'YYYY-MM-DD HH24:MI:SS')) and trunc(TO_DATE('" . $range[1] . "', 'YYYY-MM-DD HH24:MI:SS')+1) - INTERVAL '1' SECOND");
        }
        if ($strSearch) {
            $resp = $resp
                ->whereRaw("upper(VW_PERSONA_NATURAL_ALUMNO.NOM_PERSONA || VW_PERSONA_NATURAL_ALUMNO.NUM_DOCUMENTO || VW_PERSONA_NATURAL_ALUMNO.CODIGO) like upper('%" . $strSearch . "%')");
        }
        return $resp
            ->groupBy('FIN_EVIDENCIA.ID_CLIENTE',
                'FIN_EVIDENCIA.ID_SEMESTRE',
                'VW_PERSONA_NATURAL_ALUMNO.NOMBRE',
                'VW_PERSONA_NATURAL_ALUMNO.CODIGO',
                'VW_PERSONA_NATURAL_ALUMNO.NUM_DOCUMENTO')
            ->get();
    }

    public static function ammountsSummary($request, $response)
    {
        $idSemester = $request->input('id_semestre');
        $idTeachLevel = $request->input('id_nivel_ensenanza');
        $idStudeMode = $request->input('id_modalidad_estudio');
        $idCampus = $request->input('id_sede');
        $idModeContract = $request->input('id_modo_contrato');
        $studyProgram = $request->input('id_programs');
        $type = $request->input('tipo');
        $state = '1';
        $idUser = $response['id_user'];

        $pdo = DB::getPdo();
        $cant = 0;
        $stmt = $pdo->prepare("begin pkg_finances_students.SP_GENERAR_LISTA_MATRICULA(:P_ID_SEMESTRE, :P_ID_NIVEL_ENSENANZA, :P_ID_MODALIDAD_ESTUDIO, :P_ID_SEDE, :P_ID_MODO_CONTRATO, :P_ID_PROGRAMA_ESTUDIO, :P_ESTADO, :P_USER, :P_CANTIDAD); end;");
        $stmt->bindParam(':P_ID_SEMESTRE', $idSemester, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_NIVEL_ENSENANZA', $idTeachLevel, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_MODALIDAD_ESTUDIO', $idStudeMode, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_SEDE', $idCampus, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_MODO_CONTRATO', $idModeContract, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PROGRAMA_ESTUDIO', $studyProgram, PDO::PARAM_STR);
        $stmt->bindParam(':P_ESTADO', $state, PDO::PARAM_STR);
        $stmt->bindParam(':P_USER', $idUser, PDO::PARAM_INT);
        $stmt->bindParam(':P_CANTIDAD', $cant, PDO::PARAM_INT);
        $stmt->execute();


        $whereIdSemester = '';
        if ($idSemester) {
            $whereIdSemester = "AND a.ID_SEMESTRE = " . $idSemester;
        }
        $whereIdTeachLevel = '';
        if ($idTeachLevel) {
            $whereIdTeachLevel = "AND a.ID_NIVEL_ENSENANZA = " . $idTeachLevel;
        }
        $whereIdCampus = '';
        if ($idCampus) {
            $whereIdCampus = "AND a.ID_SEDE = " . $idCampus;
        }
        $whereIdStudentProgram = '';
        if ($studyProgram) {
            $whereIdStudentProgram = "AND a.ID_PROGRAMA_ESTUDIO in (" . $studyProgram . ")";
        }
        return [
            'lista' => DB::select("SELECT
                a.TIPO,
                CASE a.TIPO
                  WHEN '1CON' THEN 'CONTADO'
                  WHEN '2COND' THEN 'CONTADO_DEUDA'
                  WHEN '3MATCD' THEN 'MAT_CON_DEUDA'
                  WHEN '4MEMA' THEN 'MENOS_MAT'
                  WHEN '5SOMA' THEN 'SOLO_MAT'
                  WHEN '6MATMA' THEN 'MAT_MAT_ARMADA'
                  WHEN '7MATAR' THEN 'MAT_ARMADA'
                  WHEN '8B18' THEN 'BECA18'
                  ELSE ''
                END as tipo_desc,
                a.motivo,
                COUNT(*) as cantidad,
                sum(a.provision) as provision,
                sum(a.deposito) as deposito,
                sum(a.matriculaprov) as matricula
                FROM eliseo.REP_MATRICULADOS a
                where a.id_user=$idUser
                $whereIdSemester
                $whereIdTeachLevel
                $whereIdCampus
                $whereIdStudentProgram$whereIdSemester
                GROUP BY a.TIPO, a.motivo
                order by a.TIPO"),
            'detalle' => $type ? DB::select("SELECT a.id_persona,
                    b.codigo,
                    b.nom_persona,
                    a.nombre_facultad,
                    a.nombre_escuela,
                    FC_OBTENER_CELULAR(a.id_persona) as celular,
                    david.Ft_calcular_ciclo_programa(a.semestre,a.id_persona,a.id_plan_programa) as ciclo,
                    a.plan_pago,
                    a.provision,
                    a.deposito,
                    a.matriculaprov,
                    a.saldo
                    FROM REP_MATRICULADOS a,moises.vw_persona_natural_alumno b 
                    where a.id_persona=b.id_persona
                    and a.id_user=$idUser
                    $whereIdSemester
                    $whereIdTeachLevel
                    $whereIdCampus
                    $whereIdStudentProgram
                    and a.tipo='" . $type . "'
                    order by b.nom_persona") : [],
            'counter' => $cant
        ];
    }


}