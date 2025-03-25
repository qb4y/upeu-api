<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 11:37 AM
 */

namespace App\Http\Data\PRun;


use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\ProcessStepRun;
use Illuminate\Support\Facades\DB;

class DProcess
{
    protected $flow;
    protected $contract;
    protected $process_run;
    protected $domainProcessRun;
    protected $id_registro;
    protected $id_persona;
    protected $id_proceso;
    protected $id_alumno_contrato;
    protected $id_operacion;


    public function getIdPersona()
    {
        return $this->id_persona;
    }
    public function setIdPersona($id_persona)
    {
        $this->id_persona = $id_persona;
    }
    public function getIdOperacion()
    {
        return $this->id_operacion;
    }
    public function setIdOperacion($id_operacion)
    {
        $this->id_operacion = $id_operacion;
    }
    public function getIdAlumnoContrato()
    {
        return $this->id_alumno_contrato;
    }
    public function setIdAlumnoContrato($id_alumno_contrato)
    {
        $this->id_alumno_contrato = $id_alumno_contrato;
    }
    public function getIdProceso()
    {
        return $this->id_proceso;
    }
    public function setIdProceso($id_proceso)
    {
        $this->id_proceso = $id_proceso;
    }
    public function getFlow()
    {
        return $this->flow;
    }
    public function setFlow($flow)
    {
        $this->flow = $flow;
    }

    public function getContract()
    {
        return $this->contract;
    }
    public function setContract($contract)
    {
        $this->contract = $contract;
    }
    public function getIdRegistro()
    {
        return $this->id_registro;
    }
    public function setIdRegistro($id_registro)
    {
        $this->id_registro = $id_registro;
    }
    public function getProcessRun()
    {
        return $this->process_run;
    }
    public function setProcessRun($process_run)
    {
        $this->process_run = $process_run;
    }

    public function getDomainProcessRun()
    {
        return $this->DomainProcessRun;
    }
    public function setDomainProcessRun($domainProcessRun)
    {
        $this->DomainProcessRun = $domainProcessRun;
    }
    public function __construct($obj)
    {
        $id_operacion = $obj['id_operacion'];
        $codigo = $obj['codigo'];
        $id_persona = $obj['id_persona'];

        $domainProcessRun = new DomainProcessRun();

        $this->setDomainProcessRun($domainProcessRun);

        $process = Process::where(
            'codigo',
            $codigo
        )->first();
        $processRun = ProcessRun::where(
            'id_proceso',
            $process->id_proceso
        )->where(
            'id_operacion',
            $id_operacion
        )->first();
        $this->setIdPersona($id_persona);

        $this->setIdProceso($process->id_proceso);

        $this->setIdOperacion($id_operacion);
        $this->setProcessRun($processRun);
    }

    public function retrieveFlowByProcessRun($id_registro)
    {
        $domainProcessRun = $this->getDomainProcessRun();
        $domainProcessRun->retrieveFlowByProcessRun($id_registro);

        $flow = $domainProcessRun->getFlow();
        $process_run= $domainProcessRun->getProcessRun();

        $this->setFlow($flow);
        $this->setProcessRun($process_run);
    }

    public function retrieveFlow($id_proceso)
    {
        $domainProcessRun = $this->getDomainProcessRun();
        $domainProcessRun->retrieveFlow($id_proceso);

        $flow = $domainProcessRun->getFlow();

        $this->setFlow($flow);
    }

    public function retrieveFlowExecutedByProcessRun($id_registro)
    {
        $domainProcessRun = $this->getDomainProcessRun();
        $domainProcessRun->retrieveFlowExecutedByProcessRun($id_registro);

        $flow = $domainProcessRun->getFlow();
        $process_run= $domainProcessRun->getProcessRun();

        $this->setFlow($flow);
        $this->setProcessRun($process_run);
    }
    public function retrieveFlowByProcessRunKey($id_registro, $codigo)
    {
        $domainProcessRun = $this->getDomainProcessRun();
        $domainProcessRun->retrieveFlowByProcessRunKey($id_registro, $codigo);

        $flow = $domainProcessRun->getFlow();
        $process_run= $domainProcessRun->getProcessRun();

        $this->setFlow($flow);
        $this->setProcessRun($process_run);
    }



    public function setCurrentStep()
    {
        $id_registro = $this->getIdRegistro();
        $process_run = $this->process_run;
        $id_proceso = $process_run->id_proceso;
        $id_paso_actual = $process_run->id_paso_actual;

        $flow = $this->getFlow();

        if ($id_paso_actual == null) {
            $id_paso_actual = $flow[0]['id_paso'];

            $process_run->id_paso_actual = $id_paso_actual;
            $process_run->save();
        } else {
            $processSteprun = ProcessStepRun::where(
                'id_registro',
                $id_registro
            )->where(
                'estado',
                1
            )->whereNotNull(
                'id_paso_next'
            )->orderBy('id_detalle', 'desc') ->get()->toArray();
            $count = count($processSteprun);
            if ($count > 0) {
                $id_paso_actual = $processSteprun[0]['id_paso_next'];
                $process_run->id_paso_actual = $id_paso_actual;
                $process_run->save();
            }
        }
    }



