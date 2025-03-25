<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:19 PM
 */

namespace App\Http\Data\PRun;

use App\Http\Data\Financial\ContractStudentGeneratePdfData;
use App\Models\Contract;
use App\Models\EnrollmentCourses;
use App\Models\Secuencial;
use App\Models\StudentCourses;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DProcessData
{

    public static function finishContract($id_alumno_contrato, $session) {

        $contract = Contract::find($id_alumno_contrato);
       

        $validacion=\DB::select(" SELECT count(1) AS contador  FROM david.ACAD_ALUMNO_CONTRATO aac 
        INNER JOIN david.ACAD_MATRICULA_DETALLE amd ON amd.ID_MATRICULA_DETALLE = aac.ID_MATRICULA_DETALLE
          WHERE ID_ALUMNO_CONTRATO =$id_alumno_contrato AND amd.ID_PROCESO= 29
        ")[0]->contador;

        if($validacion==1){

            $secuencial = Secuencial::find(1);
            $next = $secuencial->secuencia+1;
            $formato = $secuencial->formato;
            $codigo =  $formato.'-'.str_pad(strval($next), 4, '0', STR_PAD_LEFT);
            $secuencial->secuencia = $next;
            $secuencial->save();
            $contract->estado ='1';
            if ($contract->codigo == null) {
                $contract->codigo=$codigo;
            }
            $contract->save();

            $mycourses = EnrollmentCourses ::where('id_alumno_contrato', $id_alumno_contrato)->pluck(
                'id_curso_alumno'
            );

            $sc = StudentCourses::whereIn('id_curso_alumno',$mycourses)->update(array("estado"=>'1'));

            $contrato = Contract::find($id_alumno_contrato);
            $contrato->origen = 3;

            //add fecha_matricula
            date_default_timezone_set('America/Lima');
            $pre_fecha_matricula = Carbon::now();
            $contrato->fecha_matricula = $pre_fecha_matricula;//->format('Y/m/d H:m:s');
            $contrato->save();

        }else{ 
            $secuencial = Secuencial::find(1);
            $next = $secuencial->secuencia+1;
            $formato = $secuencial->formato;
            $codigo =  $formato.'-'.str_pad(strval($next), 4, '0', STR_PAD_LEFT);
            $secuencial->secuencia = $next;
            $secuencial->save();

            $contract->estado ='1';
            if ($contract->codigo == null) {
                $contract->codigo=$codigo;
            }
            $contract->save();

            $obj = array();
            $obj['id_operacion'] = $contract->id_alumno_contrato;
            $obj['id_persona'] = $contract->id_persona;
            $id_alumno_contrato = $contract->id_alumno_contrato;
            $id_persona = $contract->id_persona;


            $obj = array();
            $obj['id_operacion'] = $id_alumno_contrato;
            $obj['id_persona'] =  $id_persona;
            $obj['codigo'] =  78;
            $bootProcess = new DProcess($obj);
            $bootProcess->start();
            self::create_step('PAGO', $bootProcess);


            $obj = array();
            $obj['id_operacion'] = $id_alumno_contrato;
            $obj['id_persona'] =  $id_persona;
            $obj['codigo'] =  78;
            $bootProcess = new DProcess($obj);
            $bootProcess->finish();

            $mycourses = EnrollmentCourses ::where('id_alumno_contrato', $id_alumno_contrato)->pluck(
                'id_curso_alumno'
            );

            $sc = StudentCourses::whereIn('id_curso_alumno',$mycourses)->update(array("estado"=>'1'));

            $contrato = Contract::find($id_alumno_contrato);
            $contrato->origen = 3;

            date_default_timezone_set('America/Lima');
            $pre_fecha_matricula = Carbon::now();
            $contrato->fecha_matricula = $pre_fecha_matricula;//->format('Y/m/d H:m:s');

            $contrato->save();

        }

        $genrateContract = ContractStudentGeneratePdfData::generarArchivoContratoAlumno($id_alumno_contrato, $session);
        $msgerror = $genrateContract['message'];
        

    }


    public static function create_step($llave, $bootProcess)
    {
        $processRun = $bootProcess->getProcessRun();
        $id_persona = $bootProcess->getIdPersona();
        $bootProcess->retrieveFlow($bootProcess->getIdProceso());

        $processStepRun = new DProcessStepRun();

        $current_step = $processStepRun->getCurrentStep($processRun->id_registro);

        if ($current_step === null) {
            $bootProcess->retrieveFlow($bootProcess->getIdProceso());

            $flow =  $bootProcess->getFlow();

            $next_step = $processRun->id_paso_actual;

            $bootProcess->lookForIdRegistro();

            $id_registro = $bootProcess->getIdRegistro();

            $id_persona =  $bootProcess->getIdPersona();

            $obj['flow']=$flow;

            $obj['next_step'] = $next_step;

            $obj['id_registro'] = $id_registro;



            $obj['id_persona'] = $id_persona;

            $processStepRun-> buildNextStep($obj);

            $processStepRun-> saveStep();
        }
        $finished = $processStepRun-> checkIsFinished($llave);

        if ($finished === false) {
            $processStepRun-> finishCurrentStep();
            $obj = array();

            $flow = $bootProcess->getFlow();

            $next_step = $current_step->id_paso_next;

            $id_registro =  $processRun->id_registro;

            $obj['flow']=$flow;

            $obj['next_step'] = $next_step;

            $obj['id_registro'] = $id_registro;

            $obj['id_persona'] = $id_persona;

            $id_proceso = $processRun-> id_proceso;

            $processStepRun-> nextStep($next_step, $id_proceso);

            $processStepRun-> buildNextStep($obj);

            $processStepRun-> saveNextStep();

            $bootProcess->setCurrent($id_registro, $next_step);
        }
    }


    public static function finishVariation($id_alumno_contrato, $id_user, $session)
    {
        $contract = Contract::find($id_alumno_contrato);
        $id_persona = $id_user;

        $secuencial = Secuencial::find(1);

        $next = $secuencial->secuencia+1;
        $formato = $secuencial->formato;
        $codigo =  $formato.'-'.str_pad(strval($next), 4, '0', STR_PAD_LEFT);
        $secuencial->secuencia = $next;
        $secuencial->save();

        $contract->estado ='1';
        $contract->origen =3;
        $contract->id_usuario_act=$id_persona;

        if ($contract->codigo == null) {
            $contract->codigo=$codigo;
        }
        $contract->save();
        $mycourses=EnrollmentCourses::where('id_alumno_contrato', $id_alumno_contrato)->where('id_tipo_movimiento_var',1)->pluck(
            'id_curso_alumno'
        );
        $mycoursesDrop=EnrollmentCourses::where('id_alumno_contrato', $id_alumno_contrato)->where('id_tipo_movimiento_var',2)->pluck(
            'id_curso_alumno'
        );


        $sc = StudentCourses::whereIn('id_curso_alumno',$mycourses)->update(array("estado"=>'1'));
        $scu = StudentCourses::whereIn('id_curso_alumno',$mycoursesDrop)->update(array("estado"=>'4'));
        $condicion = StudentCourses::whereIn('id_curso_alumno',$mycoursesDrop)->update(array("id_tipo_condicion"=>'15'));

        $genrateContract = ContractStudentGeneratePdfData::generarArchivoContratoAlumno($id_alumno_contrato, $session);
        $msgerror = $genrateContract['message'];
    }

}