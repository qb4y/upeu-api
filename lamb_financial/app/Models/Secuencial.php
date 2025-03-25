<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 10:43 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Secuencial extends Model
{
    protected $table = 'DAVID.ACAD_SECUENCIAL_CONTRATO';
    protected $primaryKey = 'id_secuencial';
    protected $fillable = ['id_secuencial','id_semestre', 'formato','secuencia','estado'];


    public $timestamps = false;

}