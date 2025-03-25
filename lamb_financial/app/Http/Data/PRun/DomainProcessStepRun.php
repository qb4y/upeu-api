<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:34 PM
 */

namespace App\Http\Data\PRun;


use App\Models\ProcessFlow;
use App\Models\ProcessRun;
use App\Models\ProcessStep;
use App\Models\ProcessStepRun;
use Illuminate\Support\Facades\DB;

class DomainProcessStepRun
{
    protected $id_registro;
    protected $process_run;
    protected $current_step;
    protected $next_step;
    protected $previous_step;


    public function getIdregistro()
    {
        return $this->id_registro;
    }
    public function setIdregistro($id_registro)
    {
        $this->id_registro = $id_registro;
    }
    public function getPreviousStep()
    {
        return $this->previous_step;
    }
    public function setPreviousStep($previous_step)
    {
        $this->previous_step = $previous_step;
    }

    public function getCurrentStep()
    {
        return $this->current_step;
    }
    public function setCurrentStep($current_step)
    {
        $this->current_step = $current_step;
    }
    public function getNextStep()
    {
        return $this->next_step;
    }
    public function setNextStep($next_step)
    {
        $this->next_step = $next_step;
    }

    public function retrievePreviousStep($llave)
    {
        $id_registro = $this->getIdregistro();
        $process_run = ProcessRun::find($id_registro);
        $id_proceso = $process_run->id_proceso;
        $id_paso_actual = $process_run->id_paso_actual;
        $flujo = ProcessFlow::join('eliseo.process_paso', 'eliseo.process_paso.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
            ->join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')
            ->leftJoin('eliseo.process_componente', 'eliseo.process_componente.id_componente', '=', 'eliseo.PROCESS_FLUJO.id_componente')
            ->where('eliseo.PROCESS_FLUJO.id_proceso', $id_proceso)
            ->where('eliseo.process_tipopaso.llave', 'tarea')
            ->orderBy('eliseo.process_paso.orden', 'asc')
            ->select('eliseo.process_paso.*', 'eliseo.process_flujo.*', 'eliseo.process_componente.*')
            ->get()->toArray();

        $i = 0;
        $id_paso = null;

        foreach ($flujo as $item) {
            if ($item['llave']==$llave && $i>=1) {
                $id_paso = $flujo[$i-1]['id_paso'];
            }
            $i++;
        }

        $process_run->id_paso_actual=$id_paso;

        $previous_step = ProcessStepRun::where(
            'id_paso',
            $id_paso
        )->where(
            'id_registro',
            $id_registro
        )->first();

        return $previous_step;
    }


    public function checkIsFinished($llave)
    {
        $id_registro = $this->getIdregistro();
        $process_run = ProcessRun::find($id_registro);
        $id_proceso = $process_run->id_proceso;
        $id_paso_actual = $process_run->id_paso_actual;

        $flujo = ProcessFlow::join('eliseo.process_paso', 'eliseo.process_paso.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
            ->join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')
            ->leftJoin('eliseo.process_componente', 'eliseo.process_componente.id_componente', '=', 'eliseo.PROCESS_FLUJO.id_componente')
            ->where('eliseo.PROCESS_FLUJO.id_proceso', $id_proceso)
            ->where('eliseo.process_componente.llave', $llave)
            //->where('eliseo.process_tipopaso.llave', 'tarea')
            ->whereIn('eliseo.process_tipopaso.llave', array('tarea','compuerta_exclusiva'))

            ->orderBy('eliseo.process_paso.orden', 'asc')
            ->select('eliseo.process_paso.*', 'eliseo.process_flujo.*', 'eliseo.process_componente.*')
            ->get()->toArray();
        //var_dump($flujo);
        if (count($flujo)>0) {
            if (ProcessStepRun::where(
                    'id_registro',
                    $id_registro
                )->where(
                    'id_paso',
                    $flujo[0]['id_paso']
                )->where(
                    'estado',
                    1
                )->count()>0) {
                return true;
            } else {
                return false;
            }
            //Queda pendiente buscar al process paso run del flujo, verificar si está completo y retornar True o False
            // id_detalle','id_registro','id_paso'
        }
    }

    public function querysetCurrentStep()
    {
        $id_registro = $this->getIdregistro();
        $processRun = ProcessRun::find($id_registro);
        $id_paso_actual= $processRun->id_paso_actual;
        //var_dump($processRun);

        $processStepRun = ProcessStepRun::where(
            'id_registro',
            $id_registro
        )->where(
            'id_paso',
            $id_paso_actual
        )->first();


        return $processStepRun;
    }

    public function querysetNextStep($id_paso_next, $id_proceso)
    {
        $processStep = ProcessStep::where(
            'id_proceso',
            $id_proceso
        )->where(
            'id_paso',
            $id_paso_next
        )->first();

        return $processStep;
    }

    public function delete($instance)
    {
        $instance->delete();
    }

    public function save($obj)
    {
        $results = DB::insert('insert into eliseo.process_paso_run (id_detalle, id_registro, 
                                    id_paso,id_persona,revisado,ip,estado,numero,id_paso_next,
                                    detalle,fecha) values (:id_detalle, :id_registro, :id_paso,
                                    :id_persona,:revisado,:ip,:estado,:numero,:id_paso_next,
                                    :detalle,:fecha)', $obj);

        return $results;
    }

    public function finish($id)
    {
        $results = ProcessStepRun::find($id);


        $results->estado = '1';

        $results->save();

        //var_dump($results);
    }

}