<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePosition extends Model
{
    protected $table='plla_perfil_puesto';
    protected $primaryKey='id_perfil_puesto'; 
    public $timestamps = false;
    
    protected $fillable = [
        "id_perfil_puesto",
        "id_puesto",
        "id_area",
        "id_entidad",
        "id_depto",
        "id_perfil_puesto_jefe",
        "mision",
        "expinstsimilar",
        "exppuestosimilar",
        "condviajes",
        "condmovilizar",
        "id_autonomia_puesto",
        "id_ubicacionfadm",
        "id_tipo_control_personal",
        "noexperiencia",
        "nivel",
        "email",
        "id_situacion_educativo",
        "bono",
        "id_tipo_horario"
    ];
}

