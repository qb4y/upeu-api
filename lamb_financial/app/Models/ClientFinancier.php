<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 10:30 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ClientFinancier extends Model
{
    protected $table = 'FIN_ASIGNACION';
    protected $primaryKey = 'ID_ASIGNACION';
    protected $fillable = [
        'ID_ASIGNACION',
        'id_semestre',
        'estado',
    ];


    /*
        public function semester()
        {
            return $this->belongsTo(Semester::class, 'id_semestre');
        }

        public function collectionPlanDetails()
        {
            return $this->belongsToMany(CollectionPlanIndicators::class, 'FIN_PLANCOBRO_SEMESTRE_DET', 'id_plancobro_semestre', 'id_plancobro_semestre');
        }

            public function detailsDates() {
                return $this->belongsToMany(PaymentPlanSemDetail::class, 'ELISEO.MAT_PLANPAGO_SEMESTRE_DET', 'id_planpago_semestre', 'id_planpago_semestre');
            }
            public function semesters() {
                return $this->belongsToMany(Semester::class, 'DAVID.ACAD_SEMESTRE_PROGRAMA', 'id_programa_estudio', 'id_semestre');
            }
            */


}
