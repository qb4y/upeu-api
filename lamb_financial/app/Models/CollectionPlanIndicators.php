<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 10:30 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CollectionPlanIndicators extends Model
{
    protected $table = 'FIN_PLANCOBRO_SEMESTRE_DET';
    protected $primaryKey = 'ID_PLANCOBRO_SEMESTRE_DET';
    protected $fillable = [
        'id_plancobro_semestre',
        'nombre',
        'min_val',
        'max_val',
        'num_orden',
        'color',
    ];
    protected $casts = [
        'min_val' => 'integer',
        'max_val' => 'integer',
    ];

}
