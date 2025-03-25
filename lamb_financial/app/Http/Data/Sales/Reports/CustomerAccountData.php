<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 7/04/21
 * Time: 17:16
 */

namespace App\Http\Data\Sales\Reports;


use Illuminate\Support\Facades\DB;

class CustomerAccountData
{

    public static function getCustomerAccount($params)
    {
        return DB::table("ELISEO.VW_SALES_CUENTA_CLIENTE SALES")
            ->select("alumno", "codigo", "operacion", "lote", "numero", "debito", "credito", "total")
            ->where("SALES.ID_ENTIDAD", $params['id_entidad'])
            ->where("SALES.ID_DEPTO", $params['id_depto'])
            ->where("SALES.ID_ANHO", $params['id_anho'])
            ->where("SALES.ID_MES", $params['id_mes'])
            ->where("SALES.CUENTA", $params['cuenta'])
            ->where("SALES.TIPO", $params['tipo'])
            ->paginate($params['per_page']);
        // $params = ['id_entidad' => 7124, 'id_depto' => '1', 'id_anho' => 2020, 'id_mes'=> 3, 'id_cuenta' => 1132001];

        //ELOQUENT
        /*
         * -> ORM
         * QUERY-BUILDING
         * Native sql <<
        */
    }
}