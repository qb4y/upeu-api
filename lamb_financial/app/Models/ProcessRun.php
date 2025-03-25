<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 11:58 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessRun extends Model
{
    protected $table = 'ELISEO.PROCESS_RUN';
    protected $primaryKey ='id_registro';
    protected $fillable = ['id_registro','id_proceso','id_operacion','fecha','detalle','estado','id_paso_actual'];
    public $timestamps = false;

    public function processSteps()
    {
        return $this->hasMany(ProcessStepRun::class, 'id_registro', 'id_registro');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($processRun) { // before delete() method call this
            $processRun->processSteps()->delete();
            // do the rest of the cleanup...
        });
    }
}