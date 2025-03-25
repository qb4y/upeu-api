<?php

namespace App\Http\Data;

use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use Carbon\Carbon;

class EntidadConfig
{
    static function getAnhoConfig($id_entidad)
    {
        $currentYear = date("Y");
        $anhoConfig = DB::table('eliseo.CONTA_ENTIDAD_ANHO_CONFIG')
            ->where('id_entidad', $id_entidad)
            ->where('id_anho', $currentYear)
            ->where('activo', '1')
            ->first();
        if (is_null($anhoConfig)) {
            $anhoConfig = DB::table('eliseo.CONTA_ENTIDAD_ANHO_CONFIG')
                ->where('id_entidad', $id_entidad)
                ->where('activo', '1')
                ->orderBy('id_anho', 'desc')
                ->first();
        }
        if (is_null($anhoConfig)) {
            throw new Exception('Ocurrió un error, no existe una configuración del año contable para la entidad ' . $id_entidad, 203);
            // abort(203, 'Ocurrió un error, no existe una configuración del año contable para la entidad '.$id_entidad);
        }
        $object = new \stdClass;
        $object->isValid = $anhoConfig->id_anho === $currentYear;
        $object->config = $anhoConfig;
        return $object;
    }

    static function getMesConfig($id_entidad, $id_anho)
    {
        $currentMonth = (int) date('m');
        $mesConfig = DB::table('eliseo.CONTA_ENTIDAD_MES_CONFIG')
            ->where('id_entidad', $id_entidad)
            ->where('id_anho', $id_anho)
            ->where('id_mes', $currentMonth)
            ->where('ESTADO', '1')
            ->first();
        if (is_null($mesConfig)) {
            $mesConfig = DB::table('eliseo.CONTA_ENTIDAD_MES_CONFIG')
                ->where('id_entidad', $id_entidad)
                ->where('id_anho', $id_anho)
                ->where('ESTADO', '1')
                ->orderBy('id_mes', 'desc')
                ->first();
        }
        if (is_null($mesConfig)) {
            throw new Exception('Ocurrió un error, no existe una configuración del mes contable para la entidad ' . $id_entidad, 203);
            // abort(203, 'Ocurrió un error, no existe una configuración del mes contable para la entidad '.$id_entidad);
        }
        $object = new \stdClass;
        $object->isValid = ((int) $mesConfig->id_mes) === $currentMonth;
        $object->config = $mesConfig;
        return $object;
    }

    static function getCurrencyNational()
    {
        $auth = $_SESSION["auth"];
        $anhoConfig = self::getAnhoConfig($auth->id_entidad);
        // if(!$anhoConfig->isValid) {
        // throw new Exception("Ocurrió un error, no esta configurado el año contable para la entidad", 203);
        // abort(203, "Ocurrió un error, no esta configurado el año contable para la entidad");
        // }
        return (int) $anhoConfig->config->id_moneda;
    }
    static function getCurrencyNationalByIdEntidad($id_entidad)
    {
        // $auth = $_SESSION["auth"];
        $anhoConfig = self::getAnhoConfig($id_entidad);
        // if(!$anhoConfig->isValid) {
        // throw new Exception("Ocurrió un error, no esta configurado el año contable para la entidad", 203);
        // abort(203, "Ocurrió un error, no esta configurado el año contable para la entidad");
        // }
        return (int) $anhoConfig->config->id_moneda;
    }

    static function isCurrencyNational($id_moneda)
    {
        return (int) $id_moneda === self::getCurrencyNational();
    }

    static function getTypeChange($id_moneda, $fecha, $costo = 'compra')
    {
        $idCurrencyNational = self::getCurrencyNational();
        return self::getTypeChangeMain($idCurrencyNational, $id_moneda, $fecha, $costo);
    }

