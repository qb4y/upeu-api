<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21/01/20
 * Time: 11:38 AM
 */


namespace App\Http\Data\Financial;

use App\Models\CriterionSemesterSeat;
use Illuminate\Support\Facades\DB;
use Validator;
  
  
class CriterionSemesterSeatData
{

    public static function getIndex($request){

        $cte = "(CASE
        WHEN c.id_tipoctacte = 'ENTI' 
        THEN 'es entidad'
        WHEN c.id_tipoctacte = 'DEPTO'
        THEN 'es depto'
        ELSE (SELECT NOMBRE FROM CONTA_ENTIDAD_CTA_CTE WHERE ID_ENTIDAD=a.id_entidad AND ID_TIPOCTACTE=c.id_tipoctacte and ID_CTACTE=a.id_ctacte and ROWNUM = 1)
        END) as cte";

        return DB::table('ELISEO.MAT_CRITERIO_SEMESTRE_ASIENTO a')
            ->join('ELISEO.TIPO_PLAN b', 'a.id_tipoplan', '=', 'b.id_tipoplan')
            ->join('ELISEO.CONTA_CTA_DENOMINACIONAL c', 'a.id_cuentaaasi', '=', 'c.id_cuentaaasi')
            ->join('ELISEO.CONTA_RESTRICCION d', 'a.id_restriccion', '=', 'd.id_restriccion')
            ->join('ELISEO.MAT_CRITERIO_SEMESTRE e', 'a.id_criterio_semestre', '=', 'e.id_criterio_semestre')
            ->join('ELISEO.MAT_CRITERIO f', 'e.id_criterio', '=', 'f.id_criterio')
            ->leftJoin('ELISEO.CONTA_ENTIDAD_DEPTO g', 'a.id_depto', '=', 'g.id_depto')
            ->select(
                'a.id_criterio_semestre_asiento',
                'b.nombre as tipo_plan',
                "c.nombre as cta",
                DB::raw($cte),
                "d.nombre as restriccion",
                "e.importe",
                "e.formula",
                "e.tipo_proceso",
                "f.nombre as criterio",
                "g.nombre as depto",
                'a.porcentaje',
                'a.fecha_inicio',
                'a.fecha_fin',
                'b.id_tipoplan',
                'c.id_tipoctacte',
                'a.id_cuentaaasi',
                'a.id_restriccion',
                'a.id_ctacte',
                'a.id_depto',
                'a.id_criterio_semestre',
                'a.es_eap',
                'a.tipo_dc'
            )
            ->whereRaw('a.id_tipoplan = c.id_tipoplan and a.id_restriccion = c.id_restriccion')
            ->where('a.id_criterio_semestre','=',$request->id_criterio_semestre)
            ->get();
            //->paginate(5);



        //return CriterionSemesterSeat::paginate(5);
    }

    public static function validators($request){
        return Validator::make($request, [
            'fecha_inicio' => 'required',
            'id_criterio_semestre' => 'required',
            'id_cuentaaasi' => 'required',
            'id_entidad' => 'required',
            'id_restriccion' => 'required',
            'id_tipoplan' => 'required',
            'porcentaje' => 'required',
            'es_eap' => 'required',
            'tipo_dc' => 'required'
            ]);
    }

    public static function getStore($request){
        return CriterionSemesterSeat::create($request->all());
    }

    public static function getDestroy($id){
        return CriterionSemesterSeat::destroy($id);
    }

    public static function getShow($id){
        return CriterionSemesterSeat::find($id);
    }

    public static function getUpdate($id, $request){

        return DB::table('ELISEO.MAT_CRITERIO_SEMESTRE_ASIENTO')
            ->where('id_criterio_semestre_asiento',$id)
            ->where('id_criterio_semestre',$request->id_criterio_semestre)
            ->update([
                'id_tipoplan'=>$request->id_tipoplan,
                'id_cuentaaasi'=>$request->id_cuentaaasi,
                'id_restriccion'=>$request->id_restriccion,
                'id_depto'=>$request->id_depto,
                'id_ctacte'=>$request->id_ctacte,
                'porcentaje'=>$request->porcentaje,
                'fecha_inicio'=>$request->fecha_inicio,
                'fecha_fin'=>$request->fecha_fin,
                'es_eap'=>$request->es_eap,
                'tipo_dc'=>$request->tipo_dc

            ]);

        //return CriterionSemesterSeat::find($id)->update($request->all());
    }

    public static function deleteseat($id,$id_criterio_semestre){
        return CriterionSemesterSeat::where('id_criterio_semestre_asiento',$id)
        ->where('id_criterio_semestre',$id_criterio_semestre)->delete();
    }










}