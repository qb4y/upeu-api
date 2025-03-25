<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 14:12
 */

namespace App\Http\Controllers\Treasury\VoucherCash;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Treasury\VoucherCash\VoucherCashData;
use App\Http\Requests\VoucherCashDenyRequest;
use Illuminate\Http\Request;

class VoucherCashController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function show($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = VoucherCashData::getVoucherCash($id);
        }
        return response()->json($response, $response["code"]);
    }

    public function destroy($id)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        if ($valid) {
            $deleted = VoucherCashData::deleteVoucherCash($id);
            if ($deleted) {
                $response["code"] = 200;
                $response['success'] = true;
                $response['message'] = 'OK';
                $response['data'] = $deleted;
            } else {
                $response["code"] = 200;
                $response['success'] = false;
                $response['message'] = 'error';
                $response['data'] = null;
            }
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = null;
        }
        return response()->json($response, $response["code"]);
    }

    public function denyVoucherCash(VoucherCashDenyRequest $request)
    {
        $global = GlobalMethods::authorizationLamb($this->request);
        $valid = $global["valida"];
        $mainParams = array(
            'ip' => GlobalMethods::ipClient($this->request),
            'id_user' => $global["id_user"],
            'id_entidad' => $global["id_entidad"],
        );
        if ($valid) {
            $params = array_merge($mainParams, $request->all());
            $data = VoucherCashData::denyVoucherCash($params);
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