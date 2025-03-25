<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $table='ELISEO.MAT_PROCEDURE';
    protected $primaryKey='ID_PROCEDURE';


    protected $fillable = [
        "ID_PROCEDURE",
        "NOMBRE",
        "DESCRIPCION",
        "ESTADO"
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;
}