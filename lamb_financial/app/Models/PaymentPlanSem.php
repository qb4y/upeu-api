<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 14/01/20
 * Time: 05:42 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PaymentPlanSem extends Model
{

    protected $table='ELISEO.MAT_PLANPAGO_SEMESTRE';
    protected $primaryKey='id_planpago_semestre';


    protected $fillable = [
        "id_planpago_semestre",
        "id_planpago",
        "id_semestre_programa",
        "estado"
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;


    public function planPago()
    {
        return $this->belongsTo(PaymentPlan::class, 'id_planpago');
    }

    public function details()
    {
        return $this->hasMany(PaymentPlanSemDetail::class, 'id_planpago_semestre')->orderBy('orden', 'asc');
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class, 'id_planpago');
    }

    public function programSemester() {
        return $this->belongsTo(ProgramSemester::class, 'id_semestre_programa')->join(
            'DAVID.VW_ACAD_PROGRAMA_ESTUDIO','DAVID.ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO','=','DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO')
            ->select(
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_FACULTAD',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.NOMBRE_ESCUELA',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.SEDE',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.MODALIDAD_ESTUDIO',
                'DAVID.VW_ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO',
                'DAVID.ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA');
    }



    public function detailsDates() {
        return $this->belongsToMany(PaymentPlanSemDetail::class, 'ELISEO.MAT_PLANPAGO_SEMESTRE_DET', 'id_planpago_semestre', 'id_planpago_semestre');
    }








}