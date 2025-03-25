<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Financier extends Model
{
    protected $table = 'FIN_FINANCISTA';
    protected $primaryKey = 'ID_FINANCISTA';
    protected $fillable = [
        'ID_ENTIDAD',
        'ID_DEPTO',
        'ESTADO',
        'CELULAR',
        'ID_NEXO',
    ];


    /*
     *
     *
    Route::resource('financier', 'FinancierController');
    Route::post('check-customer', 'ClientFinancierController@checkCustomer');
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
