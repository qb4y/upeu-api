<?php
namespace App\Http\Data\Purchases;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use PDO;

class SettingsData extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  static function listPurchasesOfSettings($id_entidad,$id_depto,$id_voucher) {
        $query = "SELECT A.ID_AJUSTE,
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        A.ID_ANHO,
                        A.ID_MES,
                        A.ID_COMPRA,
                        A.ID_PERSONA,
                        A.ID_PROVEEDOR,
                        PKG_PURCHASES.FC_RUC(A.ID_PROVEEDOR) AS RUC_PROVEEDOR,
                        FC_NOMBRE_PERSONA(A.ID_PROVEEDOR) AS NOMBRE_PROVEEDOR,
                        A.ID_DINAMICA,
                        A.ID_MONEDA,
                        A.ID_VOUCHER,
                        A.ID_TIPOORIGEN,
                        A.FECHA,
                        A.NUMERO,
                        A.IMPORTE,
                        A.IMPORTE_ME,
                        A.DC,
                        A.ESTADO,
                        (CASE WHEN A.ID_COMPRA IS NOT NULL THEN B.SERIE ELSE C.SERIE END) AS SERIE,
                        (CASE WHEN A.ID_COMPRA IS NOT NULL THEN B.NUMERO ELSE C.NUMERO END) AS NUMERO_COMPRA,
                        (CASE WHEN A.ID_COMPRA IS NOT NULL THEN B.IMPORTE ELSE C.IMPORTE END) AS IMPORTE_COMPRA,
                        -- B.SERIE,B.NUMERO AS NUMERO_COMPRA,B.IMPORTE IMPORTE_COMPRA,
                        (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS ACTIVO
                FROM COMPRA_AJUSTE A LEFT JOIN COMPRA B
                ON A.ID_COMPRA = B.ID_COMPRA
                AND A.ID_PROVEEDOR = B.ID_PROVEEDOR
                LEFT JOIN COMPRA_SALDO C
                ON A.ID_SALDO = C.ID_SALDO
                AND A.ID_PROVEEDOR = C.ID_PROVEEDOR
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_VOUCHER = ".$id_voucher." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

}