<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 25/02/20
 * Time: 10:03 AM
 */

namespace App\Http\Data\FinancialEnrollment;


use App\Http\Data\PRun\DProcess;
use App\Http\Data\FinancialEnrollment\ProceduresDiscounts;
use App\Http\Data\GlobalMethods;
use App\Http\Data\PRun\DProcessData;
use App\Models\Contract;
use App\Models\Secuencial;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Return_;

class ProformaPaymentStudentData extends ProceduresDiscounts
{
    // acceso del usuario financiero  se cambio el tipo de count
    protected static function getActionModuleUser($id_persona, $id_entidad, $url){
        $data = array('valid'=> false, 'value' => null, 'msg'=> null);
        $query = "SELECT a.NOMBRE ,a.CLAVE FROM LAMB_ACCION a left join LAMB_MODULO b ON (a.ID_MODULO = b.ID_MODULO)
        where b.URL = '$url' and exists(select * from LAMB_ROL_MODULO_ACCION e left join LAMB_USUARIO_ROL u on (e.ID_ROL = u.ID_ROL)
        where u.ID_PERSONA=$id_persona and u.ID_ENTIDAD=$id_entidad AND e.ID_MODULO=a.ID_MODULO AND e.ID_ACCION= a.ID_ACCION)";
        $collection = DB::select($query);
        

        if (count($collection) > 1) {
            $data['valid'] = false;
            $data['msg'] = 'No puede tener mas de un rol especifico en configuracion de matricula financiera';
        } else if (count($collection) == 1) {
            $data['valid'] = true;
            $data['value'] = $collection[0]->clave;
        } else {
            $data['valid'] = false;
            $data['msg'] = 'No tiene permiso para realizar una accion en matricula financiera';
        }
        return $data;

    }

    protected static function getEntDepStudent($id_alumno_contrato){
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select(
                'd.ID_ENTIDAD',
                DB::raw('SUBSTR(d.ID_DEPTO,1,1) as ID_DEPTO, FC_NOMBRE_PERSONA(a.ID_PERSONA) as nombre_dep, FC_DOCUMENTO_CLIENTE(a.ID_PERSONA) as documento_dep'),
                'a.ID_PERSONA',
                'a.ID_ALUMNO_CONTRATO',
                'a.ID_PERSONA as id_cliente'
            )
            ->join('DAVID.ACAD_PLAN_PROGRAMA b', 'a.ID_PLAN_PROGRAMA', '=', 'b.ID_PLAN_PROGRAMA')
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO c', 'b.ID_PROGRAMA_ESTUDIO', '=', 'c.ID_PROGRAMA_ESTUDIO')
            ->join('ELISEO.ORG_SEDE_AREA d', 'c.ID_SEDEAREA', '=', 'd.ID_SEDEAREA')
            ->where('a.ID_ALUMNO_CONTRATO','=', $id_alumno_contrato)
            ->first();

    }

    // Retorna ids de criterios CREDITO de un alumno contrato
    protected static function getCriteriaDiscStudent($id_alumno_contrato) {
        return DB::table('ELISEO.MAT_ALUMNO_CONTRATO_DET as a')
            ->join('ELISEO.VW_MAT_CRITERIO_SEMESTRE as b', 'a.ID_CRITERIO_SEMESTRE', '=', 'b.ID_CRITERIO_SEMESTRE')
            ->select('b.ID_CRITERIO')
            ->where('a.ID_ALUMNO_CONTRATO','=',$id_alumno_contrato)
            ->where('b.ID_PARENT','=',null)
            ->distinct()
            ->pluck('id_criterio')
            ->toArray();
    }


    // Retorna ids de criterios base para afectar
    protected static function getCriteriaAffectsStudent($id_alumno_contrato) {
        return DB::table('ELISEO.MAT_ALUMNO_CONTRATO_DET as a')
            ->join('ELISEO.VW_MAT_CRITERIO_SEMESTRE as b', 'a.ID_CRITERIO_SEMESTRE', '=', 'b.ID_CRITERIO_SEMESTRE')
            ->select('b.ID_CRITERIO')
            ->where('a.ID_ALUMNO_CONTRATO','=',$id_alumno_contrato)
            ->where('b.ID_PARENT','=',null)
            ->where('b.DC','=','D')
            ->distinct()
            ->pluck('id_criterio')
            ->toArray();
    }



