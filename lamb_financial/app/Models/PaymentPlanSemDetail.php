<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 14/01/20
 * Time: 06:17 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlanSemDetail extends Model
{

    protected $table='ELISEO.MAT_PLANPAGO_SEMESTRE_DET';
    protected $primaryKey='id_planpago_semestre_det';


    protected $fillable = [
        "id_planpago_semestre_det",
        "id_planpago_semestre",
        "fecha_inicio",
        "fecha_fin",
        'orden',
        "ciclo",
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

}