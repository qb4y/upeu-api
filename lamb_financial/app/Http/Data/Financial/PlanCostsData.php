<?php


namespace App\Http\Data\Financial;


use Illuminate\Support\Facades\DB;

class PlanCostsData
{


    /**
     * name="criterion",
     * description="Obtiene criterios.",
     */
    public static function index($params)
    {
        /*return DB::table('DAVID.ACAD_CARGA_CURSO_DET')
            ->select(
                'DAVID.ACAD_PLAN.ID_PLAN',
                'DAVID.ACAD_PLAN.NOMBRE',
                'MAT_PLAN_COSTO.ID_PLAN_COSTO',
                'MAT_PLAN_COSTO.IMPORTE'
            )
            ->join('DAVID.ACAD_PLAN_CURSO', 'DAVID.ACAD_CARGA_CURSO_DET.ID_PLAN_CURSO', '=', 'DAVID.ACAD_PLAN_CURSO.ID_PLAN_CURSO')
            ->join('DAVID.ACAD_PLAN', 'DAVID.ACAD_PLAN_CURSO.ID_PLAN', '=', 'DAVID.ACAD_PLAN.ID_PLAN')
            ->join('DAVID.ACAD_CARGA_CURSO', 'DAVID.ACAD_CARGA_CURSO_DET.ID_CARGA_CURSO', '=', 'DAVID.ACAD_CARGA_CURSO.ID_CARGA_CURSO')
            ->leftjoin('MAT_PLAN_COSTO', function ($join) use ($params) {
                $join->on('DAVID.ACAD_PLAN.ID_PLAN', '=', 'MAT_PLAN_COSTO.ID_PLAN')
                    ->where('MAT_PLAN_COSTO.ID_SEMESTRE_PROGRAMA', $params->id_semestre_programa)
                    ->where('MAT_PLAN_COSTO.ID_CRITERIO', $params->id_criterio);
            })
            ->where('DAVID.ACAD_CARGA_CURSO.ID_SEMESTRE_PROGRAMA', $params->id_semestre_programa)
            ->where('DAVID.ACAD_PLAN.ID_AREA', $params->id_area)
            ->groupBy('DAVID.ACAD_PLAN.ID_PLAN', 'DAVID.ACAD_PLAN.NOMBRE', 'MAT_PLAN_COSTO.ID_PLAN_COSTO', 'MAT_PLAN_COSTO.IMPORTE')
            ->orderBy('DAVID.ACAD_PLAN.ID_PLAN', 'desc')
            ->get();*/
        return DB::select("SELECT DISTINCT
                    C.ID_PLAN,
                    C.NOMBRE,
                    E.ID_PLAN_COSTO,
                    E.IMPORTE
                    FROM         
                    DAVID.ACAD_CARGA_CURSO_DET A JOIN DAVID.ACAD_PLAN_CURSO B ON A.ID_PLAN_CURSO = B.ID_PLAN_CURSO
                    RIGHT JOIN DAVID.ACAD_PLAN C ON B.ID_PLAN = C.ID_PLAN
                    LEFT JOIN DAVID.ACAD_CARGA_CURSO D ON D.ID_CARGA_CURSO = A.ID_CARGA_CURSO 
                    AND D.ID_SEMESTRE_PROGRAMA = ?
                    LEFT JOIN MAT_PLAN_COSTO E ON E.ID_PLAN = C.ID_PLAN
                    AND E.ID_CRITERIO  = ?
                    AND E.ID_SEMESTRE_PROGRAMA = ?
                    WHERE C.ID_AREA = ?
                    GROUP BY C.ID_PLAN,C.NOMBRE,E.ID_PLAN_COSTO,E.IMPORTE
                    ORDER BY C.ID_PLAN  DESC", [$params->id_semestre_programa, $params->id_criterio, $params->id_semestre_programa, $params->id_area]);
    }

    public static function addPlanCost($data)
    {
//        dd($data);
        $data = DB::table('MAT_PLAN_COSTO')->insert($data);
        return $data;
    }

    public static function updatePlanCost($data)
    {
        $resp = [];
        foreach ($data as $item) {
            $resp = DB::table('MAT_PLAN_COSTO')
                ->where('id_plan_costo', $item->id_plan_costo)
                ->update(array('importe' => $item->importe));
        }
        return $resp;
    }

    public static function massivelyRegisterPlanCosts($data)
    {
        $resp = [];
        $addList = [];
        $updateList = [];
//        dd($data);

        foreach ($data as $item) {
            if ($item->id_plan_costo) {
                array_push($updateList, $item);
            } else {
                array_push($addList, array(
                    'id_semestre_programa' => $item->id_semestre_programa,
                    'id_criterio' => $item->id_criterio,
                    'id_plan' => $item->id_plan,
                    'importe' => $item->importe,
                ));
            }
        }
//        dd($addList, $updateList);

        $data = DB::table('MAT_PLAN_COSTO')->insert($addList);


        foreach ($updateList as $item) {
            $resp = DB::table('MAT_PLAN_COSTO')
                ->where('id_plan_costo', $item->id_plan_costo)
                ->update(array('importe' => $item->importe));
        }
        return $resp;
    }

    /*
        protected static function criterios() {
            return DB::table('ELISEO.MAT_CRITERIO as a')
                ->select(
                    'a.id_criterio',
                    'a.nombre',
                    'a.codigo',
                    'a.id_parent',
                    'a.id_afecta',
                    'a.orden'
                );
        }

        public static function index($query){
            return self::planCosts($query);
        }*/


}