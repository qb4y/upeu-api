<?php
namespace App\Http\Data\PRun;

use App\Models\ProcessFlow;
use App\Models\ProcessRun;
use App\Models\ProcessStep;

/**
 * @author    Samuel Roncal
 * @copyright 2019
 * UPeU
 */
class DomainProcessRun
{
    protected $flow;
    protected $process_run;

    public function getFlow()
    {
        return $this->flow;
    }
    public function setFlow($flow)
    {
        $this->flow = $flow;
    }

    public function getProcessRun()
    {
        return $this->process_run;
    }
    public function setProcessRun($process_run)
    {
        $this->process_run = $process_run;
    }
    public function save($obj)
    {
        $var = ProcessRun::create(
            $obj
        );
        return $var;
    }
    public function retrieveFlowExecutedByProcessRun($id_registro)
    {
        global $new_id_registro;
        $new_id_registro = $id_registro;
        $process_run = ProcessRun::find($id_registro);
        $id_proceso = $process_run->id_proceso;
        $id_paso_actual = $process_run->id_paso_actual;
        $flujo = ProcessFlow::join('eliseo.process_paso', 'eliseo.process_paso.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
        ->join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')
        ->leftJoin('eliseo.process_paso_run', function ($join) {
            global $new_id_registro;
            $join->on('eliseo.process_paso_run.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')->where('eliseo.process_paso_run.id_registro', $new_id_registro);
        })
        //->leftJoin('eliseo.process_paso_run', 'eliseo.process_paso_run.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
        ->where('eliseo.PROCESS_FLUJO.id_proceso', $id_proceso)
        ->where('eliseo.process_tipopaso.llave', 'tarea')
        ->orderBy('eliseo.process_paso.orden', 'asc')
        ->select(
            'eliseo.process_paso.id_paso',
            'eliseo.process_paso.icono',
            'eliseo.process_paso.nombre',
            'eliseo.process_paso_run.estado',
            'eliseo.process_paso_run.fecha as fecha_ejecucion'
        )
        ->get()->toArray();
        foreach ($flujo as &$item) {
            if ($id_paso_actual == $item['id_paso']) {
                $item['estado']="0";
            }
            //if (is_null($item['estado'])) {
                
            //}
        }
        $this->setProcessRun($process_run);
        $this->setFlow($flujo);
    }
    public function retrieveFlowByProcessRun($id_registro)
    {
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

        $this->setProcessRun($process_run);
        $this->setFlow($flujo);
    }

    public function retrieveFlowByProcessRunKey($id_registro, $codigo)
    {
        global $new_id_registro;
        $new_id_registro = $id_registro;
        $flujo=ProcessStep::join(
            'eliseo.process_tipopaso',
            'eliseo.process_tipopaso.id_tipopaso',
            '=',
            'eliseo.PROCESS_paso.id_tipopaso'
        )
            ->join('eliseo.process', 'eliseo.process.id_proceso', '=', 'eliseo.PROCESS_paso.id_proceso')
            //->leftJoin('eliseo.process_run','eliseo.process_run.id_proceso','=','eliseo.process_paso.id_proceso')
            ->leftJoin('eliseo.process_run', function ($join) {
                global $new_id_registro;
                $join->on('eliseo.process_run.id_proceso', '=', 'eliseo.process_paso.id_proceso')->where('eliseo.process_run.id_registro', $new_id_registro);
            })
            ->leftJoin('eliseo.process_paso_run', function ($join) {
                global $new_id_registro;
                $join->on('eliseo.process_paso_run.id_registro', '=', 'eliseo.process_run.id_registro')
                ->on('eliseo.process_paso_run.id_paso', '=', 'eliseo.process_paso.id_paso')
                ->where('eliseo.process_paso_run.id_registro', $new_id_registro);
            })
        
            ->where('codigo', $codigo)
            ->whereIn(
                'eliseo.process_tipopaso.llave',
                array('tarea','compuerta_exclusiva')
            )->select(
                'eliseo.process_paso.id_paso',
                'eliseo.process_paso.icono',
                'eliseo.process_paso.nombre',
                'eliseo.process_paso_run.id_detalle',
                'eliseo.process_paso_run.estado',
                'eliseo.process_paso_run.fecha as fecha_ejecucion',
                'eliseo.process_run.id_registro as id_registro'
            )        ->orderBy('eliseo.process_paso.orden', 'asc')
                       ->get()->toArray();

        $process_run = ProcessRun::find($id_registro);
        $id_proceso = $process_run->id_proceso;
        $id_paso_actual = $process_run->id_paso_actual;
        /*

        $flujo = ProcessFlow::join('eliseo.process_paso', 'eliseo.process_paso.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
        ->join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')
        ->leftJoin('eliseo.process_componente', 'eliseo.process_componente.id_componente', '=', 'eliseo.PROCESS_FLUJO.id_componente')
        ->where('eliseo.PROCESS_FLUJO.id_proceso', $id_proceso)
        ->whereIn('eliseo.process_tipopaso.llave', array('tarea','compuerta_exclusiva'))
        ->whereIn('eliseo.process_flujo.id_paso_next', $pasos)
        ->orderBy('eliseo.process_paso.orden', 'asc')
        ->select('eliseo.process_paso.*', 'eliseo.process_flujo.*', 'eliseo.process_componente.*')
        ->get()->toArray();*/

        $this->setProcessRun($process_run);
        $this->setFlow($flujo);
    }

    public function retrieveFlow($id_proceso)
    {
        $pasos=ProcessStep::join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')->where(
            'id_proceso',
            $id_proceso
        )->where(
            'eliseo.process_tipopaso.llave',
            '!=',
            'evento_fin_simple'
        )->select('eliseo.process_paso.*')->pluck('id_paso')->toArray();
        $flujo = ProcessFlow::join('eliseo.process_paso', 'eliseo.process_paso.id_paso', '=', 'eliseo.PROCESS_FLUJO.id_paso')
        ->join('eliseo.process_tipopaso', 'eliseo.process_tipopaso.id_tipopaso', '=', 'eliseo.PROCESS_paso.id_tipopaso')
        ->leftJoin('eliseo.process_componente', 'eliseo.process_componente.id_componente', '=', 'eliseo.PROCESS_FLUJO.id_componente')
      
        ->where('eliseo.PROCESS_FLUJO.id_proceso', $id_proceso)
        ->whereIn('eliseo.process_tipopaso.llave', array('tarea','compuerta_exclusiva'))
        //->whereIn('eliseo.process_flujo.id_paso_next', $pasos)
        //->whereIn('eliseo.process_flujo.id_paso', $pasos)
        ->whereNotNull('eliseo.process_componente.llave')
        ->orderBy('eliseo.process_paso.orden', 'asc')
        ->select('eliseo.process_paso.*', 'eliseo.process_flujo.*', 'eliseo.process_componente.*')
        ->get()->toArray();

        $this->setFlow($flujo);
    }

    public function setCurrent($id_registro, $id_paso_actual)
    {
        $results = ProcessRun::find($id_registro);

        $results->id_paso_actual = $id_paso_actual;

        $results->save();
    }
}
