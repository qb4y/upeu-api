<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 13/04/21
 * Time: 17:06
 */

namespace App\Http\Data\FinancesStudent;


use App\Http\Data\Utils\FilterUtil;
use Illuminate\Support\Facades\DB;
use PDO;

class CollectionFeesData
{

    public static function getCollectionFees($params)
    {
        return DB::select("SELECT A.ID_ALUMNO_CONTRATO,
               A.ID_PERSONA,
               B.NOMBRE,
               B.PATERNO,
               B.MATERNO,
               C.CODIGO,
               PS.NOMBRE_FACULTAD,
               PS.NOMBRE_ESCUELA,
               --A.MENSUAL,
               (CASE WHEN DIAS_RESIDENCIA > 0 THEN MENSUAL_ENS_RESI ELSE MENSUAL END ) AS MENSUAL,
               A.ID_COMPROBANTE,
               A.ID_CLIENTE_LEGAL,
               A.TIPO_ALUMNO,
               DAVID.FT_CALCULAR_CICLO_PROGRAMA(X.SEMESTRE,A.ID_PERSONA,A.ID_PLAN_PROGRAMA)AS CICLO,
               NVL((SELECT NVL(X.NUM_TELEFONO,'') FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = A.ID_PERSONA AND X.ES_ACTIVO = '1' AND ROWNUM = 1 ),
               (SELECT NVL(X.CELULAR,X.TELEFONO) FROM MOISES.PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_PERSONA AND ROWNUM = 1 ))AS CELULAR,
               NVL((SELECT NVL(X.DIRECCION,'') FROM MOISES.PERSONA_VIRTUAL X WHERE X.ID_PERSONA = A.ID_PERSONA AND X.ES_ACTIVO = '1' AND ROWNUM = 1 ),
               (SELECT NVL(X.CORREO,X.CORREO_INST) FROM MOISES.PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_PERSONA AND ROWNUM = 1 )) AS EMAIL
        FROM DAVID.ACAD_ALUMNO_CONTRATO A
                 JOIN MOISES.PERSONA B ON A.ID_PERSONA = B.ID_PERSONA
                 JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON B.ID_PERSONA = C.ID_PERSONA
                 JOIN DAVID.ACAD_SEMESTRE_PROGRAMA D ON A.ID_SEMESTRE_PROGRAMA = D.ID_SEMESTRE_PROGRAMA
                 JOIN DAVID.ACAD_SEMESTRE X ON D.ID_SEMESTRE = X.ID_SEMESTRE
                 JOIN DAVID.ACAD_MATRICULA_DETALLE MD ON A.ID_MATRICULA_DETALLE = MD.ID_MATRICULA_DETALLE
                 JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO PS ON PS.ID_PROGRAMA_ESTUDIO = D.ID_PROGRAMA_ESTUDIO
                 JOIN MAT_PLANPAGO_SEMESTRE PPS ON PPS.ID_PLANPAGO_SEMESTRE = A.ID_PLANPAGO_SEMESTRE
                 JOIN MAT_PLANPAGO PP ON PP.ID_PLANPAGO = PPS.ID_PLANPAGO
                 JOIN ORG_SEDE_AREA SA ON SA.ID_SEDEAREA = PS.ID_SEDEAREA
        WHERE D.ID_SEMESTRE = ?
          AND A.ESTADO = '1'
          AND MD.ID_MODO_CONTRATO = ? --DESCOMENTAR
          AND PP.CUOTAS = ?        --SOLO EXTRAS Y DIRIGIDOS -- JULICAC
          AND PS.ID_NIVEL_ENSENANZA = ?
          AND PS.ID_MODALIDAD_ESTUDIO = ?
          AND PS.ID_PROGRAMA_ESTUDIO IN (" . FilterUtil::implodeSqlSentence($params['id_programa_estudio']) . ")--(17, 18)
          AND PS.ID_SEDE = ?
          AND PPS.ID_PLANPAGO = ?
        ORDER BY A.ID_COMPROBANTE, PS.NOMBRE_FACULTAD, PS.NOMBRE_ESCUELA, CICLO, B.PATERNO, A.ID_ALUMNO_CONTRATO",
            [
                $params['id_semestre'],
                $params['id_modo_contrato'],
                $params['plan_pago'], // cuota por planpago
                $params['id_nivel_ensenanza'],
                $params['id_modalidad_estudio'],
                $params['id_sede'],
                $params['id_planpago']
                // $params['plan_pago'],
            ]);
    }

