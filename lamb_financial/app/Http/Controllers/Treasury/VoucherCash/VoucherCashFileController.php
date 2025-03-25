<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 11:25
 */

namespace App\Http\Controllers\Treasury\VoucherCash;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Treasury\VoucherCash\VoucherCashFileData;
use App\Http\Requests\VoucherCashFileRequest;
use App\Models\ClientFinancier;
use Illuminate\Http\Request;

class VoucherCashFileController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        $response = array();
        //$idDepto = $global['id_depto'];
        $idEntidad = $global['id_entidad'];
        /*$idDepto = $this->request->query('id_depto');
        $idSemeter = $this->request->query('id_semestre');
        $idFinancista = $this->request->query('id_financista');
        $pageSize = $this->request->query('page_size');
        $this->request->query('id_financista');*/
        if ($valid) {
            $data = VoucherCashFileData::getVouchersCashFile([]);
            $response = array_merge($response, $data->toArray());
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
        }
        return response()->json($response, $response["code"]);
    }

    public function show($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = VoucherCashFileData::getVoucherCashFile($id);
        }
        return response()->json($response, $response["code"]);
    }

    public function store(VoucherCashFileRequest $request)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $params = $request->all();
            $data = VoucherCashFileData::AddVoucherCashFile($params);
            if ($data) {
                $response["code"] = 200;
                $response['success'] = true;
                $response['message'] = 'OK';
                $response['data'] = $data;
            } else {
                $response["code"] = 200;
                $response['success'] = false;
                $response['message'] = 'Error';
                $response['data'] = $data;
            }
        }
        return response()->json($response, $response["code"]);
    }
}