    static function getTypeChangeMain($id_moneda_main, $id_moneda, $fecha, $costo = 'compra')
    {
        $fechaString = isset($fecha) ? Carbon::parse($fecha)->format('d/m/Y') : '';
        $tipoCambio = DB::table('ELISEO.TIPO_CAMBIO')
            ->where('id_moneda_main', $id_moneda_main)
            ->where('id_moneda', $id_moneda)
            ->whereRaw("TO_CHAR(fecha, 'DD/MM/YYYY') = ?", [$fechaString])
            ->first();
        $moneda = DB::table('eliseo.CONTA_MONEDA')->where('id_moneda', $id_moneda)->first();
        $monedaMain = DB::table('eliseo.CONTA_MONEDA')->where('id_moneda', $id_moneda_main)->first();
        if (is_null($tipoCambio)) {
            throw new Exception("Ocurrió un error, no existe el tipo de cambio de $moneda->siglas a $monedaMain->siglas para la fecha: $fecha", 203);
            // abort(203, "Ocurrió un error, no existe el tipo de cambio de $moneda->siglas a $monedaMain->siglas para la fecha: $fecha");
        }
        // a pedido de jesus para las compra el tipo de cambio es venta. 
        return (float) ($costo === 'compra' ? $tipoCambio->cos_compra : $tipoCambio->cos_venta);
    }

    /**
     * Convertir el importe a su equivalente en moneda local.
     * @id_moneda Ingresar aquí la moneda en la que se hace la operación
     * @fecha Ingresar aquí la fecha en la que se hace la operación
     * @importe Ingresar aquí el importe de la operación
     */
    //$costo variable que define la opción de tipo de cambio.
    static function getImporte($id_moneda, $fecha, $importe, $costo)
    {
        if (empty($costo)) {
            throw new Exception("Alto! es necesario definir la opción de tipo de cambio");
        }
        $impte = 0;
        $impteMe = 0;
        $tipoCambio = 0;
        if (self::isCurrencyNational($id_moneda)) {
            $impte = (float) $importe;
            $impteMe = 0;
        } else {
            $tipoCambio = self::getTypeChange($id_moneda, $fecha, $costo);
            $impte = round($tipoCambio *  (float)$importe, 2);
            $impteMe = round($importe, 2);
        }
        $object = new \stdClass;
        $object->importe = $impte;
        $object->importeMe = $impteMe;
        $object->tipoCambio = $tipoCambio;
        return $object;
    }

    static function getImporteAvanzado($dataOrigen, $dataDestino, $importe_input, $importeMe_input = 0, $costo = 'compra')
    {
        if (self::isCurrencyNational($dataDestino->id_moneda)) {
            if ($dataDestino->id_moneda == $dataOrigen->id_moneda) {
                // Cuando el pago es en moneda nacional y la provisión tbn, solo recuperar el importe.
                return self::getImporte($dataDestino->id_moneda, $dataDestino->fecha_emision, $importe_input, $costo);
            } else {
                // print($importeMe_input);
                // Cuando el pago es en moneda nacional y la provisión no, recuperar el importe e importe_me
                $tipocambio = self::getTypeChangeMain($dataDestino->id_moneda, $dataOrigen->id_moneda, strtotime($dataDestino->fecha_emision), $costo);
                $object = new \stdClass;
                $object->importe = $importe_input;
                // $object->importeMe = round(($importe_destino / $tipocambio),2);
                // se cambio por que cuando la provisición se hizo en moneda extrajera el calculo se hace por el porcentaje 
                $object->importeMe = $importeMe_input > 0 ? round(($importeMe_input), 2) : round(($dataOrigen->importe_me / $tipocambio), 2);
                $object->tipoCambio = $tipocambio;
                return $object;
            }
        } else {
            if ($dataDestino->id_moneda === $dataOrigen->id_moneda) {
                // Cuando el pago es en moneda extranjera y su compra es en la misma moneda.
                return self::getImporte($dataDestino->id_moneda, $dataDestino->fecha_emision, $importe_input, $costo);
            } else {
                // if(self::isCurrencyNational($id_moneda_origen)) {
                // Cuando el pago es moneda extrajera, y la compra es en moneda nacional.
                $object = new \stdClass;
                $object->importe = $importe_input; // El importe ya debería venir convertido
                $object->importeMe = round(($importe_input / $dataDestino->tipocambio), 2);
                $object->tipoCambio = $dataDestino->tipocambio;
                return $object;
                // } else {
                // Cuando el pago es moneda extrajera, y la compra es en diferente monedas.
                // }
            }
        }
    }


