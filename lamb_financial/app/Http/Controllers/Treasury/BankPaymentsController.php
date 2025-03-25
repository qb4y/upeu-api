<?php


namespace App\Http\Controllers\Treasury;


use App\Http\Controllers\Purchases\PurchasesController;
use App\Http\Controllers\Purchases\Utils\PurchasesUtil;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\GlobalMethods;
use App\Models\PedidoFile as EliseoPedidoFile;
use App\Http\Data\Purchases\PurchasesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;

class BankPaymentsController
{

    public function index(Request $request)
    {
        $response = array();
        $auth = GlobalMethods::authorizationLamb($request);
        $response['message'] = $auth['message'];
        $response['success'] = $auth['success'];
        $code = $auth["code"];
        $valida = $auth["valida"];

        $id_user = $auth["id_user"];
        $id_entidad = $auth["id_entidad"];
        $id_depto = $auth["id_depto"];

        if ($valida == 'SI') {
            $request->id_entidad = $id_entidad;
            $request->id_depto = $id_depto;
            $code = 200;
            $response['message'] = 'ok';
            $response['success'] = true;
            $response['data'] = self::getData($request);
            $response['actions'] = self::getActions($auth);

        }
        return response()->json($response, $code);

    }

    public function store(Request $request)
    {

        $response = array();
        $auth = GlobalMethods::authorizationLamb($request);
        $response['message'] = $auth['message'];
        $response['success'] = $auth['success'];
        $code = $auth["code"];
        $valida = $auth["valida"];
        $id_user = $auth["id_user"];

        if ($valida == 'SI') {
            $code = 200;
            $response['message'] = 'ok';
            $response['success'] = true;
            $response['data'] = [];

            $data = array();
            $data['compras'] = explode(',', Input::get('compras'));
            $data['id_ctabancaria'] = Input::get('id_ctabancaria');
            $data['numero'] = Input::get('numero');
            $data['fecha'] = Input::get('fecha');

            $data = self::saveData($data, $id_user);

            $response['message'] = $data['message'];
            $response['success'] = $data['success'];
            $response['data'] = $data['data'];

            if (!$data['success']) {
                $code = 500;
            }

        }
        return response()->json($response, $code);

    }

    public function destroy($id_pagado, Request $request)
    {

        $response = array();
        $auth = GlobalMethods::authorizationLamb($request);
        $response['message'] = $auth['message'];
        $response['success'] = $auth['success'];
        $code = $auth["code"];
        $valida = $auth["valida"];
        $id_pcompra = $request->id_pcompra;
        $id_pedido = $request->id_pedido;

        if ($valida == 'SI') {
            DB::beginTransaction();
            try {
                $code = 200;
                $response['message'] = 'ok destroy';
                $response['success'] = true;
                $response['data'] = self::delete($id_pagado, $id_pcompra, $id_pedido);
            } catch (\Exception $e) {
                $code = 500;
                $response['success'] = false;
                $response['data'] = [];
                $response['message'] = $e->getMessage();
                DB::rollback();
            }
            DB::commit();

        }
        return response()->json($response, $code);

    }


    public static function getActions($user){
        $modulo = 'bank-payments';
        $codigo =  'BANKPAY';//'MAKE_PAYMENT';
        $id_persona = $user['id_user'];
        $id_entidad = $user['id_entidad'];

        $query = "SELECT a.NOMBRE ,a.CLAVE,
        (case when exists(select * from ELISEO.LAMB_ROL_MODULO_ACCION e left join ELISEO.LAMB_USUARIO_ROL u on (e.ID_ROL = u.ID_ROL)
        where u.ID_PERSONA=:id_persona_p and u.ID_ENTIDAD=:id_entidad_p AND e.ID_MODULO=a.ID_MODULO AND e.ID_ACCION= a.ID_ACCION) THEN '1' else '0' end) as can_see
        FROM ELISEO.LAMB_ACCION a left join ELISEO.LAMB_MODULO b ON (a.ID_MODULO = b.ID_MODULO)
        where b.URL = :modulo_p  and b.CODIGO = :codigo_p";
        $data = DB::select($query,
            ['id_persona_p' => $id_persona, 'id_entidad_p' => $id_entidad, 'modulo_p' => $modulo, 'codigo_p' => $codigo]);

        return collect($data)->map(function ($item, $key) {
            $item->can_see = boolval($item->can_see);
            return $item;
        })->pluck('can_see','clave');

    }


