<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 8/23/21
 * Time: 1:02 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPerson extends Model
{
    protected $table = 'MOISES.PERSONA_JURIDICA';
    protected $primaryKey = 'ID_RUC';

    protected $fillable = [
        "id_persona",
        "id_tipoestado",
        "id_tipocondicion",
        "id_tipocontribuyente",
        "id_tipoactividadeconomica",
        "id_tipopais",
        "fec_registro",
        "fec_inicio",
        "fec_baja",
        "es_rus",
        "es_buen_contribuyente",
        "es_agente_retencion"
    ];
// put de la compra para actualizar
    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function getEsAgenteRetencionAttribute($value) {
        return $value == 'S';
    }
    public function getEsBuenContribuyenteAttribute($value) {
        return $value == 'S';
    }
    public function getEsRusAttribute($value) {
        return $value == 'S';
    }
    /*public function getCompleteNameAttribute()
    {
        return $this->naturalPerson()->nombre;
    }

    public function naturalPerson()
    {
        return $this->belongsTo(Person::class, 'id_persona');
    }*/
}