    static function getIdVoucher($bindings)
    {
        $idVoucher = 0;
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("BEGIN ELISEO.PKG_ACCOUNTING.SP_CREAR_VOUCHER(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO,  :P_ID_MES, :P_FECHA, :P_ID_TIPOASIENTO, :P_ID_TIPOVOUCHER, :P_ID_SEAT_PARENT, :P_ACTIVO, :P_ID_PERSONA, :P_ID_VOUCHER); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $bindings['P_ID_ENTIDAD'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $bindings['P_ID_DEPTO'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $bindings['P_ID_ANHO'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $bindings['P_ID_MES'], PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA', $bindings['P_FECHA'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOASIENTO', $bindings['P_ID_TIPOASIENTO'], PDO::PARAM_STR);

            $stmt->bindParam(':P_ID_TIPOVOUCHER', $bindings['P_ID_TIPOVOUCHER'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SEAT_PARENT', $bindings['P_ID_SEAT_PARENT'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ACTIVO', $bindings['P_ACTIVO'], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PERSONA', $bindings['P_ID_PERSONA'], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VOUCHER', $idVoucher, PDO::PARAM_INT);
            $stmt->execute();
            return $idVoucher;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function genereSerieContador($id_entidad, $id_depto)
    {
        $contaEmpresa = DB::table('ELISEO.CONTA_ENTIDAD AS A')
            ->join('ELISEO.CONTA_EMPRESA AS B', 'A.ID_EMPRESA', '=', 'B.ID_EMPRESA')
            ->select('B.ID_TIPOPAIS')
            ->where('A.ID_ENTIDAD', $id_entidad)
            ->first();
        if (is_null($contaEmpresa)) {
            return null;
        }
        $comprobante = DB::table('ELISEO.TIPO_COMPROBANTE')
            ->select('ID_COMPROBANTE')
            ->where('ID_TIPOPAIS', $contaEmpresa->id_tipopais)
            ->where('CODIGO', '00')
            ->first();
        if (is_null($comprobante)) {
            return null;
        }
        $contaDocumento = DB::table('ELISEO.CONTA_DOCUMENTO')
            ->select('ID_DOCUMENTO', 'SERIE', 'CONTADOR')
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID_COMPROBANTE', $comprobante->id_comprobante)
            ->where('ACTIVO', '1')
            ->first();
        if (is_null($contaDocumento)) {
            return null;
        }
        $generado = ['serie' => $contaDocumento->serie, 'contador' => $contaDocumento->contador];
        $nuevo_contador = $contaDocumento->contador + 1;
        DB::table('ELISEO.CONTA_DOCUMENTO')
            ->where('ID_DOCUMENTO', $contaDocumento->id_documento)
            ->update(['contador' => $nuevo_contador]);
        return $generado;
    }

    // genera el la serie, numero del documento afiliado a un punto de impresión de una persona en especifico.
    public static function genereSerieContadorDocPrint($id_entidad, $id_depto, $id_persona, $cod_electronico, $cod_comprobante_afecto = null)
    {
        $contaEmpresa = DB::table('ELISEO.CONTA_ENTIDAD AS A')
            ->join('ELISEO.CONTA_EMPRESA AS B', 'A.ID_EMPRESA', '=', 'B.ID_EMPRESA')
            ->select('B.ID_TIPOPAIS')
            ->where('A.ID_ENTIDAD', $id_entidad)
            ->first();
        if (is_null($contaEmpresa)) {
            abort(422, "Un momento, no existe empresa para la entidad " . $id_entidad);
            // return null;
        }
        $comprobante = DB::table('ELISEO.TIPO_COMPROBANTE')
            ->select('ID_COMPROBANTE')
            ->where('ID_TIPOPAIS', $contaEmpresa->id_tipopais)
            ->where('COD_ELECTRONICO', $cod_electronico)
            ->first();
        if (is_null($comprobante)) {
            abort(422, "Un momento, no existe un comprobante con código: " . $cod_electronico);
            // return null;
        }
        $comprobanteAfecto = null;
        if (!is_null($cod_comprobante_afecto)) {
            $comprobanteAfecto = DB::table('ELISEO.TIPO_COMPROBANTE')
                ->select('ID_COMPROBANTE')
                ->where('ID_TIPOPAIS', $contaEmpresa->id_tipopais)
                ->where('COD_ELECTRONICO', $cod_comprobante_afecto)
                ->first();
            if (is_null($comprobanteAfecto)) {
                abort(422, "Un momento, no está configurado el fin_documento_depto para el comprobante: id_comprobante: " . $comprobante->id_comprobante . "; id_entidad: " . $id_entidad . "; id_depto: " . $id_depto);
                // return null;
            }
        }

        $contaDocumento = DB::table('ELISEO.CONTA_DOCUMENTO AS A')
            ->join(
                'ELISEO.CONTA_DOCUMENTO_IP AS B',
                'A.ID_DOCUMENTO',
                '=',
                DB::raw('B.ID_DOCUMENTO AND B.ESTADO = 1')
            )
            ->join("ELISEO.CONTA_DOCUMENTO_IP_USER AS C", 'B.ID_DOCIP', '=', DB::raw("C.ID_DOCIP AND C.ID = $id_persona"))
            ->select('A.ID_DOCUMENTO', 'A.SERIE', 'A.CONTADOR')
            ->where('A.ID_ENTIDAD', $id_entidad)
            ->where('A.ID_DEPTO', $id_depto);
        if (!is_null($comprobanteAfecto)) {
            $contaDocumento->where('ID_COMPROBANTE_AFECTO', $comprobanteAfecto->id_comprobante);
        }
        $contaDocumento = $contaDocumento->where('ID_COMPROBANTE', $comprobante->id_comprobante)
            ->where('ACTIVO', '1')
            ->first();
        if (is_null($contaDocumento)) {
            abort(422, "Un momento, no está configurado el documento para el comprobante: id_documento: " . $finDocumentoDepto->id_documento . "; id_entidad: " . $id_entidad . "; id_depto: " . $id_depto);
            // return null;
        }

        $generado = ['serie' => $contaDocumento->serie, 'contador' => $contaDocumento->contador];

        $nuevo_contador = $contaDocumento->contador + 1;
        DB::table('ELISEO.CONTA_DOCUMENTO')
            ->where('ID_DOCUMENTO', $contaDocumento->id_documento)
            ->update(['contador' => $nuevo_contador]);
        return $generado;
    }

    public static function getOperacionDinamica($id_entidad, $id_depto, $codigo_operacion)
    {

        // $oQuery = DB::table('ELISEO.CONTA_OPERACION_DINAMICA AS COD')
        // ->join('ELISEO.CONTA_OPERACION AS CO', 'CO.ID_CONTA_OPERACION', '=', 
        // DB::raw("COD.ID_CONTA_OPERACION AND CO.CODIGO = '".$codigo_operacion."'"))
        // ->join('ELISEO.CONTA_DINAMICA AS DIN','DIN.ID_DINAMICA','=','COD.ID_DINAMICA')
        // ->where('COD.ID_ENTIDAD', $id_entidad)
        // ->where('COD.ID_DEPTO', $id_depto)
        // ->select(DB::raw("COD.*"),'DIN.ID_TIPOTRANSACCION')->first();
        // return $oQuery;
        $deptos = [$id_depto];
        $item = DB::table('eliseo.conta_operacion_dinamica as cod')
            ->join('eliseo.conta_operacion as co', function ($join) use ($codigo_operacion) {
                $join->on('co.id_conta_operacion', '=', 'cod.id_conta_operacion')
                    ->where('co.codigo', '=', $codigo_operacion);
            })
            ->join('eliseo.conta_dinamica as din', 'din.id_dinamica', '=', 'cod.id_dinamica')
            ->where('cod.id_entidad', $id_entidad)
            ->whereIn('cod.id_depto', $deptos)
            ->select('cod.*', 'din.id_tipotransaccion')
            ->first();
        if (!$item) {
            abort(422, "Un momento, falta configurar la dinámica con el codigo de operación " . $codigo_operacion . ".");
        }
        return $item;
    }

    public static function getConfigVoucher($id_entidad, $id_depto, $id_anho, $id_tipovoucher)
    {
        // $oQuery = DB::table('ELISEO.CONTA_VOUCHER_CONFIG AS CVC')
        // ->where('CVC.ID_ENTIDAD', $id_entidad)
        // ->where('CVC.ID_DEPTO', $id_depto)
        // ->where('CVC.ID_ANHO', $id_anho)
        // ->where('CVC.ID_TIPOVOUCHER', $id_tipovoucher)
        // ->select(DB::raw("CVC.*"))->first();
        // return $oQuery;

        $item = DB::table('eliseo.conta_voucher_config as cvc')
            ->where('cvc.id_entidad', $id_entidad)
            ->where('cvc.id_depto', $id_depto)
            ->where('cvc.id_anho', $id_anho)
            ->where('cvc.id_tipovoucher', $id_tipovoucher)->first();
        if (!$item) {
            abort(422, "Un momento, falta configurar el voucher de tipo " . $id_tipovoucher . ".");
        }
        return $item;
    }
}
