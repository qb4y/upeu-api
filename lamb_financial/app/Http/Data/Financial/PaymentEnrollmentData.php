<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 31/01/20
 * Time: 10:06 AM
 */

namespace App\Http\Data\Financial;
use Illuminate\Support\Facades\DB;

class PaymentEnrollmentData {

    /**
     * name="criterion",
     * description="Obtiene criterios segun un nivel de enseñanza y un filtro de nombre de criterio.",
     */
    public static function studentBalance($params){
        return DB::table('ELISEO.VW_SALES_SALDO')
            ->select(DB::raw('SUM(TOTAL) as total'))
            ->where('ELISEO.VW_SALES_SALDO.ID_ENTIDAD', '=', $params['id_entidad'])
            ->where('ELISEO.VW_SALES_SALDO.ID_DEPTO', '=', $params['id_depto'])
            ->where('ELISEO.VW_SALES_SALDO.ID_CLIENTE', '=', $params['id_user'])
            ->where('ELISEO.VW_SALES_SALDO.ID_ANHO', '=', $params['id_anho'])
            ->first();
    }
}