<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:09 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessStepRun extends Model
{
    protected $table = 'ELISEO.PROCESS_PASO_RUN';
    protected $primaryKey ='id_detalle';
    protected $fillable = ['id_detalle','id_registro','id_paso','fecha','detalle','numero','revisado','ip','estado','id_paso_next','id_persona'];
    public $timestamps = false;

}