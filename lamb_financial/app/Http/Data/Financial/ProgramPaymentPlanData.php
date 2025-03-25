<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 13/01/20
 * Time: 09:21 AM
 */

namespace App\Http\Data\Financial;


use App\Models\Contract;
use App\Models\PaymentPlanSem;
use App\Models\PaymentPlanSemDetail;
use App\Models\ProgramSemester;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;
use Validator;

class ProgramPaymentPlanData
{


    protected static function getPaymentPlanDetail($id_planpago_semestre){
        return PaymentPlanSemDetail::all()
            ->where('id_planpago_semestre','=', $id_planpago_semestre)
            ->pluck('id_planpago_semestre_det')
            ->toArray();
    }


    protected static function getPaymentPlans($id_semestre_programa, $type){
        return DB::table('ELISEO.MAT_PLANPAGO_SEMESTRE')
            ->where('ID_SEMESTRE_PROGRAMA','=', $id_semestre_programa)
            ->get()
            ->pluck($type)
            ->toArray();
    }


    protected static function createOrUpdatePaymentPlan($data, $dataExists){

        foreach ($data['payment_plans'] as $item) {

        }

    }


    protected static function createOrUpdateDetails($data, $detailsExists){

        foreach ($data['details'] as $item) {

            $dataSet = [
                'id_planpago_semestre' => $data['id_planpago_semestre'],
                'fecha_inicio'=>$item['fecha_inicio'],
                'fecha_fin'=>$item['fecha_fin']
            ];

            if (in_array($item['id_planpago_semestre_det'], $detailsExists)) {
                PaymentPlanSemDetail::with([])->find($item['id_planpago_semestre_det'])
                    ->update($dataSet);
            } else {
                PaymentPlanSemDetail::insert($dataSet);
            }

        }
    }

    protected static function createPaymentPlans($obj, $id_semestre_programa){

        $data = [
            'id_planpago'=>$obj['id_planpago'],
            'id_semestre_programa'=>$id_semestre_programa
        ];




        $planpago_sem_id = DB::transaction(function() use($data) {
            PaymentPlanSem::insert($data);
            return DB::getSequence()->currentValue('SQ_MAT_PLANPAGO_SEM_ID');
        });



    }


    public static function paymentPlanConfig() {
        $data = DB::table('ELISEO.MAT_PLANPAGO a')
            ->join('ELISEO.MAT_PLANPAGO_SEMESTRE b', 'a.ID_PLANPAGO', '=', 'b.ID_PLANPAGO')
            ->where('a.estado','=',1)
            ->get();
        return collect($data)->map(function ($item) {
            $item->selected = false;
            $item->details = [];
            return $item;
        });
    }

    public static function optionsPaymentPlan() {
        $data = DB::table('ELISEO.MAT_PLANPAGO a')
            ->where('a.estado','=',1)
            ->get();
        return collect($data)->map(function ($item) {
            $item->selected = false;
            $item->details = [];
            return $item;
        });
    }




    public static function paymentPlan($id_semestre_programa, $valid_status) {
        $d = DB::table('eliseo.mat_planpago a');
            $d->join('eliseo.mat_planpago_semestre b', 'a.id_planpago', '=', 'b.id_planpago');
            $d->where('a.estado','=',1);
            if (empty($valid_status)) {
                $d->where('b.estado','=', '1');
            }
            $d->where('b.id_semestre_programa', '=', $id_semestre_programa);
            $d->select('a.id_planpago', 'a.nombre', 'a.codigo', 'a.descripcion', 'a.cuotas', 'a.estado', 'a.cuota_cro', 'a.conmat1cuota',
            'b.id_planpago_semestre', 'b.id_semestre_programa', 'b.estado as estado_mpps', 'b.valida_ciclo');
        $data = $d->get();
        return collect($data)->map(function ($item) {
            $item->selected = false;
            $item->details = [];
            $item->ciclos = [];
            if ($item->valida_ciclo == 'S') {
                $item->ciclos = self::ciclosPlanPago($item->id_planpago_semestre);
                $item->valida_ciclo = true;
            } else{ 
                $item->valida_ciclo = false;
            }
            return $item;
        });
    }
    protected static function ciclosPlanPago($id_planpago_semestre) {
        $ciclos = DB::table('mat_planpago_semestre_ciclo')
            ->where('id_planpago_semestre', '=', $id_planpago_semestre)
            ->select('ciclo')
            ->get();
        return $ciclos;
    }

