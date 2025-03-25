<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 20/05/21
 * Time: 12:31
 */

namespace App\Http\Data\FinancesStudent;


use App\Http\Data\Utils\FilterUtil;
use Illuminate\Support\Facades\DB;
use PDO;

class NCSpecialDiscountData
{
    public static function getSpecialDiscount($params)
    {
        return DB::select("SELECT A.ID_ALUMNO_CONTRATO,
               A.ID_PERSONA,
               B.NOMBRE,
               B.PATERNO,
               B.MATERNO,
               C.CODIGO,
               PS.NOMBRE_FACULTAD,
               PS.NOMBRE_ESCUELA,
               A.MENSUAL,
               A.ID_COMPROBANTE,
               A.ID_CLIENTE_LEGAL,
               A.TIPO_ALUMNO,
               ap.NOMBRE PLAN_EST,
               DAVID.FT_CALCULAR_CICLO_PROGRAMA(X.SEMESTRE,A.ID_PERSONA,A.ID_PLAN_PROGRAMA)AS CICLO
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
                 JOIN DAVID.ACAD_PLAN_PROGRAMA PP ON A.ID_PLAN_PROGRAMA = PP.ID_PLAN_PROGRAMA
                 JOIN DAVID.ACAD_PLAN AP ON PP.ID_PLAN = AP.ID_PLAN

        WHERE D.ID_SEMESTRE = ?
          AND A.ESTADO = '1'
          AND MD.ID_MODO_CONTRATO = ? --DESCOMENTAR
          AND PP.CUOTAS = ?        --SOLO EXTRAS Y DIRIGIDOS -- JULICAC
          AND A.TIPO_ALUMNO <> 'B18' -- ecluyendo beca 18
          AND PS.ID_NIVEL_ENSENANZA = ?
          AND PS.ID_MODALIDAD_ESTUDIO = ?
          AND PS.ID_PROGRAMA_ESTUDIO IN (" . FilterUtil::implodeSqlSentence($params['id_programa_estudio']) . ")--(17, 18)
          AND PS.ID_SEDE = ?
        ORDER BY A.ID_COMPROBANTE, PS.NOMBRE_FACULTAD, PS.NOMBRE_ESCUELA, B.PATERNO,ap.id_plan, A.ID_ALUMNO_CONTRATO",
            [
                $params['id_semestre'],
                $params['id_modo_contrato'],
                $params['plan_pago'],
                $params['id_nivel_ensenanza'],
                $params['id_modalidad_estudio'],
                $params['id_sede'],
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
        $id_anho = date("Y");
        $plan_pago = $params['plan_pago'];

// dd($id_entidad, $id_depto, $id_semestre, $id_persona, $id_programa_estudio, $id_modo_contrato, $id_nivel_ensenanza, $id_modalidad_estudio, $id_sede, $cuota);
        $data = [];
        DB::beginTransaction();
        try {
            /*
                SP_EXECUTE_NOTA_MAT(P_ID_ENTIDAD NUMBER,
                P_ID_DEPTO VARCHAR,
                P_ID_SEMESTRE NUMBER,
                P_ID_PERSONA NUMBER,
                P_ID_MODO_CONTRATO NUMBER,
                P_ID_NIVEL_ENSENANZA NUMBER,
                P_ID_MODALIDAD_ESTUDIO NUMBER,
                P_ID_ANHO NUMBER,
                P_ID_PROGRAMAS VARCHAR2,
                P_ID_SEDE NUMBER,
                P_ALUMNOS VARCHAR2,
                PLAN_PAGO NUMBER,
                P_NRO_CUOTA NUMBER,
                P_ERROR OUT NUMBER,
                P_MSGERROR OUT VARCHAR2);
    END
                PKG_FINANCES_STUDENTS;
    /*/
            $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_EXECUTE_NOTA_MAT(
                        :P_ID_ENTIDAD,
                        :P_ID_DEPTO,
                        :P_ID_SEMESTRE,
                        :P_ID_PERSONA,
                        :P_ID_MODO_CONTRATO,
                        :P_ID_NIVEL_ENSENANZA,
                        :P_ID_MODALIDAD_ESTUDIO,
                        :P_ID_ANHO, 
                        :P_ID_PROGRAMAS,
                        :P_ID_SEDE,
                        :P_ALUMNOS,
                        :PLAN_PAGO,
                        :P_NRO_CUOTA,
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
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PROGRAMAS', $id_programa_estudio, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SEDE', $id_sede, PDO::PARAM_INT);
            $stmt->bindParam(':P_ALUMNOS', $alumnos, PDO::PARAM_STR);
            $stmt->bindParam(':PLAN_PAGO', $plan_pago, PDO::PARAM_INT);
            $stmt->bindParam(':P_NRO_CUOTA', $cuota, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->execute();

            if ($nerror == 0) {
//                    dd($nerror);
                // DB::rollBack();
                // dd('SUCEESS',$nerror);

                DB::commit();
                $data = self::gettingDiscount($id_persona);
            } else {
                $nerror = 1;
                $msgerror = $msgerror;
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

    public static function gettingDiscount($idUser)
    {
        return DB::select("SELECT 
            B.PATERNO||' '||B.MATERNO||' '||B.NOMBRE AS NOMBRES,C.CODIGO,D.SERIE,D.NUMERO,D.GLOSA,D.TOTAL,A.OK,A.MSN
            FROM TEST_NC_DSCTO A JOIN MOISES.PERSONA B ON A.ID_CLIENTE = B.ID_PERSONA JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON A.ID_CLIENTE = C.ID_PERSONA
            LEFT JOIN VENTA D ON A.ID_VENTA = D.ID_VENTA
            WHERE A.ID_USER = ?
            AND A.REVISADO = 'N'
            AND TO_CHAR(A.FECHA,'DD/MM/YYYY') = TO_CHAR(SYSDATE,'DD/MM/YYYY')
            ORDER BY OK desc,NOMBRES", [$idUser]);
    }
}


