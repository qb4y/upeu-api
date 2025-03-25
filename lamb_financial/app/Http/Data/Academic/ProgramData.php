<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 13/01/20
 * Time: 02:53 PM
 */

namespace App\Http\Data\Academic;


use App\Http\Data\Financial\ProgramPaymentPlanData;
use App\Models\ProgramSemester;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Collection;

class ProgramData
{

    public static function campus(){
        return DB::table('ELISEO.ORG_SEDE')
        ->orderBy('id_sede')        
        ->get();
    }

    public static function semester(){
        return DB::table('DAVID.ACAD_SEMESTRE')
            ->where('estado','=',1)
            ->orderBy('semestre', 'desc')
            ->get(['id_semestre','semestre','nombre']);
    }

    public static function teachingLevel(){
        return DB::table('DAVID.TIPO_NIVEL_ENSENANZA')
            ->orderBy('id_nivel_ensenanza')  
            ->get(['ID_NIVEL_ENSENANZA','NOMBRE','CODIGO']);

    }
    public static function studyModality(){
        return DB::table('DAVID.TIPO_MODALIDAD_ESTUDIO')
            ->orderBy('ID_MODALIDAD_ESTUDIO')  
            ->get(['ID_MODALIDAD_ESTUDIO','NOMBRE','CODIGO']);

    }
    public static function contractMode(){
        return DB::table('DAVID.MODO_CONTRATO')
            ->orderBy('id_modo_contrato')   
            ->get(['ID_MODO_CONTRATO','NOMBRE','CODIGO']);

    }
    public static function listRequstudentshipType(){
        return DB::table('DAVID.TIPO_REQUISITO_BECA')
            ->where('DAVID.TIPO_REQUISITO_BECA.ESTADO', 1)
            ->get(['ID_TIPO_REQUISITO_BECA','NOMBRE', 'CODIGO']);
    }