    protected static function updateDetails($data, array $items) {

        $data->each(function($model) use ($items) {
            foreach ($items as $item) {
                if ($model->id_planpago_semestre_det == $item['id_planpago_semestre_det']) {
                    $model->update($item);
                    return;
                }
            }

            return $model->delete();
        });

    }





    protected static function getPaymentPlanPrograma($id_semestre_programa, $id_planpago) {
        return PaymentPlanSem::where('id_semestre_programa','=',$id_semestre_programa)
            ->where('id_planpago','=',$id_planpago)
            ->first();
    }



    protected static function getPaymentPlanSemester($id_semestre_programa) {
        return PaymentPlanSem::where('id_semestre_programa','=',$id_semestre_programa)
            ->get();
    }


    protected static function detachDetails($id_semestre_programa) {
        $data = PaymentPlanSem::where('id_semestre_programa','=',$id_semestre_programa)->get();
        foreach ($data as $instance) {
            $instance->details()->delete();
        }
    }


    /**
     * name="validators",
     * description="Validacion de campos requeridos para guardar",
     */
    public static function validators($data) {

        $messages = [
            'payment_plans.required' => 'Debe seleccionar un plan de pago.',
            'id_semestre_programa.required' => 'Debe elegir un programa.',
        ];

        return Validator::make($data, [
            'ids_semestre_programa' => 'required',
            'payment_plans' => 'required',
        ], $messages);

    }


    /**
     * name="create",
     * description="Crea planes de pago por programa y sus detalles de fecha de forma dinamica y multiple",
     */
    public static function create($data){

        foreach ($data['ids_semestre_programa'] as $id_semestre_programa) {

            self::detachDetails($id_semestre_programa); // elimino dependencias elimina fechas

            $payment_plans = collect($data->payment_plans)->pluck('id_planpago')->all();


            $instanceRoot = ProgramSemester::find($id_semestre_programa);
            $instanceRoot->paymentPlans()->sync($payment_plans); // sincronizo los planes de un programa

            $semestersExists = self::getPaymentPlanSemester($id_semestre_programa); // obtengo los nuevos planes programa si es que se crearon

            foreach ($data->payment_plans as $obj) {

                $dataDetails = array();
                $semester = $semestersExists->where('id_planpago','=',$obj['id_planpago'])->first(); // filtro el plan programa por plan de pago

                foreach ($obj['details'] as $detail) {
                    array_push($dataDetails, new PaymentPlanSemDetail(
                        [
                            'id_planpago_semestre'=>$semester['id_planpago_semestre'],
                            'fecha_inicio'=>$detail['fecha_inicio'],
                            'fecha_fin'=>$detail['fecha_fin'],
                            'orden'=>$detail['orden'],
                            'ciclo'=>isset($detail['ciclo']) ? $detail['ciclo'] : null,
                        ]
                    ));

                }

                $semester->details()->saveMany($dataDetails); // Inserta nuevamente las fechas


            }

        }

        return $data->all();
    }



    /**
     * name="show",
     * description="Obtiene planes de pago por programa y sus detalles de fecha.",
     */
    public static function show($id) {
        return PaymentPlanSem::with(['planPago','details'])
            ->where('id_semestre_programa','=',$id)
            ->get();
    }



    /**
     * name="show",
     * description="Obtiene planes de pago por programa",
     */
    public static function index($params) {
        return ProgramSemester::with([
            'program:id_programa_estudio,id_sede,sede,id_nivel_ensenanza,nombre_facultad,nombre_escuela,modalidad_estudio',
            'paymentPlans',
            ])
            ->select('id_semestre','id_semestre_programa','id_programa_estudio', 'plan_pago_ciclo')
            ->where('id_semestre','=',$params->id_semestre)
            ->whereHas('program', function ($query) use ($params){
                $query->where('id_sede', $params->id_sede)
                    ->where('id_nivel_ensenanza', $params->id_nivel_ensenanza);

                if ($params->nombre_programa) {
                    $query = $query->whereRaw("(UPPER(DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_ESCUELA) LIKE REPLACE(UPPER('%{$params->nombre_programa}%'),' ', '')
                    OR UPPER(DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_FACULTAD) LIKE REPLACE(UPPER('%{$params->nombre_programa}%'),' ', ''))");
                }

            })
            ->has('paymentPlans')
            ->paginate(20);

    }