    // Obtiene los criterios tipo credito por semestre
    protected static function getDiscounts($id_semestre_programa, $id_alumno_contrato) {
        $afecta = self::getCriteriaAffectsStudent($id_alumno_contrato);

        return DB::table('ELISEO.MAT_CRITERIO_SEMESTRE as a')
            ->join('ELISEO.MAT_CRITERIO b', 'a.ID_CRITERIO', '=','b.ID_CRITERIO')
            ->select(
                'a.ID_CRITERIO_SEMESTRE',
                'b.NOMBRE',
                'a.FORMULA',
                'a.TIPO_PROCESO',
                'a.IMPORTE',
                'b.ID_PARENT',
                'b.ID_CRITERIO',
                'a.ID_PROCEDURE')
            ->where('a.ID_SEMESTRE_PROGRAMA','=', $id_semestre_programa)
            ->where('b.DC','=', 'C')
            ->whereIn('b.ID_AFECTA', $afecta)
            ->get();

    }

    // Obtiene campos principales del contrato alumno
    protected static function getContract($id_alumno_contrato) {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select('a.ID_PERSONA','a.ID_MATRICULA_DETALLE', 'a.ID_ALUMNO_CONTRATO')
            ->where('a.ID_ALUMNO_CONTRATO','=', $id_alumno_contrato)
            ->first();

    }