    public static function listProgramsCriteriesSemester($params){
//        dd('->>>', $params->id_semestre);

        $exist = collect(Db::select("select distinct MAT_CRITERIO_SEMESTRE.ID_SEMESTRE_PROGRAMA
            from MAT_CRITERIO_SEMESTRE
                     join david.ACAD_SEMESTRE_PROGRAMA
                          on MAT_CRITERIO_SEMESTRE.ID_SEMESTRE_PROGRAMA = ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA
                     JOIN DAVID.ACAD_PROGRAMA_ESTUDIO
                          ON DAVID.ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO = DAVID.ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO
                     join ORG_SEDE_AREA on DAVID.ACAD_PROGRAMA_ESTUDIO.ID_SEDEAREA = ORG_SEDE_AREA.ID_SEDEAREA
            where ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE = $params->id_semestre
              and david.ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA = $params->id_nivel_ensenanza
              and ORG_SEDE_AREA.ID_SEDE = $params->id_sede"))
            ->pluck('id_semestre_programa')
            ->toArray();
        return DB::table('DAVID.ACAD_SEMESTRE_PROGRAMA')
            ->join('DAVID.VW_ACAD_PROGRAMA_ESTUDIO', 'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO', '=', 'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO')
            ->select(
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA',
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.MODALIDAD_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_AREA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA'
            )
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE','=',$params->id_sede)
            ->where('DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE','=',$params->id_semestre)
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA','=',$params->id_nivel_ensenanza)
            ->whereNotIn("DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA", $exist)
            ->get();

    }

    private static function PaymentPlanExistProgram(){
        return ProgramPaymentPlanData::getIdProgramsExists();
    }
    private static function CriteriaSemesterExistProgram($params){
        return ProgramPaymentPlanData::getIdProgramsSemesterCriteriaExists($params);
    }



    public static function typeContract($params) {
        return DB::table('DAVID.TIPO_CONTRATO')
            ->where('DAVID.TIPO_CONTRATO.ID_NIVEL_ENSENANZA',$params->id_nivel_ensenanza)
            ->get();
    }



    public static function programs($params){
        $q = DB::table('DAVID.ACAD_SEMESTRE_PROGRAMA')
            ->join('DAVID.VW_ACAD_PROGRAMA_ESTUDIO', 'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO', '=', 'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO')
            ->join('ELISEO.ORG_AREA', 'ORG_AREA.ID_AREA', '=', 'VW_ACAD_PROGRAMA_ESTUDIO.ID_AREA')
            ->select(
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA',
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.MODALIDAD_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_AREA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA',
                'ORG_AREA.CODIGO AS CODIGO_AREA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE'
            )
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE','=',$params->id_sede)
            ->where('DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE','=',$params->id_semestre)
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA','=',$params->id_nivel_ensenanza)
            ->whereraw("DAVID.VW_ACAD_PROGRAMA_ESTUDIO.MODALIDAD_ESTUDIO like '%".$params->modalidad_estudio."%'")
            ->whereraw("DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_MODALIDAD_ESTUDIO like '%".$params->id_modalidad_estudio."%'");


        // conditions filter
        if ($params->has('payment-plan-filter')) { // esto filtra programas que no estan registrados en plan de pagos
            $q = $q->whereNotIn('DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA', self::PaymentPlanExistProgram());
        }
        if ($params->has('criteries_semester_filter')) { // esto filtra programas que no estan registrados en plan de pagos
            $q = $q->whereNotIn('DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA', self::CriteriaSemesterExistProgram($params));
        }

        return $q->get();
    }

    public static function programsContract($params){
        return DB::table('DAVID.ACAD_SEMESTRE_PROGRAMA')
            ->join('DAVID.VW_ACAD_PROGRAMA_ESTUDIO', 'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO', '=', 'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO')
            ->join('DAVID.ACAD_ALUMNO_CONTRATO', 'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA', '=', 'ACAD_ALUMNO_CONTRATO.ID_SEMESTRE_PROGRAMA')
            ->select(
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA',
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.MODALIDAD_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_AREA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE'
            )
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_SEDE','=',$params->id_sede)
            ->where('DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE','=',$params->id_semestre)
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_NIVEL_ENSENANZA','=',$params->id_nivel_ensenanza)
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_MODALIDAD_ESTUDIO','=',$params->id_modalidad_estudio)
            ->where('DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_TIPO_CONTRATO','=',$params->id_tipo_contrato)
            ->distinct()->get();
    }

    public static function requiredOptions(){

        $data = array();

        $data['campus'] = self::campus();
        $data['semester'] = self::semester();
        $data['teachingLevel'] = self::teachingLevel();

        return $data;
    }


    public static function programsSemesterTree($query){

        //$query = self::programs($params)->groupBy('nombre_facultad');

        $data = array();
        foreach ($query as $key => $value) {

            $children = collect($value)->map(function ($item) {
                return ['text'=>$item->nombre.' - '.$item->modalidad_estudio,'value'=>$item,'checked'=> false];
            });

            array_push($data,['text' => $key, 'checked'=> false, 'value'=>null, 'children' =>$children]);
        }
        return $data;
    }
    public static function listPlanPlagoSemestre($params){
        $semesterPrograms = array_unique(explode(",", $params['id_semestre_programa']));
        $plan_pago = $params['plan_pago'];
        // dd($plan_pago);
        return collect(DB::select("SELECT  distinct 
                B.ID_PLANPAGO_SEMESTRE,A.NOMBRE,
                C.FECHA_INICIO,C.FECHA_FIN,
                (CASE WHEN A.CUOTAS <> A.CUOTA_CRO THEN C.ORDEN+1 ELSE C.ORDEN END) AS CUOTA,
                C.EJECUTADO,
                PE.NOMBRE_CORTO EAP
                FROM MAT_PLANPAGO A JOIN MAT_PLANPAGO_SEMESTRE B 
                ON A.ID_PLANPAGO = B.ID_PLANPAGO 
                JOIN DAVID.ACAD_SEMESTRE_PROGRAMA SP on SP.ID_SEMESTRE_PROGRAMA = B.ID_SEMESTRE_PROGRAMA
                JOIN DAVID.ACAD_PROGRAMA_ESTUDIO PE on SP.ID_PROGRAMA_ESTUDIO = PE.ID_PROGRAMA_ESTUDIO
                JOIN MAT_PLANPAGO_SEMESTRE_DET C ON B.ID_PLANPAGO_SEMESTRE = C.ID_PLANPAGO_SEMESTRE
                WHERE B.ID_SEMESTRE_PROGRAMA  in (" . implode(',', $semesterPrograms) . ") AND A.CUOTAS = ".$plan_pago."  order by cuota"  // 7434
            ))->map(function ($item) {
                $item->ejecutado = $item->ejecutado == "S";
            return $item;
        });
    }
    public static function getPlanPagoCuota($request){
        $id_semestre = $request->id_semestre;
        $id_programa_estudio = $request->id_programa_estudio;
        $ids = json_decode($id_programa_estudio);
        $valor = implode(',', $ids);
            $query ="SELECT D.CUOTAS AS CUOTAS_PAGO,'Plan '||D.CUOTAS||' Armadas' PLAN_PAGOS
            FROM DAVID.ACAD_SEMESTRE A
            INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA B ON B.ID_SEMESTRE =  A.ID_SEMESTRE
            INNER JOIN MAT_PLANPAGO_SEMESTRE C ON C.ID_SEMESTRE_PROGRAMA =  B.ID_SEMESTRE_PROGRAMA
            INNER JOIN MAT_PLANPAGO D ON D.ID_PLANPAGO =  C.ID_PLANPAGO  AND D.CUOTAS <> 1
            WHERE A.ID_SEMESTRE  = ".$id_semestre."  AND B.ID_PROGRAMA_ESTUDIO IN (".$valor.")
            GROUP BY D.CUOTAS
            ORDER BY CUOTAS";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getPlanPagoCuotaNew($request){
        $id_semestre = $request->id_semestre;
        $id_programa_estudio = $request->id_programa_estudio;
        $ids = json_decode($id_programa_estudio);
        $valor = implode(',', $ids);
            $query ="SELECT d.id_PLANPAGO,d.cuotas,d.cuota_cro,d.nombre,D.CUOTAS AS CUOTAS_PAGO
            FROM DAVID.ACAD_SEMESTRE A
            INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA B ON B.ID_SEMESTRE =  A.ID_SEMESTRE
            INNER JOIN MAT_PLANPAGO_SEMESTRE C ON C.ID_SEMESTRE_PROGRAMA =  B.ID_SEMESTRE_PROGRAMA
            INNER JOIN MAT_PLANPAGO D ON D.ID_PLANPAGO =  C.ID_PLANPAGO  AND D.CUOTAS <> 1
            WHERE A.ID_SEMESTRE  = ".$id_semestre."
            AND B.ID_PROGRAMA_ESTUDIO IN (".$valor.")
            and c.id_PLANPAGO_SEMESTRE in(
              select x.id_PLANPAGO_SEMESTRE from david.acad_alumno_contrato x, david.acad_semestre_programa y
              where x.id_semestre_programa=y.id_semestre_programa
              and y.id_semestre=".$id_semestre."
              and y.id_programa_estudio in(".$valor.")
            )
            GROUP BY d.id_PLANPAGO,d.cuotas,d.cuota_cro,d.nombre
            ORDER BY cuotas";

        $oQuery = DB::select($query);
        return $oQuery;
    }
}