    /**
     * name="detailsPaymentPlan",
     * description="Obtiene detalle de fecha de pago de plan de pago",
     */
    public static function detailsPaymentPlan($id_planpago_semestre) {
        return PaymentPlanSemDetail::where('id_planpago_semestre','=', $id_planpago_semestre)->orderBy('orden', 'asc') ->get();
    }



    public static function destroy($id) {

        self::detachDetails($id);
        $instance = PaymentPlanSem::where('id_semestre_programa','=',$id);
        $instance->delete();
    }



    public static function getIdProgramsExists(){
        return PaymentPlanSem::get(['id_semestre_programa'])->pluck('id_semestre_programa')->toArray();
    }
    public static function getIdProgramsSemesterCriteriaExists($params){
        return collect(Db::select("select distinct MAT_CRITERIO_SEMESTRE.ID_SEMESTRE_PROGRAMA
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
    }


    protected static function paymentPlanConfigNotIn($exists) {
        $data = DB::table('ELISEO.MAT_PLANPAGO a')
            ->where('a.estado','=',1)
            ->whereNotIn('a.id_planpago', $exists)
            ->get();
        return collect($data)->map(function ($item) {
            $item->selected = false;
            $item->details = [];
            return $item;
        });
    }


    public static function getPaymentPlanDetails($request){

        $id_semestre_programa = $request->id_semestre_programa;
        $id_alumno_contrato = $request->id_alumno_contrato;
        $valid_status = $request->valid_status;

        $contract = null;
        if($id_alumno_contrato) {
            $contract = Contract::findOrFail($id_alumno_contrato);
        }

        $data = ProgramSemester::with(['paymentPlans'])
            ->where('id_semestre_programa','=',$id_semestre_programa)
            ->first();

        $exist_payment_plan = $data->paymentPlans->pluck('id_planpago')->toArray();
        $sucesion = array(0 => '', 1 => 'ra', 2 => 'da', 3 => 'ra', 4 => 'ta', 5 => 'ta', 6 => 'ta', 7 => 'ma', 8 => 'va', 9 => 'na', 10 => 'ma');
        $response = self::paymentPlan($id_semestre_programa, $valid_status);
        foreach ($response as $obj) {
            if (in_array($obj->id_planpago, $exist_payment_plan)) {
                $rz = PaymentPlanSem::with('details');
                $rz->where('id_planpago','=', $obj->id_planpago);
                $rz->where('id_semestre_programa','=',$id_semestre_programa);
                if (empty($valid_status)) {
                    $rz->where('estado', '=', '1');
                }
                $details = $rz->first();
                $detalle = PaymentPlanSemDetail::where('id_planpago_semestre', $details->id_planpago_semestre);
                //-- acadsemestre programa -> plan_pago_ciclo ->S/N :  == N como esta; === S recuperar el ciclo del contrato
                if($contract and $data and $data->plan_pago_ciclo and $data->plan_pago_ciclo == 'S') {
                    $detalle = $detalle
                        ->where('ciclo', $contract->ciclo);
                }

                // setup detail plains -> conmat1cuot == 'S' ? orden + 1 : orden
                $cant = $detalle->count();
                $obj->prog_plan_pago_ciclo = $data->plan_pago_ciclo;
                $obj->details = $detalle->get()->map(function($item) use ($details, $sucesion, $cant) {
                    // $item->orden = $details->planPago->conmat1cuota == 'S' ? (intval($item->orden,10) + 1) : $item->orden;
                    $nsucc = $cant > 0 ? array_merge($sucesion, array_fill(0, $cant, 'va')) : $sucesion;
                    $item->orden_nom = isset($nsucc[$item->orden]) ? $nsucc[$item->orden] : '??';
                    return $item;
                });
                $obj->selected = true;
            }

        }

        return $response;
    }
    
    public static function updateStatusMatPlanPagoSemestre($request, $id_planpago_semestre)
    {
        $estado = $request->estado;
        $updates = DB::table('mat_planpago_semestre')
        ->where('id_planpago_semestre', '=', $id_planpago_semestre)
        ->update([
            'estado' => $estado,
        ]);
        if ($updates) {
            $response = [
                'success' => true,
                'message' => 'Actualizado',
                'data' => $updates
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No actulizado',
                'data' => $updates
            ];
        }
        return $response;
    }

}