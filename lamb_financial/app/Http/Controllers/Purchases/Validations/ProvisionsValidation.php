<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 4/03/2019
 * Time: 21:16
 */

namespace App\Http\Controllers\Purchases\Validations;


use App\Http\Controllers\Setup\Provider\sunat;
use App\Http\Data\Purchases\PurchasesData;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Setup\Person\PersonController;

class ProvisionsValidation
{
    public static function validationCall($function)
    {
        $resultCall = call_user_func(__NAMESPACE__ .'\ProvisionsValidation::'.$function);
        return $resultCall;
    }


    // Otros = 00
    public static function validateDocument00() {
        $result = new class{};

        $result->valid   = false;
        $result->invalid = true;
        $result->message = "Tipo de documento no aceptado.";
        return $result;
    }

    // Factura = 01
    public static function validateDocument01() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica === "S") {
            $rules = RulesDocuments::rulesElectronicDocument01();
        } else if($es_electronica === "N") {
            $rules = RulesDocuments::rulesDocument01();
        }
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        /*
        $es_ret_det = Input::get("es_ret_det");
        if($es_ret_det == "R") {
            $rules_retencion = RulesDocuments::rulesRetencion();
            $validator_retencion = Validator::make(Input::all(), $rules_retencion);
            if ($validator_retencion->fails())
            {
                $errorString = implode(",",$validator_retencion->messages()->all());
                $result->valid   = false;
                $result->invalid = true;
                $result->message = $errorString;
                return $result;
            }
        } else if ($es_ret_det === "D") {
            $rules_detraccion = RulesDocuments::rulesDetraccion();
            $validator_detraccion = Validator::make(Input::all(), $rules_detraccion);
            if ($validator_detraccion->fails())
            {
                $errorString = implode(",",$validator_detraccion->messages()->all());
                $result->valid   = false;
                $result->invalid = true;
                $result->message = $errorString;
                return $result;
            }
        }
        */
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Recibo por honorarios = 02
    public static function validateDocument02() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument02();
        } else {
            $rules = RulesDocuments::rulesDocument02();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    public static function validateDocumentRxH() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S"){
            $rules = RulesDocuments::rulesElectronicDocumentRxH();
        } else {
            $rules = RulesDocuments::rulesDocumentRxH();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    // Boleta de venta = 03
    public static function validateDocument03() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument03();
        }
        else // if($es_electronica == "N")
        {
            $rules = RulesDocuments::rulesDocument03();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;

    }

    // Liquidación de compra = 04
    public static function validateDocument04() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument04();
        }
        else
        {
            $rules = RulesDocuments::rulesDocument04();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Boletos de transporte Aéreo = 05
    public static function validateDocument05() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument05();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Carta de porte Aéreo = 06
    public static function validateDocument06() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument06();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Nota de crédito = 07
    public static function validateDocument07() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument07();
        }
        else
        {
            $rules = RulesDocuments::rulesDocument07();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Nota de dédito = 08
    public static function validateDocument08() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument08();
        }
        else
        {
            $rules = RulesDocuments::rulesDocument08();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Guia de remisión = 09
    public static function validateDocument09() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument09();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Recibo por arrendamiento = 10
    public static function validateDocument10() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument10();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Ticket o cinta emitida por máquina registradora = 12
    public static function validateDocument12() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument12();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Documentos emitidos por las empresas del sistema financiero y de seguros = 13
    public static function validateDocument13() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument13();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Recibo por servicios públicos de suministro de energía eléctrica, agua, teléfono = 14
    public static function validateDocument14() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument14();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Boletos emitidos por el servicio de transporte terrestre regular urbano de pasajeros = 15
    public static function validateDocument15() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument15();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Boletos de viaje emitidos por las empresas de transporte nacional de pasajeros = 16
    public static function validateDocument16() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument16();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Documents que emitan los concesionarios del servicio de revisiones técnicas vehiculares = 37
    public static function validateDocument37() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument37();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Formulario de Declaración - pago o Boleta de pago de tributos Internos = 46
    public static function validateDocument46() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument46();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Declaración Única de Aduanas - Importación definitiva = 50
    public static function validateDocument50() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument50();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Nota de Crédito Especial = 87
    public static function validateDocument87() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument87();
        }
        else
        {
            $rules = RulesDocuments::rulesDocument87();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Nota de Débito Especial = 88
    public static function validateDocument88() {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = RulesDocuments::rulesElectronicDocument88();
        }
        else
        {
            $rules = RulesDocuments::rulesDocument88();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Comprobante de No Domiciliado = 91
    public static function validateDocument91() {
        $result = new class{};
        $rules = RulesDocuments::rulesDocument91();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    // Validar que el proveedor esté validado por la Sunat.
    public static function validateRucInSunat($id_proveedor, $id_comprobante) {
        $jResponse = [];
        $dataProveedor= null;
        if ($id_comprobante === '02') { // Recibos por honorarios.
            $dataProveedor = PurchasesData::showNaturalPersonVW($id_proveedor);
        } else {
            $dataProveedor = PurchasesData::showLegalPersonVW($id_proveedor);
            if(!$dataProveedor) {
                $dataProveedor = PurchasesData::showNaturalPersonVW($id_proveedor);
            }
        }
        // if($dataProveedor != null){
        //     $cliente = new sunat(true,true);
        //     $dataSunat = $cliente->search($dataProveedor->id_ruc,true); // $ruc
        //     $isSunat = $dataSunat["success"];
        // }else{
        //     $jResponse['success'] = false;
        //     $jResponse['message'] = "No Existe el Proveedor";
        //     $jResponse['data'] = null;
        //     $jResponse['code'] = 200;
        //     goto end;
        // }
        if(is_null($dataProveedor)) {
            throw new Exception("Alto! No existe el proveedor en la base de datos LAMB", 1);
        }
        $response = [];
        // throw new Exception($dataProveedor->id_ruc, 1);
        try{   
            $response = PersonController::dataSunat($dataProveedor->id_ruc);
        }catch(Exception $e){
            throw new Exception($e->getMessage(), 1);
        }
        if($response["condicion"] !== "HABIDO" && $response["estado"] === "ACTIVO")
        {
            throw new Exception("Alto! El proveedor esta como no habido, Sunat.", 1);
        }
        if($response["condicion"] === "HABIDO" && $response["estado"] !== "ACTIVO")
        {
            throw new Exception("Alto! El proveedor esta desactivo, Sunat.", 1);
        }
        if($response["condicion"] !== "HABIDO" && $response["estado"] !== "ACTIVO")
        {
            throw new Exception("Alto! El proveedor esta como no habido y desactivo, Sunat.", 1);
        }

        // if($isSunat != true)
        // {
        //     $jResponse['success'] = false;
        //     $jResponse['message'] = "No se puede conectar a la Sunat.";
        //     $jResponse['data'] = null;
        //     $jResponse['code'] = 200;
        //     goto end;
        // }
        // if($dataSunat["Condicion"] !== "HABIDO" && $dataSunat["Estado"] === "ACTIVO")
        // {
        //     $jResponse['success'] = false;
        //     $jResponse['message'] = "El proveedor esta como no habido, Sunat.";
        //     $jResponse['data'] = null;
        //     $jResponse['code'] = 202;
        //     $code = "202";
        //     goto end;
        // }
        // if($dataSunat["Condicion"] === "HABIDO" && $dataSunat["Estado"] !== "ACTIVO")
        // {
        //     $jResponse['success'] = false;
        //     $jResponse['message'] = "El proveedor esta desactivo, Sunat.";
        //     $jResponse['data'] = null;
        //     $jResponse['code'] = 202;
        //     goto end;
        // }
        // if($dataSunat["Condicion"] !== "HABIDO" && $dataSunat["Estado"] !== "ACTIVO")
        // {
        //     $jResponse['success'] = false;
        //     $jResponse['message'] = "El proveedor esta como no habido y desactivo, Sunat.";
        //     $jResponse['data'] = null;
        //     $jResponse['code'] = 202;
        //     goto end;
        // }
        // $jResponse['success'] = true;
        // $jResponse['message'] = "Success.";
        // $jResponse['data'] = null;
        // $jResponse['code'] = 200;
        // end:
        return true;
    }

    public static function validateDetraccion($id_tipoasiento) {
        $result = new class{};
        
        $rulesCuentaBancaria = ($id_tipoasiento !== 'MI') ? ['detraccion_cuenta_bancaria' => 'required']: [];
        $rules = RulesDocuments::rulesDetraccion();

        $validator = Validator::make(Input::all(), array_merge($rules, $rulesCuentaBancaria));
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }

    public static function validateRetencion() {
        $result = new class{};
        $rules = RulesDocuments::rulesRetencion();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
}