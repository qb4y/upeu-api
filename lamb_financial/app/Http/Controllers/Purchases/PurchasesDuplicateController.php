<?php


namespace App\Http\Controllers\Purchases;


use App\Http\Data\GlobalMethods;
use App\Http\Data\Purchases\PurchasesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesDuplicateController
{

    public function providerPurchases(Request $request)
    {
        $auth = GlobalMethods::authorizationLamb($request);
        $status = $auth["valida"];
        $code = $auth['code'];

        $page_size = $request->page_size ? $request->page_size : 5;
        $id_proveedor = $request->id_proveedor;
        $id_entidad = $auth["id_entidad"];
        $id_depto = $auth["id_depto"];

        $response = array();
        $response['success'] = true;
        $response['message'] = 'OK';
        $response['data'] = null;

        if ($status == 'SI') {
            $code = 200;

            $data = DB::table('ELISEO.COMPRA a')
                ->select(
                    'a.ID_COMPRA',
                    'a.ID_ANHO',
                    'a.FECHA_DOC',
                    'a.SERIE',
                    'a.NUMERO',
                    'a.IMPORTE',
                    'a.IMPORTE_ME',
                    'a.IGV')
                ->where([
                    ['a.ID_ENTIDAD', '=', $id_entidad],
                    ['a.ID_DEPTO', '=', $id_depto],
                    ['a.ID_PROVEEDOR', '=', $id_proveedor],
                    ['a.ESTADO', '=', '1'],
                ])
                ->orderBy('a.FECHA_DOC', 'desc')
                ->paginate($page_size);


            $response['data'] = $data;

            $provider = DB::select("SELECT a.ID_PERSONA, ELISEO.PKG_PURCHASES.FC_RUC(a.ID_PERSONA) AS RUC,
                                    ELISEO.PKG_PURCHASES.FC_PROVEEDOR(a.ID_PERSONA) AS PROVEEDOR
                                    FROM MOISES.PERSONA a
                                    WHERE a.ID_PERSONA = :id_proveedor_p",
                ['id_proveedor_p' => $id_proveedor]);

            $response['provider'] = collect($provider)->first();


        }

        return response()->json($response, $code);

    }


    public function detailPurchase(Request $request)
    {
        $auth = GlobalMethods::authorizationLamb($request);
        $status = $auth["valida"];
        $code = $auth['code'];

        $id_compra = $request->id_compra;
        $id_proveedor = $request->id_proveedor;
        $id_entidad = $auth["id_entidad"];
        $id_depto = $auth["id_depto"];

        $response = array();
        $response['success'] = true;
        $response['message'] = 'OK';
        $response['data'] = null;

        if ($status == 'SI') {
            $code = 200;
            $compra = DB::select(
                "SELECT
                       ELISEO.PKG_PURCHASES.FC_RUC(a.ID_PROVEEDOR)       AS NOMBRE_RUC,
                       ELISEO.PKG_PURCHASES.FC_PROVEEDOR(a.ID_PROVEEDOR) AS NOMBRE_PROVEEDOR,
                       b.ID_COMPROBANTE ||' - '||b.NOMBRE as NOMBRE_DOC,
                       c.NOMBRE as NOMBRE_MONEDA,
                       c.SIMBOLO as NOMBRE_SIMBOLO,
                       a.*
                FROM ELISEO.COMPRA a
                left join ELISEO.TIPO_COMPROBANTE b on a.ID_COMPROBANTE = b.ID_COMPROBANTE
                left join ELISEO.CONTA_MONEDA c on a.ID_MONEDA = c.ID_MONEDA
                WHERE a.ID_ENTIDAD = :id_entidad_p
                  AND a.ID_DEPTO = :id_depto_p
                  AND a.ID_PROVEEDOR = :id_proveedor_p
                  AND a.ID_COMPRA = :id_compra_p",
                [
                    'id_entidad_p' => $id_entidad,
                    'id_depto_p' => $id_depto,
                    'id_proveedor_p' => $id_proveedor,
                    'id_compra_p' => $id_compra
                ]);
            $response['data']['purchase'] = collect($compra)->first();
            $response['data']['purchaseDetail'] = self::getDetail($id_compra);
            $response['data']['seatsAcounting'] = self::getSeatsAcounting($id_compra);
            $response['data']['entidad'] = $id_entidad;


        }

        return response()->json($response, $code);
    }


    static function getDetail($id_compra)
    {
        $datos = PurchasesData::showMyPurchases($id_compra);
        foreach ($datos as $item) {
            $id_anho = $item->id_anho;
        }

        return DB::table('COMPRA_DETALLE')
            ->select(
                'COMPRA_DETALLE.*',
                DB::raw(
                    "
                    FC_TIPO_IGV_COMPRA(ID_CTIPOIGV) AS TIPO_IGV,
                    PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS NOMBRE_ARTICULO,
                    PKG_SALES.FC_PRECIO_VENTA(ID_ALMACEN,ID_ARTICULO,".$id_anho.") AS PRECIO_VENTA,
                    PKG_PURCHASES.FC_TIPO_ALMACEN(ID_ALMACEN) AS ID_EXISTENCIA
                    "
                )
            )
            ->where('ID_COMPRA', $id_compra)
            ->orderBy('ID_DETALLE')
            ->get();
    }

    static function getSeatsAcounting($id_compra)
    {
        /*$id_anho = null;
        $id_empresa = null;
        $compra = PurchasesData::ShowCompraEmpresa($id_compra);
        foreach ($compra as $item) {
            $id_anho = $item->id_anho;
            $id_empresa = $item->id_empresa;
        }


        $query = "SELECT 
(SELECT X.ID_COMPROBANTE FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA) AS ID_COMPROBANTE,
(SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = A.ID_CUENTAAASI AND X.ID_RESTRICCION = A.ID_RESTRICCION 
                    AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = ".$id_anho." AND X.ID_EMPRESA = ".$id_empresa.") AS CTA_EMPRESARIAL,
                               A.*         
                FROM COMPRA_ASIENTO A
                WHERE ID_COMPRA = $id_compra
                ORDER BY ID_CASIENTO ASC";

        $data = DB::select($query);
        $total = PurchasesData::listPurchasesSeatsAcountingTotal($id_compra);
        return ["data" => $data, "total" => $total];

        */
        return DB::select("SELECT * FROM ELISEO.CONTA_ASIENTO
            WHERE ID_TIPOORIGEN = 3
            AND ID_ORIGEN = :id_compra_p",
            ['id_compra_p' => $id_compra]);



    }

}