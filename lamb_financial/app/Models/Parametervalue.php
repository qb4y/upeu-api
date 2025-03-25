<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametervalue extends Model
{
    protected $table='plla_parametros_valor';
    protected $primaryKey='id_parametro_valor'; 
    public $timestamps = false;
    
    protected $fillable = [
        "id_parametro_valor",
        "id_entidad",
        "id_parametro",
        "id_anho",
        "eje_formula",
        "importe"
    ];
    public function parameter()
    {
        return $this->belongTo('App\Models\Parameter', 'id_parametro');
    }
}
