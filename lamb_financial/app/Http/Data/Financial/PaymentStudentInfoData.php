<?php

/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 03/02/20
 * Time: 12:06 PM
 */

namespace App\Http\Data\Financial;

use App\Http\Data\FinancesStudent\StudentData;
use App\Models\Criterion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDO;

class PaymentStudentInfoData
{


    /**
     * name="getEntDepStudent",
     * description="Obtiene la entidad y departamento del alumno contrato.",
     */
    protected static function getEntDepStudent($id_alumno_contrato)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select(
                'd.ID_ENTIDAD',
                DB::raw('SUBSTR(d.ID_DEPTO,1,1) as ID_DEPTOX'),
                'a.ID_PERSONA',
                DB::raw('DECODE(D.ID_SEDE,1,1,2,5,3,6,4,8) AS ID_DEPTO')
            )
            ->join('DAVID.ACAD_PLAN_PROGRAMA b', 'a.ID_PLAN_PROGRAMA', '=', 'b.ID_PLAN_PROGRAMA')
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO c', 'b.ID_PROGRAMA_ESTUDIO', '=', 'c.ID_PROGRAMA_ESTUDIO')
            ->join('ELISEO.ORG_SEDE_AREA d', 'c.ID_SEDEAREA', '=', 'd.ID_SEDEAREA')
            ->where('a.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->first();
    }


    /**
     * name="getSemProgId",
     * description="Obtiene el id_semestre_programa por alumno contrato.",
     */
    protected static function getSemProgId($id_alumno_contrato)
    {

        $data = DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select('a.id_semestre_programa', 'a.id_alumno_contrato')
            ->where('a.id_alumno_contrato', '=', $id_alumno_contrato)
            ->first();

        if ($data) {
            return $data->id_semestre_programa;
        }
        return null;
    }


    /**
     * name="getPlanPaySemId",
     * description="Obtiene el id_planpago_semestre por alumno contrato y plan de pago.",
     */
    protected static function getPlanPaySemId($id_contrato_alumno, $id_planpago)
    {
        $id_semestre_programa = self::getSemProgId($id_contrato_alumno);
        if ($id_semestre_programa && $id_planpago) {
            $data = null;

            try {
                $data = DB::table('ELISEO.MAT_PLANPAGO_SEMESTRE as a')
                    ->select('a.id_planpago_semestre')
                    ->where('a.id_semestre_programa', '=', $id_semestre_programa)
                    ->where('a.id_planpago', '=', $id_planpago)
                    ->first();
            } catch (QueryException $e) {
                return null;
            }

            if ($data) {
                return $data->id_planpago_semestre;
            } else {
                return null;
            }
        }
        return null;
    }


    /**
     * name="updatePlanPaySemId",
     * description="actualiza el MEDIO DE PAGO del alumno contrato.",
     */
    protected static function updatePlanPaySemId($id_contrato_alumno, $data)
    {
        $id_planpago_semestre = self::getPlanPaySemId($id_contrato_alumno, $data->id_planpago);
        if ($id_planpago_semestre) {
            DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
                ->where('a.id_alumno_contrato', $id_contrato_alumno)
                ->update(['id_planpago_semestre' => $id_planpago_semestre]);
        }
    }


    /**
     * name="studentDebitCredit",
     * description="Metodo para sacar el debito y credito de.",
     */
    protected static function studentDebitCredit($id_alumno_contrato)
    {
        $data = DB::select(
            "SELECT (b.NOMBRE|| ': ' || a.descripcion) NOMBRE, a.IMPORTE, a.DC, b.TIENE_HIJO, a.IMP_CAL  FROM ELISEO.MAT_ALUMNO_CONTRATO_DET a
                    JOIN ELISEO.VW_MAT_CRITERIO_SEMESTRE b ON (a.ID_CRITERIO_SEMESTRE=b.ID_CRITERIO_SEMESTRE)
                    where a.ID_ALUMNO_CONTRATO=$id_alumno_contrato"
        );
        return collect($data)->groupBy('dc');
    }


    /**
     * name="studentBalance", funcion importante recuperado
     * description="Obtiene el saldo o estado de cuenta del estudiante.",
     */
    protected static function studentBalance($id_contrato_alumno, $id_anho)
    {

        $entDep = self::getEntDepStudent($id_contrato_alumno);

        if ($entDep) {
            $id_entidad = $entDep->id_entidad;
            $id_depto = $entDep->id_depto;
            $id_persona = $entDep->id_persona;

            $qry = "SELECT NVL(ABS(SUM(TOTAL)), 0)  AS TOTAL,
                           NVL(SUM(TOTAL), 0)  AS SALDO,
                           SIGN(NVL(SUM(TOTAL), 0)) AS SIGNO
                    FROM (
                             SELECT TOTAL
                             FROM VW_SALES_MOV
                             WHERE ID_ENTIDAD = $id_entidad
                               AND ID_DEPTO = '" . $id_depto . "'
                               AND ID_ANHO = $id_anho
                               AND ID_CLIENTE = $id_persona
                               AND ID_TIPOVENTA IN (1, 2, 3, 4)
                             UNION ALL
                             SELECT SUM(IMPORTE) * DECODE(SIGN(SUM(IMPORTE)), 1, -1, 0) AS TOTAL
                             FROM VW_SALES_ADVANCES
                             WHERE ID_ENTIDAD = $id_entidad
                               AND ID_DEPTO = '" . $id_depto . "'
                               AND ID_ANHO = $id_anho
                               AND ID_CLIENTE = $id_persona
                         )";
            return collect(DB::select($qry))->first();
        } else {
            return null;
        }
    }


