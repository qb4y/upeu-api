<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:26 PM
 */

namespace App\Http\Data\PRun;


use App\Models\ProcessStepRun;

class DProcessStepRun
{

    protected $flow;
    protected $contract;
    protected $process_run;
    protected $domainProcessRun;
    protected $id_registro;
    protected $id_persona;
    protected $id_proceso;
    protected $id_alumno_contrato;
    protected $process_step_run;
    protected $domainProcessStepRun;


    public function getProcessRun()
    {
        return $this->process_run;
    }
    public function setProcessRun($process_run)
    {
        $this->process_run = $process_run;
    }
    public function getProcessStepRun()
    {
        return $this->process_step_run;
    }
    public function setProcessStepRun($process_step_run)
    {
        $this->process_step_run = $process_step_run;
    }

    public function getDomainProcessRun()
    {
        return $this->domainProcessRun;
    }
    public function setDomainProcessRun($domainProcessRun)
    {
        $this->domainProcessRun = $domainProcessRun;
    }
    public function getDomainProcessStepRun()
    {
        return $this->domainProcessStepRun;
    }
    public function setDomainProcessStepRun($domainProcessStepRun)
    {
        $this->domainProcessStepRun = $domainProcessStepRun;
    }


    public function __construct()
    {
        $domainProcessStepRun = new DomainProcessStepRun();

        $this->setDomainProcessStepRun($domainProcessStepRun);
    }


    public function finishCurrentStep()
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $current_step = $domainProcessStepRun->getCurrentStep();
        if ($current_step == null) {
            $current_step  = $this->getProcessStepRun();
        }
        $domainProcessStepRun->finish($current_step->id_detalle);
    }


    public function getCurrentStep($id_registro)
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $domainProcessStepRun->setIdregistro($id_registro);

        $queryset = $domainProcessStepRun->querysetCurrentStep();
        $domainProcessStepRun->setCurrentStep($queryset);

        return $queryset;
    }

    public function getPreviousStep($id_registro, $llave)
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $domainProcessStepRun->setIdregistro($id_registro);

        $queryset = $domainProcessStepRun->retrievePreviousStep($llave);

        $domainProcessStepRun->setPreviousStep($queryset);

        return $queryset;
    }



    public function checkIsFinished($llave)
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        return $domainProcessStepRun->checkIsFinished($llave);
    }

    public function nextStep($id_paso_next, $id_proceso)
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $queryset = $domainProcessStepRun->querysetNextStep($id_paso_next, $id_proceso);

        $domainProcessStepRun->setCurrentStep($queryset);
    }

    public function buildNextStep($obj)
    {
        $flow = $obj['flow'];

        $next_step = $obj['next_step'];

        $id_registro = $obj['id_registro'];

        $id_persona = $obj['id_persona'];
        $count = count($flow);

        $i = 0;

        $obj = [];

        foreach ($flow as $item) {
            $id_paso = $item['id_paso'];
            if ($id_paso == $next_step && $i< $count) {
                if (isset($flow[$i+1])) {
                    $id_paso_next=$flow[$i+1]['id_paso'];
                } else {
                    $id_paso_next=null;
                }


                $obj = [
                    "id_detalle"=>ProcessStepRun::max('id_detalle')+1,
                    "id_registro"=>$id_registro,
                    "id_paso"=>$next_step,
                    "id_persona"=>$id_persona,
                    "revisado"=>0,
                    "ip"=>\Request::ip(),
                    "estado"=>0,
                    "numero"=>$i+1,
                    "id_paso_next"=>$id_paso_next,
                    "detalle"=>$item['nombre'],
                    "fecha"=> date('Y-m-d H:i:s')
                ];
            }
            $i=$i+1;
        }

        $domainProcessStepRun  = $this->getDomainProcessStepRun();

        $domainProcessStepRun->setNextStep($obj);
    }
    public function saveStep()
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();
        $obj = $domainProcessStepRun->getNextStep();
        //$obj['estado']= 1;

        if ($obj!=null && $obj!=array()) {
            $domainProcessStepRun->save($obj);
        }
    }
    public function saveNextStep()
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $obj = $domainProcessStepRun->getNextStep();
        if ($obj!=null && $obj!=array()) {
            $domainProcessStepRun->save($obj);
        }
    }

    public function delete($instance)
    {
        $domainProcessStepRun = $this->getDomainProcessStepRun();

        $domainProcessStepRun->delete($instance);
    }

}