    public function setCurrent($id_registro, $id_paso_actual)
    {
        $domainProcessRun = $this->getDomainProcessRun();
        if ($id_paso_actual!=null) {
            $domainProcessRun->setCurrent($id_registro, $id_paso_actual);
        }
    }


    public function get_or_create_step($id_paso)
    {
        $id_registro = $this->getIdRegistro();
        //var_dump($id_paso);
        if (ProcessStepRun::where(
                'id_registro',
                $id_registro
            )->where(
                'id_paso',
                $id_paso
            )->count() > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function insertFirstStep()
    {
        $flow = $this->getFlow();

        $count = count($flow);

        $id_persona = $this->id_persona;

        $id_registro = $this->getIdRegistro();

        if ($count>1) {
            $obj = [
                "id_detalle"=>ProcessStepRun::max('id_detalle')+1,
                "id_registro"=>$id_registro ,
                "id_paso"=>$flow[0]['id_paso'],
                "id_persona"=>$id_persona,
                "revisado"=>0,
                "ip"=>\Request::ip(),
                "estado"=>0,
                "numero"=>1,
                "id_paso_next"=>$flow[1]['id_paso'],
                "detalle"=>$flow[0]['nombre'],
                "fecha"=> date('Y-m-d H:i:s')
            ];

            $results = DB::insert('insert into eliseo.process_paso_run (id_detalle, id_registro, 
                                    id_paso,id_persona,revisado,ip,estado,numero,id_paso_next,
                                    detalle,fecha) values (:id_detalle, :id_registro, :id_paso,
                                    :id_persona,:revisado,:ip,:estado,:numero,:id_paso_next,
                                    :detalle,:fecha)', $obj);
        }
    }


    public function get_or_create()
    {
        $id_registro = $this->getIdRegistro();
        if ($id_registro == null) {
            $check = ProcessRun::where('id_proceso', $this->getIdProceso())->where(
                'id_operacion',
                $this->getIdOperacion()
            )->get()->toArray();
            if (count($check)>0) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function create()
    {
        $id_registro = $this->getIdRegistro();
        $obj = array();
        $obj['id_registro']= ProcessRun::max('id_registro')+1;
        $obj['id_proceso']= $this->getIdProceso();
        $obj['id_operacion']= $this->getIdOperacion();
        $obj['fecha']= date('Y-m-d H:i:s');
        $obj['detalle']='';
        $obj['estado']=1;

        $domainProcessRun = $this->getDomainProcessRun();
        $process_run=$domainProcessRun->save($obj);
        $this->setProcessRun($process_run);
        $this->setIdRegistro($process_run->id_registro);
    }

    public function lookForIdRegistro()
    {
        $process_run= ProcessRun::where('id_proceso', $this->getIdProceso())->where(
            'id_operacion',
            $this->getIdOperacion()
        )->first();
        $this->setProcessRun($process_run);
        $this->setIdRegistro($process_run->id_registro);
    }

    public function finish()
    {
        $processRun = $this->getProcessRun();
        $processSteprun = ProcessStepRun::where(
            'id_paso',
            $processRun->id_paso_actual
        )->where(
            'id_registro',
            $processRun->id_registro
        )->first();

        $processSteprun->estado=1;
        $processSteprun->save();
        $processRun->estado =2;
        $processRun->save();
    }

    public function start()
    {
        $get_or_create= $this->get_or_create();
        if ($get_or_create==false) {
            $this->create();
        } else {
            $process_run= ProcessRun::where('id_proceso', $this->getIdProceso())->where(
                'id_operacion',
                $this->getIdOperacion()
            )->first();
            $this->setProcessRun($process_run);
            $this->setIdRegistro($process_run->id_registro);
        }

        //$id_persona = $this->getIdPersona();

        $id_registro = $this->getIdRegistro();

        $process_run = $this->getProcessRun();

        $proceso = $this->getIdProceso();

        $this->retrieveFlow($proceso);

        $this->setCurrentStep();

        $get_or_create_first_step = $this->get_or_create_step($process_run->id_paso_actual);
        //var_dump($get_or_create_first_step);
        $id_registro = $this->getIdRegistro();
        //var_dump($id_paso);
        if (ProcessStepRun::where(
                'id_registro',
                $id_registro
            )->count() == 0) {
            $this->insertFirstStep();
        }
        //if ($get_or_create_first_step==false) {

        //}
    }

}