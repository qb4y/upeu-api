<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 19:42
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Person extends  Model
{
    protected $table='MOISES.PERSONA';
    protected $primaryKey='ID_PERSONA';

    protected $fillable = [
        "id_persona",
        "nombre",
        "paterno",
        "materno",
        "codigo",
    ];

    //const CREATED_AT = null;
    //const UPDATED_AT = null;
    public function getCompleteNameAttribute()
    {
        return $this->nombre;
    }

}