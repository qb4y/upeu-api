<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 7/04/21
 * Time: 12:18
 */

namespace App\Http\Controllers\Sales\Reports;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Sales\Reports\CustomerAccountData;
use Illuminate\Http\Request;

class CustomerAccountController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getCustomerAccounts()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $queryParams = $this->request->all();
        $valida = $jResponse["valida"]; // SI : NO
        $params = [
            'id_entidad' => $jResponse['id_entidad'], //session
            'id_depto' => $jResponse['id_depto'], //session
            'id_anho' => $queryParams['id_anho'], // queryParams del id_anho
            'id_mes' => $queryParams['id_mes'], // queryParams del id_mes
            'cuenta' => $queryParams['cuenta'], // queryParams del cliente
            'tipo' => $queryParams['tipo'], // queryParams del cliente
            'per_page' => isset($queryParams['per_page']) ? $queryParams['per_page'] : 10,// operador ternario -> if else
        ];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = CustomerAccountData::getCustomerAccount($params);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }
}