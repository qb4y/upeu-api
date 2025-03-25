<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scalegroup extends Model
{
    protected $table='plla_grupo_escala';
    protected $primaryKey='id_grupo_escala'; 
    public $timestamps = false;
    
    protected $fillable = [
        "id_grupo_escala",
        "id_nivel",
        "id_grado",
        "id_subgrado",
        "nombre",
        "descripcion",
        "vigencia"
    ];
}

