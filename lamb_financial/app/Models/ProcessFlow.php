<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 11:59 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessFlow extends Model
{
    protected $table = 'ELISEO.PROCESS_FLUJO';
    protected $primaryKey ='id_flujo';
    protected $fillable = ['id_flujo','id_proceso','id_paso','id_paso_next','tag','id_componente'];
    public $timestamps = false;

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function step()
    {
        return $this->belongsTo(ProcessStep::class, 'id_paso', 'id_paso');
    }

    public function scopeStep($query)
    {
        return $query->join('eliseo.process_paso', 'eliseo.process_flujo.id_paso', '=', 'eliseo.process_paso.id_paso');
    }

}