    // calcula el semestre contrato alumno
    protected static function getMaxSemester($contract) {

        $id_persona = $contract->id_persona;
        $id_alumno_contrato = $contract->id_alumno_contrato;
        $id_matricula_detalle = $contract->id_matricula_detalle;

        $data = DB::select("SELECT MAX(ASM.SEMESTRE) SEMESTRE FROM DAVID.ACAD_ALUMNO_CONTRATO AAC 
                  INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA ASP ON ASP.ID_SEMESTRE_PROGRAMA=AAC.ID_SEMESTRE_PROGRAMA
                  AND AAC.ID_PERSONA=$id_persona
                  AND AAC.ID_PLAN_PROGRAMA=(
                    SELECT ID_PLAN_PROGRAMA FROM DAVID.ACAD_ALUMNO_CONTRATO WHERE ID_ALUMNO_CONTRATO=$id_alumno_contrato)
                  AND AAC.ESTADO='1'
                  INNER JOIN DAVID.ACAD_SEMESTRE ASM ON ASM.ID_SEMESTRE=ASP.ID_SEMESTRE WHERE SUBSTR(ASM.SEMESTRE,-2) IN ('-1','-2')");

        if (collect($data)->count() == 1 && $data[0]->semestre) {
            return $data[0]->semestre;
        } else {
            $data = DB::select("SELECT AM.CODIGO FROM DAVID.ACAD_MATRICULA_DETALLE MD INNER JOIN DAVID.ACAD_MATRICULA AM 
                        ON AM.ID_MATRICULA=MD.ID_MATRICULA WHERE MD.ID_MATRICULA_DETALLE=$id_matricula_detalle");
            if (collect($data)->count() == 1 && $data[0]->codigo) {
                return $data[0]->codigo;
            } else {
                return null;
            }
        }
        return null;
    }


    protected static function getStudentInfo($semester, $id_alumno_contrato) {
        $data = DB::select("SELECT
               AC.ID_PERSONA,
               NVL(PN.FOTO,'lamb-academic/fotodb/sinfoto.jpg') as FOTO,
               DAVID.NOMBRE_PERSONA(AC.ID_PERSONA)AS NOMBRE_PERSONA,
               DAVID.FT_CODIGO_UNIV(AC.ID_PERSONA) AS CODIGO,
               (SELECT NOM_ESCUELA FROM DAVID.VW_ACAD_PLAN WHERE ID_PLAN=(
                        SELECT ID_PLAN FROM DAVID.ACAD_PLAN_PROGRAMA WHERE ID_PLAN_PROGRAMA = (
                        SELECT ID_PLAN_PROGRAMA FROM DAVID.ACAD_ALUMNO_CONTRATO WHERE ID_ALUMNO_CONTRATO=$id_alumno_contrato)
                        )
                    ) AS NOMBRE_PROGRAMA,
                    NVL(DAVID.FT_CALCULAR_CICLO_PROGRAMA('$semester',AC.ID_PERSONA, AC.ID_PLAN_PROGRAMA),0)+1 AS CICLO,
               '$semester' as SEMESTRE,
               DECODE(AC.ESTADO, 'M', '1', AC.ESTADO) AS ESTADO_MAT,
              --DECODE((SELECT COUNT(ID_ALUMNO_CONTRATO) FROM DAVID.ACAD_ALUMNO_CONTRATO_CURSO AACC WHERE AACC.ID_ALUMNO_CONTRATO=AC.ID_ALUMNO_CONTRATO),0,0,1) ESTADO_MAT,
               (SELECT APE.MAX_CRED_REQUERIDOS FROM DAVID.ACAD_ALUMNO_CONTRATO AAC
                   INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA ASP ON ASP.ID_SEMESTRE_PROGRAMA=AAC.ID_SEMESTRE_PROGRAMA AND ID_ALUMNO_CONTRATO=$id_alumno_contrato
                   INNER JOIN DAVID.ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO=ASP.ID_PROGRAMA_ESTUDIO) AS NUM_CRED,
               NE.NOMBRE AS NIVEL_ENSENIANZA,
               SE.NOMBRE AS PERIODO,
               AC.ID_SEMESTRE_PROGRAMA,
               AC.ID_PLAN_PROGRAMA,
               AC.ID_MATRICULA_DETALLE,
               SP.ID_SEMESTRE,
               (SELECT COUNT(AAB.ID_ALUMNO_BECA) FROM DAVID.ACAD_ALUMNO_BECA AAB WHERE AAB.ID_PERSONA=AC.ID_PERSONA AND ESTADO='1') AS ES_BECA
               FROM DAVID.ACAD_ALUMNO_CONTRATO AC
                    INNER JOIN DAVID.ACAD_MATRICULA_DETALLE AD ON AC.ID_MATRICULA_DETALLE = AD.ID_MATRICULA_DETALLE
                    INNER JOIN DAVID.TIPO_NIVEL_ENSENANZA NE ON ac.ID_NIVEL_ENSENANZA=NE.ID_NIVEL_ENSENANZA
                    INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA SP ON SP.ID_SEMESTRE_PROGRAMA = AC.ID_SEMESTRE_PROGRAMA
                    INNER JOIN DAVID.ACAD_SEMESTRE  SE ON SE.ID_SEMESTRE=SP.ID_SEMESTRE
                    INNER JOIN MOISES.PERSONA_NATURAL  PN ON PN.ID_PERSONA=AC.ID_PERSONA
                    WHERE AC.ID_ALUMNO_CONTRATO=$id_alumno_contrato");

        if (collect($data)->count() == 1) {
            return $data[0];
        } else {
            return null;
        }

    }


    public static function show($id_alumno_contrato, $user){
        $data = array();
        $rol = self::getActionModuleUser($user['id_user'], $user['id_entidad'], 'enrollment/financial-enrollment');

        if ($rol['valid'] == true) {
            $contract = self::getContract($id_alumno_contrato);
            if ($contract) {
                $semester = self::getMaxSemester($contract);
                $data['data'] = self::getStudentInfo($semester, $id_alumno_contrato);
            } else {
                $data["code"] = 403;
                $data['success'] = false;
                $data['message'] = 'No existe contrato';
            }
            $data['rol'] = $rol['value'];
            $data['enableDeposit'] = $rol['value'] == 'CAJERO';

        } else {
            $data["code"] = 403;
            $data['success'] = false;
            $data['message'] = $rol['msg'];
        }
        return $data;

    }

    public static function getCriteriaSemProgDisc($id_alumno_contrato, $id_semestre_programa) {
        $ids = self::getCriteriaDiscStudent($id_alumno_contrato);
        $data = self::getDiscounts($id_semestre_programa, $id_alumno_contrato);
        foreach($data as $row){
            $row->checked = false;
            if (in_array($row->id_criterio, $ids)) {
                $row->checked = true;
            }
        }
        return $data;
    }


    public static function updateDiscounts($id_alumno_contrato, $data) {
        return self::beca();
    }

    protected static function getDynTraAccounting($id_entidad, $id_depto) {
        $query = DB::table('ELISEO.CONTA_DINAMICA a')
            ->join('ELISEO.TIPO_TRANSACCION b', 'a.ID_TIPOTRANSACCION','=','b.ID_TIPOTRANSACCION')
            ->join('ELISEO.TIPO_GRUPO_CONTA c', 'b.ID_TIPOGRUPOCONTA','=','c.ID_TIPOGRUPOCONTA')
            ->select(
                'a.ID_TIPOTRANSACCION',
                'a.ID_DINAMICA',
                'a.ID_CTABANCARIA',
                'b.NOMBRE'
            )
            ->where([
                ['a.ID_ENTIDAD','=',$id_entidad],
                ['a.ID_DEPTO','=',$id_depto],
                ['a.ID_ANHO','=',date("Y")],
                ['a.ID_MODULO','=', 14],
                ['c.CODIGO','=','DA'],
                ['a.ACTIVO','=','S'],
            ]);
        if ($query->exists()) {
            return $query->first();
        }
        return null;
        
    }

    protected static function seatType($id_entidad, $id_depto) {
        $q = DB::table('ELISEO.CONTA_VOUCHER_CONFIG a ')
            ->select('a.id_tipoasiento')
            ->where([
                ['a.ID_ENTIDAD','=', $id_entidad],
                ['a.ID_DEPTO','=', $id_depto],
                ['a.ID_ANHO','=', date("Y")],
                ['a.ID_TIPOVOUCHER','=', 5], // SOLO INGRESO POR DEFECTO EN REQUERIMIENTO
            ]);
        if ($q->exists()) {
            return $q->first()->id_tipoasiento;
        }
        return null;

    }

    // FUNCION PRINCIPAL - GENERAR MATRICULA FINANCIERA
    public static function generateContratc($request, $user, $tipoCambio) {

        $dataOuput = array('success'=> false, 'message'=>null, 'data'=>null);


        $rol = ProformaPaymentValidatorData::getActionModuleUser($user['id_user'], $user['id_entidad'], 'enrollment/financial-enrollment');

        if ($rol['valid']) {

            $dataPost = array();
            $data = self::getEntDepStudent($request->id_alumno_contrato);

            //  Depuracion de parametros para el rol de cajero en $dataPost
            if ($rol['value'] == 'CAJERO') {

                $codigo_efectivo = '008';

                $id_entidad = $user['id_entidad'];
                $id_depto = $user['id_depto'];
                $id_persona = $user['id_user'];

                $dataPost = array_merge($dataPost, $request->all());

                $dataPost['tipocambio'] = $tipoCambio;
                $dataPost['id_anio'] = date("Y");
                $dataPost['id_mes'] = intval(date("m"));
                $dataPost['id_persona'] = $id_persona;
                $dataPost['id_entidad'] = $id_entidad;
                $dataPost['id_depto'] = $id_depto;
                $dataPost['id_cliente'] = $data->id_cliente;
                $dataPost['nombre_dep'] = $data->nombre_dep;
                $dataPost['documento_dep'] = $data->documento_dep;
                $dataPost['id_tipoasiento'] = self::seatType($id_entidad, $id_depto);

                $dinamicTrans = self::getDynTraAccounting($id_entidad, $id_depto);

                if ($dinamicTrans) {
                    $dataPost['id_tipotransaccion'] = $dinamicTrans->id_tipotransaccion;
                    $dataPost['id_dinamica'] = $dinamicTrans->id_dinamica;
                    $dataPost['id_ctabancaria'] = $dinamicTrans->id_ctabancaria;
                    $dataPost['glosa'] = $dinamicTrans->nombre;

                } else {
                    $dataPost['id_tipotransaccion'] = null;
                    $dataPost['id_dinamica'] = null;
                    $dataPost['id_ctabancaria'] = null;
                    $dataPost['glosa'] = null;
                }

                if ($request->id_mediopago === $codigo_efectivo) {
                    $dataPost['importe'] = $request->importe;
                    $dataPost['importe_tarjeta'] = null;
                } else {
                    $dataPost['importe'] = null;
                    $dataPost['importe_tarjeta'] = $request->importe;
                }

            } else {
                $dataPost = array_merge($dataPost, $request->all());
                $dataPost['id_cliente'] = $data->id_cliente;
            }

            // EJECUTA PROCEDIMIENTO PRINCIPAL
            // Dentro de esta funciona realiza el rollback y el contrato
            $contrato = self::generarContrato($data, $dataPost, $rol['value'], $user);

            if ($contrato['nerror'] == 0) {
                $dataOuput['success'] = true;
                $dataOuput['message'] = $contrato['msgerror'].' ; Alumno: '.$data->nombre_dep.'';
                $dataOuput['data'] = $contrato['dataImprime'];
            } else {
                $dataOuput['success'] = false;
                $dataOuput['message'] = $contrato['msgerror'];
            }

        } else {
            $dataOuput['success'] = $rol['valid'];
            $dataOuput['message'] = $rol['msg'];
        }

        return $dataOuput;

    }

    private static function getSemesterActive() {
        return DB::table('david.acad_config_solicitud a')
            ->select('a.id_semestre')
            ->where('a.estado', '=' ,1)
            ->pluck('id_semestre')
            ->first();
    }


    private static function getMatricula() {
        $query = "SELECT MD.* , S.ID_SEMESTRE, Mc.*   FROM DAVID.ACAD_MATRICULA_DETALLE MD
            INNER JOIN DAVID.ACAD_MATRICULA M ON MD.ID_MATRICULA=M.ID_MATRICULA
            INNER JOIN DAVID.ACAD_SEMESTRE S ON S.ID_SEMESTRE=M.ID_SEMESTRE
            INNER JOIN DAVID.MODO_CONTRATO MC ON MC.ID_MODO_CONTRATO=MD.ID_MODO_CONTRATO
            WHERE MD.ID_MODO_CONTRATO=4 AND MD.ESTADO='1'";
        return DB::select($query);
    }

    private static function getMatriculaVariacion($id_persona, $id_semestre, $id_nivel_ensenanza, $id_tipo_contrato) {
        $query = "SELECT AMD.ID_MATRICULA_DETALLE,AMD.ID_PROCESO, MC.NOMBRE as MODO_NOMBRE, MC.CODIGO AS CODIGO_MODO, MC.ID_MODO_CONTRATO, TC.NOMBRE AS TIPO_CONTRATO_NOMBRE,
                            TNE.NOMBRE AS NIVEL_ENSENIANZA_NOMBRE,
                            NOM_ESCUELA||' - '||NVL(SM.NOMBRE,TME.NOMBRE)||' - Plan: '||NOMBRE_PLAN || ' Modo: ' || MC.NOMBRE AS NOMBRE,APE.ID_TIPO_CONTRATO,
                            APE.ID_NIVEL_ENSENANZA AS ID_NIVEL_ENSENIANZA, aac.ID_PLAN_PROGRAMA,
                            AAC.ID_ALUMNO_CONTRATO, APE.SEDE , aac.id_alumno_contrato_clon,
                            (select ESTADO from david.ACAD_ALUMNO_CONTRATO WHERE ID_ALUMNO_CONTRATO = aac.id_alumno_contrato_clon and ROWNUM  = 1) as ESTADO_CONTRATO,
                            AAC.ESTADO AS ESTADO_CONTRATO_ASOCIADO,
                            APE.SEDE, SEE.ID_SEMESTRE,SEE.NOMBRE AS NOMBRE_SEMESTRE,
            APE.NOMBRE_ESCUELA as PROGRAMA_ESTUDIO,
            APE.NOMBRE_FACULTAD AS UNIDAD_ACADEMICA,
            APE.MODALIDAD_ESTUDIO,
            'Sin código' as CODIGO_SOLICITUD,

            NOMBRE_PLAN
            FROM david.acad_alumno_contrato AAC INNER JOIN
            david.ACAD_MATRICULA_DETALLE AMD ON AAC.ID_MATRICULA_DETALLE=AMD.ID_MATRICULA_DETALLE
            INNER JOIN  david.ACAD_MATRICULA M ON M.ID_MATRICULA=AMD.ID_MATRICULA
            INNER JOIN david.ACAD_SEMESTRE SEE ON SEE.ID_SEMESTRE=M.ID_SEMESTRE
            INNER JOIN david.ACAD_PLAN_PROGRAMA APP ON APP.ID_PLAN_PROGRAMA=AAC.ID_PLAN_PROGRAMA
            INNER JOIN david.VW_ACAD_PLAN AP ON AP.ID_PLAN=APP.ID_PLAN
            INNER JOIN david.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO=APP.ID_PROGRAMA_ESTUDIO
            INNER JOIN david.TIPO_MODALIDAD_ESTUDIO TME ON TME.ID_MODALIDAD_ESTUDIO=APE.ID_MODALIDAD_ESTUDIO
            LEFT JOIN david.ACAD_SEMIPRESENCIAL SM ON SM.ID_SEMIPRESENCIAL=APE.ID_SEMIPRESENCIAL
            INNER JOIN david.TIPO_CONTRATO TC ON TC.ID_TIPO_CONTRATO=APE.ID_TIPO_CONTRATO AND TC.ID_NIVEL_ENSENANZA=APE.ID_NIVEL_ENSENANZA
            INNER JOIN david.TIPO_NIVEL_ENSENANZA TNE ON TNE.ID_NIVEL_ENSENANZA=TC.ID_NIVEL_ENSENANZA
            INNER JOIN david.MODO_CONTRATO MC ON AMD.ID_MODO_CONTRATO=MC.ID_MODO_CONTRATO
        WHERE  AMD.ID_NIVEL_ENSENANZA=:id_nivel_ensenanza_p  AND amd.id_tipo_contrato=:id_tipo_contrato_p AND id_persona=:id_persona_p  AND M.ID_SEMESTRE=:id_semestre_p AND AAC.ESTADO='1'
        AND  MC.CODIGO='R'
        AND AAC.id_alumno_contrato not in (select nvl(id_alumno_contrato_clon,0) from david.acad_alumno_contrato where id_persona = :id_persona_p)";

        return DB::select($query, ['id_persona_p' => $id_persona, 'id_semestre_p' => $id_semestre, 'id_nivel_ensenanza_p' => $id_nivel_ensenanza, 'id_tipo_contrato_p' => $id_tipo_contrato]);
    }


    public static function getProgramPlanStudent($id_persona) {
        $response = array();
        $id_semestre = self::getSemesterActive();

        $query = "SELECT AMD.ID_MATRICULA_DETALLE, 
        AAC.ID_SOLICITUD_MAT_ALUM,
        AMD.ID_PROCESO, 
        MC.NOMBRE as MODO_NOMBRE, 
        MC.CODIGO AS CODIGO_MODO, 
        MC.ID_MODO_CONTRATO, 
        TC.NOMBRE AS TIPO_CONTRATO_NOMBRE,
        TNE.NOMBRE AS NIVEL_ENSENIANZA_NOMBRE,
        NOM_ESCUELA||' - '||NVL(SM.NOMBRE,TME.NOMBRE)||' - Plan: '||NOMBRE_PLAN ||' . Semestre:' || MA.CODIGO AS NOMBRE,
        APE.ID_TIPO_CONTRATO,
        APE.ID_NIVEL_ENSENANZA AS ID_NIVEL_ENSENIANZA, APP.ID_PLAN_PROGRAMA,
        AAC.ID_ALUMNO_CONTRATO,AAC.ESTADO AS ESTADO_CONTRATO, APE.SEDE, SEE.ID_SEMESTRE,SEE.NOMBRE AS NOMBRE_SEMESTRE,
        APE.NOMBRE_ESCUELA as PROGRAMA_ESTUDIO,
        APE.NOMBRE_FACULTAD AS UNIDAD_ACADEMICA,
        APE.MODALIDAD_ESTUDIO,
        NOMBRE_PLAN

        FROM david.ACAD_ALUMNO_PLAN AAP
        INNER JOIN david.ACAD_PLAN_PROGRAMA APP ON APP.ID_PLAN_PROGRAMA=AAP.ID_PLAN_PROGRAMA
        AND AAP.ID_PERSONA=:id_persona_p AND AAP.ESTADO=1
        INNER JOIN david.VW_ACAD_PLAN AP ON AP.ID_PLAN=APP.ID_PLAN
        INNER JOIN david.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO=APP.ID_PROGRAMA_ESTUDIO
        INNER JOIN david.TIPO_MODALIDAD_ESTUDIO TME ON TME.ID_MODALIDAD_ESTUDIO=APE.ID_MODALIDAD_ESTUDIO
        LEFT JOIN david.ACAD_SEMIPRESENCIAL SM ON SM.ID_SEMIPRESENCIAL=APE.ID_SEMIPRESENCIAL
        INNER JOIN david.TIPO_CONTRATO TC ON TC.ID_TIPO_CONTRATO=APE.ID_TIPO_CONTRATO AND TC.ID_NIVEL_ENSENANZA=APE.ID_NIVEL_ENSENANZA
        INNER JOIN david.TIPO_NIVEL_ENSENANZA TNE ON TNE.ID_NIVEL_ENSENANZA=TC.ID_NIVEL_ENSENANZA
        INNER JOIN david.ACAD_MATRICULA_DETALLE AMD ON AMD.ID_TIPO_CONTRATO=TC.ID_TIPO_CONTRATO AND AMD.ID_NIVEL_ENSENANZA=TC.ID_NIVEL_ENSENANZA AND AMD.ESTADO=1 AND AMD.ID_MODO_CONTRATO in (1,6,8)
        INNER JOIN david.ACAD_MATRICULA MA ON MA.ID_MATRICULA=AMD.ID_MATRICULA
        INNER JOIN david.ACAD_SEMESTRE SEE ON SEE.ID_SEMESTRE=MA.ID_SEMESTRE
        LEFT JOIN david.MODO_CONTRATO MC ON AMD.ID_MODO_CONTRATO=MC.ID_MODO_CONTRATO
        LEFT JOIN david.ACAD_ALUMNO_CONTRATO AAC ON AAC.ID_PERSONA=AAP.ID_PERSONA AND AAC.ID_MATRICULA_DETALLE=AMD.ID_MATRICULA_DETALLE AND AAC.ID_PLAN_PROGRAMA=AAP.ID_PLAN_PROGRAMA
        INNER JOIN david.MATRICULA_PROGRAMA MP ON MP.ID_MATRICULA_DETALLE=AMD.ID_MATRICULA_DETALLE AND MP.ESTADO=1
        INNER JOIN david.ACAD_SEMESTRE_PROGRAMA ASP ON ASP.ID_SEMESTRE_PROGRAMA=MP.ID_SEMESTRE_PROGRAMA
                    AND ASP.ID_PROGRAMA_ESTUDIO=APE.ID_PROGRAMA_ESTUDIO";
        $response = DB::select($query, ['id_persona_p' => $id_persona]);


        $queryCarta = "SELECT AMD.ID_MATRICULA_DETALLE,AMD.ID_PROCESO, SMA.ID_SOLICITUD_MAT_ALUM, MC.NOMBRE as MODO_NOMBRE, MC.CODIGO AS CODIGO_MODO, MC.ID_MODO_CONTRATO, TC.NOMBRE AS TIPO_CONTRATO_NOMBRE,
        TNE.NOMBRE AS NIVEL_ENSENIANZA_NOMBRE,
        SMA.CODIGO || ':' || MC.NOMBRE || ' ' || NOM_ESCUELA||' - '||NVL(SM.NOMBRE,TME.NOMBRE)||' Semestre: '|| SEE.NOMBRE ||  ' - Plan: '||NOMBRE_PLAN AS NOMBRE,APE.ID_TIPO_CONTRATO,
        APE.ID_NIVEL_ENSENANZA AS ID_NIVEL_ENSENIANZA, SMA.ID_PLAN_PROGRAMA,
        AAC.ID_ALUMNO_CONTRATO,AAC.ESTADO AS ESTADO_CONTRATO, APE.SEDE, SEE.ID_SEMESTRE,SEE.NOMBRE AS NOMBRE_SEMESTRE,
        APE.NOMBRE_ESCUELA as PROGRAMA_ESTUDIO,
        APE.NOMBRE_FACULTAD AS UNIDAD_ACADEMICA,
        APE.MODALIDAD_ESTUDIO,
        NOMBRE_PLAN,
        SMA.CODIGO as CODIGO_SOLICITUD

        FROM david.ACAD_ALUMNO_PLAN AAP
        INNER JOIN david.ACAD_PLAN_PROGRAMA APP ON APP.ID_PLAN_PROGRAMA=AAP.ID_PLAN_PROGRAMA
                    AND AAP.ID_PERSONA=:id_persona_p AND AAP.ESTADO=1
        INNER JOIN david.VW_ACAD_PLAN AP ON AP.ID_PLAN=APP.ID_PLAN
        INNER JOIN david.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO=APP.ID_PROGRAMA_ESTUDIO
        INNER JOIN david.TIPO_MODALIDAD_ESTUDIO TME ON TME.ID_MODALIDAD_ESTUDIO=APE.ID_MODALIDAD_ESTUDIO
        LEFT JOIN david.ACAD_SEMIPRESENCIAL SM ON SM.ID_SEMIPRESENCIAL=APE.ID_SEMIPRESENCIAL
        INNER JOIN david.TIPO_CONTRATO TC ON TC.ID_TIPO_CONTRATO=APE.ID_TIPO_CONTRATO AND TC.ID_NIVEL_ENSENANZA=APE.ID_NIVEL_ENSENANZA
        INNER JOIN david.TIPO_NIVEL_ENSENANZA TNE ON TNE.ID_NIVEL_ENSENANZA=TC.ID_NIVEL_ENSENANZA 
        INNER JOIN david.ACAD_MATRICULA_DETALLE AMD ON
                     AMD.ID_NIVEL_ENSENANZA=TC.ID_NIVEL_ENSENANZA AND AMD.ESTADO=1 AND AMD.ID_MODO_CONTRATO IN (2,3,5,6) AND AMD.ID_TIPO_CONTRATO = TC.ID_TIPO_CONTRATO 
        INNER JOIN david.ACAD_MATRICULA  AM ON AM.ID_MATRICULA=AMD.ID_MATRICULA
        INNER JOIN david.ACAD_SEMESTRE SEE ON SEE.ID_SEMESTRE=AM.ID_SEMESTRE
        INNER JOIN david.MODO_CONTRATO MC ON AMD.ID_MODO_CONTRATO=MC.ID_MODO_CONTRATO
        INNER JOIN david.TIPO_SOLICITUD_MATRICULA TSM ON TSM.ID_TIPO_SOLICITUD_MATRICULA= MC.ID_TIPO_SOLICITUD_MATRICULA
        INNER JOIN david.SOLICITUD_MAT_ALUM SMA ON SMA.ID_TIPO_SOLICITUD_MATRICULA=TSM.ID_TIPO_SOLICITUD_MATRICULA AND SMA.ID_PERSONA=:id_persona_p
            
            and SMA.ID_PLAN_PROGRAMA = APP.ID_PLAN_PROGRAMA AND SMA.ESTADO='2' AND SMA.ID_SEMESTRE=AM.ID_SEMESTRE
        LEFT JOIN david.ACAD_ALUMNO_CONTRATO AAC ON AAC.ID_PERSONA=AAP.ID_PERSONA AND AAC.ID_MATRICULA_DETALLE=AMD.ID_MATRICULA_DETALLE
        AND AAC.ID_PLAN_PROGRAMA=AAP.ID_PLAN_PROGRAMA AND SMA.ID_SOLICITUD_MAT_ALUM=AAC.ID_SOLICITUD_MAT_ALUM
        WHERE SMA.ID_TIPO_SOLICITUD_MATRICULA IN (7,8,13,15)";

        $runCarta = DB::select($queryCarta, ['id_persona_p' => $id_persona]);

        if ($runCarta) {
            $response=array_merge($response, $runCarta);
        }
        $matricula_variacion = self::getMatricula();
        foreach($matricula_variacion as &$item) {
            $matriculaVariacion = self::getMatriculaVariacion($id_persona, $item->id_semestre, $item->id_nivel_ensenanza, $item->id_tipo_contrato);
            // dd($response, 'variaciones', $matricula_variacion, 'otrasvariones??', $matriculaVariacion);
            foreach($matriculaVariacion as &$item2){
                $resultVariation = array(
                    "id_matricula_detalle"=>  $item->id_matricula_detalle,
                    "id_proceso"=>  $item->id_proceso,
                    "modo_nombre"=>  $item->nombre,
                    "codigo_modo"=>$item->codigo,
                    "id_modo_contrato"=>  $item->id_modo_contrato,
                    "tipo_contrato_nombre"=>  $item2->tipo_contrato_nombre,
                    "nivel_ensenianza_nombre"=>  $item2->nivel_ensenianza_nombre,
                    "nombre"=>  $item2->nombre,
                    "id_tipo_contrato"=>  $item2->id_tipo_contrato,
                    "id_nivel_ensenanza"=>  $item2->id_nivel_ensenianza,
                    "id_plan_programa"=>  $item2->id_plan_programa,
                    "id_alumno_contrato" =>$item2->id_alumno_contrato,
                    "id_alumno_contrato_clon" =>$item2->id_alumno_contrato_clon,
                    "estado_contrato" =>$item2->estado_contrato,
                    "estado_contrato_asociado" =>$item2->estado_contrato,
                    "sede" =>$item2->sede
                );
                array_push($response,$resultVariation);
            }

        }

        return $response;

    }


    public static function cleanLegalClient($id_alumno_contrato) {
        DB::table('DAVID.ACAD_ALUMNO_CONTRATO a')
            ->where('a.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->update(['a.ID_CLIENTE_LEGAL' => null]);
        
    }


    // solo para pruebas desde api (ejecucion real en Procedures discounts)
    public static function generateTicket($id_comprobante, $id_venta, $user) {
        $request = array();
        $request['id_comprobante'] = $id_comprobante;
        $request['id_venta'] = $id_venta;
        return ProformaPaymentTicket::ticket($request, $user);
    }

    // solo para pruebas desde api (ejecucion real en Procedures discounts)
    public static function generateTicketDeposito($id_venta, $user) {
        $request = array();
        $request['id_venta'] = $id_venta;
        return ProformaPaymentTicket::ticketDeposito($request, $user);
    }



    public static function changeMissionary($id_alumno_contrato, $state) {
        DB::table('DAVID.ACAD_ALUMNO_CONTRATO a')
            ->where('a.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->update(['a.MISIONERO' => $state]);

    }


    public static function getImprimeTicketContrato($request, $user) {
        return self::imprimeTicketContrato($request, $user);
    }


    public static function finishContract($id_alumno_contrato, $session) {
        $response = array();
        try {
            DB::beginTransaction();
            DProcessData::finishContract($id_alumno_contrato, $session);
            DB::commit();
            $response['success'] = true;
            $response['message'] = 'ok';
        } catch (\Exception $e) {
            DB::rollBack();
            $response['code'] = 422;
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;

    }


    public static function finishContractVariation($id_alumno_contrato, $id_user, $session) {
        $response = array();
        try {
            DB::beginTransaction();
            DProcessData::finishVariation($id_alumno_contrato, $id_user, $session);
            DB::commit();
            $response['success'] = true;
            $response['message'] = 'ok';
        } catch (\Exception $e) {
            DB::rollBack();
            $response['code'] = 422;
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }


    public static function getCreditoVariation($id_alumno_contrato) {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
            ->select('A.CREDITOSVAR')
            ->where('A.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->pluck('creditosvar')
            ->first();
    }



    public static function getModeContract($id_alumno_contrato) {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
            ->select('D.CODIGO')
            ->join('DAVID.ACAD_MATRICULA_DETALLE C', 'A.ID_MATRICULA_DETALLE', '=', 'C.ID_MATRICULA_DETALLE')
            ->join('DAVID.MODO_CONTRATO D', 'C.ID_MODO_CONTRATO', '=', 'D.ID_MODO_CONTRATO')
            ->where('A.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->pluck('codigo')
            ->first();
    }
    public static function getCheckDiscountRequest($params) {
        return DB::select("SELECT ID_PERSONA,B.NOMBRE,B.MODALIDAD, A.FECHA_REGISTRO, A.ESTADO, C.NOMBRE ESTADO_NOMBRE
        FROM DAVID.SOLICITUD_MAT_ALUM A 
            JOIN DAVID.TIPO_REQUISITO_BECA B ON A.ID_TIPO_REQUISITO_BECA = B.ID_TIPO_REQUISITO_BECA
            JOIN DAVID.TIPO_SOLICITUD_MATRICULA C on A.ID_TIPO_SOLICITUD_MATRICULA = C.ID_TIPO_SOLICITUD_MATRICULA
        WHERE A.ID_PERSONA = ?
        AND A.ID_SEMESTRE = ?
        AND A.ESTADO IN ('0','1')", [$params['id_persona'], $params['id_semestre']]);
    }













}
