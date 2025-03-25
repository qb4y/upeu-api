<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 11:26
 */

namespace App\Http\Data\Treasury\VoucherCash;


use App\Http\Controllers\Treasury\ExpensesController;
use App\Http\Controllers\Treasury\VoucherCash\VoucherCashFileController;

class VoucherCashFileData
{

    public static function getVouchersCashFile($params)
    {
        return VoucherCashFileController::paginate(20);
    }

    public static function getVoucherCashFile($idvoucherCashFile)
    {
        return VoucherCashFileController::find($idvoucherCashFile);
    }
    public static function AddVoucherCashFile($params)
    {
        return ExpensesController::privateUploadValeFile($params['id_vale'], $params['archivo_pdf'], $params['tipo']);
    }
}