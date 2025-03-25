<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 14:12
 */

namespace App\Http\Data\Treasury\VoucherCash;

use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\Process\ProcessData;
use App\Http\Data\Purchases\PurchasesData;
use App\Models\ProcessRun;
use App\Models\ProcessStepRun;
use App\Models\VoucherCash;
use mysql_xdevapi\Result;

class VoucherCashData
{
    public static function getVoucherCash($id)
    {
        $data = VoucherCash::with(['files', 'register', 'beneficiary', 'typeVoucherCash'])->find($id);
        $data->files = $data->files->map(function ($item, $key) {
            $item-> url = $item -> url;
            // $item->url = url('') . '/' . $item->url;
            return $item;
        });
        return $data;
    }

    public static function deleteVoucherCash($id)
    {
        
        $codeProcessVoucherManagmeny = '8';
        $voucherCash = VoucherCash::with('files')->find($id);
        // recover files path when type files is [1 : sustento]
        $pathFilesVoucherCash = $voucherCash->files->where('tipo', '1')->map(function ($files) {
            return $files->url;
        })->toArray();
        $storage = new StorageController(); 
        if(count($pathFilesVoucherCash)>0){
            $storage->destroyFile($pathFilesVoucherCash[0]);
        }
        
        $deletedP = false;
        $deletedC = false;
        $instanceProcess = ProcessData::showVoucherCashProcessPasoRun($voucherCash->id_vale, $voucherCash->id_entidad, $codeProcessVoucherManagmeny);
        if ($instanceProcess) {
            $processRun = ProcessRun::find($instanceProcess->id_registro);
            $deletedP = $processRun->delete(); //eliminando en cascasda processrun and processs pasos run
            $deletedC = $voucherCash->delete(); // elimnandi vale
            // eliminar archivo
            if ($deletedP and $deletedC) {
                foreach ($pathFilesVoucherCash as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

        }
        // return $deletedP and $deletedC if was deleted;
        return $deletedP and $deletedC;
    }


    public static function destroyVoucherCash($id)
    {
        $instance = VoucherCash::find($id);
        $instance->delete();
        return $instance;
    }

    public static function denyVoucherCash($params)
    {
        // code 8 for proces gestion vales
        $voucherCash = VoucherCash::find($params['id_vale']);
        $instanceProcess = ProcessData::showVoucherCashProcessPasoRun($voucherCash->id_vale, $voucherCash->id_entidad, '8');

        if ($instanceProcess) {

            $voucherCash->timestamps = false;
            $voucherCash->motivo = $params['motivo'];
            $voucherCash->save();
            $procRun = ProcessRun::find($instanceProcess->id_registro);
            $procRun->estado = 3;
            $procRun->save();
            $procStepRun = ProcessStepRun::find($instanceProcess->id_detalle);
            $procStepRun->id_paso_next = $instanceProcess->id_paso_fin ? $instanceProcess->id_paso_fin : $procStepRun->id_paso_next;
            $procStepRun->detalle = "Anulando: " . $params['motivo'];
            $procStepRun->save();
        }

        return $instanceProcess;
    }

    public static function updateMassVoucherCash($id, $params)
    {
        return VoucherCash::where('id_vale', $id)
            ->update($params);
    }

    // preserve params for update ....
    public static function updateVoucherCash($instance, $params)
    {
        $repst = VoucherCash::find($instance)
            ->update($params);
        return $repst;
    }


}