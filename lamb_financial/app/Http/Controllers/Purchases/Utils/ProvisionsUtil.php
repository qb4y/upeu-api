<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 4/03/2019
 * Time: 16:48
 */

namespace App\Http\Controllers\Purchases\Utils;


use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Treasury\ExpensesData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ProvisionsUtil
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    // Función PRO by José
    public  static function utilCall($function) {
        $resultCall = call_user_func(__NAMESPACE__ .'\ProvisionsUtil::'.$function);
        return $resultCall;
    }


    // Otros = 00
    public static function dataDocument00() { }

    // Factura = 01
    public static function dataDocument01() {
        $result = new class{};
        $data = [];
        $tipo = Input::get('tipo');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["id_comprobante"] = Input::get('id_documento'); // id_comprobante
        $data["es_electronica"] = Input::get('es_electronica');

        $data["es_transporte_carga"] = Input::get('es_transporte_carga');

        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        $data["fecha_doc"] = date_create(Input::get('fecha_doc')); // 'YY-MM-DD'

        $data["id_moneda"] = Input::get('id_moneda');
        $data["importe"] = Input::get('id_moneda');
        $data["base_inafecta"] = Input::get('inafecta');
        $data["otros"] = Input::get('otros');

        if($data["id_moneda"] == "9") { // Dolar Americano
            $fecha_doc = Input::get('fecha_doc');
            $importe_me = Input::get('importe');
            $tipo_cambio = AccountingData::showTipoCambio($fecha_doc);
            $dataMoneda = $tipo_cambio[0];

            $importe = DB::raw("(".$importe_me."*".$dataMoneda->cos_compra.")");
            $data["importe_me"] = $importe_me;
            $data["importe"] = $importe;
            $data["tipocambio"] = $dataMoneda->cos_compra;
        } else if($data["id_moneda"] == "7") { // Soles
            // $data["importe_me"] = "";
            $data["importe"] = Input::get('importe');
            // $data["tipocambio"] = "";
        }
        // CALCULOS
        $total_soles = $data["importe"]; // Esto ya esta en soles.
        $base_inafecta_soles = $data["base_inafecta"];
        $otros_soles = $data["otros"];
        $total_sincredito_soles = DB::raw('('.$total_soles.'-('.$base_inafecta_soles.'+'.$otros_soles.'))'); // total_sincredito
        $base_sincredito_soles = DB::raw('('.$total_sincredito_soles.'/1.18)'); // base_sincredito
        $igv_sincredito_soles = DB::raw($total_sincredito_soles.'-'.$base_sincredito_soles);

        if($tipo === 'G') {
            $data["base_gravada"] = $base_sincredito_soles;
            $data["igv_gravada"] = $igv_sincredito_soles;
        } else if($tipo === 'GNG') {
            $data["base_mixta"] = $base_sincredito_soles;
            $data["igv_mixta"] = $igv_sincredito_soles;
        } else if($tipo === 'NG') {
            $data["base_nogravada"] = $base_sincredito_soles;
            $data["igv_nogravada"] = $igv_sincredito_soles;
        }
        $data["base"] = $base_sincredito_soles;
        $data["igv"] = $igv_sincredito_soles;

        //$data["base_sincredito"] = $base_sincredito_soles;
        //$data["base"] = $base_sincredito_soles;
        //$igv_sincredito_soles = DB::raw($total_sincredito_soles.'-'.$base_sincredito_soles);
        //$data["igv_sincredito"] = $igv_sincredito_soles;
        // END

        $data["es_ret_det"] = Input::get('es_ret_det');
        if($data["es_ret_det"] === "R") {
            $data["retencion_forma_pago"] = Input::get('retencion_forma_pago');
            $data["retencion_numero_operacion"] = Input::get('retencion_numero_operacion');
            $data["retencion_fecha"] = Input::get('retencion_fecha');
            //$data["retencion_importe"] = Input::get('retencion_importe');
            $data["retencion_importe"] = DB::raw('0.03*'.$data["importe"]);; // La retención siempre será 3%(Por ahora según sunat)

            // $data["retencion_importe"] = Input::get('retencion_importe');
            // $data["retencion_serie"] = Input::get('retencion_serie');
            // $data["retencion_numero"] = Input::get('retencion_numero');
            // $retencion_fecha_full = Input::get('retencion_fecha');
            // $retencion_fecha = $retencion_fecha_full["year"]."/".$retencion_fecha_full["month"]."/".$retencion_fecha_full["day"];
            // $data["retencion_fecha"] = $retencion_fecha;
        } else if($data["es_ret_det"] == "D")
        {
            $data["detraccion_tipo"] = Input::get('detraccion_tipo'); // Tipo bien/servicio
            $data["detraccion_forma_pago"] = Input::get('detraccion_forma_pago');
            $data["detraccion_numero"] = Input::get('detraccion_numero');
            $data["detraccion_serie"] = Input::get('detraccion_serie');
            $dataTiposBienesServicios = ExpensesData::showTypeGoodServiceById(Input::get('detraccion_tipo'));
            $dataTipoBienServicio = $dataTiposBienesServicios[0];
            $data["detraccion_importe"] = DB::raw($dataTipoBienServicio->tasa.'*'.$data["importe"]);
            $data["detraccion_fecha"] = Input::get('detraccion_fecha');
            // $data["detraccion_numero"] = Input::get('detraccion_numero');
            // $detraccion_fecha_full = Input::get('detraccion_fecha');
            // $detraccion_fecha = $detraccion_fecha_full["year"]."/".$detraccion_fecha_full["month"]."/".$detraccion_fecha_full["day"];
            // $data["detraccion_fecha"] = $detraccion_fecha;
            // $data["detraccion_importe"] = Input::get('detraccion_importe');
            // $data["detraccion_banco"] = Input::get('detraccion_banco');
        }

        // $data["id_documento"] = Input::get('id_documento');
        // $data["es_electronica"] = Input::get('es_electronica');
        $data["fecha_provision"] = DB::raw('sysdate');
        // dd($data);
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "Data Error. ok.";
        $result->data = $data;

        return $result;
    }

    // Recibo por honorarios = 02
    public static function dataDocument02() { }

    // Boleta de venta = 03
    public static function dataDocument03() { }

    // Liquidación de compra = 04
    public static function dataDocument04() { }

    // Boletos de transporte Aéreo = 05
    public static function dataDocument05() { }

    // Carta de porte Aéreo = 06
    public static function dataDocument06() { }

    // Nota de crédito = 07
    public static function dataDocument07() { }

    // Nota de dédito = 08
    public static function dataDocument08() { }

    // Guia de remisión = 09
    public static function dataDocument09() { }

    // Recibo por arrendamiento = 10
    public static function dataDocument10() { }

    // Ticket o cinta emitido por máquina registradora = 12
    public static function dataDocument12() { }

    // Documentos emitidos por las empresas del sistema financiero y de seguros = 13
    public static function dataDocument13() { }

    // Recibo por servicios públicos de suministro de energía eléctrica, agua, teléfono = 14
    public static function dataDocument14() { }

    // Boletos emitidos por el servicio de transporte terrestre regular urbano de pasajeros = 15
    public static function dataDocument15() { }

    // Boletos de viaje emitidos por las empresas de transporte nacional de pasajeros = 16
    public static function dataDocument16() { }

    // Documentos que emitan los concesionarios del servicio de revisiones técnicas vehiculares = 37
    public static function dataDocument37() { }

    // Formulario de Declaración - pago o Boleta de pago de tributos Internos = 46
    public static function dataDocument46() { }

    // Declaración Única de Aduanas - Importación definitiva = 50
    public static function dataDocument50() { }

    // Nota de Crédito Especial = 87
    public static function dataDocument87() { }

    // Nota de Débito Especial = 88
    public static function dataDocument88() { }

    // Comprobante de No Domiciliado = 91
    public static function dataDocument91() { }
}