    static function saveData($data, $id_user)
    {

        DB::beginTransaction();

        $result = array();
        $success = array();
        $errors = array();

        $fileInput = Input::file('pdf');
        $fileRetencion = Input::file('retencion');
        $fileDetraccion = Input::file('detraccion');

        $fileSucess = self::insertFile($fileInput);

        if ($fileRetencion) {
            $fileRetencionSucess = self::insertFile($fileRetencion);
        } else {
            $fileRetencionSucess = null;
        }
        if ($fileDetraccion) {
            $fileDetraccionSucess = self::insertFile($fileDetraccion);
        } else {
            $fileDetraccionSucess = null;
        }


        foreach ($data['compras'] as $value) {

            $current = array();
            $d_compra = explode('|', $value)[0];
            $d_pedido = explode('|', $value)[1];
            $d_pcompra = explode('|', $value)[2];

            $tiene_retencion = explode('|', $value)[3]; //'Retencion'
            $tiene_detraccion = explode('|', $value)[4]; //'Detraccion'

            $item = array();
            $item['id_pagado'] = PurchasesData::getMax('CAJA_PAGO_PROCESO', 'id_pagado') + 1;
            $item['id_persona'] = $id_user;
            $item['id_ctabancaria'] = $data['id_ctabancaria'];
            $item['numero'] = $data['numero'];
            $item['fecha'] = $data['fecha'];
            $item['estado'] = 1;
            $item['id_compra'] = $d_compra;

            $pago = self::savePagoProceso($item);
            $file = self::uploadPedidoFile($d_pedido, $d_pcompra, $fileSucess, 12);


            if ($fileRetencionSucess and $tiene_retencion == 'S') {
                $fileRetencionData = self::uploadPedidoFile($d_pedido, $d_pcompra, $fileRetencionSucess, 13);
            } else {
                $fileRetencionData = null;
            }
            if ($fileDetraccionSucess and $tiene_detraccion == 'S') {
                $fileDetraccionData = self::uploadPedidoFile($d_pedido, $d_pcompra, $fileDetraccionSucess, 14);
            } else {
                $fileDetraccionData = null;
            }


            $current['pago'] = $pago;
            $current['file'] = $file;
            $current['fileRetencion'] = $fileRetencionData;
            $current['fileDetraccion'] = $fileDetraccionData;

            if ($pago['success'] == true && $file['success'] == true) {
                array_push($success, $current);
            } else {
                array_push($errors, $current);
            }
        }


        if (count($errors) > 0) {
            $result['success'] = false;
            $result['data'] = $errors;
            $result['message'] = 'Error al realizar transacción';
            DB::rollback();
        } else {
            $result['success'] = true;
            $result['data'] = $success;
            $result['message'] = 'Accion correcta';
            DB::commit();
        }
        return $result;
    }

    static function savePagoProceso($value)
    {
        $result = array();
        try {
            $result['success'] = true;
            $result['data'] = DB::table('ELISEO.CAJA_PAGO_PROCESO')->insert($value);
            $result['message'] = 'datos insertados correctamente en caja pago proceso';
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['data'] = [];
            $result['message'] = $e->getMessage();
        }
        return $result;
    }


    static function delete($id_pagado, $id_pcompra, $id_pedido)
    {
        $url = DB::table('ELISEO.PEDIDO_FILE a')
            ->select('a.url')
            ->where('a.ID_PCOMPRA', '=', $id_pcompra)
            ->where('a.ID_PEDIDO', '=', $id_pedido)
            ->whereIn('a.TIPO', [12, 13, 14])
            ->pluck('url')
            ->first();

        if ($url) {
            $exists =DB::table('ELISEO.PEDIDO_FILE a')
                ->select('a.url')
                ->where('a.url', '=', $url)
                ->get();

            if (count($exists) == 1) {
                File::delete($url);
            }
        }

        DB::table('ELISEO.PEDIDO_FILE a')
            ->select('a.url')
            ->where('a.ID_PCOMPRA', '=', $id_pcompra)
            ->where('a.ID_PEDIDO', '=', $id_pedido)
            ->whereIn('a.TIPO', [12, 13, 14])
            ->delete();

        DB::table('ELISEO.CAJA_PAGO_PROCESO a')->where('a.id_pagado', '=', $id_pagado)->delete();

        return $url;



    }


