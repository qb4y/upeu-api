<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table='plla_parametros';
    protected $primaryKey='id_parametro'; 
    public $timestamps = false;
    
    protected $fillable = [
        "id_parametro",
        "codigo",
        "nombre",
        "comentario",
        "formula",
        "importe",
        "vigencia",
        "orden"


    ];
    public function parametervalue()
    {
       return $this->hasMany('App\Models\Parametervalue', 'id_parametro');
    } 
}

