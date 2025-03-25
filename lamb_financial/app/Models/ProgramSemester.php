<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 14/01/20
 * Time: 07:46 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ProgramSemester extends Model
{

    protected $table='DAVID.ACAD_SEMESTRE_PROGRAMA';
    protected $primaryKey='id_semestre_programa';

    protected $fillable = [
        "id_semestre_programa",
        "id_semestre",
        "id_programa_estudio",
        "plan_pago_ciclo",
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;


    public function paymentPlans() {
        return $this->belongsToMany(PaymentPlan::class, 'ELISEO.MAT_PLANPAGO_SEMESTRE', 'id_semestre_programa', 'id_planpago')
            ->withPivot('id_semestre_programa', 'id_planpago','id_planpago_semestre', 'estado');


         //$q = $this->belongsToMany(PaymentPlan::class, 'ELISEO.MAT_PLANPAGO_SEMESTRE', 'id_semestre_programa', 'id_planpago')
         //    ->withPivot('id_semestre_programa', 'id_planpago','id_planpago_semestre')
         //    ->with(['details' => function($query)  use ($id) {
                //$query->whereColumn('created_at','>','ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA');
         //       $query->where('id_semestre_programa','=', $id);
                //$query->whereRaw("ELISEO.MAT_PLANPAGO_SEMESTRE.ID_SEMESTRE_PROGRAMA = ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA");

         //   }]);

        // return $q;

    }

    public function program() {
        return $this->belongsTo(StudyProgram::class, 'id_programa_estudio');
    }

    public function detailsCount() {
        return $this->morphMany(PaymentPlanSem::class, 'details');
    }




}