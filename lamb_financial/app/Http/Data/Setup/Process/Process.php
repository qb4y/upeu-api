<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/24/19
 * Time: 4:59 PM
 */

namespace App\Http\Data\Setup\Process;

use Illuminate\Support\Facades\DB;

class Process
{
    public static function getStepsProcessModule($Entidad, $Codigo, $TypeStepProcess)
    {
        $query = DB::table("PROCESS_PASO")
            ->select(
                "PROCESS_COMPONENTE_PASO.ID_PASO",
                "PROCESS_COMPONENTE.NOMBRE",
                "PROCESS_COMPONENTE.LLAVE"
            )
            ->join("PROCESS", "PROCESS_PASO.ID_PROCESO", "=", "PROCESS.ID_PROCESO")
            ->join("PROCESS_TIPOPASO", "PROCESS_PASO.ID_TIPOPASO", "=", "PROCESS_TIPOPASO.ID_TIPOPASO")
            ->join("PROCESS_COMPONENTE_PASO", "PROCESS_PASO.ID_PASO", "=", "PROCESS_COMPONENTE_PASO.ID_PASO")
            ->join("PROCESS_COMPONENTE", "PROCESS_COMPONENTE_PASO.ID_COMPONENTE", "=", "PROCESS_COMPONENTE.ID_COMPONENTE")
            ->where("PROCESS.ID_ENTIDAD", $Entidad)
            ->where("PROCESS.CODIGO", $Codigo);
        if ($TypeStepProcess) {
            $query->where("PROCESS_TIPOPASO.LLAVE", $TypeStepProcess);
        }
        $query = $query->get();
        return $query;
    }

    public static function updateProcessRun($id_registro, $data)
    {
        $query = DB::table('process_run')
            ->where('id_registro', $id_registro)
            ->update($data);
        return $query;
    }

    public static function getProcessRunByIdOperation($id_operation, $code_process)
    {
        $query = DB::table('PROCESS_RUN')
            ->join('PROCESS', 'PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
            ->where('PROCESS_RUN.ID_OPERACION', $id_operation)
            ->where('PROCESS.CODIGO', $code_process)->first();
        return $query;
    }


}