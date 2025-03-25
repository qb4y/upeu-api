<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 11:58 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessPasoRun extends Model
{
    protected $table = 'ELISEO.PROCESS_PASO_RUN';
    protected $primaryKey ='id_detalle';
    protected $fillable = [
        'id_detalle','id_registro','id_paso',
        'id_persona','fecha','detalle','numero',
        'revisado','ip','estado','id_paso_next',
    ];
    public $timestamps = false;

}