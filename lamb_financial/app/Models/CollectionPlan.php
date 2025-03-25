<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 10:30 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CollectionPlan extends Model
{
    protected $table = 'FIN_PLANCOBRO_SEMESTRE';
    protected $primaryKey = 'ID_PLANCOBRO_SEMESTRE';
    protected $fillable = [
        'id_plancobro_semestre',
        'id_semestre',
        'estado',
        'id_depto',
        'id_entidad',
        'created_at',
        'updated_at',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semestre', 'id_semestre', 'id_semestre');
    }

    public function collectionPlanDetails()
    {
        return $this->belongsToMany(CollectionPlanIndicators::class, 'ELISEO.FIN_PLANCOBRO_SEMESTRE', 'id_plancobro_semestre', 'id_plancobro_semestre', 'id_plancobro_semestre');
    }

}
