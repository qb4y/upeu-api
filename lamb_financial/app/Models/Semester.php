<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table='DAVID.ACAD_SEMESTRE';
    protected $primaryKey='ID_SEMESTRE';


    protected $fillable = [
        "ID_SEMESTRE",
        "NOMBRE",
        "CODIGO"
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

}