<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table='plla_puesto';
    protected $primaryKey='id_puesto'; 
    public $timestamps = false;
    
    protected $fillable = [
        "id_grupo_escala",
        "id_puesto",
        "nombre",
        "descripcion",
        "vigencia",
        "id_grupo_compentencia",
        "id_grupo_compentencia_org"
    ];
}

