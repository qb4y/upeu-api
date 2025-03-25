<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21/01/20
 * Time: 11:33 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriterionSemesterSeat extends Model
{
    protected $table='ELISEO.MAT_CRITERIO_SEMESTRE_ASIENTO';
    protected $primaryKey='id_criterio_semestre_asiento';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [
        "id_criterio_semestre_asiento",
        "id_tipoplan",
        "id_cuentaaasi",
        "id_restriccion",
        "id_criterio_semestre",
        "id_entidad",
        "id_depto",
        "id_ctacte",
        "porcentaje",
        "fecha_inicio",
        "fecha_fin",
        "es_eap",
        "tipo_dc"
    ];

}