    /**
     * name="studentContract",
     * description="Obtiene el campos de costo del alumno contrato.",
     */
    protected static function studentContract($id, $saldo)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->join('ELISEO.MAT_PLANPAGO_SEMESTRE as b', 'a.id_planpago_semestre', '=', 'b.id_planpago_semestre')
            ->join('ELISEO.MAT_PLANPAGO as c', 'c.id_planpago', '=', 'b.id_planpago')
            ->join('DAVID.ACAD_SEMESTRE_PROGRAMA as d', 'a.id_semestre_programa', '=', 'd.id_semestre_programa')
            ->select(
                'a.fecha_actualizacion',
                'b.id_planpago',
                'a.total_debito',
                'a.total_credito',
                'a.total',
                'a.matricula',
                'a.mensual',
                'a.contado',
                'a.pago',
                'a.matricula1cuota',
                'a.id_alumno_contrato',
                'a.id_comprobante',
                'a.id_cliente_legal',
                'a.estado',
                'c.cuotas',
                'a.misionero',
                'd.id_semestre',
                DB::raw('FC_DOCUMENTO_CLIENTE(a.id_cliente_legal) as num_documento, FC_NOMBRE_PERSONA(a.id_cliente_legal) as nombre_cliente_legal'),
                DB::raw("(
                case when c.cuotas=1 then
                  case when (a.contado + " . $saldo . ") < 0 then 
                    0
                  else
                    a.contado + " . $saldo . "
                  end
                else
                   case when (a.matricula1cuota + " . $saldo . ") < 0 then 
                    0
                  else
                    a.matricula1cuota + " . $saldo . "
                  end
                end) as pagar"),
                'a.mensual_ens_resi',
                'a.id_resid_tipo_habitacion',
                'a.id_persona',
                DB::raw("coalesce(conmat1cuota,'N') as conmat1cuota"),
                DB::raw("nvl(david.ft_ciclo_plan_alumno(a.id_cliente_legal,a.id_plan_programa),1) as ciclo_planpago")
            )
            ->where('a.ID_ALUMNO_CONTRATO', '=', $id)
            ->first();
    }


    /**
     * name="show",
     * description="CTRL: api default para el paso de pago en matricula",
     */
    public static function show($id_contrato_alumno, $id_anho, $isFinantialApp)
    {

        $data = array();

        $debitoCredito = StudentData::pagosDC($id_contrato_alumno, null);

        $studentBalance = self::studentBalance($id_contrato_alumno, $id_anho);
        $saldo = 0;
        if (!empty($studentBalance)) {
            $saldo = $studentBalance->saldo;
        }
        $data['isFinantialApp'] = $isFinantialApp;
        $data['studentBalance'] = $studentBalance;
        $data['studentDebitCredit'] = collect($debitoCredito)->groupBy('dc'); //
        $student = self::studentContract($id_contrato_alumno, $saldo);
        $data['contractStudent'] = $student;
        $data['specialDiscount'] = self::setSpecialDiscount($id_contrato_alumno, $data);
        $data['residencia'] = '';
        if ($student && $student->id_semestre && $student->id_persona) {
            $data['residencia'] = self::valResidency($student->id_semestre, $student->id_persona, $student->id_resid_tipo_habitacion);
        }
        $data['deuda_contrato'] = self::deudaContrato($id_contrato_alumno);
        return $data;
    }
    public static function deudaContrato($id_contrato_alumno)
    {

        $value = '';

        $idMatriculaDetalle = DB::table('david.vw_acad_alumno_contrato')->where('id_alumno_contrato', '=', $id_contrato_alumno)->select('id_matricula_detalle', 'id_sede')->first();
        if (!empty($idMatriculaDetalle)) {
            $value = DB::table('david.acad_deuda_contrato')
                ->where('id_matricula_detalle', '=', $idMatriculaDetalle->id_matricula_detalle)
                ->where('id_sede', '=', $idMatriculaDetalle->id_sede)
                ->where('estado', '=', '1')
                ->select('id_deuda_contrato', 'id_matricula_detalle', 'id_user', 'fecha', 'importe', 'estado')
                ->first();

            if (!empty($value)) {
                $value->existe = 'S';
            } else {
                $value['existe'] = 'N';
            }
        } else {
            $value['existe'] = 'N';
        }
        return $value;
    }
    /**
     * name="calculationDetail",
     * description=" detalle de calculo misma logica de this.show ",
     */
    public static function calculationDetail($id_contrato_alumno, $id_anho)
    {
        $data = array();

        $studentBalance = self::studentBalance($id_contrato_alumno, $id_anho);
        $saldo = 0;
        if (!empty($studentBalance)) {
            $saldo = $studentBalance->saldo;
        }
        $data['studentBalance'] = $studentBalance;

        $data['contractStudent'] = self::studentContract($id_contrato_alumno, $saldo);
        return $data;
    }

    public static function getAlumnContractDetail($id_contrato_alumno)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select(
                'a.ID_ALUMNO_CONTRATO',
                'a.ID_RESP_FINANCIERO',
                'A.MATRICULA',
                'C.NOMBRE AS NOM_PROGRAMA',
                'D.NOM_PERSONA',
                'D.NUM_DOCUMENTO',
                'D.CODIGO',
                'F.SIGLAS as nom_documento',
                'PN.FOTO',
                'PN.CELULAR',
                DB::raw("to_char(nvl(a.FECHA_MATRICULA,nvl(a.FECHA_ACTUALIZACION,a.FECHA_REGISTRO)),'dd/mm/yyyy HH:MI:ss') AS FECHA_MATRICULA"),
                DB::raw("
                (SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE MOISES.PERSONA_DIRECCION.ID_DIRECCION = (SELECT max(ID_DIRECCION)
                                                                 FROM MOISES.PERSONA_DIRECCION
                                                                 WHERE MOISES.PERSONA_DIRECCION.ID_PERSONA = A.ID_PERSONA)) as DIRECCION,
                (select DIRECCION from moises.PERSONA_VIRTUAL where ES_ACTIVO = 1 and ID_PERSONA = A.ID_PERSONA and ROWNUM = 1) as correo,
                (SELECT (SELECT nombre FROM MOISES.UBIGUEO WHERE ID_UBIGEO = MOISES.PERSONA_DIRECCION.ID_UBIGEO) FROM MOISES.PERSONA_DIRECCION where ID_PERSONA = A.ID_PERSONA and ES_ACTIVO = 1 and ROWNUM = 1) as DISTRITO,
                (SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE MOISES.PERSONA_DIRECCION.ID_DIRECCION = (SELECT max(ID_DIRECCION)
                                                                 FROM MOISES.PERSONA_DIRECCION
                                                                 WHERE MOISES.PERSONA_DIRECCION.ID_PERSONA = A.ID_RESP_FINANCIERO)) as DIRECCION_RESP,
                (SELECT (SELECT nombre FROM MOISES.UBIGUEO WHERE ID_UBIGEO = MOISES.PERSONA_DIRECCION.ID_UBIGEO) FROM MOISES.PERSONA_DIRECCION where ID_PERSONA = A.ID_RESP_FINANCIERO and ES_ACTIVO = 1 and ROWNUM = 1) as DISTRITO_RESP,
                (SELECT NUM_TELEFONO
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE MOISES.PERSONA_TELEFONO.ID_TELEFONO = (SELECT MAX(ID_TELEFONO)
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE MOISES.PERSONA_TELEFONO.ID_PERSONA = A.ID_RESP_FINANCIERO) AND MOISES.PERSONA_TELEFONO.ID_PERSONA = A.ID_RESP_FINANCIERO AND ROWNUM = 1) AS NUM_TELEFONO_RESP,
                (SELECT MOISES.PERSONA_DOCUMENTO.NUM_DOCUMENTO
                    FROM MOISES.PERSONA_DOCUMENTO
                    WHERE MOISES.PERSONA_DOCUMENTO.ID_PERSONA = A.ID_RESP_FINANCIERO
                      and ROWNUM = 1) AS num_doc_resp,
                G.NOMBRE                                                                   as tipo_resp,
                   (case
                        when A.ID_TIPO_RESP_FINANCIERO is not null then
                            decode(G.CODIGO, 'A', D.NOM_PERSONA,
                                   'P', E.PATERNO || ' ' || E.MATERNO || ', ' || E.NOMBRE ,
                                   'F', E.PATERNO || ' ' || E.MATERNO || ', ' || E.NOMBRE ,
                                   G.NOMBRE)
                        else 'sin responsable' end)                                           as NOM_RESP,
                decode(A.codigo, null, 'S/N', A.codigo) as CODIGO_CONTRATO,
                ('Son: '||FC_NUMERO_TEXTO(A.matricula)||' Soles') AS MATRICULATXT,
                (select david.TIPO_CONTRATO.CODIGO
                    FROM david.ACAD_MATRICULA_DETALLE
                             JOIN david.TIPO_CONTRATO
                                  on ACAD_MATRICULA_DETALLE.ID_TIPO_CONTRATO = TIPO_CONTRATO.ID_TIPO_CONTRATO and
                                     ACAD_MATRICULA_DETALLE.ID_NIVEL_ENSENANZA = TIPO_CONTRATO.ID_NIVEL_ENSENANZA
                    where DAVID.ACAD_MATRICULA_DETALLE.ID_MATRICULA_DETALLE =
                          A.ID_MATRICULA_DETALLE) as codigo_tipo_contrato,
                DECODE((select count(*)
                        from david.ACAD_ALUMNO_BECA
                        WHERE david.ACAD_ALUMNO_BECA.ID_TIPO_BECA_ESTATAL IS NOT NULL 
                        and DAVID.ACAD_ALUMNO_BECA.ID_PERSONA = A.ID_PERSONA), 0, '0','1') AS ES_BECA,
                DAVID.ACAD_SEMESTRE.semestre,
                   CASE WHEN (select count(vwaac.ID_ALUMNO_CONTRATO)
                    from david.vw_acad_alumno_contrato vwaac
                    where vwaac.ID_MODO_CONTRATO = '1'
                      and vwaac.estado = '1'
                      and vwaac.ID_PERSONA = A.ID_PERSONA
                      and vwaac.SEMESTRE = ACAD_SEMESTRE.SEMESTRE_ANT
                      and vwaac.SEMESTRE like '%-1'
                ) > 0 THEN 1
                   ELSE 0 END  as w_enroll,
                   (SELECT CICLO FROM david.vw_acad_alumno_contrato WHERE ID_ALUMNO_CONTRATO = A.ID_ALUMNO_CONTRATO) ciclo
                ")
            )
            ->join('DAVID.ACAD_PLAN_PROGRAMA b', 'a.ID_PLAN_PROGRAMA', '=', 'b.ID_PLAN_PROGRAMA')
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO c', 'b.ID_PROGRAMA_ESTUDIO', '=', 'c.ID_PROGRAMA_ESTUDIO')
            ->join('MOISES.VW_PERSONA_NATURAL_ALUMNO D', 'D.ID_PERSONA', '=', 'A.ID_PERSONA')
            ->join("MOISES.PERSONA_NATURAL PN", "PN.ID_PERSONA", "=", "D.ID_PERSONA")
            ->join('DAVID.ACAD_SEMESTRE_PROGRAMA', 'a.ID_SEMESTRE_PROGRAMA', '=', 'ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA')
            ->join('DAVID.ACAD_SEMESTRE', 'ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE', '=', 'ACAD_SEMESTRE.ID_SEMESTRE')
            ->leftjoin('DAVID.TIPO_RESP_FINANCIERO G', 'A.ID_TIPO_RESP_FINANCIERO', '=', 'G.ID_TIPO_RESP_FINANCIERO')
            ->leftjoin('MOISES.PERSONA E', 'E.ID_PERSONA', '=', 'A.ID_RESP_FINANCIERO')
            ->leftjoin('MOISES.TIPO_DOCUMENTO F', 'F.ID_TIPODOCUMENTO', '=', 'D.ID_TIPODOCUMENTO')
            ->where('A.ID_ALUMNO_CONTRATO', '=', $id_contrato_alumno)
            ->first();
    }

    public static function getInforEnrrollment($idStudentContract)
    {
        return DB::table('VW_MAT_CRITERIO')
            ->select(
                'MAT_ALUMNO_CONTRATO_DET.IMPORTE AS MATRICULA',
                DB::raw("('Son: '||FC_NUMERO_TEXTO(MAT_ALUMNO_CONTRATO_DET.IMPORTE)||' Soles') AS MATRICULATXT")
            )
            ->join('MAT_CRITERIO_SEMESTRE', 'VW_MAT_CRITERIO.ID_CRITERIO', '=', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO')
            ->join('MAT_ALUMNO_CONTRATO_DET', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO_SEMESTRE', '=', 'MAT_ALUMNO_CONTRATO_DET.ID_CRITERIO_SEMESTRE')
            ->where('MAT_ALUMNO_CONTRATO_DET.ID_ALUMNO_CONTRATO', $idStudentContract)
            ->where('VW_MAT_CRITERIO.CODIGO', 'MAT')
            ->first();
    }

    public static function getEnrrollmentDiscountText($idStudentContract)
    {
        return DB::table('VW_MAT_CRITERIO')
            ->select(
                DB::raw("listagg(MAT_ALUMNO_CONTRATO_DET.DESCRIPCION||'('||MAT_ALUMNO_CONTRATO_DET.IMPORTE||')', ', ') within group ( order by MAT_ALUMNO_CONTRATO_DET.DESCRIPCION ) descuentos")
            )
            ->join('MAT_CRITERIO_SEMESTRE', 'VW_MAT_CRITERIO.ID_CRITERIO', '=', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO')
            ->join('MAT_ALUMNO_CONTRATO_DET', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO_SEMESTRE', '=', 'MAT_ALUMNO_CONTRATO_DET.ID_CRITERIO_SEMESTRE')
            ->where('MAT_ALUMNO_CONTRATO_DET.ID_ALUMNO_CONTRATO', $idStudentContract)
            ->where('VW_MAT_CRITERIO.TIPO', 'M')
            ->where('MAT_ALUMNO_CONTRATO_DET.DC', 'C')->groupBy('VW_MAT_CRITERIO.TIPO')->pluck('descuentos')->first();
    }


    /**
     * name="update",
     * description="CTRL: api que recalcula los costos de pago para el alumno",
     */
    public static function update($id_contrato_alumno, $data)
    {

        $nerror = 0;
        $msgerror = "";

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $entDep = self::getEntDepStudent($id_contrato_alumno);

        if ($entDep) {

            $id_entidad = $entDep->id_entidad;
            $id_depto = $entDep->id_depto;
            $id_anho = $data->id_anho;

            DB::beginTransaction();

            try {
                self::updatePlanPaySemId($id_contrato_alumno, $data);

                $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_GENERAR_DETALLE_CONTRATO(
                                        :P_ID_ALUMNO_CONTRATO,
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); END;");

                $stmt->bindParam(':P_ID_ALUMNO_CONTRATO', $id_contrato_alumno, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();

                if ($nerror == 0) {
                    DB::commit();
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
        } else {
            $nerror = 1;
            $msgerror = 'No existe entidad o departamento';
        }

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror
        ];

        return $return;
    }

    public static function setSpecialDiscount($id, $params)
    {
        $contract = self::getContractParams($id);
        $descSpec = 0; // descuentos especial covid
        $descApplicated = 0;
        $pDesc = 0.10; // porcentaje de descuento 10%
        $saldoStudent = 0;
        $totalCredito = 0;
        $pagar = 0;
        $impEns = 0;  // Importe de enseñanaza
        $numCuotas = 0;  // num de cuotas
        $counterAvaliDisco = 0;  // num de descuentos permitidos
        //dd($contract);
        $isFinancialApp = isset($params['isFinantialApp']) and $params['isFinantialApp'] == true;
        $message = 'Saldo insuficiente, no es posible generar nota de crédito';
        $signo = $params['studentBalance']->signo;
        // [Dscto al Hijo de Misionero, Dscto al Hijo de Personal 75%, Descuento Especial Vicerrectorado Ens., Descuento Beca Aprov. Col. Adv.]
        // $specialDiscounts = ['DSCTOHHPP', 'DESHIMI', 'DESESVICEE', 'DESBECA'];
        // [Dscto al Hijo de Misionero, Dscto al Hijo de Personal 75%, Descuento Especial Vicerrectorado Ens.]
        $specialDiscounts = ['DSCTOHHPP', 'DESHIMI', 'DESESVICEE'];
        if ($contract->id_depto == '1') {
            array_push($specialDiscounts, 'DESBECA');

            /*$descBecCol = 'DESBECA';
            $crit = Criterion::with(['allChildrenCriterions'])->where('codigo',$descBecCol)->first();
            $criteChilds = $crit->allChildrenCriterions->pluck('codigo')->toArray();

            $hastenporc = in_array('DES10COAD', $criteChilds);
            dd("hola",$criteChilds, $hastenporc);
            array_push($specialDiscounts, $descBecCol);*/
        }
        // [ Beca Egresados con excelencia académica de IEA – 1er Puesto, Beca Egresados con excelencia académica de IEA – 3er-5to Puesto – 20%, Beca Egresados con excelencia académica de IEA – 2do Puesto, 1/4 Descuento Aprov. Col. Adv.]
        $specialDiscountsLima = ['DES1COAD', 'DES3Y4COAD', 'DES1Y2COAD', 'DES1Y4COAD'];
        $isIDEC = true;
        //$specialDiscounts = ['DSCTOHHPP'];
        $saldo = floatval($params['studentBalance']->total);
        $numCuotas = isset($params['contractStudent']->cuotas) ? intval($params['contractStudent']->cuotas) : 1;
        $studentSemesterPlain = intval($contract->alumno_plan_semestre); //geting  Semester student
        $isStudentTeachLvlValid = ($contract->nivel_ensenanza_codigo == 'PRE' or $contract->nivel_ensenanza_codigo == 'POS'); // if teachig level is pregrado or posgrado
        //$isHeadQValid = ($contract->id_depto == '1' or $contract->id_depto == '5'); // if heatquartees is juliaca or lima
        $isHeadQValid = true; // all headquartesIsValid
        $isRegularModeContractValid = $contract->modo_contrato_codigo == 'R'; // if mode contrac is  regular
        //$isPlainValid = $studentSemesterPlain < 2021; // if plain is difernt that 2021
        // $isPlainValid = ($contract->id_depto == '5' or $contract->id_depto == '6') ? true : ($studentSemesterPlain < 2021); // if plain is difernt that 2021 when is lima, all plains of juliaca
        $isPlainValid = ($contract->nivel_ensenanza_codigo == 'POS') ? ($studentSemesterPlain < 2021) : true; // for all plains
        $isSempTarp = ($contract->tipo_mod_est_cod == 'SP' and $contract->id_depto == '6');
        $isPresentialModeStude = ($contract->tipo_mod_est_cod == 'PR' or $isSempTarp); // mode stude is presencial[PR] or semipr[SP]/tarap
        $hasDiscountMissionarySon = $contract->misionero == 'S';
        $isAlreadyNumberDiscount = false; // the user only hav trhee discount exxept: [Descuento Matricula]
        $alreadySpecialDiscount = false;
        $alreadySpecialhighterDiscount = false;
        $balanceIsGreateThanDiscount = false;

        // descuento promosionales OJO


        /*if ($params['studentBalance']->signo === '-1') {
            $saldoStudent = floatval($params['studentBalance']->total); // REcover student Amount
        }*/
        if ($signo == "-1") {
            $saldoStudent = $saldo;
        } else if ($signo == "1") {
            $saldoStudent = $saldo * -1;
        }
        //$saldoStudent = ($signo == "-1") ? $saldo : ($signo == "1") ? $saldo*-1 : 0 ;// -1 => saldo a favor, 1=>saldo deuda, 0 saldo=0
        //dd($saldo, $signo,$saldoStudent);
        //$saldoStudent = 6116.28;

        //dd($studentSemesterPlain, $isStudentTeachLvlValid, $isHeadQValid, $isRegularModeContractValid, $params);
        if (isset($params['studentDebitCredit']) and isset($params['studentDebitCredit']['C'])) {
            foreach ($params['studentDebitCredit']['C'] as $item) {
                //$alreadySpecialDiscount = in_array($item->codigo, $specialDiscounts); // the student already a special discount
                if ($item->codigo != 'DSTOMCOVID') { // omitir descuento[Descuento Matricula]:
                    $totalCredito = $totalCredito + floatval($item->importe);
                    $counterAvaliDisco = $counterAvaliDisco + 1;
                }
                if (in_array($item->codigo, $specialDiscounts)) {
                    $alreadySpecialDiscount = true;
                    $message = 'Ya tiene descuento especial asignado, no se generará nota de crédito';
                }
                // the user already assigned higher discount
                if ($contract->id_depto == '1' and in_array($item->codigo, $specialDiscountsLima) and $studentSemesterPlain == 2021) {
                    $alreadySpecialhighterDiscount = true;
                }
            }
        }
        if (($contract->id_depto == '6' or $contract->id_depto == '5') and ($numCuotas == 1)) {
            $isAlreadyNumberDiscount = $counterAvaliDisco > 3;
        } else {
            $isAlreadyNumberDiscount = $counterAvaliDisco > 2; // 1 : false; 2 : false  3 :true  4 : true;
        }

        if (isset($params['studentDebitCredit']['D'])) {

            foreach ($params['studentDebitCredit']['D'] as $item) {
                // Obtener importe de enseñanza; para alumnos  con planes menores que 2021 y nivel pregrado, posgrado
                if ($item->codigo == "ENS") {
                    //if ($item->codigo == "ENS" and ($alumnoSemestrePlan > 2000 and $alumnoSemestrePlan < 2021) and ($alumnoNivelEnsenianza == 'POS' or $alumnoNivelEnsenianza == 'PRE')) {
                    $impEns = floatval($item->importe);

                    if ($numCuotas > 1) { // si es en cuotas
                        $pagar = floatval($params['contractStudent']->matricula1cuota);
                        //$descSpec = round((floatval($item->importe) - $totalCredito) * $pDesc, 2);
                        //$descApplicated = round($descSpec / floatval($params['contractStudent']->cuotas), 2);
                    } else if ($numCuotas == 1) { //si es al contado
                        $pagar = floatval($params['contractStudent']->contado);
                        //$descSpec = round((floatval($item->importe) - $totalCredito) * $pDesc, 2);
                        //$descApplicated = $descSpec;
                    }
                }
            }
        }
        if ($isStudentTeachLvlValid and $isHeadQValid and $isRegularModeContractValid and $isPlainValid and $isPresentialModeStude and !$alreadySpecialDiscount and !$alreadySpecialhighterDiscount and !$hasDiscountMissionarySon and !$isAlreadyNumberDiscount) {
            //$descSpec = ($impEns - $totalCredito) * $pDesc;
            //$descApplicated = $numCuotas > 1 ? $descSpec / $numCuotas : $descSpec;
            if ($isSempTarp) {
                $pDesc = $pDesc / 2;
            }

            $dS = round(($impEns - $totalCredito) * $pDesc, 2);
            $dA = round($numCuotas > 1 ? $dS / $numCuotas : $dS, 2);
            $descSpec = $dS;
            $descApplicated = $dA;
            //dd($saldoStudent, $dA);
            $slackParam = 5;
            $balanceIsGreateThanDiscount = ($saldoStudent >= (($pagar - $dA) - $slackParam));


            //dd($dA,$saldoStudent,$pagar,$dA,$slackParam,' --kehee-- ',$dA > 0 and ($saldoStudent >= (($pagar - $dA) - $slackParam)));

            if ($dS > 0) {
                $descSpec = $dS;
                if ($isFinancialApp) {
                    if ($balanceIsGreateThanDiscount) { // agregar olgura de 5 ssoles
                        DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                            ->update(['IMP_DSCTO' => $dA]);
                        $descApplicated = $dA;
                        $message = 'Se generará nota de crédito por S/.' . strval($dA);
                    } else {
                        DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                            ->update(['IMP_DSCTO' => null]);
                    }
                } else {
                    if ($balanceIsGreateThanDiscount) {
                        DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                            ->update(['IMP_DSCTO' => $dA]);
                        $descApplicated = $dA;
                    } else {
                        DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                            ->update(['IMP_DSCTO' => null]);
                        $descApplicated = null;
                        $descSpec = $dA;
                    }
                }
            } else {
                DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                    ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                    ->update(['IMP_DSCTO' => null]);
            }
            /*
                        if ($dA > 0 and  $balanceIsGreateThanDiscount) {
                            DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                                ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                                ->update(['IMP_DSCTO' => $dA]);
                            //$descSpec = $dS;
                            $descApplicated = $dA;
                            $message = 'Se generará nota de crédito por S/.' . strval($dA);
                        } else {
                            DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                                ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                                ->update(['IMP_DSCTO' => null]);
                        }*/
        } else {
            DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                ->update(['IMP_DSCTO' => null]);
        }

        /*
        if ($descSpec > 0) {
            if ($isFinancialApp) {
                if ($saldoStudent >= (($pagar - $descApplicated) - 5)) { // agregar olgura de 5 ssoles
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                } else {
                    $descApplicated = 0;
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                }
            } else {
                DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                    ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                    ->update(['IMP_DSCTO' => $descApplicated]);
            }
        }*/

        /*if ($descSpec > 0) {

            if (isset($params['isFinantialApp']) and $params['isFinantialApp'] == true) {
                if ($saldoStudent >= (($pagar - $descApplicated) - 5)) { // agregar olgura de 5 ssoles
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                } else {
                    $descApplicated = 0;
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                }
            } else {
                DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                    ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                    ->update(['IMP_DSCTO' => $descApplicated]);
            }
        }*/

        return array(
            'descuentoSpecCov' => $descSpec,
            'descuentoSpecCovApplic' => $descApplicated,
            'message' => $message,
            'validatos' => [
                'saldo_estd' => $saldoStudent,
                'pagar' => $pagar,
                'desc' => $descSpec,
                'descaplicado' => $descApplicated,
                0 => ($isStudentTeachLvlValid) . ' es nivel ense valido',
                1 => ($isHeadQValid) . ' es Sucursal valido',
                2 => ($isRegularModeContractValid) . ' es regular',
                3 => ($isPlainValid) . ' es plan valido',
                4 => ($isSempTarp) . ' es moda estudio semi tarapo valido',
                5 => ($isPresentialModeStude) . ' es moda estudio valido',
                6 => (!$alreadySpecialDiscount) . ' no tiene descuentoss especiales: ' . implode(", ", $specialDiscounts),
                7 => (!$alreadySpecialhighterDiscount) . ' no tiene descuentoss altos especiales: ' . implode(", ", $specialDiscountsLima),
                8 => (!$hasDiscountMissionarySon) . 'no tiene desc hijo de misio',
                9 => ($balanceIsGreateThanDiscount) . 'saldo es mayor que desc',
                10 => (!$isAlreadyNumberDiscount) . 'ya tiene mas  descuentos: ' . strval($counterAvaliDisco),
                11 => ($isFinancialApp) . ' es financiero app',
                12 => strval($pDesc) . ' discount percent',
                'params' => $params,
                'contract' => DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                    ->select('ACAD_ALUMNO_CONTRATO.imp_dscto')
                    ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                    ->get()
                    ->first(),
            ]
        );
    }

    public static function getContractParams($id)
    {

        return DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
            ->select(
                "TIPO_NIVEL_ENSENANZA.CODIGO  AS NIVEL_ENSENANZA_CODIGO",
                "ORG_SEDE.ID_DEPTO",
                "MODO_CONTRATO.CODIGO AS MODO_CONTRATO_CODIGO",
                "TIPO_MODALIDAD_ESTUDIO.CODIGO AS TIPO_MOD_EST_COD",
                "ACAD_ALUMNO_CONTRATO.MISIONERO"
            )
            ->selectRaw("regexp_substr(ACAD_SEMESTRE.SEMESTRE, '[^-]+', 1, 1) AS ALUMNO_PLAN_SEMESTRE")
            ->join('DAVID.ACAD_MATRICULA_DETALLE', 'ACAD_ALUMNO_CONTRATO.ID_MATRICULA_DETALLE', '=', 'ACAD_MATRICULA_DETALLE.ID_MATRICULA_DETALLE')
            ->join('DAVID.MODO_CONTRATO', 'ACAD_MATRICULA_DETALLE.ID_MODO_CONTRATO', '=', 'MODO_CONTRATO.ID_MODO_CONTRATO')
            ->join("DAVID.ACAD_PLAN_PROGRAMA", "ACAD_PLAN_PROGRAMA.ID_PLAN_PROGRAMA", "ACAD_ALUMNO_CONTRATO.ID_PLAN_PROGRAMA")
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO', 'ACAD_PLAN_PROGRAMA.ID_PROGRAMA_ESTUDIO', '=', 'ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO')
            ->join('david.TIPO_MODALIDAD_ESTUDIO', 'ACAD_PROGRAMA_ESTUDIO.ID_MODALIDAD_ESTUDIO', '=', 'TIPO_MODALIDAD_ESTUDIO.ID_MODALIDAD_ESTUDIO')
            ->join('ELISEO.ORG_SEDE_AREA', 'ACAD_PROGRAMA_ESTUDIO.ID_SEDEAREA', '=', 'ORG_SEDE_AREA.ID_SEDEAREA')
            ->join('eliseo.ORG_SEDE', 'ORG_SEDE_AREA.ID_SEDE', '=', 'ORG_SEDE.ID_SEDE')
            ->join("DAVID.ACAD_PLAN", "ACAD_PLAN.ID_PLAN", "ACAD_PLAN_PROGRAMA.ID_PLAN")
            ->join("DAVID.ACAD_SEMESTRE", "ACAD_SEMESTRE.ID_SEMESTRE", "ACAD_PLAN.ID_SEMESTRE")
            ->join("DAVID.TIPO_NIVEL_ENSENANZA", "TIPO_NIVEL_ENSENANZA.ID_NIVEL_ENSENANZA", "ACAD_ALUMNO_CONTRATO.ID_NIVEL_ENSENANZA")
            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
            ->get()
            ->first();
        /*return (object)array(
            'nivel_ensenanza_codigo' => 'POS',
            'id_depto' => '6',
            'modo_contrato_codigo' => 'R',
            'alumno_plan_semestre' => 2020,

        );*/
    }

    public static function setSpecialDiscountWW($id, $params)
    {
        $descSpec = 0; // descuentos especial covid
        $descApplicated = 0;
        $pDesc = 0.10; // porcentaje de descuento 10%
        $saldoStudent = 0;
        $pagar = 0;
        $totalCredito = 0;
        $contract = DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
            ->select(
                "ACAD_SEMESTRE.SEMESTRE",
                "TIPO_NIVEL_ENSENANZA.CODIGO  AS NIVEL_ENSENANZA_CODIGO",
                DB::raw("regexp_substr(ACAD_SEMESTRE.SEMESTRE, '[^-]+', 1, 1) AS ALUMNO_PLAN_SEMESTRE")
            )
            ->join("DAVID.ACAD_PLAN_PROGRAMA", "ACAD_PLAN_PROGRAMA.ID_PLAN_PROGRAMA", "ACAD_ALUMNO_CONTRATO.ID_PLAN_PROGRAMA")
            ->join("DAVID.ACAD_PLAN", "ACAD_PLAN.ID_PLAN", "ACAD_PLAN_PROGRAMA.ID_PLAN")
            ->join("DAVID.ACAD_SEMESTRE", "ACAD_SEMESTRE.ID_SEMESTRE", "ACAD_PLAN.ID_SEMESTRE")
            //->join("DAVID.ACAD_MATRICULA_DETALLE", "ACAD_MATRICULA_DETALLE.ID_MATRICULA_DETALLE", "ACAD_ALUMNO_CONTRATO.ID_MATRICULA_DETALLE")
            ->join("DAVID.TIPO_NIVEL_ENSENANZA", "TIPO_NIVEL_ENSENANZA.ID_NIVEL_ENSENANZA", "ACAD_ALUMNO_CONTRATO.ID_NIVEL_ENSENANZA")
            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
            ->get()
            ->first();
        $alumnoSemestrePlan = intval($contract->alumno_plan_semestre);
        $alumnoNivelEnsenianza = $contract->nivel_ensenanza_codigo;
        if ($params['studentBalance']->signo === '-1') {
            $saldoStudent = floatval($params['studentBalance']->total);
        }
        if (isset($params['studentDebitCredit']['C'])) {
            foreach ($params['studentDebitCredit']['C'] as $item) {
                if ($item->codigo != 'DSTOMCOVID') {
                    $totalCredito = $totalCredito + floatval($item->importe);
                }
            }
        }
        if (isset($params['studentDebitCredit']['D'])) {
            foreach ($params['studentDebitCredit']['D'] as $item) {
                // Obtener importe de enseñanza; para alumnos  con planes menores que 2021 y nivel pregrado, posgrado
                if ($item->codigo == "ENS" and ($alumnoSemestrePlan > 2000 and $alumnoSemestrePlan < 2021) and ($alumnoNivelEnsenianza == 'POS' or $alumnoNivelEnsenianza == 'PRE')) {
                    if (intval($params['contractStudent']->cuotas) > 1) { // si es en cuotas
                        $pagar = floatval($params['contractStudent']->matricula1cuota);
                        $descSpec = round((floatval($item->importe) - $totalCredito) * $pDesc, 2);
                        $descApplicated = round($descSpec / floatval($params['contractStudent']->cuotas), 2);
                    }
                    if ($params['contractStudent']->cuotas == '1') { //si es al contado
                        $pagar = floatval($params['contractStudent']->contado);
                        $descSpec = round((floatval($item->importe) - $totalCredito) * $pDesc, 2);
                        $descApplicated = $descSpec;
                    }
                }
            }
        }

        if ($descSpec > 0) {
            if (isset($params['isFinantialApp']) and $params['isFinantialApp'] == true) {
                if ($saldoStudent >= (($pagar - $descApplicated) - 5)) { // agregar olgura de 5 ssoles
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                } else {
                    $descApplicated = 0;
                    DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                        ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                        ->update(['IMP_DSCTO' => $descApplicated]);
                }
            } else {
                DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                    ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                    ->update(['IMP_DSCTO' => $descApplicated]);
            }
        }

        return array(
            'descuentoSpecCov' => $descSpec,
            'descuentoSpecCovApplic' => $descApplicated,
            'contract' => DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
                ->select('ACAD_ALUMNO_CONTRATO.imp_dscto')
                ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $id)
                ->get()
                ->first(),

        );
    }
    public static function valResidency($id_semestre, $id_persona, $id_resid_tipo_habitacion)
    {

        $data = DB::table("david.acad_reserva_residencia as a")
            ->select('a.estado')
            ->where('a.id_semestre', '=', $id_semestre)
            ->where('a.id_persona', '=', $id_persona)
            ->first();
        if (!empty($id_resid_tipo_habitacion)) {
            $resid = DB::table("david.residencia_tipo_habitacion as b")->where('b.id_resid_tipo_habitacion', '=', $id_resid_tipo_habitacion)->select('b.nombre')->first();
            if (!empty($resid)) {
                $data->nombre = $resid->nombre;
            }
        }
        return $data;
    }
}