    public static function insert($params)
    {
        $nerror = 0;
        $msgerror = "";
        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $id_entidad = $params['id_entidad'];
        $id_depto = $params['id_depto'];
        $id_semestre = $params['id_semestre'];
        $id_persona = $params['id_persona'];
        $alumnos = implode('|', array_unique(explode(",", $params['alumnos'])));
        $id_programa_estudio = implode('|', array_unique(explode(",", $params['id_programa_estudio'])));
        // dd($alumnos);
        $id_modo_contrato = $params['id_modo_contrato'];
        $id_nivel_ensenanza = $params['id_nivel_ensenanza'];
        $id_modalidad_estudio = $params['id_modalidad_estudio'];
        $id_sede = $params['id_sede'];
        $cuota = $params['cuota'];
        $plan_pago = $params['plan_pago'];
        $id_planpago = $params['id_planpago'];

// dd($id_entidad, $id_depto, $id_semestre, $id_persona, $id_programa_estudio, $id_modo_contrato, $id_nivel_ensenanza, $id_modalidad_estudio, $id_sede, $cuota);
        $data = [];
        DB::beginTransaction();
        try {
            
            $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_EXECUTE_CUOTA(
                        :P_ID_ENTIDAD,
                        :P_ID_DEPTO,
                        :P_ID_SEMESTRE,
                        :P_ID_PERSONA,
                        :P_ID_MODO_CONTRATO,
                        :P_ID_NIVEL_ENSENANZA,
                        :P_ID_MODALIDAD_ESTUDIO,
                        :P_ID_PROGRAMAS,
                        :P_ID_SEDE,
                        :P_ALUMNOS,
                        :PLAN_PAGO,
                        :P_CUOTA,
                        :P_ID_PLANPAGO,
                        :P_ERROR,
                        :P_MSGERROR
                        );END;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SEMESTRE', $id_semestre, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MODO_CONTRATO', $id_modo_contrato, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_NIVEL_ENSENANZA', $id_nivel_ensenanza, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MODALIDAD_ESTUDIO', $id_modalidad_estudio, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PROGRAMAS', $id_programa_estudio, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SEDE', $id_sede, PDO::PARAM_INT);
            $stmt->bindParam(':P_ALUMNOS', $alumnos, PDO::PARAM_STR);
            $stmt->bindParam(':P_CUOTA', $cuota, PDO::PARAM_INT);
            $stmt->bindParam(':PLAN_PAGO', $plan_pago, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PLANPAGO', $id_planpago, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->execute();

            if ($nerror == 0) {
//                    dd($nerror);
                //DB::rollBack();
                //dd('SUCEESS',$nerror);
                DB::commit();
            } else {
                $nerror = 1;
                $data = DB::table('eliseo.fin_cuota_qa as a')
                    ->join('eliseo.users as b', 'a.id_user', '=', 'b.id')
                    ->join('moises.persona as c', 'a.id_persona', '=', 'c.id_persona')
                    ->join('moises.persona_natural_alumno as d', 'a.id_persona', '=', 'd.id_persona')
                    ->where('a.id_user', $id_persona)
                    ->whereraw("TO_CHAR(a.fecha,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')")
                    ->select(DB::raw("c.nombre||' '||c.paterno as alumno"), 'd.codigo', 'b.email', 'a.nro_cuota', 'a.error', 'a.fecha')
                    ->orderBy('a.fecha', 'desc')
                    ->get();
                DB::rollBack();
            }

        } catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }


        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'data' => $data,
        ];
        return $return;
    }
}