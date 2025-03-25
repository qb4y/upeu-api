<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21/01/20
 * Time: 11:32 AM
 */

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Purchases\PurchasesData;
use DOMPDF;
use Exception;
use Illuminate\Http\Request;


class OrderPurchaseController extends Controller
{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    // para generar el pdf de Orden de COmpra
     public function generatePDF(Request $request){
        $id_order = $request->query("id_order");
        $jResponse = [];
        $nombre = "";

        try{
            $cabecera = PurchasesData::getOrdenCompra($id_order);
            $detalle = PurchasesData::getDetalleOrdenCompra($id_order);
            $totales = PurchasesData::getTotalesDetalleOrdenCompra($id_order);

            $igv = 0;
            $total = 0;
            foreach($totales as $item){
                $total = $item->total;
                $igv =  floatVal($total) * 0.18;

                if($item->con_igv == 'S'){
                    $item->igv = $igv;
                    $item->subtotal = $total - $igv;
                }else{
                    $item->igv = 0;
                    $item->subtotal = $total;
                }
            }

            $serie = $cabecera[0] -> serie;
            
            $pdf = DOMPDF::loadView('pdf.purchases.orderPurchase',
            [    
                'cabecera'  =>    $cabecera,
                'detalle'   =>    $detalle,
                'totales'   =>    $totales,
                'serie'     =>    $serie
            ])->setPaper('a4', 'portrait'); 

            $nombre = 'Orden de Compra N° '.$serie;
        }catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e;
            $jResponse['data'] = [];
            $code = "202";
            return response()->json($jResponse,$code);
        }
        return $pdf->stream($nombre.'.pdf');
    }
}