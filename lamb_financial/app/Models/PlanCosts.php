<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PlanCosts extends Model
{
    protected $table = 'ELISEO.MAT_PLAN_COSTO';
    protected $primaryKey = 'ID_PLAN_COSTO';


    protected $fillable = [
        "ID_PLAN_COSTO",
        "ID_SEMESTRE_PROGRAMA",
        "ID_CRITERIO_SEMESTRE",
        "ID_PLAN",
        "IMPORTE",
    ];

    public function criteria()
    {
        return $this->belongsTo('App\ORM\MatCriterioSemestre', 'id_criterio');
    }

    public function semester()
    {
        return $this->belongsTo('App\ORM\MatCriterioSemestre', 'id_criterio');
    }
}