    static function getData($params)
    {
        $id_entidad = $params->id_entidad;
        $id_depto = $params->id_depto;
        $id_anho = $params->id_anho;
        $id_mes = $params->id_mes;
        $id_voucher = $params->id_voucher;
        $id_persona = $params->id_persona;
        $tramite_pago = $params->tramite_pago;

        if ($tramite_pago != null) {
            $tramite_pago = 'AND B.TRAMITE_PAGO = ' . $tramite_pago;
        }

        $selectedOptionChunk = '';
        $selectedOption = $params->selectedOption;

        if ($selectedOption == 1) {
            $selectedOptionChunk = 'AND A.ID_COMPRA NOT IN (SELECT ID_COMPRA FROM ELISEO.CAJA_PAGO_PROCESO)';
        } elseif ($selectedOption == 2) {
            $selectedOptionChunk = 'AND A.ID_COMPRA IN (SELECT ID_COMPRA FROM ELISEO.CAJA_PAGO_PROCESO)';
        }


        return DB::select("SELECT A.ID_COMPRA,
                   A.ID_PROVEEDOR,
                   (SELECT X.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_PROVEEDOR)                AS PROVEEDOR,
                   (SELECT X.ID_RUC
                    FROM MOISES.VW_PERSONA_JURIDICA X
                    WHERE X.ID_PERSONA = A.ID_PROVEEDOR)                                                                  AS DOCUMENTO,
                   A.ID_COMPROBANTE,
                   (SELECT TO_CHAR(X.FECHA_DOC, 'DD/MM/YYYY')
                    FROM ELISEO.COMPRA X
                    WHERE X.ID_COMPRA = A.ID_COMPRA)                                                                      AS FECHA_DOC,
                   (SELECT TO_CHAR(X.FECHA_PROVISION, 'DD/MM/YYYY')
                    FROM ELISEO.COMPRA X
                    WHERE X.ID_COMPRA = A.ID_COMPRA)                                                                      AS FECHA_PROVISION,
                   A.SERIE,
                   A.NUMERO,
                   SUM(A.IMPORTE)                                                                                            IMPORTE,
                   NVL(SUM(A.IMPORTE_ME), 0)                                                                                 IMPORTE_ME,
                   SUM(A.IMPORTE_DOC)                                                                                        IMPORTE_DOC,
                   C.FECHA_PAGO,
                   C.ID_PEDIDO,
                   B.TRAMITE_PAGO,
                   B.ID_PCOMPRA,
                   (SELECT ID_PAGADO FROM ELISEO.CAJA_PAGO_PROCESO g where g.ID_COMPRA = a.ID_COMPRA ) as ID_PAGADO,
                   (SELECT MAX(ID_PCOMPRA) FROM ELISEO.CAJA_PAGO_COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA) AS ID_PROCESADO,
                   (SELECT MAX(Y.SIGLA || '-' || X.CUENTA)
                    FROM MOISES.PERSONA_CUENTA_BANCARIA X
                             JOIN ELISEO.CAJA_ENTIDAD_FINANCIERA Y ON X.ID_BANCO = Y.ID_BANCO
                    WHERE X.ID_PERSONA = DECODE(B.TRAMITE_PAGO, '1', A.ID_PROVEEDOR, B.ID_PERSONA))        as CTA,
                    ELISEO.FC_NOMBRE_CLIENTE(B.ID_PERSONA) AS FUNCIONARIO,
                    ELISEO.FC_USERNAME(C.ID_PERSONA) AS USERS,
                    (select DECODE(D.ES_RET_DET, 'R', 'Retención', 'D', 'Detracción', '') from COMPRA D where D.ID_COMPRA = A.ID_COMPRA) as RET_DET
            FROM ELISEO.VW_PURCHASES_MOV A
                     JOIN ELISEO.PEDIDO_COMPRA B ON A.ID_COMPRA = B.ID_COMPRA
                     JOIN ELISEO.PEDIDO_REGISTRO C ON C.ID_PEDIDO = B.ID_PEDIDO
            WHERE A.ID_ENTIDAD = ".$id_entidad."
              AND A.ID_DEPTO = '".$id_depto."'
            --FILTROS
            --1.FECHA
              AND A.ID_VOUCHER = NVL(:id_voucher, A.ID_VOUCHER)
            --2.RUC
              AND A.ID_PROVEEDOR = NVL(:id_persona, A.ID_PROVEEDOR)
            --3.TODOS
              AND A.ID_ANHO = :id_anho
              AND A.ID_MES <= :id_mes
              AND TO_NUMBER(TO_CHAR(NVL(C.FECHA_PAGO,SYSDATE),'MM')) <= ".$id_mes."
            --
            " . $tramite_pago . "
             --AND B.TRAMITE_PAGO = NVL(:tramite_pago, B.TRAMITE_PAGO)
            " . $selectedOptionChunk . "
            HAVING SUM(A.IMPORTE) + NVL(SUM(A.IMPORTE_ME), 0) <> 0
            GROUP BY A.ID_COMPRA, A.ID_PROVEEDOR, A.ID_COMPROBANTE, A.SERIE, A.NUMERO, C.FECHA_PAGO, C.ID_PEDIDO, B.TRAMITE_PAGO, B.ID_PCOMPRA, B.ID_PERSONA, C.ID_PERSONA
            ORDER BY A.ID_PROVEEDOR, FECHA_PROVISION DESC, A.ID_COMPRA",
            [
                'id_anho' => $id_anho,
                'id_mes' => $id_mes,
                'id_voucher' => $id_voucher,
                'id_persona' => $id_persona,
            ]);
    }


