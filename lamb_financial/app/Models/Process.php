<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:00 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $table = 'ELISEO.PROCESS';
    protected $primaryKey ='id_proceso';
    protected $fillable = ['id_proceso','id_entidad','id_depto','id_modulo','id_tipotransaccion','id_paso_inicio','id_paso_fin', 'nombre','codigo','estado'];
    public $timestamps = false;

}