<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 4/03/2019
 * Time: 21:35
 */

namespace App\Http\Controllers\Purchases\Validations;


class RulesDocuments
{

    // =================== ELECTRONICOS ==================

    // Factura Electrónica = 01
    public static function rulesElectronicDocument01(){
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'serie' => 'required|min:4|max:4',
            // 'serie' => 'required|min:4|max:4|regex:/^[F]{1}[0-9]{3}/u',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'base_inafecta' => '',
            'otros' => '',
            'es_ret_det' => 'required',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Factura = 01
    public static function rulesDocument01() {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'es_transporte_carga' => '',
            'serie' => 'required|min:4|max:4|regex:/^[0-9]+$/u',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'base_inafecta' => '',
            'otros' => '',
            'es_ret_det' => 'required',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    public static function rulesRetencion() {
        return [
            // 'retencion_forma_pago' => 'required',
            // 'retencion_cuenta_bancaria' => 'required',
            'retencion_nro' => 'required|max:10',
            'retencion_serie' => 'required|max:4',
            'retencion_fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'retencion_importe' => 'required|numeric',
            'retencion_id_voucher_mb' => 'required',
        ];
    }

    public static function rulesDetraccion() {
       
        return [
            'detraccion_tipo_operacion' => 'required',
            'detraccion_tipo_bien_servicio' => 'required',
            // 'detraccion_cuenta_bancaria' => 'required',
            'detraccion_nro_constancia' => 'required|max:10',
            'detraccion_nro_operacion' => 'required|max:18',
            'detraccion_importe' => 'required|numeric',
            'detraccion_fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'detraccion_id_voucher_mb' => 'required',
        ];
    }


    // Recibo por honorario electrónico = 02
    public static function rulesElectronicDocument02()
    {
        return [
            'id_voucher_compra' => 'required',
            // 'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required|in:S',
            'serie' => 'required|min:4|max:4|regex:/^[E]{1}[0]{1}[0]{1}[1]{1}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'tiene_suspencion' => 'required',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Recibo por honorario = 02
    public static function rulesDocument02()
    {
        return [
            'id_voucher_compra' => 'required',
            // 'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required|in:S',
            'serie' => 'required|min:3|max:3|regex:/^[0-9]{3}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'tiene_suspencion' => 'required',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }
    // Recibo por honorario electrónico = 02 => 7124
    public static function rulesElectronicDocumentRxH(){
        return [
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required|in:S',
            'serie' => 'required|min:4|max:4|regex:/^[E]{1}[0]{1}[0]{1}[1]{1}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'tiene_suspencion' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Recibo por honorario = 02 => 7124
    public static function rulesDocumentRxH(){
        return [
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required|in:S',
            'serie' => 'required|min:3|max:3|regex:/^[0-9]{3}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'tiene_suspencion' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Boleta de venta electrónico = 03
    public static function rulesElectronicDocument03()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            // 'serie' => 'required|min:4|max:4|regex:/^[B]{1}[0-9]{3}/u',
            'serie' => 'required|min:4|max:4',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Boleta de venta = 03
    public static function rulesDocument03()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            // 'serie' => 'required|min:4|max:4|regex:/^[0-9]{3}/u',
            'serie' => 'required|min:4|max:4',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Liquidación de compra electrónico = 04
    public static function rulesElectronicDocument04()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            //'serie' => 'required|max:4|regex:/^[E]{1}[0]{1}[0]{1}[1]{1}/u',
            'serie' => 'required|max:4',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Liquidación de compra = 04
    public static function rulesDocument04()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:7|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }



    // Boletos de transporte Aéreo = 05
    public static function rulesDocument05()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'required|max:1|regex:/^[1-5]{1}/u',
            'numero' => 'required|max:11|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',
            'taxs' => 'required|numeric',
            'es_ret_det' => 'required',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Carta de porte Aéreo = 06
    public static function rulesDocument06()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/|not_in:0',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Nota de crédito electrónico = 07
    public static function rulesElectronicDocument07()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            // 'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'serie' => ['required', 'min:4', 'max:4'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de crédito = 07
    public static function rulesDocument07()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            // 'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'serie' => 'required|min:4|max:4',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Nota de dédito electrónico = 08
    public static function rulesElectronicDocument08()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de dédito = 08
    public static function rulesDocument08()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }




    // Guia de remisión electrónico = 09
    public static function rulesDocument09()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'max:20',
            'numero' => 'max:20',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Recibo por arrendamiento = 10
    public static function rulesDocument10()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            // 'serie' => 'max:20',
            // 'numero' => 'max:20',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Ticket o cinta emitida por máquina registradora = 12
    public static function rulesDocument12()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'required|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }


    // Documentos emitidos por las empresas del sistema financiero y de seguros = 13
    public static function rulesDocument13()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Recibo por servicios públicos de suministro de energía eléctrica, agua, teléfono = 14
    public static function rulesDocument14()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'fecha_vencimiento' => 'required|date_format:Y-m-d',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Boletos emitidos por el servicio de transporte terrestre regular urbano de pasajeros = 15
    public static function rulesDocument15()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:today',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Boletos de viaje emitidos por las empresas de transporte nacional de pasajeros = 16
    public static function rulesDocument16()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Documents que emitan los concesionarios del servicio de revisiones técnicas vehiculares = 37
    public static function rulesDocument37()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Formulario de Declaración - pago o Boleta de pago de tributos Internos  = 46
    public static function rulesDocument46()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'max:20',
            'numero' => 'max:20',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Declaración Única de Aduanas - Importación definitiva = 50
    public static function rulesDocument50()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'max:20',
            'numero' => 'max:20',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de Crédito Especial Electrónico = 87
    public static function rulesElectronicDocument87()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de Crédito Especial = 87
    public static function rulesDocument87()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de Dédito Especial Electrónico = 88
    public static function rulesElectronicDocument88()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Nota de Dédito Especial = 88
    public static function rulesDocument88()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            'id_parent' => 'required',
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

    // Comprobante de No Domiciliado = 91
    public static function rulesDocument91()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'serie' => 'max:20',
            'numero' => 'max:20',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }
    public static function rulesDocument17()
    {
        return [
            'id_voucher' => 'required',
            'tipo' => 'required',
            'id_proveedor' => 'required',
            'id_comprobante' => 'required',
            'es_electronica' => 'required',
            // 'serie' => 'required|min:4|max:4|regex:/^[0-9]{3}/u',
            'serie' => 'required|min:4|max:4',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'fecha_doc' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'id_moneda' => 'required',
            'importe' => 'required|numeric',

            'id_dinamica' => 'required',
            'id_tipotransaccion' => 'required',
        ];
    }

}