    static function uploadPedidoFile($id_pedido, $id_pcompra, $fileSucess, $tipo)
    {
        $result = array();
        try {
            $estado = "1";

            $data = self::savePedidoFile($id_pedido, $fileSucess['originalName'], $fileSucess['formato'], $fileSucess['url'], $tipo, $estado, $id_pcompra, $fileSucess['size']);

            $result['success'] = $data['success'];
            $result['data'] = $data['data'];
            $result['message'] = $data['message'];

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['data'] = [];
            $result['message'] = $e->getMessage();
        }
        return $result;

    }

    static function insertFile($file) {
        // $destinationPath = 'purchases_files/proformas';
        // $nameRandon = PurchasesUtil::getGenereNameRandom(17);
        // $nombreDinamico = $nameRandon . "." . $file->getClientOriginalExtension();
        // $data['url'] = $destinationPath . "/" . $nombreDinamico;
        // $file->move($destinationPath, $nombreDinamico);

        $destinationPath = 'lamb-financial/purchases/proformas';
        $storage = new StorageController(); 
        $fileAdjunto = $storage->postFile($file, $destinationPath);
        // $nombre = explode("/",$fileAdjunto['data'])[4];
    
        $data = array();
        $data['formato'] = strtoupper($file->getClientOriginalExtension());
        $data['size'] = $file->getSize();
        $data['url'] = $fileAdjunto['data'];
        $data['originalName'] = $file->getClientOriginalName();

        return $data;
    }



    static function savePedidoFile($id_pedido, $nombre, $formato, $url, $tipo, $estado, $id_pcompra, $size)
    {
        $result = array();
        try {
            // $id_pfile = PurchasesData::getMax('pedido_file', 'id_pfile') + 1;
            $dataPedidoFile = array(
                // "id_pfile" => $id_pfile,
                "id_pedido" => $id_pedido,
                "nombre" => $nombre,
                "formato" => $formato,
                "url" => $url,
                "fecha" => DB::raw('sysdate'),
                "tipo" => $tipo,
                "tamanho" => $size,
                "estado" => $estado,
                "id_pcompra" => $id_pcompra
            );
            $pedidoFile = EliseoPedidoFile::create($dataPedidoFile);
            // $data = PurchasesData::addPedidoFile($dataPedidoFile);
            $result['success'] = true;
            $result['data'] = $pedidoFile;
            $result['message'] = 'Success';

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['data'] = [];
            $result['message'] = $e->getMessage();
        }
        return $result;
    }
}