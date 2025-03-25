<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 08/01/20
 * Time: 05:03 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterion extends Model
{
    protected $table='ELISEO.MAT_CRITERIO';
    protected $primaryKey='ID_CRITERIO';


    protected $fillable = [
        "ID_CRITERIO",
        "ID_NIVEL_ENSENANZA",
        "CODIGO",
        "NOMBRE",
        "TIPO_COBRO",
        "ID_PARENT",
        "DC",
        "ESTADO",
        "ID_AFECTA",
        "TIPO_ASIGNACION",
        "ORDEN",
        "ID_CRITERIO_PROC",
        'ID_MODO_CONTRATO',
        'VER_HIJO'
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;


    public function teachingLevel()
    {
        return $this->belongsTo(TeachingLevel::class, 'id_nivel_ensenanza');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'id_parent');
    }

    public function affects()
    {
        return $this->belongsTo(self::class, 'id_afecta');
    }
    public function childrenCriterions()
    {
        return $this->hasMany(self::class, 'id_parent', 'id_criterio');
    }
    public function allChildrenCriterions()
    {
        return $this->childrenCriterions()->with('allChildrenCriterions');
    }
}